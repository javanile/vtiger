<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_AccessControl {

	protected $privileges;
	protected static $PRIVILEGE_ATTRS = array('is_admin', 'current_user_role', 'current_user_parent_role_seq',
		'current_user_profiles', 'profileGlobalPermission', 'profileTabsPermission', 'profileActionPermission',
		'current_user_groups', 'subordinate_roles', 'parent_roles', 'subordinate_roles_users', 'user_info'
	);

	protected function __consturct() {
		$this->privileges = array();
	}

	protected function loadUserPrivilegesWithId($id) {
		if (!isset($this->privileges[$id])) {
			checkFileAccessForInclusion('user_privileges/user_privileges_'.$id.'.php');
			require('user_privileges/user_privileges_'.$id.'.php');

			$privilege = new stdClass;
			foreach (self::$PRIVILEGE_ATTRS as $attr) {
				if (isset($attr))
					$privilege->$attr = $$attr;
			}

			$this->privileges[$id] = $privilege;
		}
		return $this->privileges[$id];
	}

	protected static $singleton = null;
	public static function loadUserPrivileges($id) {
		if (self::$singleton == null) {
			self::$singleton = new self();
		}
		return self::$singleton->loadUserPrivilegesWithId($id);
	}

	public static function clearUserPrivileges($id) {
		if (self::$singleton == null) {
			self::$singleton = new self();
		}

		if (self::$singleton->privileges[$id]) {
			unset(self::$singleton->privileges[$id]);
		}
	}

}
