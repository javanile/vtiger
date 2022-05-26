<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');

		$actionName = ($record) ? 'EditView' : 'CreateView';
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

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		try {
			$user = Users_Record_Model::getCurrentUserModel();

			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode',false));
			$recordModel = $this->saveRecord($request);
			vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);

			$fieldModelList = $recordModel->getModule()->getFields();
			$result = array();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$recordFieldValue = $recordModel->get($fieldName);
				if(is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
					$recordFieldValue = implode(' |##| ', $recordFieldValue);
				}
				$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
				if ($fieldModel->getFieldDataType() !== 'currency' && $fieldModel->getFieldDataType() !== 'datetime' && $fieldModel->getFieldDataType() !== 'date') { 
					$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId()); 
				}
				$result[$fieldName] = array();
				if($fieldName == 'date_start') {
					$timeStart = $recordModel->get('time_start');
					$dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeStart);

					$fieldValue = $fieldValue.' '.$timeStart;

					$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
					$dateTimeComponents = explode(' ',$userDateTimeString);
					$dateComponent = $dateTimeComponents[0];
					//Conveting the date format in to Y-m-d . since full calendar expects in the same format
					$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
					$result[$fieldName]['calendar_display_value'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
					$displayValue = $fieldModel->getDisplayValue($fieldValue);
				} else if($fieldName == 'due_date') {
					$timeEnd = $recordModel->get('time_end');
					$dateTimeFieldInstance = new DateTimeField($fieldValue . ' ' . $timeEnd);

					$fieldValue = $fieldValue.' '.$timeEnd;

					$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
					$dateTimeComponents = explode(' ',$userDateTimeString);
					$dateComponent = $dateTimeComponents[0];
					//Conveting the date format in to Y-m-d . since full calendar expects in the same format
					$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
					$result[$fieldName]['calendar_display_value']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
					$displayValue = $fieldModel->getDisplayValue($fieldValue);
				}
				$result[$fieldName]['value'] = $fieldValue;
				$result[$fieldName]['display_value'] = decode_html($displayValue);
			}

			$result['_recordLabel'] = $recordModel->getName();
			$result['_recordId'] = $recordModel->getId();
			$result['calendarModule'] = $request->get('calendarModule');
			$result['sourceModule'] = $request->get('calendarModule');

			// Handled to save follow up event
			$followupMode = $request->get('followup');

			if($followupMode == 'on') {
				//Start Date and Time values
				$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('followup_time_start'));
				$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('followup_date_start') . " " . $startTime);
				list($startDate, $startTime) = explode(' ', $startDateTime);

				$subject = $request->get('subject');
				if($startTime != '' && $startDate != ''){
					$recordModel->set('eventstatus', 'Planned');
					$recordModel->set('subject','[Followup] '.$subject);
					$recordModel->set('date_start',$startDate);
					$recordModel->set('time_start',$startTime);

					$currentUser = Users_Record_Model::getCurrentUserModel();
					$activityType = $recordModel->get('activitytype');
					if($activityType == 'Call') {
						$minutes = $currentUser->get('callduration');
					} else {
						$minutes = $currentUser->get('othereventduration');
					}
					$dueDateTime = date('Y-m-d H:i:s', strtotime("$startDateTime+$minutes minutes"));
					list($endDate, $endTime) = explode(' ', $dueDateTime);

					$recordModel->set('due_date',$endDate);
					$recordModel->set('time_end',$endTime);
					$recordModel->set('mode', 'create');
					$recordModel->save();
				}
			}
			$response->setEmitType(Vtiger_Response::$EMIT_JSON);
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);

		$startDate = $request->get('date_start');
		if(!empty($startDate)) {
			//Start Date and Time values
			$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($request->get('time_start'));
			$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($startDate." ".$startTime);
			list($startDate, $startTime) = explode(' ', $startDateTime);

			$recordModel->set('date_start', $startDate);
			$recordModel->set('time_start', $startTime);
		} else {
			$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($recordModel->get('time_start'));
			$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($recordModel->get('date_start')." ".$startTime);
			list($startDate, $startTime) = explode(' ', $startDateTime);

			$recordModel->set('date_start', $startDate);
			$recordModel->set('time_start', $startTime);
		}

		$endDate = $request->get('due_date');
		if(!empty($endDate)) {
			//End Date and Time values
			$endTime = $request->get('time_end');
			$endDate = Vtiger_Date_UIType::getDBInsertedValue($request->get('due_date'));

			if ($endTime) {
				$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
				$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($request->get('due_date')." ".$endTime);
				list($endDate, $endTime) = explode(' ', $endDateTime);
			}

			$recordModel->set('time_end', $endTime);
			$recordModel->set('due_date', $endDate);
		} else {
			//End Date and Time values
			$endTime = $recordModel->get('time_end');
			$endDate = Vtiger_Date_UIType::getDBInsertedValue($recordModel->get('due_date'));

			if ($endTime) {
				$endTime = Vtiger_Time_UIType::getTimeValueWithSeconds($endTime);
				$endDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($recordModel->get('due_date')." ".$endTime);
				list($endDate, $endTime) = explode(' ', $endDateTime);
			}

			$recordModel->set('time_end', $endTime);
			$recordModel->set('due_date', $endDate);
		}

		$activityType = $request->get('activitytype');
		$visibility = $request->get('visibility');
		if(empty($activityType)) {
			$recordModel->set('activitytype', 'Task');
			$visibility = 'Private';
			$recordModel->set('visibility', $visibility);
		}

		if(empty($visibility)) {
			$assignedUserId = $recordModel->get('assigned_user_id');
			$sharedType = Calendar_Module_Model::getSharedType($assignedUserId);
			if($sharedType == 'selectedusers') {
				$sharedType = 'public';
			}
			$recordModel->set('visibility', ucfirst($sharedType));
		}

		$setReminder = $request->get('set_reminder');
		if($setReminder) {
			$_REQUEST['set_reminder'] = 'Yes';
		} else {
			$_REQUEST['set_reminder'] = 'No';
		}

		return $recordModel;
	}
}
