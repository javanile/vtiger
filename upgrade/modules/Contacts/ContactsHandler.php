<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function Contacts_sendCustomerPortalLoginDetails($entityData){
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$entityDelta = new VTEntityDelta();
	$email = $entityData->get('email');

	$isEmailChanged = $entityDelta->hasChanged($moduleName, $entityId, 'email') && $email;//changed and not empty
	$isPortalEnabled = $entityData->get('portal') == 'on' || $entityData->get('portal') == '1';

	if ($isPortalEnabled) {
		//If portal enabled / disabled, then trigger following actions
		$sql = "SELECT id, user_name, user_password, isactive FROM vtiger_portalinfo WHERE id=?";
		$result = $adb->pquery($sql, array($entityId));

		$insert = true;
		if ($adb->num_rows($result)) {
			$insert = false;
			$dbusername = $adb->query_result($result,0,'user_name');
			$isactive = $adb->query_result($result,0,'isactive');
			if($email == $dbusername && $isactive == 1 && !$entityData->isNew()){
				$update = false;
			} else if($isPortalEnabled) {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 1, $entityId));
				$update = true;
			} else {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 0, $entityId));
				$update = false;
			}
		}

		//generate new password
		$password = makeRandomPassword();
		$enc_password = Vtiger_Functions::generateEncryptedPassword($password);

		//create new portal user
		$sendEmail = false;
		if ($insert) {
			$sql = "INSERT INTO vtiger_portalinfo(id,user_name,user_password,cryptmode,type,isactive) VALUES(?,?,?,?,?,?)";
			$params = array($entityId, $email, $enc_password, 'CRYPT', 'C', 1);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//update existing portal user password
		if ($update && $isEmailChanged) {
			$sql = "UPDATE vtiger_portalinfo SET user_password=?, cryptmode=? WHERE id=?";
			$params = array($enc_password, 'CRYPT', $entityId);
			$adb->pquery($sql, $params);
			$sendEmail = true;
		}

		//trigger send email
		if ($sendEmail && $entityData->get('emailoptout') == 0) {
			global $current_user,$HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
			require_once("modules/Emails/mail.php");
			$emailData = Contacts::getPortalEmailContents($entityData,$password,'LoginDetails');
			$subject = $emailData['subject'];
			if(empty($subject)) {
				$subject = 'Customer Portal Login Details';
			}

			$contents = $emailData['body'];
			$contents= decode_html(getMergedDescription($contents, $entityId, 'Contacts'));
			if(empty($contents)) {
				require_once 'config.inc.php';
				global $PORTAL_URL;
				$contents = 'LoginDetails';
				$contents .= "<br><br> User ID : $email";
				$contents .= "<br> Password: ".$password;
				$portalURL = vtranslate('Please ',$moduleName).'<a href="'.$PORTAL_URL.'" style="font-family:Arial, Helvetica, sans-serif;font-size:13px;">'. vtranslate('click here', $moduleName).'</a>';
				$contents .= "<br>".$portalURL;
			}
			$subject = decode_html(getMergedDescription($subject, $entityId,'Contacts'));
			send_mail('Contacts', $email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents,'','','','','',true);
		}
	} else {
		$sql = "UPDATE vtiger_portalinfo SET user_name=?,isactive=0 WHERE id=?";
		$adb->pquery($sql, array($email, $entityId));
	}
}

?>
