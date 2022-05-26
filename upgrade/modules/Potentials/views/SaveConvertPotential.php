<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/include/Webservices/ConvertPotential.php');

class Potentials_SaveConvertPotential_View extends Vtiger_View_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$projectModuleModel = Vtiger_Module_Model::getInstance('Project');

		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserModel->hasModuleActionPermission($projectModuleModel->getId(), 'CreateView')) {
			throw new AppException(vtranslate('LBL_CREATE_PROJECT_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$modules = $request->get('modules');
		$assignId = $request->get('assigned_user_id');
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$entityValues = array();

		$entityValues['assignedTo'] = vtws_getWebserviceEntityId(vtws_getOwnerType($assignId), $assignId);
		$entityValues['potentialId'] = vtws_getWebserviceEntityId($request->getModule(), $recordId);

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());
		$convertPotentialFields = $recordModel->getConvertPotentialFields();

		$availableModules = array('Project');
		foreach ($availableModules as $module) {
			if(vtlib_isModuleActive($module)&& in_array($module, $modules)) {
				$entityValues['entities'][$module]['create'] = true;
				$entityValues['entities'][$module]['name'] = $module;

				// Converting lead should save records source as CRM instead of WEBSERVICE
				$entityValues['entities'][$module]['source'] = 'CRM';
				foreach ($convertPotentialFields[$module] as $fieldModel) {
					$fieldName = $fieldModel->getName();
					$fieldValue = $request->get($fieldName);

					//Potential Amount Field value converting into DB format
					if ($fieldModel->getFieldDataType() === 'currency') {
						if($fieldModel->get('uitype') == 72){
							// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
							$fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue, null, true);
						} else {
							$fieldValue = Vtiger_Currency_UIType::convertToDBFormat($fieldValue);
						}
					} elseif ($fieldModel->getFieldDataType() === 'date') {
						$fieldValue = DateTimeField::convertToDBFormat($fieldValue);
					} elseif ($fieldModel->getFieldDataType() === 'reference' && $fieldValue) {
						$ids = vtws_getIdComponents($fieldValue);
						if (count($ids) === 1) {
							$fieldValue = vtws_getWebserviceEntityId(getSalesEntityType($fieldValue), $fieldValue);
						}
					}
					$entityValues['entities'][$module][$fieldName] = $fieldValue;
				}
			}
		}
		try {
			$result = vtws_convertpotential($entityValues, $currentUser);
		} catch(Exception $e) {
			$this->showError($request, $e);
			exit;
		}

		if(!empty($result['Project'])) {
			$projectIdComponents = vtws_getIdComponents($result['Project']);
			$projectId = $projectIdComponents[1];
		}

		if(!empty($projectId)) {
			header("Location: index.php?view=Detail&module=Project&record=$projectId");
		} else {
			$this->showError($request);
			exit;
		}
	}

	function showError($request, $exception=false) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$isDupicatesFailure = false;
		if($exception != false) {
			$viewer->assign('EXCEPTION', $exception->getMessage());
			if ($exception instanceof DuplicateException) {
				$isDupicatesFailure = true;
				$viewer->assign('EXCEPTION', $exception->getDuplicationMessage());
			}
		}

		$currentUser = Users_Record_Model::getCurrentUserModel();

		$viewer->assign('IS_DUPICATES_FAILURE', $isDupicatesFailure);
		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ConvertPotentialError.tpl', $moduleName);
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
