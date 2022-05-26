<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_DragDropAjax_Action extends Calendar_SaveAjax_Action {

	function __construct() {
		$this->exposeMethod('updateDeltaOnResize');
		$this->exposeMethod('updateDeltaOnDrop');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	public function updateDeltaOnResize(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$activityType = $request->get('activitytype');
		$recordId = $request->get('id');
		$dayDelta = $request->get('dayDelta');
		$minuteDelta = $request->get('minuteDelta');
		$secondsDelta = $request->get('secondsDelta',NULL);
		$recurringEditMode = $request->get('recurringEditMode');

		$actionname = 'EditView';
		$response = new Vtiger_Response();
		try {
			if(isPermitted($moduleName, $actionname, $recordId) === 'no'){
				$result = array('ispermitted'=>false,'error'=>false);
				$response->setResult($result);
			} else {
				$result = array('ispermitted'=>true,'error'=>false);
				$record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$record->set('mode','edit');

				$startDateTime[] = $record->get('date_start');
				$startDateTime[] = $record->get('time_start');
				$startDateTime = implode(' ',$startDateTime);

				$oldDateTime[] = $record->get('due_date');
				$oldDateTime[] = $record->get('time_end');
				$oldDateTime = implode(' ',$oldDateTime);
				$resultDateTime = $this->changeDateTime($oldDateTime,$dayDelta,$minuteDelta,$secondsDelta);
				$interval = strtotime($resultDateTime) - strtotime($startDateTime);

				if(!empty($recurringEditMode) && $recurringEditMode != 'current') {
					$recurringRecordsList = $record->getRecurringRecordsList();
					foreach($recurringRecordsList as $parent=>$childs) {
						$parentRecurringId = $parent;
						$childRecords = $childs;
					}
					if($recurringEditMode == 'future') {
						$parentKey = array_keys($childRecords, $recordId);
						$childRecords = array_slice($childRecords, $parentKey[0]);
					}
					foreach($childRecords as $childId) {
						$recordModel = Vtiger_Record_Model::getInstanceById($childId, 'Events');
						$recordModel->set('mode','edit');

						$startDateTime = '';
						$startDateTime[] = $recordModel->get('date_start');
						$startDateTime[] = $recordModel->get('time_start');
						$startDateTime = implode(' ',$startDateTime);
						$dueDate = strtotime($startDateTime) + $interval;
						$formatDate = date("Y-m-d H:i:s", $dueDate);
						$parts = explode(' ',$formatDate);
						$startDateTime = new DateTime($startDateTime);

						$recordModel->set('due_date',$parts[0]);
						if(activitytype != 'Task') {
							$recordModel->set('time_end',$parts[1]);
						}

						$endDateTime = '';
						$endDateTime[] = $recordModel->get('due_date');
						$endDateTime[] = $recordModel->get('time_end');
						$endDateTime = implode(' ',$endDateTime);
						$endDateTime = new DateTime($endDateTime);

						if($startDateTime <= $endDateTime) {
							$this->setRecurrenceInfo($recordModel);
							$recordModel->save();
						} else {
							$result['error'] = true;
						}
					}
					$result['recurringRecords'] = true;
				} else {
					$oldDateTime = '';
					$oldDateTime[] = $record->get('due_date');
					$oldDateTime[] = $record->get('time_end');
					$oldDateTime = implode(' ',$oldDateTime);
					$resultDateTime = $this->changeDateTime($oldDateTime,$dayDelta,$minuteDelta,$secondsDelta);
					$parts = explode(' ',$resultDateTime);
					$record->set('due_date',$parts[0]);
					if(activitytype != 'Task') {
						$record->set('time_end',$parts[1]);
					}

					$startDateTime = '';
					$startDateTime[] = $record->get('date_start');
					$startDateTime[] = $record->get('time_start');
					$startDateTime = implode(' ',$startDateTime);
					$startDateTime = new DateTime($startDateTime);

					$endDateTime[] = $record->get('due_date');
					$endDateTime[] = $record->get('time_end');
					$endDateTime = implode(' ',$endDateTime);
					$endDateTime = new DateTime($endDateTime);
					//Checking if startDateTime is less than or equal to endDateTime
					if($startDateTime <= $endDateTime) {
						$this->setRecurrenceInfo($record);
						$record->save();
					} else {
						$result['error'] = true;
					}
					$result['recurringRecords'] = false;
				}

				$response->setResult($result);
			}
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	function setRecurrenceInfo($recordModel) {
		//Activity.php insertIntoRecurringTable api depends on $_REQUEST mode edit
		$_REQUEST['mode'] = 'edit';
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

		if ($recurringInfo['eventrecurringtype'] == 'Weekly') {
			$_REQUEST['sun_flag'] = $recurringInfo['week0'];
			$_REQUEST['mon_flag'] = $recurringInfo['week1'];
			$_REQUEST['tue_flag'] = $recurringInfo['week2'];
			$_REQUEST['wed_flag'] = $recurringInfo['week3'];
			$_REQUEST['thu_flag'] = $recurringInfo['week4'];
			$_REQUEST['fri_flag'] = $recurringInfo['week5'];
			$_REQUEST['sat_flag'] = $recurringInfo['week6'];
		}

		if ($recurringInfo['eventrecurringtype'] == 'Monthly') {
			if ($recurringInfo['repeatMonth'] == 'date') {
				$_REQUEST['repeatMonth'] = $recurringInfo['repeatMonth'];
				$_REQUEST['repeatMonth_date'] = $recurringInfo['repeatMonth_date'];
			} else if ($recurringInfo['repeatMonth'] == 'day') {
				$_REQUEST['repeatMonth_daytype'] = $recurringInfo['repeatMonth_daytype'];
				$_REQUEST['repeatMonth_day'] = $recurringInfo['repeatMonth_day'];
			}
		}
	}

	public function updateDeltaOnDrop(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$activityType = $request->get('activitytype');
		$recordId = $request->get('id');
		$dayDelta = $request->get('dayDelta');
		$minuteDelta = $request->get('minuteDelta');
		$secondsDelta = $request->get('secondsDelta');
		$recurringEditMode = $request->get('recurringEditMode');
		$actionname = 'EditView';

		$response = new Vtiger_Response();
		try {
			if(isPermitted($moduleName, $actionname, $recordId) === 'no'){
				$result = array('ispermitted'=>false);
				$response->setResult($result);
			} else {
				$result = array('ispermitted'=>true);
				$record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$record->set('mode','edit');

				$oldStartDateTime[] = $record->get('date_start');
				$oldStartDateTime[] = $record->get('time_start');
				$oldStartDateTime = implode(' ',$oldStartDateTime);
				$resultDateTime = $this->changeDateTime($oldStartDateTime, $dayDelta, $minuteDelta, $secondsDelta);
				$startDateInterval = strtotime($resultDateTime) - strtotime($oldStartDateTime);

				$oldEndDateTime[] = $record->get('due_date');
				$oldEndDateTime[] = $record->get('time_end');
				$oldEndDateTime = implode(' ', $oldEndDateTime);
				$resultDateTime = $this->changeDateTime($oldEndDateTime, $dayDelta, $minuteDelta, $secondsDelta);
				$endDateInterval = strtotime($resultDateTime) - strtotime($oldEndDateTime);

				if (!empty($recurringEditMode) && $recurringEditMode != 'current') {
					$recurringRecordsList = $record->getRecurringRecordsList();
					foreach ($recurringRecordsList as $parent => $childs) {
						$parentRecurringId = $parent;
						$childRecords = $childs;
					}
					if ($recurringEditMode == 'future') {
						$parentKey = array_keys($childRecords, $recordId);
						$childRecords = array_slice($childRecords, $parentKey[0]);
					}
					foreach ($childRecords as $childId) {
						$recordModel = Vtiger_Record_Model::getInstanceById($childId, 'Events');
						$recordModel->set('mode', 'edit');

						$startDateTime = '';
						$startDateTime[] = $recordModel->get('date_start');
						$startDateTime[] = $recordModel->get('time_start');
						$startDateTime = implode(' ', $startDateTime);
						$startDate = strtotime($startDateTime) + $startDateInterval;
						$formatStartDate = date("Y-m-d H:i:s", $startDate);
						$parts = explode(' ', $formatStartDate);
						$startDateTime = new DateTime($startDateTime);

						$recordModel->set('date_start', $parts[0]);
						if (activitytype != 'Task')
							$recordModel->set('time_start', $parts[1]);

						$endDateTime = '';
						$endDateTime[] = $recordModel->get('due_date');
						$endDateTime[] = $recordModel->get('time_end');
						$endDateTime = implode(' ', $endDateTime);
						$endDate = strtotime($endDateTime) + $endDateInterval;
						$formatEndDate = date("Y-m-d H:i:s", $endDate);
						$endDateParts = explode(' ', $formatEndDate);
						$endDateTime = new DateTime($endDateTime);
						$recordModel->set('due_date', $endDateParts[0]);
						if (activitytype != 'Task')
							$recordModel->set('time_end', $endDateParts[1]);

						$this->setRecurrenceInfo($recordModel);
						$recordModel->save();
					}
					$result['recurringRecords'] = true;
				} else {
					$oldStartDateTime = '';
					$oldStartDateTime[] = $record->get('date_start');
					$oldStartDateTime[] = $record->get('time_start');
					$oldStartDateTime = implode(' ', $oldStartDateTime);
					$resultDateTime = $this->changeDateTime($oldStartDateTime,$dayDelta,$minuteDelta,$secondsDelta);
					$parts = explode(' ',$resultDateTime);
					$record->set('date_start',$parts[0]);
					$record->set('time_start',$parts[1]);

					$oldEndDateTime = '';
					$oldEndDateTime[] = $record->get('due_date');
					$oldEndDateTime[] = $record->get('time_end');
					$oldEndDateTime = implode(' ',$oldEndDateTime);
					$resultDateTime = $this->changeDateTime($oldEndDateTime,$dayDelta,$minuteDelta,$secondsDelta);
					$parts = explode(' ',$resultDateTime);
					$record->set('due_date',$parts[0]);
					if(activitytype != 'Task') {
						$record->set('time_end',$parts[1]);
					}

					$this->setRecurrenceInfo($record);
					$record->save();
					$result['recurringRecords'] = false;
				}
			}
			$response->setResult($result);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}
	/* *
	 * Function adds days and minutes to datetime string
	 */
	public function changeDateTime($datetime,$daysToAdd,$minutesToAdd,$secondsDelta=NULL){
		$datetime = strtotime($datetime);
		if(!$secondsDelta) {
			$secondsDelta = (60*$minutesToAdd)+(24*60*60*$daysToAdd);
		}
		$futureDate = $datetime+$secondsDelta;
		$formatDate = date("Y-m-d H:i:s", $futureDate);
		return $formatDate;
	}

}
?>
