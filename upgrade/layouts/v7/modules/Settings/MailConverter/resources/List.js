/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Settings_Vtiger_Index_Js('Settings_MailConverter_List_Js', {

	checkMailBoxMaxLimit: function (url) {
		app.request.post({'url': url}).then(function (err, data) {
			if (typeof response.result != 'undefined') {
				window.location.href = 'index.php?module='+app.getModuleName()+'&parent='+app.getParentModuleName()+'&view=Edit&mode=step1&create=new';
			} else {
				app.helper.showErrorNotification({'message': err['message']});
			}
		});
	},

	triggerScan: function (url) {
		app.helper.showProgress();
		app.request.post({'url': url}).then(function (err, data) {
			app.helper.hideProgress();
			if (typeof data != 'undefined') {
				app.helper.showSuccessNotification({'message': data.message});
			} else {
				app.helper.showErrorNotification({'message': err['message']});
			}
		});
	},

	triggerDelete: function (url) {
		app.helper.showConfirmationBox({'message': app.vtranslate('LBL_DELETE_CONFIRMATION')}).then(function () {
			app.helper.showProgress();
			app.request.post({'url': url}).then(function (err, data) {
				jQuery('#SCANNER_'+data.id).remove();
				var url = window.location.href;
				var url1 = url.split('&');
				var path = url1[0]+'&'+url1[1]+'&'+url1[2];
				app.helper.showSuccessNotification({'message': app.vtranslate('JS_MAILBOX_DELETED_SUCCESSFULLY')});
				app.helper.hideProgress();
				window.location.assign(path);
			});
		});
	},

	loadMailBox: function (params) {
		params.module = app.getModuleName();
		params.parent = app.getParentModuleName();
		params.view = 'ListAjax';
		params.mode = 'getMailBoxContentView'

		app.helper.showProgress();
		app.request.post({'data': params}).then(function (err, html) {
			app.helper.hideProgress();
			var scannerContentdId = 'SCANNER_'+params.record;
			if (jQuery('#'+scannerContentdId).length > 0) {
				jQuery('#'+scannerContentdId).html(html)
			} else {
				jQuery('#listViewContents').append('<br>'+html);
			}
			app.helper.showSuccessNotification({'message': app.vtranslate('JS_MAILBOX_LOADED_SUCCESSFULLY')});
			if (typeof params.listViewUrl != 'undefined') {
				var path = params.listViewUrl+'&record='+params.record;
				window.location.assign(path);
			}
		});
	}
}, {
	registerEvents: function () {
		this._super();
	}
})