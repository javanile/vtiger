<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~include/utils/RecurringType.php');

class Calendar_Record_Model extends Vtiger_Record_Model {

/**
	 * Function returns the Entity Name of Record Model
	 * @return <String>
	 */
	function getName() {
		$name = $this->get('subject');
		if(empty($name)) {
			$name = parent::getName();
		}
		return $name;
	}

	/**
	 * Function to insert details about reminder in to Database
	 * @param <Date> $reminderSent
	 * @param <integer> $recurId
	 * @param <String> $reminderMode like edit/delete
	 */
	public function setActivityReminder($reminderSent = 0, $recurId = '', $reminderMode = '') {
		$moduleInstance = CRMEntity::getInstance($this->getModuleName());
		$moduleInstance->activity_reminder($this->getId(), $this->get('reminder_time'), $reminderSent, $recurId, $reminderMode);
	}

	/**
	 * Function returns the Module Name based on the activity type
	 * @return <String>
	 */
	function getType() {
		$activityType = $this->get('activitytype');
		if($activityType == 'Task') {
			return 'Calendar';
		}
		return 'Events';
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module=Calendar&view='.$module->getDetailViewName().'&record='.$this->getId();
	}

	/**
	 * Function returns recurring information for EditView
	 * @return <Array> - which contains recurring Information
	 */
	public function getRecurrenceInformation($request = false) {
		$recurringObject = $this->getRecurringObject();

		if ($request && !$request->get('id') && $request->get('repeat_frequency')) {
			$recurringObject = getrecurringObjValue();
		}

		if ($recurringObject) {
			$recurringData['recurringcheck'] = 'Yes';
			$recurringData['repeat_frequency'] = $recurringObject->getRecurringFrequency();
			$recurringData['eventrecurringtype'] = $recurringObject->getRecurringType();
			$recurringEndDate = $recurringObject->getRecurringEndDate(); 
			if(!empty($recurringEndDate)){ 
				$recurringData['recurringenddate'] = $recurringEndDate->get_formatted_date(); 
			} 
			$recurringInfo = $recurringObject->getUserRecurringInfo();

			if ($recurringObject->getRecurringType() == 'Weekly') {
				$noOfDays = count($recurringInfo['dayofweek_to_repeat']);
				for ($i = 0; $i < $noOfDays; ++$i) {
					$recurringData['week'.$recurringInfo['dayofweek_to_repeat'][$i]] = 'checked';
				}
			} elseif ($recurringObject->getRecurringType() == 'Monthly') {
				$recurringData['repeatMonth'] = $recurringInfo['repeatmonth_type'];
				if ($recurringInfo['repeatmonth_type'] == 'date') {
					$recurringData['repeatMonth_date'] = $recurringInfo['repeatmonth_date'];
				} else {
					$recurringData['repeatMonth_daytype'] = $recurringInfo['repeatmonth_daytype'];
					$recurringData['repeatMonth_day'] = $recurringInfo['dayofweek_to_repeat'][0];
				}
			}
		} else {
			$recurringData['recurringcheck'] = 'No';
		}
		return $recurringData;
	}

	function save() {
		//Time should changed to 24hrs format
		$_REQUEST['time_start'] = Vtiger_Time_UIType::getTimeValueWithSeconds($_REQUEST['time_start']);
		$_REQUEST['time_end'] = Vtiger_Time_UIType::getTimeValueWithSeconds($_REQUEST['time_end']);
		parent::save();
	}
	
	/**
	 * Function to delete the current Record Model
	 */
	public function delete() {
		$adb = PearDatabase::getInstance();
		$recurringEditMode = $this->get('recurringEditMode');
		$deletedRecords = array();
		if(!empty($recurringEditMode) && $recurringEditMode != 'current') {
			$recurringRecordsList = $this->getRecurringRecordsList();
			foreach($recurringRecordsList as $parent=>$childs) {
				$parentRecurringId = $parent;
				$childRecords = $childs;
			}
			if($recurringEditMode == 'future') {
				$parentKey = array_keys($childRecords, $this->getId());
				$childRecords = array_slice($childRecords, $parentKey[0]);
			}
			foreach($childRecords as $record) {
				$recordModel = $this->getInstanceById($record, $this->getModuleName());
				$adb->pquery("DELETE FROM vtiger_activity_recurring_info WHERE activityid=? AND recurrenceid=?", array($parentRecurringId, $record));
				$recordModel->getModule()->deleteRecord($recordModel);
				$deletedRecords[] = $record;
			}
		} else {
			if($recurringEditMode == 'current') {
				$parentRecurringId = $this->getParentRecurringRecord();
				$adb->pquery("DELETE FROM vtiger_activity_recurring_info WHERE activityid=? AND recurrenceid=?", array($parentRecurringId, $this->getId()));
			}
			$this->getModule()->deleteRecord($this);
			$deletedRecords[] = $this->getId();
		}
		return $deletedRecords;
	}

