<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_Field_Model extends Vtiger_Field_Model {

	static function getPicklistValueByField($fieldName) {
		$picklistValues = false;
		if ($fieldName == 'reporttype') {
			$picklistValues = array(
				'tabular'	=> vtranslate('tabular', 'Reports'),
				'chart'		=> vtranslate('chart', 'Reports')
			);
		} else if ($fieldName == 'foldername') {
			$allFolders = Reports_Folder_Model::getAll();
			foreach ($allFolders as $folder) {
				$picklistValues[$folder->get('folderid')] = vtranslate($folder->get('foldername'), 'Reports');
			}
		} else if ($fieldName == 'owner') {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$allUsers = $currentUserModel->getAccessibleUsers();
			foreach ($allUsers as $userId => $userName) {
				$picklistValues[$userId] = $userName;
			}
		} else if ($fieldName == 'primarymodule') {
            $reportModel = Reports_Record_Model::getCleanInstance();
            $picklistValues = $reportModel->getModulesList();
        }

		return $picklistValues;
	}

	static function getFieldInfoByField($fieldName) {
		$fieldInfo = array(
			'mandatory' => false,
			'presence' => true,
			'quickcreate' => false,
			'masseditable' => false,
			'defaultvalue' => false,
		);
		if ($fieldName == 'reportname') {
			$fieldInfo['type'] = 'string';
			$fieldInfo['name'] = $fieldName;
			$fieldInfo['label'] = 'Report Name';
		} else if ($fieldName == 'description') {
			$fieldInfo['type'] = 'string';
			$fieldInfo['name'] = $fieldName;
			$fieldInfo['label'] = 'Description';
		} else if ($fieldName == 'reporttype') {
			$fieldInfo['type'] = 'picklist';
			$fieldInfo['name'] = $fieldName;
			$fieldInfo['label'] = 'Report Type';
			$fieldInfo['picklistvalues'] = self::getPicklistValueByField($fieldName);
		} else if ($fieldName == 'foldername') {
			$fieldInfo['type'] = 'picklist';
			$fieldInfo['name'] = $fieldName;
			$fieldInfo['label'] = 'LBL_FOLDER_NAME';
			$fieldInfo['picklistvalues'] = self::getPicklistValueByField($fieldName);
		} else {
			$fieldInfo = false;
		}

		return $fieldInfo;
	}

	static function getListViewFieldsInfo() {
		$fields = array('reporttype', 'reportname', 'foldername', 'description');
		$fieldsInfo = array();
		foreach($fields as $field) {
			$fieldsInfo[$field] = Reports_Field_Model::getFieldInfoByField($field);
		}
		return Zend_Json::encode($fieldsInfo);
	}
}

?>
