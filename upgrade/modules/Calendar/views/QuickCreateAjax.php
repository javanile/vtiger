<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		//Need to check record permission as Calendar view is using QuickCreateAjax to show edit form	
		$record = $request->get('record');

		$actionName = ($record) ? 'EditView' : 'CreateView';
		if(!Users_Privileges_Model::isPermitted($moduleName, $actionName, $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function  process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$moduleList = array('Calendar','Events');

		$quickCreateContents = array();
		$recordId = '';$mode = '';
		foreach($moduleList as $module) {
			$info = array();

			$recordModel = Vtiger_Record_Model::getCleanInstance($module);

			//To enable popup edit support from calendar views
			if($moduleName == $module) {
				$recordId = $request->get('record','');
				$mode = $request->get('mode');
				if($mode === 'edit' && !empty($recordId)) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId,$moduleName);
				}
			}

			$moduleModel = $recordModel->getModule();

			$fieldList = $moduleModel->getFields();
			$requestFieldList = array_intersect_key($request->getAll(), $fieldList);
			$relContactId = $request->get('contact_id');
			if (($request->get('parentModule') == 'Contacts' || $request->get('returnmodule') == 'Contacts') && $relContactId) {
				$contactRecordModel = Vtiger_Record_Model::getInstanceById($relContactId);
				$requestFieldList['parent_id'] = $contactRecordModel->get('account_id');
			}

			foreach($requestFieldList as $fieldName => $fieldValue) {
				$fieldModel = $fieldList[$fieldName];
				if($fieldModel->isEditable()) {
					$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));
				}
			}

			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);

			$info['recordStructureModel'] = $recordStructureInstance;
			$info['recordStructure'] = $recordStructureInstance->getStructure();
			$info['moduleModel'] = $moduleModel;
			$quickCreateContents[$module] = $info;
			$picklistDependencyDatasource[$module] = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($module);
		}

		$existingRelatedContacts = $recordModel->getRelatedContactInfo();

		//To add contact ids that is there in the request . Happens in gotoFull form mode of quick create
		$requestContactIdValue = $request->get('contact_id');
		if(!empty($requestContactIdValue)) {
			$existingRelatedContacts[] = array('name' => decode_html(Vtiger_Util_Helper::getRecordName($requestContactIdValue)) ,'id' => $requestContactIdValue);
		}
		//If already selected contact ids, then in gotoFull form should show those selected contact ids
		$idsList = $request->get('contactidlist');
		if(!empty($idsList)) {
			$contactIdsList = explode (';', $idsList);
			foreach($contactIdsList as $contactId) {
				$existingRelatedContacts[] = array('name' => decode_html(Vtiger_Util_Helper::getRecordName($contactId)) ,'id' => $contactId);
			}
		}

		$fieldsInfo = array();
		foreach($fieldList as $name => $model){
			$fieldsInfo[$name] = $model->getFieldInfo();
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE_EVENT',Vtiger_Functions::jsonEncode($picklistDependencyDatasource['Events']));
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE_TODO',Vtiger_Functions::jsonEncode($picklistDependencyDatasource['Calendar']));

		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_'.$moduleName);
		$viewer->assign('QUICK_CREATE_CONTENTS', $quickCreateContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('RELATED_CONTACTS', $existingRelatedContacts);
		$viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));

		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('MODE', $mode);
		$viewer->view('QuickCreate.tpl', $moduleName);
	}
}
