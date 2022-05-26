/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js('Settings_Tags_List_Js',{
    triggerDelete : function(url) {
        var instance = app.controller();
        instance.deleteTag(url);
    },
    
    triggerEdit : function(event) {
        var editIconElement = jQuery(event.target);
        var instance = app.controller();
        instance.editTag(editIconElement);
    },
	
	triggerAdd : function(event) {
        var instance = app.controller();
        instance.registerTagAddEvent();
    },
	
},{
    editTagContainer : null, 
    
    getEditTagContainer : function() {
        if(this.editTagContainer == null) {
            this.editTagContainer = jQuery('#editTagContainer');
        }
        return this.editTagContainer;
    },
    
    /**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var params = this.getDefaultParams();
		params['action'] = "ListAjax";
		params['mode'] = "getPageCount";

		return params;
	},
    
    
    deleteTag : function(url) {
        var self = this;
        app.helper.showConfirmationBox({'message' : app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE')}).then(function(){
            app.request.post({'url' : url}).then(function(error, data){
                if(data){
                    self.loadListViewRecords();
                }   
            });
        })
    },
    
    updateTag : function(callerParams) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module' : 'Vtiger',
            'action' : 'TagCloud',
            'mode'   : 'update'
        }
        params = jQuery.extend(params, callerParams);
        app.request.post({'data' : params}).then(function(error, data){
            if(error == null) {
                aDeferred.resolve(data);
            }else{
                aDeferred.reject(error);
            }
        });
        return aDeferred.promise();
    },
    
    editTag : function(rowEditIconElement) {
        var thisInstance=this;
        var row = rowEditIconElement.closest('tr');
        var tagInfo = row.data('info');
        var editTagContainer = this.getEditTagContainer();
        editTagContainer.find('[name="id"]').val(tagInfo.id);

        editTagContainer.find('[name="tagName"]').val(tagInfo.tag);
        if(tagInfo.visibility == "public") {
            editTagContainer.find('[type="checkbox"]').prop('checked',true);
        }else{
            editTagContainer.find('[type="checkbox"]').prop('checked', false);
        }
        editTagContainer.removeClass('hide');
        if(editTagContainer) {
            app.helper.showModal(editTagContainer);
            thisInstance.registerEditTagSaveEvent();
        }
    }, 
    
    registerEditTagSaveEvent : function() {
        var editTagContainer = this.getEditTagContainer();
        var self = this;
        this.getEditTagContainer().find('.saveTag').on('click', function(e){
            
            var tagName = editTagContainer.find('[name="tagName"]').val();
            if(tagName.trim() == ""){
                var message = app.vtranslate('JS_PLEASE_ENTER_VALID_TAG_NAME');
                app.helper.showErrorNotification({'message':message});
    			return;
            }
            
            var valueParams = {};
            valueParams['name'] = editTagContainer.find('[name="tagName"]').val();
            var visibility = 'private';
            if(editTagContainer.find('[name="visibility"][type="checkbox"]').is(':checked')) {
                visibility = editTagContainer.find('[name="visibility"][type="checkbox"]').val();
            }
            valueParams.visibility = visibility;
            var tagId = editTagContainer.find('[name="id"]').val();
            valueParams.id = tagId;
            self.updateTag(valueParams).then(function(data){
                self.loadListViewRecords();
                app.helper.hideModal();
            }, function(error){
                app.helper.showAlertBox({'message' : error.message});
                app.helper.hideModal();
            });
        });
        
        editTagContainer.on('click', '.cancelSaveTag', function(e){
            app.helper.hideModal();
        });
    },
	registerTagAddEvent : function() {
		var thisInstance = this;
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'EditAjax';
		app.request.post({"data":params}).then(
            function(err,data) {
                if(err === null) {
						app.helper.showModal(data);
						var form = jQuery('#addTagSettings');

						form.submit(function(e) {
							e.preventDefault();
						});
						var params = {
							submitHandler : function(form) {
								var form = jQuery(form);
								thisInstance.saveTagDetails(form);
							}
						};
						form.vtValidate(params);
                    }
                }
		);
	},
	
	/**
	 * This function will save the new tag details
	 */
	saveTagDetails : function(form) {
		var thisInstance = this;
		var formData = form.serializeFormData();
		
		var saveParams = {
            'module' : 'Vtiger',
            'action' : 'TagCloud',
            'mode'   : 'saveTags',
			'addedFrom' : 'Settings'
        }
		
		var saveTagList = {};
		saveTagList['new'] = formData['createNewTag'].split(',');
		saveParams['tagsList'] = saveTagList;
        saveParams['newTagType'] = formData['visibility'];  
		
		app.request.post({"data":saveParams}).then(
			function(err,data) {
				if(err === null) {
                    app.helper.hideModal();
					
                    var successfullSaveMessage = app.vtranslate('JS_TAG_SAVED_SUCCESSFULLY');
                    app.helper.showSuccessNotification({'message':successfullSaveMessage});
					thisInstance.loadListViewRecords();
				}else {
					app.helper.showErrorNotification({'message' : err.message});
				}
			}
		);
	},
    
    registerEvents : function() {
        var self = this;
        this._super();
        app.event.on('post.listViewFilter.click', function(e){
            //clearing cached dom element. Since it will be replaced with ajax request
            self.editTagContainer = null;
            self.registerEditTagSaveEvent();
        })
    }
});