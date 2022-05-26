/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_EmailPreview_Js",{},{
	
	/**
	 * Function to get email actions params
	 */
	getEmailActionsParams : function(mode){
		var parentRecord = new Array();
		var parentRecordId = jQuery('[name="parentRecord"]').val();
		parentRecord.push(parentRecordId);
		var recordId = jQuery('[name="recordId"]').val();
		var params = {};
		params['module'] = "Emails";
		params['view'] = "ComposeEmail";
		if(mode != "emailForward"){
			params['selected_ids'] = parentRecord;
		}
		params['record'] = recordId;
		params['mode'] = mode;
		params['parentId'] = parentRecordId;
		params['relatedLoad'] = true;
		
		return params;
	},
	
	/**
	 * Function to register events for action buttons of email preview
	 */
	registerEventsForActionButtons : function(){
		var thisInstance = this;
		app.helper.showVerticalScroll(jQuery('#toAddressesDropdown'));
        jQuery('[name="previewReplyAll"], [name="previewReply"], [name="previewForward"], [name="previewEdit"]').on('click',function(e){
            var module = "Emails";
			app.helper.checkServerConfig(module).then(function(data){
				if(data === true){
					var mode = jQuery(e.currentTarget).data('mode');
					var params = thisInstance.getEmailActionsParams(mode);
					var container = jQuery(e.currentTarget).closest('.modal');
					container.one('hidden.bs.modal',function()
					{	
						app.helper.hidePopup();
						app.helper.showProgress();
						app.request.post({data:params}).then(function(err,data){
							app.helper.hideProgress();
							if(err === null){
								app.helper.showModal(data);
								var emailEditInstance = new Emails_MassEdit_Js();
								emailEditInstance.registerEvents();
							}
						});
						
					});
					container.modal('hide');
					
				} else {
					app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
				}
			})
        });
        jQuery('[name="previewPrint"]').on('click',function(e){
            var module = "Emails";
            app.helper.hideModal();
            var mode = jQuery(e.currentTarget).data('mode');
            var params = thisInstance.getEmailActionsParams(mode);
            var urlString = (typeof params == 'string') ? params : jQuery.param(params);
            var url = 'index.php?'+urlString;
            window.open(url,'_blank');
        });
	},
	
	registerEvents : function(){
        var thisInstance = this;
        app.event.on('post.EmailPreview.load',function(event,args){
            thisInstance.registerEventsForActionButtons();
        });
	}
})