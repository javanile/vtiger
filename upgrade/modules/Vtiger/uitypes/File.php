<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_File_UIType extends Vtiger_Base_UIType {
    
    /**
	 * Function to get the Template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/File.tpl';
	}
    
    /**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <String> $value
	 * @param <Integer> $recordId
	 * @param <Vtiger_Record_Model>
	 * @return <String>
	 */
	public function getDisplayValue($value, $recordId=false, $recordModel=false) {
        $db = PearDatabase::getInstance();
        $attachmentId = (int)$value;
        $displayValue = '--';
        if($attachmentId) {
            $query = 'SELECT name FROM vtiger_attachments WHERE attachmentsid = ?';
            $result = $db->pquery($query,array($attachmentId));
            $displayValue = $attachmentName = $db->query_result($result,0,'name');
            if($recordModel) {
                $url = 'index.php?module='.$recordModel->getModuleName().
                       '&action=DownloadAttachment&record='.$recordModel->getId().'&attachmentid='.$attachmentId;
                $displayValue = '<a href="'.$url.'" title="'.vtranslate('LBL_DOWNLOAD_FILE',$recordModel->getModuleName()).'">'.
                                    textlength_check($attachmentName).
                                '</a>';
            }
        }
        return $displayValue;
    }
    
}