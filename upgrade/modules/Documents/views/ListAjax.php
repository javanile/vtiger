<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_ListAjax_View extends Documents_List_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
		$this->exposeMethod('showSearchResults');
		$this->exposeMethod('ShowListColumnsEdit');
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Extending Vtiger List Ajax API to show Advance Search results
	 * @param Vtiger_Request $request
	 */
	public function showSearchResults(Vtiger_Request $request) {
		$vtigerListAjaxInstance = new Vtiger_ListAjax_View();
		$vtigerListAjaxInstance->showSearchResults($request);
	}

	/**
	 * Extending Vtiger List Ajax API to show List Columns Edit view
	 * @param Vtiger_Request $request
	 */
	public function ShowListColumnsEdit(Vtiger_Request $request){
		$vtigerListAjaxInstance = new Vtiger_ListAjax_View();
		$vtigerListAjaxInstance->ShowListColumnsEdit($request);
	}
}