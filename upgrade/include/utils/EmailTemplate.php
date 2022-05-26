<?php
/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************* */

require_once 'include/events/SqlResultIterator.inc';

/**
 * Description of EmailTemplateUtils
 *
 * @author mak
 */
class EmailTemplate {

	protected $module;
	protected $rawDescription;
	protected $processedDescription;
	protected $recordId;
	protected $processed;
	protected $templateFields;
	protected $user;
	protected $processedmodules;
	protected $referencedFields;
	protected $multiRefValues = array();
	protected $multiRefIds = array();
	public $removeTags = false;

	public function __construct($module, $description, $recordId, $user) {
		$this->module = $module;
		$this->recordId = $recordId;
		$this->processed = false;
		$this->user = $user;
		$this->setDescription($description);
	}

	public function setDescription($description) {
        // Because if we have two dollars like this "$$" it's not working because it'll be like escape char
        $description = preg_replace("/\\$\\$/","$ $",$description);
		$this->rawDescription = $description;
		$this->processedDescription = $description;
        $result = preg_match_all("/\\$(?:[a-zA-Z0-9]+)-(?:[a-zA-Z0-9]+)(?:_[a-zA-Z0-9]+)?(?::[a-zA-Z0-9]+)?(?:_[a-zA-Z0-9]+)*\\$/", $this->rawDescription, $matches);
        if($result != 0){
            $templateVariablePair = $matches[0];
            $this->templateFields = Array();
            for ($i = 0; $i < count($templateVariablePair); $i++) {
                $templateVariablePair[$i] = str_replace('$', '', $templateVariablePair[$i]);
                list($module, $columnName) = explode('-', $templateVariablePair[$i]);
                list($parentColumn, $childColumn) = explode(':', $columnName);
                $this->templateFields[$module][] = $parentColumn;
                $this->referencedFields[$parentColumn][] = $childColumn;
                $this->processedmodules[$module] = false;
            }
            $this->processed = false;
        }
	}

	private function getTemplateVariableListForModule($module) {
		return $this->templateFields[strtolower($module)];
	}
	
