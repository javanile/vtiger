/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

window.app = (function () {
	// Protected Globals
	var _controller = null;
	// Copy meta to avoid runtime tampering.
	var _module = _META.module;
	var _view = _META.view;
	var _parentModule = _META.parent;
	var _app = _META.app;

	function Request() {
		this._requestPjax = function (params) {
			var aDeferred = jQuery.Deferred();

			if (typeof params.url === 'undefined') {
				params.url = 'index.php';
			}

			if (typeof params.container === 'undefined') {
				params.container = '#pjaxContainer';
				params.defaultContainer = true;
			}

			var pjaxContainer = jQuery('#pjaxContainer');
			//Clear contents existing before
			if (params.container === '#pjaxContainer') {
				pjaxContainer.html('');
			}

			jQuery(document).on('pjax:success', function (event, data, status, jqXHR) {
				if (typeof data == 'object') {
					if (!data['success'] && data['error']['message']) {
						aDeferred.resolve(new VtError(data['error']));
						return;
					} else if (data['result']) {
						data = data['result'];
					}
				}
				if (params.defaultContainer) {
					//remove the data in default container since it will be having duplicate data which will be problem espcially with id selector
					pjaxContainer.html('');
				}
				aDeferred.resolve(null, data);
			});

			jQuery(document).on('pjax:error', function (event, jqXHR, textStatus, errorThrown) {
				aDeferred.resolve(new VtError(errorThrown));
			});
			jQuery.pjax(params);
			return aDeferred.promise();
		},
		this._request = function (params) {
			var aDeferred = jQuery.Deferred();

			if (typeof params.url === 'undefined') {
				params.url = 'index.php';
			}

			var ajaxParams = {
				method: params.type,
				url: params.url,
				data: params.data,
				success: function (response) {
					if (typeof response == 'object') {
						if (!response['success'] && response['error']['message']) {
									aDeferred.resolve(new VtError(response['error']));
							return;
						} else if (response['result']) {
							response = response['result'];
						}
					}
					aDeferred.resolve(null, response);
				},
				error: function (xhr, ajaxOptions, err) {
							aDeferred.resolve(new VtError(err));
				}
			};

			if (typeof params.contentType != 'undefined') {
				ajaxParams['contentType'] = params.contentType;
			}

			if (typeof params.processData != 'undefined') {
				ajaxParams['processData'] = params.processData;
			}

			jQuery.ajax(ajaxParams);
			return aDeferred.promise();
		},
		this.get = function (params) {
			var aDeferred = jQuery.Deferred();
			params.type = 'GET';
			this._request(params).then(function (err, data) {
				return aDeferred.resolve(err, data);
			});
			return aDeferred.promise();
		},
		this.post = function (params) {
			var aDeferred = jQuery.Deferred();
			params.type = 'POST';
			//caller has send only url so we need to break it to data 
			if (typeof params.data == 'undefined') {
				params.data = app.convertUrlToDataParams(params.url);
				delete params.url;
			}
			this._request(params).then(function (err, data) {
				return aDeferred.resolve(err, data);
			});
			return aDeferred.promise();
		},
		this.pjax = function (params) {
			var aDeferred = jQuery.Deferred();
			params.type = 'GET';
			this._requestPjax(params).then(function (err, data) {
				return aDeferred.resolve(err, data);
			});
			return aDeferred.promise();
		}
	}

	function Event() {
		this.el = jQuery({});

		this.trigger = function (/* eventName, arg1, arg2... */) {
			var args = Array.prototype.slice.call(arguments, 1);
			this.el.trigger(arguments[0], args);
		}
		this.on = function (/* eventName, fn */) {
			this.el.on.apply(this.el, arguments);
		}
		this.one = function (/* eventName, fn */) {
			this.el.one.apply(this.el, arguments);
		}
		this.off = function (/* eventName, fn */) {
			this.el.off.apply(this.el, arguments);
		}
	}

	return {
		storage: {
			NSKey: function (key) {
				return 'vtiger6.'+key; // retain the existing cached data
			},
			get: function (key, defvalue) {
				key = this.NSKey(key);
				return jQuery.jStorage.get(key, defvalue);
			},
			set: function (key, value) {
				key = this.NSKey(key);
				jQuery.jStorage.set(key, value);
			},
			delete: function (key) {
				key = this.NSKey(key);
				jQuery.jStorage.deleteKey(key);
			},
			flush: function () {
				jQuery.jStorage.flush();
			}
		},
		request: new Request(),
		event: new Event(),
		helper: new Vtiger_Helper_Js(),
		/**
		 * Function to decode the encoded htmlentities values
		 */
		getDecodedValue: function (value) {
			return jQuery('<div></div>').html(value).text();
		},
		/**
		 * To Convert give URL to POST params
		 * @param {type} url
		 * @returns {Array}
		 */
		convertUrlToDataParams: function (url) {
			var params = {};
			if (typeof url !== 'undefined' && url.indexOf('?') !== -1) {
				var urlSplit = url.split('?');
				url = urlSplit[1];
			}
			var queryParameters = url.split('&');
			for (var index = 0; index < queryParameters.length; index++) {
				var queryParam = queryParameters[index];
				var queryParamComponents = queryParam.split('=');
				params[queryParamComponents[0]] = queryParamComponents[1];
			}
			return params;
		},
		module: function () {
			return _module;
		},
		view: function () {
			return _view;
		},
		/**
		 * Function returns the current Module name
		 */
		getModuleName: function () {
			return _module;
		},
		getExtensionModuleName: function () {
			var extensionModuleName = '';
			if (typeof _EXTENSIONMETA !== 'undefined' && _EXTENSIONMETA.hasOwnProperty('module')) {
				extensionModuleName = _EXTENSIONMETA.module;
			}
			return extensionModuleName;
		},
		getAppName: function () {
			return _app;
		},
		/**
		 * Function returns the application name
		 * @returns {META.parent}
		 */
		getParentModuleName: function () {
			return _parentModule;
		},
		getDecimalSeparator: function () {
			return jQuery('body').data('user-decimalseparator');
		},
		getGroupingSeparator: function () {
			return jQuery('body').data('user-groupingseparator');
		},
		getNumberOfDecimals: function () {
			return jQuery('body').data('user-numberofdecimals');
		},
		getDateFormat: function () {
			return jQuery('body').data('user-dateformat');
		},
		getHourFormat: function () {
			return jQuery('body').data('user-hourformat');
		},
		getActivityReminderInterval: function () {
			return jQuery('body').data('userCalendarReminderInterval');
		},
		getUserId: function () {
			if (_USERMETA)
				return _USERMETA.id;
		},
		getMenuStatus: function () {
			if (_USERMETA)
				return _USERMETA.menustatus;
		},
		getRecordId: function () {
			var record = jQuery('#recordId')
			if (record.length) {
				return record.val();
			}
			return false;
		},
		getModuleSpecificViewClass: function (view, module, parentModule) {

			if (typeof module == 'undefined') {
				module = _module;
			}
			if (typeof parentModule == 'undefined') {
				parentModule = this.getParentModuleName();
			}

			if (view == '') {
				view = 'Index';
			}

			var controllerClass = null;
			var handlerClass = module+'_'+view+'_Js';
			if (parentModule == 'Settings') {
				handlerClass = parentModule+'_'+module+'_'+view+'_Js';
				if (typeof window[handlerClass] == 'undefined') {
					handlerClass = parentModule+'_Vtiger_'+view+'_Js';
				}
			}
			if (typeof window[handlerClass] != 'undefined') {
				controllerClass = handlerClass;
			} else {
				handlerClass = 'Vtiger_'+view+'_Js';
				if (typeof window[handlerClass] != 'undefined') {
					controllerClass = handlerClass;
				}
			}
			return controllerClass;
		},
		controller: function () {
			if (_controller == null) {
				var controllerClass = this.getModuleSpecificViewClass(_view);
				if (controllerClass) {
					_controller = new window[controllerClass]();
					_controller.intializeComponents();
					_controller.registerEvents();
				}
			}
			return _controller;
		},
		htmlEncode: function (value) {
			if (value) {
				return jQuery('<div />').text(value).html();
			} else {
				return '';
			}
		},
		htmlDecode: function (value) {
			if (value) {
				return $('<div />').html(value).text();
			} else {
				return '';
			}
		},
        changeURL : function(url){
            if (typeof history.pushState !== "undefined") {
                history.pushState({}, "", url);
            } else {
                console.log("Browser does not support HTML5.");
            }
        },
		/**
		 * Function returns translated string
		 */
		languageString: [],
		vtranslate: function (key) {
			//convert arguments in to proper array
			var params = [].slice.apply(arguments);
			params.shift();

			function __sprintf(translatedString, params) {
				if (params.length > 0) {
					var replaceRegex = new RegExp('(%s)', 'g');
					var paramsPointer = 0;
					translatedString = translatedString.replace(replaceRegex, function () {
						var string = params[paramsPointer];
						paramsPointer++;
						return string;
					});
				}
				return translatedString;
			}

			var translatedString = key;
			if (app.languageString[key] !== undefined) {
				translatedString = app.languageString[key];
			} else {
				var strings = jQuery('#js_strings').text();
				if (strings != '') {
					app.languageString = JSON.parse(strings);
					if (key in app.languageString) {
						translatedString = app.languageString[key];
					}
				}
			}
			if (translatedString !== null) {
				return __sprintf(translatedString, params);
			}
			return key;
		},
		/**
		 * Function will return the current users layout+skin path
		 * @param <string> img - image name
		 * @return <string>
		 */
		vimage_path: function (img) {
			return jQuery('body').data('skinpath')+'/images/'+img;
		},
		/**
		 * Function to get the select2 element from the raw select element
		 * @params: select element
		 * @return : select2Element - corresponding select2 element
		 */
		getSelect2ElementFromSelect: function (selectElement) {
			var selectId = selectElement.attr('id');
			//since select2 will add s2id_ to the id of select element
			var select2EleId = 's2id_'+selectId;
			return jQuery('#'+select2EleId);
		},
		getUserLanguage: function () {
			return jQuery('body').data('language');
		},
		convertTojQueryDatePickerFormat: function (dateFormat) {
			var i = 0;
			var splitDateFormat = dateFormat.split('-');
			for (var i in splitDateFormat) {
				var sectionDate = splitDateFormat[i];
				var sectionCount = sectionDate.length;
				if (sectionCount == 4) {
					var strippedString = sectionDate.substring(0, 2);
					splitDateFormat[i] = strippedString;
				}
			}
			var joinedDateFormat = splitDateFormat.join('-');
			return joinedDateFormat;
		},
		getDateInVtigerFormat: function (dateFormat, dateObject) {
			var finalFormat = app.convertTojQueryDatePickerFormat(dateFormat);
			var date = jQuery.datepicker.formatDate(finalFormat, dateObject);
			return date;
		},
		getUserCurrencySymbol: function () {
			return _USERMETA.currency;
		},
		getUserCurrencySymbolPlacement: function () {
			return _USERMETA.currencySymbolPlacement;
		},
		appendUserCurrencySymbol: function (value) {
			var userCurrencySymbol = app.getUserCurrencySymbol();
			var userCurrencySymbolPlacement = app.getUserCurrencySymbolPlacement();

			var appendedValue = value;
			if (userCurrencySymbolPlacement === '1.0$') {
				appendedValue = value+userCurrencySymbol;
			} else {
				appendedValue = userCurrencySymbol+value;
			}

			return appendedValue;
		},
		convertCurrencyToUserFormat: function (value, appendCurrencySymbol) {
			var displayValue;
			var isNegative = false;
			value = value.toString();
			if (parseFloat(value) < 0) {
				isNegative = true;
				value = value.replace('-', '');
			}
			var groupingPattern = _USERMETA.currencyGroupingPattern;
			var numberOfDecimals = app.getNumberOfDecimals();
			var decimalSeparator = app.getDecimalSeparator();
			var groupingSeparator = app.getGroupingSeparator();
			value = parseFloat(value).toFixed(parseInt(numberOfDecimals));
			value = value.toString();
			var valueParts = value.split('.');
			var wholePart = valueParts[0];
			var decimalPart = valueParts[1];
			var truncateTrailingZeros = _USERMETA.truncateTrailingZeros;
			var finalWholePart;
			var ignoreDecimal = false;
			if (truncateTrailingZeros == '1' && parseInt(decimalPart) === 0) {
				ignoreDecimal = true;
			}

			if (groupingPattern == '123456789') {
				finalWholePart = wholePart;
			} else if (groupingPattern == '123456,789') {
				if (wholePart.length > 3) {
					var wholeFirstPart = wholePart.substr(0, (wholePart.length - 3));
				}
				var wholeLastPart = wholePart.substr(wholePart.length - 3);
				if (wholeFirstPart) {
					wholePart = wholeFirstPart+groupingSeparator+wholeLastPart;
				}
				finalWholePart = wholePart;
			} else if (groupingPattern == '123,456,789') {
				var wholeParts = wholePart.toString().split('').reverse().join('').match(/.{1,3}/g).reverse();
				for (var i = 0; i < wholeParts.length; i++) {
					wholeParts[i] = wholeParts[i].toString().split('').reverse().join('');
				}
				finalWholePart = wholeParts.join(groupingSeparator);
			} else if (groupingPattern == '12,34,56,789') {
				if (wholePart.length > 3) {
					var wholeFirstPart = wholePart.substr(0, (wholePart.length - 3));
				}
				var wholeLastPart = wholePart.substr(wholePart.length - 3);
				if (wholeFirstPart) {
					wholeLastPart = groupingSeparator+wholeLastPart;
					var wholeFirstParts = wholeFirstPart.toString().split('').reverse().join('').match(/.{1,2}/g).reverse();
					for (var i = 0; i < wholeFirstParts.length; i++) {
						wholeFirstParts[i] = wholeFirstParts[i].toString().split('').reverse().join('');
					}
					wholeFirstPart = wholeFirstParts.join(groupingSeparator);
					finalWholePart = wholeFirstPart+wholeLastPart;
				} else {
					finalWholePart = wholeLastPart;
				}
			}

			if (ignoreDecimal) {
				displayValue = finalWholePart;
			} else {
				displayValue = finalWholePart+decimalSeparator+decimalPart;
			}

			if (isNegative) {
				displayValue = '-'+displayValue;
			}

			if (appendCurrencySymbol) {
				displayValue = app.appendUserCurrencySymbol(displayValue);
			}

			return displayValue;
		}

	}
})();


/* Turn-off jQuery Migrate warnings */
jQuery.migrateMute = true;

jQuery(function () {
	vtUtils.applyFieldElementsView(jQuery('body'));
	window.app.controller();
	String.prototype.toCamelCase = function () {
		var value = this.valueOf();
		return value.charAt(0).toUpperCase()+value.slice(1).toLowerCase()
	}
	/* To push focus on CKEditor Popup when shown with Bootstrap modal */
	/* ref https://stackoverflow.com/a/23667151 */
	jQuery.fn.modal.Constructor.prototype.enforceFocus = function() {
		modal_this = this
		jQuery(document).on('focusin.modal', function (e) {
		if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length 
		&& !jQuery(e.target.parentNode).hasClass('cke_dialog_ui_input_select') 
		&& !jQuery(e.target.parentNode).hasClass('cke_dialog_ui_input_textarea')
		&& !jQuery(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
			modal_this.$element.focus()
		}
	})};
});
