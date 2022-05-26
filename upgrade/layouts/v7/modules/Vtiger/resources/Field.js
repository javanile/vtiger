/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Vtiger_Field_Js",{

	/**
	 * Function to get Instance of the class based on moduleName
	 * @param data,data to set
	 * @param moduleName module for which Instance should be created
	 * @return Instance of field class
	 */
	getInstance : function(data,moduleName){
		if(typeof moduleName == 'undefined'){
			var moduleName = app.getModuleName();
		}
		var moduleField = moduleName+"_Field_Js";
		var moduleFieldObj = window[moduleField];
		if (typeof moduleFieldObj != 'undefined'){
			 var fieldClass = moduleFieldObj;
		}else{
			var fieldClass = Vtiger_Field_Js;
		}
		var fieldObj = new fieldClass();

		if(typeof data == 'undefined'){
			data = {};
		}
		fieldObj.setData(data);
		return fieldObj;
	}
},{
	data : {},
	/**
	 * Function to check whether field is mandatory or not
	 * @return true if feld is madatory
	 * @return false if field is not mandatory
	 */
	isMandatory : function(){
		return this.get('mandatory');
	},


	/**
	 * Function to get the value of particular key in object
	 * @return value for the passed key
	 */

	get : function(key){
		if(key in this.data){
			return this.data[key];
		}
		return '';
	},


	/**
	 * Function to get type attribute of the object
	 * @return type attribute of the object
	 */
	getType : function(){
		return this.get('type');
	},

	/**
	 * Function to get name of the field
	 * @return <String> name of the field
	 */
	getName : function() {
		return this.get('name');
	},

	/**
	 * Function to get value of the field
	 * @return <Object> value of the field or empty of there is not value
	 */
	getValue : function() {
		if('value' in this.getData()){
			return this.get('value');
		} else if('defaultValue' in this.getData()){
			return this.get('defaultValue');
		}
		return '';
	},

	/**
	 * Function to get the whole data
	 * @return <object>
	 */
	getData : function() {
		return this.data;
	},

	/**
	 * Function to set data attribute of the class
	 * @return Instance of the class
	 */
	setData : function(fieldInfo){
		this.data = fieldInfo;
		return this;
	},

	getModuleName : function() {
		return app.getModuleName();
	},

	/**
	 * Function to get the ui type specific model
	 */
	getUiTypeModel : function() {
		var currentModule = this.getModuleName();

		var type = this.getType();
		var typeClassName = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();

		var moduleUiTypeClassName = window[currentModule + "_" + typeClassName+"_Field_Js"];
		var BasicUiTypeClassName = window["Vtiger_"+ typeClassName + "_Field_Js"];

		if(typeof moduleUiTypeClassName != 'undefined') {
			var instance = new moduleUiTypeClassName();
			return instance.setData(this.getData());
		}else if (typeof BasicUiTypeClassName != 'undefined') {
			var instance = new BasicUiTypeClassName();
			return instance.setData(this.getData());
		}
		return this;
	},

	/**
	 * Funtion to get the ui for the field - generally this will be extend by the child classes to
	 * give ui type specific ui
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi : function() {
		var html = '<input class="inputElement" type="text" name="'+ this.getName() +'" data-label="'+this.get('label')+'" data-rule-'+this.getType()+'=true />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));
		return this.addValidationToElement(html);
	},

	/**
	 * Function to get the ui for a field depending on the ui type
	 * this will get the specific ui depending on the field type
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUiTypeSpecificHtml : function() {
		var uiTypeModel = this.getUiTypeModel();
		return uiTypeModel.getUi();
	},

	/**
	 * Function to add the validation for the element
	 */
	addValidationToElement : function(element) {
		var element = jQuery(element);
		var addValidationToElement = element;
		var elementInStructure = element.find('[name="'+this.getName()+'"]');
		if(elementInStructure.length > 0){
			addValidationToElement = elementInStructure;
		}
		if(this.isMandatory()) {
			addValidationToElement.attr('data-rule-required', 'true');
			var type = this.getType();
			if (type == 'reference') {
				addValidationToElement.attr('data-rule-reference_required', 'true');
			}
		}
		addValidationToElement.attr('data-fieldinfo',JSON.stringify(this.getData())).attr('data-specific-rules',JSON.stringify(this.getData().specialValidator));
		return element;
	},

	getNewFieldInfo : function() {
		return this.get('newfieldinfo');
	},

})

