<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_SaveAjax_Action extends Settings_Vtiger_Basic_Action {
    
    function __construct() {
        $this->exposeMethod('add');
        $this->exposeMethod('rename');
        $this->exposeMethod('remove');
        $this->exposeMethod('assignValueToRole');
        $this->exposeMethod('saveOrder');
        $this->exposeMethod('enableOrDisable');
        $this->exposeMethod('edit');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        $this->invokeExposedMethod($mode, $request);
    }
    
    /*
     * @function updates user tables with new picklist value for default event and status fields
     */
    public function updateDefaultPicklistValues($pickListFieldName,$oldValue,$newValue) {
		$db = PearDatabase::getInstance();
            if($pickListFieldName == 'activitytype')
                $defaultFieldName = 'defaultactivitytype';
            else
                $defaultFieldName = 'defaulteventstatus';
            $queryToGetId = 'SELECT id FROM vtiger_users WHERE '.$defaultFieldName.' IN (';
             if(is_array($oldValue)) {
                 for($i=0;$i<count($oldValue);$i++) {
                     $queryToGetId .= '"'.$oldValue[$i].'"';
                     if($i<(count($oldValue)-1)) {
                         $queryToGetId .= ',';
                     }
                 }
                 $queryToGetId .= ')';
             }
             else {
                 $queryToGetId .= '"'.$oldValue.'")';
             }
            $result = $db->pquery($queryToGetId, array());
            $rowCount =  $db->num_rows($result);
            for($i=0; $i<$rowCount; $i++) {
                $recordId = $db->query_result_rowdata($result, $i);
                $recordId = $recordId['id'];
                $record = Vtiger_Record_Model::getInstanceById($recordId, 'Users');
                $record->set('mode','edit');
                $record->set($defaultFieldName,$newValue);
                $record->save();
            }
    }
    
    public function add(Vtiger_Request $request) {
        $newValues = $request->getRaw('newValue');
        $pickListName = $request->get('picklistName');
        $moduleName = $request->get('source_module');
        $selectedColor = $request->get('selectedColor');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListName, $moduleModel);
        $rolesSelected = array();
        if($fieldModel->isRoleBased()) {
            $userSelectedRoles = $request->get('rolesSelected',array());
            //selected all roles option
            if(in_array('all',$userSelectedRoles)) {
                $roleRecordList = Settings_Roles_Record_Model::getAll();
                foreach($roleRecordList as $roleRecord) {
                    $rolesSelected[] = $roleRecord->getId();
                }
            }else{
                $rolesSelected = $userSelectedRoles;
            }
        }
        $response = new Vtiger_Response();
        try{
			$newValuesArray = explode(',', $newValues);
			$result = array();
			foreach($newValuesArray as $i => $newValue) {
				$id = $moduleModel->addPickListValues($fieldModel, trim($newValue), $rolesSelected, $selectedColor);
				$result['id'.$i] = $id['id'];
			}
            $moduleModel->handleLabels($moduleName, $newValuesArray, array(), 'add');
			$response->setResult($result);
        }  catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function rename(Vtiger_Request $request) {
        $moduleName = $request->get('source_module');
        
        $newValue = $request->getRaw('newValue');
        $pickListFieldName = $request->get('picklistName');
        
        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');
        $color = $request->get('selectedColor');
        
        $oldValue = $request->getRaw('oldValue');
		$id = $request->getRaw('id');
        
        if($moduleName == 'Events' && ($pickListFieldName == 'activitytype' || $pickListFieldName == 'eventstatus')) {
             $this->updateDefaultPicklistValues($pickListFieldName,$oldValue,$newValue);
        }   
        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try{
            $status = $moduleModel->renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id, $rolesList, $color);
            $moduleModel->handleLabels($moduleName,$newValue,$oldValue,'rename');
            $response->setResult(array('success',$status));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function remove(Vtiger_Request $request) {
        $moduleName = $request->get('source_module');
        $valueToDelete = $request->getRaw('delete_value');
        $replaceValue = $request->getRaw('replace_value');
        $pickListFieldName = $request->get('picklistName');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $pickListDeleteValue = $moduleModel->getActualPicklistValues($valueToDelete,$pickListFieldName);

		if($moduleName == 'Events' && ($pickListFieldName == 'activitytype' || $pickListFieldName == 'eventstatus')) {
			$db = PearDatabase::getInstance();
			$primaryKey = Vtiger_Util_Helper::getPickListId($pickListFieldName);
			$replaceValueQuery = $db->pquery("SELECT $pickListFieldName FROM ".$moduleModel->getPickListTableName($pickListFieldName)." WHERE $primaryKey IN (".generateQuestionMarks($replaceValue).")",array($replaceValue));
			$actualReplaceValue = decode_html($db->query_result($replaceValueQuery,0,$pickListFieldName));
			$this->updateDefaultPicklistValues($pickListFieldName,$pickListDeleteValue,$actualReplaceValue);
        } 
        $response = new Vtiger_Response();
        try{
            $status = $moduleModel->remove($pickListFieldName, $valueToDelete, $replaceValue, $moduleName);
            $moduleModel->handleLabels($moduleName,array(),$pickListDeleteValue,'delete');
            $response->setResult(array('success',$status));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    /**
     * Function which will assign existing values to the roles
     * @param Vtiger_Request $request
     */
    public function assignValueToRole(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        $valueToAssign = $request->getRaw('assign_values');
        $userSelectedRoles = $request->get('rolesSelected');
        
        $roleIdList = array();
        //selected all roles option
        if(in_array('all',$userSelectedRoles)) {
            $roleRecordList = Settings_Roles_Record_Model::getAll();
            foreach($roleRecordList as $roleRecord) {
                $roleIdList[] = $roleRecord->getId();
            }
        }else{
            $roleIdList = $userSelectedRoles;
        }
        
        $moduleModel = new Settings_Picklist_Module_Model();
        
        $response = new Vtiger_Response();
        try{
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $valueToAssign, array(),$roleIdList);
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function saveOrder(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        
        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');

        $picklistValues = $request->getRaw('picklistValues');
        
        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try{
            $moduleModel->updateSequence($pickListFieldName, $picklistValues, $rolesList);
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function enableOrDisable(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        $enabledValues = $request->getRaw('enabled_values',array());
        $disabledValues = $request->getRaw('disabled_values',array());
        $roleSelected = $request->get('rolesSelected');
        
        $moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
        try{
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $enabledValues, $disabledValues,array($roleSelected));
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
            
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
    
    public function edit(Vtiger_Request $request) {
        $moduleName = $request->get('source_module');
        
        $newValue = $request->getRaw('newValue');
        $pickListFieldName = $request->get('picklistName');
        $nonEditablePicklistValues = Settings_Picklist_Field_Model::getNonEditablePicklistValues($pickListFieldName);
        
        // we should clear cache to update with latest values
        $rolesList = $request->get('rolesList');
        $color = $request->get('selectedColor');
        
        $oldValue = $request->getRaw('oldValue');
		$id = $request->getRaw('id');
        
        if($moduleName == 'Events' && ($pickListFieldName == 'activitytype' || $pickListFieldName == 'eventstatus')) {
             $this->updateDefaultPicklistValues($pickListFieldName,$oldValue,$newValue);
        }
        
        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        if($oldValue != $newValue && empty($nonEditablePicklistValues[$id])) {
            try{
                $status = $moduleModel->renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id, $rolesList, $color);
                $response->setResult(array('success',$status));
            } catch (Exception $e) {
                $response->setError($e->getCode(), $e->getMessage());
            }
        } else {
            if($color) {
                $status = $moduleModel->updatePicklistColor($pickListFieldName, $id, $color);
                $response->setResult(array('success',$status));
            }
        }
        
        $response->emit();
    }
}