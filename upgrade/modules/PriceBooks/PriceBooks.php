<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

class PriceBooks extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "vtiger_pricebook";
	var $table_index= 'pricebookid';
	var $tab_name = Array('vtiger_crmentity','vtiger_pricebook','vtiger_pricebookcf');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_pricebook'=>'pricebookid','vtiger_pricebookcf'=>'pricebookid');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_pricebookcf', 'pricebookid');
	var $column_fields = Array();

	var $sortby_fields = Array('bookname');

        // This is the list of fields that are in the lists.
	var $list_fields = Array(
                                'Price Book Name'=>Array('pricebook'=>'bookname'),
                                'Active'=>Array('pricebook'=>'active')
                                );

	var $list_fields_name = Array(
                                        'Price Book Name'=>'bookname',
                                        'Active'=>'active'
                                     );
	var $list_link_field= 'bookname';

	var $search_fields = Array(
                                'Price Book Name'=>Array('pricebook'=>'bookname')
                                );
	var $search_fields_name = Array(
                                        'Price Book Name'=>'bookname'
                                     );

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'bookname';
	var $default_sort_order = 'ASC';

	var $mandatory_fields = Array('bookname','currency_id','pricebook_no','createdtime' ,'modifiedtime');

	// For Alphabetical search
	var $def_basicsearch_col = 'bookname';

	/**	Constructor which will set the column_fields in this object
	 */
	function PriceBooks() {
		$this->log =LoggerManager::getLogger('pricebook');
		$this->log->debug("Entering PriceBooks() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('PriceBooks');
		$this->log->debug("Exiting PriceBook method ...");
	}

	function save_module($module)
	{
		// Update the list prices in the price book with the unit price, if the Currency has been changed
		$this->updateListPrices();
	}

	/* Function to Update the List prices for all the products of a current price book
	   with its Unit price, if the Currency for Price book has changed. */
	function updateListPrices() {
		global $log, $adb;
		$log->debug("Entering function updateListPrices...");
		$pricebook_currency = $this->column_fields['currency_id'];
		$prod_res = $adb->pquery("select * from vtiger_pricebookproductrel where pricebookid=? AND usedcurrency != ?",
							array($this->id, $pricebook_currency));
		$numRows = $adb->num_rows($prod_res);

		for($i=0;$i<$numRows;$i++) {
			$product_id = $adb->query_result($prod_res,$i,'productid');
			$list_price = $adb->query_result($prod_res,$i,'listprice');
			$used_currency = $adb->query_result($prod_res,$i,'usedcurrency');
			$product_currency_info = getCurrencySymbolandCRate($used_currency);
			$product_conv_rate = $product_currency_info['rate'];
			$pricebook_currency_info = getCurrencySymbolandCRate($pricebook_currency);
			$pb_conv_rate = $pricebook_currency_info['rate'];
			$conversion_rate = $pb_conv_rate / $product_conv_rate;
			$computed_list_price = $list_price * $conversion_rate;

			$query = "update vtiger_pricebookproductrel set listprice=?, usedcurrency=? where pricebookid=? and productid=?";
			$params = array($computed_list_price, $pricebook_currency, $this->id, $product_id);
			$adb->pquery($query, $params);
		}
		$log->debug("Exiting function updateListPrices...");
	}

	/**	function used to get the products which are related to the pricebook
	 *	@param int $id - pricebook id
         *      @return array - return an array which will be returned from the function getPriceBookRelatedProducts
        **/
	function get_pricebook_products($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_pricebook_products(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='submit' name='button' onclick=\"this.form.action.value='AddProductsToPriceBook';this.form.module.value='$related_module';this.form.return_module.value='$currentModule';this.form.return_action.value='PriceBookDetailView'\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode, vtiger_products.commissionrate,
						vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
						vtiger_pricebookproductrel.listprice
				FROM vtiger_products
				INNER JOIN vtiger_pricebookproductrel ON vtiger_products.productid = vtiger_pricebookproductrel.productid
				INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
				. getNonAdminAccessControlQuery($related_module, $current_user) .'
				WHERE vtiger_pricebook.pricebookid = '.$id.' and vtiger_crmentity.deleted = 0';

		$this->retrieve_entity_info($id,$this_module);
		$return_value = getPriceBookRelatedProducts($query,$this,$returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_pricebook_products method ...");
		return $return_value;
	}

	/**	function used to get the services which are related to the pricebook
	 *	@param int $id - pricebook id
         *      @return array - return an array which will be returned from the function getPriceBookRelatedServices
        **/
	function get_pricebook_services($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_pricebook_services(".$id.") method ...");
		$this_module = $currentModule;

        $related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
        vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='submit' name='button' onclick=\"this.form.action.value='AddServicesToPriceBook';this.form.module.value='$related_module';this.form.return_module.value='$currentModule';this.form.return_action.value='PriceBookDetailView'\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
		}

		$query = 'SELECT vtiger_service.serviceid, vtiger_service.servicename, vtiger_service.commissionrate,
					vtiger_service.qty_per_unit, vtiger_service.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
					vtiger_pricebookproductrel.listprice
			FROM vtiger_service
			INNER JOIN vtiger_pricebookproductrel on vtiger_service.serviceid = vtiger_pricebookproductrel.productid
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_service.serviceid
			INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
			. getNonAdminAccessControlQuery($related_module, $current_user) .'
			WHERE vtiger_pricebook.pricebookid = '.$id.' and vtiger_crmentity.deleted = 0';

		$this->retrieve_entity_info($id,$this_module);
		$return_value = $other->getPriceBookRelatedServices($query,$this,$returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_pricebook_services method ...");
		return $return_value;
	}

	/**	function used to get whether the pricebook has related with a product or not
	 *	@param int $id - product id
	 *	@return true or false - if there are no pricebooks available or associated pricebooks for the product is equal to total number of pricebooks then return false, else return true
	 */
	function get_pricebook_noproduct($id)
	{
		global $log;
		$log->debug("Entering get_pricebook_noproduct(".$id.") method ...");

		$query = "select vtiger_crmentity.crmid, vtiger_pricebook.* from vtiger_pricebook inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_pricebook.pricebookid where vtiger_crmentity.deleted=0";
		$result = $this->db->pquery($query, array());
		$no_count = $this->db->num_rows($result);
		if($no_count !=0)
		{
       	 	$pb_query = 'select vtiger_crmentity.crmid, vtiger_pricebook.pricebookid,vtiger_pricebookproductrel.productid from vtiger_pricebook inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_pricebook.pricebookid inner join vtiger_pricebookproductrel on vtiger_pricebookproductrel.pricebookid=vtiger_pricebook.pricebookid where vtiger_crmentity.deleted=0 and vtiger_pricebookproductrel.productid=?';
			$result_pb = $this->db->pquery($pb_query, array($id));
			if($no_count == $this->db->num_rows($result_pb))
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return false;
			}
			elseif($this->db->num_rows($result_pb) == 0)
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return true;
			}
			elseif($this->db->num_rows($result_pb) < $no_count)
			{
				$log->debug("Exiting get_pricebook_noproduct method ...");
				return true;
			}
		}
		else
		{
			$log->debug("Exiting get_pricebook_noproduct method ...");
			return false;
		}
	}

	/*
	 * Function to get the primary query part of a report
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module,$queryplanner){
	 			$moduletable = $this->table_name;
	 			$moduleindex = $this->table_index;
				$modulecftable = $this->customFieldTable[0];
				$modulecfindex = $this->customFieldTable[1];

				$cfquery = '';
				if(isset($modulecftable) && $queryplanner->requireTable($modulecftable) ){
					$cfquery = "inner join $modulecftable as $modulecftable on $modulecftable.$modulecfindex=$moduletable.$moduleindex";
				}

	 			$query = "from $moduletable $cfquery
					inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex";
				if ($queryplanner->requireTable("vtiger_currency_info$module")){
				    $query .= "  left join vtiger_currency_info as vtiger_currency_info$module on vtiger_currency_info$module.id = $moduletable.currency_id";
				}
				if ($queryplanner->requireTable("vtiger_groups$module")){
				    $query .= " left join vtiger_groups as vtiger_groups$module on vtiger_groups$module.groupid = vtiger_crmentity.smownerid";
				}
				if ($queryplanner->requireTable("vtiger_users$module")){
				    $query .= " left join vtiger_users as vtiger_users$module on vtiger_users$module.id = vtiger_crmentity.smownerid";
				}
				$query .= " left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid";
				$query .= " left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid";

				if ($queryplanner->requireTable("vtiger_lastModifiedByPriceBooks")){
				    $query .= " left join vtiger_users as vtiger_lastModifiedByPriceBooks on vtiger_lastModifiedByPriceBooks.id = vtiger_crmentity.modifiedby ";
				}
                if($queryplanner->requireTable('vtiger_createdby'.$module)){
                    $query .= " left join vtiger_users as vtiger_createdby".$module." on vtiger_createdby".$module.".id = vtiger_crmentity.smcreatorid";
                }
				return $query;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryplanner) {

		$matrix = $queryplanner->newDependencyMatrix();

		$matrix->setDependency("vtiger_crmentityPriceBooks",array("vtiger_usersPriceBooks","vtiger_groupsPriceBooks"));
		if (!$queryplanner->requireTable('vtiger_pricebook', $matrix)) {
			return '';
		}
        $matrix->setDependency("vtiger_pricebook",array("vtiger_crmentityPriceBooks","vtiger_currency_infoPriceBooks"));

		$query = $this->getRelationQuery($module,$secmodule,"vtiger_pricebook","pricebookid", $queryplanner);
		// TODO Support query planner
		if ($queryplanner->requireTable("vtiger_crmentityPriceBooks",$matrix)){
		$query .=" left join vtiger_crmentity as vtiger_crmentityPriceBooks on vtiger_crmentityPriceBooks.crmid=vtiger_pricebook.pricebookid and vtiger_crmentityPriceBooks.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_currency_infoPriceBooks")){
		$query .=" left join vtiger_currency_info as vtiger_currency_infoPriceBooks on vtiger_currency_infoPriceBooks.id = vtiger_pricebook.currency_id";
		}
		if ($queryplanner->requireTable("vtiger_usersPriceBooks")){
		    $query .=" left join vtiger_users as vtiger_usersPriceBooks on vtiger_usersPriceBooks.id = vtiger_crmentityPriceBooks.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_groupsPriceBooks")){
		    $query .=" left join vtiger_groups as vtiger_groupsPriceBooks on vtiger_groupsPriceBooks.groupid = vtiger_crmentityPriceBooks.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByPriceBooks")){
		    $query .=" left join vtiger_users as vtiger_lastModifiedByPriceBooks on vtiger_lastModifiedByPriceBooks.id = vtiger_crmentityPriceBooks.smownerid";
		}
        if ($queryplanner->requireTable("vtiger_createdbyPriceBooks")){
			$query .= " left join vtiger_users as vtiger_createdbyPriceBooks on vtiger_createdbyPriceBooks.id = vtiger_crmentityPriceBooks.smcreatorid ";
		}

		//if secondary modules custom reference field is selected
        $query .= parent::getReportsUiType10Query($secmodule, $queryPlanner);

		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Products" => array("vtiger_pricebookproductrel"=>array("pricebookid","productid"),"vtiger_pricebook"=>"pricebookid"),
			"Services" => array("vtiger_pricebookproductrel"=>array("pricebookid","productid"),"vtiger_pricebook"=>"pricebookid"),
		);
		return $rel_tables[$secmodule];
	}

	function createRecords($obj){
		global $adb;
		$moduleName = $obj->module;
		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $obj->user);
		$moduleMeta = $moduleHandler->getMeta();
		$moduleObjectId = $moduleMeta->getEntityId();
		$moduleFields = $moduleMeta->getModuleFields();
		$focus = CRMEntity::getInstance($moduleName);
        $moduleSubject = 'bookname';

		$tableName = Import_Utils_Helper::getDbTableName($obj->user);
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. Import_Data_Action::$IMPORT_RECORD_NONE .' GROUP BY '. $moduleSubject;

		if($obj->batchImport) {
			$importBatchLimit = getImportBatchLimit();
			$sql .= ' LIMIT '. $importBatchLimit;
		}
		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}
		$bookNameList = array();
		$fieldMapping = $obj->fieldMapping;
		$fieldColumnMapping = $moduleMeta->getFieldColumnMapping();
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$rowId = $row['id'];
			$subject = $row['bookname'];
			$entityInfo = null;
			$fieldData = array();
			$subject = str_replace("\\", "\\\\", $subject);
			$subject = str_replace('"', '""', $subject);
			$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. Import_Data_Action::$IMPORT_RECORD_NONE .' AND '. $moduleSubject . ' = "'. $subject .'"';
			$subjectResult = $adb->query($sql);
			$count = $adb->num_rows($subjectResult);
			$subjectRowIDs = $fieldArray = $productList = array();
			for ($j = 0; $j < $count; ++$j) {
				$subjectRow = $adb->raw_query_result_rowdata($subjectResult, $j);
				array_push($subjectRowIDs, $subjectRow['id']);
				$productList[$j]['relatedto'] = $subjectRow['relatedto'];
				$productList[$j]['listprice'] = $subjectRow['listprice'];
			}
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldData[$fieldName] = $row[strtolower($fieldName)];
			}

            $entityInfo = $this->importRecord($obj, $fieldData, $productList);
            unset($productList);
			if ($entityInfo == null) {
                $entityInfo = array('id' => null, 'status' => Import_Data_Action::$IMPORT_RECORD_FAILED);
            } else if(!$entityInfo['status']){
                $entityInfo['status'] = Import_Data_Action::$IMPORT_RECORD_CREATED;
            }

                $entityIdComponents = vtws_getIdComponents($entityInfo['id']);
                $recordId = $entityIdComponents[1];
                if(!empty($recordId)) {
                    $entityfields = getEntityFieldNames($moduleName);
                    $label = '';
                    if(is_array($entityfields['fieldname'])){
                        foreach($entityfields['fieldname'] as $field){
                            $label .= $fieldData[$field]." ";
                        }
                    }else {
                        $label = $fieldData[$entityfields['fieldname']];
                    }

                    $adb->pquery('UPDATE vtiger_crmentity SET label=? WHERE crmid=?', array(trim($label), $recordId));
                    //updating solr while import records
                    $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $focus = $recordModel->getEntity();
                    $focus->id = $recordId;
                    $focus->column_fields = $fieldData;
                    $this->entityData[] = VTEntityData::fromCRMEntity($focus);
                }

                $label = trim($label);
                $adb->pquery('UPDATE vtiger_crmentity SET label=? WHERE crmid=?', array($label, $recordId));
                //Creating entity data of updated records for post save events
                if ($entityInfo['status'] !== Import_Data_Action::$IMPORT_RECORD_CREATED) {
                    $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                    $focus = $recordModel->getEntity();
                    $focus->id = $recordId;
                    $focus->column_fields = $entityInfo;
                    $this->entitydata[] = VTEntityData::fromCRMEntity($focus);
                }
				
			foreach($subjectRowIDs as $id){
				$obj->importedRecordInfo[$id] = $entityInfo;
				$obj->updateImportStatus($id, $entityInfo);
			}
		}

        $obj->entitydata = null;
		$result = null;
		return true;
	}

	function importRecord($obj, $fieldData, $productList) {
		$moduleName = 'PriceBooks';
		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $obj->user);
		$moduleMeta = $moduleHandler->getMeta();
		unset($fieldData['listprice']); unset($fieldData['relatedto']);
		$fieldData = $obj->transformForImport($fieldData, $moduleMeta);
		try{
			$entityInfo = vtws_create($moduleName, $fieldData, $obj->user);
			if($entityInfo && $productList){
				$this->relatePriceBookWithProduct($entityInfo, $productList);
			}
		} catch (Exception $e){
		}
		$entityInfo['status'] = $obj->getImportRecordStatus('created');
		return $entityInfo;
	}

	function relatePriceBookWithProduct($entityinfo, $productList) {
		if(count($productList) > 0){
			foreach($productList as $product){
				if(!$product['relatedto'])
					continue;
				$productName = $product['relatedto'];
				$productName = explode('::::', $productName);
				$productId = getEntityId($productName[0], $productName[1]);
                $presence = isRecordExists($productId);
                if($presence){
                    $productInstance = Vtiger_Record_Model::getInstanceById($productId);
                    $pricebookId = vtws_getIdComponents($entityinfo['id']);
                    if($productInstance){
                        $recordModel = Vtiger_Record_Model::getInstanceById($pricebookId[1]);
                        $recordModel->updateListPrice($productId, $product['listprice']);
                    }
                }
			}
		}
	}

	function getGroupQuery($tableName){
		return 'SELECT status FROM '.$tableName.' GROUP BY bookname';
	}
}
?>
