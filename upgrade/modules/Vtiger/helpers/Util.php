<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Util_Helper {
	/**
	 * Function used to transform mulitiple uploaded file information into useful format.
	 * @param array $_files - ex: array( 'file' => array('name'=> array(0=>'name1',1=>'name2'),
	 *												array('type'=>array(0=>'type1',2=>'type2'),
	 *												...);
	 * @param type $top
	 * @return array   array( 'file' => array(0=> array('name'=> 'name1','type' => 'type1'),
	 *									array(1=> array('name'=> 'name2','type' => 'type2'),
	 *												...);
	 */
	private static $_transformedFile = false;

	public static function transformUploadedFiles(array $_files, $top = TRUE) {
		if(self::$_transformedFile) return $_files;
		$files = array();
		foreach($_files as $name=>$file) {
			if ($file['name']) {
			if($top) $subName = $file['name'];
			 else	$subName = $name;

			 if(is_array($subName)) {
				 foreach(array_keys($subName) as $key) {
					$files[$name][$key] = array(
							 'name'     => $file['name'][$key],
							 'type'     => $file['type'][$key],
							 'tmp_name' => $file['tmp_name'][$key],
							 'error'    => $file['error'][$key],
							 'size'     => $file['size'][$key],
					 );
					 $files[$name] = self::transformUploadedFiles($files[$name], FALSE);
				}
			}else {
				$files[$name] = $file;
			}
		 }
		}
		self::$_transformedFile = true;
		return $files;
	}

	/**
	 * Function parses date into readable format
	 * @param <Date Time> $dateTime
	 * @return <String>
	 */
	public static function formatDateDiffInStrings($dateTime, $isUserFormat = FALSE) {
		try{
			// http://www.php.net/manual/en/datetime.diff.php#101029
			$currentDateTime = date('Y-m-d H:i:s');

			if($isUserFormat) {
				$dateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($dateTime);
			}
			$seconds =  strtotime($currentDateTime) - strtotime($dateTime);

			if ($seconds == 0) return vtranslate('LBL_JUSTNOW');
			if ($seconds > 0) {
				$prefix = '';
				$suffix = ' '. vtranslate('LBL_AGO');
			} else if ($seconds < 0) {
				$prefix = vtranslate('LBL_DUE') . ' ';
				$suffix = '';
				$seconds = -($seconds);
			}

			$minutes = floor($seconds/60);
			$hours = floor($minutes/60);
			$days = floor($hours/24);
			$months = floor($days/30);

			if ($seconds < 60)	return $prefix . self::pluralize($seconds,	"LBL_SECOND") . $suffix;
			if ($minutes < 60)	return $prefix . self::pluralize($minutes,	"LBL_MINUTE") . $suffix;
			if ($hours < 24)	return $prefix . self::pluralize($hours,	"LBL_HOUR") . $suffix;
			if ($days < 30)		return $prefix . self::pluralize($days,		"LBL_DAY") . $suffix;
			if ($months < 12)	return $prefix . self::pluralize($months,	"LBL_MONTH") . $suffix;
			if ($months > 11)	return $prefix . self::pluralize(floor($days/365), "LBL_YEAR") . $suffix;
		}catch(Exception $e){
			//Not handling if failed to parse
		}	
	}

	/**
	 * Function returns singular or plural text
	 * @param <Number> $count
	 * @param <String> $text
	 * @return <String>
	 */
	public static function pluralize($count, $text) {
		return $count ." ". (($count == 1) ? vtranslate("$text") : vtranslate("${text}S"));
	}

	/**
	 * Function to make the input safe to be used as HTML
	 */
	public static function toSafeHTML($input) {
		global $default_charset;
		return htmlentities($input, ENT_QUOTES, $default_charset);
	}

	/**
	 * Function that will strip all the tags while displaying
	 * @param <String> $input - html data
	 * @return <String> vtiger6 displayable data
	 */
	public static function toVtiger6SafeHTML($input) {
		$allowableTags = '<a><br>';
		return strip_tags($input, $allowableTags);
	}
	/**
	 * Function to validate the input with given pattern.
	 * @param <String> $string
	 * @param <Boolean> $skipEmpty Skip the check if string is empty.
	 * @return <String>
	 * @throws AppException
	 */
	public static function validateStringForSql($string, $skipEmpty=true) {
		if (vtlib_purifyForSql($string, $skipEmpty)) {
			return $string;
		}
		return false;
	}

	/**
	 * Function Checks the existence of the record
	 * @param <type> $recordId - module recordId
	 * returns 1 if record exists else 0
	 */
	public static function checkRecordExistance($recordId){
		global $adb;
		$query = 'Select deleted from vtiger_crmentity where crmid=?';
		$result = $adb->pquery($query, array($recordId));
		return $adb->query_result($result, 'deleted');
	}

	/**
	 * Function to parses date into string format
	 * @param <Date> $date
	 * @param <Time> $time
	 * @return <String>
	 */
	public static function formatDateIntoStrings($date, $time = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue($date . ' ' . $time);

		list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateTimeInUserFormat);
		list($hours, $minutes, $seconds) = explode(':', $timeInUserFormat);

		$displayTime = $hours .':'. $minutes;
		if ($currentUser->get('hour_format') === '12') {
			$displayTime = Vtiger_Time_UIType::getTimeValueInAMorPM($displayTime);
		}

		$today = Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$tomorrow = Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d H:i:s', strtotime('tomorrow')));

		if ($dateInUserFormat == $today) {
			$formatedDate = vtranslate('LBL_TODAY');
			if ($time) {
				$formatedDate .= ' '. vtranslate('LBL_AT') .' '. $displayTime;
			}
		} elseif ($dateInUserFormat == $tomorrow) {
			$formatedDate = vtranslate('LBL_TOMORROW');
			if ($time) {
				$formatedDate .= ' '. vtranslate('LBL_AT') .' '. $displayTime;
			}
		} else {
			/**
			 * To support strtotime() for 'mm-dd-yyyy' format the separator should be '/'
			 * For more referrences
			 * http://php.net/manual/en/datetime.formats.date.php
			 */
			if ($currentUser->get('date_format') === 'mm-dd-yyyy') {
				$dateInUserFormat = str_replace('-', '/', $dateInUserFormat);
			}

			$date = strtotime($dateInUserFormat);
			$formatedDate = vtranslate('LBL_'.date('D', $date)) . ' ' . date('d', $date) . ' ' . vtranslate('LBL_'.date('M', $date));
			if (date('Y', $date) != date('Y')) {
				$formatedDate .= ', '.date('Y', $date);
			}
		}
		return $formatedDate;
	}

	/**
	 * Function to replace spaces with under scores
	 * @param <String> $string
	 * @return <String>
	 */
	public static function replaceSpaceWithUnderScores($string) {
		return str_replace(' ', '_', $string);
	}

	public static function getRecordName ($recordId, $checkDelete=false) {
		if($recordId == 0){
			/**
			* In List view for reference field we are setting raw value in the dom element
			* If we don't have any value for that field then raw value will be 0
			*/
			return false;
		}
		$adb = PearDatabase::getInstance();

		$query = 'SELECT label from vtiger_crmentity where crmid=?';
		if($checkDelete) {
			$query.= ' AND deleted=0';
		}
		$result = $adb->pquery($query,array($recordId));

		$num_rows = $adb->num_rows($result);
		if($num_rows) {
			return $adb->query_result($result,0,'label');
		}
		return false;
	}

	public static function getRecordId($recordName, $module = array(), $checkDelete = false) {
		$adb = PearDatabase::getInstance();

		if(!is_array($module)) {
			$module = array($module);
		}

		$query = 'SELECT crmid from vtiger_crmentity where label=?';
		$params = array($recordName);
		if(!empty($module)) {
			$query .= ' AND setype IN ('. generateQuestionMarks($module).')';
			$params = array_merge($params, $module);
		}
		if($checkDelete) {
			$query.= ' AND deleted=0';
		}
		$result = $adb->pquery($query, $params);

		$num_rows = $adb->num_rows($result);
		if($num_rows) {
			return $adb->query_result($result,0,'crmid');
		}
		return false;
	}

	/**
	 * Function to parse dateTime into Days
	 * @param <DateTime> $dateTime
	 * @return <String>
	 */
	public static function formatDateTimeIntoDayString($dateTime, $skipConversion = FALSE) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = $dateTime;
		if (!$skipConversion) {
			$dateTimeInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue($dateTime);
		}

		list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateTimeInUserFormat);
		list($hours, $minutes, $seconds) = explode(':', $timeInUserFormat);

		$displayTime = $hours .':'. $minutes;
		if ($currentUser->get('hour_format') === '12') {
			$displayTime = Vtiger_Time_UIType::getTimeValueInAMorPM($displayTime);
		}

		/**
		 * To support strtotime() for 'mm-dd-yyyy' format the separator should be '/'
		 * For more referrences
		 * http://php.net/manual/en/datetime.formats.date.php
		 */
		if ($currentUser->get('date_format') === 'mm-dd-yyyy') {
			$dateInUserFormat = str_replace('-', '/', $dateInUserFormat);
		}

		$date = strtotime($dateInUserFormat);
		//Adding date details
		$formatedDate = vtranslate('LBL_'.date('D', $date)). ', ' .vtranslate('LBL_'.date('M', $date)). ' ' .date('d', $date). ', ' .date('Y', $date);
		//Adding time details
		$formatedDate .= ' ' .vtranslate('LBL_AT'). ' ' .$displayTime;

		return $formatedDate;
	}

	/**
	 * Function to get picklist key for a picklist
	 */
	public static function getPickListId($fieldName){
		$pickListIds = array('opportunity_type' => 'opptypeid',
								'sales_stage'	=> 'sales_stage_id',
								'rating'		=> 'rating_id',
								'ticketpriorities'	=> 'ticketpriorities_id',
								'ticketseverities'	=> 'ticketseverities_id',
								'ticketstatus'		=> 'ticketstatus_id',
								'ticketcategories'	=> 'ticketcategories_id',
								'salutationtype'	=> 'salutationid',
								'faqstatus'			=> 'faqstatus_id',
								'faqcategories'		=> 'faqcategories_id',
								'recurring_frequency'=> 'recurring_frequency_id',
								'payment_duration'	=> 'payment_duration_id',
								'language'			=> 'id',
								'recurringtype' => 'recurringeventid',
								'duration_minutes' => 'minutesid'
							);
		if(array_key_exists($fieldName, $pickListIds)){
			return $pickListIds[$fieldName];
		}
		return $fieldName.'id';
	}

	/**
	 * Function which will give the picklist values for a field
	 * @param type $fieldName -- string
	 * @return type -- array of values
	 */
	public static function getPickListValues($fieldName) {
		$cache = Vtiger_Cache::getInstance();
		if($cache->getPicklistValues($fieldName)) {
			return $cache->getPicklistValues($fieldName);
		}
		$db = PearDatabase::getInstance();

		$primaryKey = Vtiger_Util_Helper::getPickListId($fieldName);
		$query = 'SELECT '.$primaryKey.', '.$fieldName.' FROM vtiger_'.$fieldName.' order by sortorderid';
		$values = array();
		$result = $db->pquery($query, array());
		$num_rows = $db->num_rows($result);
		for($i=0; $i<$num_rows; $i++) {
			//Need to decode the picklist values twice which are saved from old ui
			$values[$db->query_result($result,$i,$primaryKey)] = decode_html(decode_html($db->query_result($result,$i,$fieldName)));
		}
		$cache->setPicklistValues($fieldName, $values);
		return $values;
	}

	/**
	 * Function gets the CRM's base Currency information
	 * @return Array
	 */
	public static function getBaseCurrency() {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM vtiger_currency_info WHERE defaultid < 0', array());
		if($db->num_rows($result)) return $db->query_result_rowdata($result, 0);
	}

	/**
	 * Function to get role based picklist values
	 * @param <String> $fieldName
	 * @param <Integer> $roleId
	 * @return <Array> list of role based picklist values
	 */
	public static function getRoleBasedPicklistValues($fieldName, $roleId) {
		if(Vtiger_Cache::get('PicklistRoleBasedValues',$fieldName.$roleId)){
			return Vtiger_Cache::get('PicklistRoleBasedValues',$fieldName.$roleId);
		}
		$db = PearDatabase::getInstance();

		$query = "SELECT $fieldName
				  FROM vtiger_$fieldName
					  INNER JOIN vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$fieldName.picklist_valueid
				  WHERE roleid=? and picklistid in (select picklistid from vtiger_picklist) order by sortorderid";
		$result = $db->pquery($query, array($roleId));
		$picklistValues = Array();
		if($db->num_rows($result) > 0) { 
			while ($row = $db->fetch_array($result)) { 
				//Need to decode the picklist values twice which are saved from old ui 
				$picklistValues[$row[$fieldName]] = decode_html(decode_html($row[$fieldName])); 
			}
		}
		Vtiger_Cache::set('PicklistRoleBasedValues',$fieldName.$roleId,$picklistValues);
		return $picklistValues;
	}

	/**
	 * Function to sanitize the uploaded file name
	 * @param <String> $fileName
	 * @param <Array> $badFileExtensions
	 * @return <String> sanitized file name
	 */
	public static function sanitizeUploadFileName($fileName, $badFileExtensions) {
		$fileName = preg_replace('/\s+/', '_', $fileName);//replace space with _ in filename
		$fileName = rtrim($fileName, '\\/<>?*:"<>|');

		$fileNameParts = explode('.', $fileName);
		$countOfFileNameParts = count($fileNameParts);
		$badExtensionFound = false;

		for ($i=0; $i<$countOfFileNameParts; $i++) {
			$partOfFileName = $fileNameParts[$i];
			if(in_array(strtolower($partOfFileName), $badFileExtensions)) {
				$badExtensionFound = true;
				$fileNameParts[$i] = $partOfFileName . 'file';
			}
		}

		$newFileName = implode('.', $fileNameParts);
		if ($badExtensionFound) {
			$newFileName .= ".txt";
		}
		return $newFileName;
	}

	/**
	 * Function to get maximum upload size
	 * @return <Float> maximum upload size
	 */
	public static function getMaxUploadSize() {
		return ceil(vglobal('upload_maxsize') / (1024 * 1024)); 
	}

	/**
	 * Function to get maximum upload size in bytes
	 * @return <Float> maximum upload size
	 */
	public static function getMaxUploadSizeInBytes() {
		return (self::getMaxUploadSize() * 1024 * 1024);
	}

	/**
	 * Function to get Owner name for ownerId
	 * @param <Integer> $ownerId
	 * @return <String> $ownerName
	 */
	public static function getOwnerName($ownerId) {
		$cache = Vtiger_Cache::getInstance();
		if ($cache->hasOwnerDbName($ownerId)) {
			return $cache->getOwnerDbName($ownerId);
		}

		$ownerModel = Users_Record_Model::getInstanceById($ownerId, 'Users');
		$userName = $ownerModel->get('user_name');
		$ownerName = '';
		if ($userName) {
			$ownerName = $userName;
		} else {
			$ownerModel = Settings_Groups_Record_Model::getInstance($ownerId);
			if(!empty($ownerModel)) {
				$ownerName = $ownerModel->getName();
			}
		}
		if(!empty($ownerName)) {
		$cache->setOwnerDbName($ownerId, $ownerName);
		}
		return $ownerName;
	}

	/**
	 * Function decodes the utf-8 characters
	 * @param <String> $string
	 * @return <String>
	 */
	public static function getDecodedValue($string) {
		return html_entity_decode($string, ENT_COMPAT, 'UTF-8');
	}

	public static function getActiveAdminCurrentDateTime() {
		global $default_timezone;
		$admin = Users::getActiveAdminUser();
		$adminTimeZone = $admin->time_zone;
		@date_default_timezone_set($adminTimeZone);
		$date = date('Y-m-d H:i:s');
		@date_default_timezone_set($default_timezone);
		return $date;
	}
