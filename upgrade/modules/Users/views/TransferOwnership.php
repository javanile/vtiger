<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_TransferOwnership_View extends Vtiger_Index_View {
    
    public function checkPermission(Vtiger_Request $request){
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(!$currentUserModel->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}
    
    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $usersList = Users_Record_Model::getActiveAdminUsers();
        $activeAdminId = Users::getActiveAdminId();
        unset($usersList[$activeAdminId]);
        $viewer = $this->getViewer($request);
        $viewer->assign('USERS_MODEL', $usersList);
        $viewer->assign('MODULE', $moduleName);
        $viewer->view('TransferOwnership.tpl', $moduleName);
    }
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}