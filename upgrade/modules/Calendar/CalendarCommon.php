<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/

/**
 * To get the lists of vtiger_users id who shared their calendar with specified user
 * @param $sharedid -- The shared user id :: Type integer
 * @returns $shared_ids -- a comma seperated vtiger_users id  :: Type string
 */
function getSharedCalendarId($sharedid)
{
	global $adb;
	$query = "SELECT * from vtiger_sharedcalendar where sharedid=?";
	$result = $adb->pquery($query, array($sharedid));
	if($adb->num_rows($result)!=0)
	{
		for($j=0;$j<$adb->num_rows($result);$j++)
			$userid[] = $adb->query_result($result,$j,'userid');
		$shared_ids = implode (",",$userid);
	}
	return $shared_ids;
}

/**
 * To get hour,minute and format
 * @param $starttime -- The date&time :: Type string
 * @param $endtime -- The date&time :: Type string
 * @param $format -- The format :: Type string
 * @returns $timearr :: Type Array
*/
function getaddEventPopupTime($starttime,$endtime,$format)
{
	$timearr = Array();
	list($sthr,$stmin) = explode(":",$starttime);
	list($edhr,$edmin)  = explode(":",$endtime);
	if($format == 'am/pm' || $format == '12')
	{
		$hr = $sthr+0;
		$timearr['startfmt'] = ($hr >= 12) ? "pm" : "am";
		if($hr == 0) $hr = 12;
		$timearr['starthour'] = twoDigit(($hr>12)?($hr-12):$hr);
		$timearr['startmin']  = $stmin;

		$edhr = $edhr+0;
		$timearr['endfmt'] = ($edhr >= 12) ? "pm" : "am";
		if($edhr == 0) $edhr = 12;
		$timearr['endhour'] = twoDigit(($edhr>12)?($edhr-12):$edhr);
		$timearr['endmin']    = $edmin;
		return $timearr;
	}
	if($format == '24')
	{
		$timearr['starthour'] = twoDigit($sthr);
		$timearr['startmin']  = $stmin;
		$timearr['startfmt']  = '';
		$timearr['endhour']   = twoDigit($edhr);
		$timearr['endmin']    = $edmin;
		$timearr['endfmt']    = '';
		return $timearr;
	}
}

/**
 * Function to get Acception Invitation short url
 * @param <Int> $user_id - userId (invitee)
 * @param <Events_Record_Model> $eventRecordModel
 * @return <URL> - short url for tracking acception action
 */
function getAcceptInvitationUrl($user_id,$eventRecordModel) {
    $options = array(
       'handler_path' => 'modules/Events/handlers/TrackAcceptInvitation.php',
       'handler_class' => 'Events_TrackAcceptInvitation_Handler',
       'handler_function' => "acceptInvitation",
       'handler_data' => array(
			'eventId' => $eventRecordModel->getId(),
			'userId' => $user_id
       )
   );
   return Vtiger_ShortURL_Helper::generateURL($options);
}

/**
 * Function to add accept event tracking link
 * @param <String> $body - email body 
 * @param <Int> $user_id - userId
 * @param <Events_Record_Model> $recordModel
 * @return <String> - updated body with accept tracking link
 */
function addAcceptEventLink($body,$user_id,$recordModel) {
    if(!$recordModel) {
        return $body;
    }
    $acceptInvitationUrl = getAcceptInvitationUrl($user_id,$recordModel);
    if(strpos($body,'$AcceptTrackingUrl')) {
        return str_replace('$AcceptTrackingUrl',$acceptInvitationUrl,$body);
    }
    //$AcceptTrackingUrl not found in body of template
    $acceptLink = '<div class="invitationresponse"><a href="' . 
            $acceptInvitationUrl. '" target="_blank">Accept - Add Event to Vtiger Calendar</a></div>';
    return substr_replace($body, $acceptLink, strpos($body, '</body>'), 0);
}

/**
 * Function to get the vtiger_activity details for mail body
 * @param   string   $description       - activity description
 * @param   string   $from              - to differenciate from notification to invitation.
 * return   string   $list              - HTML in string format
 */
function getActivityDetails($description,$user_id,$from='',$recordModel=false) {
    global $log,$current_user;
	require_once 'include/utils/utils.php';
	$log->debug("Entering getActivityDetails(".$description.") method ...");

	// Show the start date and end date in the users date format and in his time zone
	$inviteeUser = CRMEntity::getInstance('Users');
	$inviteeUser->retrieveCurrentUserInfoFromFile($user_id);
	$startDate = new DateTimeField($description['st_date_time']);
	$endDate = new DateTimeField($description['end_date_time']);
	$current_username = getUserFullName($current_user->id);
	$name = getUserFullName($user_id);
	
	$db = PearDatabase::getInstance();
	$query='SELECT body FROM vtiger_emailtemplates WHERE subject=? AND systemtemplate=?';
	$result = $db->pquery($query, array('Invitation', '1'));
	$body=decode_html($db->query_result($result,0,'body'));
	$body=addAcceptEventLink($body,$user_id,$recordModel);
    $list = $body;
	$list = str_replace('$invitee_name$', $name, $list);
	$list = str_replace('$events-date_start$',$startDate->getDisplayDateTimeValue($inviteeUser) .' '.vtranslate($inviteeUser->time_zone, 'Users'),$list);
	$list = str_replace('$events-due_date$',$endDate->getDisplayDateTimeValue($inviteeUser).' '.vtranslate($inviteeUser->time_zone, 'Users'),$list);
	$list = str_replace('$events-contactid$',$description['contact_name'],$list);
	$list = str_replace('$current_user_name$',$current_username,$list);

    $log->debug("Exiting getActivityDetails method ...");
    return $list;
}

