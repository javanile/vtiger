<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
********************************************************************************/

/**
 * Class to handle repeating events
 */
class Calendar_RepeatEvents {
	
	public static $recurringDataChanged;
	public static $recurringTypeChanged;

	/**
	 * Get timing using YYYY-MM-DD HH:MM:SS input string.
	 */
	static function mktime($fulldateString) {
		$splitpart = self::splittime($fulldateString);
		$datepart = split('-', $splitpart[0]);
		$timepart = split(':', $splitpart[1]);
		return mktime($timepart[0], $timepart[1], 0, $datepart[1], $datepart[2], $datepart[0]);
	}
	/**
	 * Increment the time by interval and return value in YYYY-MM-DD HH:MM format.
	 */
	static function nexttime($basetiming, $interval) {
		return date('Y-m-d H:i', strtotime($interval, $basetiming));
	}
	/**
	 * Based on user time format convert the YYYY-MM-DD HH:MM value.
	 */
	static function formattime($timeInYMDHIS) {
		global $current_user;
		$format_string = 'Y-m-d H:i';
		switch($current_user->date_format) {
			case 'dd-mm-yyyy': $format_string = 'd-m-Y H:i'; break;
			case 'mm-dd-yyyy': $format_string = 'm-d-Y H:i'; break;
			case 'yyyy-mm-dd': $format_string = 'Y-m-d H:i'; break;
		}
		return date($format_string, self::mktime($timeInYMDHIS));
	}
	/**
	 * Split full timing into date and time part.
	 */
	static function splittime($fulltiming) {
		return split(' ', $fulltiming);
	}
	/**
	 * Calculate the time interval to create repeated event entries.
	 */
	static function getRepeatInterval($type, $frequency, $recurringInfo, $start_date, $limit_date) {
		$repeatInterval = Array();
		$starting = self::mktime($start_date);
		$limiting = self::mktime($limit_date);

		if($type == 'Daily') {	
			$count = 0;
			while(true) {
				++$count;
				$interval = ($count * $frequency);
				if(self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
					break;
				}
				$repeatInterval[] = $interval;
			}
		} else if($type == 'Weekly') {
			if($recurringInfo->dayofweek_to_rpt == null) {
				$count = 0;
				$weekcount = 7;
				while(true) {
					++$count;
					$interval = $count * $weekcount;
					if(self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
						break;
					}
					$repeatInterval[] = $interval;
				}
			} else {
				$count = 0;
				while(true) {
					++$count;
					$interval = $count;
					$new_timing = self::mktime(self::nexttime($starting, "+$interval days"));
					$new_timing_dayofweek = date('N', $new_timing);
					if($new_timing > $limiting) {
						break;
					}
					if(in_array($new_timing_dayofweek-1, $recurringInfo->dayofweek_to_rpt)) {
						$repeatInterval[] = $interval;
					}
				}
			}
		} else if($type == 'Monthly') {
			$count = 0;
			$avg_monthcount = 30; // TODO: We need to handle month increments precisely!
			while(true) {
				++$count;
				$interval = $count * $avg_monthcount;
				if(self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
					break;
				}
				$repeatInterval[] = $interval;
			}
		} else if($type == 'Yearly') {
			$count = 0;
			$avg_monthcount = 30;
				while(true) {
					++$count;
					$interval = $count * $avg_monthcount;
					if(self::mktime(self::nexttime($starting, "+$interval days")) > $limiting) {
						break;
				}
				$repeatInterval[] = $interval;
			}
		}
		return $repeatInterval;
	}

