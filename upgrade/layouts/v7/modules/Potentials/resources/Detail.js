/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Potentials_Detail_Js",{

	//cache will store the convert Potential data(Model)
	cache : {},

	//Holds detail view instance
	detailCurrentInstance : false,

	/*
	 * function to trigger Convert Potential action
	 * @param: Convert Potential url, currentElement.
	 */
	convertPotential : function(convertPotentialUrl, buttonElement) {
		var instance = Potentials_Detail_Js.detailCurrentInstance;
		//Initially clear the elements to overwtite earliear cache
		instance.convertPotentialContainer = false;
		instance.convertPotentialForm = false;
		instance.convertPotentialModules = false;
		if(jQuery.isEmptyObject(Potentials_Detail_Js.cache)) {
			app.request.get({"url": convertPotentialUrl}).then(function (err, data) {
					if(data) {
						Potentials_Detail_Js.cache = data;
						instance.displayConvertPotentialModel(data, buttonElement);
					}
				},
				function(error,err){

				}
			);
		} else {
			instance.displayConvertPotentialModel(Potentials_Detail_Js.cache, buttonElement);
		}
	}
},{

	//Contains the convert Potential form
	convertPotentialForm : false,

	//contains the convert Potential container
	convertPotentialContainer : false,

	//contains all the checkbox elements of modules
	convertPotentialModules : false,

	detailViewRecentContactsLabel : 'Contacts',
	detailViewRecentProductsTabLabel : 'Products',

	//constructor
	init : function() {
		this._super();
		Potentials_Detail_Js.detailCurrentInstance = this;
	},

	/*
	 * function to get Convert Potential Form
	 */
	getConvertPotentialForm : function() {
		if(this.convertPotentialForm == false) {
			this.convertPotentialForm = jQuery('#convertPotentialForm');
		}
		return this.convertPotentialForm;
	},

	/*
	 * function to get Convert Potential Container
	 */
	getConvertPotentialContainer : function() {
		if(this.convertPotentialContainer == false) {
			this.convertPotentialContainer = jQuery('#potentialAccordion');
		}
		return this.convertPotentialContainer;
	},

	/*
	 * function to get all the checkboxes which are representing the modules selection
	 */
	getConvertPotentialModules : function() {
		var container = this.getConvertPotentialContainer();
		if(this.convertPotentialModules == false) {
			this.convertPotentialModules = jQuery('.convertPotentialModuleSelection', container);
		}
		return this.convertPotentialModules;
	},

	/*
	 * function to disable the Convert Potential button
	 */
	disableConvertPotentialButton : function(button) {
		jQuery(button).attr('disabled','disabled');
	},

	/*
	 * function to enable the Convert Potential button
	 */
	enableConvertPotentialButton : function(button) {
		jQuery(button).removeAttr('disabled');
	},

	/*
	 * function to enable all the input and textarea elements
	 */
	removeDisableAttr : function(moduleBlock) {
		moduleBlock.find('input,textarea,select').removeAttr('disabled');
	},

	/*
	 * function to disable all the input and textarea elements
	 */
	addDisableAttr : function(moduleBlock) {
		moduleBlock.find('input,textarea,select').attr('disabled', 'disabled');
	},

	/*
	 * function to display the convert Potential model
	 * @param: data used to show the model, currentElement.
	 */
	displayConvertPotentialModel : function(data, buttonElement) {
		var instance = this;
		var errorElement = jQuery(data).find('#convertPotentialError');
		if(errorElement.length != '0') {

		} else {
			var callBackFunction = function(data){
				var editViewObj = Vtiger_Edit_Js.getInstance();
				jQuery(data).find('.fieldInfo').collapse({
					'parent': '#potentialAccordion',
					'toggle' : false
				});
				app.helper.showVerticalScroll(jQuery(data).find('#potentialAccordion'), {'setHeight': '350px'});
				editViewObj.registerBasicEvents(data);
				var checkBoxElements = instance.getConvertPotentialModules();
				jQuery.each(checkBoxElements, function(index, element){
					instance.checkingModuleSelection(element);
				});
				instance.registerForReferenceField();
				instance.registerConvertPotentialEvents();
				instance.registerConvertPotentialSubmit();
			}
			app.helper.showModal(data, {"cb": callBackFunction});
		}
	},

	/*
	 * function to check which module is selected 
	 * to disable or enable all the elements with in the block
	 */
	checkingModuleSelection : function(element) {
		var instance = this;
		var module = jQuery(element).val();
		var moduleBlock = jQuery(element).closest('.accordion-group').find('#'+module+'_FieldInfo');
		if(jQuery(element).is(':checked')) {
			instance.removeDisableAttr(moduleBlock);
		} else {
			instance.addDisableAttr(moduleBlock);
		}
	},

	registerForReferenceField : function() {
		var container = this.getConvertPotentialContainer();
		var referenceField = jQuery('.reference', container);
		if(referenceField.length > 0) {
			jQuery('#ProjectModule').attr('readonly', 'readonly');
		}
	},

	/*
	 * function to register Convert Potential Events
	 */
	registerConvertPotentialEvents : function() {
		var container = this.getConvertPotentialContainer();
		var instance = this;

		//Trigger Event to change the icon while shown and hidden the accordion body 
		container.on('click', '.accordion-group', function (e) { 
			var currentElement = jQuery(e.currentTarget).find('.Project_faAngle');
			if (jQuery('.Project_FieldInfo').hasClass('in')) {
				currentElement.removeClass('fa-angle-up');
				currentElement.addClass('fa-angle-down');
			} else {
				currentElement.removeClass('fa-angle-down');
				currentElement.addClass('fa-angle-up');
			}
		});

		//Trigger Event on click of the Modules selection to convert the lead 
		container.on('click','.convertPotentialModuleSelection', function(e){
			var currentTarget = jQuery(e.currentTarget);
			var currentModuleName = currentTarget.val();
			var moduleBlock = currentTarget.closest('.accordion-group').find('#'+currentModuleName+'_FieldInfo');

			if(currentTarget.is(':checked')) {
				moduleBlock.collapse('show');
				instance.removeDisableAttr(moduleBlock);
			} else {
				moduleBlock.collapse('hide');
				instance.addDisableAttr(moduleBlock);
			}
			e.stopImmediatePropagation();
		});
	},

	/*
	 * function to register Convert Potential Submit Event
	 */
	registerConvertPotentialSubmit : function() {
		var thisInstance = this;
		var formElement = this.getConvertPotentialForm();
		var params = {
			"ignore": "disabled",
			submitHandler: function (form) {
			   var convertPotentialModuleElements = thisInstance.getConvertPotentialModules();
			   var moduleArray = [];
			   var projectModel = formElement.find('#ProjectModule');

			   jQuery.each(convertPotentialModuleElements, function(index, element) {
				   if(jQuery(element).is(':checked')) {
					   moduleArray.push(jQuery(element).val());
				   }
			   });
			   formElement.find('input[name="modules"]').val(JSON.stringify(moduleArray));

			   var projectElement = projectModel.length;

			   if(projectElement != '0') {
				   if(jQuery.inArray('Project',moduleArray) == -1) {
					   app.helper.showErrorNotification({message:app.vtranslate('JS_SELECT_PROJECT_TO_CONVERT_LEAD')});
					   return false;
				   } 
			   }
			   return true;
			}
		 }
		formElement.vtValidate(params);
	},

	/**
	 * Function which will register all the events
	 */
	registerEvents : function() {
		this._super();
		var detailContentsHolder = this.getContentHolder();
		var thisInstance = this;

		detailContentsHolder.on('click','.moreRecentContacts', function(){ 
			var recentContactsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentContactsLabel); 
			recentContactsTab.trigger('click'); 
		}); 

		detailContentsHolder.on('click','.moreRecentProducts', function(){ 
			var recentProductsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentProductsTabLabel); 
			recentProductsTab.trigger('click'); 
		});
	}
})