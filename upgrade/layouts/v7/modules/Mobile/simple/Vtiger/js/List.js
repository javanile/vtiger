/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

mobileapp.controller('VtigerListController', function ($scope, $api, $mdDialog) {
    var url = jQuery.url();

    $scope.module = url.param('module');
    $scope.selectedFilter = null;
    $scope.filters = [];
    $scope.listrecords = [];
    $scope.records = [];
    $scope.page = 1;
    $scope.headers = [];
    $scope.nameFields = [];
    $scope.orderBy = null;
    $scope.sortOrder = ""; //desc
    $scope.moreRecordsExists = false;
    $scope.nextRecords = [];
    $scope.showActions = false;
	$scope.moduleLabel = $scope.module;

    // To fetch Module Filters
    $api('fetchModuleFilters', {module: $scope.module}, function (e, r) {
        $scope.filters = r.filters;
		$scope.moduleLabel = r.moduleLabel;
        $scope.loadRecords();
    });

    // To fetch data from service with the given params
    $scope.loadRecords = function () {
        $scope.pageTitle = $scope.moduleLabel;
        $api('listModuleRecords', {module: $scope.module, filterid: $scope.selectedFilter, page: $scope.page, orderBy: $scope.orderBy, sortOrder: $scope.sortOrder}, function (e, r) {
            $scope.records = r.records;
            $scope.selectedFilter = r.selectedFilter;
            $scope.headers = r.headers;
            $scope.nameFields = r.nameFields;
            $scope.page = parseInt(r.page);
            $scope.orderBy = r.orderBy;
            $scope.sortOrder = r.sortOrder;
            $scope.moreRecordsExists = r.moreRecords;
        });
    };


    $scope.loadMoreRecords = function () {
        $scope.page++;
        $api('listModuleRecords', {module: $scope.module, filterid: $scope.selectedFilter, page: $scope.page, orderBy: $scope.orderBy, sortOrder: $scope.sortOrder}, function (e, r) {
            $scope.selectedFilter = r.selectedFilter;
            $scope.headers = r.headers;
            $scope.nameFields = r.nameFields;
            $scope.orderBy = r.orderBy;
            $scope.sortOrder = r.sortOrder;
            $scope.nextRecords = r.records;
            $scope.moreRecordsExists = r.moreRecords;
            if (r.records) {
                for (var i = 0; i < r.records.length; i++) {
                    $scope.records.push($scope.nextRecords[i]);
                }
            }
        });
    };
    $scope.listViewCreateEvent = function(){
        window.location.href = "index.php?module=" + $scope.module + "&view=Edit&app=" + $scope.selectedApp;
    };

    // Method to Reorder records in Asc / Desc
    $scope.sortRecords = function () {
        $scope.sortOrder = ($scope.sortOrder === 'asc') ? 'desc' : 'asc';
        $scope.loadRecords();
    };

    // 
    $scope.gotoDetailView = function (rid) {
        window.location.href = "index.php?module=" + $scope.module + "&view=Detail&record=" + rid + "&app=" + $scope.selectedApp;
    };

    $scope.hideRecordActions = function () {
       $scope.showActions = false;
    };

    $scope.listViewEditEvent = function(id){
        window.location.href = "index.php?module=" + $scope.module + "&view=Edit&record="+id+"&app=" + $scope.selectedApp;
    };
    
    $scope.showConfirmDelete = function(ev, id) {
        var confirm = $mdDialog.confirm()
              .title('Would you like to delete?')
              .ok('OK')
              .cancel('Cancel')
              .targetEvent(ev);
        $mdDialog.show(confirm).then(function() {
            $api('deleteRecords', {record:id}, function(e,r) {
//                console.log(ev.currentTarget)
            });
        });
    };
    
    // Method to watch Selected Filter and load records
    $scope.$watch('selectedFilter', function (newValue, oldValue) {
        if (newValue !== oldValue && newValue != null) {
            $scope.selectedFilter = newValue;
            $scope.loadRecords();
        }
    });

    // Method to watch the order by field
    $scope.$watch('orderBy', function (newValue, oldValue) {
        if (newValue !== oldValue && newValue != null) {
            $scope.orderBy = newValue;
            $scope.sortOrder = "asc";
            $scope.loadRecords();
        }
    });

    /** Function that gives index inside Namefields array for a given header (field) ****************/
    /** Alternatively it can check the given header field is a Name Field or not (Return -1 if not) */
    $scope.headerIndex = function (arr, text) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] === text)
                return i;
        }
        return -1;
    };
});