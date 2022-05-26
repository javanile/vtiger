/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger_Popup_Js("Products_ProductRelatedProductBundles_Js", {}, {
    popupSelectedRecords : {},
    /**
     * Function to register event for enabling list price
     */
    registerEventForCheckboxChange: function() {
        var thisInstance = this;
        var popupPageContentsContainer = this.getPopupPageContainer();
        popupPageContentsContainer.on('click', 'input.entryCheckBox', function(e) {
            var elem = jQuery(e.currentTarget);
            var parentRow = elem.closest('tr');
            var id = parentRow.data("id");

            if (elem.is(':checked')) {
                jQuery('.qtyForDisplay', parentRow).addClass('hide').removeClass('show');
                jQuery('.qtyForEdit', parentRow).addClass('show').removeClass('hide');
                jQuery('.quantityTextBox').focus();
            }
            else {
                jQuery('.qtyForEdit', parentRow).addClass('hide').removeClass('show');
                jQuery('.qtyForDisplay', parentRow).addClass('show').removeClass('hide');
                delete thisInstance.popupSelectedRecords[id];
            }

        });

    },
    registerSelectButton: function() {
        var popupPageContentsContainer = jQuery('#popupPage');
        var self = this;
        popupPageContentsContainer.on('click','button.addProducts', function(e){
            popupPageContentsContainer.vtValidate({
                ignore: '.listSearchContributor,.qtyForEdit.hide input',
                submitHandler: function(form) {
                    var tableEntriesElement = popupPageContentsContainer.find('table.listViewEntriesTable');
                    var selectedRecords = jQuery('input.entryCheckBox', tableEntriesElement).filter(':checked');
                    
                    var selectedRecordDetails = {};
                    selectedRecords.each(function(index, checkBoxElement) {
                        var checkBoxJqueryObject = jQuery(checkBoxElement); 
                        var row = checkBoxJqueryObject.closest('tr');
                        var id = row.data('id');

                        var rowQuantity = row.find('.quantityTextBox');
                        selectedRecordDetails[id] = rowQuantity.val();
                    });
                    selectedRecordDetails = jQuery.extend(selectedRecordDetails,self.popupSelectedRecords);
                    if (Object.keys(selectedRecordDetails).length === 0) {
                        var message = app.vtranslate("JS_PLEASE_SELECT_ONE_RECORD");
                        app.helper.showErrorNotification({message: message});
                        return;
                    }
                    self.done(selectedRecordDetails, self.getEventName());
                }
            });
        });
    },
    selectAllHandler: function(e) {
        this._super(e);
        var currentElement = jQuery(e.currentTarget);
        var isMainCheckBoxChecked = currentElement.is(':checked');
        var tableElement = currentElement.closest('table');
        if (isMainCheckBoxChecked) {
            jQuery('input.entryCheckBox', tableElement).closest('tr').find('.qtyForDisplay').addClass('hide').removeClass('show');
            jQuery('input.entryCheckBox', tableElement).closest('tr').find('.qtyForEdit').addClass('show').removeClass('hide');
        } else {
            jQuery('input.entryCheckBox', tableElement).closest('tr').find('.qtyForEdit').addClass('hide').removeClass('show');
            jQuery('input.entryCheckBox', tableElement).closest('tr').find('.qtyForDisplay').addClass('show').removeClass('hide');
        }
    },
    registerEventForActionsButtons: function() {
        var thisInstance = this;
        var popupPageContentsContainer = this.getPopupPageContainer();
        popupPageContentsContainer.on('click', 'a.cancelLink', function(e) {
            thisInstance.done();
        });
    },
    registerEventForListViewEntryClick: function() {
        var popupPageContentsContainer = this.getPopupPageContainer();
        popupPageContentsContainer.on('click', '.listViewEntries', function(e) {
            return;
        });
    },
    
    /**
	 * Function to get complete params
	 */
	getCompleteParams : function(){
		var params = this._super();
        var selectedRecords = this.popupSelectedRecords;
        params["selectedRecords"] = selectedRecords;
        return params;
	},
    
    /**
	 * Function to handle next page navigation
	 */
	nextPageHandler : function(){
       app.event.trigger("pre.popupNavigationButton.click");
        var aDeferred = jQuery.Deferred();
        this._super().then(function(data){
            aDeferred.resolve(data);
        });
        return aDeferred.promise();
	},
    
     /**
	 * Function to handle Previous page navigation
	 */
	previousPageHandler : function(){
        app.event.trigger("pre.popupNavigationButton.click");
        var aDeferred = jQuery.Deferred();
        this._super().then(function(data){
            aDeferred.resolve(data);
        });
        return aDeferred.promise();
	},
    
    registerEvents: function() {
        var thisInstance = this;
        this._super();
        this.registerEventForActionsButtons();
        
        app.event.on("pre.popupNavigationButton.click",function(event){
            var popupPageContentsContainer = jQuery('#popupPage');
            var tableEntriesElement = popupPageContentsContainer.find('table.listViewEntriesTable');
            var selectedRecords = jQuery('input.entryCheckBox', tableEntriesElement).filter(':checked');
            if ((selectedRecords.length) > 0) {
                selectedRecords.each(function(index, checkBoxElement) {
                    var checkBoxJqueryObject = jQuery(checkBoxElement);
                    var row = checkBoxJqueryObject.closest('tr');
                    var id = row.data('id');

                    var rowQuantityVal = row.find('.quantityTextBox').val();
                    if(rowQuantityVal > 0){
                        thisInstance.popupSelectedRecords[id] = rowQuantityVal;
                    }
                });
            }
        })
    }

});
