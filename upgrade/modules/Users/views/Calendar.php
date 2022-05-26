<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class Users_Calendar_View extends Vtiger_Detail_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('calendarSettingsEdit');
		$this->exposeMethod('calendarSettingsDetail');
	}
	
	
	public function checkPermission(Vtiger_Request $request) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$record = $request->get('record');

		if($currentUserModel->isAdminUser() == true || $currentUserModel->get('id') == $record) {
			return true;
		} else {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	/**
	 * Function to returns the preProcess Template Name
	 * @param <type> $request
	 * @return <String>
	 */
	public function preProcessTplName(Vtiger_Request $request) {
		return 'CalendarDetailViewPreProcess.tpl';
	}

	public function preProcess(Vtiger_Request $request, $display=true) {
		if($this->checkPermission($request)) {
			$qualifiedModuleName = $request->getModule(false);
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$recordId = $request->get('record');
			$moduleName = $request->getModule();
			$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			$recordModel = $detailViewModel->getRecord();

			$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
			$detailViewLinks = $detailViewModel->getDetailViewLinks($detailViewLinkParams);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);

			$viewer->assign('MODULE_MODEL', $detailViewModel->getModule());
			$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
			$viewer->assign('MODULE_BASIC_ACTIONS', array());

			$viewer->assign('IS_EDITABLE', $detailViewModel->getRecord()->isEditable($moduleName));
			$viewer->assign('IS_DELETABLE', $detailViewModel->getRecord()->isDeletable($moduleName));

			$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
			$linkModels = $detailViewModel->getSideBarLinks($linkParams);
			$viewer->assign('QUICK_LINKS', $linkModels);
			$viewer->assign('PAGETITLE', $this->getPageTitle($request));
			$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
			$viewer->assign('STYLES',$this->getHeaderCss($request));
			$viewer->assign('LANGUAGE_STRINGS', $this->getJSLanguageStrings($request));
			$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());

			$menuModelsList = Vtiger_Menu_Model::getAll(true);
			$selectedModule = $request->getModule();
			$menuStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($menuModelsList, $selectedModule);

			// Order by pre-defined automation process for QuickCreate.
			uksort($menuModelsList, array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));

			$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
			$companyLogo = $companyDetails->getLogo();

			$viewer->assign('CURRENTDATE', date('Y-n-j'));
			$viewer->assign('MODULE', $selectedModule);
			$viewer->assign('PARENT_MODULE', $request->get('parent'));
            $viewer->assign('VIEW', $request->get('view'));
			$viewer->assign('MENUS', $menuModelsList);
            $viewer->assign('QUICK_CREATE_MODULES', Vtiger_Menu_Model::getAllForQuickCreate());
			$viewer->assign('MENU_STRUCTURE', $menuStructure);
			$viewer->assign('MENU_SELECTED_MODULENAME', $selectedModule);
			$viewer->assign('MENU_TOPITEMS_LIMIT', $menuStructure->getLimit());
			$viewer->assign('COMPANY_LOGO',$companyLogo);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

			$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
			$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
			$viewer->assign('HEADER_LINKS',$this->getHeaderLinks());
			$viewer->assign('ANNOUNCEMENT', $this->getAnnouncement());
			$viewer->assign('CURRENT_VIEW', $request->get('view'));
			$viewer->assign('SKIN_PATH', Vtiger_Theme::getCurrentUserThemePath());
			$viewer->assign('LANGUAGE', $currentUser->get('language'));
			$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
			$viewer->assign('SELECTED_MENU_CATEGORY', 'MARKETING');
			$settingsModel = Settings_Vtiger_Module_Model::getInstance();
			$menuModels = $settingsModel->getMenus();

			if(!empty($selectedMenuId)) {
				$selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($selectedMenuId);
			} elseif(!empty($moduleName) && $moduleName != 'Vtiger') {
				$fieldItem = Settings_Vtiger_Index_View::getSelectedFieldFromModule($menuModels,$moduleName);
				if($fieldItem){
					$selectedMenu = Settings_Vtiger_Menu_Model::getInstanceById($fieldItem->get('blockid'));
					$fieldId = $fieldItem->get('fieldid');
				} else {
					reset($menuModels);
					$firstKey = key($menuModels);
					$selectedMenu = $menuModels[$firstKey];
				}
			} else {
				reset($menuModels);
				$firstKey = key($menuModels);
				$selectedMenu = $menuModels[$firstKey];
			}

			$settingsMenItems = array();
			foreach($menuModels as $menuModel) {
				$menuItems = $menuModel->getMenuItems();
				foreach($menuItems as $menuItem) {
					$settingsMenItems[$menuItem->get('name')] = $menuItem;
				}
			}
			$viewer->assign('SETTINGS_MENU_ITEMS', $settingsMenItems);

			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

			$moduleFields = $moduleModel->getFields();
			foreach($moduleFields as $fieldName => $fieldModel){
				$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
			}
			$eventsModuleModel = Vtiger_Module_Model::getInstance('Events');
			$eventFields = array('defaulteventstatus' => 'eventstatus', 'defaultactivitytype' => 'activitytype');
			foreach($eventFields as $userField => $eventField) {
				$fieldsInfo[$userField]['picklistvalues'] = $eventsModuleModel->getField($eventField)->getPicklistValues();
			}
			$viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));

			$activeBLock = Settings_Vtiger_Module_Model::getActiveBlockName($request);
			$viewer->assign('ACTIVE_BLOCK', $activeBLock);

			if($display) {
				$this->preProcessDisplay($request);
			}
		}
	}

	protected function preProcessDisplay(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view($this->preProcessTplName($request), $request->getModule());
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if($mode == 'Edit'){
			$this->invokeExposedMethod('calendarSettingsEdit',$request);
		} else {
			$this->invokeExposedMethod('calendarSettingsDetail',$request);
		}
	}
	
	public function initializeView($viewer,Vtiger_Request $request){
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$module = $request->getModule();
		$detailViewModel = Vtiger_DetailView_Model::getInstance('Users', $currentUserModel->id);
		$userRecordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($detailViewModel->getRecord(), Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $userRecordStructure->getStructure();
//		$allUsers = Users_Record_Model::getAll(true);
		$sharedUsers = Calendar_Module_Model::getCaledarSharedUsers($currentUserModel->id);
		$sharedType = Calendar_Module_Model::getSharedType($currentUserModel->id);
		$dayStartPicklistValues = Users_Record_Model::getDayStartsPicklistValues($recordStructure);
        $hourFormatFeildModel = $recordStructure['LBL_CALENDAR_SETTINGS']['hour_format'];
		$calendarSettings['LBL_CALENDAR_SETTINGS'] = $recordStructure['LBL_CALENDAR_SETTINGS'];
		$recordModel = $detailViewModel->getRecord();
		$moduleModel = $recordModel->getModule();
		$viewer->assign('IS_AJAX_ENABLED', $recordModel->isEditable());
		$blocksList = $moduleModel->getBlocks();
		$viewer->assign('CURRENTUSER_MODEL',$currentUserModel);
		$viewer->assign('BLOCK_LIST',$blocksList);
		$viewer->assign('SHAREDUSERS', $sharedUsers);
		$viewer->assign("DAY_STARTS", Zend_Json::encode($dayStartPicklistValues));
//		$viewer->assign('ALL_USERS',$allUsers);
		$viewer->assign('RECORD_STRUCTURE',$calendarSettings);
		$viewer->assign('MODULE',$module);
		$viewer->assign('MODULE_NAME',$module);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_ID', $recordId);
		
		$viewer->assign('SHAREDTYPE', $sharedType);
        $viewer->assign('HOUR_FORMAT_VALUE', $hourFormatFeildModel->get('fieldvalue'));
	}
	
	
	public function calendarSettingsEdit(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$this->initializeView($viewer,$request);
		$viewer->view('CalendarSettingsEditView.tpl', $request->getModule());
	}
	
	
	
	public function calendarSettingsDetail(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$this->initializeView($viewer,$request);
		$viewer->view('CalendarSettingsDetailView.tpl', $request->getModule());
	}

    public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
        $moduleDetailFile = 'modules.'.$moduleName.'.resources.PreferenceDetail';
        unset($headerScriptInstances[$moduleDetailFile]);

		$jsFileNames = array(
            "modules.Users.resources.Detail",
			"modules.Users.resources.Users",
            'modules.'.$moduleName.'.resources.PreferenceDetail',
			'modules.'.$moduleName.'.resources.Calendar',
			'modules.'.$moduleName.'.resources.PreferenceEdit',
             'modules.Settings.Vtiger.resources.Index',
			"~layouts/v7/lib/jquery/Lightweight-jQuery-In-page-Filtering-Plugin-instaFilta/instafilta.min.js"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * HTTP REFERER check was removed in Parent class Vtiger_Detail_View, because of 
	 * CRM Detail View URL option in Workflow Send Mail task.
	 * But here http referer check is required.
	 */
	public function validateRequest(Vtiger_Request $request) {
		$request->validateReadAccess();
	}

}