	public function process($params) {
		$module = $this->module;
		$recordId = $this->recordId;
		$variableList = $this->getTemplateVariableListForModule($module);
		$handler = vtws_getModuleHandlerFromName($module, $this->user);
		$meta = $handler->getMeta();
		$referenceFields = $meta->getReferenceFieldDetails();
		$fieldColumnMapping = $meta->getFieldColumnMapping();
		$columnTableMapping = $meta->getColumnTableMapping();
        $currentUsersModel = Users_Record_Model::getCurrentUserModel();

		if ($this->isProcessingReferenceField($params)) {
			$parentFieldColumnMapping = $meta->getFieldColumnMapping();
			$module = $params['referencedMeta']->getEntityName();
			if (!$this->isModuleActive($module)) {
				return;
			}
			$recordId = $params['id'];

			$meta = $params['referencedMeta'];
			$referenceFields = $meta->getReferenceFieldDetails();
			$fieldColumnMapping = $meta->getFieldColumnMapping();
			$columnTableMapping = $meta->getColumnTableMapping();
			$referenceColumn = $parentFieldColumnMapping[$params['field']];
			$variableList = $this->referencedFields[$referenceColumn];
		}

		$tableList = array();
		$columnList = array();
		$allColumnList = $meta->getUserAccessibleColumns();
		$fieldList = array();

		$baseTable = $meta->getEntityBaseTable();
		$tableList[$baseTable] = $baseTable;
		
		if (count($variableList) > 0) {
			foreach ($variableList as $column) {
				if (in_array($column, $allColumnList)) {
					$fieldList[] = array_search($column, $fieldColumnMapping);
					$columnList[] = $column;
				}
			}
			foreach ($fieldList as $field) {
				if (!empty($columnTableMapping[$fieldColumnMapping[$field]])) {
					$tableList[$columnTableMapping[$fieldColumnMapping[$field]]] = '';
				}
			}
            $columnListTable = array();
            foreach ($columnList as $column) {
				$columnListTable[] = $columnTableMapping[$column] . "." . $column;
            }
			$tableList = array_keys($tableList);
			$defaultTableList = $meta->getEntityDefaultTableList();
			foreach ($defaultTableList as $defaultTable) {
				if (!in_array($defaultTable, $tableList)) {
					$tableList[] = $defaultTable;
				}
			}

			if (count($tableList) > 0 && count($columnListTable) > 0) {
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				$sql = 'SELECT '.$tableList[0].'.'.$moduleTableIndexList[$tableList[0]].' AS vt_recordid, ' . implode(', ', $columnListTable) . ' FROM ' . $tableList[0];
				foreach ($tableList as $index => $tableName) {
					if ($tableName != $tableList[0]) {
						if($tableName == 'vtiger_seactivityrel' || $tableName == 'vtiger_cntactivityrel') {
							$sql .= ' LEFT JOIN ';
						} else {
							$sql .= ' INNER JOIN ';
						}
						$sql .= $tableName . ' ON ' . $tableList[0] . '.' .
								$moduleTableIndexList[$tableList[0]] . '=' . $tableName . '.' .
								$moduleTableIndexList[$tableName];
					}
				}
				//If module is Leads and if you are not selected any leads fields then query failure is happening.
				//By default we are checking where condition on base table.
				if($module == 'Leads' && !in_array('vtiger_leaddetails', $tableList)){
					$sql .=' INNER JOIN vtiger_leaddetails ON vtiger_leaddetails.leadid = vtiger_crmentity.crmid';
				}
				
				$sql .= ' WHERE';
				$deleteQuery = $meta->getEntityDeletedQuery();
				if (!empty($deleteQuery)) {
					$sql .= ' ' . $meta->getEntityDeletedQuery() . ' AND';
				}
				/*If we are processing multi reference fields, we might have record 
				 * id as'24,23'. So we need to explode with comma(,). 
				 */
				$recordIds = explode(',', $recordId);
				$sql .= ' ' . $tableList[0] . '.' . $moduleTableIndexList[$tableList[0]] . ' IN('.  generateQuestionMarks($recordIds).')';
				$sqlparams = $recordIds;
				$db = PearDatabase::getInstance();
				$result = $db->pquery($sql, $sqlparams);
				$it = new SqlResultIterator($db, $result);
			//assuming there can only be one row.
				$values = array();
				foreach ($it as $row) {
					foreach ($fieldList as $field) {
						$moduleModel = Vtiger_Module_Model::getInstance($module);
						$fieldModel = Vtiger_Field_Model::getInstance($field, $moduleModel);
						if(!$fieldModel->isViewable()){
							continue;
						}
						$value = $row->get($fieldColumnMapping[$field]);
						//Emails are wrapping with hyperlinks, so skipping email fields as well
						if($fieldModel->isReferenceField() || $fieldModel->isOwnerField() || $fieldModel->get('uitype') == 13) {
							if ($referenceColumn == 'contactid' && $this->module == 'Events') {
								/**Getting multi reference record's reference/owner/uitype = 13 values 
								 * and storing it in a class variable, later we will glue them with comma(,)
s								 */
								$this->multiRefValues[$field][] = strip_tags($fieldModel->getDisplayValue($value, $row->get('vt_recordid')));
							} else {
								if ($value) {
									$values[$field] = $value;
								}
							}
						} else if ($this->module == 'Events' && $fieldModel->getFieldDataType() == 'multireference') {
							//get all the multi reference record ids and implode with comma(,)
							$this->multiRefIds[] = $value;
							$values[$field] = implode(',', array_unique($this->multiRefIds));
						} else {
							if ($referenceColumn == 'contactid' && $this->module == 'Events') {
								$this->multiRefValues[$field][] = $fieldModel->getDisplayValue($value, $row->get('vt_recordid'));
							} else {
								//If removetags variable is set to true then remove tags around value
								if($this->removeTags) {
									$values[$field] = $fieldModel->getDisplayValue($value, $row->get('vt_recordid'), false, $this->removeTags);
								} else {
									$values[$field] = $fieldModel->getDisplayValue($value, $row->get('vt_recordid'));
								}

								$uiType = $fieldModel->get('uitype');
								if (in_array($uiType, array('71', '72'))) {
									if ($uiType == '72' && $fieldModel->getName() == 'unit_price') {
										$currencyId = getProductBaseCurrency($row->get('vt_recordid'), $module);
									} else if ($uiType == '71') {
										$currencyId = $this->user->currency_id;
									}

									$currencyInfo = getCurrencySymbolandCRate($currencyId);
									$values[$field] = CurrencyField::appendCurrencySymbol($values[$field], $currencyInfo['symbol']);
								}
							}
						}
					}
				}
				$moduleFields = $meta->getModuleFields();
				foreach ($moduleFields as $fieldName => $webserviceField) {
                    $presence = $webserviceField->getPresence();
                    if(!in_array($presence,array(0,2))){
                        continue;
                    }
					if (isset($values[$fieldName]) &&
							$values[$fieldName] !== null) {
						if (strcasecmp($webserviceField->getFieldDataType(), 'reference') === 0) {
							$details = $webserviceField->getReferenceList();
							if (count($details) == 1) {
								$referencedObjectHandler = vtws_getModuleHandlerFromName(
										$details[0], $this->user);
							} else {
								$type = getSalesEntityType(
										$values[$fieldName]);
								$referencedObjectHandler = vtws_getModuleHandlerFromName($type,
										$this->user);
							}
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							if (!$this->isProcessingReferenceField($params) && !empty($values[$fieldName])) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName]));
							}
							$values[$fieldName] =
									$referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(),
									$values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'owner') === 0) {
							$referencedObjectHandler = vtws_getModuleHandlerFromName(
									vtws_getOwnerType($values[$fieldName]),
									$this->user);
							$referencedObjectMeta = $referencedObjectHandler->getMeta();
							/*
							* operation supported for format $module-parentcolumn:childcolumn$
							*/
							if (in_array($fieldColumnMapping[$fieldName], array_keys($this->referencedFields))) {
								$this->process(array('parentMeta' => $meta, 'referencedMeta' => $referencedObjectMeta, 'field' => $fieldName, 'id' => $values[$fieldName], 'owner' => true));
							}

							$values[$fieldName] =
									$referencedObjectMeta->getName(vtws_getId(
									$referencedObjectMeta->getEntityId(),
									$values[$fieldName]));
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'picklist') === 0) {
							if ($referenceColumn == 'contactid' && $this->module == 'Events') {
								$this->multiRefValues[$fieldName][] = getTranslatedString($values[$fieldName], $module);
							} else {
								$values[$fieldName] = getTranslatedString(
								$values[$fieldName], $module);
							}
						} elseif (strcasecmp($fieldName, 'salutationtype') === 0 && $webserviceField->getUIType() == '55'){
							$values[$fieldName] = getTranslatedString(
									$values[$fieldName], $module);
						} elseif (strcasecmp($webserviceField->getFieldDataType(), 'datetime') === 0) {
							if ($referenceColumn == 'contactid' && $this->module == 'Events') {
								$$this->multiRefValues[$fieldName][] = $values[$fieldName] . ' ' . $currentUsersModel->time_zone;
							} else {
								$values[$fieldName] = $values[$fieldName] . ' ' . $currentUsersModel->time_zone;
							}
						}
					}
				}

				if (!$this->isProcessingReferenceField($params)) {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . "-$column$";
						$replaceValue = $values[array_search($column, $fieldColumnMapping)];
						if($this->removeTags){
							$encodedValue = json_encode($replaceValue);
							$replaceValue = substr($encodedValue, 1, -1);
						}

						$this->processedDescription = str_replace($needle,
								$replaceValue , $this->processedDescription);
					}
				} else {
					foreach ($columnList as $column) {
						$needle = '$' . strtolower($this->module) . '-' . $parentFieldColumnMapping[$params['field']] . ':' . $column . '$';
						//mergeing all multi reference fields with their respective values.
						if ($this->module == 'Events' && $referenceColumn == 'contactid') {
							$multiRefValues = $this->multiRefValues[array_search($column, $fieldColumnMapping)];
							if (is_array($multiRefValues)) {
								$replacer = implode(',', $this->multiRefValues[array_search($column, $fieldColumnMapping)]);
							} else {
								$replacer = '';
							}
						} else {
							$replacer = $values[array_search($column, $fieldColumnMapping)];
						}
						if($this->removeTags){
							$encodedValue = json_encode($replacer);
							$replacer = substr($encodedValue, 1, -1);
						}
						
						$this->processedDescription = str_replace($needle, $replacer, $this->processedDescription);
					}
					if (!$params['owner'])
						$this->processedmodules[$module] = true;
				}
			}
		}
		$this->processed = true;
	}

	public function isProcessingReferenceField($params) {
		if (!empty($params['referencedMeta'])
				&& (!empty($params['id']))
				&& (!empty($params['field']))
		) {
			return true;
		}

		return false;
	}

	public function getProcessedDescription() {
		if (!$this->processed) {
			$this->process(null);
		}
		return $this->processedDescription;
	}

	public function isModuleActive($module) {
		include_once 'include/utils/VtlibUtils.php';
		if (vtlib_isModuleActive($module) && ((isPermitted($module, 'EditView') == 'yes'))) {
			return true;
		}
		return false;
	}

	public function isActive($field, $mod) {
		global $adb;
		$tabid = getTabid($mod);
		$query = 'select * from vtiger_field where fieldname = ?  and tabid = ? and presence in (0,2)';
		$res = $adb->pquery($query, array($field, $tabid));
		$rows = $adb->num_rows($res);
		if ($rows > 0) {
			return true;
		}else
			return false;
	}

}

?>