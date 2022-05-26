/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Index_Js('Settings_ModuleManager_List_Js', {
}, {
	/*
	 * function to update the module status for the module
	 * @params: currentTarget - checkbox related to module.
	 */
	updateModuleStatus: function (currentTarget) {
		var aDeferred = jQuery.Deferred();
		var forModule = currentTarget.data('module');
		var status = currentTarget.is(':checked');
		app.helper.showProgress();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['updateStatus'] = status;
		params['forModule'] = forModule;
		params['action'] = 'Basic';
		params['mode'] = 'updateModuleStatus';

		app.request.post({'data': params}).then(
			function (err, data) {
				if (err === null) {
					app.helper.hideProgress();
					aDeferred.resolve(data);
				}
			},
			function (error) {
				app.helper.hideProgress();
				//TODO : Handle error
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	registerEventsForImportFromZip: function (container) {
		// Forcefully disable file-input and submit
		var fileuploadWrap = container.find('.fileUploadBtn').parent();
		var importFromZip = container.find('[name="importFromZip"]');
		var moduleZip = container.find('[name="moduleZip"]');
		var fileDetails = jQuery('#moduleFileDetails');

		fileuploadWrap.hide();
		importFromZip.attr('disabled', 'disabled');

		container.on('change', '[name="acceptDisclaimer"]', function (e) {
			var element = jQuery(e.currentTarget);
			if (element.is(':checked')) {
				fileuploadWrap.show();
			} else {
				fileuploadWrap.hide();
				importFromZip.attr('disabled', 'disabled');
				fileDetails.removeAttr('title').html('');
				moduleZip.val('');
			}
		});

		container.on('change', '[name="moduleZip"]', function (e) {
			var uploadedFile = moduleZip.val();
			if (uploadedFile) {
				jQuery('#moduleFileDetails').attr('title', uploadedFile).html(uploadedFile);
			}
			var acceptDisclaimer = container.find('[name="acceptDisclaimer"]');
			if (acceptDisclaimer.is(':checked') && uploadedFile) {
				importFromZip.removeAttr('disabled');
			}
		});

		container.on('click', '.finishButton', function () {
			window.location.href = jQuery('[data-name="VTLIB_LBL_MODULE_MANAGER"]').attr('href');
		});

		container.on('click', '.importModule, .updateModule', function (e) {
			var element = jQuery(e.currentTarget);
			var params = {};
			if (element.hasClass('updateModule')) {
				params = {
					'module': app.getModuleName(),
					'parent': app.getParentModuleName(),
					'action': 'Basic',
					'mode': 'updateUserModuleStep3'
				};
			} else if (element.hasClass('importModule')) {
				params = {
					'module': app.getModuleName(),
					'parent': app.getParentModuleName(),
					'action': 'Basic',
					'mode': 'importUserModuleStep3'
				};
			}
			params['module_import_file'] = container.find('[name="module_import_file"]').val();
			params['module_import_type'] = container.find('[name="module_import_type"]').val();
			params['module_import_name'] = container.find('[name="module_import_name"]').val();

			app.helper.showProgress();

			AppConnector.request(params).then(
				function (data) {
					app.helper.hideProgress();
					element.addClass('hide');
					var headerMessage, containerMessage;

					if (element.hasClass('updateModule')) {
						headerMessage = app.vtranslate('JS_UPDATE_SUCCESSFULL');
						containerMessage = app.vtranslate('JS_UPDATED_MODULE');
					} else if (element.hasClass('importModule')) {
						headerMessage = app.vtranslate('JS_IMPORT_SUCCESSFULL');
						containerMessage = app.vtranslate('JS_IMPORTED_MODULE');
					}
					app.helper.showSuccessNotification({'title': headerMessage, 'message': data.result.importModuleName+' '+containerMessage});
					setTimeout(function () {
						window.location.href = jQuery('[data-name="VTLIB_LBL_MODULE_MANAGER"]').attr('href');
					}, 3000);
				}
			);
		});

		container.on('click', '.acceptLicense', function (e) {
			var element = jQuery(e.currentTarget);
			var saveButton = container.find('[name="saveButton"]')
			if (element.is(':checked')) {
				saveButton.removeAttr("disabled");
			} else {
				if (typeof saveButton.attr('disabled') == 'undefined') {
					saveButton.attr('disabled', "disabled");
				}
			}
		});
	},
	registerEvents: function (e) {
		var thisInstance = this;
		var container = jQuery('#moduleManagerContents');
		this._super(container);
		var importFromZipContainer = jQuery('#importModules');
		if (importFromZipContainer.length > 0) {
			thisInstance.registerEventsForImportFromZip(importFromZipContainer);
		}

		//register click event for check box to update the module status
		container.on('click', '[name="moduleStatus"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var moduleBlock = currentTarget.closest('.moduleManagerBlock');
			var actionButtons = moduleBlock.find('.actions');
			var forModule = currentTarget.data('moduleTranslation');
			var moduleDetails = moduleBlock.find('.moduleImage, .moduleName');

			if (currentTarget.is(':checked')) {
				//show the settings button for the module.
				actionButtons.removeClass('hide');

				//changing opacity of the block if the module is enabled
				moduleDetails.removeClass('dull');

				//update the module status as enabled
				thisInstance.updateModuleStatus(currentTarget).then(function (data) {
					var message = forModule+' '+app.vtranslate('JS_MODULE_ENABLED');
					app.helper.showSuccessNotification({'message': message});
				});
			} else {
				//hide the settings button for the module.
				actionButtons.addClass('hide');

				//changing opacity of the block if the module is disabled
				moduleDetails.addClass('dull');

				//update the module status as disabled
				thisInstance.updateModuleStatus(currentTarget).then(function (data) {
					var message = forModule+' '+app.vtranslate('JS_MODULE_DISABLED');
					app.helper.showSuccessNotification({'message': message});
				});
			}
		});
	}
});