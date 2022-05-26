/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Field_Js("Users_Field_Js",{},{})

Vtiger_Field_Js('Users_Picklist_Field_Js',{},{

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
		var html = '<select  class="select2 inputElement inlinewidth" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = app.htmlDecode(this.getValue());
        if(jQuery.trim(selectedOption).length === 0) {
			selectedOption = '&nbsp;';
		}
		html += '';
		for(var option in pickListValues) {
			if(jQuery.trim(option).length === 0) {
				option = '&nbsp;';
			}
			
			html += '<option value="'+option+'" ';
			
			if(option == selectedOption) {
				html += ' selected ';
			}
			
			if(option == '&nbsp;' && (this.getName() == 'currency_decimal_separator' || this.getName() == 'currency_grouping_separator')) {
				html += '>'+app.vtranslate('Space')+'</option>';
			}
			html += '>'+pickListValues[option]+'</option>';
		}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});