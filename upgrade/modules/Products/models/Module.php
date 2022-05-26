<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array($this->getName(), 'Vendors', 'Leads', 'Accounts', 'Contacts', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " vtiger_products.discontinued = 1 ";
			if ($sourceModule === $this->getName()) {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel WHERE setype = '". $this->getName(). "' UNION SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record') AND vtiger_products.productid <> '$record' ";
			} elseif ($sourceModule === 'PriceBooks') {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif ($sourceModule === 'Vendors') {
				$condition .= " AND vtiger_products.vendor_id != '$record' ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel WHERE crmid = '$record')";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = preg_split('/where/i', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND vtiger_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
 	}

	/**
	 * Function to get prices for specified products with specific currency
	 * @param <Integer> $currenctId
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList, $skipActualPrice = false) {
		return getPricesForProducts($currencyId, $productIdsList, $this->getName(), $skipActualPrice);
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Vtiger_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule) && (in_array($relatedModule, getInventoryModules()))) {
			$matchingRecords = Products_Record_Model::getSearchResult($searchValue, $this->getName());
		}else {
			return parent::searchRecord($searchValue);
		}

		return $matchingRecords;
	}
	
	/**
	 * Function returns query for Product-PriceBooks relation
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_product_pricebooks($recordModel, $relatedModuleModel) {
		$query = 'SELECT vtiger_pricebook.pricebookid, vtiger_pricebook.bookname, vtiger_pricebook.active, vtiger_crmentity.crmid, 
						vtiger_crmentity.smownerid, vtiger_pricebookproductrel.listprice, vtiger_products.unit_price
					FROM vtiger_pricebook
					INNER JOIN vtiger_pricebookproductrel ON vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
					INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_pricebook.pricebookid
					INNER JOIN vtiger_products on vtiger_products.productid = vtiger_pricebookproductrel.productid
					INNER JOIN vtiger_pricebookcf on vtiger_pricebookcf.pricebookid = vtiger_pricebook.pricebookid
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
					. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
					WHERE vtiger_products.productid = '.$recordModel->getId().' and vtiger_crmentity.deleted = 0';
					
		return $query;
	}
    
    /**
	 * Function returns export query which included currency_id field
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($focus, $query) {
		$baseTableName = $focus->table_name;
		$splitQuery = preg_split('/ FROM /i', $query);
		$columnFields = explode(',', $splitQuery[0]);
        $columnFields[] = ' vtiger_currency_info.currency_name AS currency_id, crmid';

		$joinSplit = preg_split('/ WHERE /i',$splitQuery[1]);
		$joinSplit[0] .= " LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = $baseTableName.currency_id";
		$splitQuery[1] = $joinSplit[0].' WHERE ' .$joinSplit[1];

		$query = implode(',', $columnFields).' FROM '.$splitQuery[1];
		return $query;
	}

	/**
	 * Function to search records based on sequence number
	 * @param <String> $searchValue
	 * @param <String> $relatedModule
	 * @return <Array> $matchedRecordModels
	 */
	public function searchRecordsOnSequenceNumber($searchValue, $relatedModule) {
		if (in_array($relatedModule, getInventoryModules())) {
			$db = PearDatabase::getInstance();
			$moduleName = $this->getName();
			$tableName = $this->basetable;
			$baseFieldName = $this->basetableid;

			$fieldName = 'product_no';
			if ($moduleName === 'Services') {
				$fieldName = 'service_no';
			}

			$query = "SELECT label, crmid, $fieldName FROM vtiger_crmentity
						INNER JOIN $tableName ON $tableName.$baseFieldName = vtiger_crmentity.crmid
						WHERE $fieldName LIKE ? AND vtiger_crmentity.deleted = 0 AND discontinued = 1";
			$result = $db->pquery($query, array("%$searchValue%"));
			$noOfRows = $db->num_rows($result);

			$matchingRecords = array();
			for($i=0; $i<$noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
					$row['id'] = $row['crmid'];
					$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
					$recordInstance = new $modelClassName();
					$matchingRecords[$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($this);
				}
			}
			return $matchingRecords;
		}
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array('Import', 'Export', 'DuplicatesHandling');
	}

	/**
	 * Function to check the passed products are child products or not
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function areChildProducts($productIdsList = array()) {
		if ($productIdsList) {
			if (!is_array($productIdsList)) {
				$productIdsList = array($productIdsList);
			}

			$db = PearDatabase::getInstance();
			$query = 'SELECT DISTINCT vtiger_seproductsrel.crmid FROM vtiger_seproductsrel
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seproductsrel.productid
						WHERE vtiger_crmentity.deleted = 0 AND vtiger_seproductsrel.setype = "Products"
						AND vtiger_seproductsrel.crmid IN ('.generateQuestionMarks($productIdsList).')';

			$result = $db->pquery($query, $productIdsList);

			$childProductIdsList = array();
			while($rowData = $db->fetch_array($result)) {
				$childProductIdsList[] = $rowData['crmid'];
			}

			$childProductsResult = array();
			foreach ($productIdsList as $productId) {
				$isChildProduct = false;
				if (in_array($productId, $childProductIdsList)) {
					$isChildProduct = true;
				}
				$childProductsResult[$productId] = $isChildProduct;
			}

			return $childProductsResult;
		}
	}

	public function getAdditionalImportFields() {
		if (!$this->importableFields) {
			$taxModels = Inventory_TaxRecord_Model::getProductTaxes();
			foreach ($taxModels as $taxId => $taxModel) {
				if ($taxModel->isDeleted()) {
					unset($taxModels[$taxId]);
				}
			}

			$taxHeaders = array();
			$allRegions = Inventory_TaxRegion_Model::getAllTaxRegions();
			foreach ($taxModels as $taxId => $taxModel) {
				$tax = $taxModel->get('taxname');
				$taxName = $taxModel->getName();
				$taxHeaders[$tax] = decode_html($taxName);

				$regions = $taxModel->getRegionTaxes();
				foreach ($regions as $regionsTaxInfo) {
					foreach(array_fill_keys($regionsTaxInfo['list'], $regionsTaxInfo['value']) as $regionId => $taxPercentage) {
						if ($allRegions[$regionId]) {
							$taxRegionName = $taxName.'-'.$allRegions[$regionId]->getName();
							$taxHeaders[$tax."_$regionId"] = decode_html($taxRegionName);
						}
					}
				}
			}

			$this->importableFields = array();
			foreach ($taxHeaders as $fieldName => $fieldLabel) {
				$fieldModel = new Vtiger_Field_Model();
				$fieldModel->name = $fieldName;
				$fieldModel->label = $fieldLabel;
				$fieldModel->column = $fieldName;
				$fieldModel->uitype = '83';
				$webServiceField = $fieldModel->getWebserviceFieldObject();
				$webServiceField->setFieldDataType($fieldModel->getFieldDataType());
				$fieldModel->webserviceField = $webServiceField;
				$this->importableFields[$fieldName] = $fieldModel;
			}
		}
		return $this->importableFields;
	}

	/**
	 * Function to get popup view fields
	 */
	public function getPopupViewFieldsList(){
		$summaryFieldsList = parent::getPopupViewFieldsList();
		foreach($summaryFieldsList as $key=>$fieldname){
			if($fieldname == 'qty_per_unit'){
				$out = array_splice($summaryFieldsList, $key, 1);
				array_splice($summaryFieldsList, 1, 0, $out);
			}
		}
		return $summaryFieldsList;
	}

}
