<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/libraries/freetag/freetag.class.php');

class Vtiger_Tag_Model extends Vtiger_Base_Model {

	//Num of tags that need to show by default in detail view 
	const NUM_OF_TAGS_DETAIL = 3;
	const NUM_OF_TAGS_LIST = 5;
	const PRIVATE_TYPE = 'private';
	const PUBLIC_TYPE = 'public';

	private $_freetag = false;

	static $TAG_FETCH_LIMIT = 100;

	function __construct() {
		$this->_freetag = new freetag();
	}

	public function getId() {
		return $this->get('id');
	}

	public function setId($tagId) {
		$this->set('id', $tagId);
		return $this;
	}

	public function getName() {
		return $this->get('tag');
	}

	public function setName($name) {
		$this->set('tag', $name);
		return $this;
	}

	public function getType() {
		return $this->get('visibility');
	}

	public function setType($type) {
		$this->set('visibility', $type);
		return $this;
	}



	/**
	 * Function saves a tag to database
	 */
	public function save() {
		$this->_freetag->tag_object($this->get('userid'), $this->get('record'), $this->get('tagname'), $this->get('module'));
	}

	public function create(){
		$db = PearDatabase::getInstance();
		$tagName = $this->getName();
		$visibility = $this->getType();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$checkRes = $db->pquery("SELECT id FROM vtiger_freetags WHERE tag=?",array($this->getName()));
		if($db->num_rows($checkRes) > 0) {
			$id = $db->query_result($checkRes, 0, 'id');
		}else{
			$id = $db->getUniqueId('vtiger_freetags');
			$db->pquery("INSERT INTO vtiger_freetags values(?,?,?,?,?)", array($id, $tagName, $tagName, $visibility, $currentUser->getId()));
		}
		$this->set('id', $id);
		return $id;
	}

	public function update() {
		$db = PearDatabase::getInstance();
		$query = "UPDATE vtiger_freetags SET tag=?, raw_tag=?, visibility=? WHERE id=?";
		$db->pquery($query, array($this->getName(), $this->getName(), $this->getType(), $this->getId()));
		return true;
	}

	public function isEditable($userId = '') {
		if(empty($userId)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->id;
		}
		return ($this->get('owner') == $userId) ? true : false;
	}

