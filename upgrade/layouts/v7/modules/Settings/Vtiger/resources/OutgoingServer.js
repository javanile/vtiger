/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_OutgoingServer_Js",{},{
	
    init : function() {
       this.addComponents();
    },
   
    addComponents : function (){
        this.addModuleSpecificComponent('Index',app.module(), app.getParentModuleName());
    },

	/*
	 * function to Save the Outgoing Server Details
	 */
	saveOutgoingDetails : function(form) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var data = form.serializeFormData();
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action': 'OutgoingServerSaveAjax'
			};
           
       jQuery.extend(params,data);
		app.request.post({'data' : params}).then(
			function(err, data) {
                app.helper.showProgress();
				if(err === null){
                    var OutgoingServerDetailUrl = form.data('detailUrl');
                    	thisInstance.loadContents(OutgoingServerDetailUrl).then(
						function(data) {
						jQuery('.settingsPageDiv').html(data);
						thisInstance.registerDetailViewEvents();
                            app.helper.hideProgress();
						}
					);
				aDeferred.resolve(data);
                }else {
                app.helper.hideProgress();
					jQuery('.errorMessage', form).removeClass('hide');
                    aDeferred.reject();
				}
			}
		);
        return aDeferred.promise();
	},
	
	/*
	 * function to load the contents from the url through pjax
	 */
    registerFilters: function(){
        var filters = jQuery('#outgoingServer');
        filters.find('.cursorPointer').on('click',function(e){
            var currentTarget = jQuery(e.currentTarget);
            if(currentTarget.attr('data-toggletext')=== 'Show more') {
                currentTarget.attr('data-toggletext','Show less');
                currentTarget.html('Show less');
            }else{
                currentTarget.attr('data-toggletext','Show more');
                currentTarget.html('Show more');
            }
        });
    },
    
    
	loadContents : function(url) {
		var aDeferred = jQuery.Deferred();
		app.request.pjax({"url" : url}).then(
			function(err, data){
                if(err === null){
				jQuery('.settingsPageDiv ').html(data);
				aDeferred.resolve(data);
            }
			},
			function(error, err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	
	/*
	 * function to register the events in editView
	 */
	registerEditViewEvents : function(e) {
		var thisInstance = this;
		var form = jQuery('#OutgoingServerForm');
		var resetButton = jQuery('.resetButton', form);
		var cancelLink = jQuery('.cancelLink', form);
        
		//register validation engine
		var params = {
            submitHandler : function(form) {
                  app.helper.showProgress();
                var form = jQuery(form);
				thisInstance.saveOutgoingDetails(form);
            }
		};
		if (form.length) {
        	form.vtValidate(params);
		 	form.on('submit', function(e){
            	e.preventDefault();
            	return false;
        	});
		}
		
		//register click event for resetToDefault Button
		resetButton.click(function(e) {
			jQuery('[name="default"]', form).val('true');
			var message = app.vtranslate('JS_CONFIRM_DEFAULT_SETTINGS');
			app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
                   app.helper.showProgress();
					thisInstance.saveOutgoingDetails(form);
				}
			);
		});
		
		//register click event for cancelLink
		cancelLink.click(function(e) {
			var OutgoingServerDetailUrl = form.data('detailUrl');
			thisInstance.loadContents(OutgoingServerDetailUrl).then(
				function(data) {
                     jQuery('.editViewPageDiv').html(data);
					//after loading contents, register the events
					thisInstance.registerDetailViewEvents();
				}
			);
		});
	},
	
	/*
	 * function to register the events in DetailView
	 */
	registerDetailViewEvents : function() {
		var thisInstance = this;
		//Detail view container
		var container = jQuery('#OutgoingServerDetails');
		var editButton = jQuery('.editButton', container);
		//register click event for edit button
		editButton.click(function(e) {
			app.helper.showProgress();

			var url = editButton.data('url');
			thisInstance.loadContents(url).then(
				function(data) {
                    jQuery('.settingsPageDiv ').html(data);
                    app.helper.hideProgress();
					//after load the contents register the edit view events
					thisInstance.registerEditViewEvents();
                    thisInstance.registerOnChangeEventOfserverType();
                    vtUtils.showSelect2ElementView(jQuery('select[name="serverType"]'));
                    
				}
			);
		});
	},
	
    registerOnChangeEventOfserverType:function(e){
		var form = jQuery('#OutgoingServerForm');
            form.find('[name="serverType"]').on('change',function(e){
                
                var servertypevalue=form.find('[name="serverType"]').val();
				form.find('[name="server"]').val(servertypevalue);
                
                if(servertypevalue === "mail.od1.vtiger.com"){
                   var vtigerUser= form.find('[name="vtigerServer"]').data("username");
                   var vtigerpassword= form.find('[name="vtigerServer"]').data("password");
                   var vtigeremail= form.find('[name="vtigerServer"]').data("email");
                    form.find('[name="server_username"]').val(vtigerUser);
                    form.find('[name="server_password"]').val(vtigerpassword);
                    form.find('[name="from_email_field"]').val(vtigeremail);
                }else{
					form.find('[name="server_username"]').val("");
					form.find('[name="server_password"]').val("");
					form.find('[name="from_email_field"]').val("");
				}
			});
	},
    
	registerEvents: function() {
		var thisInstance = this;
		thisInstance.registerEditViewEvents();
		thisInstance.registerOnChangeEventOfserverType();
		thisInstance.registerDetailViewEvents();
		
        this.registerFilters();
	}

});


Settings_Vtiger_OutgoingServer_Js("Settings_Vtiger_OutgoingServerEdit_Js",{},{});

Settings_Vtiger_OutgoingServer_Js("Settings_Vtiger_OutgoingServerDetail_Js",{},{});
