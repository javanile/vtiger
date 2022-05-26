<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/


/**
 * Description of ListViewController
 *
 * @author MAK
 */
class ListViewController {
	/**
	 *
	 * @var QueryGenerator
	 */
	protected $queryGenerator;
	/**
	 *
	 * @var PearDatabase
	 */
	protected $db;
	protected $nameList;
	protected $typeList;
	protected $ownerNameList;
	protected $user;
	protected $picklistValueMap;
	protected $picklistRoleMap;
	protected $headerSortingEnabled;
	public function __construct($db, $user, $generator) {
		$this->queryGenerator = $generator;
		$this->db = $db;
		$this->user = $user;
		$this->nameList = array();
		$this->typeList = array();
		$this->ownerNameList = array();
		$this->picklistValueMap = array();
		$this->picklistRoleMap = array();
		$this->headerSortingEnabled = true;
	}

	public function isHeaderSortingEnabled() {
		return $this->headerSortingEnabled;
	}

	public function setHeaderSorting($enabled) {
		$this->headerSortingEnabled = $enabled;
	}

	public function setupAccessiblePicklistValueList($name) {
		$isRoleBased = vtws_isRoleBasedPicklist($name);
		$this->picklistRoleMap[$name] = $isRoleBased;
		if ($this->picklistRoleMap[$name]) {
			$this->picklistValueMap[$name] = getAssignedPicklistValues($name,$this->user->roleid, $this->db);
		}
	}

	public function fetchNameList($field, $result) {
		$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
		$fieldName = $field->getFieldName();
		$rowCount = $this->db->num_rows($result);

		$columnName = $field->getColumnName();
		if($field->referenceFieldName) {
			preg_match('/(\w+) ; \((\w+)\) (\w+)/', $field->referenceFieldName, $matches);
			if (count($matches) != 0) {
				list($full, $parentReferenceFieldName, $referenceModule, $referenceFieldName) = $matches;
			}
			$columnName = $parentReferenceFieldName.$referenceFieldName;
		}

		$idList = array();
		for ($i = 0; $i < $rowCount; $i++) {
			$id = $this->db->query_result($result, $i, $columnName);
			if (!isset($this->nameList[$fieldName][$id]) && $id != null) {
				$idList[$id] = $id;
			}
		}

		$idList = array_keys($idList);
		if(count($idList) == 0) {
			return;
		}
		if($parentReferenceFieldName) {
			$moduleList = $referenceFieldInfoList[$field->referenceFieldName];
		} else {
			$moduleList = $referenceFieldInfoList[$fieldName];
		}

		if($moduleList) {
			foreach ($moduleList as $module) {
				$meta = $this->queryGenerator->getMeta($module);
				if ($meta->isModuleEntity()) {
					if($module == 'Users') {
						$nameList = getOwnerNameList($idList);
					} else {
						//TODO handle multiple module names overriding each other.
						$nameList = getEntityName($module, $idList);
					}
				} else {
					$nameList = vtws_getActorEntityName($module, $idList);
				}
				$entityTypeList = array_intersect(array_keys($nameList), $idList);
				foreach ($entityTypeList as $id) {
					$this->typeList[$id] = $module;
				}
				if(empty($this->nameList[$fieldName])) {
					$this->nameList[$fieldName] = array();
				}
				foreach ($entityTypeList as $id) {
					$this->typeList[$id] = $module;
					$this->nameList[$fieldName][$id] = $nameList[$id];
				}
			}
		}
	}

	public function getListViewHeaderFields() {
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
		$moduleFields = $this->queryGenerator->getModuleFields();
		$fields = $this->queryGenerator->getFields(); 
		$headerFields = array();
		foreach($fields as $fieldName) {
			if(array_key_exists($fieldName, $moduleFields)) {
				$headerFields[$fieldName] = $moduleFields[$fieldName];
			}
		}
		return $headerFields;
	}

