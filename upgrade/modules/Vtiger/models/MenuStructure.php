<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger MenuStructure Model
 */
class Vtiger_MenuStructure_Model extends Vtiger_Base_Model {

	protected $limit = 5; // Max. limit of persistent top-menu items to display.
	protected $enableResponsiveMode = true; // Should the top-menu items be responsive (width) on UI?

	const TOP_MENU_INDEX = 'top';
	const MORE_MENU_INDEX = 'more';

	protected $menuGroupByParent = array();

	/**
	 * Function to get all the top menu models
	 * @return <array> - list of Vtiger_Menu_Model instances
	 */
	public function getTop() {
		return $this->get(self::TOP_MENU_INDEX);
	}

	/**
	 * Function to get all the more menu models
	 * @return <array> - Associate array of Parent name mapped to Vtiger_Menu_Model instances
	 */
	public function getMore() {
		$moreTabs = $this->get(self::MORE_MENU_INDEX); 
		foreach($moreTabs as $key=>$value){ 
			if(!$value){ 
						unset($moreTabs[$key]); 
				} 
		} 
		return $moreTabs;
	}

	/**
	 * Function to get the limit for the number of menu models on the Top list
	 * @return <Number>
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * Function to determine if the structure should support responsive UI.
	 */
	public function getResponsiveMode() {
		return $this->enableResponsiveMode;
	}

	public function getMenuGroupedByParent() {
		return $this->menuGroupByParent;
	}

	public function setMenuGroupedByParent($structure) {
		$this->menuGroupByParent = $structure;
		return $this;
	}

	/**
	 * Function to get an instance of the Vtiger MenuStructure Model from list of menu models
	 * @param <array> $menuModelList - array of Vtiger_Menu_Model instances
	 * @return Vtiger_MenuStructure_Model instance
	 */
	public static function getInstanceFromMenuList($menuModelList, $selectedMenu='') {
		$structureModel = new self();
		$topMenuLimit = $structureModel->getResponsiveMode() ? 0 : $structureModel->getLimit();
		$currentTopMenuCount = 0;
		$menuGroupedListByParent = array();
		$menuListArray = array();
		$menuListArray[self::TOP_MENU_INDEX] = array();
		$menuListArray[self::MORE_MENU_INDEX] = $structureModel->getEmptyMoreMenuList();

		foreach($menuModelList as $menuModel) {
			if(($menuModel->get('tabsequence') != -1 && (!$topMenuLimit || $currentTopMenuCount < $topMenuLimit)) ) {
				$menuListArray[self::TOP_MENU_INDEX][$menuModel->get('name')] = $menuModel;
				$currentTopMenuCount++;
			}

			$parent = ucfirst(strtolower($menuModel->get('parent')));
			if($parent == 'Sales' || $parent == 'Marketing'){
				$parent = 'MARKETING_AND_SALES';
			}
			$menuListArray[self::MORE_MENU_INDEX][strtoupper($parent)][$menuModel->get('name')] = $menuModel;
			$menuGroupedListByParent[strtoupper($parent)][$menuModel->get('name')] = $menuModel;
		}

		if(!empty($selectedMenu) && !array_key_exists($selectedMenu, $menuListArray[self::TOP_MENU_INDEX])) {
			$selectedMenuModel = $menuModelList[$selectedMenu];
			if($selectedMenuModel) {
				$menuListArray[self::TOP_MENU_INDEX][$selectedMenuModel->get('name')] = $selectedMenuModel;
			}
		}

		// Apply custom comparator
		foreach ($menuListArray[self::MORE_MENU_INDEX] as $parent => &$values) {
			uksort($values, array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));
		}
		//uksort($menuListArray[self::TOP_MENU_INDEX], array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));

