<?php
/*+************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 **************************************************************************************/

require_once 'include/QueryGenerator/QueryGenerator.php';

class EnhancedQueryGenerator extends QueryGenerator {

	public function __construct($module, $user) {
		parent::__construct($module, $user);
		$this->tableIndexList = array();
		$this->addTableIndexList($this->meta->getEntityTableIndexList());

		$this->getModuleFields();
		$this->referenceFieldList = array_keys($this->referenceFieldInfoList);
	}

	function addTableIndexList($tablesList) {
		if ($tablesList) {
			$this->tableIndexList = array_merge($this->tableIndexList, $tablesList);
		}
	}

	function getTableIndexList() {
		return $this->tableIndexList;
	}

	public function getModuleFields() {
		if ($this->moduleFields == null) {
			$moduleFields = parent::getModuleFields();

			//add reference fields also in the list
			foreach ($moduleFields as $fieldName => $fieldModel) {
				if ($fieldModel->getFieldDataType() == 'reference') {
					$referenceModules = $fieldModel->getReferenceList();
					$this->referenceFieldInfoList[$fieldName] = $referenceModules;
					foreach ($referenceModules as $referenceModule) {
						if ($referenceModule == 'Users')
							continue; // ignore users module for now
						$referenceModuleMeta = $this->getMeta($referenceModule);
						//webserviceField does not know if its a entity name field
						$nameFields = $referenceModuleMeta->getNameFields();
						$nameFields = explode(',', $nameFields);
						// update tablenames with their indexes for reference modules
						$this->addTableIndexList($referenceModuleMeta->getEntityTableIndexList());

						$referenceModuleFields = $referenceModuleMeta->getModuleFields();
						foreach ($referenceModuleFields as $referenceFieldName => $referenceFieldModel) {
							$newReferenceFieldModel = clone $referenceFieldModel;
							$name = "($fieldName ; ($referenceModule) $referenceFieldName)";
							$fieldModel->moduleName = $referenceModule;
							$newReferenceFieldModel->parentReferenceField = clone $fieldModel;
							$newReferenceFieldModel->referenceFieldName = $name;
							$newReferenceFieldModel->referenceFieldLabel = $fieldModel->getFieldLabelKey().'-'.$newReferenceFieldModel->getFieldLabelKey();
							$moduleFields[$name] = $newReferenceFieldModel;
							//update the referenceList for prefetching names
							$newReferenceFieldDataType = $newReferenceFieldModel->getFieldDataType();
							if ($newReferenceFieldDataType == 'reference') {
								$this->referenceFieldInfoList[$name] = $newReferenceFieldModel->getReferenceList();
							}
							if ($newReferenceFieldDataType == 'owner') {
								array_push($this->ownerFields, $name);
							}

							// webserviceField does not have info about its modulename and name fields, so setting them here to use in listviews
							if (in_array($referenceFieldName, $nameFields)) {
								$newReferenceFieldModel->isNameField = true;
								$newReferenceFieldModel->moduleName = $referenceModule;
							}
						}
					}
				}
			}
			$this->moduleFields = $moduleFields;
		}
		return $this->moduleFields;
	}

