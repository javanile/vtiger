/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Products_Edit_Js", {
	getMessageForChildProductDeletionOrInActivation: function(params) {
		var aDeferred = jQuery.Deferred();
		var message = '';
		if (params['module'] == 'Products') {
			params['action'] = 'Mass';
			params['mode'] = 'isChildProduct';

			app.request.post({data:params}).then(function(err, data) {
				var responseData = data.result;
				for (var id in responseData) {
					if (responseData[id] == true) {
						message = app.vtranslate('JS_DELETION_OR_IN_ACTIVATION_CHILD_PRODUCT_MESSAGE');
					}
				}
				aDeferred.resolve(message);
			});
		} else {
			aDeferred.resolve(message);
		}
		return aDeferred.promise();
	}
},
{
	baseCurrency: '',
	baseCurrencyName: '',
	//Container which stores the multi currency element
	multiCurrencyContainer: false,
	//Container which stores unit price
	unitPrice: false,
	/**
	 * Function to get unit price
	 */
	getUnitPrice: function() {
		if (this.unitPrice == false) {
			this.unitPrice = jQuery('input.unitPrice', this.getForm());
		}
		return this.unitPrice;
	},
	/**
	 * Function to get more currencies container
	 */
	getMoreCurrenciesContainer: function() {
		if (this.multiCurrencyContainer == false) {
			this.multiCurrencyContainer = jQuery('.multiCurrencyEditUI');
		}
		return this.multiCurrencyContainer;
	},
	/**
	 * Function to get current Element
	 */
	getCurrentElem: function(e) {
		return jQuery(e.currentTarget);
	},
	/**
	 * Function to return stripped unit price
	 */
	getDataBaseFormatUnitPrice: function() {
		var field = this.getUnitPrice();
		var container = jQuery('.multiCurrencyEditUI:visible');

		var baseCurrencyEle = container.find('.baseCurrency').filter(':checked');
		var baseCurrecnyParentElem = baseCurrencyEle.closest('tr');
		var baseCurrencyPrice = jQuery('.convertedPrice', baseCurrecnyParentElem);

		var unitPrice = baseCurrencyPrice.val();
		if (unitPrice == '') {
			unitPrice = 0;
		} else {
			var fieldData = field.data();
			//As replace is doing replace of single occurence and using regex
			//replace has a problem with meta characters like (.,$),so using split and join
			var strippedValue = unitPrice.split(fieldData.groupSeparator);
			strippedValue = strippedValue.join("");
			strippedValue = strippedValue.replace(fieldData.decimalSeparator, '.');
			unitPrice = strippedValue;
		}
		return unitPrice;
	},
	/**
	 * Function to get more currencies UI
	 */
	getMoreCurrenciesUI: function() {
		var aDeferred = jQuery.Deferred();
		var moduleName = this.getModuleName();
		var baseCurrency = jQuery('input[name="base_currency"]').val();
		var recordId = jQuery('input[name="record"]').val();

		var moreCurrenciesContainer = jQuery('#moreCurrenciesContainer');
		var moreCurrenciesUi;
		moreCurrenciesUi = moreCurrenciesContainer.find('.multiCurrencyEditUI');

		if (moreCurrenciesUi.length == 0) {
			var params = {
				'module': moduleName,
				'view': "MoreCurrenciesList",
				'currency': baseCurrency,
				'record': recordId
			};

			app.request.get({data: params}).then(
				function(err, data) {
					if (data) {
						moreCurrenciesContainer.html(data);
						aDeferred.resolve(data);
					}
				});
		}
		else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	/*
	 * function to register events for more currencies link
	 */
	registerEventForMoreCurrencies: function() {
		var self = this;
		jQuery('#moreCurrencies').on('click', function(e) {
			app.helper.showProgress();
			self.getMoreCurrenciesUI().then(function(data) {
				app.helper.hideProgress();
				var moreCurrenciesUi = jQuery('#moreCurrenciesContainer').find('.multiCurrencyEditUI');
				if (moreCurrenciesUi.length > 0) {
					moreCurrenciesUi = moreCurrenciesUi.clone();
					var callback = function(data) {
						var form = data.find('#currencyContainer');
						form.vtValidate({
							submitHandler: function(form) {
								self.saveCurrencies();
								return false;
							}
						});
						self.baseCurrency = self.getUnitPrice().val();
						var multiCurrencyEditUI = jQuery('.multiCurrencyEditUI');
						self.multiCurrencyContainer = multiCurrencyEditUI;
						self.calculateConversionRate();
						self.registerEventForEnableCurrency();
						self.registerEventForEnableBaseCurrency();
						self.registerEventForResetCurrency();
						self.triggerForBaseCurrencyCalc();
					};
					var moreCurrenciesContainer = jQuery('#moreCurrenciesContainer').find('.multiCurrencyEditUI');
					var contentInsideForm = moreCurrenciesUi.find('.multiCurrencyContainer').html();
					moreCurrenciesUi.find('.multiCurrencyContainer').remove();
					var form = '<form id="currencyContainer"></form>'
					jQuery(form).insertAfter(moreCurrenciesUi.find('.modal-header'));
					moreCurrenciesUi.find('form').html(contentInsideForm);
					moreCurrenciesContainer.find('input[name^=curname]').each(function(index, element) {
						var dataValue = jQuery(element).val();
						var dataId = jQuery(element).attr('id');
						moreCurrenciesUi.find('#'+dataId).val(dataValue);
					});
					app.helper.showModal(moreCurrenciesUi, {cb: callback});

				}
			});
		});
	},
	saveCurrencies: function() {
		var thisInstance = this;
		var errorMessage;
		var form = jQuery('#currencyContainer');
		var editViewForm = thisInstance.getForm();
		var modalContainer = jQuery('.myModal');

		var enabledBaseCurrency = modalContainer.find('.enableCurrency').filter(':checked');
		if(enabledBaseCurrency.length < 1){
			errorMessage = app.vtranslate('JS_PLEASE_SELECT_BASE_CURRENCY_FOR_PRODUCT');
			app.helper.showErrorNotification({message: errorMessage});
			form.removeData('submit');
			return;
		}
		enabledBaseCurrency.attr('checked',"checked");
		modalContainer.find('.enableCurrency').filter(":not(:checked)").removeAttr('checked');
		var selectedBaseCurrency = modalContainer.find('.baseCurrency').filter(':checked');
		if(selectedBaseCurrency.length < 1){
			errorMessage = app.vtranslate('JS_PLEASE_ENABLE_BASE_CURRENCY_FOR_PRODUCT');
			app.helper.showErrorNotification({message: errorMessage});
			form.removeData('submit');
			return;
		}
		selectedBaseCurrency.attr('checked',"checked");
		modalContainer.find('.baseCurrency').filter(":not(:checked)").removeAttr('checked');

		var parentElem = selectedBaseCurrency.closest('tr');
		var currencySymbol = jQuery('.currencySymbol', parentElem).text();
		var convertedPrice = jQuery('.convertedPrice', parentElem).val();

		thisInstance.baseCurrencyName = parentElem.data('currencyId');
		thisInstance.baseCurrency = convertedPrice;

		thisInstance.getUnitPrice().val(thisInstance.baseCurrency);
		jQuery('input[name="base_currency"]', editViewForm).val(thisInstance.baseCurrencyName);
		jQuery('#baseCurrencySymbol', editViewForm).text(currencySymbol);

		var savedValuesOfMultiCurrency = modalContainer.find('.currencyContent').html();
		var moreCurrenciesContainer = jQuery('#moreCurrenciesContainer');
		moreCurrenciesContainer.find('.currencyContent').html(savedValuesOfMultiCurrency);
		modalContainer.find('input[name^=curname]').each(function(index, element) {
			var dataValue = jQuery(element).val();
			var dataId = jQuery(element).attr('id');
			moreCurrenciesContainer.find('.currencyContent').find('#' + dataId).val(dataValue);
		});
		app.helper.hideModal();
	},

	calculateConversionRate: function() {
		var container = jQuery('.multiCurrencyEditUI:visible');
		var baseCurrencyRow = container.find('.baseCurrency').filter(':checked').closest('tr');
		var baseCurrencyConvestationRate = baseCurrencyRow.find('.conversionRate');
		//if basecurrency has conversation rate as 1 then you dont have calculate conversation rate
		if (baseCurrencyConvestationRate.val() == "1") {
			return;
		}
		var baseCurrencyRatePrevValue = baseCurrencyConvestationRate.val();

		container.find('.conversionRate').each(function(key, domElement) {
			var element = jQuery(domElement);
			if (!element.is(baseCurrencyConvestationRate)) {
				var prevValue = element.val();
				element.val((prevValue / baseCurrencyRatePrevValue));
			}
		});
		baseCurrencyConvestationRate.val("1");
	},
	/**
	 * Function to register event for enabling currency on checkbox checked
	 */
	registerEventForEnableCurrency: function() {
		var container = this.getMoreCurrenciesContainer();
		var thisInstance = this;
		jQuery(container).on('change', '.enableCurrency', function(e) {
			var elem = thisInstance.getCurrentElem(e);
			var parentRow = elem.closest('tr');

			if (elem.is(':checked')) {
				elem.prop('checked', true);
				var conversionRate = jQuery('.conversionRate', parentRow).val();
				var unitPriceFieldData = thisInstance.getUnitPrice().data();
				var unitPrice = thisInstance.getDataBaseFormatUnitPrice();
				var price = parseFloat(unitPrice) * parseFloat(conversionRate);
				jQuery('input', parentRow).prop('disabled', true).removeAttr('disabled');
				jQuery('button.currencyReset', parentRow).prop('disabled', true).removeAttr('disabled');
				var userPreferredDecimalPlaces = unitPriceFieldData.numberOfDecimalPlaces;
				price = price.toFixed(userPreferredDecimalPlaces);
				var calculatedPrice = price.toString().replace('.', unitPriceFieldData.decimalSeparator);
				jQuery('input.convertedPrice', parentRow).val(calculatedPrice)
			} else {
				var baseCurrency = jQuery('.baseCurrency', parentRow);
				if (baseCurrency.is(':checked')) {
					var currencyName = jQuery('.currencyName', parentRow).text();
					var errorMessage = app.vtranslate('JS_BASE_CURRENCY_CHANGED_TO_DISABLE_CURRENCY') + '"' + currencyName + '"';
					app.helper.showErrorNotification({message: errorMessage});
					elem.prop('checked', true);
					return;
				}
				jQuery('input', parentRow).prop('disabled', true);
				jQuery('input.enableCurrency',parentRow).removeAttr('disabled');
				jQuery('button.currencyReset', parentRow).attr('disabled', 'disabled');
			}
		})
		return this;
	},
	/**
	 * Function to register event for enabling base currency on radio button clicked
	 */
	registerEventForEnableBaseCurrency: function() {
		var container = this.getMoreCurrenciesContainer();
		var thisInstance = this;
		jQuery(container).on('change', '.baseCurrency', function(e) {
			var elem = thisInstance.getCurrentElem(e);
			var parentElem = elem.closest('tr');
			if (elem.is(':checked')) {
				var convertedPrice = jQuery('.convertedPrice', parentElem).val();
				thisInstance.baseCurrencyName = parentElem.data('currencyId');
				thisInstance.baseCurrency = convertedPrice;

				var elementsList = jQuery('.enableCurrency', container);
				jQuery.each(elementsList, function(index, element) {
					var ele = jQuery(element);
					var parentRow = ele.closest('tr');

					if (ele.is(':checked')) {
						jQuery('button.currencyReset', parentRow).removeAttr('disabled');
					}
				});
				jQuery('button.currencyReset', parentElem).attr('disabled', 'disabled');
				thisInstance.calculateConversionRate();
			}
		});

		var baseCurrencyEle = container.find('.baseCurrency').filter(':checked');
		var parentElem = baseCurrencyEle.closest('tr');
		jQuery('button.currencyReset', parentElem).attr('disabled', 'disabled');

		return this;
	},
	/**
	 * Function to register event for reseting the currencies
	 */
	registerEventForResetCurrency: function() {
		var container = this.getMoreCurrenciesContainer();
		var thisInstance = this;
		jQuery(container).on('click', '.currencyReset', function(e) {
			var parentElem = thisInstance.getCurrentElem(e).closest('tr');
			var unitPriceFieldData = thisInstance.getUnitPrice().data();
			var unitPrice = thisInstance.getDataBaseFormatUnitPrice();
			var conversionRate = jQuery('.conversionRate', parentElem).val();
			var price = parseFloat(unitPrice) * parseFloat(conversionRate);
			var userPreferredDecimalPlaces = unitPriceFieldData.numberOfDecimalPlaces;
			price = price.toFixed(userPreferredDecimalPlaces);
			var calculatedPrice = price.toString().replace('.', unitPriceFieldData.decimalSeparator);
			jQuery('.convertedPrice', parentElem).val(calculatedPrice);
		});
		return this;
	},
	/**
	 * Function to calculate base currency price value if unit
	 * present on click of more currencies
	 */
	triggerForBaseCurrencyCalc: function() {
		var multiCurrencyEditUI = this.getMoreCurrenciesContainer();
		var baseCurrency = multiCurrencyEditUI.find('.enableCurrency');
		jQuery.each(baseCurrency, function(key, val) {
			if (jQuery(val).is(':checked')) {
				var baseCurrencyRow = jQuery(val).closest('tr');
				var unitPrice = jQuery('.unitPrice');
				var isPriceChanged = unitPrice.data('isPriceChanged');
				if (isPriceChanged) {
					var changedUnitPrice = unitPrice.val();
					baseCurrencyRow.find('.convertedPrice').val(changedUnitPrice);
					baseCurrencyRow.find('.currencyReset').trigger('click');
				}
				if (parseFloat(baseCurrencyRow.find('.convertedPrice').val()) == 0) {
					baseCurrencyRow.find('.currencyReset').trigger('click');
				}
			} else {
				var baseCurrencyRow = jQuery(val).closest('tr');
				baseCurrencyRow.find('.convertedPrice').val('');
			}
		});
	},
	/**
	 * Function to register onchange event for unit price
	 */
	registerEventForUnitPrice: function() {
		var unitPrice = this.getUnitPrice();
		unitPrice.on('focusout', function() {
			var oldValue = unitPrice.data('oldValue');
			if (oldValue != unitPrice.val()) {
				unitPrice.data('isPriceChanged', true);
			}
		})
	},
	issetInActivationMessage: false,
	registerRecordPreSaveEvent: function(form) {
		var self = this;
		if (typeof form == 'undefined') {
			form = this.getForm();
		}
		app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function(e, data) {
			var isActiveEle = form.find('input[name="discontinued"]');
			var recordId = jQuery('input[name="record"]').val();

			if (isActiveEle.length > 0 && recordId.length > 0 && self.issetInActivationMessage == false) {
				var selectedIds = new Array();
				selectedIds.push(recordId);

				var isActive = isActiveEle.is(':checked');
				if (isActive == false) {
					e.preventDefault();
					var params = {
						'module': self.getModuleName(),
						'selected_ids': selectedIds
					};

					Products_Edit_Js.getMessageForChildProductDeletionOrInActivation(params).then(function(message) {
						if (message != '') {
							app.helper.showConfirmationBox({'message': message}).then(
								function(data) {
									self.checkMoreCurrenciesUI(e, form);
									self.issetInActivationMessage = true;
								},
								function(error, err) {
									self.issetInActivationMessage = false;
									form.removeData('submit');
								}
								);
						} else {
							self.checkMoreCurrenciesUI(e, form);
							self.issetInActivationMessage = true;
						}
					});
				} else {
					self.checkMoreCurrenciesUI(e, form);
				}
			} else {
				self.checkMoreCurrenciesUI(e, form);
			}
		})
	},
	checkMoreCurrenciesUI: function(e, form) {
		var thisInstance = this;
		var multiCurrencyContent = jQuery('#moreCurrenciesContainer').find('.currencyContent');
		var unitPrice = thisInstance.getUnitPrice();
		if ((multiCurrencyContent.length < 1) && (unitPrice.length > 0)) {
			e.preventDefault();
			thisInstance.getMoreCurrenciesUI().then(function(data) {
				thisInstance.preSaveConfigOfForm(form);
				form.submit();
			})
		} else if (multiCurrencyContent.length > 0) {
			thisInstance.preSaveConfigOfForm(form);
		}
	},
	/**
	 * Function to handle settings before save of record
	 */
	preSaveConfigOfForm: function(form) {
		var unitPrice = this.getUnitPrice();
		if (unitPrice.length > 0) {
			var unitPriceValue = unitPrice.val();
			var baseCurrencyName = form.find('[name="base_currency"]').val();
			form.find('[name="' + baseCurrencyName + '"]').val(unitPriceValue);
			form.find('#requstedUnitPrice').attr('name', baseCurrencyName).val(unitPriceValue);
		}
	},
	registerTaxEvents : function(container) {
		app.helper.showScroll(jQuery('.regionsList'), {'height':'100px'});
		container.on('change','.taxes', function(e){
			var element = jQuery(e.currentTarget);
			var taxIdSelector = element.data('taxName');
			if(element.is(":checked")) {
				jQuery('#'+taxIdSelector).removeClass('hide').addClass('show');
			}else{
				jQuery('#'+taxIdSelector).removeClass('show').addClass('hide');
			}
		});
	},
        registerImageChangeEvent : function() {
            
        },
	registerBasicEvents : function(container) {
            this._super(container);
            this.registerTaxEvents(container);
            this.registerEventForMoreCurrencies();
            this.registerEventForUnitPrice();
            this.registerRecordPreSaveEvent();
	},
})

