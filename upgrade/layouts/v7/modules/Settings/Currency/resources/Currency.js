/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class('Settings_Currency_Js', {
	
	//holds the currency instance
	currencyInstance : false,
	
	/**
	 * This function used to triggerAdd Currency
	 */
	triggerAdd : function(event) {
		event.stopPropagation();
		var instance = Settings_Currency_Js.currencyInstance;
		instance.showEditView();
	},
	
	/**
	 * This function used to trigger Edit Currency
	 */
	triggerEdit : function(event, id) {
		event.stopPropagation();
		var instance = Settings_Currency_Js.currencyInstance;
        instance.showEditView(id);
	},
	
	/**
	 * This function used to trigger Delete Currency
	 */
	triggerDelete : function(event, id) {
		event.stopPropagation();
         
		var currentTarget = jQuery(event.currentTarget);
		var currentTrEle = currentTarget.closest('tr'); 
		var instance = Settings_Currency_Js.currencyInstance;
		instance.transformEdit(id).then(
			function(data) {
                app.helper.showModal(data);

                var form = jQuery('#transformCurrency');

                form.on('submit', function(e){
                    e.preventDefault();
                    var transferCurrencyEle = form.find('select[name="transform_to_id"]');
                    instance.deleteCurrency(id, transferCurrencyEle, currentTrEle);
                });

        });     
	}
	
}, {
	
	//constructor
	init : function() {
		Settings_Currency_Js.currencyInstance = this;
	},
	
	/*
	 * function to show editView for Add/Edit Currency
	 * @params: id - currencyId
	 */
	showEditView : function(id) {
      
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'EditAjax';
		params['record'] = id;
		app.request.post({"data":params}).then(
            function(err,data) {
                if(err === null) {
                    app.helper.showModal(data);
                    var form = jQuery('#editCurrency');
                    var record = form.find('[name="record"]').val();
                        
                    var currencyStatus = form.find('[name="currency_status"]').is(':checked');
                    if(record != '' && currencyStatus) {
                        //While editing currency, register the status change event
                        thisInstance.registerCurrencyStatusChangeEvent(form);
                    }
                    //If we change the currency name, change the code and symbol for that currency
                    thisInstance.registerCurrencyNameChangeEvent(form);

                        form.submit(function(e) {
                            e.preventDefault();
                        });

                        var params = {
								submitHandler : function(form) {
									var form = jQuery(form);
									thisInstance.saveCurrencyDetails(form);
                                }
                            };

                        form.vtValidate(params);
                    }else {
                        aDeferred.reject(err);
                    }
                }
		);
		return aDeferred.promise();
	},
	
	/**
	 * Register Change event for currency status
	 */
	registerCurrencyStatusChangeEvent : function(form) {
		/*If the status changed to Inactive while editing currency, 
		currency should transfer to other existing currencies */
		form.find('[name="currency_status"]').on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			if(currentTarget.is(':checked')) {
				form.find('div.transferCurrency').addClass('hide');
			} else {
				form.find('div.transferCurrency').removeClass('hide');
			}
		});
	},
	
	/**
	 * Register Change event for currency Name
	 */
	registerCurrencyNameChangeEvent : function(form) {
		var currencyNameEle = form.find('select[name="currency_name"]');
        //on change of currencyName, update the currency code & symbol
		currencyNameEle.on('change', function(e) {
			var selectedCurrencyOption = currencyNameEle.find('option:selected');
			form.find('[name="currency_code"]').val(selectedCurrencyOption.data('code'));
			form.find('[name="currency_symbol"]').val(selectedCurrencyOption.data('symbol'));
		});
	},
	
	/**
	 * This function will save the currency details
	 */
	saveCurrencyDetails : function(form) {
		var thisInstance = this;
		var data = form.serializeFormData();
		data['module'] = app.getModuleName();
		data['parent'] = app.getParentModuleName();
		data['action'] = 'SaveAjax';
		
		app.request.post({"data":data}).then(
			function(err,data) {
				if(err === null) {
                    app.helper.hideModal();
                    var successfullSaveMessage = app.vtranslate('JS_CURRENCY_DETAILS_SAVED');
                    app.helper.showSuccessNotification({'message':successfullSaveMessage});
					thisInstance.loadListViewContents();
				}else {
					app.helper.showErrorNotification({'message' : err.message});
				}
			}
		);
	},
	
	/**
	 * This function will load the listView contents after Add/Edit currency
	 */
	loadListViewContents : function() {
		var thisInstance = this;
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'List';
		
		app.request.post({"data":params}).then(
			function(err,data) {
                if(err === null) {
                    //replace the new list view contents
                    jQuery('#listViewContent').html(data);
                    thisInstance.registerRowClick();
                }
			}
		);
	},
	
	/**
	 * This function will show the Transform Currency view while delete the currency
	 */
	transformEdit : function(id) {
		var aDeferred = jQuery.Deferred();
		
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'TransformEditAjax';
		params['record'] = id;
		
		app.request.post({"data":params}).then(
			function(err,data) {
                if(err === null) {
                    aDeferred.resolve(data);
                }else {
                    aDeferred.reject();
                }
			});
		return aDeferred.promise();
	},
	
	/**
	 * This function will delete the currency and save the transferCurrency details
	 */
	deleteCurrency : function(id, transferCurrencyEle, currentTrEle) {
		var transferCurrencyId = transferCurrencyEle.find('option:selected').val();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'DeleteAjax';
		params['record'] = id;
		params['transform_to_id'] = transferCurrencyId;

		app.request.post({"data":params}).then(
			function(err,data) {
                if(err === null){
                    app.helper.hideModal();
                    var successfullSaveMessage = app.vtranslate('JS_CURRENCY_DELETED_SUEESSFULLY');
                    app.helper.showSuccessNotification({'message':successfullSaveMessage});
                    currentTrEle.fadeOut('slow').remove();
                }else {
					app.helper.showErrorNotification({'message' : err.message});
				}
		});
	},
	
    registerRowClick : function() {
		var thisInstance = this;
		jQuery('.listViewEntries').on('click',function(e) {
			var currentRow = jQuery(e.currentTarget);
			if(currentRow.find('.fa-pencil').length <= 0) {
				return;
			} 
			thisInstance.showEditView(currentRow.data('id'));
		})  
    },
	
    registerEvents : function() {
        this.registerRowClick();
    }
	
});