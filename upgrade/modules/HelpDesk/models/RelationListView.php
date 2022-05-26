<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_RelationListView_Model extends Vtiger_RelationListView_Model {

	public function getCreateViewUrl() {
		$createViewUrl = parent::getCreateViewUrl();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();
		$createViewUrl .= '&relationOperation=true&contact_id='.$parentRecordModule->get('contact_id').'&account_id='.$parentRecordModule->get('parent_id').'&sourceRecord='.$parentRecordModule->getId().'&sourceModule='.$parentModule->getName();
		return $createViewUrl;
	}

}

?>
