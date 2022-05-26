<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_RelationAjax_Action extends Vtiger_RelationAjax_Action {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('addListPrice');
		$this->exposeMethod('updateShowBundles');
		$this->exposeMethod('updateQuantity');
		$this->exposeMethod('changeBundleCost');
	}
	
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 */
	function addRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$qtysList = $request->get('quantities');
		if (!is_array($qtysList)) {
			$qtysList = array();
		}

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId, $relatedRecordId, array('quantities' => $qtysList));
			if($relatedModule == 'PriceBooks'){
				$recordModel = Vtiger_Record_Model::getInstanceById($relatedRecordId);
				if ($sourceRecordId && ($sourceModule === 'Products' || $sourceModule === 'Services')) {
					$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
					$recordModel->updateListPrice($sourceRecordId, $parentRecordModel->get('unit_price'));
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
	
	/**
	 * Function adds Products/Services-PriceBooks Relation
	 * @param type $request
	 */
	function addListPrice($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule =  $request->get('related_module');
		$relInfos = $request->get('relinfo');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relInfos as $relInfo) {
			$price = CurrencyField::convertToDBFormat($relInfo['price'], null, true);
			$relationModel->addListPrice($sourceRecordId, $relInfo['id'], $price);
		}
	}

	public function updateShowBundles(Vtiger_Request $request) {
		$sourceModule = $request->getModule();
		$recordId = $request->get('record');
		$relatedModule = $request->get('relatedModule');
		$value = $request->get('value');
		$tabLabel = $request->get('tabLabel');

		if ($relatedModule === 'Products' && $tabLabel === 'Product Bundles') {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
			$relationModel->updateShowBundlesOption($recordId, $value);
		}
	}

	public function updateQuantity(Vtiger_Request $request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule =  $request->get('related_module');
		$relatedRecordsInfo = $request->get('relatedRecords');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);

		foreach($relatedRecordsInfo as $relatedRecordId => $quantity) {
			$relationModel->updateQuantity($sourceRecordId, $relatedRecordId, $quantity);
		}
	}

	public function changeBundleCost(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$relatedModule = $request->get('relatedModule');
		$tabLabel = $request->get('tabLabel');
		$unitPrice = $request->get('unit_price');

		if ($moduleName === $relatedModule && $tabLabel === 'Product Bundles' && $unitPrice) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			$fieldModelList = $recordModel->getModule()->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				//For not converting created time and modified time to user format
				$uiType = $fieldModel->get('uitype');
				if ($uiType != 70) {
					$fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));
				}
				if ($fieldName === 'unit_price') {
					$fieldValue = $unitPrice;
				}

				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}

				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}

			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$currencyId = $currentUserModel->get('currency_id');
			$curNameId = 'curname'.$currencyId;
			$curCheckName = 'cur_'.$currencyId.'_check';

			//Getting $_REQUEST Values
			$currentRequest = array();
			$currentRequest['action']		= $_REQUEST['action'];
			$currentRequest[$curNameId]		= $_REQUEST[$curNameId];
			$currentRequest['unit_price']	= $_REQUEST['unit_price'];
			$currentRequest[$curCheckName]	= $_REQUEST[$curCheckName];
			$currentRequest['base_currency']= $_REQUEST['base_currency'];

			//Setting $_REQUEST Values
			$_REQUEST['action']			= 'CurrencyUpdate';
			$_REQUEST[$curNameId]		= $unitPrice;
			$_REQUEST['unit_price']		= $unitPrice;
			$_REQUEST[$curCheckName]	= 1;
			$_REQUEST['base_currency']	= $curNameId;

			$recordModel->save();

			//Reverting $_REQUEST Values
			$_REQUEST['action']			= $currentRequest['action'];
			$_REQUEST[$curNameId]		= $currentRequest[$curNameId];
			$_REQUEST['unit_price']		= $currentRequest['unit_price'];
			$_REQUEST[$curCheckName]	= $currentRequest[ $curCheckName];
			$_REQUEST['base_currency']	= $currentRequest['base_currency'];
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

}
?>
