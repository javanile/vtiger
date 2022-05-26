<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_List_View extends Settings_Vtiger_Index_View {

	public function preProcess (Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);
		$qualifiedModuleName = $request->getModule(false);
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		$viewer = $this->getViewer($request);
		$viewer->assign('LISTVIEW_LINKS', $listViewModel->getListViewLinks());
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Vtiger_Request $request) {
		return parent::preProcessTplName($request);
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$scannerId = $request->get('record');
		if ($scannerId == '') {
			$scannerId = Settings_MailConverter_Module_Model::getDefaultId();
		}
		$qualifiedModuleName = $request->getModule(false);
		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);
		$recordExists = Settings_MailConverter_Module_Model::MailBoxExists();
		$recordModel = Settings_MailConverter_Record_Model::getAll();
		$viewer = $this->getViewer($request);

		$viewer->assign('LISTVIEW_LINKS', $listViewModel->getListViewLinks());
		$viewer->assign('MODULE_MODEL', Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName));
		$viewer->assign('MAILBOXES', Settings_MailConverter_Module_Model::getMailboxes());
	
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('CRON_RECORD_MODEL', Settings_CronTasks_Record_Model::getInstanceByName('MailScanner'));
		$viewer->assign('RECORD_EXISTS', $recordExists);
	
		if ($scannerId) {
			$viewer->assign('SCANNER_ID', $scannerId);
			$viewer->assign('RECORD', $recordModel[$scannerId]);
			$viewer->assign('SCANNER_MODEL', Settings_MailConverter_Record_Model::getInstanceById($scannerId));
			$viewer->assign('RULE_MODELS_LIST', Settings_MailConverter_RuleRecord_Model::getAll($scannerId));
			$viewer->assign('FOLDERS_SCANNED', Settings_MailConverter_Module_Model::getScannedFolders($scannerId));
		}
		$viewer->view('RulesList.tpl', $qualifiedModuleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.List"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
