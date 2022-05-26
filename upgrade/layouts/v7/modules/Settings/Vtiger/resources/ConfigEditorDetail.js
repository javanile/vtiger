/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_ConfigEditorDetail_Js", {}, {
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addModuleSpecificComponent('Index', app.getModuleName, app.getParentModuleName());
	},
	/*
	 * Function to save the Configuration Editor content
	 */
	saveConfigEditor: function (form) {
		var aDeferred = jQuery.Deferred();
		var data = form.serializeFormData();
		var updatedFields = {};
		jQuery.each(data, function (key, value) {
			updatedFields[key] = value;
		});

		var params = {
			'module': app.getModuleName(),
			'parent': app.getParentModuleName(),
			'action': 'ConfigEditorSaveAjax',
			'updatedFields': JSON.stringify(updatedFields)
		};

		app.request.post({"data": params}).then(
			function (err, data) {
				if (err === null) {
					aDeferred.resolve(data);
				}
			},
			function (error, err) {
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},
	/*
	 * Function to load the contents from the url through pjax
	 */
	loadContents: function (url) {
		var aDeferred = jQuery.Deferred();
		app.request.post({"url": url}).then(
			function (err, data) {
				aDeferred.resolve(data);
			}
		);
		return aDeferred.promise();
	},
	/*
	 * function to register the events in editView
	 */
	registerEditViewEvents: function () {
		var thisInstance = this;
		var form = jQuery('#ConfigEditorForm');
		var detailUrl = form.data('detailUrl');
		//register validation 
		var params = {
			submitHandler: function (form) {
				var form = jQuery(form);
				thisInstance.saveConfigEditor(form).then(
					function (data) {
						if (data) {
							var message = app.vtranslate('JS_CONFIGURATION_DETAILS_SAVED');
							thisInstance.loadContents(detailUrl).then(
								function (data) {
									jQuery('.settingsPageDiv').html(data);
									thisInstance.registerDetailViewEvents();
								}
							);
						}
						app.helper.showSuccessNotification({'message': message});
					});
			}
		};
		form.vtValidate(params);
		form.on('submit', function (e) {
			e.preventDefault();
			return false;
		});

		//Register click event for cancel link
		var cancelLink = form.find('.cancelLink');
		cancelLink.click(function () {
			thisInstance.loadContents(detailUrl).then(
				function (data) {
					jQuery('.settingsPageDiv').html(data);
					thisInstance.registerDetailViewEvents();
				});
		});
		vtUtils.enableTooltips();
	},
	/*
	 * function to register the events in DetailView
	 */
	registerDetailViewEvents: function () {
		var thisInstance = this;
		var container = jQuery('#ConfigEditorDetails');
		var editButton = container.find('.editButton');
		//Register click event for edit button
		editButton.click(function () {
			var url = editButton.data('url');
			thisInstance.loadContents(url).then(
				function (data) {
					jQuery('#ConfigEditorDetails').html(data);
					vtUtils.showSelect2ElementView(jQuery('#editViewContent').find('.select2-container'));
					thisInstance.registerEditViewEvents();
					thisInstance.registerEvents();
				});
		});
		vtUtils.enableTooltips();
	},

	registerEvents: function () {
		if (jQuery('#ConfigEditorDetails').length > 0) {
			this.registerDetailViewEvents();
		} else {
			this.registerEditViewEvents();
		}
	}

});


