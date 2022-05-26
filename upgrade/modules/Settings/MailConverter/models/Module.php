<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_Module_Model extends Settings_Vtiger_Module_Model {

	var $name = 'MailConverter';

	/**
	 * Function to get Create record url
	 * @return <String> Url
	 */
	public function getCreateRecordUrl() {
		return 'index.php?module=MailConverter&parent=Settings&view=Edit&mode=step1&create=new';
	}

	/**
	 * Function to get List of fields for mail converter record
	 * @return <Array> List of fields
	 */
	public function getFields() {
		$fields =  array(
			'scannername'	=> array('name' => 'scannername',	'typeofdata' => 'V~M',	'label' => 'Scanner Name',	'datatype' => 'string'),
			'server'		=> array('name' => 'server',		'typeofdata' => 'V~M',	'label' => 'Server',		'datatype' => 'string'),
			'username'		=> array('name' => 'username',		'typeofdata' => 'V~M',	'label' => 'User Name',		'datatype' => 'string'),
			'password'		=> array('name' => 'password',		'typeofdata' => 'V~M',	'label' => 'Password',		'datatype' => 'password'),
			'protocol'		=> array('name' => 'protocol',		'typeofdata' => 'C~O',	'label' => 'Protocol',		'datatype' => 'radio'),
			'ssltype'		=> array('name' => 'ssltype',		'typeofdata' => 'C~O',	'label' => 'SSL Type',		'datatype' => 'radio'),
			'sslmethod'		=> array('name' => 'sslmethod',		'typeofdata' => 'C~O',	'label' => 'SSL Method',	'datatype' => 'radio'),
			'connecturl'	=> array('name' => 'connecturl',	'typeofdata' => 'V~O',	'label' => 'Connect URL',	'datatype' => 'string', 'isEditable' => false),
			'searchfor'		=> array('name' => 'searchfor',		'typeofdata' => 'V~O',	'label' => 'Look For',		'datatype' => 'picklist'),
			'markas'		=> array('name' => 'markas',		'typeofdata' => 'V~O',	'label' => 'After Scan',	'datatype' => 'picklist'),
			'isvalid'		=> array('name' => 'isvalid',		'typeofdata' => 'C~O',	'label' => 'Status',		'datatype' => 'boolean'),
			'time_zone'		=> array('name' => 'time_zone',		'typeofdata' => 'V~O',	'label' => 'Time Zone',		'datatype' => 'picklist'));

		$fieldsList = array();
		foreach($fields as $fieldName => $fieldInfo) {
			$fieldModel = new Settings_MailConverter_Field_Model();
			foreach($fieldInfo as $key=>$value) {
				$fieldModel->set($key, $value);
			}
			$fieldsList[$fieldName] = $fieldModel;
		}
		return $fieldsList;
	}

	/**
	 * Function to get the field of setup Rules
	 *  @return <Array> List of setup rule fields
	 */

	public function getSetupRuleFields() {
		$ruleFields = array(
			'fromaddress'	=> array('name' => 'fromaddress',	'label' => 'LBL_FROM',		'datatype' => 'email'),
			'toaddress'		=> array('name' => 'toaddress',		'label' => 'LBL_TO',		'datatype' => 'email'),
			'cc'			=> array('name' => 'cc',			'label' => 'LBL_CC',		'datatype' => 'email'),
			'bcc'			=> array('name' => 'bcc',			'label' => 'LBL_BCC',		'datatype' => 'email'),
			'subject'		=> array('name' => 'subject',		'label' => 'LBL_SUBJECT',	'datatype' => 'picklist'),
			'body'			=> array('name' => 'body',			'label' => 'LBL_BODY',		'datatype' => 'picklist'),
			'matchusing'	=> array('name' => 'matchusing',	'label' => 'LBL_MATCH',		'datatype' => 'radio'),
			'action'		=> array('name' => 'action',		'label' => 'LBL_ACTION',	'datatype' => 'picklist')
		);
		$ruleFieldsList = array();
		foreach($ruleFields as $fieldName => $fieldInfo) {
			$fieldModel = new Settings_MailConverter_RuleField_Model();
			foreach($fieldInfo as $key=>$value) {
				$fieldModel->set($key, $value);
			}
			$ruleFieldsList[$fieldName] = $fieldModel;
		}
		return $ruleFieldsList;
	}

	/**
	 * Function to get Default url for this module
	 * @return <String> Url
	 */
	public function getDefaultUrl() {
		return 'index.php?module='.$this->getName().'&parent='.$this->getParentName().'&view=List';
	}

	public function isPagingSupported() {
		return false;
	}

	public function MailBoxExists() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT COUNT(*) AS count FROM vtiger_mailscanner", array());
		$response = $db->query_result($result, 0, 'count');
		if ($response == 0)
			return false;
		return true;
	}

	public function getDefaultId() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT MIN(scannerid) AS id FROM vtiger_mailscanner", array());
		$id = $db->query_result($result, 0, 'id');
		return $id;
	}

	public function getMailboxes() {
		$mailBox = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT scannerid, scannername FROM vtiger_mailscanner", array());
		$numOfRows = $db->num_rows($result);
		for ($i = 0; $i < $numOfRows; $i++) {
			$mailBox[$i]['scannerid'] = $db->query_result($result, $i, 'scannerid');
			$mailBox[$i]['scannername'] = $db->query_result($result, $i, 'scannername');
		}
		return $mailBox;
	}

	public function getScannedFolders($id) {
		$folders = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT foldername FROM vtiger_mailscanner_folders WHERE scannerid=? AND enabled=1", array($id));
		$numOfRows = $db->num_rows($result);
		for ($i = 0; $i < $numOfRows; $i++) {
			$folders[$i] = $db->query_result($result, $i, 'foldername');
		}
		return $folders;
	}

	public function getFolders($id) {
		include_once 'modules/Settings/MailConverter/handlers/MailScannerInfo.php';
		include_once 'modules/Settings/MailConverter/handlers/MailBox.php';
		$scannerName = Settings_MailConverter_Module_Model::getScannerName($id);
		$scannerInfo = new Vtiger_MailScannerInfo($scannerName);
		$mailBox = new Vtiger_MailBox($scannerInfo);
		$isConnected = $mailBox->connect();
		if($isConnected) {
			$allFolders = $mailBox->getFolders();
			$folders = array();
			$selectedFolders = Settings_MailConverter_Module_Model::getScannedFolders($id);
			if(is_array($allFolders)) {
				foreach ($allFolders as $a) {
						if (in_array($a, $selectedFolders)) {
							$folders[$a] = 'checked';
						} else {
							$folders[$a] = '';
						}
				}
				return $folders;
			} else {
				return $allFolders;
			}

		}
		return false;
	}

	public function getScannerName($id) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT scannername FROM vtiger_mailscanner WHERE scannerid=?", array($id));
		$scannerName = $db->query_result($result, 0, 'scannername');
		return $scannerName;
	}

	public function updateFolders($scannerId, $folders) {
		include_once 'modules/Settings/MailConverter/handlers/MailScannerInfo.php';
		$db = PearDatabase::getInstance();
		$scannerName = Settings_MailConverter_Module_Model::getScannerName($scannerId);
		$scannerInfo = new Vtiger_MailScannerInfo($scannerName);
		$lastScan = $scannerInfo->dateBasedOnMailServerTimezone('d-M-Y');
		$db->pquery("DELETE FROM vtiger_mailscanner_folders WHERE scannerid=?", array($scannerId));
		foreach ($folders as $folder) {
			$db->pquery("INSERT INTO vtiger_mailscanner_folders VALUES(?,?,?,?,?,?)", array('', $scannerId, $folder, $lastScan, '0', '1'));
		}
	}

	public function hasCreatePermissions() {
		$permissions = false;
		$recordsCount = Settings_MailConverter_Record_Model::getCount();

		global $max_mailboxes;
		if ($recordsCount < $max_mailboxes) {
			$permissions = true;
		}
		return $permissions;
	}

}
