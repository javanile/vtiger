/*************************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 **************************************************************************************/
window.mobileapp = angular.module('mobileapp', ['ngMaterial', 'ngTouch', 'ngAnimate','ngMaterialDatePicker']);
mobileapp.factory('$api', function ($http, $mdDialog) {
    var APIBASE = 'api.php', APIVERSION = 'v2';
    this.progressDialog = null;
    
    return function (operation, params, next) {
        if (typeof params == 'function') {
            next = params;
            params = {};
        }
        if (typeof params == 'undefined')
            params = {};

        params._operation = operation;

        var options = {};
        options.method = 'POST';
        options.url = APIBASE;
        options.data = params;
        options.headers = {'X-API-VERSION': APIVERSION};
        if(!this.progressDialog){
            var parentEl = angular.element(document.body);
            var alert = $mdDialog.alert({
               parent: parentEl,
               fullscreen: false,
               clickOutsideToClose: false,
               template: '<md-dialog aria-label="Loading Bar">'+
                           '<md-dialog-content>'+
                           '<md-progress-linear md-mode="indeterminate"></md-progress-linear>'+
                               '<div layout="row" style="margin: 20px;">'+
                                   '<span style="margin:15px 10px; opacity: 0.5;"><i class="mdi mdi-clock"></i> &nbsp; in progress...</span>'+
                               '</div>'+
                           '</md-dialog-content>'+
                       '</md-dialog>'
           });
           this.progressDialog = $mdDialog.show(alert);
        }
        $http(options).success(function (data, status, headers, config) {
            $mdDialog.hide();
            if (next) {
                next(!data.success ? new Error(data.error.message) : null,
                        data.success ? data.result : null);
            }
        });
    };
});
