<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_MentionedUsers_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return true;
	}
    
    public function process(Vtiger_Request $request) {
        $mentionRule = Settings_Notifications_Task_Model::getInstance('Mention');
        $message = $request->get('message');
        $mentionedUsers = $mentionRule->getMentionedNames($message);
        $commentId = $request->get('crmid');
        $commentRecord = Vtiger_Record_Model::getInstanceById($commentId, Vtiger_Module_Model::getInstance('ModComments'));
        $commentOwnerId = $commentRecord->get('creator');
        $commentOwnerName = Users_Record_Model::getInstanceById($commentOwnerId, Users_Module_Model::getInstance('Users'))->getName();
        $commentOwnerName = str_replace(' ', '',$commentOwnerName);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUser->getId();
        
        if($commentOwnerId !== $currentUserId) {
            $mentionedUsers[] = decode_html($commentOwnerName);
        }
        $currentUserName = decode_html(str_replace(' ', '',$currentUser->getName()));
        //Unset current user from the mentioned users
        if(($key = array_search(strtolower($currentUserName), $mentionedUsers)) !== false) {
            unset($mentionedUsers[$key]);
        }
        
        $usersString = '@'.implode(' @', $mentionedUsers);
        $mentionedUsersData['usersString'] = $usersString.' ';
        
        $response = new Vtiger_Response();
        $response->setResult($mentionedUsersData);
        $response->emit();
    }
}
