<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_InRelation_View extends Vtiger_RelatedList_View {

	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');

		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
		$moduleFields = $relatedModuleModel->getFields();

		$requestedPage = $request->get('page');
		if (empty($requestedPage)) {
			$requestedPage = 1;
		}

		$searchParams = $request->get('search_params');
		if (empty($searchParams)) {
			$searchParams = array();
		}

		$whereCondition = array();

		foreach ($searchParams as $fieldListGroup) {
			foreach ($fieldListGroup as $fieldSearchInfo) {
				$fieldModel = $moduleFields[$fieldSearchInfo[0]];
				$tableName = $fieldModel->get('table');
				$column = $fieldModel->get('column');
				$whereCondition[$fieldSearchInfo[0]] = array($tableName.'.'.$column, $fieldSearchInfo[1], $fieldSearchInfo[2]);

				$fieldSearchInfoTemp = array();
				$fieldSearchInfoTemp['searchValue'] = $fieldSearchInfo[2];
				$fieldSearchInfoTemp['fieldName'] = $fieldName = $fieldSearchInfo[0];
				$fieldSearchInfoTemp['comparator'] = $fieldSearchInfo[1];
				$searchParams[$fieldName] = $fieldSearchInfoTemp;
			}
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $requestedPage);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);

		if (!empty($whereCondition))
			$relationListView->set('whereCondition', $whereCondition);

		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if ($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		if (!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder', $sortOrder);
		}
		$relationListView->tab_label = $request->get('tab_label');
		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);

		$relationModel = $relationListView->getRelationModel();
		$relationField = $relationModel->getRelationField();

		$subProductsTotalCost = 0.00;
		$subProductsCostsInfo = array();
		if ($moduleName === $relatedModuleName && $relationListView->tab_label === 'Product Bundles') {//Products && Child Products
			$parentModuleModel = $parentRecordModel->getModule();
			$relationField = $parentModuleModel->getField('qty_per_unit');

			if ((!$request->get('sortorder') && !$request->get('page'))) {
				$parentRecordModel->set('currency_id', getProductBaseCurrency($parentId, $parentModuleModel->getName()));

				$subProductsCostsInfo = $parentRecordModel->getSubProductsCostsAndTotalCostInUserCurrency();
				$subProductsTotalCost = $subProductsCostsInfo['subProductsTotalCost'];
				$subProductsCostsInfo = $subProductsCostsInfo['subProductsCosts'];
			}
		}

		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$moduleFields = $relatedModuleModel->getFields();
		$fieldsInfo = array();
		foreach ($moduleFields as $fieldName => $fieldModel) {
			$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_RECORDS', $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		$viewer->assign('SUB_PRODUCTS_TOTAL_COST', $subProductsTotalCost);
		$viewer->assign('SUB_PRODUCTS_COSTS_INFO', $subProductsCostsInfo);
		$viewer->assign('RELATED_FIELDS_INFO', json_encode($fieldsInfo));

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if ($pageCount == 0) {
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		}

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
		$viewer->assign('IS_CREATE_PERMITTED', $relatedModuleModel->isPermitted('CreateView'));

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('SORT_ORDER', $sortOrder);
		$viewer->assign('NEXT_SORT_ORDER', $nextSortOrder);
		$viewer->assign('SORT_IMAGE', $sortImage);
		$viewer->assign('COLUMN_NAME', $orderBy);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		$viewer->assign('TAB_LABEL', $request->get('tab_label'));

		return $viewer->view('RelatedList.tpl', $moduleName, 'true');
	}

}
