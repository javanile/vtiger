<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_Record_Model extends Vtiger_Record_Model {
	
	/**
	 * Checks if the key is in property or data.
	 */
	public function has($key) {
		return property_exists($this, $key) || parent::has($key);
	}

	/**
	 * Gets the value of the key . First it will check whether specified key is a property if not it
	 *  will get from normal data attribure from base class
	 * @param <string> $key - property or key name
	 * @return <object>
	 */
	public function get($key) {
		if(property_exists($this, $key)) {
			return $this->$key;
		}
		return parent::get($key);
	}
    
    /**
     * Sets the value of the key . First it will check whether specified key is a property if not it
     * will set from normal set from base class
     * @param <string> $key - property or key name
     * @param <string> $value
     */
    public function set($key, $value) {
        if(property_exists($this, $key)) {
            $this->$key = $value;
        }
        parent::set($key, $value);
        return $this;
    }

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$module->getDetailViewName().'&record='.$this->getId();
	}
	
	/**
	 * Function to get the Detail View url for the Preferences page
	 * @return <String> - Record Detail View Url
	 */
	public function getPreferenceDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view=PreferenceDetail&parent=Settings&record='.$this->getId();
	}
	
	public function getCalendarSettingsDetailViewUrl(){
		return 'index.php?module=' .$this->getModuleName() . '&parent=Settings&view=Calendar&record='.$this->getId();
	}
	
	public function getCalendarSettingsEditViewUrl(){
		return 'index.php?module='.$this->getModuleName() . '&parent=Settings&view=Calendar&mode=Edit&record='.$this->getId();
	}
    
    public function getMyTagSettingsListUrl() {
        return 'index.php?module=Tags&parent=Settings&view=List&record='.$this->getId();
    }
	/**
	 * Function to get the url for the Profile page
	 * @return <String> - Profile Url
	 */
	public function getProfileUrl() {
		$module = $this->getModule();
		return 'index.php?module=Users&view=ChangePassword&mode=Profile';
	}
	
	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$module->getEditViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the Edit View url for the Preferences page
	 * @return <String> - Record Detail View Url
	 */
	public function getPreferenceEditViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view=PreferenceEdit&parent=Settings&record='.$this->getId();
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$module->getDeleteActionName().'User&record='.$this->getId();
	}

	public function getChangeUsernameUrl(){
		return 'index.php?module='.$this->getModuleName().'&view=EditAjax&mode=changeUsername&record='.$this->getId();
	}

	public function getChangePwdUrl(){
		return 'index.php?module='.$this->getModuleName().'&view=EditAjax&mode=changePassword&recordId='.$this->getId();
	}

	/**
	 * Function to check whether the user is an Admin user
	 * @return <Boolean> true/false
	 */
	public function isAdminUser() {
		$adminStatus = $this->get('is_admin');
		if ($adminStatus == 'on') {
			return true;
		}
		return false;
	}

	/**
	 * Function to get the module name
	 * @return <String> Module Name
	 */
	public function getModuleName() {
		$module = $this->getModule();
		if($module) {
			return parent::getModuleName();
		}
		//get from the class propety module_name
		return $this->get('module_name');
	}

	/**
	 * Function to save the current Record Model
	 */
	public function save() {
        $newUser = false;
        $userId = $this->getId();
        
        if(empty($userId)) {
            $newUser = true;
        }
		parent::save();
		$this->saveTagCloud();
        $this->addDashboardTabs();
	}
    
    /**
     * Funtion to add default Dashboard Tab in Vtiger7 Home Page
     */
    function addDashboardTabs(){
        $db = PearDatabase::getInstance();
        $tabs = array("1"=>array('name'=>"My Dashboard",'app'=>'','module' =>''));
        $isDefault = 1;
        foreach($tabs as $seq => $tabName){
            if(is_array($tabName)) {
                $appName = $tabName['app'];
                $moduleName = $tabName['module'];
                $tabName = $tabName['name'];
            }
           
            $db->pquery("INSERT INTO vtiger_dashboard_tabs(tabname,userid,isdefault,sequence,appname,modulename) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE tabname=?,userid=?,appname=?,modulename=?",
                    array($tabName, $this->getId(),$isDefault,$seq,$appName,$moduleName,$tabName, $this->getId(),$appName, $moduleName));
        }
    }

	/**
	 * Function to get all the Home Page components list
	 * @return <Array> List of the Home Page components
	 */
	public function getHomePageComponents() {
		$entity = $this->getEntity();
		$homePageComponents = $entity->getHomeStuffOrder($this->getId());
		return $homePageComponents;
	}

	/**
	 * Static Function to get the instance of the User Record model for the current user
	 * @return Users_Record_Model instance
	 */
	public static $currentUserModels = array();
	public static function getCurrentUserModel() {
		//TODO : Remove the global dependency
		$currentUser = vglobal('current_user');
		if(!empty($currentUser)) {
			
			// Optimization to avoid object creation every-time
			// Caching is per-id as current_user can get swapped at runtime (ex. workflow)
			$currentUserModel = NULL;
			if (isset(self::$currentUserModels[$currentUser->id])) {
				$currentUserModel = self::$currentUserModels[$currentUser->id];
				if ($currentUser->column_fields['modifiedtime'] != $currentUserModel->get('modifiedtime')) {
					$currentUserModel = NULL;
		}
			}
			if (!$currentUserModel) {
				$currentUserModel = self::getInstanceFromUserObject($currentUser);
				self::$currentUserModels[$currentUser->id] = $currentUserModel;
			}
			return $currentUserModel;
		}
		return new self();
	}

	/**
	 * Static Function to get the instance of the User Record model from the given Users object
	 * @return Users_Record_Model instance
	 */
	public static function getInstanceFromUserObject($userObject) {
		$objectProperties = get_object_vars($userObject);
		$userModel = new self();
		foreach($objectProperties as $properName=>$propertyValue){
			$userModel->$properName = $propertyValue;
		}
		return $userModel->setData($userObject->column_fields)->setModule('Users')->setEntity($userObject);
	}

	/**
	 * Static Function to get the instance of all the User Record models
	 * @return <Array> - List of Users_Record_Model instances
	 */
	public static function getAll($onlyActive=true, $excludeDefaultAdmin = true) {
		$db = PearDatabase::getInstance();

		$sql = 'SELECT id FROM vtiger_users';
		$params = array();
		if($onlyActive) {
			$sql .= ' WHERE status = ?';
			$params[] = 'Active';
		}
		$result = $db->pquery($sql, $params);

		$noOfUsers = $db->num_rows($result);
		$users = array();
		if($noOfUsers > 0) {
			$focus = new Users();
			for($i=0; $i<$noOfUsers; ++$i) {
				$userId = $db->query_result($result, $i, 'id');
				$focus->id = $userId;
				$focus->retrieve_entity_info($userId, 'Users');

				$userModel = self::getInstanceFromUserObject($focus);
				$users[$userModel->getId()] = $userModel;
			}
		}
		return $users;
	}

	/**
	 * Function returns the Subordinate users
	 * @return <Array>
	 */
	function getSubordinateUsers() {
		$privilegesModel = $this->get('privileges');

		if(empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		$subordinateUsers = array();
		$subordinateRoleUsers = $privilegesModel->get('subordinate_roles_users');
		if($subordinateRoleUsers) {
			foreach($subordinateRoleUsers as $role=>$users) {
				foreach($users as $user) {
					$subordinateUsers[$user] = $privilegesModel->get('first_name').' '.$privilegesModel->get('last_name');
				}
			}
		}
		return $subordinateUsers;
	}

	/**
	 * Function returns the Users Parent Role
	 * @return <String>
	 */
	function getParentRoleSequence() {
		$privilegesModel = $this->get('privileges');

		if(empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		return $privilegesModel->get('parent_role_seq');
	}

	/**
	 * Function returns the Users Current Role
	 * @return <String>
	 */
	function getRole() {
		$privilegesModel = $this->get('privileges');

		if(empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		return $privilegesModel->get('roleid');
	}
    
    /**
     * Function returns User Current Role Name
     * @return <String>
     */
    function getUserRoleName(){
        global $adb;
        $roleName = null;
        $roleId = $this->get('roleid');
        $query = "SELECT rolename from vtiger_role WHERE roleid = ?";
        $result = $adb->pquery($query, array($roleId));
        if($result){
            $roleName = $adb->query_result($result, 0,'rolename');
        }
        return $roleName;
    }
    

	/**
	 * Function returns List of Accessible Users for a Module
	 * @param <String> $module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleUsersForModule($module) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$curentUserPrivileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if($currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$users = $this->getAccessibleUsers("",$module);
		} else {
			$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($module);
			if($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$users = $this->getAccessibleUsers('private',$module);
			} else {
				$users = $this->getAccessibleUsers("",$module);
			}
		}
		return $users;
	}

	/**
	 * Function returns List of Accessible Users for a Module
	 * @param <String> $module
	 * @return <Array of Users_Record_Model>
	 */
	public function getAccessibleGroupForModule($module) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$curentUserPrivileges = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if($currentUser->isAdminUser() || $curentUserPrivileges->hasGlobalWritePermission()) {
			$groups = $this->getAccessibleGroups("",$module);
		} else {
			$sharingAccessModel = Settings_SharingAccess_Module_Model::getInstance($module);
			if($sharingAccessModel && $sharingAccessModel->isPrivate()) {
				$groups = $this->getAccessibleGroups('private',$module);
			} else {
				$groups = $this->getAccessibleGroups("",$module);
			}
		}
		return $groups;
	}

	/**
	 * Function to get Images Data
	 * @return <Array> list of Image names and paths
	 */
	public function getImageDetails() {
		$db = PearDatabase::getInstance();

		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
                        // Not a good approach to get all the fields if not required(May lead to Performance issue)
			$query = "SELECT vtiger_attachments.attachmentsid, vtiger_attachments.path, vtiger_attachments.name FROM vtiger_attachments
                                  LEFT JOIN vtiger_salesmanattachmentsrel ON vtiger_salesmanattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
                                  WHERE vtiger_salesmanattachmentsrel.smid=?";

			$result = $db->pquery($query, array($recordId));

			$imageId = $db->query_result($result, 0, 'attachmentsid');
			$imagePath = $db->query_result($result, 0, 'path');
			$imageName = $db->query_result($result, 0, 'name');

			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = urlencode(decode_html($imageName));

			$imageDetails[] = array(
					'id' => $imageId,
					'orgname' => $imageOriginalName,
					'path' => $imagePath.$imageId,
					'name' => $imageName
			);
		}
		return $imageDetails;
	}


    /**
	 * Function to get all the accessible users
	 * @return <Array>
	 */
	public function getAccessibleUsers($private="",$module = false) {
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$accessibleUser = Vtiger_Cache::get('vtiger-'.$this->getRole().'-'.$currentUserRoleModel->get('allowassignedrecordsto'), 'accessibleusers');
        if(empty($accessibleUser)) {
			if($currentUserRoleModel->get('allowassignedrecordsto') === '1' || $private == 'Public') {
				$accessibleUser = get_user_array(false, "ACTIVE", "", $private,$module);
			} else if($currentUserRoleModel->get('allowassignedrecordsto') === '2'){
				$accessibleUser = $this->getSameLevelUsersWithSubordinates();
			} else if($currentUserRoleModel->get('allowassignedrecordsto') === '3') {
				$accessibleUser = $this->getRoleBasedSubordinateUsers();
			}
			Vtiger_Cache::set('vtiger-'.$this->getRole().'-'.$currentUserRoleModel->get('allowassignedrecordsto'), 'accessibleusers',$accessibleUser);
		}
		return $accessibleUser;
	}

	/**
	 * Function to get same level and subordinates Users
	 * @return <array> Users
	 */
	public function getSameLevelUsersWithSubordinates(){
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$sameLevelRoles = $currentUserRoleModel->getSameLevelRoles();
		$sameLevelUsers = $this->getAllUsersOnRoles($sameLevelRoles);
		$subordinateUsers = $this->getRoleBasedSubordinateUsers();
		foreach ($subordinateUsers as $userId => $userName) {
			$sameLevelUsers[$userId] = $userName;
		}
		return $sameLevelUsers;
	}
	
	/**
	 * Function to get subordinates Users
	 * @return <array> Users
	 */
	public function getRoleBasedSubordinateUsers(){
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($this->getRole());
		$childernRoles = $currentUserRoleModel->getAllChildren();
		$users = $this->getAllUsersOnRoles($childernRoles);
        $currentUserDetail = array($this->getId() => $this->get('first_name').' '.$this->get('last_name'));
        $users = $currentUserDetail + $users;
        return $users;
	}

	/**
	 * Function to get the users based on Roles
	 * @param type $roles
	 * @return <array>
	 */
	public function getAllUsersOnRoles($roles) {
		$db = PearDatabase::getInstance();
		$roleIds = array();
		foreach ($roles as $key => $role) {
			$roleIds[] = $role->getId();
		}
        
        if(empty($roleIds)) {
            return array();
        }
        
		$sql = 'SELECT userid FROM vtiger_user2role WHERE roleid IN ('.  generateQuestionMarks($roleIds).')';
		$result = $db->pquery($sql, $roleIds);
		$noOfUsers = $db->num_rows($result);
		$userIds = array();
		$subUsers = array();
		if($noOfUsers > 0) {
			for($i=0; $i<$noOfUsers; ++$i) {
				$userIds[] = $db->query_result($result, $i, 'userid');
			}
			$query = 'SELECT id, first_name, last_name FROM vtiger_users WHERE status = ? AND id IN ('.  generateQuestionMarks($userIds).')';
			$result = $db->pquery($query, array('ACTIVE', $userIds));
			$noOfUsers = $db->num_rows($result);
			for($j=0; $j<$noOfUsers; ++$j) {
				$userId = $db->query_result($result, $j,'id');
				$firstName = $db->query_result($result, $j, 'first_name');
				$lastName = $db->query_result($result, $j, 'last_name');
				$subUsers[$userId] = $firstName .' '.$lastName;
			}
		}
		return $subUsers;
	}
	
	/**
	 * Function to get all the accessible groups
	 * @return <Array>
	 */
	public function getAccessibleGroups($private="",$module = false) {
		//TODO:Remove dependence on $_REQUEST for the module name in the below API
        $accessibleGroups = Vtiger_Cache::get('vtiger-'.$private, 'accessiblegroups');
        if(!$accessibleGroups){
            $accessibleGroups = get_group_array(false, "ACTIVE", "", $private,$module);
            Vtiger_Cache::set('vtiger-'.$private, 'accessiblegroups',$accessibleGroups);
        }
		return $accessibleGroups;
	}

	/**
	 * Function to get privillage model
	 * @return $privillage model
	 */
	public function getPrivileges() {
		$privilegesModel = $this->get('privileges');

		if (empty($privilegesModel)) {
			$privilegesModel = Users_Privileges_Model::getInstanceById($this->getId());
			$this->set('privileges', $privilegesModel);
		}

		return $privilegesModel;
	}

	/**
	 * Function to get user default activity view
	 * @return <String>
	 */
	public function getActivityView() {
		$activityView = $this->get('activity_view');
		return $activityView;
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId) {
		$db = PearDatabase::getInstance();

		$checkResult = $db->pquery('SELECT smid FROM vtiger_salesmanattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$smId = $db->query_result($checkResult, 0, 'smid');

		if ($this->getId() === $smId) {
			$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM vtiger_salesmanattachmentsrel WHERE attachmentsid = ?', array($imageId));
            $db->pquery('DELETE FROM vtiger_crmentity WHERE crmid = ?',array($imageId));
			return true;
		}
		return false;
	}


	/**
	 * Function to get the Day Starts picklist values
	 * @param type $name Description
	 */
	public static function getDayStartsPicklistValues($stucturedValues){
		$fieldModel = $stucturedValues['LBL_CALENDAR_SETTINGS'];
		$hour_format = $fieldModel['hour_format']->getPicklistValues();
		$start_hour = $fieldModel['start_hour']->getPicklistValues();

		$defaultValues = array('00:00'=>'12:00 AM','01:00'=>'01:00 AM','02:00'=>'02:00 AM','03:00'=>'03:00 AM','04:00'=>'04:00 AM','05:00'=>'05:00 AM',
					'06:00'=>'06:00 AM','07:00'=>'07:00 AM','08:00'=>'08:00 AM','09:00'=>'09:00 AM','10:00'=>'10:00 AM','11:00'=>'11:00 AM','12:00'=>'12:00 PM',
					'13:00'=>'01:00 PM','14:00'=>'02:00 PM','15:00'=>'03:00 PM','16:00'=>'04:00 PM','17:00'=>'05:00 PM','18:00'=>'06:00 PM','19:00'=>'07:00 PM',
					'20:00'=>'08:00 PM','21:00'=>'09:00 PM','22:00'=>'10:00 PM','23:00'=>'11:00 PM');

		$picklistDependencyData = array();
		foreach ($hour_format as $value) {
			if($value == 24){
				$picklistDependencyData['hour_format'][$value]['start_hour'] = $start_hour;
			}else{
				$picklistDependencyData['hour_format'][$value]['start_hour'] = $defaultValues;
			}
		}
		if(empty($picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'])) {
			$picklistDependencyData['hour_format']['__DEFAULT__']['start_hour'] = $defaultValues;
		}
		return $picklistDependencyData;
	}

	/**
	 * Function returns if tag cloud is enabled or not
	 */
	function getTagCloudStatus() {
		$db = PearDatabase::getInstance();
		$query = "SELECT visible FROM vtiger_homestuff WHERE userid=? AND stufftype='Tag Cloud'";
		$visibility = $db->query_result($db->pquery($query, array($this->getId())), 0, 'visible');
		if($visibility == 0) {
			return true;
		} 
		return false; 
	}

	/**
	 * Function saves tag cloud
	 */
	function saveTagCloud() {
		$db = PearDatabase::getInstance();
		$db->pquery("UPDATE vtiger_homestuff SET visible = ? WHERE userid=? AND stufftype='Tag Cloud'",
				array($this->get('tagcloud'), $this->getId()));
	}

	/**
	 * Function to get user groups
	 * @param type $userId
	 * @return <array> - groupId's
	 */
	public static function getUserGroups($userId){
        self::getAllUserGroups();
        return self::$allUserGroups[$userId];
	}
    
    /**
	 * Function to get all users groups
	 * @return <array> - all users groupId's
	 */
    static $allUserGroups;
    public static function getAllUserGroups() {
        if (empty(self::$allUserGroups)) {
            $db = PearDatabase::getInstance();
            $query = "SELECT * FROM vtiger_users2group";
            $result = $db->pquery($query, array());
            for ($i = 0; $i < $db->num_rows($result); $i++) {
                $userId = $db->query_result($result, $i, 'userid');
                $userGroups = self::$allUserGroups[$userId];
                $userGroups[] = $db->query_result($result, $i, 'groupid');
                self::$allUserGroups[$userId] = $userGroups;
            }
        }
		return self::$allUserGroups;
    }

	/**
	 * Function returns the users activity reminder in seconds
	 * @return string
	 */
	/**
	 * Function returns the users activity reminder in seconds
	 * @return string
	 */
	function getCurrentUserActivityReminderInSeconds() {
		$activityReminder = $this->reminder_interval;
		$activityReminderInSeconds = '';
		if($activityReminder != 'None') {
			preg_match('/([0-9]+)[\s]([a-zA-Z]+)/', $activityReminder, $matches);
			if($matches) {
				$number = $matches[1];
				$string = $matches[2];
				if($string) {
					switch($string) {
						case 'Minute':
						case 'Minutes': $activityReminderInSeconds = $number * 60;			break;
						case 'Hour'   : $activityReminderInSeconds = $number * 60 * 60;		break;
						case 'Day'    : $activityReminderInSeconds = $number * 60 * 60 * 24;break;
						default : $activityReminderInSeconds = '';
					}
				}
			}
		}
		return $activityReminderInSeconds;
	}	
    
    /**
     * Function to get the users count
     * @param <Boolean> $onlyActive - If true it returns count of only acive users else only inactive users
     * @return <Integer> number of users
     */
    public static function getCount($onlyActive = false) {
        $db = PearDatabase::getInstance();
        $query = 'SELECT 1 FROM vtiger_users ';
        $params = array();
        
        if($onlyActive) {
            $query.= ' WHERE status=? ';
            array_push($params,'active');
        }

        $result = $db->pquery($query,$params);
        
        $numOfUsers = $db->num_rows($result);
        return $numOfUsers;
    }
	
	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&parent=Settings&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true';

	}
	
	/**
	 * Function to get instance of user model by name
	 * @param <String> $userName
	 * @return <Users_Record_Model>
	 */
	public static function getInstanceByName($userName) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT id FROM vtiger_users WHERE user_name = ?', array($userName));

		if ($db->num_rows($result)) {
			return Users_Record_Model::getInstanceById($db->query_result($result, 0, 'id'), 'Users');
		}
		return false;
	}
	
	/**
	 * Function to delete the current Record Model
	 */
	public function delete() {
		$this->getModule()->deleteRecord($this);
	}
	
	public function isAccountOwner() {
		$db = PearDatabase::getInstance();
		$query = 'SELECT is_owner FROM vtiger_users WHERE id = ?';
		$isOwner = $db->query_result($db->pquery($query, array($this->getId())), 0, 'is_owner');
		if($isOwner == 1) {
			return true;
		} 
		return false;
	}
	
	public function getActiveAdminUsers() {
		$db = PearDatabase::getInstance();

		$sql = 'SELECT id FROM vtiger_users WHERE status=? AND is_admin=?';
		$result = $db->pquery($sql, array('ACTIVE', 'on'));

		$noOfUsers = $db->num_rows($result);
		$users = array();
		if($noOfUsers > 0) {
			$focus = new Users();
			for($i=0; $i<$noOfUsers; ++$i) {
				$userId = $db->query_result($result, $i, 'id');
				$focus->id = $userId;
				$focus->retrieve_entity_info($userId, 'Users');

				$userModel = self::getInstanceFromUserObject($focus);
				$users[$userModel->getId()] = $userModel;
			}
		}
		return $users;
	}
	
	public function isFirstTimeLogin($userId) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT 1 FROM vtiger_crmsetup WHERE userid = ? and setup_status = ?';
		$result = $db->pquery($query, array($userId, 1));
		if($db->num_rows($result) == 0){
			return true;
		}
		return false;
    }
	
	/**
	 * Function to get the user hash
	 * @param type $userId
	 * @return boolean
	 */
	public function getUserHash() {
		$db = PearDatabase::getInstance();
		$query = 'SELECT user_hash FROM vtiger_users WHERE id = ?';
		$result = $db->pquery($query, array($this->getId()));
		if($db->num_rows($result) > 0){
			return $db->query_result($result, 0, 'user_hash');
			
		}
	}
        
	/*
	 * Function to delete user permanemtly from CRM and
	 * assign all record which are assigned to that user
	 * and not transfered to other user to other user
	 * 
	 * @param User Ids of user to be deleted and user
	 * to whom records should be assigned
	 */
	public function deleteUserPermanently($userId, $newOwnerId) {
		$db = PearDatabase::getInstance();

		$sql = "UPDATE vtiger_crmentity SET smcreatorid=?,smownerid=?,modifiedtime=? WHERE smcreatorid=? AND setype=?";
		$db->pquery($sql, array($newOwnerId, $newOwnerId, date('Y-m-d H:i:s'), $userId,'ModComments'));

		// Update creator Id in vtiger_crmentity table
		$sql = "UPDATE vtiger_crmentity SET smcreatorid = ? WHERE smcreatorid = ? AND setype <> ?";
		$db->pquery($sql, array($newOwnerId, $userId,'ModComments'));

		//update history details in vtiger_modtracker_basic 
		$sql ="update vtiger_modtracker_basic set whodid=? where whodid=?"; 
		$db->pquery($sql, array($newOwnerId, $userId)); 

		//update comments details in vtiger_modcomments 
		$sql ="update vtiger_modcomments set userid=? where userid=?"; 
		$db->pquery($sql, array($newOwnerId, $userId));

		$sql = "DELETE FROM vtiger_users WHERE id=?";
		$db->pquery($sql, array($userId));
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return getFullNameFromArray($this->getModuleName(),$this->getData());
	}

	/**
	 * Function to return user object from preference file.
	 */
	public static function getInstanceFromPreferenceFile($userId) {
		$focusObj = new Users();
		$focusObj->retrieveCurrentUserInfoFromFile($userId);
		return self::getInstanceFromUserObject($focusObj);
	}
    
    /**
     * Function returns all the subordinates based on Reports To field
     * @return Array
     */
    public function getAllSubordinatesByReportsToField($forUserId) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT id, reports_to_id FROM vtiger_users where status = ?', array('Active'));
        $rows = $db->num_rows($result);
        for($i=0; $i<$rows; $i++) {
            $userId = $db->query_result($result, $i, 'id');
            $reportsToId = $db->query_result($result, $i, 'reports_to_id');
            $users[$userId] = $reportsToId;
        }
        $subUsers = array($forUserId);
        foreach($users as $user => $manager) {
            if(in_array($manager, $subUsers)) {
                $subUsers[] = (int)$user;
            }
        }
        $subUsers = array_diff($subUsers, array($forUserId));
        return $subUsers;
    }
    
     /**
	 * Function returns List of Accessible Users given Group
	 * @param <String> groupid
	 * @return <Array of Users>
	 */
	public function getAccessibleGroupUsers($groupId) {
        vimport('~~/include/utils/GetGroupUsers.php');
        $getGroupUsers = new GetGroupUsers();
        $getGroupUsers->getAllUsersInGroup($groupId);
        return $getGroupUsers->group_users;
	}
    /**
	 * Function returns List of All Group Users
	 * @return <Array of Group and Users>
	 */
    public function getAllAccessibleGroupUsers(){
       $groups = $this->getAccessibleGroups();
        $groupUsers = array();
        foreach ($groups as $groupid => $name) {
            $groupUsers[$groupid] = $this->getAccessibleGroupUsers($groupid);
        }
        return $groupUsers;
    }


	/**
	 * Function to change username 
	 * @param <string> $newUsername
	 * @param <string> $newpassword
	 * @param <string> $oldPassword
	 * @param <integer> $forUserId
	 * @return <array> $reponse
	 */
	public static function changeUsername($newUsername,$newpassword,$oldPassword,$forUserId) {
		$response = array('success'=> false,'message' => 'error');
		$record = self::getInstanceFromPreferenceFile($forUserId);
		$moduleName = $record->getModuleName();
		
		if(!Users_Privileges_Model::isPermittedToChangeUsername($forUserId)) {
			$response['message'] = vtranslate('LBL_PERMISSION_DENIED', $moduleName);
			return $response;
		}

		if($newUsername !== $record->get('user_name')){
			$status = self::isUserExists($newUsername);
			if($status) {
				$response['message'] = vJSTranslate('JS_USER_EXISTS',  $moduleName);
			}else{
				//save new username and password
				$record->set('mode','edit');
				$record->set('user_name',$newUsername);

				try{
					$record->save();
					$users = CRMEntity::getInstance('Users');
					$users->retrieveCurrentUserInfoFromFile($forUserId);
					$changePwdResponse = $users->change_password($oldPassword,$newpassword);
					if($changePwdResponse) {
						$response['success'] = true;
						$response['message'] = vtranslate('LBL_USERNAME_CHANGED',  $moduleName);	
					}else{
						$response['message'] = vtranslate('ERROR_CHANGE_USERNAME',  $moduleName);
					}
				}  catch (Exception $e) {
					$response['success'] = false;
					$response['message'] = vtranslate('ERROR_CHANGE_USERNAME',  $moduleName);
				}		
			}
		}else{
			$response['message'] = vJSTranslate('JS_ENTERED_CURRENT_USERNAME_MSG', $moduleName);
		}
		return $response;
	}

	/**
	 * Function to check whether user exists in CRM and in VAS
	 * @param <string> $userName
	 * @return <boolean> $status
	 */
	public static function isUserExists($userName) {
		$userModuleModel = Users_Module_Model::getCleanInstance('Users');
		$status = false;
		// To check username existence in db
		return $userModuleModel->checkDuplicateUser($userName);
	}

}
