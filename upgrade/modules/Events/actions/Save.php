<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_Save_Action extends Calendar_Save_Action {

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$adb = PearDatabase::getInstance();
		$recordModel = $this->getRecordModelFromRequest($request);
		$recurObjDb = false;
		if($recordModel->get('mode') == 'edit') {
			$recurObjDb = $recordModel->getRecurringObject();
		}
		$recordModel->save();
		$originalRecordId = $recordModel->getId();
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			if($relatedModule->getName() == 'Events'){
				$relatedModule = Vtiger_Module_Model::getInstance('Calendar');
			}
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}

		// Handled to save follow up event
		$followupMode = $request->get('followup');

		//Start Date and Time values
		$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
		$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
		list($startDate, $startTime) = explode(' ', $startDateTime);

		$subject = $request->get('subject');
		if($followupMode == 'on' && $startTime != '' && $startDate != ''){
			$record = $this->getRecordModelFromRequest($request);
			$record->set('eventstatus', 'Planned');
			//recurring events status should not be held for future events
			$recordModel->set('eventstatus', 'Planned');
			$record->set('subject','[Followup] '.$subject);
			$record->set('date_start',$startDate);
			$record->set('time_start',$startTime);

			$currentUser = Users_Record_Model::getCurrentUserModel();
			$activityType = $record->get('activitytype');
			if($activityType == 'Call') {
				$minutes = $currentUser->get('callduration');
			} else {
				$minutes = $currentUser->get('othereventduration');
			}
			$dueDateTime = date('Y-m-d H:i:s', strtotime("$startDateTime+$minutes minutes"));
			list($startDate, $startTime) = explode(' ', $dueDateTime);

			$record->set('due_date',$startDate);
			$record->set('time_end',$startTime);
			$record->set('recurringtype', '');
			$record->set('mode', 'create');
			$record->save();
			$heldevent = true;
		}
		$recurringEditMode = $request->get('recurringEditMode');
		$recordModel->set('recurringEditMode', $recurringEditMode);

		vimport('~~/modules/Calendar/RepeatEvents.php');
		$recurObj = getrecurringObjValue();
		$recurringDataChanged = Calendar_RepeatEvents::checkRecurringDataChanged($recurObj, $recurObjDb);
		//TODO: remove the dependency on $_REQUEST
		if(($_REQUEST['recurringtype'] != '' && $_REQUEST['recurringtype'] != '--None--' && $recurringEditMode != 'current') || ($recurringDataChanged && empty($recurObj))) {
			$focus =  CRMEntity::getInstance('Events');
			//get all the stored data to this object
			$focus->column_fields = new TrackableObject($recordModel->getData());
			try {
				Calendar_RepeatEvents::repeatFromRequest($focus, $recurObjDb);
			} catch (DuplicateException $e) {
                $requestData = $request->getAll();
			    $requestData['view'] = 'Edit';
				$requestData['mode'] = 'Events';
				$requestData['module'] = 'Events';
				$requestData['duplicateRecords'] = $e->getDuplicateRecordIds();
                
                global $vtiger_current_version;
				$viewer = new Vtiger_Viewer();
                $viewer->assign('REQUEST_DATA', $requestData);
				$viewer->assign('REQUEST_URL', 'index.php?module=Calendar&view=Edit&mode=Events&record='.$request->get('record'));
				$viewer->view('RedirectToEditView.tpl', 'Vtiger');
                exit();
            } catch (Exception $ex) {
				throw new Exception($ex->getMessage());
			}
		}
		return $recordModel;
	}


	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);
		if($request->has('selectedusers')) {
			$recordModel->set('selectedusers', $request->get('selectedusers'));
		}
		return $recordModel;
	}
}
