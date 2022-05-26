<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $linkTypes, $linkParams);

		$basicLinks = $this->getBasicLinks();
		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}
        
        $links['LISTVIEW'] = array();
        $advancedLinks = $this->getAdvancedLinks();
		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
		}
        
        $usersList = Users_Record_Model::getActiveAdminUsers();
        $settingLinks = array();
        if(count($usersList) ) {
            $changeOwnerLink = array(
                'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_CHANGE_OWNER',
				'linkurl' => $this->getModule()->getChangeOwnerUrl(),
				'linkicon' => ''
            );
            array_push($settingLinks, $changeOwnerLink);
        }

		$settingLinks = array_merge($settingLinks, $this->getSettingLinks());
        if(count($settingLinks) > 0) {
            foreach($settingLinks as $settingLink) {
                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingLink);
            }
        }

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		return array();
	}

	/**
	 * Functions returns the query
	 * @return string
	 */
    public function getQuery() {
            $listQuery = parent::getQuery();
            $searchKey = $this->get('search_key');
            
            if(!empty($searchKey)) {
                $listQueryComponents = explode(" WHERE vtiger_users.status='Active' AND", $listQuery);
                $listQuery = implode(' WHERE ', $listQueryComponents);
            }
            $listQuery .= " AND (vtiger_users.user_name != 'admin' OR vtiger_users.is_owner = 1)";
            return $listQuery;
    }

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel, $status (Active or Inactive User). Default false
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$queryGenerator = $this->get('query_generator');
                
		// Added as Users module do not have custom filters and id column is added by querygenerator.
		$fields = $queryGenerator->getFields();
		$fields[] = 'id';
		$queryGenerator->setFields($fields);
		
		return parent::getListViewEntries($pagingModel);
	}
        
    /*
	 * Function to give advance links of Users module
	 * @return array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if($importPermission && $createPermission) {
			$advancedLinks[] = array(
                'linktype' => 'LISTVIEW',
                'linklabel' => 'LBL_IMPORT',
                'linkurl' => $moduleModel->getImportUrl(),
                'linkicon' => ''
			);
		}

		return $advancedLinks;
	}
}