	public function isUnLinkable($userId = '') {
		if(empty($userId)) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->id;
		}
		return ($this->get('owner') == $userId) ? true : false;
	}

	/**
	 * Function deletes a tag from database
	 */
	public function delete() {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_freetagged_objects WHERE tag_id = ? AND object_id = ?',
				array($this->get('tag_id'), $this->get('record')));
	}

	public function remove() {
		$db = PearDatabase::getInstance();
		$query = "DELETE FROM vtiger_freetags WHERE id=?";
		$db->pquery($query, array($this->getId()));
	}

	/**
	 * Function returns the tags
	 * @param type $userId
	 * @param type $module
	 * @param type $record
	 * @return type
	 */
	public static function getAll($userId = NULL, $module = "", $record = NULL) {
		$tag = new self();
		return $tag->_freetag->get_tag_cloud_tags(self::$TAG_FETCH_LIMIT, $userId, $module, $record);
	}

	public static function getAllUserTags($userId) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM vtiger_freetags WHERE owner=? OR visibility=?", array($userId, self::PUBLIC_TYPE));
		$num_rows = $db->num_rows($result);

		$tagsList = array();
		for($i=0; $i<$num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tagModel = new self();
			$tagModel->setData($row);
			$tagsList[$tagModel->getId()] = $tagModel;
		}
		return $tagsList;
	}

	public static function getAllAccessible($userId, $module = "", $recordId = null, $mode="") {
		$db = PearDatabase::getInstance();
		$query = "SELECT * ";

		if($mode == "count") {
			$query = "SELECT count(1) AS count";
		}
		$query .= " FROM vtiger_freetags 
					INNER JOIN vtiger_freetagged_objects ON vtiger_freetags.id = vtiger_freetagged_objects.tag_id 
					WHERE (vtiger_freetagged_objects.tagger_id = ? OR vtiger_freetags.visibility='public') ";
		$params = array($userId);
		if(!empty($module)) {
			$query .=  ' AND vtiger_freetagged_objects.module=?';
			array_push($params, $module);
		}

		if(!empty($recordId)) {
			$query .= ' AND vtiger_freetagged_objects.object_id=?';
			array_push($params, $recordId);
		}
//        if($mode != "count"){
//            $query .= ' GROUP BY vtiger_freetags.id';
//        }
		$query .=' ORDER BY tagged_on ';
		$result = $db->pquery($query , $params);
		$num_rows = $db->num_rows($result);
		if($mode == "count") {
			if($num_rows > 0) {
				return $db->query_result($result, 0,'count');
			}else{
				return $num_rows;
			}
		}
		$tagsList = array();
		for($i=0; $i<$num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tagModel = new self();
			$tagModel->setData($row);
			$tagsList[$tagModel->getId()] = $tagModel;
		}
		return $tagsList;
	}

	public static function getTaggedRecords($tagId) {
		$recordModels = array();
		if(!empty($tagId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT vtiger_crmentity.* FROM vtiger_freetags 
				INNER JOIN vtiger_freetagged_objects ON vtiger_freetags.id = vtiger_freetagged_objects.tag_id 
				INNER JOIN vtiger_crmentity ON vtiger_freetagged_objects.object_id=vtiger_crmentity.crmid 
					AND vtiger_crmentity.deleted=0 
				WHERE tag_id = ?", array($tagId));
			$rows = $db->num_rows($result);
			for($i=0; $i<$rows; $i++) {
				$row = $db->query_result_rowdata($result, $i);
				$recordModel = Vtiger_Record_Model::getCleanInstance($row['setype']);
				$recordModel->setData($row);
				$recordModel->setId($row['crmid']);
				$recordModels[$row['setype']][] = $recordModel;
			}
		}
		return $recordModels;
	}

	public static function saveForRecord($recordId , $tagList , $userId='', $module='') {
		$db = PearDatabase::getInstance();

		if(empty($userId)) {
		   $currentUser = Users_Record_Model::getCurrentUserModel();
		   $userId = $currentUser->getId();
		}

		if(empty($module)) {
			$module = Vtiger_Functions::getCRMRecordType($recordId);
		}

		if(!is_array($tagList)) {
			$tagList = array($tagList);
		}
		$date_var = date('Y-m-d H:i:s');
		$createdOn = $db->formatDate($date_var, true);
		foreach ($tagList as $tagId) {
			$saveQuery = "INSERT INTO vtiger_freetagged_objects VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE tag_id=?";
			$params = array($tagId, $userId, $recordId, $createdOn, $module,$tagId);
			$db->pquery($saveQuery, $params);
		}
	}

	public static function deleteForRecord($recordId, $tagList, $userId ='', $module='') {
		$db = PearDatabase::getInstance();

		if(empty($userId)) {
		   $currentUser = Users_Record_Model::getCurrentUserModel();
		   $userId = $currentUser->getId();
		}

		if(empty($module)) {
			$module = Vtiger_Functions::getCRMRecordType($recordId);
		}

		if(!is_array($tagList)) {
			$tagList = array($tagList);
		}

		$tagModel = new self();
		foreach ($tagList as $tagId) {
			$tagModel->set('tag_id', $tagId)->set('record', $recordId);
			$tagModel->delete();
		}
	}

	public static function getCleanInstance(){
		return new self();
	}

	public static function getInstanceById($tagId) {
		$db = PearDatabase::getInstance();

		$query = "SELECT * FROM vtiger_freetags WHERE id=?";
		$result = $db->pquery($query, array($tagId));
		$tagModel = false;
		if($db->num_rows($result) > 0) {
			$tagModel = new self();
			$rowData = $db->query_result_rowdata($result, '0');
			$tagModel->setData($rowData);
		}
		return $tagModel;
	}

	public static function getInstanceByName($name, $userId, $excludedTagId = false) {
		$db = PearDatabase::getInstance();
		$query = "SELECT * FROM vtiger_freetags WHERE (tag=? OR raw_tag=?) AND (owner=? OR visibility=?)";
		$params = array($name, $name, $userId, self::PUBLIC_TYPE);
		global $log;
		$log->fatal($excludedTagId);
		if($excludedTagId !== false) {
			$query .= ' AND id != ?';
			array_push($params, $excludedTagId);
		}
		global $log;
		$log->fatal($db->convert2Sql($query , $params));
		$result = $db->pquery($query, $params);
		$tagModel = false;
		if($db->num_rows($result) > 0) {
			$tagModel = new self();
			$rowData = $db->query_result_rowdata($result, '0');
			$tagModel->setData($rowData);
		}
		return $tagModel;
	}

	public static function checkIfOtherUsersUsedTag($tagId, $userIdToExclude) {
		$db = PearDatabase::getInstance();
		$checkQuery = "SELECT 1 FROM vtiger_freetagged_objects 
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_freetagged_objects.object_id
						WHERE tag_id=? and tagger_id !=? AND vtiger_crmentity.deleted=0";
		//TODO : check for module specific delete query as well 
		$result = $db->pquery($checkQuery, array($tagId, $userIdToExclude));
		return $db->num_rows($result) > 0 ? true : false;
	}
}

?>