		return $structureModel->setData($menuListArray)->setMenuGroupedByParent($menuGroupedListByParent);
	}

	/**
	 * Custom comparator to sort the menu items by process.
	 * Refer: http://php.net/manual/en/function.uksort.php
	 */
	static function sortMenuItemsByProcess($a, $b) {
		static $order = NULL;
		if ($order == NULL) {
			$order = array(
				'Campaigns',
				'Leads',
				'Contacts',
				'Accounts',
				'Potentials',
				'Quotes',
				'Invoice',
				'SalesOrder',
				'HelpDesk',
				'Faq',
				'Project',
				'Assets',
				'ServiceContracts',
				'Products',
				'Services',
				'PriceBooks',
				'Vendors',
				'PurchaseOrder',
				'MailManager',
				'Calendar',
				'Documents',
				'SMSNotifier',
				'RecycleBin',
				'ProjectTask',
				'ProjectMilestone'
			);
		}
		$apos  = array_search($a, $order);
		$bpos  = array_search($b, $order);

		if ($apos === false) return PHP_INT_MAX;
		if ($bpos === false) return -1*PHP_INT_MAX;

		return ($apos - $bpos);
	}


	private function getEmptyMoreMenuList(){
		return array('CONTACT'=>array(), 'MARKETING_AND_SALES'=>array(),'SUPPORT'=>array(),'INVENTORY'=>array(),'TOOLS'=>array(),'ANALYTICS'=>array());
	}

	public static function getIgnoredModules() {
		return array('Calendar', 'Documents', 'MailManager', 'SMSNotifier', 'Reports');
	}

	function regroupMenuByParent($menuGroupedByParent) {
		$editionsToAppMap = array(
									'Contacts'		=> array('MARKETING', 'SALES', 'INVENTORY', 'SUPPORT', 'PROJECT'),
									'Accounts'		=> array('MARKETING', 'SALES', 'INVENTORY', 'SUPPORT', 'PROJECT'),
									'Campaigns'		=> array('MARKETING'),
									'Leads'			=> array('MARKETING'),
									'Potentials'	=> array('SALES'),
									'Quotes'		=> array('SALES'),
									'Invoice'		=> array('INVENTORY'),
									'HelpDesk'		=> array('SUPPORT'),
									'Faq'			=> array('SUPPORT'),
									'Assets'		=> array('SUPPORT'),
									'Products'		=> array('SALES', 'INVENTORY'),
									'Services'		=> array('SALES', 'INVENTORY'),
									'Pricebooks'	=> array('INVENTORY'),
									'Vendors'		=> array('INVENTORY'),
									'PurchaseOrder'	=> array('INVENTORY'),
									'SalesOrder'	=> array('INVENTORY'),
									'Project'		=> array('PROJECT'),
									'ProjectTask'	=> array('PROJECT'),
									'ProjectMilestone'	=> array('PROJECT'),
									'ServiceContracts'	=> array('SUPPORT'),
									'EmailTemplates'=> array('TOOLS'),
									'Rss'			=> array('TOOLS'),
									'Portal'		=> array('TOOLS'),
									'RecycleBin'	=> array('TOOLS'),
							);

		$oldToNewAppMap = Vtiger_MenuStructure_Model::getOldToNewAppMapping();
		$ignoredModules = self::getIgnoredModules();
		$regroupMenuByParent = array();
		foreach($menuGroupedByParent as $appName => $appModules) {
			foreach ($appModules as $moduleName => $moduleModel) {
				if(!empty($editionsToAppMap[$moduleName])) {
					foreach ($editionsToAppMap[$moduleName] as $app) {
						$regroupMenuByParent[$app][$moduleName] = $moduleModel;
					}
				} else {
					if(!in_array($moduleName, $ignoredModules)) {
						$app = $oldToNewAppMap[$appName];
						$regroupMenuByParent[$app][$moduleName] = $moduleModel;
					}                
				}
			}
		}

		return $regroupMenuByParent;
	}

	public static function getOldToNewAppMapping() {
		$oldToNewAppMap = array(
							'CONTACT'				=> 'SALES',
							'MARKETING_AND_SALES'	=> 'MARKETING',
							'INVENTORY'				=> 'INVENTORY',
							'SUPPORT'				=> 'SUPPORT',
							'PROJECT'				=> 'PROJECT',
							'TOOLS'					=> 'TOOLS'
						  );
		return $oldToNewAppMap;
	}

	/**
	 * Function to get the app menu items in order
	 * @return <array>
	 */
	public static function getAppMenuList(){
		return array('MARKETING','SALES','INVENTORY','SUPPORT','PROJECT','TOOLS');
	}

	public static function getAppIcons() {
		$appImageIcons = array(	'MARKETING' => 'fa-users',
								'SALES'		=> 'fa-dot-circle-o',
								'SUPPORT'	=> 'fa-life-ring',
								'INVENTORY'	=> 'vicon-inventory',
								'PROJECT'	=> 'fa-briefcase',
								'TOOLS'		=> 'fa-wrench'
							);
		return $appImageIcons;
	}
}
