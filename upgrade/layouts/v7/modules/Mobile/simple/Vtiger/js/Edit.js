/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

mobileapp.controller('VtigerEditController', function ($scope, $api, $mdToast, $filter, $q) {
    var url = jQuery.url();
    $scope.module = url.param('module');
    $scope.record = url.param('record');
    $scope.describeObject = null;
    $scope.fields = null;
    $scope.createable = null;
    $scope.updateable = null;
    $scope.deleteable = null;
    $scope.fieldsData = null;
    $scope.editdata = [];
	var _processFields = function(field, newrecord, value){
        if(newrecord){
            if (typeof field.default != 'undefined') field.raw = field.default;
            else if (typeof field.type.defaultValue != 'undefined') field.raw = field.type.defaultValue;
        }
        if(!newrecord && value){
            field.raw = value;
        }
        if($scope.module == 'Calendar' && field.name == 'activitytype'){
            field.raw = 'Task';
        }
        switch(field.type.name) {
            case 'date':
                if(value){
                    field.raw = new Date(value);
                }
                else{
                    field.raw = new Date();
                }
                break;
            case 'time':
                if(value){
                    field.raw = new Date(value);
                }
                else{
                    field.raw = new Date();
                }
                break;
            case 'reference':
                if(value){
                    field.raw = value.value;
                    field.valueLabel = value.label;
                }
                break;
            case 'owner':
                if(value){
                    field.raw = value.value;
                    field.display = value.label;
                }
                break;
            case 'boolean':
                if(value){
                    field.raw = value == '1' ? true : false;
                }
                break;
        }
        return field;
    };
    var ignorefields = ['notime','starred','tags','modifiedby','reminder_time','imagename','taxclass','isconvertedfromlead','donotcall'];

    //Function to prepare create data.
    var prepareCreateData = function(newRecord, record){
        var fields = $scope.fields;
        var processedData = {};
        for(var i=0; i < fields.length; i++) {
            var field = fields[i];
            if(ignorefields.indexOf(field.name) !== -1){
                continue;
            }
            if(field.editable) {
                //salutationtype type is not picklist
                if(field.name == 'salutationtype'){
                    field.type.name = 'picklist';
                }
                if(newRecord){
                    //set default value
                    if(field.default){
                        field.raw = field.default;
                    }
                    //set today date as default date.
                    if(!field.default && (field.type.name == 'date' || field.type.name == 'time')){
                        field.raw = new Date();
                    }
                }
                else{
                    field.raw = record.record[field.name];
                }
                //Process the field data
                if(newRecord){
                    field = _processFields(field, true);
                }
                else{
                    field = _processFields(field, false, record.record[field.name]);
                }
                processedData[field.name] = field;
            }

        }
        $scope.fieldsData = processedData;
    };

	$api('describe', {module: $scope.module}, function (e, r) {
       $scope.describeObject = r.describe;
       $scope.fields = $scope.describeObject.fields;
       $scope.createable = $scope.describeObject.createable;
       $scope.updateable = $scope.describeObject.updateable;
       $scope.deleteable = $scope.describeObject.deleteable;
       if($scope.record){
           $scope.loadFields();
       }
       else{
		   if ($scope.userinfo) {
                prepareCreateData(true);
           } else {
               $scope.$root.$on('UserInfo.Changed', function(){
                    prepareCreateData(true);
               });
           }
       }
   });
    
    $scope.gobacktoUrl = function () {
        window.history.back();
    };
    
    $scope.loadFields = function () {
        $api('fetchRecord', {module: $scope.module, record: $scope.record, view_mode:'web'}, function (e, r) {
            if(r){
                prepareCreateData(false, r);
				$scope.record = r.record.id;
            }
        });
    };  
    $scope.editdata = {};
    $scope.processEditData = function(fieldsData) {
        for (var index in fieldsData) {
            var field = fieldsData[index];
            var value = field.raw;
            if(!value) value='';
            switch (field.type.name){
                //Should convert date time to utc.
                case 'date' :
                    value = field.raw;
                    value = moment.utc(value).format('MM-DD-YYYY');
                    break;
                    
                case 'time' :
                    value = field.raw;
                    value = moment.utc(value).format('HH:mm:ss');
                    break;
            }
            if(field.editable){
                $scope.editdata[field.name] = value;
            }
        }
    };
    
    $scope.isValid = function(form){
        if(!form.$valid) {
            return false;
        }
        return true;
    };
    
    $scope.saveThisRecord = function (editForm) {
        if(!$scope.isValid(editForm)) {
            var toast = $mdToast.simple().content('Mandatory Fields Missing').position($scope.getToastPosition()).hideDelay(1000);
            $mdToast.show(toast);
            return;
        }
        $scope.processEditData($scope.fieldsData);
        $api('saveRecord', {module: $scope.module, record: $scope.record, values: $scope.editdata}, function (e, r) {
            if (r) {
                //split the ws id to get actual record id to fetch.
                var id = r.record.id.split('x')[1];
                var toast = $mdToast.simple().content('Record Saved Successfully!').position($scope.getToastPosition()).hideDelay(1000);
                window.location.href = "index.php?module="+$scope.module+"&view=Detail&record="+id+"&app="+$scope.selectedApp;
            } else {
				var message = 'Some thing went wrong ! \n Save is not Succesfull.';
				if (e.message) {
					message = e.message;
				}
                var toast = $mdToast.simple().content(message).position($scope.getToastPosition()).hideDelay(1000);
                $mdToast.show(toast);
                //window.location.href = "index.php?module="+$scope.module+"&view=List&app="+$scope.selectedApp;
            }
        });
    };

    $scope.toastPosition = {
        bottom: true,
        top: false,
        left: false,
        right: true
    };

    $scope.getToastPosition = function () {
        return Object.keys($scope.toastPosition)
                .filter(function (pos) {
                    return $scope.toastPosition[pos];
                }).join('');
    };
    
    //Search reference records
    $scope.getMatchedReferenceFields = function (query, field) {
        var deferred = $q.defer();
        var refModule = field.type.refersTo[0];
        if(query) {
            $api('fetchReferenceRecords', {module: refModule, searchValue: query}, function (error, response) {
                if(response) {
                    var result = [];
                    angular.forEach(response, function (item, key) {
                        item['valueLabel'] = item.label;
                        result.push(item)
                    });
                    return deferred.resolve(result);
                }
            });
        }
        return deferred.promise;
    };
    
    $scope.setReferenceFieldValue = function(item, field){
        if(item){
            field.raw = item.value;
            field.display = item.label;
            field.selectedItem = { 'id' : item.id,  'label' : item.label };
        }
    };
});
