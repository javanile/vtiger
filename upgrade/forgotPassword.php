<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

require_once 'includes/main/WebUI.php';
require_once 'include/utils/utils.php';
require_once 'include/utils/VtlibUtils.php';
require_once 'modules/Vtiger/helpers/ShortURL.php';
require_once 'vtlib/Vtiger/Mailer.php';

global $adb;
$adb = PearDatabase::getInstance();

if (isset($_REQUEST['username']) && isset($_REQUEST['emailId'])) {
	$username = vtlib_purify($_REQUEST['username']);
	$result = $adb->pquery('select email1 from vtiger_users where user_name= ? ', array($username));
	if ($adb->num_rows($result) > 0) {
		$email = $adb->query_result($result, 0, 'email1');
	}

	if (vtlib_purify($_REQUEST['emailId']) == $email) {
		$time = time();
		$options = array(
			'handler_path' => 'modules/Users/handlers/ForgotPassword.php',
			'handler_class' => 'Users_ForgotPassword_Handler',
			'handler_function' => 'changePassword',
			'handler_data' => array(
				'username' => $username,
				'email' => $email,
				'time' => $time,
				'hash' => md5($username.$time)
			)
		);
		$trackURL = Vtiger_ShortURL_Helper::generateURL($options);
		$content = 'Dear Customer,<br><br> 
						You recently requested a password reset for your VtigerCRM Open source Account.<br> 
						To create a new password, click on the link <a target="_blank" href='.$trackURL.'>here</a>. 
						<br><br> 
						This request was made on '.date("Y-m-d H:i:s").' and will expire in next 24 hours.<br><br> 
						Regards,<br> 
						VtigerCRM Open source Support Team.<br>';

		$subject = 'Vtiger CRM: Password Reset';

		$mail = new Vtiger_Mailer();
		$mail->IsHTML();
		$mail->Body = $content;
		$mail->Subject = $subject;
		$mail->AddAddress($email);

		$status = $mail->Send(true);
		if ($status === 1 || $status === true) {
			header('Location:  index.php?modules=Users&view=Login&mailStatus=success');
		} else {
			header('Location:  index.php?modules=Users&view=Login&error=statusError');
		}
	} else {
		header('Location:  index.php?modules=Users&view=Login&error=fpError');
	}
}
