<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

require_once('include/database/PearDatabase.php');
require_once("modules/Users/Users.php");
require_once 'include/Webservices/WebserviceField.php';
require_once 'include/Webservices/EntityMeta.php';
require_once 'include/Webservices/VtigerWebserviceObject.php';
require_once("include/Webservices/VtigerCRMObject.php");
require_once("include/Webservices/VtigerCRMObjectMeta.php");
require_once("include/Webservices/DataTransform.php");
require_once("include/Webservices/WebServiceError.php");
require_once 'include/utils/utils.php';
require_once 'include/utils/UserInfoUtil.php';
require_once 'include/Webservices/ModuleTypes.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'include/Webservices/WebserviceEntityOperation.php';
require_once 'include/Webservices/PreserveGlobal.php';

/* Function to return all the users in the groups that this user is part of.
 * @param $id - id of the user
 * returns Array:UserIds userid of all the users in the groups that this user is part of.
 */
function vtws_getUsersInTheSameGroup($id){
	require_once('include/utils/GetGroupUsers.php');
	require_once('include/utils/GetUserGroups.php');

	$groupUsers = new GetGroupUsers();
	$userGroups = new GetUserGroups();
	$allUsers = Array();
	$userGroups->getAllUserGroups($id);
	$groups = $userGroups->user_groups;

	foreach ($groups as $group) {
		$groupUsers->getAllUsersInGroup($group);
		//Clearing static cache for sub groups
		GetGroupUsers::$groupIdsList = array();
		$usersInGroup = $groupUsers->group_users;
		foreach ($usersInGroup as $user) {
		if($user != $id){
				$allUsers[$user] = getUserFullName($user);
			}
		}
	}
	return $allUsers;
}

function vtws_generateRandomAccessKey($length=10){
	$source = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$accesskey = "";
	$maxIndex = strlen($source);
	for($i=0;$i<$length;++$i){
		$accesskey = $accesskey.substr($source,rand(null,$maxIndex),1);
	}
	return $accesskey;
}

/**
 * get current vtiger version from the database.
 */
function vtws_getVtigerVersion(){
	global $adb;
	$query = 'select * from vtiger_version';
	$result = $adb->pquery($query, array());
	$version = '';
	while($row = $adb->fetch_array($result))
	{
		$version = $row['current_version'];
	}
	return $version;
}

function vtws_getUserAccessibleGroups($moduleId, $user){
	global $adb;
	require('user_privileges/user_privileges_'.$user->id.'.php');
	require('user_privileges/sharing_privileges_'.$user->id.'.php');
	$tabName = getTabname($moduleId);
	if($is_admin==false && $profileGlobalPermission[2] == 1 &&
			($defaultOrgSharingPermission[$moduleId] == 3 or $defaultOrgSharingPermission[$moduleId] == 0)){
		$result=get_current_user_access_groups($tabName);
	}else{
		$result = get_group_options();
	}

	$groups = array();
	if($result != null && $result != '' && is_object($result)){
		$rowCount = $adb->num_rows($result);
		for ($i = 0; $i < $rowCount; $i++) {
			$nameArray = $adb->query_result_rowdata($result,$i);
			$groupId=$nameArray["groupid"];
			$groupName=$nameArray["groupname"];
			$groups[] = array('id'=>$groupId,'name'=>$groupName);
		}
	}
	return $groups;
}

function vtws_getWebserviceGroupFromGroups($groups){
	global $adb;
	$webserviceObject = VtigerWebserviceObject::fromName($adb,'Groups');
	foreach($groups as $index=>$group){
		$groups[$index]['id'] = vtws_getId($webserviceObject->getEntityId(),$group['id']);
	}
	return $groups;
}

function vtws_getUserWebservicesGroups($tabId,$user){
	$groups = vtws_getUserAccessibleGroups($tabId,$user);
	return vtws_getWebserviceGroupFromGroups($groups);
}

function vtws_getIdComponents($elementid){
	return explode("x",$elementid);
}

function vtws_getId($objId, $elemId){
	return $objId."x".$elemId;
}

function getEmailFieldId($meta, $entityId){
	global $adb;
	//no email field accessible in the module. since its only association pick up the field any way.
	$query="SELECT fieldid,fieldlabel,columnname FROM vtiger_field WHERE tabid=?
		and uitype=13 and presence in (0,2)";
	$result = $adb->pquery($query, array($meta->getTabId()));

	//pick up the first field.
	$fieldId = $adb->query_result($result,0,'fieldid');
	return $fieldId;
}

function vtws_getParameter($parameterArray, $paramName,$default=null){

	if (!get_magic_quotes_gpc()) {
		if(is_array($parameterArray[$paramName])) {
			$param = array_map('addslashes', $parameterArray[$paramName]);
		} else {
			$param = addslashes($parameterArray[$paramName]);
		}
	} else {
		$param = $parameterArray[$paramName];
	}
	if(!$param){
		$param = $default;
	}
	return $param;
}

function vtws_getEntityNameFields($moduleName){

	global $adb;
	$query = "select fieldname,tablename,entityidfield from vtiger_entityname where modulename = ?";
	$result = $adb->pquery($query, array($moduleName));
	$rowCount = $adb->num_rows($result);
	$nameFields = array();
	if($rowCount > 0){
		$fieldsname = $adb->query_result($result,0,'fieldname');
		if(!(strpos($fieldsname,',') === false)){
			 $nameFields = explode(',',$fieldsname);
		}else{
			array_push($nameFields,$fieldsname);
		}
	}
	return $nameFields;
}

/** function to get the module List to which are crm entities.
 *  @return Array modules list as array
 */
function vtws_getModuleNameList(){
	global $adb;

	$sql = "select name from vtiger_tab where isentitytype=1 and name not in ('Rss','Webmails',".
	"'Recyclebin','Events') order by tabsequence";
	$res = $adb->pquery($sql, array());
	$mod_array = Array();
	while($row = $adb->fetchByAssoc($res)){
		array_push($mod_array,$row['name']);
	}
	return $mod_array;
}

