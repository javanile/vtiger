<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

require_once('modules/Emails/Emails.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/ModComments/ModComments.php');
require_once('modules/Users/Users.php');
require_once('modules/Documents/Documents.php');
require_once ('modules/Leads/Leads.php');
require_once ('modules/Contacts/Contacts.php');
require_once ('modules/Accounts/Accounts.php');

/**
 * Mail Scanner Action
 */
class Vtiger_MailScannerAction {
	// actionid for this instance
	var $actionid	= false;
	// scanner to which this action is associated
	var $scannerid	= false;
	// type of mailscanner action
	var $actiontype	= false;
	// text representation of action
	var $actiontext	= false;
	// target module for action
	var $module		= false;
	// lookup information while taking action
	var $lookup		= false;

	// Storage folder to use
	var $STORAGE_FOLDER = 'storage/mailscanner/';

	var $recordSource = 'MAIL SCANNER';

	/** DEBUG functionality */
	var $debug		= false;
	function log($message) {
		global $log;
		if($log && $this->debug) { $log->debug($message); }
		else if($this->debug) echo "$message\n";
	}

	/**
	 * Constructor.
	 */
	function __construct($foractionid) {
		$this->initialize($foractionid);
	}

	/**
	 * Initialize this instance.
	 */
	function initialize($foractionid) {
		global $adb;
		$result = $adb->pquery("SELECT * FROM vtiger_mailscanner_actions WHERE actionid=? ORDER BY sequence", Array($foractionid));

		if($adb->num_rows($result)) {
			$this->actionid		= $adb->query_result($result, 0, 'actionid');
			$this->scannerid	= $adb->query_result($result, 0, 'scannerid');
			$this->actiontype	= $adb->query_result($result, 0, 'actiontype');
			$this->module		= $adb->query_result($result, 0, 'module');
			$this->lookup		= $adb->query_result($result, 0, 'lookup');
			$this->actiontext	= "$this->actiontype,$this->module,$this->lookup";
		}
	}