Vtiger_Field_Js('Vtiger_Reference_Field_Js',{},{

	getReferenceModules : function(){
		return this.get('referencemodules');
	},

	getUi : function(){
		var referenceModules = this.getReferenceModules();
		var value = this.getValue();
		var html = '<div class="referencefield-wrapper';
		if(value){
			html += ' selected';
		} else {
			html += '"';
		}
		html += '">';
		html += '<input name="popupReferenceModule" type="hidden" value="'+referenceModules[0]+'"/>';
		html += '<div class="input-group ">'
		html += '<input class="autoComplete inputElement sourceField" type="search" data-fieldtype="reference" name="'+this.getName()+'"';
		var reset = false;
		if(value){
			html += ' value="'+value+'" disabled="disabled"';
			reset = true;
		}
		html += '/>';

		if(reset){
			html += '<a href="#" class="clearReferenceSelection"> X </a>';
		}else {
			html += '<a href="#" class="clearReferenceSelection hide"> X </a>';
		}
		//popup search element
		html += '<span class="input-group-addon relatedPopup cursorPointer" title="'+referenceModules[0]+'">';
		html += '<i class="fa fa-search"></i>';
		html += '</span>';

		html += '</div>';
		html += '</div>';
		return this.addValidationToElement(html);
	}

});


