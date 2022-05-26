<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ActivityTypeViews_View extends Vtiger_Index_View {

	function __construct() {
		$this->exposeMethod('addActivityType');
		$this->exposeMethod('editActivityType');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function addActivityType(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$calendarViews = Calendar_Module_Model::getCalendarViewTypes($currentUser->id);
		$allViewTypes = Calendar_Module_Model::getCalendarViewTypesToAdd($currentUser->id);
		$allViewConditions = Calendar_Module_Model::getCalendarViewConditions();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWTYPES', $calendarViews);
		$viewer->assign('ADDVIEWS', $allViewTypes);
		$viewer->assign('VIEWCONDITIONS', $allViewConditions);
		$viewer->view('AddActivityType.tpl', $moduleName);
	}

	public function editActivityType(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$calendarViews = Calendar_Module_Model::getCalendarViewTypes($currentUser->id);
		$visibleViewTypes = Calendar_Module_Model::getVisibleCalendarViewTypes($currentUser->id);
		$allViewConditions = Calendar_Module_Model::getCalendarViewConditions();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWTYPES', $calendarViews);
		$viewer->assign('EDITVIEWS', $visibleViewTypes);
		$viewer->assign('VIEWCONDITIONS', $allViewConditions);
		$viewer->view('EditActivityType.tpl', $moduleName);
	}

}
