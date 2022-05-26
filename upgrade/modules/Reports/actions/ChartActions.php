<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartActions_Action extends Vtiger_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('pinChartToDashboard');
		$this->exposeMethod('unpinChartFromDashboard');
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    /**
     * Function to add the report chart to dashboard
     * @param Vtiger_Request $request
     */
    public function pinChartToDashboard(Vtiger_Request $request){
        $db = PearDatabase::getInstance();
        $reportid = $request->get('reportid');
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentuserid = $currentUser->getId();
        $widgetTitle = $request->get('title');
        $response = new Vtiger_Response();
        
        $query = "SELECT 1 FROM vtiger_module_dashboard_widgets WHERE reportid = ? AND userid = ?";
        $param = array($reportid,$currentuserid);
        $result = $db->pquery($query, $param);
        $numOfRows = $db->num_rows($result);
        if($numOfRows >= 1){
            $result = array('pinned'=>false,'duplicate'=>true);
            $response->setResult($result);
            $response->emit();
            return;
        }
        $dashBoardTabId = $request->get('dashBoardTabId');
        if(empty($dashBoardTabId)) {
            // In Vtiger7, we need to pin this report widget to first tab of that user
            $dasbBoardModel = Vtiger_DashBoard_Model::getInstance("Reports");
            $defaultTab = $dasbBoardModel->getUserDefaultTab($currentUser->getId());
            $dashBoardTabId = $defaultTab['id'];
        }
        
        $query = "INSERT INTO vtiger_module_dashboard_widgets (userid,reportid,linkid,title,dashboardtabid) VALUES (?,?,?,?,?)";
        $param = array($currentuserid,$reportid,0,$widgetTitle,$dashBoardTabId);
        $result = $db->pquery($query, $param);

        $result = array('pinned'=>true,'duplicate'=>false);
        $response->setResult($result);
        $response->emit();
        
    }
    
	function unpinChartFromDashboard($request) {
		$db = PearDatabase::getInstance();
        $reportid = $request->get('reportid');
        $currentUser = Users_Record_Model::getCurrentUserModel();
		
		$widgetInstance = Vtiger_Widget_Model::getInstanceWithReportId($reportid, $currentUser->getId());
		$widgetInstance->remove();
		
		$response = new Vtiger_Response();
		$response->setResult(array('unpinned' => true));
		$response->emit();
	}
}
