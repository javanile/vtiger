<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

require_once 'include/events/VTEventHandler.inc';

class CheckDuplicateHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($eventName == 'vtiger.entity.beforesave') {
			$this->triggerCheckDuplicateHandler($entityData);
		} else if ($eventName == 'vtiger.entity.beforerestore') {
			$this->triggerCheckDuplicateHandler($entityData);
		}
	}

	public function triggerCheckDuplicateHandler($entityData) {
		global $skipDuplicateCheck;
		$fieldValues = $entityData->getData();

		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'Activity') {
			$moduleName = ($fieldValues['activitytype'] == 'Task') ? 'Calendar' : 'Events';
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleModel->allowDuplicates && !$skipDuplicateCheck) {
			$fields = $moduleModel->getFields();

			if ($moduleName == 'Events') {
				$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
			}

			$baseTableName = $moduleModel->get('basetable');
			$baseTableId = $moduleModel->get('basetableid');
			$crmentityTable = 'vtiger_crmentity';
			$tabIndexes = $entityData->focus->tab_name_index;

			$uniqueFields = array();
			$tablesList = array();
			foreach ($fields as $fieldName => $fieldModel) {
				if ($fieldModel->isUniqueField() && $fieldModel->isEditable()) {
					$uniqueFields[$fieldName] = $fieldModel;

					if (in_array($moduleName, array('Events', 'Calendar')) && in_array($fieldName, array('date_start', 'due_date'))) {
						$timeField = 'time_start';
						if ($fieldName === 'due_date') {
							$timeField = 'time_end';
						}
						$uniqueFields[$timeField] = $fields[$timeField];
					}

					$fieldTableName = $fieldModel->get('table');
					if (!in_array($fieldTableName, array($baseTableName, $crmentityTable)) && $tabIndexes && $tabIndexes[$fieldTableName]) {
						$tablesList[$fieldTableName] = $tabIndexes[$fieldTableName];
					}
				}
			}

			if (count($uniqueFields) > 0) {
				$checkDuplicates = false;
				$uniqueFieldsData = array();
				foreach ($uniqueFields as $fieldName => $fieldModel) {
					$fieldDataType = $fieldModel->getFieldDataType();
					$fieldValue = $fieldValues[$fieldName];

					switch ($fieldDataType) {
						case 'reference'	:	if ($fieldValue == 0) {
													$fieldValue = '';
												}
												break;
						case 'date'			:
						case 'currency'		:
						case 'multipicklist':	if ($fieldValue) {
													$fieldValue = $fieldModel->getDBInsertValue($fieldValue);
												}
												break;
					}

					if ($fieldValue !== '' && $fieldValue !== NULL) {
						if ($fieldDataType == 'currency') {
							$countedDigits = 8;
							if ($fieldModel->isCustomField()) {
								$countedDigits = 5;
							}
							$fieldValue = round($fieldValue, $countedDigits);
						}

						$uniqueFieldsData[$fieldName] = $fieldValue;
						$checkDuplicates = true;
					}
				}

				if ($checkDuplicates) {
					$db = PearDatabase::getInstance();
					$recordId = $entityData->getId();

					$query = "SELECT $crmentityTable.crmid, $crmentityTable.label FROM $crmentityTable INNER JOIN $baseTableName ON $baseTableName.$baseTableId = $crmentityTable.crmid";
					foreach ($tablesList as $tableName => $tabIndex) {
						if ($moduleName == 'Calendar' || $moduleName == 'Events') {
							$query .= " LEFT JOIN $tableName ON $tableName.$tabIndex = $baseTableName.$baseTableId";
						} else {
							//INNER JOIN used instead of LEFT JOIN because all fields should be match
						$query .= " INNER JOIN $tableName ON $tableName.$tabIndex = $baseTableName.$baseTableId";
						}
					}
					$query .= " WHERE $crmentityTable.deleted = ?";

					$params = array(0);
					$conditions = array();
					foreach ($uniqueFields as $fieldName => $fieldModel) {
						$fieldTableName = $fieldModel->get('table');
						$fieldColumnName = $fieldModel->get('column');

						// For Calendar Start Date & Time or End Date & Time we need to concat date and time fields to search
						if (in_array($moduleName, array('Events', 'Calendar')) && in_array($fieldName, array('date_start', 'due_date', 'time_start', 'time_end'))) {
							if (in_array($fieldName, array('time_start', 'time_end'))) {
								continue;
							}

							$dateFieldColumnName = 'date_start';
							$timeFieldColumnName = 'time_start';
							if ($fieldName == 'due_date') {
								$dateFieldColumnName = 'due_date';
								$timeFieldColumnName = 'time_end';
							}

							$condition = "CONCAT($fieldTableName.$dateFieldColumnName,' ',$fieldTableName.$timeFieldColumnName) = ?";
							array_push($conditions, $condition);
							$params[] = trim(implode(" ", array($uniqueFieldsData[$dateFieldColumnName], $uniqueFieldsData[$timeFieldColumnName])));
							continue;
						}

						$fieldValue = $uniqueFieldsData[$fieldName];
						if (isset($fieldValue)) {
							array_push($conditions, "$fieldTableName.$fieldColumnName = ?");
						} else {
							$fieldValue = '';
							array_push($conditions, "($fieldTableName.$fieldColumnName = ? OR $fieldTableName.$fieldColumnName IS NULL)");
						}
						$params[] = $fieldValue;

						if ($fieldModel->get('uitype') == 72) {
							array_push($conditions, "$fieldTableName.currency_id = ?");
							$currencyIdDetails = split('curname', $_REQUEST['base_currency']);
							$params[] = $currencyIdDetails[1];
						}
					}

					if (count($conditions) > 0) {
						$conditionsSql = implode(" AND ", $conditions);
						$query .= " AND ($conditionsSql)";
					}

					if ($recordId) {
						$query .= " AND $crmentityTable.crmid != ?";
						$params[] = $recordId;
					}

					if ($moduleName == 'Events') {
						$query .= " AND $baseTableName.activitytype NOT IN (?, ?)";
						array_push($params, 'Task', 'Emails');
					} else if ($moduleName == 'Calendar') {
						$query .= " AND $baseTableName.activitytype = ?";
						array_push($params, 'Task');
					} else {
						$query .= " AND $crmentityTable.setype = ?";
						array_push($params, $moduleName);

						if ($moduleName == 'Leads' || $moduleName == 'Potentials') {
							$query .= " AND $baseTableName.converted = 0";
						}
					}
					$query .= ' LIMIT 6';

					$result = $db->pquery($query, $params);

					$duplicateRecordsList = array();
					while ($result && $row = $db->fetch_array($result)) {
						$duplicateRecordsList[$row['crmid']] = $row['label'];
					}

					if (count($duplicateRecordsList) > 0) {
						$exception = new DuplicateException(vtranslate('LBL_DUPLICATES_DETECTED'));
						$exception->setModule($moduleName)
								  ->setDuplicateRecordLabels($duplicateRecordsList)
								  ->setDuplicateRecordIds(array_keys($duplicateRecordsList));
						throw $exception;
					}
				}
			}
		}
	}
}

class DuplicateException extends Exception {

	private $duplicateRecordIds;
	public function setDuplicateRecordIds(array $duplicateRecordIds) {
		$this->duplicateRecordIds = $duplicateRecordIds;
		return $this;
	}

	public function getDuplicateRecordIds() {
		return $this->duplicateRecordIds;
	}

	private $duplicateRecordLabels;
	public function setDuplicateRecordLabels(array $duplicateRecordLabels) {
		$this->duplicateRecordLabels = $duplicateRecordLabels;
		return $this;
	}

	public function getDuplicateRecordLabels() {
		return $this->duplicateRecordLabels;
	}

	private $module;
	public function setModule($module) {
		$this->module = $module;
		return $this;
	}

	public function getModule() {
		return $this->module;
	}

	public function getDuplicationMessage() {
		$moduleName = $this->getModule();
		$duplicateRecordsList = $this->getDuplicateRecordIds();
		return getDuplicatesPreventionMessage($moduleName, $duplicateRecordsList);
	}
}