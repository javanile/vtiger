<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

global $calpath;
global $app_strings, $mod_strings;
global $theme;
global $log;

$theme_path = "themes/" . $theme . "/";
$image_path = $theme_path . "images/";
require_once('include/database/PearDatabase.php');
require_once('data/CRMEntity.php');
require_once("modules/Reports/Reports.php");
require_once 'modules/Reports/ReportUtils.php';
require_once("vtlib/Vtiger/Module.php");
require_once('modules/Vtiger/helpers/Util.php');
require_once('include/RelatedListView.php');

/*
 * Helper class to determine the associative dependency between tables.
 */
class ReportRunQueryDependencyMatrix {

	protected $matrix = array();
	protected $computedMatrix = null;

	function setDependency($table, array $dependents) {
		$this->matrix[$table] = $dependents;
	}

	function addDependency($table, $dependent) {
		if (isset($this->matrix[$table]) && !in_array($dependent, $this->matrix[$table])) {
			$this->matrix[$table][] = $dependent;
		} else {
			$this->setDependency($table, array($dependent));
		}
	}

	function getDependents($table) {
		$this->computeDependencies();
		return isset($this->computedMatrix[$table]) ? $this->computedMatrix[$table] : array();
	}

	protected function computeDependencies() {
		if ($this->computedMatrix !== null)
			return;

		$this->computedMatrix = array();
		foreach ($this->matrix as $key => $values) {
			$this->computedMatrix[$key] = $this->computeDependencyForKey($key, $values);
		}
	}

	protected function computeDependencyForKey($key, $values) {
		$merged = array();
		foreach ($values as $value) {
			$merged[] = $value;
			if (isset($this->matrix[$value])) {
				$merged = array_merge($merged, $this->matrix[$value]);
			}
		}
		return $merged;
	}

}

class ReportRunQueryPlanner {

	// Turn-off the query planning to revert back - backward compatiblity
	protected $disablePlanner = false;
	protected $tables = array();
	protected $tempTables = array();
	protected $tempTablesInitialized = false;
	// Turn-off in case the query result turns-out to be wrong.
	protected $allowTempTables = true;
	protected $tempTablePrefix = 'vtiger_reptmptbl_';
	protected static $tempTableCounter = 0;
	protected $registeredCleanup = false;
	var $reportRun = false;

	function addTable($table) {
		if (!empty($table))
			$this->tables[$table] = $table;
	}

	function requireTable($table, $dependencies = null) {

		if ($this->disablePlanner) {
			return true;
		}

		if (isset($this->tables[$table])) {
			return true;
		}
		if (is_array($dependencies)) {
			foreach ($dependencies as $dependentTable) {
				if (isset($this->tables[$dependentTable])) {
					return true;
				}
			}
		} else if ($dependencies instanceof ReportRunQueryDependencyMatrix) {
			$dependents = $dependencies->getDependents($table);
			if ($dependents) {
				return count(array_intersect($this->tables, $dependents)) > 0;
			}
		}
		return false;
	}

	function getTables() {
		return $this->tables;
	}

	function newDependencyMatrix() {
		return new ReportRunQueryDependencyMatrix();
	}

	function registerTempTable($query, $keyColumns, $module = null) {
		if ($this->allowTempTables && !$this->disablePlanner) {
			global $current_user;

			$keyColumns = is_array($keyColumns) ? array_unique($keyColumns) : array($keyColumns);

			// Minor optimization to avoid re-creating similar temporary table.
			$uniqueName = NULL;
			foreach ($this->tempTables as $tmpUniqueName => $tmpTableInfo) {
				if (strcasecmp($query, $tmpTableInfo['query']) === 0 && $tmpTableInfo['module'] == $module) {
					// Capture any additional key columns
					$tmpTableInfo['keycolumns'] = array_unique(array_merge($tmpTableInfo['keycolumns'], $keyColumns));
					$uniqueName = $tmpUniqueName;
					break;
				}
			}

			// Nothing found?
			if ($uniqueName === NULL) {
				// TODO Adding randomness in name to avoid concurrency
				// even when same-user opens the report multiple instances at same-time.
				$uniqueName = $this->tempTablePrefix .
						str_replace('.', '', uniqid($current_user->id, true)) . (self::$tempTableCounter++);

				$this->tempTables[$uniqueName] = array(
					'query' => $query,
					'keycolumns' => is_array($keyColumns) ? array_unique($keyColumns) : array($keyColumns),
					'module' => $module
				);
			}

			return $uniqueName;
		}
		return "($query)";
	}

	function initializeTempTables() {
		global $adb;

		$oldDieOnError = $adb->dieOnError;
		$adb->dieOnError = false; // If query planner is re-used there could be attempt for temp table...
		foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
			$reportConditions = $this->getReportConditions($tempTableInfo['module']);
			if ($tempTableInfo['module'] == 'Emails') {
				$query1 = sprintf('CREATE TEMPORARY TABLE %s AS %s', $uniqueName, $tempTableInfo['query']);
			} else {
				$query1 = sprintf('CREATE TEMPORARY TABLE %s AS %s %s', $uniqueName, $tempTableInfo['query'], $reportConditions);
			}
			$adb->pquery($query1, array());

			$keyColumns = $tempTableInfo['keycolumns'];
			foreach ($keyColumns as $keyColumn) {
				$query2 = sprintf('ALTER TABLE %s ADD INDEX (%s)', $uniqueName, $keyColumn);
				$adb->pquery($query2, array());
			}
		}

		$adb->dieOnError = $oldDieOnError;

		// Trigger cleanup of temporary tables when the execution of the request ends.
		// NOTE: This works better than having in __destruct
		// (as the reference to this object might end pre-maturely even before query is executed)
		if (!$this->registeredCleanup) {
			register_shutdown_function(array($this, 'cleanup'));
			// To avoid duplicate registration on this instance.
			$this->registeredCleanup = true;
		}
	}

	function cleanup() {
		global $adb;

		$oldDieOnError = $adb->dieOnError;
		$adb->dieOnError = false; // To avoid abnormal termination during shutdown...
		foreach ($this->tempTables as $uniqueName => $tempTableInfo) {
			$adb->pquery('DROP TABLE ' . $uniqueName, array());
		}
		$adb->dieOnError = $oldDieOnError;

		$this->tempTables = array();
	}

	/**
	 * Function to get report condition query for generating temporary table based on condition given on report.
	 * It generates condition query by considering fields of $module's base table or vtiger_crmentity table fields.
	 * It doesn't add condition for reference fields in query.
	 * @param String $module Module name for which temporary table is generated (Reports secondary module)
	 * @return string Returns condition query for generating temporary table.
	 */
	function getReportConditions($module) {
		$db = PearDatabase::getInstance();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$moduleBaseTable = $moduleModel->get('basetable');
		$reportId = $this->reportRun->reportid;
		if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'generate') {
			$advanceFilter = $_REQUEST['advanced_filter'];
			$advfilterlist = transformAdvFilterListToDBFormat(json_decode($advanceFilter, true));
		} else {
			$advfilterlist = $this->reportRun->getAdvFilterList($reportId);
		}
		$newAdvFilterList = array();
		$k = 0;

		foreach ($advfilterlist as $i => $columnConditions) {
			$conditionGroup = $advfilterlist[$i]['columns'];
			reset($conditionGroup);
			$firstConditionKey = key($conditionGroup);
			$oldColumnCondition = $advfilterlist[$i]['columns'][$firstConditionKey]['column_condition'];
			foreach ($columnConditions['columns'] as $j => $condition) {
				$columnName = $condition['columnname'];
				$columnParts = explode(':', $columnName);
				list($moduleName, $fieldLabel) = explode('_', $columnParts[2], 2);
				$fieldInfo = getFieldByReportLabel($moduleName, $columnParts[3], 'name');
				if(!empty($fieldInfo)) {
					$fieldInstance = WebserviceField::fromArray($db, $fieldInfo);
					$dataType = $fieldInstance->getFieldDataType();
					$uiType = $fieldInfo['uitype'];
					$fieldTable = $fieldInfo['tablename'];
					$allowedTables = array('vtiger_crmentity', $moduleBaseTable);
					$columnCondition = $advfilterlist[$i]['columns'][$j]['column_condition'];
					if (!in_array($fieldTable, $allowedTables) || $moduleName != $module || isReferenceUIType($uiType) || $columnCondition == 'or' || $oldColumnCondition == 'or' || in_array($dataType, array('reference', 'multireference'))) {
						$oldColumnCondition = $advfilterlist[$i]['columns'][$j]['column_condition'];
					} else {
						$columnParts[0] = $fieldTable;
						$newAdvFilterList[$i]['columns'][$k]['columnname'] = implode(':', $columnParts);
						$newAdvFilterList[$i]['columns'][$k]['comparator'] = $advfilterlist[$i]['columns'][$j]['comparator'];
						$newAdvFilterList[$i]['columns'][$k]['value'] = $advfilterlist[$i]['columns'][$j]['value'];
						$newAdvFilterList[$i]['columns'][$k++]['column_condition'] = $oldColumnCondition;
					}
				}
			}
			if (count($newAdvFilterList[$i])) {
				$newAdvFilterList[$i]['condition'] = $advfilterlist[$i]['condition'];
			}
			if (isset($newAdvFilterList[$i]['columns'][$k - 1])) {
				$newAdvFilterList[$i]['columns'][$k - 1]['column_condition'] = '';
			}
			if (count($newAdvFilterList[$i]) != 2) {
				unset($newAdvFilterList[$i]);
			}
		}
		end($newAdvFilterList);
		$lastConditionsGrpKey = key($newAdvFilterList);
		if (count($newAdvFilterList[$lastConditionsGrpKey])) {
			$newAdvFilterList[$lastConditionsGrpKey]['condition'] = '';
		}

		$advfiltersql = $this->reportRun->generateAdvFilterSql($newAdvFilterList);
		if ($advfiltersql && !empty($advfiltersql)) {
			$advfiltersql = ' AND ' . $advfiltersql;
		}
		return $advfiltersql;
	}

}

class ReportRun extends CRMEntity {

	// Maximum rows that should be emitted in HTML view.
	static $HTMLVIEW_MAX_ROWS = 1000;
	var $reportid;
	var $primarymodule;
	var $secondarymodule;
	var $orderbylistsql;
	var $orderbylistcolumns;
	var $selectcolumns;
	var $groupbylist;
	var $reporttype;
	var $reportname;
	var $totallist;
	var $_groupinglist = false;
    var $_groupbycondition = false;
    var $_reportquery = false;
    var $_tmptablesinitialized = false;
	var $_columnslist = false;
	var $_stdfilterlist = false;
	var $_columnstotallist = false;
	var $_advfiltersql = false;
	// All UItype 72 fields are added here so that in reports the values are append currencyId::value
	var $append_currency_symbol_to_value = array('Products_Unit_Price', 'Services_Price',
		'Invoice_Total', 'Invoice_Sub_Total', 'Invoice_Pre_Tax_Total', 'Invoice_S&H_Amount', 'Invoice_Discount_Amount', 'Invoice_Adjustment',
		'Quotes_Total', 'Quotes_Sub_Total', 'Quotes_Pre_Tax_Total', 'Quotes_S&H_Amount', 'Quotes_Discount_Amount', 'Quotes_Adjustment',
		'SalesOrder_Total', 'SalesOrder_Sub_Total', 'SalesOrder_Pre_Tax_Total', 'SalesOrder_S&H_Amount', 'SalesOrder_Discount_Amount', 'SalesOrder_Adjustment',
		'PurchaseOrder_Total', 'PurchaseOrder_Sub_Total', 'PurchaseOrder_Pre_Tax_Total', 'PurchaseOrder_S&H_Amount', 'PurchaseOrder_Discount_Amount', 'PurchaseOrder_Adjustment',
		'Invoice_Received', 'PurchaseOrder_Paid', 'Invoice_Balance', 'PurchaseOrder_Balance'
	);
	var $ui10_fields = array();
	var $ui101_fields = array();
	var $groupByTimeParent = array('Quarter' => array('Year'),
		'Month' => array('Year')
	);
	var $queryPlanner = null;
	protected static $instances = false;
	// Added to support line item fields calculation, if line item fields
	// are selected then module fields cannot be selected and vice versa
	var $lineItemFieldsInCalculation = false;

	/** Function to set reportid,primarymodule,secondarymodule,reporttype,reportname, for given reportid
	 *  This function accepts the $reportid as argument
	 *  It sets reportid,primarymodule,secondarymodule,reporttype,reportname for the given reportid
	 *  To ensure single-instance is present for $reportid
	 *  as we optimize using ReportRunPlanner and setup temporary tables.
	 */
	function ReportRun($reportid) {
		$oReport = new Reports($reportid);
		$this->reportid = $reportid;
		$this->primarymodule = $oReport->primodule;
		$this->secondarymodule = $oReport->secmodule;
		$this->reporttype = $oReport->reporttype;
		$this->reportname = $oReport->reportname;
		$this->queryPlanner = new ReportRunQueryPlanner();
		$this->queryPlanner->reportRun = $this;
	}

	public static function getInstance($reportid) {
		if (!isset(self::$instances[$reportid])) {
			self::$instances[$reportid] = new ReportRun($reportid);
		}
		return self::$instances[$reportid];
	}

	/** Function to get the columns for the reportid
	 *  This function accepts the $reportid and $outputformat (optional)
	 *  This function returns  $columnslist Array($tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname As Header value,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 As Header value,
	 * 					      					|
	 * 					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen As Header value
	 * 				      	     )
	 *
	 */
	function getQueryColumnsList($reportid, $outputformat = '') {
		// Have we initialized information already?
		if ($this->_columnslist !== false) {
			return $this->_columnslist;
		}

		global $adb;
		global $modules;
		global $log, $current_user, $current_language;
		$ssql = "select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid";
		$ssql .= " left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid";
		$ssql .= " where vtiger_report.reportid = ?";
		$ssql .= " order by vtiger_selectcolumn.columnindex";
		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = Array();

        $selectedModuleFields = array();
        require('user_privileges/user_privileges_'.$current_user->id.'.php');
		while ($columnslistrow = $adb->fetch_array($result)) {
			$fieldname = "";
			$fieldcolname = $columnslistrow["columnname"];
			list($tablename, $colname, $module_field, $fieldname, $single) = split(":", $fieldcolname);
			list($module, $field) = split("_", $module_field, 2);
            $selectedModuleFields[$module][] = $fieldname;
			$inventory_fields = array('serviceid');
			$inventory_modules = getInventoryModules();
			if (sizeof($permitted_fields[$module]) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
				$permitted_fields[$module] = $this->getaccesfield($module);
			}
			if (in_array($module, $inventory_modules)) {
				if (!empty($permitted_fields)) {
					foreach ($inventory_fields as $value) {
						array_push($permitted_fields[$module], $value);
					}
				}
			}
			$selectedfields = explode(":", $fieldcolname);
			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && !in_array($selectedfields[3], $permitted_fields[$module])) {
				//user has no access to this field, skip it.
				continue;
			}
			$querycolumns = $this->getEscapedColumns($selectedfields);
			if (isset($module) && $module != "") {
				$mod_strings = return_module_language($current_language, $module);
			}

			$targetTableName = $tablename;

			$fieldlabel = trim(preg_replace("/$module/", " ", $selectedfields[2], 1));
			$mod_arr = explode('_', $fieldlabel);
			$fieldlabel = trim(str_replace("_", " ", $fieldlabel));
			//modified code to support i18n issue
			$fld_arr = explode(" ", $fieldlabel);
			if (($mod_arr[0] == '')) {
				$mod = $module;
				$mod_lbl = getTranslatedString($module, $module); //module
			} else {
				$mod = $mod_arr[0];
				array_shift($fld_arr);
				$mod_lbl = getTranslatedString($fld_arr[0], $mod); //module
			}
			$fld_lbl_str = implode(" ", $fld_arr);
			$fld_lbl = getTranslatedString($fld_lbl_str, $module); //fieldlabel
			$fieldlabel = $mod_lbl . " " . $fld_lbl;
			if (($selectedfields[0] == "vtiger_usersRel1") && ($selectedfields[1] == 'user_name') && ($selectedfields[2] == 'Quotes_Inventory_Manager')) {
				$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
				$columnslist[$fieldcolname] = "trim( $concatSql ) as " . $module . "_Inventory_Manager";
				$this->queryPlanner->addTable($selectedfields[0]);
				continue;
			} 
			if ((CheckFieldPermission($fieldname, $mod) != 'true' && $colname != "crmid" && (!in_array($fieldname, $inventory_fields) && in_array($module, $inventory_modules))) || empty($fieldname)) {
				continue;
			} else {
				$this->labelMapping[$selectedfields[2]] = str_replace(" ", "_", $fieldlabel);

				// To check if the field in the report is a custom field
				// and if yes, get the label of this custom field freshly from the vtiger_field as it would have been changed.
				// Asha - Reference ticket : #4906

				if ($querycolumns == "") {
					$columnslist[$fieldcolname] = $this->getColumnSQL($selectedfields);
				} else {
					$columnslist[$fieldcolname] = $querycolumns;
				}

				$this->queryPlanner->addTable($targetTableName);
			}
		}

		if ($outputformat == "HTML" || $outputformat == "PDF" || $outputformat == "PRINT") {
			if($this->primarymodule == 'ModComments') {
				$columnslist['vtiger_modcomments:related_to:ModComments_Related_To_Id:related_to:V'] = "vtiger_modcomments.related_to AS '".$this->primarymodule."_LBL_ACTION'";
			} else {
				$columnslist['vtiger_crmentity:crmid:LBL_ACTION:crmid:I'] = 'vtiger_crmentity.crmid AS "' . $this->primarymodule . '_LBL_ACTION"';
			}
			if ($this->secondarymodule) {
				$secondaryModules = explode(':', $this->secondarymodule);
				foreach ($secondaryModules as $secondaryModule) {
                    $columnsSelected = (array)$selectedModuleFields[$secondaryModule];
					$moduleModel = Vtiger_Module_Model::getInstance($secondaryModule);
                    /**
                     * To check whether any column is selected from secondary module. If so, then only add 
                     * that module table to query planner
                     */
                    $moduleFields = $moduleModel->getFields();
                    $moduleFieldNames = array_keys($moduleFields);
                    $commonFields = array_intersect($moduleFieldNames, $columnsSelected);
                    if(count($commonFields) > 0){
						$baseTable = $moduleModel->get('basetable');
						$this->queryPlanner->addTable($baseTable);
						if ($secondaryModule == "Emails") {
							$baseTable .= "Emails";
						}
						$baseTableId = $moduleModel->get('basetableid');
						$columnslist[$baseTable . ":" . $baseTableId . ":" . $secondaryModule . ":" . $baseTableId . ":I"] = $baseTable . "." . $baseTableId . " AS " . $secondaryModule . "_LBL_ACTION";
					}
				}
			}
		}
		// Save the information
		$this->_columnslist = $columnslist;

