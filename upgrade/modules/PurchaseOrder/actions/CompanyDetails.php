<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PurchaseOrder_CompanyDetails_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName, $moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

     function __construct() {
        parent::__construct();
        $this->exposeMethod('getCompanyDetails');
        $this->exposeMethod('getAddressDetails');
    }

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		return false;
	}

    function getCompanyDetails($request){
        $companyModel = Vtiger_CompanyDetails_Model::getInstanceById();
        $companyDetails = array(
            'street' => $companyModel->get('organizationname') .' '.$companyModel->get('address'),
            'city' => $companyModel->get('city'),
            'state' => $companyModel->get('state'),
            'code' => $companyModel->get('code'),
            'country' =>  $companyModel->get('country'),
            );
		$response = new Vtiger_Response();
		$response->setResult($companyDetails);
		$response->emit();
	}

	function getAddressDetails($request){
		$recordModel = Vtiger_Record_Model::getInstanceById($request->get('recordId'));
		$addressType = $request->get('type');
		$code = 'code';
		if($recordModel->getModuleName() == 'Vendors'){
			$addressType = '';
			$code = 'postalcode';
		} else if($recordModel->getModuleName() == 'Contacts'){
			$code = 'zip';
			if($addressType == 'bill')
				$addressType = 'mailing';
			else
				$addressType = 'other';
		} else {
			$addressType = $addressType.'_';
		}
		$addressDetails = array(
			'street'	=> html_entity_decode($recordModel->get($addressType.'street'),	ENT_COMPAT, 'UTF-8'),
			'city'		=> html_entity_decode($recordModel->get($addressType.'city'),	ENT_COMPAT, 'UTF-8'), 
			'state'		=> html_entity_decode($recordModel->get($addressType.'state'),	ENT_COMPAT, 'UTF-8'), 
			'code'		=> html_entity_decode($recordModel->get($addressType.$code),	ENT_COMPAT, 'UTF-8'),
			'pobox'		=> html_entity_decode($recordModel->get($addressType.'pobox'),	ENT_COMPAT, 'UTF-8'),
			'country'	=> html_entity_decode($recordModel->get($addressType.'country'),ENT_COMPAT, 'UTF-8'),
		);
		$response = new Vtiger_Response();
		$response->setResult($addressDetails);
		$response->emit();
	}
}

?>