	public function parseAdvFilterList($advFilterList, $glue = '') {
		if ($glue) {
			$this->addConditionGlue($glue);
		}
		$customView = new CustomView($this->module);
		$dateSpecificConditions = $customView->getStdFilterConditions();
		$specialDateTimeConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
		foreach ($advFilterList as $groupindex => $groupcolumns) {
			$filtercolumns = $groupcolumns['columns'];
			if (count($filtercolumns) > 0) {
				$this->startGroup('');
				foreach ($filtercolumns as $index => $filter) {
					//If comparator is "e" or "n" then do not escapeSqlString.
					if (!in_array($filter['comparator'], array('e', 'n'))) {
						$filter['value'] = Vtiger_Util_Helper::escapeSqlString($filter['value']);
					}
					$nameComponents = explode(':', $filter['columnname']);
					// For Events "End Date & Time" field datatype should be DT. But, db will give D for due_date field
					if ($nameComponents[2] == 'due_date' && $nameComponents[3] == 'Events_End_Date_&_Time')
						$nameComponents[4] = 'DT';
					if (empty($nameComponents[2]) && $nameComponents[1] == 'crmid' && $nameComponents[0] == 'vtiger_crmentity') {
						$name = $this->getSQLColumn('id');
					} else {
						$name = $nameComponents[2];
					}
					if (($nameComponents[4] == 'D' || $nameComponents[4] == 'DT') && in_array($filter['comparator'], $dateSpecificConditions)) {
						$filter['stdfilter'] = $filter['comparator'];
						$valueComponents = explode(',', $filter['value']);
						if ($filter['comparator'] == 'custom') {
							if ($nameComponents[4] == 'DT') {
								$startDateTimeComponents = explode(' ', $valueComponents[0]);
								$endDateTimeComponents = explode(' ', $valueComponents[1]);
								$filter['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
								$filter['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
							} else {
								$filter['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
								$filter['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
							}
						}
						$dateFilterResolvedList = $customView->resolveDateFilterValue($filter);
						// If datatype is DT then we should append time also
						if ($nameComponents[4] == 'DT') {
							$startdate = explode(' ', $dateFilterResolvedList['startdate']);
							if ($startdate[1] == '')
								$startdate[1] = '00:00:00';
							$dateFilterResolvedList['startdate'] = $startdate[0].' '.$startdate[1];

							$enddate = explode(' ', $dateFilterResolvedList['enddate']);
							if ($enddate[1] == '')
								$enddate[1] = '23:59:59';
							$dateFilterResolvedList['enddate'] = $enddate[0].' '.$enddate[1];
						}
						$value = array();
						$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['startdate']);
						$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['enddate'], false);
						$this->addCondition($name, $value, 'BETWEEN');
					} else if (($nameComponents[4] == 'D' || $nameComponents[4] == 'DT') && in_array($filter['comparator'], $specialDateTimeConditions)) {
						$values = self::getSpecialDateConditionValue($filter['comparator'], $filter['value'], $nameComponents[4], true);
						$this->addCondition($name, $values['date'], $values['comparator']);
					} else if ($nameComponents[4] == 'DT' && ($filter['comparator'] == 'e' || $filter['comparator'] == 'n')) {
						$filter['stdfilter'] = $filter['comparator'];
						$dateTimeComponents = explode(' ', $filter['value']);
						$filter['startdate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
						$filter['enddate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);

						$startDate = $this->fixDateTimeValue($name, $filter['startdate']);
						$endDate = $this->fixDateTimeValue($name, $filter['enddate'], false);

						$value = array();
						$start = explode(' ', $startDate);
						if ($start[1] == "")
							$startDate = $start[0].' '.'00:00:00';

						$end = explode(' ', $endDate);
						if ($end[1] == "")
							$endDate = $end[0].' '.'23:59:59';

						$value[] = $startDate;
						$value[] = $endDate;
						if ($filter['comparator'] == 'n') {
							$this->addCondition($name, $value, 'NOTEQUAL');
						} else {
							$this->addCondition($name, $value, 'BETWEEN');
						}
					} else if ($nameComponents[4] == 'DT' && ($filter['comparator'] == 'a' || $filter['comparator'] == 'b')) {
						$dateTime = explode(' ', $filter['value']);
						$date = DateTimeField::convertToDBFormat($dateTime[0]);
						$value = array();
						$value[] = $this->fixDateTimeValue($name, $date, true);
						// Still fixDateTimeValue returns only date value, we need to append time because it is DT type
						for ($i = 0; $i < count($value); $i++) {
							$values = explode(' ', $value[$i]);
							if ($values[1] == '') {
								$values[1] = '00:00:00';
							}
							$value[$i] = $values[0].' '.$values[1];
						}
						$this->addCondition($name, $value, $filter['comparator']);
					} elseif ($nameComponents[2] == 'time_start' || $nameComponents[2] == 'time_end') {
						if ($nameComponents[2] == 'time_end' && $filter['value'] == '') {
							$filter['value'] = '';
						} else {
							$filter['value'] = DateTimeField::convertToDBTimeZone($filter['value'])->format('H:i:s');
						}
						$this->addCondition($nameComponents[2], $filter['value'], $filter['comparator']);
					} else {
						$this->addCondition($name, $filter['value'], $filter['comparator']);
					}
					$columncondition = $filter['column_condition'];
					if ($columncondition) {
						$this->addConditionGlue($columncondition);
					}
				}
				$this->endGroup();
				$groupConditionGlue = $groupcolumns['condition'];
				if ($groupConditionGlue) {
					$this->addConditionGlue($groupConditionGlue);
				}
			}
		}
	}

	public function getSQLColumn($name, $fieldObject = false) {
		if ($name == 'id') {
			$baseTable = $this->meta->getEntityBaseTable();
			$moduleTableIndexList = $this->getTableIndexList();
			$baseTableIndex = $moduleTableIndexList[$baseTable];
			return $baseTable.'.'.$baseTableIndex;
		}

		$moduleFields = $this->getModuleFields();
		$field = $moduleFields[$name];

		$referenceField = '';
		if ($fieldObject && isset($fieldObject->referenceFieldName)) {
			// if its a reference field then we need to add the fieldname to table name
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldObject->referenceFieldName, $matches);
			if (count($matches) != 0) {
				list($full, $referenceField, $referenceModule, $fieldname) = $matches;
			}
			$field = $fieldObject;
		}

		$column = $field->getColumnName();
		return $field->getTableName().$referenceField.'.'.$column;
	}

	public function getSelectClauseColumnSQL() {
		$columns = array();
		$moduleFields = $this->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);

		$moduleFields = $this->getModuleFields();

		$accessibleFieldList[] = 'id';
		$this->fields = array_intersect($this->fields, $accessibleFieldList);
		foreach ($this->fields as $field) {
			// handle for reference field
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $field, $matches);
			if (count($matches) != 0) {
				list($full, $referenceField, $referenceModule, $fieldname) = $matches;
				$parentReferenceFieldModel = null;
				$parentReferenceFieldModel = $moduleFields[$field];
				if ($parentReferenceFieldModel) {
					$columns[] = $parentReferenceFieldModel->getTableName().$referenceField.'.'.$parentReferenceFieldModel->getColumnName() .
							' AS '.$referenceField.$fieldname;

					//if the field is related to reference module's field, then we might need id of that record for example emails field in listviews
					$referenceModuleModelMeta = $this->getMeta($referenceModule);
					$referenceModuleTableIndex = $referenceModuleModelMeta->getEntityTableIndexList();
					$columns[] = $parentReferenceFieldModel->getTableName().$referenceField.'.'.$referenceModuleTableIndex[$parentReferenceFieldModel->getTableName()] .
							' AS '.$referenceField.$fieldname.'_id';
				}
			} else {
				$columns[] = $this->getSQLColumn($field);
			}

			//To merge date and time fields
			if ($this->meta->getEntityName() == 'Calendar' && ($field == 'date_start' || $field == 'due_date' || $field == 'taskstatus' || $field == 'eventstatus')) {
				if ($field == 'date_start') {
					$timeField = 'time_start';
					$sql = $this->getSQLColumn($timeField);
				} else if ($field == 'due_date') {
					$timeField = 'time_end';
					$sql = $this->getSQLColumn($timeField);
				} else if ($field == 'taskstatus' || $field == 'eventstatus') {
					//In calendar list view, Status value = Planned is not displaying
					$sql = "CASE WHEN (vtiger_activity.status not like '') THEN vtiger_activity.status ELSE vtiger_activity.eventstatus END AS ";
					if ($field == 'taskstatus') {
						$sql .= "status";
					} else {
						$sql .= $field;
					}
				}
				$columns[] = $sql;
			}
		}
		$this->columns = implode(', ', $columns);
		return $this->columns;
	}

	public function getFromClause() {
		global $current_user;
		if ($this->query || $this->fromClause) {
			return $this->fromClause;
		}
		$baseModule = $this->getModule();
		$moduleFields = $this->getModuleFields();
		$tableList = array();
		$tableJoinMapping = array();
		$tableJoinCondition = array();
		$referenceFieldTableList = array();
		$i = 1;

		$modulebaseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->getTableIndexList();
		$fieldList = $this->fields;
		foreach ($fieldList as $fieldName) {
			if ($fieldName == 'id') {
				continue;
			}
			$field = $moduleFields[$fieldName];
			if (!$field)
				continue;

			$baseFieldName = $fieldName;
			$referenceParentFieldName = '';
			// for reference field do not add the table names to the list
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
			if (count($matches) != 0) {
				list($full, $referenceParentFieldName, $referenceModuleName, $fieldName) = $matches;
			}

			$fieldType = $field->getFieldDataType();
			$tableName = $field->getTableName();

			if (empty($referenceParentFieldName)) {
				// for normal base module fields
				if ($fieldType == 'owner') {
					$tableList['vtiger_users'] = 'vtiger_users';
					$tableList['vtiger_groups'] = 'vtiger_groups';
					$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
					$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
				}
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] = $this->meta->getJoinClause($field->getTableName());

				if ($fieldName == 'roleid' && $baseModule == 'Users') {
					$tableJoinMapping['vtiger_role'] = 'INNER JOIN';
					$tableList['vtiger_role'] = 'vtiger_role';
				}
			} else {
				// handling reference fields joins
				$referenceParentFieldModel = $field->parentReferenceField;
				$referenceParentFieldModuleName = $referenceParentFieldModel->moduleName;
				$referenceParentFieldModuleMeta = $this->getMeta($referenceParentFieldModuleName);
				$moduleColumnIndex = $referenceParentFieldModuleMeta->getEntityTableIndexList();
				$referenceParentFieldTable = $referenceParentFieldModel->getTableName();
				$fieldTableName = $field->getTableName();
				$tableAlias = $fieldTableName.$referenceParentFieldName;

				if ($fieldType == 'reference') {
					if (!in_array($referenceParentFieldTable, $tableList)) {
						$tableList[$referenceParentFieldTable] = $referenceParentFieldTable;
						$tableJoinMapping[$referenceParentFieldTable] = $this->meta->getJoinClause($referenceParentFieldTable);
					}

					$moduleList = $field->getReferenceList();

					foreach ($moduleList as $module) {
						$meta = $this->getMeta($module);
						$nameFields = $meta->getNameFields();
						$nameFieldList = explode(',', $nameFields);
						foreach ($nameFieldList as $index => $column) {
							$referenceField = $meta->getFieldByColumnName($column);
							$referenceTable = $referenceField->getTableName();
							$tableIndexList = $meta->getEntityTableIndexList();
							$referenceTableIndex = $tableIndexList[$referenceTable];

							$tableAlias = $fieldTableName.$referenceParentFieldName;
							if (!array_key_exists($tableAlias, $tableJoinMapping)) {
								$tableJoinMapping[$tableAlias] = 'LEFT JOIN '.$fieldTableName.' AS';
								$tableJoinCondition[$referenceParentFieldName.$fieldName][$tableAlias] = $tableAlias.'.'.$moduleColumnIndex[$fieldTableName].' = ' .
										$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
							}
						}
					}
				} else if ($fieldType == 'owner') {
					$tableAlias = $fieldTableName.$referenceParentFieldName;
					$tableJoinMapping[$tableAlias] = 'LEFT JOIN '.$fieldTableName.' AS ';
					$tableJoinCondition[$referenceParentFieldName.$fieldName][$tableAlias] = $tableAlias.'.'.$moduleColumnIndex[$fieldTableName].' = ' .
							$referenceParentFieldModel->getTableName().'.'.$referenceParentFieldModel->getColumnName();
				} else {
					// if the reference field does not belong to base table but belongs to custom field table then we need to join it
					if (!array_key_exists($referenceParentFieldTable, $tableJoinMapping)) {
						$tableList[$referenceParentFieldTable] = $referenceParentFieldTable;
						$tableJoinMapping[$referenceParentFieldTable] = $this->meta->getJoinClause($referenceParentFieldTable);
					}

					if (!array_key_exists($fieldTableName.$referenceParentFieldName, $tableJoinMapping)) {
						$tableJoinMapping[$fieldTableName.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTableName.' AS ';
						$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTableName.$referenceParentFieldName] = $fieldTableName.$referenceParentFieldName.".".$moduleColumnIndex[$fieldTableName].' = ' .
								$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
					}
				}
			}
		}

		foreach ($this->whereFields as $fieldName) {
			if (empty($fieldName))
				continue;

			$field = $moduleFields[$fieldName];
			if (empty($field))
				continue; // not accessible field.

			$baseFieldName = $fieldName;
			$referenceParentFieldName = '';
			// for reference field do not add the table names to the list
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
			if (count($matches) != 0) {
				list($full, $referenceParentFieldName, $referenceModuleName, $fieldName) = $matches;
			}

			$fieldType = $field->getFieldDataType();
			$fieldTable = $field->getTableName();

			if (empty($referenceParentFieldName)) {
				// When a field is included in Where Clause, but not is Select Clause, and the field table is not base table,
				// The table will not be present in tablesList and hence needs to be added to the list.
				if (empty($tableList[$fieldTable])) {
					$tableList[$fieldTable] = $fieldTable;
					$tableJoinMapping[$fieldTable] = $this->meta->getJoinClause($fieldTable);
				}

				if ($fieldType == 'reference') {
					$moduleList = $field->getReferenceList();
					foreach ($moduleList as $module) {
						if ($module == 'Users' && $baseModule != 'Users') {
							$tableJoinMapping['vtiger_users'.$fieldName] = 'LEFT JOIN vtiger_users AS';
							$tableJoinCondition[$fieldName]['vtiger_users'.$fieldName] = $fieldTable.'.'.$field->getColumnName().' = vtiger_users'.$fieldName.'.id';
						} else if ($module == 'Currency') {
							$tableJoinMapping['vtiger_currency_info'.$fieldName] = 'LEFT JOIN vtiger_currency_info AS';
							$tableJoinCondition[$fieldName]['vtiger_currency_info'.$fieldName] = $fieldTable.'.'.$field->getColumnName().' = vtiger_currency_info'.$fieldName.'.id';
						} else {
							$tableJoinMapping['vtiger_crmentity'.$fieldName] = 'LEFT JOIN vtiger_crmentity AS';
							$tableJoinCondition[$fieldName]['vtiger_crmentity'.$fieldName] = 'vtiger_crmentity'.$fieldName.'.crmid' .
									' = '.$fieldTable.'.'.$field->getColumnName();
						}
					}
					if ($fieldName == 'roleid' && $baseModule == 'Users') {
						$tableJoinMapping['vtiger_role'] = 'INNER JOIN';
						$tableList['vtiger_role'] = 'vtiger_role';
					}
				} else if ($fieldType == 'owner') {
					$tableList['vtiger_users'] = 'vtiger_users';
					$tableList['vtiger_groups'] = 'vtiger_groups';
					$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
					$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
				}

				// if the field name is tags then we need to join with specific table 
				if ($fieldName == 'tags') {
					$tableList['vtiger_freetagged_objects'] = 'vtiger_freetagged_objects';
					$tableJoinMapping['vtiger_freetagged_objects'] = 'INNER JOIN';
				}
			} else {
				$referenceParentFieldModel = $field->parentReferenceField;
				$referenceParentFieldModuleMeta = $this->getMeta($referenceParentFieldModel->moduleName);
				$referenceModuleColumnIndex = $referenceParentFieldModuleMeta->getEntityTableIndexList();
				$referenceParentFieldTable = $referenceParentFieldModel->getTableName();

				if ($fieldType == 'owner') {
					// Need to join with vtiger_crmentity table
					if (!array_key_exists($fieldTable.$referenceParentFieldName, $tableJoinMapping)) {
						$tableJoinMapping[$fieldTable.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTable.' AS ';
						$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTable.$referenceParentFieldName] = $fieldTable.$referenceParentFieldName.'.'.$referenceModuleColumnIndex[$fieldTable].' = ' .
								$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
					}

					$tableJoinMapping['vtiger_users'.$referenceParentFieldName.$fieldName] = 'LEFT JOIN vtiger_users AS ';
					$tableJoinCondition[$referenceParentFieldName.$fieldName]['vtiger_users'.$referenceParentFieldName.$fieldName] = 'vtiger_users'.$referenceParentFieldName.$fieldName.'.id = '.$fieldTable.$referenceParentFieldName.'.'.$field->getColumnName();

					$tableJoinMapping['vtiger_groups'.$referenceParentFieldName.$fieldName] = 'LEFT JOIN vtiger_groups AS ';
					$tableJoinCondition[$referenceParentFieldName.$fieldName]['vtiger_groups'.$referenceParentFieldName.$fieldName] = 'vtiger_groups'.$referenceParentFieldName.$fieldName.'.groupid = '.$fieldTable.$referenceParentFieldName.'.'.$field->getColumnName();
				} else if ($fieldType == 'reference') {
					$moduleList = $field->getReferenceList();
					foreach ($moduleList as $module) {
						if ($module == 'Users' && $baseModule != 'Users') {// && ($fieldName == 'created_user_id' || $fieldName == 'modifiedby')) {
							//if the reference field belong to custom table then we need to add that join too
							if (!array_key_exists($referenceParentFieldTable, $tableJoinMapping)) {
								$tableList[$referenceParentFieldTable] = $referenceParentFieldTable;
								$tableJoinMapping[$referenceParentFieldTable] = $this->meta->getJoinClause($referenceParentFieldTable);
							}
							// Need to join with vtiger_crmentity table if its not joined earlier
							if (!array_key_exists($fieldTable.$referenceParentFieldName, $tableJoinMapping)) {
								$tableJoinMapping[$fieldTable.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTable.' AS ';
								$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTable.$referenceParentFieldName] = $fieldTable.$referenceParentFieldName.'.'.$referenceModuleColumnIndex[$fieldTable].' = '.$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
							}
							$tableJoinMapping['vtiger_users'.$referenceParentFieldName.$fieldName] = 'LEFT JOIN vtiger_users AS ';
							$tableJoinCondition[$referenceParentFieldName.$fieldName]['vtiger_users'.$referenceParentFieldName.$fieldName] = 'vtiger_users'.$referenceParentFieldName.$fieldName.'.id = '.$fieldTable.$referenceParentFieldName.'.'.$field->getColumnName();
						} else if ($module == 'Currency') {
							if (!array_key_exists($fieldTable.$referenceParentFieldName, $tableJoinMapping)) {
								$tableJoinMapping[$fieldTable.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTable.' AS ';
								$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTable.$referenceParentFieldName] = $fieldTable.$referenceParentFieldName.'.'.$referenceModuleColumnIndex[$fieldTable].' = '.$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
							}

							$tableJoinMapping['vtiger_currency_info'.$referenceParentFieldName.$fieldName] = 'LEFT JOIN vtiger_currency_info AS';
							$tableJoinCondition[$fieldName]['vtiger_currency_info'.$referenceParentFieldName.$fieldName] = $fieldTable.$referenceParentFieldName.'.'.$field->getColumnName().' = vtiger_currency_info'.$referenceParentFieldName.$fieldName.'.id';
						} else {
							if (!array_key_exists($fieldTable.$referenceParentFieldName, $tableJoinMapping)) {
								$tableJoinMapping[$fieldTable.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTable.' AS ';
								$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTable.$referenceParentFieldName] = $fieldTable.$referenceParentFieldName.'.'.$referenceModuleColumnIndex[$fieldTable].' = ' .
										$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
							}

							$tableAlias = 'vtiger_crmentity'.$referenceParentFieldName.$fieldName;
							if (!array_key_exists($tableAlias, $tableJoinMapping)) {
								$tableJoinMapping[$tableAlias] = 'LEFT JOIN vtiger_crmentity AS ';
								$tableJoinCondition[$referenceParentFieldName.$fieldName][$tableAlias] = $tableAlias.'.crmid = '.$fieldTable.$referenceParentFieldName.'.'.$field->getColumnName();
							}
						}
					}
				} else {
					// if the reference field does not belong to base table but belongs to custom field table then we need to join it
					if (!array_key_exists($referenceParentFieldTable, $tableJoinMapping)) {
						$tableList[$referenceParentFieldTable] = $referenceParentFieldTable;
						$tableJoinMapping[$referenceParentFieldTable] = $this->meta->getJoinClause($referenceParentFieldTable);
					}

					if (!array_key_exists($fieldTable.$referenceParentFieldName, $tableJoinMapping)) {
						$tableJoinMapping[$fieldTable.$referenceParentFieldName] = 'LEFT JOIN '.$fieldTable.' AS ';
						$tableJoinCondition[$referenceParentFieldName.$fieldName][$fieldTable.$referenceParentFieldName] = $fieldTable.$referenceParentFieldName.".".$referenceModuleColumnIndex[$fieldTable].' = ' .
								$referenceParentFieldTable.'.'.$referenceParentFieldModel->getColumnName();
					}
				}
			}
		}

		$referenceTableList = array();
		$baseTable = $this->meta->getEntityBaseTable();
		$baseTableIndex = $moduleTableIndexList[$baseTable];

		$defaultTableList = $this->meta->getEntityDefaultTableList();
		foreach ($defaultTableList as $table) {
			$tableList[$table] = $table;
			$tableJoinMapping[$table] = 'INNER JOIN';
		}
		$ownerFields = $this->meta->getOwnerFields();
		if (count($ownerFields) > 0) {
			$ownerField = $ownerFields[0];
		}

		$sqlTablesList = array();
		$sql = " FROM $baseTable ";
		unset($tableList[$baseTable]);
		foreach ($defaultTableList as $tableName) {
			$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable." .
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			unset($tableList[$tableName]);
		}

		foreach ($tableList as $tableName) {
			if ($tableName == 'vtiger_users') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName()."." .
						$field->getColumnName()." = $tableName.id";
			} elseif ($tableName == 'vtiger_groups') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName()."." .
						$field->getColumnName()." = $tableName.groupid";
			} elseif ($tableName == 'vtiger_freetagged_objects') {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.$baseTableIndex = $tableName.object_id " .
						"INNER JOIN vtiger_freetags ON $tableName.tag_id = vtiger_freetags.id ";
			} elseif ($tableName == 'vtiger_role') {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON vtiger_role.roleid = vtiger_user2role.roleid";
			} else {
				$tableCondition = $tableName.'.'.$moduleTableIndexList[$tableName];
				if (Vtiger_Functions::isUserSpecificFieldTable($tableName, $this->getModule())) {
					$tableCondition.= ' AND '.$tableName.'.userid='.$this->user->id;
				}
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable." .
						"$baseTableIndex = $tableCondition";
			}
			$sqlTablesList[] = $tableName;
		}

		if ($this->meta->getTabName() == 'Documents') {
			$tableJoinCondition['folderid'] = array(
				'vtiger_attachmentsfolderfolderid' => "$baseTable.folderid = vtiger_attachmentsfolderfolderid.folderid"
			);
			$tableJoinMapping['vtiger_attachmentsfolderfolderid'] = 'INNER JOIN vtiger_attachmentsfolder';
		}

		foreach ($tableJoinCondition as $fieldName => $conditionInfo) {
			foreach ($conditionInfo as $tableName => $condition) {
				if ($tableList[$tableName]) {
					$tableNameAlias = $tableName.'2';
					$condition = str_replace($tableName, $tableNameAlias, $condition);
				} else {
					$tableNameAlias = '';
				}
				if (!in_array($tableName, $sqlTablesList)) {
					$sql .= " $tableJoinMapping[$tableName] $tableName $tableNameAlias ON $condition";
					$sqlTablesList[] = $tableName;
				}
			}
		}

		$sql .= $this->meta->getEntityAccessControlQuery();
		$this->fromClause = $sql;

		return $sql;
	}

	public function getWhereClause() {
		global $current_user;
		if ($this->query || $this->whereClause) {
			return $this->whereClause;
		}
		$deletedQuery = $this->meta->getEntityDeletedQuery();
		$sql = '';
		if ($deletedQuery) {
			$sql .= " WHERE $deletedQuery";
		}
		if ($this->conditionInstanceCount > 0) {
			$sql .= ' AND ';
		} elseif (empty($deletedQuery)) {
			$sql .= ' WHERE ';
		}
		$baseModule = $this->getModule();
		$moduleFieldList = $this->getModuleFields();
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		$groupSql = $this->groupInfo;
		$fieldSqlList = array();

		foreach ($this->conditionals as $index => $conditionInfo) {
			$parentReferenceField = '';
			$baseFieldName = $fieldName = $conditionInfo['name'];
			$field = $moduleFieldList[$fieldName];

			// if its a reference field then we need to add the fieldname to table name
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $baseFieldName, $matches);
			if (count($matches) != 0) {
				list($full, $parentReferenceField, $referenceModule, $fieldName) = $matches;
			}

			if (empty($field) || $conditionInfo['operator'] == 'None') {
				continue;
			}

			$tableName = $field->getTableName().$parentReferenceField;
			$fieldSql = '(';
			$fieldGlue = '';
			$valueSqlList = $this->getConditionValue($conditionInfo['value'], $conditionInfo['operator'], $field);
			$operator = strtolower($conditionInfo['operator']);
			if ($operator == 'between' && $this->isDateType($field->getFieldDataType())) {
				$start = explode(' ', $conditionInfo['value'][0]);
				if (count($start) == 2)
					$conditionInfo['value'][0] = getValidDBInsertDateTimeValue($start[0].' '.$start[1]);

				$end = explode(' ', $conditionInfo['values'][1]);
				// Dates will be equal for Today, Tomorrow, Yesterday.
				if (count($end) == 2) {
					if ($start[0] == $end[0]) {
						$dateTime = new DateTime($conditionInfo['value'][0]);
						$nextDay = $dateTime->modify('+1 days');
						$nextDay = $nextDay->format('Y-m-d H:i:s');
						$values = explode(' ', $nextDay);
						$conditionInfo['value'][1] = getValidDBInsertDateTimeValue($values[0]).' '.$values[1];
					} else {
						$end = $conditionInfo['value'][1];
						$dateObject = new DateTimeField($end);
						$conditionInfo['value'][1] = $dateObject->getDBInsertDateTimeValue();
					}
				}
			}
			if (!is_array($valueSqlList)) {
				$valueSqlList = array($valueSqlList);
			}
			foreach ($valueSqlList as $valueSql) {
				if (in_array($baseFieldName, $this->referenceFieldList)) {
					if ($conditionInfo['operator'] == 'y') {
						$columnName = $field->getColumnName();
						// We are checking for zero since many reference fields will be set to 0 if it doest not have any value
						$fieldSql .= "$fieldGlue $tableName.$columnName $valueSql OR $tableName.$columnName = '0'";
						$fieldGlue = ' OR';
					} else {
						$moduleList = $this->referenceFieldInfoList[$baseFieldName];
						if (in_array('Users', $moduleList)) {
							$columnSqlTable = 'vtiger_users'.$parentReferenceField.$fieldName;
							$columnSql = getSqlForNameInDisplayFormat(array('first_name' => $columnSqlTable.'.first_name',
								'last_name' => $columnSqlTable.'.last_name'), 'Users');
						} else if (in_array('DocumentFolders', $moduleList)) {
							$columnSql = "vtiger_attachmentsfolder".$fieldName.".foldername";
						} else if (in_array('Currency', $moduleList)) {
							$columnSql = "vtiger_currency_info$parentReferenceField$fieldName.currency_name";
						} else if ($baseFieldName == 'roleid') {
							$columnSql = 'vtiger_role.rolename';
						} else {
							$columnSql = 'vtiger_crmentity'.$parentReferenceField.$fieldName.'.label';
						}
						$fieldSql .= "$fieldGlue trim($columnSql) $valueSql";
						$fieldGlue = ' OR';
					}
				} elseif (in_array($baseFieldName, $this->ownerFields)) {
					if ($parentReferenceField)
						$ownerTableName = $parentReferenceField.$fieldName;
					else
						$ownerTableName = '';
					$concatSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users'.$ownerTableName.'.first_name',
						'last_name' => 'vtiger_users'.$ownerTableName.'.last_name'), 'Users');
					if ($conditionInfo['operator'] == 'y') {
						//if both user name and group name empty, then only should list in isempty condition
						$fieldSql .= "$fieldGlue ((trim($concatSql) $valueSql) AND (vtiger_groups$ownerTableName.groupname $valueSql))";
					} else {
						$fieldSql .= "$fieldGlue (trim($concatSql) $valueSql or vtiger_groups$ownerTableName.groupname $valueSql)";
					}
				} elseif ($field->getFieldDataType() == 'date' && ($baseModule == 'Events' || $baseModule == 'Calendar') && ($fieldName == 'date_start' || $fieldName == 'due_date')) {
					$value = $conditionInfo['value'];
					if ($fieldName == 'date_start') {
						$dateFieldColumnName = 'vtiger_activity.date_start';
						$timeFieldColumnName = 'vtiger_activity.time_start';
					} else {
						$dateFieldColumnName = 'vtiger_activity.due_date';
						$timeFieldColumnName = 'vtiger_activity.time_end';
					}
					if ($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if (count($startDateValue) == 2 && count($endDateValue) == 2) {
							$fieldSql .= " CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql";
						} else {
							$fieldSql .= "$dateFieldColumnName $valueSql";
						}
					} else {
						if (is_array($value)) {
							$value = $value[0];
						}
						$values = explode(' ', $value);
						if (count($values) == 2) {
							$fieldSql .= "$fieldGlue CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql ";
						} else {
							$fieldSql .= "$fieldGlue $dateFieldColumnName $valueSql";
						}
					}
				} elseif ($field->getFieldDataType() == 'datetime') {
					$value = $conditionInfo['value'];
					if ($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if (empty($startDateValue[1]) && empty($endDateValue[1])) {
							$fieldSql .= "$fieldGlue date(".$tableName.'.'.$field->getColumnName().') '.$valueSql;
						} else {
							$fieldSql .= "$fieldGlue ".$tableName.'.'.$field->getColumnName().' '.$valueSql;
						}
					} elseif ($operator == 'between' || $operator == 'notequal' || $operator == 'a' || $operator == 'b') {
						$fieldSql .= "$fieldGlue ".$tableName.'.'.$field->getColumnName().' '.$valueSql;
					} else {
						$values = explode(' ', $value);
						if ($values[1] == '00:00:00') {
							$fieldSql .= "$fieldGlue CAST(".$$tableName.'.'.$field->getColumnName()." AS DATE) $valueSql";
						} else {
							$fieldSql .= "$fieldGlue ".$tableName.'.'.$field->getColumnName().' '.$valueSql;
						}
					}
				} else if (($baseModule == 'Events' || $baseModule == 'Calendar') && ($field->getColumnName() == 'status' || $field->getColumnName() == 'eventstatus')) {
					$otherFieldName = 'eventstatus';
					if ($field->getColumnName() == 'eventstatus') {
						$otherFieldName = 'taskstatus';
					}
					$otherField = $moduleFieldList[$otherFieldName];

					$specialCondition = '';
					$specialConditionForOtherField = '';
					$conditionGlue = ' OR ';
					if ($conditionInfo['operator'] == 'n' || $conditionInfo['operator'] == 'k' || $conditionInfo['operator'] == 'y') {
						$conditionGlue = ' AND ';
						if ($conditionInfo['operator'] == 'n') {
							$specialCondition = ' OR '.$tableName.'.'.$field->getColumnName().' IS NULL ';
							if ($otherField) {
								$specialConditionForOtherField = ' OR '.$otherField->getTableName().'.'.$otherField->getColumnName().' IS NULL ';
							}
						}
					}

					$otherFieldValueSql = $valueSql;
					if ($conditionInfo['operator'] == 'ny' && $otherField) {
						$otherFieldValueSql = "IS NOT NULL AND ".$otherField->getTableName().'.'.$otherField->getColumnName()." != ''";
					}

					$fieldSql .= "$fieldGlue ((".$tableName.'.'.$field->getColumnName().' '.$valueSql." $specialCondition) ";
					if ($otherField) {
						$fieldSql .= $conditionGlue.'('.$otherField->getTableName().'.'.$otherField->getColumnName().' '.$otherFieldValueSql.' '.$specialConditionForOtherField.'))';
					} else {
						$fieldSql .= ')';
					}
				}else if (Vtiger_Functions::isUserSpecificFieldTable($field->getTableName(), getTabModuleName($field->getTabId())) && $fieldName == "starred" && $conditionInfo['value'] != 1) {
					// since not for all records you will have entry in starred field table. So for disabled (value 0) we need to check both 0 and null
					$fieldSql .= "$fieldGlue (".$field->getTableName().'.'.$field->getColumnName().' '.$valueSql.' OR ';
					$fieldSql .= $field->getTableName().'.'.$field->getColumnName().' IS NULL)';
				} else if ($fieldName == "tags") {
					$fieldSql .= " $fieldGlue ( vtiger_freetags.id ".$valueSql.' AND ' .
							'( vtiger_freetagged_objects.tagger_id = '.$this->user->id.' OR vtiger_freetags.visibility = "public")) ';
				} else {
					if ($fieldName == 'birthday' && !$this->isRelativeSearchOperators($conditionInfo['operator'])) {
						$fieldSql .= "$fieldGlue DATE_FORMAT(".$tableName.'.' .
								$field->getColumnName().",'%m%d') ".$valueSql;
					} else {
						if ($conditionInfo['operator'] == 'n' && $field->getFieldDataType() == 'multipicklist') {
							$specialCondition = ' OR '.$tableName.'.'.$field->getColumnName().' IS NULL ';
							$fieldSql .= "$fieldGlue ".$tableName.'.' .
									$field->getColumnName().' '.$valueSql.$specialCondition;
						} else {
							$fieldSql .= "$fieldGlue ".$tableName.'.' .
									$field->getColumnName().' '.$valueSql;
						}
					}
				}
				if (($conditionInfo['operator'] == 'n' || $conditionInfo['operator'] == 'k') && ($field->getFieldDataType() == 'owner' ||
						$field->getFieldDataType() == 'picklist' || $field->getFieldDataType() == 'multipicklist')) {
					$fieldGlue = ' AND';
				} else {
					$fieldGlue = ' OR';
				}
			}
			$tmpTableName = 'vtiger_crmentity'.$parentReferenceField;
			if ($tmpTableName == $tableName && $referenceModule) {
				$fieldSql .= " and ".$tmpTableName.".setype = '".$referenceModule."'";
			}
			$fieldSql .= ')';
			$fieldSqlList[$index] = $fieldSql;
		}
		foreach ($this->manyToManyRelatedModuleConditions as $index => $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(), $conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$fieldSql = "(".$relationInfo['relationTable'].'.' .
					$relationInfo[$conditionInfo['column']].$conditionInfo['SQLOperator'] .
					$conditionInfo['value'].")";
			$fieldSqlList[$index] = $fieldSql;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(($baseModule == 'Calendar' || $baseModule == 'Events') && !$currentUserModel->isAdminUser()) {
			$moduleFocus = CRMEntity::getInstance('Calendar');
			$condition = $moduleFocus->buildWhereClauseConditionForCalendar();

			if($condition) {
				if($this->conditionInstanceCount > 0) {
					$sql .= $condition.' AND ';
				}else {
					$sql .= ' AND '.$condition;
				}
			}
		}

		// This is needed as there can be condition in different order and there is an assumption in makeGroupSqlReplacements API
		// that it expects the array in an order and then replaces the sql with its the corresponding place
		ksort($fieldSqlList);
		$groupSql = $this->makeGroupSqlReplacements($fieldSqlList, $groupSql);
		if ($this->conditionInstanceCount > 0) {
			$this->conditionalWhere = $groupSql;
			$sql .= $groupSql;
		}
		$sql .= " AND $baseTable.$baseTableIndex > 0";
		$this->whereClause = $sql;
		return $sql;
	}

	/**
	 * Function returns table column for the given sort field name
	 * @param <String> $fieldName
	 * @return <String> columnname
	 */
	function getOrderByColumn($fieldName) {
		$fieldList = $this->getModuleFields();
		$orderByFieldModel = $fieldList[$fieldName];

		$parentReferenceField = '';
		preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
		if (count($matches) != 0) {
			list($full, $parentReferenceField, $referenceModule, $fieldName) = $matches;
		}
		if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == 'reference') {
			$referenceModules = $orderByFieldModel->getReferenceList();
			if (in_array('DocumentFolders', $referenceModules)) {
				$orderByColumn = "vtiger_attachmentsfolder".$orderByFieldModel->getFieldName().".foldername";
			} else if (in_array('Currency', $referenceModules)) {
				if ($parentReferenceField) {
					$orderByColumn = 'vtiger_currency_info'.$parentReferenceField.$orderByFieldModel->getFieldName().'.currency_name';
				} else {
					$orderByColumn = 'vtiger_currency_info'.$fieldName.'.currency_name';
				}
			} else if (in_array('Users', $referenceModules)) {
				$columnSqlTable = 'vtiger_users'.$parentReferenceField.$fieldName;
				$orderByColumn = getSqlForNameInDisplayFormat(array('first_name' => $columnSqlTable.'.first_name',
					'last_name' => $columnSqlTable.'.last_name'), 'Users');
			} else {
				$orderByColumn = 'vtiger_crmentity'.$parentReferenceField.$orderByFieldModel->getFieldName().'.label'; //.$fieldModel->get('column');
			}
		} else if ($orderByFieldModel && $orderByFieldModel->getFieldDataType() == 'owner') {
			if ($parentReferenceField) {
				$userTableName = 'vtiger_users'.$parentReferenceField.$orderByFieldModel->getFieldName();
				$groupTableName = 'vtiger_groups'.$parentReferenceField.$orderByFieldModel->getFieldName();
				$orderByColumn = "COALESCE(CONCAT($userTableName.first_name,$userTableName.last_name),$groupTableName.groupname)";
			} else {
				$orderByColumn = 'COALESCE(CONCAT(vtiger_users.first_name,vtiger_users.last_name),vtiger_groups.groupname)';
			}
		} else if (($orderByFieldModel->getFieldName() == 'taskstatus' || $orderByFieldModel->getFieldName() == 'eventstatus') && $this->module == 'Calendar') {
			$orderByColumn = 'status';
		} else if ($orderByFieldModel) {
			$orderByColumn = $orderByFieldModel->getTableName().$parentReferenceField.'.'.$orderByFieldModel->getColumnName();
		}
		return $orderByColumn;
	}

	/**
	 * Function get the date value and comparator
	 * @param <string> $comparator - date comparator
	 * @param <integer> $value - date value
	 * @param <string> $type - date type D or DT
	 * @param <boolean> $queryGenerator - for querygenerator the format is different
	 * @return <array> with comparator and calculated date values
	 */
	static function getSpecialDateConditionValue($comparator, $value, $type, $queryGenerator = false) {
		switch ($comparator) {
			case 'lessthandaysago' : $days = $value;
				$olderDate = date('Y-m-d', strtotime('-'.$days.' days'));
				$today = date('Y-m-d');
				if ($queryGenerator) {
					return array('comparator' => 'bw', 'date' => $olderDate.",".$today);
				}

				return array('comparator' => 'bw', 'date' => array($olderDate, $today));

			case 'morethandaysago' : $days = $value - 1;
				$olderDate = date('Y-m-d', strtotime('-'.$days.' days'));
				return array('comparator' => 'l', 'date' => $olderDate);

			case 'inlessthan' : $days = $value;
				$today = date('Y-m-d');
				$futureDate = date('Y-m-d', strtotime('+'.$days.' days'));
				if ($queryGenerator) {
					return array('comparator' => 'bw', 'date' => $today.",".$futureDate);
				}

				return array('comparator' => 'bw', 'date' => array($today, $futureDate));

			case 'inmorethan' : $days = $value - 1;
				$futureDate = date('Y-m-d', strtotime('+'.$days.' days'));
				return array('comparator' => 'g', 'date' => $futureDate);

			case 'daysago' : $olderDate = date('Y-m-d', strtotime('-'.$value.' days'));
				if ($type == 'DT') {
					return array('comparator' => 'c', 'date' => $olderDate);
				}

				return array('comparator' => 'e', 'date' => $olderDate);

			case 'dayslater' : $futureDate = date('Y-m-d', strtotime('+'.$value.' days'));
				if ($type == 'DT') {
					return array('comparator' => 'c', 'date' => $futureDate);
				}

				return array('comparator' => 'e', 'date' => $futureDate);

			case 'lessthanhoursbefore' : $currentTime = date('Y-m-d H:i:s');
				$olderDateTime = date('Y-m-d H:i:s', strtotime('-'.$value.' hours'));
				if ($queryGenerator) {
					// convert to user format
					$currentDateTimeInstance = new DateTimeField($currentTime);
					$currentTime = $currentDateTimeInstance->getDisplayDateTimeValue();
					$olderDateTimeInstance = new DateTimeField($olderDateTime);
					$olderDateTime = $olderDateTimeInstance->getDisplayDateTimeValue();

					return array('comparator' => 'bw', 'date' => $olderDateTime.",".$currentTime);
				}

				return array('comparator' => 'bw', 'date' => array($olderDateTime, $currentTime));

			case 'lessthanhourslater' : $currentTime = date('Y-m-d H:i:s');
				$futureDateTime = date('Y-m-d H:i:s', strtotime('+'.$value.' hours'));
				if ($queryGenerator) {
					// convert to user format
					$currentDateTimeInstance = new DateTimeField($currentTime);
					$currentTime = $currentDateTimeInstance->getDisplayDateTimeValue();
					$futureDateTimeInstance = new DateTimeField($futureDateTime);
					$futureDateTime = $futureDateTimeInstance->getDisplayDateTimeValue();

					return array('comparator' => 'bw', 'date' => $currentTime.",".$futureDateTime);
				}

				return array('comparator' => 'bw', 'date' => array($currentTime, $futureDateTime));

			case 'morethanhoursbefore' : $olderDateTime = date('Y-m-d H:i:s', strtotime('-'.$value.' hours'));
				if ($queryGenerator) {
					// convert to user format
					$olderDateTimeInstance = new DateTimeField($olderDateTime);
					$olderDateTime = $olderDateTimeInstance->getDisplayDateTimeValue();
				}

				return array('comparator' => 'l', 'date' => $olderDateTime);

			case 'morethanhourslater' : $futureDateTime = date('Y-m-d H:i:s', strtotime('+'.$value.' hours'));
				if ($queryGenerator) {
					// convert to user format
					$futureDateTimeInstance = new DateTimeField($futureDateTime);
					$futureDateTime = $futureDateTimeInstance->getDisplayDateTimeValue();
				}

				return array('comparator' => 'g', 'date' => $futureDateTime);

			default : return '';
		}
	}

	/**
	 * Function to get the condition query for special date condtions
	 * @param <string> $comparator
	 * @param <date> or <array> $date - date value or array of dates for between condition 
	 * @return <string> condition query
	 */
	static function getSpecialDateConditionQuery($comparator, $date) {
		switch ($comparator) {
			case 'bw' : return " BETWEEN '$date[0]' AND '$date[1]' ";

			case 'l' : return " < '$date' ";

			case 'g' : return " > '$date' ";

			case 'e' : return " = '$date' ";

			case 'c' : return " LIKE '%$date%' ";

			default : return '';
		}
	}

}
