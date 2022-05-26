<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_TaxIndex_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('showChargesAndItsTaxes');
		$this->exposeMethod('showTaxRegions');
	}

    public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$taxRecordModel = new Inventory_TaxRecord_Model();
		$productAndServicesTaxList = Inventory_TaxRecord_Model::getProductTaxes();
		$qualifiedModuleName = $request->getModule(false);

        $viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
		$viewer->assign('PRODUCT_AND_SERVICES_TAXES', $productAndServicesTaxList);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TaxIndex.tpl', $qualifiedModuleName);
    }

	public function showChargesAndItsTaxes(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$taxRecordModel = new Inventory_TaxRecord_Model();
		$charges = Inventory_Charges_Model::getInventoryCharges();
		$chargeTaxes = Inventory_TaxRecord_Model::getChargeTaxes();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
		$viewer->assign('CHARGE_MODELS_LIST', $charges);
		$viewer->assign('CHARGE_TAXES', $chargeTaxes);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ChargesAndItsTaxes.tpl', $qualifiedModuleName);
	}

	public function showTaxRegions(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$taxRegions = Inventory_TaxRegion_Model::getAllTaxRegions();

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_REGIONS', $taxRegions);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('TaxRegions.tpl', $qualifiedModuleName);
	}

	function getPageTitle(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return vtranslate('LBL_TAX_CALCULATIONS',$qualifiedModuleName);
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.$moduleName.resources.Tax"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}