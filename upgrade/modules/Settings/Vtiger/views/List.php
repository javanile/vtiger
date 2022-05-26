<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_List_View extends Settings_Vtiger_Index_View {
	protected $listViewEntries = false;
	protected $listViewHeaders = false;

	function __construct() {
		parent::__construct();
	}

	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, true);

		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$sourceModule  = $request->get('sourceModule');
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->view('ListViewHeader.tpl', $request->getModule(false));
	}

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewContents.tpl', $request->getModule(false));
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$sourceModule = $request->get('sourceModule');
		$forModule = $request->get('formodule');
		
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		
		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);

        // preProcess is already loading this, we can reuse
        if(!$this->pagingModel){
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
        } else{
            $pagingModel = $this->pagingModel;
        }

		if(!empty($searchKey) && !empty($searchValue)) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}
		
		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}
		if(!empty($sourceModule)) {
			$listViewModel->set('sourceModule', $sourceModule);
		}
		if(!empty($forModule)) {
			$listViewModel->set('formodule', $forModule);
		}
		if(!$this->listViewHeaders){
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		if(!$this->listViewEntries){
            $pagingModel = new Vtiger_Paging_Model();
            $pagingModel->set('page', $pageNumber);
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
            $this->pagingModel = $pagingModel;
		}
		$noOfEntries = count($this->listViewEntries);
		if(!$this->listViewLinks){
			$this->listViewLinks = $listViewModel->getListViewLinks();
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE_MODEL', $listViewModel->getModule());

        if(!$this->pagingModel){
            $this->pagingModel = $pagingModel;
        }
        
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('LISTVIEW_ENTRIES_COUNT',$noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('SHOW_LISTVIEW_CHECKBOX', true);

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			if(!$this->listViewCount){
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$totalCount = $this->listViewCount;
			$pageLimit = $this->pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}
	}
    
    public function postProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->view('ListViewFooter.tpl', $request->getModule(false));
        parent::postProcess($request);
    }

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			'modules.Settings.Vtiger.resources.List',
			"modules.Settings.$moduleName.resources.List",
			"modules.Settings.Vtiger.resources.$moduleName",
            "~layouts/v7/lib/jquery/sadropdown.js",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
    
     /**
     * Setting module related Information to $viewer (for Vtiger7)
     * @param type $request
     * @param type $moduleModel
     */
    public function setModuleInfo($request, $moduleModel){
        
        $viewer = $this->getViewer($request);
        $listViewModel = Settings_Vtiger_ListView_Model::getInstance($request->getModule(false));
        $linkParams = array('MODULE'=>$request->getModule(false), 'ACTION'=>$request->get('view'));

        if(!$this->listViewLinks){
            $this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
        }
        $viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
        
    }
    
}
