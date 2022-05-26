<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_Logout_Action extends Vtiger_Action_Controller {
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		//Redirect into the referer page
		$logoutURL = $this->getLogoutURL();
        session_regenerate_id(true);
		Vtiger_Session::destroy();
		
		//Track the logout History
		$moduleName = $request->getModule();
		$moduleModel = Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		//End

		if(!empty($logoutURL)) {
			header('Location: '.$logoutURL);
			exit();
		} else {
			header ('Location: index.php');
		}
	}
	
	protected function getLogoutURL() {
		$logoutUrl = Vtiger_Session::get('LOGOUT_URL');
		if (isset($logoutUrl) && !empty($logoutUrl)) {
			return $logoutUrl;
		}
		return VtigerConfig::getOD('LOGIN_URL');
	}
}