<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Create.php';
require_once 'include/Webservices/Delete.php';
require_once 'include/Webservices/DescribeObject.php';
require_once 'includes/Loader.php';
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');

function vtws_convertPotential($entityvalues, $user) {
	global $adb, $log;
	if (empty($entityvalues['assignedTo'])) {
		$entityvalues['assignedTo'] = vtws_getWebserviceEntityId('Users', $user->id);
	}
	if (empty($entityvalues['transferRelatedRecordsTo'])) {
		$entityvalues['transferRelatedRecordsTo'] = 'Project';
	}

	$potentialObject = VtigerWebserviceObject::fromName($adb, 'Potentials');
	$handlerPath = $potentialObject->getHandlerPath();
	$handlerClass = $potentialObject->getHandlerClass();
	require_once $handlerPath;

	$potentialHandler = new $handlerClass($potentialObject, $user, $adb, $log);
	$potentialInfo = vtws_retrieve($entityvalues['potentialId'], $user);
	$sql = 'SELECT converted FROM vtiger_potential WHERE converted=1 AND potentialid=?';
	$potentialIdComponents = vtws_getIdComponents($entityvalues['potentialId']);
	$result = $adb->pquery($sql, array($potentialIdComponents[1]));
	if ($result === false) {
		throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, vtws_getWebserviceTranslatedString('LBL_'.WebServiceErrorCode::$DATABASEQUERYERROR));
	}
	$rowCount = $adb->num_rows($result);
	if ($rowCount > 0) {
		throw new WebServiceException(WebServiceErrorCode::$POTENTIAL_ALREADY_CONVERTED, 'Potential is already converted');
	}

	$entityIds = array();

	$availableModules = array('Project');

	if (!(($entityvalues['entities']['Project']['create']))) {
		return null;
	}

	foreach ($availableModules as $entityName) {
		if ($entityvalues['entities'][$entityName]['create']) {
			$entityvalue = $entityvalues['entities'][$entityName];
			$entityObject = VtigerWebserviceObject::fromName($adb, $entityvalue['name']);
			$handlerPath = $entityObject->getHandlerPath();
			$handlerClass = $entityObject->getHandlerClass();

			require_once $handlerPath;

			$entityHandler = new $handlerClass($entityObject, $user, $adb, $log);

			$entityObjectValues = array();
			$entityObjectValues['assigned_user_id'] = $entityvalues['assignedTo'];
			$entityObjectValues = vtws_populateConvertPotentialEntities($entityvalue, $entityObjectValues, $entityHandler, $potentialHandler, $potentialInfo);

			try {
				$entityObjectValues['isconvertedfrompotential'] = 1;
				$entityRecord = vtws_create($entityvalue['name'], $entityObjectValues, $user);
				$entityIds[$entityName] = $entityRecord['id'];
			} catch (DuplicateException $e) {
				throw $e;
			} catch (Exception $e) {
				throw new WebServiceException(WebServiceErrorCode::$FAILED_TO_CREATE, $e->getMessage().' : '.$entityvalue['name']);
			}
		}
	}

	try {
		vtws_convertPotentialTransferHandler($potentialIdComponents, $entityIds, $entityvalues);
		vtws_updateConvertPotentialStatus($entityIds, $entityvalues['potentialId'], $user);
	} catch (Exception $e) {
		foreach ($entityIds as $entity => $id) {
			vtws_delete($id, $user);
		}
		return null;
	}
	return $entityIds;
}

/*
 * populate the entity fields with the Potential info.
 * if mandatory field is not provided populate with '????'
 * returns the entity array.
 */
function vtws_populateConvertPotentialEntities($entityvalue, $entity, $entityHandler, $potentialHandler, $potentialinfo) {
	global $adb, $log;
	$entityName = $entityvalue['name'];
	$sql = 'SELECT * FROM vtiger_convertpotentialmapping';
	$result = $adb->pquery($sql, array());
	if ($adb->num_rows($result)) {
		$column = 'potentialfid';
		if ($entityName == 'Project') {
			$column = 'projectfid';
		}

		$potentialFields = $potentialHandler->getMeta()->getModuleFields();
		$entityFields = $entityHandler->getMeta()->getModuleFields();
		$row = $adb->fetch_array($result);
		$count = 1;
		do {
			$entityField = vtws_getFieldfromFieldId($row[$column], $entityFields);
			if ($entityField == null) {
				//user doesn't have access so continue.TODO update even if user doesn't have access
				continue;
			}
			$potentialField = vtws_getFieldfromFieldId($row['potentialfid'], $potentialFields);
			if ($potentialField == null) {
				//user doesn't have access so continue.TODO update even if user doesn't have access
				continue;
			}
			$potentialFieldName = $potentialField->getFieldName();
			$entityFieldName = $entityField->getFieldName();
			$entity[$entityFieldName] = $potentialinfo[$potentialFieldName];
			$count++;
		} while ($row = $adb->fetch_array($result));

		foreach ($entityFields as $fieldName => $fieldModel) {
			if (!empty($entityFields[$fieldName]) && $fieldModel->getDefault() && $fieldName != 'isconvertedfrompotential') {
				if (!isset($entityvalue[$fieldName]) && empty($entity[$fieldName])) {
					$entityvalue[$fieldName] = $fieldModel->getDefault();
				}
			}
		}

		foreach ($entityvalue as $fieldname => $fieldvalue) {
			if (!empty($fieldvalue)) {
				$entity[$fieldname] = $fieldvalue;
			}
		}
		$entity['potentialid'] = $potentialinfo['id'];

		$entity = vtws_validateConvertEntityMandatoryValues($entity, $entityHandler, $entityName);
	}
	return $entity;
}

/**
 * function to handle the transferring of related records for Potential
 * @param <Array> $potentialIdComponents - Exploded Webservice Id
 * @param <Array> $entityIds - Converted Project Id
 * @param <Array> $entityvalues - Mapped Potential and Project values
 * @return <Boolean>  
 */
function vtws_convertPotentialTransferHandler($potentialIdComponents, $entityIds, $entityvalues) {
	try {
		$entityidComponents = vtws_getIdComponents($entityIds[$entityvalues['transferRelatedRecordsTo']]);
		vtws_transferPotentialRelatedRecords($potentialIdComponents[1], $entityidComponents[1], $entityvalues['transferRelatedRecordsTo']);
	} catch (Exception $e) {
		return false;
	}
	return true;
}

function vtws_updateConvertPotentialStatus($entityIds, $potentialId, $user) {
	global $adb, $log;
	$potentialIdComponents = vtws_getIdComponents($potentialId);
	if ($entityIds['Project'] != '' || $entityIds['Contacts'] != '') {
		$sql = 'UPDATE vtiger_potential SET converted=1 where potentialid=?';
		$result = $adb->pquery($sql, array($potentialIdComponents[1]));
		if ($result === false) {
			throw new WebServiceException(WebServiceErrorCode::$FAILED_TO_MARK_POTENTIAL_CONVERTED, 'Failed mark potential converted');
		}

		//update the modifiedtime and modified by information for the record
		$potentialModifiedTime = $adb->formatDate(date('Y-m-d H:i:s'), true);
		$crmentityUpdateSql = 'UPDATE vtiger_crmentity SET modifiedtime=?, modifiedby=? WHERE crmid=?';
		$adb->pquery($crmentityUpdateSql, array($potentialModifiedTime, $user->id, $potentialIdComponents[1]));
	}
}
