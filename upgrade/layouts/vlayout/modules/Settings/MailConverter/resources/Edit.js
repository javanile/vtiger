/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Settings_MailConverter_Edit_Js",{
	
	firstStep : function(e) {
		jQuery('form[name="step1"]').on('submit',function(e) {
			e.preventDefault();
			var validationEngineOptions = app.validationEngineOptions;
			validationEngineOptions['promptPosition'] = 'bottomRight',
			validationEngineOptions['onValidationComplete'] = function(form, isValid) {
				if(isValid){
					Settings_MailConverter_Edit_Js.saveMailBox();
				} else {
					return false;
				}
			}
			var container = jQuery(e.currentTarget);
			container.validationEngine('attach',validationEngineOptions);
		});
	},
    
	saveMailBox : function() {
		jQuery('form[name="step1"]').off('submit');
		var progressInstance = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		data = jQuery('form[name="step1"]').serialize();
		data.scannername = jQuery('input[name="scannername"]').val();
		data = data + '&module=' +app.getModuleName()+ '&parent=' + app.getParentModuleName()+ '&action=SaveMailBox';
		AppConnector.request(data).then(
			function(response){
				if(typeof response.result != 'undefined'){					
					var create = jQuery("#create").val();
					window.location.href = "index.php?module="+app.getModuleName()+"&parent="+app.getParentModuleName()+"&view=Edit&mode=step2&create="+create+"&record="+response.result.id;
				} else {
					progressInstance.progressIndicator({
						'mode' : 'hide'
					});
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: response.error.message,
						animation: 'show',
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			}
			);  
	},
    
	secondStep : function(e) {
		jQuery('form[name="step2"]').submit(function(e) {
			e.preventDefault();
			var checked = jQuery("input[type=checkbox][name=folders]:checked").length;
			if(checked < 1) {
				var params = {
					title : app.vtranslate('JS_MESSAGE'),
					text: "You must select atleast one folder...",
					animation: 'show',
					type: 'error'
				};
				Vtiger_Helper_Js.showPnotify(params);
				return false;
			}
			var selectedFolders = jQuery('input[name=folders]:checked').map(function()
			{
				return jQuery(this).val();
			}).get();
			var response = Settings_MailConverter_Edit_Js.saveFolders(selectedFolders);
		});
	},
    
	saveFolders : function(selectedFolders) {
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		var create = jQuery("#create").val();
		var id = jQuery("#recordId").val();
		var data = 'index.php?module='+app.getModuleName()+'&parent='+app.getParentModuleName()+'&action=SaveFolders&folders='+selectedFolders+'&create='+create+"&record="+id;
		AppConnector.request(data).then(
			function(response){
				progressIndicatorElement.progressIndicator({
					'mode' : 'hide'
				});
				if(typeof response.result != 'undefined'){
					if(create=="new") 
						window.location.href = "index.php?module="+app.getModuleName()+"&parent="+app.getParentModuleName()+"&view=Edit&mode=step3&create="+create+"&record="+response.result.id;
					else
						window.location.href = "index.php?parent="+app.getParentModuleName()+"&module="+app.getModuleName()+"&view=List&record="+response.result.id;
				}
			});
	},
    
	thirdStep : function(e) {
		jQuery('form[name="step3"]').submit(function(e) {
			e.preventDefault();
			Settings_MailConverter_Edit_Js.saveRule(e);
		});
	},
    
	saveRule : function(e) {
		var form = jQuery(e.currentTarget);
		var progressIndicatorElement = jQuery.progressIndicator({
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		var params = form.serializeFormData();
		params.record = "";
		AppConnector.request(params).then(function(data) {
			progressIndicatorElement.progressIndicator({
				'mode' : 'hide'
			});
			if(typeof data.result != 'undefined') {
				window.location.href = "index.php?parent="+app.getParentModuleName()+"&module="+app.getModuleName()+"&view=List&record="+data.result.scannerId;
			}
		});
	},
	
	/*
	 * Function to activate the header based on the class
	 * @params class name
	 */
	activateHeader : function() {
		var step = jQuery("#step").val();
       jQuery('#'+step).addClass('active');
	}  
	
},
{	
	registerEvents : function() {
		Settings_MailConverter_Edit_Js.firstStep();
		Settings_MailConverter_Edit_Js.activateHeader();
		jQuery('form[name="step1"]').validationEngine(app.validationEngineOptions);
	}
});