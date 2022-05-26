<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class Vtiger_Record_Model extends Vtiger_Base_Model {

	protected $module = false;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value) {
		return $this->set('id',$value);
	}

	/**
	 * Function to get column fields of record
	 * @return <Array> 
	 */
	public function getData(){
		$data = $this->valueMap;
		// column_fields will be a trackable object, we should get column fields from that object
		if(is_object($data)){
			return $data->getColumnFields();
		}
		return $data;
	}

	/**
	 * Fuction to get the Name of the record
	 * @return <String> - Entity Name of the record
	 */
	public function getName() {
		$displayName = $this->get('label');
		$module = $this->getModule();
		$entityFields = $module->getNameFields();
		if($entityFields){
			foreach($entityFields as $field){
				if($this->get($field)){
					$name[] = $this->get($field);
				}
			}
			if(!empty($name)){
				$displayName = implode(" ", $name);
			}
		}

		if(empty($displayName)) {
			$displayName = $this->getDisplayName();
		}
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	/**
	 * Function to get the Module to which the record belongs
	 * @return Vtiger_Module_Model
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName) {
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param <Vtiger_Module_Model> $module
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the entity instance of the recrod
	 * @return CRMEntity object
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
		return $this;
	}

	/**
	 * Function to get raw data
	 * @return <Array>
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/**
	 * Function to set raw data
	 * @param <Array> $data
	 * @return Vtiger_Record_Model instance
	 */
	public function setRawData($data) {
		$this->rawData = $data;
		return $this;
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getFullDetailViewUrl() {
		$module = $this->getModule();
		// If we don't send tab label then it will show full detail view, but it will select summary tab
		$moduleName = $this->getModuleName();
		$fullDetailViewLabel = vtranslate('SINGLE_'.$moduleName, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName);
		return 'index.php?module='.$moduleName.'&view='.$module->getDetailViewName().'&record='.$this->getId().'&mode=showDetailViewByMode&requestMode=full&tab_label='.$fullDetailViewLabel;
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the Update View url for the record
	 * @return <String> - Record Upadte view Url
	 */
	public function getUpdatesUrl() {
		return $this->getDetailViewUrl()."&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES";
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&action='.$module->getDeleteActionName().'&record='.$this->getId();
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return <String> - Record Module Name
	 */
	public function getModuleName() {
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return Vtiger_Util_Helper::getRecordName($this->getId());
	}

	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @return <String>
	 */
	public function getDisplayValue($fieldName,$recordId = false) {
		if(empty($recordId)) {
			$recordId = $this->getId();
		}
		$fieldModel = $this->getModule()->getField($fieldName);

		// For showing the "Date Sent" and "Time Sent" in email related list in user time zone
		if($fieldName == "time_start" && $this->getModule()->getName() == "Emails"){
			$date = new DateTime();
			$dateTime = new DateTimeField($date->format('Y-m-d').' '.$this->get($fieldName));
			$value = Vtiger_Time_UIType::getDisplayValue($dateTime->getDisplayTime());
			$this->set($fieldName, $value);
			return $value;
		}else if($fieldName == "date_start" && $this->getModule()->getName() == "Emails"){
			$dateTime = new DateTimeField($this->get($fieldName).' '.$this->get('time_start'));
			$value = $dateTime->getDisplayDate();
			$this->set($fieldName, $value);
			return $value;
		}
		// End

		if($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $recordId, $this);
		}
		return false;
	}

	/**
	 * Function returns the Vtiger_Field_Model
	 * @param <String> $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName) {
		return $this->getModule()->getField($fieldName);
	}

	/**
	 * Function returns all the field values in user format
	 * @return <Array>
	 */
	public function getDisplayableValues() {
		$displayableValues = array();
		$data = $this->getData();
		foreach($data as $fieldName=>$value) {
			$fieldValue = $this->getDisplayValue($fieldName);
			$displayableValues[$fieldName] = ($fieldValue || $fieldValue === '0') ? $fieldValue : $value;
		}
		return $displayableValues;
	}

	/**
	 * Function to save the current Record Model
	 */
	public function save() {
		$this->getModule()->saveRecord($this);
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete() {
		$this->getModule()->deleteRecord($this);
	}

	/**
	 * Static Function to get the instance of a clean Vtiger Record Model for the given module name
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getCleanInstance($moduleName) {
		//TODO: Handle permissions
		$focus = CRMEntity::getInstance($moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
	}

	/**
	 * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name
	 * @param <Number> $recordId
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceById($recordId, $module=null) {
		//TODO: Handle permissions
		if(is_object($module) && is_a($module, 'Vtiger_Module_Model')) {
			$moduleName = $module->get('name');
		} elseif (is_string($module)) {
			$module = Vtiger_Module_Model::getInstance($module);
			$moduleName = $module->get('name');
		} elseif(empty($module)) {
			$moduleName = getSalesEntityType($recordId);
			$module = Vtiger_Module_Model::getInstance($moduleName);
		}

		$focus = CRMEntity::getInstance($moduleName);
		$focus->id = $recordId;
		$focus->retrieve_entity_info($recordId, $moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->set('id',$recordId)->setModuleFromInstance($module)->setEntity($focus);
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT label, crmid, setype, createdtime FROM vtiger_crmentity WHERE label LIKE ? AND vtiger_crmentity.deleted = 0';
		$params = array("%$searchKey%");

		if($module !== false) {
			$query .= ' AND setype = ?';
			$params[] = $module;
		}
		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$moduleModels = $matchingRecords = $leadIdsList = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);

		for($i=0, $recordsCount = 0; $i<$noOfRows && $recordsCount<100; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
		}
		return $matchingRecords;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'EditView', $this->getId());
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isDeletable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'Delete', $this->getId());
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true';

	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($fieldName) {
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
	}

	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails() {
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = ? and vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($this->getModuleName().' Image',$recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = urlencode(decode_html($imageName));

			if(!empty($imageName)){
				$imageDetails[] = array(
						'id' => $imageId,
						'orgname' => $imageOriginalName,
						'path' => $imagePath.$imageId,
						'name' => $imageName
				);
			}
		}
		return $imageDetails;
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId) {
		$db = PearDatabase::getInstance();

		$checkResult = $db->pquery('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$crmId = intval($db->query_result($checkResult, 0, 'crmid'));
		if (intval($this->getId()) === $crmId) {
			$db->pquery('DELETE FROM vtiger_seattachmentsrel WHERE crmid = ? AND attachmentsid = ?', array($crmId,$imageId));
			$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM vtiger_crmentity WHERE crmid = ?',array($imageId));
			return true;
		}
		return false;
	}

	/**
	 * Function to get Descrption value for this record
	 * @return <String> Descrption
	 */
	public function getDescriptionValue() {
		$description = $this->get('description');
		if(empty($description)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT description FROM vtiger_crmentity WHERE crmid = ?", array($this->getId()));
			$description =  $db->query_result($result, 0, "description");
		}
		return $description;
	}

	/**
	 * Function to transfer related records of parent records to this record
	 * @param <Array> $recordIds
	 * @return <Boolean> true/false
	 */
	public function transferRelationInfoOfRecords($recordIds = array()) {
		if ($recordIds) {
			$moduleName = $this->getModuleName();
			$focus = CRMEntity::getInstance($moduleName);
			if (method_exists($focus, 'transferRelatedRecords')) {
				$focus->transferRelatedRecords($moduleName, $recordIds, $this->getId());
			}
		}
		return true;
	}

	/**
	  * Function to get the url for getting the related Popup contents
	  * @return <string>
	  */
	function getParentPopupContentsUrl() {
		return 'index.php?module='.$this->getModuleName().'&mode=getRelatedRecordInfo&action=RelationAjax&id=' . $this->getId();
	}

	/**
	 * Function to get the record models from set of record ids and moudlename.
	 * This api will be used in cases(eg: Import) where we need to create 
	 * record models from set of ids. Normally we use self::getInstaceById($recordId),
	 * but it is a performance hit for set of records. 
	 * @param <array> $recordIds
	 * @param <string> $moduleName
	 * @return <mixed> $records
	 */
	public static function getInstancesFromIds($recordIds, $moduleName) {
		$records = array();
		$module = Vtiger_Module_Model::getInstance($moduleName);
		$adb = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($module->getName(), $user);

		$meta = $queryGenerator->getMeta($module->getName());
		$moduleFieldNames = $meta->getModuleFields();
		$inventoryModules = getInventoryModules();

		if (in_array($module, $inventoryModules)) {
			$fields = vtws_describe('LineItem', $user);
			foreach ($fields['fields'] as $field) {
				unset($moduleFieldNames[$field['name']]);
			}
			foreach ($moduleFieldNames as $field => $fieldObj) {
				if (substr($field, 0, 5) == 'shtax') {
					unset($moduleFieldNames[$field]);
				}
			}
		}

		$fieldArray = array_keys($moduleFieldNames);
		$fieldArray[] = 'id';
		$queryGenerator->setFields($fieldArray);
		//getting updated meta after setting the fields
		$meta = $queryGenerator->getMeta($module->getName());

		$query = $queryGenerator->getQuery();
		$baseTable = $meta->getEntityBaseTable();
		$moduleTableIndexList = $meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		if($moduleName == 'Users') {
			$query .= ' AND vtiger_users.id IN (' . generateQuestionMarks($recordIds) . ')';
		} else{
			$query .= ' AND vtiger_crmentity.crmid IN (' . generateQuestionMarks($recordIds) . ')';
		}
		$result = $adb->pquery($query, array($recordIds));

		if ($result) {
			while ($row = $adb->fetchByAssoc($result)) {
				$newRow = array();
				$fieldColumnMapping = $meta->getFieldColumnMapping();
				$columnFieldMapping = array_flip($fieldColumnMapping);
				foreach ($row as $col => $val) {
					if (array_key_exists($col, $columnFieldMapping))
						$newRow[$columnFieldMapping[$col]] = decode_html($val);
				}
				$newRow['id'] = $row[$baseTableIndex];
				$record = self::getCleanInstance($meta->getEntityName());
				$record->setData($newRow);
				//Updating entity details
				$entity = $record->getEntity();
				$entity->column_fields = $record->getData();
				$entity->id = $record->getId();
				$record->setEntity($entity);
				$records[$record->getId()] = $record;
			}
		}
		$result = null;
		return $records;
	}

	public function getFileDetails($attachmentId = false) {
		$db = PearDatabase::getInstance();
		$fileDetails = array();
		$query = "SELECT * FROM vtiger_attachments
				INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
				WHERE crmid = ? ";
		$params = array($this->get('id'));
		if($attachmentId) {
			$query .= 'AND vtiger_attachments.attachmentsid = ?';
			$params[] = $attachmentId;
		}
		$result = $db->pquery($query, $params);

		while($row = $db->fetch_array($result)){
			if(!empty($row)){
				$fileDetails[] = $row;
			}
		}
		return $fileDetails;
	}

	public function downloadFile($attachmentId = false) {
		$attachments = $this->getFileDetails($attachmentId);
		if(is_array($attachments[0])) {
			$fileDetails = $attachments[0];
		} else {
			$fileDetails = $attachments;
		}
		$fileContent = false;
		if (!empty ($fileDetails)) {
			$filePath = $fileDetails['path'];
			$fileName = $fileDetails['name'];
			$fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
			$savedFile = $fileDetails['attachmentsid']."_".$fileName;
			$fileSize = filesize($filePath.$savedFile);
			$fileSize = $fileSize + ($fileSize % 1024);
			if (fopen($filePath.$savedFile, "r")) {
				$fileContent = fread(fopen($filePath.$savedFile, "r"), $fileSize);
				header("Content-type: ".$fileDetails['type']);
				header("Pragma: public");
				header("Cache-Control: private");
				header("Content-Disposition: attachment; filename=\"$fileName\"");
				header("Content-Description: PHP Generated Data");
				header("Content-Encoding: none");
			}
		}
		echo $fileContent;
	}

	public function getTitle($fieldInstance) {
		$fieldName = $fieldInstance->get('listViewRawFieldName');
		$fieldValue = $this->get($fieldName); 
		$rawData = $this->getRawData();
		$rawValue = $rawData[$fieldName];
		if ($fieldInstance) {
			$dataType = $fieldInstance->getFieldDataType();
			$uiType = $fieldInstance->get('uitype');
			$nonRawValueDataTypes = array('date', 'datetime', 'time', 'currency', 'boolean', 'owner');
			$nonRawValueUITypes = array(117);

			if (in_array($dataType, $nonRawValueDataTypes) || in_array($uiType, $nonRawValueUITypes)) {
				return $fieldValue;
			}
			if (in_array($dataType, array('reference', 'multireference'))) {
				$recordName = Vtiger_Util_Helper::getRecordName($rawValue);
				if ($recordName) {
					return $recordName;
				} else {
					return '';
				}
			}
			if($dataType == 'multipicklist') {
				$rawValue = $fieldInstance->getDisplayValue($rawValue);
			}
		}
		return $rawValue;
	}

	function getRollupCommentsForModule($startIndex = 0, $pageLimit = 10) {
		$rollupComments = array();
		$modulename = $this->getModuleName();
		$recordId = $this->getId();

		$relatedModuleRecordIds = $this->getCommentEnabledRelatedEntityIds($modulename, $recordId);
		array_unshift($relatedModuleRecordIds, $recordId);

		if ($relatedModuleRecordIds) {

			$listView = Vtiger_ListView_Model::getInstance('ModComments');
			$queryGenerator = $listView->get('query_generator');
			$queryGenerator->setFields(array('parent_comments', 'createdtime', 'modifiedtime', 'related_to', 'assigned_user_id',
				'commentcontent', 'creator', 'id', 'customer', 'reasontoedit', 'userid', 'from_mailconverter', 'is_private', 'customer_email'));

			$query = $queryGenerator->getQuery();

			$query .= " AND vtiger_modcomments.related_to IN (" . generateQuestionMarks($relatedModuleRecordIds)
					. ") AND vtiger_modcomments.parent_comments=0 ORDER BY vtiger_crmentity.createdtime DESC LIMIT "
					. " $startIndex,$pageLimit";

			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, $relatedModuleRecordIds);
			if ($db->num_fields($result)) {
				for ($i = 0; $i < $db->num_rows($result); $i++) {
					$rowdata = $db->query_result_rowdata($result, $i);
					$recordInstance = new ModComments_Record_Model();
					$rowdata['module'] = getSalesEntityType($rowdata['related_to']);
					$recordInstance->setData($rowdata);
					$rollupComments[] = $recordInstance;
				}
			}
		}

		return $rollupComments;
	}

	function getCommentEnabledRelatedEntityIds($modulename, $recordId) {
		$user = Users_Record_Model::getCurrentUserModel();
		$relatedModuleRecordIds = array();
		$restrictedFieldnames = array('modifiedby', 'created_user_id', 'assigned_user_id');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $modulename);
		$moduleInstance = Vtiger_Module_Model::getInstance($modulename);
		$referenceFieldsModels = $moduleInstance->getFieldsByType('reference');
		$userPrevilegesModel = Users_Privileges_Model::getInstanceById($user->id);
		$directrelatedModuleRecordIds = array();

		foreach ($referenceFieldsModels as $referenceFieldsModel) {
			$relmoduleFieldname = $referenceFieldsModel->get('name');
			$relModuleFieldValue = $recordModel->get($relmoduleFieldname);

			if (!empty($relModuleFieldValue) && !in_array($relmoduleFieldname, $restrictedFieldnames) && isRecordExists($relModuleFieldValue)) {
				$relModuleRecordModel = Vtiger_Record_Model::getInstanceById($relModuleFieldValue);
				$relmodule = $relModuleRecordModel->getModuleName();

				$relatedmoduleModel = Vtiger_Module_Model::getInstance($relmodule);
				$isCommentEnabled = $relatedmoduleModel->isCommentEnabled();

				if ($isCommentEnabled) {
					$tabid = getTabid($relmodule);
					$modulePermission = $userPrevilegesModel->hasModulePermission($tabid);
					$hasDetailViewPermission = Users_Privileges_Model::isPermitted($relmodule, 'DetailView', $relModuleFieldValue);

					if ($modulePermission && $hasDetailViewPermission)
						$directrelatedModuleRecordIds[] = $relModuleFieldValue;
				}
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($modulename);
		$relatedModuleModels = Vtiger_Relation_Model::getAllRelations($moduleModel, false);
		$commentEnabledModules = array();

		foreach ($relatedModuleModels as $relatedModuleModel) {
			$relatedModuleName = $relatedModuleModel->get('relatedModuleName');
			$relatedmoduleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$isCommentEnabled = $relatedmoduleModel->isCommentEnabled();

			if ($isCommentEnabled) {
				$tabid = getTabid($relatedModuleName);
				$modulePermission = $userPrevilegesModel->hasModulePermission($tabid);

				if ($modulePermission)
					$commentEnabledModules['related_modules'][] = $relatedModuleModel->get('relation_id');
			}
		}

		//To get all the record ids for all the modules that are shown in related tab
		$indirectrelatedModuleRecordIds = $moduleModel->getRelatedModuleRecordIds(new Vtiger_Request($commentEnabledModules), array($recordId), true);

		return array_merge($relatedModuleRecordIds, $directrelatedModuleRecordIds, $indirectrelatedModuleRecordIds);
	}

}