	/**
	 * Repeat Activity instance till given limit.
	 */
	static function repeat($focus, $recurObj) {
		$adb = PearDatabase::getInstance();
		$frequency = $recurObj->recur_freq;
		$repeattype= $recurObj->recur_type;
		
		$base_focus = CRMEntity::getInstance('Events');
		$base_focus->column_fields = $focus->column_fields;
		$base_focus->id = $focus->id;
		$parentId = $focus->column_fields['id'];
		
		$vtEntityDelta = new VTEntityDelta();
        $delta = $vtEntityDelta->getEntityDelta('Events', $parentId, true);
		$skip_focus_fields = Array ('record_id', 'createdtime', 'modifiedtime');
		
		if($focus->column_fields['mode'] == 'edit') {
			$childRecords = array();
			$result = $adb->pquery("SELECT * FROM vtiger_activity_recurring_info WHERE activityid=?", array($parentId));
			$noofrows = $adb->num_rows($result);
			$parentRecurringId = $parentId;
			if($noofrows <= 0) {
				$queryResult = $adb->pquery("SELECT * FROM vtiger_activity_recurring_info WHERE recurrenceid=?", array($parentId));
				if($adb->num_rows($queryResult) > 0) {
					$parentRecurringId = $adb->query_result($queryResult, 0,"activityid");
					$result = $adb->pquery("SELECT * FROM vtiger_activity_recurring_info WHERE activityid=?", array($parentRecurringId));
					$noofrows = $adb->num_rows($result);
					
					if($focus->column_fields['recurringEditMode'] == 'all') {
						$parentModel = Vtiger_Record_Model::getInstanceById($parentId);
						$parentResult = $adb->pquery("SELECT 1 FROM vtiger_activity_recurring_info WHERE recurrenceid=?", array($parentRecurringId));
						if($adb->num_rows($parentResult) >= 1) {
							$parentModel = Vtiger_Record_Model::getInstanceById($parentRecurringId);
						} else {
							$recurringRecordsList = $parentModel->getRecurringRecordsList();
							foreach($recurringRecordsList as $parent=>$childs) {
								$parentRecurringId = $parent;
								$recurringRecords = $childs;
							}
							$parentModel = Vtiger_Record_Model::getInstanceById($recurringRecords[0]);
						}
						$_REQUEST['date_start'] = $parentModel->get('date_start');
						$recurObj = getrecurringObjValue();
					}
				}
			}
			for($i=0; $i<$noofrows; $i++) {
				$childRecords[] = $adb->query_result($result, $i,"recurrenceid");
			}
			if($focus->column_fields['recurringEditMode'] == 'future') {
				$parentKey = array_keys($childRecords, $parentId);
				$childRecords = array_slice($childRecords, $parentKey[0]);
			}
			$eventStartDate = $focus->column_fields['date_start'];
			$interval = strtotime($focus->column_fields['due_date']) - 
						strtotime($focus->column_fields['date_start']);
			$i = 0;
			$updatedRecords = array();
			
			if(self::$recurringTypeChanged && $focus->column_fields['recurringEditMode'] == 'future') {
				foreach($childRecords as $record) {
					$adb->pquery("DELETE FROM vtiger_activity_recurring_info WHERE activityid=? AND recurrenceid=?", array($parentRecurringId, $record));
				}
				$parentRecurringId = $parentId;
			}
			
			foreach ($recurObj->recurringdates as $index => $startDate) {
				$recordId = $childRecords[$i];
				if(!empty($recordId) && !empty($startDate)) {
					$i++;
					if(!self::$recurringDataChanged && empty($delta['date_start']) && empty($delta['due_date'])) {
						$skip_focus_fields[] = 'date_start';
						$skip_focus_fields[] = 'due_date';
					}
					if($index == 0 && $eventStartDate == $startDate && $focus->column_fields['recurringEditMode'] != 'future') {
						$updatedRecords[] = $recordId;
						continue;
					}
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
					$recordModel->set('mode', 'edit');
					if($focus->column_fields['recurringEditMode'] == 'future' && $recordModel->get('date_start') >= $eventStartDate) {
						$startDateTimestamp = strtotime($startDate);
						$endDateTime = $startDateTimestamp + $interval;
						$endDate = date('Y-m-d', $endDateTime);
						
						foreach($base_focus->column_fields as $key=>$value) {
							if(in_array($key, $skip_focus_fields)) {
								// skip copying few fields
							} else if($key == 'date_start') {
								$recordModel->set('date_start',$startDate);
							} else if($key == 'due_date') {
								$recordModel->set('due_date',$endDate);
							}  else {
								if(!empty($delta[$key])) {
									$recordModel->set($key, $value);
								}
							}
						}
						$recordModel->set('id', $recordId);
						if($numberOfRepeats > 10 && $index > 10) {
							unset($recordModel['sendnotification']);
						}
						$updatedRecords[] = $recordId;
						$recordModel->save('Calendar');
						if(self::$recurringTypeChanged) {
							$adb->pquery("INSERT INTO vtiger_activity_recurring_info VALUES (?,?)", array($parentId, $recordId));
						}
					} else if($focus->column_fields['recurringEditMode'] == 'all') {
						$startDateTimestamp = strtotime($startDate);
						$endDateTime = $startDateTimestamp + $interval;
						$endDate = date('Y-m-d', $endDateTime);
						foreach($base_focus->column_fields as $key=>$value) {
							if(in_array($key, $skip_focus_fields)) {
								// skip copying few fields
							} else if($key == 'date_start') {
								$recordModel->set('date_start',$startDate);
							} else if($key == 'due_date') {
								$recordModel->set('due_date',$endDate);
							} else {
								if(!empty($delta[$key])) {
									$recordModel->set($key, $value);
								}
							}
						}
						$recordModel->set('id', $recordId);

						if($numberOfRepeats > 10 && $index > 10) {
							unset($recordModel['sendnotification']);
						}
						$updatedRecords[] = $recordId;
						$recordModel->save('Calendar');
					}

				} else if(empty($recordId) && !empty($startDate) && self::$recurringDataChanged) {
					//create new record with new start date
					$datesList = array();
					$datesList[] = $startDate;
					if(empty($focus->column_fields['eventstatus'])) {
						$focus->column_fields['eventstatus'] = 'Planned';
					}
					self::createRecurringEvents($focus, $recurObj, $datesList, $parentRecurringId);
				}
			}
			$deletingRecords = array_diff($childRecords, $updatedRecords);
			if(self::$recurringDataChanged && !empty($deletingRecords)) {
				foreach($deletingRecords as $record) {
					//delete reocrd with that reocrdid
					$recordModel = Vtiger_Record_Model::getInstanceById($record);
					if(!empty($parentRecurringId)) {
						$parentId = $parentRecurringId;
					} 
					$adb->pquery("DELETE FROM vtiger_activity_recurring_info WHERE activityid=? AND recurrenceid=?", array($parentId, $record));
					$recordModel->delete();
				}
			}
		} else {
			$recurringDates = $recurObj->recurringdates;
			self::createRecurringEvents($focus, $recurObj, $recurringDates);
		}
	}
	
