/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var Settings_Roles_Js = {
	
	newPriviliges : false,
	
	initDeleteView: function() {
		var form = jQuery('#roleDeleteForm');
		
		var params = {
			submitHandler : function(form) {
				var form = jQuery(form);
				var transferRecordNameEle = jQuery('[name="transfer_record_display"]', form);
				var transferRecordEle = jQuery('[name="transfer_record"]', form);
				var transferRecordName = transferRecordNameEle.val();
				var transferRecordRoleId = transferRecordEle.val();
				
				if(transferRecordName === '' || transferRecordRoleId === '') {
					transferRecordNameEle.addClass('input-error');
					return false;
				}else {
					transferRecordNameEle.removeClass('input-error');
				}
				
				app.helper.showProgress();
				var formData = form.serializeFormData();
				
				app.request.post({'data' : formData}).then(function(err, data){
					app.helper.hideProgress();
					app.helper.hideModal();
					if(err === null){
						jQuery('.settingsPageDiv').html(data);
						Settings_Roles_Js.initTreeView();
					}
				});
				
			}
		};
		
		form.vtValidate(params);
        
        //toggle readonly- So validation works for the element
        jQuery('[name="transfer_record_display"]', form).on('focus', function() {
            jQuery(this).prop('readonly',true);
        }).on('focusout', function() {
            jQuery(this).prop('readonly',false);
        });
		
		jQuery('[data-action="popup"]').on('click',function(e) {
			e.preventDefault();
			var target = $(e.currentTarget);
			var field  = target.data('field');
			var popupjs = new Vtiger_Popup_Js();
			
			var transferRole = function(container) {
				jQuery('.roleEle', container).click(function(e){
					e.preventDefault();
					var target = jQuery(e.currentTarget);
					var selectedRoledId = target.closest('li').data('roleid');
					jQuery('[name="'+field+'_display"]', form).val(target.text());
                     jQuery('.clearReferenceSelection').removeClass('hide');
					jQuery('[name="'+field+'"]', form).val(selectedRoledId);
                    jQuery('[name="transfer_record_display"]', form).valid();
					app.helper.hidePopup();
				});
			};
			
			popupjs.showPopup(target.data('url'),'', transferRole);
		});
		
		jQuery('#clearRole').on('click',function(e){
			jQuery('[name="transfer_record_display"]', form).val('');
			jQuery('[name="transfer_record"]', form).val('');
            jQuery('.clearReferenceSelection').addClass('hide');
		});
	},
	
	initTreeView: function() {
		
		function applyMoveChanges(roleid, parent_roleid) {
			var params = {
				module: 'Roles',
				action: 'MoveAjax',
				parent: 'Settings',
				record: roleid,
				parent_roleid: parent_roleid
			}
			
			app.request.post({'data' : params}).then(function(err, res) {
				if (err) {
					alert(app.vtranslate('JS_FAILED_TO_SAVE'));
					window.location.reload();
				}
			});
		}
		
		function modalActionHandler(event) {
			var target = $(event.currentTarget);
			var params = {};
			params.cb = function(data){
				Settings_Roles_Js.initDeleteView();
			};
			
			app.request.get({'url' : target.data('url')}).then(function(err, data){
				if(err === null) {
					app.helper.showModal(data, params);
				}
			});
		}
		
		jQuery('[data-action="modal"]').click(modalActionHandler);
		
		jQuery('.toolbar').hide();
		
		jQuery('.toolbar-handle').bind('mouseover', function(e){
			var target = $(e.currentTarget);
			jQuery('.toolbar', target).css({display: 'inline'});
		});
		jQuery('.toolbar-handle').bind('mouseout', function(e){
			var target = $(e.currentTarget);
			jQuery('.toolbar', target).hide();
		});
		
		jQuery('.draggable').draggable({
			containment: '.treeView',
			start : function(event, ui) {
				var container = jQuery(ui.helper);
				var referenceid = container.data('refid');
				var sourceGroup = jQuery('[data-grouprefid="'+referenceid+'"]');
				var sourceRoleId = sourceGroup.data('roleid');
				if(sourceRoleId == 'H5' || sourceRoleId == 'H2') {
					var params = {};
					params.title = app.vtranslate('JS_PERMISSION_DENIED');
					params.message = app.vtranslate('JS_NO_PERMISSIONS_TO_MOVE');
					app.helper.showErrorNotification(params);
				}
			},
			helper: function(event) {
				var target = $(event.currentTarget);
				var targetGroup = target.closest('li');
				var timestamp = +(new Date());

				var container = $('<div/>');
                container.css('z-index',1000);
				container.data('refid', timestamp);
				container.html(targetGroup.clone());

				// For later reference we shall assign the id before we return
				targetGroup.attr('data-grouprefid', timestamp);
                //remove tooltip in the clone
                container.find('.tooltip').remove();
				return container;
			}
		});
		jQuery('.droppable').droppable({
			hoverClass: 'btn-primary',
			tolerance: 'pointer',
			drop: function(event, ui) {
				var container = $(ui.helper);
				var referenceid = container.data('refid');
				var sourceGroup = $('[data-grouprefid="'+referenceid+'"]');
				
				var thisWrapper = $(this).closest('div');

				var targetRole  = thisWrapper.closest('li').data('role');
				var targetRoleId= thisWrapper.closest('li').data('roleid');
				var sourceRole   = sourceGroup.data('role');
				var sourceRoleId = sourceGroup.data('roleid');

				// Attempt to push parent-into-its own child hierarchy?
				if (targetRole.indexOf(sourceRole) == 0) {
					// Sorry
					return;
				}
				//Attempt to move the roles CEO and Sales Person
				if (sourceRoleId == 'H5' || sourceRoleId == 'H2') {
					return;
				}
				sourceGroup.appendTo(thisWrapper.next('ul'));

				applyMoveChanges(sourceRoleId, targetRoleId);
			}
		});
        vtUtils.enableTooltips();
	},
	
	registerShowNewProfilePrivilegesEvent : function() {
		jQuery('[name="profile_directly_related_to_role"]').on('change',function(e){
			var target = jQuery(e.currentTarget);
			var hanlder = target.data('handler');
			var container = jQuery('[data-content="'+ hanlder + '"]');
 
            vtUtils.hideValidationMessage(jQuery('#profilesList'));            
			if(hanlder === 'new'){
				Settings_Roles_Js.getProfilePriviliges();
				return false;
			}else {
				jQuery('#profilesList', container).removeClass('hide');
                Settings_Roles_Js.registerProfileEvents();
			}
            
			jQuery('[data-content]').not(container).fadeOut('slow',function(){
				container.fadeIn('slow');
			});
		});
	},
    
    /**
     * To register Profile Edit View Events
     */
	_registeredProfileEvents: false,
    registerProfileEvents : function() {
        if(!this._registeredProfileEvents && typeof window['Settings_Profiles_Edit_Js'] != 'undefined'){
            var instance = new Settings_Profiles_Edit_Js();
			this._registeredProfileEvents = true;
        }
    },
	
	onLoadProfilePrivilegesAjax : function() {
		jQuery('[name="profile_directly_related_to_role"]:checked').trigger('change');
	},
	
	getProfilePriviliges : function() {
		var content = jQuery('[data-content="new"]');
		var profileId = jQuery('[name="profile_directly_related_to_role_id"]').val();
		var params = {
			data : {
				module : 'Profiles',
				parent: 'Settings',
				view : 'EditAjax',
				record : profileId
			}
		}
		if(Settings_Roles_Js.newPriviliges == true) {
			jQuery('[data-content="existing"]').fadeOut('slow',function(){
				content.fadeIn('slow');
			});
			return false;
		}
		
		app.helper.showProgress();
		
		app.request.post(params).then(function(err, data) {
			app.helper.hideProgress();
			if(err === null) {
				content.find('.profileData').html(data);
				vtUtils.showSelect2ElementView(jQuery('#directProfilePriviligesSelect'));
				Settings_Roles_Js.registerExistingProfilesChangeEvent();
				Settings_Roles_Js.newPriviliges = true;
				jQuery('[data-content="existing"]').fadeOut('slow',function(){
					content.fadeIn('slow',function(){
					});
				});
                Settings_Roles_Js.registerProfileEvents();
			}else {
				app.helper.showErrorNotification({'message' : err.message});
			}
		});
	},
	
	registerExistingProfilesChangeEvent : function() {
		jQuery('#directProfilePriviligesSelect').on('change',function(e) {
			var profileId = jQuery(e.currentTarget).val();
			var params = {
				module : 'Profiles',
				parent: 'Settings',
				view : 'EditAjax',
				record : profileId
			};
			app.helper.showProgress();
			
			app.request.get({'data' : params}).then(function(err, data) {
				app.helper.hideProgress();
				if(err === null) {
					jQuery('[data-content="new"]').find('.profileData').html(data);
					vtUtils.showSelect2ElementView(jQuery('#directProfilePriviligesSelect'));
					Settings_Roles_Js.registerExistingProfilesChangeEvent();
                    Settings_Roles_Js.registerProfileEvents();
				}
			});
		});
	},
	
	registerSubmitEvent : function() {
		var thisInstance = this;
		var form = jQuery('[name="EditRole"]');
		
		var params = {
			submitHandler : function(data) {
                jQuery("button[name='saveButton']").attr("disabled","disabled");
				var form = jQuery(data);
				if(form.data('submit') == 'true' && form.data('performCheck') == 'true') {
					return true;
				} else {
					if(this.numberOfInvalids() <= 0) {
						app.helper.showProgress();
						var formData = form.serializeFormData();
						
						thisInstance.checkDuplicateName({
							'rolename' : formData.rolename,
							'record' : formData.record
						}).then(
							function(data){
								app.helper.showProgress();
								form.data('submit', 'true');
								form.data('performCheck', 'true');

								app.request.post({'data' : formData}).then(function(err, data){
									app.helper.hideProgress();
									if(err === null ){
										window.history.back();
									}else {

									}
								});
							},
							function(err){
								app.helper.hideProgress();
								app.helper.showErrorNotification({'message' : err.message});
							});
					} else {
						//If validation fails, form should submit again
						form.removeData('submit');
					}
				}
			}
		};
		
		form.vtValidate(params);
	},
	
	/*
	 * Function to check Duplication of Role Names
	 * returns boolean true or false
	 */

	checkDuplicateName : function(details) {
		var aDeferred = jQuery.Deferred();
		
		var params = {
		'module' : app.getModuleName(),
		'parent' : app.getParentModuleName(),
		'action' : 'EditAjax',
		'mode'   : 'checkDuplicate',
		'rolename' : details.rolename,
		'record' : details.record
		};
		
		app.request.get({'data' : params}).then(
			function(err, response) {
				if(err === null) {
					var result = response['success'];
					if(result === true) {
						aDeferred.reject(response);
					} else {
						aDeferred.resolve(response);
					}
				}
			});
		return aDeferred.promise();
	},
	
	registerEvents : function() {
        var view = app.view();
        if(view === 'Index') {
            Settings_Roles_Js.initTreeView();
        } else if(view === 'Edit') {
            Settings_Roles_Js.registerShowNewProfilePrivilegesEvent();
            Settings_Roles_Js.onLoadProfilePrivilegesAjax();
            Settings_Roles_Js.registerSubmitEvent();
        }
        
	}
};

Vtiger.Class("Settings_Roles_Index_Js",{},{
	init : function() {
		this.addComponents();
                Settings_Roles_Js.registerEvents();
	},
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});

Vtiger.Class("Settings_Roles_Edit_Js",{},{
	init : function() {
		this.addComponents();
                Settings_Roles_Js.registerEvents();
	},
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});
