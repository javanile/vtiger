<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 2.0
 * ("License.txt"); You may not use this file except in compliance with the License
 * The Original Code is: Vtiger CRM Open Source
 * The Initial Developer of the Original Code is Vtiger.
 * Portions created by Vtiger are Copyright (C) Vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Vtiger_SaveWidgetSize_Action extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$id = $request->get('id');
		$tabId = $request->get('tabid');
		$size = Zend_Json::encode($request->get('size'));
		list ($linkid, $widgetid) = explode('-', $id);

		if ($widgetid) {
			Vtiger_Widget_Model::updateWidgetSize($size, NULL, $widgetid, $currentUser->getId(), $tabId);
		} else {
			Vtiger_Widget_Model::updateWidgetSize($size, $linkid, NULL, $currentUser->getId(), $tabId);
		}

		$response = new Vtiger_Response();
		$response->setResult(array('Save' => 'OK'));
		$response->emit();
	}

}
