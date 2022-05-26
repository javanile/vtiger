<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Migration_Module_Model extends Vtiger_Module_Model {
	
	public function getDBVersion(){
		$db = PearDatabase::getInstance();
		
		$result = $db->pquery('SELECT current_version FROM vtiger_version', array());
		if($db->num_rows($result) > 0){
			$currentVersion = $db->query_result($result, 0, 'current_version');
		}
		return $currentVersion;
	}
	
	public static function getInstance() {
		return new self();
	}
	
	public function getAllowedMigrationVersions(){
		$versions = array(
			array('540'   => '5.4.0'),
			array('600RC' => '6.0.0 RC'),
			array('600' => '6.0.0'),
			array('610' => '6.1.0'),
			array('620' => '6.2.0'),
			array('630' => '6.3.0'),
			array('640' => '6.4.0'),
			array('650' => '6.5.0'),
			array('660' => '6.6.0'),
			array('700' => '7.0.0'),
			array('701' => '7.0.1'),
			array('710' => '7.1.0'),
		);
		return $versions;
	}
	
	public function getLatestSourceVersion(){
		return vglobal('vtiger_current_version');
	}
	
	/**
	 * Function to update the latest vtiger version in db
	 * @return type
	 */
	public function updateVtigerVersion(){
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_version SET current_version=?,old_version=?', array($this->getLatestSourceVersion(), $this->getDBVersion()));
		return true;
	}
	
	/**
	 * Function to rename the migration file and folder
	 * Writing tab data in flat file
	 */
	public function postMigrateActivities(){
		//Writing tab data in flat file
		perform_post_migration_activities();
		
		//rename the migration file and folder
		$renamefile = uniqid(rand(), true);
				
		if(!@rename("migrate/", $renamefile."migrate/")) {
			if (@copy ("migrate/", $renamefile."migrate/")) {
				@unlink("migrate/");
			} 
		}
	}
}
