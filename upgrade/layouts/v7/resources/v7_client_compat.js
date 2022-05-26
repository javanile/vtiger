/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

var AppConnector = {
	request: function (params) {
		var aDeferred = jQuery.Deferred();
		app.request.post({data: params}).then(
				function (err, data) {
					if (err == null) {
						var result = {'success': true, 'result': data};
						aDeferred.resolve(result);
					} else {
						aDeferred.reject(err);
					}
				});
		return aDeferred.promise();
	},
	requestPjax: function (url) {
		if (typeof url == 'object') {
			var params = {
				data: url
			};
		} else if (typeof url == 'string') {
			var params = {
				url: url
			};
		}

		var aDeferred = jQuery.Deferred();
		app.request.pjax(params).then(
				function (err, res) {
					if (res) {
						aDeferred.resolve(res);
					} else {
						aDeferred.reject(err);
					}
				});
		return aDeferred.promise();
	}
};

jQuery.fn.progressIndicator = function (options) {
	if (options && typeof options.mode != 'undefined' && options.mode == 'hide') {
		app.helper.hideProgress();
	}
	return jQuery('<body>');
};

jQuery.progressIndicator = function (options) {
	if (options) {
		if (typeof options.message != 'undefined') {
			app.helper.showProgress(options.message);
		} else {
			app.helper.showProgress();
		}
	}
	return jQuery('<body>');
};

jQuery.extend(window.app, {
	validationEngineOptions: {},
	showSelect2ElementView: function (selectElement, params) {
		vtUtils.showSelect2ElementView(selectElement, params);
	},
	changeSelectElementView: function (container) {
		vtUtils.applyFieldElementsView(container);
	},
	notifyPostAjaxReady: function () {
		jQuery(document).trigger('postajaxready');
	},
	listenPostAjaxReady: function (callback) {
		jQuery(document).on('postajaxready', callback);
	},
	hideModalWindow: function () {
		app.helper.hideModal();
	},
	showModalWindow: function (data, callback) {
		app.helper.showModal(data.result, {cb: callback});
	},
	hidePopup: function () {
		app.helper.hidePopup();
	},
	getViewName: function () {
		return app.view();
	}
});
jQuery.extend(window, {
	close: function () {
		app.helper.hidePopup();
	}
});

jQuery.triggerParentEvent = function (eventToTrigger, data) {
	if (typeof eventToTrigger == 'function') {
		eventToTrigger(data);
	} else {
		app.event.trigger(eventToTrigger, data);
	}
};

Vtiger_Helper_Js.showHorizontalTopScrollBar = function (container) {
	app.helper.showHorizontalScroll(container);
};

Vtiger_Helper_Js.showPnotify = function (params) {
	if (params.type != 'error') {
		app.helper.showSuccessNotification({'message': params.title});
	} else {
		app.helper.showErrorNotification({'message': params.title});
	}
};

Vtiger_Helper_Js.showMessage = function (params) {
	Vtiger_Helper_Js.showPnotify(params);
};

Vtiger_List_Js.prototype.registerTimeListSearch = function (container) {
	vtUtils.registerEventForTimeFields(container);
};

Vtiger_List_Js.prototype.registerDateListSearch = function (container) {
	vtUtils.registerEventForDateFields(container);
};

Vtiger_List_Js.prototype.calculatePages = function () {
	var thisInstance = this;
	var aDeferred = jQuery.Deferred();
	var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
	thisInstance.totalNumOfRecords(totalNumberOfRecords);
	aDeferred.resolve();
	return aDeferred.promise();
};

Vtiger_List_Js.prototype.checkSelectAll = function () {
	var state = true;
	jQuery('.listViewEntriesCheckBox').each(function (index, element) {
		if (jQuery(element).is(':checked')) {
			state = true;
		} else {
			state = false;
			return false;
		}
	});
	if (state == true) {
		jQuery('#listViewEntriesMainCheckBox').attr('checked', true);
	} else {
		jQuery('#listViewEntriesMainCheckBox').attr('checked', false);
	}
};

Vtiger_List_Js.prototype.registerChangeCustomFilterEvent = function () {
	var thisInstance = this;
	jQuery('#listViewContent').on('change', '#customFilter', function () {
		jQuery('#pageNumber').val('1');
		jQuery('#pageToJump').val('1');
		jQuery('#orderBy').val('');
		jQuery('#sortOrder').val('');
		var cvId = thisInstance.getCurrentCvId();
		selectedIds = new Array();
		excludedIds = new Array();

		var urlParams = {
			'viewname': cvId,
			'page': '',
			//to make alphabetic search empty
			'search_value': '',
			'search_params': ''
		}
		//Make the select all count as empty
		jQuery('#recordsCount').val('');
		//Make total number of pages as empty
		jQuery('#totalPageCount').text('');
		thisInstance.loadListViewRecords(urlParams).then(function () {
			thisInstance.updatePagination();
		});
	});
};

Vtiger_List_Js.prototype.getListViewContentContainer = function () {
	return this.getListViewContainer();
};

Vtiger_Popup_Js.prototype.show = function (params, callback) {
	if (typeof params == 'object' && (typeof params['view'] == 'undefined')) {
		params['view'] = 'Popup';
	}
	app.event.one('After_show_popup_select', function (e, data) {
		callback(data);
	});
	this.showPopup(params, 'After_show_popup_select');
};

Vtiger_Popup_Js.prototype.calculatePages = function () {
	var thisInstance = this;
	var aDeferred = jQuery.Deferred();
	var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
	thisInstance.totalNumOfRecords(totalNumberOfRecords);
	aDeferred.resolve();
	return aDeferred.promise();
};