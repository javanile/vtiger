<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_RecipientPreference_Model extends Vtiger_Record_Model {

	protected static $table = 'vtiger_emails_recipientprefs';
	protected static $index = 'tabid';
	protected static $index2 = 'userid';
	protected static $columns = array('id', 'tabid', 'prefs','userid');
	protected static $instanceCache = array();

	public static function getInstance($moduleName) {
		if(!isset(self::$instanceCache[$moduleName])) {
			$db = PearDatabase::getInstance();
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$sql = 'SELECT * FROM ' . self::$table . ' WHERE ' . self::$index . ' =? AND '.self::$index2.'=?';
			$result = $db->pquery($sql, array(getTabid($moduleName),$currentUserModel->getId()));
			if ($db->num_rows($result) > 0) {
				$instance = new self();
				$columns = self::$columns;
				foreach ($columns as $column) {
					$value = $db->query_result($result, 0, $column);

					if ($column == 'prefs') {
						$value = Zend_Json::decode(html_entity_decode($value));
					}
					$instance->set($column, $value);
				}
				self::$instanceCache[$moduleName] = $instance;
			} else {
				return null;
			}
		}
		return self::$instanceCache[$moduleName];
	}

	public function save() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if ($this->getId()) {
			$sql = 'UPDATE ' . self::$table . ' SET prefs = ? WHERE id = ? AND '.self::$index.'=? AND '.self::$index2.'=?';
			$params = array(Zend_Json::encode($this->get('prefs')), $this->getId(), $this->get(self::$index),  $this->get(self::$index2));
			$db->pquery($sql, $params);
			return true;
		} else {
			$sql = 'INSERT INTO ' . self::$table . ' ('.self::$index.',prefs,'.self::$index2.') VALUES (?,?,?)';
			$params = array($this->get(self::$index), Zend_Json::encode($this->get('prefs')),  $currentUserModel->getId());
			$db->pquery($sql, $params);
			return true;
		}
	}

	public function delete() {
		$db = PearDatabase::getInstance();
		$sql = 'DELETE FROM ' . self::$table . ' WHERE id = ?';
		$params = array($this->getId());
		$db->pquery($sql, $params);
		return true;
	}

	public function getPreferences() {
		return $this->get('prefs');
	}

	public function getSourceModule() {
		return getTabModuleName($this->get(self::$index));
	}
	
	public function setSourceModule($moduleName){
		$this->set(self::$index,  getTabid($moduleName));
	}
	
}
