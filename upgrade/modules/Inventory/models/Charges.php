<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_Charges_Model extends Vtiger_Base_Model {

	const CHARGES_TABLE_NAME = 'vtiger_inventorycharges';

	public function getId() {
		return $this->get('chargeid');
	}

	public function getName() {
		return $this->get('name');
	}

	public function getValue() {
		return $this->get('value');
	}

	public function isTaxable() {
		return $this->get('istaxable');
	}

	public static function getCreateChargeUrl() {
		return '?module=Vtiger&parent=Settings&view=TaxAjax&mode=editCharge';
	}

	public function getEditChargeUrl() {
		return '?module=Vtiger&parent=Settings&view=TaxAjax&mode=editCharge&chargeId='.$this->getId();
	}

	public function getDeleteChargeUrl() {
		return '?module=Vtiger&parent=Settings&action=TaxAjax&mode=deleteCharge&chargeId='.$this->getId();
	}

	public function getSelectedRegions() {
		$regions = $this->get('regions');
		if ($regions) {
			return Zend_Json::decode(html_entity_decode($regions));
		}
		return array();
	}

	public function getDisplayValue() {
		$value = $this->getValue();
		if ($this->get('format') === 'Flat') {
			$value = CurrencyField::convertToUserFormat($value, null, true);
		} else {
			$value = number_format($value, getCurrencyDecimalPlaces(), '.', '') . '%';
		}
		return $value;
	}

	/**
	 * Function to get selected taxes
	 * @param <Boolean> $deleted
	 * @return <Array> list of Inventory_TaxRecord_Model
	 */
	public function getSelectedTaxes($deleted = true) {
		if (!$this->taxes) {
			$taxModelsList = array();
			$isTaxable = $this->isTaxable();
			if ($isTaxable) {
				$taxes = $this->get('taxes');
				if (!is_array($taxes)) {
					$taxes = Zend_Json::decode(html_entity_decode($this->get('taxes')));
				}

				$db = PearDatabase::getInstance();
				$query = 'SELECT * FROM '.Inventory_TaxRecord_Model::CHARGES_TAX_TABLE_NAME.' WHERE taxid IN ('.generateQuestionMarks($taxes).')';
				if ($deleted) {
					$query .= 'AND deleted = 0';
				}

				$result = $db->pquery($query, $taxes);
				while($rowData = $db->fetch_array($result)) {
					$taxModelsList[$rowData['taxid']] = new Inventory_TaxRecord_Model($rowData);
				}
			}
			$this->taxes = $taxModelsList;
		}
		return $this->taxes;
	}

	/**
	 * Function to save the charge details
	 * @return <Number> charge id
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$chargeId = $this->getId();

		$regions = Zend_Json::encode($this->get('regions'));
		$taxes = Zend_Json::encode($this->get('taxes'));
		$params = array($this->getName(), $this->get('format'), $this->get('type'), $this->getValue(), $regions, $this->get('istaxable'), $taxes);

		if($chargeId) {
			$query = 'UPDATE '.self::CHARGES_TABLE_NAME.' SET name=?, format=?, type=?, value=?, regions=?, istaxable=?, taxes=? WHERE chargeid=?';
			$params[] = $chargeId;
			$db->pquery($query,$params);
		} else {
			$query = 'INSERT INTO '.self::CHARGES_TABLE_NAME.'(name, format, type, value, regions, istaxable, taxes) VALUES('.generateQuestionMarks($params).')';
			$db->pquery($query,$params);

			$result = $db->pquery('SELECT chargeid FROM '.self::CHARGES_TABLE_NAME.' WHERE name=?', array($this->getName()));
			$this->set('chargeid', $db->query_result($result, 0, 'chargeid'));
		}
		return $this->getId();
	}

	/**
	 * Function to get list of charges
	 * @return <Array> list of Inventory_Charges_Models
	 */
	public static function getInventoryCharges() {
		$db = PearDatabase::getInstance();
		$inventoryChargeModelsList = array();

		$result = $db->pquery('SELECT * FROM '.self::CHARGES_TABLE_NAME.' WHERE deleted=?', array(0));
		while($rowData = $db->fetch_array($result)) {
			$inventoryChargeModelsList[$rowData['chargeid']] = new self($rowData);
		}
		return $inventoryChargeModelsList;
	}

	/**
	 * Function to get charge model
	 * @param <Number> $chargeId
	 * @return <Inventory_Charges_Model>
	 */
	public static function getChargeModel($chargeId = false) {
		if ($chargeId) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT * FROM '.self::CHARGES_TABLE_NAME.' WHERE chargeid=? AND deleted = 0', array($chargeId));
			while($rowData = $db->fetch_array($result)) {
				$chargeRecordModel = new self($rowData);
			}
		} else {
			$chargeRecordModel = new self();
		}
		return $chargeRecordModel;
	}

	/**
	 * Function to delete selected charges
	 * @param <Array> $chargeIdsList
	 * @return <Boolean> True/False
	 */
	public static function deleteCharges($chargeIdsList = array()) {
		if ($chargeIdsList) {
			if (!is_array($chargeIdsList)) {
				$chargeIdsList = array($chargeIdsList);
			}
			$db = PearDatabase::getInstance();
			$db->pquery('UPDATE '.self::CHARGES_TABLE_NAME.' SET deleted=1 WHERE chargeid IN ('.generateQuestionMarks($chargeIdsList).')', $chargeIdsList);
		}
		return true;
	}

	/**
	 * Function to get Inventory_Charges_Model model by using region id
	 * @param <Number> $regionId
	 * @return <Array> list of Inventory_Charges_Models
	 */
	public static function getInstancesByRegionId($regionId) {
		$db = PearDatabase::getInstance();
		$recordModelsList = array();

		$result = $db->pquery('SELECT * FROM '.self::CHARGES_TABLE_NAME.' WHERE regions LIKE (?) AND deleted = 0', array("%\"$regionId\"%"));
		while($rowData = $db->fetch_array($result)) {
			$recordModelsList[$rowData['chargeid']] = new self($rowData);
		}
		return $recordModelsList;
	}

	/**
	 * Function to get charge models list
	 * @param <Array> $idsList
	 * @param <String> $type
	 * @return <Array> list of Inventory_Charges_Models
	 */
	public static function getChargeModelsList($idsList = array(), $type = 'all') {
		$chargeModelsList = array();
		if ($idsList) {
			$db = PearDatabase::getInstance();
			$sql = 'SELECT * FROM '.self::CHARGES_TABLE_NAME.' WHERE chargeid IN ('.  generateQuestionMarks($idsList).')';
			if ($type != 'all') {
				$sql .= ' AND deleted = 0';
			}

			$result = $db->pquery($sql, $idsList);
			while($rowData = $db->fetch_array($result)) {
				$chargeModelsList[$rowData['chargeid']] = new self($rowData);
			}
		}
		return $chargeModelsList;
	}

	/**
	 * Function to get charges taxes list
	 * @return <Array> list of Inventory_TaxRecord_Models
	 */
	public static function getChargeTaxesList() {
		$chargeTaxesList = array();
		foreach (Inventory_Charges_Model::getInventoryCharges() as $chargeId => $chargeModel) {
			foreach ($chargeModel->getSelectedTaxes() as $taxId => $taxModel) {
				$chargeTaxesList[$chargeId][$taxId] = $taxModel->getData();
			}
		}
		return $chargeTaxesList;
	}

	/**
	 * Function to check duplicate charge name
	 * @param <String> $chargeName
	 * @param <Number> $excludedId
	 * @return <Boolean> True/False
	 */
	public static function checkDuplicateInventoryCharge($chargeName, $excludedId = false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT 1 FROM '.self::CHARGES_TABLE_NAME.' WHERE name=? AND deleted=?';
		$params = array($chargeName, '0');

		if ($excludedId) {
			$query .= ' AND chargeid != ?';
			$params[] = $excludedId;
		}

		$result = $db->pquery($query, $params);
		return ($db->num_rows($result) > 0) ? true : false;
	}

}
