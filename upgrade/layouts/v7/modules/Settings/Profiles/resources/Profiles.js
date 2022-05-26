/*+*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

var Settings_Profiles_Js = {
	
	initEditView: function() {

		function toggleEditViewTableRow(e) {
			var target = jQuery(e.currentTarget);
			var container = jQuery('[data-togglecontent="'+ target.data('togglehandler') + '"]');
			var closestTrElement = container.closest('tr');
			
			if (target.find('i').hasClass('fa-chevron-down')) {
				closestTrElement.removeClass('hide');
				container.slideDown('slow');
				target.find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
			} else {
				container.slideUp('slow',function(){
					closestTrElement.addClass('hide');
				});
				target.find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
			}
		}
		
		function handleChangeOfPermissionRange(e, ui) {
			var target = jQuery(ui.handle);
			if (!target.hasClass('mini-slider-control')) {
				target = target.closest('.mini-slider-control');
			}
			var input  = jQuery('[data-range-input="'+target.data('range')+'"]');
			input.val(ui.value);
			target.attr('data-value', ui.value);
		}
		
		function handleModuleSelectionState(e) {
			var target = jQuery(e.currentTarget);
			var tabid  = target.data('value');
			
			var parent = target.closest('tr');
			if (target.is(':checked')) {
				jQuery('[data-action-state]', parent).prop('checked', true);
				jQuery('[data-action-tool="'+tabid+'"]').prop('checked', true);
				jQuery('[data-handlerfor]', parent).removeAttr('disabled');
			} else {
				jQuery('[data-action-state]', parent).prop('checked', false);
				
				// Pull-up fields / tools details in disabled state.
				jQuery('[data-handlerfor]', parent).attr('disabled', 'disabled');
				jQuery('[data-handlerfor]', parent).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
				jQuery('[data-togglecontent="'+tabid+'-fields"]').hide();
				jQuery('[data-togglecontent="'+tabid+'-tools"]').hide();
				jQuery('[data-togglecontent="'+tabid+'-fields"]').closest('tr').addClass('hide');
			}
		}
		
		function handleActionSelectionState(e) {
			var target = jQuery(e.currentTarget);
			var parent = target.closest('tr');
			var checked = target.prop('checked')? true : false;
			
			if (jQuery.inArray(target.data('action-state'), ['CreateView', 'EditView', 'Delete']) != -1) {
				if (checked) {
					jQuery('[data-action-state="DetailView"]', parent).prop('checked', true);
					jQuery('[data-module-state]', parent).prop('checked', true);
					jQuery('[data-handlerfor]', parent).removeAttr('disabled');
				}
			}
			if (target.data('action-state') == 'DetailView') {
				if (!checked) {
					jQuery('[data-action-state]', parent).prop('checked', false);
					jQuery('[data-module-state]', parent).prop('checked', false).trigger('change');
				} else {
					jQuery('[data-module-state]', parent).prop('checked', true);
					jQuery('[data-handlerfor]', parent).removeAttr('disabled');
				}
			}
		}
		
		function selectAllModulesViewAndToolPriviliges(e) {
			var target = jQuery(e.currentTarget);
			var checked = target.is(':checked');
			if(checked) {
				jQuery('#mainAction4CheckBox').prop('checked', true);
				jQuery('#mainModulesCheckBox').prop('checked', true);
				jQuery('.modulesCheckBox').prop('checked', true);
				jQuery('.action4CheckBox').prop('checked', true);
				jQuery('[data-handlerfor]').removeAttr('disabled');
			}
		}
		
		jQuery('[data-module-state]').change(handleModuleSelectionState);
		jQuery('[data-action-state]').change(handleActionSelectionState);
		jQuery('#mainAction1CheckBox,#mainAction7CheckBox,#mainAction2CheckBox').change(selectAllModulesViewAndToolPriviliges);
		
		jQuery('[data-togglehandler]').click(toggleEditViewTableRow);
		jQuery('[data-range]').each(function(index, item) {
			item = jQuery(item);
			if(item.data('locked')){
				jQuery('.editViewMiniSlider').css('cursor','pointer');
			}
			var value = item.data('value');
			item.slider({
				min: 0,
				max: 2,
				value: value,
				disabled: item.data('locked'),
				slide: handleChangeOfPermissionRange
			});
		});	
		
		//fix for IE jQuery UI slider
		jQuery('[data-range]').find('a').css('filter','');

	},
	
	registerSelectAllModulesEvent : function() {
		var moduleCheckBoxes = jQuery('.modulesCheckBox');
		var viewAction = jQuery('#mainAction4CheckBox');
		var createAction = jQuery('#mainAction7CheckBox');
		var editAction = jQuery('#mainAction1CheckBox');
		var deleteAction = jQuery('#mainAction2CheckBox');
		var mainModulesCheckBox = jQuery('#mainModulesCheckBox');
		mainModulesCheckBox.on('change',function(e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if(mainCheckBox.is(':checked')){
				moduleCheckBoxes.prop('checked',true);
				viewAction.prop('checked',true);
				createAction.show().prop('checked',true);
				editAction.show().prop('checked',true);
				deleteAction.show().prop('checked',true);
				moduleCheckBoxes.trigger('change');
			} else {
				moduleCheckBoxes.filter(':visible').not(':disabled').prop('checked',false);
				moduleCheckBoxes.trigger('change');
				viewAction.prop('checked',false);
				createAction.prop('checked', false);
				editAction.prop('checked', false);
				deleteAction.prop('checked', false);
			}
		});
		
		moduleCheckBoxes.on('change',function(){
			Settings_Profiles_Js.checkSelectAll(moduleCheckBoxes,mainModulesCheckBox);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action4CheckBox'),viewAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action7CheckBox'),createAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action1CheckBox'),editAction);
			Settings_Profiles_Js.checkSelectAll(jQuery('.action2CheckBox'),deleteAction);
		});
	},
	
	registerSelectAllViewActionsEvent : function() {
		var viewActionCheckBoxes = jQuery('.action4CheckBox');
		var mainViewActionCheckBox = jQuery('#mainAction4CheckBox');
		var modulesMainCheckBox = jQuery('#mainModulesCheckBox');
		
		mainViewActionCheckBox.on('change',function(e){
			var mainCheckBox = jQuery(e.currentTarget);
			if(mainCheckBox.is(':checked')){
				modulesMainCheckBox.prop('checked',true);
				modulesMainCheckBox.trigger('change');
			} else {
				modulesMainCheckBox.prop('checked',false);
				modulesMainCheckBox.trigger('change');
			}
		});
		
		viewActionCheckBoxes.on('change',function() {
			Settings_Profiles_Js.checkSelectAll(viewActionCheckBoxes,mainViewActionCheckBox);
		});
		
	},

	registerSelectAllCreateActionsEvent : function() {
		var createActionCheckBoxes = jQuery('.action7CheckBox');
		var mainCreateActionCheckBox = jQuery('#mainAction7CheckBox');
		mainCreateActionCheckBox.on('change', function (e) {
			var mainCheckBox = jQuery(e.currentTarget);
			if (mainCheckBox.is(':checked')) {
				createActionCheckBoxes.prop('checked', true);
			} else {
				createActionCheckBoxes.prop('checked', false);
			}
		});
		createActionCheckBoxes.on('change', function () {
			Settings_Profiles_Js.checkSelectAll(createActionCheckBoxes, mainCreateActionCheckBox);
		});

	},

	registerSelectAllEditActionsEvent : function() {
		var createActionCheckBoxes = jQuery('.action1CheckBox');
		var mainCreateActionCheckBox =  jQuery('#mainAction1CheckBox');
		mainCreateActionCheckBox.on('change',function(e){
			var mainCheckBox = jQuery(e.currentTarget);
			if(mainCheckBox.is(':checked')){
				createActionCheckBoxes.prop('checked',true);
			} else {
				createActionCheckBoxes.prop('checked',false);
			}
		});
		createActionCheckBoxes.on('change',function() {
			Settings_Profiles_Js.checkSelectAll(createActionCheckBoxes,mainCreateActionCheckBox);
		});
		
	},
	
	registerSelectAllDeleteActionsEvent : function() {
		var deleteActionCheckBoxes = jQuery('.action2CheckBox');
		var mainDeleteActionCheckBox =  jQuery('#mainAction2CheckBox');
		mainDeleteActionCheckBox.on('change',function(e){
			var mainCheckBox = jQuery(e.currentTarget);
			if(mainCheckBox.is(':checked')){
				deleteActionCheckBoxes.prop('checked',true);
			} else {
				deleteActionCheckBoxes.prop('checked',false);
			}
		});
		deleteActionCheckBoxes.on('change',function() {
			Settings_Profiles_Js.checkSelectAll(deleteActionCheckBoxes,mainDeleteActionCheckBox);
		});
	},

	checkSelectAll : function(checkBoxElement,mainCheckBoxElement){
		var state = true;
		if(typeof checkBoxElement == 'undefined' || typeof mainCheckBoxElement == 'undefined'){
			return false;
		}
		checkBoxElement.each(function(index,element){
			if(jQuery(element).is(':checked')){
				state = true;
			}else{
				state = false;
				return false;
			}
		});
		if(state == true){
			mainCheckBoxElement.prop('checked',true);
		} else {
			mainCheckBoxElement.prop('checked', false);
		}
	},
	
	performSelectAllActionsOnLoad : function() {
		if(jQuery('[data-module-unchecked]').length > 0){
			jQuery('#mainModulesCheckBox').prop('checked',false);
		}

		if(jQuery('[data-action4-unchecked]').length <= 0){
			jQuery('#mainAction4CheckBox').prop('checked',true);
		}
		if(jQuery('[data-action7-unchecked]').length <= 0) {
			jQuery('#mainAction7CheckBox').prop('checked',true);
		}
		if(jQuery('[data-action1-unchecked]').length <= 0) {
			jQuery('#mainAction1CheckBox').prop('checked',true);
		}
		if(jQuery('[data-action2-unchecked]').length > 0) {
			jQuery('#mainAction2CheckBox').prop('checked',false);
		}
	}, 
	
	registerSubmitEvent : function() {
		var thisInstance = this;
		var form = jQuery('[name="EditProfile"]');
//		var values = form.serializeArray()
//		values = values.concat( jQuery('input[type=checkbox]:not(:checked)', form).map( function() { 
//			return {"name": this.name, "value": this.value} 
//		}).get() );
//		
//		var startItems = Settings_Profiles_Js.convertSerializedArrayToHash(values);
		
		form.on('submit',function(e) {
			e.preventDefault();
		});
		
		var params = {
			submitHandler : function(form) {
				var form = jQuery(form);
				jQuery('[name="EditProfile"]').find('.saveButton').attr('disabled',true);
				if(form.data('submit') === 'true' && form.data('performCheck') === 'true') {
					return true;
				} else {
					if(this.numberOfInvalids() <= 0) {
						var formData = form.serializeFormData();
						app.helper.showProgress();
						
//						var values = form.serializeArray();
//						values = values.concat( jQuery('input[type=checkbox]:not(:checked)', form).map( function() { 
//							return {"name": this.name, "value": 'off'} }).get() 
//						);
//						var currentItems = Settings_Profiles_Js.convertSerializedArrayToHash(values);
//						var itemsToSubmit = Settings_Profiles_Js.hashDiff( startItems, currentItems);
//						var hiddenParams = jQuery('#submitParams :input').serializeFormData();
//						jQuery.extend(itemsToSubmit, hiddenParams);
						
						thisInstance.checkDuplicateName({
							'profileName' : formData.profilename,
							'profileId' : formData.record
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
								jQuery('[name="EditProfile"]').find('.saveButton').removeAttr('disabled');
								app.helper.hideProgress();
								app.helper.showErrorNotification({'message' : err.message});
							});
					} else {
						//If validation fails, form should submit again
						jQuery('[name="EditProfile"]').find('.saveButton').removeAttr('disabled');
						form.removeData('submit');
					}
				}
			}
		};
		
		form.vtValidate(params);
	},
	
//	hashDiff : function(h1, h2) {
//		var d = {};
//		var form = jQuery('#EditView');
//		for (k in h2) {
//		  if (h1[k] !== h2[k]) { 
//			  d[k] = h2[k];
//		  }
//		}
//		return d;
//	},
//
//	convertSerializedArrayToHash : function(a) { 
//		var r = {}; 
//		for (var i = 0;i<a.length;i++) { 
//			if(a[i].name) {
//				r[a[i].name] = a[i].value;
//			}
//		}
//		return r;
//	},
	
	/*
	 * Function to check Duplication of Profile Name
	 * returns boolean true or false
	 */

	checkDuplicateName : function(details) {
		var profileName = details.profileName;
		var recordId = details.profileId;
		var aDeferred = jQuery.Deferred();
		
		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'EditAjax',
			'mode' : 'checkDuplicate',
			'profilename' : profileName,
			'record' : recordId
		};
		
		app.request.post({'data' : params}).then(
			function(err, data) {
				if(err === null) {
					var result = data['success'];
					if(result == true) {
						aDeferred.reject(data);
					} else {
						aDeferred.resolve(data);
					}
				}
			});
		return aDeferred.promise();
	},
	
	registerGlobalPermissionActionsEvent : function() {
		var editAllAction = jQuery('[name="editall"]').filter(':checkbox');
		var viewAllAction = jQuery('[name="viewall"]').filter(':checkbox');
		
		if(editAllAction.is(':checked')) {
			viewAllAction.attr('readonly', 'readonly');
		}
		
		viewAllAction.on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			if(currentTarget.attr('readonly') == 'readonly') {
				var status = jQuery(e.currentTarget).is(':checked');
				if(!status){
					jQuery(e.currentTarget).prop('checked', true)
				}else{
					jQuery(e.currentTarget).removeAttr('checked');
				}
				e.preventDefault();
			}
		})
		
		editAllAction.on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			if(currentTarget.is(':checked')) {
				viewAllAction.prop('checked', true);
				viewAllAction.attr('readonly', 'readonly');
			} else {
				viewAllAction.removeAttr('readonly');
			}
		})
	},
	
	registerEvents : function() {
		Settings_Profiles_Js.initEditView();
		Settings_Profiles_Js.registerSelectAllModulesEvent();
		Settings_Profiles_Js.registerSelectAllViewActionsEvent();
		Settings_Profiles_Js.registerSelectAllCreateActionsEvent();
		Settings_Profiles_Js.registerSelectAllEditActionsEvent();
		Settings_Profiles_Js.registerSelectAllDeleteActionsEvent();
		Settings_Profiles_Js.performSelectAllActionsOnLoad();
		Settings_Profiles_Js.registerGlobalPermissionActionsEvent();
		if(app.getModuleName() === 'Profiles' && app.view() === 'Edit') {
			Settings_Profiles_Js.registerSubmitEvent();
		}
	}

};

Vtiger.Class("Settings_Profiles_Detail_Js",{},{
	init : function() {
		this.addComponents();
		Settings_Profiles_Js.registerEvents();
	},

	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});

Vtiger.Class("Settings_Profiles_Edit_Js",{},{
	init : function() {
		this.addComponents();
		Settings_Profiles_Js.registerEvents();
	},
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});
