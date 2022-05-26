/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Settings_Vtiger_CustomRecordNumbering_Js', {}, {
	
	form : false,
	getForm : function(){
		if(this.form === false){
			this.form = jQuery('#EditView');
		}
        return this.form;
	},
	
    
	init : function() {
       this.addComponents();
	},
   
	addComponents : function (){
	   this.addComponent('Vtiger_Index_Js');
	},
	/**
	 * Function to register change event for source module field
	 */
	registerOnChangeEventOfSourceModule :function(){
		var editViewForm = this.getForm();
		editViewForm.find('[name="sourceModule"]').on('change',function(e){
			jQuery('.saveButton').removeAttr('disabled');
            var element = jQuery(e.currentTarget);
            var params = {};
			var sourceModule = element.val();
                params = {
				'module' : app.getModuleName(),
				'parent' : app.getParentModuleName(),
				'action' : "CustomRecordNumberingAjax",
				'mode' : "getModuleCustomNumberingData",
				'sourceModule' : sourceModule
			};
			
			app.request.post({"data":params}).then(
                function(err,data){
                    if(err === null){
                        editViewForm.find('[name="prefix"]').val(data.prefix);
                        editViewForm.find('[name="sequenceNumber"]').val(data.sequenceNumber);
                        editViewForm.find('[name="sequenceNumber"]').data('oldSequenceNumber',data.sequenceNumber);
                    }
                });
		});
	},
	
	/**
	 * Function to register event for saving module custom numbering
	 */
	saveModuleCustomNumbering : function(form){
        
		var editViewForm = form;
        var params = {};
		var sourceModule = editViewForm.find('[name="sourceModule"]').val();
        var sourceModuleLabel = editViewForm.find('option[value="'+sourceModule+'"]').text();
		var prefix = editViewForm.find('[name="prefix"]');
		var currentPrefix = jQuery.trim(prefix.val());
		var oldPrefix = prefix.data('oldPrefix');
        var sequenceNumberElement = editViewForm.find('[name="sequenceNumber"]');
		var sequenceNumber = sequenceNumberElement.val();
        var oldSequenceNumber = sequenceNumberElement.data('oldSequenceNumber');
		if((sequenceNumber < oldSequenceNumber) && (currentPrefix === oldPrefix)){
			var errorMessage = app.vtranslate('JS_SEQUENCE_NUMBER_MESSAGE')+" "+oldSequenceNumber;
			app.helper.showErrorNotification({'message':errorMessage});
			return;
		}

		params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : "CustomRecordNumberingAjax",
			'mode' : "saveModuleCustomNumberingData",
			'sourceModule' : sourceModule,
			'prefix' : currentPrefix,
			'sequenceNumber' : sequenceNumber
		}
        
		jQuery('.saveButton').attr("disabled","disabled");
		app.request.post({"data":params}).then(
            function(err, data){
              if(err === null){
                  var successfullSaveMessage = app.vtranslate('JS_RECORD_NUMBERING_SAVED_SUCCESSFULLY_FOR')+" "+sourceModuleLabel;
                  app.helper.showSuccessNotification({'message':successfullSaveMessage});
                }else{
                    app.helper.showErrorNotification({'message':err.message});
                }
            });
	},
	
	/**
	 * Function to handle update record with the given sequence number
	 */
	registerEventToUpdateRecordsWithSequenceNumber : function(){
		var editViewForm = this.getForm();
        editViewForm.find('[name="updateRecordWithSequenceNumber"]').on('click',function(){
			var params = {};
			var sourceModule = editViewForm.find('[name="sourceModule"]').val();
            var sourceModuleLabel = editViewForm.find('option[value="'+sourceModule+'"]').text();
			
			params = {
				'module' : app.getModuleName(),
				'parent' : app.getParentModuleName(),
				'action' : "CustomRecordNumberingAjax",
				'mode' : "updateRecordsWithSequenceNumber",
				'sourceModule' : sourceModule
			};
			
			app.request.post({"data":params}).then(
                function(err, data){
                    var successfullSaveMessage = app.vtranslate('JS_RECORD_NUMBERING_UPDATED_SUCCESSFULLY_FOR')+" "+sourceModuleLabel;
                    if(err === null){
                        app.helper.showSuccessNotification({'message':successfullSaveMessage});
                    }else{
                        app.helper.showErrorNotification({'message':err.message});
                    }
            });
		});
	},
	
	/**
	 * Function to register change event for prefix and sequence number
	 */
	registerChangeEventForPrefixAndSequenceNumber : function() {
		var editViewForm = this.getForm();
         editViewForm.find('[name="prefix"],[name="sequenceNumber"]').on('change',function(){
            jQuery('.saveButton').removeAttr('disabled');
		});
	},
    
	
	/**
	 * Function to register events
	 */
	registerEvents : function(){
		var thisInstance = this;
		var editViewForm = thisInstance.getForm();
		thisInstance.registerOnChangeEventOfSourceModule();
		thisInstance.registerEventToUpdateRecordsWithSequenceNumber();
		thisInstance.registerChangeEventForPrefixAndSequenceNumber();
        
        editViewForm.on('submit', function(e){
            e.preventDefault();
        });
        
        var params = {
            submitHandler : function(form) {
                var form = jQuery(form);
                thisInstance.saveModuleCustomNumbering(form);
            }
        };
        editViewForm.vtValidate(params);
		
		var instance = new Settings_Vtiger_Index_Js();
		instance.registerBasicSettingsEvents();
	}
});

