<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class EmailLookupHandler extends VTEventHandler{
    
     
    function handleEvent($eventName, $entityData) {
        $moduleName = $entityData->getModuleName();
        
        if($eventName == 'vtiger.entity.aftersave'){
            EmailLookupHandler::handleEmailLookupSaveEvent($entityData, $moduleName);
        }
        
        if($eventName == 'vtiger.entity.afterdelete' || $eventName == 'vtiger.lead.convertlead'){
            $this->handleEmailLookupDeleteEvent($entityData);
        }
        
        if($eventName == 'vtiger.entity.afterrestore'){
            $this->handleEmailLookupRestoreEvent($entityData, $moduleName);
        }
    }
    
    /**
     * To save email lookup record
     * @param type $entityData
     * @param type $moduleName
     */
    static function handleEmailLookupSaveEvent($entityData, $moduleName){
        $EmailsModuleModel = Vtiger_Module_Model::getInstance('Emails');
        $emailSupportedModulesList = $EmailsModuleModel->getEmailRelatedModules();
        $recordModel = new Emails_Record_Model();
        
        if(in_array($moduleName, $emailSupportedModulesList) && $moduleName != 'Users'){
            $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
            $fieldModels = $moduleInstance->getFieldsByType('email');
            
            $data = $entityData->getData();
            
            $values['crmid'] = $entityData->getId();
            $values['setype'] = $moduleName;
            $isNew = $entityData->isNew();
            
            foreach ($fieldModels as $field => $fieldModel) {
                $fieldName = $fieldModel->get('name');
                $fieldId = $fieldModel->get('id');
                $values[$fieldId] = $data[$fieldName];
                
                if(!$isNew && !$values[$fieldId]){
                    $recordModel->deleteEmailLookup($values['crmid'], $fieldId);
                }else{
                    if($values[$fieldId])
                        $recordModel->recieveEmailLookup($fieldId,$values);
                }
            }   
        }
    }
    
    /**
     * To delete email lookup record
     * @param type $entityData
     */
    function handleEmailLookupDeleteEvent($entityData){
        $recordid = $entityData->getId();
        $recordModel = new Emails_Record_Model;
        $recordModel->deleteEmailLookup($recordid);
    }
    
    /**
     * To restore email lookup record
     * @param type $entityData
     * @param type $moduleName
     */
    function handleEmailLookupRestoreEvent($entityData, $moduleName){
        $recordId = $entityData->getId();
        $emailsRecordModel = new Emails_Record_Model;
        //To get the record model of the restored record
        $recordmodel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        
        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldModels = $moduleInstance->getFieldsByType('email');
        $values['crmid'] = $recordId;
        $values['setype'] = $moduleName;    
        foreach($fieldModels as $field => $fieldModel){
            $fieldName = $fieldModel->get('name');
            $fieldId = $fieldModel->get('id');
            $values[$fieldId] = $recordmodel->get($fieldName);
            
            if($values[$fieldId]){
                $emailsRecordModel->recieveEmailLookup($fieldId,$values);
            }
        }
    }
}


class EmailLookupBatchHandler extends VTEventHandler {
    
    /**
     * For handling email lookup events for import
     * @param type $eventName
     * @param type $entityDatas
     */
    function handleEvent($eventName, $entityDatas) {
        foreach ($entityDatas as $entityData) {

            $moduleName = $entityData->getModuleName();
            
            if ($eventName == 'vtiger.batchevent.save') {
                EmailLookupHandler::handleEmailLookupSaveEvent($entityData, $moduleName);
            }
            
        }
    }
}