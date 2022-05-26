<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport('modules.Calendar.iCal.iCalendar_rfc2445');
vimport('modules.Calendar.iCal.iCalendar_components');
vimport('modules.Calendar.iCal.iCalendar_properties');
vimport('modules.Calendar.iCal.iCalendar_parameters');

class Calendar_ExportData_Action extends Vtiger_ExportData_Action {

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	public function getExportQueryForIcal(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $moduleModel->getExportQuery('');
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return <String>
	 */
	public function getExportContentType(Vtiger_Request $request) {
		if ($request->get('type') == 'csv') {
			return parent::getExportContentType($request);
		}
		return 'text/calendar';
	}

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	public function ExportData(Vtiger_Request $request) {
		if ($request->get('type') == 'csv') {
			parent::ExportData($request);
			return;
		}

		$db = PearDatabase::getInstance();
		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$moduleModel->getFields();

		$moduleModel->setEventFieldsForExport();
		$moduleModel->setTodoFieldsForExport();

		$query = $this->getExportQueryForIcal($request);
		$result = $db->pquery($query, array());

		$this->outputIcal($request, $result, $moduleModel);
	}

	/**
	 * Function that create the exported file
	 * @param Vtiger_Request $request
	 * @param <Array> $result
	 * @param Vtiger_Module_Model $moduleModel
	 */
	public function outputIcal($request, $result, $moduleModel) {
		$fileName = $request->getModule();
		// for content disposition header comma should not be there in filename 
		$fileName = str_replace(',', '_', $fileName);
		$exportType = $this->getExportContentType($request);

		// Send the right content type and filename
		header("Content-type: $exportType");
		header("Content-Disposition: attachment; filename={$fileName}.ics");

		$timeZone = new iCalendar_timezone;
		$timeZoneId = split('/', date_default_timezone_get());

		if(!empty($timeZoneId[1])) {
			$zoneId = $timeZoneId[1];
		} else {
			$zoneId = $timeZoneId[0];
		}

		$timeZone->add_property('TZID', $zoneId);
		$timeZone->add_property('TZOFFSETTO', date('O'));

		if(date('I') == 1) {
			$timeZone->add_property('DAYLIGHTC', date('I'));
		} else {
			$timeZone->add_property('STANDARDC', date('I'));
		}

		$myiCal = new iCalendar;
		$myiCal->add_component($timeZone);

		while (!$result->EOF) {
			$eventFields = $result->fields;
			$id = $eventFields['activityid'];
			$type = $eventFields['activitytype'];
			if($type != 'Task') {
				$temp = $moduleModel->get('eventFields');
				foreach($temp as $fieldName => $access) {
					/* Priority property of ical is Integer
					 * http://kigkonsult.se/iCalcreator/docs/using.html#PRIORITY
					 */
					if($fieldName == 'priority'){
						$priorityMap = array('High'=>'1','Medium'=>'2','Low'=>'3');
						$priorityval = $eventFields[$fieldName];
						$icalZeroPriority = 0;
						if(array_key_exists($priorityval, $priorityMap))
							$temp[$fieldName] = $priorityMap[$priorityval];
						else 
							$temp[$fieldName] = $icalZeroPriority;
					}
					else
						$temp[$fieldName] = $eventFields[$fieldName];
				}
				$temp['id'] = $id;

				$iCalTask = new iCalendar_event;
				$iCalTask->assign_values($temp);

				$iCalAlarm = new iCalendar_alarm;
				$iCalAlarm->assign_values($temp);
				$iCalTask->add_component($iCalAlarm);
			} else {
				$temp = $moduleModel->get('todoFields');
				foreach($temp as $fieldName => $access) {
					if($fieldName == 'priority'){
						$priorityMap = array('High'=>'1','Medium'=>'2','Low'=>'3');
						$priorityval = $eventFields[$fieldName];
						$icalZeroPriority = 0;
						if(array_key_exists($priorityval, $priorityMap))
							$temp[$fieldName] = $priorityMap[$priorityval];
						else 
							$temp[$fieldName] = $icalZeroPriority;
					}
					else
						$temp[$fieldName] = $eventFields[$fieldName];
				}
				$iCalTask = new iCalendar_todo;
				$iCalTask->assign_values($temp);
			}

			$myiCal->add_component($iCalTask);
			$result->MoveNext();
		}
		echo $myiCal->serialize();
	}

	public function getExportQuery(Vtiger_Request $request) {
		$query = parent::getExportQuery($request);

		$queryComponents = preg_split('/ FROM /i', $query);
		if (count($queryComponents) == 2) {
			$exportQuery = "$queryComponents[0], vtiger_activity.activityid FROM $queryComponents[1]";
		}

		$queryComponents = preg_split('/ WHERE /i', $exportQuery);
		$exportQuery = "$queryComponents[0] WHERE vtiger_activity.activitytype != 'Emails' AND $queryComponents[1]";

		$orderByComponents = preg_split('/ ORDER BY /i', $exportQuery);
		if (count($orderByComponents) == 1) {
			$limitQuery = '';
			if ($request->getMode() == 'ExportCurrentPage') {
				list($exportQuery, $limitQuery) = preg_split('/ LIMIT /i', $exportQuery);
			}
			$exportQuery = "$exportQuery ORDER BY str_to_date(concat(date_start,time_start),'%Y-%m-%d %H:%i:%s') DESC";

			if ($limitQuery) {
				$exportQuery = "$exportQuery LIMIT $limitQuery";
			}
		}

		return $exportQuery;
	}

	public function moduleFieldInstances($moduleName) {
		$skippedFields = array('contact_id', 'duration_hours', 'duration_minutes', 'recurringtype', 'reminder_time');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$moduleFields = $moduleModel->getFields();

		$eventsModuleModel = Vtiger_Module_Model::getInstance('Events');
		$eventModuleFieldList = $eventsModuleModel->getFields();
		$moduleFields = array_merge($moduleFields, $eventModuleFieldList);

		foreach ($moduleFields as $fieldName => $fieldModel) {
			if (in_array($fieldName, $skippedFields)) {
				unset($moduleFields[$fieldName]);
			}
		}

		return $moduleFields;
	}

	public function sanitizeValues($arr) {
		$activityId = $arr['activityid'];
		$sanitizeValues = parent::sanitizeValues($arr);

		$startDateParts = explode(' ', $sanitizeValues['date_start']);
		$dueDateParts = explode(' ', $sanitizeValues['due_date']);

		$sanitizeValues['time_start']	= $startDateParts[1];
		if ($sanitizeValues['time_end']) {
			$sanitizeValues['time_end']	= $dueDateParts[1];
		}
		$sanitizeValues['due_date']		= trim($dueDateParts[0].' '.$sanitizeValues['time_end']);
		$sanitizeValues['activitytype'] = $arr['activitytype'];

		$moduleModel = Vtiger_Module_Model::getInstance('Events');
		$recordModel = Vtiger_Record_Model::getInstanceById($activityId, $moduleModel);
		$db = PearDatabase::getInstance();

		$query = 'SELECT label FROM vtiger_crmentity
					INNER JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.contactid = vtiger_crmentity.crmid
					WHERE vtiger_cntactivityrel.activityid = ?';
		$result = $db->pquery($query, array($activityId));
		$numOfRows = $db->num_rows($result);

		$relatedContacts = array();
		while ($rowData = $db->fetch_row($result)) {
			$relatedContacts[] = 'Contacts::::'.decode_html(Vtiger_Util_Helper::toSafeHTML($rowData['label']));
		}
		$contactInfo = implode(', ', $relatedContacts);
		$sanitizeValues['contact_id'] = $contactInfo;

		if ($recordModel->getType() == 'Events') {
			$sanitizeValues['status'] = $sanitizeValues['eventstatus'];
		}
		unset($sanitizeValues['eventstatus']);
		return $sanitizeValues;
	}

	public function getHeaders() {
		$translatedHeaders = array_unique(parent::getHeaders());
		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');

		$fieldModel = $moduleModel->getField('contact_id');
		$translatedHeaders[] = vtranslate(html_entity_decode($fieldModel->get('label'), ENT_QUOTES), 'Calendar');

		return $translatedHeaders;
	}

}