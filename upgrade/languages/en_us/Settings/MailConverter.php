<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
$languageStrings = array(
	'MailConverter' => 'Mail Converter',
	'MailConverter_Description' => 'Convert emails to respective records',
	'MAILBOX' => 'MailBox',
	'RULE' => 'Rule',
	'LBL_ADD_RECORD' => 'Add MailBox',
	'ALL' => 'All',
	'UNSEEN' => 'Unread',
	'LBL_MARK_READ' => 'Mark Read',
	'SEEN' => 'Read',
	'LBL_EDIT_MAILBOX' => 'Edit MailBox',
	'LBL_CREATE_MAILBOX' => 'Create MailBox',
	'LBL_BACK_TO_MAILBOXES' => 'Back to MailBoxes',
	'LBL_MARK_MESSAGE_AS' => 'Mark message as',
	'LBL_CREATE_MAILBOX_NOW' => 'Create Mailbox now',
	'LBL_ADDING_NEW_MAILBOX' => 'Adding New Mail Box',
	'MAILBOX_DETAILS' => 'Mail Box Details',
	'SELECT_FOLDERS' => 'Select Folders',
	'ADD_RULES' => 'Add Rules',
	'CREATE_Leads_SUBJECT' => 'Create Lead',
	'CREATE_Contacts_SUBJECT' => 'Create Contact',
	'CREATE_Accounts_SUBJECT' => 'Create Organization',
	'LBL_ACTIONS' => 'Actions',
	'LBL_MAILBOX' => 'Mail Box',
	'LBL_RULE' => 'Rule',
	'LBL_CONDITIONS' => 'Conditions',
	'LBL_FOLDERS_SCANNED' => 'Folders Scanned',
	'LBL_NEXT' => 'Next',
	'LBL_FINISH' => 'Finish',
	'TO_CHANGE_THE_FOLDER_SELECTION_DESELECT_ANY_OF_THE_SELECTED_FOLDERS' => 'To change the folder selection deselect any of the selected folders',
	'LBL_MAILCONVERTER_DESCRIPTION' => "Mail Converter enables you to configure your mailbox to scan your emails and create appropriate entities in Vtiger CRM.<br />You'll also need to define rules to specify what actions should be performed on your emails.<br />Your emails are scanned automatically, unless you've disabled Mail Scanner task in Scheduler. <br /><br /><br />",
	
	//Server Messages
	'LBL_MAX_LIMIT_ONLY_TWO' => 'You can configure only two mailboxes',
	'LBL_IS_IN_RUNNING_STATE' => 'In running state',
	'LBL_SAVED_SUCCESSFULLY' => 'Saved successfully',
	'LBL_CONNECTION_TO_MAILBOX_FAILED' => 'Connecting to mailbox failed.',
	'LBL_DELETED_SUCCESSFULLY' => 'Deleted Successfully',
	'LBL_RULE_DELETION_FAILED' => 'Rule deletion failed',
	'LBL_RULES_SEQUENCE_INFO_IS_EMPTY' => 'Rules sequence info is empty',
	'LBL_SEQUENCE_UPDATED_SUCCESSFULLY' => 'Sequence updated successfully',
	'LBL_SCANNED_SUCCESSFULLY' => 'Scanned successfully',

	//Field Names
	'scannername' => 'Scanner Name',
	'server' => 'IMAP Server Name',
	'protocol' => 'Protocol',
	'username' => 'User Name',
	'password' => 'Password',
	'ssltype' =>  'SSL Type',
	'sslmethod' => 'SSL Method',
	'connecturl' => 'Connect Url',
	'searchfor' => 'Look For',
	'markas' => 'After Scan',
    'isvalid' => 'Status',
    'time_zone' => 'Mail Server Timezone',
    'scanfrom' => 'Scan Mails From',
    'YESTERDAY' => 'Yesterday',

	//Field values & Messages
	'LBL_ENABLE' => 'Enable',
	'LBL_DISABLE' =>'Disable',
	'LBL_STATUS_MESSAGE' => 'Check To make active',
	'LBL_VALIDATE_SSL_CERTIFICATE' => 'Validate SSL Certificate',
	'LBL_DO_NOT_VALIDATE_SSL_CERTIFICATE' => 'Do Not Validate SSL Certificate',
	'LBL_ALL_MESSAGES_FROM_LAST_SCAN' => 'All messages from last scan',
	'LBL_UNREAD_MESSAGES_FROM_LAST_SCAN' => 'Unread messages from last scan',
	'LBL_MARK_MESSAGES_AS_READ' => 'Mark messages as read',
	'LBL_I_DONT_KNOW' => "I don't know",

	//Mailbox Actions
	'LBL_SCAN_NOW' => 'Scan Now',
	'LBL_RULES_LIST' => 'Rules List',
	'LBL_SELECT_FOLDERS' => 'Select Folders',

	//Action Messages
	'LBL_DELETED_SUCCESSFULLY' => 'Deleted successfully',
	'LBL_RULE_DELETION_FAILED' => 'Rule deletion failed',
	'LBL_SAVED_SUCCESSFULLY' => 'Saved successfully',
	'LBL_SCANED_SUCCESSFULLY' => 'Scanned successfully',
	'LBL_IS_IN_RUNNING_STATE' => 'is in running state',
	'LBL_FOLDERS_INFO_IS_EMPTY' => 'Folders information is empty',
	'LBL_RULES_SEQUENCE_INFO_IS_EMPTY' => 'Rules sequnce information is empty',

	//Folder Actions
	'LBL_UPDATE_FOLDERS' => 'Update Folders',

	//Rule Fields
	'fromaddress' => 'From',
	'toaddress' => 'To',
	'subject' => 'Subject',
	'body' => 'Body',
	'matchusing' => 'Match',
	'action' => 'Action',

	//Rules List View labels
	'LBL_PRIORITY' => 'Priority',
	'PRIORITISE_MESSAGE' => 'Drag and drop block to prioritise the rule',
	'LBL_NOTE'=>'Note',
	'LBL_MAILCONVERTER_DISABLE_MESSAGE'=>'Mail Converter will be removed on July 31st. Mailroom provides an easier way to scan your emails. To activate Mailroom, please ',
	'LBL_CLICK_HERE'=>'click here',

	//Rule Field values & Messages
	'LBL_ALL_CONDITIONS' => 'All Conditions',
	'LBL_ANY_CONDITIOn' => 'Any Condition',

	//Rule Conditions
	'Contains' => 'Contains',
	'Not Contains' => 'Not Contains',
	'Equals' => 'Equals',
	'Not Equals' => 'Not Equals',
	'Begins With' => 'Begin',
	'Ends With' => 'End',
	'Regex' => 'Regex',
    'LBL_FROM_ADDRESS_PLACE_HOLDER' => 'Email address or domain name',

	//Rule Actions
	'CREATE_HelpDesk_FROM' => 'Create Ticket (With Contact)',
    'CREATE_HelpDeskNoContact_FROM' => 'Create Ticket (Without Contact)',
	'UPDATE_HelpDesk_SUBJECT' => 'Update Ticket',
	'LINK_Contacts_FROM' => 'Add to Contact [FROM]',
	'LINK_Contacts_TO' => 'Add to Contact [TO]',
	'LINK_Accounts_FROM' => 'Add to Organization [FROM]',
	'LINK_Accounts_TO' => 'Add to Organization [TO]',
	'LINK_Leads_FROM' => 'Add to Lead [FROM]',
	'LINK_Leads_TO' => 'Add to Lead [TO]',
    'CREATE_Potentials_SUBJECT' => 'Create Opportunity (With Contact)',
    'CREATE_PotentialsNoContact_SUBJECT' => 'Create Opportunity (Without Contact)',
    'LINK_Potentials_FROM' => 'Add to Opportunity [FROM]',
    'LINK_Potentials_TO' => 'Add to Opportunity [TO]',
    'LINK_HelpDesk_FROM' => 'Add to Ticket [FROM]',
    'LINK_HelpDesk_TO' => 'Add to Ticket [TO]',
    
    //Select Folder
    'LBL_UPDATE_FOLDERS' => 'Update Folders',
    'LBL_UNSELECT_ALL' => 'Unselect All',
	
	//Setup Rules
	'LBL_CONVERT_EMAILS_TO_RESPECTIVE_RECORDS' => 'Convert emails to respective records',
	'LBL_DRAG_AND_DROP_BLOCK_TO_PRIORITISE_THE_RULE' => 'The rule number indicates the priority. Drag and drop to change priority.',
	'LBL_ADD_RULE' => 'Add Rule',
    'LBL_EDIT_RULE' => 'Edit Rule',
	'LBL_PRIORITY' => 'Priority',
	'LBL_DELETE_RULE' => 'Delete rule',
	'LBL_BODY' => 'Body',
	'LBL_MATCH' => 'Match',
	'LBL_ACTION' => 'Action',
	'LBL_FROM' => 'From',
    'LBL_CONNECTION_ERROR' => 'Connecting to Mailbox failed. Check network connection and try again.',
    'LBL_RULE_CONDITIONS' => 'Rule Conditions',
    'LBL_RULE_ACTIONS' => 'Rule Actions',
    // Body Rule
    'LBL_AUTOFILL_VALUES_FROM_EMAIL_BODY' => 'Autofill values from Email body',
    'LBL_DELIMITER' => 'Delimiter',
    'LBL_COLON' => ': (Colon)',
    'LBL_SEMICOLON' => '; (Semi-Colon)',
    'LBL_DASH' => '- (Hyphen)',
    'LBL_EQUAL' => '= (Equals)',
    'LBL_GREATER_THAN' => '> (Greater Than)',
    'LBL_COLON_DASH' => ':- (Colon-Hyphen)',
    'LBL_COLON_EQUAL' => ':= (Colon-Equals)',
    'LBL_SEMICOLON_DASH' => ';- (Semicolon-Hyphen)',
    'LBL_SEMICOLON_EQUAL' => ';= (Semicolon-Equals)',
    'LBL_EQUAL_GREATER_THAN' => '=> (Equals-Greater Than)',
    'LBL_OTHER' => 'Other',
    'LBL_DELIMITER_INFO' => 'Select the delimiter that separates values from labels in your email body',
    'LBL_EMAIL_BODY_INFO' => 'Copy text from a sample email to be scanned into the below box. Vtiger CRM will try to locate values and help you map to CRM fields.',
    'LBL_SAMPLE_BODY_TEXT' => 'Sample Body Text',
    'LBL_FIND_FIELDS' => 'Click here to find values from email body',
    'LBL_BODY_FIELDS' => 'Values from Email',
    'LBL_CRM_FIELDS' => 'CRM Fields',
    'LBL_MAP_TO_CRM_FIELDS' => 'Map Values to CRM Fields',
    'SELECT_FIELD' => 'Select Field',
    'LBL_SAVE_MAPPING_INFO' => 'Saving body rule for an existing Mail Converter rule will overwrite existing body rule for that rule.',
    'LBL_MULTIPLE_FIELDS_MAPPED' => 'Cannot map one CRM field with multiple fields',
    'LBL_BODY_RULE' => 'Body Rule Defined',
    
    'LBL_MAIL_SCANNER_INACTIVE' => 'This Mailbox is in Inactive State',
    'LBL_NO_RULES' => 'No rules defined for this Mailbox',
    
    'LBL_SCANNERNAME_ALPHANUMERIC_ERROR' => 'Scanner Name accepts only alpha-numeric value. Special characters are not allowed.',
    'LBL_SERVER_NAME_ERROR' => 'Invalid server name. Special characters are not allowed for server name.',
    'LBL_USERNAME_ERROR' => 'Please enter a valid email address for user name.',
    'servertype' => 'Server Type',
    'LBL_DUPLICATE_USERNAME_ERROR' => 'There is already a Mail Converter configured with this email address. You cannot create duplicate Mail Converter with same email address.',
    'LBL_DUPLICATE_SCANNERNAME_ERROR' => 'There is already a Mail Converter configured with this name. You cannot create Mail Converter with duplicate name.',
       
);
$jsLanguageStrings = array(
	'JS_MAILBOX_DELETED_SUCCESSFULLY' => 'MailBox deleted Successfully',
	'JS_MAILBOX_LOADED_SUCCESSFULLY' => 'MailBox loaded Successfully',
    'JS_SELECT_ATLEAST_ONE' => 'Please map atleast one field',
    'JS_SERVER_NAME' => 'Enter server name',
    'JS_TIMEZONE' => 'Mail Server Timezone',
    'JS_SCAN_FROM' => 'Scan Mails From',
    'JS_TIMEZONE_INFO' => 'Please select timezone where your Mail Server is located. Selecting wrong timezone might skip some mails from scanning.',
    'JS_SCAN_FROM_INFO' => 'This field decides whether all mails in your Mailbox should be scanned or mails which has landed in your Mailbox yesterday or later should be scanned. This field is applicable only for first time configuration or when you select a new folder to scan.',
    'JS_SELECT_ONE_FOLDER' => 'You must select atleast one folder.',
);	
