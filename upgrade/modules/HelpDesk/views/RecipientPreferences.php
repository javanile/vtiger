<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class HelpDesk_RecipientPreferences_View extends Project_RecipientPreferences_View {

	public function process(Vtiger_Request $request) {
		$sourceModule = $request->getModule();
		$emailFieldsInfo = $this->getEmailFieldsInfo($sourceModule);
		$viewer = $this->getViewer($request);
		$viewer->assign('EMAIL_FIELDS_LIST', $emailFieldsInfo);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		echo $viewer->view('RecipientPreferences.tpl', 'Project', true);
	}
}
