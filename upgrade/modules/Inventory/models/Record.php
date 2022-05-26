<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Inventory_Record_Model extends Vtiger_Record_Model {

	function getCurrencyInfo() {
		$moduleName = $this->getModuleName();
		$currencyInfo = getInventoryCurrencyInfo($moduleName, $this->getId());
		return $currencyInfo;
	}

	function getProductTaxes() {
		$taxDetails = $this->get('taxDetails');
		if ($taxDetails) {
			return $taxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$taxDetails = $relatedProducts[1]['final_details']['taxes'];
		} else {
			$taxDetailsFromDB = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
			$taxDetails = array();
			foreach ($taxDetailsFromDB as $key => $taxInfo) {
				$taxInfo['regions'] = Zend_Json::decode(html_entity_decode($taxInfo['regions']));
				$taxInfo['compoundon'] = Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
				$taxDetails[$taxInfo['taxid']] = $taxInfo;
			}
		}

		foreach ($taxDetails as $key => $taxInfo) {
			if ($taxInfo['method'] === 'Deducted') {
				unset($taxDetails[$key]);
			}
		}

		$this->set('taxDetails', $taxDetails);
		return $taxDetails;
	}

	function getShippingTaxes() {
		$shippingTaxDetails = $this->get('shippingTaxDetails');
		if ($shippingTaxDetails) {
			return $shippingTaxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$shippingTaxDetails = $relatedProducts[1]['final_details']['sh_taxes'];
		} else {
			$shippingTaxDetails = getAllTaxes('available', 'sh', 'edit', $this->getId());
		}

		$this->set('shippingTaxDetails', $shippingTaxDetails);
		return $shippingTaxDetails;
	}

	function getProducts() {
		$numOfCurrencyDecimalPlaces = getCurrencyDecimalPlaces();
		$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$productsCount = count($relatedProducts);

		//Updating Tax details
		$taxtype = $relatedProducts[1]['final_details']['taxtype'];
		$productIdsList = array();
		for ($i=1;$i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];
			$productId = $product['hdnProductId'.$i];
			$totalAfterDiscount = $product['totalAfterDiscount'.$i];

			if ($taxtype == 'individual') {
				$taxDetails = getTaxDetailsForProduct($productId, 'all');
				$taxCount = count($taxDetails);
				$taxTotal = '0';

				for($j=0; $j<$taxCount; $j++) {
					$taxValue = $product['taxes'][$j]['percentage'];

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
					$taxTotal = $taxTotal + $taxAmount;

					$product['taxes'][$j]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxes'][$j]['amount'] = $taxAmount;
				}

				$productTaxes = array();
				if ($product['taxes']) {
					foreach ($product['taxes'] as $key => $taxInfo) {
						$taxInfo['key'] = $key;
						$productTaxes[$taxInfo['taxid']] = $taxInfo;
					}
				}

				$taxTotal = 0.00;
				foreach ($productTaxes as $taxId => $taxInfo) {
					$taxAmount = $taxInfo['amount'];
					if ($taxInfo['compoundon']) {
						$amount = $totalAfterDiscount;
						foreach ($taxInfo['compoundon'] as $compTaxId) {
							$amount = $amount + $productTaxes[$compTaxId]['amount'];
						}
						$taxAmount = $amount * $taxInfo['percentage'] / 100;
					}
					$taxTotal = $taxTotal + $taxAmount;

					$relatedProducts[$i]['taxes'][$taxInfo['key']]['amount'] = $taxAmount;
					$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
				}
				$netPrice = $totalAfterDiscount + $taxTotal;
				$relatedProducts[$i]['netPrice'.$i] = number_format($netPrice, $numOfCurrencyDecimalPlaces, '.', '');
			}

			if ($relatedProducts[$i]['entityType'.$i] == 'Products') {
				$productIdsList[] = $productId;
			}
		}

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						+ (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $numOfCurrencyDecimalPlaces,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal'] - (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $numOfCurrencyDecimalPlaces,'.','');
		$relatedProducts[1]['final_details']['discount_amount_final'] = number_format((float)$relatedProducts[1]['final_details']['discount_amount_final'], $numOfCurrencyDecimalPlaces,'.','');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $this->getCharges();
		if (!$selectedChargesAndItsTaxes) {
			$selectedChargesAndItsTaxes = array();
		}
		$relatedProducts[1]['final_details']['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0.00;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float)$amount * (float)$chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float)$calculatedOn * (float)$taxPercent) / 100;
			}
		}
		$relatedProducts[1]['final_details']['shtax_totalamount'] = number_format($totalAmount, $numOfCurrencyDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$totalAfterDiscount = (float) $relatedProducts[1]['final_details']['totalAfterDiscount'];
		$deductedTaxesTotalAmount = 0.00;

		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float)$taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $numOfCurrencyDecimalPlaces,'.','');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}

		$relatedProducts[1]['final_details']['deductTaxes'] = $deductTaxes;
		$relatedProducts[1]['final_details']['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $numOfCurrencyDecimalPlaces,'.','');

		if ($productIdsList) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i=1; $i<=$productsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product['hdnProductId'.$i];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]['productImage'.$i] = $imageDetails[0]['path'].'_'.$imageDetails[0]['orgname'];
				}
			}
		}

		return $relatedProducts;
	}

	/**
	 * Function to set record module field values
	 * @param parent record model
	 * @return <Model> returns Vtiger_Record_Model
	 */
	function setRecordFieldValues($parentRecordModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$fieldsList = array_keys($this->getModule()->getFields());
		$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());

		$commonFields = array_intersect($fieldsList, $parentFieldsList);
		foreach ($commonFields as $fieldName) {
			if (getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), $fieldName) == 0) {
				$this->set($fieldName, $parentRecordModel->get($fieldName));
			}
		}
		if($this->getModuleName() == 'PurchaseOrder' && getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), 'account_id') == 0) {
			$this->set('accountid',$parentRecordModel->get('account_id'));
		}
		return $this;
	}

	/**
	 * Function to get inventoy terms and conditions
	 * @return <String>
	 */
	function getInventoryTermsAndConditions() {
		return getTermsAndConditions($this->getModuleName());
	}

	/**
	 * Function to set data of parent record model to this record
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return Inventory_Record_Model
	 */
	public function setParentRecordData(Vtiger_Record_Model $parentRecordModel) {
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $parentRecordModel->getModuleName();

		$data = array();
		$fieldMappingList = $parentRecordModel->getInventoryMappingFields();

		foreach ($fieldMappingList as $fieldMapping) {
			$parentField = $fieldMapping['parentField'];
			$inventoryField = $fieldMapping['inventoryField'];
			$fieldModel = Vtiger_Field_Model::getInstance($parentField, Vtiger_Module_Model::getInstance($moduleName));
			if ($fieldModel && $fieldModel->getPermissions()) {
				$data[$inventoryField] = $parentRecordModel->get($parentField);
			} else {
				$data[$inventoryField] = $fieldMapping['defaultValue'];
			}
		}
		return $this->setData($data);
	}

	/**
	 * Function to get URL for Export the record as PDF
	 * @return <type>
	 */
	public function getExportPDFUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ExportPDF&record=".$this->getId();
	}

	/**
	  * Function to get the send email pdf url
	  * @return <string>
	  */
	public function getSendEmailPDFUrl() {
		return 'module='.$this->getModuleName().'&view=SendEmail&mode=composeMailData&record='.$this->getId();
	}

	/**
	 * Function to get this record and details as PDF
	 */
	public function getPDF() {
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_". $moduleName ."PDFController";

		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$fileName = $moduleName.'_'.getModuleSequenceNumber($moduleName, $recordId);
		$controller->Output($fileName.'.pdf', 'D');
	}

	/**
	 * Function to get the pdf file name . This will conver the invoice in to pdf and saves the file
	 * @return <String>
	 *
	 */
	public function getPDFFileName() {
		$moduleName = $this->getModuleName();
		if ($moduleName == 'Quotes') {
			vimport("~~/modules/$moduleName/QuotePDFController.php");
			$controllerClassName = "Vtiger_QuotePDFController";
		} else {
			vimport("~~/modules/$moduleName/$moduleName" . "PDFController.php");
			$controllerClassName = "Vtiger_" . $moduleName . "PDFController";
		}

		$recordId = $this->getId();
		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$sequenceNo = getModuleSequenceNumber($moduleName,$recordId);
		$translatedName = vtranslate($moduleName, $moduleName);
		$filePath = "storage/$translatedName"."_".$sequenceNo.".pdf";
		//added file name to make it work in IE, also forces the download giving the user the option to save
		$controller->Output($filePath,'F');
		return $filePath;
	}

	/**
	 * Function to get related line items of parent record
	 * @param <Vtiger_Record_Model> $parentRecordModel
	 * @return <Array>
	 */
	public function getParentRecordRelatedLineItems($parentRecordModel) {
		$userCurrencyInfo = Vtiger_Util_Helper::getUserCurrencyInfo();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyId = $currentUserModel->get('currency_id');
		$numOfCurrencyDecimals = $currentUserModel->get('no_of_currency_decimals');

		$moduleName = $this->getModuleName();
		$productDetails = getAssociatedProducts($parentRecordModel->getModuleName(), $parentRecordModel->getEntity(), $parentRecordModel->getId(), $this->getModuleName());

		$productIdsList = array();
		foreach ($productDetails as $key => $lineItemDetail) {
			$productId	= $lineItemDetail['hdnProductId'.$key];
			$entityType = $lineItemDetail['entityType'.$key];
			$productIdsList[$entityType][] = $productId;
		}

		//Getting list price value of each product in user currency
		$convertedPriceDetails = array();
		foreach ($productIdsList as $entityType => $productIds) {
			$convertedPriceDetails[$entityType] = getPricesForProducts($currencyId, $productIds, $entityType);
		}

		//Getting image details of each product
		$imageDetailsList = array();
		if ($productIdsList['Products']) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList['Products']);
		}

		foreach ($productDetails as $key => $lineItemDetail) {
			$productId = $lineItemDetail['hdnProductId'.$key];
			$entityType = $lineItemDetail['entityType'.$key];

			//updating list price details
			$productDetails[$key]['listPrice'.$key] = number_format((float)$convertedPriceDetails[$entityType][$productId], $numOfCurrencyDecimals, '.', '');

			//updating cost price details
			$purchaseCost = (float)$userCurrencyInfo['conversion_rate'] * (float)$lineItemDetail['purchaseCost'.$key];
			$productDetails[$key]['purchaseCost'.$key] = number_format($purchaseCost, $numOfCurrencyDecimals, '.', '');

			if($moduleName === 'PurchaseOrder') {
				$productDetails[$key]['listPrice'.$key] = number_format((float)$purchaseCost, $numOfCurrencyDecimals,'.','');
			}

			//Image detail
			if ($imageDetailsList[$productId]) {
				$imageDetails = $imageDetailsList[$productId];
				$productDetails[$key]['productImage'.$key] = $imageDetails[0]['path'].'_'.$imageDetails[0]['orgname'];
			}
		}
		return $productDetails;
	}

	/**
	 * Function to get charges
	 * @return <Array>
	 */
	public function getCharges() {
		if (!$this->chargesAndItsTaxes) {
			$this->chargesAndItsTaxes = array();
			$recordId = $this->getId();
			if ($recordId) {
				$db = PearDatabase::getInstance();
				$result = $db->pquery('SELECT * FROM vtiger_inventorychargesrel WHERE recordid = ?', array($recordId));
				while ($rowData = $db->fetch_array($result)) {
					$this->chargesAndItsTaxes = Zend_Json::decode(html_entity_decode($rowData['charges']));
				}
			}
		}
		return $this->chargesAndItsTaxes;
	}

	/**
	 * Function to get deduct taxes
	 * @return <Array>
	 */
	public function getDeductTaxes() {
		$deductTaxes = $this->get('deductTaxes');
		if ($deductTaxes) {
			return $deductTaxes;
		}

		$deductTaxes = Inventory_TaxRecord_Model::getDeductTaxesList($active = false);
		$record = $this->getId();
		if ($record && $deductTaxes) {
			$db = PearDatabase::getInstance();
			$deductTaxNamesList = array();
			foreach ($deductTaxes as $taxId => $taxInfo) {
				$deductTaxNamesList[] = $taxInfo['taxname'];
			}

			$result = $db->pquery('SELECT '.implode(',', $deductTaxNamesList).' FROM vtiger_inventoryproductrel WHERE id = ?', array($record));
			foreach ($deductTaxes as $taxId => $taxInfo) {
				$percent = $db->query_result($result, 0, $taxInfo['taxname']);
				if ($percent !== NULL && $percent < 0) {
					$deductTaxes[$taxId]['selected']	= true;
					$deductTaxes[$taxId]['percentage']	= -$percent;
				}
			}
		}

		$this->set('deductTaxes', $deductTaxes);
		return $deductTaxes;
	}

	public function getProductsForPurchaseOrder() {
		$relatedProducts = $this->getProducts();

		$productsCount = count($relatedProducts);
		for ($i = 1; $i <= $productsCount; $i++) {
			$relatedProducts[$i]['discountTotal'.$i] = 0;
			$relatedProducts[$i]['discount_percent'.$i] = 0;
			$relatedProducts[$i]['discount_amount'.$i]=0;
			$relatedProducts[$i]['checked_discount_zero'.$i] = 'checked';
			$relatedProducts[$i]['listPrice'.$i] = $relatedProducts[$i]['purchaseCost'.$i] / $relatedProducts[$i]['qty'.$i];
		}
		$relatedProducts[1]['final_details']['discount_percentage_final'] = 0;
		$relatedProducts[1]['final_details']['discount_amount_final'] = 0;
		$relatedProducts[1]['final_details']['discount_type_final'] = 'zero';
		return $relatedProducts;
	}

	/**
	 * Function to get regions list
	 * @return <Array>
	 */
	public function getRegionsList() {
		$recordId = $this->getId();
		$selectedRegionId = $this->get('region_id');

		//Constructing taxes for regions
		$taxesForRegions = array();
		$inventoryTaxes = Inventory_TaxRecord_Model::getProductTaxes();
		foreach ($inventoryTaxes as $taxId => $taxRecordModel) {
			if ($taxRecordModel->getTaxMethod() !== 'Deducted') {
				$taxInfo = array();
				$taxInfo['values']['default'] = $taxRecordModel->getTax();
				foreach ($taxRecordModel->getRegionTaxes() as $list) {
					if (is_array($list['list'])) {
						foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
							$taxInfo['values'][$key] = $value;
						}
					}
				}

				$taxInfo['compoundOn'] = $taxRecordModel->getTaxesOnCompound();
				$taxesForRegions[$taxId] = $taxInfo;
			}
		}

		//Constructing charges for regions
		$chargesForRegions = array();
		$charges = Inventory_Charges_Model::getInventoryCharges();
		foreach ($charges as $chargeId => $chargeModel) {
			$chargeInfo = array();
			$chargeInfo['values']['default'] = $chargeModel->getValue();
			foreach ($chargeModel->getSelectedRegions() as $list) {
				if (is_array($list['list'])) {
					foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
						$chargeInfo['values'][$key] = $value;
					}
				}
			}

			$chargeInfo['isPercent'] = ($chargeModel->get('format') === 'Percent') ? true : false;
			$chargeInfo['taxes'] = Zend_Json::decode(html_entity_decode($chargeModel->get('taxes')));
			$chargesForRegions[$chargeId] = $chargeInfo;
		}

		//Constructing charge taxes for regions
		$chargeTaxesForRegions = array();
		$chargeTaxes = Inventory_TaxRecord_Model::getChargeTaxes();
		foreach ($chargeTaxes as $taxId => $taxRecordModel) {
			$taxInfo = array();
			$taxInfo['values']['default'] = $taxRecordModel->getTax();
			foreach ($taxRecordModel->getRegionTaxes() as $list) {
				if (is_array($list['list'])) {
					foreach(array_fill_keys($list['list'], $list['value']) as $key => $value) {
						$taxInfo['values'][$key] = $value;
					}
				}
			}

			$taxInfo['compoundOn'] = $taxRecordModel->getTaxesOnCompound();
			$chargeTaxesForRegions[$taxId] = $taxInfo;
		}

		//Constructing Regions Info
		$allRegionsList = array();
		$taxes = $this->getProductTaxes();
		$selectedCharges = $this->getCharges();
		$conversionRateInfo = getCurrencySymbolandCRate($this->get('currency_id'));
		foreach ($selectedCharges as $chargeId => $chargeInfo) {
			$selectedCharges[$chargeId]['value'] = (float)$chargeInfo['value'] / (float)$conversionRateInfo['rate'];
		}

		foreach (Inventory_TaxRegion_Model::getAllTaxRegions() as $regionId => $regionModel) {
			$regionInfo['name'] = $regionModel->getName();

			foreach ($taxesForRegions as $taxId => $taxInfo) {
				$taxValue = $taxInfo['values']['default'];
				if (array_key_exists($regionId, $taxInfo['values'])) {
					$taxValue = $taxInfo['values'][$regionId];
				}

				if ($recordId && $selectedRegionId == $regionId) {
					$taxValue = $taxes[$taxId]['percentage'];
				}
				$regionInfo['taxes'][$taxId]['value'] = $taxValue;

				$compoundOn = $taxInfo['compoundOn'];
				if ($recordId) {
					$compoundOn = array();
					if ($taxes[$taxId]) {
						$compoundOn = $taxes[$taxId]['compoundon'];
					}
				}
				$regionInfo['taxes'][$taxId]['compoundOn'] = $compoundOn;
			}

			foreach ($chargesForRegions as $chargeId => $chargeInfo) {
				$updatedRegionInfo = array();
				$chargeValue = $chargeInfo['values']['default'];
				if (array_key_exists($regionId, $chargeInfo['values'])) {
					$chargeValue = $chargeInfo['values'][$regionId];
				}

				$checked = true;
				$key = ($chargeInfo['isPercent']) ? 'percent' : 'value';
				if ($recordId) {
					if ($selectedRegionId == $regionId) {
						$key = isset($selectedCharges[$chargeId]['percent']) ? 'percent' : 'value';
						$chargeValue = $selectedCharges[$chargeId][$key];
					}

					if (!$selectedCharges[$chargeId]) {
						$checked = false;
					}
				}
				$updatedRegionInfo[$key] = $chargeValue;
				$updatedRegionInfo['checked'] = $checked;

				if (is_array($chargeInfo['taxes'])) {
					foreach ($chargeInfo['taxes'] as $taxId) {
						$taxInfo = $chargeTaxesForRegions[$taxId];

						$taxValue = $taxInfo['values']['default'];
						if (array_key_exists($regionId, $taxInfo['values'])) {
							$taxValue = $taxInfo['values'][$regionId];
						}

						$taxChecked = $checked;
						if ($recordId) {
							if ($selectedCharges[$chargeId]['taxes'][$taxId]) {
								$taxChecked = true;
								if ($selectedRegionId == $regionId) {
									$taxValue = $selectedCharges[$chargeId]['taxes'][$taxId];
								}
							}
						}
						$updatedRegionInfo['taxes'][$taxId]['value']		= $taxValue;
						$updatedRegionInfo['taxes'][$taxId]['checked']		= $taxChecked;
						$updatedRegionInfo['taxes'][$taxId]['compoundOn']	= $taxInfo['compoundOn'];
					}
					$regionInfo['charges'][$chargeId] = $updatedRegionInfo;
				}
			}

			$allRegionsList[$regionId] = $regionInfo;
		}

		$defaultRegionInfo = array();
		foreach ($taxesForRegions as $taxId => $taxInfo) {
			$taxValue = $taxesForRegions[$taxId]['values']['default'];
			if (!$selectedRegionId) {
				$taxValue = $taxes[$taxId]['percentage'];
			}
			$defaultRegionInfo['taxes'][$taxId]['value'] = $taxValue;

			$compoundOn = $taxInfo['compoundOn'];
			if ($recordId) {
				$compoundOn = array();
				if ($taxes[$taxId]) {
					$compoundOn = $taxes[$taxId]['compoundon'];
				}
			}
			$defaultRegionInfo['taxes'][$taxId]['compoundOn'] = $compoundOn;
		}

		foreach ($chargesForRegions as $chargeId => $chargeInfo) {
			$key = ($chargeInfo['isPercent']) ? 'percent' : 'value';
			$chargeValue = $chargeInfo['values']['default'];

			$checked = true;
			if ($recordId) {
				if (!$selectedRegionId) {
					$key = isset($selectedCharges[$chargeId]['percent']) ? 'percent' : 'value';
					$chargeValue = $selectedCharges[$chargeId][$key];
					if (!$chargeValue) {
						$chargeValue = 0;
					}
				}

				if (!$selectedCharges[$chargeId]) {
					$checked = false;
				}
			}
			$defaultRegionInfo['charges'][$chargeId][$key] = $chargeValue;
			$defaultRegionInfo['charges'][$chargeId]['checked'] = $checked;

			if (is_array($chargeInfo['taxes'])) {
				foreach ($chargeInfo['taxes'] as $taxId) {
					$taxInfo = $chargeTaxesForRegions[$taxId];
					$taxValue = $taxInfo['values']['default'];

					$taxChecked = $checked;
					if ($recordId) {
						if ($selectedCharges[$chargeId]['taxes'][$taxId]) {
							$taxChecked = true;
							if (!$selectedRegionId) {
								$taxValue = $selectedCharges[$chargeId]['taxes'][$taxId];
							}
						}
					}

					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['value']		= $taxValue;
					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['checked']	= $taxChecked;
					$defaultRegionInfo['charges'][$chargeId]['taxes'][$taxId]['compoundOn'] = $taxInfo['compoundOn'];
				}
			}
		}

		$allRegionsList[0] = $defaultRegionInfo;
		return $allRegionsList;
	}

	/**
	 * Function to get charge tax models list
	 * @param Integer $chargeId
	 * @return Array
	 */
	public function getChargeTaxModelsList($chargeId) {
		if ($chargeId) {
			$chargeTaxModelsList = array();
			$chargesAndItsTaxes = $this->getCharges();
			$chargeInfo = $chargesAndItsTaxes[$chargeId];
			if ($chargeInfo && $chargeInfo['taxes']) {
				$taxes = array_keys($chargeInfo['taxes']);
				foreach ($taxes as $taxId) {
					$chargeTaxModelsList[$taxId] = Inventory_TaxRecord_Model::getInstanceById($taxId, Inventory_TaxRecord_Model::SHIPPING_AND_HANDLING_TAX);
				}
			}

			$chargeModel = Inventory_Charges_Model::getChargeModel($chargeId);
			$selectedChargeTaxes = $chargeModel->getSelectedTaxes();
			foreach ($selectedChargeTaxes as $taxId => $taxRecordModel) {
				$chargeTaxModelsList[$taxId] = $taxRecordModel;
			}
			return $chargeTaxModelsList;
		}
		return array();
	}

	public function convertRequestToProducts(Vtiger_Request $request) {
		$requestData = $request->getAll();
		$noOfDecimalPlaces = getCurrencyDecimalPlaces();
		$totalProductsCount = $requestData['totalProductCount'];

		$productIdsList = array();
		$relatedProducts = array();
		for ($i=1; $i<=$totalProductsCount; $i++) {
			$productId = $requestData["hdnProductId$i"];
			$productIdsList[] = $productId;
			$itemRecordModel = Vtiger_Record_Model::getInstanceById($productId);

			$productData = array();
			$productData["hdnProductId$i"]	= $productId;
			$productData["productName$i"]	= $itemRecordModel->getName();
			$productData["comment$i"]		= $requestData["comment$i"];
			$productData["qtyInStock$i"]	= $itemRecordModel->get('qtyinstock');
			$productData["qty$i"]			= $requestData["qty$i"];
			$productData["listPrice$i"]		= number_format($requestData["listPrice$i"], $noOfDecimalPlaces, '.', '');
			$productData["unitPrice$i"]		= number_format($requestData["listPrice$i"], $noOfDecimalPlaces, '.', '');
			$productData["purchaseCost$i"]	= number_format($purchaseCost, $noOfDecimalPlaces, '.', '');
			$productData["productDescription$i"]= $requestData["productDescription$i"];

			$margin = (float)$requestData["margin$i"];
			if (is_numeric($margin)) {
				$productData["margin$i"] = number_format($margin, $noOfDecimalPlaces, '.', '');
			}

			$productTotal = $requestData["qty$i"] * $requestData["listPrice$i"];
			$productData["productTotal$i"]	= number_format($productTotal, $noOfDecimalPlaces, '.', '');

			$subQtysList = array();
			$subProducts = $requestData["subproduct_ids$i"];
			$subProducts = split(',', rtrim($subProducts, ','));

			foreach ($subProducts as $subProductInfo) {
				 list($subProductId, $subProductQty) = explode(':', $subProductInfo);
				 if ($subProductId) {
					 $subProductName = getProductName($subProductId);
					 $subQtysList[$subProductId] = array('name' => $subProductName, 'qty' => $subProductQty);
				 }
			}
			$productData["subproduct_ids$i"]= $requestData["subproduct_ids$i"];
			$productData["subprod_qty_list$i"]	= $subQtysList;

			//individual disount calculation
			$discountType = $productData["discount_type$i"] = $requestData["discount_type$i"];
			$productData["discount_percent$i"]	= 0;
			$productData["discount_amount$i"]	= 0;
			$discountTotal = 0;

			if ($discountType === 'percentage') {
				$productData["discount_percent$i"] = $requestData["discount_percentage$i"];
				$productData["checked_discount_percent$i"] = 'checked';
				$discountTotal = $productTotal * $productData["discount_percent$i"] / 100;
			} elseif ($discountType === 'amount') {
				$productData["discount_amount$i"] = $requestData["discount_amount$i"];
				$productData["checked_discount_amount$i"] = 'checked';
				$discountTotal = $productData["discount_amount$i"];
			} else {
				$productData["checked_discount_zero$i"] = 'checked';
			}
			$productData["discountTotal$i"]		= number_format($discountTotal, $noOfDecimalPlaces, '.', '');

			//individual taxes calculation
			$taxType = $requestData['taxtype'];
			$itemTaxDetails = $itemRecordModel->getTaxClassDetails();	
			$regionsList = array();
			foreach ($itemTaxDetails as $taxInfo) {
				$regionsInfo = array('default' => $taxInfo['percentage']);
				if ($taxInfo['productregions']) {
					foreach ($taxInfo['productregions'] as $list) {
						if (is_array($list['list'])) {
							foreach (array_fill_keys($list['list'], $list['value']) as $key => $value) {
								$regionsInfo[$key] = $value;
							}
						}
					}
				}
				$regionsList[$taxInfo['taxid']] = $regionsInfo;
			}

			$taxTotal = 0;
			$totalAfterDiscount = $productTotal-$discountTotal;
			$netPrice = $totalAfterDiscount;
			$taxDetails = array();

			foreach ($itemTaxDetails as &$taxInfo) {
				$taxId = $taxInfo['taxid'];
				$taxName = $taxInfo['taxname'];
				$taxValue = 0;
				$taxAmount = 0;

				$taxValue = $taxInfo['percentage'];
				if ($taxType == 'individual') {
					$selectedRegionId = $requestData['region_id'];
					$taxValue = $requestData[$taxName.'_percentage'.$i];
					if ($selectedRegionId) {
						$regionsList[$taxId][$selectedRegionId] = $taxValue;
					} else {
						$regionsList[$taxId]['default'] = $taxValue;
					}

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
				}

				$taxInfo['amount']		= $taxAmount;
				$taxInfo['percentage']	= $taxValue;
				$taxInfo['regionsList']	= $regionsList[$taxInfo['taxid']];
				$taxDetails[$taxId] = $taxInfo;
			}

			$taxTotal = 0;
			foreach ($taxDetails as $taxId => $taxInfo) {
				$taxAmount = $taxInfo['amount'];
				if ($taxInfo['compoundon']) {
					$amount = $totalAfterDiscount;
					foreach ($taxInfo['compoundon'] as $compTaxId) {
						$amount = $amount + $taxDetails[$compTaxId]['amount'];
					}
					$taxAmount = $amount * $taxInfo['percentage'] / 100;
				}
				$taxTotal = $taxTotal + $taxAmount;

				$taxDetails[$taxId]['amount'] = $taxAmount;
				$relatedProducts[$i]['taxTotal'.$i]	= number_format($taxTotal, $numOfCurrencyDecimalPlaces, '.', '');
			}

			$productData["taxTotal$i"]			= number_format($taxTotal, $noOfDecimalPlaces, '.', '');
			$productData["totalAfterDiscount$i"]= number_format($totalAfterDiscount, $noOfDecimalPlaces, '.', '');
			$productData["netPrice$i"]			= number_format($totalAfterDiscount + $taxTotal, $noOfDecimalPlaces, '.', '');

			$productData['taxes'] = $taxDetails;
			$relatedProducts[$i] = $productData;
		}

		//Final details started
		$finalDetails = array();
		$finalDetails['hdnSubTotal'] = number_format($requestData['subtotal'], $noOfDecimalPlaces, '.', '');

		//final discount calculation
		$discountTotalFinal = 0;
		$finalDiscountType = $finalDetails['discount_type_final'] = $requestData['discount_type_final'];
		if ($finalDiscountType === 'percentage') {
			$finalDetails['discount_percentage_final'] = $requestData['discount_percentage_final'];
			$finalDetails['checked_discount_percentage_final'] = 'checked';
			$discountTotalFinal = $finalDetails['discount_percentage_final'];
		} else if ($finalDetails === 'amount') {
			$finalDetails['discount_percentage_final'] = $requestData['discount_amount_final'];
			$finalDetails['checked_discount_amount_final'] = 'checked';
			$discountTotalFinal = $finalDetails['discount_percentage_final'];
		}
		$finalDetails['discountTotal_final'] = number_format($discountTotalFinal, $noOfDecimalPlaces, '.', '');

		//group taxes calculation
		$taxDetails = array();
		$taxTotal = 0;
		$allTaxes = getAllTaxes('available');
		foreach ($allTaxes as $taxInfo) {
			if ($taxInfo['method'] === 'Deducted') {
				continue;
			}

			$taxName = $taxInfo['taxname'];
			if ($taxType == 'group') {
				$taxPercent = $requestData[$taxName.'_group_percentage'];
			} else {
				$taxPercent = $taxInfo['percentage'];
			}
			if ($taxPercent == '' || $taxPercent == 'NULL') {
				$taxPercent = 0;
			}

			$taxInfo['percentage']	= $taxPercent;
			$taxInfo['amount']		= $requestData[$taxName.'_group_amount'];;
			$taxInfo['regions']		= Zend_Json::decode(html_entity_decode($taxInfo['regions']));
			$taxInfo['compoundon']	= Zend_Json::decode(html_entity_decode($taxInfo['compoundon']));
			$taxDetails[$taxInfo['taxid']] = $taxInfo;

			$taxTotal = $taxTotal + $taxInfo['amount'];
		}

		$finalDetails['taxtype']		= $taxType;
		$finalDetails['taxes']			= $taxDetails;
		$finalDetails['tax_totalamount']= number_format($taxTotal, $noOfDecimalPlaces, '.', '');
		$finalDetails['adjustment']		= number_format($requestData['adjustment'], $noOfDecimalPlaces, '.', '');
		$finalDetails['grandTotal']		= number_format($requestData['total'], $noOfDecimalPlaces, '.', '');
		$finalDetails['preTaxTotal']	= number_format($requestData['pre_tax_total'], $noOfDecimalPlaces, '.', '');
		$finalDetails['shipping_handling_charge'] = number_format($requestData['shipping_handling_charge'], $noOfDecimalPlaces, ',', '');
		$finalDetails['adjustment']		= $requestData['adjustmentType'].number_format($requestData['adjustment'], $noOfDecimalPlaces, '.', '');

		//charge value setting to related products array
		$selectedChargesAndItsTaxes = $requestData['charges'];
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			$selectedChargesAndItsTaxes[$chargeId] = Zend_Json::decode(html_entity_decode($chargeInfo));
		}
		$finalDetails['chargesAndItsTaxes'] = $selectedChargesAndItsTaxes;

		$allChargeTaxes = array();
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			if (is_array($chargeInfo['taxes'])) {
				$allChargeTaxes = array_merge($allChargeTaxes, array_keys($chargeInfo['taxes']));
			} else {
				$selectedChargesAndItsTaxes[$chargeId]['taxes'] = array();
			}
		}

		$shippingTaxes = array();
		$allShippingTaxes = getAllTaxes('all', 'sh');
		foreach ($allShippingTaxes as $shTaxInfo) {
			$shippingTaxes[$shTaxInfo['taxid']] = $shTaxInfo;
		}

		$totalAmount = 0;
		foreach ($selectedChargesAndItsTaxes as $chargeId => $chargeInfo) {
			foreach ($chargeInfo['taxes'] as $taxId => $taxPercent) {
				$amount = $calculatedOn = $chargeInfo['value'];

				if ($shippingTaxes[$taxId]['method'] === 'Compound') {
					$compoundTaxes = Zend_Json::decode(html_entity_decode($shippingTaxes[$taxId]['compoundon']));
					if (is_array($compoundTaxes)) {
						foreach ($compoundTaxes as $comTaxId) {
							if ($shippingTaxes[$comTaxId]) {
								$calculatedOn += ((float) $amount * (float) $chargeInfo['taxes'][$comTaxId]) / 100;
							}
						}
					}
				}
				$totalAmount += ((float) $calculatedOn * (float) $taxPercent) / 100;
			}
		}
		$finalDetails['shtax_totalamount'] = number_format($totalAmount, $noOfDecimalPlaces, '.', '');

		//deduct tax values setting to related products
		$deductedTaxesTotalAmount = 0;
		$deductTaxes = $this->getDeductTaxes();
		foreach ($deductTaxes as $taxId => $taxInfo) {
			$taxAmount = ($totalAfterDiscount * (float) $taxInfo['percentage']) / 100;
			$deductTaxes[$taxId]['amount'] = number_format($taxAmount, $noOfDecimalPlaces, '.', '');
			if ($taxInfo['selected']) {
				$deductedTaxesTotalAmount = $deductedTaxesTotalAmount + $taxAmount;
			}
		}
		$finalDetails['deductTaxes'] = $deductTaxes;
		$finalDetails['deductTaxesTotalAmount'] = number_format($deductedTaxesTotalAmount, $noOfDecimalPlaces, '.', '');

		$imageFieldModel = $this->getModule()->getField('image');
		if ($productIdsList && $imageFieldModel && $imageFieldModel->isViewable()) {
			$imageDetailsList = Products_Record_Model::getProductsImageDetails($productIdsList);

			for ($i = 1; $i <= $totalProductsCount; $i++) {
				$product = $relatedProducts[$i];
				$productId = $product["hdnProductId$i"];
				$imageDetails = $imageDetailsList[$productId];
				if ($imageDetails) {
					$relatedProducts[$i]["productImage$i"] = $imageDetails[0]['path'] . '_' . $imageDetails[0]['orgname'];
				}
			}
		}

		if ($relatedProducts[1]) {
			$relatedProducts[1]['final_details'] = $finalDetails;
		}
		return $relatedProducts;
	}

}
