<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_FilterRecordStructure_Model extends Vtiger_FilterRecordStructure_Model {

	/**
	 * Function to get the fields & reference fields in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		if (!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$baseModuleModel = $moduleModel = $this->getModule();
		$baseModuleName = $baseModuleModel->getName();
		$blockModelList = $moduleModel->getBlocks();
		foreach ($blockModelList as $blockLabel => $blockModel) {
			$fieldModelList = $blockModel->getFields();
			if ($fieldModelList) {
				$values[vtranslate($blockLabel, $baseModuleName)] = array();
				foreach ($fieldModelList as $fieldName => $fieldModel) {
					if ($fieldModel->isViewableInFilterView()) {
						$newFieldModel = clone $fieldModel;
						if ($recordExists) {
							$newFieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$values[vtranslate($blockLabel, $baseModuleName)][$fieldName] = $newFieldModel;
					}
				}
			}
		}

		//All the reference fields should also be sent
		$fields = $moduleModel->getFieldsByType(array('reference'));
		foreach ($fields as $parentFieldName => $field) {
			if ($field->isViewable()) {
				if ($parentFieldName == 'contact_id') {
					continue; // it has multi values so excluding it.
				}
				$referenceModules = $field->getReferenceList();
				foreach ($referenceModules as $refModule) {
					if ($refModule == 'Users') {
						continue;
					}

					$refModuleModel = Vtiger_Module_Model::getInstance($refModule);
					$blockModelList = $refModuleModel->getBlocks();
					$fieldModelList = null;
					foreach ($blockModelList as $blockLabel => $blockModel) {
						$fieldModelList = $blockModel->getFields();
						if ($fieldModelList) {
							if (count($referenceModules) > 1) {
								// block label format : reference field label (modulename) - block label. Eg: Related To (Organization) Address Details
								$newblockLabel = vtranslate($field->get('label'), $baseModuleName).' ('.vtranslate($refModule, $refModule).') - '.vtranslate($blockLabel, $refModule);
							} else {
								$newblockLabel = vtranslate($field->get('label'), $baseModuleName).'-'.vtranslate($blockLabel, $refModule);
							}

							$values[$newblockLabel] = array();
							$fieldModel = $fieldName = null;
							foreach ($fieldModelList as $fieldName => $fieldModel) {
								if ($fieldModel->isViewableInFilterView() && $fieldModel->getDisplayType() != '5') {
									$newFieldModel = clone $fieldModel;
									$name = "($parentFieldName ; ($refModule) $fieldName)";
									$label = vtranslate($field->get('label'), $baseModuleName).'-'.vtranslate($fieldModel->get('label'), $refModule);
									$newFieldModel->set('reference_fieldname', $name)->set('label', $label);
									$values[$newblockLabel][$name] = $newFieldModel;
								}
							}
						}
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}

}