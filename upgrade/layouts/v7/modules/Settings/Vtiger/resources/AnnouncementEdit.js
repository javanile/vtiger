/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_AnnouncementEdit_Js",{},{
	
	//Contains Announcement container
	container : false,
	
	//return the container of Announcements
	getContainer : function() {
        if(this.container === false){
			this.container = jQuery('#AnnouncementContainer').find('.contents');
		}
        return this.container;
    },
	
    init : function() {
       this.addComponents();
	},
   
	addComponents : function (){
      this.addComponent('Vtiger_Index_Js'); 
	},
    
	/*
	 * Function to save the Announcement content
	 */
	saveAnnouncement : function(textAreaElement) {
		var aDeferred = jQuery.Deferred();
		var content = textAreaElement.val();
		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'AnnouncementSaveAjax',
			'announcement' : content
		};
		app.request.post({"data":params}).then(
			function(error,data) {
                if(error === null){
                    aDeferred.resolve();
                }else {
                    aDeferred.reject();
                }
            });
		return aDeferred.promise();
	},
    
    registerEventForTextAreaFields : function(parentElement) {
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}
        parentElement = jQuery(parentElement);
        
        if(parentElement.is('textarea')){
			var element = parentElement;
		}else{
			var element = jQuery('textarea', parentElement);
		}
		if(element.length === 0){
			return;
		}
		
	},
	/*
	 * Function to register keyUp event for text area to show save button
	 */
	registerKeyUpEvent : function() {
        var thisInstance = this;
		var container = thisInstance.getContainer();
		container.one('keyup', '.announcementContent', function(e) {
			jQuery('.saveAnnouncement', container).removeClass('hide');
		});
	},
	
	registerEvents: function() {
		var thisInstance = this;
		var container = thisInstance.getContainer();
        var textAreaElement = jQuery('.announcementContent', container);
        var saveButton = jQuery('.saveAnnouncement', container);
		
		//register text area fields to autosize
		this.registerEventForTextAreaFields(textAreaElement);
		thisInstance.registerKeyUpEvent();
		
		//Register click event for save button
		saveButton.click(function(e) {
			saveButton.addClass('hide');
			
			//save the new Announcement
			thisInstance.saveAnnouncement(textAreaElement).then(
				function(data) {
                        thisInstance.registerKeyUpEvent();
                        var Message =  app.vtranslate('JS_ANNOUNCEMENT_SAVED')
                       
                    app.helper.showSuccessNotification({'message':Message});
				});
		});
	}

});
