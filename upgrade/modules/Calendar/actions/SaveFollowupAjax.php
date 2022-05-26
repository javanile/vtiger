<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_SaveFollowupAjax_Action extends Calendar_SaveAjax_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$actionName = ($record && $request->getMode() != 'createFollowupEvent') ? 'EditView' : 'CreateView';
		if(!Users_Privileges_Model::isPermitted($moduleName, $actionName, $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		if ($record) {
			$activityModulesList = array('Calendar', 'Events');
			$recordEntityName = getSalesEntityType($record);
			if (!in_array($recordEntityName, $activityModulesList) || !in_array($moduleName, $activityModulesList)) {
				throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
			}
		}
	}

    function __construct() {
        $this->exposeMethod('createFollowupEvent');
        $this->exposeMethod('markAsHeldCompleted');
    }
    
    public function process(Vtiger_Request $request) {  
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	public function createFollowupEvent(Vtiger_Request $request) {
        
        $recordId = $request->get('record');
        
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $subject = $recordModel->get('subject');
        $followupSubject = "[Followup] ".$subject;
        $recordModel->set('subject',$followupSubject);
        //followup event is Planned
        $recordModel->set('eventstatus',"Planned");
        
        $activityType = $recordModel->get('activitytype');
        if($activityType == "Call")
            $eventDuration = $request->get('defaultCallDuration');
        else
            $eventDuration = $request->get('defaultOtherEventDuration');
        
        $followupStartTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
		$followupStartDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start')." ".$followupStartTime);
		list($followupStartDate, $followupStartTime) = explode(' ', $followupStartDateTime);
        //Duration of followup event based on activitytype
        $durationMS = $eventDuration*60;
        $followupStartDateTimeMS = strtotime($followupStartDateTime);
        $followupEndDateTimeMS = $followupStartDateTimeMS+$durationMS;
        $followupEndDateTime = date("Y-m-d H:i:s", $followupEndDateTimeMS);
        list($followupEndDate, $followupEndTime) = explode(' ', $followupEndDateTime);
        
		$recordModel->set('date_start', $followupStartDate);
		$recordModel->set('time_start', $followupStartTime);
        
        $recordModel->set('due_date', $followupEndDate);
		$recordModel->set('time_end', $followupEndTime);

		$response = new Vtiger_Response();
		try {
			$recordModel->set('id',null);
			$recordModel->save();
			$result = array('created'=>true);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
    
    public function markAsHeldCompleted(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId,$moduleName);
        $recordModel->set('mode','edit');
        $activityType = $recordModel->get('activitytype');
        $response = new Vtiger_Response();
        
        if($activityType == 'Task'){
            $status = 'Completed';
            $recordModel->set('taskstatus',$status);
            $result = array("valid"=>TRUE,"markedascompleted"=>TRUE,"activitytype"=>"Task");
        }
        else{
            //checking if the event can be marked as Held (status validation)
            $startDateTime[] = $recordModel->get('date_start');
            $startDateTime[] = $recordModel->get('time_start');
            $startDateTime = implode(' ',$startDateTime);
            $startDateTime = new DateTime($startDateTime);
            $currentDateTime = date("Y-m-d H:i:s");
            $currentDateTime = new DateTime($currentDateTime);
            if($startDateTime > $currentDateTime){
                $result = array("valid"=>FALSE,"markedascompleted"=>FALSE);
                $response->setResult($result);
                $response->emit();
                return;
            }
            $status = 'Held';
            $recordModel->set('eventstatus',$status);
            $result = array("valid"=>TRUE,"markedascompleted"=>TRUE,"activitytype"=>"Event");
        }
		$_REQUEST['mode'] = 'edit';
		$this->setRecurrenceInfo($recordModel);
		try {
			$recordModel->save();
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
        $response->emit();
    }
	
	function setRecurrenceInfo($recordModel) {
		$startDateTime = DateTimeField::convertToUserTimeZone($recordModel->get('date_start') . ' ' . $recordModel->get('time_start'));
		$endDateTime = DateTimeField::convertToUserTimeZone($recordModel->get('due_date') . ' ' . $recordModel->get('time_end'));
		$_REQUEST['date_start'] = $startDateTime->format('Y-m-d');
		$_REQUEST['time_start'] = $startDateTime->format('H:i');
		$_REQUEST['due_date'] = $endDateTime->format('Y-m-d');
		$_REQUEST['time_end'] = $endDateTime->format('H:i');
		
		$recurringInfo = $recordModel->getRecurrenceInformation();
		$_REQUEST['recurringcheck'] = $recurringInfo['recurringcheck'];
		$_REQUEST['repeat_frequency'] = $recurringInfo['repeat_frequency'];
		$_REQUEST['recurringtype'] = $recurringInfo['eventrecurringtype'];
		$_REQUEST['calendar_repeat_limit_date'] = $recurringInfo['recurringenddate'];
		
		if($recurringInfo['eventrecurringtype'] == 'Weekly') {
			$_REQUEST['sun_flag'] = $recurringInfo['week0'];
			$_REQUEST['mon_flag'] = $recurringInfo['week1'];
			$_REQUEST['tue_flag'] = $recurringInfo['week2'];
			$_REQUEST['wed_flag'] = $recurringInfo['week3'];
			$_REQUEST['thu_flag'] = $recurringInfo['week4'];
			$_REQUEST['fri_flag'] = $recurringInfo['week5'];
			$_REQUEST['sat_flag'] = $recurringInfo['week6'];
		}
		
		if($recurringInfo['eventrecurringtype'] == 'Monthly') {
			if($recurringInfo['repeatMonth'] == 'date') {
				$_REQUEST['repeatMonth'] = $recurringInfo['repeatMonth'];
				$_REQUEST['repeatMonth_date'] = $recurringInfo['repeatMonth_date'];
			} else if($recurringInfo['repeatMonth'] == 'day') {
				$_REQUEST['repeatMonth_daytype'] = $recurringInfo['repeatMonth_daytype'];
				$_REQUEST['repeatMonth_day'] = $recurringInfo['repeatMonth_day'];
			}
		}
	}
}
