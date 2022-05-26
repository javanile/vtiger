<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/
/**
 * Calendar QuickCreate Record Structure Model
 */
class Calendar_QuickCreateRecordStructure_Model extends Vtiger_QuickCreateRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$moduleModel = $this->getModule();

		$fieldModelList = $moduleModel->getQuickCreateFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordModelFieldValue = $recordModel->get($fieldName);
			if (!empty($recordModelFieldValue)) {
				$fieldValue = $recordModelFieldValue;
				if ($fieldName == 'date_start') {
					$fieldValue = $fieldValue.' '.$recordModel->get('time_start');
				} else if ($fieldName == 'due_date' && $moduleModel->get('name') != 'Calendar') {
					//Do not concat duedate and endtime for Tasks as it contains only duedate
					if ($moduleModel->getName() != 'Calendar') {
						$fieldValue = $fieldValue.' '.$recordModel->get('time_end');
					}
				}
				$fieldModel->set('fieldvalue', $fieldValue);
			} else if ($fieldName == 'eventstatus') {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaulteventstatus = $currentUserModel->get('defaulteventstatus');
				$fieldValue = $defaulteventstatus;
				if (!$defaulteventstatus || $defaulteventstatus == 'Select an Option') {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldModel->set('fieldvalue', $fieldValue);
			} else if ($fieldName == 'activitytype') {
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$defaultactivitytype = $currentUserModel->get('defaultactivitytype');
				$fieldValue = $defaultactivitytype;
				if (!$defaultactivitytype || $defaultactivitytype == 'Select an Option') {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldModel->set('fieldvalue', $fieldValue);
			} else {
				$defaultValue = $fieldModel->getDefaultFieldValue();
				if ($defaultValue) {
					$fieldModel->set('fieldvalue', $defaultValue);
				}
			}
			$values[$fieldName] = $fieldModel;
		}
		$this->structuredValues = $values;
		return $values;
	}

}
