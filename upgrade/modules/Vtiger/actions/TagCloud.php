<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_TagCloud_Action extends Vtiger_Mass_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
		$this->exposeMethod('saveTags');
		$this->exposeMethod('update');
		$this->exposeMethod('remove');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if(!$permission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
		return true;
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function saves a tag for a record
	 * @param Vtiger_Request $request
	 */
	public function save(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$tagModel = new Vtiger_Tag_Model();
		$tagModel->set('userid', $currentUser->id);
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tagname', decode_html($request->get('tagname')));
		$tagModel->set('module', $request->getModule());
		$tagModel->save();

		$taggedInfo = Vtiger_Tag_Model::getAll($currentUser->id, $request->getModule(), $request->get('record'));
		$response = new Vtiger_Response();
		$response->setResult($taggedInfo);
		$response->emit($taggedInfo);
	}

	/**
	 * Function deleted a tag
	 * @param Vtiger_Request $request
	 */
	public function delete(Vtiger_Request $request) {
		$tagModel = new Vtiger_Tag_Model();
		$tagModel->set('record', $request->get('record'));
		$tagModel->set('tag_id', $request->get('tag_id'));
		$tagModel->delete();
	}

	/**
	 * Function returns list of tage for the record
	 * @param Vtiger_Request $request
	 */
	public function getTags(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');
		$module = $request->getModule();
		$tags = Vtiger_Tag_Model::getAll($currentUser->id, $module, $record);

		$response = new Vtiger_Response();
		$response->emit($tags);
	}

	public function saveTags(Vtiger_Request $request) {
		$module = $request->get('module');
		$parent = $request->get('addedFrom');

		if($request->has('selected_ids')) {
			$recordIds = $this->getRecordsListFromRequest($request);
		}else{
			$recordIds = array($request->get('record'));
		}

		if($parent && $parent == 'Settings'){
			$recordIds = array();
		}

		$tagsList = $request->get('tagsList');
		$newTags = $tagsList['new'];
		if(empty($newTags)) {
			$newTags = array();
		}
		$existingTags = $tagsList['existing'];
		if(empty($existingTags)) {
			$existingTags = array();
		}
		$deletedTags = $tagsList['deleted'];
		if(empty($deletedTags)) {
			$deletedTags = array();
		}
		$newTagType = $request->get('newTagType');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		if(!is_array($existingTags)) {
			$existingTags = array();
		}

		$result = array();
		foreach($newTags as $tagName) {
			if(empty($tagName)) continue;
			$tagModel = new Vtiger_Tag_Model();
			$tagModel->set('tag', $tagName)->setType($newTagType);
			$tagId = $tagModel->create();
			array_push($existingTags, $tagId);
			$result['new'][$tagId] = array('name'=> decode_html($tagName), 'type' => $newTagType);
		}
		$existingTags = array_unique($existingTags);

		foreach($recordIds as $recordId) {
			if(!empty($recordId)){
				Vtiger_Tag_Model::saveForRecord($recordId, $existingTags, $userId, $module);
				Vtiger_Tag_Model::deleteForRecord($recordId, $deletedTags, $userId, $module);
			}
		}


		$allAccessibleTags =  Vtiger_Tag_Model::getAllAccessible($userId, $module, $recordId);
		foreach ($allAccessibleTags as $tagModel) {
			$result['tags'][] = array('name'=> decode_html($tagModel->getName()), 'type'=>$tagModel->getType(),'id' => $tagModel->getId());
		}
		$allAccessibleTagCount = count($allAccessibleTags);
		$result['moreTagCount'] = $allAccessibleTagCount - Vtiger_Tag_Model::NUM_OF_TAGS_DETAIL;
		$result['deleted'] = $deletedTags;

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function update(Vtiger_Request $request) {
		$module = $request->get('module');
		$tagId = $request->get('id');
		$tagName = $request->get('name');
		$visibility = $request->get('visibility');
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$response = new Vtiger_Response();
		try{
			$tagModel = Vtiger_Tag_Model::getInstanceById($tagId);
			$otherTagModelWithSameName = Vtiger_Tag_Model::getInstanceByName($tagName, $currentUser->getId(), $tagId);
			if($otherTagModelWithSameName !== false) {
				throw new Exception(vtranslate('LBL_SAME_TAG_EXISTS', $module, $tagName));
			}
			if($tagModel->getType() == Vtiger_Tag_Model::PUBLIC_TYPE && $visibility == Vtiger_Tag_Model::PRIVATE_TYPE) {
				//TODO : check if there are no other records tagged by other users 
			   if(Vtiger_Tag_Model::checkIfOtherUsersUsedTag($tagId, $currentUser->getId())) {
				   throw new Exception(vtranslate('LBL_CANT_MOVE_FROM_PUBLIC_TO_PRIVATE'));
			   } 
			}
			$tagModel->setName($tagName)->setType($visibility);
			$tagModel->update();
			$result = array();
			$result['name'] = $tagName;
			$result['type'] = $visibility;

			$response->setResult($result);
		}catch(Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function remove(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$tagId = $request->get('tag_id');
		if( Vtiger_Tag_Model::checkIfOtherUsersUsedTag($tagId, $currentUser->getId())) {
			throw new Exception(vtranslate('LBL_CANNOT_DELETE_TAG'));
		}
		$tagModel = new Vtiger_Tag_Model();
		$tagModel->setId($tagId);

		$response = new Vtiger_Response();
		try{
			$tagModel->remove();
			$response->setResult(array('success' => true));
		}catch(Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
