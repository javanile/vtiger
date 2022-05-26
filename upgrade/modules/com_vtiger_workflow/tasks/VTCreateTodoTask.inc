<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('include/Webservices/Utils.php');
require_once("include/Webservices/VtigerCRMObject.php");
require_once("include/Webservices/VtigerCRMObjectMeta.php");
require_once("include/Webservices/DataTransform.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/Webservices/ModuleTypes.php';
require_once('include/Webservices/Create.php');
require_once 'include/Webservices/DescribeObject.php';
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';

require_once("modules/Users/Users.php");

class VTCreateTodoTask extends VTTask{
	public $executeImmediately = true;

	public function getFieldNames(){return array('todo', 'description', 'sendNotification', 'time', 'date', 'status', 'priority', 'days', 'direction', 'datefield', 'sendNotification', 'assigned_user_id');}

	function getAdmin(){
		$user = Users::getActiveAdminUser();
		global $current_user;
		$this->originalUser = $current_user;
		$current_user = $user;
		return $user;
	}

	public function doTask($entityData){
		$moduleName = 'Calendar';
		if(!vtlib_isModuleActive($moduleName)) {
			return;
		}
		global $adb, $current_user;
		$userId = $entityData->get('assigned_user_id');
		if($userId===null){
			$userId = vtws_getWebserviceEntityId('Users', 1);
		}

		$baseDate = $entityData->get($this->datefield);
		$dateTime = explode(' ', $baseDate);
		if($dateTime[1]){
			$userFormatDate = DateTimeField::convertToUserTimeZone($baseDate);
			$baseDate = $userFormatDate->format('Y-m-d H:i:s');
			$baseTime = $userFormatDate->format('H:i:s');
		}
		if($baseDate == '') {
			$baseDate = date('Y-m-d H:i:s');
			$userFormatDate = DateTimeField::convertToUserTimeZone($baseDate);
			$baseDate = $userFormatDate->format('Y-m-d H:i:s');
		}
		$days = $this->days;
		if($days == '') {
			$days = '0';
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		$date = strftime('%Y-%m-%d', $baseDate+$days*24*60*60*(strtolower($this->direction)=='before'?-1:1));
		// Added to check if the user/group is active
		if(!empty($this->assigned_user_id)) {
			$userExists = $adb->pquery('SELECT 1 FROM vtiger_users WHERE id = ? AND status = ?', array($this->assigned_user_id, 'Active'));
			if($adb->num_rows($userExists)) {
				$assignedUserId = vtws_getWebserviceEntityId('Users', $this->assigned_user_id);
				$userId = $assignedUserId;
			} else {
				$groupExist = $adb->pquery('SELECT 1 FROM vtiger_groups WHERE groupid = ?', array($this->assigned_user_id));
				if($adb->num_rows($groupExist)) {
					$assignedGroupId = vtws_getWebserviceEntityId('Groups', $this->assigned_user_id);
					$userId = $assignedGroupId;
				}
				else{
					if($this->assigned_user_id == 'copyParentOwner'){
						$userId = $entityData->get('assigned_user_id');
					}
				}
			}
		}
		
		if($this->time) {
			$timeWithSec = Vtiger_Time_UIType::getTimeValueWithSeconds($this->time);
			$dbInsertDateTime = DateTimeField::convertToDBTimeZone($date.' '.$timeWithSec);
			$startDate = $dbInsertDateTime->format('Y-m-d');
			$time = $dbInsertDateTime->format('H:i:s');
		} else {
			if($baseTime) {
				$userTimeValue = $baseTime;
			} else {
				$time = date('H:i:s');
				$userTime = DateTimeField::convertToUserTimeZone($time);
				$userTimeValue = $userTime->format('H:i:s');
			}
			$dbInsertDateTime = DateTimeField::convertToDBTimeZone($date.' '.$userTimeValue);
			$startDate = $dbInsertDateTime->format('Y-m-d');
			$time = $dbInsertDateTime->format('H:i:s');
		}

		$fields = array(
			'activitytype'=>'Task',
			'description'=> decode_html($this->description),
			'subject'=> decode_html($this->todo),
			'taskpriority'=>$this->priority,
			'taskstatus'=>$this->status,
			'assigned_user_id'=>$userId,
			'time_start'=> $time,
			'sendnotification'=>($this->sendNotification!='' && $this->sendNotification!='N') ? true: false,
			'date_start'=>$startDate, 
			'due_date'=>$date,
			'visibility'=>'Private',
			'eventstatus'=>''
		);
		$parentModuleName = $entityData->getModuleName();
		$adminUser = $this->getAdmin();
		$id = $entityData->getId();
		if($parentModuleName=='Contacts'){
			$fields['contact_id'] = $id;
		}else{
			$data = vtws_describe($moduleName, $adminUser);
			$fieldInfo = $data['fields'];
			foreach($fieldInfo as $field){
				if($field['name']=='parent_id'){
					$parentIdField = $field;
				}
			}
			$refersTo = $parentIdField['type']['refersTo'];

			if(in_array($parentModuleName, $refersTo)){
				$fields['parent_id'] = $id;
			}
		}
		
		$entityModuleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
		$handlerMeta = $entityModuleHandler->getMeta();
		$mandatoryFields = $handlerMeta->getMandatoryFields();
		$moduleFields = $handlerMeta->getModuleFields();
		foreach ($moduleFields as $name => $fieldModel) {
			if(!empty($fields[$name])) {
				continue;
			} else if(!empty($this->$name)) {
				$fields[$name] = $this->$name;
			}
		}
		
		foreach ($mandatoryFields as $fieldName) {
			$fieldInstance = $moduleFields[$fieldName];
			$fieldDataType = $fieldInstance->getFieldDataType();
			if(!empty($fields[$fieldName])) {
				continue;
			} else {
				$fieldValue = $this->$fieldName;
				if(empty($fieldValue)) {
					$defaultValue = $fieldInstance->getDefault();
					$fieldValue = $defaultValue;
				}
				if(empty($fieldValue)) {
					$fieldValue = Vtiger_Util_Helper::getDefaultMandatoryValue($fieldDataType);
					if($fieldDataType == 'picklist' || $fieldDataType == 'multipicklist') {
						$picklistValues = $fieldInstance->getPickListOptions();
						$fieldValue = $picklistValues[0]['label'];
					}
				}
				$fields[$fieldName] = $fieldValue;
			}
		}

		try {
			$fields['source'] = 'WORKFLOW';
			$todo = vtws_create($moduleName, $fields, $adminUser);
			$todoIdDetails = vtws_getIdComponents($todo['id']);
			$entityIdDetails = vtws_getIdComponents($id);

			relateEntities(CRMEntity::getInstance($parentModuleName), $parentModuleName, $entityIdDetails[1], $moduleName, $todoIdDetails[1]);
		} catch (DuplicateException $e) {
			$workFlowManager = new VTWorkflowManager($adb);
			$workFlow = $workFlowManager->retrieve($this->workflowId);

			$mailBody = vtranslate('LBL_DUPLICATION_FAILURE_FROM_WORKFLOWS', $moduleName, vtranslate('SINGLE_'.$moduleName, $moduleName),
													decode_html($workFlow->workflowname), vtranslate('SINGLE_'.$moduleName, $moduleName));
			sendMailToUserOnDuplicationPrevention($moduleName, $fields, $mailBody);
		} catch (Exception $e) {
		}

		global $current_user;
		$current_user = $this->originalUser;
	}

	static function conv12to24hour($timeStr){
		$arr = array();
		preg_match('/(\d{1,2}):(\d{1,2})(am|pm)/', $timeStr, $arr);
		if($arr[3]=='am'){
			$hours = ((int)$arr[1]) % 12;
		}else{
			$hours = ((int)$arr[1]) % 12 + 12;
		}
		return str_pad($hours, 2, '0', STR_PAD_LEFT).':'.str_pad($arr[2], 2, '0', STR_PAD_LEFT);
	}

	public function getTimeFieldList() {
		return array('time');
	}

}
?>
