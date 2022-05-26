<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_QuickCreateAjax_View extends Vtiger_IndexAjax_View {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if (!(Users_Privileges_Model::isPermitted($moduleName, 'CreateView'))) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$moduleModel = $recordModel->getModule();

		$documentTypes = array('I' => array('tabName' => 'InternalDoc', 'label' => 'LBL_INTERNAL'));
		foreach($documentTypes as $documentType => $typeDetails){
			$fields[$documentType] = $this->getFields($documentType);
		}

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$recordStructure = $recordStructureInstance->getStructure();
		foreach($fields as $docType => $specificFields){
			foreach($specificFields as $specificFieldName){
				if(in_array($specificFieldName,  array_keys($recordStructure)))
					$specificFieldModels[] = $recordStructure[$specificFieldName];
			}
			$quickCreateContents[$docType] = $specificFieldModels;
			unset($specificFieldModels);
		}

		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$relationOperation = $request->get('relationOperation');
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);
        foreach($requestFieldList as $requestFieldName => $requestFieldValue) {
            if(array_key_exists($requestFieldName, $fieldList)) {
                $moduleFieldModel = $fieldList[$requestFieldName];
                $recordModel->set($requestFieldName, $moduleFieldModel->getDBInsertValue($requestFieldValue));
            }
        }

		$fieldsInfo = array();
		foreach ($fieldList as $name => $model) {
			if ($relationOperation && array_key_exists($name, $requestFieldList)) {
				$relationFieldName = $name;
			}
			$fieldsInfo[$name] = $model->getFieldInfo();
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', Vtiger_Functions::jsonEncode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('DOC_TYPES',$documentTypes);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_'.$moduleName);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUICK_CREATE_CONTENTS', $quickCreateContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
		$viewer->assign('FIELD_MODELS', $fieldList);
		$viewer->assign('FILE_LOCATION_TYPE', $request->get('type'));
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

		if ($relationOperation) {
			$viewer->assign('RELATION_OPERATOR', $relationOperation);
			$viewer->assign('PARENT_MODULE', $request->get('sourceModule'));
			$viewer->assign('PARENT_ID', $request->get('sourceRecord'));
			if ($relationFieldName) {
				$viewer->assign('RELATION_FIELD_NAME', $relationFieldName);
			}
		}

		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		echo $viewer->view('QuickCreate.tpl',$moduleName,true);
	}

	public function getFields($documentType){
		$fields = array();
		switch ($documentType) {
			case 'I' :
			case 'E' :	$fields = array('filename', 'assigned_user_id', 'folderid');	break;
			case 'W' :	$recordModel = Vtiger_Record_Model::getCleanInstance('Documents');
						$moduleModel = $recordModel->getModule();
						$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
						$quickCreateFields = $recordStructureInstance->getStructure();
						//make sure the note content is always at the bottom
						$fields = array_diff(array_keys($quickCreateFields), array('notecontent'));
						$fields[] = 'notecontent';
		}
		return $fields;
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$jsFileNames = array(
			"modules.$moduleName.resources.Edit",
			"modules.Vtiger.resources.CkEditor"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

}