/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Edit_Js("PurchaseOrder_Edit_Js",{},{

    
	// To change the drop down when user select to copy address from reference field
	billingAddress: false, 
	shippingAddress: false,
    
    billingShippingFields: {'bill' :{
										'street':'',
										'pobox':'',
										'city'	:'',
										'state':'',
										'code'	:'',
										'country':''
								},
                            'ship' :{
                                        'street':'',
										'pobox':'',
										'city'	:'',
										'state':'',
										'code'	:'',
										'country':''
                                    }
										
								},
	companyDetails: false,

	addressDetails: {
		'billaccountDetails': false,
		'shipaccountDetails': false,
		'billvendorDetails' : false,
		'shipvendorDetails' : false,
		'billcompanyDetails' : false,
		'shipcompanyDetails' : false,
		'billcontactDetails' : false
	},
    
    /**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField',container);
		}

		if(sourceFieldElement.attr('name') == 'contact_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="vendor_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
        }
        return params;
    },
    
    copyAddressFields: function(addressType, targetType){
        var form = this.getForm();
        var company = this.addressDetails[targetType];
        var container = jQuery('.addressBlock', form);
        
        var fields = this.billingShippingFields[addressType];
        for(var key in fields){
            container.find('[name="'+ addressType +'_' + key+'"]').val(company[key]);
            container.find('[name="'+ addressType +'_' + key+'"]').trigger('change');
        }
	},
    
    registerSwapAddress : function(selectedElement){
		var thisInstance = this;
		if(selectedElement == 'bill' || selectedElement == 'ship'){
			var swapMode;
			if(selectedElement == "ship"){
				swapMode = "false";
			}else if(selectedElement == "bill"){
				swapMode = "true";
			}
			thisInstance.copyAddress(swapMode);
			return false;
		}
	},
    
    registerSelectAddress : function(){
		var self = this;
        var editViewForm = this.getForm();
		jQuery('#ShippingAddress,#BillingAddress', editViewForm).change(function(e){
			var selectedElement = jQuery(e.currentTarget).val();
			var addressType = jQuery(e.currentTarget).data('target');
			if(selectedElement == 'bill' || selectedElement == 'ship'){
				self.registerSwapAddress(selectedElement);
				return ;
			}
			var moduleName = app.getModuleName();
			var mode, moduleIdElement, recordId;
			if(selectedElement == 'vendor' || selectedElement == 'contact'){
				moduleIdElement = selectedElement +'_id';
				mode = 'getAddressDetails';
			} else if(selectedElement == 'account'){
				moduleIdElement = 'accountid';
				mode = 'getAddressDetails';
			} else if(selectedElement == 'company'){
				mode = 'getCompanyDetails';
			}
			
			if(moduleIdElement){
				var recordId = jQuery('input[name='+moduleIdElement+ ']').val();
			}
			if((selectedElement == 'account' || selectedElement == 'vendor' ||  selectedElement == 'contact') && (recordId == '' || recordId == undefined)){
				app.helper.showErrorNotification({'message':app.vtranslate('JS_'+ selectedElement.toUpperCase() +'_NOT_FOUND_MESSAGE')});
                return;
			}
			
			var url = {
                'mode': mode,
                'action': 'CompanyDetails',
                'recordId': recordId,
                'type': addressType,
                'module' : moduleName
            }
			if(!self.addressDetails[addressType + selectedElement +'Details']){
				app.request.get({'data':url}).then(function(error, data){
					if(error == null){
						var response = data;
						self.addressDetails[addressType+ selectedElement +'Details'] = response;
						self.copyAddressFields(addressType, addressType+ selectedElement + 'Details');
					}
				});
			}else{
				 self.copyAddressFields(addressType,  addressType+ selectedElement +'Details');
			}
		});
	},
    
    registerReferenceSelectionEvent : function(container) { 
		this._super(container); 
		var self = this; 
			
		jQuery('input[name="vendor_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){ 
			self.referenceSelectionEventHandler(data, container);
		});

		jQuery('input[name="accountid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){ 
			self.referenceSelectionEventHandler(data, container);
		});
	},
        
        registerLineItemAutoComplete : function(container) {
		var self = this;
		if(typeof container == 'undefined') {
			container = this.lineItemsHolder;
		}
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
                // Added for overlay edit as the module is different
                if(params.search_module == 'Products' || params.search_module == 'Services') {
                    params.module = 'PurchaseOrder';
                }
				self.searchModuleNames(params).then(function(data){
					var reponseDataList = new Array();
					var serverDataFormat = data;
					if(serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label' : app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type'  : 'no results'
						});
					}
					for(var id in serverDataFormat){
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select' : function(event, ui ){
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if(typeof selectedItemData.type != 'undefined' && selectedItemData.type=="no results"){
					return false;
				}
				var element = jQuery(this);
				element.attr('disabled','disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
				var dataUrl = "index.php?module=PurchaseOrder&action=GetTaxes&record="+selectedItemData.id+"&currency_id="+jQuery('#currency_id option:selected').val()+"&sourceModule="+app.getModuleName();
				app.request.get({'url':dataUrl}).then(
					function(error, data){
                        if(error == null) {
                            var itemRow = self.getClosestLineItemRow(element)
                            itemRow.find('.lineItemType').val(selectedModule);
                            self.mapResultsToFields(itemRow, data[0]);
                        }
					},
					function(error,err){

					}
				);
			},
			'change' : function(event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if(element.attr('disabled')== undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
		});
	},
        
        registerBasicEvents: function(container){
            this._super(container);
            this.registerEventForCopyAddress();
            this.registerSelectAddress();
        }
});