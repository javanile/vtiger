<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Module_Model extends Vtiger_Module_Model {

	public static $supportedModules = false;
	const ONE_TO_ONE = '1:1';
	const ONE_TO_MANY = '1:N';
	const MANY_TO_ONE = 'N:1';
	const MANY_TO_MANY = 'N:N';

	/**
	 * Function that returns all the fields for the module
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFields() {
		if(empty($this->fields)){
			$fieldList = array();
			$blocks = $this->getBlocks();
			$blockId = array();
			foreach ($blocks as $block) {
				//to skip events hardcoded block id
				if($block->get('id') == 'EVENT_INVITE_USER_BLOCK_ID') {
					continue;
				}
				$blockId[] = $block->get('id');
			}
			if(count($blockId) > 0) {
				$fieldList = Settings_LayoutEditor_Field_Model::getInstanceFromBlockIdList($blockId,$moduleModel);
			}
			//To handle special case for invite users
			if($this->getName() == 'Events') {
				$blockModel = new Settings_LayoutEditor_Block_Model();
				$blockModel->set('id','EVENT_INVITE_USER_BLOCK_ID');
				$blockModel->set('label','LBL_INVITE_USER_BLOCK');
				$blockModel->set('module', $this);

				$fieldModel = new Settings_LayoutEditor_Field_Model();
				$fieldModel->set('name','selectedusers');
				$fieldModel->set('label','LBL_INVITE_USERS');
				$fieldModel->set('block',$blockModel);
				$fieldModel->setModule($this);
				$fieldList[] = $fieldModel;
			}
			$this->fields = $fieldList;
		}
		return $this->fields;
	}

	/**
	 * Function returns all the blocks for the module
	 * @return <Array of Vtiger_Block_Model> - list of block models
	 */
	public function getBlocks() {
		if(empty($this->blocks)) {
			$blocksList = array();
			$moduleBlocks = Settings_LayoutEditor_Block_Model::getAllForModule($this);
			foreach($moduleBlocks as $block){
				if(!$block->get('label')) {
					continue;
				}
				if($this->getName() == 'HelpDesk' && $block->get('label') == 'LBL_COMMENTS'){
					continue;
				}

				if($block->get('label') != 'LBL_RELATED_PRODUCTS') {
					$blocksList[$block->get('label')] = $block;
				}
			}
			//To handle special case for invite users block
			if($this->getName() == 'Events') {
				$blockModel = new Settings_LayoutEditor_Block_Model();
				$blockModel->set('id','EVENT_INVITE_USER_BLOCK_ID');
				$blockModel->set('label','LBL_INVITE_USER_BLOCK');
				$blockModel->set('module', $this);
				$blocksList['LBL_INVITE_USER_BLOCK'] = $blockModel;
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	public function getAddSupportedFieldTypes() {
		return array(
			'Text','Decimal','Integer','Percent','Currency','Date','Email','Phone','Picklist',
			'URL','Checkbox','TextArea','MultiSelectCombo','Skype','Time'
		);
	}

	/**
	 * Function whcih will give information about the field types that are supported for add
	 * @return <Array>
	 */
	public function getAddFieldTypeInfo() {
		$fieldTypesInfo = array();
		$addFieldSupportedTypes = $this->getAddSupportedFieldTypes();
		$lengthSupportedFieldTypes = array('Text','Decimal','Integer','Currency');
		foreach($addFieldSupportedTypes as $fieldType) {
			$details = array();
			if(in_array($fieldType,$lengthSupportedFieldTypes)) {
				$details['lengthsupported'] = true;
			}
			if($fieldType == 'Decimal' || $fieldType == 'Currency') {
				$details['decimalSupported']  = true;
				$details['maxFloatingDigits'] = 5;
				if($fieldType == 'Currency') {
					$details['decimalReadonly'] = true;
					$details['decimalhidden'] = true;
				}
				//including mantisaa and integer part
				$details['maxLength'] = 64;
			}
			if($fieldType == 'Picklist' || $fieldType == 'MultiSelectCombo') {
				$details['preDefinedValueExists'] = true;
				//text area value type , can give multiple values
				$details['preDefinedValueType'] = 'text';
				if($fieldType == 'Picklist')
					$details['picklistoption'] = true;
			}
			$fieldTypesInfo[$fieldType] = $details;
		}
		return $fieldTypesInfo;
	}

	public function addField($fieldType, $blockId, $params) {

		$db = PearDatabase::getInstance();

		$label = $params['fieldLabel'] = trim($params['fieldLabel']);
		if($this->checkFieldExists($label)){
			throw new Exception(vtranslate('LBL_DUPLICATE_FIELD_EXISTS', 'Settings::LayoutEditor'), 513);
		}
		$supportedFieldTypes = $this->getAddSupportedFieldTypes();
		if(!in_array($fieldType, $supportedFieldTypes)) {
			throw new Exception(vtranslate('LBL_WRONG_FIELD_TYPE', 'Settings::LayoutEditor'), 513);
		}

		$max_fieldid = $db->getUniqueID("vtiger_field");
		$columnName = 'cf_'.$max_fieldid;
		$custfld_fieldid = $max_fieldid;
		$moduleName = $this->getName();

		$focus = CRMEntity::getInstance($moduleName);
		if (isset($focus->customFieldTable)) {
			$tableName=$focus->customFieldTable[0];
		} else {
			$tableName= 'vtiger_'.strtolower($moduleName).'cf';
		}

		$details = $this->getTypeDetailsForAddField($fieldType, $params);
		$uitype = $details['uitype'];
		$typeofdata = $details['typeofdata'];
		$dbType = $details['dbType'];

		$quickCreate = in_array($moduleName,  getInventoryModules()) ? 3 : $params['quickcreate'];

		$fieldModel = new Settings_LayoutEditor_Field_Model();
		$fieldModel->set('name', $columnName)
				   ->set('table', $tableName)
				   ->set('generatedtype',2)
				   ->set('uitype', $uitype)
				   ->set('label', $label)
				   ->set('typeofdata',$typeofdata)
				   ->set('quickcreate',$quickCreate)
				   ->set('columntype', $dbType)
				   ->updateTypeofDataFromMandatory($params['mandatory'])
				   ->set('masseditable',$params['masseditable'])
				   ->set('summaryfield',$params['summaryfield'])
				   ->set('headerfield',$params['headerfield']);

		$defaultValue = $params['fieldDefaultValue'];
		if(strtolower($fieldType) == 'date') {
			$dateInstance = new Vtiger_Date_UIType();
			$defaultValue = $dateInstance->getDBInsertedValue($defaultValue);
		} else if (strtolower($fieldType) == 'time') {
			$defaultValue = Vtiger_Time_UIType::getTimeValueWithSeconds($defaultValue);
		} else if (strtolower($fieldType) == 'currency') {
			$defaultValue = CurrencyField::convertToDBFormat($defaultValue, null, true);
		} else if (strtolower($fieldType) == 'decimal') {
			$defaultValue = CurrencyField::convertToDBFormat($defaultValue, null, true);
		}

		if (is_array($defaultValue)) {
			$defaultValue = implode(' |##| ', $defaultValue);
		}
		$fieldModel->set('defaultvalue', $defaultValue);

		$blockModel = Vtiger_Block_Model::getInstance($blockId, $this);
		$blockModel->addField($fieldModel);

		// If the column failed to create then do not add entry to vtiger_field table
		if(!in_array($columnName, $db->getColumnNames($tableName))) {
			throw new Exception(vtranslate('LBL_FIELD_COULD_NOT_BE_CREATED', 'Settings::LayoutEditor', $label), 513);
		}

		if($fieldType == 'Picklist' || $fieldType == 'MultiSelectCombo') {
			$pickListValues = explode(',',$params['pickListValues']);
			$fieldModel->setPicklistValues($pickListValues);
		}
		return $fieldModel;
	}

	public function getTypeDetailsForAddField($fieldType,$params) {
		switch ($fieldType) {
				Case 'Text' :
							   $fieldLength = $params['fieldLength'];
							   $uichekdata='V~O~LE~'.$fieldLength;
							   $uitype = 1;
							   $type = "VARCHAR(".$fieldLength.") default ''"; // adodb type
							   break;
				Case 'Decimal' :
							   $fieldLength = $params['fieldLength'];
							   $decimal = $params['decimal'];
							   $uitype = 7;
							   //this may sound ridiculous passing decimal but that is the way adodb wants
							   $dbfldlength = $fieldLength + $decimal + 1;
							   $type="NUMERIC(".$dbfldlength.",".$decimal.")";	// adodb type
							   // Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/6363
							   $uichekdata='NN~O';
							   break;
			   Case 'Percent' :
							   $uitype = 9;
							   $type="NUMERIC(5,2)"; //adodb type
							   $uichekdata='N~O~2~2';
							   break;
			   Case 'Currency' :
							   $fieldLength = $params['fieldLength'];
							   $decimal = $params['decimal'];
							   $uitype = 71;
							   $dbfldlength = $fieldLength + $decimal + 1;
							   $decimal = $decimal + 3;
							   $type="NUMERIC(".$dbfldlength.",".$decimal.")"; //adodb type
							   $uichekdata='N~O';
							   break;
			   Case 'Date' :
							   $uichekdata='D~O';
							   $uitype = 5;
							   $type = "DATE"; // adodb type
							   break;
			   Case 'Email' :
							   $uitype = 13;
							   $type = "VARCHAR(50) default '' "; //adodb type
							   $uichekdata='E~O';
							   break;
			   Case 'Time' :
							   $uitype = 14;
							   $type = "TIME";
							   $uichekdata='T~O';
							   break;
			   Case 'Phone' :
							   $uitype = 11;
							   $type = "VARCHAR(30) default '' "; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'Picklist' :
							   $uitype = 16;
							   if(!empty($params['isRoleBasedPickList']))
									$uitype = 15;
							   $type = "VARCHAR(255) default '' "; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'URL' :
							   $uitype = 17;
							   $type = "VARCHAR(255) default '' "; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'Checkbox' :
							   $uitype = 56;
							   $type = "VARCHAR(3) default 0"; //adodb type
							   $uichekdata='C~O';
							   break;
			   Case 'TextArea' :
							   $uitype = 21;
							   $type = "TEXT"; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'MultiSelectCombo' :
							   $uitype = 33;
							   $type = "TEXT"; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'Skype' :
							   $uitype = 85;
							   $type = "VARCHAR(255) default '' "; //adodb type
							   $uichekdata='V~O';
							   break;
			   Case 'Integer' :
							   $fieldLength = $params['fieldLength'];
							   $uitype = 7;
							   if ($fieldLength > 10) {
									$type = "BIGINT(".$fieldLength.")"; //adodb type
							   } else {
									$type = "INTEGER(".$fieldLength.")"; //adodb type
							   }
							   $uichekdata='I~O';
							   break;
		}
		return array(
			'uitype' => $uitype,
			'typeofdata' => $uichekdata,
			'dbType' => $type,
		);

	}

	public function checkFieldExists($fieldLabel) {
		$db = PearDatabase::getInstance();
		$tabId = array($this->getId());
		if($this->getName() == 'Calendar' || $this->getName() == 'Events') {
			//Check for fiel exists in both calendar and events module
			$tabId = array('9','16');
		}
		$query = 'SELECT 1 FROM vtiger_field WHERE tabid IN ('.  generateQuestionMarks($tabId).') AND fieldlabel=?';
		$result = $db->pquery($query, array($tabId,$fieldLabel));
		return ($db->num_rows($result) > 0 ) ? true : false;
	}

	public static function getSupportedModules() {
		if(empty(self::$supportedModules)) {
		   self::$supportedModules = self::getEntityModulesList();
		}
		return self::$supportedModules;
	}


	public static function getInstanceByName($moduleName) {
		$moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$objectProperties = get_object_vars($moduleInstance);
		$selfInstance = new self();
		foreach($objectProperties as $properName=>$propertyValue){
			$selfInstance->$properName = $propertyValue;
		}
		return $selfInstance;
	}

	/**
	 * Function to get Entity module names list
	 * @return <Array> List of Entity modules
	 */
	public static function getEntityModulesList() {
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();

		$presence = array(0, 2);
		$restrictedModules = array('Webmails', 'SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter');

		$query = 'SELECT name FROM vtiger_tab WHERE
						presence IN ('. generateQuestionMarks($presence) .')
						AND isentitytype = ?
						AND name NOT IN ('. generateQuestionMarks($restrictedModules) .')';
		$result = $db->pquery($query, array($presence, 1, $restrictedModules));
		$numOfRows = $db->num_rows($result);

		$modulesList = array();
		for($i=0; $i<$numOfRows; $i++) {
			$moduleName = $db->query_result($result, $i, 'name');
			$modulesList[$moduleName] = vtranslate($moduleName, $moduleName);
			//Calendar needs to be shown as TODO so we are translating using Layout editor specific translations
			if ($moduleName == 'Calendar') {
				$modulesList[$moduleName] = vtranslate($moduleName, 'Settings:LayoutEditor');
			}
		}
		// If calendar is disabled we should not show events module too
		// in layout editor
		if(!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}

	/**
	 * Function to check field is editable or not
	 * @return <Boolean> true/false
	 */
	public function isSortableAllowed() {
		$moduleName = $this->getName();
		if (in_array($moduleName, array('Calendar', 'Events'))) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check blocks are sortable for the module
	 * @return <Boolean> true/false
	 */
	public function isBlockSortableAllowed($blockName) {
		$moduleName = $this->getName();
		if (in_array($moduleName, array('Calendar', 'Events'))) {
			return false;
		}

		if (($blockName === 'LBL_INVITE_USER_BLOCK')
				|| (in_array($moduleName, getInventoryModules()) && $blockName === 'LBL_ITEM_DETAILS')) {
			return false;
		}
		return true;
	}

	/**
	 * Function to check fields are sortable for the block
	 * @return <Boolean> true/false
	 */
	public function isFieldsSortableAllowed($blockName) {
		$moduleName = $this->getName();
		$blocksEliminatedArray = array('HelpDesk' => array('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'),
										'Faq' => array('LBL_COMMENT_INFORMATION'),
										'Calendar' => array('LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION'),
										'Invoice' => array('LBL_ITEM_DETAILS'),
										'Quotes' => array('LBL_ITEM_DETAILS'),
										'SalesOrder' => array('LBL_ITEM_DETAILS'),
										'PurchaseOrder' => array('LBL_ITEM_DETAILS'),
										'Events' => array('LBL_EVENT_INFORMATION', 'LBL_REMINDER_INFORMATION', 'LBL_RECURRENCE_INFORMATION', 'LBL_RELATED_TO', 'LBL_DESCRIPTION_INFORMATION', 'LBL_INVITE_USER_BLOCK'));
		if (in_array($moduleName, array_merge(getInventoryModules(), array('Calendar', 'Events', 'HelpDesk', 'Faq')))) {
			if(!empty($blocksEliminatedArray[$moduleName])) {
				if(in_array($blockName, $blocksEliminatedArray[$moduleName])) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}

	public function getRelations() {
		if($this->relations === null) {
			$this->relations = Vtiger_Relation_Model::getAllRelations($this, false);
		}

		// Contacts relation-tab is turned into custom block on DetailView.
		if ($this->getName() == 'Calendar') {
			$contactsIndex = false;
			foreach ($this->relations as $index => $model) {
				if ($model->getRelationModuleName() == 'Contacts') {
					$contactsIndex = $index;
					break;
				}
			}
			if ($contactsIndex !== false) {
				array_splice($this->relations, $contactsIndex, 1);
			}
		}

		return $this->relations;
	}

	public function getRelationTypeFromRelationField($fieldModel) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM vtiger_relatedlists WHERE relationfieldid=?',array($fieldModel->getId()));
		return ($db->num_rows($result) > 0) ? self::MANY_TO_ONE : self::ONE_TO_ONE;
	}

	public function updateDuplicateHandling($rule, $fieldIdsList = array(), $syncActionId = 1) {
		$db = PearDatabase::getInstance();
		$tabId = $this->getId();

		if (!$fieldIdsList) {
			$fieldIdsList = array(0);
		}

		//Fields Info
		if (count($fieldIdsList) < 4) {//Maximum 3 fields are allowed
			$query = 'UPDATE vtiger_field SET isunique = CASE WHEN fieldid IN ('.  generateQuestionMarks($fieldIdsList).') THEN 1 ELSE 0 END WHERE tabid=?';
			$params = array_merge($fieldIdsList, array($tabId));
			$db->pquery($query, $params);
		}

		if (!$syncActionId) {
			$syncActionId = 1;
		}

		//Rule
		$db->pquery('UPDATE vtiger_tab SET allowduplicates=?, sync_action_for_duplicates=? WHERE tabid=?', array($rule, $syncActionId, $tabId));
		Vtiger_Cache::flushModuleCache($this->getName());
		return true;
	}

}