	function getListViewRecords($focus, $module, $result) {
		global $listview_max_textlength, $theme, $default_charset;

		require('user_privileges/user_privileges_'.$this->user->id.'.php');
		$fields = $this->queryGenerator->getFields();
		$meta = $this->queryGenerator->getMeta($this->queryGenerator->getModule());
		$baseModule = $module;
		$moduleFields = $this->queryGenerator->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$listViewFields = array_intersect($fields, $accessibleFieldList);

		$referenceFieldList = $this->queryGenerator->getReferenceFieldList();
		if($referenceFieldList) {
			foreach ($referenceFieldList as $fieldName) {
				if (in_array($fieldName, $listViewFields)) {
					$field = $moduleFields[$fieldName];
					$this->fetchNameList($field, $result);
				}
			}
		}

		$db = PearDatabase::getInstance();
		$rowCount = $db->num_rows($result);
		$ownerFieldList = $this->queryGenerator->getOwnerFieldList();

		foreach ($ownerFieldList as $fieldName) {
			if (in_array($fieldName, $listViewFields)) {
				$field = $moduleFields[$fieldName];
				$idList = array();

				//if the assigned to is related to the reference field
				preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
				if(count($matches) > 0) {
					list($full, $referenceParentField, $module, $fieldName) = $matches;
					$columnName = strtolower($referenceParentField.$fieldName);
				} else {
					$columnName = $field->getColumnName();
				}

				for ($i = 0; $i < $rowCount; $i++) {
					$id = $this->db->query_result($result, $i, $columnName);
					if (!isset($this->ownerNameList[$fieldName][$id])) {
						$idList[] = $id;
					}
				}
				if(count($idList) > 0) {
					if(!is_array($this->ownerNameList[$fieldName])) {
						$this->ownerNameList[$fieldName] = getOwnerNameList($idList);
					} else {
						//array_merge API loses key information so need to merge the arrays
						// manually.
						$newOwnerList = getOwnerNameList($idList);
						foreach ($newOwnerList as $id => $name) {
							$this->ownerNameList[$fieldName][$id] = $name;
						}
					}
				}
			}
		}
		$fileTypeFields = array();
		foreach ($listViewFields as $fieldName) {
			$field = $moduleFields[$fieldName];
			if(!$is_admin && ($field->getFieldDataType() == 'picklist' ||
					$field->getFieldDataType() == 'multipicklist')) {
				$this->setupAccessiblePicklistValueList($fieldName);
			}
			if($field->getUIType() == '61') {
				$fileTypeFields[] = $field->getColumnName();
			}
		}

		//performance optimization for uitype 61
		$attachmentsCache = array();
		$attachmentIds = array();
		if(count($fileTypeFields)) {
			foreach($fileTypeFields as $fileTypeField) {
				for ($i = 0; $i < $rowCount; ++$i) {
					$attachmentId = $db->query_result($result,$i,$fileTypeField);
					if($attachmentId) $attachmentIds[] = $attachmentId;
				}
			}
		}
		if(count($attachmentIds)) {
			$getAttachmentsNamesSql = 'SELECT attachmentsid,name FROM vtiger_attachments WHERE attachmentsid IN (' . generateQuestionMarks($attachmentIds) . ')';
			$attachmentNamesRes = $db->pquery($getAttachmentsNamesSql,$attachmentIds);
			$attachmentNamesRowCount = $db->num_rows($attachmentNamesRes);
			for($i=0;$i<$attachmentNamesRowCount;$i++) {
				$attachmentsName = $db->query_result($attachmentNamesRes,$i,'name');
				$attachmentsId = $db->query_result($attachmentNamesRes,$i,'attachmentsid');
				$attachmentsCache[$attachmentsId] = decode_html($attachmentsName);
			}
		}

		$moduleInstance = Vtiger_Module_Model::getInstance("PBXManager");
		if($moduleInstance && $moduleInstance->isActive()) {
			$outgoingCallPermission = PBXManager_Server_Model::checkPermissionForOutgoingCall();
			$clickToCallLabel = vtranslate("LBL_CLICK_TO_CALL");
		}

		$data = array();
		for ($i = 0; $i < $rowCount; ++$i) {
			//Getting the recordId
			if($module != 'Users') {
				$baseTable = $meta->getEntityBaseTable();
				$moduleTableIndexList = $meta->getEntityTableIndexList();
				$baseTableIndex = $moduleTableIndexList[$baseTable];

				$baseRecordId = $recordId = $db->query_result($result,$i,$baseTableIndex);
			}else {
				$baseRecordId = $recordId = $db->query_result($result,$i,"id");
			}
			$row = array();

			foreach ($listViewFields as $fieldName) {
				$recordId = $baseRecordId;
				$rawFieldName = $fieldName;
				$field = $moduleFields[$fieldName];
				$uitype = $field->getUIType();
				$fieldDataType = $field->getFieldDataType();
				// for reference fields read the value differently
				preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
				if(count($matches) > 0) {
					list($full, $referenceParentField, $module, $fieldName) = $matches;
					$matches = null;
					$rawValue = $this->db->query_result($result, $i, strtolower($referenceParentField.$fieldName));
					//if the field is related to reference module's field, then we might need id of that record for example emails field
					$recordId = $this->db->query_result($result, $i, strtolower($referenceParentField.$fieldName).'_id');
				} else {
					$rawValue = $this->db->query_result($result, $i, $field->getColumnName());
					//if not reference module field then we need to reset the module
					$module = $baseModule;
				}

				if(in_array($uitype,array(15,33,16))){
					$value = html_entity_decode($rawValue,ENT_QUOTES,$default_charset); 
				} else { 
					$value = $rawValue; 
				}

				if($module == 'Documents' && $fieldName == 'filename') {
					$downloadtype = $db->query_result($result,$i,'filelocationtype');
					$fileName = $db->query_result($result,$i,'filename');

					$downloadType = $db->query_result($result,$i,'filelocationtype');
					$status = $db->query_result($result,$i,'filestatus');
					$fileIdQuery = "select attachmentsid from vtiger_seattachmentsrel where crmid=?";
					$fileIdRes = $db->pquery($fileIdQuery,array($recordId));
					$fileId = $db->query_result($fileIdRes,0,'attachmentsid');
					if($fileName != '' && $status == 1) {
						if($downloadType == 'I' ) {
							$value = '<a href="index.php?module=Documents&action=DownloadFile&record='.$recordId.'&fileid='.$fileId.'"'.
									' title="'.	getTranslatedString('LBL_DOWNLOAD_FILE',$module).
									'" >'.textlength_check($value).
									'</a>';
						} elseif($downloadType == 'E') {
							$value = '<a onclick="event.stopPropagation()"'.
									' href="'.$fileName.'" target="_blank"'.
									' title="'.	getTranslatedString('LBL_DOWNLOAD_FILE',$module).
									'" >'.textlength_check($value).
									'</a>';
						} else {
							$value = ' --';
						}
					} else{
						$value = textlength_check($value);
					}
					$value = $fileicon.$value;
				} elseif($module == 'Documents' && $fieldName == 'filesize') {
					$downloadType = $db->query_result($result,$i,'filelocationtype');
					if($downloadType == 'I') {
						$filesize = $value;
						if($filesize < 1024)
							$value=$filesize.' B';
						elseif($filesize > 1024 && $filesize < 1048576)
							$value=round($filesize/1024,2).' KB';
						else if($filesize > 1048576)
							$value=round($filesize/(1024*1024),2).' MB';
					} else {
						$value = ' --';
					}
				} elseif( $module == 'Documents' && $fieldName == 'filestatus') {
					if($value == 1)
						$value=getTranslatedString('yes',$module);
					elseif($value == 0)
						$value=getTranslatedString('no',$module);
					else
						$value='--';
				} elseif( $module == 'Documents' && $fieldName == 'filetype') {
					$downloadType = $db->query_result($result,$i,'filelocationtype');
					if($downloadType == 'E' || $downloadType != 'I') {
						$value = '--';
					}
				} elseif ($field->getUIType() == '27') {
					if ($value == 'I') {
						$value = getTranslatedString('LBL_INTERNAL',$module);
					}elseif ($value == 'E') {
						$value = getTranslatedString('LBL_EXTERNAL',$module);
					}else {
						$value = ' --';
					}
				}elseif ($fieldDataType == 'picklist') {
					//not check for permissions for non admin users for status and activity type field
					if($module == 'Calendar' && ($fieldName == 'taskstatus' || $fieldName == 'eventstatus' || $fieldName == 'activitytype')) {
						$value = Vtiger_Language_Handler::getTranslatedString($value,$module);
						$value = textlength_check($value);
					}
					else if ($value != '' && !$is_admin && $this->picklistRoleMap[$fieldName] &&
							!in_array($value, $this->picklistValueMap[$fieldName]) && strtolower($value) != '--none--' && strtolower($value) != 'none' ) {
						$value = "<font color='red'>". Vtiger_Language_Handler::getTranslatedString('LBL_NOT_ACCESSIBLE',
								$module)."</font>";
					} else {
						$value =  Vtiger_Language_Handler::getTranslatedString($value,$module);
						$value = textlength_check($value);
					}
				}elseif($fieldDataType == 'date' || $fieldDataType == 'datetime') {
					if($value != '' && $value != '0000-00-00' && $value != 'NULL') {
						if($module == 'Calendar' &&($fieldName == 'date_start' || $fieldName == 'due_date')) {
							if($fieldName == 'date_start') {
								$timeField = 'time_start';
							}else if($fieldName == 'due_date') {
								$timeField = 'time_end';
							}
							$timeFieldValue = $this->db->query_result($result, $i, $timeField);
							if(!empty($timeFieldValue)){
								$value .= ' '. $timeFieldValue;
								//TO make sure it takes time value as well
								$fieldDataType = 'datetime';
							}
						}
						if($fieldDataType == 'datetime' && $value != '0000-00-00 00:00:00') {
							$value = Vtiger_Datetime_UIType::getDateTimeValue($value);
						} else if($fieldDataType == 'date') {
							$date = new DateTimeField($value);
							$value = $date->getDisplayDate();
						}
					} elseif ($value == '0000-00-00') {
						$value = '';
					}
				} elseif($fieldDataType == 'time') {
					if(!empty($value)){
						if(($module == 'Calendar') && ($fieldName == 'time_start' || $fieldName == 'time_end')) {
							$time = new DateTimeField(date('Y-m-d').' '.$value);
							$value = $time->getDisplayTime();
						}
						$userModel = Users_Privileges_Model::getCurrentUserModel();
						if($userModel->get('hour_format') == '12'){
							$value = Vtiger_Time_UIType::getTimeValueInAMorPM($value);
						}
					}
				} elseif($fieldDataType == 'currency') {
					if($value != '') {
						if($field->getUIType() == 72) {
							if($fieldName == 'unit_price') {
								$currencyId = getProductBaseCurrency($recordId,$module);
								$cursym_convrate = getCurrencySymbolandCRate($currencyId);
								$currencySymbol = $cursym_convrate['symbol'];
							} else {
								$currencyInfo = getInventoryCurrencyInfo($module, $recordId);
								$currencySymbol = $currencyInfo['currency_symbol'];
							}
							$value = CurrencyField::convertToUserFormat($value, null, true);
							$row['currencySymbol'] = $currencySymbol;
//							$value = CurrencyField::appendCurrencySymbol($currencyValue, $currencySymbol);
						} else {
							if (!empty($value)) {
								$value = CurrencyField::convertToUserFormat($value);
								$userCurrencyInfo = getCurrencySymbolandCRate($this->user->currency_id);
								$row['userCurrencySymbol'] = $userCurrencyInfo['symbol'];
							}
						}
					}
				} elseif ($fieldDataType == 'double') {
					$value = decimalFormat($value);
				} elseif($fieldDataType == 'url') {
					$matchPattern = "^[\w]+:\/\/^";
					preg_match($matchPattern, $rawValue, $matches);
					if(!empty ($matches[0])){
						$value = '<a class="urlField cursorPointer" href="'.$rawValue.'" target="_blank">'.textlength_check($value).'</a>';
					}else{
						$value = '<a class="urlField cursorPointer" href="http://'.$rawValue.'" target="_blank">'.textlength_check($value).'</a>';
					}
				} elseif ($fieldDataType == 'email') {
					global $current_user;
					if($current_user->internal_mailer == 1){
						//check added for email link in user detailview
						$value = "<a class='emailField' data-rawvalue=\"$rawValue\" onclick=\"Vtiger_Helper_Js.getInternalMailer($recordId,".
						"'$fieldName','$module');\">".textlength_check($value)."</a>";
					} else {
						$value = '<a class="emailField" data-rawvalue="'.$rawValue.'" href="mailto:'.$rawValue.'">'.textlength_check($value).'</a>';
					}
				} elseif($fieldDataType == 'boolean') {
					if ($value === 'on') {
						$value = 1;
					} else if ($value == 'off') {
						$value = 0;
					}
					if($value == 1) {
						$value = vtranslate('LBL_YES',$module);
					} elseif($value == 0) {
						$value = vtranslate('LBL_NO',$module);
					} else {
						$value = '--';
					}
				} elseif($field->getUIType() == 98) {
					$value = '<a href="index.php?module=Roles&parent=Settings&view=Edit&record='.$value.'">'.textlength_check(getRoleName($value)).'</a>';
				} elseif($fieldDataType == 'multipicklist') {
					if(!$is_admin && $value != '') {
						$valueArray = ($rawValue != "") ? explode(' |##| ',$rawValue) : array();
						$notaccess = '<font color="red">'.getTranslatedString('LBL_NOT_ACCESSIBLE',
								$module)."</font>";
						$tmp = '';
						$tmpArray = array();
						foreach($valueArray as $index => $val) {
							$val = decode_html($val);
							if(!$listview_max_textlength ||
									!(strlen(preg_replace("/(<\/?)(\w+)([^>]*>)/i","",$tmp)) >
											$listview_max_textlength)) {
								if (!$is_admin && $this->picklistRoleMap[$fieldName] &&
										!in_array(trim($val), $this->picklistValueMap[$fieldName])) {
									$tmpArray[] = $notaccess;
									$tmp .= ', '.$notaccess;
								} else {
									$tmpArray[] = $val;
									$tmp .= ', '.$val;
								}
							} else {
								$tmpArray[] = '...';
								$tmp .= '...';
							}
						}
						$value = implode(', ', $tmpArray);
						$value = textlength_check($value);
					} else if ($value != '') {
						$moduleName = getTabModuleName($field->getTabId());
						$value = explode(' |##| ', $value);
						foreach ($value as $key => $val) {
							$value[$key] = vtranslate($val, $moduleName);
						}
						$value = implode(' |##| ', $value);
						$value = str_replace(' |##| ', ', ', $value);
					}
				} elseif ($fieldDataType == 'skype') {
					$value = ($value != "") ? "<a href='skype:$value?call'>".textlength_check($value)."</a>" : "";
				} elseif ($field->getUIType() == 11) {
					if($outgoingCallPermission && !empty($value)) {
						$phoneNumber = $value;
						$value = $phoneNumber;
					}else {
						$value = textlength_check($value);
					}
				} elseif($field->getFieldDataType() == 'reference') {
					$referenceFieldInfoList = $this->queryGenerator->getReferenceFieldInfoList();
					$moduleList = $referenceFieldInfoList[$fieldName];
					if(count($moduleList) == 1) {
						$parentModule = $moduleList[0];
					} else {
						$parentModule = $this->typeList[$value];
					}
					if(!empty($value) && !empty($this->nameList[$fieldName]) && !empty($parentModule)) {
						$parentMeta = $this->queryGenerator->getMeta($parentModule);
						$value = textlength_check($this->nameList[$fieldName][$value]);
						if ($parentMeta->isModuleEntity() && $parentModule != "Users") {
							$value = "<a class='js-reference-display-value' href='?module=$parentModule&view=Detail&".
								"record=$rawValue' title='".getTranslatedString($parentModule, $parentModule)."'>$value</a>";
						}
					} else {
						$value = '--';
					}
				} elseif($fieldDataType == 'owner' || $fieldDataType == 'ownergroup') {
					$value = textlength_check($this->ownerNameList[$fieldName][$value]);
				} elseif ($field->getUIType() == 25) {
					//TODO clean request object reference.
					$contactId=$_REQUEST['record'];
					$emailId=$this->db->query_result($result,$i,"activityid");
					$result1 = $this->db->pquery("SELECT access_count FROM vtiger_email_track WHERE ".
							"crmid=? AND mailid=?", array($contactId,$emailId));
					$value=$this->db->query_result($result1,0,"access_count");
					if(!$value) {
						$value = 0;
					}
				} elseif($field->getUIType() == 8){
					if(!empty($value)){
						$temp_val = html_entity_decode($value,ENT_QUOTES,$default_charset);
						$json = new Zend_Json();
						$value = vt_suppressHTMLTags(implode(',',$json->decode($temp_val)));
					}
				} elseif ( in_array($uitype,array(7,9,90)) ) {
					$value = "<span align='right'>".textlength_check($value)."</span>";
				} elseif($field && $field->isNameField) {
					$value = "<a href='?module=$field->moduleName&view=Detail&".
								"record=$recordId' title='".vtranslate($field->moduleName, $field->moduleName)."'>$value</a>";
				} elseif($field->getUIType() == 61) {
					$attachmentId = (int)$value;
					$displayValue = '--';
					if($attachmentId) {
						$displayValue = $attachmentName = $attachmentsCache[$attachmentId];
						$url = 'index.php?module='.$module.
							   '&action=DownloadAttachment&record='.$recordId.'&attachmentid='.$attachmentId;
						$displayValue = '<a href="'.$url.'" title="'.vtranslate('LBL_DOWNLOAD_FILE',$module).'">'.
											textlength_check($attachmentName).
										'</a>';
					}
					$value = $displayValue;
				} else {
					$value = textlength_check($value);
				}

//				// vtlib customization: For listview javascript triggers
//				$value = "$value <span type='vtlib_metainfo' vtrecordid='{$recordId}' vtfieldname=".
//					"'{$fieldName}' vtmodule='$module' style='display:none;'></span>";
//				// END
				$row[$rawFieldName] = $value;
			}
			$data[$baseRecordId] = $row;
		}
		return $data;
	}
}
?>