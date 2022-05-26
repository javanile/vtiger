<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Portal_List_View extends Vtiger_Index_View {

	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request);

		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);
		$viewer->view('ListViewHeader.tpl', $request->getModule(false));
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);

		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $request->getModule();
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$searchValue = $request->get('search_value');

		$orderParams = Vtiger_ListView_Model::getSortParamsSession($moduleName);
		if($request->get('mode') == 'removeAlphabetSearch') {
			Vtiger_ListView_Model::deleteParamsSession($moduleName, array('search_value'));
			$searchValue = '';
		}
		if($request->get('mode') == 'removeSorting') {
			Vtiger_ListView_Model::deleteParamsSession($moduleName, array('orderby', 'sortorder'));
			$orderBy = '';
			$sortOrder = '';
		}
		if(empty($orderBy) && empty($searchValue) && empty($pageNumber)) {
			$orderParams = Vtiger_ListView_Model::getSortParamsSession($moduleName);
			if($orderParams) {
				$pageNumber = $orderParams['page'];
				$orderBy = $orderParams['orderby'];
				$sortOrder = $orderParams['sortorder'];
				$searchValue = $orderParams['search_value'];
			}
		} else if($request->get('nolistcache') != 1) {
			$params = array('page' => $pageNumber, 'orderby' => $orderBy, 'sortorder' => $sortOrder, 'search_value' => $searchValue);
			Vtiger_ListView_Model::setSortParamsSession($moduleName, $params);
		}

		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
			$faSortImage = "fa-sort-desc";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
			$faSortImage = "fa-sort-asc";
		}

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

		$listViewModel = new Portal_ListView_Model();

		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder',$sortOrder);
		}
		if(!empty($searchValue)) {
			$listViewModel->set('search_value', $searchValue);
		}

		// preProcess is already loading this, we can reuse
		if(!$this->pagingModel){
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('viewid', $request->get('viewname'));
		} else{
			$pagingModel = $this->pagingModel;
		}

		$listviewEntries = $listViewModel->getListViewEntries($pagingModel);

		//if list view entries restricted to show, paging should not fail
		if(!$this->noOfEntries) {
			$noOfEntries = count($listviewEntries);
		}

		$viewer->assign('PAGE_NUMBER',$pageNumber);
		$listviewEntries = $listViewModel->getListViewEntries($pagingModel);
		$pagingModel->calculatePageRange($listviewEntries);

		$viewer->assign('LISTVIEW_ENTRIES', $listviewEntries);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT',$noOfEntries);
		$viewer->assign('ALPHABET_VALUE', $searchValue);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('RECORD_COUNT', count($listviewEntries));
		$viewer->assign('CURRENT_PAGE', $pageNumber);
		$viewer->assign('PAGING_INFO', $listViewModel->calculatePageRange($listviewEntries, $pagingModel));
		$viewer->assign('FASORT_IMAGE',$faSortImage);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pagingModel->get('page'));
		$viewer->assign('NO_OF_ENTRIES', count($listviewEntries));
	}

	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			"~layouts/v7/lib/jquery/sadropdown.js",
			"~layouts/".Vtiger_Viewer::getDefaultLayoutName()."/lib/jquery/floatThead/jquery.floatThead.js",
			"~layouts/".Vtiger_Viewer::getDefaultLayoutName()."/lib/jquery/perfect-scrollbar/js/perfect-scrollbar.jquery.js",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function getRecordsCount(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$listQuery = $this->getQuery();
		$queryParts = explode('FROM', $listQuery);
		$query = 'SELECT COUNT(*) AS count FROM '.$queryParts[1];
		$result = $db->pquery($query, array());

		return $db->query_result($result, 0, 'count');
	}

	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			"~layouts/".Vtiger_Viewer::getDefaultLayoutName()."/lib/jquery/perfect-scrollbar/css/perfect-scrollbar.css",
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}

}
