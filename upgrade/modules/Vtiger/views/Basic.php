<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Contains a variety of utility functions used to display UI
 * components such as top level menus,more menus,header links,crm logo,global search
 * and quick links of header part
 * footer is also loaded
 * function that connect to db connector to get data
 ********************************************************************************/

abstract class Vtiger_Basic_View extends Vtiger_Footer_View {

	function __construct() {
		parent::__construct();
	}

	function preProcess (Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);

		$viewer = $this->getViewer($request);

		$menuModelsList = Vtiger_Menu_Model::getAll(true);
		$selectedModule = $request->getModule();
		$menuStructure = Vtiger_MenuStructure_Model::getInstanceFromMenuList($menuModelsList, $selectedModule);

		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();
		$currentDate  = Vtiger_Date_UIType::getDisplayDateValue(date('Y-n-j'));
		$viewer->assign('CURRENTDATE', $currentDate);
		$viewer->assign('MODULE', $selectedModule);
		$viewer->assign('MODULE_NAME', $selectedModule);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('PARENT_MODULE', $request->get('parent'));
		$viewer->assign('VIEW', $request->get('view'));

		// Order by pre-defined automation process for QuickCreate.
		uksort($menuModelsList, array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));

		$selectedModuleMenuCategory = 'MARKETING';
		$moduleFound = false;

		$menuGroupedByParent = Settings_MenuEditor_Module_Model::getAllVisibleModules();
		$supportGroup = $menuGroupedByParent['SUPPORT'];
		unset($menuGroupedByParent['SUPPORT']);
		$menuGroupedByParent['SUPPORT'] = $supportGroup;

		foreach ($menuGroupedByParent as $parentCategory => $menuList) {
			if($parentCategory == 'ANALYTICS' || $parentCategory == 'SETTINGS') continue;
			if(count($menuList) > 0) {
				if(array_key_exists($selectedModule, $menuList) && $parentCategory) {
					$moduleFound = true;
					$selectedModuleMenuCategory = $parentCategory;
				}
			}
		}

		$requestAppName = $request->get('app');
		if(!empty($requestAppName)) {
			$selectedModuleMenuCategory = $requestAppName;
		}

		//If module is not found in any category we need to show the module itself 
		//Eg : Home->DashBoard view we ned to show Home 
		if($moduleFound) {
			$selectedMenuCategoryLabel = vtranslate('LBL_'.$selectedModuleMenuCategory, $selectedModule);
		}else{
			$selectedMenuCategoryLabel = vtranslate($selectedModule, $selectedModule);
		}

		$viewer->assign('SELECTED_MENU_CATEGORY',$selectedModuleMenuCategory);
		$viewer->assign('SELECTED_MENU_CATEGORY_LABEL', $selectedMenuCategoryLabel);
		$viewer->assign('SELECTED_CATEGORY_MENU_LIST',$menuGroupedByParent[$selectedModuleMenuCategory]);
		$viewer->assign('MENUS', $menuModelsList);
		$viewer->assign('QUICK_CREATE_MODULES', Vtiger_Menu_Model::getAllForQuickCreate());
		$viewer->assign('MENU_STRUCTURE', $menuStructure);
		$viewer->assign('MENU_SELECTED_MODULENAME', $selectedModule);
		$viewer->assign('MENU_TOPITEMS_LIMIT', $menuStructure->getLimit());
		$viewer->assign('COMPANY_LOGO',$companyLogo);
		$viewer->assign('COMPANY_DETAILS_SETTINGS',new Settings_Vtiger_CompanyDetails_Model());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$homeModuleModel = Vtiger_Module_Model::getInstance('Home');
		$viewer->assign('HOME_MODULE_MODEL', $homeModuleModel);
		$viewer->assign('HEADER_LINKS',$this->getHeaderLinks());
		$viewer->assign('ANNOUNCEMENT', $this->getAnnouncement());
		$viewer->assign('SEARCHABLE_MODULES', Vtiger_Module_Model::getSearchableModules());

		$inventoryModules = getInventoryModules();
		$viewer->assign('INVENTORY_MODULES',  $inventoryModules);
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	protected function preProcessTplName(Vtiger_Request $request) {
		return 'BasicHeader.tpl';
	}

	//Note: To get the right hook for immediate parent in PHP,
	// specially in case of deep hierarchy
	/*function preProcessParentTplName(Vtiger_Request $request) {
		return parent::preProcessTplName($request);
	}*/

	function postProcess(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		//$viewer->assign('GUIDERSJSON', Vtiger_Guider_Model::toJsonList($this->getGuiderModels($request)));
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
			'libraries.bootstrap.js.eternicode-bootstrap-datepicker.js.bootstrap-datepicker',
			'~libraries/bootstrap/js/eternicode-bootstrap-datepicker/js/locales/bootstrap-datepicker.'.Vtiger_Language_Handler::getShortLanguageName().'.js',
			'~layouts/'.Vtiger_Viewer::getDefaultLayoutName().'/lib/jquery/timepicker/jquery.timepicker.min.js',
			"~libraries/jquery/lazyYT/lazyYT.min.js",
			'modules.Vtiger.resources.Header',
			'modules.Vtiger.resources.Edit',
			"modules.$moduleName.resources.Edit",
			'modules.Vtiger.resources.Popup',
			"modules.$moduleName.resources.Popup",
			'modules.Vtiger.resources.Field',
			"modules.$moduleName.resources.Field",
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.$moduleName.resources.validator.FieldValidator",
			'libraries.jquery.jquery_windowmsg',
			'modules.Vtiger.resources.BasicSearch',
			"modules.$moduleName.resources.BasicSearch",
			'modules.Vtiger.resources.AdvanceFilter',
			"modules.$moduleName.resources.AdvanceFilter",
			'modules.Vtiger.resources.SearchAdvanceFilter',
			"modules.$moduleName.resources.SearchAdvanceFilter",
			'modules.Vtiger.resources.AdvanceSearch',
			"modules.$moduleName.resources.AdvanceSearch",
			"modules.Vtiger.resources.BaseList",
			"modules.$moduleName.resources.BaseList",
			"modules.Vtiger.resources.List",
			"modules.$moduleName.resources.AdvanceSearchList",
			"modules.Vtiger.resources.AdvanceSearchList",
			"modules.Vtiger.resources.RecordSelectTracker",
			"modules.Vtiger.resources.Pagination",
			"~layouts/v7/modules/Import/resources/Popup.js",
			"modules.Emails.resources.MassEdit",
			'modules.Vtiger.resources.EmailsRelatedPopup',
			"~layouts/v7/lib/jquery/sadropdown.js",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsScriptInstances,$headerScriptInstances);
		return $headerScriptInstances;
	}

	function getGuiderModels(Vtiger_Request $request) {
		return array();
	}

	public function validateRequest(Vtiger_Request $request) {
		//Removed validation check for specific views
		$allowedViews = array("List", "Index", "Detail", "PreferenceDetail", "ExtensionStore", "CompanyDetails", "TaxIndex", "OutgoingServerDetail",
								"TermsAndConditionsEdit", "AnnouncementEdit", "CustomRecordNumbering", "ConfigEditorDetail", "ChartDetail");
		$view = $request->get("view");
		$mode = $request->get("mode");
		if (!(in_array($view, $allowedViews) || ($view == "Import" && !$mode) || ($view == "Edit" && $request->get("module") == "Workflows" && !$mode))) {
			$request->validateReadAccess();
		}
	}
}