	/**
	 * Create/Update the information of Action into database.
	 */
	function update($ruleid, $actiontext) {
		global $adb;

		$inputparts = explode(',', $actiontext);
		$this->actiontype	= $inputparts[0]; // LINK, CREATE
		$this->module		= $inputparts[1]; // Module name
		$this->lookup		= $inputparts[2]; // FROM, TO

		$this->actiontext = $actiontext;

		if($this->actionid) {
			$adb->pquery("UPDATE vtiger_mailscanner_actions SET scannerid=?, actiontype=?, module=?, lookup=? WHERE actionid=?",
				Array($this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->actionid));
		} else {
			$this->sequence = $this->__nextsequence();
			$adb->pquery("INSERT INTO vtiger_mailscanner_actions(scannerid, actiontype, module, lookup, sequence) VALUES(?,?,?,?,?)",
				Array($this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->sequence));
			$this->actionid = $adb->database->Insert_ID();
		}
		$checkmapping = $adb->pquery("SELECT COUNT(*) AS ruleaction_count FROM vtiger_mailscanner_ruleactions
			WHERE ruleid=? AND actionid=?", Array($ruleid, $this->actionid));
		if($adb->num_rows($checkmapping) && !$adb->query_result($checkmapping, 0, 'ruleaction_count')) {
			$adb->pquery("INSERT INTO vtiger_mailscanner_ruleactions(ruleid, actionid) VALUES(?,?)",
				Array($ruleid, $this->actionid));
		}
	}

	/**
	 * Delete the actions from tables.
	 */
	function delete() {
		global $adb;
		if($this->actionid) {
			$adb->pquery("DELETE FROM vtiger_mailscanner_actions WHERE actionid=?", Array($this->actionid));
			$adb->pquery("DELETE FROM vtiger_mailscanner_ruleactions WHERE actionid=?", Array($this->actionid));
		}
	}

	/**
	 * Get next sequence of Action to use.
	 */
	function __nextsequence() {
		global $adb;
		$seqres = $adb->pquery("SELECT max(sequence) AS max_sequence FROM vtiger_mailscanner_actions", Array());
		$maxsequence = 0;
		if($adb->num_rows($seqres)) {
			$maxsequence = $adb->query_result($seqres, 0, 'max_sequence');
		}
		++$maxsequence;
		return $maxsequence;
	}

	/**
	 * Apply the action on the mail record.
	 */
	function apply($mailscanner, $mailrecord, $mailscannerrule, $matchresult) {
		$returnid = false;
		if($this->actiontype == 'CREATE') {
			if($this->module == 'HelpDesk') {
				$returnid = $this->__CreateTicket($mailscanner, $mailrecord,$mailscannerrule);
			} else if ($this->module == 'Contacts') {
				$returnid = $this->__CreateContact($mailscanner, $mailrecord,$mailscannerrule);
			} else if ($this->module == 'Leads') {
				$returnid = $this->__CreateLead($mailscanner, $mailrecord,$mailscannerrule);
			} else if ($this->module == 'Accounts') {
				$returnid = $this->__CreateAccount($mailscanner, $mailrecord,$mailscannerrule);
			}
		} else if($this->actiontype == 'LINK') {
			$returnid = $this->__LinkToRecord($mailscanner, $mailrecord);
		} else if ($this->actiontype == 'UPDATE') {
			if ($this->module == 'HelpDesk') {
				$returnid = $this->__UpdateTicket($mailscanner, $mailrecord, $mailscannerrule->hasRegexMatch($matchresult),$mailscannerrule);
			}
		}
		return $returnid;
	}

	/**
	 * Update ticket action.
	 */
	function __UpdateTicket($mailscanner, $mailrecord, $regexMatchInfo,$mailscannerrule) {
		global $adb;
		$returnid = false;

		$usesubject = false;
		if($this->lookup == 'SUBJECT') {
			// If regex match was performed on subject use the matched group
			// to lookup the ticket record
			if($regexMatchInfo) $usesubject = $regexMatchInfo['matches'];
			else $usesubject = $mailrecord->_subject;

			// Get the ticket record that was created by SENDER earlier
			$fromemail = $mailrecord->_from[0];

			$linkfocus = $mailscanner->GetTicketRecord($usesubject, $fromemail);

			$commentedBy = $mailscanner->LookupContact($fromemail);
			if(!$commentedBy) {
				$commentedBy = $mailscanner->LookupAccount($fromemail);
			}

			// If matching ticket is found, update comment, attach email
			if($linkfocus) {
				$commentFocus = new ModComments();
				$commentFocus->column_fields['commentcontent'] = $mailrecord->getBodyText();
				$commentFocus->column_fields['related_to'] = $linkfocus->id;
				$commentFocus->column_fields['assigned_user_id'] = $mailscannerrule->assigned_to;
				if($commentedBy) {
					$commentFocus->column_fields['customer'] = $commentedBy;
					$commentFocus->column_fields['from_mailconverter'] = 1;
				} else {
					$commentFocus->column_fields['userid'] = $mailscannerrule->assigned_to;
				}
				$commentFocus->saveentity('ModComments');

				// Set the ticket status to Open if its Closed
				$adb->pquery("UPDATE vtiger_troubletickets set status=? WHERE ticketid=? AND status='Closed'", Array('Open', $linkfocus->id));

				$returnid = $this->__CreateNewEmail($mailrecord, $this->module, $linkfocus);

			} else {
				// TODO If matching ticket was not found, create ticket?
				// $returnid = $this->__CreateTicket($mailscanner, $mailrecord);
			}
		}
		return $returnid;
	}

	/**
	 * Create ticket action.
	 */
	function __CreateContact($mailscanner, $mailrecord, $mailscannerrule) {
		if($mailscanner->LookupContact($mailrecord->_from[0])) {
			$this->lookup = 'FROM';
			return $this->__LinkToRecord($mailscanner, $mailrecord);
		}
		$name = $this->getName($mailrecord);
		$email = $mailrecord->_from[0];
		$description = $mailrecord->getBodyText();

		$contact = new Contacts();
		$this->setDefaultValue('Contacts', $contact);
		$contact->column_fields['firstname'] = $name[0];
		$contact->column_fields['lastname'] = $name[1];
		$contact->column_fields['email'] = $email;
		$contact->column_fields['assigned_user_id'] = $mailscannerrule->assigned_to;
		$contact->column_fields['description'] = $description;
		$contact->column_fields['source'] = $this->recordSource;

		try {
			$contact->save('Contacts');

			$this->__SaveAttachements($mailrecord, 'Contacts', $contact);
			return $contact->id;
		} catch (Exception $e) {
			//TODO - Review
			return false;
		}
	}

	/**
	 * Create Lead action.
	 */
	function __CreateLead($mailscanner, $mailrecord, $mailscannerrule) {
		if($mailscanner->LookupLead($mailrecord->_from[0])) {
			$this->lookup = 'FROM';
			return $this->__LinkToRecord($mailscanner, $mailrecord);
		}
		$name = $this->getName($mailrecord);
		$email = $mailrecord->_from[0];
		$description = $mailrecord->getBodyText();

		$lead = new Leads();
		$this->setDefaultValue('Leads', $lead);
		$lead->column_fields['firstname'] = $name[0];
		$lead->column_fields['lastname'] = $name[1];
		$lead->column_fields['email'] = $email;
		$lead->column_fields['assigned_user_id'] = $mailscannerrule->assigned_to;
		$lead->column_fields['description'] = $description;
		$lead->column_fields['source'] = $this->recordSource;

		try {
			$lead->save('Leads');

			$this->__SaveAttachements($mailrecord, 'Leads', $lead);

			return $lead->id;
		} catch (Exception $e) {
			//TODO - Review
			return false;
		}
	}

	/**
	 * Create Account action.
	 */
	function __CreateAccount($mailscanner, $mailrecord, $mailscannerrule) {
		if($mailscanner->LookupAccount($mailrecord->_from[0])) {
			$this->lookup = 'FROM';
			return $this->__LinkToRecord($mailscanner, $mailrecord);
		}
		$name = $this->getName($mailrecord);
		$email = $mailrecord->_from[0];
		$description = $mailrecord->getBodyText();

		$account = new Accounts();
		$this->setDefaultValue('Accounts', $account);
		$account->column_fields['accountname'] = $name[0].' '.$name[1];
		$account->column_fields['email1'] = $email;
		$account->column_fields['assigned_user_id'] = $mailscannerrule->assigned_to;
		$account->column_fields['description'] = $description;
		$account->column_fields['source'] = $this->recordSource;

		try {
			$account->save('Accounts');
			$this->__SaveAttachements($mailrecord, 'Accounts', $account);

			return $account->id;
		} catch (Exception $e) {
			//TODO - Review
			return false;
		}
	}

	/**
	 * Create ticket action.
	 */
	function __CreateTicket($mailscanner, $mailrecord, $mailscannerrule) {
		// Prepare data to create trouble ticket
		$usetitle = $mailrecord->_subject;
		$description = $mailrecord->getBodyText();

		// There will be only on FROM address to email, so pick the first one
		$fromemail = $mailrecord->_from[0];
		$contactLinktoid = $mailscanner->LookupContact($fromemail);
		if(!$contactLinktoid) {
			$contactLinktoid = $this-> __CreateContact($mailscanner, $mailrecord, $mailscannerrule);
		}
		if ($contactLinktoid)
			$linktoid = $mailscanner->getAccountId($contactLinktoid);
		if(!$linktoid)
			$linktoid = $mailscanner->LookupAccount($fromemail);

		// Create trouble ticket record
		$ticket = new HelpDesk();
		$this->setDefaultValue('HelpDesk', $ticket);
		if(empty($ticket->column_fields['ticketstatus']) || $ticket->column_fields['ticketstatus'] == '?????')
			$ticket->column_fields['ticketstatus'] = 'Open';
		$ticket->column_fields['ticket_title'] = $usetitle;
		$ticket->column_fields['description'] = $description;
		$ticket->column_fields['assigned_user_id'] = $mailscannerrule->assigned_to;
		if ($contactLinktoid)
			$ticket->column_fields['contact_id'] = $contactLinktoid;
		if ($linktoid)
			$ticket->column_fields['parent_id'] = $linktoid;

		$ticket->column_fields['source'] = $this->recordSource;

		try {
			$ticket->save('HelpDesk');

			// Associate any attachement of the email to ticket
			$this->__SaveAttachements($mailrecord, 'HelpDesk', $ticket);

			if($contactLinktoid)
				$relatedTo = $contactLinktoid;
			else
				$relatedTo = $linktoid;
			$this->linkMail($mailscanner, $mailrecord, $relatedTo);

			return $ticket->id;
		} catch (Exception $e) {
			//TODO - Review
			return false;
		}
	}

	/**
	 * Function to link email record to contact/account/lead
	 * record if exists with same email id
	 * @param type $mailscanner
	 * @param type $mailrecord
	 */
	function linkMail($mailscanner, $mailrecord, $relatedTo) {
		$fromemail = $mailrecord->_from[0];

		$linkfocus = $mailscanner->GetContactRecord($fromemail, $relatedTo);
		$module = 'Contacts';
		if(!$linkfocus) {
			$linkfocus = $mailscanner->GetAccountRecord($fromemail, $relatedTo);
			$module = 'Accounts';
		}

		if($linkfocus) {
			$this->__CreateNewEmail($mailrecord, $module, $linkfocus);
		}
	}

	/**
	 * Add email to CRM record like Contacts/Accounts
	 */
	function __LinkToRecord($mailscanner, $mailrecord) {
		$linkfocus = false;

		$useemail = false;
		if($this->lookup == 'FROM') $useemail = $mailrecord->_from;
		else if($this->lookup == 'TO') $useemail = $mailrecord->_to;

		if ($this->module == 'Contacts') {
			foreach ($useemail as $email) {
				$linkfocus = $mailscanner->GetContactRecord($email);
				if ($linkfocus)
					break;
			}
		} else if ($this->module == 'Accounts') {
			foreach ($useemail as $email) {
				$linkfocus = $mailscanner->GetAccountRecord($email);
				if ($linkfocus)
					break;
			}
		} else if ($this->module == 'Leads') {
			foreach ($useemail as $email) {
				$linkfocus = $mailscanner->GetLeadRecord($email);
				if ($linkfocus)
					break;
			}
		}

		$returnid = false;
		if($linkfocus) {
			$returnid = $this->__CreateNewEmail($mailrecord, $this->module, $linkfocus);
		}
		return $returnid;
	}

	/**
	 * Create new Email record (and link to given record) including attachements
	 */
	function __CreateNewEmail($mailrecord, $module, $linkfocus) {
		global $current_user, $adb;
		if(!$current_user) {
			$current_user = Users::getActiveAdminUser();
		}
		$assignedToId = $linkfocus->column_fields['assigned_user_id'];
		if(vtws_getOwnerType($assignedToId) == 'Groups') {
			$assignedToId = Users::getActiveAdminId();
		}

		$focus = new Emails();
		$focus->column_fields['parent_type'] = $module;
		$focus->column_fields['activitytype'] = 'Emails';
		$focus->column_fields['parent_id'] = "$linkfocus->id@-1|";
		$focus->column_fields['subject'] = $mailrecord->_subject;

		$focus->column_fields['description'] = $mailrecord->getBodyHTML();
		$focus->column_fields['assigned_user_id'] = $assignedToId;
		$focus->column_fields["date_start"] = date('Y-m-d', $mailrecord->_date);
		$focus->column_fields["time_start"] = gmdate("H:i:s");
		$focus->column_fields["email_flag"] = 'MAILSCANNER';

		$from=$mailrecord->_from[0];
		$to = $mailrecord->_to[0];
		$cc = (!empty($mailrecord->_cc))? implode(',', $mailrecord->_cc) : '';
		$bcc= (!empty($mailrecord->_bcc))? implode(',', $mailrecord->_bcc) : '';
		$flag=''; // 'SENT'/'SAVED'
		//emails field were restructured and to,bcc and cc field are JSON arrays
		$focus->column_fields['from_email'] = $from;
		$focus->column_fields['saved_toid'] = $to;
		$focus->column_fields['ccmail'] = $cc;
		$focus->column_fields['bccmail'] = $bcc;
		$focus->column_fields['source'] = $this->recordSource;
		$focus->save('Emails');

		$emailid = $focus->id;
		$this->log("Created [$focus->id]: $mailrecord->_subject linked it to " . $linkfocus->id);

		// TODO: Handle attachments of the mail (inline/file)
		$this->__SaveAttachements($mailrecord, 'Emails', $focus);

		return $emailid;
	}

	/**
	 * Save attachments from the email and add it to the module record.
	 */
	function __SaveAttachements($mailrecord, $basemodule, $basefocus) {
		global $adb;

		// If there is no attachments return
		if(!$mailrecord->_attachments) return;

		$userid = $basefocus->column_fields['assigned_user_id'];
		$setype = "$basemodule Attachment";

		$date_var = $adb->formatDate(date('YmdHis'), true);

		foreach($mailrecord->_attachments as $filename=>$filecontent) {
			$attachid = $adb->getUniqueId('vtiger_crmentity');
			$description = $filename;
			$usetime = $adb->formatDate($date_var, true);

			$adb->pquery("INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid,
				modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				Array($attachid, $userid, $userid, $userid, $setype, $description, $usetime, $usetime, 1, 0));

			$issaved = $this->__SaveAttachmentFile($attachid, $filename, $filecontent);
			if($issaved) {
				// Create document record
				$document = new Documents();
				$document->column_fields['notes_title']		= $filename;
				$document->column_fields['filename']		= $filename;
				$document->column_fields['filesize']		= mb_strlen($filecontent, '8bit');
				$document->column_fields['filestatus']		= 1;
				$document->column_fields['filelocationtype']= 'I';
				$document->column_fields['folderid']		= 1; // Default Folder
				$document->column_fields['assigned_user_id']= $userid;
				$document->column_fields['source']			= $this->recordSource;
				$document->save('Documents');

				// Link file attached to document
				$adb->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",
					Array($document->id, $attachid));

				// Link document to base record
				$adb->pquery("INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)",
					Array($basefocus->id, $document->id));

				// Link document to Parent entity - Account/Contact/...
				list($eid,$junk)=explode('@',$basefocus->column_fields['parent_id']);
				$adb->pquery("INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)",
					Array($eid, $document->id));

				// Link Attachement to the Email
				$adb->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",
					Array($basefocus->id, $attachid));
			}
		}
	}

	/**
	 * Save the attachment to the file
	 */
	function __SaveAttachmentFile($attachid, $filename, $filecontent) {
		global $adb;

		$dirname = $this->STORAGE_FOLDER;
		if(!is_dir($dirname)) mkdir($dirname);

		$description = $filename;
		$filename = str_replace(' ', '-', $filename);
		$saveasfile = "$dirname$attachid" . "_$filename";
		if(!file_exists($saveasfile)) {

			$this->log("Saved attachement as $saveasfile\n");

			$fh = fopen($saveasfile, 'wb');
			fwrite($fh, $filecontent);
			fclose($fh);
		}

		$mimetype = MailAttachmentMIME::detect($saveasfile);

		$adb->pquery("INSERT INTO vtiger_attachments SET attachmentsid=?, name=?, description=?, type=?, path=?",
			Array($attachid, $filename, $description, $mimetype, $dirname));

		return true;
	}

	function setDefaultValue($module, $moduleObj) { 
		$moduleInstance = Vtiger_Module_Model::getInstance($module);

		$fieldInstances = Vtiger_Field_Model::getAllForModule($moduleInstance);
		foreach($fieldInstances as $blockInstance) {
			foreach($blockInstance as $fieldInstance) {
				$fieldName = $fieldInstance->getName();
				$defaultValue = $fieldInstance->getDefaultFieldValue();
				if($defaultValue) {
					$moduleObj->column_fields[$fieldName] = decode_html($defaultValue);
				}
				if($fieldInstance->isMandatory() && !$defaultValue) {
					$moduleObj->column_fields[$fieldName] = Vtiger_Util_Helper::getDefaultMandatoryValue($fieldInstance->getFieldDataType());
				}
			}
		}
	}

	/**
	 * Function to get Mail Sender's Name
	 * @param <Vtiger_MailRecord Object> $mailrecord
	 * @return <Array> containing First Name and Last Name
	 */
	function getName($mailrecord) {
		$name = $mailrecord->_fromname;
		if(!empty($name)) {
			$nameParts = explode(' ', $name);
			if(count($nameParts) > 1) {
				$firstName = $nameParts[0];
				unset($nameParts[0]);
				$lastName = implode(' ', $nameParts);
			} else {
				$firstName = '';
				$lastName = $nameParts[0];
			}
		} else {
			$firstName = '';
			$lastName = $mailrecord->_from[0];
		}

		return array($firstName, $lastName);
	}

}
?>