function vtws_getWebserviceEntities(){
	global $adb;

	$sql = "select name,id,ismodule from vtiger_ws_entity";
	$res = $adb->pquery($sql, array());
	$moduleArray = Array();
	$entityArray = Array();
	while($row = $adb->fetchByAssoc($res)){
		if($row['ismodule'] == '1'){
			array_push($moduleArray,$row['name']);
		}else{
			array_push($entityArray,$row['name']);
		}
	}
	return array('module'=>$moduleArray,'entity'=>$entityArray);
}

/**
 *
 * @param VtigerWebserviceObject $webserviceObject
 * @return CRMEntity
 */
function vtws_getModuleInstance($webserviceObject){
	$moduleName = $webserviceObject->getEntityName();
	return CRMEntity::getInstance($moduleName);
}

function vtws_isRecordOwnerUser($ownerId){
	global $adb;
	
	static $cache = array();
	if (!array_key_exists($ownerId, $cache)) {
		$result = $adb->pquery("select first_name from vtiger_users where id = ?",array($ownerId));
		$rowCount = $adb->num_rows($result);
		$ownedByUser = ($rowCount > 0);
		$cache[$ownerId] = $ownedByUser;
	} else {
		$ownedByUser = $cache[$ownerId];
	}
	
	return $ownedByUser;
}

function vtws_isRecordOwnerGroup($ownerId){
	global $adb;

	static $cache = array();
	if (!array_key_exists($ownerId, $cache)) {
		$result = $adb->pquery("select groupname from vtiger_groups where groupid = ?",array($ownerId));
		$rowCount = $adb->num_rows($result);
		$ownedByGroup = ($rowCount > 0);
		$cache[$ownerId] = $ownedByGroup;
	} else {
		$ownedByGroup = $cache[$ownerId];
	}
	
	return $ownedByGroup;
}

function vtws_getOwnerType($ownerId){
	if(vtws_isRecordOwnerGroup($ownerId) == true){
		return 'Groups';
	}
	if(vtws_isRecordOwnerUser($ownerId) == true){
		return 'Users';
	}
	return false;
//	throw new WebServiceException(WebServiceErrorCode::$INVALIDID,"Invalid owner of the record");
}

function vtws_runQueryAsTransaction($query,$params,&$result){
	global $adb;

	$adb->startTransaction();
	$result = $adb->pquery($query,$params);
	$error = $adb->hasFailedTransaction();
	$adb->completeTransaction();
	return !$error;
}

function vtws_getCalendarEntityType($id){
	global $adb;

	$sql = "select activitytype from vtiger_activity where activityid=?";
	$result = $adb->pquery($sql,array($id));
	$seType = 'Calendar';
	if($result != null && isset($result)){
		if($adb->num_rows($result)>0){
			$activityType = $adb->query_result($result,0,"activitytype");
			if($activityType !== "Task"){
				$seType = "Events";
			}
		}
	}
	return $seType;
}

/***
 * Get the webservice reference Id given the entity's id and it's type name
 */
function vtws_getWebserviceEntityId($entityName, $id){
	global $adb;
	$webserviceObject = VtigerWebserviceObject::fromName($adb,$entityName);
	return $webserviceObject->getEntityId().'x'.$id;
}

function vtws_addDefaultModuleTypeEntity($moduleName){
	global $adb;
	$isModule = 1;
	$moduleHandler = array('file'=>'include/Webservices/VtigerModuleOperation.php',
		'class'=>'VtigerModuleOperation');
	return vtws_addModuleTypeWebserviceEntity($moduleName,$moduleHandler['file'],$moduleHandler['class'],$isModule);
}

function vtws_addModuleTypeWebserviceEntity($moduleName,$filePath,$className){
	global $adb;
	$checkres = $adb->pquery('SELECT id FROM vtiger_ws_entity WHERE name=? AND handler_path=? AND handler_class=?',
		array($moduleName, $filePath, $className));
	if($checkres && $adb->num_rows($checkres) == 0) {
		$isModule=1;
		$entityId = $adb->getUniqueID("vtiger_ws_entity");
		$adb->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
			array($entityId,$moduleName,$filePath,$className,$isModule));
	}
}

function vtws_deleteWebserviceEntity($moduleName) {
	global $adb;
	$adb->pquery('DELETE FROM vtiger_ws_entity WHERE name=?',array($moduleName));
}

function vtws_addDefaultActorTypeEntity($actorName,$actorNameDetails,$withName = true){
	$actorHandler = array('file'=>'include/Webservices/VtigerActorOperation.php',
		'class'=>'VtigerActorOperation');
	if($withName == true){
		vtws_addActorTypeWebserviceEntityWithName($actorName,$actorHandler['file'],$actorHandler['class'],
			$actorNameDetails);
	}else{
		vtws_addActorTypeWebserviceEntityWithoutName($actorName,$actorHandler['file'],$actorHandler['class'],
			$actorNameDetails);
	}
}

function vtws_addActorTypeWebserviceEntityWithName($moduleName,$filePath,$className,$actorNameDetails){
	global $adb;
	$isModule=0;
	$entityId = $adb->getUniqueID("vtiger_ws_entity");
	$adb->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
		array($entityId,$moduleName,$filePath,$className,$isModule));
	vtws_addActorTypeName($entityId,$actorNameDetails['fieldNames'],$actorNameDetails['indexField'],
		$actorNameDetails['tableName']);
}

function vtws_addActorTypeWebserviceEntityWithoutName($moduleName,$filePath,$className,$actorNameDetails){
	global $adb;
	$isModule=0;
	$entityId = $adb->getUniqueID("vtiger_ws_entity");
	$adb->pquery('insert into vtiger_ws_entity(id,name,handler_path,handler_class,ismodule) values (?,?,?,?,?)',
		array($entityId,$moduleName,$filePath,$className,$isModule));
}

function vtws_addActorTypeName($entityId,$fieldNames,$indexColumn,$tableName){
	global $adb;
	$adb->pquery('insert into vtiger_ws_entity_name(entity_id,name_fields,index_field,table_name) values (?,?,?,?)',
		array($entityId,$fieldNames,$indexColumn,$tableName));
}

