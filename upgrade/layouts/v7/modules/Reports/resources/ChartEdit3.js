/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit3_Js("Reports_ChartEdit3_Js",{

	registerFieldForChosen : function() {
		vtUtils.showSelect2ElementView(jQuery('#groupbyfield'));
		vtUtils.showSelect2ElementView(jQuery('#datafields'));
	},

	initSelectValues : function() {
		var groupByField = jQuery('#groupbyfield');
		var dataFields = jQuery('#datafields');

		var groupByFieldValue = jQuery('input[name=groupbyfield]').val();
		var dataFieldsValue = jQuery('input[name=datafields]').val();

		var groupByHTML = jQuery('#groupbyfield_element').clone().html();
		var dataFieldsHTML = jQuery('#datafields_element').clone().html();

		groupByField.html(groupByHTML);
		dataFields.html(dataFieldsHTML);

		if(dataFieldsValue)
			dataFieldsValue = JSON.parse(dataFieldsValue);

		var selectedChartType = jQuery('input[name=charttype]').val();

		groupByField.select2().select2("val", groupByFieldValue);

		if(selectedChartType == 'pieChart') {
			if(!dataFieldsValue){
				dataFieldsValue = dataFields.find('option:first').val();
			}
			dataFields.attr('multiple', false).select2().select2("val", dataFieldsValue[0]);
		} else if(dataFieldsValue && dataFieldsValue[0]) {
			dataFields.attr('multiple', true).select2({maximumSelectionSize: 3}).select2("val", dataFieldsValue);
		}

		if(selectedChartType) {
			jQuery('ul[name=charttab] li.active').removeClass('active');
			jQuery('ul[name=charttab] li a[data-type='+selectedChartType+']').addClass('active contentsBackground backgroundColor').trigger('click');
		} else {
			jQuery('ul[name=charttab] li a[data-type=pieChart]').addClass('contentsBackground backgroundColor').trigger('click'); // by default piechart should be selected
		}

		var primaryModule = jQuery('input[name="primary_module"]').val();
		var inventoryModules = ['Invoice', 'Quotes', 'PurchaseOrder', 'SalesOrder'];
		var secodaryModules = jQuery('input[name="secondary_modules"]').val();
		var secondaryIsInventory = false;
		inventoryModules.forEach(function (entry) {
			if (secodaryModules.indexOf(entry) != -1) {
				secondaryIsInventory = true;
			}
		});
		if ((jQuery.inArray(primaryModule, inventoryModules) !== -1 || secondaryIsInventory) && selectedChartType !== 'pieChart') {
			var reg = new RegExp(/vtiger_inventoryproductrel*/);
			if (dataFields.val() && reg.test(dataFields.val())) {
				jQuery('#datafields option').not('[value^="vtiger_inventoryproductrel"]').remove();
			} else {
				jQuery('#datafields option[value^="vtiger_inventoryproductrel"]').remove();
			}
		}
	}

},{
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#chart_report_step3');
		}
		if(container.is('#chart_report_step3')) {
			this.setContainer(container);
		} else {
			this.setContainer(jQuery('#chart_report_step3'));
		}
	},

	registerForChartTabClick : function() {
		var dataFields = jQuery('#datafields');
		var thisInstance = this;

		jQuery('ul[name=charttab] li a').on('click', function(e){
			var chartType = jQuery(e.currentTarget).data('type');
			if(chartType == 'pieChart') {
				var dataFieldsValue = dataFields.val();
				var dataFieldsHTML = jQuery('#datafields_element').clone().html();
				dataFields.html(dataFieldsHTML);
				if(!dataFieldsValue){
					dataFieldsValue = dataFields.find('option:first').val();
				}
				dataFields.attr('multiple', false).select2().select2("val",dataFieldsValue);
			} else {
				if (thisInstance.isInventoryModule) {
					var reg = new RegExp(/vtiger_inventoryproductrel*/);
					if (dataFields.val() && reg.test(dataFields.val())) {
						jQuery('#datafields option').not('[value^="vtiger_inventoryproductrel"]').remove();
					} else {
						jQuery('#datafields option[value^="vtiger_inventoryproductrel"]').remove();
					}
				}
				dataFields.attr('multiple', true).select2({maximumSelectionSize: 3});
			}
			jQuery('input[name=charttype]').val(chartType);
			jQuery('ul[name=charttab] li.active a').removeClass('contentsBackground backgroundColor');
			jQuery(this).addClass('contentsBackground backgroundColor');
		});
	},
    
     calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = jQuery('#advanced_filter','#chart_report_step2').val();// value from step2
		jQuery('#advanced_filter','#chart_report_step3').val(advfilterlist);
	},

	registerSubmitEvent : function() {
		var thisInstance = this;
		jQuery('#generateReport').on('click', function(e) {
			var legend = jQuery('#groupbyfield').val();
			var sector = jQuery('#datafields').val();
			var form = thisInstance.getContainer();
			if(sector && legend) {
				vtUtils.hideValidationMessage(jQuery('#s2id_groupbyfield'));
				vtUtils.hideValidationMessage(jQuery('#s2id_datafields'));
				form.submit();
			} else if(!legend){
				vtUtils.showValidationMessage(jQuery('#s2id_groupbyfield'), app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'));
				e.preventDefault();
			}else if(!sector){
				vtUtils.showValidationMessage(jQuery('#s2id_datafields'), app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'));
				e.preventDefault();
			}
		});
	},

    /**
	 * Function is used to limit the calculation for line item fields and inventory module fields.
	 * only one of these fields can be used at a time
	 */
	lineItemCalculationLimit: function () {
		var thisInstance = this;
		var dataFields = jQuery('#datafields');
		if (thisInstance.isInventoryModule()) {
			dataFields.on('change', function (e) {
				var value = dataFields.val();
				var reg = new RegExp(/vtiger_inventoryproductrel*/);
				var selectedChartType = jQuery('input[name=charttype]').val();
				if (selectedChartType !== 'pieChart') {
					if (value && value.length > 0) {
						if (reg.test(value)) {
							// line item field selected remove module fields
							jQuery('#datafields option').not('[value^="vtiger_inventoryproductrel"]').remove();
						} else {
							jQuery('#datafields option[value^="vtiger_inventoryproductrel"]').remove();
						}
					} else {
						//If nothing is selected then reset it.
						var dataFieldsHTML = jQuery('#datafields_element').clone().html();
						dataFields.html(dataFieldsHTML);
					}
					thisInstance.displayLineItemFieldLimitationMessage();
				}
			});
		}
	},

	isInventoryModule: function () {
		var primaryModule = jQuery('input[name="primary_module"]').val();
		var inventoryModules = ['Invoice', 'Quotes', 'PurchaseOrder', 'SalesOrder'];
		// To limit the calculation fields if secondary module contains inventoryModule
		var secodaryModules = jQuery('input[name="secondary_modules"]').val();
		var secondaryIsInventory = false;
		inventoryModules.forEach(function (entry) {
			if (secodaryModules.indexOf(entry) != -1) {
				secondaryIsInventory = true;
			}
		});
		if (jQuery.inArray(primaryModule, inventoryModules) !== -1 || secondaryIsInventory) {
			return true;
		} else {
			return false;
		}
	},

	displayLineItemFieldLimitationMessage: function () {
		var message = app.vtranslate('JS_CALCULATION_LINE_ITEM_FIELDS_SELECTION_LIMITATION');
		if (jQuery('#calculationLimitationMessage').length == 0) {
			jQuery('#datafields').parent().append('<div id="calculationLimitationMessage" class="alert alert-info">' + message + '</div>');
		} else {
			jQuery('#calculationLimitationMessage').html(message);
		}
	},

	registerEvents : function(){
		this._super();
		this.calculateValues();
		this.registerForChartTabClick();
		this.lineItemCalculationLimit();
		Reports_ChartEdit3_Js.registerFieldForChosen();
		Reports_ChartEdit3_Js.initSelectValues();
	}
});