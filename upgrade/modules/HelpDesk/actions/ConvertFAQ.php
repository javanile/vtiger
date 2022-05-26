<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_ConvertFAQ_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$recordPermission = Users_Privileges_Model::isPermitted('Faq', 'CreateView');

		if(!$recordPermission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$result = array();
		if (!empty ($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			$faqRecordModel = Faq_Record_Model::getInstanceFromHelpDesk($recordModel);

			$answer = $faqRecordModel->get('faq_answer');
			if ($answer) {
				try {
					$faqRecordModel->save();
					header("Location: ".$faqRecordModel->getDetailViewUrl());
				} catch (DuplicateException $e) {
					$requestData = $request->getAll();
					unset($requestData['__vtrftk']);
					unset($requestData['action']);
					unset($requestData['record']);
					$requestData['view'] = 'Edit';
					$requestData['module'] = 'HelpDesk';
					$requestData['duplicateRecords'] = $e->getDuplicateRecordIds();

					global $vtiger_current_version;
					$viewer = new Vtiger_Viewer();
					$viewer->assign('REQUEST_DATA', $requestData);
					$viewer->assign('REQUEST_URL', $faqRecordModel->getEditViewUrl()."&parentId=$recordId&parentModule=$moduleName");
					$viewer->view('RedirectToEditView.tpl', 'Vtiger');
				} catch (Exception $e) {
				}
			} else {
				header("Location: ".$faqRecordModel->getEditViewUrl()."&parentId=$recordId&parentModule=$moduleName");
			}
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
