<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_EditAjax_View extends Vtiger_QuickCreateAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if (!(Users_Privileges_Model::isPermitted($moduleName, 'CreateView'))) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function getFields($documentType){
		switch($documentType){
			case 'I' : case 'E' : return array('filename','assigned_user_id','folderid');
			case 'W' : return array('notes_title','assigned_user_id','folderid','filename','fileversion');
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $recordModel->getModule();
		$showType = $documentType = $request->get('type');

		$fieldNames = $this->getFields($documentType);
		$allFields = $moduleModel->getFields();
		if($documentType == 'W') {
			$documentType = 'I';
			//To Add Custom fields for webdocument create view
			$fieldsToEliminate = array('document_source','createdtime','modifiedtime','filetype','filesize','filedownloadcount','folderid','note_no','modifiedby','created_user_id');
			$fieldsToEliminate = array_merge($fieldNames,$fieldsToEliminate);
			$allFieldsNames = array_keys($allFields);
			$documentsCustomFields = array_diff($allFieldsNames,$fieldsToEliminate);
			$fieldNames = array_diff(array_merge($fieldNames,$documentsCustomFields),array('notecontent'));
			//Add note content as the last field
			$fieldNames[] = 'notecontent';
		}
		if($request->get('relationOperation')=='true'){
				$requestFieldList = array_intersect_key($request->getAll(), $allFields);
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $recordStructureInstance->getStructure();
		foreach($recordStructure as $blocks) {
			foreach($blocks as $fieldLabel=> $fieldValue) {
				if($requestFieldList && array_key_exists($fieldLabel, $requestFieldList)) {
					$relationFieldName = $fieldLabel;
					$fieldValue->set('fieldvalue', $request->get($fieldLabel));
				}
				if(in_array($fieldLabel,$fieldNames)) $fieldModel[] = $fieldValue;
			}
		}
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FIELD_MODELS',$fieldModel);
		$viewer->assign('DOCUMENT_TYPE',$documentType);
		$viewer->assign('DOCUMENT_SHOW_TYPE',$showType);
		if($request->get('relationOperation')){
			$viewer->assign('RELATION_OPERATOR', $request->get('relationOperation'));
			$viewer->assign('PARENT_MODULE', $request->get('sourceModule'));
			$viewer->assign('PARENT_ID', $request->get('sourceRecord'));
			if ($relationFieldName) {
				$viewer->assign('RELATION_FIELD_NAME', $relationFieldName);
			}
		}
		$viewer->assign('SINGLE_MODULE', 'SINGLE_'.$moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		echo $viewer->view('AjaxEdit.tpl',$moduleName,true);

	}


	public function getHeaderScripts(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.$moduleName.resources.Edit"
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}
}