<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once "include/Webservices/VtigerActorOperation.php";
require_once 'include/Webservices/LineItem/VtigerTaxMeta.php';
require_once("include/events/include.inc");
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'data/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/VtigerLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Emails/mail.php';


/**
 * Description of VtigerTaxOperation
 */
class VtigerTaxOperation  extends VtigerActorOperation {

	public function __construct($webserviceObject, $user, $adb, $log) {
		parent::__construct($webserviceObject,$user,$adb,$log);
		$this->entityTableName = $this->getActorTables();
		if($this->entityTableName === null){
			throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY,"Entity is not associated with any tables");
		}
		$this->meta = new VtigerTaxMeta($this->entityTableName,$webserviceObject,$adb,$user);
		$this->moduleFields = null;
	}

	public function create($elementType, $taxElement) {
		$element = $this->restrictFields($taxElement);

		$taxFormula = $taxElement[$taxElement['taxname'].'_formula'];
		if (!$taxFormula) {
			$taxFormula = $taxElement['formula'];
		}
		$element['formula'] = $taxFormula;

		$taxName = $this->getNewTaxName();
		$element['taxname'] = $taxName;
		$element['deleted'] = 0;
		$element = $this->sanitizeElementForInsert($element);
		$createdElement = parent::create($elementType, $element);
		$sql = "alter table vtiger_inventoryproductrel add column $taxName decimal(7,3)";
		$result = $this->pearDB->pquery($sql,array());
		if(!is_object($result)) {
			list($typeId,$id) = vtws_getIdComponents($element['id']);
			$this->dropRow($id);
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				"Database error while adding tax column($taxName) for inventory lineitem table");
		}
		return $createdElement;
	}

	public function update($element) {
		$element['taxname'] = $this->getTaxName($element);
		$element = $this->sanitizeElementForInsert($element);

		return parent::update($element);
	}

	public function delete($id) {
		$ids = vtws_getIdComponents($id);
		$elemId = $ids[1];

		$result = null;
		$query = 'update '.$this->entityTableName.' set deleted=1 where '.$this->meta->getObectIndexColumn().'=?';
		$transactionSuccessful = vtws_runQueryAsTransaction($query,array($elemId),$result);
		if(!$transactionSuccessful){
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR,
				"Database error while performing required operation");
		}
		return array("status"=>"successful");
	}

	private function dropRow($id) {
		$sql = 'delete from vtiger_inventorytaxinfo where taxid = ?';
		$params = array($id);
		$result = $this->pearDB->pquery($sql, $params);
	}

	private function getCurrentTaxName() {
		$sql = 'select taxname from vtiger_inventorytaxinfo order by taxid desc limit 1';
		$params = array();
		$result = $this->pearDB->pquery($sql, $params);
		$it = new SqlResultIterator($this->pearDB, $result);
		$currentTaxName = null;
		foreach ($it as $row) {
			$currentTaxName = $row->taxname;
		}
		return $currentTaxName;
	}

	/**
	 * Function get tax name
	 * @param <Array> $element
	 * @return <String> taxName
	 */
	private function getTaxName($element) {
		if ($element['taxlabel']) {
			$sql = 'SELECT taxname FROM vtiger_inventorytaxinfo WHERE taxlabel = ?';
			$params = array($element['taxlabel']);
			$result = $this->pearDB->pquery($sql, $params);
			$it = new SqlResultIterator($this->pearDB, $result);
			$taxName = NULL;
			foreach ($it as $row) {
				$taxName = $row->taxname;
			}
			return $taxName;
		}
		return $this->getCurrentTaxName();
	}

	private function getNewTaxName() {
		$currentTaxName = $this->getCurrentTaxName();

		if(empty($currentTaxName)) {
			return 'tax1';
		}

		$matches = null;
		if ( preg_match('/tax(\d+)/', $currentTaxName, $matches) != 0 ) {
			$taxNumber = (int) $matches[1];
			$taxNumber++;
			return 'tax'.$taxNumber;
		}
		return 'tax1';
	}

	public function retrieve($id) {
		$element = parent::retrieve($id);

		//Constructing regions as element fields
		$regions = Zend_Json::decode(html_entity_decode($element['regions']));
		if ($regions) {
			$allRegions = getAllRegions();
			foreach ($allRegions as $regionId => $regionInfo) {
				$regionInfo['name'] = strtolower(str_replace(' ', '_', $regionInfo['name']));
				$allRegions[$regionId] = $regionInfo;
			}

			foreach ($regions as $regionInfo) {
				foreach ($regionInfo['list'] as $regionId) {
					$element[$allRegions[$regionId]['name']] = $regionInfo['value'];
				}
			}
		}
		unset($element['regions']);

		//Constructing compound info as element field
		$compoundOn	= Zend_Json::decode(html_entity_decode($element['compoundon']));
		if ($compoundOn) {
			$allTaxes = array();
			$allItemTaxes = getAllTaxes();
			foreach ($allItemTaxes as $taxInfo) {
				$allTaxes[$taxInfo['taxid']] = $taxInfo;
			}

			$compoundInfo = '';
			foreach ($compoundOn as $taxId) {
				$compoundInfo = "$compoundInfo+".$allTaxes[$taxId]['taxname'];
			}
			$element[$element['taxname'].'_formula'] = ltrim($compoundInfo, '+');
		}
		unset($element['compoundon']);

		return $element;
	}

	/**
	 * Function to sanitize element for insert
	 * @param <Array> $element
	 * @return <Array>
	 */
	private function sanitizeElementForInsert($element) {
		$compoundOn = $regions = array();
		$type = 'Fixed';
		$method = 'Simple';

		$taxFormula = $element[$element['taxname'].'_formula'];
		if (!$taxFormula) {
			$taxFormula = $element['formula'];
		}

		if ($taxFormula) {
			$taxFormulaElements = explode('+', $taxFormula);
			$sql = 'SELECT taxid, method FROM vtiger_inventorytaxinfo WHERE taxname IN ('.generateQuestionMarks($taxFormulaElements).')';
			$params = $taxFormulaElements;
			$result = $this->pearDB->pquery($sql, $params);
			$it = new SqlResultIterator($this->pearDB, $result);
			foreach ($it as $row) {
				if ($row->method === 'Simple') {
					$compoundOn[] = $row->taxid;
				}
			}
		}
		if ($compoundOn) {
			$method = 'Compound';
		}

		$regionsList = array();
		$allRegions = getAllRegions();
		foreach ($allRegions as $regionId => $regionInfo) {
			$regionName = strtolower(str_replace(' ', '_', $regionInfo['name']));
			if (array_key_exists($regionName, $element)) {
				$regionValue = $element[$regionName];
				$regionsList[$regionValue][] = $regionId;
			}
		}

		foreach ($regionsList as $regionValue => $regions) {
			$regions[] = array('list' => $regions, 'value' => $regionValue);
		}
		if ($regions) {
			$type = 'Variable';
		}

		if ($element['method'] === 'Deducted' && !$compoundOn && !$regions) {
			$method = 'Deducted';
		}

		$element['type'] = $type;
		$element['method'] = $method;
		$element['regions'] = Zend_Json::encode($regions);
		$element['compoundon'] = Zend_Json::encode($compoundOn);

		return $element;
	}

}
?>