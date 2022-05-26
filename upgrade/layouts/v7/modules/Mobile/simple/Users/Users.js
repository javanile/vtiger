/*************************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *************************************************************************************/
mobileapp.controller('UsersLoginController', function($scope, $api) {

	$scope.auth = {};

	$scope.login = function(){
		$api('login', $scope.auth, function(e, r){
			if (e) {
				// Login failed
			} else {
				window.location.reload();
			}
		});
	};
	
});