/**
	 * Function to get Creator of this record
	 * @param <Integer> $recordId
	 * @return <Integer>
	 */
	public static function getCreator($recordId) {
		$cache = Vtiger_Cache::getInstance();
		if ($cache->hasCreator($recordId)) {
			return $cache->getCreator($recordId);
		}

		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT smcreatorid FROM vtiger_crmentity WHERE crmid = ?', array($recordId));
		$creatorId = $db->query_result($result, 0, 'smcreatorid');

		if ($creatorId) {
			$cache->setCreator($recordId, $creatorId);
		}
		return $creatorId;
	}


	/**
	 * Function to get the datetime value in user preferred hour format
	 * @param <DateTime> $dateTime
	 * @param <Vtiger_Users_Model> $userObject
	 * @return <String> date and time with hour format
	 */
	public static function convertDateTimeIntoUsersDisplayFormat($dateTime, $userObject = null) {
		require_once 'includes/runtime/LanguageHandler.php';
		require_once 'includes/runtime/Globals.php';
		if ($userObject) {
			$userModel = Users_Privileges_Model::getInstanceFromUserObject($userObject);
		} else {
			$userModel = Users_Privileges_Model::getCurrentUserModel();
		}

		$date = new DateTime($dateTime);
		$dateTimeField = new DateTimeField($date->format('Y-m-d H:i:s'));

		$date = $dateTimeField->getDisplayDate($userModel);
		$time = $dateTimeField->getDisplayTime($userModel);

		if ($userModel->get('hour_format') == '12') {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}

		return $date.' ' .$time;
	}

	/**
	 * Function to get the time value in user preferred hour format
	 * @param <Time> $time
	 * @param <Vtiger_Users_Model> $userObject
	 * @return <String> time with hour format
	 */
	public static function convertTimeIntoUsersDisplayFormat($time, $userObject = null) {
		require_once 'includes/runtime/LanguageHandler.php';
		require_once 'includes/runtime/Globals.php';
		if ($userObject) {
			$userModel = Users_Privileges_Model::getInstanceFromUserObject($userObject);
		} else {
			$userModel = Users_Privileges_Model::getCurrentUserModel();
		}

		if($userModel->get('hour_format') == '12') {
			$time = Vtiger_Time_UIType::getTimeValueInAMorPM($time);
		}

		return $time;
	}

	/**
	 * Function gets the CRM's base Currency information according to user preference
	 * @return Array
	 */
	public static function getUserCurrencyInfo() {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$result = $db->pquery('SELECT * FROM vtiger_currency_info WHERE id = ?', array($currentUser->get('currency_id'))); 
		if($db->num_rows($result)) return $db->query_result_rowdata($result, 0);
	}

	public static function getGroupsIdsForUsers($userId) {
		vimport('~~/include/utils/GetUserGroups.php');

		$userGroupInstance = new GetUserGroups();
		$userGroupInstance->getAllUserGroups($userId);
		return $userGroupInstance->user_groups;
	}

	public static function transferListSearchParamsToFilterCondition($listSearchParams, $moduleModel) {
		if(empty($listSearchParams)) {
			$listSearchParams = array();
		}
		$advFilterConditionFormat = array();
		$glueOrder = array('and','or');
		$groupIterator = 0;
		foreach($listSearchParams as $groupInfo){
			if(empty($groupInfo)){
				$advFilterConditionFormat[] = array();
				$groupIterator++;
				continue;
			}
			$groupConditionInfo = array();
			$groupColumnsInfo = array();
			$groupConditionGlue = $glueOrder[$groupIterator];
			foreach($groupInfo as $fieldSearchInfo){
				   $advFilterFieldInfoFormat = array();
				   $fieldName = $fieldSearchInfo[0];
				   preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);
					if (count($matches) != 0) {
						list($full, $referenceParentField, $referenceModule, $referenceFieldName) = $matches;
						$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
						$fieldInfo = Vtiger_Field_Model::getInstance($referenceFieldName, $referenceModuleModel);
						$fieldInfo->set('reference_fieldname', $fieldName);
					} else {
						$fieldInfo = $moduleModel->getField($fieldName);
						$referenceModule = $moduleModel->getName();
						$referenceFieldName = $fieldName;
					}
					//handling events custom fields from calendar
					if(empty($fieldInfo) && $referenceModule == 'Calendar') {
						$eventsModuleModel = Vtiger_Module_Model::getInstance('Events');
						$fieldInfo = Vtiger_Field_Model::getInstance($referenceFieldName, $eventsModuleModel);
					}

				   $operator = $fieldSearchInfo[1];
				   $fieldValue = $fieldSearchInfo[2];


				   //Request will be having in terms of AM and PM but the database will be having in 24 hr format so converting
					//Database format

					if($fieldInfo->getFieldDataType() == "time") {
						$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
					}

					$specialDateTimeConditions = Vtiger_Functions::getSpecialDateTimeCondtions();
					if($fieldName == 'date_start' || $fieldName == 'due_date' || $fieldInfo->getFieldDataType() == "datetime" && !in_array($operator, $specialDateTimeConditions) ) {
						$dateValues = explode(',', $fieldValue);
						//Indicate whether it is fist date in the between condition
						$isFirstDate = true;
						foreach($dateValues as $key => $dateValue) {
							$dateTimeCompoenents = explode(' ', $dateValue);
							if(empty($dateTimeCompoenents[1])) {
								if($isFirstDate)
									$dateTimeCompoenents[1] = '00:00:00';
								else
									$dateTimeCompoenents[1] = '23:59:59';

							}
							$dateValue = implode(' ',$dateTimeCompoenents);
							$dateValues[$key] = $dateValue;
							$isFirstDate = false;
						}
						$fieldValue = implode(',',$dateValues);
					}

				   $advFilterFieldInfoFormat['columnname'] = $fieldInfo->getCustomViewColumnName();
				   $advFilterFieldInfoFormat['comparator'] = $operator;
				   $advFilterFieldInfoFormat['value'] = $fieldValue;
				   $advFilterFieldInfoFormat['column_condition'] = $groupConditionGlue;
				   $groupColumnsInfo[] = $advFilterFieldInfoFormat;
			}
			$noOfConditions = count($groupColumnsInfo);
			//to remove the last column condition
			$groupColumnsInfo[$noOfConditions-1]['column_condition']  = '';
			$groupConditionInfo['columns'] = $groupColumnsInfo;
			$groupConditionInfo['condition'] = 'and';
			$advFilterConditionFormat[] = $groupConditionInfo;
			$groupIterator++;
		}
		//We aer removing last condition since this condition if there is next group and this is the last group
		unset($advFilterConditionFormat[count($advFilterConditionFormat)-1]['condition']);
		return $advFilterConditionFormat;

	}

	 /***
	* Function to set the default calendar activity types for new user
	* @param <Integer> $userId - id of the user
	*/
	public static function setCalendarDefaultActivityTypesForUser($userId) {
		$db = PearDatabase::getInstance();
		$userEntries = $db->pquery('SELECT 1 FROM vtiger_calendar_user_activitytypes WHERE userid=?', array($userId));
		if($db->num_rows($userEntries) <= 0) {
			$queryResult = $db->pquery('SELECT id, defaultcolor FROM vtiger_calendar_default_activitytypes WHERE isdefault = ?', array('1'));
			$numRows = $db->num_rows($queryResult);
			$activityIds = array();
			for ($i = 0; $i < $numRows; $i++) {
				$row = $db->query_result_rowdata($queryResult, $i);
				$activityIds[$row['id']] = $row['defaultcolor'];
			}

			foreach($activityIds as $activityId=>$color) {
				$db->pquery('INSERT INTO vtiger_calendar_user_activitytypes (id, defaultid, userid, color) VALUES (?,?,?,?)', array($db->getUniqueID('vtiger_calendar_user_activitytypes'), $activityId, $userId, $color));
			}
		}

	}

	public static function getAllSkins(){
		return array('alphagrey' => '#666666',	'softed'	=> '#1560BD',	'bluelagoon'=> '#204E81',
					 'nature'	=> '#008D4C',	'woodspice' => '#C19803',	'orchid'	=> '#C65479',
					 'firebrick'=> '#E51400',	'twilight'	=> '#404952',	'almond'	=> '#894400');
	}

	public static function isUserDeleted($userid) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT deleted FROM vtiger_users WHERE id = ? AND (status=? OR deleted=?)', array($userid, 'Inactive', 1));
		$count = $db->num_rows($result);
		if($count > 0)
			return true;

		return false;
	}

	/*
	* Function used to get default value based on data type
	* @param $dataType - data type of field
	* @return returns default value for data type if match case found
	* else returns empty string
	*/
   function getDefaultMandatoryValue($dataType) {
	   $value;
	   switch ($dataType) {
		   case 'date':
		   case 'datetime' :
				   $dateObject = new DateTime();
				   $value = DateTimeField::convertToUserFormat($dateObject->format('Y-m-d'));
			   break;
		   case 'time' :
			   $value = '00:00';
			   break;
		   case 'boolean':
			   $value = false;
			   break;
		   case 'email':
			   $value = '??@??.??';
			   break;
		   case 'url':
			   $value = '???.??';
			   break;
		   case 'integer':
			   $value = 0;
			   break;
		   case 'double':
			   $value = 00.00;
			   break;
		   case 'currency':
			   $value = 0.00;
			   break;
		   case 'reference' :
			   $value = '';
			   break;
		   case 'picklist' :
			   $value = '';
			   break;
		   case 'multipicklist' :
			   $value = '';
			   break;
		   default :
			   $value = '?????';
			   break;
	   }
	   return $value;
   }
   public static function checkDbUTF8Support($conn) {
		global $db_type;
		if($db_type == 'pgsql')
			return true;
		$dbvarRS = $conn->Execute("show variables like '%_database' ");
		$db_character_set = null;
		$db_collation_type = null;
		while(!$dbvarRS->EOF) {
			$arr = $dbvarRS->FetchRow();
			$arr = array_change_key_case($arr);
			switch($arr['variable_name']) {
				case 'character_set_database' : $db_character_set = $arr['value']; break;
				case 'collation_database'     : $db_collation_type = $arr['value']; break;
			}
			// If we have all the required information break the loop. 
			if($db_character_set != null && $db_collation_type != null) break;
		}
		return (stristr($db_character_set, 'utf8') && stristr($db_collation_type, 'utf8'));
	}

	public static function checkDbLocalInfileSupport() {
		$db = PearDatabase::getInstance();
		$rs = $db->pquery("show variables like 'local_infile'", array());
		$db_local_infile = null;
		while ($arr = $db->fetch_array($rs)) {
			switch($arr['variable_name']) {
				case 'local_infile': $db_local_infile = $arr['value']; break;
			}
			if ($db_local_infile != null) break;
		}
		return ($db_local_infile == '1' || strtolower($db_local_infile) == 'on');
	}

	/**
	 * Function to get both date string and date difference string
	 * @param <Date Time> $dateTime
	 * @return <String>
	 */
	public static function formatDateAndDateDiffInString($dateTime) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$dateTimeInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue($dateTime);

		list($dateInUserFormat, $timeInUserFormat) = explode(' ', $dateTimeInUserFormat);
		list($hours, $minutes, $seconds) = explode(':', $timeInUserFormat);

		$displayTime = $hours.':'.$minutes;
		if ($currentUser->get('hour_format') === '12') {
			$displayTime = Vtiger_Time_UIType::getTimeValueInAMorPM($displayTime);
		}

		$today = Vtiger_Date_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));

		if ($dateInUserFormat == $today) {
			$formatedDate = $displayTime;
		} else {
			/**
			 * To support strtotime() for 'mm-dd-yyyy' format the separator should be '/'
			 * For more referrences
			 * http://php.net/manual/en/datetime.formats.date.php
			 */
			if ($currentUser->get('date_format') === 'mm-dd-yyyy') {
				$dateInUserFormat = str_replace('-', '/', $dateInUserFormat);
			}

			$date = strtotime($dateInUserFormat);
			$formatedDate = date('d', $date).' '.vtranslate('LBL_'.date('M', $date));
			if (date('Y', $date) != date('Y')) {
				$formatedDate = $dateInUserFormat;
			}
		}
		$dateDiffString = self::formatDateDiffInStrings($dateTime);
		$formatedDateAndDiff = $formatedDate." (".$dateDiffString.")";

		return $formatedDateAndDiff;
	}

	 /**
	 * Function to convert PHP array to Json format.
	 * This is similiar to json_encode($data, JSON_UNESCAPED_UNICODE); to work 
	 * in php ver < 5.4
	 * 
	 * Refrences : http://stackoverflow.com/questions/9801533/json-encode-with-option-json-unescaped-unicode
	 *             https://code.google.com/p/apns-php/issues/detail?id=22 
	 * @param <array> $data
	 * @return <json> $unescapedUtf8Json
	 */    
	public static function toJsonWithUnescapedUtf8($data) {
		if (!is_array($data)) {
			$data = array($data);
		}
		$escapedUtf8Json = Zend_Json::encode($data);

		$unescapedUtf8Json = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function($matches) {
			if (function_exists('mb_convert_encoding')) {
				return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UTF-16');
			} else {
				//Slower conversion from UTF-16 to UTF-8 (BMP Only)
				//See: http://www.cl.cam.ac.uk/~mgk25/unicode.html
				$decimal_code = hexdec($matches[1]);
				$character = "";
				if ((0x7F & $decimal_code) == $decimal_code) {
					//UTF-8 1-byte aka ASCII
					$first_byte = 0x7F & $decimal_code;
					$character = chr($first_byte);
				} elseif ((0x7FF & $decimal_code) == $decimal_code) {
					//UTF-8 2-bytes
					$first_byte = 0xC0 | (($decimal_code >> 6) & 0x1F);
					$second_byte = 0x80 | ($decimal_code & 0x3F);
					$character = chr($first_byte).chr($second_byte);
				} elseif ((0xFFFF & $decimal_code) == $decimal_code) {
					//UTF-8 3-bytes
					$first_byte = 0xE0 | (($decimal_code >> 12) & 0x0F);
					$second_byte = 0x80 | (($decimal_code >> 6) & 0x3F);
					$third_byte = 0x80 | ($decimal_code & 0x3F);
					$character = chr($first_byte).chr($second_byte).chr($third_byte);
				}
				return $character;
			}
		}, $escapedUtf8Json);

		return $unescapedUtf8Json;
	}

	/*
	 * Function to escape string for sql query.
	 * It returns string with escaped _ and %
	 */
	public static function escapeSqlString($string) {
		return str_replace(array('\\','_', '%'), array('\\\\','\_', '\%'), $string);
	}

	public static function GetDirectorySize($path) {
		$bytestotal = 0;
		$path = realpath($path);
		if ($path !== false) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
				$bytestotal += $object->getSize();
			}
		}
		return $bytestotal;
	}

	public static function getCalendarReferenceModulesList() {
		$moduleInstance = Vtiger_Module_Model::getInstance('Calendar');
		$fieldInstance = Vtiger_Field_Model::getInstance('parent_id', $moduleInstance);
		$referenceModuleList = $fieldInstance->getReferenceList();
		return $referenceModuleList;
	}

	public static function getBrowserInfo() {
		$u_agent = vtlib_purify($_SERVER['HTTP_USER_AGENT']);
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version = "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		} elseif (preg_match('/Firefox/i', $u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		} elseif (preg_match('/Chrome/i', $u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		} elseif (preg_match('/Safari/i', $u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		} elseif (preg_match('/Opera/i', $u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		} elseif (preg_match('/Netscape/i', $u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>'.join('|', $known) .
				')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} else {
			$version = $matches['version'][0];
		}

		// check if we have a number
		if ($version == null || $version == "") {
			$version = "?";
		}

		$browserInfo = array(
			'userAgent' => $u_agent,
			'name' => $bname,
			'userBrowser' => strtolower($ub),
			'version' => $version,
			'platform' => $platform,
			'pattern' => $pattern
		);

		$browserInfoModel = new Vtiger_Base_Model($browserInfo);
		return $browserInfoModel;
	}

	static function detectModulenameFromRecordId($wsrecordid) {
		global $adb;
		$idComponents = vtws_getIdComponents($wsrecordid);
		$result = $adb->pquery("SELECT name FROM vtiger_ws_entity WHERE id=?", array($idComponents[0]));
		if ($result && $adb->num_rows($result)) {
			return $adb->query_result($result, 0, 'name');
		}
		return false;
	}

	static $detectFieldnamesToResolveCache = array();

	static function detectFieldnamesToResolve($module) {
		global $adb;

		// Cache hit?
		if (isset(self::$detectFieldnamesToResolveCache[$module])) {
			return self::$detectFieldnamesToResolveCache[$module];
		}

		$resolveUITypes = array(10, 101, 116, 117, 26, 357, 50, 51, 52, 53, 57, 58, 59, 66, 68, 73, 75, 76, 77, 78, 80, 81);

		$result = $adb->pquery(
			"SELECT DISTINCT fieldname FROM vtiger_field WHERE uitype IN(" .
				generateQuestionMarks($resolveUITypes).") AND tabid=?", array($resolveUITypes, getTabid($module))
		);
		$fieldnames = array();
		while ($resultrow = $adb->fetch_array($result)) {
			$fieldnames[] = $resultrow['fieldname'];
		}

		// Cache information		
		self::$detectFieldnamesToResolveCache[$module] = $fieldnames;

		return $fieldnames;
	}

	static function resolveRecordValues(&$record, $user = null, $ignoreUnsetFields = false) {
		$userTypeFields = array('assigned_user_id', 'creator', 'userid', 'created_user_id', 'modifiedby');

		if (empty($record))
			return $record;

		$module = self::detectModulenameFromRecordId($record['id']);
		$fieldnamesToResolve = self::detectFieldnamesToResolve($module);

		if (!empty($fieldnamesToResolve)) {
			foreach ($fieldnamesToResolve as $resolveFieldname) {

				if (isset($record[$resolveFieldname]) && !empty($record[$resolveFieldname])) {
					$fieldvalueid = $record[$resolveFieldname];

					if (in_array($resolveFieldname, $userTypeFields)) {
						$fieldvalue = decode_html(trim(vtws_getName($fieldvalueid, $user)));
					} else {
						$fieldvalue = self::fetchRecordLabelForId($fieldvalueid);
					}
					$record[$resolveFieldname] = $fieldvalue;
				}
			}
		}
		return $record;
	}

	static function fetchRecordLabelsForIds($recordIds) {
		global $adb;
		$crmIds = array();

		foreach ($recordIds as $id) {
			$idComponents = vtws_getIdComponents($id);
			$crmIds[] = $idComponents[1];
		}
		$sqlResult = $adb->pquery("SELECT crmid,label from vtiger_crmentity WHERE crmid IN (".generateQuestionMarks($crmIds).") ;", $crmIds);
		$num_rows = $adb->num_rows($sqlResult);

		$labels = array();
		for ($i = 0; $i < $num_rows; $i++) {
			$crmId = $adb->query_result($sqlResult, $i, 'crmid');
			$recordId = $recordIds[array_search($crmId, $crmIds)];
			$labels[$recordId] = decode_html($adb->query_result($sqlResult, $i, 'label'));
		}
		return $labels;
	}

	static function fetchRecordLabelForId($recordId) {
		$recordLabels = self::fetchRecordLabelsForIds(array($recordId));

		foreach ($recordLabels as $key => $value) {
			if ($recordId == $key) {
				return $value;
			}
		}
		return null;
	}

	static function getRelatedModuleLabel($relatedModule, $parentModule = "Contacts") {
		global $adb;

		if (in_array($relatedModule, array('ProjectTask', 'ProjectMilestone')))
			$parentModule = 'Project';
		$sql = "SELECT vtiger_relatedlists.label FROM vtiger_relatedlists
				INNER JOIN vtiger_tab ON vtiger_relatedlists.related_tabid =vtiger_tab.tabid WHERE vtiger_tab.name=? AND vtiger_relatedlists.tabid=?";
		$sqlResult = $adb->pquery($sql, array($relatedModule, getTabid($parentModule)));

		if ($adb->num_rows($sqlResult) > 0) {
			$relatedModuleLabel = $adb->query_result($sqlResult, 0, 'label');
		}

		return $relatedModuleLabel;
	}

