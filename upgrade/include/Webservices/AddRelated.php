<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

/**
 * Function to relate CRM records for relationships exists in vtiger_relatedlists table.
 * @param $sourceRecordId - Source record webservice id.
 * @param $relatedRecordId - Related record webservice id(s). One record id or array of ids for same module.
 * @param $relationIdLabel - Relation id or label as in vtiger_relatedlists table.
 * @param $user
 */
function vtws_add_related($sourceRecordId, $relatedRecordId, $relationIdLabel = false, $user = false) {
	$db = PearDatabase::getInstance();
	if (!is_array($relatedRecordId)) {
		$relatedRecordId = array($relatedRecordId);
	}

	$sourceRecordIdParts = vtws_getIdComponents($sourceRecordId);
	$relatedRecordIdParts = vtws_getIdComponents($relatedRecordId[0]);
	if (!isRecordExists($sourceRecordIdParts[1])) {
		throw new Exception("Source record $sourceRecordIdParts is deleted");
	}

	try {
		$sourceRecordWsObject = VtigerWebserviceObject::fromId($db, $sourceRecordIdParts[0]);
		$relatedRecordWsObject = VtigerWebserviceObject::fromId($db, $relatedRecordIdParts[0]);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceRecordWsObject->getEntityName());
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedRecordWsObject->getEntityName());

		$relationLabel = false;
		$relationId = false;
		if (is_numeric($relationIdLabel)) {
			$relationId = $relationIdLabel;
		} else if (!empty($relationIdLabel)) {
			$relationLabel = $relationIdLabel;
		}

		if ($sourceModuleModel && $relatedModuleModel) {
			$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel, $relationLabel, $relationId);
			if ($relationModel) {
				foreach ($relatedRecordId as $id) {
					$idParts = vtws_getIdComponents($id);
					if ($idParts[0] == $relatedRecordIdParts[0]) {
						$relationModel->addRelation($sourceRecordIdParts[1], $idParts[1]);
					}
				}
			}
		}
		return array('message' => 'successfull');
	} catch (Exception $ex) {
		throw new Exception($ex->getMessage());
	}
}
