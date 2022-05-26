<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/vtlib/Vtiger/Module.php');

/**
 * Vtiger Module Model Class
 */
class Vtiger_Module_Model extends Vtiger_Module {

	protected $blocks = false;
	protected $nameFields = false;
	protected $moduleMeta = false;
	protected $fields = false;
	protected $relations = null;

	/**
	 * Function to get the Module/Tab id
	 * @return <Number>
	 */
	public function getId() {
		return $this->id;
	}

	public function getName() {
		return $this->name;
	}

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isEntityModule() {
		return ($this->isentitytype== '1') ? true :false ;
	}

	/**
	 * Function to check whether the module is enabled for quick create
	 * @return <Boolean> - true/false
	 */
	public function isQuickCreateSupported() {
		return $this->isEntityModule();
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	/**
	 * Function to get singluar label key
	 * @return <String> - Singular module label key
	 */
	public function getSingularLabelKey(){
		return 'SINGLE_'.$this->get('name');
	}

	/**
	 * Function to get the value of a given property
	 * @param <String> $propertyName
	 * @return <Object>
	 * @throws Exception
	 */
	public function get($propertyName) {
		if(property_exists($this,$propertyName)){
			return $this->$propertyName;
		}
		throw new Exception( $propertyName.' doest not exists in class '.get_class($this));
	}

	/**
	 * Function to set the value of a given property
	 * @param <String> $propertyName
	 * @param <Object> $propertyValue
	 * @return Vtiger_Module_Model instance
	 */
	public function set($propertyName, $propertyValue) {
		$this->$propertyName = $propertyValue;
		return $this;
	}

	/**
	 * Function checks if the module is Active
	 * @return <Boolean>
	 */
	public function isActive() {
		return in_array($this->get('presence'), array(0,2));
	}

	/**
	 * Function checks if the module is enabled for tracking changes
	 * @return <Boolean>
	 */
	public function isTrackingEnabled() {
		require_once 'modules/ModTracker/ModTracker.php';
		$trackingEnabled = ModTracker::isTrackingEnabledForModule($this->getName());
		return ($this->isActive() && $trackingEnabled);
	}

	/**
	 * Function checks if comment is enabled
	 * @return boolean
	 */
	public function isCommentEnabled() {
		$enabled = false;
		$db = PearDatabase::getInstance();
		$commentsModuleModel = Vtiger_Module_Model::getInstance('ModComments');
		if($commentsModuleModel && $commentsModuleModel->isActive()) {
			$relatedToFieldResult = $db->pquery('SELECT fieldid FROM vtiger_field WHERE fieldname = ? AND tabid = ?',
					array('related_to', $commentsModuleModel->getId()));
			$fieldId = $db->query_result($relatedToFieldResult, 0, 'fieldid');
			if(!empty($fieldId)) {
				$relatedModuleResult = $db->pquery('SELECT relmodule FROM vtiger_fieldmodulerel WHERE fieldid = ?', array($fieldId));
				$rows = $db->num_rows($relatedModuleResult);

				for($i=0; $i<$rows; $i++) {
					$relatedModule = $db->query_result($relatedModuleResult, $i, 'relmodule');
					if($this->getName() == $relatedModule) {
						$enabled = true;
					}
				}
			}
		} else {
			$enabled = false;
		}
		return $enabled;
	}

	public function isQuickPreviewEnabled(){
		$enabled = false;
		if($this->isSummaryViewSupported()){
			$enabled = true;
		}
		return $enabled;
	}
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = $recordModel->getEntity();
		$fields = $focus->column_fields;
		foreach($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if(is_array($fieldValue)){
				$focus->column_fields[$fieldName] = $fieldValue;
			}else if($fieldValue !== null) {
				/*
				 * for ajax edit, in Vtiger_SaveAjax_Action we are setting relatedContact to 
				 * the record model which is an object
				 * Note : decode_html expects only strings
				 */
				$value = is_string($fieldValue) ? decode_html($fieldValue) : $fieldValue;
				$focus->column_fields[$fieldName] = $value;
			}
		}
		$focus->mode = $recordModel->get('mode');
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);
		return $recordModel->setId($focus->id);
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$focus->trash($moduleName, $recordModel->getId());
		if(method_exists($focus, 'transferRelatedRecords')) {
			if($recordModel->get('transferRecordIDs'))
				$focus->transferRelatedRecords($moduleName, $recordModel->get('transferRecordIDs'), $recordModel->getId());
		}
	}

	/**
	 * Function to get the module meta information
	 * @param <type> $userModel - user model
	 */
	public function getModuleMeta($userModel = false) {
		if(empty($this->moduleMeta)){
			if(empty($userModel)) {
			$userModel = Users_Record_Model::getCurrentUserModel();
		}
			$this->moduleMeta = Vtiger_ModuleMeta_Model::getInstance($this->get('name'), $userModel);
		}
		return $this->moduleMeta;
	}

	//Note : This api is using only in RelationListview - for getting columnfields of Related Module
	//Need to review........

	/**
	 * Function to get the module field mapping
	 * @return <array>
	 */
	public function getColumnFieldMapping(){
		$moduleMeta = $this->getModuleMeta();
		$meta = $moduleMeta->getMeta();
		$fieldColumnMapping =  $meta->getFieldColumnMapping();
		return array_flip($fieldColumnMapping);
	}

	/**
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getListViewName() {
		return 'List';
	}

	/**
	 * Function to get the DetailView Component Name
	 * @return string
	 */
	public function getDetailViewName() {
		return 'Detail';
	}

	/**
	 * Function to get the EditView Component Name
	 * @return string
	 */
	public function getEditViewName(){
		return 'Edit';
	}

	/**
	 * Function to get the DuplicateView Component Name
	 * @return string
	 */
	public function getDuplicateViewName(){
		return 'Edit';
	}

	/**
	 * Function to get the Delete Action Component Name
	 * @return string
	 */
	public function getDeleteActionName() {
		return 'Delete';
	}

	/**
	 * Function to get the Default View Component Name
	 * @return string
	 */
	public function getDefaultViewName() {
		return 'List';
	}

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getDefaultViewName();
	}

	/**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getListViewName();
	}

	/**
	 * Function returns the All filter for the module
	 * @return <Int> custom filter id
	 */
	public function getAllFilterCvidForModule() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT cvid FROM vtiger_customview WHERE viewname = 'All' AND entitytype = ?",
					array($this->getName()));
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'cvid');
		}
		return false;
	}

	/**
	 * Function to get listview url with all filter
	 * @return <string> URL
	 */
	public function getListViewUrlWithAllFilter(){
		return $this->getListViewUrl().'&viewname='.$this->getAllFilterCvidForModule();
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getCreateRecordUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getEditViewName();
	}

	/**
	 * Function to get the url for the Create Record view of the module
	 * @return <String> - url
	 */
	public function getQuickCreateUrl() {
		return 'index.php?module='.$this->get('name').'&view=QuickCreateAjax';
	}

	/**
	 * Function to get the url for the Import action of the module
	 * @return <String> - url
	 */
	public function getImportUrl() {
		return 'index.php?module='.$this->get('name').'&view=Import';
	}

	/**
	 * Function to get the url for the Export action of the module
	 * @return <String> - url
	 */
	public function getExportUrl() {
		return 'index.php?module='.$this->get('name').'&view=Export';
	}

	/**
	 * Function to get the url for the Find Duplicates action of the module
	 * @return <String> - url
	 */
	public function getFindDuplicatesUrl() {
		return 'index.php?module='.$this->get('name').'&view=FindDuplicates';
	}

	/**
	 * Function to get the url to view Dashboard for the module
	 * @return <String> - url
	 */
	public function getDashBoardUrl() {
		return 'index.php?module='. $this->get('name').'&view=DashBoard';
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return <String> - url
	 */
	public function getDetailViewUrl($id) {
		return 'index.php?module='. $this->get('name').'&view='.$this->getDetailViewName().'&record='.$id;
	}
	/**
	 * Function to get a Vtiger Record Model instance from an array of key-value mapping
	 * @param <Array> $valueArray
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function getRecordFromArray($valueArray, $rawData=false) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $this->get('name'));
		$recordInstance = new $modelClassName();
		return $recordInstance->setData($valueArray)->setModuleFromInstance($this)->setRawData($rawData);
	}

	/**
	 * Function returns all the blocks for the module
	 * @return <Array of Vtiger_Block_Model> - list of block models
	 */
	public function getBlocks() {
		if(empty($this->blocks)) {
			$blocksList = array();
			$moduleBlocks = Vtiger_Block_Model::getAllForModule($this);
			foreach($moduleBlocks as $block){
				$blocksList[$block->get('label')] = $block;
			}
			$this->blocks = $blocksList;
		}
		return $this->blocks;
	}

	/**
	 * Function that returns all the fields for the module
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFields($blockInstance=false) {
		if(empty($this->fields)){
			$moduleBlockFields = Vtiger_Field_Model::getAllForModule($this);
			$this->fields = array();
			foreach($moduleBlockFields as $moduleFields){
				foreach($moduleFields as $moduleField){
					 $block = $moduleField->get('block');
					if(empty($block)) {
						continue;
				}
					$this->fields[$moduleField->get('name')] = $moduleField;
			}
			}
		}
		return $this->fields;
	}


	/**
	 * Function gives fields based on the type
	 * @param <String> $type - field type
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getFieldsByType($type) {
		if(!is_array($type)) {
			$type = array($type);
		}
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldType = $field->getFieldDataType();
			if(in_array($fieldType,$type)) {
				$fieldList[$field->getName()] = $field;
			}
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the type
	 * @return <Vtiger_Field_Model> with field label as key
	 */
	public function getFieldsByLabel() {
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldLabel = $field->get('label');
			$fieldList[$fieldLabel] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function gives fields based on the fieldid
	 * @return <Vtiger_Field_Model> with field id as key
	 */
	public function getFieldsById() {
		$fields = $this->getFields();
		$fieldList = array();
		foreach($fields as $field) {
			$fieldId = $field->getId();
			$fieldList[$fieldId] = $field;
		}
		return $fieldList;
	}

	/**
	 * Function returns all the relation models
	 * @return <Array of Vtiger_Relation_Model>
	 */
	public function getRelations() {
		if(empty($this->relations)) {
			return Vtiger_Relation_Model::getAllRelations($this);
		}
		return $this->relations;
	}

	/**
	 * Function that returns all the quickcreate fields for the module
	 * @return <Array of Vtiger_Field_Model> - list of field models
	 */
	public function getQuickCreateFields() {
		$blocksList = $this->getBlocks();
		$quickCreateFieldList = array();
		foreach($blocksList as $blockName => $blockModel) {
			$fieldList = $blockModel->getFields();
			foreach($fieldList as $fieldName => $fieldModel) {
				if($fieldModel->isQuickCreateEnabled() && $fieldModel->isEditable()) {
					$quickCreateFieldList[$fieldName] = $fieldModel;
				}
			}
		}
		return $quickCreateFieldList;
	}

	/**
	 * Function to get the field mode
	 * @param <String> $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName){
		return Vtiger_Field_Model::getInstance($fieldName,$this);
	}

	/**
	 * Function to get the field by column name.
	 * @param <String> $columnName - column name
	 * @return <Vtiger_Field_Model>
	 */
	public function getFieldByColumn($columnName) {
		$fields = $this->getFields();
		if ($fields) {
			foreach ($fields as $field) {
				if ($field->get('column') == $columnName) {
					return $field;
				}
			}
		}
		return NULL;
	}

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields(){

		$nameFieldObject = Vtiger_Cache::get('EntityField',$this->getName());
		$moduleName = $this->getName();
		if($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM vtiger_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array($this->getId()));
			$this->nameFields = array();
			if($result){
				$rowCount = $adb->num_rows($result);
				if($rowCount > 0){
					$fieldNames = $adb->query_result($result,0,'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}

			//added to handle entity names for these two modules
			//@Note: need to move these to database
			switch($moduleName) {
				case 'HelpDesk': $this->nameFields = array('ticket_title'); $fieldNames = 'ticket_title'; break;
				case 'Documents': $this->nameFields = array('notes_title'); $fieldNames = 'notes_title';  break;
			}
			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid =  $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname =  $fieldNames;
			Vtiger_Cache::set('EntityField',$this->getName(), $entiyObj);
		}

		return $this->nameFields;
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = $this->getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$query = 'SELECT * FROM vtiger_crmentity '.$nonAdminQuery.' WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function that returns deleted records condition
	 * @return <String>
	 */
	public function getDeletedRecordCondition() {
		return 'vtiger_crmentity.deleted = 0';
	}

	/**
	 * Funtion that returns fields that will be showed in the record selection popup
	 * @return <Array of fields>
	 */
	public function getPopupFields() {
		$entityInstance = CRMEntity::getInstance($this->getName());
		return $entityInstance->search_fields_name;
	}

	/**
	 * Function that returns related list header fields that will be showed in the Related List View
	 * @return <Array> returns related fields list.
	 */
	public function getRelatedListFields() {
		$relatedListFields = array();
		$entityInstance = CRMEntity::getInstance($this->getName());
		if (isset($entityInstance->list_fields_name)) {
			$list_fields_name = $entityInstance->list_fields_name;
			$list_fields = $entityInstance->list_fields;
			foreach ($list_fields as $key => $fieldInfo) {
				foreach ($fieldInfo as $columnName) {
					if(array_key_exists($key, $list_fields_name)){
						$relatedListFields[$columnName] = $list_fields_name[$key];
					}
				}
			}
		}
		return $relatedListFields;
	}

	public function getConfigureRelatedListFields(){
		$showRelatedFieldModel = $this->getHeaderAndSummaryViewFieldsList();
		$relatedListFields = array();
		if(count($showRelatedFieldModel) > 0) {
			foreach ($showRelatedFieldModel as $key => $field) {
				$relatedListFields[$field->get('column')] = $field->get('name');
			}
		}

		if(count($relatedListFields)>0) {
			$nameFields = $this->getNameFields();
			foreach($nameFields as $fieldName){
				if(!$relatedListFields[$fieldName]) {
					$fieldModel = $this->getField($fieldName);
					$relatedListFields[$fieldModel->get('column')] = $fieldModel->get('name');
				}
			}
		}

		return $relatedListFields;
	}

	public function isWorkflowSupported() {
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');
		if($this->isEntityModule() && VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			return true;
		}
		return false;
	}

	/**
	 * Function checks if a module has module sequence numbering
	 * @return boolean
	 */
	public function hasSequenceNumberField() {
		if(!empty($this->fields)) {
			$fieldList = $this->getFields();
			foreach($fieldList as $fieldName => $fieldModel) {
				if($fieldModel->get('uitype') === '4') {
					return true;
				}
			}
		}else{
			$db = PearDatabase::getInstance();
			$query = 'SELECT 1 FROM vtiger_field WHERE uitype=4 and tabid=?';
			$params = array($this->getId());
			$result = $db->pquery($query, $params);
			return $db->num_rows($result) > 0 ? true : false;
		}
		return false;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value) {
		$instance = false;
		$moduleObject = parent::getInstance($value);
		if($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
		}
		return $instance;
	}


	/**
	 * Function to get the instance of Vtiger Module Model from a given Vtiger_Module object
	 * @param Vtiger_Module $moduleObj
	 * @return Vtiger_Module_Model instance
	 */
	public static function getInstanceFromModuleObject(Vtiger_Module $moduleObj){
		$objectProperties = get_object_vars($moduleObj);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $objectProperties['name']);
		$moduleModel = new $modelClassName();
		foreach($objectProperties as $properName=>$propertyValue){
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given list of key-value mapping
	 * @param <Array> $valueArray
	 * @return Vtiger_Module_Model instance
	 */
	public static function getInstanceFromArray($valueArray) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $valueArray['name']);
		$instance = new $modelClassName();
		$instance->initialize($valueArray);
		return $instance;
	}

	/**
	 * Function to get all modules from CRM
	 * @param <array> $presence
	 * @param <array> $restrictedModulesList
	 * @return <array> List of module models <Vtiger_Module_Model>
	 */
	public static function getAll($presence = array(), $restrictedModulesList = array()) {
		$db = PearDatabase::getInstance();
		self::preModuleInitialize2();
		$cacheKey = 'modules';
		if(!$presence){
			$moduleModels = Vtiger_Cache::get('vtiger', $cacheKey);
		}else{
			$cacheKey = 'modules_'.implode("_",$presence);
			$moduleModels = Vtiger_Cache::get('vtiger', $cacheKey);
		}


		if(!$moduleModels){
			$moduleModels = array();

			$query = 'SELECT * FROM vtiger_tab';
			$params = array();
			if($presence) {
				$query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
				array_push($params, $presence);
			}

			$result = $db->pquery($query, $params);
			$noOfModules = $db->num_rows($result);
			for($i=0; $i<$noOfModules; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$moduleModels[$row['tabid']] = self::getInstanceFromArray($row);
				Vtiger_Cache::set('module',$row['tabid'], $moduleModels[$row['tabid']]);
				Vtiger_Cache::set('module',$row['name'], $moduleModels[$row['tabid']]);
			}
			if(!$presence){
				Vtiger_Cache::set('vtiger',$cacheKey,$moduleModels);
			}else{
				Vtiger_Cache::set('vtiger', $cacheKey,$moduleModels);
			}
		}

		if($presence && $moduleModels){
			foreach ($moduleModels as $key => $moduleModel){
				if(!in_array($moduleModel->get('presence'), $presence)){
					unset($moduleModels[$key]);
				}
			}
		}

		if($restrictedModulesList && $moduleModels) {
			foreach ($moduleModels as $key => $moduleModel){
				if(in_array($moduleModel->getName(), $restrictedModulesList)){
					unset($moduleModels[$key]);
				}
			}
		}

		return $moduleModels;
	}

	public static function getEntityModules() {
		self::preModuleInitialize2();
		$moduleModels = Vtiger_Cache::get('vtiger','EntityModules');
		if(!$moduleModels){
			$presence = array(0, 2);
			$moduleModels = self::getAll($presence);
			$restrictedModules = array('Webmails', 'Emails', 'Integration', 'Dashboard');
			foreach($moduleModels as $key => $moduleModel){
				if(in_array($moduleModel->getName(),$restrictedModules) || $moduleModel->get('isentitytype') != 1){
					unset($moduleModels[$key]);
				}
			}
			Vtiger_Cache::set('vtiger','EntityModules',$moduleModels);
		}
		return $moduleModels;
	}

	/**
	 * Function to get the list of all searchable modules
	 * @return <Array> - List of Vtiger_Module_Model instances
	 */
	public static function getSearchableModules() {
		$userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$entityModules = self::getEntityModules();

		$searchableModules = array();
		foreach ($entityModules as $tabid => $moduleModel) {
				$moduleName = $moduleModel->getName();
				if ($moduleName == 'Users' || $moduleName == 'Emails' || $moduleName == 'Events') continue;
				if($userPrivModel->hasModuleActionPermission($moduleModel->getId(), 'DetailView')) {
						$searchableModules[$moduleName] = $moduleModel;
				}
		}
		return $searchableModules;
	}

	protected static function preModuleInitialize2() {
		if(!Vtiger_Cache::get('EntityField','all')){
			$db = PearDatabase::getInstance();
			// Initialize meta information - to speed up instance creation (Vtiger_ModuleBasic::initialize2)
			$result = $db->pquery('SELECT modulename,tablename,entityidfield,fieldname FROM vtiger_entityname', array());

			for($index = 0, $len = $db->num_rows($result); $index < $len; ++$index) {

				$fieldNames = $db->query_result($result, $index, 'fieldname');
				$modulename = $db->query_result($result, $index, 'modulename');
				//added to handle entity names for these two modules
				//@Note: need to move these to database
				switch($modulename) {
					case 'HelpDesk': $fieldNames = 'ticket_title'; break;
					case 'Documents': $fieldNames = 'notes_title';  break;
				}
				$entiyObj = new stdClass();
				$entiyObj->basetable = $db->query_result($result, $index, 'tablename');
				$entiyObj->basetableid =  $db->query_result($result, $index, 'entityidfield');
				$entiyObj->fieldname =  $fieldNames;

				Vtiger_Cache::set('EntityField',$modulename,$entiyObj);
				Vtiger_Cache::set('EntityField','all',true);
			}
		}
	}

	public static function getPicklistSupportedModules() {
		vimport('~~/modules/PickList/PickListUtils.php');
		$modules = getPickListModules();
		$modulesModelsList = array();
		foreach($modules as $moduleLabel => $moduleName) {
			$instance = new self();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModelsList[] = $instance;
		}
		return $modulesModelsList;
	}

	public static function getCleanInstance($moduleName){
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Module', $moduleName);
		$instance = new $modelClassName();
		$instance->name = $moduleName;
		return $instance;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array(
			array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_RECENTLY_MODIFIED',
				'linkurl' => 'module='.$this->get('name').'&view=IndexAjax&mode=showActiveRecords',
				'linkicon' => ''
			),
		);
		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns export query - deprecated
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($where) {
		$focus = CRMEntity::getInstance($this->getName());
		$query = $focus->create_export_query($where);
		return $query;
	}

	/**
	 * Function returns the default custom filter for the module
	 * @return <Int> custom filter id
	 */
	public function getDefaultCustomFilter() {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT cvid FROM vtiger_customview WHERE setdefault = 1 AND entitytype = ?",
					array($this->getName()));
		if ($db->num_rows($result)) {
			return $db->query_result($result, 0, 'cvid');
		}
		return false;
	}

	/**
	 * Function returns latest comments for the module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel,$user, $dateFilter='') {
		$comments = array();
		if(!$this->isCommentEnabled()) {
			return $comments;
		}
		//TODO: need to handle security and performance
		$db = PearDatabase::getInstance();
		$params = array($this->getName());
		$sql = 'SELECT vtiger_modcomments.*,vtiger_crmentity.createdtime AS createdtime,vtiger_crmentity.smownerid AS smownerid 
				FROM vtiger_modcomments INNER JOIN vtiger_crmentity ON vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid 
				AND vtiger_crmentity.deleted = 0 
				INNER JOIN vtiger_crmentity crmentity2 ON vtiger_modcomments.related_to = crmentity2.crmid 
				AND crmentity2.deleted = 0 AND crmentity2.setype = ? 
				INNER JOIN vtiger_modtracker_basic ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid';

		$currentUser = Users_Record_Model::getCurrentUserModel();
		if($user === 'all') {
			if(!$currentUser->isAdminUser()) {
				$nonAdminAccessQuery = Users_Privileges_Model::getNonAdminAccessControlQuery('ModComments');
				$sql .= $nonAdminAccessQuery;
				$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
				$sql .= ' AND userid IN('.  generateQuestionMarks($accessibleUsers).')';
				$params = array_merge($params, $accessibleUsers);
			}
		}else{
			$sql .= ' AND userid = ?';
			$params[] = $user;
		}
		//handling date filter for history widget in home page
		if(!empty($dateFilter)) {
			$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
			$params[] = $dateFilter['start'];
			$params[] = $dateFilter['end'];
		}

		$sql .= ' ORDER BY vtiger_crmentity.createdtime DESC LIMIT ?, ?';
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit();
		$result = $db->pquery($sql,$params);

		$noOfRows = $db->num_rows($result);
		//setting up the count of records before checking permissions in history
		$pagingModel->set('historycount', $noOfRows);
		for($i=0; $i<$noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
			$commentModel->setData($row);
			$comments[] = $commentModel;
		}

		return $comments;
	}

	/**
	 * Function returns comments and recent activities across module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type='', $userId='', $dateFilter='') {
		if(empty($userId)) $userId = 'all';
				if(empty($type)) $type = 'all';

		//TODO: need to handle security
		$comments = array();
		if($type == 'all' || $type == 'comments') {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			if($modCommentsModel->isPermitted('DetailView')){
				$comments = $this->getComments($pagingModel, $userId, $dateFilter);
			}
			if($type == 'comments') {
				return $comments;
			}
		}

		$db = PearDatabase::getInstance();
				$sql = 'SELECT vtiger_modtracker_basic.*
								FROM vtiger_modtracker_basic
								INNER JOIN vtiger_crmentity ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
								AND module = ?';

				$currentUser = Users_Record_Model::getCurrentUserModel();
				$params = array($this->getName());

				if($userId === 'all') {
					if(!$currentUser->isAdminUser()) {
						$accessibleUsers = array_keys($currentUser->getAccessibleUsers());
						$sql .= ' AND whodid IN ('.  generateQuestionMarks($accessibleUsers).')';
						$params = array_merge($params, $accessibleUsers);
					}
				}else{
					$sql .= ' AND whodid = ?';
					$params[] = $userId;
				}
				//handling date filter for history widget in home page
				if(!empty($dateFilter)) {
					$sql .= ' AND vtiger_modtracker_basic.changedon BETWEEN ? AND ? ';
					$params[] = $dateFilter['start'];
					$params[] = $dateFilter['end'];
				}

				$sql .= ' ORDER BY vtiger_modtracker_basic.id DESC LIMIT ?, ?';
				$params[] = $pagingModel->getStartIndex();
				$params[] = $pagingModel->getPageLimit();
		$result = $db->pquery($sql,$params);

		$activites = array();
		$noOfRows = $db->num_rows($result);
		//set the records count before checking permissions and unsetting it
		//If updates count more than comments count, this count should consider
		if($pagingModel->get('historycount') < $noOfRows) {
			$pagingModel->set('historycount', $noOfRows);
		}
		for($i=0; $i<$noOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if(Users_Privileges_Model::isPermitted($row['module'], 'DetailView', $row['crmid'])){
				$modTrackerRecorModel = new ModTracker_Record_Model();
				$modTrackerRecorModel->setData($row)->setParent($row['crmid'], $row['module']);
				$time = $modTrackerRecorModel->get('changedon');
				$activites[] = $modTrackerRecorModel;
			}
		}

		$history = array_merge($activites, $comments);

		$dateTime = array();
		foreach($history as $model) {
			if(get_class($model) == 'ModComments_Record_Model') {
				$time = $model->get('createdtime');
			} else {
				$time = $model->get('changedon');
			}
			$dateTime[] = $time;
		}

		if(!empty($history)) {
			array_multisort($dateTime,SORT_DESC,SORT_STRING,$history);
			return $history;
		}
		return false;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT vtiger_crmentity.crmid, crmentity2.crmid AS parent_id, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_activity.* FROM vtiger_activity
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
					INNER JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
					INNER JOIN vtiger_crmentity AS crmentity2 ON vtiger_seactivityrel.crmid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE vtiger_crmentity.deleted=0
					AND (vtiger_activity.activitytype NOT IN ('Emails'))
					AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held','Cancelled'))";

		if(!$currentUser->isAdminUser()) {
			$moduleFocus = CRMEntity::getInstance('Calendar');
			$condition = $moduleFocus->buildWhereClauseConditionForCalendar();
			if($condition) {
				$query .= ' AND '.$condition;
			}
		}

		$params = array($this->getName());

		if ($recordId) {
			$query .= " AND vtiger_seactivityrel.crmid = ?";
			array_push($params, $recordId);
		} elseif ($mode === 'upcoming') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype='Task' THEN due_date >= '$currentDate' ELSE CONCAT(due_date,' ',time_end) >= '$nowInDBFormat' END";
		} elseif ($mode === 'overdue') {
			$query .= " AND CASE WHEN vtiger_activity.activitytype='Task' THEN due_date < '$currentDate' ELSE CONCAT(due_date,' ',time_end) < '$nowInDBFormat' END";
		}

		if($user != 'all' && $user != '') {
			$query .= " AND vtiger_crmentity.smownerid = ?";
			array_push($params, $user);
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);


		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$activities = array();
		$recordsToUnset = array();
		for($i=0; $i<$numOfRows; $i++) {
			$newRow = $db->query_result_rowdata($result, $i);
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$ownerId = $newRow['smownerid'];
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$visibleFields = array('activitytype','date_start','time_start','due_date','time_end','assigned_user_id','visibility','smownerid','crmid');
			$visibility = true;
			if(in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if($ownerId == $currentUser->getId()){
				$visibility = false;
			}
			if(!$currentUser->isAdminUser() && $newRow['activitytype'] != 'Task' && $newRow['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach($newRow as $data => $value) {
					if(in_array($data, $visibleFields) != -1) {
						unset($newRow[$data]);
					}
				}
				$newRow['subject'] = vtranslate('Busy','Events').'*';
			}
			if($newRow['activitytype'] == 'Task') {
				unset($newRow['visibility']);

				$due_date = $newRow["due_date"];
				$dayEndTime = "23:59:59";
				$EndDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($due_date." ".$dayEndTime);
				$dueDateTimeInDbFormat = explode(' ',$EndDateTime);
				$dueTimeInDbFormat = $dueDateTimeInDbFormat[1];
				$newRow['time_end'] = $dueTimeInDbFormat;
			}

			$model->setData($newRow);
			$model->setId($newRow['crmid']);
			$activities[$newRow['crmid']] = $model;
			if(!$currentUser->isAdminUser() && $newRow['activitytype'] == 'Task' && isToDoPermittedBySharing($newRow['crmid']) == 'no') { 
				$recordsToUnset[] = $newRow['crmid'];
			}
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		//after setting paging model, unsetting the records which has no permissions
		foreach($recordsToUnset as $record) {
			unset($activities[$record]);
		}

		return $activities;
	}

	/**
	 * Function to get list of fields which are required while importing records
	 * @param <String> $module
	 * @return <Array> list of fields
	 */
	function getRequiredFields($module = '') {
		$moduleInstance = CRMEntity::getInstance($this->getName());
		$requiredFields = $moduleInstance->required_fields;
		if (empty ($requiredFields)) {
			if (empty ($module)) {
				$module = $this->getName();
			}
			$moduleInstance->initRequiredFields($module);
		}
		return $moduleInstance->required_fields;
	}

	/**
	 * Function to get the module is permitted to specific action
	 * @param <String> $actionName
	 * @return <boolean>
	 */
	public function isPermitted($actionName) {
		return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if($relatedModule == 'Documents'){
			return ' AND vtiger_notes.filestatus = 1 ';
		}
		return;
	}

	/**
	 * Function to get where condition query for dashboards
	 * @param <Integer> $owner
	 * @return <String> query
	 */
	public function getOwnerWhereConditionForDashBoards ($owner) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($this->getName());
		$params = array();
		if(!empty($owner) && $currentUserModel->isAdminUser()) {//If admin user, then allow users data
			$ownerSql =  ' smownerid = '. $owner;
			$params[] = $owner;
		} else if(!empty($owner)){//If not admin user, then check sharing access for that module
			if($sharingAccessModel->isPrivate()) {
				$subordinateUserModels = $currentUserModel->getSubordinateUsers();
				$subordinateUsers = array();
				foreach($subordinateUserModels as $id=>$name) {
					$subordinateUsers[] = $id;
				}
				if(in_array($owner, $subordinateUsers)) {
					$ownerSql = ' smownerid = '. $owner ;
				} else {
					$ownerSql = ' smownerid = '. $currentUserModel->getId();
				}
			} else {
				$ownerSql = ' smownerid = '. $owner ;
			}
		} else {//If no owner filter, then check if the module access is Private
			if($sharingAccessModel->isPrivate() && (!$currentUserModel->isAdminUser())) {
				$subordinateUserModels = $currentUserModel->getSubordinateUsers();
				foreach($subordinateUserModels as $id=>$name) {
					$subordinateUsers[] = $id;
					$params[] = $id;
				}
				if($subordinateUsers) {
					$ownerSql =  ' smownerid IN ('. implode(',' , $subordinateUsers) .')';
				} else {
					$ownerSql =  ' smownerid = '.$currentUserModel->getId();
				}
			}
		}
		return $ownerSql;
	}

	/**
	 * Function to get Module Header Links (for Vtiger7)
	 * @return array
	 */
	public function getModuleBasicLinks(){
		if(!$this->isEntityModule() && $this->getName() !== 'Users') {
			return array();
		}
		$createPermission = Users_Privileges_Model::isPermitted($this->getName(), 'CreateView');
		$moduleName = $this->getName();
		$basicLinks = array();
		if($createPermission) {
			if($moduleName === "Calendar"){
				$basicLinks[] = array(
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_ADD_TASK',
					'linkurl' => $this->getCreateTaskRecordUrl(),
					'linkicon' => 'fa-plus'
				);
				$basicLinks[] = array(
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_ADD_EVENT',
					'linkurl' => $this->getCreateEventRecordUrl(),
					'linkicon' => 'fa-plus'
				);
			} else {
				$basicLinks[] = array(
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => $this->getCreateRecordUrl(),
					'linkicon' => 'fa-plus'
				);
			}
			$importPermission = Users_Privileges_Model::isPermitted($this->getName(), 'Import');
			if($importPermission && $createPermission) {
				$basicLinks[] = array(
					'linktype' => 'BASIC',
					'linklabel' => 'LBL_IMPORT',
					'linkurl' => $this->getImportUrl(),
					'linkicon' => 'fa-download'
				);
			}
		}
		return $basicLinks;
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks(){
		if(!$this->isEntityModule() && $this->getName() !== 'Users') {
			return array();
		}

		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();

		$currentUser = Users_Record_Model::getCurrentUserModel();

		if($currentUser->isAdminUser()) {
			$settingsLinks[] = array(
						'linktype' => 'LISTVIEWSETTING',
						'linklabel' => 'LBL_EDIT_FIELDS',
						'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
						'linkicon' => $layoutEditorImagePath
			);

			if($this->isWorkflowSupported()) {
				$settingsLinks[] = array(
						'linktype' => 'LISTVIEWSETTING',
						'linklabel' => 'LBL_EDIT_WORKFLOWS',
						'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule='.$this->getName(),
						'linkicon' => $editWorkflowsImagePath
				);
			}

			$settingsLinks[] = array(
						'linktype' => 'LISTVIEWSETTING',
						'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
						'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module='.$this->getName(),
						'linkicon' => ''
			);

			if($this->hasSequenceNumberField()) {
				$settingsLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
					'linkurl' => 'index.php?parent=Settings&module=Vtiger&view=CustomRecordNumbering&sourceModule='.$this->getName(),
					'linkicon' => ''
				);
			}

			$webForms = Vtiger_Module_Model::getInstance('Webforms');
			if ($webForms && $webForms->isActive()) {
				$webformSupportedModule = Settings_Webforms_Module_Model :: getSupportedModulesList();
				if(array_key_exists($this->getName(), $webformSupportedModule)){
					$settingsLinks[] =	array(
							'linktype' => 'LISTVIEWSETTING',
							'linklabel' => 'LBL_SETUP_WEBFORMS',
							'linkurl' => 'index.php?module=Webforms&parent=Settings&view=Edit&sourceModule='.$this->getName(),
							'linkicon' => '');
				}
			}
		}

		return $settingsLinks;
	}

	public function isCustomizable() {
		return $this->customized == '1' ? true : false;
	}

	public function isModuleUpgradable() {
		return $this->isCustomizable() ? true : false;
	}

	public function isExportable() {
		return $this->isCustomizable() ? true : false;
	}

	/**
	 * Function to get list of field for summary view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	public function getSummaryViewFieldsList() {
		if (!$this->summaryFields) {
			$summaryFields = array();
			$fields = $this->getFields();
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel->isSummaryField() && $fieldModel->isViewable()) {
					$summaryFields[$fieldName] = $fieldModel;
				}
			}
			$this->summaryFields = $summaryFields;
		}
		return $this->summaryFields;
	}

	/**
	 * Function to get list of field for header view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	public function getHeaderViewFieldsList() {
		if (!$this->headerFields) {
			$headerFields = array();
			$fields = $this->getFields();
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel->isHeaderField() && $fieldModel->isViewable()) {
					$headerFields[$fieldName] = $fieldModel;
				}
			}
			$this->headerFields = $headerFields;
		}
		return $this->headerFields;
	}

	/**
	 * Function to get list of field for header view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 */
	public function getHeaderAndSummaryViewFieldsList() {
		if(!$this->relationListViewFields) {
			$summaryViewFields = $this->getSummaryViewFieldsList();
			$headerViewFields = $this->getHeaderViewFieldsList();
			$allRelationListViewFields = array_merge($headerViewFields,$summaryViewFields);
			$relationListViewFields = array();
			$nameFields = $this->getNameFields();
			foreach($nameFields as $nameField) {
				if(array_key_exists($nameField, $summaryViewFields)) {
					$relationListViewFields[$nameField] = $summaryViewFields[$nameField];
				}
			}
			foreach($allRelationListViewFields as $fieldName => $fieldModel) {
				if(!in_array($fieldName, $nameFields)) {
					$relationListViewFields[$fieldName] = $fieldModel;
				}
			}
			$this->relationListViewFields = $relationListViewFields;
		}
		return $this->relationListViewFields;
	}


	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue,$searchFields, $parentId=false, $parentModule=false) {
		return "SELECT ".implode(',',$searchFields)." FROM vtiger_crmentity WHERE label LIKE '%$searchValue%' AND vtiger_crmentity.deleted = 0";
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
			$searchFields = array('crmid','label','setype');
		if(!empty($searchValue) && empty($parentId) && empty($parentModule)) {
			$matchingRecords = Vtiger_Record_Model::getSearchResult($searchValue, $this->getName());
		} else if($parentId && $parentModule) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery($this->getSearchRecordsQuery($searchValue,$searchFields, $parentId, $parentModule), array());
			$noOfRows = $db->num_rows($result);

			$moduleModels = array();
			$matchingRecords = array();
			for($i=0; $i<$noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])){
					$row['id'] = $row['crmid'];
					$moduleName = $row['setype'];
					if(!array_key_exists($moduleName, $moduleModels)) {
						$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
					}
					$moduleModel = $moduleModels[$moduleName];
					$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
					$recordInstance = new $modelClassName();
					$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				}
			}
		}

		return $matchingRecords;
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule, $relationId) {
		$relatedModuleName = $relatedModule->getName();

		$focus = CRMEntity::getInstance($this->getName());
		$focus->id = $recordId;

		$result = $focus->$functionName($recordId, $this->getId(), $relatedModule->getId(), $relationId);
		$query = $result['query'] .' '. $this->getSpecificRelationQuery($relatedModuleName);
		$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);

		//modify query if any module has summary fields, those fields we are displayed in related list of that module
		$relatedListFields = $relatedModule->getConfigureRelatedListFields();

		if($relatedModuleName == 'Documents') {
			$relatedListFields['filelocationtype'] = 'filelocationtype';
			$relatedListFields['filestatus'] = 'filestatus';
		}

		if(count($relatedListFields) > 0) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
			$queryGenerator->setFields($relatedListFields);
			$selectColumnSql = $queryGenerator->getSelectClauseColumnSQL();
			$newQuery = preg_split('/FROM/i', $query);
			$selectColumnSql = 'SELECT DISTINCT vtiger_crmentity.crmid,'.$selectColumnSql;
			$query = $selectColumnSql.' FROM '.$newQuery[1];
		}

		if ($nonAdminQuery) {
			$query = appendFromClauseToQuery($query, $nonAdminQuery);

			if($functionName == 'get_activities' && trim($nonAdminQuery)) {
				$moduleFocus = CRMEntity::getInstance('Calendar');
				$condition = $moduleFocus->buildWhereClauseConditionForCalendar();
				if($condition) {
					$query .= ' AND '.$condition;
				}
			}
		}

		return $query;
	}

	/**
	 * Function to get Non admin access control query
	 * @param <String> $relatedModuleName
	 * @return <String>
	 */
	public function getNonAdminAccessControlQueryForRelation($relatedModuleName) {
		$modulesList = array('Faq', 'PriceBook', 'Vendors', 'Users');

		if (!in_array($relatedModuleName, $modulesList)) {
			return Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleName);
		}
	}

	/**
	 * Function returns the default column for Alphabetic search
	 * @return <String> columnname
	 */
	public function getAlphabetSearchField(){
		$focus = CRMEntity::getInstance($this->get('name'));
		return $focus->def_basicsearch_col;
	}

	/**
	 * Function which will give complusory mandatory fields
	 * @return type
	 */
	public function getCompulsoryMandatoryFieldList() {
		$focus = CRMEntity::getInstance($this->getName());
		$compulsoryMandtoryFields = $focus->mandatory_fields;
		if(empty($compulsoryMandtoryFields)) {
			$compulsoryMandtoryFields = array();
		}
		return $compulsoryMandtoryFields;
	}


	/**
	 * Function returns all the related modules for workflows create entity task
	 * @return <JSON>
	 */
	public function vtJsonDependentModules() {
		vimport('~~/modules/com_vtiger_workflow/WorkflowComponents.php');
		$db = PearDatabase::getInstance();
		$param = array('modulename'=>$this->getName());
		return vtJsonDependentModules($db, $param);
	}

	/**
	 * Function returns mandatory field Models
	 * @return <Array of Vtiger_Field_Model>
	 */
	public function getMandatoryFieldModels(){
		$fields = $this->getFields();
		$mandatoryFields = array();
		if ($fields) {
			foreach ($fields as $field) {
				if ($field->isMandatory()) {
					$mandatoryFields[] = $field;
				}
			}
		}
		return $mandatoryFields;
	}

	public function getRelatedModuleRecordIds(Vtiger_Request $request, $recordIds = array(), $nonAdminCheck = false) {
		$db = PearDatabase::getInstance();
		$relationIds = $request->get('related_modules');
		if(empty($relationIds))  return array();

		$focus = CRMEntity::getInstance($this->getName());
		$relatedModuleMapping = $focus->related_module_table_index;

		$relationFieldMapping = array();
		$queryParams = array($this->getId());
		foreach($relationIds as $reltionId) {
			array_push($queryParams,$reltionId);
		}
		$query = "SELECT relationfieldid,related_tabid
					FROM vtiger_relatedlists
					WHERE vtiger_relatedlists.tabid=? AND relation_id IN (".generateQuestionMarks($relationIds).")";


		$relationRes = $db->pquery($query,$queryParams);

		$num_rows = $db->num_rows($relationRes);
		for($i=0 ;$i<$num_rows; $i++) {
			$relatedTabId = $db->query_result($relationRes,$i,'related_tabid');
			$relationfieldid = $db->query_result($relationRes,$i,'relationfieldid');
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedTabId);
			$relationFieldMapping[] = array('relatedModuleName'=>$relatedModuleModel->getName(),'relationfieldid'=>$relationfieldid);
		}

		$relatedIds = array();
		if(!empty($relationFieldMapping)) {
			foreach ($relationFieldMapping as $mappingDetails){
			//for ($i=0; $i<count($relatedModules); $i++) {
				$params = array();
				$module = $mappingDetails['relatedModuleName'];
				$relationFieldId = $mappingDetails['relationfieldid'];
				$sql = "SELECT vtiger_crmentity.crmid FROM vtiger_crmentity";

				if($nonAdminCheck) {
					if(empty($relatedModuleFocus)) $relatedModuleFocus = CRMEntity::getInstance($module);
					$user = Users_Record_Model::getCurrentUserModel();
					$relationAccessQuery = $relatedModuleFocus->getNonAdminAccessControlQuery($module, $user);
					$sql .= ' '.$relationAccessQuery;
				}

				if(empty($relationFieldId)){
					$tablename = $relatedModuleMapping[$module]['table_name'];
					$tabIndex = $relatedModuleMapping[$module]['table_index'];
					$relIndex = $relatedModuleMapping[$module]['rel_index'];

					//Fallback to vtiger_crmentityrel if both focus and relationfieldid is empty
					if(empty($tablename)) {
						$tablename = 'vtiger_crmentityrel';
						$tabIndex = 'crmid';
						$relIndex = 'crmid';
					}
					//END

					if($tablename == 'vtiger_crmentityrel'){
						$sql .= " INNER JOIN $tablename ON ($tablename.relcrmid = vtiger_crmentity.crmid OR $tablename.crmid = vtiger_crmentity.crmid)
							WHERE ($tablename.crmid IN (".  generateQuestionMarks($recordIds).")) OR ($tablename.relcrmid IN (".  generateQuestionMarks($recordIds)."))";
						foreach ($recordIds as $key => $recordId) {
							array_push($params, $recordId);
						}
					} else {
						$sql .= " INNER JOIN $tablename ON $tablename.$tabIndex = vtiger_crmentity.crmid
							WHERE $tablename.$relIndex IN (".  generateQuestionMarks($recordIds).")";
					}
				}else{
					$fieldModel = Vtiger_Field_Model::getInstance($relationFieldId);
					$relatedModuleFocus = CRMEntity::getInstance($module);
					$tablename = $fieldModel->get('table');
					$relIndex = $fieldModel->get('column');
					if($tablename == $relatedModuleFocus->table_name){
						$tabIndex = $relatedModuleFocus->table_index;
						$sql .= " INNER JOIN $tablename ON $tablename.$tabIndex = vtiger_crmentity.crmid
							WHERE $tablename.$relIndex IN (".  generateQuestionMarks($recordIds).")";
					}else{
						$modulePrimaryTableName = $relatedModuleFocus->table_name;
						$modulePrimaryTableIndex = $relatedModuleFocus->table_index;
						$tabIndex = $relatedModuleFocus->tab_name_index[$tablename];
						$sql .= " INNER JOIN $modulePrimaryTableName ON $modulePrimaryTableName.$modulePrimaryTableIndex = vtiger_crmentity.crmid
							INNER JOIN $tablename ON $tablename.$tabIndex = $modulePrimaryTableName.$modulePrimaryTableIndex 
							WHERE $tablename.$relIndex IN (".  generateQuestionMarks($recordIds).")";
					}
				}

				$sql .=' AND vtiger_crmentity.deleted = 0';
				foreach ($recordIds as $key => $recordId) {
					array_push($params, $recordId);
				}

				$result1 = $db->pquery($sql, $params);
				$num_rows = $db->num_rows($result1);
				for($j=0; $j<$num_rows; $j++){
					$relatedIds[] = $db->query_result($result1, $j, 'crmid');
				}
			}
			return $relatedIds;
		} else {
			return $relatedIds;
		}
	}



	public function transferRecordsOwnership($transferOwnerId, $relatedModuleRecordIds){
		$moduleName = $this->getName();
		foreach($relatedModuleRecordIds as $recordId) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				try {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
					$recordModel->set('assigned_user_id', $transferOwnerId);
					$recordModel->set('mode', 'edit');
					// Transferring ownership with related module as Inventory modules, removes line item details.
					// So setting $_REQUEST['ajxaction'] to DETAILVIEW
					$_REQUEST['ajxaction'] = 'DETAILVIEW';
					$recordModel->save();
				} catch (DuplicateException $e) {
					return $e->getDuplicationMessage();
				} catch (Exception $e) {
				}
			}
		}
	}

	/**
	* Function to get orderby sql from orderby field
	*/
	public function getOrderBySql($orderBy){
			 $orderByField = $this->getFieldByColumn($orderBy);
			 return $orderByField->get('table') . '.' . $orderBy;
	}

	 public function getDefaultSearchField(){
		$nameFields = $this->getNameFields();
		//To make the first field as the name field
		return $nameFields[0];
	}

	/**
	 * Function to get popup view fields
	 */
	public function getPopupViewFieldsList(){
		$summaryFieldsList = $this->getHeaderAndSummaryViewFieldsList();

		if(count($summaryFieldsList) > 0){
			 $popupFields = array_keys($summaryFieldsList);
		}else{
			$popupFields = array_values($this->getRelatedListFields());
		}
		return $popupFields;
	}

	/**
	 * Funxtion to identify if the module supports quick search or not
	 */
	public function isQuickSearchEnabled() {
		return true;
	}

	/**
	 * function to check if the extension module is permitted for utility action
	 * @return <boolean> false
	 */
	public function isUtilityActionEnabled() {
		return false;
	}

	public function isListViewNameFieldNavigationEnabled() {
		return true;
	}

	/**
	 * function to check if duplicate option is allowed in DetailView
	 * @param <string> $action, $recordId
	 * @return <boolean>
	 */
	public function isDuplicateOptionAllowed($action, $recordId) {
		return Users_Privileges_Model::isPermitted($this->getName(), $action, $recordId);
	}

	/**
	 * function to check if the module is related to supplied module name
	 * @param <string> $moduleName
	 * @return boolean - true if module is related
	 */
	public function isModuleRelated($moduleName) {
		$relations = $this->getRelations();
		$relatedModules = array();
		foreach($relations as $relation){
			$relatedModules[] = $relation->getRelationModuleName();
		}
		if(in_array($moduleName, $relatedModules)) return true; return false;
	}

	/**
	 * Function to get the autofill reference module for a module
	 * @param <String> $moduleName
	 * @return <String> Auto Fill Module Name
	 */
	public function getAutoFillModule($moduleName) {
		$autoFillModules = array();
		switch ($moduleName) {
			case 'Contacts'	: $autoFillModules[] = 'Accounts';					break;
			case 'Project'	: $autoFillModules = array('Contacts','Accounts');	break;
		}
		return $autoFillModules;
	}

	/**
	 * Function to get the Auto Fill Module and Field in array 
	 * @param <String> $moduleName
	 * @return <Array> $autoFill - Contains Module Name and Field Name
	 */
	public function getAutoFillModuleAndField($moduleName) {
		$autoFill = array();
		$autoFillValues = $fieldNamesList = array();
		$autoFillModule = $this->getAutoFillModule($moduleName);
		if (!$autoFillModule) {
			$referenceFields = $this->getFieldsByType('reference');
			foreach ($referenceFields as $fieldName=>$fieldModel) {
				$referenceList = $fieldModel->getReferenceList();
				foreach ($referenceList as $referenceModuleName) {
					if($referenceModuleName == $autoFillModule && !$fieldModel->isCustomField() && !in_array($fieldName, $fieldNamesList)) {
						$autoFill['module'] = $autoFillModule;
						$autoFill['fieldname'] = $fieldName;
						$fieldNamesList[] = $fieldName;
						$autoFillValues[] = $autoFill;
						break;
					}
				}
			}
		}
		return $autoFillValues;
	}

	/**
	* Function is used to give links in the All menu bar
	*/
	public function getQuickMenuModels() {
		if($this->isEntityModule()) {
			$moduleName = $this->getName();
			$listViewModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
			$basicListViewLinks = $listViewModel->getBasicLinks();

			$createPermission = Users_Privileges_Model::isPermitted($moduleName, 'CreateView');
			$importPermission = Users_Privileges_Model::isPermitted($moduleName, 'Import');
			if($importPermission && $createPermission) {
				$basicListViewLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_IMPORT',
					'linkurl' => $this->getImportUrl(),
					'linkicon' => ''
				);
			}
		}
		if($basicListViewLinks) {
			foreach($basicListViewLinks as $basicListViewLink) {
				if(is_array($basicListViewLink)) {
					$links[] = Vtiger_Link_Model::getInstanceFromValues($basicListViewLink);
				} else if(is_a($basicListViewLink, 'Vtiger_Link_Model')) {
					$links[] = $basicListViewLink;
				}
			}
		}
		return $links;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array('Import', 'Export', 'Merge', 'DuplicatesHandling');
	}

	/*
	 * Function to get pair of supported utility actionid and action name.
	 * Used when creating a new profile.
	 */
	function getUtilityActions() {
		$utilityActions = array();
		$utilityActionsArray = $this->getUtilityActionsNames();

		foreach($utilityActionsArray as $utilityAction) {
			$utilityActionId = getActionid($utilityAction);
			$utilityActions[$utilityActionId] = $utilityAction;
		}

		return $utilityActions;
	}

	/**
	 * function to check if module allows profile level utility
	 * @return <boolean>
	 */
	public function isProfileLevelUtilityAllowed() {
		return true;
	}

	/**
	 * function to check if module is restricted from compose email popup
	 * @return <boolean>
	 */
	public function restrictToListInComposeEmailPopup() {
		//does not restrict the module to be listed in compose email popup
		return false;
	}

	public function getAdditionalImportFields() {
		return array();
	}

	/**
	 * Function which will be give you the actions that are allowed when this module is added as a tab 
	 */
	public function getRelationShipActions() {
		return array("ADD","SELECT");
	}


	public function isNew() {
		return false;
	}

	/** 
	 * Function to get the basic view url of extension 
	 * @param type $sourceModule 
	 * @return type 
	 */ 
	function getBaseExtensionUrl($sourceModule) { 
		return 'index.php?module='.$sourceModule.'&view=Extension&extensionModule='.$this->getName().'&extensionView=Index'; 
	} 

	function getExtensionSettingsUrl($sourceModule) { 
		return $this->getBaseExtensionUrl($sourceModule).'&mode=settings'; 
	}

	function getExtensionLogsListViewUrl($sourceModule) { 
		return $this->getBaseExtensionUrl($sourceModule).'&mode=showLogs'; 
	} 

	/**
	 * Function get the launch url of extension module.
	 */
	function getExtensionLaunchUrl() {
		return 'index.php?module='.$this->getName().'&view=List';
	}

	function isFilterColumnEnabled() {
		return true;
	}

	function isStarredEnabled(){
		return true;
	}

	/**
	 * Function to get the app name for module
	 */
	function getAppName() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT appname,visible FROM vtiger_app2tab WHERE tabid = ?', array($this->getId()));
		$count = $db->num_rows($result);
		$apps = array();
		if ($count > 0) {
			for ($i = 0; $i < $count; $i++) {
				$appName = $db->query_result($result, $i, 'appname');
				$visibility = $db->query_result($result, $i, 'visible');
				$apps[$appName] = $visibility;
			}
		}

		return $apps;
	}

	public function getCustomPicklistDependency() {
		return array();
	}

	function isTagsEnabled() {
		return true;
	}

	
	public static function getSyncActionsInDuplicatesCheck() {
		return array(	1 => 'LBL_PREFER_LATEST_RECORD',
						2 => 'LBL_PREFER_INTERNAL_RECORD',
//						3 => 'LBL_PREFER_VTIGER_RECORD',
						4 => 'LBL_PREFER_EXTERNAL_RECORD');
	}

	
	public function isFieldsDuplicateCheckAllowed() {
		return true;
	}

	public function isExcelEditAllowed() {
		return $this->isPermitted('EditView');
	}

	public function getModuleIcon() {
		$moduleName = $this->getName();
		$lowerModuleName = strtolower($moduleName);
		$title = vtranslate($moduleName, $moduleName);

		$moduleIcon = "<i class='vicon-$lowerModuleName' title='$title'></i>";
		if ($this->source == 'custom') {
			$moduleShortName = mb_substr(trim($title), 0, 2);
			$moduleIcon = "<span class='custom-module' title='$title'>$moduleShortName</span>";
		}

		$imageFilePath = 'layouts/'.Vtiger_Viewer::getLayoutName()."/modules/$moduleName/$moduleName.png";
		if (file_exists($imageFilePath)) {
			$moduleIcon = "<img src='$imageFilePath' title='$title'/>";
		}

		return $moduleIcon;
	}

	public static function getModuleIconPath($moduleName) {
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		return $moduleModel->getModuleIcon();
	}
}