	static function createRecurringEvents($focus, $recurObj, $recurringDates, $parentId = false) {
		$adb = PearDatabase::getInstance();
		$base_focus = CRMEntity::getInstance('Events');
		$base_focus->column_fields = $focus->column_fields;
		$base_focus->id = $focus->id;
		$skip_focus_fields = Array ('record_id', 'createdtime', 'modifiedtime');
		if(empty($parentId)) {
			$parentId = $focus->column_fields['id'];
		}
		/** Create instance before and reuse */
		$new_focus = CRMEntity::getInstance('Events');

		$eventStartDate = $focus->column_fields['date_start'];
		$interval = strtotime($focus->column_fields['due_date']) - 
				strtotime($focus->column_fields['date_start']);
		
		foreach ($recurringDates as $index => $startDate) {
			if($index == 0 && $eventStartDate == $startDate) {
				continue;
			}
			$startDateTimestamp = strtotime($startDate);
			$endDateTime = $startDateTimestamp + $interval;
			$endDate = date('Y-m-d', $endDateTime);

			// Reset the new_focus and prepare for reuse
			if(isset($new_focus->id)) unset($new_focus->id);
			$new_focus->column_fields = new TrackableObject();

			foreach($base_focus->column_fields as $key=>$value) {
				if(in_array($key, $skip_focus_fields)) {
					// skip copying few fields
				} else if($key == 'date_start') {
					$new_focus->column_fields['date_start'] = $startDate;
				} else if($key == 'due_date') {
					$new_focus->column_fields['due_date']   = $endDate;
				} else {
					$new_focus->column_fields[$key]         = $value;
				}
			}
			if($numberOfRepeats > 10 && $index > 10) {
				unset($new_focus->column_fields['sendnotification']);
			}
			$new_focus->save('Calendar');
			$record = $new_focus->id;
			
			$adb->pquery("INSERT INTO vtiger_activity_recurring_info VALUES (?,?)", array($parentId, $record));

		}
	}
	
