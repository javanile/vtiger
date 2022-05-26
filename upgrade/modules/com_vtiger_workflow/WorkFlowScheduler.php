<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once ('modules/com_vtiger_workflow/WorkflowScheduler.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once 'modules/Users/Users.php';

class WorkFlowScheduler {

	private $user;
	private $db;

	public function __construct($adb) {
		$util = new VTWorkflowUtils();
		$adminUser = $util->adminUser();
		$this->user = $adminUser;
		$this->db = $adb;
	}

	public function getWorkflowQuery($workflow, $start=0, $limit=0) {
		$conditions = Zend_Json :: decode(decode_html($workflow->test));

		$moduleName = $workflow->moduleName;
		$queryGenerator = new EnhancedQueryGenerator($moduleName, $this->user);
		$queryGenerator->setFields(array('id'));
		$this->addWorkflowConditionsToQueryGenerator($queryGenerator, $conditions);

		if($moduleName == 'Calendar' || $moduleName == 'Events') {
            if($conditions){
			$queryGenerator->addConditionGlue('AND');
            }
            // We should only get the records related to proper activity type
            if($moduleName == 'Calendar'){
                $queryGenerator->addCondition('activitytype','Emails','n');
                $queryGenerator->addCondition('activitytype','Task','e','AND');
            }else if($moduleName == "Events"){
			$queryGenerator->addCondition('activitytype','Emails','n');
                $queryGenerator->addCondition('activitytype','Task','n','AND');
            }
		}

		$query = $queryGenerator->getQuery();
		if($limit) {
			$query .= ' LIMIT '. ($start * $limit) . ',' .$limit;
		}
		return $query;
	}

	public function getEligibleWorkflowRecords($workflow, $start=0, $limit=0) {
		$adb = $this->db;
		$query = $this->getWorkflowQuery($workflow, $start, $limit);
		$result = $adb->query($query);
		$noOfRecords = $adb->num_rows($result);
		$recordsList = array();
		for ($i = 0; $i < $noOfRecords; ++$i) {
			$recordsList[] = $adb->query_result($result, $i, 0);
		}
		$result = null;
		return $recordsList;
	}

	public function queueScheduledWorkflowTasks() {
		global $default_timezone;
        $scheduleDates = array();
		$adb = $this->db;

		$vtWorflowManager = new VTWorkflowManager($adb);
		$taskQueue = new VTTaskQueue($adb);
		$entityCache = new VTEntityCache($this->user);

		// set the time zone to the admin's time zone, this is needed so that the scheduled workflow will be triggered
		// at admin's time zone rather than the systems time zone. This is specially needed for Hourly and Daily scheduled workflows
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);
		$currentTimestamp  = date("Y-m-d H:i:s");
		@date_default_timezone_set($default_timezone);

		$scheduledWorkflows = $vtWorflowManager->getScheduledWorkflows($currentTimestamp);
		$noOfScheduledWorkflows = count($scheduledWorkflows);
		for ($i = 0; $i < $noOfScheduledWorkflows; ++$i) {
			$workflow = $scheduledWorkflows[$i];
			$tm = new VTTaskManager($adb);
			$tasks = $tm->getTasksForWorkflow($workflow->id);
			if ($tasks) {
				// atleast one task for the workflow should be active
				$taskActive = false;
				foreach ($tasks as $task) {
					if ($task->active) {
						$taskActive = true;
					}
				}

				if(!$taskActive) continue;
				$page = 0;
				do {
					$records = $this->getEligibleWorkflowRecords($workflow, $page++, 100);
					$noOfRecords = count($records);
					
					if ($noOfRecords < 1) break;
					
					for ($j = 0; $j < $noOfRecords; ++$j) {
						$recordId = $records[$j];
						// We need to pass proper module name to get the webservice 
						if($workflow->moduleName == 'Calendar') {
							$moduleName = vtws_getCalendarEntityType($recordId);
						} else {
							$moduleName = $workflow->moduleName;
						}
						$wsEntityId = vtws_getWebserviceEntityId($moduleName, $recordId);
						$entityData = $entityCache->forId($wsEntityId);
						$data = $entityData->getData();
						//Setting events contact_id values to $_REQUEST object as save_module function of Activity.php depends on $_REQUEST
						if($moduleName == 'Events') {
							Vtiger_Functions::setEventsContactIdToRequest($recordId);
						}
						foreach ($tasks as $task) {
							if ($task->active) {
								$delay = 0;
								 $taskClassName = get_class($task); 
								//Check whether task is VTEmailTask and then check emailoptout value 
								//if enabled don't queue the email 
								if($taskClassName == 'VTEmailTask'){ 
									if($data['emailoptout'] == 1) continue; 
								} 
								$trigger = $task->trigger;
								if ($trigger != null) {
									$delay = strtotime($data[$trigger['field']]) + $trigger['days'] * 86400;
								}
								// If task is scheduled then we have to schedule CronTx with that specified time
								$time = time();
								if($delay > 0 && $delay >= $time){
									$scheduleDates[] = gmdate('Y-m-d H:i:s',$delay);
								}else{
									$delay = 0;
								}

								if ($task->executeImmediately == true) {
									$task->doTask($entityData);
								} else {
									$taskQueue->queueTask($task->id, $entityData->getId(), $delay);
								}
							}
						}
					}
				} while(true);
			}
			$vtWorflowManager->updateNexTriggerTime($workflow);
		}
		$scheduledWorkflows = null;
	}

	function addWorkflowConditionsToQueryGenerator($queryGenerator, $conditions) {
		$conditionMapping = array(
			"equal to" => 'e',
			"less than" => 'l',
			"greater than" => 'g',
			"does not equal" => 'n',
			"less than or equal to" => 'm',
			"greater than or equal to" => 'h',
			"is" => 'e',
			"contains" => 'c',
			"does not contain" => 'k',
			"starts with" => 's',
			"ends with" => 'ew',
			"is not" => 'n',
			"is not empty" => 'n',
			'before' => 'l',
			'after' => 'g',
			'between' => 'bw',
			'less than days ago' => 'bw',
			'more than days ago' => 'l',
			'in less than' => 'bw',
			'in more than' => 'g',
			'days ago' => 'e',
			'days later' => 'e',
			'less than hours before' => 'bw',
			'less than hours later' => 'bw',
			'more than hours before' => 'l',
			'more than hours later' => 'g',
			'is today' => 'c',
            'is empty' => 'y',
            'is tomorrow' => 'c',
            'is yesterday' => 'c',
            'less than days later' => 'bw',
            'more than days later' => 'g',
		);
		$noOfConditions = count($conditions);
		//Algorithm :
		//1. If the query has already where condition then start a new group with and condition, else start a group
		//2. Foreach of the condition, if its a condition in the same group just append with the existing joincondition
		//3. If its a new group, then start the group with the group join.
		//4. And for the first condition in the new group, dont append any joincondition.

        if ($noOfConditions > 0) {
			if ($queryGenerator->conditionInstanceCount > 0) {
				$queryGenerator->startGroup(QueryGenerator::$AND);
			} else {
				$queryGenerator->startGroup('');
			}
            foreach ($conditions as $index => $condition) {
				$operation = $condition['operation'];

				//Cannot handle this condition for scheduled workflows
				if($operation == 'has changed') continue;
				if($operation == 'has changed to') continue;
				if($operation == 'has changed from') continue;

				$value = $condition['value'];
				if(in_array($operation, $this->_specialDateTimeOperator())) {
					$value = $this->_parseValueForDate($condition);
				}
				$columnCondition = $condition['joincondition'];
				$groupId = $condition['groupid'];
				$groupJoin = $condition['groupjoin'];
				$operator = $conditionMapping[$operation];
				$fieldname = $condition['fieldname'];
				$valueType = $condition['valuetype'];
                
                $specialDateComparator = array('is today', 'is tomorrow', 'is yesterday');
                if(strpos('birthday', $fieldname) !== false && in_array($operation, $specialDateComparator)) {
                    $operator = 'e';
                }

				if($index > 0 && $groupId != $conditions[$index-1]['groupid']) {	// if new group, end older group and start new
					$queryGenerator->endGroup();
					if($groupJoin) {
						$queryGenerator->startGroup($groupJoin);
					} else {
						$queryGenerator->startGroup(QueryGenerator::$AND);
					}
				}

				if($index > 0 && $groupId != $conditions[$index-1]['groupid']) {	//if first condition in new group, send empty condition to append
					$columnCondition = null;
				} else if(empty($columnCondition) && $index > 0) {
					$columnCondition = $conditions[$index-1]['joincondition'];
				}
				$value = html_entity_decode($value);
				preg_match('/(\w+) : \((\w+)\) (\w+)/', $condition['fieldname'], $matches);
				if (count($matches) != 0) {
					list($full, $referenceField, $referenceModule, $fieldname) = $matches;
				}
                if($fieldname == 'assigned_user_id') {
                    $userName = Vtiger_Functions::getUserRecordLabel($value);
                    if(empty($userName)) {
                        $userName = Vtiger_Functions::getGroupRecordLabel($value);
                    }
                    $value = $userName;
                }
				if($referenceField) {
					$moduleName = $referenceModule;
				} else {
					$moduleName = $queryGenerator->getModule();
				}
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
				$fieldInstance = Vtiger_Field_Model::getInstance($fieldname, $moduleModel);
				if($fieldInstance && $fieldInstance->getFieldDataType() == 'datetime' && $operator == 'e' && $operation != 'is empty') {
					$operator = 'c';
				}
				if($referenceField) {
					$referenceField = null;
                    // this is needed as enhanced querygenerator expects fieldname in (word ; (word) word) format
                    $replacedFieldName = str_replace(' : ', ' ; ', $condition['fieldname']);
                    $queryGenerator->addCondition($replacedFieldName, $value, $operator, $columnCondition);
				} else {
					$queryGenerator->addCondition($fieldname, $value, $operator, $columnCondition);
				}
			}
			$queryGenerator->endGroup();
		}
	}

	/**
	 * Special Date functions
	 * @return <Array>
	 */
	function _specialDateTimeOperator() {
		return array('less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later',
			'less than hours before', 'less than hours later', 'more than hours later', 'more than hours before', 'is today',
                    'is tomorrow', 'is yesterday', 'less than days later', 'more than days later');
	}

	/**
	 * Function parse the value based on the condition
	 * @param <Array> $condition
	 * @return <String>
	 */
	function _parseValueForDate($condition) {
		$value = $condition['value'];
		$operation = $condition['operation'];

		// based on the admin users time zone, since query generator expects datetime at user timezone
		global $default_timezone;
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);

		switch($operation) {
			case 'less than days ago' :		//between current date and (currentdate - givenValue)
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('-'.$days.' days')).','.date('Y-m-d', strtotime('+1 day'));
				break;

			case 'more than days ago' :		// less than (current date - givenValue)
				$days = $condition['value']-1;
				$value = date('Y-m-d', strtotime('-'.$days.' days'));
				break;

			case 'in less than' :			// between current date and future date(current date + givenValue)
				$days = $condition['value']+1;
				$value = date('Y-m-d', strtotime('-1 day')).','.date('Y-m-d', strtotime('+'.$days.' days'));
				break;

			case 'in more than' :			// greater than future date(current date + givenValue)
				$days = $condition['value']-1;
				$value = date('Y-m-d', strtotime('+'.$days.' days'));
				break;

			case 'days ago' :
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('-'.$days.' days'));
				break;

			case 'days later' :
				$days = $condition['value'];
				$value = date('Y-m-d', strtotime('+'.$days.' days'));
				break;

			case 'is today' :
				$value = date('Y-m-d');
				break;

			case 'less than hours before' :
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('-'.$hours.' hours')).','.date('Y-m-d H:i:s');
				break;

			case 'less than hours later' :
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s').','.date('Y-m-d H:i:s', strtotime('+'.$hours.' hours'));
				break;

			case 'more than hours later' :
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('+'.$hours.' hours'));
				break;

			case 'more than hours before' :
				$hours = $condition['value'];
				$value = date('Y-m-d H:i:s', strtotime('-'.$hours.' hours'));
				break;
            
            case 'is tomorrow' :
                $value = date('Y-m-d', strtotime('+1 days'));
                break;
            
            case 'is yesterday' :
                $value = date('Y-m-d', strtotime('-1 days'));
                break;
            
            case 'less than days later' :
                $days = $condition['value']+1;
				$value = date('Y-m-d', strtotime('-1 day')).','.date('Y-m-d', strtotime('+'.$days.' days'));
                break;
            
            case 'more than days later' :
                $days = $condition['value']-1;
				$value = date('Y-m-d', strtotime('+'.$days.' days'));
                break;
		}
		@date_default_timezone_set($default_timezone);
		return $value;
	}

}
