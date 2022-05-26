mobileapp.controller('VtigerDetailController', function ($scope, $api) {
    var url = jQuery.url();
    $scope.module = url.param('module');
    $scope.record = url.param('record');
    $scope.describeObject = null;
    $scope.fields = null;
    $scope.createable = null;
    $scope.updateable = null;
    $scope.deleteable = null;
    $scope.recordData = null;
    
    $api('describe', {module:$scope.module}, function(e, r) {
        $scope.describeObject = r.describe;
        $scope.fields = $scope.describeObject.fields;
        $scope.createable = $scope.describeObject.createable;
        $scope.updateable = $scope.describeObject.updateable;
        $scope.deleteable = $scope.describeObject.deleteable;
        $scope.loadRecord();
    });
    
    $scope.gobacktoUrl = function(){
        //window.history.back();
        // Binding DetailView back action to List view. (as Edit + Save puts back in wrong state)
        window.location.href = (window.location.href.replace(/view=Detail/, "view=List"));
    };
    
    var _VTIGER_RESTRICTIONS = {
	'Vtiger' : {
		'View': {
			'Detail': {
				'Fields': {
					'Ignore_Fields': [
						'modifiedby',
						'last_contacted_via',
						'last_contacted_on',
						'reassign_count',
						'from_portal',
						'prev_sales_stage',
						'txtAdjustment',
						'hdnGrandTotal',
						'hdnTaxType',
						'hdnSubTotal',
						'currency_id',
						'conversion_rate',
						'pre_tax_total',
						'received',
						'balance',
						'hdnS_H_Amount',
						'paid',
						'tags',
						'shipping_&_handling',
						'shipping_&_handling_shtax1',
						'shipping_&_handling_shtax2',
						'shipping_&_handling_shtax3',
						'starred',
						'hdnS_H_Percent',
						'tax1',
						'tax2',
						'tax3',
                                                
					]
				}
			}
		}
	}
    };
    $scope.lineitems = [];
    $scope.lineItemsSummary = {};
    
    $scope.prepareLineItems = function(response){
         $scope.lineitems = response.record['LineItems'];
         var processedLineItems = [];
         for(var index in $scope.lineitems) {
             var item = $scope.lineitems[index];
             processedLineItems.push(item);
         }

         var lineItemFinalDetails = response.record['LineItems_FinalDetails'][1]['final_details'];
         for(var index in response.record['LineItems_FinalDetails']) {
             var final_detail = response.record['LineItems_FinalDetails'][index];
             processedLineItems[index - 1]['netPrice'] = final_detail["netPrice"+index];
         }
         $scope.lineitems = processedLineItems;
         $scope.lineItemsSummary['pre_tax_total'] = response.record.pre_tax_total;
         $scope.lineItemsSummary['sub_total'] = response.record.hdnSubTotal;
         $scope.lineItemsSummary['grand_total'] = response.record.hdnGrandTotal;
         $scope.lineItemsSummary['group_discount'] = response.record.hdnDiscountAmount;
         $scope.lineItemsSummary['total_tax'] = lineItemFinalDetails['tax_totalamount'];
         $scope.lineItemsSummary['totalAfterDiscount'] = lineItemFinalDetails['totalAfterDiscount'];
         $scope.lineItemsSummary['adjustment'] = lineItemFinalDetails['adjustment'];
    };
    
    $scope.loadRecord = function () {
        $api('fetchRecord', {module:$scope.module, record:$scope.record, view_mode:'web'}, function(e,r) {
            $scope.record_label = r.record.label;
            $scope.recordId = r.record.id;
            if($scope.module == 'Invoice' || $scope.module == 'Quotes' || $scope.module == 'PurchaceOrder' || $scope.module == 'SalesOrder'){
                $scope.prepareLineItems(r);
            }
            var processedData = [];
            var ignoreFields  = _VTIGER_RESTRICTIONS['Vtiger']['View']['Detail']['Fields']['Ignore_Fields'];
            for(var index in $scope.fields) {
                if(ignoreFields.indexOf($scope.fields[index].name) === -1) {
                    var value = r.record[$scope.fields[index].name];
                    if(typeof value === 'object') {
                        processedData.push({label:$scope.fields[index].label, value:value.label, type:$scope.fields[index].type.name});
                    
                    } else {
                        processedData.push({label:$scope.fields[index].label, value:value, type:$scope.fields[index].type.name});
                    }
                }
            }
            $scope.pageTitle = r.record.label;
            $scope.recordData = processedData;
        });
        //related tab
        
        $api('fetchRecord', {mode:'getRelatedRecordCount', module:$scope.module, record:$scope.record}, function(er, re) {
            if(re){
                $scope.relatedModules = re;
            }
        });
    };
    
    $scope.detailViewEditEvent = function(id){
        window.location.href = "index.php?module=" + $scope.module + "&view=Edit&record="+$scope.record+"&app=" + $scope.selectedApp;
    };
    
    $scope.isUpdateable = function() {
        return ($scope.updateable)? true : false;
    };
    
    $scope.isDeleteable = function() {
        return ($scope.deleteable)? true : false;
    };
    $scope.showRelatedList = function(module){
        window.location.href = "index.php?module="+module+"&view=List&app="+$scope.selectedApp;
    };
});


/** WIP inline EDIT Controller */
mobileapp.controller('InlineEditorController', function($scope){

	// $scope is a special object that makes
	// its properties available to the view as
	// variables. Here we set some default values:

	$scope.showtooltip = false;
	$scope.value = 'Edit me.';

	// Some helper functions that will be
	// available in the angular declarations

	$scope.hideTooltip = function(){

		// When a model is changed, the view will be automatically
		// updated by by AngularJS. In this case it will hide the tooltip.

		$scope.showtooltip = false;
	};

	$scope.toggleTooltip = function(e){
		e.stopPropagation();
		$scope.showtooltip = !$scope.showtooltip;
	};
});
