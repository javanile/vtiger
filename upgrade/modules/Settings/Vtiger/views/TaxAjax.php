<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_TaxAjax_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('editTax');
		$this->exposeMethod('editTaxRegion');
		$this->exposeMethod('editCharge');
	}

    public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function editTax(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxId = $request->get('taxid');
		$type = $request->get('type');

		if(empty($taxId)) {
			$taxRecordModel = new Inventory_TaxRecord_Model();
		} else {
			$taxRecordModel = Inventory_TaxRecord_Model::getInstanceById($taxId,$type);
		}

		$viewer->assign('TAX_TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_RECORD_MODEL', $taxRecordModel);
		$viewer->assign('SIMPLE_TAX_MODELS_LIST', Inventory_TaxRecord_Model::getSimpleTaxesList($taxId, $type));
		$viewer->assign('TAX_REGIONS', Inventory_TaxRegion_Model::getAllTaxRegions());
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

		echo $viewer->view('EditTax.tpl', $qualifiedModuleName, true);
    }

	public function editTaxRegion(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$taxRegionId = $request->get('taxRegionId');
		$taxRegionRecordModel = Inventory_TaxRegion_Model::getRegionModel($taxRegionId);

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TAX_REGION_MODEL', $taxRegionRecordModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

		echo $viewer->view('EditRegion.tpl', $qualifiedModuleName, true);
    }

	public function editCharge(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$chargeId = $request->get('chargeId');
		$inventoryChargeModel = Inventory_Charges_Model::getChargeModel($chargeId);

		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('CHARGE_MODEL', $inventoryChargeModel);
		$viewer->assign('TAX_REGIONS', Inventory_TaxRegion_Model::getAllTaxRegions());
		$viewer->assign('CHARGE_TAXES', Inventory_TaxRecord_Model::getChargeTaxes());
		$viewer->assign('SELECTED_TAXES', array_keys($inventoryChargeModel->getSelectedTaxes()));
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

		echo $viewer->view('EditCharge.tpl', $qualifiedModuleName, true);
	}

}