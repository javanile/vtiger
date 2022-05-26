<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTUpdateFieldsTask extends VTTask {

	public $executeImmediately = true;
	//array which contains the focus instances of reference fields 
	private $referenceFieldFocusList = array();

	public function getFieldNames() {
		return array('field_value_mapping');
	}

	public function doTask($entity) {
		$tmpFiles = $_FILES;
		unset($_FILES);
		global $adb, $current_user, $default_charset,$default_timezone;
		$referenceModuleUpdated = array();
		$util = new VTWorkflowUtils();
		$util->adminUser();

		$moduleName = $entity->getModuleName();
		$entityId = $entity->getId();
		$recordId = vtws_getIdComponents($entityId);
		$recordId = $recordId[1];

		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $current_user);
		$handlerMeta = $moduleHandler->getMeta();
		$moduleFields = $handlerMeta->getModuleFields();

		$fieldValueMapping = array();
		if (!empty($this->field_value_mapping)) {
			$fieldValueMapping = Zend_Json::decode($this->field_value_mapping);
		}

		$referenceFieldFocus = array();
		if (!empty($fieldValueMapping) && count($fieldValueMapping) > 0) {
			require_once('data/CRMEntity.php');
			$focus = CRMEntity::getInstance($moduleName);
			$focus->id = $recordId;
			$focus->mode = 'edit';
			$focus->retrieve_entity_info($recordId, $moduleName);
			$focus->clearSingletonSaveFields();

			$util->loggedInUser();

			// The data is transformed from db format to user format, there is a chance that date and currency fields might get tracked for changes which should be avoided
			$focus->column_fields->pauseTracking();
			$focus->column_fields = DataTransform::sanitizeDateFieldsForInsert($focus->column_fields,$handlerMeta);
			$focus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($focus->column_fields,$handlerMeta);
			$entityFields = $referenceEntityFields = false;
			$focus->column_fields->resumeTracking();

			foreach ($fieldValueMapping as $fieldInfo) {
				$fieldName = $fieldInfo['fieldname'];
				$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
				if ($moduleModel && in_array($fieldName, $moduleModel->getNameFields())) {
					$entityFields = true;
				}
				$fieldValueType = $fieldInfo['valuetype'];
				$fieldValue = trim($fieldInfo['value']);
				preg_match('/\((\w+) : \((\w+)\) (\w+)\)/',$fieldName,$matches);
				if(count($matches) > 0){
					//reference field update
					$referenceField = $matches[1];
					$referencedModule = $matches[2];
					$referencedFieldName = $matches[3];
					$refModuleModel = Vtiger_Module_Model::getInstance($referencedModule);
					if ($refModuleModel && in_array($referencedFieldName,$refModuleModel->getNameFields())) {
						$referenceEntityFields = true;
					}
					$referenceRecordId = $focus->column_fields[$referenceField];
					if(empty($referenceRecordId) || isRecordExists($referenceRecordId) === false || 
							getSalesEntityType($referenceRecordId) !== $referencedModule){ // if the record belongs to other module, for ex in events we have related to field
						//if no value exists for the reference field then we dont have to update
						//if reference record is deleted then we dont have to update
						continue;
					}
					$referenceRecordFocus = $this->getReferenceFieldFocus($referencedModule, $referenceField, $referenceRecordId);

					$moduleFocus = $referenceRecordFocus;

					$referenceModuleHandler = vtws_getModuleHandlerFromName($referencedModule, $current_user);
					$referenceHandlerMeta = $referenceModuleHandler->getMeta();
					$referenceModuleFields = $referenceHandlerMeta->getModuleFields();
					$fieldInstance = $referenceModuleFields[$referencedFieldName];
					$referenceModuleUpdated[$referencedModule][$referenceField] = $referenceRecordId;
					$fieldName = $referencedFieldName;
				}else{
					$moduleFocus = $focus;
					$fieldInstance = $moduleFields[$fieldName];
				}

				if ($fieldValueType == 'fieldname') {
					$fieldDataType = $fieldInstance->getFieldDataType();
					preg_match('/\((\w+) : \((\w+)\) (\w+)\)/',$fieldValue,$matches1);
					if(count($matches1) > 0){
						$referenceField = $matches1[1];
						$referencedModule = $matches1[2];
						$referencedFieldName = $matches1[3];
						$referenceRecordId = $focus->column_fields[$referenceField];
						if(empty($referenceRecordId)){
							//if no value exists for the reference field then we dont have to update
							continue;
						}else{
							$referenceFocus = $this->getReferenceFieldFocus($referencedModule, $referenceField, $referenceRecordId);
							$fieldValue= $referenceFocus->column_fields[$referencedFieldName];

							// while getting the focus currency fields are converted to user format
							$currencyfieldValueInDB = $referenceFocus->column_fields[$referencedFieldName . "_raw"];
							$currencyfieldConvertedValue = $referenceFocus->column_fields[$referencedFieldName . "_raw_converted"];

							$rightOperandReferenceModuleHandler = vtws_getModuleHandlerFromName($referencedModule, $current_user);
							$rightOperandReferenceHandlerMeta = $rightOperandReferenceModuleHandler->getMeta();
							$rightOperandReferenceModuleFields = $rightOperandReferenceHandlerMeta->getModuleFields();
							$rightOperandFieldInstance = $rightOperandReferenceModuleFields[$referencedFieldName];
							if($rightOperandFieldInstance){
								$rightOperandFieldType = $rightOperandFieldInstance->getFieldDataType();
							}
						}
					}else{
						$rightOperandFieldInstance = $moduleFields[$fieldValue];
						if($rightOperandFieldInstance) {
							$rightOperandFieldType = $rightOperandFieldInstance->getFieldDataType();
						}

						// while getting the focus currency fields are converted to user format
						$currencyfieldValueInDB = $focus->column_fields[$fieldValue . "_raw"];
						$currencyfieldConvertedValue = $focus->column_fields[$fieldValue . "_raw_converted"];

						$fieldValue = $focus->column_fields[$fieldValue];
					}
					$fieldValueInDB = $fieldValue;

					// for currency field value should be in database format
					if (!empty($currencyfieldValueInDB) && $fieldDataType == "currency" && $fieldInstance->getUIType() != 72) {
						$fieldValueInDB = $currencyfieldValueInDB;
						if (!empty($currencyfieldConvertedValue)) {
							$fieldValue = $currencyfieldConvertedValue;
						}
					}

					if($fieldDataType == 'date' && !empty($fieldValue)) {
						$dbDateValue = getValidDBInsertDateTimeValue($fieldValue);
						//Convert the DB Date Time Format to User Date Time Format
						$dateTime = new DateTimeField($dbDateValue);
						$fieldValue = $dateTime->getDisplayDateTimeValue();

						$date = explode(' ', $fieldValue);
						$fieldValue = $date[0];
					}
					//for Product Unit Price value converted with based product currency
					if($fieldDataType == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					}
					// for calendar time_start field db value will be in UTC format, we should convert to user format
					if(trim($fieldInfo['value']) == 'time_start' && $moduleName == 'Calendar' && $fieldDataType == 'time' && !empty($fieldValue)){
						$date = new DateTime();
						$dateTime = new DateTimeField($date->format('Y-m-d').' '. $fieldValue);
						$fieldValue = $dateTime->getDisplayTime();
					} else if ($fieldDataType == 'time' && !empty($fieldValue)) {
						$fieldValueInstance = $moduleFields[trim($fieldInfo['value'])];
						if ($fieldValueInstance) {
							$fieldValueDataType = $fieldValueInstance->getFieldDataType();
						}
						//If time field is updating by datetime field then we have to convert to user format
						if ($fieldValueDataType == 'datetime') {
							$date = new DateTime();
							$dateTime = new DateTimeField($fieldValue);
							$fieldValue = $dateTime->getDisplayTime();
						}
					}

					if($rightOperandFieldType == "reference") {
						if(!empty($fieldValue)) {
							if(!empty($rightOperandFieldInstance)){
								$referenceList = $rightOperandFieldInstance->getReferenceList();
								if((count($referenceList) == 1) && $referenceList[0] == "Users") {
									$userRecordLabels = Vtiger_Functions::getOwnerRecordLabels($fieldValue);
									$fieldValue = $userRecordLabels[$fieldValue];
								}elseif((count($referenceList) == 1) && $referenceList[0] == "Currency"){
									$fieldValue = getCurrencyName($fieldValue);
								}elseif($rightOperandFieldInstance->getFieldName()=="roleid"){
									$fieldValue = getRoleName($fieldValue);
								}else{
									$fieldValue = Vtiger_Util_Helper::getRecordName($fieldValue);
								}
							}else{
								$fieldValue = Vtiger_Util_Helper::getRecordName($fieldValue);
							}
						}else{
							//Not value is there for reference fields . So skip this field mapping
							continue;
						}
					}
					// End
				} elseif ($fieldValueType == 'expression') {
					require_once 'modules/com_vtiger_workflow/expression_engine/include.inc';
					//Added to generate date value in user timezone.
					date_default_timezone_set($current_user->time_zone);
					$parser = new VTExpressionParser(new VTExpressionSpaceFilter(new VTExpressionTokenizer($fieldValue)));
					$expression = $parser->expression();
					$exprEvaluater = new VTFieldExpressionEvaluater($expression);
					$exprEvaluater->fieldInstance = $fieldInstance;
					$fieldValue = $exprEvaluater->evaluate($entity);
					$fieldValueInDB = $fieldValue;

					date_default_timezone_set($default_timezone);
					//for Product Unit Price value converted with based product currency
					if($fieldInstance && $fieldInstance->getFieldDataType() == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					}else{
						$fieldValue = $this->convertValueToUserFormat($fieldInstance, $fieldValue);
					}
				} else {
					if (preg_match('/([^:]+):boolean$/', $fieldValue, $match)) {
						$fieldValue = $match[1];
						if ($fieldValue == 'true') {
							$fieldValue = '1';
						} else {
							$fieldValue = '0';
						}
					}
					//for Product Unit Price value converted with based product currency
					if($fieldInstance && $fieldInstance->getFieldDataType() == 'currency' && $fieldName == 'unit_price') {
						$fieldValue = $this->calculateProductUnitPrice($fieldValue);
					}
					$fieldValueInDB = $fieldValue;
				}

				if ($fieldInstance && $fieldInstance->getFieldDataType() === 'owner') {
					if(!is_numeric($fieldValue)){
						//If name is given
						$userId = getUserId_Ol($fieldValue);
						$groupId = getGrpId($fieldValue);

						if ($userId == 0 && $groupId == 0) {
							$fieldValue = $moduleFocus->column_fields[$fieldName];
						} else {
							$fieldValue = ($userId == 0) ? $groupId : $userId;
						}

						if($userId == 0) {
							$webserviceObject = VtigerWebserviceObject::fromName($adb,'Groups');
							$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(),$fieldValue);
						} else {
							$webserviceObject = VtigerWebserviceObject::fromName($adb,'Users');
							$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(),$fieldValue);
						}
					} else {
						$ownerType = vtws_getOwnerType($fieldValue);
						$webserviceObject = VtigerWebserviceObject::fromName($adb,$ownerType);
						$fieldValueInDB = vtws_getId($webserviceObject->getEntityId(),$fieldValue);
					}
				}

				if ($fieldInstance && $fieldInstance->getFieldDataType() === 'multipicklist') {
					$valueArray = explode(',', $fieldValue);
					$fieldValue = implode(' |##| ', $valueArray);
					$fieldValueInDB = $fieldValue;
				}

				if($fieldInstance && $fieldInstance->getFieldDataType() == 'reference') {
						$referenceModuleList = $fieldInstance->getReferenceList();
						$fieldReferenceModule = $referenceModuleList[0];
						$recordId = Vtiger_Util_Helper::getRecordId($fieldValue, $fieldReferenceModule,true);
						if(!empty($recordId)) {
							$fieldValue = $recordId;
						} else {
							$fieldValue = '';
						}
					}

				$moduleFocus->column_fields[$fieldName] = $fieldValue;
				if (count($matches) > 0) {
					$wsReferenceRecordId = vtws_getWebserviceEntityId($referencedModule, $referenceRecordId);
					$referenceRecordEntity = VTEntityCache::getCachedEntity($wsReferenceRecordId);
					if (empty($referenceRecordEntity)) {
						$referenceEntity = new VTEntityCache($current_user);
						$referenceRecordEntity = $referenceEntity->forId($wsReferenceRecordId);
						VTEntityCache::setCachedEntity($wsReferenceRecordId, $referenceRecordEntity);
					}
					$referenceRecordEntity->set($referencedFieldName, $fieldValueInDB);
					// to set the current reference record id. so that if there any field which will use current field name will get updated value
					// (account_id : (Accounts) phone) = '5433'  updating accounts phone to 5433
					// phone = (account_id : (Accounts) phone)  contacts phone = accounts phone . Here accounts phone need to have the updated value
					VTEntityCache::setCachedEntity($wsReferenceRecordId, $referenceRecordEntity);
				} else {
					$entity->data[$fieldName] = $fieldValueInDB;
				}
			}
			foreach ($focus->column_fields as $fieldName => $fieldValue) {
				$focus->column_fields[$fieldName] = html_entity_decode($fieldValue, ENT_QUOTES, $default_charset);
			}
			$_REQUEST['file'] = '';
			$_REQUEST['ajxaction'] = '';

			// Added as Mass Edit triggers workflow and date and currency fields are set to user format
			// When saving the information in database saveentity API should convert to database format
			// and save it. But it converts in database format only if that date & currency fields are
			// changed(massedit) other wise they wont be converted thereby changing the values in user
			// format, CRMEntity.php line 474 has the login to check wheather to convert to database format
			$actionName = $_REQUEST['action'];
			$_REQUEST['action'] = '';

			// For workflows update field tasks is deleted all the lineitems.
			$focus->isLineItemUpdate = false;

			//For workflows we need to update all the fields irrespective if the logged in user has the
			//permission or not.
			$focus->isWorkFlowFieldUpdate = true;
			$changedFields = $focus->column_fields->getChanged();
			if(count($changedFields) > 0){
				// save only if any field is changed
				$focus->saveentity($moduleName);
			}
			if ($entityFields == true) {
				Vtiger_Functions::updateCRMRecordLabel($moduleName, $focus->id);
			}
				$referenceIds = array();
			foreach($referenceModuleUpdated as $module => $info){
				foreach($info as $refFieldName => $referenceRecordId){
					$referenceFocus = $this->referenceFieldFocusList[$refFieldName][$referenceRecordId];
						foreach ($referenceFocus->column_fields as $fieldName => $fieldValue) {
							$referenceFocus->column_fields[$fieldName] = html_entity_decode($fieldValue, ENT_QUOTES, $default_charset);
						}
						$referenceFocus->isLineItemUpdate = false;
						$referenceFocus->isWorkFlowFieldUpdate = true;
						$referenceFocus->saveentity($referenceFocus->moduleName);
						if ($referenceEntityFields == true) {
							Vtiger_Functions::updateCRMRecordLabel($referenceFocus->moduleName, $referenceFocus->id);
						}
						$referenceIds[] = $referenceFocus->id;
					}
				}

			// Reverting back the action name as there can be some dependencies on this.
			$_REQUEST['action'] = $actionName;
		}
		$util->revertUser();
		$_FILES = $tmpFiles;
	}

	//Function use to convert the field value in to current user format
	public function convertValueToUserFormat($fieldObj, $fieldValue) {
		global $current_user;
		if(!empty ($fieldObj)) {
			// handle the case for Date field
			if($fieldObj->getFieldDataType()=="date") {
				if(!empty($fieldValue)) {
					$dateFieldObj = new DateTimeField($fieldValue);
					$fieldValue = $dateFieldObj->getDisplayDate($current_user);
				}
			}

			// handle the case for currency field
			if($fieldObj->getFieldDataType()=="currency" && !empty($fieldValue)) {
				if($fieldObj->getUIType() == '71') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue,$current_user,false);
				} else if($fieldObj->getUIType() == '72') {
					$fieldValue = CurrencyField::convertToUserFormat($fieldValue,$current_user,true);
				}
			}
		}
		return $fieldValue;
	}

	/**
	 * Function to calculate Product Unit Price.
	 * Product Unit Price value converted with based product currency
	 * @param type $fieldValue
	 */
	public function calculateProductUnitPrice($fieldValue) {
		$currency_details = getAllCurrencies('all');
		for($i=0;$i<count($currency_details);$i++)  {
			$curid = $currency_details[$i]['curid'];
			$cur_checkname = 'cur_' . $curid . '_check';
			$cur_valuename = 'curname' . $curid;
			if($cur_valuename == $_REQUEST['base_currency'] && ($_REQUEST[$cur_checkname] == 'on' || $_REQUEST[$cur_checkname] == 1)) {
				$fieldValue = $fieldValue * $currency_details[$i]['conversionrate'];
				$_REQUEST[$cur_valuename] = $fieldValue;
			}
		}
		return $fieldValue;
	}

	public function getReferenceFieldFocus($referencedModule,$referenceField,$referenceRecordId){
		global $current_user;
		$referenceRecordFocus = $this->referenceFieldFocusList[$referenceField][$referenceRecordId];
		if(empty($referenceRecordFocus)){
		$referenceRecordFocus = CRMEntity::getInstance($referencedModule);
		$referenceRecordFocus->id = $referenceRecordId;
		$referenceRecordFocus->mode = 'edit';
		if(isRecordExists($referenceRecordId) || $referencedModule=="Users") {
			$referenceRecordFocus->retrieve_entity_info($referenceRecordId, $referencedModule);
		}
		$referenceRecordFocus->clearSingletonSaveFields();

		$referenceModuleHandler = vtws_getModuleHandlerFromName($referencedModule, $current_user);
		$referenceHandlerMeta = $referenceModuleHandler->getMeta();
		$referenceRecordFocus->column_fields->pauseTracking();
		$referenceRecordFocus->column_fields = DataTransform::sanitizeDateFieldsForInsert($referenceRecordFocus->column_fields,$referenceHandlerMeta);
		$referenceRecordFocus->column_fields = DataTransform::sanitizeCurrencyFieldsForInsert($referenceRecordFocus->column_fields,$referenceHandlerMeta);
		$referenceRecordFocus->column_fields->resumeTracking();
			$this->referenceFieldFocusList[$referenceField][$referenceRecordId] = $referenceRecordFocus;
		}
		return $referenceRecordFocus;
	}

}

?>