	/**
	 * Function to get recurring information for the current record in detail view
	 * @return <Array> - which contains Recurring Information
	 */
	public function getRecurringDetails() {
		$recurringObject = $this->getRecurringObject();
		if ($recurringObject) {
			$recurringInfoDisplayData = $recurringObject->getDisplayRecurringInfo();
			$recurringEndDate = $recurringObject->getRecurringEndDate(); 
		} else {
			$recurringInfoDisplayData['recurringcheck'] = vtranslate('LBL_NO', $currentModule);
			$recurringInfoDisplayData['repeat_str'] = '';
		}
		if(!empty($recurringEndDate)){ 
			$recurringInfoDisplayData['recurringenddate'] = $recurringEndDate->get_formatted_date(); 
		}

		return $recurringInfoDisplayData;
	}

	/**
	 * Function to get the recurring object
	 * @return Object - recurring object
	 */
	public function getRecurringObject() {
		$db = PearDatabase::getInstance();
		$query = 'SELECT vtiger_recurringevents.*, vtiger_activity.date_start, vtiger_activity.time_start, vtiger_activity.due_date, vtiger_activity.time_end FROM vtiger_recurringevents
					INNER JOIN vtiger_activity ON vtiger_activity.activityid = vtiger_recurringevents.activityid
					WHERE vtiger_recurringevents.activityid = ?';
		$result = $db->pquery($query, array($this->getId()));
		if ($db->num_rows($result)) {
			return RecurringType::fromDBRequest($db->query_result_rowdata($result, 0));
		}
		return false;
	}

	/**
	 * Function updates the Calendar Reminder popup's status
	 */
	public function updateReminderStatus($status=1) {
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_activity_reminder_popup set status = ? where recordid = ?", array($status, $this->getId()));

	}
	/**
	 * Function to get parent recurring event Id
	 */
	public function getParentRecurringRecord() {
		$adb = PearDatabase::getInstance();
		$recordId = $this->getId();
		$result = $adb->pquery("SELECT * FROM vtiger_activity_recurring_info WHERE activityid=? OR activityid = (SELECT activityid FROM vtiger_activity_recurring_info WHERE recurrenceid=?) LIMIT 1", array($recordId, $recordId));
		$parentRecurringId = $adb->query_result($result, 0,"activityid");
		return $parentRecurringId;
	}
	
	/**
	 * Function to get recurring records list
	 */
	public function getRecurringRecordsList() {
		$adb = PearDatabase::getInstance();
		$recurringRecordsList = array();
		$recordId = $this->getId();
		$result = $adb->pquery("SELECT * FROM vtiger_activity_recurring_info WHERE activityid=? OR activityid = (SELECT activityid FROM vtiger_activity_recurring_info WHERE recurrenceid=?)", array($recordId, $recordId));
		$noofrows = $adb->num_rows($result);
		$parentRecurringId = $adb->query_result($result, 0,"activityid");
		$childRecords = array();
		for($i=0; $i<$noofrows; $i++) {
			$childRecords[] = $adb->query_result($result, $i,"recurrenceid");
		}
		$recurringRecordsList[$parentRecurringId] = $childRecords;
		return $recurringRecordsList;
	}
	
	/**
	 * Function to get recurring enabled for record
	 */
	public function isRecurringEnabled() {
		$recurringInfo = $this->getRecurringDetails();
		if($recurringInfo['recurringcheck'] == 'Yes') {
			return true;
		}
		return false;
	}
	
}
