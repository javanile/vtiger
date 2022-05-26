<?php
/* ************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Chart_Model extends Vtiger_Base_Model {

	public static function getInstanceById($reportModel) {
		$self = new self();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_reporttype WHERE reportid = ?', array($reportModel->getId()));
		$data = $db->query_result($result, 0, 'data');
		if(!empty($data)) {
			$decodeData = Zend_Json::decode(decode_html($data));
			$self->setData($decodeData);
			$self->setParent($reportModel);
			$self->setId($reportModel->getId());
		}
		return $self;
	}

	function getId() {
		return $this->get('reportid');
	}

	function setId($id) {
		$this->set('reportid', $id);
	}

	function getParent() {
		return $this->parent;
	}

	function setParent($parent) {
		$this->parent = $parent;
	}

	function getChartType() {
		$type = $this->get('type');
		if(empty($type)) $type = 'pieChart';
		return $type;
	}

	function getGroupByField() {
		return $this->get('groupbyfield');
	}

	function getDataFields() {
		return $this->get('datafields');
	}

	function getData() {
		$type = ucfirst($this->getChartType());
		$chartModel = new $type($this);
		return $chartModel->generateData();
	}
}

abstract class Base_Chart extends Vtiger_Base_Model{

	function __construct($parent) {
		$this->setParent($parent);
		$this->setReportRunObject();

		$this->setQueryColumns($this->getParent()->getDataFields());
		$this->setGroupByColumns($this->getParent()->getGroupByField());
	}

	function setParent($parent) {
		$this->parent = $parent;
	}

	function getParent() {
		return $this->parent;
	}

	function getReportModel() {
		$parent = $this->getParent();
		return $parent->getParent();
	}

	function isRecordCount() {
		return $this->isRecordCount;
	}

	function setRecordCount() {
		$this->isRecordCount = true;
	}

	function setReportRunObject() {
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();
		$this->reportRun = ReportRun::getInstance($reportModel->get('reportid'));
	}

	function getReportRunObject() {
		return $this->reportRun;
	}

	function getFieldModelByReportColumnName($column) {
		$fieldInfo = explode(':', $column);
		$moduleFieldLabelInfo = explode('_', $fieldInfo[2]);
		$moduleName = $moduleFieldLabelInfo[0];
		$fieldName = $fieldInfo[3];

		if($moduleName && $fieldName) {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldInstance = $moduleModel->getField($fieldName);
			if($moduleName == "Calendar" && !$fieldInstance){
				$moduleModel = Vtiger_Module_Model::getInstance("Events");
				return $moduleModel->getField($fieldName);
			}
			return $fieldInstance;
		}
		return false;
	}

	function getQueryColumnsByFieldModel() {
		return $this->fieldModels;
	}

	function setQueryColumns($columns) {
		if($columns && is_string($columns)) $columns = array($columns);

		if(is_array($columns)) {
			foreach($columns as $column) {
				if($column == 'count(*)') {
					$this->setRecordCount();
				} else {

					$fieldModel = $this->getFieldModelByReportColumnName($column);
					$columnInfo = explode(':', $column);

					$referenceFieldReportColumnSQL = $this->getReportRunObject()->getEscapedColumns($columnInfo);

					$aggregateFunction = $columnInfo[5];
					if(empty($referenceFieldReportColumnSQL)) {
						$reportColumnSQL = $this->getReportTotalColumnSQL($columnInfo);
						$reportColumnSQLInfo = split(' AS ', $reportColumnSQL);

						if($aggregateFunction == 'AVG') {	// added as mysql will ignore null values
							$label = "`".$this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]).'_AVG'."`";
							$reportColumn = '(SUM('. $reportColumnSQLInfo[0] .')/COUNT(*)) AS '.$label;
						} else {
							$label = "`".$this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]).'_'.$aggregateFunction."`";
							$reportColumn = $aggregateFunction. '('. $reportColumnSQLInfo[0] .') AS '.$label;
						}

						$fieldModel->set('reportcolumn', $reportColumn);
						$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($label));
					} else {
						$reportColumn = $referenceFieldReportColumnSQL;
						$groupColumnSQLInfo = split(' AS ', $referenceFieldReportColumnSQL);
						$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($groupColumnSQLInfo[1]));
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($reportColumn));
					}

					$fieldModel->set('reportcolumninfo', $column);

					if($fieldModel) {
						$fieldModels[] = $fieldModel;
					}
				}
			}
		}
		if($fieldModels) $this->fieldModels = $fieldModels;
	}

	function setGroupByColumns($columns) {
		if($columns && is_string($columns)) $columns = array($columns);

		if(is_array($columns)) {
			foreach($columns as $column) {
				$fieldModel = $this->getFieldModelByReportColumnName($column);

				if($fieldModel) {
					$columnInfo = explode(':', $column);

					$referenceFieldReportColumnSQL = $this->getReportRunObject()->getEscapedColumns($columnInfo);
					if(empty($referenceFieldReportColumnSQL)) {
						$reportColumnSQL = $this->getReportColumnSQL($columnInfo);
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($reportColumnSQL));
						// Added support for date and date time fields with Year and Month support
						if($columnInfo[4] == 'D' || $columnInfo[4] == 'DT') {
							$reportColumnSQLInfo = split(' AS ', $reportColumnSQL);
							$fieldModel->set('reportlabel', trim($this->reportRun->replaceSpecialChar($reportColumnSQLInfo[1]), '\'')); // trim added as single quote on labels was not grouping properly
						} else {
							$fieldModel->set('reportlabel', $this->reportRun->replaceSpecialChar($columnInfo[2]));
						}
					} else {
						$groupColumnSQLInfo = split(' AS ', $referenceFieldReportColumnSQL);
						$fieldModel->set('reportlabel', trim($this->reportRun->replaceSpecialChar($groupColumnSQLInfo[1]), '\''));
						$fieldModel->set('reportcolumn', $this->reportRun->replaceSpecialChar($referenceFieldReportColumnSQL));
					}

					$fieldModel->set('reportcolumninfo', $column);

					$fieldModels[] = $fieldModel;
				}
			}
		}
		if($fieldModels) $this->groupByFieldModels = $fieldModels;
	}

	function getGroupbyColumnsByFieldModel() {
		return $this->groupByFieldModels;
	}

	/**
	 * Function returns sql column for group by fields
	 * @param <Array> $selectedfields - field info report format
	 * @return <String>
	 */
	function getReportColumnSQL($selectedfields) {
		$reportRunObject = $this->getReportRunObject();
		$append_currency_symbol_to_value = $reportRunObject->append_currency_symbol_to_value;
		$reportRunObject->append_currency_symbol_to_value = array();

		$columnSQL = $reportRunObject->getColumnSQL($selectedfields);

		$reportRunObject->append_currency_symbol_to_value = $append_currency_symbol_to_value;
		return $columnSQL;
	}


	/**
	 * Function returns sql column for data fields
	 * @param <Array> $fieldInfo - field info report format
	 * @return <string>
	 */
	function getReportTotalColumnSQL($fieldInfo) {
		$primaryModule = $this->getPrimaryModule();
		$columnTotalSQL = $this->getReportRunObject()->getColumnsTotalSQL($fieldInfo, $primaryModule). ' AS '. $fieldInfo[2];
		return $columnTotalSQL;
	}

	/**
	 * Function returns labels for aggregate functions
	 * @param type $aggregateFunction
	 * @return string
	 */
	function getAggregateFunctionLabel($aggregateFunction) {
		switch($aggregateFunction) {
			case 'SUM' : return 'LBL_TOTAL_SUM_OF';
			case 'AVG' : return 'LBL_AVG_OF';
			case 'MIN' : return 'LBL_MIN_OF';
			case 'MAX' : return 'LBL_MAX_OF';
		}
	}

	/**
	 * Function returns translated label for the field from report label
	 * Report label format MODULE_FIELD_LABEL eg:Leads_Lead_Source
	 * @param <String> $column
	 */
	function getTranslatedLabelFromReportLabel($column) {
		$columnLabelInfo = explode('_', trim($column, '`'));
		$columnLabelInfo = array_diff($columnLabelInfo, array('SUM','MIN','MAX','AVG')); // added to remove aggregate functions from the graph labels
		return vtranslate(implode(' ', array_slice($columnLabelInfo, 1)), $columnLabelInfo[0]);
	}

	/**
	 * Function returns primary module of the report
	 * @return <String>
	 */
	function getPrimaryModule() {
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();
		$primaryModule = $reportModel->getPrimaryModule();
		return $primaryModule;
	}

	/**
	 * Function returns list view url of the Primary module
	 * @return <String>
	 */
	function getBaseModuleListViewURL() {
		$primaryModule = $this->getPrimaryModule();
		$primaryModuleModel = Vtiger_Module_Model::getInstance($primaryModule);
		$listURL = $primaryModuleModel->getListViewUrlWithAllFilter();

		return $listURL;
	}

	abstract function generateData();

	function getQuery() {
		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();

		$this->reportRun = ReportRun::getInstance($reportModel->getId());
		$advFilterSql = $reportModel->getAdvancedFilterSQL();

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();

		if(is_array($queryColumnsByFieldModel)) {
			foreach($queryColumnsByFieldModel as $field) {
				$this->reportRun->queryPlanner->addTable($field->get('table'));
				$columns[] = $field->get('reportcolumn');
			}
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		if(is_array($groupByColumnsByFieldModel)) {
			foreach($groupByColumnsByFieldModel as $groupField) {
				/**
				 *  In ReportRun getQueryColumnsList(), we are not adding any secondary module tables
				 *  to query planner unless any column is selected from that table. We need to handle 
				 *  this here if it is selected in group by
				 */
				$fieldModule = $groupField->getModule();
				$this->reportRun->queryPlanner->addTable($fieldModule->basetable);
				$this->reportRun->queryPlanner->addTable($groupField->get('table'));
				$groupByColumns[] = "`".$groupField->get('reportlabel')."`"; // to escape special characters
				$columns[] = $groupField->get('reportcolumn');
			}
		}

		$sql = split(' from ', $this->reportRun->sGetSQLforReport($reportModel->getId(), $advFilterSql, 'PDF'), 2);

		$columnLabels = array();

		$chartSQL = "SELECT ";
		if($this->isRecordCount()) {
			$chartSQL .= " count(*) AS RECORD_COUNT,";
		}

		// Add other columns
		if($columns && is_array($columns)) {
			$columnLabels = array_merge($columnLabels, (array)$groupByColumns);
			$chartSQL .= implode(',', $columns);
		}

		$chartSQL .= " FROM $sql[1] ";

		if($groupByColumns && is_array($groupByColumns)) {
			$chartSQL .= " GROUP BY " . implode(',', $groupByColumns);
		}
		return $chartSQL;
	}

	/**
	 * Function generate links
	 * @param <String> $field - fieldname
	 * @param <Decimal> $value - value
	 * @return <String>
	 */
	function generateLink($field, $value) {
		$reportRunObject= $this->getReportRunObject();

		$chartModel = $this->getParent();
		$reportModel = $chartModel->getParent();

		$filter = $reportRunObject->getAdvFilterList($reportModel->getId(), true);

		// Special handling for date fields
		$comparator = 'e';
		$dataFieldInfo = @explode(':', $field);
		if(($dataFieldInfo[4] == 'D' || $dataFieldInfo[4] == 'DT') && !empty($dataFieldInfo[5])) {
			$dataValue = explode(' ',$value);
			if(count($dataValue) > 1) {
				$comparator = 'bw';
				if($dataFieldInfo[4] == 'D') {
					$value = date('Y-m-d', strtotime($value)).','.date('Y-m-d', strtotime('last day of'.$value));
				} else {
					$value = date('Y-m-d H:i:s' ,strtotime($value)).','.date('Y-m-d' ,strtotime('last day of'.$value)).' 23:59:59';
				}
			} else {
				$comparator = 'bw';
				if($dataFieldInfo[4] == 'D') {
					$value = date('Y-m-d', strtotime('first day of JANUARY '.$value)).','.date('Y-m-d', strtotime('last day of DECEMBER '.$value));
				} else {
					$value = date('Y-m-d H:i:s' ,strtotime('first day of JANUARY '.$value)).','.date('Y-m-d' ,strtotime('last day of DECEMBER '.$value)).' 23:59:59';
				}
			}
		} elseif($dataFieldInfo[4] == 'DT') {
			$value = Vtiger_Date_UIType::getDisplayDateTimeValue($value);
		}

		if(empty($value)) {
			$comparator = 'empty';
		}

		$advancedFilterConditions = $reportModel->transformToNewAdvancedFilter();
		//Step 1. Add the filter condition for the field
		if(count($advancedFilterConditions[1]['columns']) < 1) {
			//If count is less than 1 that means there is only ANY conditions in report. There is no ALL conditions selected.
			$groupCondition = array();
			$groupCondition['columns'][] = array(
				'columnname' => $field,
				'comparator' => $comparator,
				'value' => $value,
				'column_condition' => ''
			);
			array_unshift($filter, $groupCondition);
		} else {
			$filter[1]['columns'][] = array(
				'columnname' => $field,
				'comparator' => $comparator,
				'value' => $value,
				'column_condition' => ''
			);
		}

		//Step 2. Convert report field format to normal field names
		foreach($filter as $index => $filterInfo) {
			foreach($filterInfo['columns'] as $i => $column) {
				if($column) {
					$fieldInfo = @explode(':', $column['columnname']);
					$filter[$index]['columns'][$i]['columnname'] = $fieldInfo[3];
				}
			}
		}

		//Step 3. Convert advanced filter format to list view search format
		$listSearchParams = array();
		$i=0;
		if($filter) {
			foreach($filter as $index => $filterInfo) {
				foreach($filterInfo['columns'] as $j => $column) {
					if($column) {
						$listSearchParams[$i][] = array($column['columnname'], $column['comparator'], urlencode(escapeSlashes($column['value'])));
					}
				}
				$i++;
			}
		}
		//Step 4. encode and create the link
		$baseModuleListLink = $this->getBaseModuleListViewURL();
		return $baseModuleListLink.'&search_params='. json_encode($listSearchParams).'&nolistcache=1';
	}

	/**
	 * Function generates graph label
	 * @return <String>
	 */
	function getGraphLabel() {
		return $this->getReportModel()->getName();
	}

	public function getDataTypes() {
		$chartModel = $this->getParent();
		$selectedDataFields = $chartModel->get('datafields');
		$dataTypes = array();
		foreach ($selectedDataFields as $dataField) {
			list($tableName, $columnName, $moduleField, $fieldName, $single) = split(':', $dataField);
			list($relModuleName, $fieldLabel) = split('_', $moduleField);
			$relModuleModel = Vtiger_Module_Model::getInstance($relModuleName);
			$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $relModuleModel);
			if ($fieldModel) {
				$dataTypes[] = $fieldModel->getFieldDataType();
			} else {
				$dataTypes[] = '';
			}
		}
		return $dataTypes;
	}
}

