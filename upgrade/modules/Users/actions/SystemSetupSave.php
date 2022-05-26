<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetupSave_Action extends Users_Save_Action {
    
        function checkPermission(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser() && !$currentUser->isAccountOwner()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}
	
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$packages = $request->get(packages);
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$userModuleModel::savePackagesInfo($packages);
		header ('Location: index.php?module=Users&parent=Settings&view=UserSetup');
		exit();
	}
}