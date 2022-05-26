<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_TaxAjax_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('checkDuplicateTaxName');
		$this->exposeMethod('checkDuplicateInventoryCharge');
		$this->exposeMethod('checkDuplicateTaxRegion');
		$this->exposeMethod('saveTax');
		$this->exposeMethod('updateTaxStatus');
		$this->exposeMethod('saveTaxRegion');
		$this->exposeMethod('deleteTaxRegion');
		$this->exposeMethod('saveCharge');
		$this->exposeMethod('deleteCharge');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->saveTax($request);
	}

	public function saveTax(Vtiger_Request $request) {
		$taxId = $request->get('taxid');
		$type = $request->get('type');
		if (empty($taxId)) {
			$taxRecordModel = new Inventory_TaxRecord_Model();
		} else {
			$taxRecordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
		}

		$fields = array('taxlabel'=> '', 'percentage' => 0, 'deleted' => 0, 'method' => '', 'compoundon' => array(), 'regions' => array(), 'taxType' => '');
		foreach($fields as $fieldName => $defaultValue) {

			if($request->has($fieldName)) {
				$fieldValue = $request->get($fieldName);
				if ($fieldName == 'compoundon' && !is_array($fieldValue)) {
					$fieldValue = array($fieldValue);
				}

				if ($fieldName == 'regions') {
					foreach ($fieldValue as $key => $regionsInfo) {
						if (!is_array($regionsInfo['list'])) {
							$regionsInfo['list'] = array($regionsInfo['list']);
						}
						$fieldValue[$key] = $regionsInfo;
					}
				}

			} else {
				$fieldValue = $defaultValue;
			}
			$taxRecordModel->set($fieldName, $fieldValue);

		}
		$taxRecordModel->setType($type);
		$taxRecordModel->set('type', $taxRecordModel->get('taxType'));

		$taxMethod = $request->get('method');
		if ($taxMethod && in_array($taxMethod, array('Simple', 'Deducted'))) {
			$taxRecordModel->set('compoundon', array());
			if ($taxMethod === 'Deducted') {
				$taxRecordModel->set('regions', array());
				$taxRecordModel->set('type', 'Fixed');
			}
		}

		$taxType = $request->get('taxType');
		if ($taxType && $taxType === 'Fixed') {
			$taxRecordModel->set('regions', array());
		} else {
			if($request->has('defaultPercentage')) {
				$taxRecordModel->set('percentage', $request->get('defaultPercentage'));
			}
		}

		$taxRecordModel->set('taxlabel', html_entity_decode($taxRecordModel->get('taxlabel')));
		$response = new Vtiger_Response();
		try {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$taxId = $taxRecordModel->save();

			$recordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
			$response->setResult(array_merge($recordModel->getData(), array('taxlabel'=> decode_html($recordModel->getName())), array('_editurl' => $recordModel->getEditTaxUrl(), 'type' => $recordModel->getType(), 'row_type' => $currentUser->get('rowheight'))));

		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function updateTaxStatus(Vtiger_Request $request) {
		$taxId = $request->get('taxid');
		$type = $request->get('type');

		$taxRecordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
		$taxRecordModel->set('deleted', $request->get('deleted'));
		$response = new Vtiger_Response();
		try {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$taxId = $taxRecordModel->updateTaxStatus();

			$recordModel = Inventory_TaxRecord_Model::getInstanceById($taxId, $type);
			$response->setResult(array_merge($recordModel->getData(), array('_editurl' => $recordModel->getEditTaxUrl(), 'type' => $recordModel->getType(), 'row_type' => $currentUser->get('rowheight'))));
		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function saveTaxRegion(Vtiger_Request $request) {
		$taxRegionId = $request->get('taxRegionId');
		$taxRegionModel = Inventory_TaxRegion_Model::getRegionModel($taxRegionId);
		$taxRegionModel->set('name', html_entity_decode($request->get('name')));

		$response = new Vtiger_Response();
		try {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$taxRegionId = $taxRegionModel->save();

			$taxRegionModel = Inventory_TaxRegion_Model::getRegionModel($taxRegionId);
			$response->setResult(array_merge(array('regionid' => $taxRegionId, 'name' => decode_html($taxRegionModel->getName())),
					array('_editurl' => $taxRegionModel->getEditRegionUrl(), '_deleteurl' => $taxRegionModel->getDeleteRegionUrl(), 'row_type' => $currentUser->get('rowheight'))));

		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function deleteTaxRegion(Vtiger_Request $request) {
		$taxRegionId = $request->get('taxRegionId');
		$response = new Vtiger_Response();
		if ($taxRegionId) {
			try {
				Inventory_TaxRegion_Model::deleteRegions(array($taxRegionId));
				$response->setResult(array());
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		} else {
			$response->setError();
		}
		$response->emit();
	}

	public function saveCharge(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$chargeId = $request->get('chargeid');
		if (empty($chargeId)) {
			$chargeModel = new Inventory_Charges_Model();
		} else {
			$chargeModel = Inventory_Charges_Model::getChargeModel($chargeId);
		}

		$fields = array('name'=> '', 'format' => '', 'type' => 'Fixed', 'value' => '', 'istaxable'=> 1, 'regions' => array(), 'taxes' => array());
		foreach($fields as $fieldName => $defaultValue) {

			if($request->has($fieldName)) {
				$fieldValue = $request->get($fieldName);

				if ($fieldName == 'taxes' && !is_array($fieldValue)) {
					$fieldValue = array($fieldValue);
				}

				if ($fieldName == 'regions') {
					foreach ($fieldValue as $key => $regionsInfo) {
						if (!is_array($regionsInfo['list'])) {
							$regionsInfo['list'] = array($regionsInfo['list']);
						}
						$fieldValue[$key] = $regionsInfo;
					}
				}

			} else if($chargeId) {
				$fieldValue = $chargeModel->get($fieldName);
				if (in_array($fieldName, array('compoundon', 'regions'))) {
					$fieldValue = Zend_Json::decode(html_entity_decode($fieldValue));
				}
			} else {
				$fieldValue = $defaultValue;
			}
			$chargeModel->set($fieldName, $fieldValue);

		}

		$type = $request->get('type');
		if ($type && $type === 'Fixed') {
			$chargeModel->set('regions', array());
		} else {
			$chargeModel->set('value', $request->get('defaultValue'));
		}

		$isTaxable = $request->get('istaxable');
		if (!$isTaxable) {
			$chargeModel->set('taxes', array());
		}

		$chargeModel->set('name', html_entity_decode($chargeModel->get('name')));
		$response = new Vtiger_Response();
		try {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$chargeId = $chargeModel->save();

			$recordModel = Inventory_Charges_Model::getChargeModel($chargeId);
			$recordData = $recordModel->getData();

			$selectedTaxes = '';
			foreach ($recordModel->getSelectedTaxes() as $taxId => $taxModel) {
				$selectedTaxes .= decode_html($taxModel->getName()).', ';
			}
			$recordData['selectedTaxes']= trim($selectedTaxes, ', ');
			$recordData['name']			= decode_html($recordModel->getName());
			$recordData['value']		= $recordModel->getDisplayValue();
			$recordData['isTaxable']	= $recordModel->isTaxable() ? vtranslate('LBL_YES', $qualifiedModuleName) : vtranslate('LBL_NO', $qualifiedModuleName);
			$recordData['_editurl']		= $recordModel->getEditChargeUrl();
			$recordData['_deleteurl']	= $recordModel->getDeleteChargeUrl();
			$recordData['row_type']		= $currentUser->get('rowheight');
			$response->setResult($recordData);

		} catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}

	public function deleteCharge(Vtiger_Request $request) {
		$chargeId = $request->get('chargeId');
		$response = new Vtiger_Response();
		if ($chargeId) {
			try {
				Inventory_Charges_Model::deleteCharges(array($chargeId));
				$response->setResult(array());
			} catch (Exception $e) {
				$response->setError($e->getCode(), $e->getMessage());
			}
		} else {
			$response->setError();
		}
		$response->emit();
	}

	public function checkDuplicateTaxName(Vtiger_Request $request) {
		$exists = Inventory_TaxRecord_Model::checkDuplicate(trim($request->get('taxlabel')), $request->get('taxid'), $request->get('type'));
		if (!$exists) {
			$result = array('success' => false);
		} else {
			$result = array('success' => true, 'message' => vtranslate('LBL_TAX_NAME_EXIST', $request->getModule(false)));
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function checkDuplicateInventoryCharge(Vtiger_Request $request) {
		$exists = Inventory_Charges_Model::checkDuplicateInventoryCharge(trim($request->get('name')), $request->get('chargeid'));
		if (!$exists) {
			$result = array('success' => false);
		} else {
			$result = array('success' => true, 'message' => vtranslate('LBL_CHARGE_NAME_EXIST', $request->getModule(false)));
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function checkDuplicateTaxRegion(Vtiger_Request $request) {
		$exists = Inventory_TaxRegion_Model::checkDuplicateTaxRegion(trim($request->get('taxRegionName')), $request->get('taxRegionId'));
		if (!$exists) {
			$result = array('success' => false);
		} else {
			$result = array('success' => true, 'message' => vtranslate('LBL_TAX_REGION_EXIST', $request->getModule(false)));
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

}