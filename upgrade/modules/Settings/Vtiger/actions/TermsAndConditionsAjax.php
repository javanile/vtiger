<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_TermsAndConditionsAjax_Action extends Settings_Vtiger_Basic_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('getModuleTermsAndConditions');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function save(Vtiger_Request $request) {
		$moduleName = $request->get('type');
		$model = Settings_Vtiger_TermsAndConditions_Model::getInstance($moduleName);
		$model->setText($request->get('tandc'));
		$model->save();

		$response = new Vtiger_Response();
		$response->emit();
	}

	public function getModuleTermsAndConditions(Vtiger_Request $request) {
		$moduleName = $request->get('type');
		$model = Settings_Vtiger_TermsAndConditions_Model::getInstance($moduleName);
		$conditionText = $model->getText();

		$response = new Vtiger_Response();
		$response->setResult(decode_html($conditionText));
		$response->emit();
	}

}
