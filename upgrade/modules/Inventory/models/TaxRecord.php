<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_TaxRecord_Model extends Vtiger_Base_Model {

	const INVENTORY_TAXES_TABLE_NAME = 'vtiger_inventorytaxinfo';
	const CHARGES_TAX_TABLE_NAME = 'vtiger_shippingtaxinfo';
	const PRODUCT_AND_SERVICE_TAX = 0;
	const SHIPPING_AND_HANDLING_TAX = 1;

	private $type;

	public function getId() {
		return $this->get('taxid');
	}

	public function getName() {
		return $this->get('taxlabel');
	}

	public function getTax() {
		return $this->get('percentage');
	}

	public function getTaxType() {
		return $this->get('type');
	}

	public function getTaxMethod() {
		return $this->get('method');
	}

	public function isDeleted() {
		return $this->get('deleted') == 0 ? false : true;
	}

	public function markDeleted() {
		return $this->set('deleted','1');
	}

	public function unMarkDeleted() {
		return $this->set('deleted','0');
	}

	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	public function getType() {
		return $this->type;
	}

	public function isProductTax() {
		return ($this->getType() == self::PRODUCT_AND_SERVICE_TAX) ? true : false;
	}

	public function isShippingTax() {
		return ($this->getType() == self::SHIPPING_AND_HANDLING_TAX) ? true : false;
	}

	public function getCreateTaxUrl() {
		return '?module=Vtiger&parent=Settings&view=TaxAjax&mode=editTax';
	}

	public function getEditTaxUrl() {
		return '?module=Vtiger&parent=Settings&view=TaxAjax&mode=editTax&type='.$this->getType().'&taxid='.$this->getId();
	}

	public function getRegionTaxes() {
		$regions = $this->get('regions');
		if ($regions) {
			return Zend_Json::decode(html_entity_decode($regions));
		}
		return array();
	}

	public function getTaxesOnCompound() {
		$compoundOn = $this->get('compoundon');
		if ($compoundOn) {
			return Zend_Json::decode(html_entity_decode($compoundOn));
		}
		return array();
	}

	private function getTableNameFromType() {
		$tablename = self::INVENTORY_TAXES_TABLE_NAME;

		if($this->isShippingTax()) {
			$tablename = self::CHARGES_TAX_TABLE_NAME;
		}
		return $tablename;
	}

	public function save() {
		$db = PearDatabase::getInstance();

		$taxId = $this->getId();
		if(!empty($taxId)) {
			$deleted = 0;
			if($this->isDeleted()) {
				$deleted = 1;
			}

			$compoundOn = Zend_Json::encode($this->get('compoundon'));
			$regions = Zend_Json::encode($this->get('regions'));

			$query = 'UPDATE '.$this->getTableNameFromType().' SET taxlabel=?, percentage=?, deleted=?, method=?, type=?, compoundon=?, regions=? WHERE taxid=?';
			$params = array($this->getName(), $this->get('percentage'), $deleted, $this->get('method'), $this->get('type'), $compoundOn, $regions, $taxId);
			$db->pquery($query,$params);
		} else {
			$taxId = $this->addTax();
		}
		return $taxId;
	}

	/**	Function used to add the tax type which will do database alterations
	 * @param string $taxlabel - tax label name to be added
	 * @param string $taxvalue - tax value to be added
	 * @param string $sh - sh or empty , if sh passed then the tax will be added in shipping and handling related table
	 * @return void
	*/
	public function addTax() {
		$adb = PearDatabase::getInstance();

		$tableName = $this->getTableNameFromType();
		$taxid = $adb->getUniqueID($tableName);
		$taxLabel = $this->getName();
		$percentage = $this->get('percentage');

		//if the tax is not available then add this tax.
		//Add this tax as a column in related table
		if($this->isShippingTax()) {
			$taxname = "shtax".$taxid;
			$query = "ALTER TABLE vtiger_inventoryshippingrel ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
		} else {
			$taxname = "tax".$taxid;
			$query = "ALTER TABLE vtiger_inventoryproductrel ADD COLUMN $taxname decimal(7,3) DEFAULT NULL";
		}
		$res = $adb->pquery($query, array());

		vimport('~~/include/utils/utils.php');

		if ($this->isProductTax()) {
			// TODO Review: if field addition is required in shipping-tax case too.
			// NOTE: shtax1, shtax2, shtax3 that is added as default should also be taken care.

			$inventoryModules = getInventoryModules();
			foreach ($inventoryModules as $moduleName) {
				$moduleInstance = Vtiger_Module::getInstance($moduleName);
				$blockInstance = Vtiger_Block::getInstance('LBL_ITEM_DETAILS',$moduleInstance);
				$field = new Vtiger_Field();

				$field->name = $taxname;
				$field->label = $taxLabel;
				$field->column = $taxname;
				$field->table = 'vtiger_inventoryproductrel';
				$field->uitype = '83';
				$field->typeofdata = 'V~O';
				$field->readonly = '0';
				$field->displaytype = '5';
				$field->masseditable = '0';

				$blockInstance->addField($field);
			}
		}

		//if the tax is added as a column then we should add this tax in the list of taxes
		if($res) {
			$deleted = 0;
			if($this->isDeleted()) {
				$deleted = 1;
			}

			$compoundOn = Zend_Json::encode($this->get('compoundon'));
			$regions = Zend_Json::encode($this->get('regions'));

			$query = 'INSERT INTO '.$tableName.' values(?,?,?,?,?,?,?,?,?)';
			$params = array($taxid, $taxname, $taxLabel, $percentage, $deleted, $this->get('method'), $this->get('type'), $compoundOn, $regions);
			$adb->pquery($query, $params);
			return $taxid;
		}
		throw new Error('Error occurred while adding tax');
	}

	public function updateTaxStatus() {
		$db = PearDatabase::getInstance();
		$taxId = $this->getId();
		$db->pquery('UPDATE '.$this->getTableNameFromType().' SET deleted=? WHERE taxid=?', array($this->get('deleted'), $taxId));
		return $taxId;
	}

	public static function getProductTaxes() {
		vimport('~~/include/utils/InventoryUtils.php');
		$taxes = getAllTaxes();
		$recordList = array();
		foreach($taxes as $taxInfo) {
			$taxRecord = new self();
			$taxRecord->setData($taxInfo)->setType(self::PRODUCT_AND_SERVICE_TAX);
			$recordList[$taxRecord->getId()] = $taxRecord;
		}
		return $recordList;
	}

	public static function getChargeTaxes() {
		vimport('~~/include/utils/InventoryUtils.php');
		$taxes = getAllTaxes('all','sh');
		$recordList = array();
		foreach($taxes as $taxInfo) {
			$taxRecord = new self();
			$taxRecord->setData($taxInfo)->setType(self::SHIPPING_AND_HANDLING_TAX);
			$recordList[$taxRecord->getId()] = $taxRecord;
		}
		return $recordList;
	}

	public static function getInstanceById($id, $type = self::PRODUCT_AND_SERVICE_TAX) {
		$db = PearDatabase::getInstance();
		$tablename = self::INVENTORY_TAXES_TABLE_NAME;

		if($type == self::SHIPPING_AND_HANDLING_TAX) {
			$tablename = self::CHARGES_TAX_TABLE_NAME;
		}

		$query = 'SELECT * FROM '.$tablename.' WHERE taxid=?';
		$result = $db->pquery($query,array($id));
		$taxRecordModel = new self();
		if($db->num_rows($result) > 0) {
			$row = $db->query_result_rowdata($result,0);
			$taxRecordModel->setData($row)->setType($type)->set('taxType', $row['type']);
		}
		return $taxRecordModel;
	}

	public static function checkDuplicate($label, $excludedId = false, $type = self::PRODUCT_AND_SERVICE_TAX) {
		$db = PearDatabase::getInstance();
		$tablename = self::INVENTORY_TAXES_TABLE_NAME;
		if($type == self::SHIPPING_AND_HANDLING_TAX) {
			$tablename = self::CHARGES_TAX_TABLE_NAME;
		}

		$query = 'SELECT 1 FROM '.$tablename.' WHERE taxlabel=?';
		$params = array($label);

		if ($excludedId) {
			$query .= ' AND taxid != ?';
			$params[] = $excludedId;
		}
		$result = $db->pquery($query,$params);
		return ($db->num_rows($result) > 0) ? true : false;
	}

	public static function getSimpleTaxesList($taxId, $type = self::PRODUCT_AND_SERVICE_TAX) {
		$db = PearDatabase::getInstance();
		$tableName = self::INVENTORY_TAXES_TABLE_NAME;

		if($type == self::SHIPPING_AND_HANDLING_TAX) {
			$tableName = self::CHARGES_TAX_TABLE_NAME;
		}

		$simpleTaxModelsList = array();
		$query = 'SELECT taxid, taxlabel, percentage FROM '.$tableName.' WHERE method=? AND deleted=?';
		$params = array('Simple', 0);

		if ($taxId) {
			$query .= ' AND taxid != ?';
			$params[] = $taxId;
		}

		$result = $db->pquery($query, $params);
		while($rowData = $db->fetch_array($result)) {
			$taxRecordModel = new self($rowData);
			$taxRecordModel->setType($type);
			$simpleTaxModelsList[$taxRecordModel->getId()] = $taxRecordModel;
		}
		return $simpleTaxModelsList;
	}

	public static function getInstancesByRegionId($regionId, $type = self::PRODUCT_AND_SERVICE_TAX) {
		$db = PearDatabase::getInstance();
		$tableName = self::INVENTORY_TAXES_TABLE_NAME;
		if($type == self::SHIPPING_AND_HANDLING_TAX) {
			$tableName = self::CHARGES_TAX_TABLE_NAME;
		}

		$recordModelsList = array();
		$result = $db->pquery('SELECT * FROM '.$tableName.' WHERE regions LIKE (?)', array("%\"$regionId\"%"));
		while($rowData = $db->fetch_array($result)) {
			$taxRecordModel = new self($rowData);
			$taxRecordModel->setType($type);
			$recordModelsList[$taxRecordModel->getId()] = $taxRecordModel;
		}
		return $recordModelsList;
	}

	public static function getDeductTaxesList($active = true) {
		$db = PearDatabase::getInstance();
		$tableName = Inventory_TaxRecord_Model::INVENTORY_TAXES_TABLE_NAME;

		$where = '';
		if ($active) {
			$where = ' AND deleted=0';
		}

		$deductTaxesList = array();
		$result = $db->pquery("SELECT * FROM $tableName WHERE method=? $where", array('Deducted'));
		while($rowData = $db->fetch_array($result)) {
			$deductTaxesList[$rowData['taxid']] = $rowData;
		}
		return $deductTaxesList;
	}
}