function twoDigit( $no ){
	if($no < 10 && strlen(trim($no)) < 2) return "0".$no;
	else return "".$no;
}

function sendInvitation($inviteesid,$mode,$recordModel,$desc) {
	global $current_user,$mod_strings;
	require_once("vtlib/Vtiger/Mailer.php");
	$invitees_array = explode(';',$inviteesid);
	
	if($desc['mode'] == 'edit') {
        $subject = vtranslate("LBL_UPDATED_INVITATION", "Calendar").' : ';
    } else {
        $subject = vtranslate("LBL_INVITATION", "Calendar").' : ';
    }
	$subject .= $recordModel->get('subject');
	$attachment = generateIcsAttachment($desc);
	foreach($invitees_array as $inviteeid) {
		if($inviteeid != '') {
			$description=getActivityDetails($desc,$inviteeid,"invite",$recordModel);
			$description = getMergedDescription($description, $recordModel->getId(), 'Events');
			$to_email = getUserEmailId('id',$inviteeid);
			$to_name = getUserFullName($inviteeid);
            $mail = new Vtiger_Mailer();
            $mail->IsHTML(true);
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$userName = $currentUserModel->getName();
			$fromEmail = Emails_Record_Model::getFromEmailAddress();
			$mail->ConfigSenderInfo($fromEmail,$userName);
            $mail->Subject = $subject;
            $mail->Body = $description;
            $mail->AddAttachment($attachment, '', 'base64', 'text/calendar');
            $mail->SendTo($to_email, decode_html($to_name), false, false, true);
		}
	}
    unlink($attachment);

}

// User Select Customization
/**
 * Function returns the id of the User selected by current user in the picklist of the ListView or Calendar view of Current User
 * return String -  Id of the user that the current user has selected
 */
function calendarview_getSelectedUserId() {
	global $current_user, $default_charset;
	$only_for_user = htmlspecialchars(strip_tags(vtlib_purifyForSql($_REQUEST['onlyforuser'])),ENT_QUOTES,$default_charset);
	if($only_for_user == '') $only_for_user = $current_user->id;
	return $only_for_user;
}

function calendarview_getSelectedUserFilterQuerySuffix() {
	global $current_user, $adb;
	$only_for_user = calendarview_getSelectedUserId();
	$qcondition = '';
	if(!empty($only_for_user)) {
		if($only_for_user != 'ALL') {
			// For logged in user include the group records also.
			if($only_for_user == $current_user->id) {
				$user_group_ids = fetchUserGroupids($current_user->id);
				// User does not belong to any group? Let us reset to non-existent group
				if(!empty($user_group_ids)) $user_group_ids .= ',';
				else $user_group_ids = '';
				$user_group_ids .= $current_user->id;
				$qcondition = " AND vtiger_crmentity.smownerid IN (" . $user_group_ids .")";
			} else {
				$qcondition = " AND vtiger_crmentity.smownerid = "  . $adb->sql_escape_string($only_for_user);
			}
		}
	}
	return $qcondition;
}

/*
 * Function to generate ICS file to send as attachment with email
 * invitation when a user is invited for an event
 * @params $record Event record
 * @return filename as event name
 */
function generateIcsAttachment($record) {
    $fileName = str_replace(' ', '_', decode_html($record['subject']));
    $assignedUserId = $record['user_id'];
    $userModel = Users_Record_Model::getInstanceById($assignedUserId, 'Users');
    $firstName = $userModel->entity->column_fields['first_name'];
    $lastName = $userModel->entity->column_fields['last_name'];
    $email = $userModel->entity->column_fields['email1'];
    $fp = fopen('test/upload/'.$fileName.'.ics', "w");
    fwrite($fp, "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\n");
    fwrite($fp, "ORGANIZER;CN=".$firstName." ".$lastName.":MAILTO:".$email."\n");
    fwrite($fp, "DTSTART:".date('Ymd\THis\Z', strtotime($record['st_date_time']))."\n");
    fwrite($fp, "DTEND:".date('Ymd\THis\Z', strtotime($record['end_date_time']))."\n");
    fwrite($fp, "DTSTAMP:".date('Ymd\THis\Z')."\n");
    fwrite($fp, "DESCRIPTION:".$record['description']."\nLOCATION:".$record['location']."\n");
    fwrite($fp, "STATUS:CONFIRMED\nSUMMARY:".$record['subject']."\nEND:VEVENT\nEND:VCALENDAR");
    fclose($fp);
    
    return 'test/upload/'.$fileName.'.ics';
}

?>