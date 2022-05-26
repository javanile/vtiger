<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_ComposeEmail_View extends Vtiger_Footer_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('emailPreview');
		$this->exposeMethod('previewPrint');
		$this->exposeMethod('emailForward');
		$this->exposeMethod('emailEdit');
		$this->exposeMethod('composeMailData');
		$this->exposeMethod('emailReply');
		$this->exposeMethod('emailReplyAll');
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$actionName = ($record) ? 'EditView' : 'CreateView';
		if(!Users_Privileges_Model::isPermitted($moduleName, $actionName, $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function preProcess(Vtiger_Request $request, $display=true) {
		if($request->getMode() == 'previewPrint'){
			return;
		}
		return parent::preProcess($request,$display);
	}

	public function composeMailData($request){
		$moduleName = 'Emails';
		$fieldModule = $request->get('fieldModule');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		$sourceModule = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids',array());
		$excludedIds = $request->get('excluded_ids',array());
		$selectedFields = $request->get('selectedFields');
		$relatedLoad = $request->get('relatedLoad');
		$documentIds = $request->get('documentIds');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('FIELD_MODULE',$fieldModule);
		$viewer->assign('VIEWNAME', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('USER_MODEL', $userRecordModel);
		$viewer->assign('MAX_UPLOAD_SIZE', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		$viewer->assign('RELATED_MODULES', $moduleModel->getEmailRelatedModules());
		$viewer->assign('SOURCE_MODULE', $request->get('source_module'));

		if ($documentIds) {
			$attachements = array();
			foreach ($documentIds as $documentId) {
				$documentRecordModel = Vtiger_Record_Model::getInstanceById($documentId, $sourceModule);
				if ($documentRecordModel->get('filelocationtype') == 'I') {
					$fileDetails = $documentRecordModel->getFileDetails();
					if ($fileDetails) {
						$fileDetails['fileid'] = $fileDetails['attachmentsid'];
						$fileDetails['docid'] = $fileDetails['crmid'];
						$fileDetails['attachment'] = $fileDetails['name'];
						$fileDetails['size'] = filesize($fileDetails['path'] . $fileDetails['attachmentsid'] . "_". $fileDetails['name']);
						$attachements[] = $fileDetails;
					}
				}
			}
			$viewer->assign('ATTACHMENTS', $attachements);
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',$operator);
			$viewer->assign('ALPHABET_VALUE',$searchValue);
			$viewer->assign('SEARCH_KEY',$searchKey);
		}

		$searchParams = $request->get('search_params');
		if(!empty($searchParams)) {
			$viewer->assign('SEARCH_PARAMS',$searchParams);
		}

		$to =array();
		$toMailInfo = array();
		$toMailNamesList = array();
		$selectIds = $this->getRecordsListFromRequest($request);

		$ccMailInfo = $request->get('ccemailinfo');
		if(empty($ccMailInfo)){
			$ccMailInfo = array();
		}

		$bccMailInfo = $request->get('bccemailinfo');
		if(empty($bccMailInfo)){
			$bccMailInfo = array();
		}

		$sourceRecordId = $request->get('record');
		if ($sourceRecordId) {
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId);
			if ($sourceRecordModel->get('email_flag') === 'SAVED') {
				$selectIds = explode('|', $sourceRecordModel->get('parent_id'));
			}
		}

		$fallBack = false;
		if (!empty($selectedFields)) {
			if($request->get('emailSource') == 'ListView') {
				$sourceModule = $request->get('source_module');
				foreach($selectIds as $recordId) {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
					if($recordModel) {
						if($recordModel->get('emailoptout')) {
							continue;
						}
						foreach($selectedFields as $selectedFieldJson) {
							$selectedFieldInfo = Zend_Json::decode($selectedFieldJson);
							if(!empty($selectedFieldInfo['basefield'])) {
								$refField = $selectedFieldInfo['basefield'];
								$refModule = getTabModuleName($selectedFieldInfo['module_id']);
								$fieldName = $selectedFieldInfo['field'];
								$refFieldValue = $recordModel->get($refField);
								if(!empty($refFieldValue)) {
									try {
										$refRecordModel = Vtiger_Record_Model::getInstanceById($refFieldValue, $refModule);
										$emailValue = $refRecordModel->get($fieldName);
										$moduleLabel = $refModule;
									} catch(Exception $e) {
										continue;
									}
								}
							} else {
								$fieldName = $selectedFieldInfo['field'];
								$emailValue = $recordModel->get($fieldName);
								$moduleLabel = $sourceModule;
							}
							if(!empty($emailValue)) {
								$to[] = $emailValue;
								$toMailInfo[$recordId][] = $emailValue;
								$toMailNamesList[$recordId][] = array('label' => decode_html($recordModel->get('label')).' : '.vtranslate('SINGLE_'.$moduleLabel, $moduleLabel), 'value' => $emailValue);
							}
						}
					}
				}
			} else {
				foreach ($selectedFields as $selectedFieldJson) {
					$selectedFieldInfo = Zend_Json::decode($selectedFieldJson);
					if($selectedFieldInfo) {
						$to[] = $selectedFieldInfo['field_value'];
						$toMailInfo[$selectedFieldInfo['record']][] = $selectedFieldInfo['field_value'];
						$toMailNamesList[$selectedFieldInfo['record']][] = array('label' => decode_html($selectedFieldInfo['record_label']), 'value' => $selectedFieldInfo['field_value']);
					}else{
						$fallBack = true;
					}
				}
			}
		}

		//fallback to old code
		if($fallBack){
			foreach($selectIds as $id) { 
				  if ($id) { 
						$parentIdComponents = explode('@', $id); 
						if (count($parentIdComponents) > 1) { 
								$id = $parentIdComponents[0]; 
								if ($parentIdComponents[1] === '-1') { 
										$recordModel = Users_Record_Model::getInstanceById($id, 'Users'); 
								} else { 
										$recordModel = Vtiger_Record_Model::getInstanceById($id); 
								} 
						} else if($fieldModule) { 
								$recordModel = Vtiger_Record_Model::getInstanceById($id, $fieldModule); 
						} else { 
								$recordModel = Vtiger_Record_Model::getInstanceById($id); 
						} 
						if($selectedFields){ 
								foreach($selectedFields as $field) { 
										$value = $recordModel->get($field); 
										$emailOptOutValue = $recordModel->get('emailoptout'); 
										if(!empty($value) && (!$emailOptOutValue)) { 
												$to[] = $value; 
												$toMailInfo[$id][] = $value; 
												$toMailNamesList[$id][] = array('label' => decode_html($recordModel->getName()), 'value' => decode_html($value)); 
										} 
							} 
						} 
				  }
		  }
		}
		$requestTo = $request->get('to');
		if (!$to && is_array($requestTo)) {
			$to = $requestTo;
		}

		$documentsModel = Vtiger_Module_Model::getInstance('Documents');
		$documentsURL = $documentsModel->getInternalDocumentsURL();

		$emailTemplateModuleModel = Vtiger_Module_Model::getInstance('EmailTemplates');
		$emailTemplateListURL = $emailTemplateModuleModel->getPopupUrl();

		$viewer->assign('DOCUMENTS_URL', $documentsURL);
		$viewer->assign('EMAIL_TEMPLATE_URL', $emailTemplateListURL);
		$viewer->assign('TO', $to);
		$viewer->assign('TOMAIL_INFO', $toMailInfo);
		$viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
		$viewer->assign('CC', $request->get('cc'));
		$viewer->assign('CCMAIL_INFO', $ccMailInfo);
		$viewer->assign('BCC', $request->get('bcc'));
		$viewer->assign('BCCMAIL_INFO', $bccMailInfo);

		//EmailTemplate module percission check
		$userPrevilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$viewer->assign('MODULE_IS_ACTIVE', $userPrevilegesModel->hasModulePermission(Vtiger_Module_Model::getInstance('EmailTemplates')->getId()));
		//

		if($relatedLoad){
			$viewer->assign('RELATED_LOAD', true);
		}
	}

	public function emailActionsData($request){
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$attachment = array();

		if(!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$this->composeMailData($request);
		$subject = $recordModel->get('subject');
		$description = $recordModel->get('description');
		$attachmentDetails = $recordModel->getAttachmentDetails();

		$viewer->assign('SUBJECT', $subject);
		$viewer->assign('DESCRIPTION', $description);
		$viewer->assign('ATTACHMENTS', $attachmentDetails);
		$viewer->assign('PARENT_EMAIL_ID', $recordId);
		$viewer->assign('PARENT_RECORD', $request->get('parentId'));
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
		$this->composeMailData($request);
		$viewer = $this->getViewer($request);
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	function postProcess(Vtiger_Request $request) {
		return;
	}

	public function getRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}

		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');
		if ($sourceRecord && $sourceModule) {
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			return $sourceRecordModel->getSelectedIdsList($request->get('parentModule'), $excludedIds);
		}

		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if(!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}
			$customViewModel->set('search_params', $request->get('search_params'));
			return $customViewModel->getRecordIds($excludedIds);
		}
		return array();
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"libraries.jquery.ckeditor.ckeditor",
			"libraries.jquery.ckeditor.adapters.jquery",
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.Emails.resources.MassEdit",
			"modules.Emails.resources.EmailPreview",
			"modules.Vtiger.resources.CkEditor",
			'modules.Vtiger.resources.Popup',
			'libraries.jquery.jquery_windowmsg',
			'libraries.jquery.multiplefileupload.jquery_MultiFile'
		);

		$jsHeaderScriptNames = array(
			'layouts.vlayout.modules.Vtiger.resources.Header',
			'layouts.vlayout.modules.Vtiger.resources.BasicSearch'
		);

		$jsHeaderScriptInstances = $this->checkAndConvertJsScripts($jsHeaderScriptNames);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($jsHeaderScriptInstances, $headerScriptInstances); // for text search
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function emailPreview($request){
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if(!$this->record){
		$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$viewer = $this->getViewer($request);
		$TO = Zend_Json::decode(html_entity_decode($recordModel->get('saved_toid')));
		$CC = Zend_Json::decode(html_entity_decode($recordModel->get('ccmail')));
		$BCC = Zend_Json::decode(html_entity_decode($recordModel->get('bccmail')));

		$parentId = $request->get('parentId');
		if(empty($parentId)) {
			list($parentRecord, $status) = explode('@', reset(array_filter(explode('|', $recordModel->get('parent_id')))));
			if(isRecordExists($parentRecord)) {
				$parentId = $parentRecord;
			}
		}

		if(!empty($parentId)) {
			$recordModel->replaceMergeTags($parentId);
		}
		$attachmentDetails = $recordModel->getAttachmentDetails();
		$recordModel = $this->replaceBodyHtml($recordModel,$attachmentDetails);

		$viewer->assign('FROM', $recordModel->get('from_email'));
		$viewer->assign('TO',$TO);
		$viewer->assign('CC', implode(',',$CC));
		$viewer->assign('BCC', implode(',',$BCC));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('PARENT_RECORD', $parentId);
		if($request->get('parentModule')) {
			$viewer->assign('PARENT_MODULE', $request->get('parentModule'));
		}

		if($request->get('mode') == 'previewPrint') {
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
			echo $viewer->view('EmailPreviewPrint.tpl',$moduleName,true);
		}else{
			echo $viewer->view('EmailPreview.tpl',$moduleName,true);
		}

	}

	function emailEdit($request){
		$viewer = $this->getViewer($request);
		$this->emailActionsData($request);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$TO = Zend_Json::decode(html_entity_decode($recordModel->get('saved_toid')));
		$CC = Zend_Json::decode(html_entity_decode($recordModel->get('ccmail')));
		$BCC = Zend_Json::decode(html_entity_decode($recordModel->get('bccmail')));

		$parentIds = explode('|',$recordModel->get('parent_id'));

		$toMailInfo = $toMailNamesList = array();
		foreach($parentIds as $index=>$parentFieldId) {
			$emailOptOutFlag = false;
			$emailIdsToRemove = array();
			if(empty($parentFieldId)){
				continue;
			}
			$parentIdComponents = explode('@',$parentFieldId);
			$parentId = $parentIdComponents[0];
			if ($parentIdComponents[1] === '-1') {
				$parentRecordModel = Users_Record_Model::getInstanceById($parentId, 'Users');
			} else {
				if(!isRecordExists($parentId)) {
					continue;
				}
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId);
			}
			if($parentRecordModel->get('emailoptout')) {
				$emailOptOutFlag = true;
			}
			$emailFields = array_keys($parentRecordModel->getModule()->getFieldsByType('email'));
			foreach ($emailFields as $emailField) {
				$emailValue = $parentRecordModel->get($emailField);
				if (in_array($emailValue, $TO)) {
					if($emailOptOutFlag) {
						array_push($emailIdsToRemove, $emailValue);
						continue;
					}
					//expecting parent ids and to will be same order
					$toMailInfo[$parentId][] = $emailValue;
					$toMailNamesList[$parentId][] = array('label' => decode_html($parentRecordModel->getName()), 'value' => decode_html($emailValue));
				}
			}
			if($emailOptOutFlag) {
				foreach($emailIdsToRemove as $emailAddress) {
					$key = array_search($emailAddress, $TO);
					unset($TO[$key]);
				}
				continue;
			}

			$referenceFields = array_keys($parentRecordModel->getModule()->getFieldsByType('reference'));

			//for handling labels when editing emails which is having email values of reference records
			foreach ($referenceFields as $fieldName) {
				$refRecordId = $parentRecordModel->get($fieldName);
				$refFieldModlule = Vtiger_Functions::getCRMRecordType($refRecordId);
				if(!$refFieldModlule) continue;
				if(!isRecordExists($refRecordId)) {
					continue;
				}
				$refRecordModel = Vtiger_Record_Model::getInstanceById($refRecordId);
				if(!$refRecordModel) continue;
				$refModuleEmailFields = array_keys($refRecordModel->getModule()->getFieldsByType(array('email')));
				foreach ($refModuleEmailFields as $fieldName) {
					$emailValue = $refRecordModel->get($fieldName);
					if(in_array($emailValue,$TO)) {
						$toMailInfo[$parentId][] = $emailValue;
						$toMailNamesList[$parentId][] = array('label' => decode_html($parentRecordModel->getName()).':'.vtranslate('SINGLE_'.$refRecordModel->getModuleName(),$refRecordModel->getModuleName()), 'value' => decode_html($emailValue));
					}
				}
			}
		}
		if ($parentRecordModel) {
			$viewer->assign('FIELD_MODULE',$parentRecordModel->getModuleName());
		}

		$viewer->assign('TO',$TO);
		$viewer->assign('TOMAIL_INFO', $toMailInfo);
		$viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
		$viewer->assign('CC', implode(',',$CC));
		$viewer->assign('BCC', implode(',',$BCC));
		$viewer->assign('RECORDID', $request->get('record'));
		$viewer->assign('RELATED_LOAD', true);
		$viewer->assign('EMAIL_MODE', 'edit');
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	function emailForward($request){
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$this->emailActionsData($request);
		$viewer->assign('TO', '');
		$viewer->assign('TOMAIL_INFO', array());
		$viewer->assign('RELATED_LOAD', true);
		$viewer->assign('EMAIL_MODE', 'forward');
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	function emailReply($request){
		$viewer = $this->getViewer($request);
		$this->emailActionsData($request);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$TO = Zend_Json::decode(html_entity_decode($recordModel->get('saved_toid')));
		$parentIds = explode('|',$recordModel->get('parent_id'));
		$toMailInfo = $toMailNamesList = array();
		foreach($parentIds as $index=>$parentFieldId) {
			if(empty($parentFieldId)){
				continue;
			}
			$parentIdComponents = explode('@',$parentFieldId);
			$parentId = $parentIdComponents[0];
			if ($parentIdComponents[1] === '-1') {
				$parentRecordModel = Users_Record_Model::getInstanceById($parentId, 'Users');
			} else {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId);
			}
			$emailFields = array_keys($parentRecordModel->getModule()->getFieldsByType('email'));
			foreach ($emailFields as $emailField) {
				$emailValue = $parentRecordModel->get($emailField);
				if (in_array($emailValue, $TO)) {
					//expecting parent ids and to will be same order
					$toMailInfo[$parentId][] = $emailValue;
					$toMailNamesList[$parentId][] = array('label' => decode_html($parentRecordModel->getName()), 'value' => decode_html($emailValue));
				}
			}
		}
		//if origin is mail manager or mail converter, reply to should be from email
		if($recordModel->get('email_flag') == 'MailManager' || $recordModel->get('email_flag') == 'MAILSCANNER') {
			$TO = array($recordModel->get('from_email'));
		}
		$viewer->assign('TO',$TO);
		$viewer->assign('TOMAIL_INFO', $toMailInfo);
		$viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
		$viewer->assign('RELATED_LOAD', true);
		$viewer->assign('EMAIL_MODE', 'reply');
		//adding Re: to the subject
		$subject = $recordModel->get('subject');
		$subject = (strpos($subject, 'Re:') === 0) ? $subject : 'Re:'.$subject;
		$viewer->assign('SUBJECT', $subject);
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	function emailReplyAll($request){
		$viewer = $this->getViewer($request);
		$this->emailActionsData($request);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$TO = Zend_Json::decode(html_entity_decode($recordModel->get('saved_toid')));
		$CC = Zend_Json::decode(html_entity_decode($recordModel->get('ccmail')));
		$BCC = Zend_Json::decode(html_entity_decode($recordModel->get('bccmail')));

		$parentIds = explode('|',$recordModel->get('parent_id'));
		$toMailInfo = $toMailNamesList = array();
		foreach($parentIds as $index=>$parentFieldId) {
			if(empty($parentFieldId)){
				continue;
			}
			$parentIdComponents = explode('@',$parentFieldId);
			$parentId = $parentIdComponents[0];
			if ($parentIdComponents[1] === '-1') {
				$parentRecordModel = Users_Record_Model::getInstanceById($parentId, 'Users');
			} else {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId);
			}
			$emailFields = array_keys($parentRecordModel->getModule()->getFieldsByType('email'));
			foreach ($emailFields as $emailField) {
				$emailValue = $parentRecordModel->get($emailField);
				if (in_array($emailValue, $TO)) {
					//expecting parent ids and to will be same order
					$toMailInfo[$parentId][] = $emailValue;
					$toMailNamesList[$parentId][] = array('label' => decode_html($parentRecordModel->getName()), 'value' => decode_html($emailValue));
				}
			}
		}
		$emailFlagsList = array('MailManager', 'MAILSCANNER');
		$emailFlag = $recordModel->get('email_flag');
		if(in_array($emailFlag, $emailFlagsList)) {
			//mailboxemail - from where this email added to parent record
			$mailBoxEmails = Zend_Json::decode(html_entity_decode($recordModel->get('mailboxemail')));
			$mailBoxEMail = $mailBoxEmails[0];
			if(empty($mailBoxEMail) && $emailFlag == 'MailManager') {
				$mailBoxModel = MailManager_Mailbox_Model::activeInstance();
				$mailBoxEMail = $mailBoxModel->username();
			}
			//show To email addresses also in cc exclude mailbox email id
			//array_filter will remove the empty values
			$CC = array_merge($TO, array_filter($CC));
			foreach($CC as $email) {
				if(strpos($email, $mailBoxEMail) !== false) {
					$keys = array_keys($CC, $email);
					unset($CC[$keys[0]]);
					break;
				}
			}
			//if origin is mail converter or mail manager, reply to should be from email
			$TO = array($recordModel->get('from_email'));
		}
		$viewer->assign('TO',$TO);
		$viewer->assign('TOMAIL_INFO', $toMailInfo);
		$viewer->assign('TOMAIL_NAMES_LIST', json_encode($toMailNamesList, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
		$viewer->assign('CC', implode(',',$CC));
		$viewer->assign('BCC', implode(',',$BCC));
		$viewer->assign('RELATED_LOAD', true);
		$viewer->assign('EMAIL_MODE', 'replyall');
		//adding Re: to the subject
		$subject = $recordModel->get('subject');
		$subject = (strpos($subject, 'Re:') === 0) ? $subject : 'Re:'.$subject;
		$viewer->assign('SUBJECT', $subject);
		echo $viewer->view('ComposeEmailForm.tpl', $moduleName, true);
	}

	public function previewPrint($request) {
		$this->emailPreview($request);
	}

	public function replaceBodyHtml($recordModel,$attachmentDetails){
		$bodyHtml = $recordModel->get('description');

		if($attachmentDetails){
			foreach($attachmentDetails as $attachmentDetail){
				if($attachmentDetail['cid']){
					$cid = $attachmentDetail['cid'];
					$attch_name = $attachmentDetail['fileid'].'_'.$attachmentDetail['attachment'];
					$src = 'storage/mailroom/'.$attch_name;
					$bodyHtml = preg_replace('/cid:'.$cid.'/', $src, $bodyHtml);
				}
			}
			$recordModel->set('description',$bodyHtml);
		}
		return $recordModel;
	}
}
