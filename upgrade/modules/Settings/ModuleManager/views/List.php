<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_ModuleManager_List_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$viewer->assign('ALL_MODULES', Settings_ModuleManager_Module_Model::getAll());
		$viewer->assign('RESTRICTED_MODULES_LIST', Settings_ModuleManager_Module_Model::getActionsRestrictedModulesList());
		$viewer->assign('IMPORT_MODULE_URL', Settings_ModuleManager_Module_Model::getNewModuleImportUrl());
		$viewer->assign('EXTENSION_LOADER', function_exists('_vtextnld'));
		$viewer->assign('IMPORT_EXTENSION_STORE_URL', Settings_ModuleManager_Module_Model::getExtensionStoreUrl());
		$viewer->assign('IMPORT_USER_MODULE_FROM_FILE_URL', Settings_ModuleManager_Module_Model::getUserModuleFileImportUrl());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		echo $viewer->view('ListContents.tpl', $qualifiedModuleName, true);
	}
}