Vtiger_Field_Js('Vtiger_Picklist_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		//added class inlinewidth
		var html = '<select class="select2 inputElement inlinewidth" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());

		if(typeof pickListValues[' '] == 'undefined' || pickListValues[' '].length <= 0 || pickListValues[' '] != 'Select an Option') {
			html += '<option value="">'+app.vtranslate('JS_SELECT_OPTION')+'</option>';
		}

		var data = this.getData();
		var picklistColors = data['picklistColors'];

		var fieldName = this.getName();
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';

			if (picklistColors) {
				var className = '';
				if (picklistColors[option]) {
					className = 'picklistColor_'+fieldName+'_'+option.replace(' ', '_');
					html += 'class="'+className+'"';
				}
			}

			if(option == selectedOption) {
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';

		if (picklistColors) {
			html +='<style type="text/css">';
			for(option in picklistColors) {
				var picklistColor = picklistColors[option];
				if (picklistColor) {
					className = '.picklistColor_'+fieldName+'_'+option.replace(' ', '_');
					html += className+'{background-color: '+picklistColor+' !important;}';

					className = className + '.select2-highlighted';
					html += className+'{white: #ffffff !important; background-color: #337ab7 !important;}';
				}
			}
			html +='<\style>';
		}

		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Field_Js('Vtiger_Currencylist_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getCurrencyList : function() {
		return this.get('currencyList');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		var html = '<select class="select2 inputElement" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var currencyLists = this.getCurrencyList();
		var selectedOption = app.htmlDecode(this.getValue());
		for(var option in currencyLists) {
			html += '<option value="'+option+'" ';
			if(option == selectedOption) {
				html += ' selected ';
			}
			html += '>'+currencyLists[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Field_Js('Vtiger_Multipicklist_Field_Js',{},{
	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	getSelectedOptions : function(selectedOption){
		var valueArray = selectedOption.split('|##|');
		var selectedOptionsArray = [];
		for(var i=0;i<valueArray.length;i++){
			selectedOptionsArray.push(valueArray[i].trim());
		}
		return selectedOptionsArray;
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		var html = '<select class="select2 inputElement" multiple name="'+ this.getName() +'[]" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
		var selectedOptionsArray = this.getSelectedOptions(selectedOption);

		var data = this.getData();
		var picklistColors = data['picklistColors'];

		var fieldName = this.getName();
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';

			if (picklistColors) {
				var className = '';
				if (picklistColors[option]) {
					className = 'picklistColor_'+fieldName+'_'+option.replace(' ', '_');
					html += 'class="'+className+'"';
				}
			}

			if(jQuery.inArray(option,selectedOptionsArray) != -1){
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';

		if (picklistColors) {
			html +='<style type="text/css">';
			for(option in picklistColors) {
				var picklistColor = picklistColors[option];
				if (picklistColor) {
					className = '.picklistColor_'+fieldName+'_'+option.replace(' ', '_');
					html += className+'{background-color: '+picklistColor+' !important;}';
				}
			}
			html +='<\style>';
		}

		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
}),

Vtiger_Field_Js('Vtiger_Boolean_Field_Js',{},{

	/**
	 * Function to check whether the field is checked or not
	 * @return <Boolean>
	 */
	isChecked : function() {
		var value = this.getValue();
		if(value==1 || value == '1' || (value && (value.toLowerCase() == 'on' || value.toLowerCase() == 'yes'))){
			return true;
		}
		return false;
	},

	/**
	 * Function to get the ui
	 * @return - checkbox element
	 */
	getUi : function() {
		var	html = '<input type="hidden" name="'+this.getName() +'" value="0"/><input class="inputElement" type="checkbox" name="'+ this.getName() +'" ';
		if(this.isChecked()) {
			html += 'checked';
		}
		html += ' />'
		return this.addValidationToElement(html);
	}
});


Vtiger_Field_Js('Vtiger_Date_Field_Js',{},{

	/**
	 * Function to get the user date format
	 */
	getDateFormat : function(){
		return this.get('date-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		//wrappig with another div for consistency
		var html = '<div class="referencefield-wrapper"><div class="input-group date">'+
						'<input class="inputElement dateField form-control" type="text" data-rule-date="true" data-format="'+ this.getDateFormat() +'" name="'+ this.getName() +'" value="'+ this.getValue() + '" />'+
						'<span class="input-group-addon"><i class="fa fa-calendar"></i></span>'+
					'</div></div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Currency_Field_Js',{},{

	/**
	 * get the currency symbol configured for the user
	 */
	getCurrencySymbol : function() {
		return this.get('currency_symbol');
	},

	getUi : function() {
		//wrappig with another div for consistency
		var html = '<div class="referencefield-wrapper"><div class="input-group">'+
						'<span class="input-group-addon" id="basic-addon1">'+this.getCurrencySymbol()+'</span>'+
						'<input class="inputElement" type="text" name="'+ this.getName() +'" data-rule-currency="true" value="'+ this.getValue() + '" />'+
					'</div></div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});


Vtiger_Field_Js('Vtiger_Owner_Field_Js',{},{

	/**
	 * Function to get the picklist values
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	getUi : function() {
		var html = '<select class="select2 inputElement" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
		for(var optGroup in pickListValues){
			html += '<optgroup label="'+ optGroup +'">';
			var optionGroupValues = pickListValues[optGroup];
			for(var option in optionGroupValues) {
				html += '<option value="'+option+'" ';
				if(option == selectedOption) {
					html += ' selected ';
				}
				html += '>'+optionGroupValues[option]+'</option>';
			}
			html += '</optgroup>';
		}

		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Date_Field_Js('Vtiger_Datetime_Field_Js',{},{

});

Vtiger_Field_Js('Vtiger_Time_Field_Js',{},{

	/**
	 * Function to get the user date format
	 */
	getTimeFormat : function(){
		return this.get('time-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<div class="referencefield-wrapper">'+'<div class="input-group time">'+
						'<input class="timepicker-default form-control inputElement" type="text" data-format="'+ this.getTimeFormat() +'" name="'+ this.getName() +'" value="'+ this.getValue() + '" />'+
						'<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>'+
					'</div>'+'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Text_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		var html = '<textarea class="input-xxlarge form-control inputElement" name="'+ this.getName() +'" value="'+ this.getValue() + '" >'+ this.getValue() + '</textarea>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input percentage field
	 */
	getUi : function() {
		var html = '<div class="input-group percentage-input-group">'+
						'<input type="text" class="form-control inputElement percentage-input-element" name="'+this.getName() +'" value="'+ this.getValue() + '" step="any" data-rule-'+this.getType()+'=true/>'+
						'<span class="input-group-addon">%</span>'+
					'</div>';
		var element = jQuery(html);
		return this.addValidationToElement(element);
	}
});
Vtiger_Field_Js('Vtiger_Recurrence_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		var html = '<select class="select2 inputElement" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
		for(var option in pickListValues) {
			html += '<option value="'+option+'" ';
			if(option == selectedOption) {
				html += ' selected ';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Field_Js('Vtiger_Email_Field_Js',{},{

	/**
	 * Funtion to get the ui for the email field
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi : function() {
		var html = '<input class="inputElement" type="text" name="'+ this.getName() +'" data-label="'+this.get('label')+'" data-rule-email="true" data-rule-illegal="true"/>';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));
		this.addValidationToElement(html);
		return jQuery(html);
	}
});

Vtiger_Field_Js('Vtiger_Image_Field_Js',{},{

	/**
	 * Funtion to get the ui for the Image field
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi : function() {
		var html = '';
		return jQuery(html);
	}
});

Vtiger_Field_Js('Vtiger_Integer_Field_Js',{},{
	getUi : function() {
		var html = '<input class="inputElement" type="text" name="'+ this.getName() +'" data-label="'+this.get('label')+'" data-rule-'+this.getType()+'=true />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));
		return this.addValidationToElement(html);
	}
});