//	Source should be Zapier if record is Created from Zapier
	static function fillMandatoryFields($fieldName, $module, $source = '') {
		global $adb;
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
		$fieldDataType = $fieldModel->getFieldDataType();
		$defaultValue = $fieldModel->getDefaultFieldValue();

		switch ($fieldDataType) {
			case 'date'			:	$value = $defaultValue;
									if (empty($defaultValue)) {
										$dateObject = new DateTime();
										$value = $dateObject->format('Y-m-d');
									}
									break;
			case 'datetime'		:	$value = $defaultValue;
									if (empty($defaultValue)) {
										$dateObject = new DateTime();
										$value = DateTimeField::convertToUserFormat($dateObject->format('Y-m-d'));
									}
									break;
			case 'time'			:	$value = '00:00:00';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'text'			:	$value = '?????';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'phone'		:	$value = '?????';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'boolean'		:	$value = false;
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'email'		:	$value = '??@??.??';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'string'		:	$value = '?????';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'url'			:	$value = '???.??';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'integer'		:	$value = 0;
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'double'		:	$value = 00.00;
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'currency'		:	$value = 0.00;
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'skype'		:	$value = '?????';
									if (!empty($defaultValue)) {
										$value = $defaultValue;
									}
									break;
			case 'picklist'		:	$pickListDetails = $fieldModel->getPicklistValues();
									foreach ($pickListDetails as $key => $value) {
										$value = $key;
										break;
									}
									break;
			case 'multipicklist':	$pickListDetails = $fieldModel->getPicklistValues();
									foreach ($pickListDetails as $key => $value) {
										$value = $key;
										break;
									}
									break;
			case 'documentsFolder':	// get default folder wsId
									$value = vtws_getWebserviceEntityId("DocumentFolders", "1");
									break;
			case 'reference'	:	$referenceFieldModule = $fieldModel->getReferenceList(true);
									if (count($referenceFieldModule) > 0) {
										$user = Users_Record_Model::getCurrentUserModel();
										$referenceModule = $referenceFieldModule[0];
										$referenceFieldModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
										$mandatoryFieldModels = $referenceFieldModuleModel->getMandatoryFieldModels();
										$nameFields = $referenceFieldModuleModel->getNameFields();
										$element = array();

										foreach ($mandatoryFieldModels as $mandatoryFieldModel) {
											$fieldName = $mandatoryFieldModel->get('name');
											$type = $mandatoryFieldModel->getFieldDataType();
											if ($type == 'reference')
												return '';

											$fieldValue = Vtiger_Util_Helper::fillMandatoryFields($fieldName, $referenceModule);
											$element[$fieldName] = $fieldValue;
										}
										$element['assigned_user_id'] = vtws_getWebserviceEntityId('Users', $user->id);

										$fieldLabel = '';
										foreach ($nameFields as $nameField) {
											$fieldLabel .= $element[$nameField].' ';
										}
										$fieldLabel = trim($fieldLabel);

										$query = "SELECT crmid FROM vtiger_crmentity WHERE label = ? AND deleted = ? AND setype = ?";
										$result = $adb->pquery($query, array($fieldLabel, 0, $referenceModule));

										if ($adb->num_rows($result)) {
											$crmid = $adb->query_result($result, 0, 'crmid');
											return $crmid;
										} else {
											try {
												if (isset($source) && !empty($source)) {
													$element['source'] = $source;
												}
												if (!function_exists(vtws_create)) {
													include_once 'include/Webservices/Create.php';
												}
												$entity = vtws_create($referenceModule, $element, $user);
												$wsId = vtws_getIdComponents($entity['id']);
												return $wsId[1];
											} catch (Exception $ex) {
												return '';
											}
										}
									} else {
										return '';
									}
									break;
			default				:	$value = '?????';
									break;
		}
		return $value;
	}

	public static function convertSpaceToHyphen($string) {
		if (!empty($string)) {
			return str_replace(" ", "-", $string);
		}
	}

	public static function escapeCssSpecialCharacters($string) {
		if(!empty($string)) {
			$pattern = "/[!#$%&'()*+,.\/:;<=>?@^`~]/";
			return preg_replace($pattern, '\\\\$0', $string);
		}
	}
}