function vtws_getName($id,$user){
	global $log,$adb;

	$webserviceObject = VtigerWebserviceObject::fromId($adb,$id);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject,$user,$adb,$log);
	$meta = $handler->getMeta();
	return $meta->getName($id);
}

function vtws_preserveGlobal($name,$value){
	return VTWS_PreserveGlobal::preserveGlobal($name,$value);
}

/**
 * Takes the details of a webservices and exposes it over http.
 * @param $name name of the webservice to be added with namespace.
 * @param $handlerFilePath file to be include which provides the handler method for the given webservice.
 * @param $handlerMethodName name of the function to the called when this webservice is invoked.
 * @param $requestType type of request that this operation should be, if in doubt give it as GET,
 * 	general rule of thumb is that, if the operation is adding/updating data on server then it must be POST
 * 	otherwise it should be GET.
 * @param $preLogin 0 if the operation need the user to authorised to access the webservice and
 * 	1 if the operation is called before login operation hence the there will be no user authorisation happening
 * 	for the operation.
 * @return Integer operationId of successful or null upon failure.
 */
function vtws_addWebserviceOperation($name,$handlerFilePath,$handlerMethodName,$requestType,$preLogin = 0){
	global $adb;
	$createOperationQuery = "insert into vtiger_ws_operation(operationid,name,handler_path,handler_method,type,prelogin)
		values (?,?,?,?,?,?);";
	if(strtolower($requestType) != 'get' && strtolower($requestType) != 'post'){
		return null;
	}
	$requestType = strtoupper($requestType);
	if(empty($preLogin)){
		$preLogin = 0;
	}else{
		$preLogin = 1;
	}
	$operationId = $adb->getUniqueID("vtiger_ws_operation");
	$result = $adb->pquery($createOperationQuery,array($operationId,$name,$handlerFilePath,$handlerMethodName,
		$requestType,$preLogin));
	if($result !== false){
		return $operationId;
	}
	return null;
}

function vtws_deleteWebServiceOperation($oprationId) {
	global $adb;
	$params = array();
	$sql = 'DELETE from vtiger_ws_operation WHERE operationid=?';
	$adb->pquery($sql, array($oprationId));
	$sql = 'DELETE from vtiger_ws_operation_parameters WHERE operationid=?';
	$adb->pquery($sql, array($oprationId));
	return true;
}

/**
 * Add a parameter to a webservice.
 * @param $operationId Id of the operation for which a webservice needs to be added.
 * @param $paramName name of the parameter used to pickup value from request(POST/GET) object.
 * @param $paramType type of the parameter, it can either 'string','datetime' or 'encoded'
 * 	encoded type is used for input which will be encoded in JSON or XML(NOT SUPPORTED).
 * @param $sequence sequence of the parameter in the definition in the handler method.
 * @return Boolean true if the parameter was added successfully, false otherwise
 */
function vtws_addWebserviceOperationParam($operationId,$paramName,$paramType,$sequence){
	global $adb;
	$supportedTypes = array('string','encoded','datetime','double','boolean');
	if(!is_numeric($sequence)){
		$sequence = 1;
	}if($sequence <=1){
		$sequence = 1;
	}
	if(!in_array(strtolower($paramType),$supportedTypes)){
		return false;
	}
	$createOperationParamsQuery = "insert into vtiger_ws_operation_parameters(operationid,name,type,sequence)
		values (?,?,?,?);";
	$result = $adb->pquery($createOperationParamsQuery,array($operationId,$paramName,$paramType,$sequence));
	return ($result !== false);
}

/**
 *
 * @global PearDatabase $adb
 * @global <type> $log
 * @param <type> $name
 * @param <type> $user
 * @return WebserviceEntityOperation
 */
function vtws_getModuleHandlerFromName($name,$user){
	global $adb, $log;
	$webserviceObject = VtigerWebserviceObject::fromName($adb,$name);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject,$user,$adb,$log);
	return $handler;
}

function vtws_getModuleHandlerFromId($id,$user){
	global $adb, $log;
	$webserviceObject = VtigerWebserviceObject::fromId($adb,$id);
	$handlerPath = $webserviceObject->getHandlerPath();
	$handlerClass = $webserviceObject->getHandlerClass();

	require_once $handlerPath;

	$handler = new $handlerClass($webserviceObject,$user,$adb,$log);
	return $handler;
}

function vtws_CreateCompanyLogoFile($fieldname) {
	global $root_directory;
	$uploaddir = $root_directory ."/test/logo/";
	$allowedFileTypes = array("jpeg", "png", "jpg", "pjpeg" ,"x-png");
	$binFile = $_FILES[$fieldname]['name'];
	$fileType = $_FILES[$fieldname]['type'];
	$fileSize = $_FILES[$fieldname]['size'];
	$fileTypeArray = explode("/",$fileType);
	$fileTypeValue = strtolower($fileTypeArray[1]);
	if($fileTypeValue == '') {
		$fileTypeValue = substr($binFile,strrpos($binFile, '.')+1);
	}
	if($fileSize != 0) {
		if(in_array($fileTypeValue, $allowedFileTypes)) {
			move_uploaded_file($_FILES[$fieldname]["tmp_name"],
					$uploaddir.$_FILES[$fieldname]["name"]);
			copy($uploaddir.$_FILES[$fieldname]["name"], $uploaddir.'application.ico');
			return $binFile;
		}
		throw new WebServiceException(WebServiceErrorCode::$INVALIDTOKEN,
			"$fieldname wrong file type given for upload");
	}
	throw new WebServiceException(WebServiceErrorCode::$INVALIDTOKEN,
			"$fieldname file upload failed");
}

function vtws_getActorEntityName ($name, $idList) {
	$db = PearDatabase::getInstance();
	if (!is_array($idList) && count($idList) == 0) {
		return array();
	}
	$entity = VtigerWebserviceObject::fromName($db, $name);
	return vtws_getActorEntityNameById($entity->getEntityId(), $idList);
}

