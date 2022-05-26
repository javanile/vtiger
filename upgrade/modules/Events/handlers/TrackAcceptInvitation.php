<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_TrackAcceptInvitation_Handler {

	function acceptInvitation($data) {
		global $current_user;
		$eventId = $data['eventId'];
		$userId = $data['userId'];
		$current_user = Users_Privileges_Model::getInstanceById($userId);
		$recordModel = Events_Record_Model::getInstanceById($eventId, 'Events');
		$inviteeDetails = $recordModel->getInviteesDetails($userId);
		if ($inviteeDetails[$userId] !== 'accepted') {
			$recordModel->updateInvitationStatus($eventId, $userId, 'accepted');
			$recordModel->set('assigned_user_id', $userId);
			$recordModel->save();
		}
		echo 'Event added to your calendar - Thank you!';
	}

}

?>