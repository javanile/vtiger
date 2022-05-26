<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Products_Detail_View extends Vtiger_Detail_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('showBundleTotalCostView');
	}
	
	function preProcess(Vtiger_Request $request, $display = true) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		
		parent::preProcess($request, $display);
	}

	public function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
		$viewer->assign('TAXCLASS_DETAILS', $recordModel->getTaxClassDetails());
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}

	public function showModuleBasicView(Vtiger_Request $request) {
		return $this->showModuleDetailView($request);
	}
	
	public function getOverlayHeaderScripts(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.'.$moduleName.'.resources.Detail';
		$jsFileNames = array(
			'~libraries/jquery/boxslider/jquery.bxslider.min.js',
			'modules.PriceBooks.resources.Detail',
		);
		$jsFileNames[] = $moduleDetailFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;	
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
		$moduleDetailFile = 'modules.'.$moduleName.'.resources.Detail';
		$moduleRelatedListFile = 'modules.'.$moduleName.'.resources.RelatedList';
		unset($headerScriptInstances[$moduleDetailFile]);
		unset($headerScriptInstances[$moduleRelatedListFile]);

		$jsFileNames = array(
			'~libraries/jquery/jquery.cycle.min.js',
			'~libraries/jquery/boxslider/jquery.bxslider.min.js', 
			'modules.PriceBooks.resources.Detail',
			'modules.PriceBooks.resources.RelatedList',
		);
		
		$jsFileNames[] = $moduleDetailFile;
		$jsFileNames[] = $moduleRelatedListFile;

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	public function showBundleTotalCostView(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentRecordId = $request->get('record');
		$tabLabel = $request->get('tabLabel');

		if ($moduleName === $relatedModuleName && $tabLabel === 'Product Bundles') {//Products && Child Products
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $moduleName);
			$parentModuleModel = $parentRecordModel->getModule();
			$parentRecordModel->set('currency_id', getProductBaseCurrency($parentRecordId, $parentModuleModel->getName()));

			$subProductsCostsInfo = $parentRecordModel->getSubProductsCostsAndTotalCostInUserCurrency();
			$subProductsTotalCost = $subProductsCostsInfo['subProductsTotalCost'];
			$subProductsCostsInfo = $subProductsCostsInfo['subProductsCosts'];

			$viewer = $this->getViewer($request);
			$viewer->assign('MODULE', $moduleName);
			$viewer->assign('TAB_LABEL', $tabLabel);
			$viewer->assign('PARENT_RECORD', $parentRecordModel);
			$viewer->assign('SUB_PRODUCTS_TOTAL_COST', $subProductsTotalCost);
			$viewer->assign('SUB_PRODUCTS_COSTS_INFO', $subProductsCostsInfo);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

			return $viewer->view('BundleCostView.tpl', $moduleName, 'true');
		}
	}
}
