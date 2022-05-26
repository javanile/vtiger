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
 * Vtiger ListView Model Class
 */
class Calendar_ListView_Model extends Vtiger_ListView_Model {

	public function getBasicLinks() {
		$basicLinks = array();
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_TASK',
					'linkurl' => $this->getModule()->getCreateTaskRecordUrl(),
					'linkicon' => ''
			);

			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_EVENT',
					'linkurl' => $this->getModule()->getCreateEventRecordUrl(),
					'linkicon' => ''
			);
		}
		return $basicLinks;
	}


	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'CreateView');
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

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("'.$moduleModel->getExportUrl().'")',
					'linkicon' => ''
				);
		}
		return $advancedLinks;
	}

	/**
	 * Function to get query to get List of records in the current page
	 * @return <String> query
	 */
	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		// Added to remove emails from the calendar list
		$queryGenerator->addCondition('activitytype','Emails','n','AND');

		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}


	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		$massActionLinks = array();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_CHANGE_OWNER',
				'linkurl' => 'javascript:Calendar_List_Js.triggerMassEdit("index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module='.$moduleModel->get('name').'&action=MassDelete");',
				'linkicon' => ''
			);
		}

		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}
    
    /**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
        $listViewContoller = $this->get('listview_controller');
        $module = $this->getModule();
        $moduleName = $module->get('name');
		$headerFieldModels = array();
		$headerFields = $listViewContoller->getListViewHeaderFields();
		foreach($headerFields as $fieldName => $webserviceField) {
			if($webserviceField && !in_array($webserviceField->getPresence(), array(0,2))) continue;
			if($webserviceField && $webserviceField->parentReferenceField && !in_array($webserviceField->parentReferenceField->getPresence(), array(0,2))){
				continue;
			}
            //to eliminate starred field to be shown in the list view 
            if($webserviceField->getDisplayType() == '6') continue;
			// check if the field is reference field
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
			if(count($matches) > 0) {
				list($full, $referenceParentField, $referenceModule, $referenceFieldName) = $matches;
				$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
				$referenceFieldModel = Vtiger_Field_Model::getInstance($referenceFieldName, $referenceModuleModel);
                // added tp use in list view to see the title, for reference field rawdata key is different than the actual field
                // eg: in rawdata its account_idcf_2342 (raw column name used in querygenerator), actual field name (account_id ;(Accounts) cf_2342)
                // When generating the title we use rawdata and from field model we have no way to find querygenrator raw column name.
                $referenceFieldModel->set('listViewRawFieldName', $referenceParentField.$referenceFieldName);

                // this is added for picklist colorizer (picklistColorMap.tpl), for fetching picklist colors we need the actual field name of the picklist
                $referenceFieldModel->set('_name', $referenceFieldName);
				$headerFieldModels[$fieldName] = $referenceFieldModel->set('name', $fieldName); // resetting the fieldname as we use it to fetch the value from that name
				$matches=null;
			} else {
				$fieldInstance = Vtiger_Field_Model::getInstance($fieldName,$module);
				if(!$fieldInstance) {
					if($moduleName == 'Calendar') {
						$eventmodule = Vtiger_Module_Model::getInstance('Events');
						$fieldInstance = Vtiger_Field_Model::getInstance($fieldName,$eventmodule);
					}
				}
                $fieldInstance->set('listViewRawFieldName', $fieldInstance->get('column'));
				$headerFieldModels[$fieldName] = $fieldInstance;
			}
		}
		return $headerFieldModels;
	}
    
    /**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		require('user_privileges/user_privileges_'.$currentUser->id.'.php');
		require('user_privileges/sharing_privileges_'.$currentUser->id.'.php');
		
		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');
		$listViewFields = array('visibility','assigned_user_id');
		$queryGenerator->setFields(array_unique(array_merge($queryGenerator->getFields(), $listViewFields)));
		
        $searchParams = $this->get('search_params');
        if(empty($searchParams)) {
            $searchParams = array();
        }
        
        $glue = "";
        if(count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
            $glue = QueryGenerator::$AND;
        }
        $queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}
        
        $orderBy = $this->get('orderby');
		$sortOrder = $this->get('sortorder');
        if(empty($sortOrder)) {
            $sortOrder = 'DESC';
        }

        if(!empty($orderBy)){
			$queryGenerator = $this->get('query_generator');
			$fieldModels = $queryGenerator->getModuleFields();
			$orderByFieldModel = $fieldModels[$orderBy];
           if($orderByFieldModel && ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE ||
					$orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::OWNER_TYPE)){
                $queryGenerator->addWhereField($orderBy);
            }
        }
		
		$listQuery = $this->getQuery();

		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		// If activity is related to two entity records(Contacts/Accounts/...) then we'll get duplicates
        $listQuery .= " GROUP BY $moduleFocus->table_name.$moduleFocus->table_index ";

		
		if($orderBy == 'date_start' || empty($orderBy)) {
            $listQuery .= " ORDER BY str_to_date(concat(date_start,time_start),'%Y-%m-%d %H:%i:%s') $sortOrder ";
        } else if($orderBy == 'due_date') {
            $listQuery .= " ORDER BY str_to_date(concat(due_date,time_end),'%Y-%m-%d %H:%i:%s') $sortOrder ";
        } else if(!empty($orderBy) && $orderByFieldModel) {
			$listQuery .= ' ORDER BY '.$queryGenerator->getOrderByColumn($orderBy).' '.$sortOrder;
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$viewid = ListViewSession::getCurrentView($moduleName);
        if(empty($viewid)){
            $viewid = $pagingModel->get('viewid');
        }
        $_SESSION['lvs'][$moduleName][$viewid]['start'] = $pagingModel->get('page');
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQueryWithNoLimit = $listQuery;
		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);
		
		$listResult = $db->pquery($listQuery, array());
		$listViewRecordModels = array();
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus,$moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}
		
		$groupsIds = Vtiger_Util_Helper::getGroupsIdsForUsers($currentUser->getId());
		$index = 0;
		$recordsToUnset = array();
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$visibleFields = array('activitytype','date_start','due_date','assigned_user_id','visibility','smownerid');
			$ownerId = $rawData['smownerid'];
			$visibility = true;
			if(in_array($ownerId, $groupsIds)) {
				$visibility = false;
			} else if($ownerId == $currentUser->getId()){
				$visibility = false;
			}

			// if the user is having view all permission then it should show the record
			// as we are showing in detail view
			if($profileGlobalPermission[1] ==0 || $profileGlobalPermission[2] ==0) {
				$visibility = false;
			}

			if(!$currentUser->isAdminUser() && $rawData['activitytype'] != 'Task' && $rawData['visibility'] == 'Private' && $ownerId && $visibility) {
				foreach($record as $data => $value) {
					if(in_array($data, $visibleFields) != -1) {
						unset($rawData[$data]);
						unset($record[$data]);
					}
				}
				$record['subject'] = vtranslate('Busy','Events').'*';
			}
			if($record['activitytype'] == 'Task') {
				unset($record['visibility']);
				unset($rawData['visibility']);
			}
			
			$record['id'] = $recordId;
            $listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
            if(!$currentUser->isAdminUser() && $rawData['activitytype'] == 'Task' && isToDoPermittedBySharing($recordId) == 'no') {
				$recordsToUnset[] = $recordId;
			}
		}
		//setting list view count before unsetting permission denied records - to make sure paging should not fail
		$pagingModel->set('_listcount', count($listViewRecordModels));
		foreach($recordsToUnset as $record) {
			unset($listViewRecordModels[$record]);
		}
		return $listViewRecordModels;
	}
}