function vtws_getActorEntityNameById ($entityId, $idList) {
	$db = PearDatabase::getInstance();
	if (!is_array($idList) && count($idList) == 0) {
		return array();
	}
	$nameList = array();
	$webserviceObject = VtigerWebserviceObject::fromId($db, $entityId);
	$query = "select * from vtiger_ws_entity_name where entity_id = ?";
	$result = $db->pquery($query, array($entityId));
	if (is_object($result)) {
		$rowCount = $db->num_rows($result);
		if ($rowCount > 0) {
			$nameFields = $db->query_result($result,0,'name_fields');
			$tableName = $db->query_result($result,0,'table_name');
			$indexField = $db->query_result($result,0,'index_field');
			if (!(strpos($nameFields,',') === false)) {
				$fieldList = explode(',',$nameFields);
				$nameFields = "concat(";
				$nameFields = $nameFields.implode(",' ',",$fieldList);
				$nameFields = $nameFields.")";
			}

			$query1 = "select $nameFields as entityname, $indexField from $tableName where ".
				"$indexField in (".generateQuestionMarks($idList).")";
			$params1 = array($idList);
			$result = $db->pquery($query1, $params1);
			if (is_object($result)) {
				$rowCount = $db->num_rows($result);
				for ($i = 0; $i < $rowCount; $i++) {
					$id = $db->query_result($result,$i, $indexField);
					$nameList[$id] = $db->query_result($result,$i,'entityname');
				}
				return $nameList;
			}
		}
	}
	return array();
}

function vtws_isRoleBasedPicklist($name) {
	$db = PearDatabase::getInstance();
	$sql = "select picklistid from vtiger_picklist where name = ?";
	$result = $db->pquery($sql, array($name));
	return ($db->num_rows($result) > 0);
}

function vtws_getConvertLeadFieldMapping(){
	global $adb;
	$sql = "select * from vtiger_convertleadmapping";
	$result = $adb->pquery($sql,array());
	if($result === false){
		return null;
	}
	$mapping = array();
	$rowCount = $adb->num_rows($result);
	for($i=0;$i<$rowCount;++$i){
		$row = $adb->query_result_rowdata($result,$i);
		$mapping[$row['leadfid']] = array('Accounts'=>$row['accountfid'],
			'Potentials'=>$row['potentialfid'],'Contacts'=>$row['contactfid']);
	}
	return $mapping;
}

/**	Function used to get the lead related Notes and Attachments with other entities Account, Contact and Potential
 *	@param integer $id - leadid
 *	@param integer $relatedId -  related entity id (accountid / contactid)
 */
function vtws_getRelatedNotesAttachments($id,$relatedId) {
	global $adb,$log;

	$sql = "select * from vtiger_senotesrel where crmid=?";
	$result = $adb->pquery($sql, array($id));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);

	$sql="insert into vtiger_senotesrel(crmid,notesid) values (?,?)";
	for($i=0; $i<$rowCount;++$i ) {
		$noteId=$adb->query_result($result,$i,"notesid");
		$resultNew = $adb->pquery($sql, array($relatedId, $noteId));
		if($resultNew === false){
			return false;
		}
	}

	$sql = "select * from vtiger_seattachmentsrel where crmid=?";
	$result = $adb->pquery($sql, array($id));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);

	$sql = "insert into vtiger_seattachmentsrel(crmid,attachmentsid) values (?,?)";
	for($i=0;$i<$rowCount;++$i) {
		$attachmentId=$adb->query_result($result,$i,"attachmentsid");
		$resultNew = $adb->pquery($sql, array($relatedId, $attachmentId));
		if($resultNew === false){
			return false;
		}
	}
	return true;
}

/**	Function used to save the lead related products with other entities Account, Contact and Potential
 *	$leadid - leadid
 *	$relatedid - related entity id (accountid/contactid/potentialid)
 *	$setype - related module(Accounts/Contacts/Potentials)
 */
function vtws_saveLeadRelatedProducts($leadId, $relatedId, $setype) {
	global $adb;

	$result = $adb->pquery("select * from vtiger_seproductsrel where crmid=?", array($leadId));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for($i = 0; $i < $rowCount; ++$i) {
		$productId = $adb->query_result($result,$i,'productid');
		$resultNew = $adb->pquery('INSERT INTO vtiger_seproductsrel VALUES(?,?,?,?)', array($relatedId, $productId, $setype, 1));
		if($resultNew === false){
			return false;
		}
	}
	return true;
}

/**	Function used to save the lead related services with other entities Account, Contact and Potential
 *	$leadid - leadid
 *	$relatedid - related entity id (accountid/contactid/potentialid)
 *	$setype - related module(Accounts/Contacts/Potentials)
 */
function vtws_saveLeadRelations($leadId, $relatedId, $setype) {
	global $adb;

	$result = $adb->pquery("select * from vtiger_crmentityrel where crmid=?", array($leadId));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for($i = 0; $i < $rowCount; ++$i) {
		$recordId = $adb->query_result($result,$i,'relcrmid');
		$recordModule = $adb->query_result($result,$i,'relmodule');
		$adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($relatedId, $setype, $recordId, $recordModule));
		if($resultNew === false){
			return false;
		}
	}
	$result = $adb->pquery("select * from vtiger_crmentityrel where relcrmid=?", array($leadId));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for($i = 0; $i < $rowCount; ++$i) {
		$recordId = $adb->query_result($result,$i,'crmid');
		$recordModule = $adb->query_result($result,$i,'module');
		$adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($relatedId, $setype, $recordId, $recordModule));
		if($resultNew === false){
			return false;
		}
	}

	return true;
}

function vtws_getFieldfromFieldId($fieldId, $fieldObjectList){
	foreach ($fieldObjectList as $field) {
		if($fieldId == $field->getFieldId()){
			return $field;
		}
	}
	return null;
}

/**	Function used to get the lead related activities with other entities Account and Contact
 *	@param integer $leadId - lead entity id
 *	@param integer $accountId - related account id
 *	@param integer $contactId -  related contact id
 *	@param integer $relatedId - related entity id to which the records need to be transferred
 */
