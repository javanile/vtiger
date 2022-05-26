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

class VTCreateEventTask extends VTTask{
	public $executeImmediately = true;

	public function getFieldNames(){
		return array('eventType', 'eventName', 'description', 'sendNotification',
								 'startTime', 'startDays', 'startDirection', 'startDatefield',
								 'endTime','endDays', 'endDirection', 'endDatefield',
								 'status', 'priority','recurringcheck','repeat_frequency',
								 'recurringtype','calendar_repeat_limit_date',
								 'mon_flag','tue_flag','wed_flag','thu_flag','fri_flag','sat_flag','sun_flag',
								 'repeatMonth','repeatMonth_date','repeatMonth_daytype','repeatMonth_day', 'assigned_user_id');
	}

	function getAdmin(){
		$user = Users::getActiveAdminUser();
		global $current_user;
		$this->originalUser = $current_user;
		$current_user = $user;
		return $user;
	}

	public function doTask($entityData){
		if(!vtlib_isModuleActive('Calendar')) {
			return;
		}
		global $adb, $current_user;
		$userId = $entityData->get('assigned_user_id');
		if($userId===null){
			$userId = vtws_getWebserviceEntityId('Users', Users::getActiveAdminId());
		}

		$moduleName = 'Events';
		$parentModuleName = $entityData->getModuleName();
		$adminUser = $this->getAdmin();

		$startDate = $this->calculateDate($entityData, $this->startDays,
															$this->startDirection, $this->startDatefield);
		$endDate = $this->calculateDate($entityData, $this->endDays,
															$this->endDirection, $this->endDatefield);

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
		
		$time = ($this->startTime) ? self::convertToDBFormat($this->startTime) : date('H:i:s');
		$endTime = ($this->endTime) ? self::convertToDBFormat($this->endTime) : date('H:i:s');
		
		$fields = array(
			'activitytype'=>$this->eventType,
			'description'=> decode_html($this->description),
			'subject'=> decode_html($this->eventName),
			'taskpriority'=>$this->priority,
			'eventstatus'=>$this->status,
			'assigned_user_id'=>$userId,
			'time_start'=>$time,
			'date_start'=> $startDate,
			'time_end'=>$endTime,
			'due_date'=>$endDate,
			'taskstatus'=>'',
			'duration_hours'=>'0',
			'recurringtype' => $this->recurringtype
		);
		
		//Setting visibility value
		$assignedTo = explode('x', $userId);
		$sharedType = Calendar_Module_Model::getSharedType($assignedTo[1]);
		if($sharedType == 'selectedusers' || empty($sharedType)) {
			$sharedType = 'public';
		}
		$fields['visibility'] = ucfirst($sharedType);
		
		$id = $entityData->getId();
		if($parentModuleName=='Contacts'){
			$fields['contact_id'] = $id;
		}else{
			$data = vtws_describe('Calendar', $adminUser);
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
		$moduleFields = $handlerMeta->getModuleFields();
		foreach ($moduleFields as $name => $fieldModel) {
			if(!empty($fields[$name])) {
				continue;
			} else if(!empty($this->$name)) {
				$fields[$name] = $this->$name;
			}
		}
		
		$mandatoryFields = $handlerMeta->getMandatoryFields();
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
		
		//Activity Save api depends on $_REQUEST to save the recurring info, here we will get the parent module $_REQUEST
		//So, setting up the event details to $_REQUEST as repeat events should work properly
		if($this->recurringcheck) {
			$fieldNames = $this->getFieldNames();
			foreach($fieldNames as $name) {
				$_REQUEST[$name] = $this->$name;
			}
		}
		//If recurring check empty, recurring type should not be set
		if(empty($this->recurringcheck)) {
			unset($_REQUEST['recurringtype']);
			unset($fields['recurringtype']);
		}

		try {
			$fields['source'] = 'WORKFLOW';
			$event = vtws_create($moduleName, $fields, $adminUser);
			$eventIdDetails = vtws_getIdComponents($event['id']);
			$entityIdDetails = vtws_getIdComponents($id);

			relateEntities(CRMEntity::getInstance($parentModuleName), $parentModuleName, $entityIdDetails[1], 'Calendar', $eventIdDetails[1]);

			$handler = vtws_getModuleHandlerFromName($moduleName, $adminUser);
			$meta = $handler->getMeta();
			$recordValues = DataTransform::sanitizeForInsert($event,$meta);
			list($typeId, $id) = vtws_getIdComponents($event['id']);
			$event = CRMEntity::getInstance($moduleName);
			$event->id = $id;
			foreach($recordValues as $recordFieldname => $recordFieldValue){
				$event->column_fields[$recordFieldname] = $recordFieldValue;
			}
			$event->column_fields['id'] = $event->id;

			if($this->recurringcheck && !empty($startDate) &&
					($this->calendar_repeat_limit_date)) {
				// Added this to relate these events to parent module.
				$_REQUEST['createmode'] = 'link';
				$_REQUEST['return_module'] = $parentModuleName;
				$_REQUEST['return_id'] = $entityIdDetails[1];

				//repeatFromRequest calling getrecurringObjValue(), api expecting UserTimeZone values, so converting to UserTimeZone values
				$startDateObj = DateTimeField::convertToUserTimeZone($startDate . ' ' . self::convertToDBFormat($this->startTime));
				$_REQUEST['date_start'] = $startDateObj->format('Y-m-d');
				$_REQUEST['time_start'] = $startDateObj->format('H:i');
				$endDateObj = DateTimeField::convertToUserTimeZone($endDate . ' ' . self::convertToDBFormat($this->endTime));
				$_REQUEST['due_date'] = $endDateObj->format('Y-m-d');
				$_REQUEST['time_end'] = $endDateObj->format('H:i');
				//comparing date_start with recurring dates, which are in the format of Y-m-d always
				$event->column_fields['date_start'] = $startDateObj->format('Y-m-d');
				$event->column_fields['due_date'] = $endDateObj->format('Y-m-d');

				include_once 'modules/Calendar/RepeatEvents.php';
				Calendar_RepeatEvents::repeatFromRequest($event, false);

				$_REQUEST['createmode'] = '';

			}
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

	private function calculateDate($entityData, $days, $direction, $datefield){
		$baseDate = $entityData->get($datefield);
		if($baseDate == '') {
			$baseDate = date('Y-m-d');
		}
		if($days == '') {
			$days = 0;
		}
		preg_match('/\d\d\d\d-\d\d-\d\d/', $baseDate, $match);
		$baseDate = strtotime($match[0]);
		$date = strftime('%Y-%m-%d', $baseDate+$days*24*60*60*
										 (strtolower($direction)=='before'?-1:1));
		return $date;
	}

	/**
	 * To convert time_start & time_end values to db format
	 * @param type $timeStr
	 * @return time
	 */
	static function convertToDBFormat($timeStr){
		$date = new DateTime();
		$time = Vtiger_Time_UIType::getTimeValueWithSeconds($timeStr);
		$dbInsertDateTime = DateTimeField::convertToDBTimeZone($date->format('Y-m-d').' '.$time);
		return $dbInsertDateTime->format('H:i:s');
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
		return array('startTime', 'endTime');
	}

}
?>