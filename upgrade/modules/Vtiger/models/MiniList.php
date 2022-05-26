<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MiniList_Model extends Vtiger_Widget_Model {

	protected $widgetModel;
	protected $extraData;

	protected $listviewController;
	protected $queryGenerator;
	protected $listviewHeaders;
	protected $listviewRecords;
	protected $targetModuleModel;

	public function setWidgetModel($widgetModel) {
		$this->widgetModel = $widgetModel;
		$this->extraData = $this->widgetModel->get('data');

		// Decode data if not done already.
		if (is_string($this->extraData)) {
			$this->extraData = Zend_Json::decode(decode_html($this->extraData));
		}
		if ($this->extraData == NULL) {
			throw new Exception("Invalid data");
		}
	}

	public function getTargetModule() {
		return $this->extraData['module'];
	}

	public function getTargetFields() {
		$fields = $this->extraData['fields'];
		if (!in_array("id", $fields)) $fields[] = "id";
		return $fields;
	}

	public function getTargetModuleModel() {
		if (!$this->targetModuleModel) {
			$this->targetModuleModel = Vtiger_Module_Model::getInstance($this->getTargetModule());
		}
		return $this->targetModuleModel;
	}

	protected function initListViewController() {
		if (!$this->listviewController) {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$db = PearDatabase::getInstance();

			$filterid = $this->widgetModel->get('filterid');
			$this->queryGenerator = new EnhancedQueryGenerator($this->getTargetModule(), $currentUserModel);
			$this->queryGenerator->initForCustomViewById($filterid);
			$this->queryGenerator->setFields( $this->getTargetFields() );

			if (!$this->listviewController) {
				$this->listviewController = new ListViewController($db, $currentUserModel, $this->queryGenerator);
			}

			$this->listviewHeaders = $this->listviewRecords = NULL;
		}
	}

	public function getTitle($prefix='') {
		$this->initListViewController();

		$db = PearDatabase::getInstance();

		$suffix = '';
		$customviewrs = $db->pquery('SELECT viewname FROM vtiger_customview WHERE cvid=?', array($this->widgetModel->get('filterid')));
		if ($db->num_rows($customviewrs)) {
			$customview = $db->fetch_array($customviewrs);
			$suffix = ' - ' . $customview['viewname'];
		}
		return $prefix . vtranslate($this->getTargetModuleModel()->label, $this->getTargetModule()). $suffix;
	}

	public function getHeaders() {
		$this->initListViewController();

		if (!$this->listviewHeaders) {
			$headerFieldModels = array();
			foreach ($this->listviewController->getListViewHeaderFields() as $fieldName => $webserviceField) {
				$fieldObj = Vtiger_Field::getInstance($webserviceField->getFieldId());
				$headerFieldModels[$fieldName] = Vtiger_Field_Model::getInstanceFromFieldObject($fieldObj);
			}
			$this->listviewHeaders = $headerFieldModels;
		}
		return $this->listviewHeaders;
	}

	public function getHeaderCount() {
		if($this->listviewHeaders) return count($this->listviewHeaders);
		return count($this->getHeaders());
	}

	public function getRecordLimit() {
		$pageLimit = vglobal('list_max_entries_per_page');
        if(empty($pageLimit)) {
            $pageLimit = 10;
        }
        return $pageLimit;
	}
    
    function getStartIndex() {
        $nextPage = $this->get('nextPage');
        $startIndex = (($nextPage - 1) * $this->getRecordLimit());
        return $startIndex;
    }

	public function getRecords() {

		$this->initListViewController();

		if (!$this->listviewRecords) {
			$db = PearDatabase::getInstance();

			$query = $this->queryGenerator->getQuery();
			$query .= ' ORDER BY vtiger_crmentity.modifiedtime DESC';
			$query .= ' LIMIT ' . $this->getStartIndex() . ',' . $this->getRecordLimit();
			$query = str_replace(" FROM ", ",vtiger_crmentity.crmid as id FROM ", $query);
            if($this->getTargetModule() == 'Calendar') {
                $query = str_replace(" WHERE ", " WHERE vtiger_crmentity.setype = 'Calendar' AND ", $query);
            }

			$result = $db->pquery($query, array());

			$targetModuleName = $this->getTargetModule();
			$targetModuleFocus= CRMEntity::getInstance($targetModuleName);

			$entries = $this->listviewController->getListViewRecords($targetModuleFocus,$targetModuleName,$result);

			$this->listviewRecords = array();
			$index = 0;
			foreach ($entries as $id => $record) {
				$rawData = $db->query_result_rowdata($result, $index++);
				$record['id'] = $id;
				$this->listviewRecords[$id] = $this->getTargetModuleModel()->getRecordFromArray($record, $rawData);
			}
		}

		return $this->listviewRecords;
	}
    
    function moreRecordExists() {
        $this->initListViewController();
        $db = PearDatabase::getInstance();
        $query = $this->queryGenerator->getQuery();
        
        $startIndex = $this->getStartIndex() + $this->getRecordLimit();
        $query .= ' LIMIT ' . $startIndex . ',' . $this->getRecordLimit();
        if($this->getTargetModule() == 'Calendar') {
            $query = str_replace(" WHERE ", " WHERE vtiger_crmentity.setype = 'Calendar' AND ", $query);
        }
        
        $result = $db->pquery($query, array());
        if($db->num_rows($result) > 0) {
            return true;
        }
        return false;
    }
}