/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Popup_Js("Vtiger_EmailsRelatedModule_Popup_Js",{},{
	
	getListViewEntries: function(e){
		var thisInstance = this;
		var row  = jQuery(e.currentTarget);
		var id = row.data('id');
		var recordName = row.data('name');
		var emailFields = jQuery(row).find('.emailField');
		var emailValue = '';
		jQuery.each(emailFields,function(i,element) {
			emailValue = jQuery(element).data('rawvalue');
			if(emailValue != ''){
				return false;
			}
		});
		if(emailValue == ""){
               app.helper.showErrorNotification({message: recordName+" "+app.vtranslate("JS_DO_NOT_HAVE_AN_EMAIL_ID")});
			return false;
		}
		var response ={};
		response[id] = {'name' : recordName,'email' : emailValue} ;
		thisInstance.done(response, thisInstance.getEventName());
		e.preventDefault();
	},
    
    registerSelectButton : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		var thisInstance = this;
		popupPageContentsContainer.on('click','button.select', function(e){
			var tableEntriesElement = popupPageContentsContainer.find('table');
			var selectedRecordDetails = {};
			var selectedData = thisInstance.readSelectedIds();
			for(var data in selectedData){
				var id = selectedData[data]['id'];
				var name = selectedData[data]['name'];
				var emailFields = selectedData[data]['email'];
                var emailValue = '';
                jQuery.each(emailFields,function(i,element) {
                    emailValue = jQuery(element).data('rawvalue');
                    if(emailValue != ''){
                        return false;
                    }
                });
                if(emailValue == ''){
                    app.helper.showErrorNotification({message: name+" "+app.vtranslate("JS_DO_NOT_HAVE_AN_EMAIL_ID")});
                    return false;
                }
				selectedRecordDetails[id] = {name : name, 'email' : emailValue};
			}
			if(Object.keys(selectedRecordDetails).length <= 0) {
				app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD')});
			}else{
                thisInstance.done(selectedRecordDetails, thisInstance.getEventName());
			}
		});
	},
	
	registerEvents: function(){
		this._super();
	}
})