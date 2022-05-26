/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Popup_Js("Product_PriceBooks_Popup_Js",{

},{
	/**
	 * Function to register event for enabling list price
	 */
	checkBoxChangeHandler : function(e){
        this._super(e);
        var elem = jQuery(e.currentTarget);
        var parentRow = elem.closest('tr');
        if(elem.is(':checked')) {
            jQuery('input[name=unit_price]',parentRow).removeClass('invisible');
        }else{
            jQuery('input[name=unit_price]',parentRow).addClass('invisible');
        }
	},

	/**
	 * Function to register event for add to pricebook button in the popup
	 */

	registerSelectButton : function(){
		var popupPageContentsContainer = jQuery('#popupPage');
		var thisInstance = this;
		popupPageContentsContainer.on('click','button.select', function(e){
			e.preventDefault();
			var selectedRecords = thisInstance.readSelectedIds();
			if((selectedRecords.length) == 0){
				var message = app.vtranslate("JS_PLEASE_SELECT_ONE_RECORD");
				bootbox.alert(message);
				return;
			}
            popupPageContentsContainer.vtValidate({onsubmit : false});
            if(popupPageContentsContainer.valid()) {
                var selectedRecordDetails = new Array();
                for(var data in selectedRecords){
                    if(typeof selectedRecords[data] == "object"){
                        var id = selectedRecords[data]['id'];
                        var row = popupPageContentsContainer.find('[data-id='+id+']');
                        var rowListPrice = row.find('input[name=unit_price]');
                        var listPrice = rowListPrice.val();
                        selectedRecordDetails.push({'id' : id,'price' : listPrice});
                    }
                }
                thisInstance.done(selectedRecordDetails, thisInstance.getEventName());
            }
		});
	},
	/**
	 * Function to handle select all in the popup
	 */

	selectAllHandler : function(e){
		this._super(e);
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('input.entryCheckBox', tableElement).closest('tr').find('input[name="listPrice"]').removeClass('invisible');
		}else {
			jQuery('input.entryCheckBox', tableElement).closest('tr').find('input[name="listPrice"]').addClass('invisible');
		}
	},

	/**
	 * Function to register event for actions buttons
	 */
	registerEventForActionsButtons : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','a.cancelLink',function(e){
			thisInstance.done();
		})
	},

	/**
	 * Function to get Page Records
	 */
	getPageRecords : function(params){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		this._super(params).then(
			function(data){
				thisInstance.popupSlimScroll();
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to handle sort
	 */
	sortHandler : function(headerElement){
		var thisInstance = this;
		//Listprice column should not be sorted so checking for class noSorting
		if(headerElement.hasClass('noSorting')){
			return;
		}
		this._super(headerElement).then(
			function(data){
				thisInstance.popupSlimScroll();
			},

			function(textStatus, errorThrown){

			}
		);
	},

	/**
	 * Function to handle slim scroll for popup
	 */
	popupSlimScroll : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		var element = popupPageContentsContainer.find('.popupEntriesDiv');
		app.helper.showVerticalScroll(element, {setHeight: 400});
	},

     /**
     * Function which will register event when user clicks on the row
     */
    registerEventForListViewEntryClick : function() {
        //To Make sure we will not close the window once he clicks on the row,
        //which is default behaviour in normal popup
        return true;
    },
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this._super();
		this.registerEventForActionsButtons();
		this.popupSlimScroll();
	}
});