function vtws_getRelatedActivities($leadId,$accountId,$contactId,$relatedId) {

	if(empty($leadId) || empty($relatedId) || (empty($accountId) && empty($contactId))){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move related Activities/Emails");
	}
	global $adb;
	$sql = "select * from vtiger_seactivityrel where crmid=?";
	$result = $adb->pquery($sql, array($leadId));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for($i=0;$i<$rowCount;++$i) {
		$activityId=$adb->query_result($result,$i,"activityid");

		$sql ="select setype from vtiger_crmentity where crmid=?";
		$resultNew = $adb->pquery($sql, array($activityId));
		if($resultNew === false){
			return false;
		}
		$type=$adb->query_result($resultNew,0,"setype");

		$sql="delete from vtiger_seactivityrel where crmid=?";
		$resultNew = $adb->pquery($sql, array($leadId));
		if($resultNew === false){
			return false;
		}
		if($type != "Emails") {
				if(!empty($accountId)){
					$sql = "insert into vtiger_seactivityrel(crmid,activityid) values (?,?)";
					$resultNew = $adb->pquery($sql, array($accountId, $activityId));
					if($resultNew === false){
						return false;
				}
			}
				if(!empty($contactId)){
					$sql="insert into vtiger_cntactivityrel(contactid,activityid) values (?,?)";
					$resultNew = $adb->pquery($sql, array($contactId, $activityId));
					if($resultNew === false){
						return false;
				}
			}
		} else {
			$sql = "insert into vtiger_seactivityrel(crmid,activityid) values (?,?)";
			$resultNew = $adb->pquery($sql, array($relatedId, $activityId));
			if($resultNew === false){
				return false;
			}
		}
	}
	return true;
}

/**
 * Function used to save the lead related Campaigns with Contact
 * @param $leadid - leadid
 * @param $relatedid - related entity id (contactid/accountid)
 * @param $setype - related module(Accounts/Contacts)
 * @return Boolean true on success, false otherwise.
 */
