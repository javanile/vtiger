<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_MailConverter_CheckMailBoxMaxLimit_Action extends Settings_Vtiger_Index_Action {
	
	public function process(Vtiger_Request $request) {
		$recordsCount = Settings_MailConverter_Record_Model::getCount();
		$qualifiedModuleName = $request->getModule(false);
		$response = new Vtiger_Response();
        global $max_mailboxes;
        if ($recordsCount < $max_mailboxes) {
			$result = array(true);
			$response->setResult($result);
		} else {
			$response->setError(vtranslate('LBL_MAX_LIMIT_EXCEEDED', $qualifiedModuleName));
		}
		$response->emit();
	}
}
?>
