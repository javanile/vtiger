<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Products_ExportData_Action extends Vtiger_ExportData_Action {

	var $allTaxes = array();
	var $allRegions = array();
	var $taxHeaders = array();

	public function getAllTaxes() {
		if (!$this->allTaxes) {
			$this->allTaxes = Inventory_TaxRecord_Model::getProductTaxes();
		}
		return $this->allTaxes;
	}

	public function getAllRegions() {
		if (!$this->allRegions) {
			$this->allRegions = Inventory_TaxRegion_Model::getAllTaxRegions();
		}
		return $this->allRegions;
	}

	public function getHeaders() {
		if (!$this->headers) {
			$translatedHeaders = parent::getHeaders();
			$taxModels = $this->getAllTaxes();
			foreach ($taxModels as $taxId => $taxModel) {
				$taxName = $taxModel->getName();
				$decodedTaxName = decode_html($taxName);
				$translatedHeaders[] = $decodedTaxName;
				$this->taxHeaders[] = $decodedTaxName;

				$regions = $taxModel->getRegionTaxes();
				foreach ($regions as $regionsTaxInfo) {
					$allRegions = $this->getAllRegions();
					foreach (array_fill_keys($regionsTaxInfo['list'], $regionsTaxInfo['value']) as $regionId => $taxPercentage) {
						if ($allRegions[$regionId]) {
							$taxRegionName = $taxName . '-' . $allRegions[$regionId]->getName();
							$taxRegionName = decode_html($taxRegionName);
							$translatedHeaders[] = $taxRegionName;
							$this->taxHeaders[] = $taxRegionName;
						}
					}
				}
			}
			$this->headers = $translatedHeaders;
		}
		return $this->headers;
	}

	public function sanitizeValues($arr) {
		$recordId = $arr['crmid'];
		$arr = parent::sanitizeValues($arr);

		$headers = $this->getHeaders();
		$taxModels = $this->getAllTaxes();
		$taxValues = array();

		foreach ($taxModels as $taxId => $taxModel) {
			$taxName = $taxModel->getName();
			$taxPercentageInfo = getProductTaxPercentage($taxModel->get('taxname'), $recordId);
			$taxValues[$taxName] = $taxPercentageInfo['percentage'];

			if ($taxPercentageInfo['regions']) {
				foreach ($taxPercentageInfo['regions'] as $regionsTaxInfo) {
					$allRegions = $this->getAllRegions();
					foreach (array_fill_keys($regionsTaxInfo['list'], $regionsTaxInfo['value']) as $regionId => $taxPercentage) {
						if ($allRegions[$regionId]) {
							$regionTaxName = $taxName . '-' . $allRegions[$regionId]->getName();
							$taxValues[$regionTaxName] = $taxPercentage;
						}
					}
				}
			}
		}

		foreach ($this->taxHeaders as $fieldName) {
			$arr[$fieldName] = $taxValues[$fieldName];
		}

		return $arr;
	}

}
