<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Ownergroup_UIType extends Vtiger_Owner_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/OwnerGroup.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		$recordModel = new Settings_Groups_Record_Model();
		$recordModel->set('groupid', $value);
		$detailViewUrl = $recordModel->getDetailViewUrl();
		$groupName = getGroupName($value);
		return "<a href=".$detailViewUrl.">".$groupName[0]."</a>";
	}

	public function getListSearchTemplateName() {
		return 'uitypes/OwnerGroupFieldSearchView.tpl';
	}

}
