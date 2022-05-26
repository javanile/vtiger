<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_Edit_View extends Settings_Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('step1');
		$this->exposeMethod('step2');
		$this->exposeMethod('step3');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function preProcess(Vtiger_Request $request) {
		parent::preProcess($request);
		$recordId = $request->get('record');
		$mode = $request->get('mode');
		if (!$mode)
			$mode = "step1";
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		if ($recordId) {
			$recordModel = Settings_MailConverter_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = Settings_MailConverter_Record_Model::getCleanInstance();
		}
		$viewer = $this->getViewer($request);

		if ($recordId) {
			$viewer->assign('RECORD_ID', $recordId);
		}
		$viewer->assign('CREATE', $request->get('create'));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('MODULE_MODEL', $recordModel->getModule());
		$viewer->assign('STEP', $mode);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('EditHeader.tpl', $qualifiedModuleName);
	}

	public function step1(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}

	public function step2(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$folders = Settings_MailConverter_Module_Model::getFolders($recordId);

		$viewer = $this->getViewer($request);
		if (is_array($folders)) {
			$viewer->assign('FOLDERS', $folders);
		} else if ($folders) {
			$viewer->assign('IMAP_ERROR', $folders);
		} else {
			$viewer->assign('CONNECTION_ERROR', true);
		}
		$viewer->view('Step2.tpl', $qualifiedModuleName);
	}

	public function step3(Vtiger_Request $request) {
		$scannerId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Settings_MailConverter_RuleRecord_Model::getCleanInstance($scannerId);
		$qualifiedModuleName = $request->getModule(false);
		global $current_user;
		$currentUserId = $current_user->id;
		$viewer = $this->getViewer($request);

		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('DEFAULT_MATCH', "AND");
		$viewer->assign('MODULE_MODEL', new Settings_MailConverter_Module_Model());

		$viewer->assign('SCANNER_ID', $scannerId);
		$viewer->assign('SCANNER_MODEL', Settings_MailConverter_Record_Model::getInstanceById($scannerId));

		$viewer->assign('DEFAULT_OPTIONS', Settings_MailConverter_RuleRecord_Model::getDefaultConditions());
		$viewer->assign('DEFAULT_ACTIONS', Settings_MailConverter_RuleRecord_Model::getDefaultActions());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ASSIGNED_USER', $currentUserId);

		$viewer->view('Step3.tpl', $qualifiedModuleName);
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'modules.Settings.MailConverter.resources.Edit'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}
