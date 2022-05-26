/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Settings_PotentialMapping_Js",{
	
	/**
	 * Function to register event to edit Potentials mapping
	 */
	triggerEdit : function(editUrl){
		app.helper.showProgress();

		app.request.post({'url' : editUrl}).then(
			function(err, data) {
				app.helper.hideProgress();
				if(err === null) {
					var detailContentsHolder = jQuery('.settingsPageDiv div');
					detailContentsHolder.html(data);
					var potentialMappingInstance = new Settings_PotentialMapping_Js();
					potentialMappingInstance.registerEventsForEditView();
				}
		});
	},
	
	/**
	 * Function to register event for delete Potential mapping
	 */
	triggerDelete : function(event,url){
		var element = jQuery(event.currentTarget);
		var mappingContainer = element.closest('.listViewEntries');
		var mappingId = mappingContainer.data('cfmid');
		var deleteUrl = url+'&mappingId='+mappingId;
		app.helper.showProgress();
		
		app.request.post({'url' : deleteUrl}).then(
			function(err, data) {
				app.helper.hideProgress();
				if(err === null){
					mappingContainer.remove();
					app.helper.showSuccessNotification({'message' : data});
				}else {
					app.helper.showErrorNotification({'message' : err.message});
				}
			});
	}
},{
	/**
	 * Function to register events for edit view of potentials mapping
	 */
	registerEventsForEditView : function(){
		var form = jQuery('#potentialsMapping');
		var select2Elements = form.find('.select2');
		vtUtils.showSelect2ElementView(select2Elements);
		this.registerEventForAddingNewMapping();
		this.registerOnChangeEventForSourceModule();
		this.registerEventToDeleteMapping();
		this.registerEventForFormSubmit();
		this.registerOnChangeEventOfTargetModule();
		jQuery('select.projectFields.select2').trigger('change',false);
	},
	
	/**
	 * Function to register event for adding new convert to potentials mapping
	 */
	registerEventForAddingNewMapping : function(){
		jQuery('.potentialsFieldMappingEditPageDiv').on('click', '#addMapping', function(e){
			var convertPotentialMappingTable = jQuery('#convertPotentialMapping');
			var lastSequenceNumber = convertPotentialMappingTable.find('tr:not(.newMapping):last').attr('sequence-number');
			var newSequenceNumber = parseInt(lastSequenceNumber)+1;
			var newMapping = jQuery('.newMapping').clone(true,true);
			newMapping.attr('sequence-number',newSequenceNumber);
			newMapping.find('select.potentialFields.newSelect').attr("name",'mapping['+newSequenceNumber+'][potential]');
			newMapping.find('select.projectFields.newSelect').attr("name",'mapping['+newSequenceNumber+'][project]');
			newMapping.removeClass('hide newMapping');
			newMapping.appendTo(convertPotentialMappingTable);
			newMapping.find('.newSelect').removeClass('newSelect').addClass('select2');
			var select2Elements = newMapping.find('.select2');
			vtUtils.showSelect2ElementView(select2Elements);
			jQuery('select.projectFields.select2',newMapping).trigger('change',false);
		})
	},
	
	/**
	 * Function to register on change event for select2 element
	 */
	registerOnChangeEventForSourceModule : function(){
		var form = jQuery('#potentialsMapping');
		form.on('change','.potentialFields',function(e){
			var element = jQuery(e.currentTarget);
			var container = jQuery(element.closest('tr'));
			var selectedValue = element.val();
			var selectedOption = element.find('option[value="'+selectedValue+'"]');
			var selectedDataType = selectedOption.data('type');
			var projectFieldsSelectElement = container.find('select.projectFields.select2');
			
			var projectFieldsBasedOnType = form.find('.newMapping').find('.projectFields').children().filter('[data-type="'+selectedDataType+'"]').clone(true,true);
			selectedDataType = app.vtranslate("JS_"+selectedDataType);
			container.find('.selectedFieldDataType').html(selectedDataType);
			
			var noneValue = app.vtranslate('JS_NONE');
			projectFieldsSelectElement.html(projectFieldsBasedOnType);
			
			if(selectedDataType != "None"){
				projectFieldsSelectElement.prepend('<option data-type="'+noneValue+'" label="'+noneValue+'" value="0" selected>'+noneValue+'</option>');
			}
			
			projectFieldsSelectElement.trigger("liszt:updated").trigger('change',false);
		})
	},

	/**
	 * Function to register event to delete mapping
	 */
	registerEventToDeleteMapping : function(){
		var form = jQuery('#potentialsMapping');
		form.on('click','.deleteMapping',function(e){
			var element = jQuery(e.currentTarget);
			var mappingContainer = element.closest('tr');
			var mappingContainerSequenceNumber = mappingContainer.attr('sequence-number');
			var deletableName = 'mapping['+mappingContainerSequenceNumber+'][deletable]';
			mappingContainer.prepend('<input type="hidden" name="'+deletableName+'" />')
			mappingContainer.data('deletable',true).hide('slow');
			app.helper.showSuccessNotification({'message' : app.vtranslate('JS_MAPPING_DELETED_SUCCESSFULLY')});
		})
	},
	
	/**
	 * Function to handle edit view convert potential form submit
	 */
	registerEventForFormSubmit : function() {
		jQuery('#potentialsMapping').on('submit',function(e){
			e.preventDefault();
			var mappingDetails = jQuery(this).serializeFormData();
			var params = { 
				'module' : app.getModuleName(),
				'parent' : app.getParentModuleName(),
				'action' : 'MappingSave',
				'mapping' : mappingDetails
			};

			app.helper.showProgress();

			app.request.post({'data' : params}).then(
				function(err, data) {
					app.helper.hideProgress();
					if(err === null){
						var detailViewParams = {
							'module' : app.getModuleName(),
							'parent' : app.getParentModuleName(),
							'view' : 'MappingDetail'
						};
						app.helper.showProgress();
						
						app.request.post({'data' : detailViewParams}).then(function(err, data){
							app.helper.hideProgress();
							
							if(err === null) {
								var detailContentsHolder = jQuery('.settingsPageDiv div');
								detailContentsHolder.html(data);
							}
						});
					}
				});
			});
	},
	
	/**
	 * Function to register on chnage event of target module
	 */
	registerOnChangeEventOfTargetModule : function(){
		var form = jQuery('#potentialsMapping');
		form.on('change','select.projectFields.select2',function(e,executeChange){
			if(typeof executeChange == "undefined"){
				executeChange = true;
			}
			var selectElement = jQuery(e.currentTarget);
			var selectedValue = selectElement.children().filter('option:selected').text();
			var selectedOptionId = selectElement.children().filter('option:selected').val();
			var mappingContainer = selectElement.closest('tr');
			
			var duplicateOption = false;
			var existingIdElement;
			if(selectedOptionId == "0"){
				selectedOptionId = "false";
			}
			
			if((!executeChange) || (selectedOptionId == "false")){
				selectElement.attr('selectedId',selectedOptionId);
				return;
			}
			
			//check for source module field
			var sourceModuleSelect = mappingContainer.find('select.potentialFields.select2');
			var sourceModuleSelectedField = sourceModuleSelect.val();
			var sourceModuleSelectedFieldLabel = sourceModuleSelect.find('option[value="'+sourceModuleSelectedField+'"]').text();
			if(sourceModuleSelectedField == "0"){
				duplicateOption = true;
				var errorParams = {
					message: app.vtranslate('JS_CANT_MAP')+" "+ selectedValue+" "+ app.vtranslate('JS_WITH')+" "+ sourceModuleSelectedFieldLabel,
				}
				app.helper.showErrorNotification(errorParams);
			}
			
			var restrictedFieldsList = JSON.parse(jQuery('#restrictedFieldsList').val());
			if(jQuery.inArray(selectedOptionId, restrictedFieldsList) != -1) {
				duplicateOption = true;
			} else {
				if(selectElement.hasClass('projectFields')){
					existingIdElement = jQuery('select.projectFields.select2');
                    jQuery.each(existingIdElement, function( key, element ) {
                        var element = jQuery(element);
                        var selectedValue = element.val();
						
                        //If Mapping is deleted, we just hiding the row, still element exists
                        //should not consider the deleted mapping for duplicate check
                        var elementRow = existingIdElement.closest('tr:visible');
						
                        if(selectedValue == selectedOptionId && existingIdElement.length > 0 && elementRow.length > 0 && element.attr('name') != selectElement.attr('name')) {
                            duplicateOption = true;
                        }
                    });
				}
			}
			
			if(duplicateOption){
				var selectedFieldId = selectElement.attr('selectedid');
				var previousSelectedValue;
				if(selectedFieldId == "false"){
					previousSelectedValue = selectElement.find('option[label="None"]').text();
					selectElement.attr('selectedId',"false");
				} else if(selectedFieldId != "false"){
					previousSelectedValue = selectElement.find('option[value="'+selectedFieldId+'"]').text();
					selectElement.attr('selectedId',selectedFieldId);
				}
				var params = {
						'id' : previousSelectedValue,
						'text' : previousSelectedValue
					}
				var warningMessage = selectedValue+" "+app.vtranslate('JS_IS_ALREADY_BEEN_MAPPED');
				var notificationParams = {
					message: warningMessage,
				};
				app.helper.showAlertNotification(notificationParams);
				selectElement.select2("data",params);
			} else if(duplicateOption == false){
				selectElement.attr('selectedId',selectedOptionId);
			}
		})
	}
});

Vtiger.Class("Settings_Potentials_MappingDetail_Js", {}, {
	
	init : function() {
        this.addComponents();
//        if(jQuery('.sticky-wrap').length == 0){
//            stickyheader.controller();
//            var container = jQuery('#detailView');
//            container.find('.sticky-thead').addClass('listview-table');
//        }
        if(window.hasOwnProperty('Vtiger_List_Js')) {
            var listInstance = new Vtiger_List_Js();
            setTimeout(function(){
                listInstance.registerFloatingThead();
            }, 10);

            app.event.on('Vtiger.Post.MenuToggle', function() {
                listInstance.reflowList();
            });
            listInstance.registerDynamicDropdownPosition();
        }
    },
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
	
});
