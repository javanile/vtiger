<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/
require_once 'modules/WSAPP/WSAPPLogs.php';

class Vtiger_ExtensionViews_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showLogs');
		$this->exposeMethod('showLogDetail');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$this->showLogs($request);
	}

	function getHeaderScripts(Vtiger_Request $request) {
		$moduleName = $request->get('extensionModule');
		$jsFileNames = array(
			'modules.'.$moduleName.'.resources.Index',
			'modules.'.$moduleName.'.resources.Settings'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $jsScriptInstances;
	}

	/**
	 * Function to transform log data to user format
	 * @param <array> $logData
	 * @retun <array> $data
	 */
	function convertDataToUserFormat($logData) {
		$data = array();
		foreach ($logData as $log) {
			$date = new DateTimeField($log['sync_datetime']);
			$log['sync_date'] = $date->getDisplayDate();
			$log['sync_time'] = $date->getDisplayTime();
			$data[] = $log;
		}

		return $data;
	}

	/**
	 * Function to convert log details to user format
	 * @param <array> $logDetails
	 * @return <array> $data
	 */
	function convertLogDetailsToUserFormat($logDetails, $moduleName) {
		$db = PearDatabase::getInstance();
		$data = array();
		$i = 0;
		foreach ($logDetails as $logId) {
			if(!is_numeric($logId)) {
				list ($moduleId, $recordId) = explode('x', $logId);
				if($logId && $moduleId) {
					$wsObject = VtigerWebserviceObject::fromId($db, $moduleId);
					$moduleName = $wsObject->getEntityName();
				}
			} else {
				if($logId) {
					$recordId = $logId;
					$moduleName = getSalesEntityType($recordId);
				}
			}
			$name = getEntityName($moduleName, $recordId);
			if(!empty($name)) {
				$data[$i]['module'] = $moduleName;
				$data[$i]['name'] = $name[$recordId];
				$data[$i]['link'] = $this->getDetailViewLink($moduleName, $recordId);
				$i++;
			}
		}

		return $data;
	}

	function getDetailViewLink($moduleName, $recordId) {
		return 'index.php?module='.$moduleName.'&view=Detail&record='.$recordId;
	}

	/**
	 * Function to check if sync settings exists
	 * @return <boolean> true/false
	 */
	function checkSyncSettings() {
		return true;
	}

	/**
	 * Function to check if sync is ready
	 * @return <boolean> true/false
	 */
	function checkIsSyncReady() {
		return true;
	}

	function showLogs(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$sourceModule = $request->getModule();
		$moduleName = $request->get('extensionModule');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$page = $request->get('page');
		$syncReady = $this->checkIsSyncReady();
		$viewType = $request->get('viewType');

		$pagingModel = new Vtiger_Paging_Model();
		if(!$page || $page == 1) {
			$page = 1;
			$pagingModel->set('prevPageExists', false);
		}
		$pagingModel->set('page', $page);
		$logData = WSAPP_Logs::getSyncCounts($pagingModel, $moduleName);
		$logsCount = count($logData);

		// if user has not authenticated the extension redirect to settings page
		if(!$syncReady && $viewType != 'modal' && $logsCount == 0) {
			if(!$request->isAjax()){
				$settingsUrl = $moduleModel->getExtensionSettingsUrl($sourceModule);
				header("Location: $settingsUrl");
			}
		}

		$pagingModel->calculatePageRange($logData);
		if(count($logData) > $pagingModel->getPageLimit()){
			array_pop($logData);
			$logsCount = $logsCount - 1;
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}

		$data = $this->convertDataToUserFormat($logData);

		$totalCount = WSAPP_Logs::getTotalSyncCount($moduleName);
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}

		$viewer->assign('PAGE_COUNT', $pageCount);
		$viewer->assign('TOTAL_RECORD_COUNT', $totalCount);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $logsCount);
		$viewer->assign('IS_SYNC_READY', $syncReady);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('DATA', $data);
		$viewer->assign('PAGING_MODEL', $pagingModel);

		if ($viewType == 'modal') {
			$viewer->assign('MODAL', true);
			echo $viewer->view('ExtensionListImportLog.tpl',$moduleName);
		} else {
			$viewer->view('ExtensionListLog.tpl', $moduleName);
		}
	}

	function showLogDetail(Vtiger_Request $request) {
		$id = $request->get('logid');
		$type = $request->get('logtype');
		$moduleName = $request->get('module');
		$logModule = WSAPP_Logs::getModuleFromLogId($id);
		$sourceModule = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$viewer = $this->getViewer($request);
		$logData = WSAPP_Logs::getSyncCountDetails($id);
		if($type == 'app_skip' || $type == 'vt_skip') {
			$data = json_decode(decode_html($logData[$type.'_info'], true));
			$i = 0;
			$tmpData = array();
			foreach ($data as $skipInfo) {
				$skipError = (array) $skipInfo;
				foreach ($skipError as $name=>$errorMsg) {
					$tmpData[$i]['module'] = $logModule;
					$tmpData[$i]['name'] = $name;
					$tmpData[$i]['error'] = $errorMsg;
					$i++;
				}
			}
			$data = $tmpData;
		} else {
			$data = json_decode(decode_html($logData[$type.'_ids'], true));
			$data = $this->convertLogDetailsToUserFormat($data, $logModule);
		}
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('LOG_MODULE', $logModule);
		$viewer->assign('LOG_ID', $id);
		$viewer->assign('TYPE', $type);
		$viewer->assign('DATA', $data);
		$viewer->view('ExtensionLogDetail.tpl', $moduleName);
	}

}