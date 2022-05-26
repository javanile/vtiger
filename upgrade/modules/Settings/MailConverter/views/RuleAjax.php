<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MailConverter_RuleAjax_View extends Settings_Vtiger_IndexAjax_View {
	
	public function process(Vtiger_Request $request) {
		$scannerId = $request->get('scannerId');
		$ruleId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);

		$viewer->assign('SCANNER_ID', $scannerId);
		$viewer->assign('SCANNER_MODEL', Settings_MailConverter_Record_Model::getInstanceById($scannerId));
		$viewer->assign('RULE_MODEL', Settings_MailConverter_RuleRecord_Model::getRule($scannerId,$ruleId));
		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$fields = $moduleModel->getSetupRuleFields();

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('FIELDS', $fields);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

		$viewer->view('Rule.tpl', $qualifiedModuleName);
	}
}
?>
