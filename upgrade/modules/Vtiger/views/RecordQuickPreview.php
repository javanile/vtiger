<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

class Vtiger_RecordQuickPreview_View extends Vtiger_Index_View {

	protected $record = false;

	function __construct() {
		parent::__construct();
	}

	function process(Vtiger_Request $request) {

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$recordId = $request->get('record');

		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		if ($request->get('navigation') == 'true') {
			$this->assignNavigationRecordIds($viewer, $recordId);
		}

		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		$moduleModel = $recordModel->getModule();

		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('$SOCIAL_ENABLED', false);
		$viewer->assign('LIST_PREVIEW', true);
		$appName = $request->get('app');
		$viewer->assign('SELECTED_MENU_CATEGORY', $appName);
		$pageNumber = 1;
		$limit = 5;

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('limit', $limit);

		if ($moduleModel->isCommentEnabled()) {
			//Show Top 5
			$recentComments = ModComments_Record_Model::getRecentComments($recordId, $pagingModel);
			$viewer->assign('COMMENTS', $recentComments);
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$viewer->assign('CURRENTUSER', $currentUserModel);
		}

		$viewer->assign('SHOW_ENGAGEMENTS', 'false');
		$recentActivities = ModTracker_Record_Model::getUpdates($recordId, $pagingModel, $moduleName);
		//To show more button for updates if there are more than 5 records
		if (count($recentActivities) >= 5) {
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('RECENT_ACTIVITIES', $recentActivities);
		$viewer->view('ListViewQuickPreview.tpl', $moduleName);
	}

	public function assignNavigationRecordIds($viewer, $recordId) {
		//Navigation to next and previous records.
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);
		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach ($navigationInfo as $page => $pageInfo) {
				foreach ($pageInfo as $index => $record) {
					//If record found then next record in the interation
					//will be next record
					if ($found) {
						$nextRecordId = $record;
						break;
					}
					if ($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if (!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if ($found && !empty($nextRecordId)) {
					break;
				}
			}
		}
		$viewer->assign('PREVIOUS_RECORD_ID', $prevRecordId);
		$viewer->assign('NEXT_RECORD_ID', $nextRecordId);
		$viewer->assign('NAVIGATION', true);
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateReadAccess();
	}

}