	static function repeatFromRequest($focus, $recurObjDb = false) {
		global $log, $default_charset, $current_user;
		$adb = PearDatabase::getInstance();
		$recurObj = getrecurringObjValue();
		self::$recurringDataChanged = self::checkRecurringDataChanged($recurObj, $recurObjDb);
		if(!empty($recurObjDb) && self::$recurringDataChanged && $recurObj->recur_type != $recurObjDb->recur_type) {
			self::$recurringTypeChanged = true;
		} else {
			self::$recurringTypeChanged = false;
		}
		if($focus->column_fields['recurringtype'] != '' && $focus->column_fields['recurringtype'] != '--None--' && $focus->column_field['recurringEditMode'] != 'current') {
			//If no followup mode, recurring events status should not be held for future events
			if($focus->column_fields['eventstatus'] == 'Held') {
				if($focus->column_fields['mode'] == '') {
					$focus->column_fields['eventstatus'] = 'Planned';
				} else {
					unset($focus->column_fields['eventstatus']);
				}
			}
			
			$originalRecordId = $focus->column_fields['id'];
			//If recurring Enabled, insert the entry only once for parent also
			if(empty($recurObjDb) && self::$recurringDataChanged) {
				$adb->pquery("INSERT INTO vtiger_activity_recurring_info VALUES (?,?)", array($originalRecordId, $originalRecordId));
			}
			self::repeat($focus, $recurObj);
		} else if(empty($recurObj) && self::$recurringDataChanged) {
			//If recurring info unchecked, should delete all the events in the series
			self::deleteRepeatEvents($focus->column_fields['id']);
		}
	}
    
	static function deleteRepeatEvents($parentId) {
		$adb = PearDatabase::getInstance();
		$recordModel = Vtiger_Record_Model::getCleanInstance('Events');
		$recordModel->set('id', $parentId);
		$recurringRecordsList = $recordModel->getRecurringRecordsList();
		foreach($recurringRecordsList as $parent=>$childs) {
			$parentRecurringId = $parent;
			$childRecords = $childs;
		}
		foreach($childRecords as $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$adb->pquery("DELETE FROM vtiger_activity_recurring_info WHERE activityid=? AND recurrenceid=?", array($parentRecurringId, $record));
			if($record == $parentId) {
				continue;
			}
			$recordModel->delete();
		}
	}
	
    static function checkRecurringDataChanged($recurObjRequest, $recurObjDb) {
        if(($recurObjRequest->recur_type == $recurObjDb->recur_type) && ($recurObjRequest->recur_freq == $recurObjDb->recur_freq)
                && ($recurObjRequest->recurringdates[0] == $recurObjDb->recurringdates[0]) && ($recurObjRequest->recurringenddate == $recurObjDb->recurringenddate)
                && ($recurObjRequest->dayofweek_to_rpt == $recurObjDb->dayofweek_to_rpt) && ($recurObjRequest->repeat_monthby == $recurObjDb->repeat_monthby)
                && ($recurObjRequest->rptmonth_datevalue == $recurObjDb->rptmonth_datevalue) && ($recurObjRequest->rptmonth_daytype == $recurObjDb->rptmonth_daytype)) {
            return false;
        } else {
            return true;
        }
    }
}

?>