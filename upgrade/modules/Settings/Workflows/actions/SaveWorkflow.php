<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Workflows_SaveWorkflow_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		
	}

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$summary = $request->get('summary');
		$moduleName = $request->get('module_name');
		$conditions = $request->get('conditions');
		$filterSavedInNew = $request->get('filtersavedinnew');
		$workflow_trigger = $request->get('workflow_trigger');
		$workflow_recurrence = $request->get('workflow_recurrence');
		$name = $request->get('workflowname');
		if ($workflow_trigger == 3) {
			$executionCondition = $workflow_recurrence;
		} else {
			$executionCondition = $workflow_trigger;
		}

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($request->getModule(false));
		if ($recordId) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
		} else {
			$workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
		}

		$status = $request->get('status');
		if ($status == "active") {
			$status = 1;
		} else {
			$status = 0;
		}
		require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';

		foreach ($conditions as $info) {
			foreach ($info['columns'] as $conditionRow) {
				if ($conditionRow['valuetype'] == "expression") {
					try {
						$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($conditionRow['value'])));
						$expression = $parser->expression();
					} catch (Exception $e) {
						//It should generally not come in to this block of code , Since before save we will be checking expression validation as 
						//Seperte ajax request
						echo $e->getMessage();
						die;
					}
				}
			}
		}

		$workflowModel->set('summary', $summary);
		$workflowModel->set('module_name', $moduleName);
		$workflowModel->set('conditions', $conditions);
		$workflowModel->set('execution_condition', $executionCondition);
		$workflowModel->set('status', $status);
		$workflowModel->set('name', $name);
		if ($executionCondition == '6') {
			$schtime = $request->get("schtime");
			if (!preg_match('/^[0-2]\d(:[0-5]\d){1,2}$/', $schtime) or substr($schtime, 0, 2) > 23) {  // invalid time format
				$schtime = '00:00';
			}
			$schtime .=':00';

			$workflowModel->set('schtime', $schtime);

			$workflowScheduleType = $request->get('schtypeid');
			$workflowModel->set('schtypeid', $workflowScheduleType);

			$dayOfMonth = null;
			$dayOfWeek = null;
			$month = null;
			$annualDates = null;

			if ($workflowScheduleType == Workflow::$SCHEDULED_WEEKLY) {
				$dayOfWeek = Zend_Json::encode(explode(',', $request->get('schdayofweek')));
			} else if ($workflowScheduleType == Workflow::$SCHEDULED_MONTHLY_BY_DATE) {
				$dayOfMonth = Zend_Json::encode($request->get('schdayofmonth'));
			} else if ($workflowScheduleType == Workflow::$SCHEDULED_ON_SPECIFIC_DATE) {
				$date = $request->get('schdate');
				$dateDBFormat = DateTimeField::convertToDBFormat($date);
				$nextTriggerTime = $dateDBFormat . ' ' . $schtime;
				$currentTime = Vtiger_Util_Helper::getActiveAdminCurrentDateTime();
				if ($nextTriggerTime > $currentTime) {
					$workflowModel->set('nexttrigger_time', $nextTriggerTime);
				} else {
					$workflowModel->set('nexttrigger_time', date('Y-m-d H:i:s', strtotime('+10 year')));
				}
				$annualDates = Zend_Json::encode(array($dateDBFormat));
			} else if ($workflowScheduleType == Workflow::$SCHEDULED_ANNUALLY) {
				$annualDates = Zend_Json::encode($request->get('schannualdates'));
			}
			$workflowModel->set('schdayofmonth', $dayOfMonth);
			$workflowModel->set('schdayofweek', $dayOfWeek);
			$workflowModel->set('schannualdates', $annualDates);
		}

		// Added to save the condition only when its changed from vtiger6
		if ($filterSavedInNew == '6') {
			//Added to change advanced filter condition to workflow
			$workflowModel->transformAdvanceFilterToWorkFlowFilter();
		}
		$workflowModel->set('filtersavedinnew', $filterSavedInNew);

		if ($executionCondition == '6') {
			if ($workflowScheduleType == Workflow::$SCHEDULED_HOURLY) {
				$workflowModel->set('nexttrigger_time', $workflowModel->getWorkflowObject()->getNextTriggerTimeValue());
			}
			$workflowModel->save();
			//Update only for scheduled workflows other than specific date
			if (($workflowScheduleType != Workflow::$SCHEDULED_ON_SPECIFIC_DATE || $workflowScheduleType == Workflow::$SCHEDULED_HOURLY) && $executionCondition == '6') {
				$workflowModel->updateNextTriggerTime();
			}
		} else {
			$workflowModel->save();
		}

		$this->saveTasks($workflowModel, $request);

		$returnPage = $request->get("returnpage", null);
		$returnSourceModule = $request->get("returnsourcemodule", null);
		$returnSearchValue = $request->get("returnsearch_value", null);
		$redirectUrl = $moduleModel->getDefaultUrl() . "&sourceModule=$returnSourceModule&page=$returnPage&search_value=$returnSearchValue";

		header("Location: " . $redirectUrl);
	}

	function saveTasks($workflowModel, $request) {
		$tasks = $request->getRaw('tasks');
		$id = $workflowModel->get('workflow_id');
		if (!empty($tasks)) {
			foreach ($tasks as $task) {
				$taskDecodedArray = json_decode($task, true);
				$taskAjaxObject = new Settings_Workflows_TaskAjax_Action();
				$request = new Vtiger_Request($taskDecodedArray, $taskDecodedArray);
				$request->set('for_workflow', $id);
				$taskAjaxObject->process($request);
			}
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
