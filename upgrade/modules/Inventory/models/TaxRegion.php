<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_TaxRegion_Model extends Vtiger_Base_Model {

	const REGIONS_TABLE_NAME = 'vtiger_taxregions';

	public function getId() {
		return $this->get('regionid');
	}

	public function getName() {
		return $this->get('name');
	}

	public function getEditRegionUrl() {
		return '?module=Vtiger&parent=Settings&view=TaxAjax&mode=editTaxRegion&taxRegionId='.$this->getId();
	}

	public function getDeleteRegionUrl() {
		return '?module=Vtiger&parent=Settings&action=TaxAjax&mode=deleteTaxRegion&taxRegionId='.$this->getId();
	}

	/**
	 * Function to save the region info
	 * @return <type>
	 */
	public function save() {
		$db = PearDatabase::getInstance();

		$taxRegionId = $this->getId();
		$taxRagionName = $this->getName();
		if(!empty($taxRegionId)) {
			$db->pquery('UPDATE '.self::REGIONS_TABLE_NAME.' SET name=? WHERE regionid=?', array($taxRagionName, $taxRegionId));
		} else {
			$db->pquery('INSERT INTO '.self::REGIONS_TABLE_NAME.'(name) VALUES(?)', array($taxRagionName));
			$result = $db->pquery('SELECT regionid FROM '.self::REGIONS_TABLE_NAME.' WHERE name=?', array($taxRagionName));
			$this->set('regionid', $db->query_result($result, 0, 'regionid'));
		}
		Vtiger_Cache::flushPicklistCache('regionid');
		return $this->getId();
	}

	/**
	 * Function to get all tax regions
	 * @return <Array> list of Inventory_TaxRegion_Model
	 */
	public static function getAllTaxRegions() {
		$db = PearDatabase::getInstance();
		$taxRegions = array();

		$result = $db->pquery('SELECT * FROM '.self::REGIONS_TABLE_NAME, array());
		while($rowData = $db->fetch_array($result)) {
			$taxRegions[$rowData['regionid']] = new self($rowData);
		}
		return $taxRegions;
	}

	/**
	 * Function to check duplicate name
	 * @param <String> $taxRegionName
	 * @param <Number> $taxRegionId
	 * @return <Boolean> true/false
	 */
	public static function checkDuplicateTaxRegion($taxRegionName, $taxRegionId = false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT 1 FROM '.self::REGIONS_TABLE_NAME.' WHERE name=?';
		$params = array($taxRegionName);

		if ($taxRegionId) {
			$query .= ' AND regionid != ?';
			$params[] = $taxRegionId;
		}

		$result = $db->pquery($query, $params);
		return ($db->num_rows($result) > 0) ? true : false;
	}

	/**
	 * Function to get region model
	 * @param <Number> $taxRegionId
	 * @return <Inventory_TaxRegion_Model>
	 */
	public static function getRegionModel($taxRegionId = false) {
		if ($taxRegionId) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT * FROM '.self::REGIONS_TABLE_NAME.' WHERE regionid=?', array($taxRegionId));
			while($rowData = $db->fetch_array($result)) {
				$regionModel = new self($rowData);
			}
		} else {
			$regionModel = new self();
		}
		return $regionModel;
	}

	/**
	 * Function to delete selected regions
	 * @param <Array> $taxRegionIdsList
	 * @return <Boolean> True/False
	 */
	public static function deleteRegions($taxRegionIdsList = array()) {
		if ($taxRegionIdsList) {
			if (!is_array($taxRegionIdsList)) {
				$taxRegionIdsList = array($taxRegionIdsList);
			}
			$db = PearDatabase::getInstance();
			$db->pquery('DELETE FROM '.self::REGIONS_TABLE_NAME.' WHERE regionid IN ('.generateQuestionMarks($taxRegionIdsList).')', $taxRegionIdsList);

			$taxRecordModelsList = array();
			foreach ($taxRegionIdsList as $regionId) {
				$taxRecordModelsList = array_merge($taxRecordModelsList, Inventory_TaxRecord_Model::getInstancesByRegionId($regionId));
			}
			foreach ($taxRegionIdsList as $regionId) {
				$taxRecordModelsList = array_merge($taxRecordModelsList, Inventory_TaxRecord_Model::getInstancesByRegionId($regionId, Inventory_TaxRecord_Model::SHIPPING_AND_HANDLING_TAX));
			}

			foreach ($taxRecordModelsList as $taxRecordModel) {
				$taxRecordModel->set('isChargeModel', false);
				$taxRecordModel->set('compoundon', $taxRecordModel->getTaxesOnCompound());
			}

			$chargeModelsList = array();
			foreach ($taxRegionIdsList as $regionId) {
				$chargeModelsList = array_merge($chargeModelsList, Inventory_Charges_Model::getInstancesByRegionId($regionId));
			}

			foreach ($chargeModelsList as $chargeModel) {
				$chargeModel->set('isChargeModel', true);
				$chargeModel->set('taxes', Zend_Json::decode(html_entity_decode($chargeModel->get('taxes'))));
			}

			$recordModelsList = array_merge($taxRecordModelsList, $chargeModelsList);
			foreach ($recordModelsList as $recordModel) {
				$isChargeModel = $recordModel->get('isChargeModel');
				if ($isChargeModel) {
					$recordRegions = $recordModel->getSelectedRegions();
				} else {
					$recordRegions = $recordModel->getRegionTaxes();
				}

				for ($i=0; $i<count($recordRegions); $i++) {
					$regionsList = $recordRegions[$i]['list'];
					if (count($regionsList) === 1) {
						if (in_array($regionId, $regionsList)) {
							unset($recordRegions[$i]);
						}
					} else {
						foreach ($regionsList as $key => $id) {
							if ($regionId == $id) {
								unset($regionsList[$key]);
							}
							if ($regionsList) {
								$recordRegions[$i]['list'] = $regionsList;
							} else {
								unset($recordRegions[$i]);
							}
						}
					}
				}
				if (!$recordRegions) {
					$recordRegions = array();
				}

				$recordModel->set('regions', array_values($recordRegions));
				try {
					$recordModel->save();
				} catch (Exception $e) {
					return $e->getMessage();
				}
			}
		}
		return true;
	}

}
