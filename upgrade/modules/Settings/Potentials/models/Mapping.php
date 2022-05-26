<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Potentials_Mapping_Model extends Settings_Leads_Mapping_Model {
    
    var $name = 'Potentials';

	/**
	 * Function to get headers for detail view
	 * @return <Array> headers list
	 */
	public function getHeaders() {
		return array('Potentials' => 'Potentials', 'Type' => 'Type', 'Projects' => 'Projects');
	}

	/**
	 * Function to get list of detail view link models
	 * @return <Array> list of detail view link models <Vtiger_Link_Model>
	 */
	public function getDetailViewLinks() {
		return array(Vtiger_Link_Model::getInstanceFromValues(array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => 'javascript:Settings_PotentialMapping_Js.triggerEdit("'. $this->getEditViewUrl() .'")',
				'linkicon' => ''
				)));
	}

	/**
	 * Function to get list of mapping link models
	 * @return <Array> list of mapping link models <Vtiger_Link_Model>
	 */
	public function getMappingLinks() {
		return array(Vtiger_Link_Model::getInstanceFromValues(array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Settings_PotentialMapping_Js.triggerDelete(event,"'. $this->getMappingDeleteUrl() .'")',
				'linkicon' => ''
				)));
	}

	/**
	 * Function to get mapping details
	 * @return <Array> list of mapping details
	 */
	public function getMapping($editable = false) {
		if (!$this->mapping) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT * FROM vtiger_convertpotentialmapping';
			if ($editable) {
				$query .= ' WHERE editable = 1';
			}

			$result = $db->pquery($query, array());
			$numOfRows = $db->num_rows($result);
            $mapping = array();
			for ($i=0; $i<$numOfRows; $i++) {
				$rowData = $db->query_result_rowdata($result, $i);
				$mapping[$rowData['cfmid']] = $rowData;
			}

			$finalMapping = $fieldIdsList = array();
			foreach ($mapping as $mappingDetails) {
				array_push($fieldIdsList, $mappingDetails['potentialfid'], $mappingDetails['projectfid']);
			}
            $fieldLabelsList = array();
            if(!empty($fieldIdsList)){
                $fieldLabelsList = $this->getFieldsInfo(array_unique($fieldIdsList));
            }
			foreach ($mapping as $mappingId => $mappingDetails) {
				$finalMapping[$mappingId] = array(
						'editable'	=> $mappingDetails['editable'],
						'Potentials'		=> $fieldLabelsList[$mappingDetails['potentialfid']],
						'Project'	=> $fieldLabelsList[$mappingDetails['projectfid']]
				);
			}

			$this->mapping = $finalMapping;
		}
		return $this->mapping;
	}

	/**
	 * Function to save the mapping info
	 * @param <Array> $mapping info
	 */
	public function save($mapping) {
		$db = PearDatabase::getInstance();
		$deleteMappingsList = $updateMappingsList = $createMappingsList = array();
		foreach ($mapping as $mappingDetails) {
			$mappingId = $mappingDetails['mappingId'];
			if ($mappingDetails['potential']) {
				if ($mappingId) {
					if ((array_key_exists('deletable', $mappingDetails)) || (!$mappingDetails['project'])) {
						$deleteMappingsList[] = $mappingId;
					} else {
						if ($mappingDetails['project']) {
							$updateMappingsList[] = $mappingDetails;
						}
					}
				} else {
					if ($mappingDetails['project']) {
						$createMappingsList[] = $mappingDetails;
					}
				}
			}
		}

		if ($deleteMappingsList) {
			$db->pquery('DELETE FROM vtiger_convertpotentialmapping WHERE editable = 1 AND cfmid IN ('. generateQuestionMarks($deleteMappingsList) .')', $deleteMappingsList);
		}

		if ($createMappingsList) {
			$insertQuery = 'INSERT INTO vtiger_convertpotentialmapping(potentialfid, projectfid) VALUES ';

			$count = count($createMappingsList);
			for ($i=0; $i<$count; $i++) {
				$mappingDetails = $createMappingsList[$i];
				$insertQuery .= '('. $mappingDetails['potential'] .', '. $mappingDetails['project'] .')';
				if ($i !== $count-1) {
					$insertQuery .= ', ';
				}
			}
			$db->pquery($insertQuery, array());
		}

		if ($updateMappingsList) {
			$potentialQuery		= ' SET potentialfid = CASE ';
			$projectQuery	= ' projectfid = CASE ';

			foreach ($updateMappingsList as $mappingDetails) {
				$mappingId		 = $mappingDetails['mappingId'];
				$potentialQuery		.= " WHEN cfmid = $mappingId THEN ". $mappingDetails['potential'];
				$projectQuery	.= " WHEN cfmid = $mappingId THEN ". $mappingDetails['project'];
			}
			$potentialQuery		.= ' ELSE potentialfid END ';
			$projectQuery	.= ' ELSE projectfid END ';

			$db->pquery("UPDATE vtiger_convertpotentialmapping $potentialQuery, $projectQuery WHERE editable = ?", array(1));
		}
	}

	/**
	 * Function to get restricted field ids list
	 * @return <Array> list of field ids
	 */
	public static function getRestrictedFieldIdsList() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_convertpotentialmapping WHERE editable = ?', array(0));
		$numOfRows = $db->num_rows($result);

		$restrictedIdsList = array();
		for ($i=0; $i<$numOfRows; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);
			if ($rowData['projectfid']) {
				$restrictedIdsList[] = $rowData['projectfid'];
			}
		}
		return $restrictedIdsList;
	}

	/**
	 * Function to get mapping supported modules list
	 * @return <Array>
	 */
	public static function getSupportedModulesList() {
		return array('Project');
	}

	/**
	 * Function to delate the mapping
	 * @param <Array> $mappingIdsList
	 */
	public static function deleteMapping($mappingIdsList) {
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_convertpotentialmapping WHERE cfmid IN ('. generateQuestionMarks($mappingIdsList). ')', $mappingIdsList);
	}
    
    /**
	 * Function to get instance
	 * @param <Boolean> true/false
	 * @return <Settings_Potentials_Mapping_Model>
	 */
	public static function getInstance($editable = false) {
		$instance = new self();
		$instance->getMapping($editable);
		return $instance;
	}

	/**
	 * Function to get instance
	 * @return <Settings_Potentials_Mapping_Model>
	 */
	public static function getCleanInstance() {
		return new self();
	}
}