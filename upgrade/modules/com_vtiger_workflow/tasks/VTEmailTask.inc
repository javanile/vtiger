<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc');
require_once('modules/Emails/mail.php');
require_once('include/simplehtmldom/simple_html_dom.php');
require_once('modules/Emails/models/Mailer.php'); 

class VTEmailTask extends VTTask{
	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = false;

	public function getFieldNames(){
		return array("subject", "content", "recepient", 'emailcc', 'emailbcc', 'fromEmail', 'pdf', 'pdfTemplateId',
			'signature','replyTo');
	}
	public function doTask($entity){
		global $current_user;
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$module = $entity->getModuleName();
		$taskContents = Zend_Json::decode($this->getContents($entity));
		$relatedInfo = Zend_Json::decode($this->getRelatedInfo());
		$from_email	= $taskContents['fromEmail'];
		$from_name	= $taskContents['fromName'];
//		$to_email	= $taskContents['toEmail'];
		$cc			= $taskContents['ccEmail'];
		$bcc		= $taskContents['bccEmail'];
		$replyTo 	= $taskContents['replyTo'];
		$subject	= $taskContents['subject'];
		$content	= $taskContents['content'];
		$isPdfTemplateEnabled = $this->pdf;
		$pdfTemplateId = $this->pdfTemplateId;

		if (!$entityCache) {
			$entityCache = new VTEntityCache($admin);
		}

		$et = new VTEmailRecipientsTemplate($this->recepient);
		$to_email = $et->render($entityCache, $entity->getId());

		if(!empty($to_email)) {
			//Storing the details of emails
			$entityIdDetails = vtws_getIdComponents($entity->getId());
			$entityId = $entityIdDetails[1];
			$moduleName = 'Emails';
			$userId = $current_user->id;
			$emailFocus = CRMEntity::getInstance($moduleName);
			$processedContent = Emails_Mailer_Model::getProcessedContent($content); // To remove script tags
			$mailerInstance = Emails_Mailer_Model::getInstance();
			$mailerInstance->isHTML(true);
			$processedContentWithURLS = $mailerInstance->convertToValidURL($processedContent);

			$emailFocus->column_fields['assigned_user_id'] = $userId;
			$emailFocus->column_fields['subject'] = $subject;
			$emailFocus->column_fields['description'] = $processedContentWithURLS;
			$emailFocus->column_fields['from_email'] = $from_email;
			$emailFocus->column_fields['saved_toid'] = $to_email;
			$emailFocus->column_fields['ccmail'] = $cc;
			$emailFocus->column_fields['bccmail'] = $bcc;
			$emailFocus->column_fields['parent_id'] = $entityId."@$userId|";
			$emailFocus->column_fields['email_flag'] = 'SENT';
			$emailFocus->column_fields['activitytype'] = $moduleName;
			$emailFocus->column_fields['date_start'] = date('Y-m-d');
			$emailFocus->column_fields['time_start'] = date('H:i:s');
			$emailFocus->column_fields['mode'] = '';
			$emailFocus->column_fields['id'] = '';
			$emailFocus->save($moduleName);

			// To add entry in ModTracker
			$entityFocus = CRMEntity::getInstance($module);
			$entityFocus->retrieve_entity_info($entityId, $module);
			relateEntities($entityFocus, $module, $entityId, 'Emails', $emailFocus->id);

			//Including email tracking details
			$emailId = $emailFocus->id;
			$imageDetails = Vtiger_Functions::getTrackImageContent($emailId, $entityId);
			$content = $content.$imageDetails;

			if (stripos($content, '<img src="cid:logo" />')) {
				$mailerInstance->AddEmbeddedImage('layouts/v7/skins/images/logo_mail.jpg', 'logo', 'logo.jpg',"base64","image/jpg");
			}

			$nameEmailArray = Vtiger_Functions::extractNameEmail($from_email);
			if($nameEmailArray) {
				$from_name = $nameEmailArray['name'];
				$from_email = $nameEmailArray['email'];
			}
			//set properties
			$toEmail = trim($to_email,',');
			if(!empty($toEmail)) {
				if(is_array($toEmail)) {
					foreach ($toEmail as $email) {
						$mailerInstance->AddAddress($email);
					}
				}else{
					$toEmails = explode(',', $toEmail);
					foreach ($toEmails as $email) {
						$mailerInstance->AddAddress($email);
					}
				}
			}
			//Retrieve MessageID from Mailroom table only if module is not users 
			$inReplyToMessageId = $mailerInstance->retrieveMessageIdFromMailroom($entityId);
			$generatedMessageId = $mailerInstance->generateMessageID();

			if (empty($inReplyToMessageId)) {
				$inReplyToMessageId = $generatedMessageId;
			}

			//Set messageId header for every sending email
			if (!empty($generatedMessageId)) {
				$mailerInstance->MessageID = $generatedMessageId;
			}

			//If variable is not empty then add custom header 
			if (!empty($inReplyToMessageId)) {
				$mailerInstance->AddCustomHeader("In-Reply-To", $inReplyToMessageId);
			}

			$this->addCCAddress($mailerInstance, $cc);
			$this->addCCAddress($mailerInstance, $bcc,true);
			$mailerInstance->From = $from_email;
			$mailerInstance->FromName = decode_html($from_name);
			$mailerInstance->AddReplyTo($replyTo);
			$mailerInstance->Subject = strip_tags(decode_html($subject));
			$mailerInstance->Body = decode_emptyspace_html($content);
			$mailerInstance->Body = Emails_Mailer_Model::convertCssToInline($mailerInstance->Body);
			$mailerInstance->Body = Emails_Mailer_Model::makeImageURLValid($mailerInstance->Body);
			$emailRecord = Emails_Record_Model::getInstanceById($emailId);
			$mailerInstance->Body = $emailRecord->convertUrlsToTrackUrls($mailerInstance->Body,$entityId);
			$plainBody = decode_html($content);
			$plainBody = preg_replace(array("/<p>/i","/<br>/i","/<br \/>/i"),array("\n","\n","\n"),$plainBody);
			$plainBody = strip_tags($plainBody);
			$plainBody = Emails_Mailer_Model::convertToAscii($plainBody);
			$plainBody = $emailRecord->convertUrlsToTrackUrls($plainBody,$entityId,'plain');
			$mailerInstance->AltBody = $plainBody;
			
			//Block to get file details if comment is having attachment
			if(!empty($relatedInfo) && $relatedInfo['module'] == 'ModComments'){
				$modcommentsRecordId = $relatedInfo['id'];
				$modcommentsRecordModel = ModComments_Record_Model::getInstanceById($modcommentsRecordId);
				$modcommentsRecordModel->set('id',$modcommentsRecordId);
				$fileDetails = $modcommentsRecordModel->getFileDetails();
				//If no attachment details are found
				$path = '';
				
				//There can be multiple attachments for a single comment
				foreach($fileDetails as $fileDetail){
					if(!empty($fileDetail)){
						$path = $fileDetail['path'].$fileDetail['attachmentsid'].'_'.decode_html($fileDetail['name']);
						$mailerInstance->AddAttachment($path);
					}
				}
			}

			$mailerInstance->send(true);

			$error = $mailerInstance->getError();
			if(!empty($emailId)) {
				$emailFocus->setEmailAccessCountValue($emailId);
			}
			if($path){
				if(!empty($fileDetails) && is_array($fileDetails)){
					foreach($fileDetails as $fileDetail){
						$modcommentsRecordModel->uploadAndSaveFile($emailId,$fileDetail['attachmentsid']);
					}
				}
			}
			if($error) {
				//If mail is not sent then removing the details about email
				$emailFocus->trash($moduleName, $emailId);
			} else {
				//If mail sending is success store message Id for given crmId
				if($generatedMessageId && $entityId){
					$mailerInstance->updateMessageIdByCrmId($generatedMessageId,$entityId);
				}
			}
		}
		$util->revertUser();

	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($entity, $entityCache=false) {
		if (!$this->contents) {
			global $adb, $current_user;
			$taskContents = array();
			$entityId = $entity->getId();

			$utils = new VTWorkflowUtils();
			$adminUser = $utils->adminUser();
			if (!$entityCache) {
				$entityCache = new VTEntityCache($adminUser);
			}

			$fromUserId = Users::getActiveAdminId();
			$entityOwnerId = $entity->get('assigned_user_id');
			if ($entityOwnerId) {
				list ($moduleId, $fromUserId) = explode('x', $entityOwnerId);
			}

			$ownerEntity = $entityCache->forId($entityOwnerId);
			if($ownerEntity->getModuleName() === 'Groups') {
				list($moduleId, $recordId) = vtws_getIdComponents($entityId);
				$fromUserId = Vtiger_Util_Helper::getCreator($recordId);
			}
			$userObj = CRMEntity::getInstance('Users');
			$userObj->retrieveCurrentUserInfoFromFile($fromUserId);
			if ($this->fromEmail && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->fromEmail, 'assigned_user_id : (Users) ') !== false)) {
				/**From email merge tag have combination of name<email> format, So VTSimpleTemplate only
				 * merge the name not email part because of anchor pair. So we need to explode them and then
				 * assign them to VTSimpleTemplate to merge properly
				**/
				if(strpos($this->fromEmail, '&lt;') && strpos($this->fromEmail, '&gt;')) {
					list($fromNameTag, $fromEmailTag) = explode('&lt;', $this->fromEmail);
					list($fromEmailTag, $rest) = explode('&gt;', $fromEmailTag);
				}elseif (strpos($this->fromEmail, '<') && strpos($this->fromEmail, '>')) {
					list($fromNameTag, $fromEmailTag) = explode('<', $this->fromEmail);
					list($fromEmailTag, $rest) = explode('>', $fromEmailTag);
				} else {
					/**In this case user entered only email or name and email without anchor tags or mergetag without anchor tags etc..
					 * So we need to check if user given only email and if it is valid, then we will set it as valid email string and
					 * from name as current user name else we will treat it as from name and set from email as active admin's primary email
					 */
					if(filter_var($this->fromEmail,FILTER_VALIDATE_EMAIL)) {
						$fromEmailTag = $this->fromEmail;
						$fromNameTag = $this->fromEmail;
					}else{
						$fromNameTag = $this->fromEmail;
						if($userObj) {
							$fromEmailTag = $userObj->email1;
						}else{
							$fromEmailTag = $this->getDefaultFromEmail($this->fromEmail);
						}
					}
				}
				$et = new VTEmailRecipientsTemplate($fromEmailTag);
				$fromEmail = $et->render($entityCache, $entityId);
				
				$nt = new VTEmailRecipientsTemplate($fromNameTag);
				$fromName = $nt->render($entityCache, $entityId);
			} else {
				if ($userObj) {
					$fromEmail = $userObj->email1;
					$fromName =	trim($userObj->first_name.' '.$userObj->last_name);
				} else {
					$fromEmail = $this->getDefaultFromEmail();
					$userObj = Users::getActiveAdminUser();
					$fromName =	trim($userObj->first_name.' '.$userObj->last_name);
				}
			}

			if (!$fromEmail) {
				$utils->revertUser();
				return false;
			}

			$taskContents['fromEmail'] = $fromEmail;
			$taskContents['fromName'] =	$fromName;
			$defReplyTo = $this->getDefaultReplyToEmail();
			if ($this->replyTo && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->replyTo, 'assigned_user_id : (Users) ') !== false)) {
				$et = new VTEmailRecipientsTemplate($this->replyTo);
				$replyToEmailDetails = $et->render($entityCache, $entityId);
				$replyToEmailDetails = trim($replyToEmailDetails,',');
				//ReplyTo might be empty when record's email value is not set
				if(filter_var($replyToEmailDetails,FILTER_VALIDATE_EMAIL)) {
					$replyToEmail = $replyToEmailDetails;
				}else{
					$replyToEmail = $defReplyTo;
				}
			} else {
				$replyToEmail = $defReplyTo;
			}
			$taskContents['replyTo'] = $replyToEmail;

			if ($entity->getModuleName() === 'Events') {
				$contactId = $entity->get('contact_id');
				if ($contactId) {
					$contactIds = '';
					list($wsId, $recordId) = explode('x', $entityId);
					$webserviceObject = VtigerWebserviceObject::fromName($adb, 'Contacts');

					$result = $adb->pquery('SELECT contactid FROM vtiger_cntactivityrel WHERE activityid = ?', array($recordId));
					$numOfRows = $adb->num_rows($result);
					for($i=0; $i<$numOfRows; $i++) {
						$contactIds .= vtws_getId($webserviceObject->getEntityId(), $adb->query_result($result, $i, 'contactid')).',';
					}
				}
				$entity->set('contact_id', trim($contactIds, ','));
				$entityCache->cache[$entityId] = $entity;
			}

			$et = new VTEmailRecipientsTemplate($this->recepient);
			$toEmail = $et->render($entityCache, $entityId);

			$ecct = new VTEmailRecipientsTemplate($this->emailcc);
			$ccEmail = $ecct->render($entityCache, $entityId);

			$ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
			$bccEmail = $ebcct->render($entityCache, $entityId);

			if(strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
				$utils->revertUser();
				return false;
			}

			$taskContents['toEmail'] = $toEmail;
			$taskContents['ccEmail'] = $ccEmail;
			$taskContents['bccEmail'] = $bccEmail;

			$this->parseEmailTemplate($entity);
			//line item merge tags also should replace with proper values for subject
			$st = new VTSimpleTemplate($this->subject);
			$taskContents['subject'] = $st->render($entityCache, $entityId);
			
			$ct = new VTSimpleTemplate($this->content);
			$taskContents['content'] = $ct->render($entityCache, $entityId);
			//adding signatue to body
			// To handle existing workflows those having signature value as empty so assigning value 
			// "Yes" for those workflows.
			if(empty($this->signature)){
				$this->signature = 'Yes';
			}
			$content = $taskContents['content'];
			if($this->signature == 'Yes') {
				$userObj = CRMEntity::getInstance('Users');
				$userObj->retrieveCurrentUserInfoFromFile($fromUserId);
				$content .= '<br><br>'.  decode_html($userObj->signature);
			}
			$taskContents['content'] = $content;
			$this->contents = $taskContents;
			$utils->revertUser();
		}
		if(is_array($this->contents)) {
			$this->contents = Zend_Json::encode($this->contents);
		}
		return $this->contents;
	}

	/**
	 * Function to parse merge tags related to email template selected
	 * while creating email task
	 * @param <object> $entity
	 */
	public function parseEmailTemplate($entity) {
		$moduleName = $entity->getModuleName();
		list($wsId, $recordId) = explode('x', $entity->getId());
		$mergedHtml = getMergedDescription($this->content, $recordId, $moduleName);
		$this->content = $mergedHtml;
	}

	function getDefaultReplyToEmail() {
		global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
		$defaultReplyToEmail = null;
		if (!empty($HELPDESK_SUPPORT_EMAIL_REPLY_ID) && $HELPDESK_SUPPORT_EMAIL_REPLY_ID !== 'support@company-name.com') {
			$defaultReplyToEmail = $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
		} else {
			$cachedOutgoingFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
			if (empty($cachedOutgoingFromEmail)) {
				global $adb;
				$sql = 'SELECT from_email_field FROM vtiger_systems WHERE server_type=?';
				$result = $adb->pquery($sql, array('email'));
				$outgoingFromEamil = $adb->query_result($result, 0, 'from_email_field');
				if (empty($outgoingFromEamil)) {
					$activeAdmin = Users::getActiveAdminUser();
					$defaultReplyToEmail = $activeAdmin->email1;
				} else {
					$defaultReplyToEmail = $outgoingFromEamil;
					VTCacheUtils::setOutgoingMailFromEmailAddress($outgoingFromEamil);
				}
			} else {
				$defaultReplyToEmail = $cachedOutgoingFromEmail;
			}
		}
		return $defaultReplyToEmail;
	}

	function getDefaultFromEmail($fromName = null) {
		$defaultFromEmail = null;
		$cachedOutgoingFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
		if (empty($cachedOutgoingFromEmail)) {
			global $adb;
			$sql = 'SELECT from_email_field FROM vtiger_systems WHERE server_type=?';
			$result = $adb->pquery($sql, array('email'));
			$outgoingFromEamil = $adb->query_result($result, 0, 'from_email_field');
			if (empty($outgoingFromEamil)) {
				if ($fromName) {
					$userEmail = getUserEmailId('user_name', $fromName);
					$defaultFromEmail = $userEmail;
				}
				if (!$defaultFromEmail) {
					$activeAdminUser = Users::getActiveAdminUser();
					$defaultFromEmail = $activeAdminUser->email1;
				}
			} else {
				$defaultFromEmail = $outgoingFromEamil;
				VTCacheUtils::setOutgoingMailFromEmailAddress($outgoingFromEamil);
			}
		} else {
			$defaultFromEmail = $cachedOutgoingFromEmail;
		}
		return $defaultFromEmail;
	}

	function addCCAddress($mailerObj, $address, $isBCC = false) {
		$method = (!empty($isBCC)) ? 'AddBCC' : 'AddCC';
		if (!empty($address)) {
			$addresses = explode(',', trim($address, ','));
			foreach ($addresses as $cc) {
				$name = preg_replace('/([^@]+)@(.*)/', '$1', $cc); // First Part Of Email
				if (stripos($cc, '<')) {
					$nameAddrPair = explode('<', $cc);
					$name = $nameAddrPair[0];
					$cc = trim($nameAddrPair[1], '>');
				}
				if (!empty($cc)) {
					$mailerObj->$method($cc, $name);
				}
			}
		}
	}

}
