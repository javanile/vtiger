/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.validator.addMethod("date", function(value, element, params) {
		try {
			if(value) {
				app.helper.getDateInstance(value, app.getDateFormat(),'date');
			}
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("datetime", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("reference", function(value, element, params) {
		return true;
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("double", function(value, element, params) {
		element = jQuery(element);

		var groupSeparator = app.getGroupingSeparator();
		var decimalSeparator = app.getDecimalSeparator();

		var strippedValue = value.replace(decimalSeparator, '');
		var spacePattern = /\s/;
		if(spacePattern.test(decimalSeparator) || spacePattern.test(groupSeparator)) {
			strippedValue = strippedValue.replace(/ /g, '');
		}
		if(groupSeparator === "$"){
			groupSeparator = "\\$";
		}
		//Replace all occurence of groupSeparator with ''.
		var regex = new RegExp(groupSeparator,'g');
		strippedValue = strippedValue.replace(regex, '');

		if(isNaN(strippedValue)) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_VALUE'))
);



jQuery.validator.addMethod("WholeNumber", function(value, element, params) {
		var regex= /[^+\-0-9.]+/; // not number?
		
		if(value.match(regex)){
			return false;
		}

		if((value % 1) != 0){  // is decimal?
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('INVALID_NUMBER'))
);
/*
 * Experimental
 * 
jQuery.Class('Calendar_greaterThanDependentField_Validator', {}, {
	validate : function(value, element, params) {
		console.log("value : ",value);
		console.log("element : ",element);
	}
});

jQuery.Class('Vtiger_Validations_Helper', {
	getInstance : function() {
		return new Vtiger_Validations_Helper();
	}
},{ 
	getModuleSpecificValidatorClassName : function(validatorName) {
		var moduleName = app.getModuleName();
		var moduleSpecificClassName = moduleName + '_' + validatorName + '_Validator';
		return moduleSpecificClassName;
	},

	isModuleSpecificValidatorExists : function(validatorName) {
		var moduleSpecificClassName = this.getModuleSpecificValidatorClassName(validatorName);
		if(typeof window[moduleSpecificClassName] !== 'undefined') {
			return true;
		}
		return false;
	},

	getModuleSpecificValidatorInstance : function(validatorName) {
		var moduleSpecificValidatorClassName = this.getModuleSpecificValidatorClassName(validatorName);
		return new window[moduleSpecificValidatorClassName];
	}
});

jQuery.validator.addMethod("greaterThanDependentField", function(value, element, params) {
	var helper = Vtiger_Validations_Helper.getInstance();
	var validatorName = 'greaterThanDependentField';
	if(helper.isModuleSpecificValidatorExists(validatorName)) {
		var validatorInstance = helper.getModuleSpecificValidatorInstance(validatorName);
		return validatorInstance.validate(value, element, params);
	}
	//fallback to generic validation
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);
*/

jQuery.validator.addMethod("Calendar_greaterThanDependentField", function(value, element, params) {
		var form = jQuery(element).closest('form');
		var startDateElement = form.find('[name="date_start"]');
		var startTimeElement = form.find('[name="time_start"]');
		var endDateElement = form.find('[name="due_date"]');
		var endTimeElement = form.find('[name="time_end"]');

		var dateFormat = app.getDateFormat();
		var hourFormat = app.getHourFormat();

		//converting to moment time format
		var timeFormat = 'HH:mm';
		if(hourFormat === 12) {
			timeFormat = 'hh:mm A';
		}

		var startDate = startDateElement.val();
		var endDate = endDateElement.val();
		var startTime = '00:00';
		var endTime = '23:59';
		if(hourFormat === 12) {
			startTime = '12:00 AM';
			endTime = '11:59 PM';
		}
		if(startTimeElement.length) {
			startTime = startTimeElement.val();
		}
		if(endTimeElement.length) {
			endTime = endTimeElement.val();
		}


		var momentFormat = dateFormat.toUpperCase() + ' ' +timeFormat;
		var m1 = moment(startDate + ' ' + startTime, momentFormat);
		var m2 = moment(endDate + ' ' + endTime, momentFormat);
		return m1.unix() < m2.unix();
	}, jQuery.validator.format(app.vtranslate('JS_CHECK_START_AND_END_DATE_SHOULD_BE_GREATER'))
);

jQuery.validator.addMethod("greaterThanDependentField", function(value, element, params) {
		var result = true;
		if(!value.length) {
			return result;
		}

		var validationMeta = this.settings.validationMeta;
		if (typeof validationMeta != 'undefined') {
			var meta = validationMeta;
		} else if (typeof uimeta != 'undefined') {
			var meta = uimeta;
		} else {
			var meta = quickcreate_uimeta;
		}

		var sourceField = jQuery(element);
		var sourceFieldInfo = meta.field.get(sourceField.attr('name'));
		var depFieldName = params[0];
		var depFieldInfo = meta.field.get(depFieldName);

		//Remove this once uimeta cleanup is done
		if(typeof sourceFieldInfo == 'undefined' || typeof depFieldInfo == 'undefined')
			return result;

		if((sourceFieldInfo.type !== 'date' || depFieldInfo.type !== 'date') 
				&& (sourceFieldInfo.type !== 'datetime' || depFieldInfo.type !== 'datetime')) { //start_date, end_date fields data type is datetime.
			console.log('greaterThanDependentField() validation method should be used for date fields only');
			return result;
		}

		//To avoid conflicts in selecting target element in UI first select the closest
		//edit view form and then call controller specific field value if parent fails.
		//eg: Perform this validation method when quick create a module record within same module
		//list view
		var recordEditViewForm = sourceField.closest('form.recordEditView');
		if(recordEditViewForm.length > 0){
			var closestForm = sourceField.closest('form.recordEditView').first();
		}else{
			var closestForm = sourceField.closest('form#detailView');
		}
		if(closestForm.length > 0) {
			var depFieldVal = closestForm.find('[name="'+depFieldName+'"]').val();
			if(typeof depFieldVal === 'undefined'){
				var controller = app.controller();
				depFieldVal=jQuery('.fieldBasicData').filter('[data-name="'+depFieldName+'"]').data('displayvalue');
			}
		}else{
			var controller = app.controller();
			var depFieldVal = controller.getFieldValue(depFieldName,sourceField);
		}

		if(typeof depFieldVal === 'undefined') {
			return result;
		}

		var dateFormat = app.getDateFormat();
		var m1 = moment(value,dateFormat.toUpperCase());
		var m2 = moment(depFieldVal,dateFormat.toUpperCase());

		//To ignore validation when dependent field value is empty
		if(depFieldVal.trim().length <= 0 ) {
			return true;
		}

		result = m1.unix() >= m2.unix(); 

		jQuery.validator.messages.greaterThanDependentField = sourceFieldInfo.label+' '+app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO')+' '+depFieldInfo.label+'';
	return result;
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_VALUE'))
);

jQuery.validator.addMethod("currency", function(value, element, params) {
	element = jQuery(element);
	var groupSeparator = app.getGroupingSeparator();
	var decimalSeparator = app.getDecimalSeparator();

	var strippedValue = value.replace(decimalSeparator, '');
	var spacePattern = /\s/;
	if(spacePattern.test(decimalSeparator) || spacePattern.test(groupSeparator)) {
		strippedValue = strippedValue.replace(/ /g, '');
	}

	if(groupSeparator === "$"){
		groupSeparator = "\\$"
	}
	var regex = new RegExp(groupSeparator,'g');
	strippedValue = strippedValue.replace(regex, '');
	if(isNaN(strippedValue)){
		return false;
	}
	if(strippedValue < 0){
		return false;
	}
	return true;
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_VALUE'))
);

jQuery.validator.addMethod("currencyList", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the valid value")
);

jQuery.validator.addMethod("integer", function(value, element, params) {
		var integerRegex= /(^[-+]?\d+)$/ ;
		var decimalIntegerRegex = /(^[-+]?\d*).\d+$/ ;
		if (value.length && (!value.match(integerRegex))) {
			if(!value.match(decimalIntegerRegex)){
				return false;
			} else {
				return true;
			}
		} else{
			return true;
		}
	}, jQuery.validator.format(app.vtranslate("JS_PLEASE_ENTER_INTEGER_VALUE"))
);

jQuery.validator.addMethod("boolean", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the valid value")
);

jQuery.validator.addMethod("ReferenceField", function(value, element, params) {
		return true;
	}, jQuery.validator.format("Please enter the correctvalue")
);

jQuery.validator.addMethod("owner", function(value, element, params) {
		return true;
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("check-filter-duplicate", function (value, element, params) {
		var duplicateExist = false;
		var recordId = jQuery(element).data('recordId');
		for (var id in params) {
			var filterName = params[id];
			if (recordId != id && filterName.toLowerCase() == value.toLowerCase().trim()) {
				duplicateExist = true;
				return false;
			}
		}
		if (duplicateExist == false) {
			return true;
		}
	}, jQuery.validator.format(app.vtranslate('JS_VIEW_ALREADY_EXISTS'))
);

jQuery.validator.addMethod("time", function(value, element, params) {
		element = jQuery(element);
		if(!value) return true;
		try {
			var fieldValue = value;
			var time = fieldValue.replace(fieldValue.match(/[AP]M/i),'');
			var timeValue = time.split(":");
			var dateformat = element.data('format');

			if(timeValue.length != 2 || isNaN(timeValue[0]) || isNaN(timeValue[1])
				|| timeValue[0] > dateformat || timeValue[1] > 59) {
				return false;
			}
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_TIME'))
);

jQuery.validator.addMethod("email", function(value, element, params) {
		value = value.trim();
		var emailFilter = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`'{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\.]?[a-zA-Z0-9\-]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;

		if(!value) return true;

		if (!emailFilter.test(value)) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_EMAIL_ADDRESS'))
);

jQuery.validator.addMethod("multiEmails", function(value, element, params) {
		var emailFilter = /^[_/a-zA-Z0-9*]+([!"#$%&'()*+,./:;<=>?\^_`'{|}~-]?[a-zA-Z0-9/_/-])*@[a-zA-Z0-9]+([\_\.]?[a-zA-Z0-9\-]+)*\.([\-\_]?[a-zA-Z0-9])+(\.?[a-zA-Z0-9]+)?$/;

		if(!value) return true;
		var fieldValuesList = value.split(',');
		for (var i in fieldValuesList) {
			var splittedFieldValue = fieldValuesList[i];
			splittedFieldValue = splittedFieldValue.trim();
			var response = emailFilter.test(splittedFieldValue);
			if(response != true) {
				return false;
			}
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_EMAIL_ADDRESS'))
);

jQuery.validator.addMethod("illegal", function(value, element, params) {
		var illegalChars= /[\(\)\<\>\,\;\:\\\\"\[\]\'\/\`\&]/;
		//allow apostrophe
		if (jQuery(element).attr('data-rule-email')) {
			illegalChars = /[\(\)\<\>\,\;\:\\\\"\[\]\/\`\&]/;
		}
		if (value.match(illegalChars)) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_CONTAINS_ILLEGAL_CHARACTERS'))
);

jQuery.validator.addMethod("salutation", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("multipicklist", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("string", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("text", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("picklist", function(value, element, params) {
		try {
			var specialChars = /(\<|\>)/gi ;
			if (specialChars.test(value)) {
				return false;
			}
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format(app.vtranslate('JS_SPECIAL_CHARACTERS')+" < >"+app.vtranslate('JS_NOT_ALLOWED'))
);

jQuery.validator.addMethod("phone", function(value, element, params) {
		try {
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("url", function(value, element, params) {
		try {
			value = value.trim();
			if(!value) return true;

			var regexp = /(^|\s)((https?:\/\/)?[\w-]+(\.[\w-]+)+\.?(:\d+)?(\/\S*)?)/gi;
			var result = regexp.test(value);
			if (!result) {
				return false;
			}
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format(app.vtranslate('JS_INVALID_URL'))
);

jQuery.validator.addMethod("lessThanToday", function(value, element, params) {
		try {
			if(value) {
				var fieldDateInstance = app.helper.getDateInstance(value, app.getDateFormat());
				fieldDateInstance.setHours(0,0,0,0);
				var todayDateInstance = new Date();
				todayDateInstance.setHours(0,0,0,0);
				var comparedDateVal = todayDateInstance - fieldDateInstance;
				if(comparedDateVal <= 0){
					return false;
				}
			}
			return true;
		} catch(err) {
			return false;
		}
	}, function(params, element) {
		return app.vtranslate('JS_SHOULD_BE_LESS_THAN_CURRENT_DATE');
	}
);

jQuery.validator.addMethod("lessThanOrEqualToToday", function(value, element, params) {
		try {
			if(value) {
				var fieldDateInstance = app.helper.getDateInstance(value, app.getDateFormat());
				fieldDateInstance.setHours(0,0,0,0);
				var todayDateInstance = new Date();
				todayDateInstance.setHours(0,0,0,0);
				var comparedDateVal = todayDateInstance - fieldDateInstance;
				if(comparedDateVal < 0){
					return false;
				}
			}
			return true;
		} catch(err) {
			return false;
		}
	}, function(params, element) {
		return app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO')+" "+app.vtranslate('JS_CURRENT_DATE');
	}
);

jQuery.validator.addMethod("greaterThanOrEqualToToday", function(value, element, params) {
		try {
			if(value) {
				var fieldDateInstance = app.helper.getDateInstance(value, app.getDateFormat());
				fieldDateInstance.setHours(0,0,0,0);
				var todayDateInstance = new Date();
				todayDateInstance.setHours(0,0,0,0);
				var comparedDateVal = fieldDateInstance - todayDateInstance;
				if(comparedDateVal < 0){
					return false;
				}
			}
			return true;
		} catch(err) {
			return false;
		}
	}, function(params, element) {
		return app.vtranslate('JS_SHOULD_BE_GREATER_THAN_OR_EQUAL_TO')+" "+app.vtranslate('JS_CURRENT_DATE');
	}
);

jQuery.validator.addMethod("lessThanDependentField", function(value, element, params) {
		var result = true; 
		if(!value.length) { 

			return result; 
		} 

		var validationMeta = this.settings.validationMeta;
		if (typeof validationMeta != 'undefined') {
			var meta = validationMeta;
		} else if (typeof uimeta != 'undefined') {
			var meta = uimeta;
		} else {
			var meta = quickcreate_uimeta;
		}

		var sourceField = jQuery(element); 
		var sourceFieldInfo = meta.field.get(sourceField.attr('name')); 
		var depFieldName = params[0];
		var depFieldInfo = meta.field.get(depFieldName);

		//Remove this once uimeta cleanup is done 
		if(typeof sourceFieldInfo == 'undefined' || typeof depFieldInfo == 'undefined') 
			return result; 

		if(sourceFieldInfo.type !== 'date' || depFieldInfo.type !== 'date') { 
			return result; 
		} 

		//To avoid conflicts in selecting target element in UI first select the closest 
		//edit view form and then call controller specific field value if parent fails. 
		//eg: Perform this validation method when quick create a module record within same module 
		//list view 
		var closestForm = sourceField.closest('form.recordEditView').first(); 
		var depFieldDateFormat = app.getDateFormat();
		if(closestForm.length > 0) { 
			var depFieldVal = closestForm.find('[name="'+depFieldName+'"]').val(); 
		}else{ 
			var controller = app.controller(); 
			var depFieldVal = jQuery('.fieldBasicData').filter('[data-name="'+depFieldName+'"]').data('displayvalue');
			//depFieldVal for date field will be in yyyy-mm-dd format. If user format is other than yyyy-mm-dd moment function
			//generates wrong date and validation fails. For example ajax edit of Support Start Date in Contact detail view,
			//depFieldVal will be in yyyy-mm-dd format.
			if(depFieldVal) {
				var depFieldValParts = depFieldVal.split('-');
				if(depFieldValParts[0].length == 4) {
					depFieldDateFormat = 'yyyy-mm-dd';
				}
			}
		} 
		if(typeof depFieldVal === 'undefined' || depFieldVal == '') { 
			return result; 
		} 
		var dateFormat = app.getDateFormat(); 
		var m1 = moment(value,dateFormat.toUpperCase()); 
		var m2 = moment(depFieldVal,depFieldDateFormat.toUpperCase()); 
		result = m1.unix() <= m2.unix();

		jQuery.validator.messages.lessThanDependentField = sourceFieldInfo.label+' '+app.vtranslate('JS_SHOULD_BE_LESS_THAN_OR_EQUAL_TO')+' '+depFieldInfo.label+''; 
		return result; 
	}, jQuery.validator.format("Please enter the correct date")
);

jQuery.validator.addMethod("futureEventCannotBeHeld", function(value, element, params) {
		try {
			if(value == "Held"){
				var sourceField = jQuery(element);
				var formElem = sourceField.closest('form');
				for(var i=0; i<params.length; i++){
					var dependentField = params[i];
					var dependentFieldInContext = jQuery('input[name='+dependentField+']',formElem);
					if(dependentFieldInContext.length > 0){
						var todayDateInstance = new Date();
						var dateFormat = dependentFieldInContext.data('date-format');
						var time = jQuery('input[name=time_start]',formElem);
						var fieldValue = dependentFieldInContext.val()+" "+time.val();
						var dependentFieldDateInstance = app.helper.getDateInstance(fieldValue,dateFormat);
						var comparedDateVal = todayDateInstance - dependentFieldDateInstance;
						if(comparedDateVal < 0){
							return false;
						}
					}
				}
			}
			return true;
		} catch(err) {
			console.log(err);
			return false;
		}
	}, jQuery.validator.format('Status '+app.vtranslate('JS_FUTURE_EVENT_CANNOT_BE_HELD')+' Date Start')
);

jQuery.validator.addMethod("recurrence", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);

// Documents Module
jQuery.validator.addMethod("documentsFileUpload", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);

jQuery.validator.addMethod("fileLocationType", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);

jQuery.validator.addMethod("documentsFolder", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);

jQuery.validator.addMethod("maximumlength", function(value, element, params) {
		if(value > params) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_LENGTH_SHOULD_BE_LESS_THAN_EQUAL_TO') + ' {0}')
);

jQuery.validator.addMethod("maxsize", function(value, element, params) {
		if(value.length > params) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate('JS_MAX_ALLOWED_CHARACTERS') + ' {0}')
);

jQuery.validator.addMethod("range", function(value, element, params) {
		value = parseInt(value);
		if (value < params[0] || value > params[1]) {
			return false;
		}
		return true;
	}, jQuery.validator.format(app.vtranslate("JS_PLEASE_ENTER_NUMBER_IN_RANGE") + ' {0} ' + app.vtranslate("TO") + ' {1}' )
);

jQuery.validator.addMethod("positive", function(value, element, params){
	var negativeRegex= /(^[-]+\d+)$/ ;
	if(isNaN(value) || value < 0 || value.match(negativeRegex)){
		return false;
	}
	return true;
}, jQuery.validator.format(app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER')));

jQuery.validator.addMethod("positiveExcludingZero", function(value, element, params){
	var negativeRegex= /(^[-]+\d+)$/;
	if(isNaN(value) || value < 0 || value == 0 || value.match(negativeRegex)){
		return false;
	}
	return true;
}, jQuery.validator.format(app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO')));

jQuery.validator.addMethod("PositiveNumber",function(value,element,params){
	return jQuery.validator.methods.positive.call(this,value,element,params);
}, jQuery.validator.format(app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER')));

jQuery.validator.addMethod("percentage", function(value, element, params){
		var decimalSeparator = app.getDecimalSeparator();
		var strippedValue = value.replace(decimalSeparator, '');
		var spacePattern = /\s/;
		if(spacePattern.test(decimalSeparator)) {
			strippedValue = strippedValue.replace(/ /g, '');
		}
		if(isNaN(strippedValue)) {
			return false;
		}
		return true;
}, jQuery.validator.format(app.vtranslate('JS_PLEASE_ENTER_VALID_VALUE')));

jQuery.validator.addMethod("inventory_percentage", function(value, element, params){
	var valid = jQuery.validator.methods.percentage.call(this,value,element,params);
	if (valid) {
		jQuery.validator.messages.inventory_percentage = app.vtranslate('JS_PERCENTAGE_SHOULD_BE_LESS_THAN_100');
		return (value > 100) ? false : true;
	} else {
		jQuery.validator.messages.inventory_percentage = app.vtranslate('JS_ACCEPT_POSITIVE_NUMBER');
		return valid;
	}
}, jQuery.validator.format(app.vtranslate('JS_PERCENTAGE_SHOULD_BE_LESS_THAN_100')));

jQuery.validator.addMethod("greater_than_zero", function(value, element, params){
	return (value <= 0) ? false : true;
}, jQuery.validator.format(app.vtranslate('JS_VALUE_SHOULD_BE_GREATER_THAN_ZERO')));

// End

jQuery.validator.addMethod("RepeatMonthDate", function(value, element, params) {
	return true;
	}, jQuery.validator.format("Please enter the proper value")
);

jQuery.validator.addMethod("reference_required", function (value, element, params) {
	var referenceValue = jQuery(element).parent().parent().find('input.sourceField').val();
	if (isNaN(referenceValue)) {
		referenceValue = jQuery(element).parent().parent().find('input.sourceField').attr('value');
	}
	if (referenceValue && parseInt(referenceValue) > 0) {
		return true;
	} else {
		return false;
	}
}, jQuery.validator.format(app.vtranslate('JS_REQUIRED_FIELD')));

function validateAndSubmitForm (form, params, meta) {
	if(typeof meta === 'undefined' && typeof uimeta !== "undefined"){
		meta = uimeta;
	}
	if(typeof meta === 'undefined' && typeof adv_search_uimeta === "object") {
		meta = adv_search_uimeta;
	}

	form.vtValidate(params);

	try {
		 form.submit();
	} catch(err) {
		console.log(err);
	}
}

function calculateValidationRules(form,params,meta){
	var rules = {};
	if(meta===false){
		return rules;
	}
	if(typeof meta === 'undefined'){
		if(typeof uimeta === 'undefined') {
			//for non-entity modules return empty rules
			return rules;
		}else{
			if(jQuery('.overlayEdit').length > 0 || jQuery('.overlayDetail').length > 0) {
				meta = related_uimeta;
			} else {
				meta = uimeta;
			}
		 }
	}

	if(typeof params.ignoreTypes === 'undefined'){
		params.ignoreTypes = [];
	}
	var inputElements = 0;
	//There can be no form element for a view
	//calling elements on undefined function will
	//stop javascript from executing next code
	if(typeof form.get(0) != 'undefined'){
		inputElements = form.get(0).elements;
	}
	for(var i=0;i<inputElements.length;i++){
		var element = jQuery(inputElements[i]);
		var fieldName = element.attr('name');
		var fieldBasicInfo = meta.field.get(fieldName);
		if(typeof fieldBasicInfo !== 'undefined'){
			var ruleFieldName = fieldName;
			if(fieldBasicInfo['type'] === 'multipicklist') {
				ruleFieldName = fieldName+'[]';
			}
			rules[ruleFieldName] = {};
			if(fieldBasicInfo['mandatory'] === true) {
				if(params.ignoreTypes.indexOf('mandatory') === -1){
					rules[ruleFieldName]['required'] = true;
				}
			}
			if(fieldBasicInfo['type'] in jQuery.validator.methods){
				rules[ruleFieldName][fieldBasicInfo['type']] = true;
			}

			if(typeof fieldBasicInfo['validator']!=='undefined' && fieldBasicInfo['validator'].length > 0) {
				for(var j=0; j<fieldBasicInfo['validator'].length; j++) {
					var val = fieldBasicInfo['validator'][j];
					if(val['name'] in jQuery.validator.methods){
						if(typeof val['params'] !== 'undefined') {
							rules[ruleFieldName][val['name']] = val['params'];
						}else{
							rules[ruleFieldName][val['name']] = true;
						}
					}
				}
			}
		}
	}
	return rules;
}

(function ( $ ) {

	/**
	* Function to resolve validation data to rules
	* @param <jQuery> form
	* @returns Object
	*/
	function getResolvedRules(container, meta) {
		var basicRules = calculateValidationRules(container,{}, meta);
		var rules = {};
		var elements = container.find('[data-specific-rules]');
		elements.each(function(index,domElement) {
			var currentElement = jQuery(domElement);
			var fieldName = currentElement.attr('name');
			var specificRulesMeta = currentElement.data('specificRules');
			for(var key in specificRulesMeta) {
				var specialValidatorName = specificRulesMeta[key]['name'];
				var specialValidatorParams = specificRulesMeta[key]['params'];

				//providing fallback support
				var moduleName = container.find('[name="module"]').val();
				if(moduleName === 'Events') {
					moduleName = 'Calendar';
				}
				var moduleSpecificValidatorName = moduleName + '_' + specialValidatorName;
				if(moduleSpecificValidatorName in jQuery.validator.methods) {
					specialValidatorName = moduleSpecificValidatorName;
				}

				var validationParams = true;
				if(specialValidatorParams) {
					validationParams = specialValidatorParams;
				}
				if(typeof rules[fieldName] === 'undefined') {
					rules[fieldName] = {};
				}

				rules[fieldName][specialValidatorName] = validationParams;
			}
		});
		jQuery.extend(true,rules,basicRules);
		return rules;
	}

	$.fn.vtValidate = function(params) {
		if(!jQuery(this).is('form')) {
			try{
				throw new Error();
			}catch(err) {
				console.log(err);
			}
		}

		if(typeof params === 'undefined') {
			params = {};
		}

		var defaults = {
		'errorClass': 'input-error',
		'focusInvalid': true,
		//Refer for explanation on ignore attrbute https://github.com/select2/select2/issues/215
		//As element with class select2-input doesn't have name attribute is leading to issue
		'ignore' : ":hidden,.ignore-validation,.select2-input",
		errorPlacement: function(error, element) {
			if($(error).text()) {

				if(element.hasClass('select2')) {
					element = app.helper.getSelect2FromSelect(element);
				}

				var positionsConf = {
					my: 'bottom left',
					at: 'top left'
				};

				var positionContainer = element.closest('.editViewContents');
				if(element.closest('.editViewPageDiv').length){
					positionContainer = element[0];
				}
				if(!positionContainer.length) {
					if(element.closest('#overlayPageContent').length) {
						var overlayElement = element.closest('#overlayPageContent');
						positionsConf.adjust = {
							x: parseInt(overlayElement.css('margin-left'))
						};
						positionContainer = overlayElement;
					}

					if(element.closest('.slimScrollDiv').length) {
						positionContainer = element.closest('.slimScrollDiv');
					} else if(element.closest('.mCustomScrollbar').length) {
						positionContainer = element.closest('.mCSB_container');
					} else if(element.closest('.modal').length){
						positionContainer = element.closest('.modal');
					} else {
						positionContainer = element.closest('form');
					}
				}

				if(positionContainer.length) {
					positionsConf.container = positionContainer;
					if(element.closest('#overlayPageContent').length) {
						var overlayElement = element.closest('#overlayPageContent');
						positionsConf.adjust = {
							x: parseInt(overlayElement.css('margin-left'))
						};
						positionContainer = overlayElement;
					}
				}

				element.qtip({
					content: {
						text: $(error).text()
					},
					show: {
						event: 'Vtiger.Validation.Show.Messsage'
					},
					hide: {
						event: 'Vtiger.Validation.Hide.Messsage'
					},
					position: positionsConf,
					style: {
						classes: 'qtip-red qtip-shadow'
					},
					events : {
						render: function(event, api) {
							var tooltip = api.elements.tooltip;
							setTimeout(function() {
								tooltip.hide();
							}, 5000);
							tooltip.on('click', function(event, api) {
								tooltip.hide();
							});
						}
					}
				});
				element.trigger('Vtiger.Validation.Show.Messsage');
			}
			/*
			 * Experimental : Using tooltipster
			 *
			if(!element.hasClass('tooltipstered')) {
				element.tooltipster({
					trigger: 'custom',
					onlyOne: false,
					position: 'top-left',
					animation: 'fade',
					parent: element.closest('.editViewContents')
				});
			}

			if($(error).text()) {
				element.tooltipster('update', $(error).text());
				element.tooltipster('show');
			}

			*/
		},
		success: function (label, element) {
			element = $(element);
			if(element.prop('tagName') === 'SELECT') {
				element = app.helper.getSelect2FromSelect(element);
			}
			element.trigger('Vtiger.Validation.Hide.Messsage');
//			element.tooltipster('hide');
		},
		highlight: function (element, errorClass, validClass) {
			var elem = $(element);
			if (elem.hasClass("select2-offscreen")) {
				var select2Ele = app.helper.getSelect2FromSelect(elem);
				select2Ele.find("ul").addClass(errorClass);
				select2Ele.find('a.select2-choice').addClass(errorClass);
			} else {
				elem.addClass(errorClass);
			}
		},

		unhighlight: function (element, errorClass, validClass) {
			var elem = $(element);
			if (elem.hasClass("select2-offscreen")) {
				var select2Ele = app.helper.getSelect2FromSelect(elem);
				select2Ele.find('ul').removeClass(errorClass);
				select2Ele.find('a.select2-choice').removeClass(errorClass);
			} else {
				elem.removeClass(errorClass);
			}
		}

		/*,
		showErrors: function(errorMap, errorList) {
			$.each( this.successList , function(index, value) {
				$(value).popover('hide');
			});
			$.each( errorList , function(index, value) {
				var _popover = $(value.element).popover({
					trigger: 'manual',
					placement: 'top',
					content: value.message,
					template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"><p></p></div></div></div>'
				});
				_popover.data('bs.popover').options.content = value.message;
				$(value.element).popover('show');
			});
		},*/
		};

		var rules = params.rules;
		var resolvedRules = getResolvedRules(this, params.validationMeta);
		rules = jQuery.extend(true,rules,resolvedRules);
		params.rules = rules;

		var newParams = $.extend(defaults, params);
		if(this.data('validator')){
			// Note : Second time submit is submitting values of first submit (found in Ajax edit)
			// https://github.com/jzaefferer/jquery-validation/issues/214
			this.data('validator').settings.submitHandler = false;
			this.removeData('validator');
		}

		var validobj = this.validate(newParams);

//		$(document).one("change", ".select2-offscreen", function () {
//			if (!$.isEmptyObject(validobj.submitted)) {
//				validobj.form();
//			}
//		});

		$(document).on("select2-opening", function (arg) {
			var elem = $(arg.target);
			var select2Elem = app.helper.getSelect2FromSelect(elem);
			if (select2Elem.find("ul").hasClass("input-error") || select2Elem.find('a.select2-choice').hasClass("input-error")) {
				//jquery checks if nthe class exists before adding.
				$(".select2-drop").addClass("input-error");
				$(".select2-drop ul").addClass("input-error");
			} else {
				$(".select2-drop").removeClass("input-error");
				$(".select2-drop ul").removeClass("input-error");
			}

		});

		//invoke validation on change of select elements
		this.on('change','select', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			if(!jQuery.isEmptyObject(currentTarget.rules())) {
				currentTarget.valid();
			}
		});

		//invoke validation on date change
		this.on('changeDate', function(e) {
			var target = jQuery(e.target);
			if(!jQuery.isEmptyObject(target.rules())) {
				target.valid();
			}
		});

		//invoke validation on reference post selection
		this.on('Vtiger.PostReference.Selection Vtiger.PostReference.QuickCreateSave', function (e) {
			var referenceWrapper = jQuery(e.target).closest('.referencefield-wrapper');
			var referenceElement = referenceWrapper.find('[data-fieldtype="reference"]').length ?
					referenceWrapper.find('[data-fieldtype="reference"]') :
					referenceWrapper.find('[data-fieldtype="multireference"]');
			referenceElement.valid();
		});

		return validobj;
	};



}( jQuery ));