function vtws_saveLeadRelatedCampaigns($leadId, $relatedId, $seType) {
	global $adb;

	$result = $adb->pquery("select * from vtiger_campaignleadrel where leadid=?", array($leadId));
	if($result === false){
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for($i = 0; $i < $rowCount; ++$i) {
		$campaignId = $adb->query_result($result,$i,'campaignid');
		if($seType == 'Accounts') {
			$resultNew = $adb->pquery("insert into vtiger_campaignaccountrel (campaignid, accountid) values(?,?)",
				array($campaignId, $relatedId));
		} elseif ($seType == 'Contacts') {
			$resultNew = $adb->pquery("insert into vtiger_campaigncontrel (campaignid, contactid) values(?,?)",
				array($campaignId, $relatedId));
		}
		if($resultNew === false){
			return false;
		}
	}
	return true;
}

/**
 * Function used to transfer all the lead related records to given Entity(Contact/Account) record
 * @param $leadid - leadid
 * @param $relatedid - related entity id (contactid/accountid)
 * @param $setype - related module(Accounts/Contacts)
 */
function vtws_transferLeadRelatedRecords($leadId, $relatedId, $seType) {

	if(empty($leadId) || empty($relatedId) || empty($seType)){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move related Records");
	}
	$status = vtws_getRelatedNotesAttachments($leadId, $relatedId);
	if($status === false){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move related Documents to the ".$seType);
	}
	//Retrieve the lead related products and relate them with this new account
	$status = vtws_saveLeadRelatedProducts($leadId, $relatedId, $seType);
	if($status === false){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move related Products to the ".$seType);
	}
	$status = vtws_saveLeadRelations($leadId, $relatedId, $seType);
	if($status === false){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move Records to the ".$seType);
	}
	$status = vtws_saveLeadRelatedCampaigns($leadId, $relatedId, $seType);
	if($status === false){
		throw new WebServiceException(WebServiceErrorCode::$LEAD_RELATED_UPDATE_FAILED,
			"Failed to move Records to the ".$seType);
	}
	vtws_transferComments($leadId, $relatedId);
}

function vtws_transferComments($sourceRecordId, $destinationRecordId) {
	if(vtlib_isModuleActive('ModComments')) {
		CRMEntity::getInstance('ModComments'); ModComments::transferRecords($sourceRecordId, $destinationRecordId);
	}
}

function vtws_transferOwnership($ownerId, $newOwnerId, $delete=true) {
	$db = PearDatabase::getInstance();
	//Updating the smownerid, modifiedby in vtiger_crmentity
	$db->pquery('UPDATE vtiger_crmentity SET smownerid=?, modifiedtime = ? WHERE smownerid=? AND setype<>?', array($newOwnerId, date('Y-m-d H:i:s'), $ownerId, 'ModComments'));
	$db->pquery('UPDATE vtiger_crmentity SET modifiedby=? WHERE modifiedby=?', array($newOwnerId, $ownerId));

	//deleting from vtiger_tracker
	$db->pquery('DELETE FROM vtiger_tracker WHERE user_id=?', array($ownerId));

	//delete from vtiger_homestuff
	$db->pquery('DELETE FROM vtiger_homestuff WHERE userid=?', array($ownerId));

	//updating the vtiger_import_maps
	$db->pquery('UPDATE vtiger_import_maps SET assigned_user_id=? WHERE assigned_user_id=?', array($newOwnerId, $ownerId));

	if (Vtiger_Utils::CheckTable('vtiger_customerportal_prefs')) {
		$query = 'UPDATE vtiger_customerportal_prefs SET prefvalue = ? WHERE prefkey IN (?, ?) AND prefvalue = ?';
		$params = array($newOwnerId, 'userid', 'defaultassignee', $ownerId);
		$db->pquery($query, $params);
	}

	$sql = "SELECT tablename,columnname FROM vtiger_field 
            LEFT JOIN vtiger_fieldmodulerel ON vtiger_field.fieldid=vtiger_fieldmodulerel.fieldid 
            WHERE (uitype IN (?,?,?,?) OR (uitype=? AND relmodule=?)) AND columnname <> ? GROUP BY tablename,columnname ORDER BY NULL";
	$result = $db->pquery($sql, array(52, 53, 77, 101, 10, 'Users', 'smcreatorid'));

	$it = new SqlResultIterator($db, $result);
	$columnList = array();
	foreach ($it as $row) {
		$column = $row->tablename.'.'.$row->columnname;
		if (!in_array($column, $columnList)) {
			$columnList[] = $column;
			if ($row->columnname == 'smownerid') {
				$sql = "UPDATE $row->tablename set $row->columnname=? WHERE $row->columnname=? AND setype<>?";
				$db->pquery($sql, array($newOwnerId, $ownerId, 'ModComments'));
			} elseif ($row->tablename == 'vtiger_users' && $row->columnname == 'reports_to_id') {
				$sql = "UPDATE $row->tablename SET $row->columnname = CASE WHEN id=$newOwnerId THEN ? ELSE ? END WHERE $row->columnname=?";
				$db->pquery($sql, array('', $newOwnerId, $ownerId));
			} else {
				$sql = "UPDATE $row->tablename SET $row->columnname=? WHERE $row->columnname=?";
				$db->pquery($sql, array($newOwnerId, $ownerId));
			}
		}
	}

	//update webforms assigned userid
	$db->pquery("UPDATE vtiger_webforms SET ownerid = ? WHERE ownerid = ?", array($newOwnerId, $ownerId));

	//update workflow tasks Assigned User from Deleted User to Transfer User
	$newOwnerModel = Users_Record_Model::getInstanceById($newOwnerId, 'Users');
	$ownerModel = Users_Record_Model::getInstanceById($ownerId, 'Users');

	vtws_transferOwnershipForWorkflowTasks($ownerModel, $newOwnerModel);
	vtws_updateWebformsRoundrobinUsersLists($ownerId, $newOwnerId);

	//transferring non-private filters (status not 1) of deleted user to new selected user
	$db->pquery('UPDATE vtiger_customview SET userid = ? WHERE userid = ? AND status != ?', array($newOwnerId, $ownerId, 1));
	//transferring private shared filters of deleted user to selected user
	$db->pquery('UPDATE vtiger_customview SET userid = ? WHERE userid = ? AND status = ? AND cvid IN (SELECT cvid FROM vtiger_cv2users UNION SELECT cvid FROM vtiger_cv2group UNION SELECT cvid FROM vtiger_cv2role UNION SELECT cvid FROM vtiger_cv2rs)', array($newOwnerId, $ownerId, 1));

	if ($delete) {
		//Delete from vtiger_users to vtiger_role vtiger_table
		$db->pquery('DELETE FROM vtiger_users2group WHERE userid=?', array($ownerId));

		//Mark user as deleted =1 
		$db->pquery('UPDATE vtiger_users SET deleted=? WHERE id=?', array(1, $ownerId));

		//Change the owner for report
		$db->pquery('UPDATE vtiger_report SET owner=? WHERE owner=?', array($newOwnerId, $ownerId));

		//Recalculate user privelege file
		RecalculateSharingRules();
	}
}

function vtws_updateWebformsRoundrobinUsersLists($ownerId, $newOwnerId) {
	$db = PearDatabase::getInstance();
	$sql = 'SELECT id,roundrobin_userid FROM vtiger_webforms;';
	$result = $db->pquery($sql, array());
	$numOfRows = $db->num_rows($result);
	for ($i = 0; $i < $numOfRows; $i++) {
		$rowdata = $db->query_result_rowdata($result, $i);
		$webformId = $rowdata['id'];
		$encodedUsersList = $rowdata['roundrobin_userid'];
		$encodedUsersList = str_replace("&quot;", "\"", $encodedUsersList);
		$usersList = json_decode($encodedUsersList, true);
		if (is_array($usersList)) {
			if (($key = array_search($ownerId, $usersList)) !== false) {
				if (!in_array($newOwnerId, $usersList)) {
					$usersList[$key] = $newOwnerId;
				} else {
					unset($usersList[$key]);
					$revisedUsersList = array();
					$j = 0;
					foreach ($usersList as $uid) {
						$revisedUsersList[$j++] = $uid;
					}
					$usersList = $revisedUsersList;
				}
				if (count($usersList) == 0) {
					$db->pquery('UPDATE vtiger_webforms SET roundrobin_userid = ?,roundrobin = ? where id =?', array("--None--", 0, $webformId));
				} else {
					$usersList = json_encode($usersList);
					$db->pquery('UPDATE vtiger_webforms SET roundrobin_userid = ? where id =?', array($usersList, $webformId));
				}
			}
		}
	}
}

function vtws_transferOwnershipForWorkflowTasks($ownerModel, $newOwnerModel) {
	$db = PearDatabase::getInstance();
	
	//update workflow tasks Assigned User from Deleted User to Transfer User
	$newOwnerName = $newOwnerModel->get('user_name');
	if(!$newOwnerName) {
		$newOwnerName = $newOwnerModel->getName();
	}
	$newOwnerId = $newOwnerModel->getId();
	
	$ownerName = $ownerModel->get('user_name');
	if(!$ownerName) {
		$ownerName = $ownerModel->getName();
	}
	$ownerId = $ownerModel->getId();
	
	$nameSearchValue = '"fieldname":"assigned_user_id","value":"'.$ownerName.'"';
	$idSearchValue = '"fieldname":"assigned_user_id","value":"'.$ownerId.'"';
	$fieldSearchValue = 's:16:"assigned_user_id"';
	$query = "SELECT task,task_id,workflow_id FROM com_vtiger_workflowtasks where task LIKE '%".$nameSearchValue."%' OR task LIKE '%".$idSearchValue.
			"%' OR task LIKE '%".$fieldSearchValue."%'";
	$result = $db->pquery($query, array());
	
	$num_rows = $db->num_rows($result);
	for ($i = 0; $i < $num_rows; $i++) {
		$row = $db->raw_query_result_rowdata($result, $i);
		$task = $row['task'];
		$taskComponents = explode(':', $task);
		$classNameWithDoubleQuotes = $taskComponents[2];
		$className = str_replace('"', '', $classNameWithDoubleQuotes);
		require_once("modules/com_vtiger_workflow/VTTaskManager.inc");
		require_once 'modules/com_vtiger_workflow/tasks/'.$className.'.inc';
		$unserializeTask = unserialize($task);
		if(array_key_exists("field_value_mapping",$unserializeTask)) {
			$fieldMapping = Zend_Json::decode($unserializeTask->field_value_mapping);
			if (!empty($fieldMapping)) {
				foreach ($fieldMapping as $key => $condition) {
					if ($condition['fieldname'] == 'assigned_user_id') {
						$value = $condition['value'];
						if(is_numeric($value) && $value == $ownerId) {
							$condition['value'] = $newOwnerId;
						} else if($value == $ownerName) {
							$condition['value'] = $newOwnerName;
						}
					}
					$fieldMapping[$key] = $condition;
				}
				$updatedTask = Zend_Json::encode($fieldMapping);
				$unserializeTask->field_value_mapping = $updatedTask;
				$serializeTask = serialize($unserializeTask);
				
				$query = 'UPDATE com_vtiger_workflowtasks SET task=? where workflow_id=? AND task_id=?';
				$db->pquery($query, array($serializeTask, $row['workflow_id'], $row['task_id']));
			}
		} else {
			//For VTCreateTodoTask and VTCreateEventTask
			if(array_key_exists('assigned_user_id', $unserializeTask)){
				$value = $unserializeTask->assigned_user_id;
				if($value == $ownerId) {
					$unserializeTask->assigned_user_id = $newOwnerId;
				}
				$serializeTask = serialize($unserializeTask);
				$query = 'UPDATE com_vtiger_workflowtasks SET task=? where workflow_id=? AND task_id=?';
				$db->pquery($query, array($serializeTask, $row['workflow_id'], $row['task_id']));
			}
		}
	}
}

function vtws_getWebserviceTranslatedStringForLanguage($label, $currentLanguage) {
	static $translations = array();
	$currentLanguage = vtws_getWebserviceCurrentLanguage();
	if(empty($translations[$currentLanguage])) {
		include 'languages/'.$currentLanguage.'/Webservices.php';
		$translations[$currentLanguage] = $languageStrings;
	}
	if(isset($translations[$currentLanguage][$label])) {
		return $translations[$currentLanguage][$label];
	}
	return null;
}

function vtws_getWebserviceTranslatedString($label) {
	$currentLanguage = vtws_getWebserviceCurrentLanguage();
	$translation = vtws_getWebserviceTranslatedStringForLanguage($label, $currentLanguage);
	if(!empty($translation)) {
		return $translation;
	}

	//current language doesn't have translation, return translation in default language
	//if default language is english then LBL_ will not shown to the user.
	$defaultLanguage = vtws_getWebserviceDefaultLanguage();
	$translation = vtws_getWebserviceTranslatedStringForLanguage($label, $defaultLanguage);
	if(!empty($translation)) {
		return $translation;
	}

	//if default language is not en_us then do the translation in en_us to eliminate the LBL_ bit
	//of label.
	if('en_us' != $defaultLanguage) {
		$translation = vtws_getWebserviceTranslatedStringForLanguage($label, 'en_us');
		if(!empty($translation)) {
			return $translation;
		}
	}
	return $label;
}

function vtws_getWebserviceCurrentLanguage() {
	global $default_language, $current_language;
	if(empty($current_language)) {
		return $default_language;
	}
	return $current_language;
}

function vtws_getWebserviceDefaultLanguage() {
	global $default_language;
	return $default_language;
}

/**
 * Function used to transfer all the potential related records to given Entity(Project) record
 * @param $leadid - potentialid
 * @param $relatedid - related entity id (projectid)
 * @param $setype - related module(Project)
 */
function vtws_transferPotentialRelatedRecords($potentialId, $relatedId, $seType) {

	if (empty($potentialId) || empty($relatedId) || empty($seType)) {
		throw new WebServiceException(WebServiceErrorCode::$POTENTIAL_RELATED_UPDATE_FAILED, "Failed to move related Records");
	}
	if (vtlib_isModuleActive('Calendar')) {
		vtws_transferRelatedPotentialActivities($potentialId, $relatedId);
	}
	if (vtlib_isModuleActive('Quotes')) {
		vtws_transferRelatedPotentialQuotes($potentialId, $relatedId);
	}
	if (vtlib_isModuleActive('ModComments')) {
		CRMEntity::getInstance('ModComments');
		ModComments::copyCommentsToRelatedRecord($potentialId, $relatedId);
	}
	if (vtlib_isModuleActive('Documents')) {
		vtws_transferRelatedPotentialDocuments($potentialId, $relatedId);
	}
}

/** 	Function used to get the potential related activities and transfer to Project
 * 	@param integer $entityId - potential entity id
 * 	@param integer $relatedId - related entity id to which the records need to be transferred
 */
function vtws_transferRelatedPotentialActivities($entityId, $relatedId) {
	global $adb;

	$relatedRecordInstance = Vtiger_Record_Model::getInstanceById($relatedId);
	$relatedModuleName = $relatedRecordInstance->getModuleName();

	if (empty($entityId) || empty($relatedId)) {
		throw new WebServiceException(WebServiceErrorCode::$POTENTIAL_RELATED_UPDATE_FAILED, "Failed to move related Activities");
	}

	$sql = "SELECT * FROM vtiger_crmentityrel 
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid 
			WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentityrel.relmodule=? AND vtiger_crmentity.setype <> ?";
	$result = $adb->pquery($sql, array($entityId, 'Calendar', 'Emails'));

	if ($result === false) {
		return false;
	}
	$rowCount = $adb->num_rows($result);
	for ($i = 0; $i < $rowCount; $i++) {
		$activityId = $adb->query_result($result, $i, "relcrmid");
		$activityModuleName = $adb->query_result($result, $i, "relmodule");

		if (!empty($relatedId)) {
			$sql = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) values (?,?,?,?)";
			$resultNew = $adb->pquery($sql, array($relatedId, $relatedModuleName, $activityId, $activityModuleName));
			if ($resultNew === false) {
				return false;
			}
		}
	}

	$sql = 'SELECT activityid,setype FROM vtiger_seactivityrel
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_seactivityrel.activityid
			WHERE vtiger_seactivityrel.crmid=? AND vtiger_crmentity.setype <> ?';
	$result = $adb->pquery($sql, array($entityId, 'Emails'));
	if ($result === false) {
		return false;
	}
	$rowCount = $adb->num_rows($result);

	for ($i = 0; $i < $rowCount; $i++) {
		$activityId = $adb->query_result($result, $i, "activityid");
		$activityModuleName = $adb->query_result($result, $i, "setype");

		if (!empty($relatedId)) {
			$sql = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) values (?,?,?,?)";
			$resultNew = $adb->pquery($sql, array($relatedId, $relatedModuleName, $activityId, $activityModuleName));
			if ($resultNew === false) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Function To Transfer Related Quotes From Parent Record to Related Record
 * @param <Integer> $entityId - Parent Id
 * @param <Integer> $relatedId - Related Id
 * @return boolean
 */
function vtws_transferRelatedPotentialQuotes($entityId, $relatedId) {
	$db = PearDatabase::getInstance();
	$entityRecordModel = Vtiger_Record_Model::getInstanceById($entityId);
	$relatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedId);

	$relatedModel = $relatedRecordModel->getModule();
	$relatedModuleName = $relatedModel->getName();
	$quotesModel = Vtiger_Module_Model::getInstance('Quotes');

	$query = 'SELECT vtiger_quotes.quoteid FROM vtiger_quotes
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_quotes.quoteid
				INNER JOIN vtiger_potential on vtiger_potential.potentialid=vtiger_quotes.potentialid
				WHERE vtiger_crmentity.deleted=0 and vtiger_potential.potentialid=?';
	$result = $db->pquery($query, array($entityId));
	if ($result == false) {
		return false;
	}

	$rowCount = $db->num_rows($result);
	for ($i = 0; $i < $rowCount; $i++) {
		$quoteId = $db->query_result($result, 0, 'quoteid');
		$relationModel = Vtiger_Relation_Model::getInstance($relatedModel, $quotesModel);
		$relationModel->addRelation($relatedId, $quoteId);
	}

	$sql = "SELECT * FROM vtiger_crmentityrel 
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_crmentityrel.relcrmid 
			WHERE vtiger_crmentityrel.crmid=? AND vtiger_crmentityrel.relmodule=?";
	$result = $db->pquery($sql, array($entityId, 'Quotes'));

	if ($result === false) {
		return false;
	}

	$rowCount = $db->num_rows($result);
	for ($i = 0; $i < $rowCount; $i++) {
		$quoteId = $db->query_result($result, $i, "relcrmid");
		$quoteModuleName = $db->query_result($result, $i, "relmodule");

		if (!empty($relatedId)) {
			$sql = "INSERT INTO vtiger_crmentityrel (crmid,module,relcrmid,relmodule) values (?,?,?,?)";
			$resultNew = $db->pquery($sql, array($relatedId, $relatedModuleName, $quoteId, $quoteModuleName));
			if ($resultNew === false) {
				return false;
			}
		}
	}
}

/**
 * Function To Transfer Related Documents From Parent Record to Related Record
 * @param <Integer> $entityId - Parent Id
 * @param <Integer> $relatedId - Related Id
 * @return boolean
 */
function vtws_transferRelatedPotentialDocuments($entityId, $relatedId) {
	$db = PearDatabase::getInstance();

	$sql = "SELECT vtiger_senotesrel.notesid FROM vtiger_senotesrel 
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_senotesrel.crmid 
			WHERE vtiger_senotesrel.crmid=?";
	$result = $db->pquery($sql, array($entityId));

	if ($result === false) {
		return false;
	}

	$rowCount = $db->num_rows($result);
	for ($i = 0; $i < $rowCount; $i++) {
		$documentId = $db->query_result($result, $i, "notesid");

		if (!empty($relatedId)) {
			$sql = "INSERT INTO vtiger_senotesrel (crmid,notesid) values (?,?)";
			$resultNew = $db->pquery($sql, array($relatedId, $documentId));
			if ($resultNew === false) {
				return false;
			}
		}
	}

	return true;
}

function vtws_validateConvertEntityMandatoryValues($entity, $entityHandler, $module) {
	$mandatoryFields = $entityHandler->getMeta()->getMandatoryFields();
	foreach ($mandatoryFields as $field) {
		if (empty($entity[$field])) {
			$fieldInfo = vtws_getConvertEntityFieldInfo($module, $field);
			if (($fieldInfo['type']['name'] == 'picklist' || $fieldInfo['type']['name'] == 'multipicklist' || $fieldInfo['type']['name'] == 'date' || $fieldInfo['type']['name'] == 'datetime') && ($fieldInfo['editable'] == true)) {
				$entity[$field] = $fieldInfo['default'];
			} else {
				$entity[$field] = '????';
			}
		}
	}
	return $entity;
}

function vtws_getConvertEntityFieldInfo($module, $fieldname) {
	global $adb, $log, $current_user;
	$describe = vtws_describe($module, $current_user);
	foreach ($describe['fields'] as $index => $fieldInfo) {
		if ($fieldInfo['name'] == $fieldname) {
			return $fieldInfo;
		}
	}
	return false;
}

function vtws_getCompanyEncodedImage($logoname) {
	global $root_directory;
	$image = "$root_directory/test/logo/$logoname";
	$image_data = file_get_contents($image);
	$encoded_image = base64_encode($image_data);
	return $encoded_image;
}

function vtws_getCompanyId() {
	$db = PearDatabase::getInstance();
	$result = $db->pquery('SELECT organization_id FROM vtiger_organizationdetails', array());
	if ($db->num_rows($result) == 1) {
		$id = $db->query_result($result, 0, 'organization_id');
	}
	return vtws_getWebserviceEntityId('CompanyDetails', $id);
}

function vtws_recordExists($recordId) {
	$ids = vtws_getIdComponents($recordId);
	return !Vtiger_Util_Helper::CheckRecordExistance($ids[1]);
}

function vtws_isDuplicatesAllowed($webserviceObject){
	$moduleName = $webserviceObject->getEntityName();
	$allowed = true;
	if ($moduleName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT allowduplicates FROM vtiger_tab WHERE name=?', array($moduleName));
		$allowed = ($db->query_result($result, 'allowduplicates')) ? true : false;
	}
	return $allowed;
}

?>