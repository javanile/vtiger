/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_Edit_Js("Settings_Groups_Edit_Js",{},{
	
	registerCancel: function(){
		jQuery('.cancelLink').click(function(){
			window.history.back();
			return false;
		});
	},

	/**
	 * Function to Validate and Save Event 
	 * @returns {undefined}
	 */
	registerValidation : function () {
		var thisInstance = this;
		var form = jQuery('#EditView');
			var params = {
			submitHandler : function(form) {
				var form = jQuery(form);
				jQuery('.saveButton').attr('disabled', true);
				if(form.data('submit') === 'true' && form.data('performCheck') === 'true') {
					return true;
				} else {
					if(this.numberOfInvalids() <= 0) {
						var formData = form.serializeFormData();
						var groupName = formData.groupname;
						var specialChars = /[<\>\"\,]/;
						if(specialChars.test(groupName)) {
							var groupNameEle = jQuery('[name="groupname"]');
							var errorMsg = app.vtranslate('JS_COMMA_NOT_ALLOWED_GROUP');
							app.helper.showErrorNotification({message:errorMsg});
							jQuery('.saveButton').removeAttr('disabled');
							return false;
						}
						var groupName = formData.groupname;
						if(groupName.indexOf(',') !== -1) {
							var groupNameEle = jQuery('[name="groupname"]');
							 errorMsg = app.vtranslate('JS_COMMA_NOT_ALLOWED_GROUP');
							app.helper.showErrorNotification({message:errorMsg});
							jQuery('.saveButton').removeAttr('disabled');
							return false;
						}
						thisInstance.checkDuplicateName({
							'groupname' : formData.groupname,
							'record' : formData.record
						}).then(
							function(data){
								app.event.trigger('POST.GROUP.SAVE',formData);
								form.data('submit', 'true');
								form.data('performCheck', 'true');
								form.submit();
							},
							function(data, err){
								app.helper.showErrorNotification({message:app.vtranslate('JS_DUPLICATES_EXIST')});
								jQuery('.saveButton').removeAttr('disabled');
								return false;
							}
						);
					}
				}
			}
		};
		form.vtValidate(params);
	},

	checkDuplicateName : function(details) {
		var aDeferred = jQuery.Deferred();

		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'EditAjax',
			'mode' : 'checkDuplicate',
			'groupname' : details.groupname,
			'record' : details.record
		};
		app.request.post({'data' : params}).then(
			function(err, data) {
				if(err === null) {
					var result = data['success'];
					if(result === true) {
						aDeferred.reject(data);
					} else {
						aDeferred.resolve(data);
					}
				}
			});
		return aDeferred.promise();
	},

	memberSelectElement : false,
	getMemberSelectElement : function () {
		if(this.memberSelectElement == false) {
			this.memberSelectElement = jQuery('#memberList');
		}
		return this.memberSelectElement;
	},
	/**
	 * Function to register event for select2 element
	 */
	registerEventForSelect2Element : function(){
		var editViewForm = this.getForm();
		var selectElement = this.getMemberSelectElement();
		selectElement.addClass('select2');
		var params = {};
		params.formatSelection = function(object,container){
			var selectedId = object.id;
			var selectedOptionTag = editViewForm.find('option[value="'+selectedId+'"]');
			var selectedMemberType = selectedOptionTag.data('memberType');
			container.prevObject.addClass(selectedMemberType);
			var element = '<div>'+selectedOptionTag.text()+'</div>';
			return element;
		};
		selectElement.select2('destroy');
		this.changeSelectElementView(selectElement, 'select2',params);
	},

	changeSelectElementView : function(parent, view, viewParams){
		if(typeof parent == 'undefined') {
			parent = jQuery('body');
		}

		//If view is select2, This will convert the ui of select boxes to select2 elements.
		if(view == 'select2') {
			vtUtils.showSelect2ElementView(parent, viewParams);
			return;
		}
	},

	registerEvents : function() {
		this._super();
		this.registerCancel();
		this.registerEventForSelect2Element();
		this.registerValidation();
	}
});