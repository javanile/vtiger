<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * Workflow Record Model Class
 */
require_once 'modules/com_vtiger_workflow/include.inc';
require_once 'modules/com_vtiger_workflow/expression_engine/VTExpressionsManager.inc';

class Settings_Workflows_Record_Model extends Settings_Vtiger_Record_Model {

	public function getId() {
		return $this->get('workflow_id');
	}

	public function getName() {
		return $this->get('summary');
	}

	public function get($key) {
//		if($key == 'execution_condition') {
//			$executionCondition = parent::get($key);
//			$executionConditionAsLabel = Settings_Workflows_Module_Model::$triggerTypes[$executionCondition];
//			return Vtiger_Language_Handler::getTranslatedString($executionConditionAsLabel, 'Settings:Workflows');
//		}
//		if($key == 'module_name') {
//			$moduleName = parent::get($key);
//			return Vtiger_Language_Handler::getTranslatedString($moduleName, $moduleName);
//		}
		return parent::get($key);
	}

	public function getEditViewUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=Edit&record='.$this->getId();
	}

	public function getTasksListUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=TasksList&record='.$this->getId();
	}

	public function getAddTaskUrl() {
		return 'index.php?module=Workflows&parent=Settings&view=EditTask&for_workflow='.$this->getId();
	}

	protected function setWorkflowObject($wf) {
		$this->workflow_object = $wf;
		return $this;
	}

	public function getWorkflowObject() {
		return $this->workflow_object;
	}

	public function getModule() {
		return $this->module;
	}

	public function setModule($moduleName) {
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	public function getTasks($active=false) {
		return Settings_Workflows_TaskRecord_Model::getAllForWorkflow($this, $active);
	}

	public function getTaskTypes() {
		return Settings_Workflows_TaskType_Model::getAllForModule($this->getModule());
	}

	public function isDefault() {
		$wf = $this->getWorkflowObject();
		if($wf->defaultworkflow == 1) {
			return true;
		}
		return false;
	}

	public function save() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);

		$wf = $this->getWorkflowObject();
		$wf->description = $this->get('summary');
		$wf->test = Zend_Json::encode($this->get('conditions'));
		$wf->moduleName = $this->get('module_name');
		$wf->executionCondition = $this->get('execution_condition');
		$wf->filtersavedinnew = $this->get('filtersavedinnew');
		$wf->schtypeid = $this->get('schtypeid');
		$wf->schtime = $this->get('schtime');
		$wf->schdayofmonth = $this->get('schdayofmonth');
		$wf->schdayofweek = $this->get('schdayofweek');
		$wf->schmonth = $this->get('schmonth');
		$wf->schmonth = $this->get('schmonth');
		$wf->schannualdates = $this->get('schannualdates');
		$wf->nexttrigger_time = $this->get('nexttrigger_time');
		$wf->status = $this->get('status');
		$wf->name = $this->get('name');
		$wm->save($wf);

		$this->set('workflow_id', $wf->id);
	}

	public function delete() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wm->delete($this->getId());
	}

	/**
	 * Functions returns the Custom Entity Methods that are supported for a module
	 * @return <Array>
	 */
	public function getEntityMethods() {
		$db = PearDatabase::getInstance();
		$emm = new VTEntityMethodManager($db);
		$methodNames = $emm->methodsForModule($this->get('module_name'));
		return $methodNames;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks() {

		$links = array();

		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'icon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Vtiger_List_Js.deleteRecord('.$this->getId().');',
				'linkicon' => 'icon-trash'
			)
		);
		foreach($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	public static function getInstance($workflowId) {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $wm->retrieve($workflowId);
		return self::getInstanceFromWorkflowObject($wf);
	}

	public static function getCleanInstance($moduleName) {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $wm->newWorkflow($moduleName);
		$wf->filtersavedinnew = 6;
		$wf->status = 1;
		return self::getInstanceFromWorkflowObject($wf);
	}

	public static function getInstanceFromWorkflowObject($wf) {
		$workflowModel = new self();

		$workflowModel->set('summary', $wf->description);
		$workflowModel->set('conditions', Zend_Json::decode($wf->test));
		$workflowModel->set('execution_condition', $wf->executionCondition);
		$workflowModel->set('module_name', $wf->moduleName);
		$workflowModel->set('workflow_id', $wf->id);
		$workflowModel->set('filtersavedinnew', $wf->filtersavedinnew);
		$workflowModel->set('schtypeid', $wf->schtypeid);
		$workflowModel->set('schtime', $wf->schtime);
		$workflowModel->set('schdayofmonth', $wf->schdayofmonth);
		$workflowModel->set('schdayofweek', $wf->schdayofweek);
		$workflowModel->set('schmonth', $wf->schmonth);
		$workflowModel->set('schannualdates', $wf->schannualdates);
		$workflowModel->set('nexttrigger_time', $wf->nexttrigger_time);
		$workflowModel->set('status', $wf->status);
		$workflowModel->set('name', $wf->workflowname);

		$workflowModel->setWorkflowObject($wf);
		$workflowModel->setModule($wf->moduleName);
		return $workflowModel;
	}

	function executionConditionAsLabel($executionCondition=null){
		if($executionCondition == null) {
			$executionCondition = $this->get('execution_condition');
		}
		$arr = array('ON_FIRST_SAVE', 'ONCE', 'ON_EVERY_SAVE', 'ON_MODIFY', '', 'ON_SCHEDULE', 'MANUAL');
		return $arr[$executionCondition-1];
	}

	function getV7executionConditionAsLabel($executionCondition=null, $module_name) {
		if($executionCondition == null) {
			$executionCondition = $this->get('execution_condition');
		}
		$module = "Settings:Workflows";
		$arr = array(vtranslate($module_name, $module_name)." ".vtranslate('LBL_CREATION', $module),
					 vtranslate('LBL_FIRST_TIME_CONDITION_MET', $module), 
					 vtranslate('LBL_EVERY_TIME_CONDITION_MET', $module),
					 vtranslate('ON_MODIFY', $module),
					 '', 
					 vtranslate('LBL_TIME_INTERVAL', $module), 
					 'MANUAL');
		return $arr[$executionCondition-1];
	}

	function isFilterSavedInNew() {
		$wf = $this->getWorkflowObject();
		if($wf->filtersavedinnew == '6') {
			return true;
		}
		return false;
	}
	/**
	 * Functions transforms workflow filter to advanced filter
	 * @return <Array>
	 */
	function transformToAdvancedFilterCondition() {
		$conditions = $this->get('conditions');
		$transformedConditions = array();

		if(!empty($conditions)) {
			foreach($conditions as $index => $info) {
				$columnName = $info['fieldname'];
				$value = $info['value'];
				// To convert date value from yyyy-mm-dd format to user format
				$valueArray = explode(',', $value);
				$isDateValue = false;
				for($i = 0; $i < count($valueArray); $i++) {
					if(Vtiger_Functions::isDateValue($valueArray[$i])) {
						$isDateValue = true;
						$valueArray[$i] = DateTimeField::convertToUserFormat($valueArray[$i]);
					}
				}
				if($isDateValue) {
					$value = implode(',', $valueArray);
				}
				// End
				if($columnName == 'filelocationtype')
					$value = ($value == 'I') ? vtranslate('LBL_INTERNAL','Documents') : vtranslate('LBL_EXTERNAL','Documents');
				elseif ($columnName == 'folderid') {
					$folderInstance = Documents_Folder_Model::getInstanceById($value);
					$value = $folderInstance->getName();
				}
				if(!($info['groupid'])) {
					$firstGroup[] = array('columnname' => $columnName, 'comparator' => $info['operation'], 'value' => $value,
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				} else {
					$secondGroup[] = array('columnname' => $columnName, 'comparator' => $info['operation'], 'value' => $value,
						'column_condition' => $info['joincondition'], 'valuetype' => $info['valuetype'], 'groupid' => $info['groupid']);
				}
			}
		}
		$transformedConditions[1] = array('columns'=>$firstGroup);
		$transformedConditions[2] = array('columns'=>$secondGroup);
		return $transformedConditions;
	}

	/**
	 * Function returns valuetype of the field filter
	 * @return <String>
	 */
	function getFieldFilterValueType($fieldname) {
		$conditions = $this->get('conditions');
		if(!empty($conditions) && is_array($conditions)) {
			foreach($conditions as $filter) {
				if($fieldname == $filter['fieldname']) {
					return $filter['valuetype'];
				}
			}
		}
		return false;
	}

	/**
	 * Function transforms Advance filter to workflow conditions
	 */
	function transformAdvanceFilterToWorkFlowFilter() {
		$conditions = $this->get('conditions');
		$wfCondition = array();

		if(!empty($conditions)) {
			foreach($conditions as $index => $condition) {
				$columns = $condition['columns'];
				if($index == '1' && empty($columns)) {
					$wfCondition[] = array('fieldname'=>'', 'operation'=>'', 'value'=>'', 'valuetype'=>'',
						'joincondition'=>'', 'groupid'=>'0');
				}
				if(!empty($columns) && is_array($columns)) {
					foreach($columns as $column) {
						$wfCondition[] = array('fieldname'=>$column['columnname'], 'operation'=>$column['comparator'],
							'value'=>$column['value'], 'valuetype'=>$column['valuetype'], 'joincondition'=>$column['column_condition'],
							'groupjoin'=>$condition['condition'], 'groupid'=>$column['groupid']);
					}
				}
			}
		}
		$this->set('conditions', $wfCondition);
	}

	/**
	 * Function returns all the related modules for workflows create entity task
	 * @return <JSON>
	 */
	public function getDependentModules() {
		$modulesList = Settings_LayoutEditor_Module_Model::getEntityModulesList();
		$primaryModule = $this->getModule();

		if($primaryModule->isCommentEnabled()) {
			$modulesList['ModComments'] = 'ModComments';
		}
		$createModuleModels = array();
		// List of modules which will not be supported by 'Create Entity' workflow task
		$filterModules = array('Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder', 'Emails', 'Calendar', 'Events');

		foreach ($modulesList as $moduleName => $translatedModuleName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if (in_array($moduleName, $filterModules))
				continue;
			$createModuleModels[$moduleName] = $moduleModel;
		}
		return $createModuleModels;
	}


	/**
	 * Function to get reference field name
	 * @param <String> $relatedModule
	 * @return <String> fieldname
	 */
	public function getReferenceFieldName($relatedModule) {
		if ($relatedModule) {
			$db = PearDatabase::getInstance();

			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
			if ($relatedModuleModel) {
				$referenceFieldsList = $relatedModuleModel->getFieldsByType('reference');

				foreach ($referenceFieldsList as $fieldName => $fieldModel) {
					if (in_array($this->getModule()->getName(), $fieldModel->getReferenceList())) {
						return $fieldName;
					}
				}
			}
		}
		return false;
	}
	public function updateNextTriggerTime() {
		$db = PearDatabase::getInstance();
		$wm = new VTWorkflowManager($db);
		$wf = $this->getWorkflowObject();
		$wm->updateNexTriggerTime($wf);
	}

	/**
	 * function to delete the update workflow related to a field
	 * @param type $moduleName
	 * @param type $fieldName
	 */
	public static function deleteUpadateFieldWorkflow($moduleName, $fieldName) {
		$ids = Settings_Workflows_Record_Model::getUpdateFieldTaskIdsForModule($moduleName, $fieldName);
		if($ids) {
			foreach ($ids as $id) {
				$taskModel = Settings_Workflows_TaskRecord_Model::getInstance($id);
				$taskTypeModel = $taskModel->getTaskType();
				if($taskTypeModel->get('tasktypename') == 'VTUpdateFieldsTask') {
					$taskObject = $taskModel->getTaskObject();
					$fieldMapping = Zend_Json::decode($taskObject->field_value_mapping);
					foreach ($fieldMapping as $key=>$field) {
						if($field['fieldname'] == $fieldName || strpos($field['value'],$fieldName) !== false) {
							unset($fieldMapping[$key]);
						}
					}
					$taskObject->field_value_mapping = Zend_Json::encode($fieldMapping);
					$taskModel->setTaskObject($taskObject);
					$taskModel->save();
				}
			}
		}
	}

	/**
	 * Function to get the update field task ids from modulename and fieldname
	 * @param type $moduleName
	 * @param type $fieldName
	 * @return $ids
	 */
	public static function getUpdateFieldTaskIdsForModule($moduleName, $fieldName) {
		$ids = array();
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM com_vtiger_workflows
				INNER JOIN com_vtiger_workflowtasks ON com_vtiger_workflows.workflow_id = com_vtiger_workflowtasks.workflow_id
				WHERE module_name = ?
				AND task LIKE ? 
				AND task LIKE ? ';
		$result = $db->pquery($sql, array($moduleName, '%VTUpdateFieldsTask%', "%".$fieldName."%"));
		$count = $db->num_rows($result);
		if($count > 0) {
			for($i=0;$i<$count;$i++) {
				$ids[] = $db->query_result($result, $i, 'task_id');
			}
			return $ids;
		}
		return false;
	}

	public static function updateWorkflowStatus($record, $status){
	  $db = PearDatabase::getInstance();
	  $sql = 'UPDATE com_vtiger_workflows SET status = ? WHERE workflow_id = ?';
	  $db->pquery($sql, array($status, $record));
	}

	function getConditonDisplayValue() {
		$test = $this->get('raw_test');
		$moduleName = $this->get('raw_module_name');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$wfCond = json_decode($test,true);
		$conditionList = array();
		if(is_array($wfCond)) {
			for ($k=0; $k<(count($wfCond)); ++$k){
				$fieldName = $wfCond[$k]['fieldname'];
				preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $fieldName, $matches);

				if(count($matches)==0){
					$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
					if($fieldModel) {
						$fieldLabel = vtranslate($fieldModel->get('label'), $moduleName);
					} else {
						$fieldLabel = $fieldName;
					}
				} else {
					list($full, $referenceField, $referenceModule, $fieldName) = $matches;
					$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
					$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $referenceModuleModel);
					$referenceFieldModel = Vtiger_Field_Model::getInstance($referenceField, $moduleModel);
					if($fieldModel) {
						$translatedReferenceModule = vtranslate($referenceModule, $referenceModule);
						$referenceFieldLabel = vtranslate($referenceFieldModel->get('label'), $moduleName);
						$fieldLabel = vtranslate($fieldModel->get('label'), $referenceModule);
						$fieldLabel = "(".$translatedReferenceModule.") ".$referenceFieldLabel." - ".$fieldLabel;
					} else {
						$fieldLabel = $fieldName;
					}
				}
				$value = $wfCond[$k]['value'];
				$operation = $wfCond[$k]['operation'];
				if($wfCond[$k]['groupjoin'] == 'and') {
					$conditionGroup = 'All';
				} else {
					$conditionGroup = 'Any';
				}

				$fieldDataType = '';
				if ($fieldModel) {
					$fieldDataType = $fieldModel->getFieldDataType();
				}
				if($value == 'true:boolean' || ($fieldModel && $fieldDataType == 'boolean' && $value == '1')) {
					$value = 'LBL_ENABLED';
				}
				if($value == 'false:boolean' || ($fieldModel && $fieldDataType == 'boolean' && $value == '0')) {
					$value = 'LBL_DISABLED';
				}
				if ($fieldModel && (($fieldModel->column === 'smownerid') || (($fieldModel->column === 'smgroupid')))) {
					if (vtws_getOwnerType($value) == 'Users') {
						$value = getUserFullName($value);
					} else {
						$groupNameList = getGroupName($value);
						$value = $groupNameList[0];
					}
				}
				if ($value) {
					if ($fieldModel && in_array('Currency', $fieldModel->getReferenceList())) {
						$currencyNamewithSymbol = getCurrencyName($value);
						$currencyName = explode(':', $currencyNamewithSymbol);
						$value = $currencyName[0];
					}
					if ($fieldModel && (in_array($fieldDataType, array('picklist', 'multipicklist')))) {
						$picklistValues = explode(',', $value);
						if (count($picklistValues) > 1) {
							$translatedValues = array();
							foreach ($picklistValues as $selectedValue) {
								array_push($translatedValues, vtranslate($selectedValue, $moduleName));
							}
							$value = implode(',', $translatedValues);
						} else {
							$value = vtranslate($value, $moduleName);
						}
					}
				}
				if($fieldLabel == '_VT_add_comment') {
					$fieldLabel = 'Comment';
				}
				$conditionList[$conditionGroup][] = $fieldLabel.' '.vtranslate($operation, $moduleName).' '.vtranslate($value, $moduleName);
			}
		}

		return $conditionList;
	}

	function getActionsDisplayValue() {
		$actions = array();
		$tasks = Settings_Workflows_TaskRecord_Model::getAllForWorkflow($this, true);
		foreach($tasks as $task) {
			$taskName = $task->getTaskType()->get('tasktypename');
			$actions[$taskName] = $actions[$taskName] + 1;
		}
		return $actions;
	}
}
