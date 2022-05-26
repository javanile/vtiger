<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Products_ProductsPopup_View extends Vtiger_Popup_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$qtyPerUnitField = $moduleModel->getField('qty_per_unit');

		if (!$qtyPerUnitField || ($qtyPerUnitField && !$qtyPerUnitField->isEditable())) {
			return parent::process($request);
		}

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();

		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('COMPANY_LOGO', $companyLogo);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('ProductsPopup.tpl', $moduleName);
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$qtyPerUnitField = $moduleModel->getField('qty_per_unit');

		if (!$qtyPerUnitField || ($qtyPerUnitField && !$qtyPerUnitField->isEditable())) {
			$_REQUEST['multi_select'] = true;
			$request->set('multi_select', true);
			return $headerScriptInstances;
		}

		$jsFileNames = array(
			"modules.$moduleName.resources.ProductRelatedProductBundles",
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.$moduleName.resources.validator.FieldValidator"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		parent::initializeListViewContents($request, $viewer);
		$sourceModule = $request->get('src_module');
		if ($sourceModule && in_array($sourceModule, getInventoryModules())) {
			$viewer->assign('GETURL', 'getTaxesURL');
		}
	}
}