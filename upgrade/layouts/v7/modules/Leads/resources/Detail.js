/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Leads_Detail_Js", {
    //cache will store the convert lead data(Model)
    cache: {},
    //Holds detail view instance
    detailCurrentInstance: false,
    /* function to trigger Convert Lead action
     * @param: Convert Lead url, currentElement.
     */
    convertLead: function (convertLeadUrl, buttonElement) {
        var instance = Leads_Detail_Js.detailCurrentInstance;
        //Initially clear the elements to overwtite earliear cache
        instance.convertLeadContainer = false;
        instance.convertLeadForm = false;
        instance.convertLeadModules = false;
        if (jQuery.isEmptyObject(Leads_Detail_Js.cache)) {
            app.helper.showProgress();
            app.request.get({"url": convertLeadUrl}).then(
                    function (err, data) {
                        app.helper.hideProgress();
                        if (data) {
                            Leads_Detail_Js.cache = data;
                            instance.displayConvertLeadModel(data, buttonElement);
                        }
                    },
                    function (error, err) {

                    }
            );
        } else {
            instance.displayConvertLeadModel(Leads_Detail_Js.cache, buttonElement);
        }
    }

}, {
    //Contains the convert lead form
    convertLeadForm: false,
    //contains the convert lead container
    convertLeadContainer: false,
    //contains all the checkbox elements of modules
    convertLeadModules: false,
    //constructor
    init: function () {
        this._super();
        Leads_Detail_Js.detailCurrentInstance = this;
   },
    /*
     * function to enable all the input and textarea elements
     */
    removeDisableAttr: function (moduleBlock) {
        moduleBlock.find('input,textarea,select').removeAttr('disabled');
    },
    /*
     * function to disable all the input and textarea elements
     */
    addDisableAttr: function (moduleBlock) {
        moduleBlock.find('input,textarea,select').attr('disabled', 'disabled');
    },
    /*
     * function to get Convert Lead Form
     */
    getConvertLeadForm: function () {
        if (this.convertLeadForm == false) {
            this.convertLeadForm = jQuery('#convertLeadForm');
        }
        return this.convertLeadForm;
    },
    /*
     * function to get all the checkboxes which are representing the modules selection
     */
    getConvertLeadModules: function () {
        var container = this.getConvertLeadContainer();
        if (this.convertLeadModules == false) {
            this.convertLeadModules = jQuery('.convertLeadModuleSelection', container);
        }
        return this.convertLeadModules;
    },
    /*
     * function to get Convert Lead Container
     */
    getConvertLeadContainer: function () {
        if (this.convertLeadContainer == false) {
            this.convertLeadContainer = jQuery('#leadAccordion');
        }
        return this.convertLeadContainer;
    },
    /*
     * function to display the convert lead model
     * @param: data used to show the model, currentElement.
     */
    displayConvertLeadModel: function (data, buttonElement) {
        var instance = this;
        var errorElement = jQuery(data).find('#convertLeadError');
        if (errorElement.length != '0') {

        } else {
            var callBackFunction = function (data) {
                var editViewObj = Vtiger_Edit_Js.getInstance();
                jQuery(data).find('.fieldInfo').collapse({
                    'parent': '#leadAccordion',
                    'toggle': false
                });
                app.helper.showVerticalScroll(jQuery(data).find('#leadAccordion'), {'setHeight': '350px'});
                editViewObj.registerBasicEvents(data);
                var checkBoxElements = instance.getConvertLeadModules();
                jQuery.each(checkBoxElements, function (index, element) {
                    instance.checkingModuleSelection(element);
                });
                instance.registerForReferenceField();
                instance.registerForDisableCheckEvent();
                instance.registerConvertLeadEvents();
                instance.registerConvertLeadSubmit();
            }
            app.helper.showModal(data, {"cb": callBackFunction});
        }
    },
    /*
     * function to check which module is selected 
     * to disable or enable all the elements with in the block
     */
    checkingModuleSelection: function (element) {
        var instance = this;
        var module = jQuery(element).val();
        var moduleBlock = jQuery(element).closest('.accordion-group').find('#' + module + '_FieldInfo');
        if (jQuery(element).is(':checked')) {
            instance.removeDisableAttr(moduleBlock);
        } else {
            instance.addDisableAttr(moduleBlock);
        }
    },
    registerForReferenceField: function () {
        var container = this.getConvertLeadContainer();
        var referenceField = jQuery('.reference', container);
        if (referenceField.length > 0) {
            jQuery('#AccountsModule').attr('readonly', 'readonly');
        }
    },
    /*
     * function to register Convert Lead Events
     */
    registerConvertLeadEvents: function () {
        var container = this.getConvertLeadContainer();
        var instance = this;

        //Trigger Event to change the icon while shown and hidden the accordion body 
        container.on('hidden', '.accordion-body', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            currentTarget.closest('.convertLeadModules').find('.iconArrow').removeClass('icon-chevron-up').addClass('icon-chevron-down');
        }).on('shown', '.accordion-body', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            currentTarget.closest('.convertLeadModules').find('.iconArrow').removeClass('icon-chevron-down').addClass('icon-chevron-up');
        });

        //Trigger Event on click of Transfer related records modules
        container.on('click', '.transferModule', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var module = currentTarget.val();
            var moduleBlock = jQuery('#' + module + '_FieldInfo');
            if (currentTarget.is(':checked')) {
                jQuery('#' + module + 'Module').attr('checked', 'checked');
                moduleBlock.collapse('show');
                instance.removeDisableAttr(moduleBlock);
            }
        });

        //Trigger Event on click of the Modules selection to convert the lead 
        container.on('click', '.convertLeadModuleSelection', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var currentModuleName = currentTarget.val();
            var moduleBlock = currentTarget.closest('.accordion-group').find('#' + currentModuleName + '_FieldInfo');
            var currentTransferModuleElement = jQuery('#transfer' + currentModuleName);
            var otherTransferModuleElement = jQuery('input[name="transferModule"]').not(currentTransferModuleElement);
            var otherTransferModuleValue = jQuery(otherTransferModuleElement).val();
            var otherModuleElement = jQuery('#' + otherTransferModuleValue + 'Module');

            if (currentTarget.is(':checked')) {
                moduleBlock.collapse('show');
                instance.removeDisableAttr(moduleBlock);
                if (!otherModuleElement.is(':checked')) {
                    jQuery(currentTransferModuleElement).attr('checked', 'checked');
                }
            } else {
                moduleBlock.collapse('hide');
                instance.addDisableAttr(moduleBlock);
                jQuery(currentTransferModuleElement).removeAttr('checked');
                if (otherModuleElement.is(':checked')) {
                    jQuery(otherTransferModuleElement).attr('checked', 'checked');
                }
            }
            e.stopImmediatePropagation();
        });
    },
    /*
     * function to register Convert Lead Submit Event
     */
    registerConvertLeadSubmit: function () {
        var thisInstance = this;
        var formElement = this.getConvertLeadForm();

        var params = {
            "ignore": "disabled",
            submitHandler: function (form) {
                var convertLeadModuleElements = thisInstance.getConvertLeadModules();
                var moduleArray = [];
                var contactModel = formElement.find('#ContactsModule');
                var accountModel = formElement.find('#AccountsModule');

                jQuery.each(convertLeadModuleElements, function (index, element) {
                    if (jQuery(element).is(':checked')) {
                        moduleArray.push(jQuery(element).val());
                    }
                });
                formElement.find('input[name="modules"]').val(JSON.stringify(moduleArray));

                var contactElement = contactModel.length;
                var organizationElement = accountModel.length;

                if (contactElement != '0' && organizationElement != '0') {
                    if (jQuery.inArray('Accounts', moduleArray) == -1 && jQuery.inArray('Contacts', moduleArray) == -1) {
                        app.helper.showErrorNotification({message:app.vtranslate('JS_SELECT_ORGANIZATION_OR_CONTACT_TO_CONVERT_LEAD')});
                        return false;
                    }
                } else if (organizationElement != '0') {
                    if (jQuery.inArray('Accounts', moduleArray) == -1) {
                       app.helper.showErrorNotification({message:app.vtranslate('JS_SELECT_ORGANIZATION')});
                        return false;
                    }
                } else if (contactElement != '0') {
                    if (jQuery.inArray('Contacts', moduleArray) == -1) {
                        app.helper.showErrorNotification({message:app.vtranslate('JS_SELECT_CONTACTS')});
                        return false;
                    }
                }
                return true;
            }
        }
        formElement.vtValidate(params);
    },
    registerForDisableCheckEvent: function () {
        var container = this.getConvertLeadContainer();
        var oppAccMandatory = jQuery('#oppAccMandatory').val();
        var oppConMandatory = jQuery('#oppConMandatory').val();
        var conAccMandatory = jQuery('#conAccMandatory').val();

        jQuery('#PotentialsModule').on('click', function () {
            if ((jQuery('#PotentialsModule').is(':checked')) && oppAccMandatory) {
                jQuery('#AccountsModule').attr({'disabled': 'disabled', 'checked': 'checked'});
            } else if (!conAccMandatory || !jQuery('#ContactsModule').is(':checked')) {
                jQuery('#AccountsModule').removeAttr('disabled');
            }
            if ((jQuery('#PotentialsModule').is(':checked')) && oppConMandatory) {
                jQuery('#ContactsModule').attr({'disabled': 'disabled', 'checked': 'checked'});
            } else {
                jQuery('#ContactsModule').removeAttr('disabled');
            }
        });
        jQuery('#ContactsModule').on('click', function () {
            if ((jQuery('#ContactsModule').is(':checked')) && conAccMandatory) {
                jQuery('#AccountsModule').attr({'disabled': 'disabled', 'checked': 'checked'});
            } else if (!oppAccMandatory || !jQuery('#PotentialsModule').is(':checked')) {
                jQuery('#AccountsModule').removeAttr('disabled');
            }
        });
    },
});