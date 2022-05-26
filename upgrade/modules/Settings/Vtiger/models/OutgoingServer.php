<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_OutgoingServer_Model extends Settings_Vtiger_Systems_Model {
    
    private $defaultLoaded = false;


    public function getSubject() {
        return 'Test mail about the mail server configuration.';
    }
    
    public function getBody() {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        return 'Dear '.$currentUser->get('user_name').', <br><br><b> This is a test mail sent to confirm if a mail is 
                actually being sent through the smtp server that you have configured. </b><br>Feel free to delete this mail.
                <br><br>Thanks  and  Regards,<br> Team vTiger <br><br>';
    }
    
	public function loadDefaultValues() {
        $defaultOutgoingServerDetails = VtigerConfig::getOD('DEFAULT_OUTGOING_SERVER_DETAILS');
        if (empty($defaultOutgoingServerDetails)) {
            $db = PearDatabase::getInstance();
            $db->pquery('DELETE FROM vtiger_systems WHERE server_type = ?', array('email'));
            return;
        }
        foreach ($defaultOutgoingServerDetails as $key=>$value){
            $this->set($key,$value);
        }

        $this->defaultLoaded = true;
    }
	
	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem() {
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_MAIL_SERVER_SETTINGS');
		return $menuItem;
	}
    
	public function getEditViewUrl() {
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=OutgoingServerEdit&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
	}
	
	public function getDetailViewUrl() {
		$menuItem = $this->getMenuItem();
		return '?module=Vtiger&parent=Settings&view=OutgoingServerDetail&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
	}
	
    public function isDefaultSettingLoaded() {
        return $this->defaultLoaded;
    }
    
    public function save($request){
        vimport('~~/modules/Emails/mail.php');
        $currentUser = Users_Record_Model::getCurrentUserModel();

        $from_email =  $request->get('from_email_field');
        $to_email = getUserEmailId('id',$currentUser->getId());
        
        $subject = $this->getSubject();
        $description = $this->getBody();
		// This is added so that send_mail API will treat it as user initiated action
        $olderAction = $_REQUEST['action'];
		$_REQUEST['action'] = 'Save';
        if($to_email != ''){
            $mail_status = send_mail('Users',$to_email,$currentUser->get('user_name'),$from_email,$subject,$description,'','','','','',true);
        }
		$_REQUEST['action'] = $olderAction;
        if($mail_status != 1 && !$this->isDefaultSettingLoaded()) {
            throw new Exception('Error occurred while sending mail');
        } 
        return parent::save();
    }
}
