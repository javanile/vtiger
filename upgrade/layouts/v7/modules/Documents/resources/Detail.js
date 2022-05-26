/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Documents_Detail_Js", {
   
   //It stores the CheckFileIntegrity response data
	checkFileIntegrityResponseCache : {},
	
	/*
	 * function to trigger CheckFileIntegrity action
	 * @param: CheckFileIntegrity url.
	 */
	checkFileIntegrity : function(checkFileIntegrityUrl) {
		Documents_Detail_Js.getFileIntegrityResponse(checkFileIntegrityUrl).then(
			function(data){
				Documents_Detail_Js.displayCheckFileIntegrityResponse(data);
			}
		);
	},
   
   /*
	 * function to get the CheckFileIntegrity response data
	 */
	getFileIntegrityResponse : function(params){
		var aDeferred = jQuery.Deferred();
		
		//Check in the cache 
		if(!(jQuery.isEmptyObject(Documents_Detail_Js.checkFileIntegrityResponseCache))) {
			aDeferred.resolve(Documents_Detail_Js.checkFileIntegrityResponseCache);
		}
		else{
			app.request.post({"url":params}).then(
				function(err,data) {
					//store it in the cache, so that we dont do multiple request
					Documents_Detail_Js.checkFileIntegrityResponseCache = data;
					aDeferred.resolve(Documents_Detail_Js.checkFileIntegrityResponseCache);
				}
			);
		}
		return aDeferred.promise();
	},
	
	/*
	 * function to display the CheckFileIntegrity message
	 */
	displayCheckFileIntegrityResponse : function(data) {
		var result = data;
		var success = result['success'];
		var message = result['message'];
		if(success) {
                    app.helper.showSuccessNotification({message:message});
		}
                else {
                     app.helper.showErrorNotification({message:message});
		}
	},
   
    triggerSendEmail : function(recordIds) {
		var params = {
			"module" : "Documents",
			"view" : "ComposeEmail",
			"documentIds" : recordIds
		};
		var emailEditInstance = new Emails_MassEdit_Js();
		emailEditInstance.showComposeEmailForm(params);
	}
},{
    
});