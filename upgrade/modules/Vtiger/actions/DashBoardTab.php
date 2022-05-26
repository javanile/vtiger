<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_DashBoardTab_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('addTab');
		$this->exposeMethod('deleteTab');
		$this->exposeMethod('renameTab');
		$this->exposeMethod('updateTabSequence');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function to add Dashboard Tab
	 * @param Vtiger_Request $request
	 */
	function addTab(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$tabName = $request->getRaw('tabName');

		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$tabExist = $dashBoardModel->checkTabExist($tabName);
		$tabLimitExceeded = $dashBoardModel->checkTabsLimitExceeded();
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);

		if ($tabLimitExceeded) {
			$response->setError(100, vtranslate('LBL_TABS_LIMIT_EXCEEDED', $moduleName));
		} else if ($tabExist) {
			$response->setError(100, vtranslate('LBL_DASHBOARD_TAB_ALREADY_EXIST', $moduleName));
		} else {
			$tabData = $dashBoardModel->addTab($tabName);
			$response->setResult($tabData);
		}
		$response->emit();
	}

	/**
	 * Function to delete Dashboard Tab
	 * @param Vtiger_Request $request
	 */
	function deleteTab(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$tabId = $request->get('tabid');
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$result = $dashBoardModel->deleteTab($tabId);
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		if ($result) {
			$response->setResult($result);
		} else {
			$response->setError(100, 'Failed To Delete Tab');
		}
		$response->emit();
	}

	/**
	 * Funtion to rename Dashboard Tab
	 * @param Vtiger_Request $request
	 */
	function renameTab(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$tabName = $request->get('tabname');
		$tabId = $request->get('tabid');
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$result = $dashBoardModel->renameTab($tabId, $tabName);
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		if ($result) {
			$response->setResult($result);
		} else {
			$response->setError(100, 'Failed To rename Tab');
		}
		$response->emit();
	}

	function updateTabSequence(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$sequence = $request->get("sequence");
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$result = $dashBoardModel->updateTabSequence($sequence);
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		if ($result) {
			$response->setResult($result);
		} else {
			$response->setError(100, 'Failed To rearrange Tabs');
		}
		$response->emit();
	}
}
