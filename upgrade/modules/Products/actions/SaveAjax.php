<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		//the new values are added to $_REQUEST for Ajax Save, are removing the Tax details depend on the 'ajxaction' value
		$_REQUEST['action'] = 'MassEditSave';
		$request->set('action', 'MassEditSave');
		vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', $request->get('_timeStampNoChangeMode', false));
		$recordModel = $this->saveRecord($request);
		vglobal('VTIGER_TIMESTAMP_NO_CHANGE_MODE', false);

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = array();
		$picklistColorMap = array();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordFieldValue = $recordModel->get($fieldName);
			if (is_array($recordFieldValue) && $fieldModel->getFieldDataType() == 'multipicklist') {
				foreach ($recordFieldValue as $picklistValue) {
					$picklistColorMap[$picklistValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $picklistValue);
				}
				$recordFieldValue = implode(' |##| ', $recordFieldValue);
			}
			if ($fieldModel->getFieldDataType() == 'picklist') {
				$picklistColorMap[$recordFieldValue] = Settings_Picklist_Module_Model::getPicklistColorByValue($fieldName, $recordFieldValue);
			}
			$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
			if ($fieldName !== 'unit_price' && $fieldModel->getFieldDataType() !== 'datetime'  && $fieldModel->getFieldDataType() !== 'double') {
				$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
			}
			if ($fieldModel->getFieldDataType() == 'reference') {
				$displayValue = $fieldModel->getEditViewDisplayValue($fieldValue);
			}
			if (!empty($picklistColorMap)) {
				$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue, 'colormap' => $picklistColorMap);
			} else {
				$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
			}
		}

		// removed decode_html to eliminate XSS vulnerability
		$result['_recordLabel'] = decode_html($recordModel->getName());
		$result['_recordId'] = $recordModel->getId();
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	function getRecordModelFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$recordModel = parent::getRecordModelFromRequest($request);
		$fieldModelList = $moduleModel->getFields();

		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldDataType = $fieldModel->getFieldDataType();
			// This is added as we are marking massedit in vtiger6 as not an ajax operation
			// and this will force the date fields to be saved in user format. If the user format
			// is other than y-m-d then it fails. We need to review the above process API changes
			// which was added to fix unit price issue where it was getting changed when mass edited.
			if($fieldDataType == 'date' || $fieldDataType == 'currency') {
				$recordModel->set($fieldName, $fieldModel->getUITypeModel()->getDBInsertValue($recordModel->get($fieldName)));
			}
		}
		return $recordModel;
	}
}
