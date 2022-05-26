<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Emails_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl($parentId = false) {
		if(!$parentId) {
			list($parentId, $status) = explode('@', reset(array_filter(explode('|', $this->get('parent_id')))));
		}
		return 'Javascript:Vtiger_Index_Js.showEmailPreview("'.$this->getId().'","'.$parentId.'")';
	}

	/**
	 * Function to save an Email
	 */
	public function save() {
		//Opensource fix for MailManager data mail attachment
		if($this->get('email_flag')!='MailManager'){
			$this->set('date_start', date('Y-m-d'));
			$this->set('time_start', date('H:i'));
		}
		$this->set('activitytype', 'Emails');

		//$currentUserModel = Users_Record_Model::getCurrentUserModel();
		//$this->set('assigned_user_id', $currentUserModel->getId());
		$this->getModule()->saveRecord($this);
		$documentIds = $this->get('documentids');
		if (!empty ($documentIds)) {
			$this->deleteDocumentLink();
			$this->saveDocumentDetails();
		}
	}

	/**
	 * Function sends mail
	 */
	public function send($addToQueue = false) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$rootDirectory = vglobal('root_directory');

		$mailer = Emails_Mailer_Model::getInstance();
		$mailer->IsHTML(true);

		$fromEmail = $this->getFromEmailAddress();
		$replyTo = $this->getReplyToEmail();
		$userName = $currentUserModel->getName();

		// To eliminate the empty value of an array
		$toEmailInfo = array_filter($this->get('toemailinfo'));
		$emailsInfo = array();
		foreach ($toEmailInfo as $id => $emails) {
			foreach($emails as $key => $value){
				array_push($emailsInfo, $value);
			}
		}

		$toEmailInfo = array_map("unserialize", array_unique(array_map("serialize", array_map("array_unique", $toEmailInfo))));
		$toFieldData = array_diff(explode(',', $this->get('saved_toid')), $emailsInfo);
		$toEmailsData = array();
		$i = 1;
		foreach ($toFieldData as $value) {
			$toEmailInfo['to'.$i++] = array($value);
		}
		$attachments = $this->getAttachmentDetails();
		$status = false;

		// Merge Users module merge tags based on current user.
		$mergedDescription = getMergedDescription($this->get('description'), $currentUserModel->getId(), 'Users');
		$mergedSubject = getMergedDescription($this->get('subject'),$currentUserModel->getId(), 'Users');
		foreach($toEmailInfo as $id => $emails) {
			$inReplyToMessageId = ''; 
			$generatedMessageId = '';
			$mailer->reinitialize();
			$mailer->ConfigSenderInfo($fromEmail, $userName, $replyTo);
			$old_mod_strings = vglobal('mod_strings');
			$description = $this->get('description');
			$subject = $this->get('subject');
			$parentModule = $this->getEntityType($id);
			if ($parentModule) {
				$currentLanguage = Vtiger_Language_Handler::getLanguage();
				$moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage,$parentModule);
				vglobal('mod_strings', $moduleLanguageStrings['languageStrings']);
				$mergedDescriptionWithHyperLinkConversion = $this->replaceBrowserMergeTagWithValue($mergedDescription,$parentModule,$id); 
				if ($parentModule != 'Users') {
					//Retrieve MessageID from Mailroom table only if module is not users 
					$inReplyToMessageId = $mailer->retrieveMessageIdFromMailroom($id); 

					$generatedMessageId = $mailer->generateMessageID();
					//If there is no reference id exist in crm. 
					//Generate messageId for sending email and attach to mailer header
					if(empty($inReplyToMessageId)){
						$inReplyToMessageId = $generatedMessageId;
					}
					// Apply merge for non-Users module merge tags.
					$description = getMergedDescription($mergedDescriptionWithHyperLinkConversion, $id, $parentModule);
					$subject = getMergedDescription($mergedSubject, $id, $parentModule);
				} else {
					// Re-merge the description for user tags based on actual user.
					$description = getMergedDescription($mergedDescriptionWithHyperLinkConversion, $id, 'Users');
					$subject = getMergedDescription($mergedSubject, $id, 'Users');
					vglobal('mod_strings', $old_mod_strings);
				}
			}

			//If variable is not empty then add custom header 
			if(!empty($inReplyToMessageId)){ 
				$mailer->AddCustomHeader("In-Reply-To", $inReplyToMessageId); 
			} 

			if(!empty($generatedMessageId)){
				$mailer->MessageID = $generatedMessageId;
			}

			if (strpos($description, '$logo$')) {
				$description = str_replace('$logo$',"<img src='cid:companyLogo' />", $description);
				$logo = true;
			}

			foreach($emails as $email) {
				$mailer->Body = $description;
				if ($parentModule) {
					$mailer->Body = $this->convertUrlsToTrackUrls($mailer->Body, $id);;
					$mailer->Body .= $this->getTrackImageDetails($id, $this->isEmailTrackEnabled($parentModule));
				}
				//Checking whether user requested to add signature or not
				if($this->get('signature') == 'Yes'){
					$mailer->Signature = $currentUserModel->get('signature');
					if($mailer->Signature != '') {
						$mailer->Body.= '<br><br>'.decode_html($mailer->Signature);
					}
				}
				$mailer->Subject = decode_html(strip_tags($subject));

				$plainBody = decode_emptyspace_html($description);
				$plainBody = preg_replace(array("/<p>/i","/<br>/i","/<br \/>/i"),array("\n","\n","\n"),$plainBody);
				$plainBody .= "\n\n".$currentUserModel->get('signature');
				$plainBody = utf8_encode(strip_tags($plainBody));
				$plainBody = Emails_Mailer_Model::convertToAscii($plainBody);
				$plainBody = $this->convertUrlsToTrackUrls($plainBody, $id,'plain');
				$mailer->AltBody = $plainBody;
				$mailer->AddAddress($email);

				//Adding attachments to mail
				if(is_array($attachments)) {
					foreach($attachments as $attachment) {
						$fileNameWithPath = $rootDirectory.$attachment['path'].$attachment['fileid']."_".$attachment['attachment'];
						if(is_file($fileNameWithPath)) {
							$mailer->AddAttachment($fileNameWithPath, $attachment['attachment']);
						}
					}
				}
				if ($logo) {
					$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
					$companyLogoDetails = $companyDetails->getLogo();
					//While sending email template and which has '$logo$' then it should replace with company logo
					$mailer->AddEmbeddedImage($companyLogoDetails->get('imagepath'), 'companyLogo', 'attachment', 'base64', 'image/jpg');
				}

				$ccs = array_filter(explode(',',$this->get('ccmail')));
				$bccs = array_filter(explode(',',$this->get('bccmail')));

				if(!empty($ccs)) {
					foreach($ccs as $cc) $mailer->AddCC($cc);
				}
				if(!empty($bccs)) {
					foreach($bccs as $bcc) $mailer->AddBCC($bcc);
				}
			}
			// to convert external css to inline css
			$mailer->Body = Emails_Mailer_Model::convertCssToInline($mailer->Body);	
			//To convert image url to valid
			$mailer->Body = Emails_Mailer_Model::makeImageURLValid($mailer->Body);
			if ($addToQueue) {
				$status = $mailer->Send(false, $this->get('parent_id'));
			} else {
				$status = $mailer->Send(true);
			}
			if(!$status) {
				$status = $mailer->getError();
				//If mailer error, then update emailflag as saved
				if($status){
					$this->updateEmailFlag();
				}
			} else {
				//If mail sending is success store message Id for given crmId
				if($generatedMessageId && $id){
					$mailer->updateMessageIdByCrmId($generatedMessageId,$id);
				}

				$mailString=$mailer->getMailString();
				$mailBoxModel = MailManager_Mailbox_Model::activeInstance();
				$folderName = $mailBoxModel->folder();
				if(!empty($folderName) && !empty($mailString)) {
					$connector = MailManager_Connector_Connector::connectorWithModel($mailBoxModel, '');
					$message = str_replace("\n", "\r\n", $mailString);
					if (function_exists('mb_convert_encoding')) {
						$folderName = mb_convert_encoding($folderName, "UTF7-IMAP", "UTF-8");
					}
					imap_append($connector->mBox, $connector->mBoxUrl.$folderName, $message, "\\Seen");
				}
			}
		}
		return $status;
	}

	/**
	 * Returns the From Email address that will be used for the sent mails
	 * @return <String> - from email address
	 */
	function getFromEmailAddress() {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$fromEmail = false;
		$result = $db->pquery('SELECT from_email_field FROM vtiger_systems WHERE server_type=?', array('email'));
		if ($db->num_rows($result)) {
			$fromEmail = decode_html($db->query_result($result, 0, 'from_email_field'));
		}
		if (empty($fromEmail)) $fromEmail = $currentUserModel->get('email1');
		return $fromEmail;
	}

	/**
	 * Function returns the attachment details for a email
	 * @return <Array> List of attachments
	 */
	function getAttachmentDetails() {
		$db = PearDatabase::getInstance();

		$attachmentRes = $db->pquery("SELECT * FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid = ?", array($this->getId()));
		$numOfRows = $db->num_rows($attachmentRes);
		$attachmentsList = array();
		if($numOfRows) {
			for($i=0; $i<$numOfRows; $i++) {
				$attachmentsList[$i]['fileid'] = $db->query_result($attachmentRes, $i, 'attachmentsid');
				$attachmentsList[$i]['attachment'] = decode_html($db->query_result($attachmentRes, $i, 'name'));
				$path = $db->query_result($attachmentRes, $i, 'path');
				$attachmentsList[$i]['path'] = $path;
				$attachmentsList[$i]['size'] = filesize($path.$attachmentsList[$i]['fileid'].'_'.$attachmentsList[$i]['attachment']);
				$attachmentsList[$i]['type'] = $db->query_result($attachmentRes, $i, 'type');
				$attachmentsList[$i]['cid'] = $db->query_result($attachmentRes, $i, 'cid');
			}
		}

		$documentsList = $this->getRelatedDocuments();

		//Attachments are getting duplicated when forwarding a mail in Mail Manager.
		if($documentsList) {
			foreach ($documentsList as $document) {
				$flag = false;
				foreach ($attachmentsList as $attachment) {
					if($attachment['fileid'] == $document['fileid']) {
						$flag = true;
						break;
					}
				}
				if(!$flag) $attachmentsList[] = $document;
			}
		}

		return $attachmentsList;
	}

	/**
	 * Function returns the document details for a email
	 * @return <Array> List of Documents
	 */
	public function getRelatedDocuments() {
		$db = PearDatabase::getInstance();

		$documentRes = $db->pquery("SELECT * FROM vtiger_senotesrel
						INNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?
						INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid
						INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_crmentity.deleted = 0", array($this->getId()));
		$numOfRows = $db->num_rows($documentRes);

		$documentsList = array();
		if($numOfRows) {
			for($i=0; $i<$numOfRows; $i++) {
				$documentsList[$i]['name'] = $db->query_result($documentRes, $i, 'filename');
				$filesize = $db->query_result($documentRes, $i, 'filesize');
				$documentsList[$i]['size'] = $this->getFormattedFileSize($filesize);
				$documentsList[$i]['docid'] = $db->query_result($documentRes, $i, 'notesid');
				$documentsList[$i]['path'] = $db->query_result($documentRes, $i, 'path');
				$documentsList[$i]['fileid'] = $db->query_result($documentRes, $i, 'attachmentsid');
				$documentsList[$i]['attachment'] = decode_html($db->query_result($documentRes, $i, 'name'));
				$documentsList[$i]['type'] = $db->query_result($documentRes, $i, 'type');
			}
		}
		return $documentsList;
	}

	/**
	 * Function to get File size
	 * @param <Integer> $filesize
	 * @return <String> filesize
	 */
	public function getFormattedFileSize($filesize) {
		if($filesize < 1024) {
			$filesize = sprintf("%0.2f",round($filesize, 2)).'B';
		} else if($filesize > 1024 && $filesize < 1048576) {
			$filesize = sprintf("%0.2f",round($filesize/1024, 2)).'KB';
		} else if($filesize > 1048576) {
			$filesize = sprintf("%0.2f",round($filesize/(1024*1024), 2)).'MB';
		}
		return $filesize;
	}

	/**
	 * Function to save details of document and email
	 */
	public function saveDocumentDetails() {
		$db = PearDatabase::getInstance();
		$record = $this->getId();

		$documentIds = array_unique($this->get('documentids'));

		$count = count($documentIds);
		for ($i=0; $i<$count; $i++) {
			$db->pquery("INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?, ?)", array($record, $documentIds[$i]));
		}
	}

	/**
	 * Function which will remove all the exising document links with email
	 * @param <Array> $idList - array of ids
	 */
	public function deleteDocumentLink($idList = array()){
		$db = PearDatabase::getInstance();
		$query = 'DELETE FROM vtiger_senotesrel where crmid=?';
		$params = array($this->getId());
		if(count($idList) > 0) {
			$query .= 'AND notesid IN ('.generateQuestionMarks($idList).')';
			$params = array_merge($params,$idList);
		}
		$db->pquery($query,$params);
	}

	/**
	 * Function which will delete the existing attachments for the emails
	 * @param <Array> $emailAttachmentDetails - array of value which will be having fileid key as attachement id which need to be deleted
	 */
	public function deleteAttachment($emailAttachmentDetails = array()) {
		$db = PearDatabase::getInstance();

		if(count($emailAttachmentDetails) <= 0) {
			return;
		}
		$attachmentIdList = array();
		foreach($emailAttachmentDetails as $index => $attachInfo){
			$attachmentIdList[] = $attachInfo['fileid'];
		}

		$db->pquery('UPDATE vtiger_crmentity SET deleted=0 WHERE crmid IN('.generateQuestionMarks($attachmentIdList).')',$attachmentIdList);
		$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid IN('.generateQuestionMarks($attachmentIdList).')',$attachmentIdList);
		$db->pquery('DELETE FROM vtiger_seattachmentsrel WHERE crmid=? and attachmentsid IN('.generateQuestionMarks($attachmentIdList).')',
				array_merge(array($this->getId()),$attachmentIdList));

	}

	/**
	 * Function to check the total size of files is morethan max upload size or not
	 * @param <Array> $documentIds
	 * @return <Boolean> true/false
	 */
	public function checkUploadSize($documentIds = false) {
		$totalFileSize = 0;
		if (!empty ($_FILES)) {
			foreach ($_FILES as $fileDetails) {
				$totalFileSize = $totalFileSize + (int) $fileDetails['size'];
			}
		}
		if (!empty ($documentIds)) {
			$count = count($documentIds);
			for ($i=0; $i<$count; $i++) {
				try {
					$documentRecordModel = Vtiger_Record_Model::getInstanceById($documentIds[$i], 'Documents');
					$totalFileSize = $totalFileSize + (int) $documentRecordModel->get('filesize');
				} catch(Exception $ex) {
					continue;
				}
			}
		}
		$uploadLimit = Vtiger_Util_Helper::getMaxUploadSizeInBytes();
		if ($totalFileSize > $uploadLimit) {
			return false;
		}
		return true;
	}

	/**
	 * Function to get Track image details
	 * @param <Integer> $crmId
	 * @param <boolean> $emailTrack true/false
	 * @return <String>
	 */
	public function getTrackImageDetails($crmId, $emailTrack = true) {
		// return open tracking shorturl only if email tracking is enabled in configuration editor
		if($emailTrack){
			$emailId = $this->getId();
			$imageDetails = Vtiger_Functions::getTrackImageContent($emailId, $crmId);
			return $imageDetails;
		} else {
			return null;
		}
	}


	/**
	 * Function check email track enabled or not
	 * @return <boolean> true/false
	 */
	public function isEmailTrackEnabled() {
		$emailTracking = vglobal("email_tracking");
		if($emailTracking == 'Yes'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Function to update Email track(opens) details.
	 * @param <String> $parentId
	 */
	public function updateTrackDetails($parentId) {
		$db = PearDatabase::getInstance();
		$recordId = $this->getId();

		$db->pquery("INSERT INTO vtiger_email_access(crmid, mailid, accessdate, accesstime) VALUES(?, ?, ?, ?)", array($parentId, $recordId, date('Y-m-d'), date('Y-m-d H:i:s')));

		$result = $db->pquery("SELECT 1 FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		if ($db->num_rows($result)>0) {
			$db->pquery("UPDATE vtiger_email_track SET access_count = access_count+1 WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		} else {
			$db->pquery("INSERT INTO vtiger_email_track(crmid, mailid, access_count) values(?, ?, ?)", array($parentId, $recordId, 1));
		}
	}

	/**
	 * Function to set Access count value by default as 0
	 */
	public function setAccessCountValue() {
		$record = $this->getId();
		$moduleName = $this->getModuleName();

		$focus = new $moduleName();
		$focus->setEmailAccessCountValue($record);
	}

	public function getClickCountValue($parentId){
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT click_count FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $this->getId()));
		return $db->query_result($result, 0, 'click_count');
	}

	/**
	 * Function to get Access count value
	 * @param <String> $parentId
	 * @return <String>
	 */
	public function getAccessCountValue($parentId) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT access_count FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $this->getId()));
		return $db->query_result($result, 0, 'access_count');
	}

	public static function getTrackingInfo($emailIds,$parentId) {
		if(!is_array($emailIds)) {
			$emailIds = array($emailIds);
		}
		$trackingInfo = array();

		if(empty($emailIds)) {
			return $trackingInfo;
		}
		$db = PearDatabase::getInstance();

		$sql = 'SELECT mailid, access_count,click_count FROM vtiger_email_track WHERE crmid = ? AND mailid IN('.generateQuestionMarks($emailIds).')';
		$result = $db->pquery($sql, array($parentId, $emailIds));
		$numRows = $db->num_rows($result);
		if($numRows > 0) {
			for($i=0;$i<$numRows;$i++){
				$row = $db->query_result_rowdata($result,$i);
				$trackingInfo[$row['mailid']] = array('access_count' => $row['access_count'],'click_count' => $row['click_count']);
			}
		}
		return $trackingInfo;
	}

	public function getEmailFlag() {
		if(!array_key_exists('email_flag', $this->getData())) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT email_flag FROM vtiger_emaildetails WHERE emailid = ?", array($this->getId()));
			if($db->num_rows($result) > 0) {
				$this->set('email_flag', $db->query_result($result, 0, 'email_flag'));
			} else {
				return false;
			}
		}
		return $this->get('email_flag');
	}

	function getEntityType($id) {
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$emailRelatedModules = $moduleModel->getEmailRelatedModules();
		$relatedModule = '';
		if (!empty($id)) {
			$sql = "SELECT setype FROM vtiger_crmentity WHERE crmid=?";
			$result = $db->pquery($sql, array($id));
			$relatedModule = $db->query_result($result, 0, "setype");

			if(!in_array($relatedModule, $emailRelatedModules)){
				$sql = 'SELECT id FROM vtiger_users WHERE id=?';
				$result = $db->pquery($sql, array($id));
				if($db->num_rows($result) > 0){
					$relatedModule = 'Users';
				}
			}
		}
		return $relatedModule;
	}

	/**
	 * Function stores emailid,parentmodule and generates shorturl 
	 * @param type $parentModule 
	 * @return type 
	 */
	public function getTrackingShortUrl($parentModule) {
		$options = array(
			'handler_path' => 'modules/Emails/handlers/ViewInBrowser.php',
			'handler_class' => 'Emails_ViewInBrowser_Handler',
			'handler_function' => "viewInBrowser",
			'handler_data' => array(
				'emailId' => $this->getId(),
				'parentModule' => $parentModule
			)
		);
		$trackURL = Vtiger_ShortURL_Helper::generateURL($options);
		return $trackURL;
	}

	/**
	 * Function to replace browser merge tag with value 
	 * @param type $mergedDescription 
	 * @param type $parentModule 
	 * @param type $recipientId 
	 * @return type 
	 */
	public function replaceBrowserMergeTagWithValue($mergedDescription, $parentModule, $recipientId) {
		global $application_unique_key;
		if (!$this->trackURL) {
			$this->trackURL = $this->getTrackingShortUrl($parentModule);
		}
		$receiverId = $parentModule[0] . $recipientId;
		$urlParameters = http_build_query(array('rid' => $receiverId, 'applicationKey' => $application_unique_key));
		$rlock = $this->generateSecureKey($urlParameters);
		$URL = $this->trackURL . "&$urlParameters" . "&rv=$rlock";
		return str_replace(EmailTemplates_Module_Model::$BROWSER_MERGE_TAG, $URL, $mergedDescription);
	}

	public function generateSecureKey($urlParameters) {
		return md5($urlParameters);
	}

	/**
	 * Functiont to track clicks
	 * @param <int> $parentId
	 */
	public function trackClicks($parentId) {
		$db = PearDatabase::getInstance();
		$recordId = $this->getId();

		$db->pquery("INSERT INTO vtiger_email_access(crmid, mailid, accessdate, accesstime) VALUES(?, ?, ?, ?)", array($parentId, $recordId, date('Y-m-d'), date('Y-m-d H:i:s')));

		$result = $db->pquery("SELECT 1 FROM vtiger_email_track WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		if ($db->num_rows($result) > 0) {
			$db->pquery("UPDATE vtiger_email_track SET click_count = click_count+1 WHERE crmid = ? AND mailid = ?", array($parentId, $recordId));
		} else {
			$db->pquery("INSERT INTO vtiger_email_track(crmid, mailid, click_count) values(?, ?, ?)", array($parentId, $recordId, 1));
		}
	}

	/**
	 * Function to get Sender Name for the email
	 * @return <String> Sender Name or Email
	 */
	public function getSenderName($relatedModule = false, $relatedRecordId = false) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT from_email,idlists FROM vtiger_emaildetails WHERE emailid = ?", array($this->getId()));
		if ($db->num_rows($result) > 0) {
				$fromEmail = $db->query_result($result, 0, 'from_email');
				$supportEmail = vglobal('HELPDESK_SUPPORT_EMAIL_ID');
				$supportName = vglobal('HELPDESK_SUPPORT_NAME');
				if ($fromEmail == $supportEmail && !empty($supportName)) {
					return $supportName;
				}
				$moduleModel = $this->getModule();
				$emails = $moduleModel->searchEmails($fromEmail);
				if ($emails) {
					if ($emails[$relatedModule][$relatedRecordId]) {
						return $emails[$relatedModule][$relatedRecordId][0]['name'];
					}

					if ($emails['Users']) {
						$emailInfo = array_values($emails['Users']);
					} else {
						$emailsInfo = array_values($emails);
						$emailInfo = array_values($emailsInfo[0]);
					}
					return $emailInfo[0][0]['name'];
				}
				return $fromEmail;
			} else {
				return false;
			}
	}

	public function convertUrlsToTrackUrls($content, $crmid, $type = 'html') {
		if ($this->isEmailTrackEnabled()) {
			$extractedUrls = Vtiger_Functions::getUrlsFromHtml($content);

			foreach ($extractedUrls as $sourceUrl => $value) {
				$trackingUrl = $this->getTrackUrlForClicks($crmid, $sourceUrl);
				$content = $this->replaceLinkWithShortUrl($content, $trackingUrl, $sourceUrl, $type);
			}
		}
		return $content;
	}

	public function replaceLinkWithShortUrl($content, $toReplace, $search, $type) {
		if ($type == 'html') {
			$search = '"' . $search . '"';
			$toReplace = '"' . $toReplace . '"';
		}
		$pos = strpos($content, $search);

		if ($pos != false) {
			$replacedContent = substr_replace($content, $toReplace, $pos) . substr($content, $pos + strlen($search));
			return $replacedContent;
		}

		return $content;
	}

	public function getTrackUrlForClicks($parentId, $redirectUrl = false) {
		$siteURL = vglobal('site_URL');
		$applicationKey = vglobal('application_unique_key');
		$recordId = $this->getId();
		$trackURL = "$siteURL/modules/Emails/actions/TrackAccess.php?record=$recordId&parentId=$parentId&applicationKey=$applicationKey&method=click";
		if ($redirectUrl) {
			$encodedRedirUrl = rawurlencode($redirectUrl);
			$trackURL .= "&redirectUrl=$encodedRedirUrl";
		}
		return $trackURL;
	}

	/**
	 * Function to save email lookup value for searching
	 * @param type $fieldName
	 * @param type $values
	 */
	function recieveEmailLookup($fieldId, $values) {
		$db = PearDatabase::getInstance();
		$params = array($values['crmid'], $values['setype'], $values[$fieldId], $fieldId);

		$db->pquery('INSERT INTO vtiger_emailslookup
					(crmid, setype, value, fieldid) 
					VALUES(?,?,?,?) 
					ON DUPLICATE KEY 
					UPDATE value=VALUES(value)', $params);
	}

	 /**
	 * Function to delete email lookup value for searching
	 * @param type $crmid
	 * @param type $fieldid
	 */
	function deleteEmailLookup($crmid, $fieldid = false) {
		$db = PearDatabase::getInstance();
		if ($fieldid) {
			$params = array($crmid, $fieldid);
			$db->pquery('DELETE FROM vtiger_emailslookup WHERE crmid=? AND fieldid=?', $params);
		} else {
			$params = array($crmid);
			$db->pquery('DELETE FROM vtiger_emailslookup WHERE crmid=?', $params);
		}
	}

	 /**
	 * Function to update Email flag if SMTP fails
	 */
	public function updateEmailFlag() {
		$db = PearDatabase::getInstance();
		$query = 'UPDATE vtiger_emaildetails SET email_flag="SAVED" WHERE emailid=?';
		$db->pquery($query, array($this->get('id')));
	}

	function replaceMergeTags($id) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$entityType = $this->getEntityType($id);

		$description = getMergedDescription($this->get('description'), $currentUserModel->getId(), 'Users');
		$subject = getMergedDescription($this->get('subject'), $currentUserModel->getId(), 'Users');

		$description = getMergedDescription($description, $id, $entityType);
		$subject = getMergedDescription($subject, $id, $entityType);

		if (strpos($description, '$logo$')) {
			$description = str_replace('$logo$', "<img src='cid:logo' />", $description);
		}

		$this->set('description', $description);
		$this->set('subject', strip_tags($subject));
	}

	function getReplyToEmail() {
		$db = PearDatabase::getInstance();
		$defaultReplyTo = vglobal('default_reply_to');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$replyTo = $currentUserModel->get('email1');

		if ($defaultReplyTo == 'outgoing_server_from_email') {
			$result = $db->pquery('SELECT from_email_field FROM vtiger_systems WHERE server_type=?', array('email'));
			if ($db->num_rows($result)) {
				$fromEmail = decode_html($db->query_result($result, 0, 'from_email_field'));
			}
			if (!empty($fromEmail)) {
				$replyTo = $fromEmail;
			}
		} else if ($defaultReplyTo == 'hepldesk_support_email') {
			$helpDeskEmail = vglobal('HELPDESK_SUPPORT_EMAIL_ID');
			if (!empty($helpDeskEmail)) {
				$replyTo = $helpDeskEmail;
			}
		}

		return $replyTo;
	}
}