class PieChart extends Base_Chart {

	function generateData(){
		$db = PearDatabase::getInstance();
		$values = array();

		$chartSQL = $this->getQuery();
		$result = $db->pquery($chartSQL, array());
		$rows = $db->num_rows($result);

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();
		if(is_array($queryColumnsByFieldModel)) {
			foreach($queryColumnsByFieldModel as $field) {
				$sector  = strtolower($field->get('reportlabel'));
				$sectorField = $field;
			}
		}

		if($this->isRecordCount()) {
			$sector = strtolower('RECORD_COUNT');
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();

		if(is_array($groupByColumnsByFieldModel)) {
			foreach($groupByColumnsByFieldModel as $groupField) {
				$legend = $groupByColumns[] = $groupField->get('reportlabel');
				$legendField = $groupField;
			}
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = getCurrencySymbolandCRate($currentUserModel->currency_id);

		if(($legendField->getFieldDataType() == 'picklist' || $legendField->getFieldDataType() == 'multipicklist') && vtws_isRoleBasedPicklist($legendField->getName())){
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$picklistvaluesmap = getAssignedPicklistValues($legendField->getName(),$currentUserModel->getRole(), $db);
		}

		$sector = trim($sector, '`'); // remove backticks from sector
		for($i = 0; $i < $rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row[1]=  decode_html($row[1]);
			//translate picklist and multiselect picklist values
			if ($legendField) {
				$fieldDataType = $legendField->getFieldDataType();
				if ($fieldDataType == 'picklist') {
					if(vtws_isRoleBasedPicklist($legendField->getName()) && !in_array($row[1], $picklistvaluesmap)) continue;
					$label = vtranslate($row[strtolower($legend)], $legendField->getModuleName());
				} else if ($fieldDataType == 'multipicklist') {
					$multiPicklistValue = $row[strtolower($legend)];
					$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
					foreach($multiPicklistValues as $multiPicklistValue) {
						$labelList[] = vtranslate($multiPicklistValue, $legendField->getModuleName());
					}
					$label = implode(',', $labelList);
					unset($labelList);
				} else if ($fieldDataType == 'date') {
					if($row[strtolower($legendField->get('reportlabel'))]) {
						$groupByDataField = explode(':', $this->getParent()->getGroupByField());
						if ($groupByDataField[5] == 'M' || $groupByDataField[5] == 'Y' || $groupByDataField[5] == 'MY') {
							$label = $row[strtolower($legendField->get('reportlabel'))];
						} else {
							$label = Vtiger_Date_UIType::getDisplayDateValue($row[strtolower($legendField->get('reportlabel'))]);
						}
					} else {
						$label = '--';
					}

				} else if ($fieldDataType == 'datetime') {
					if($row[strtolower($legendField->get('reportlabel'))]) {
						$groupByDataField = explode(':', $this->getParent()->getGroupByField());
						if ($groupByDataField[5] == 'M' || $groupByDataField[5] == 'Y' || $groupByDataField[5] == 'MY') {
							$label = $row[strtolower($legendField->get('reportlabel'))];
						} else {
							$label = Vtiger_Date_UIType::getDisplayDateTimeValue($row[strtolower($legendField->get('reportlabel'))]);
						}
					} else {
						$label = '--';
					}
				} else {
					$label = $row[strtolower($legend)];
				}
			} else {
				$label = $row[strtolower($legend)];
			}
			$label = decode_html($label);
			$labels[] = (mb_strlen($label, 'UTF-8') > 30) ? mb_substr($label, 0, 30, 'UTF-8').'..' : $label;
			$links[] = $this->generateLink($legendField->get('reportcolumninfo'), $row[strtolower($legend)]);
			$value = (float) $row[$sector];

			if(!$this->isRecordCount()) {
				if($sectorField) {
					if($sectorField->get('uitype') == '71' || $sectorField->get('uitype') == '72') {	//convert currency fields
						$value = (float) ($row[$sector]);
						$value =  CurrencyField::convertFromDollar($value, $currencyRateAndSymbol['rate']);
					} else if($sectorField->getFieldDataType() == 'double') {
						$value = (float) ($row[$sector]);
					} else {
						$value =  (int) $sectorField->getDisplayValue($row[$sector]);
					}
				}
			}

			$values[] = $value;
		}
		$data = array(	'labels' => $labels,
						'values' => $values,
						'links' => $links,
						'graph_label' => $this->getGraphLabel()
					);
		return $data;
	}
}

class VerticalbarChart extends Base_Chart {
	function generateData() {
		$db = PearDatabase::getInstance();
		$chartSQL = $this->getQuery();

		$result = $db->pquery($chartSQL, array());
		$rows = $db->num_rows($result);
		$values = array();

		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();

		$recordCountLabel = '';
		if($this->isRecordCount()) {
			$recordCountLabel = 'RECORD_COUNT';
		}

		$groupByColumnsByFieldModel = $this->getGroupbyColumnsByFieldModel();
		foreach($groupByColumnsByFieldModel as $eachGroupByField) {
			if($eachGroupByField->getFieldDataType() == 'picklist' && vtws_isRoleBasedPicklist($eachGroupByField->getName())){
				$currentUserModel = Users_Record_Model::getCurrentUserModel();
				$picklistValueMap[$eachGroupByField->getName()] = getAssignedPicklistValues($eachGroupByField->getName(),$currentUserModel->getRole(), $db);
			}
		}
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$currencyRateAndSymbol = getCurrencySymbolandCRate($currentUserModel->currency_id);
		$links = array();
		$j=-1;
		for($i = 0; $i < $rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			if ($groupByColumnsByFieldModel) {
				foreach ($groupByColumnsByFieldModel as $gFieldModel) {
					$fieldDataType = $gFieldModel->getFieldDataType();
					if ($fieldDataType == 'picklist') {
						$picklistValue=$row[strtolower($gFieldModel->get('reportlabel'))];
						if(vtws_isRoleBasedPicklist($gFieldModel->getName())){
							if(!in_array(decode_html($picklistValue), $picklistValueMap[$gFieldModel->getName()])){
								continue;
							}
						}
						$label = vtranslate($picklistValue, $gFieldModel->getModuleName());
					} else if ($fieldDataType == 'multipicklist') {
						$multiPicklistValue = $row[strtolower($gFieldModel->get('reportlabel'))];
						$multiPicklistValues = explode(' |##| ', $multiPicklistValue);
						foreach ($multiPicklistValues as $multiPicklistValue) {
							$labelList[] = vtranslate($multiPicklistValue, $gFieldModel->getModuleName());
						}
						$label = implode(',', $labelList);
						unset($labelList);
					} else if ($fieldDataType == 'date') {
						if($row[strtolower($gFieldModel->get('reportlabel'))] != null) {
							$groupByDataField = explode(':', $this->getParent()->getGroupByField());
							if ($groupByDataField[5] == 'M' || $groupByDataField[5] == 'Y' || $groupByDataField[5] == 'MY') {
								$label = $row[strtolower($gFieldModel->get('reportlabel'))];
							} else {
								$label = Vtiger_Date_UIType::getDisplayDateValue($row[strtolower($gFieldModel->get('reportlabel'))]);
							}
						} else {
							$label = '--';
						}
					} else if ($fieldDataType == 'datetime') {
						if($row[strtolower($gFieldModel->get('reportlabel'))] != null) {
							$groupByDataField = explode(':', $this->getParent()->getGroupByField());
							if ($groupByDataField[5] == 'M' || $groupByDataField[5] == 'Y' || $groupByDataField[5] == 'MY') {
								$label = $row[strtolower($gFieldModel->get('reportlabel'))];
							} else {
								$label = Vtiger_Date_UIType::getDisplayDateValue($row[strtolower($gFieldModel->get('reportlabel'))]);
							}
						} else {
							$label = '--';
						}
					} else {
						$label = $row[strtolower($gFieldModel->get('reportlabel'))];
					}
					$j++;
					$label = decode_html($label);
					$labels[] = (mb_strlen($label, 'UTF-8') > 30) ? mb_substr($label, 0, 30, 'UTF-8').'..' : $label;
					$links[] = $this->generateLink($gFieldModel->get('reportcolumninfo'), $row[strtolower($gFieldModel->get('reportlabel'))]);
					if($recordCountLabel) {
						$values[$j][] = (int) $row[strtolower($recordCountLabel)];
					}

					if($queryColumnsByFieldModel) {
						foreach($queryColumnsByFieldModel as $fieldModel) {
							if($fieldModel->get('uitype') == '71' || $fieldModel->get('uitype') == '72') {
								$reportLabel = trim(strtolower($fieldModel->get('reportlabel')),'`'); // remove backticks
								$value = (float) ($row[$reportLabel]);
								$values[$j][] = CurrencyField::convertFromDollar($value, $currencyRateAndSymbol['rate']);
							} else if($fieldModel->getFieldDataType() == 'double') {
								$reportLabel = trim(strtolower($fieldModel->get('reportlabel')),'`'); // remove backticks
								$values[$j][] = (float) $row[$reportLabel];
							} else {
								$reportLabel = trim(strtolower($fieldModel->get('reportlabel')),'`'); // remove backticks
								$values[$j][] = (int) $row[$reportLabel];
							}
						}
					}
				}
			}
		}

		$data = array(	'labels' => $labels,
						'values' => $values,
						'links' => $links,
						'type' => (count($values[0]) == 1) ? 'singleBar' : 'multiBar',
						'data_labels' => $this->getDataLabels(),
						'data_type' => $this->getDataTypes(),
						'graph_label' => $this->getGraphLabel()
					);
		$groupByFiledInfo = $this->getParent()->getGroupByField();
		$groupByFieldType = explode(':', $groupByFiledInfo);
		// to check for month order
		if(!empty($groupByFieldType[5]) && ($groupByFieldType[5] == 'MY' || $groupByDataField[5] == 'M')) { 
			$data = $this->sortReportByMonth($data);
		}
		return $data;
	}

	function getDataLabels() {
		$dataLabels = array();
		if($this->isRecordCount()) {
			$dataLabels[] = vtranslate('LBL_RECORD_COUNT', 'Reports');
		}
		$queryColumnsByFieldModel = $this->getQueryColumnsByFieldModel();
		if($queryColumnsByFieldModel) {
			foreach($queryColumnsByFieldModel as $fieldModel) {
				$fieldTranslatedLabel = $this->getTranslatedLabelFromReportLabel($fieldModel->get('reportlabel'));
				$reportColumn = $fieldModel->get('reportcolumninfo');
				$reportColumnInfo = explode(':', $reportColumn);

				$aggregateFunction = $reportColumnInfo[5];
				$aggregateFunctionLabel = $this->getAggregateFunctionLabel($aggregateFunction);

				$dataLabels[] = vtranslate($aggregateFunctionLabel, 'Reports', $fieldTranslatedLabel);
			}
		}
		return $dataLabels;
	}

	/**
	 * Functin to sort the report data by month order
	 * @param type $data
	 * @return type
	 */
	function sortReportByMonth($data) {
		$sortedLabels = array();
		$sortedValues = array();
		$sortedLinks = array();
		$years = array();
		$mOrder = array("January" => 0,"February" => 1,"March" => 2, "April" => 3, "May" => 4, "June" => 5,"July" => 6,"August" => 7,"September" => 8,"October" => 9,"November" => 10,"December" => 11);
		foreach($data['labels'] as $key=>$label) {
			list($month, $year) = explode(' ', $label);
			if(!empty($year)) {
				$indexes =  $years[$year];
				if(empty($indexes)) {
					$indexes = array();
					$indexes[$mOrder[$month]] = $key;
					$years[$year] = $indexes; 
				} else {
					$indexes[$mOrder[$month]] = $key;
					$years[$year] = $indexes;
				}
			} else if ($label == '--'){
				$indexes =  $years['unknown'];
				if(empty($indexes)) {
					$indexes = array();
					$indexes[] = $key;
					$years['unknown'] = $indexes; 
				} else {
					die;
					$indexes[] = $key;
					$years['unknown'] = $indexes;
				}
			} else {
				break;
			}
		}

		if(!empty($years)) {
			ksort($years);
			foreach ($years as $indexes) {
				ksort($indexes); // to sort according to the index
				foreach($indexes as $index) {
					$sortedLabels[] = $data['labels'][$index];
					$sortedValues[] = $data['values'][$index];
					$sortedLinks[] = $data['links'][$index];
				}
			}

		} else {
			$indexes = array();
			foreach ($data['labels'] as $key=>$label) {
				if(isset($mOrder[$label])) {
					$indexes[$mOrder[$label]] = $key;
				} else {
					$indexes['unknown'] = $key;
				}
			}

			ksort($indexes);
			foreach ($indexes as $index) {
				$sortedLabels[] = $data['labels'][$index];
				$sortedValues[] = $data['values'][$index];
				$sortedLinks[] = $data['links'][$index];
			}
		}

		$data['labels'] = $sortedLabels;
		$data['values'] = $sortedValues;
		$data['links'] = $sortedLinks;

		return $data;
	}
}

class HorizontalbarChart extends VerticalbarChart {

}

class LineChart extends VerticalbarChart{

}