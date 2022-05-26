<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'include/Webservices/VtigerModuleOperation.php';
require_once 'include/Webservices/Utils.php';

class VtigerProductOperation extends VtigerModuleOperation {

	private static $currencyWsId = null;

	public function create($elementType, $element) {
		$element = $this->sanitizeTaxes($element);
		$element = $this->sanitizeCurrency($element, 'Create');
		$element = parent::create($elementType, $element);
		$createdId = $element['id'];
		$currencyTaxElement = $this->retrieveTaxesAndCurrency($createdId, $elementType);
		return array_merge($element, $currencyTaxElement);
	}

	public function update($element) {
		$element = $this->sanitizeTaxes($element);
		$element = $this->sanitizeCurrency($element);
		$element = parent::update($element);
		$entityName = $this->meta->getObjectEntityName($element['id']);
		$updatedId = $element['id'];
		$currencyTaxElement = $this->retrieveTaxesAndCurrency($updatedId, $entityName);
		return array_merge($element, $currencyTaxElement);
	}

	public function revise($element) {
		$element = $this->sanitizeTaxes($element);
		$element = $this->sanitizeCurrency($element);
		$element = parent::revise($element);
		$entityName = $this->meta->getObjectEntityName($element['id']);
		$revisedId = $element['id'];
		$currencyTaxElement = $this->retrieveTaxesAndCurrency($revisedId, $entityName);
		return array_merge($element, $currencyTaxElement);
	}

	public function retrieve($wsId) {
		$element = parent::retrieve($wsId);
		$entityName = $this->meta->getObjectEntityName($element['id']);
		$currencyTaxElement = $this->retrieveTaxesAndCurrency($wsId, $entityName);
		return array_merge($element, $currencyTaxElement);
	}

	public function describe($elementType) {
		$describe = parent::describe($elementType);
		$taxes = array();
		$taxes = getAllTaxes('available');
		foreach ($taxes as $index => $taxInfo) {
			$taxField = array();
			$taxField['name'] = $taxInfo['taxname'];
			$taxField['label'] = $taxInfo['taxlabel'];
			$taxField['type'] = array('name' => 'double');
			$taxField['nullable'] = '1';
			$taxField['editable'] = '1';
			$taxField['mandatory'] = '';
			$taxField['default'] = $taxInfo['percentage'];
			$describe['fields'][] = $taxField;
		}
		$currency = array();
		$currencyDetails = getPriceDetailsForProduct('', '', 'available', $elementType);

		foreach ($currencyDetails as $currencyDetail) {
			$currencyId = $currencyDetail['curid'];
			$currency['name'] = "currency$currencyId";
			$currency['label'] = $currencyDetail['currencylabel']." ".$currencyDetail['currencycode'];
			$currency['type'] = array('name' => 'double');
			$currency['nullable'] = '1';
			$currency['editable'] = '1';
			$currency['mandatory'] = '';
			$currency['default'] = '';
			$describe['fields'][] = $currency;
		}
		return $describe;
	}

	protected function sanitizeTaxes($element) {
		$taxes = getAllTaxes('available');

		foreach ($taxes as $taxInfo) {
			$taxName = $taxInfo['taxname'];
			unset($_REQUEST[$taxName.'_check']);
			unset($_REQUEST[$taxName]);

			if (array_key_exists($taxName, $element) && $element[$taxName] !== '') {
				$_REQUEST[$taxName.'_check'] = 1;
				$_REQUEST[$taxName] = $element[$taxName];
			}
		}
		return $element;
	}

	protected function sanitizeCurrency($element, $mode = false) {
		$currencies = getAllCurrencies('available');
		$activeCurrencies = array();
		foreach ($currencies as $currencyInfo) {
			$curId = $currencyInfo['curid'];
			$activeCurrencies[] = $curId;
			$currencyName = "currency$curId";
			unset($_REQUEST[$currencyName]);
			unset($_REQUEST["cur_$curId"."_check"]);
			if (isset($element[$currencyName]) && is_numeric($element[$currencyName])) {
				$_REQUEST["cur_$curId"."_check"] = 1;
				$_REQUEST["curname$curId"] = CurrencyField::convertToUserFormat($element[$currencyName], null, true);
			}
		}
		unset($_REQUEST['base_currency']);

		if (!empty($element['currency_id'])) {
			$ids = vtws_getIdComponents($element['currency_id']);
			//Import will be sending currency id not in webservice format .	
			//So we are taking 0th index as value
			if (count($ids) == 1) {
				$curId = $ids[0];
			} else {
				$curId = $ids[1];
			}
			if ($curId && is_numeric($curId) && in_array($curId, $activeCurrencies)) {
				$_REQUEST['base_currency'] = "curname$curId";
				$_REQUEST["cur_$curId"."_check"] = 1;
				$_REQUEST["curname$curId"] = CurrencyField::convertToUserFormat($element['unit_price'], null, true);
			} else {
				throw new WebServiceException(WebServiceErrorCode::$INACTIVECURRENCY, "Provided Curreny is Inactive");
			}
		} else if ($mode == 'Create') {
			$curidArray = Vtiger_Util_Helper::getBaseCurrency();
			$curid = $curidArray['id'];
			$_REQUEST['base_currency'] = "curname$curid";
			$_REQUEST["cur_$curid"."_check"] = 1;
			$_REQUEST["curname$curid"] = CurrencyField::convertToUserFormat($element['unit_price'], null, true);
		}
		$_REQUEST['unit_price'] = $element['unit_price'];
		return $element;
	}

	Public function retrieveTaxesAndCurrency($wsId, $entityName) {
		$db = PearDatabase::getInstance();
		$newElement = array();
		if (!self::$currencyWsId) {
			$currencyObject = VtigerWebserviceObject::fromName($db, "Currency");
			self::$currencyWsId = $currencyObject->getEntityId();
		}
		$ids = vtws_getIdComponents($wsId);
		$productId = $ids[1];

		$taxDetails = getTaxDetailsForProduct($productId);
		if ($taxDetails) {
			foreach ($taxDetails as $tax) {
				$taxName = $tax['taxname'];
				$taxPercent = $tax['percentage'];
				$newElement[$taxName] = $taxPercent;
			}
		}

		$priceDetails = getPriceDetailsForProduct($productId, '', '', $entityName);
		foreach ($priceDetails as $priceDetail) {
			$currencyId = $priceDetail['curid'];
			$newElement["currency$currencyId"] = CurrencyField::convertToDBFormat($priceDetail['curvalue']);
			if ($priceDetail['is_basecurrency']) {
				$newElement['currency_id'] = vtws_getId(self::$currencyWsId, $currencyId);
			}
		}

		return $newElement;
	}

}