		$log->info("ReportRun :: Successfully returned getQueryColumnsList" . $reportid);
		return $columnslist;
	}

	function getColumnSQL($selectedfields) {
		global $adb;
		$header_label = $selectedfields[2] = addslashes($selectedfields[2]); // Header label to be displayed in the reports table

		list($module, $field) = split("_", $selectedfields[2]);
		$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
		$emailTableName = "vtiger_activity";
		if ($module != $this->primarymodule) {
			$emailTableName .="Emails";
		}

		if ($selectedfields[0] == 'vtiger_inventoryproductrel') {

			if ($selectedfields[1] == 'discount_amount') {
				$columnSQL = "CASE WHEN (vtiger_inventoryproductreltmp{$module}.discount_amount != '') THEN vtiger_inventoryproductreltmp{$module}.discount_amount ELSE ROUND((vtiger_inventoryproductreltmp{$module}.listprice * vtiger_inventoryproductreltmp{$module}.quantity * (vtiger_inventoryproductreltmp{$module}.discount_percent/100)),3) END AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0].'tmp'.$module);
			} else if ($selectedfields[1] == 'productid') {
				$columnSQL = "CASE WHEN (vtiger_products{$module}.productname NOT LIKE '') THEN vtiger_products{$module}.productname ELSE vtiger_service{$module}.servicename END AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable("vtiger_products{$module}");
				$this->queryPlanner->addTable("vtiger_service{$module}");
			} else if ($selectedfields[1] == 'listprice') {
				$moduleInstance = CRMEntity::getInstance($module);
				$fieldName = $selectedfields[0] .'tmp'. $module . "." . $selectedfields[1];
				$columnSQL = "CASE WHEN vtiger_currency_info{$module}.id = vtiger_users{$module}.currency_id THEN $fieldName/vtiger_currency_info{$module}.conversion_rate ELSE $fieldName/$moduleInstance->table_name.conversion_rate END AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] .'tmp'. $module);
				$this->queryPlanner->addTable('vtiger_currency_info' . $module);
				$this->queryPlanner->addTable('vtiger_users' . $module);
			} else if(in_array($this->primarymodule, array('Products', 'Services'))) {
				$columnSQL = $selectedfields[0] . 'tmp' . $module . ".$selectedfields[1] AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] . $module);
			} else {
				if($selectedfields[0] == 'vtiger_inventoryproductrel'){
					$selectedfields[0] = $selectedfields[0]. 'tmp';
				}
				$columnSQL = $selectedfields[0] . $module . ".$selectedfields[1] AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] . $module);
			}
		} else if($selectedfields[0] == 'vtiger_pricebookproductrel'){
			if ($selectedfields[1] == 'listprice') {
				$listPriceFieldName = $selectedfields[0].'tmp'. $module . "." . $selectedfields[1];
				$currencyPriceFieldName = $selectedfields[0].'tmp'. $module . "." . 'usedcurrency';
				$columnSQL = 'CONCAT('.$currencyPriceFieldName.",'::',". $listPriceFieldName .")". " AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable($selectedfields[0] .'tmp'. $module);
			}
		} else if ($selectedfields[4] == 'C') {
			$field_label_data = split("_", $selectedfields[2]);
			$module = $field_label_data[0];
			if ($module != $this->primarymodule) {
				$columnSQL = "case when (" . $selectedfields[0] . "." . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity$module.crmid !='') then 'no' else '-' end end AS '" . decode_html($selectedfields[2]) . "'";
				$this->queryPlanner->addTable("vtiger_crmentity$module");
			} else {
				$columnSQL = "case when (" . $selectedfields[0] . "." . $selectedfields[1] . "='1')then 'yes' else case when (vtiger_crmentity.crmid !='') then 'no' else '-' end end AS '" . decode_html($selectedfields[2]) . "'";
				$this->queryPlanner->addTable($selectedfields[0]);
			}
		} elseif ($selectedfields[4] == 'D' || $selectedfields[4] == 'DT') {
			if ($selectedfields[5] == 'Y') {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					if ($module == 'Emails') {
						$columnSQL = "YEAR(cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE)) AS Emails_Date_Sent_Year";
					} else {
						$columnSQL = "YEAR(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME)) AS Calendar_Start_Date_and_Time_Year";
					}
				} else if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
					$columnSQL = "YEAR(vtiger_crmentity." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "_Year'";
				} else {
					$columnSQL = 'YEAR(' . $selectedfields[0] . "." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "_Year'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} elseif ($selectedfields[5] == 'M') {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					if ($module == 'Emails') {
						$columnSQL = "MONTHNAME(cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE)) AS Emails_Date_Sent_Month";
					} else {
						$columnSQL = "MONTHNAME(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME)) AS Calendar_Start_Date_and_Time_Month";
					}
				} else if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
					$columnSQL = "MONTHNAME(vtiger_crmentity." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "_Month'";
				} else {
					$columnSQL = 'MONTHNAME(' . $selectedfields[0] . "." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "_Month'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} elseif ($selectedfields[5] == 'W') {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					if ($module == 'Emails') {
						$columnSQL = "CONCAT('Week ',WEEK(cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE), 1)) AS Emails_Date_Sent_Week";
					} else {
						$columnSQL = "CONCAT('Week ',WEEK(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME), 1)) AS Calendar_Start_Date_and_Time_Week";
					}
				} else if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
					$columnSQL = "CONCAT('Week ',WEEK(vtiger_crmentity." . $selectedfields[1] . ", 1)) AS '" . decode_html($header_label) . "_Week'";
				} else {
					$columnSQL = "CONCAT('Week ',WEEK(" . $selectedfields[0] . "." . $selectedfields[1] . ", 1)) AS '" . decode_html($header_label) . "_Week'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} elseif ($selectedfields[5] == 'MY') { // used in charts to get the year also, which will be used for click throughs
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					if ($module == 'Emails') {
						$columnSQL = "date_format(cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE), '%M %Y') AS Emails_Date_Sent_Month";
					} else {
						$columnSQL = "date_format(cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME), '%M %Y') AS Calendar_Start_Date_and_Time_Month";
					}
				} else if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
					$columnSQL = "date_format(vtiger_crmentity." . $selectedfields[1] . ", '%M %Y') AS '" . decode_html($header_label) . "_Month'";
				} else {
					$columnSQL = 'date_format(' . $selectedfields[0] . "." . $selectedfields[1] . ", '%M %Y') AS '" . decode_html($header_label) . "_Month'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			} else {
				if ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
					if ($module == 'Emails') {
						$columnSQL = "cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE) AS Emails_Date_Sent";
					} else {
						$columnSQL = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME) AS Calendar_Start_Date_and_Time";
					}
				} else if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
					$columnSQL = "vtiger_crmentity." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
				} else {
					$columnSQL = $selectedfields[0] . "." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
				}
				$this->queryPlanner->addTable($selectedfields[0]);
			}
		} elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'status') {
			$columnSQL = " case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end AS Calendar_Status";
		} elseif ($selectedfields[0] == 'vtiger_activity' && $selectedfields[1] == 'date_start') {
			if ($module == 'Emails') {
				$columnSQL = "cast(concat($emailTableName.date_start,'  ',$emailTableName.time_start) as DATE) AS Emails_Date_Sent";
			} else {
				$columnSQL = "cast(concat(vtiger_activity.date_start,'  ',vtiger_activity.time_start) as DATETIME) AS Calendar_Start_Date_and_Time";
			}
		} elseif (stristr($selectedfields[0], "vtiger_users") && ($selectedfields[1] == 'user_name')) {
			$temp_module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
			if ($module != $this->primarymodule) {
				$condition = "and vtiger_crmentity" . $module . ".crmid!=''";
				$this->queryPlanner->addTable("vtiger_crmentity$module");
			} else {
				$condition = "and vtiger_crmentity.crmid!=''";
			}
			if ($temp_module_from_tablename == $module) {
				$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
				$columnSQL = " case when(" . $selectedfields[0] . ".last_name NOT LIKE '' $condition ) THEN " . $concatSql . " else vtiger_groups" . $module . ".groupname end AS '" . decode_html($header_label) . "'";
				$this->queryPlanner->addTable('vtiger_groups' . $module); // Auto-include the dependent module table.
			} else {//Some Fields can't assigned to groups so case avoided (fields like inventory manager)
				$columnSQL = $selectedfields[0] . ".user_name AS '" . decode_html($header_label) . "'";
			}
			$this->queryPlanner->addTable($selectedfields[0]);
		} elseif (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'modifiedby')) {
			$targetTableName = 'vtiger_lastModifiedBy' . $module;
			$concatSql = getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
			$columnSQL = "trim($concatSql) AS $header_label";
			$this->queryPlanner->addTable("vtiger_crmentity$module");
			$this->queryPlanner->addTable($targetTableName);

			// Added when no fields from the secondary module is selected but lastmodifiedby field is selected
			$moduleInstance = CRMEntity::getInstance($module);
			$this->queryPlanner->addTable($moduleInstance->table_name);
		} else if (stristr($selectedfields[0], "vtiger_crmentity") && ($selectedfields[1] == 'smcreatorid')) {
			$targetTableName = 'vtiger_createdby' . $module;
			$concatSql = getSqlForNameInDisplayFormat(array('last_name' => $targetTableName . '.last_name', 'first_name' => $targetTableName . '.first_name'), 'Users');
			$columnSQL = "trim($concatSql) AS " . decode_html($header_label) . "";
			$this->queryPlanner->addTable("vtiger_crmentity$module");
			$this->queryPlanner->addTable($targetTableName);

			// Added when no fields from the secondary module is selected but creator field is selected
			$moduleInstance = CRMEntity::getInstance($module);
			$this->queryPlanner->addTable($moduleInstance->table_name);
		} elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
			$columnSQL = "vtiger_crmentity." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
		} elseif ($selectedfields[0] == 'vtiger_products' && $selectedfields[1] == 'unit_price') {
			$columnSQL = "concat(" . $selectedfields[0] . ".currency_id,'::',innerProduct.actual_unit_price) AS '" . decode_html($header_label) . "'";
			$this->queryPlanner->addTable("innerProduct");
		} elseif (in_array(decode_html($selectedfields[2]), $this->append_currency_symbol_to_value)) {
			if ($selectedfields[1] == 'discount_amount') {
				$columnSQL = "CONCAT(" . $selectedfields[0] . ".currency_id,'::', IF(" . $selectedfields[0] . ".discount_amount != ''," . $selectedfields[0] . ".discount_amount, (" . $selectedfields[0] . ".discount_percent/100) * " . $selectedfields[0] . ".subtotal)) AS " . decode_html($header_label);
			} else {
				$columnSQL = "concat(" . $selectedfields[0] . ".currency_id,'::'," . $selectedfields[0] . "." . $selectedfields[1] . ") AS '" . decode_html($header_label) . "'";
			}
		} elseif ($selectedfields[0] == 'vtiger_notes' && ($selectedfields[1] == 'filelocationtype' || $selectedfields[1] == 'filesize' || $selectedfields[1] == 'folderid' || $selectedfields[1] == 'filestatus')) {
			if ($selectedfields[1] == 'filelocationtype') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when 'I' then 'Internal' when 'E' then 'External' else '-' end AS '" . decode_html($selectedfields[2]) . "'";
			} else if ($selectedfields[1] == 'folderid') {
				$columnSQL = "vtiger_attachmentsfolder.foldername AS '$selectedfields[2]'";
				$this->queryPlanner->addTable("vtiger_attachmentsfolder");
			} elseif ($selectedfields[1] == 'filestatus') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '1' then 'yes' when '0' then 'no' else '-' end AS '" . decode_html($selectedfields[2]) . "'";
			} elseif ($selectedfields[1] == 'filesize') {
				$columnSQL = "case " . $selectedfields[0] . "." . $selectedfields[1] . " when '' then '-' else concat(" . $selectedfields[0] . "." . $selectedfields[1] . "/1024,'  ','KB') end AS '" . decode_html($selectedfields[2]) . "'";
			}
		} else {
			$tableName = $selectedfields[0];
			if ($module != $this->primarymodule && $module == "Emails" && $tableName == "vtiger_activity") {
				$tableName = $emailTableName;
			}
			$columnSQL = $tableName . "." . $selectedfields[1] . " AS '" . decode_html($header_label) . "'";
			$this->queryPlanner->addTable($selectedfields[0]);
		}
		return $columnSQL;
	}

	/** Function to get field columns based on profile
	 *  @ param $module : Type string
	 *  returns permitted fields in array format
	 */
	function getaccesfield($module) {
		global $current_user;
		global $adb;
		$access_fields = Array();

		$profileList = getCurrentUserProfileList();
		$query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
		$params = array();
		if ($module == "Calendar") {
			if (count($profileList) > 0) {
				$query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
				array_push($params, $profileList);
			} else {
				$query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0
								and vtiger_field.presence IN (0,2) group by vtiger_field.fieldid order by block,sequence";
			}
		} else {
			array_push($params, $module);
			if (count($profileList) > 0) {
				$query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ") group by vtiger_field.fieldid order by block,sequence";
				array_push($params, $profileList);
			} else {
				$query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?)) and vtiger_field.displaytype in (1,2,3,5) and vtiger_profile2field.visible=0
								and vtiger_field.presence IN (0,2) and vtiger_def_org_field.visible=0 group by vtiger_field.fieldid order by block,sequence";
			}
		}
		$result = $adb->pquery($query, $params);

		while ($collistrow = $adb->fetch_array($result)) {
			$access_fields[] = $collistrow["fieldname"];
		}
		//added to include ticketid for Reports module in select columnlist for all users
		if ($module == "HelpDesk")
			$access_fields[] = "ticketid";
		return $access_fields;
	}

	/** Function to get Escapedcolumns for the field in case of multiple parents
	 *  @ param $selectedfields : Type Array
	 *  returns the case query for the escaped columns
	 */
	function getEscapedColumns($selectedfields) {

		$tableName = $selectedfields[0];
		$columnName = $selectedfields[1];
		$moduleFieldLabel = $selectedfields[2];
		$fieldName = $selectedfields[3];
		list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
		$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);

		if ($moduleName == 'ModComments' && $fieldName == 'creator') {
			$concatSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_usersModComments.first_name',
				'last_name' => 'vtiger_usersModComments.last_name'), 'Users');
			$queryColumn = "trim(case when (vtiger_usersModComments.user_name not like '' and vtiger_crmentity.crmid!='') then $concatSql end) AS ModComments_Creator";
			$this->queryPlanner->addTable('vtiger_usersModComments');
			$this->queryPlanner->addTable("vtiger_usersModComments");
		} elseif ((($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) && $fieldInfo['tablename'] != 'vtiger_inventoryproductrel') && $fieldInfo['uitype'] != '52' && $fieldInfo['uitype'] != '53') {
			$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
			if (count($fieldSqlColumns) > 0) {
				$queryColumn = "(CASE WHEN $tableName.$columnName NOT LIKE '' THEN (CASE";
				foreach ($fieldSqlColumns as $columnSql) {
					$queryColumn .= " WHEN $columnSql NOT LIKE '' THEN $columnSql";
				}
				$queryColumn .= " ELSE '' END) ELSE '' END) AS '".decode_html($moduleFieldLabel)."'";
				$this->queryPlanner->addTable($tableName);
			}
		}
		return $queryColumn;
	}

	/** Function to get selectedcolumns for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */
	function getSelectedColumnsList($reportid) {

		global $adb;
		global $modules;
		global $log;

		$ssql = "select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid";
		$ssql .= " left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid where vtiger_report.reportid = ? ";
		$ssql .= " order by vtiger_selectcolumn.columnindex";

		$result = $adb->pquery($ssql, array($reportid));
		$noofrows = $adb->num_rows($result);

		if ($this->orderbylistsql != "") {
			$sSQL .= $this->orderbylistsql . ", ";
		}

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$ordercolumnsequal = true;
			if ($fieldcolname != "") {
				for ($j = 0; $j < count($this->orderbylistcolumns); $j++) {
					if ($this->orderbylistcolumns[$j] == $fieldcolname) {
						$ordercolumnsequal = false;
						break;
					} else {
						$ordercolumnsequal = true;
					}
				}
				if ($ordercolumnsequal) {
					$selectedfields = explode(":", $fieldcolname);
					if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
						$selectedfields[0] = "vtiger_crmentity";
					$sSQLList[] = $selectedfields[0] . "." . $selectedfields[1] . " '" . $selectedfields[2] . "'";
				}
			}
		}
		$sSQL .= implode(",", $sSQLList);

		$log->info("ReportRun :: Successfully returned getSelectedColumnsList" . $reportid);
		return $sSQL;
	}

	/** Function to get advanced comparator in query form for the given Comparator and value
	 *  @ param $comparator : Type String
	 *  @ param $value : Type String
	 *  returns the check query for the comparator
	 */
	function getAdvComparator($comparator, $value, $datatype = "", $columnName = '') {

		global $log, $adb, $default_charset, $ogReport;
		$value = html_entity_decode(trim($value), ENT_QUOTES, $default_charset);
		$value_len = strlen($value);
		$is_field = false;
		if ($value_len > 1 && $value[0] == '$' && $value[$value_len - 1] == '$') {
			$temp = str_replace('$', '', $value);
			$is_field = true;
		}
		if ($datatype == 'C') {
			$value = str_replace("yes", "1", str_replace("no", "0", $value));
		}

		if ($is_field == true) {
			$value = $this->getFilterComparedField($temp);
		}
		if ($comparator == "e" || $comparator == 'y') {
			if (trim($value) == "NULL") {
				$rtvalue = " is NULL";
			} elseif (trim($value) != "") {
				$rtvalue = " = " . $adb->quote($value);
			} elseif (trim($value) == "" && $datatype == "V") {
				$rtvalue = " = " . $adb->quote($value);
			} else {
				$rtvalue = " is NULL";
			}
		}
		if ($comparator == "n" || $comparator == 'ny') {
			if (trim($value) == "NULL") {
				$rtvalue = " is NOT NULL";
			} elseif (trim($value) != "") {
				if ($columnName)
					$rtvalue = " <> " . $adb->quote($value) . " OR " . $columnName . " IS NULL ";
				else
					$rtvalue = " <> " . $adb->quote($value);
			}elseif (trim($value) == "" && $datatype == "V") {
				$rtvalue = " <> " . $adb->quote($value);
			} else {
				$rtvalue = " is NOT NULL";
			}
		}
		if ($comparator == "s") {
			$rtvalue = " like '" . formatForSqlLike($value, 2, $is_field) . "'";
		}
		if ($comparator == "ew") {
			$rtvalue = " like '" . formatForSqlLike($value, 1, $is_field) . "'";
		}
		if ($comparator == "c") {
			$rtvalue = " like '" . formatForSqlLike($value, 0, $is_field) . "'";
		}
		if ($comparator == "k") {
			$rtvalue = " not like '" . formatForSqlLike($value, 0, $is_field) . "'";
		}
		if ($comparator == "l") {
			$rtvalue = " < " . $adb->quote($value);
		}
		if ($comparator == "g") {
			$rtvalue = " > " . $adb->quote($value);
		}
		if ($comparator == "m") {
			$rtvalue = " <= " . $adb->quote($value);
		}
		if ($comparator == "h") {
			$rtvalue = " >= " . $adb->quote($value);
		}
		if ($comparator == "b") {
			$rtvalue = " < " . $adb->quote($value);
		}
		if ($comparator == "a") {
			$rtvalue = " > " . $adb->quote($value);
		}
		if ($is_field == true) {
			$rtvalue = str_replace("'", "", $rtvalue);
			$rtvalue = str_replace("\\", "", $rtvalue);
		}
		$log->info("ReportRun :: Successfully returned getAdvComparator");
		return $rtvalue;
	}

	/** Function to get field that is to be compared in query form for the given Comparator and field
	 *  @ param $field : field
	 *  returns the value for the comparator
	 */
	function getFilterComparedField($field) {
		global $adb, $ogReport;
		if (!empty($this->secondarymodule)) {
			$secModules = explode(':', $this->secondarymodule);
			foreach ($secModules as $secModule) {
				$secondary = CRMEntity::getInstance($secModule);
				$this->queryPlanner->addTable($secondary->table_name);
			}
		}
		$field = split('#', $field);
		$module = $field[0];
		$fieldname = trim($field[1]);
		$tabid = getTabId($module);
		$field_query = $adb->pquery("SELECT tablename,columnname,typeofdata,fieldname,uitype FROM vtiger_field WHERE tabid = ? AND fieldname= ?", array($tabid, $fieldname));
		$fieldtablename = $adb->query_result($field_query, 0, 'tablename');
		$fieldcolname = $adb->query_result($field_query, 0, 'columnname');
		$typeofdata = $adb->query_result($field_query, 0, 'typeofdata');
		$fieldtypeofdata = ChangeTypeOfData_Filter($fieldtablename, $fieldcolname, $typeofdata[0]);
		$uitype = $adb->query_result($field_query, 0, 'uitype');
		/* if($tr[0]==$ogReport->primodule)
		  $value = $adb->query_result($field_query,0,'tablename').".".$adb->query_result($field_query,0,'columnname');
		  else
		  $value = $adb->query_result($field_query,0,'tablename').$tr[0].".".$adb->query_result($field_query,0,'columnname');
		 */
		if ($uitype == 68 || $uitype == 59) {
			$fieldtypeofdata = 'V';
		}
		if ($fieldtablename == "vtiger_crmentity" && $module != $this->primarymodule) {
			$fieldtablename = $fieldtablename . $module;
		}
		if ($fieldname == "assigned_user_id") {
			$fieldtablename = "vtiger_users" . $module;
			$fieldcolname = "user_name";
		}
		if ($fieldtablename == "vtiger_crmentity" && $fieldname == "modifiedby") {
			$fieldtablename = "vtiger_lastModifiedBy" . $module;
			$fieldcolname = "user_name";
		}
		if ($fieldname == "assigned_user_id1") {
			$fieldtablename = "vtiger_usersRel1";
			$fieldcolname = "user_name";
		}

		$value = $fieldtablename . "." . $fieldcolname;

		$this->queryPlanner->addTable($fieldtablename);
		return $value;
	}

	/** Function to get the advanced filter columns for the reportid
	 *  This function accepts the $reportid
	 *  This function returns  $columnslist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 * 					      					|
	 * 					      $tablenamen:$columnnamen:$fieldlabeln:$fieldnamen:$typeofdatan=>$tablenamen.$columnnamen filtercriteria
	 * 				      	     )
	 *
	 */
	function getAdvFilterList($reportid, $forClickThrough = false) {
		global $adb, $log;

		$advft_criteria = array();

		// Not a good approach to get all the fields if not required(May leads to Performance issue)
		$sql = 'SELECT groupid,group_condition FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($reportid));

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_relcriteria.* from vtiger_report
						inner join vtiger_relcriteria on vtiger_relcriteria.queryid = vtiger_report.queryid
						left join vtiger_relcriteria_grouping on vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								and vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql.= " where vtiger_report.reportid = ? AND vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex";

			$result = $adb->pquery($ssql, array($reportid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $adb->fetch_array($result)) {
				$columnIndex = $relcriteriarow["columnindex"];
				$criteria = array();
				$criteria['columnname'] = html_entity_decode($relcriteriarow["columnname"]);
				$criteria['comparator'] = $relcriteriarow["comparator"];
				$advfilterval = $relcriteriarow["value"];
				$col = explode(":",$relcriteriarow["columnname"]);
				$criteria['value'] = $advfilterval;
				$criteria['column_condition'] = $relcriteriarow["column_condition"];

				$advft_criteria[$i]['columns'][$j] = $criteria;
				$advft_criteria[$i]['condition'] = $groupCondition;
				$j++;

				$this->queryPlanner->addTable($col[0]);
			}
			if (!empty($advft_criteria[$i]['columns'][$j - 1]['column_condition'])) {
				$advft_criteria[$i]['columns'][$j - 1]['column_condition'] = '';
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';
		return $advft_criteria;
	}

	function generateAdvFilterSql($advfilterlist) {

		global $adb;

		$advfiltersql = "";
		$customView = new CustomView();
		$dateSpecificConditions = $customView->getStdFilterConditions();
		$specialDateComparators = array('yesterday', 'today', 'tomorrow');
		foreach ($advfilterlist as $groupindex => $groupinfo) {
			$groupcondition = $groupinfo['condition'];
			$groupcolumns = $groupinfo['columns'];

			if (count($groupcolumns) > 0) {

				$advfiltergroupsql = "";
				foreach ($groupcolumns as $columnindex => $columninfo) {
					$fieldcolname = $columninfo["columnname"];
					$comparator = $columninfo["comparator"];
					$value = $columninfo["value"];
					$columncondition = $columninfo["column_condition"];
					$advcolsql = array();

					$selectedFields = explode(':', $fieldcolname);
					$moduleFieldLabel = $selectedFields[2];
					list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
					$emailTableName = '';
					if ($moduleName == "Emails" && $moduleName != $this->primarymodule && $selectedFields[0] == "vtiger_activity") {
						$emailTableName = "vtiger_activityEmails";
					}

					if ($fieldcolname != "" && $comparator != "") {
						if (in_array($comparator, $dateSpecificConditions)) {
							if ($fieldcolname != 'none') {
								$selectedFields = explode(':', $fieldcolname);
								if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
									$selectedFields[0] = 'vtiger_crmentity';
								}

								if ($comparator != 'custom') {
									list($startDate, $endDate) = $this->getStandarFiltersStartAndEndDate($comparator);
								} else {
									list($startDateTime, $endDateTime) = explode(',', $value);
									list($startDate, $startTime) = explode(' ', $startDateTime);
									list($endDate, $endTime) = explode(' ', $endDateTime);
								}

								$type = $selectedFields[4];
								if ($startDate != '0000-00-00' && $endDate != '0000-00-00' && $startDate != '' && $endDate != '') {
									if ($type == 'DT') {
										$startDateTime = new DateTimeField($startDate . ' ' . date('H:i:s'));
										$endDateTime = new DateTimeField($endDate . ' ' . date('H:i:s'));
										$userStartDate = $startDateTime->getDisplayDate() . ' 00:00:00';
										$userEndDate = $endDateTime->getDisplayDate() . ' 23:59:59';
									} else if (in_array($comparator, $specialDateComparators)) {
										$startDateTime = new DateTimeField($startDate . ' ' . date('H:i:s'));
										$endDateTime = new DateTimeField($endDate . ' ' . date('H:i:s'));
										$userStartDate = $startDateTime->getDisplayDate();
										$userEndDate = $endDateTime->getDisplayDate();
									} else {
										$startDateTime = new DateTimeField($startDate);
										$endDateTime = new DateTimeField($endDate);
										$userStartDate = $startDateTime->getDisplayDate();
										$userEndDate = $endDateTime->getDisplayDate();
									}
									$startDateTime = getValidDBInsertDateTimeValue($userStartDate);
									$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

									if ($selectedFields[1] == 'birthday') {
										$tableColumnSql = 'DATE_FORMAT(' . $selectedFields[0] . '.' . $selectedFields[1] . ', "%m%d")';
										$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
										$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
									} else {
										if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
											$tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
										} else {
											if (empty($emailTableName)) {
												$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
											} else {
												$tableColumnSql = $emailTableName . '.' . $selectedFields[1];
											}
										}
										$startDateTime = "'$startDateTime'";
										$endDateTime = "'$endDateTime'";
									}

									$advfiltergroupsql .= "$tableColumnSql BETWEEN $startDateTime AND $endDateTime";
									if (!empty($columncondition)) {
										$advfiltergroupsql .= ' ' . $columncondition . ' ';
									}

									$this->queryPlanner->addTable($selectedFields[0]);
								}
							}
							continue;
						}
						$selectedFields = explode(":", $fieldcolname);
						$tempComparators = array('e', 'n', 'bw', 'a', 'b');
						$tempComparators = array_merge($tempComparators, Vtiger_Functions::getSpecialDateTimeCondtions());
						if ($selectedFields[4] == 'DT' && in_array($comparator, $tempComparators)) {
							if ($selectedFields[0] == 'vtiger_crmentity' . $this->primarymodule) {
								$selectedFields[0] = 'vtiger_crmentity';
							}

							if ($selectedFields[0] == 'vtiger_activity' && ($selectedFields[1] == 'date_start')) {
								$tableColumnSql = 'CAST((CONCAT(date_start, " ", time_start)) AS DATETIME)';
							} else {
								if (empty($emailTableName)) {
									$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
								} else {
									$tableColumnSql = $emailTableName . '.' . $selectedFields[1];
								}
							}

							if ($value != null && $value != '') {
								if ($comparator == 'e' || $comparator == 'n') {
									$dateTimeComponents = explode(' ', $value);
									$dateTime = new DateTime($dateTimeComponents[0] . ' ' . '00:00:00');
									$date1 = $dateTime->format('Y-m-d H:i:s');
									$dateTime->modify("+1 days");
									$date2 = $dateTime->format('Y-m-d H:i:s');
									$tempDate = strtotime($date2) - 1;
									$date2 = date('Y-m-d H:i:s', $tempDate);

									$start = getValidDBInsertDateTimeValue($date1);
									$end = getValidDBInsertDateTimeValue($date2);
									$start = "'$start'";
									$end = "'$end'";
									if ($comparator == 'e')
										$advfiltergroupsql .= "$tableColumnSql BETWEEN $start AND $end";
									else
										$advfiltergroupsql .= "$tableColumnSql NOT BETWEEN $start AND $end";
								}else if ($comparator == 'bw') {
									$values = explode(',', $value);
									$startDateTime = explode(' ', $values[0]);
									$endDateTime = explode(' ', $values[1]);

									$startDateTime = new DateTimeField($startDateTime[0] . ' ' . date('H:i:s'));
									$userStartDate = $startDateTime->getDisplayDate();
									$userStartDate = $userStartDate . ' 00:00:00';
									$start = getValidDBInsertDateTimeValue($userStartDate);

									$endDateTime = new DateTimeField($endDateTime[0] . ' ' . date('H:i:s'));
									$userEndDate = $endDateTime->getDisplayDate();
									$userEndDate = $userEndDate . ' 23:59:59';
									$end = getValidDBInsertDateTimeValue($userEndDate);

									$advfiltergroupsql .= "$tableColumnSql BETWEEN '$start' AND '$end'";
								} else if (in_array($comparator, Vtiger_Functions::getSpecialDateConditions())) {
									$values = EnhancedQueryGenerator::getSpecialDateConditionValue($comparator, $value, $selectedFields[4]);
									$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
									$condtionQuery = EnhancedQueryGenerator::getSpecialDateConditionQuery($values['comparator'], $values['date']);
									$advfiltergroupsql .= "date($tableColumnSql) $condtionQuery";
								} else if (in_array($comparator, Vtiger_Functions::getSpecialTimeConditions())) {
									$values = EnhancedQueryGenerator::getSpecialDateConditionValue($comparator, $value, $selectedFields[4]);
									$condtionQuery = EnhancedQueryGenerator::getSpecialDateConditionQuery($values['comparator'], $values['date']);
									$advfiltergroupsql .= "$tableColumnSql $condtionQuery";
								} else if ($comparator == 'a' || $comparator == 'b') {
									$value = explode(' ', $value);
									$dateTime = new DateTime($value[0]);
									if ($comparator == 'a') {
										$modifiedDate = $dateTime->modify('+1 days');
										$nextday = $modifiedDate->format('Y-m-d H:i:s');
										$temp = strtotime($nextday) - 1;
										$date = date('Y-m-d H:i:s', $temp);
										$value = getValidDBInsertDateTimeValue($date);
										$advfiltergroupsql .= "$tableColumnSql > '$value'";
									} else {
										$prevday = $dateTime->format('Y-m-d H:i:s');
										$temp = strtotime($prevday) - 1;
										$date = date('Y-m-d H:i:s', $temp);
										$value = getValidDBInsertDateTimeValue($date);
										$advfiltergroupsql .= "$tableColumnSql < '$value'";
									}
								}
								if (!empty($columncondition)) {
									$advfiltergroupsql .= ' ' . $columncondition . ' ';
								}
								$this->queryPlanner->addTable($selectedFields[0]);
							} else if ($value == '') {
								$sqlComparator = $this->getAdvComparator($comparator, $value, 'DT');
								if($sqlComparator) {
									$advfiltergroupsql .= " ".$selectedFields[0].".".$selectedFields[1].$sqlComparator;
								} else {
									$advfiltergroupsql .= " " . $selectedFields[0] . "." . $selectedFields[1] . " = '' ";
								}
							}
							continue;
						}

						$selectedfields = explode(":", $fieldcolname);
						$moduleFieldLabel = $selectedfields[2];
						list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
						$fieldInfo = getFieldByReportLabel($moduleName, $selectedfields[3], 'name');
						$concatSql = getSqlForNameInDisplayFormat(array('first_name' => $selectedfields[0] . ".first_name", 'last_name' => $selectedfields[0] . ".last_name"), 'Users');
						// Added to handle the crmentity table name for Primary module
						if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
							$selectedfields[0] = "vtiger_crmentity";
						}
						//Added to handle yes or no for checkbox  field in reports advance filters. -shahul
						if ($selectedfields[4] == 'C') {
							if (strcasecmp(trim($value), "yes") == 0)
								$value = "1";
							if (strcasecmp(trim($value), "no") == 0)
								$value = "0";
						}
						if (in_array($comparator, $dateSpecificConditions)) {
							$customView = new CustomView($moduleName);
							$columninfo['stdfilter'] = $columninfo['comparator'];
							$valueComponents = explode(',', $columninfo['value']);
							if ($comparator == 'custom') {
								if ($selectedfields[4] == 'DT') {
									$startDateTimeComponents = explode(' ', $valueComponents[0]);
									$endDateTimeComponents = explode(' ', $valueComponents[1]);
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
								} else {
									$columninfo['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
									$columninfo['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
								}
							}
							$dateFilterResolvedList = $customView->resolveDateFilterValue($columninfo);
							$startDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['startdate']);
							$endDate = DateTimeField::convertToDBFormat($dateFilterResolvedList['enddate']);
							$columninfo['value'] = $value = implode(',', array($startDate, $endDate));
							$comparator = 'bw';
						}
						$datatype = (isset($selectedfields[4])) ? $selectedfields[4] : "";
						$fieldDataType = '';

						$fields = array();
						$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
						if ($moduleModel) {
							$fields = $moduleModel->getFields();
							if ($fields && $selectedfields[3]) {
								$fieldModel = $fields[$selectedfields[3]];
								if ($fieldModel) {
									$fieldDataType = $fieldModel->getFieldDataType();
								}
							}
						}
						$commaSeparatedFieldTypes = array('picklist', 'multipicklist', 'owner', 'date', 'datetime', 'time');
						if(in_array($fieldDataType, $commaSeparatedFieldTypes)) {
							$valuearray = explode(",", trim($value));
						} else {
							$valuearray = array($value);
						}
						if (isset($valuearray) && count($valuearray) > 1 && $comparator != 'bw') {

							$advcolumnsql = "";
							for ($n = 0; $n < count($valuearray); $n++) {
								$secondaryModules = explode(':', $this->secondarymodule);
								$firstSecondaryModule = $secondaryModules[0];
								$secondSecondaryModule = $secondaryModules[1]; 
								if (($selectedfields[0] == "vtiger_users" . $this->primarymodule || ($firstSecondaryModule && $selectedfields[0] == "vtiger_users".$firstSecondaryModule) || ($secondSecondaryModule && $selectedfields[0] == "vtiger_users".$secondSecondaryModule)) && $selectedfields[1] == 'user_name') {
									$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
									$advcolsql[] = " (trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . " or vtiger_groups" . $module_from_tablename . ".groupname " . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype) . ")";
									$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
								} elseif ($selectedfields[1] == 'status') {//when you use comma seperated values.
									if ($selectedfields[2] == 'Calendar_Status') {
										$advcolsql[] = "(case when (vtiger_activity.status not like '') then vtiger_activity.status else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else if ($selectedfields[2] == 'HelpDesk_Status') {
										$advcolsql[] = "vtiger_troubletickets.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else if ($selectedfields[2] == 'Faq_Status') {
										$advcolsql[] = "vtiger_faq.status" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									} else
										$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} elseif ($selectedfields[1] == 'description') {//when you use comma seperated values.
									if ($selectedfields[0] == 'vtiger_crmentity' . $this->primarymodule)
										$advcolsql[] = "vtiger_crmentity.description" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
									else
										$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
									$advcolsql[] = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype));
								} elseif ($selectedfields[1] == 'modifiedby') {
									$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
									if ($module_from_tablename != '') {
										$tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
									} else {
										$tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
									}
									$advcolsql[] = 'trim(' . getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
											$this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} elseif ($selectedfields[1] == 'smcreatorid') {
									$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
									if ($module_from_tablename != '') {
										$tableName = 'vtiger_createdby' . $module_from_tablename;
									} else {
										$tableName = 'vtiger_createdby' . $this->primarymodule;
									}
									if ($moduleName == 'ModComments') {
										$tableName = 'vtiger_users' . $moduleName;
									}
									$this->queryPlanner->addTable($tableName);
									$advcolsql[] = 'trim(' . getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
											$this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								} else {
									$advcolsql[] = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($valuearray[$n]), $datatype);
								}
							}
							//If negative logic filter ('not equal to', 'does not contain') is used, 'and' condition should be applied instead of 'or'
							if ($comparator == 'n' || $comparator == 'k')
								$advcolumnsql = implode(" and ", $advcolsql);
							else
								$advcolumnsql = implode(" or ", $advcolsql);
							$fieldvalue = " (" . $advcolumnsql . ") ";
						} elseif ($selectedfields[1] == 'user_name') {
							if ($selectedfields[0] == "vtiger_users" . $this->primarymodule) {
								$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
								$fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
								$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
							} else {
								$secondaryModules = explode(':', $this->secondarymodule);
								$firstSecondaryModule = "vtiger_users" . $secondaryModules[0];
								$secondSecondaryModule = "vtiger_users" . $secondaryModules[1];
								if (($firstSecondaryModule && $firstSecondaryModule == $selectedfields[0]) || ($secondSecondaryModule && $secondSecondaryModule == $selectedfields[0])) {
									$module_from_tablename = str_replace("vtiger_users", "", $selectedfields[0]);
									$moduleInstance = CRMEntity::getInstance($module_from_tablename);
									$fieldvalue = " trim(case when (" . $selectedfields[0] . ".last_name NOT LIKE '') then " . $concatSql . " else vtiger_groups" . $module_from_tablename . ".groupname end) " . $this->getAdvComparator($comparator, trim($value), $datatype);
									$this->queryPlanner->addTable("vtiger_groups" . $module_from_tablename);
									$this->queryPlanner->addTable($moduleInstance->table_name);
								}
							}
						} elseif ($comparator == 'bw' && count($valuearray) == 2) {
							if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
								$fieldvalue = "(" . "vtiger_crmentity." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
							} else {
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " between '" . trim($valuearray[0]) . "' and '" . trim($valuearray[1]) . "')";
							}
						} elseif ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule) {
							$fieldvalue = "vtiger_crmentity." . $selectedfields[1] . " " . $this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($selectedfields[2] == 'Quotes_Inventory_Manager') {
							$fieldvalue = ("trim($concatSql)" . $this->getAdvComparator($comparator, trim($value), $datatype));
						} elseif ($selectedfields[1] == 'modifiedby') {
							$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
							if ($module_from_tablename != '') {
								$tableName = 'vtiger_lastModifiedBy' . $module_from_tablename;
							} else {
								$tableName = 'vtiger_lastModifiedBy' . $this->primarymodule;
							}
							$this->queryPlanner->addTable($tableName);
							$fieldvalue = 'trim(' . getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
									$this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($selectedfields[1] == 'smcreatorid') {
							$module_from_tablename = str_replace("vtiger_crmentity", "", $selectedfields[0]);
							if ($module_from_tablename != '') {
								$tableName = 'vtiger_createdby' . $module_from_tablename;
							} else {
								$tableName = 'vtiger_createdby' . $this->primarymodule;
							}
							if ($moduleName == 'ModComments') {
								$tableName = 'vtiger_users' . $moduleName;
							}
							$this->queryPlanner->addTable($tableName);
							$fieldvalue = 'trim(' . getSqlForNameInDisplayFormat(array('last_name' => "$tableName.last_name", 'first_name' => "$tableName.first_name"), 'Users') . ')' .
									$this->getAdvComparator($comparator, trim($value), $datatype);
						} elseif ($selectedfields[0] == "vtiger_activity" && ($selectedfields[1] == 'status' || $selectedfields[1] == 'eventstatus')) {
							// for "Is Empty" condition we need to check with "value NOT NULL" OR "value = ''" conditions
							if ($comparator == 'y') {
								$fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status
                                                else vtiger_activity.eventstatus end) IS NULL OR (case when (vtiger_activity.status not like '')
                                                then vtiger_activity.status else vtiger_activity.eventstatus end) = ''";
							} else {
								$fieldvalue = "(case when (vtiger_activity.status not like '') then vtiger_activity.status
                                                else vtiger_activity.eventstatus end)" . $this->getAdvComparator($comparator, trim($value), $datatype);
							}
						} else if ($comparator == 'ny') {
							if ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NOT NULL AND " . $selectedfields[0] . "." . $selectedfields[1] . " != '' AND " . $selectedfields[0] . "." . $selectedfields[1] . "  != '0')";
							else
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NOT NULL AND " . $selectedfields[0] . "." . $selectedfields[1] . " != '')";
						}elseif ($comparator == 'y' || ($comparator == 'e' && (trim($value) == "NULL" || trim($value) == ''))) {
							if ($selectedfields[0] == 'vtiger_inventoryproductrel') {
								$selectedfields[0] = 'vtiger_inventoryproductreltmp' . $moduleName;
							}
							if ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype']))
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '' OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '0')";
							else
								$fieldvalue = "(" . $selectedfields[0] . "." . $selectedfields[1] . " IS NULL OR " . $selectedfields[0] . "." . $selectedfields[1] . " = '')";
						} elseif ($selectedfields[0] == 'vtiger_inventoryproductrel') {
							$selectedfields[0] = $selectedfields[0]. 'tmp';
							if ($selectedfields[1] == 'productid') {
								$fieldvalue = "(vtiger_products$moduleName.productname " . $this->getAdvComparator($comparator, trim($value), $datatype);
								$fieldvalue .= " OR vtiger_service$moduleName.servicename " . $this->getAdvComparator($comparator, trim($value), $datatype);
								$fieldvalue .= ")";
								$this->queryPlanner->addTable("vtiger_products$moduleName");
								$this->queryPlanner->addTable("vtiger_service$moduleName");
							} else {
								//for inventory module table should be follwed by the module name
								$selectedfields[0] = 'vtiger_inventoryproductreltmp' . $moduleName;
								$fieldvalue = $selectedfields[0] . "." . $selectedfields[1] . $this->getAdvComparator($comparator, $value, $datatype);
							}
						} elseif ($fieldInfo['uitype'] == '10' || isReferenceUIType($fieldInfo['uitype'])) {

							$fieldSqlColumns = $this->getReferenceFieldColumnList($moduleName, $fieldInfo);
							$comparatorValue = $this->getAdvComparator($comparator, trim($value), $datatype, $fieldSqlColumns[0]);
							$fieldSqls = array();

							foreach ($fieldSqlColumns as $columnSql) {
								$fieldSqls[] = $columnSql . $comparatorValue;
							}
							$fieldvalue = ' (' . implode(' OR ', $fieldSqls) . ') ';
						} else if (in_array($comparator, Vtiger_Functions::getSpecialDateConditions())) {
							$values = EnhancedQueryGenerator::getSpecialDateConditionValue($comparator, $value, $selectedFields[4]);
							$tableColumnSql = $selectedFields[0] . '.' . $selectedFields[1];
							$condtionQuery = EnhancedQueryGenerator::getSpecialDateConditionQuery($values['comparator'], $values['date']);
							$fieldvalue = "date($tableColumnSql) $condtionQuery";
						} else if (in_array($comparator, Vtiger_Functions::getSpecialTimeConditions())) {
							$values = EnhancedQueryGenerator::getSpecialDateConditionValue($comparator, $value, $selectedFields[4]);
							$condtionQuery = EnhancedQueryGenerator::getSpecialDateConditionQuery($values['comparator'], $values['date']);
							$fieldvalue = "$tableColumnSql $condtionQuery";
						} else {
							$selectFieldTableName = $selectedfields[0];
							if (!empty($emailTableName)) {
								$selectFieldTableName = $emailTableName;
							}
							$fieldvalue = $selectFieldTableName . "." . $selectedfields[1] . $this->getAdvComparator($comparator, trim($value), $datatype);
						}
						$advfiltergroupsql .= $fieldvalue;
						if (!empty($columncondition)) {
							$advfiltergroupsql .= ' ' . $columncondition . ' ';
						}

						$this->queryPlanner->addTable($selectedfields[0]);
					}
				}
				if (trim($advfiltergroupsql) != "") {
					$advfiltergroupsql = "( $advfiltergroupsql ) ";
					if (!empty($groupcondition)) {
						$advfiltergroupsql .= ' ' . $groupcondition . ' ';
					}

					$advfiltersql .= $advfiltergroupsql;
				}
			}
		}
		if (trim($advfiltersql) != "")
			$advfiltersql = '(' . $advfiltersql . ')';

		return $advfiltersql;
	}

	function getAdvFilterSql($reportid) {
		// Have we initialized information already?
		if ($this->_advfiltersql !== false) {
			return $this->_advfiltersql;
		}
		global $log;

		$advfilterlist = $this->getAdvFilterList($reportid);
		$advfiltersql = $this->generateAdvFilterSql($advfilterlist);

		// Save the information
		$this->_advfiltersql = $advfiltersql;

		$log->info("ReportRun :: Successfully returned getAdvFilterSql" . $reportid);
		return $advfiltersql;
	}

	/** Function to get the Standard filter columns for the reportid
	 *  This function accepts the $reportid datatype Integer
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel:$fieldname:$typeofdata=>$tablename.$columnname filtercriteria,
	 * 					      $tablename1:$columnname1:$fieldlabel1:$fieldname1:$typeofdata1=>$tablename1.$columnname1 filtercriteria,
	 * 				      	     )
	 *
	 */
	function getStdFilterList($reportid) {
		// Have we initialized information already?
		if ($this->_stdfilterlist !== false) {
			return $this->_stdfilterlist;
		}

		global $adb, $log;
		$stdfilterlist = array();

		$stdfiltersql = "select vtiger_reportdatefilter.* from vtiger_report";
		$stdfiltersql .= " inner join vtiger_reportdatefilter on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid";
		$stdfiltersql .= " where vtiger_report.reportid = ?";

		$result = $adb->pquery($stdfiltersql, array($reportid));
		$stdfilterrow = $adb->fetch_array($result);
		if (isset($stdfilterrow)) {
			$fieldcolname = $stdfilterrow["datecolumnname"];
			$datefilter = $stdfilterrow["datefilter"];
			$startdate = $stdfilterrow["startdate"];
			$enddate = $stdfilterrow["enddate"];

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";

				$moduleFieldLabel = $selectedfields[3];
				list($moduleName, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($moduleName, $fieldLabel);
				$typeOfData = $fieldInfo['typeofdata'];
				list($type, $typeOtherInfo) = explode('~', $typeOfData, 2);

				if ($datefilter != "custom") {
					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);
					$startdate = $startenddate[0];
					$enddate = $startenddate[1];
				}

				if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $startdate != "" && $enddate != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {

					$startDateTime = new DateTimeField($startdate . ' ' . date('H:i:s'));
					$userStartDate = $startDateTime->getDisplayDate();
					if ($type == 'DT') {
						$userStartDate = $userStartDate . ' 00:00:00';
					}
					$startDateTime = getValidDBInsertDateTimeValue($userStartDate);

					$endDateTime = new DateTimeField($enddate . ' ' . date('H:i:s'));
					$userEndDate = $endDateTime->getDisplayDate();
					if ($type == 'DT') {
						$userEndDate = $userEndDate . ' 23:59:00';
					}
					$endDateTime = getValidDBInsertDateTimeValue($userEndDate);

					if ($selectedfields[1] == 'birthday') {
						$tableColumnSql = "DATE_FORMAT(" . $selectedfields[0] . "." . $selectedfields[1] . ", '%m%d')";
						$startDateTime = "DATE_FORMAT('$startDateTime', '%m%d')";
						$endDateTime = "DATE_FORMAT('$endDateTime', '%m%d')";
					} else {
						if ($selectedfields[0] == 'vtiger_activity' && ($selectedfields[1] == 'date_start')) {
							$tableColumnSql = '';
							$tableColumnSql = "CAST((CONCAT(date_start,' ',time_start)) AS DATETIME)";
						} else {
							$tableColumnSql = $selectedfields[0] . "." . $selectedfields[1];
						}
						$startDateTime = "'$startDateTime'";
						$endDateTime = "'$endDateTime'";
					}

					$stdfilterlist[$fieldcolname] = $tableColumnSql . " between " . $startDateTime . " and " . $endDateTime;
					$this->queryPlanner->addTable($selectedfields[0]);
				}
			}
		}
		// Save the information
		$this->_stdfilterlist = $stdfilterlist;

		$log->info("ReportRun :: Successfully returned getStdFilterList" . $reportid);
		return $stdfilterlist;
	}

	/** Function to get the RunTime filter columns for the given $filtercolumn,$filter,$startdate,$enddate
	 *  @ param $filtercolumn : Type String
	 *  @ param $filter : Type String
	 *  @ param $startdate: Type String
	 *  @ param $enddate : Type String
	 *  This function returns  $stdfilterlist Array($columnname => $tablename:$columnname:$fieldlabel=>$tablename.$columnname 'between' $startdate 'and' $enddate)
	 *
	 */
	function RunTimeFilter($filtercolumn, $filter, $startdate, $enddate) {
		if ($filtercolumn != "none") {
			$selectedfields = explode(":", $filtercolumn);
			if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
				$selectedfields[0] = "vtiger_crmentity";
			if ($filter == "custom") {
				if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $startdate != "" &&
						$enddate != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
					$stdfilterlist[$filtercolumn] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . " 00:00:00' and '" . $enddate . " 23:59:00'";
				}
			} else {
				if ($startdate != "" && $enddate != "") {
					$startenddate = $this->getStandarFiltersStartAndEndDate($filter);
					if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
						$stdfilterlist[$filtercolumn] = $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startenddate[0] . " 00:00:00' and '" . $startenddate[1] . " 23:59:00'";
					}
				}
			}
		}
		return $stdfilterlist;
	}

	/** Function to get the RunTime Advanced filter conditions
	 *  @ param $advft_criteria : Type Array
	 *  @ param $advft_criteria_groups : Type Array
	 *  This function returns  $advfiltersql
	 *
	 */
	function RunTimeAdvFilter($advft_criteria, $advft_criteria_groups) {
		$adb = PearDatabase::getInstance();

		$advfilterlist = array();
		$advfiltersql = '';
		if (!empty($advft_criteria)) {
			foreach ($advft_criteria as $column_index => $column_condition) {

				if (empty($column_condition))
					continue;

				$adv_filter_column = $column_condition["columnname"];
				$adv_filter_comparator = $column_condition["comparator"];
				$adv_filter_value = $column_condition["value"];
				$adv_filter_column_condition = $column_condition["columncondition"];
				$adv_filter_groupid = $column_condition["groupid"];

				$column_info = explode(":", $adv_filter_column);

				$moduleFieldLabel = $column_info[2];
				$fieldName = $column_info[3];
				list($module, $fieldLabel) = explode('_', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;
				if (!empty($fieldInfo)) {
					$field = WebserviceField::fromArray($adb, $fieldInfo);
					$fieldType = $field->getFieldDataType();
				}

				if ($fieldType == 'currency') {
					// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
					if ($field->getUIType() == '72') {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
					} else {
						$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
					}
				}

				$specialDateConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
				$temp_val = explode(",", $adv_filter_value);
				if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' ) && !in_array($adv_filter_comparator, $specialDateConditions)) {
					$val = Array();
					for ($x = 0; $x < count($temp_val); $x++) {
						if ($column_info[4] == 'D') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateValue();
						} elseif ($column_info[4] == 'DT') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertDateTimeValue();
						} elseif ($fieldType == 'time') {
							$val[$x] = Vtiger_Time_UIType::getTimeValueWithSeconds($temp_val[$x]);
						} else {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDBInsertTimeValue();
						}
					}
					$adv_filter_value = implode(",", $val);
				}
				$criteria = array();
				$criteria['columnname'] = $adv_filter_column;
				$criteria['comparator'] = $adv_filter_comparator;
				$criteria['value'] = $adv_filter_value;
				$criteria['column_condition'] = $adv_filter_column_condition;

				$advfilterlist[$adv_filter_groupid]['columns'][] = $criteria;
			}

			foreach ($advft_criteria_groups as $group_index => $group_condition_info) {
				if (empty($group_condition_info))
					continue;
				if (empty($advfilterlist[$group_index]))
					continue;
				$advfilterlist[$group_index]['condition'] = $group_condition_info["groupcondition"];
				$noOfGroupColumns = count($advfilterlist[$group_index]['columns']);
				if (!empty($advfilterlist[$group_index]['columns'][$noOfGroupColumns - 1]['column_condition'])) {
					$advfilterlist[$group_index]['columns'][$noOfGroupColumns - 1]['column_condition'] = '';
				}
			}
			$noOfGroups = count($advfilterlist);
			if (!empty($advfilterlist[$noOfGroups]['condition'])) {
				$advfilterlist[$noOfGroups]['condition'] = '';
			}

			$advfiltersql = $this->generateAdvFilterSql($advfilterlist);
		}
		return $advfiltersql;
	}

	/** Function to get standardfilter for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the query of columnlist for the selected columns
	 */
	function getStandardCriterialSql($reportid) {
		global $adb;
		global $modules;
		global $log;

		$sreportstdfiltersql = "select vtiger_reportdatefilter.* from vtiger_report";
		$sreportstdfiltersql .= " inner join vtiger_reportdatefilter on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid";
		$sreportstdfiltersql .= " where vtiger_report.reportid = ?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "datecolumnname");
			$datefilter = $adb->query_result($result, $i, "datefilter");
			$startdate = $adb->query_result($result, $i, "startdate");
			$enddate = $adb->query_result($result, $i, "enddate");

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";
				if ($datefilter == "custom") {

					if ($startdate != "0000-00-00" && $enddate != "0000-00-00" && $selectedfields[0] != "" && $selectedfields[1] != "" && $startdate != '' && $enddate != '') {

						$startDateTime = new DateTimeField($startdate . ' ' . date('H:i:s'));
						$startdate = $startDateTime->getDisplayDate();
						$endDateTime = new DateTimeField($enddate . ' ' . date('H:i:s'));
						$enddate = $endDateTime->getDisplayDate();

						$sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
					}
				} else {

					$startenddate = $this->getStandarFiltersStartAndEndDate($datefilter);

					$startDateTime = new DateTimeField($startenddate[0] . ' ' . date('H:i:s'));
					$startdate = $startDateTime->getDisplayDate();
					$endDateTime = new DateTimeField($startenddate[1] . ' ' . date('H:i:s'));
					$enddate = $endDateTime->getDisplayDate();

					if ($startenddate[0] != "" && $startenddate[1] != "" && $selectedfields[0] != "" && $selectedfields[1] != "") {
						$sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " between '" . $startdate . "' and '" . $enddate . "'";
					}
				}
			}
		}
		$log->info("ReportRun :: Successfully returned getStandardCriterialSql" . $reportid);
		return $sSQL;
	}

	/** Function to get standardfilter startdate and enddate for the given type
	 *  @ param $type : Type String
	 *  returns the $datevalue Array in the given format
	 * 		$datevalue = Array(0=>$startdate,1=>$enddate)
	 */
	function getStandarFiltersStartAndEndDate($type) {
		global $current_user;
		$userPeferredDayOfTheWeek = $current_user->column_fields['dayoftheweek'];

		$today = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
		$todayName = date('l', strtotime($today));

		$tomorrow = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
		$yesterday = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 1, date("Y")));

		$currentmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m"), "01", date("Y")));
		$currentmonth1 = date("Y-m-t");
		$lastmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, "01", date("Y")));
		$lastmonth1 = date("Y-m-t", strtotime("-1 Month"));
		$nextmonth0 = date("Y-m-d", mktime(0, 0, 0, date("m") + 1, "01", date("Y")));
		$nextmonth1 = date("Y-m-t", strtotime("+1 Month"));

		// (Last Week) If Today is "Sunday" then "-2 week Sunday" will give before last week Sunday date
		if ($todayName == $userPeferredDayOfTheWeek)
			$lastweek0 = date("Y-m-d", strtotime("-1 week $userPeferredDayOfTheWeek"));
		else
			$lastweek0 = date("Y-m-d", strtotime("-2 week $userPeferredDayOfTheWeek"));
		$prvDay = date('l', strtotime(date('Y-m-d', strtotime('-1 day', strtotime($lastweek0)))));
		$lastweek1 = date("Y-m-d", strtotime("-1 week $prvDay"));

		// (This Week) If Today is "Sunday" then "-1 week Sunday" will give last week Sunday date
		if ($todayName == $userPeferredDayOfTheWeek)
			$thisweek0 = date("Y-m-d", strtotime("-0 week $userPeferredDayOfTheWeek"));
		else
			$thisweek0 = date("Y-m-d", strtotime("-1 week $userPeferredDayOfTheWeek"));
		$prvDay = date('l', strtotime(date('Y-m-d', strtotime('-1 day', strtotime($thisweek0)))));
		$thisweek1 = date("Y-m-d", strtotime("this $prvDay"));

		// (Next Week) If Today is "Sunday" then "this Sunday" will give Today's date
		if ($todayName == $userPeferredDayOfTheWeek)
			$nextweek0 = date("Y-m-d", strtotime("+1 week $userPeferredDayOfTheWeek"));
		else
			$nextweek0 = date("Y-m-d", strtotime("this $userPeferredDayOfTheWeek"));
		$prvDay = date('l', strtotime(date('Y-m-d', strtotime('-1 day', strtotime($nextweek0)))));
		$nextweek1 = date("Y-m-d", strtotime("+1 week $prvDay"));

		$next7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 6, date("Y")));
		$next30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 29, date("Y")));
		$next60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 59, date("Y")));
		$next90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 89, date("Y")));
		$next120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + 119, date("Y")));

		$last7days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
		$last14days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 13, date("Y")));
		$last30days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 29, date("Y")));
		$last60days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 59, date("Y")));
		$last90days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 89, date("Y")));
		$last120days = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 119, date("Y")));

		$currentFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
		$currentFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y")));
		$lastFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") - 1));
		$lastFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") - 1));
		$nextFY0 = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
		$nextFY1 = date("Y-m-t", mktime(0, 0, 0, "12", date("d"), date("Y") + 1));

		if (date("m") <= 3) {
			$cFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
			$cFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
			$nFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
			$nFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
			$pFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y") - 1));
			$pFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y") - 1));
		} else if (date("m") > 3 and date("m") <= 6) {
			$pFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y")));
			$pFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y")));
			$cFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
			$cFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
			$nFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
			$nFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
		} else if (date("m") > 6 and date("m") <= 9) {
			$nFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
			$nFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
			$pFq = date("Y-m-d", mktime(0, 0, 0, "04", "01", date("Y")));
			$pFq1 = date("Y-m-d", mktime(0, 0, 0, "06", "30", date("Y")));
			$cFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
			$cFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
		} else if (date("m") > 9 and date("m") <= 12) {
			$nFq = date("Y-m-d", mktime(0, 0, 0, "01", "01", date("Y") + 1));
			$nFq1 = date("Y-m-d", mktime(0, 0, 0, "03", "31", date("Y") + 1));
			$pFq = date("Y-m-d", mktime(0, 0, 0, "07", "01", date("Y")));
			$pFq1 = date("Y-m-d", mktime(0, 0, 0, "09", "30", date("Y")));
			$cFq = date("Y-m-d", mktime(0, 0, 0, "10", "01", date("Y")));
			$cFq1 = date("Y-m-d", mktime(0, 0, 0, "12", "31", date("Y")));
		}

		if ($type == "today") {

			$datevalue[0] = $today;
			$datevalue[1] = $today;
		} elseif ($type == "yesterday") {

			$datevalue[0] = $yesterday;
			$datevalue[1] = $yesterday;
		} elseif ($type == "tomorrow") {

			$datevalue[0] = $tomorrow;
			$datevalue[1] = $tomorrow;
		} elseif ($type == "thisweek") {

			$datevalue[0] = $thisweek0;
			$datevalue[1] = $thisweek1;
		} elseif ($type == "lastweek") {

			$datevalue[0] = $lastweek0;
			$datevalue[1] = $lastweek1;
		} elseif ($type == "nextweek") {

			$datevalue[0] = $nextweek0;
			$datevalue[1] = $nextweek1;
		} elseif ($type == "thismonth") {

			$datevalue[0] = $currentmonth0;
			$datevalue[1] = $currentmonth1;
		} elseif ($type == "lastmonth") {

			$datevalue[0] = $lastmonth0;
			$datevalue[1] = $lastmonth1;
		} elseif ($type == "nextmonth") {

			$datevalue[0] = $nextmonth0;
			$datevalue[1] = $nextmonth1;
		} elseif ($type == "next7days") {

			$datevalue[0] = $today;
			$datevalue[1] = $next7days;
		} elseif ($type == "next30days") {

			$datevalue[0] = $today;
			$datevalue[1] = $next30days;
		} elseif ($type == "next60days") {

			$datevalue[0] = $today;
			$datevalue[1] = $next60days;
		} elseif ($type == "next90days") {

			$datevalue[0] = $today;
			$datevalue[1] = $next90days;
		} elseif ($type == "next120days") {

			$datevalue[0] = $today;
			$datevalue[1] = $next120days;
		} elseif ($type == "last7days") {

			$datevalue[0] = $last7days;
			$datevalue[1] = $today;
		} elseif ($type == "last14days") {
			$datevalue[0] = $last14days;
			$datevalue[1] = $today;
		} elseif ($type == "last30days") {

			$datevalue[0] = $last30days;
			$datevalue[1] = $today;
		} elseif ($type == "last60days") {

			$datevalue[0] = $last60days;
			$datevalue[1] = $today;
		} else if ($type == "last90days") {

			$datevalue[0] = $last90days;
			$datevalue[1] = $today;
		} elseif ($type == "last120days") {

			$datevalue[0] = $last120days;
			$datevalue[1] = $today;
		} elseif ($type == "thisfy") {

			$datevalue[0] = $currentFY0;
			$datevalue[1] = $currentFY1;
		} elseif ($type == "prevfy") {

			$datevalue[0] = $lastFY0;
			$datevalue[1] = $lastFY1;
		} elseif ($type == "nextfy") {

			$datevalue[0] = $nextFY0;
			$datevalue[1] = $nextFY1;
		} elseif ($type == "nextfq") {

			$datevalue[0] = $nFq;
			$datevalue[1] = $nFq1;
		} elseif ($type == "prevfq") {

			$datevalue[0] = $pFq;
			$datevalue[1] = $pFq1;
		} elseif ($type == "thisfq") {
			$datevalue[0] = $cFq;
			$datevalue[1] = $cFq1;
		} else {
			$datevalue[0] = "";
			$datevalue[1] = "";
		}
		return $datevalue;
	}

	function hasGroupingList() {
		global $adb;
		$result = $adb->pquery('SELECT 1 FROM vtiger_reportsortcol WHERE reportid=? and columnname <> "none"', array($this->reportid));
		return ($result && $adb->num_rows($result)) ? true : false;
	}

	/** Function to get getGroupingList for the given reportid
	 *  @ param $reportid : Type Integer
	 *  returns the $grouplist Array in the following format
	 *  		$grouplist = Array($tablename:$columnname:$fieldlabel:fieldname:typeofdata=>$tablename:$columnname $sorder,
	 * 				   $tablename1:$columnname1:$fieldlabel1:fieldname1:typeofdata1=>$tablename1:$columnname1 $sorder,
	 * 				   $tablename2:$columnname2:$fieldlabel2:fieldname2:typeofdata2=>$tablename2:$columnname2 $sorder)
	 * This function also sets the return value in the class variable $this->groupbylist
	 */
	function getGroupingList($reportid) {
		global $adb;
		global $modules;
		global $log;

		// Have we initialized information already?
		if ($this->_groupinglist !== false) {
			return $this->_groupinglist;
		}
		$primaryModule = $this->primarymodule; 

		$sreportsortsql = " SELECT vtiger_reportsortcol.*, vtiger_reportgroupbycolumn.* FROM vtiger_report";
		$sreportsortsql .= " inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid";
		$sreportsortsql .= " LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_report.reportid = vtiger_reportgroupbycolumn.reportid AND vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid)";
		$sreportsortsql .= " where vtiger_report.reportid =? AND vtiger_reportsortcol.columnname IN (SELECT columnname from vtiger_selectcolumn WHERE queryid=?) order by vtiger_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid, $reportid));
		$grouplist = array();

		$inventoryModules = getInventoryModules();
		while ($reportsortrow = $adb->fetch_array($result)) {
			$fieldcolname = $reportsortrow["columnname"];
			list($tablename, $colname, $module_field, $fieldname, $single) = split(":", $fieldcolname);
			$sortorder = $reportsortrow["sortorder"];

			if ($sortorder == "Ascending") {
				$sortorder = "ASC";
			} elseif ($sortorder == "Descending") {
				$sortorder = "DESC";
			}

			if ($fieldcolname != "none") {
				$selectedfields = explode(":", $fieldcolname);
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";
				if($selectedfields[0] == 'vtiger_inventoryproductrel') {
					list($moduleName, $field) = explode('_', $selectedfields[2], 2);
					$selectedfields[0] = $selectedfields[0].$moduleName;
				}
				if($selectedfields[0] == 'vtiger_pricebookproductrel') {
					list($moduleName, $field) = explode('_', $selectedfields[2], 2);
					$selectedfields[0] = $selectedfields[0].'tmp'.$moduleName;
				}

				$sqlvalue = $selectedfields[0] . '.' . $selectedfields[1] . ' ' . $sortorder;
				if ($selectedfields[4] == "D" && strtolower($reportsortrow["dategroupbycriteria"]) != "none") {
					$groupField = $module_field;
					$groupCriteria = $reportsortrow["dategroupbycriteria"];
					if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach ($parentCriteria as $criteria) {
							$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupField) . " " . $sortorder;
						}
					}
					$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupField) . " " . $sortorder;
					$sqlvalue = implode(", ", $groupByCondition);
				}
				$fieldModuleName = explode('_',$module_field); 
				$fieldId = getFieldid(getTabid($fieldModuleName[0]), $fieldname);
				$fieldModel = Vtiger_Field_Model::getInstance($fieldId);
				if($fieldModel && ($fieldModel->getFieldDataType()=='reference' || $fieldModel->getFieldDataType()=='owner')){
					$sqlvalue = $module_field . ' ' . $sortorder;
				}
				$grouplist[$fieldcolname] = $sqlvalue;
				$temp = split("_", $selectedfields[2], 2);
				$module = $temp[0];
				if (in_array($module, $inventoryModules) && $fieldname == 'serviceid') {
					$grouplist[$fieldcolname] = $sqlvalue;
				} else if($primaryModule == 'PriceBooks' && $fieldname == 'listprice' && in_array($module, array('Products', 'Services'))){
					$grouplist[$fieldcolname] = $sqlvalue;
				} else if (CheckFieldPermission($fieldname, $module) == 'true') {
					$grouplist[$fieldcolname] = $sqlvalue;
				} else {
					$grouplist[$fieldcolname] = $selectedfields[0] . "." . $selectedfields[1];
				}

				$this->queryPlanner->addTable($tablename);
			}
		}

		// Save the information
		$this->_groupinglist = $grouplist;

		$log->info("ReportRun :: Successfully returned getGroupingList" . $reportid);
		return $grouplist;
	}

	/** function to replace special characters
	 *  @ param $selectedfield : type string
	 *  this returns the string for grouplist
	 */
	function replaceSpecialChar($selectedfield) {
		$selectedfield = decode_html(decode_html($selectedfield));
		preg_match('/&/', $selectedfield, $matches);
		if (!empty($matches)) {
			$selectedfield = str_replace('&', 'and', ($selectedfield));
		}
		return $selectedfield;
	}

	/** function to get the selectedorderbylist for the given reportid
	 *  @ param $reportid : type integer
	 *  this returns the columns query for the sortorder columns
	 *  this function also sets the return value in the class variable $this->orderbylistsql
	 */
	function getSelectedOrderbyList($reportid) {

		global $adb;
		global $modules;
		global $log;

		$sreportsortsql = "select vtiger_reportsortcol.* from vtiger_report";
		$sreportsortsql .= " inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid";
		$sreportsortsql .= " where vtiger_report.reportid =? order by vtiger_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$sortorder = $adb->query_result($result, $i, "sortorder");

			if ($sortorder == "Ascending") {
				$sortorder = "ASC";
			} elseif ($sortorder == "Descending") {
				$sortorder = "DESC";
			}

			if ($fieldcolname != "none") {
				$this->orderbylistcolumns[] = $fieldcolname;
				$n = $n + 1;
				$selectedfields = explode(":", $fieldcolname);
				if ($n > 1) {
					$sSQL .= ", ";
					$this->orderbylistsql .= ", ";
				}
				if ($selectedfields[0] == "vtiger_crmentity" . $this->primarymodule)
					$selectedfields[0] = "vtiger_crmentity";
				$sSQL .= $selectedfields[0] . "." . $selectedfields[1] . " " . $sortorder;
				$this->orderbylistsql .= $selectedfields[0] . "." . $selectedfields[1] . " " . $selectedfields[2];
			}
		}
		$log->info("ReportRun :: Successfully returned getSelectedOrderbyList" . $reportid);
		return $sSQL;
	}

	/** function to get secondary Module for the given Primary module and secondary module
	 *  @ param $module : type String
	 *  @ param $secmodule : type String
	 *  this returns join query for the given secondary module
	 */
	function getRelatedModulesQuery($module, $secmodule) {
		global $log, $current_user;
		$query = '';
		if ($secmodule != '') {
			$secondarymodule = explode(":", $secmodule);
			foreach ($secondarymodule as $key => $value) {
				if (!Vtiger_Module_Model::getInstance($value)) {
					continue;
				}

				$foc = CRMEntity::getInstance($value);

				// Case handling: Force table requirement ahead of time.
				$this->queryPlanner->addTable('vtiger_crmentity' . $value);

				$focQuery = $foc->generateReportsSecQuery($module, $value, $this->queryPlanner);
				
				if ($focQuery) {
					if (count($secondarymodule) > 1) {
						$query .= $focQuery . $this->getReportsNonAdminAccessControlQuery($value, $current_user, $value);
					} else {
						$query .= $focQuery . getNonAdminAccessControlQuery($value, $current_user, $value);
					}
				}
			}
			if ($this->queryPlanner->requireTable('vtiger_inventoryproductreltmp'.$value) && stripos($query, 'join vtiger_inventoryproductrel') === false) {
				$query .= " LEFT JOIN vtiger_inventoryproductrel AS vtiger_inventoryproductreltmp$value ON vtiger_inventoryproductreltmp$value.id = $foc->table_name.$foc->table_index ";
			}
		}
		$log->info("ReportRun :: Successfully returned getRelatedModulesQuery" . $secmodule);

		return $query;
	}

	/**
	 * Non admin user not able to see the records of report even he has permission
	 * Fix for Case :- Report with One Primary Module, and Two Secondary modules, let's say for one of the
	 * secondary module, non-admin user don't have permission, then reports is not showing the record even
	 * the user has permission for another seconday module.
	 * @param type $module
	 * @param type $user
	 * @param type $scope
	 * @return $query
	 */
	function getReportsNonAdminAccessControlQuery($module, $user, $scope = '') {
		require('user_privileges/user_privileges_' . $user->id . '.php');
		require('user_privileges/sharing_privileges_' . $user->id . '.php');
		$query = ' ';
		$tabId = getTabid($module);
		if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1 && $defaultOrgSharingPermission[$tabId] == 3) {
			$sharingRuleInfoVariable = $module . '_share_read_permission';
			$sharingRuleInfo = $$sharingRuleInfoVariable;
			$sharedTabId = null;

			if ($module == "Calendar") {
				$sharedTabId = $tabId;
				$tableName = 'vt_tmp_u' . $user->id . '_t' . $tabId;
			} else if (!empty($sharingRuleInfo) && (count($sharingRuleInfo['ROLE']) > 0 ||
					count($sharingRuleInfo['GROUP']) > 0)) {
				$sharedTabId = $tabId;
			}

			if (!empty($sharedTabId)) {
				$module = getTabModuleName($sharedTabId);
				if ($module == "Calendar") {
					// For calendar we have some special case to check like, calendar shared type
					$moduleInstance = CRMEntity::getInstance($module);
					$query = $moduleInstance->getReportsNonAdminAccessControlQuery($tableName, $tabId, $user, $current_user_parent_role_seq, $current_user_groups);
				} else {
					$query = $this->getNonAdminAccessQuery($module, $user, $current_user_parent_role_seq, $current_user_groups);
				}

				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array());
				$rows = $db->num_rows($result);
				for ($i = 0; $i < $rows; $i++) {
					$ids[] = $db->query_result($result, $i, 'id');
				}
				if (!empty($ids)) {
					$query = " AND vtiger_crmentity$scope.smownerid IN (" . implode(',', $ids) . ") ";
				}
			}
		}
		return $query;
	}

	/** function to get report query for the given module
	 *  @ param $module : type String
	 *  this returns join query for the given module
	 */
	function getReportsQuery($module, $type = '') {
		global $log, $current_user, $adb;
		$secondary_module = "'";
		$secondary_module .= str_replace(":", "','", $this->secondarymodule);
		$secondary_module .="'";

		if ($module == "Leads") {
			$query = "from vtiger_leaddetails
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_leaddetails.leadid";

			if ($this->queryPlanner->requireTable('vtiger_leadsubdetails')) {
				$query .= "	inner join vtiger_leadsubdetails on vtiger_leadsubdetails.leadsubscriptionid=vtiger_leaddetails.leadid";
			}
			if ($this->queryPlanner->requireTable('vtiger_leadaddress')) {
				$query .= "	inner join vtiger_leadaddress on vtiger_leadaddress.leadaddressid=vtiger_leaddetails.leadid";
			}
			if ($this->queryPlanner->requireTable('vtiger_leadscf')) {
				$query .= " inner join vtiger_leadscf on vtiger_leaddetails.leadid = vtiger_leadscf.leadid";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsLeads')) {
				$query .= "	left join vtiger_groups as vtiger_groupsLeads on vtiger_groupsLeads.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersLeads')) {
				$query .= " left join vtiger_users as vtiger_usersLeads on vtiger_usersLeads.id = vtiger_crmentity.smownerid";
			}

			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByLeads')) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByLeads on vtiger_lastModifiedByLeads.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyLeads')) {
				$query .= " left join vtiger_users as vtiger_createdbyLeads on vtiger_createdbyLeads.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " " . $this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0 and vtiger_leaddetails.converted=0";
		} else if ($module == "Accounts") {
			$query = "from vtiger_account
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_account.accountid";

			if ($this->queryPlanner->requireTable('vtiger_accountbillads')) {
				$query .= " inner join vtiger_accountbillads on vtiger_account.accountid=vtiger_accountbillads.accountaddressid";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountshipads')) {
				$query .= " inner join vtiger_accountshipads on vtiger_account.accountid=vtiger_accountshipads.accountaddressid";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountscf')) {
				$query .= " inner join vtiger_accountscf on vtiger_account.accountid = vtiger_accountscf.accountid";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsAccounts')) {
				$query .= " left join vtiger_groups as vtiger_groupsAccounts on vtiger_groupsAccounts.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountAccounts')) {
				$query .= "	left join vtiger_account as vtiger_accountAccounts on vtiger_accountAccounts.accountid = vtiger_account.parentid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersAccounts')) {
				$query .= " left join vtiger_users as vtiger_usersAccounts on vtiger_usersAccounts.id = vtiger_crmentity.smownerid";
			}

			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByAccounts')) {
				$query.= " left join vtiger_users as vtiger_lastModifiedByAccounts on vtiger_lastModifiedByAccounts.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyAccounts')) {
				$query .= " left join vtiger_users as vtiger_createdbyAccounts on vtiger_createdbyAccounts.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0 ";
		} else if ($module == "Contacts") {
			$query = "from vtiger_contactdetails
				inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_contactdetails.contactid";

			if ($this->queryPlanner->requireTable('vtiger_contactaddress')) {
				$query .= "	inner join vtiger_contactaddress on vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid";
			}
			if ($this->queryPlanner->requireTable('vtiger_customerdetails')) {
				$query .= "	inner join vtiger_customerdetails on vtiger_customerdetails.customerid = vtiger_contactdetails.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactsubdetails')) {
				$query .= "	inner join vtiger_contactsubdetails on vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactscf')) {
				$query .= "	inner join vtiger_contactscf on vtiger_contactdetails.contactid = vtiger_contactscf.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsContacts')) {
				$query .= " left join vtiger_groups vtiger_groupsContacts on vtiger_groupsContacts.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsContacts')) {
				$query .= "	left join vtiger_contactdetails as vtiger_contactdetailsContacts on vtiger_contactdetailsContacts.contactid = vtiger_contactdetails.reportsto";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountContacts')) {
				$query .= "	left join vtiger_account as vtiger_accountContacts on vtiger_accountContacts.accountid = vtiger_contactdetails.accountid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersContacts')) {
				$query .= " left join vtiger_users as vtiger_usersContacts on vtiger_usersContacts.id = vtiger_crmentity.smownerid";
			}

			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
				left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByContacts')) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByContacts on vtiger_lastModifiedByContacts.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyContacts')) {
				$query .= " left join vtiger_users as vtiger_createdbyContacts on vtiger_createdbyContacts.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "Potentials") {
			$query = "from vtiger_potential
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_potential.potentialid";

			if ($this->queryPlanner->requireTable('vtiger_potentialscf')) {
				$query .= " inner join vtiger_potentialscf on vtiger_potentialscf.potentialid = vtiger_potential.potentialid";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountPotentials')) {
				$query .= " left join vtiger_account as vtiger_accountPotentials on vtiger_potential.related_to = vtiger_accountPotentials.accountid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsPotentials')) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsPotentials on vtiger_potential.contact_id = vtiger_contactdetailsPotentials.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_campaignPotentials')) {
				$query .= " left join vtiger_campaign as vtiger_campaignPotentials on vtiger_potential.campaignid = vtiger_campaignPotentials.campaignid";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsPotentials')) {
				$query .= " left join vtiger_groups vtiger_groupsPotentials on vtiger_groupsPotentials.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersPotentials')) {
				$query .= " left join vtiger_users as vtiger_usersPotentials on vtiger_usersPotentials.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByPotentials')) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByPotentials on vtiger_lastModifiedByPotentials.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyPotentials')) {
				$query .= " left join vtiger_users as vtiger_createdbyPotentials on vtiger_createdbyPotentials.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0 ";
		}

		//For this Product - we can related Accounts, Contacts (Also Leads, Potentials)
		else if ($module == "Products") {
			$query .= " from vtiger_products";
			$query .= " inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_products.productid";
			if ($this->queryPlanner->requireTable("vtiger_productcf")) {
				$query .= " left join vtiger_productcf on vtiger_products.productid = vtiger_productcf.productid";
			}
			if ($this->queryPlanner->requireTable("vtiger_lastModifiedByProducts")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByProducts on vtiger_lastModifiedByProducts.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyProducts')) {
				$query .= " left join vtiger_users as vtiger_createdbyProducts on vtiger_createdbyProducts.id = vtiger_crmentity.smcreatorid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersProducts")) {
				$query .= " left join vtiger_users as vtiger_usersProducts on vtiger_usersProducts.id = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsProducts")) {
				$query .= " left join vtiger_groups as vtiger_groupsProducts on vtiger_groupsProducts.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_vendorRelProducts")) {
				$query .= " left join vtiger_vendor as vtiger_vendorRelProducts on vtiger_vendorRelProducts.vendorid = vtiger_products.vendor_id";
			}
			if ($this->queryPlanner->requireTable("innerProduct")) {
				$query .= " LEFT JOIN (
						SELECT vtiger_products.productid,
								(CASE WHEN (vtiger_products.currency_id = 1 ) THEN vtiger_products.unit_price
									ELSE (vtiger_products.unit_price / vtiger_currency_info.conversion_rate) END
								) AS actual_unit_price
						FROM vtiger_products
						LEFT JOIN vtiger_currency_info ON vtiger_products.currency_id = vtiger_currency_info.id
						LEFT JOIN vtiger_productcurrencyrel ON vtiger_products.productid = vtiger_productcurrencyrel.productid
						AND vtiger_productcurrencyrel.currencyid = " . $current_user->currency_id . "
				) AS innerProduct ON innerProduct.productid = vtiger_products.productid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
						getNonAdminAccessControlQuery($this->primarymodule,$current_user)."
				where vtiger_crmentity.deleted=0";
		} else if ($module == "HelpDesk") {
			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_crmentityRelHelpDesk', array('vtiger_accountRelHelpDesk', 'vtiger_contactdetailsRelHelpDesk'));

			$query = "from vtiger_troubletickets inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_troubletickets.ticketid";

			if ($this->queryPlanner->requireTable('vtiger_ticketcf')) {
				$query .= " inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid";
			}
			if ($this->queryPlanner->requireTable('vtiger_crmentityRelHelpDesk', $matrix)) {
				$query .= " left join vtiger_crmentity as vtiger_crmentityRelHelpDesk on vtiger_crmentityRelHelpDesk.crmid = vtiger_troubletickets.parent_id";
			}
			if ($this->queryPlanner->requireTable('vtiger_accountRelHelpDesk')) {
				$query .= " left join vtiger_account as vtiger_accountRelHelpDesk on vtiger_accountRelHelpDesk.accountid=vtiger_crmentityRelHelpDesk.crmid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsRelHelpDesk')) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsRelHelpDesk on vtiger_contactdetailsRelHelpDesk.contactid= vtiger_troubletickets.contact_id";
			}
			if ($this->queryPlanner->requireTable('vtiger_productsRel')) {
				$query .= " left join vtiger_products as vtiger_productsRel on vtiger_productsRel.productid = vtiger_troubletickets.product_id";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsHelpDesk')) {
				$query .= " left join vtiger_groups as vtiger_groupsHelpDesk on vtiger_groupsHelpDesk.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersHelpDesk')) {
				$query .= " left join vtiger_users as vtiger_usersHelpDesk on vtiger_crmentity.smownerid=vtiger_usersHelpDesk.id";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_crmentity.smownerid=vtiger_users.id";

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByHelpDesk')) {
				$query .= "  left join vtiger_users as vtiger_lastModifiedByHelpDesk on vtiger_lastModifiedByHelpDesk.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyHelpDesk')) {
				$query .= " left join vtiger_users as vtiger_createdbyHelpDesk on vtiger_createdbyHelpDesk.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0 ";
		} else if ($module == "Calendar") {
			$referenceModuleList = Vtiger_Util_Helper::getCalendarReferenceModulesList();
			$referenceTablesList = array();
			foreach ($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$referenceTablesList[] = $entityTableName . 'RelCalendar';
			}

			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_cntactivityrel', array('vtiger_contactdetailsCalendar'));
			$matrix->setDependency('vtiger_seactivityrel', array('vtiger_crmentityRelCalendar'));
			$matrix->setDependency('vtiger_crmentityRelCalendar', $referenceTablesList);

			$query = "from vtiger_activity
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid";

			if ($this->queryPlanner->requireTable('vtiger_activitycf')) {
				$query .= " left join vtiger_activitycf on vtiger_activitycf.activityid = vtiger_crmentity.crmid";
			}
			if ($this->queryPlanner->requireTable('vtiger_cntactivityrel', $matrix)) {
				$query .= " left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid";
			}
			if ($this->queryPlanner->requireTable('vtiger_contactdetailsCalendar')) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsCalendar on vtiger_contactdetailsCalendar.contactid= vtiger_cntactivityrel.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_groupsCalendar')) {
				$query .= " left join vtiger_groups as vtiger_groupsCalendar on vtiger_groupsCalendar.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable('vtiger_usersCalendar')) {
				$query .= " left join vtiger_users as vtiger_usersCalendar on vtiger_usersCalendar.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable('vtiger_seactivityrel', $matrix)) {
				$query .= " left join vtiger_seactivityrel on vtiger_seactivityrel.activityid = vtiger_activity.activityid";
			}
			if ($this->queryPlanner->requireTable('vtiger_activity_reminder')) {
				$query .= " left join vtiger_activity_reminder on vtiger_activity_reminder.activity_id = vtiger_activity.activityid";
			}
			if ($this->queryPlanner->requireTable('vtiger_recurringevents')) {
				$query .= " left join vtiger_recurringevents on vtiger_recurringevents.activityid = vtiger_activity.activityid";
			}
			if ($this->queryPlanner->requireTable('vtiger_crmentityRelCalendar', $matrix)) {
				$query .= " left join vtiger_crmentity as vtiger_crmentityRelCalendar on vtiger_crmentityRelCalendar.crmid = vtiger_seactivityrel.crmid";
			}

			foreach ($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityIdFieldName = $entityTableFieldNames['entityidfield'];
				$referenceTable = $entityTableName . 'RelCalendar';
				if ($this->queryPlanner->requireTable($referenceTable)) {
					$query .= " LEFT JOIN $entityTableName AS $referenceTable ON $referenceTable.$entityIdFieldName = vtiger_crmentityRelCalendar.crmid";
				}
			}

			if ($this->queryPlanner->requireTable('vtiger_lastModifiedByCalendar')) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByCalendar on vtiger_lastModifiedByCalendar.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyCalendar')) {
				$query .= " left join vtiger_users as vtiger_createdbyCalendar on vtiger_createdbyCalendar.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" WHERE vtiger_crmentity.deleted=0 and (vtiger_activity.activitytype != 'Emails')";
		} else if ($module == "Quotes") {
			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_inventoryproductreltmpQuotes', array('vtiger_productsQuotes', 'vtiger_serviceQuotes'));

			$query = "from vtiger_quotes
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_quotes.quoteid";

			if ($this->queryPlanner->requireTable('vtiger_quotesbillads')) {
				$query .= " inner join vtiger_quotesbillads on vtiger_quotes.quoteid=vtiger_quotesbillads.quotebilladdressid";
			}
			if ($this->queryPlanner->requireTable('vtiger_quotesshipads')) {
				$query .= " inner join vtiger_quotesshipads on vtiger_quotes.quoteid=vtiger_quotesshipads.quoteshipaddressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_currency_info$module")) {
				$query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_quotes.currency_id";
			}
			if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
				if ($this->queryPlanner->requireTable("vtiger_inventoryproductreltmpQuotes", $matrix)) {
					$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpQuotes on vtiger_quotes.quoteid = vtiger_inventoryproductreltmpQuotes.id";
				}
				if ($this->queryPlanner->requireTable("vtiger_productsQuotes")) {
					$query .= " left join vtiger_products as vtiger_productsQuotes on vtiger_productsQuotes.productid = vtiger_inventoryproductreltmpQuotes.productid";
				}
				if ($this->queryPlanner->requireTable("vtiger_serviceQuotes")) {
					$query .= " left join vtiger_service as vtiger_serviceQuotes on vtiger_serviceQuotes.serviceid = vtiger_inventoryproductreltmpQuotes.productid";
				}
			}
			if ($this->queryPlanner->requireTable("vtiger_quotescf")) {
				$query .= " left join vtiger_quotescf on vtiger_quotes.quoteid = vtiger_quotescf.quoteid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsQuotes")) {
				$query .= " left join vtiger_groups as vtiger_groupsQuotes on vtiger_groupsQuotes.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersQuotes")) {
				$query .= " left join vtiger_users as vtiger_usersQuotes on vtiger_usersQuotes.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedByQuotes")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByQuotes on vtiger_lastModifiedByQuotes.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyQuotes')) {
				$query .= " left join vtiger_users as vtiger_createdbyQuotes on vtiger_createdbyQuotes.id = vtiger_crmentity.smcreatorid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersRel1")) {
				$query .= " left join vtiger_users as vtiger_usersRel1 on vtiger_usersRel1.id = vtiger_quotes.inventorymanager";
			}
			if ($this->queryPlanner->requireTable("vtiger_potentialRelQuotes")) {
				$query .= " left join vtiger_potential as vtiger_potentialRelQuotes on vtiger_potentialRelQuotes.potentialid = vtiger_quotes.potentialid";
			}
			if ($this->queryPlanner->requireTable("vtiger_contactdetailsQuotes")) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsQuotes on vtiger_contactdetailsQuotes.contactid = vtiger_quotes.contactid";
			}
			if ($this->queryPlanner->requireTable("vtiger_leaddetailsQuotes")) {
				$query .= " left join vtiger_leaddetails as vtiger_leaddetailsQuotes on vtiger_leaddetailsQuotes.leadid = vtiger_quotes.contactid";
			}
			if ($this->queryPlanner->requireTable("vtiger_accountQuotes")) {
				$query .= " left join vtiger_account as vtiger_accountQuotes on vtiger_accountQuotes.accountid = vtiger_quotes.accountid";
			}
			if ($this->queryPlanner->requireTable('vtiger_currency_info')) {
				$query .= ' LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_quotes.currency_id';
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$focus = CRMEntity::getInstance($module);
			$query .= " " . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
					getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "PurchaseOrder") {

			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_inventoryproductreltmpPurchaseOrder', array('vtiger_productsPurchaseOrder', 'vtiger_servicePurchaseOrder'));

			$query = "from vtiger_purchaseorder
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_purchaseorder.purchaseorderid";

			if ($this->queryPlanner->requireTable("vtiger_pobillads")) {
				$query .= " inner join vtiger_pobillads on vtiger_purchaseorder.purchaseorderid=vtiger_pobillads.pobilladdressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_poshipads")) {
				$query .= " inner join vtiger_poshipads on vtiger_purchaseorder.purchaseorderid=vtiger_poshipads.poshipaddressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_currency_info$module")) {
				$query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_purchaseorder.currency_id";
			}
			if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
				if ($this->queryPlanner->requireTable("vtiger_inventoryproductreltmpPurchaseOrder", $matrix)) {
					$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpPurchaseOrder on vtiger_purchaseorder.purchaseorderid = vtiger_inventoryproductreltmpPurchaseOrder.id";
				}
				if ($this->queryPlanner->requireTable("vtiger_productsPurchaseOrder")) {
					$query .= " left join vtiger_products as vtiger_productsPurchaseOrder on vtiger_productsPurchaseOrder.productid = vtiger_inventoryproductreltmpPurchaseOrder.productid";
				}
				if ($this->queryPlanner->requireTable("vtiger_servicePurchaseOrder")) {
					$query .= " left join vtiger_service as vtiger_servicePurchaseOrder on vtiger_servicePurchaseOrder.serviceid = vtiger_inventoryproductreltmpPurchaseOrder.productid";
				}
			}
			if ($this->queryPlanner->requireTable("vtiger_purchaseordercf")) {
				$query .= " left join vtiger_purchaseordercf on vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsPurchaseOrder")) {
				$query .= " left join vtiger_groups as vtiger_groupsPurchaseOrder on vtiger_groupsPurchaseOrder.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersPurchaseOrder")) {
				$query .= " left join vtiger_users as vtiger_usersPurchaseOrder on vtiger_usersPurchaseOrder.id = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_accountsPurchaseOrder")) {
				$query .= " left join vtiger_account as vtiger_accountsPurchaseOrder on vtiger_accountsPurchaseOrder.accountid = vtiger_purchaseorder.accountid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedByPurchaseOrder")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByPurchaseOrder on vtiger_lastModifiedByPurchaseOrder.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyPurchaseOrder')) {
				$query .= " left join vtiger_users as vtiger_createdbyPurchaseOrder on vtiger_createdbyPurchaseOrder.id = vtiger_crmentity.smcreatorid";
			}
			if ($this->queryPlanner->requireTable("vtiger_vendorRelPurchaseOrder")) {
				$query .= " left join vtiger_vendor as vtiger_vendorRelPurchaseOrder on vtiger_vendorRelPurchaseOrder.vendorid = vtiger_purchaseorder.vendorid";
			}
			if ($this->queryPlanner->requireTable("vtiger_contactdetailsPurchaseOrder")) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsPurchaseOrder on vtiger_contactdetailsPurchaseOrder.contactid = vtiger_purchaseorder.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_currency_info')) {
				$query .= ' LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_purchaseorder.currency_id';
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " " . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
					getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "Invoice") {
			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_inventoryproductreltmpInvoice', array('vtiger_productsInvoice', 'vtiger_serviceInvoice'));

			$query = "from vtiger_invoice
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid";

			if ($this->queryPlanner->requireTable("vtiger_invoicebillads")) {
				$query .=" inner join vtiger_invoicebillads on vtiger_invoice.invoiceid=vtiger_invoicebillads.invoicebilladdressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_invoiceshipads")) {
				$query .=" inner join vtiger_invoiceshipads on vtiger_invoice.invoiceid=vtiger_invoiceshipads.invoiceshipaddressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_currency_info$module")) {
				$query .=" left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_invoice.currency_id";
			}
			// lineItemFieldsInCalculation - is used to when line item fields are used in calculations
			if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
				// should be present on when line item fields are selected for calculation
				if ($this->queryPlanner->requireTable("vtiger_inventoryproductreltmpInvoice", $matrix)) {
					$query .=" left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpInvoice on vtiger_invoice.invoiceid = vtiger_inventoryproductreltmpInvoice.id";
				}
				if ($this->queryPlanner->requireTable("vtiger_productsInvoice")) {
					$query .=" left join vtiger_products as vtiger_productsInvoice on vtiger_productsInvoice.productid = vtiger_inventoryproductreltmpInvoice.productid";
				}
				if ($this->queryPlanner->requireTable("vtiger_serviceInvoice")) {
					$query .=" left join vtiger_service as vtiger_serviceInvoice on vtiger_serviceInvoice.serviceid = vtiger_inventoryproductreltmpInvoice.productid";
				}
			}
			if ($this->queryPlanner->requireTable("vtiger_salesorderInvoice")) {
				$query .= " left join vtiger_salesorder as vtiger_salesorderInvoice on vtiger_salesorderInvoice.salesorderid=vtiger_invoice.salesorderid";
			}
			if ($this->queryPlanner->requireTable("vtiger_invoicecf")) {
				$query .= " left join vtiger_invoicecf on vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsInvoice")) {
				$query .= " left join vtiger_groups as vtiger_groupsInvoice on vtiger_groupsInvoice.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersInvoice")) {
				$query .= " left join vtiger_users as vtiger_usersInvoice on vtiger_usersInvoice.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedByInvoice")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedByInvoice on vtiger_lastModifiedByInvoice.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbyInvoice')) {
				$query .= " left join vtiger_users as vtiger_createdbyInvoice on vtiger_createdbyInvoice.id = vtiger_crmentity.smcreatorid";
			}
			if ($this->queryPlanner->requireTable("vtiger_accountInvoice")) {
				$query .= " left join vtiger_account as vtiger_accountInvoice on vtiger_accountInvoice.accountid = vtiger_invoice.accountid";
			}
			if ($this->queryPlanner->requireTable("vtiger_contactdetailsInvoice")) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsInvoice on vtiger_contactdetailsInvoice.contactid = vtiger_invoice.contactid";
			}
			if ($this->queryPlanner->requireTable('vtiger_currency_info')) {
				$query .= ' LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_invoice.currency_id';
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " " . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
					getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "SalesOrder") {
			$matrix = $this->queryPlanner->newDependencyMatrix();

			$matrix->setDependency('vtiger_inventoryproductreltmpSalesOrder', array('vtiger_productsSalesOrder', 'vtiger_serviceSalesOrder'));

			$query = "from vtiger_salesorder
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_salesorder.salesorderid";

			if ($this->queryPlanner->requireTable("vtiger_sobillads")) {
				$query .= " inner join vtiger_sobillads on vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_soshipads")) {
				$query .= " inner join vtiger_soshipads on vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid";
			}
			if ($this->queryPlanner->requireTable("vtiger_currency_info$module")) {
				$query .= " left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = vtiger_salesorder.currency_id";
			}
			if ($type !== 'COLUMNSTOTOTAL' || $this->lineItemFieldsInCalculation == true) {
				if ($this->queryPlanner->requireTable("vtiger_inventoryproductreltmpSalesOrder", $matrix)) {
					$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductreltmpSalesOrder on vtiger_salesorder.salesorderid = vtiger_inventoryproductreltmpSalesOrder.id";
				}
				if ($this->queryPlanner->requireTable("vtiger_productsSalesOrder")) {
					$query .= " left join vtiger_products as vtiger_productsSalesOrder on vtiger_productsSalesOrder.productid = vtiger_inventoryproductreltmpSalesOrder.productid";
				}
				if ($this->queryPlanner->requireTable("vtiger_serviceSalesOrder")) {
					$query .= " left join vtiger_service as vtiger_serviceSalesOrder on vtiger_serviceSalesOrder.serviceid = vtiger_inventoryproductreltmpSalesOrder.productid";
				}
			}
			if ($this->queryPlanner->requireTable("vtiger_salesordercf")) {
				$query .=" left join vtiger_salesordercf on vtiger_salesorder.salesorderid = vtiger_salesordercf.salesorderid";
			}
			if ($this->queryPlanner->requireTable("vtiger_contactdetailsSalesOrder")) {
				$query .= " left join vtiger_contactdetails as vtiger_contactdetailsSalesOrder on vtiger_contactdetailsSalesOrder.contactid = vtiger_salesorder.contactid";
			}
			if ($this->queryPlanner->requireTable("vtiger_quotesSalesOrder")) {
				$query .= " left join vtiger_quotes as vtiger_quotesSalesOrder on vtiger_quotesSalesOrder.quoteid = vtiger_salesorder.quoteid";
			}
			if ($this->queryPlanner->requireTable("vtiger_accountSalesOrder")) {
				$query .= " left join vtiger_account as vtiger_accountSalesOrder on vtiger_accountSalesOrder.accountid = vtiger_salesorder.accountid";
			}
			if ($this->queryPlanner->requireTable("vtiger_potentialRelSalesOrder")) {
				$query .= " left join vtiger_potential as vtiger_potentialRelSalesOrder on vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potentialid";
			}
			if ($this->queryPlanner->requireTable("vtiger_invoice_recurring_info")) {
				$query .= " left join vtiger_invoice_recurring_info on vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsSalesOrder")) {
				$query .= " left join vtiger_groups as vtiger_groupsSalesOrder on vtiger_groupsSalesOrder.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersSalesOrder")) {
				$query .= " left join vtiger_users as vtiger_usersSalesOrder on vtiger_usersSalesOrder.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedBySalesOrder")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedBySalesOrder on vtiger_lastModifiedBySalesOrder.id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable('vtiger_createdbySalesOrder')) {
				$query .= " left join vtiger_users as vtiger_createdbySalesOrder on vtiger_createdbySalesOrder.id = vtiger_crmentity.smcreatorid";
			}
			if ($this->queryPlanner->requireTable('vtiger_currency_info')) {
				$query .= ' LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_salesorder.currency_id';
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " " . $this->getRelatedModulesQuery($module, $this->secondarymodule) .
					getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "Campaigns") {
			$query = "from vtiger_campaign
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_campaign.campaignid";
			if ($this->queryPlanner->requireTable("vtiger_campaignscf")) {
				$query .= " inner join vtiger_campaignscf as vtiger_campaignscf on vtiger_campaignscf.campaignid=vtiger_campaign.campaignid";
			}
			if ($this->queryPlanner->requireTable("vtiger_productsCampaigns")) {
				$query .= " left join vtiger_products as vtiger_productsCampaigns on vtiger_productsCampaigns.productid = vtiger_campaign.product_id";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsCampaigns")) {
				$query .= " left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersCampaigns")) {
				$query .= " left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedBy$module")) {
				$query .= " left join vtiger_users as vtiger_lastModifiedBy" . $module . " on vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable("vtiger_createdby$module")) {
				$query .= " left join vtiger_users as vtiger_createdby$module on vtiger_createdby$module.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';

			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" where vtiger_crmentity.deleted=0";
		} else if ($module == "Emails") {
			$query = "from vtiger_activity
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_activity.activitytype = 'Emails'";

			if ($this->queryPlanner->requireTable("vtiger_email_track")) {
				$query .= " LEFT JOIN vtiger_email_track ON vtiger_email_track.mailid = vtiger_activity.activityid";
			}
			if ($this->queryPlanner->requireTable("vtiger_groupsEmails")) {
				$query .= " LEFT JOIN vtiger_groups AS vtiger_groupsEmails ON vtiger_groupsEmails.groupid = vtiger_crmentity.smownerid";
			}
			if ($this->queryPlanner->requireTable("vtiger_usersEmails")) {
				$query .= " LEFT JOIN vtiger_users AS vtiger_usersEmails ON vtiger_usersEmails.id = vtiger_crmentity.smownerid";
			}

			// TODO optimize inclusion of these tables
			$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";
			$query .= " LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

			if ($this->queryPlanner->requireTable("vtiger_lastModifiedBy$module")) {
				$query .= " LEFT JOIN vtiger_users AS vtiger_lastModifiedBy" . $module . " ON vtiger_lastModifiedBy" . $module . ".id = vtiger_crmentity.modifiedby";
			}
			if ($this->queryPlanner->requireTable("vtiger_createdby$module")) {
				$query .= " left join vtiger_users as vtiger_createdby$module on vtiger_createdby$module.id = vtiger_crmentity.smcreatorid";
			}

			$focus = CRMEntity::getInstance($module);
			$relquery = $focus->getReportsUiType10Query($module, $this->queryPlanner);
			$query .= $relquery . ' ';
			
			$query .= " ".$this->getRelatedModulesQuery($module,$this->secondarymodule).
					getNonAdminAccessControlQuery($this->primarymodule,$current_user).
					" WHERE vtiger_crmentity.deleted = 0";
		} else {
			if ($module != '') {
				$focus = CRMEntity::getInstance($module);
				$query = $focus->generateReportsQuery($module, $this->queryPlanner) .
						$this->getRelatedModulesQuery($module, $this->secondarymodule) .
						getNonAdminAccessControlQuery($this->primarymodule, $current_user) .
						" WHERE vtiger_crmentity.deleted=0";
			}
		}
		$log->info("ReportRun :: Successfully returned getReportsQuery" . $module);


		$secondarymodule = explode(":", $this->secondarymodule);
		if(in_array('Calendar', $secondarymodule) || $module == 'Calendar') {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$tabId = getTabid('Calendar');
			$task_tableName = 'vt_tmp_u'.$currentUserModel->id.'_t'.$tabId.'_task';
			$event_tableName = 'vt_tmp_u'.$currentUserModel->id.'_t'.$tabId.'_events';
			if(!$currentUserModel->isAdminUser()
				&& stripos($query, $event_tableName) && stripos($query, $task_tableName)) {
				$moduleFocus = CRMEntity::getInstance('Calendar');
				$scope = '';
				if(in_array('Calendar', $secondarymodule)) $scope = 'Calendar';
				$condition = $moduleFocus->buildWhereClauseConditionForCalendar($scope);
				if($condition) {
					$query .= ' AND '.$condition;
				}
			}
		}

		return $query;
	}

	/** function to get query for the given reportid,filterlist,type
	 *  @ param $reportid : Type integer
	 *  @ param $filtersql : Type Array
	 *  @ param $module : Type String
	 *  this returns join query for the report
	 */
	function sGetSQLforReport($reportid, $filtersql, $type = '', $chartReport = false, $startLimit = false, $endLimit = false) {
		global $log;

		$columnlist = $this->getQueryColumnsList($reportid, $type);
		$groupslist = $this->getGroupingList($reportid);
		$groupTimeList = $this->getGroupByTimeList($reportid);
		$stdfilterlist = $this->getStdFilterList($reportid);
		$columnstotallist = $this->getColumnsTotal($reportid);
		$advfiltersql = $this->getAdvFilterSql($reportid);

		$this->totallist = $columnstotallist;
		
		$wheresql = "";
		
		global $current_user;
		//Fix for ticket #4915.
		$selectlist = $columnlist;
		//columns list
		if (isset($selectlist)) {
			$selectedcolumns = implode(", ", $selectlist);
			if ($chartReport == true) {
				$selectedcolumns .= ", count(*) AS 'groupby_count'";
			}
		}
		//groups list
		if (isset($groupslist)) {
			$groupsquery = implode(", ", $groupslist);
		}
		if (isset($groupTimeList)) {
			$groupTimeQuery = implode(", ", $groupTimeList);
		}

		//standard list
		if (isset($stdfilterlist)) {
			$stdfiltersql = implode(", ", $stdfilterlist);
		}
		//columns to total list
		if (isset($columnstotallist)) {
			$columnstotalsql = implode(", ", $columnstotallist);
		}
		if ($stdfiltersql != "") {
			$wheresql = " and " . $stdfiltersql;
		}

		if (isset($filtersql) && $filtersql !== false && $filtersql != '') {
			$advfiltersql = $filtersql;
		}
		if ($advfiltersql != "") {
			$wheresql .= " and " . $advfiltersql;
		}

        if($this->_reportquery == false){
			$reportquery = $this->getReportsQuery($this->primarymodule, $type);
            $this->_reportquery = $reportquery;
        } else {
            $reportquery = $this->_reportquery;
        }

		// If we don't have access to any columns, let us select one column and limit result to shown we have not results
		// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad
		$allColumnsRestricted = false;

		if ($type == 'COLUMNSTOTOTAL') {
			if ($columnstotalsql != '') {
				$reportquery = "select " . $columnstotalsql . " " . $reportquery . " " . $wheresql;
			}
		} else {
			if ($selectedcolumns == '') {
				// Fix for: http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/4758 - Prasad

				$selectedcolumns = "''"; // "''" to get blank column name
				$allColumnsRestricted = true;
			}

			$removeDistinct = false;
			foreach ($columnlist as $key => $value) {
				$tableList = explode(':', $key); // 0 => tablename, 1= > fieldname, 2=> FieldnameAliases, 3=>fieldname, 4=> typeof field
				if($tableList[0] == 'vtiger_inventoryproductrel'){
					$removeDistinct = true;
					break;
				}
			}
			if($removeDistinct) {
				$reportquery = "SELECT " . $selectedcolumns . " " . $reportquery . " " . $wheresql;
			} else {
				$reportquery = "SELECT DISTINCT " . $selectedcolumns . " " . $reportquery . " " . $wheresql;
			}
		}

		$reportquery = listQueryNonAdminChange($reportquery, $this->primarymodule);

		if (trim($groupsquery) != "" && $type !== 'COLUMNSTOTOTAL') {
			if ($chartReport == true) {
				$reportquery .= "group by " . $this->GetFirstSortByField($reportid);
			} else {
				$reportquery .= " order by " . $groupsquery;
			}
		}

		// Prasad: No columns selected so limit the number of rows directly.
		if ($allColumnsRestricted) {
			$reportquery .= " limit 0";
		} else if ($startLimit !== false && $endLimit !== false) {
			$reportquery .= " LIMIT $startLimit, $endLimit";
		}

		preg_match('/&amp;/', $reportquery, $matches);
		if (!empty($matches)) {
			$report = str_replace('&amp;', '&', $reportquery);
			$reportquery = $this->replaceSpecialChar($report);
		}
		$log->info("ReportRun :: Successfully returned sGetSQLforReport" . $reportid);

        if(!$this->_tmptablesinitialized){
			$this->queryPlanner->initializeTempTables();
            $this->_tmptablesinitialized = true;
        }

		return $reportquery;
	}

	/** function to get the report output in HTML,PDF,TOTAL,PRINT,PRINTTOTAL formats depends on the argument $outputformat
	 *  @ param $outputformat : Type String (valid parameters HTML,PDF,TOTAL,PRINT,PRINT_TOTAL)
	 *  @ param $filtersql : Type String
	 *  This returns HTML Report if $outputformat is HTML
	 *  		Array for PDF if  $outputformat is PDF
	 * 		HTML strings for TOTAL if $outputformat is TOTAL
	 * 		Array for PRINT if $outputformat is PRINT
	 * 		HTML strings for TOTAL fields  if $outputformat is PRINTTOTAL
	 * 		HTML strings for
	 */
	// Performance Optimization: Added parameter directOutput to avoid building big-string!
	function GenerateReport($outputformat, $filtersql, $directOutput = false, $startLimit = false, $endLimit = false, $operation = false) {
		global $adb, $current_user, $php_max_execution_time;
		global $modules, $app_strings;
		global $mod_strings, $current_language;
		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		$modules_selected = array();
		$modules_selected[] = $this->primarymodule;
		if (!empty($this->secondarymodule)) {
			$sec_modules = split(":", $this->secondarymodule);
			for ($i = 0; $i < count($sec_modules); $i++) {
				$modules_selected[] = $sec_modules[$i];
			}
		}

		$userCurrencyInfo = getCurrencySymbolandCRate($current_user->currency_id);
		$userCurrencySymbol = $userCurrencyInfo['symbol'];

		// Update Reference fields list list
		$referencefieldres = $adb->pquery("SELECT tabid, fieldlabel, uitype from vtiger_field WHERE uitype in (10,101)", array());
		if ($referencefieldres) {
			foreach ($referencefieldres as $referencefieldrow) {
				$uiType = $referencefieldrow['uitype'];
				$modprefixedlabel = getTabModuleName($referencefieldrow['tabid']) . ' ' . $referencefieldrow['fieldlabel'];
				$modprefixedlabel = str_replace(' ', '_', $modprefixedlabel);

				if ($uiType == 10 && !in_array($modprefixedlabel, $this->ui10_fields)) {
					$this->ui10_fields[] = $modprefixedlabel;
				} elseif ($uiType == 101 && !in_array($modprefixedlabel, $this->ui101_fields)) {
					$this->ui101_fields[] = $modprefixedlabel;
				}
			}
		}

		if ($outputformat == "PDF") {
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, $outputformat, false, $startLimit, $endLimit);
			$result = $adb->pquery($sSQL, array());
			if ($is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1)
				$picklistarray = $this->getAccessPickListValues();
			$noofrows = $adb->num_rows($result);
        	$arr_val = array();
			if ($noofrows > 0) {
				// Number of fields in the result
				$y = $adb->num_fields($result);
				$custom_field_values = $adb->fetch_array($result);

				$fieldsList = array();
				// to get Field and it's Header Labels
				for ($i = 0; $i < $y; $i++) {
					$field = $adb->field_name($result, $i);

					list($module, $fieldLabel) = explode('_', $field->name, 2);
					$translatedLabel = getTranslatedString($fieldLabel, $module);
					if ($fieldLabel == $translatedLabel) {
						$translatedLabel = getTranslatedString(str_replace('_', ' ', $fieldLabel), $module);
					} else {
						$translatedLabel = str_replace('_', ' ', $translatedLabel);
					}
					// In reports we are converting "&" to "and" in query. So field name will not be translated
					// if this replacement is done. Added to handle that case.
					if ((strpos($fieldLabel, '_and_') !== false) && ($translatedLabel == str_replace('_', ' ', $fieldLabel))) {
						$tempLabel = getTranslatedString(str_replace('and', '&', $translatedLabel), $module);
						if ($tempLabel !== $translatedLabel) {
							$translatedLabel = $tempLabel;
						}
					}
					// End
					$moduleLabel ='';
					if(in_array($module,$modules_selected)){
						$moduleLabel = getTranslatedString($module,$module);
					}
					$headerLabel = $translatedLabel;
					if(!empty($this->secondarymodule)) {
						if($moduleLabel != '') {
							$headerLabel = $moduleLabel." ". $translatedLabel;
						}
					}
					$fieldsList[$i]['field'] = $field; 
					$fieldsList[$i]['headerlabel'] = $headerLabel; 
				}
				do {
					$arraylists = Array();
					for ($i = 0; $i < $y; $i++) {
						$fld = $fieldsList[$i]['field'];
						$headerLabel = $fieldsList[$i]['headerlabel'];
						// Check for role based pick list
						$fieldvalue = getReportFieldValue($this, $picklistarray, $fld, $custom_field_values, $i, $operation);

						if ($fld->name == $this->primarymodule . '_LBL_ACTION' && $fieldvalue != '-' && $operation != 'ExcelExport') {
							if($this->primarymodule == 'ModComments') {
								$fieldvalue = "<a href='index.php?module=".getSalesEntityType($fieldvalue)."&view=Detail&record=".$fieldvalue."' target='_blank'>" . getTranslatedString('LBL_VIEW_DETAILS', 'Reports') . "</a>";
							} else {
								$fieldvalue = "<a href='index.php?module={$this->primarymodule}&view=Detail&record={$fieldvalue}' target='_blank'>" . getTranslatedString('LBL_VIEW_DETAILS', 'Reports') . "</a>";
							}
						}
						if (is_array($sec_modules) && (in_array(str_replace('_LBL_ACTION', '', $fld->name), $sec_modules))) {
							continue;
						}

						$arraylists[$headerLabel] = $fieldvalue;
					}
					$arr_val[] = $arraylists;
					set_time_limit($php_max_execution_time);
				} while ($custom_field_values = $adb->fetch_array($result));
            	$data['data'] = $arr_val;
			}
			$data['count'] = $noofrows;
			return $data;
		} elseif ($outputformat == "TOTALXLS") {
			$escapedchars = Array('_SUM', '_AVG', '_MIN', '_MAX');
			$totalpdf = array();
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");
			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);

					static $mod_query_details = array();
					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);
						$key = $fieldlist[1] . '_' . $fieldlist[2];
						if (!isset($mod_query_details[$key]['modulename']) && !isset($mod_query_details[$key]['uitype'])) {
							$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
							$moduleName = getTabModuleName($adb->query_result($mod_query, 0, 'tabid'));
							$mod_query_details[$key]['translatedmodulename'] = getTranslatedString($moduleName, $moduleName);
							$mod_query_details[$key]['modulename'] = $moduleName;
							$mod_query_details[$key]['uitype'] = $adb->query_result($mod_query, 0, "uitype");
						}

						if ($adb->num_rows($mod_query) > 0) {
							$module_name = $mod_query_details[$key]['modulename'];
							$translatedModuleLabel = $mod_query_details[$key]['translatedmodulename'];
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("_", " ", $fieldlabel);
							if ($module_name) {
								$field = $translatedModuleLabel . " " . getTranslatedString($fieldlabel, $module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						// Since there are duplicate entries for this table
						if ($fieldlist[1] == 'vtiger_inventoryproductrel') {
							$module_name = $this->primarymodule;
						}
						$uitype_arr[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $mod_query_details[$key]['uitype'];
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $field;
					}
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					$rowcount = 0;
					foreach ($totclmnflds as $key => $value) {
						$col_header = trim(str_replace($modules, " ", $value));
						$fld_name_1 = $this->primarymodule . "_" . trim($value);
						$fld_name_2 = $this->secondarymodule . "_" . trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 ||
								in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$value = trim($key);
						$arraykey = $value . '_SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '_AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '_MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}

						$arraykey = $value . '_MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71 ,72))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$arraykey] = $conv_value;
						} else {
							$totalpdf[$rowcount][$arraykey] = '';
						}
						$rowcount++;
					}
				}
			}
			return $totalpdf;
		} elseif ($outputformat == 'XLS') {
			$escapedchars = Array('_SUM', '_AVG', '_MIN', '_MAX');
			$totalpdf = array();
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");
			if (isset($this->totallist)) {
				if ($sSQL != '') {
					$result = $adb->query($sSQL);
					$y = $adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);

					static $mod_query_details = array();
					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(':', $key);
						$key = $fieldlist[1].'_'.$fieldlist[2];
						if (!isset($mod_query_details[$this->reportid][$key]['modulename']) && !isset($mod_query_details[$this->reportid][$key]['uitype'])) {
							$mod_query = $adb->pquery('SELECT DISTINCT(tabid) AS tabid, uitype AS uitype FROM vtiger_field WHERE tablename = ? AND columnname=?', array($fieldlist[1], $fieldlist[2]));
							$moduleName = getTabModuleName($adb->query_result($mod_query, 0, 'tabid'));
							$mod_query_details[$this->reportid][$key]['translatedmodulename'] = getTranslatedString($moduleName, $moduleName);
							$mod_query_details[$this->reportid][$key]['modulename'] = $moduleName;
							$mod_query_details[$this->reportid][$key]['uitype'] = $adb->query_result($mod_query, 0, 'uitype');
						}

						if ($adb->num_rows($mod_query) > 0) {
							$module_name = $mod_query_details[$this->reportid][$key]['modulename'];
							$translatedModuleLabel = $mod_query_details[$this->reportid][$key]['translatedmodulename'];
							$fieldlabel = trim(str_replace($escapedchars, ' ', $fieldlist[3]));
							$fieldlabel = str_replace('_', ' ', $fieldlabel);
							if ($module_name) {
								$field = $translatedModuleLabel.' '.getTranslatedString($fieldlabel, $module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						// Since there are duplicate entries for this table
						if ($fieldlist[1] == 'vtiger_inventoryproductrel') {
							$module_name = $this->primarymodule;
						}
						$uitype_arr[str_replace($escapedchars, ' ', $module_name.'_'.$fieldlist[3])] = $mod_query_details[$this->reportid][$key]['uitype'];
						$totclmnflds[str_replace($escapedchars, ' ', $module_name.'_'.$fieldlist[3])] = $field;
					}

					$sumcount = 0;
					$avgcount = 0;
					$mincount = 0;
					$maxcount = 0;
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->field_name($result, $i);
						if (strpos($fld->name, '_SUM') !== false) {
							$sumcount++;
						} else if (strpos($fld->name, '_AVG') !== false) {
							$avgcount++;
						} else if (strpos($fld->name, '_MIN') !== false) {
							$mincount++;
						} else if (strpos($fld->name, '_MAX') !== false) {
							$maxcount++;
						}
						$keyhdr[decode_html($fld->name)] = $custom_field_values[$i];
					}

					$rowcount = 0;
					foreach ($totclmnflds as $key => $value) {
						$col_header = trim(str_replace($modules, ' ', $value));
						$fld_name_1 = $this->primarymodule.'_'.trim($value);
						$fld_name_2 = $this->secondarymodule.'_'.trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 || $uitype_arr[$key] == 74 ||
								in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= ' ('.$app_strings['LBL_IN'].' '.$current_user->currency_symbol.')';
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$value = trim($key);
						$totalpdf[$rowcount]['Field Names'] = $col_header;
						$originalkey = $value.'_SUM';
						$arraykey = $this->replaceSpecialChar($value).'_SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$originalkey] = $conv_value;
						} else if ($sumcount) {
							$totalpdf[$rowcount][$originalkey] = '';
						}

						$originalkey = $value.'_AVG';
						$arraykey = $this->replaceSpecialChar($value).'_AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$originalkey] = $conv_value;
						} else if ($avgcount) {
							$totalpdf[$rowcount][$originalkey] = '';
						}

						$originalkey = $value.'_MIN';
						$arraykey = $this->replaceSpecialChar($value).'_MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$originalkey] = $conv_value;
						} else if ($mincount) {
							$totalpdf[$rowcount][$originalkey] = '';
						}

						$originalkey = $value.'_MAX';
						$arraykey = $this->replaceSpecialChar($value).'_MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, false, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							} else {
								if ($operation == 'ExcelExport') {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true, true);
									if ($uitype_arr[$key] == 74) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								} else {
									$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
									if (in_array($uitype_arr[$key], array(71, 72, 74))) {
										$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
									}
								}
							}
							$totalpdf[$rowcount][$originalkey] = $conv_value;
						} else if ($maxcount) {
							$totalpdf[$rowcount][$originalkey] = '';
						}
						$rowcount++;
					}
					$totalpdf[$rowcount]['sumcount'] = $sumcount;
					$totalpdf[$rowcount]['avgcount'] = $avgcount;
					$totalpdf[$rowcount]['mincount'] = $mincount;
					$totalpdf[$rowcount]['maxcount'] = $maxcount;
				}
			}
			return $totalpdf;
		} elseif ($outputformat == "TOTALHTML") {
			$escapedchars = Array('_SUM', '_AVG', '_MIN', '_MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");

			static $modulename_cache = array();

			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);
					$reportModule = 'Reports';
					$coltotalhtml .= "<table align='center' width='60%' cellpadding='3' cellspacing='0' border='0' class='rptTable'><tr><td class='rptCellLabel'>" . vtranslate('LBL_FIELD_NAMES', $reportModule) . "</td><td class='rptCellLabel'>" . vtranslate('LBL_SUM', $reportModule) . "</td><td class='rptCellLabel'>" . vtranslate('LBL_AVG', $reportModule) . "</td><td class='rptCellLabel'>" . vtranslate('LBL_MIN', $reportModule) . "</td><td class='rptCellLabel'>" . vtranslate('LBL_MAX', $reportModule) . "</td></tr>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);

						$module_name = NULL;
						$cachekey = $fieldlist[1] . ":" . $fieldlist[2];
						if (!isset($modulename_cache[$cachekey])) {
							$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
							if ($adb->num_rows($mod_query) > 0) {
								$module_name = getTabModuleName($adb->query_result($mod_query, 0, 'tabid'));
								$modulename_cache[$cachekey] = $module_name;
							}
						} else {
							$module_name = $modulename_cache[$cachekey];
						}
						if ($module_name) {
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("_", " ", $fieldlabel);
							$field = getTranslatedString($module_name, $module_name) . " " . getTranslatedString($fieldlabel, $module_name);
						} else {
							$field = getTranslatedString($fieldlabel);
						}

						$uitype_arr[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $adb->query_result($mod_query, 0, "uitype");
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $field;
					}
					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}

					foreach ($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead" valign=top>';
						$col_header = trim(str_replace($modules, " ", $value));
						$fld_name_1 = $this->primarymodule . "_" . trim($value);
						$fld_name_2 = $this->secondarymodule . "_" . trim($value);
						if ($uitype_arr[$key] == 71 || $uitype_arr[$key] == 72 ||
								in_array($fld_name_1, $this->append_currency_symbol_to_value) || in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$col_header .= " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">' . $col_header . '</td>';
						$value = trim($key);
						$arraykey = $value . '_SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '_AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '_MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$arraykey = $value . '_MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price)
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
							else
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							$coltotalhtml .= '<td class="rptTotal">' . $conv_value . '</td>';
						}else {
							$coltotalhtml .= '<td class="rptTotal">&nbsp;</td>';
						}

						$coltotalhtml .= '<tr>';

						// Performation Optimization: If Direct output is desired
						if ($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</table>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		} elseif ($outputformat == "PRINT") {
			$reportData = $this->GenerateReport('PDF', $filtersql);
			if (is_array($reportData) && $reportData['count'] > 0) {
				$data = $reportData['data'];
				$noofrows = $reportData['count'];
				$firstRow = reset($data);
				$headers = array_keys($firstRow);
				foreach ($headers as $headerName) {
					if ($headerName == 'ACTION' || $headerName == vtranslate('LBL_ACTION', $this->primarymodule) || $headerName == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || $headerName == vtranslate('LBL ACTION', $this->primarymodule) || $key == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule)) {
						continue;
					}
					$header .= '<th>' . $headerName . '</th>';
				}
				$groupslist = $this->getGroupingList($this->reportid);
				foreach ($groupslist as $reportFieldName => $reportFieldValue) {
					$nameParts = explode(":", $reportFieldName);
					list($groupFieldModuleName, $groupFieldName) = split("_", $nameParts[2], 2);
					$groupByFieldNames[] = vtranslate(str_replace('_', ' ', $groupFieldName), $groupFieldModuleName);
				}
				if (count($groupByFieldNames) > 0) {
					if (count($groupByFieldNames) == 1) {
						$firstField = $groupByFieldNames[0];
					} else if (count($groupByFieldNames) == 2) {
						$firstField = $groupByFieldNames[0];
						$secondField = $groupByFieldNames[1];
					} else if (count($groupByFieldNames) == 3) {
						$firstField = $groupByFieldNames[0];
						$secondField = $groupByFieldNames[1];
						$thirdField = $groupByFieldNames[2];
					}
					$firstValue = ' ';
					$secondValue = ' ';
					$thirdValue = ' ';
					foreach ($data as $key => $valueArray) {
						$valtemplate .= '<tr>';
						foreach ($valueArray as $fieldName => $fieldValue) {
							if ($fieldName == 'ACTION' || $fieldName == vtranslate('LBL_ACTION', $this->primarymodule) || $fieldName == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || $fieldName == vtranslate('LBL ACTION', $this->primarymodule) || $fieldName == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule)) {
								continue;
							}
							if (($fieldName == $firstField || strstr($fieldName, $firstField)) && ($firstValue == $fieldValue || $firstValue == " ")) {
								if ($firstValue == ' ' || $fieldValue == '-') {
									$valtemplate .= "<td style='border-bottom: 0;'>" . $fieldValue . "</td>";
								} else {
									$valtemplate .= "<td style='border-bottom: 0; border-top: 0;'>&nbsp;</td>";
								}
								if ($fieldValue != ' ') {
									$firstValue = $fieldValue;
								}
							} else if (($fieldName == $secondField || strstr($fieldName, $secondField)) && ($secondValue == $fieldValue || $secondValue == " ")) {
								if ($secondValue == ' ' || $secondValue == '-') {
									$valtemplate .= "<td style='border-bottom: 0;'>" . $fieldValue . "</td>";
								} else {
									$valtemplate .= "<td style='border-bottom: 0; border-top: 0;'>&nbsp;</td>";
								}
								if ($fieldValue != ' ') {
									$secondValue = $fieldValue;
								}
							} else if (($fieldName == $thirdField || strstr($fieldName, $thirdField)) && ($thirdValue == $fieldValue || $thirdValue == " ")) {
								if ($thirdValue == ' ' || $thirdValue == '-') {
									$valtemplate .= "<td style='border-bottom: 0;'>" . $fieldValue . "</td>";
								} else {
									$valtemplate .= "<td style='border-bottom: 0; border-top: 0;'>&nbsp;</td>";
								}
								if ($fieldValue != ' ') {
									$thirdValue = $fieldValue;
								}
							} else {
								$valtemplate .= "<td style='border-bottom: 0;'>" . $fieldValue . "</td>";
								if ($fieldName == $firstField || strstr($fieldName, $firstField)) {
									$firstValue = $fieldValue;
								} else if ($fieldName == $secondField || strstr($fieldName, $secondField)) {
									$secondValue = $fieldValue;
								} else if ($fieldName == $thirdField || strstr($fieldName, $thirdField)) {
									$thirdValue = $fieldValue;
								}
							}
						}
						$valtemplate .= '</tr>';
					}
				} else {
					foreach ($data as $key => $values) {
						$valtemplate .= '<tr>';
						foreach ($values as $fieldName => $value) {
							if ($fieldName == 'ACTION' || $fieldName == vtranslate('LBL_ACTION', $this->primarymodule) || $fieldName == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || $fieldName == vtranslate('LBL ACTION', $this->primarymodule) || $fieldName == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule)) {
								continue;
							}
							$valtemplate .= "<td>" . $value . "</td>";
						}
					}
				}
				$sHTML = '<thead>' . $header . '</thead>' . "<tbody>" . $valtemplate . "</tbody>";
				$return_data[] = $sHTML;
				$return_data[] = $noofrows;
			} else {
				$return_data = array('', 0);
			}
			return $return_data;
		} elseif ($outputformat == "PRINT_TOTAL") {
			$escapedchars = Array('_SUM', '_AVG', '_MIN', '_MAX');
			$sSQL = $this->sGetSQLforReport($this->reportid, $filtersql, "COLUMNSTOTOTAL");
			if (isset($this->totallist)) {
				if ($sSQL != "") {
					$result = $adb->query($sSQL);
					$y = $adb->num_fields($result);
					$custom_field_values = $adb->fetch_array($result);
					$reportModule = 'Reports';

					$coltotalhtml .= "<br /><table align='center' width='60%' cellpadding='3' cellspacing='0' border='1' class='printReport'><tr><td class='rptCellLabel'><b>" . vtranslate('LBL_FIELD_NAMES', $reportModule) . "</b></td><td><b>" . vtranslate('LBL_SUM', $reportModule) . "</b></td><td><b>" . vtranslate('LBL_AVG', $reportModule) . "</b></td><td><b>" . vtranslate('LBL_MIN', $reportModule) . "</b></td><td><b>" . vtranslate('LBL_MAX', $reportModule) . "</b></td></tr>";

					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END

					static $mod_query_details = array();
					foreach ($this->totallist as $key => $value) {
						$fieldlist = explode(":", $key);
						$detailsKey = implode('_', array($fieldlist[1], $fieldlist[2]));
						if (!isset($mod_query_details[$detailsKey]['modulename']) && !isset($mod_query_details[$detailsKey]['uitype'])) {
							$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid, uitype as uitype from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
							$moduleName = getTabModuleName($adb->query_result($mod_query, 0, 'tabid'));
							$mod_query_details[$detailsKey]['modulename'] = $moduleName;
							$mod_query_details[$detailsKey]['translatedmodulename'] = getTranslatedString($moduleName, $moduleName);
							$mod_query_details[$detailsKey]['uitype'] = $adb->query_result($mod_query, 0, "uitype");
						}
						if ($adb->num_rows($mod_query) > 0) {
							$module_name = $mod_query_details[$detailsKey]['modulename'];
							$translated_moduleName = $mod_query_details[$detailsKey]['translatedmodulename'];
							$fieldlabel = trim(str_replace($escapedchars, " ", $fieldlist[3]));
							$fieldlabel = str_replace("_", " ", $fieldlabel);
							if ($module_name) {
								$field = $translated_moduleName . " " . getTranslatedString($fieldlabel, $module_name);
							} else {
								$field = getTranslatedString($fieldlabel);
							}
						}
						$uitype_arr[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $mod_query_details[$detailsKey]['uitype'];
						$totclmnflds[str_replace($escapedchars, " ", $module_name . "_" . $fieldlist[3])] = $field;
					}

					for ($i = 0; $i < $y; $i++) {
						$fld = $adb->field_name($result, $i);
						$keyhdr[$fld->name] = $custom_field_values[$i];
					}
					foreach ($totclmnflds as $key => $value) {
						$coltotalhtml .= '<tr class="rptGrpHead">';
						$col_header = getTranslatedString(trim(str_replace($modules, " ", $value)));
						$fld_name_1 = $this->primarymodule . "_" . trim($value);
						$fld_name_2 = $this->secondarymodule . "_" . trim($value);
						if (in_array($uitype_arr[$key], array('71', '72'))
								|| in_array($fld_name_1, $this->append_currency_symbol_to_value)
								|| in_array($fld_name_2, $this->append_currency_symbol_to_value)) {
							$convert_price = true;
						} else {
							$convert_price = false;
						}
						$coltotalhtml .= '<td class="rptData">' . $col_header . '</td>';
						$value = trim($key);
						$arraykey = $value . '_SUM';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
								$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
							} else {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '_AVG';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
								$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
							} else {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '_MIN';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
								$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
							} else {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$arraykey = $value . '_MAX';
						if (isset($keyhdr[$arraykey])) {
							if ($convert_price) {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey]);
								$conv_value = CurrencyField::appendCurrencySymbol($conv_value, $userCurrencySymbol);
							} else {
								$conv_value = CurrencyField::convertToUserFormat($keyhdr[$arraykey], null, true);
							}
							$coltotalhtml .= "<td class='rptTotal'>" . $conv_value . '</td>';
						} else {
							$coltotalhtml .= "<td class='rptTotal'>&nbsp;</td>";
						}

						$coltotalhtml .= '</tr>';

						// Performation Optimization: If Direct output is desired
						if ($directOutput) {
							echo $coltotalhtml;
							$coltotalhtml = '';
						}
						// END
					}

					$coltotalhtml .= "</table>";
					// Performation Optimization: If Direct output is desired
					if ($directOutput) {
						echo $coltotalhtml;
						$coltotalhtml = '';
					}
					// END
				}
			}
			return $coltotalhtml;
		}
	}

	//<<<<<<<new>>>>>>>>>>
	function getColumnsTotal($reportid) {
		// Have we initialized it already?
		if ($this->_columnstotallist !== false) {
			return $this->_columnstotallist;
		}

		global $adb;
		global $modules;
		global $log, $current_user;

		static $modulename_cache = array();

		// Not a good approach to get all the fields if not required(May leads to Performance issue)
		$query = "select primarymodule,secondarymodules from vtiger_reportmodules where reportmodulesid =?";
		$res = $adb->pquery($query, array($reportid));
		$modrow = $adb->fetch_array($res);
		$premod = $modrow["primarymodule"];
		$secmod = $modrow["secondarymodules"];
		$coltotalsql = "select vtiger_reportsummary.* from vtiger_report";
		$coltotalsql .= " inner join vtiger_reportsummary on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid";
		$coltotalsql .= " where vtiger_report.reportid =?";

		$result = $adb->pquery($coltotalsql, array($reportid));

		while ($coltotalrow = $adb->fetch_array($result)) {
			$fieldcolname = $coltotalrow["columnname"];
			if ($fieldcolname != "none") {
				$fieldlist = explode(":", $fieldcolname);
				$field_tablename = $fieldlist[1];
				$field_columnname = $fieldlist[2];

				$cachekey = $field_tablename . ":" . $field_columnname;
				if (!isset($modulename_cache[$cachekey])) {
					$mod_query = $adb->pquery("SELECT distinct(tabid) as tabid from vtiger_field where tablename = ? and columnname=?", array($fieldlist[1], $fieldlist[2]));
					if ($adb->num_rows($mod_query) > 0) {
						$module_name = getTabModuleName($adb->query_result($mod_query, 0, 'tabid'));
						$modulename_cache[$cachekey] = $module_name;
					}
				} else {
					$module_name = $modulename_cache[$cachekey];
				}

				$fieldlabel = trim($fieldlist[3]);
				if ($field_tablename == 'vtiger_inventoryproductrel') {
					$field_columnalias = $premod . "_" . $fieldlist[3];
				} else {
					if ($module_name) {
						$field_columnalias = $module_name . "_" . $fieldlist[3];
					} else {
						$field_columnalias = $module_name . "_" . $fieldlist[3];
					}
				}

				//$field_columnalias = $fieldlist[3];
				$field_permitted = false;
				if (CheckColumnPermission($field_tablename, $field_columnname, $premod) != "false") {
					$field_permitted = true;
				} else {
					$mod = split(":", $secmod);
					foreach ($mod as $key) {
						if (CheckColumnPermission($field_tablename, $field_columnname, $key) != "false") {
							$field_permitted = true;
						}
					}
				}

				//Calculation fields of "Events" module should show in Calendar related report
				$secondaryModules = split(":", $secmod);
				if ($field_permitted === false && ($premod === 'Calendar' || in_array('Calendar', $secondaryModules)) && CheckColumnPermission($field_tablename, $field_columnname, "Events") != "false") {
					$field_permitted = true;
				}

				if ($field_permitted == true) {
					$field = $this->getColumnsTotalSQL($fieldlist, $premod);

					if ($fieldlist[4] == 2) {
						$stdfilterlist[$fieldcolname] = "sum($field) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 3) {
						//Fixed average calculation issue due to NULL values ie., when we use avg() function, NULL values will be ignored.to avoid this we use (sum/count) to find average.
						//$stdfilterlist[$fieldcolname] = "avg(".$fieldlist[1].".".$fieldlist[2].") '".$fieldlist[3]."'";
						$stdfilterlist[$fieldcolname] = "(sum($field)/count(*)) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 4) {
						$stdfilterlist[$fieldcolname] = "min($field) '" . $field_columnalias . "'";
					}
					if ($fieldlist[4] == 5) {
						$stdfilterlist[$fieldcolname] = "max($field) '" . $field_columnalias . "'";
					}

					$this->queryPlanner->addTable($field_tablename);
				}
			}
		}
		// Save the information
		$this->_columnstotallist = $stdfilterlist;

		$log->info("ReportRun :: Successfully returned getColumnsTotal" . $reportid);
		return $stdfilterlist;
	}

	//<<<<<<new>>>>>>>>>


	function getColumnsTotalSQL($fieldlist, $premod) {
		// Added condition to support detail report calculations
		if ($fieldlist[0] == 'cb') {
			$field_tablename = $fieldlist[1];
			$field_columnname = $fieldlist[2];
		} else {
			$field_tablename = $fieldlist[0];
			$field_columnname = $fieldlist[1];
			list($module, $fieldName) = split('_', $fieldlist[2], 2);
		}

		$field = $field_tablename . "." . $field_columnname;
		if ($field_tablename == 'vtiger_products' && $field_columnname == 'unit_price') {
			// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
			$field = " innerProduct.actual_unit_price";
			$this->queryPlanner->addTable("innerProduct");
		}
		if ($field_tablename == 'vtiger_service' && $field_columnname == 'unit_price') {
			// Query needs to be rebuild to get the value in user preferred currency. [innerProduct and actual_unit_price are table and column alias.]
			$field = " innerService.actual_unit_price";
			$this->queryPlanner->addTable("innerService");
		}
		if (($field_tablename == 'vtiger_invoice' || $field_tablename == 'vtiger_quotes' || $field_tablename == 'vtiger_purchaseorder' || $field_tablename == 'vtiger_salesorder') && ($field_columnname == 'total' || $field_columnname == 'subtotal' || $field_columnname == 'discount_amount' || $field_columnname == 's_h_amount' || $field_columnname == 'paid' || $field_columnname == 'balance' || $field_columnname == 'received' || $field_columnname == 'adjustment' || $field_columnname == 'pre_tax_total')) {
			$field = " $field_tablename.$field_columnname/$field_tablename.conversion_rate ";
		}

		if ($field_tablename == 'vtiger_inventoryproductrel') {
			// Check added so that query planner can prepare query properly for inventory modules
			$this->lineItemFieldsInCalculation = true;
			$secondaryModules = explode(':', $this->secondarymodule);
			$inventoryModules = getInventoryModules();

			if(in_array($premod, $inventoryModules)){
				$inventoryModuleInstance = CRMEntity::getInstance($premod);
				$inventoryModuleName = $premod;
			} else {
				foreach($secondaryModules as $secondaryModule) {
					if(in_array($secondaryModule, $inventoryModules)){
						$inventoryModuleName = $secondaryModule;
						$inventoryModuleInstance = CRMEntity::getInstance($secondaryModule);
						$secmodule = $secondaryModule;
						break;
					}
				}
			}

			$field = $field_tablename.'tmp'.$inventoryModuleName.'.'.$field_columnname;
			$itemTableName = 'vtiger_inventoryproductreltmp' . $inventoryModuleName;
			$this->queryPlanner->addTable($itemTableName);
//			$primaryModuleInstance = CRMEntity::getInstance($premod);
			if ($field_columnname == 'listprice') {
				$field = $field . '/' . $inventoryModuleInstance->table_name . '.conversion_rate';
			} else if ($field_columnname == 'discount_amount') {
				$field = ' CASE WHEN ' . $itemTableName . '.discount_amount is not null THEN ' . $itemTableName . '.discount_amount/' . $inventoryModuleInstance->table_name . '.conversion_rate ' .
						'WHEN ' . $itemTableName . '.discount_percent IS NOT NULL THEN (' . $itemTableName . '.listprice*' . $itemTableName . '.quantity*' . $itemTableName . '.discount_percent/100/' . $inventoryModuleInstance->table_name . '.conversion_rate) ELSE 0 END ';
			}
		}
		return $field;
	}

	/** function to get query for the columns to total for the given reportid
	 *  @ param $reportid : Type integer
	 *  This returns columnstoTotal query for the reportid
	 */
	function getColumnsToTotalColumns($reportid) {
		global $adb;
		global $modules;
		global $log;

		$sreportstdfiltersql = "select vtiger_reportsummary.* from vtiger_report";
		$sreportstdfiltersql .= " inner join vtiger_reportsummary on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid";
		$sreportstdfiltersql .= " where vtiger_report.reportid =?";

		$result = $adb->pquery($sreportstdfiltersql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");

			if ($fieldcolname != "none") {
				$fieldlist = explode(":", $fieldcolname);
				if ($fieldlist[4] == 2) {
					$sSQLList[] = "sum(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 3) {
					$sSQLList[] = "avg(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 4) {
					$sSQLList[] = "min(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
				if ($fieldlist[4] == 5) {
					$sSQLList[] = "max(" . $fieldlist[1] . "." . $fieldlist[2] . ") " . $fieldlist[3];
				}
			}
		}
		if (isset($sSQLList)) {
			$sSQL = implode(",", $sSQLList);
		}
		$log->info("ReportRun :: Successfully returned getColumnsToTotalColumns" . $reportid);
		return $sSQL;
	}

	/** Function to convert the Report Header Names into i18n
	 *  @param $fldname: Type Varchar
	 *  Returns Language Converted Header Strings
	 * */
	function getLstringforReportHeaders($fldname) {
		global $modules, $current_language, $current_user, $app_strings;
		$rep_header = ltrim($fldname);
		$rep_header = decode_html($rep_header);
		$labelInfo = explode('_', $rep_header);
		$rep_module = $labelInfo[0];
		if (is_array($this->labelMapping) && !empty($this->labelMapping[$rep_header])) {
			$rep_header = $this->labelMapping[$rep_header];
		} else {
			if ($rep_module == 'LBL') {
				$rep_module = '';
			}
			array_shift($labelInfo);
			$fieldLabel = decode_html(implode("_", $labelInfo));
			$rep_header_temp = preg_replace("/\s+/", "_", $fieldLabel);
			$rep_header = "$rep_module $fieldLabel";
		}
		$curr_symb = "";
		$fieldLabel = ltrim(str_replace($rep_module, '', $rep_header), '_');
		$fieldInfo = getFieldByReportLabel($rep_module, $fieldLabel);
		if ($fieldInfo['uitype'] == '71') {
			$curr_symb = " (" . $app_strings['LBL_IN'] . " " . $current_user->currency_symbol . ")";
		}
		$rep_header .=$curr_symb;

		return $rep_header;
	}

	/** Function to get picklist value array based on profile
	 *          *  returns permitted fields in array format
	 * */
	function getAccessPickListValues() {
		global $adb;
		global $current_user;
		$id = array(getTabid($this->primarymodule));
		if ($this->secondarymodule != '')
			array_push($id, getTabid($this->secondarymodule));

		$query = 'select fieldname,columnname,fieldid,fieldlabel,tabid,uitype from vtiger_field where tabid in(' . generateQuestionMarks($id) . ') and uitype in (15,33,55)'; //and columnname in (?)';
		$result = $adb->pquery($query, $id); //,$select_column));
		$roleid = $current_user->roleid;
		$subrole = getRoleSubordinates($roleid);
		if (count($subrole) > 0) {
			$roleids = $subrole;
			array_push($roleids, $roleid);
		} else {
			$roleids = $roleid;
		}

		$temp_status = Array();
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$fieldname = $adb->query_result($result, $i, "fieldname");
			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			$tabid = $adb->query_result($result, $i, "tabid");
			$uitype = $adb->query_result($result, $i, "uitype");

			$fieldlabel1 = str_replace(" ", "_", $fieldlabel);
			$keyvalue = getTabModuleName($tabid) . "_" . $fieldlabel1;
			$fieldvalues = Array();
			if (count($roleids) > 1) {
				$mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid in (\"" . implode($roleids, "\",\"") . "\") and picklistid in (select picklistid from vtiger_$fieldname)"; // order by sortid asc - not requried
			} else {
				$mulsel = "select distinct $fieldname from vtiger_$fieldname inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldname.picklist_valueid where roleid ='" . $roleid . "' and picklistid in (select picklistid from vtiger_$fieldname)"; // order by sortid asc - not requried
			}
			if ($fieldname != 'firstname')
				$mulselresult = $adb->query($mulsel);
			for ($j = 0; $j < $adb->num_rows($mulselresult); $j++) {
				$fldvalue = $adb->query_result($mulselresult, $j, $fieldname);
				if (in_array($fldvalue, $fieldvalues))
					continue;
				$fieldvalues[] = $fldvalue;
			}
			$field_count = count($fieldvalues);
			if ($uitype == 15 && $field_count > 0 && ($fieldname == 'taskstatus' || $fieldname == 'eventstatus')) {
				$temp_count = count($temp_status[$keyvalue]);
				if ($temp_count > 0) {
					for ($t = 0; $t < $field_count; $t++) {
						$temp_status[$keyvalue][($temp_count + $t)] = $fieldvalues[$t];
					}
					$fieldvalues = $temp_status[$keyvalue];
				} else
					$temp_status[$keyvalue] = $fieldvalues;
			}

			if ($uitype == 33)
				$fieldlists[1][$keyvalue] = $fieldvalues;
			else if ($uitype == 55 && $fieldname == 'salutationtype')
				$fieldlists[$keyvalue] = $fieldvalues;
			else if ($uitype == 15)
				$fieldlists[$keyvalue] = $fieldvalues;
		}
		return $fieldlists;
	}

	function getReportPDF($filterlist = false) {
		require_once 'libraries/tcpdf/tcpdf.php';

		$reportData = $this->GenerateReport("PDF", $filterlist);
		$arr_val = $reportData['data'];

		if (isset($arr_val)) {
			foreach ($arr_val as $wkey => $warray_value) {
				foreach ($warray_value as $whd => $wvalue) {
					if (strlen($wvalue) < strlen($whd)) {
						$w_inner_array[] = strlen($whd);
					} else {
						$w_inner_array[] = strlen($wvalue);
					}
				}
				$warr_val[] = $w_inner_array;
				unset($w_inner_array);
			}

			foreach ($warr_val[0] as $fkey => $fvalue) {
				foreach ($warr_val as $wkey => $wvalue) {
					$f_inner_array[] = $warr_val[$wkey][$fkey];
				}
				sort($f_inner_array, 1);
				$farr_val[] = $f_inner_array;
				unset($f_inner_array);
			}

			foreach ($farr_val as $skkey => $skvalue) {
				if ($skvalue[count($arr_val) - 1] == 1) {
					$col_width[] = ($skvalue[count($arr_val) - 1] * 50);
				} else {
					$col_width[] = ($skvalue[count($arr_val) - 1] * 10) + 10;
				}
			}
			$count = 0;
			foreach ($arr_val[0] as $key => $value) {
				$headerHTML .= '<td width="' . $col_width[$count] . '" bgcolor="#DDDDDD"><b>' . $this->getLstringforReportHeaders($key) . '</b></td>';
				$count = $count + 1;
			}

			foreach ($arr_val as $key => $array_value) {
				$valueHTML = "";
				$count = 0;
				foreach ($array_value as $hd => $value) {
					$valueHTML .= '<td width="' . $col_width[$count] . '">' . $value . '</td>';
					$count = $count + 1;
				}
				$dataHTML .= '<tr>' . $valueHTML . '</tr>';
			}
		}

		$totalpdf = $this->GenerateReport("PRINT_TOTAL", $filterlist);
		$html = '<table border="0.5"><tr>' . $headerHTML . '</tr>' . $dataHTML . '<tr><td>' . $totalpdf . '</td></tr>' . '</table>';
		$columnlength = array_sum($col_width);
		if ($columnlength > 14400) {
			die("<br><br><center>" . $app_strings['LBL_PDF'] . " <a href='javascript:window.history.back()'>" . $app_strings['LBL_GO_BACK'] . ".</a></center>");
		}
		if ($columnlength <= 420) {
			$pdf = new TCPDF('P', 'mm', 'A5', true);
		} elseif ($columnlength >= 421 && $columnlength <= 1120) {
			$pdf = new TCPDF('L', 'mm', 'A3', true);
		} elseif ($columnlength >= 1121 && $columnlength <= 1600) {
			$pdf = new TCPDF('L', 'mm', 'A2', true);
		} elseif ($columnlength >= 1601 && $columnlength <= 2200) {
			$pdf = new TCPDF('L', 'mm', 'A1', true);
		} elseif ($columnlength >= 2201 && $columnlength <= 3370) {
			$pdf = new TCPDF('L', 'mm', 'A0', true);
		} elseif ($columnlength >= 3371 && $columnlength <= 4690) {
			$pdf = new TCPDF('L', 'mm', '2A0', true);
		} elseif ($columnlength >= 4691 && $columnlength <= 6490) {
			$pdf = new TCPDF('L', 'mm', '4A0', true);
		} else {
			$columnhight = count($arr_val) * 15;
			$format = array($columnhight, $columnlength);
			$pdf = new TCPDF('L', 'mm', $format, true);
		}
		$pdf->SetMargins(10, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->setLanguageArray($l);
		$pdf->AddPage();

		$pdf->SetFillColor(224, 235, 255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('FreeSerif', 'B', 14);
		$pdf->Cell(($pdf->columnlength * 50), 10, getTranslatedString($oReport->reportname), 0, 0, 'C', 0);
		//$pdf->writeHTML($oReport->reportname);
		$pdf->Ln();

		$pdf->SetFont('FreeSerif', '', 10);

		$pdf->writeHTML($html);

		return $pdf;
	}

	function writeReportToExcelFile($fileName, $filterlist = '') {
		
		global $currentModule, $current_language;
		$mod_strings = return_module_language($current_language, $currentModule);

		require_once("libraries/PHPExcel/PHPExcel.php");

		$workbook = new PHPExcel();
		$worksheet = $workbook->setActiveSheetIndex(0);

		$reportData = $this->GenerateReport("PDF", $filterlist, false, false, false, 'ExcelExport');
		$arr_val = $reportData['data'];
		$totalxls = $this->GenerateReport("XLS", $filterlist, false, false, false, 'ExcelExport');
		$numericTypes = array('currency', 'double', 'integer', 'percentage');

		$header_styles = array(
			'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E1E0F7')),
				//'font' => array( 'bold' => true )
		);

		if (isset($arr_val)) {
			$count = 0;
			$rowcount = 1;
			//copy the first value details
			$arrayFirstRowValues = $arr_val[0];
			foreach ($arrayFirstRowValues as $key => $value) {
				// It'll not translate properly if you don't mention module of that string
				if ($key == 'ACTION' || $key == vtranslate('LBL_ACTION', $this->primarymodule) || $key == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || $key == vtranslate('LBL ACTION', $this->primarymodule) || $key == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule)) {
					continue;
				}
				$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, decode_html($key), true);
				$worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);

				// NOTE Performance overhead: http://stackoverflow.com/questions/9965476/phpexcel-column-size-issues
				//$worksheet->getColumnDimensionByColumn($count)->setAutoSize(true);

				$count = $count + 1;
			}

			$rowcount++;
			foreach ($arr_val as $key => $array_value) {
				$count = 0;
				foreach ($array_value as $hdr => $valueDataType) {
					if (is_array($valueDataType)) {
						$value = $valueDataType['value'];
						$dataType = $valueDataType['type'];
					} else {
						$value = $valueDataType;
						$dataType = '';
					}
					// It'll not translate properly if you don't mention module of that string
					if ($hdr == 'ACTION' || $hdr == vtranslate('LBL_ACTION', $this->primarymodule) || $hdr == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || $hdr == vtranslate('LBL ACTION', $this->primarymodule) || $hdr == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule))
						continue;
					$value = decode_html($value);
					if (in_array($dataType, $numericTypes)) {
						$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
					} else {
						$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$count = $count + 1;
				}
				$rowcount++;
			}

			// Summary Total
			$rowcount++;
			$count = 0;
			if (is_array($totalxls[0])) {
				foreach ($totalxls[0] as $key => $value) {
					$exploedKey = explode('_', $key);
					$chdr = end($exploedKey);
					$translated_str = in_array($chdr, array_keys($mod_strings)) ? $mod_strings[$chdr] : $chdr;
					$worksheet->setCellValueExplicitByColumnAndRow($count, $rowcount, $translated_str);

					$worksheet->getStyleByColumnAndRow($count, $rowcount)->applyFromArray($header_styles);

					$count = $count + 1;
				}
			}

			$ignoreValues = array('sumcount','avgcount','mincount','maxcount');
			$rowcount++;
			foreach ($totalxls as $key => $array_value) {
				$count = 0;
				foreach ($array_value as $hdr => $value) {
					if (in_array($hdr, $ignoreValues)) {
						continue;
					}
					$value = decode_html($value);
					$excelDatatype = PHPExcel_Cell_DataType::TYPE_STRING;
					if (is_numeric($value)) {
						$excelDatatype = PHPExcel_Cell_DataType::TYPE_NUMERIC;
					}
					$worksheet->setCellValueExplicitByColumnAndRow($count, $key + $rowcount, $value, $excelDatatype);
					$count = $count + 1;
				}
			}
		}
		//Reference Article:  http://phpexcel.codeplex.com/discussions/389578
		ob_clean();
		$workbookWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel5');
		$workbookWriter->save($fileName);
	}

	function writeReportToCSVFile($fileName, $filterlist = '') {

		global $currentModule, $current_language;
		$mod_strings = return_module_language($current_language, $currentModule);

		$reportData = $this->GenerateReport("PDF", $filterlist);
		$arr_val = $reportData['data'];

		$fp = fopen($fileName, 'w+');

		if (isset($arr_val)) {
			$csv_values = array();
			// Header
			$csv_values = array_map('decode_html', array_keys($arr_val[0]));
			$unsetValue = false;
			// It'll not translate properly if you don't mention module of that string
			if (end($csv_values) == vtranslate('LBL_ACTION', $this->primarymodule) || end($csv_values) == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL_ACTION', $this->primarymodule) || end($csv_values) == vtranslate('LBL ACTION', $this->primarymodule) || end($csv_values) == vtranslate($this->primarymodule, $this->primarymodule) . " " . vtranslate('LBL ACTION', $this->primarymodule)) {
				unset($csv_values[count($csv_values) - 1]); //removed action header in csv file
				$unsetValue = true;
			}
			fputcsv($fp, $csv_values);
			foreach ($arr_val as $key => $array_value) {
				if ($unsetValue) {
					array_pop($array_value); //removed action link
				}
				$csv_values = array_map('decode_html', array_values($array_value));
				fputcsv($fp, $csv_values);
			}
		}
		fclose($fp);
	}

	function getGroupByTimeList($reportId) {
		global $adb;
        // Have we initialized information already?
		if ($this->_groupbycondition !== false) {
			return $this->_groupbycondition;
		}
        
		$groupByTimeQuery = "SELECT * FROM vtiger_reportgroupbycolumn WHERE reportid=?";
		$groupByTimeRes = $adb->pquery($groupByTimeQuery, array($reportId));
		$num_rows = $adb->num_rows($groupByTimeRes);
		for ($i = 0; $i < $num_rows; $i++) {
			$sortColName = $adb->query_result($groupByTimeRes, $i, 'sortcolname');
			list($tablename, $colname, $module_field, $fieldname, $single) = split(':', $sortColName);
			$groupField = $module_field;
			$groupCriteria = $adb->query_result($groupByTimeRes, $i, 'dategroupbycriteria');
			if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
				$parentCriteria = $this->groupByTimeParent[$groupCriteria];
				foreach ($parentCriteria as $criteria) {
					$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupField);
				}
			}
			$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupField);
			$this->queryPlanner->addTable($tablename);
		}
        $this->_groupbycondition = $groupByCondition;
		return $groupByCondition;
	}

	function GetTimeCriteriaCondition($criteria, $dateField) {
		$condition = "";
		if (strtolower($criteria) == 'year') {
			$condition = "DATE_FORMAT($dateField, '%Y' )";
		} else if (strtolower($criteria) == 'month') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')%13)";
		} else if (strtolower($criteria) == 'quarter') {
			$condition = "CEIL(DATE_FORMAT($dateField,'%m')/3)";
		}
		return $condition;
	}

	function GetFirstSortByField($reportid) {
		global $adb;
		$groupByField = "";
		$sortFieldQuery = "SELECT * FROM vtiger_reportsortcol
                            LEFT JOIN vtiger_reportgroupbycolumn ON (vtiger_reportsortcol.sortcolid = vtiger_reportgroupbycolumn.sortid and vtiger_reportsortcol.reportid = vtiger_reportgroupbycolumn.reportid)
                            WHERE columnname!='none' and vtiger_reportsortcol.reportid=? ORDER By sortcolid";
		$sortFieldResult = $adb->pquery($sortFieldQuery, array($reportid));
		$inventoryModules = getInventoryModules();
		if ($adb->num_rows($sortFieldResult) > 0) {
			$fieldcolname = $adb->query_result($sortFieldResult, 0, 'columnname');
			list($tablename, $colname, $module_field, $fieldname, $typeOfData) = explode(":", $fieldcolname);
			list($modulename, $fieldlabel) = explode('_', $module_field, 2);
			$groupByField = $module_field;
			if ($typeOfData == "D") {
				$groupCriteria = $adb->query_result($sortFieldResult, 0, 'dategroupbycriteria');
				if (strtolower($groupCriteria) != 'none') {
					if (in_array($groupCriteria, array_keys($this->groupByTimeParent))) {
						$parentCriteria = $this->groupByTimeParent[$groupCriteria];
						foreach ($parentCriteria as $criteria) {
							$groupByCondition[] = $this->GetTimeCriteriaCondition($criteria, $groupByField);
						}
					}
					$groupByCondition[] = $this->GetTimeCriteriaCondition($groupCriteria, $groupByField);
					$groupByField = implode(", ", $groupByCondition);
				}
			} elseif (CheckFieldPermission($fieldname, $modulename) != 'true') {
				if (!(in_array($modulename, $inventoryModules) && $fieldname == 'serviceid')) {
					$groupByField = $tablename . "." . $colname;
				}
			}
		}
		return $groupByField;
	}

	function getReferenceFieldColumnList($moduleName, $fieldInfo) {
		$adb = PearDatabase::getInstance();

		$columnsSqlList = array();

		$fieldInstance = WebserviceField::fromArray($adb, $fieldInfo);
		$referenceModuleList = $fieldInstance->getReferenceList(false);
		if(in_array('Calendar', $referenceModuleList) && in_array('Events', $referenceModuleList)) {
			$eventKey = array_keys($referenceModuleList, 'Events');
			unset($referenceModuleList[$eventKey[0]]);
		}
		
		$reportSecondaryModules = explode(':', $this->secondarymodule);

		if ($moduleName != $this->primarymodule && in_array($this->primarymodule, $referenceModuleList)) {
			$entityTableFieldNames = getEntityFieldNames($this->primarymodule);
			$entityTableName = $entityTableFieldNames['tablename'];
			$entityFieldNames = $entityTableFieldNames['fieldname'];

			$columnList = array();
			if (is_array($entityFieldNames)) {
				foreach ($entityFieldNames as $entityColumnName) {
					$columnList["$entityColumnName"] = "$entityTableName.$entityColumnName";
				}
			} else {
				$columnList[] = "$entityTableName.$entityFieldNames";
			}
			if (count($columnList) > 1) {
				$columnSql = getSqlForNameInDisplayFormat($columnList, $this->primarymodule);
			} else {
				$columnSql = implode('', $columnList);
			}
			$columnsSqlList[] = $columnSql;
		} else {
			foreach ($referenceModuleList as $referenceModule) {
				$entityTableFieldNames = getEntityFieldNames($referenceModule);
				$entityTableName = $entityTableFieldNames['tablename'];
				$entityFieldNames = $entityTableFieldNames['fieldname'];
				$fieldName = $fieldInstance->getFieldName();

				$referenceTableName = '';
				$dependentTableName = '';
				if ($moduleName == 'Calendar' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsCalendar';
				} elseif ($moduleName == 'Calendar' && $fieldName == "parent_id") {
					$referenceTableName = $entityTableName . 'RelCalendar';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Accounts' && $fieldName == "parent_id") {
					$referenceTableName = 'vtiger_accountRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsRelHelpDesk';
				} elseif ($moduleName == 'HelpDesk' && $referenceModule == 'Products' && $fieldName == "product_id") {
					$referenceTableName = 'vtiger_productsRel';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountContacts';
				} elseif ($moduleName == 'Contacts' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsContacts';
				} elseif ($moduleName == 'Accounts' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountAccounts';
				} elseif ($moduleName == 'Campaigns' && $referenceModule == 'Products' && $fieldName == "product_id") {
					$referenceTableName = 'vtiger_productsCampaigns';
				} elseif ($moduleName == 'Faq' && $referenceModule == 'Products' && $fieldName == "product_id") {
					$referenceTableName = 'vtiger_productsFaq';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'SalesOrder' && $fieldName == "salesorder_id") {
					$referenceTableName = 'vtiger_salesorderInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsInvoice';
				} elseif ($moduleName == 'Invoice' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountInvoice';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Campaigns' && $fieldName == "campaignid") {
					$referenceTableName = 'vtiger_campaignPotentials';
				} elseif ($moduleName == 'Products' && $referenceModule == 'Vendors' && $fieldName == "vendor_id") {
					$referenceTableName = 'vtiger_vendorRelProducts';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsPurchaseOrder';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Accounts' && $fieldName == "accountid") {
					$referenceTableName = 'vtiger_accountsPurchaseOrder';
				} elseif ($moduleName == 'PurchaseOrder' && $referenceModule == 'Vendors' && $fieldName == "vendor_id") {
					$referenceTableName = 'vtiger_vendorRelPurchaseOrder';
				} elseif ($moduleName == 'Subscription' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsSubscription';
				} elseif ($moduleName == 'Subscription' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountsSubscription';
				} elseif ($moduleName == 'Subscription' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
					$referenceTableName = 'vtiger_potentialSubscription';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
					$referenceTableName = 'vtiger_potentialRelQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsQuotes';
				} elseif ($moduleName == 'Quotes' && $referenceModule == 'Leads' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_leaddetailsQuotes';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Potentials' && $fieldName == "potential_id") {
					$referenceTableName = 'vtiger_potentialRelSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Accounts' && $fieldName == "account_id") {
					$referenceTableName = 'vtiger_accountSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsSalesOrder';
				} elseif ($moduleName == 'SalesOrder' && $referenceModule == 'Quotes' && $fieldName == "quote_id") {
					$referenceTableName = 'vtiger_quotesSalesOrder';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Contacts' && $fieldName == "contact_id") {
					$referenceTableName = 'vtiger_contactdetailsPotentials';
				} elseif ($moduleName == 'Potentials' && $referenceModule == 'Accounts' && $fieldName == "related_to") {
					$referenceTableName = 'vtiger_accountPotentials';
				} elseif ($moduleName == 'ModComments' && $referenceModule == 'Users') {
					$referenceTableName = 'vtiger_usersModComments';
				} elseif (in_array($referenceModule, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
					$referenceTableName = "{$entityTableName}Rel$referenceModule";
					$dependentTableName = "vtiger_crmentityRel{$referenceModule}{$fieldInstance->getFieldId()}";
				} elseif (in_array($moduleName, $reportSecondaryModules) && $fieldInstance->getUIType() != 10) {
					$referenceTableName = "{$entityTableName}Rel$moduleName";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
				} else {
					$referenceTableName = "{$entityTableName}Rel{$moduleName}{$fieldInstance->getFieldId()}";
					$dependentTableName = "vtiger_crmentityRel{$moduleName}{$fieldInstance->getFieldId()}";
				}
				$this->queryPlanner->addTable($referenceTableName);

				if (isset($dependentTableName)) {
					$this->queryPlanner->addTable($dependentTableName);
				}
				$columnList = array();
				if (is_array($entityFieldNames)) {
					foreach ($entityFieldNames as $entityColumnName) {
						$columnList["$entityColumnName"] = "$referenceTableName.$entityColumnName";
					}
				} else {
					$columnList[] = "$referenceTableName.$entityFieldNames";
				}
				if (count($columnList) > 1) {
					$columnSql = getSqlForNameInDisplayFormat($columnList, $referenceModule);
				} else {
					$columnSql = implode('', $columnList);
				}
				if ($referenceModule == 'DocumentFolders' && $fieldInstance->getFieldName() == 'folderid') {
					$columnSql = 'vtiger_attachmentsfolder.foldername';
					$this->queryPlanner->addTable("vtiger_attachmentsfolder");
				}
				if ($referenceModule == 'Currency' && $fieldInstance->getFieldName() == 'currency_id') {
					$columnSql = "vtiger_currency_info$moduleName.currency_name";
					$this->queryPlanner->addTable("vtiger_currency_info$moduleName");
				}
				$columnsSqlList[] = "trim($columnSql)";
			}
		}
		return $columnsSqlList;
	}

}

?>
