<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_CalendarUserActions_Action extends Vtiger_Action_Controller{
	
	function __construct() {
		$this->exposeMethod('deleteUserCalendar');
		$this->exposeMethod('addUserCalendar');
		$this->exposeMethod('deleteCalendarView');
		$this->exposeMethod('addCalendarView');
		$this->exposeMethod('checkDuplicateView');
	}
	
	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		
		if(!Users_Privileges_Model::isPermitted($moduleName, 'View', $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to delete the user calendar from shared calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function deleteUserCalendar(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('userid');
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT 1 FROM vtiger_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		if($db->num_rows($result) > 0) {
			$db->pquery('UPDATE vtiger_shareduserinfo SET visible=? WHERE userid=? AND shareduserid=?', array('0', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO vtiger_shareduserinfo (userid, shareduserid, visible) VALUES(?, ?, ?)', array($userId, $sharedUserId, '0'));
		}
		
		$userName = getUserFullName($sharedUserId);
		if(!$userName) {
			$userName = Vtiger_Functions::getGroupRecordLabel($sharedUserId);
		}
		$result = array('userid' => $userId, 'sharedid' => $sharedUserId, 'username' => $userName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add other user calendar to shared calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function addUserCalendar(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$sharedUserId = $request->get('selectedUser');
		$color = $request->get('selectedColor');
		
		$db = PearDatabase::getInstance();
		
		$queryResult = $db->pquery('SELECT 1 FROM vtiger_shareduserinfo WHERE userid=? AND shareduserid=?', array($userId, $sharedUserId));
		
		if($db->num_rows($queryResult) > 0) {
			$db->pquery('UPDATE vtiger_shareduserinfo SET color=?, visible=? WHERE userid=? AND shareduserid=?', array($color, '1', $userId, $sharedUserId));
		} else {
			$db->pquery('INSERT INTO vtiger_shareduserinfo (userid, shareduserid, color, visible) VALUES(?, ?, ?, ?)', array($userId, $sharedUserId, $color, '1'));
		}
		
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true));
		$response->emit();
	}
	
	/**
	 * Function to check duplication for calendar views while adding
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function checkDuplicateView(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		if (Calendar_Module_Model::checkDuplicateView($request)) {
			$result = array('success' => true, 'message' => vtranslate('LBL_DUPLICATE_VIEW_EXIST', $moduleName));
		} else {
			$result = array('success' => false);
		}
		
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to delete the calendar view from My Calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function deleteCalendarView(Vtiger_Request $request) {
		Calendar_Module_Model::deleteCalendarView($request);
		
		$result = array('viewmodule' => $request->get('viewmodule'), 'viewfieldname' => $request->get('viewfieldname'), 'viewfieldlabel' => $request->get('viewfieldlabel'));
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	/**
	 * Function to add calendar views to My calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function addCalendarView(Vtiger_Request $request) {
		$type = Calendar_Module_Model::addCalendarView($request);
		
		$response = new Vtiger_Response();
		$response->setResult(array('success' => true, 'type' => $type));
		$response->emit();
	}
	

}