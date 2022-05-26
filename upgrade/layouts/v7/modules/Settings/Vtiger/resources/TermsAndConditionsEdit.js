/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_TermsAndConditionsEdit_Js", {}, {
    //Contains Terms and Conditions container
    container: false,
    //return the container of Terms and Conditions
    getContainer: function () {
        if (this.container === false) {
            this.container = jQuery('#TermsAndConditionsContainer');
        }
        return this.container;
    },
    init: function () {
        this.addComponents();
    },
    addComponents: function () {
        this.addComponent('Vtiger_Index_Js');
    },
    /*
     * Function to save the Terms & Conditions content
     */
    saveTermsAndConditions: function (textAreaElement) {
        var aDeferred = jQuery.Deferred();
        var tandcContent = textAreaElement.val();
        var selectedModule = jQuery('#TermsAndConditionsContainer select.selectModule').val();
        var params = {
            'module': app.getModuleName(),
            'parent': app.getParentModuleName(),
            'action': 'TermsAndConditionsAjax',
            'tandc': tandcContent,
            'mode': 'save',
            'type': selectedModule
        };
        app.request.post({"data": params}).then(
                function (error, data) {
                    if (error === null) {
                        aDeferred.resolve();
                    } else {
                        aDeferred.reject();
                    }
                });
        return aDeferred.promise();
    },
    /*
     * Function to register keyUp event for text area to show save button
     */
    registerKeyUpEvent: function () {
        var thisInstance = this;
        var container = thisInstance.getContainer();
        container.find('.TCContent').keyup(function (e) {
            jQuery('.saveTC', container).removeClass('hide');
        });
    },
    registerEventForTextAreaFields: function (parentElement) {
        if (typeof parentElement === 'undefined') {
            parentElement = jQuery('body');
        }
        parentElement = jQuery(parentElement);
        if (parentElement.is('textarea')) {
            var element = parentElement;
        } else {
            var element = jQuery('textarea', parentElement);
        }
        if (element.length == 0) {
            return;
        }

    },
    registerSelectModuleEvent: function () {
        var container = this.getContainer();
        var textAreaElement = jQuery('.TCContent', container);
        container.find('.selectModule').on('change', function (e) {
            var type = jQuery(e.currentTarget).val();

            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'action': 'TermsAndConditionsAjax',
                'mode': 'getModuleTermsAndConditions',
                'type': type
            };
            app.request.post({"data": params}).then(function (err, data) {
                if (err === null) {
                    if(typeof data === 'object') {
                        jQuery('.TCContent', container).val(data.result);
                    } else {
                        jQuery('.TCContent', container).val(data);
                    }
                    jQuery('.saveTC', container).removeClass('hide');
                }
            });
        });
    },
    registerEvents: function () {
        var thisInstance = this;
        var container = thisInstance.getContainer();
        var textAreaElement = jQuery('.TCContent', container);
        var saveButton = jQuery('.saveTC', container);

        //register text area fields to autosize
        this.registerEventForTextAreaFields(textAreaElement);
        thisInstance.registerKeyUpEvent();
        thisInstance.registerSelectModuleEvent();

		//Register click event for save button
		saveButton.click(function(e) {
			saveButton.addClass('hide');
			
			//save the new T&C content
			thisInstance.saveTermsAndConditions(textAreaElement).then(
				function(data) {
					thisInstance.registerKeyUpEvent();
					var message = app.vtranslate('JS_TERMS_AND_CONDITIONS_SAVED')
					 app.helper.showSuccessNotification({'message':message});
				});
		});
		
		var instance = new Settings_Vtiger_Index_Js();
		instance.registerBasicSettingsEvents();
	}

});
