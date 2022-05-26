/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class('Settings_PickListDependency_Js', {
	
	//holds the picklist dependency instance
	pickListDependencyInstance : false,
	
	/**
	 * Function used to triggerAdd new Dependency for the picklists
	 */
	triggerAdd : function(event) {
		event.stopPropagation();
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
		instance.updatedSourceValues = [];
		instance.showEditView(instance.listViewForModule).then(
			function(data) {
				instance.registerAddViewEvents();
				instance.registerTargetFieldsClickEvent(jQuery('#dependencyGraph'));
			}
		);
	},
	
	/**
	 * This function used to trigger Edit picklist dependency
	 */
	triggerEdit : function(event, module, sourceField, targetField) {
		event.stopPropagation();
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
		instance.updatedSourceValues = [];
		instance.showEditView(module, sourceField, targetField).then(
			function(data){
				var form = jQuery('#pickListDependencyForm');
				form.find('[name="sourceModule"],[name="sourceField"],[name="targetField"]').select2('disable');
				var element = form.find('.dependencyMapping');
                app.helper.showHorizontalScroll(element);
				instance.registerDependencyGraphEvents();
				instance.registerTargetFieldsClickEvent(jQuery('#dependencyGraph'));
				instance.registerSubmitEvent();
			}
		);
	},
	
	/**
	 * This function used to trigger Delete picklist dependency
	 */
	triggerDelete : function(event, module, sourceField, targetField) {
		event.stopPropagation();
		var currentTarget = jQuery(event.currentTarget);
		var currentTrEle = currentTarget.closest('tr');
		var instance = Settings_PickListDependency_Js.pickListDependencyInstance;
		
		var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
		app.helper.showConfirmationBox({'message' : message}).then(
			function(e) {
				instance.deleteDependency(module, sourceField, targetField).then(
					function(data){
						var params = {};
						params.message = app.vtranslate('JS_DEPENDENCY_DELETED_SUEESSFULLY');
						app.helper.showSuccessNotification(params);
						currentTrEle.fadeOut('slow').remove();
					}
				);
			},
			function(error, err){
				
			}
		);
	}
	
}, {
	
	//constructor
	init : function() {
            this.addComponents();
            Settings_PickListDependency_Js.pickListDependencyInstance = this;
	},
        
        addComponents : function() {
            this.addModuleSpecificComponent('Index',app.getModuleName,app.getParentModuleName());
        },
	
	//holds the listview forModule
	listViewForModule : '',
	
	//holds the updated sourceValues while editing dependency
	updatedSourceValues : [],
	
	//holds the new mapping of source values and target values
	valueMapping : [],
	
	//holds the list of selected source values for dependency
	selectedSourceValues : [],
	
	/*
	 * function to show editView for Add/Edit Dependency
	 * @params: module - selected module
	 *			sourceField - source picklist
	 *			targetField - target picklist
	 */
	showEditView : function(module, sourceField, targetField) {
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'Edit';
		params['sourceModule'] = module;
		params['sourcefield'] = sourceField;
		params['targetfield'] = targetField;
		
		app.request.pjax({data: params}).then(
			function(error, data) {
				app.helper.hideProgress();
				var container = jQuery('.settingsPageDiv div');
				container.html(data);
				//register all select2 Elements
				vtUtils.showSelect2ElementView(container.find('select.select2'));
				aDeferred.resolve(data);
			},
			function(error) {
				app.helper.hideProgress();
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to get the Dependency graph based on selected module
	 */
	getModuleDependencyGraph : function(form) {
		var thisInstance = this;
		form.find('[name="sourceModule"]').on('change', function() {
			var forModule = form.find('[name="sourceModule"]').val();
			thisInstance.showEditView(forModule).then(
				function(data) {
					thisInstance.registerAddViewEvents();
					thisInstance.registerTargetFieldsClickEvent(jQuery('#dependencyGraph'));
				}
			);
		})
	},
	
	/**
	 * Register change event for picklist fields in add/edit picklist dependency
	 */
	registerPicklistFieldsChangeEvent : function(form) {
		var thisInstance = this;
		form.find('[name="sourceField"],[name="targetField"]').on('change', function() {
			thisInstance.checkValuesForDependencyGraph(form);
		})
	},
	
	/**
	 * Function used to check the selected picklist fields are valid before showing dependency graph
	 */
	checkValuesForDependencyGraph : function(form) {
		var thisInstance = this;
		var sourceField = form.find('[name="sourceField"]');
		var targetField = form.find('[name="targetField"]');
		var sourceFieldValue = sourceField.val();
		var targetFieldValue = targetField.val();
		var dependencyGraph = jQuery('#dependencyGraph');
		if(sourceFieldValue != '' && targetFieldValue != '') {
			var result = app.vtranslate('JS_SOURCE_AND_TARGET_FIELDS_SHOULD_NOT_BE_SAME');
			form.find('.errorMessage').addClass('hide');
			if(sourceFieldValue == targetFieldValue) {
				app.helper.showErrorNotification({'message':result});
				dependencyGraph.html('');
			} else {
				var sourceModule = form.find('[name="sourceModule"]').val();
				app.helper.showProgress();
				thisInstance.checkCyclicDependency(sourceModule, sourceFieldValue, targetFieldValue).then(
					function(result) {
                        app.helper.hideProgress();
						if(!result['result']) {
							thisInstance.addNewDependencyPickList(sourceModule, sourceFieldValue, targetFieldValue);
						} else {
							form.find('.errorMessage').removeClass('hide');
                            form.find('.errorMessage').find('strong').html(result.message);
							dependencyGraph.html('');
						}
					}
				);
			}
		} else {
			form.find('.errorMessage').addClass('hide');
			var result = app.vtranslate('JS_SELECT_SOME_VALUE');
			if(sourceFieldValue == '') {
				console.log(result);
			}else if(targetFieldValue == '') {
				console.log(result);
			}
		}
	},
	
	/**
	 * Function used to check the cyclic dependency of the selected picklist fields
	 * @params: sourceModule - selected module
	 *			sourceFieldValue - source picklist value
	 *			targetFieldValue - target picklist value
	 */
	checkCyclicDependency : function(sourceModule, sourceFieldValue, targetFieldValue) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = 'checkCyclicDependency';
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Index';
		params['sourceModule'] = sourceModule;
		params['sourcefield'] = sourceFieldValue;
		params['targetfield'] = targetFieldValue;
		
		app.request.post({data: params}).then(
			function(error, data) {
                if(!error) {
                    aDeferred.resolve(data);
                } else {
                    aDeferred.reject();
                }
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function used to show the new picklist dependency graph
	 * @params: sourceModule - selected module
	 *			sourceFieldValue - source picklist value
	 *			targetFieldValue - target picklist value
	 */
	addNewDependencyPickList : function(sourceModule, sourceFieldValue, targetFieldValue) {
		var thisInstance = this;
		thisInstance.updatedSourceValues = [];
		var params = {};
		params['mode'] = 'getDependencyGraph';
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'IndexAjax';
		params['sourceModule'] = sourceModule;
		params['sourcefield'] = sourceFieldValue;
		params['targetfield'] = targetFieldValue;
		
		app.request.post({data: params}).then(
			function(error, data) {
				var dependencyGraph = jQuery('#dependencyGraph');
				dependencyGraph.html(data);
				var element = dependencyGraph.find('.dependencyMapping');
                app.helper.showHorizontalScroll(element);
				thisInstance.registerDependencyGraphEvents();
				element.find('.pickListDependencyTable').find('tr.blockHeader').find('th').each(function(i, row) {
					if(jQuery(row).css('display') != "none") {
						var sourceValue = jQuery(row).data('source-value');
						if(jQuery.inArray(sourceValue, thisInstance.updatedSourceValues) == -1) {
							thisInstance.updatedSourceValues.push(sourceValue);
						}
					}
				});
			}
		);
	},
	
	/**
	 * This function will delete the pickList Dependency
	 * @params: module - selected module
	 *			sourceField - source picklist value
	 *			targetField - target picklist value
	 */
	deleteDependency : function(module, sourceField, targetField) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'DeleteAjax';
		params['sourceModule'] = module;
		params['sourcefield'] = sourceField;
		params['targetfield'] = targetField;
		
		app.request.post({data: params}).then(
			function(error, data) {
                if(!error) {
                    aDeferred.resolve(data);
                } else {
                    aDeferred.reject();
                }
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Register all the events related to addView of picklist dependency
	 */
	registerAddViewEvents : function() {
		var thisInstance = this;
		var form = jQuery('#pickListDependencyForm');
		thisInstance.getModuleDependencyGraph(form);
		thisInstance.registerPicklistFieldsChangeEvent(form);
		thisInstance.registerSubmitEvent();
	},
	
	/**
	 * Register all the events in editView of picklist dependency
	 */
	registerDependencyGraphEvents : function() {
		var thisInstance = this;
		var form = jQuery('#pickListDependencyForm');
		var dependencyGraph = jQuery('#dependencyGraph');
		thisInstance.registerSelectSourceValuesClick(dependencyGraph);
		thisInstance.registerSelectAllSourceValuesClick(dependencyGraph);
		thisInstance.registerUnSelectAllSourceValuesClick(dependencyGraph);
	},
	
	/**
	 * Register all the events related to listView of picklist dependency
	 */
	registerListViewEvents : function() {
		var thisInstance = this;
		var forModule = jQuery('.settingsPageDiv div').find('.pickListSupportedModules').val();
		thisInstance.listViewForModule = forModule;
		thisInstance.registerSourceModuleChangeEvent();
	},
	
	/**
	 * Register the click event for cancel picklist dependency changes
	 */
	registerCancelButtonClickEvent : function(container) {
		var thisInstance = this;
		container.on('click', '.cancelLink', function() {
			thisInstance.loadListViewContents(thisInstance.listViewForModule);
		})
	},
	
	/**
	 * Register the click event for target fields in dependency graph
	 */
	registerTargetFieldsClickEvent : function(dependencyGraph) {
		var thisInstance = this;
		thisInstance.updatedSourceValues = [];
		dependencyGraph.on('click', 'td.picklistValueMapping', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var sourceValue = currentTarget.data('sourceValue');
			if(jQuery.inArray(sourceValue, thisInstance.updatedSourceValues) == -1) {
				thisInstance.updatedSourceValues.push(sourceValue);
			}
			if(currentTarget.hasClass('selectedCell')) {
				currentTarget.addClass('unselectedCell').removeClass('selectedCell').find('i.fa.fa-check').remove();
			} else {
				currentTarget.addClass('selectedCell').removeClass('unselectedCell').prepend('<i class="fa fa-check pull-left"></i>');
			}
		});
	},
	
	/**
	 * Function used to update the value mapping to save the picklist dependency
	 */
	updateValueMapping : function(dependencyGraph) {
		var thisInstance = this;
		thisInstance.valueMapping = [];
		var sourceValuesArray = thisInstance.updatedSourceValues;
		var dependencyTable = dependencyGraph.find('.pickListDependencyTable');
		for(var key in sourceValuesArray) {
			if(typeof sourceValuesArray[key] == 'string'){
				var encodedSourceValue = sourceValuesArray[key].replace(/"/g, '\\"');
			} else {
				encodedSourceValue = sourceValuesArray[key];
			}
			var selectedTargetValues = dependencyTable.find('td[data-source-value="'+encodedSourceValue+'"]').filter('.selectedCell');
			var targetValues = [];
			if(selectedTargetValues.length > 0) {
				jQuery.each(selectedTargetValues, function(index, element) {
					targetValues.push(jQuery(element).data('targetValue'));
				});
			} else {
				targetValues.push('');
			}
			thisInstance.valueMapping.push({'sourcevalue' : sourceValuesArray[key], 'targetvalues' : targetValues});
		}
	},
	
	/**
	 * register click event for select source values button in add/edit view
	 */
	registerSelectSourceValuesClick : function(dependencyGraph) {
		var thisInstance = this;
		dependencyGraph.find('button.sourceValues').click(function() {
			var selectSourceValues = dependencyGraph.find('.modalCloneCopy');
			var clonedContainer = selectSourceValues.clone(true, true).removeClass('modalCloneCopy');
			var callBackFunction = function(data) {
				data.find('.sourcePicklistValuesModal').removeClass('hide');
				data.find('[name="saveButton"]').click(function(e) {
					thisInstance.selectedSourceValues = [];
					var sourceValues = data.find('.sourceValue');
					jQuery.each(sourceValues, function(index, ele) {
						var element = jQuery(ele);
						var value = element.val();
						if(typeof value == 'string'){
							var encodedValue = value.replace(/"/g, '\\"');
						} else {
							encodedValue = value;
						}
						var hiddenElement = selectSourceValues.find('[class*="'+encodedValue+'"]');
						if(element.is(':checked')) {
							thisInstance.selectedSourceValues.push(value);
							hiddenElement.prop('checked',true);
						} else {
							hiddenElement.prop('checked',false);
						}
					});
					app.helper.hideModal();
					thisInstance.loadMappingForSelectedValues(dependencyGraph);
				});
			}
			
			app.helper.showModal(clonedContainer,{cb: callBackFunction});
		})
	},
	
	/**
	 * register click event for selectAllSourceValues button in add/edit view
	 */
	registerSelectAllSourceValuesClick : function(dependencyGraph) {
		var thisInstance = this;
		dependencyGraph.find('button.selectAllValues').on('click', function() {
			var selectedElements = dependencyGraph.find('.dependencyMapping td.picklistValueMapping').filter(':visible').filter('.unselectedCell');
			if(selectedElements.length > 0) {
				thisInstance.updatedSourceValuesListUpdation(dependencyGraph);
			}
			selectedElements.addClass('selectedCell').removeClass('unselectedCell').prepend('<i class="fa fa-check pull-left"></i>');
		})
	},
	
	/**
	 * register click event for unSelectAllSourceValues button in add/edit view
	 */
	registerUnSelectAllSourceValuesClick : function(dependencyGraph) {
		var thisInstance = this;
		dependencyGraph.find('button.unSelectAllValues').on('click', function() {
			var selectedElements = dependencyGraph.find('.dependencyMapping td.picklistValueMapping').filter(':visible').filter('.selectedCell');
			if(selectedElements.length > 0) {
				thisInstance.updatedSourceValuesListUpdation(dependencyGraph);
			}
			selectedElements.addClass('unselectedCell').removeClass('selectedCell').find('i.fa.fa-check').remove();
		})
	},
	
	/**
	 * Update the list of updatedSourceValues
	 */
	updatedSourceValuesListUpdation : function(dependencyGraph) {
		var thisInstance = this;
		var dependencyTable = dependencyGraph.find('.pickListDependencyTable');
		var sourceValueElements = dependencyTable.find('th:visible');
		thisInstance.updatedSourceValues = [];
		jQuery.each(sourceValueElements, function(index, ele) {
			var sourceValue = jQuery(ele).data('sourceValue');
			thisInstance.updatedSourceValues.push(sourceValue);
		});
	},
	
	/**
	 * Function used to load mapping for selected picklist fields
	 */
	loadMappingForSelectedValues : function(dependencyGraph) {
		var thisInstance = this;
		var allSourcePickListValues = jQuery.parseJSON(dependencyGraph.find('.allSourceValues').val());
		var dependencyTable = dependencyGraph.find('.pickListDependencyTable');
		for(var key in allSourcePickListValues) {
			if(typeof key == 'string'){
				var encodedSourcePickListValue = key.replace(/"/g, '\\"');
			} else {
				encodedSourcePickListValue = key;
			}
			var mappingCells = dependencyTable.find('[data-source-value="'+encodedSourcePickListValue+'"]');
			if(jQuery.inArray(key, thisInstance.selectedSourceValues) == -1) {
				mappingCells.hide();
			} else {
				mappingCells.show();
			}
		}
        dependencyGraph.find('.dependencyMapping').mCustomScrollbar("update");
	},
	
	/**
	 * This function will save the picklist dependency details
	 */
	savePickListDependency : function(form) {
		var thisInstance = this;
		app.helper.showProgress();
		var data = form.serializeFormData();
		data['module'] = app.getModuleName();
		data['parent'] = app.getParentModuleName();
		data['action'] = 'SaveAjax';
        data['sourceModule'] = form.find('[name=sourceModule]').val();
        data['sourceField'] = form.find('[name=sourceField]').val();
        data['targetField'] = form.find('[name=targetField]').val();
		data['mapping'] = JSON.stringify(thisInstance.valueMapping);
		app.request.post({data: data}).then(
			function(error, data) {
                app.helper.hideProgress();
				if(!error) {
					app.helper.showSuccessNotification({message: app.vtranslate('JS_PICKLIST_DEPENDENCY_SAVED')});
					thisInstance.loadListViewContents(thisInstance.listViewForModule);
				}
			}
		);
	},
	
	/**
	 * This function will load the listView contents after Add/Edit picklist dependency
	 */
	loadListViewContents : function(forModule) {
		var thisInstance = this;
		app.helper.showProgress();
		var params = {};
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'List';
		params['formodule'] = forModule;
		
		app.request.pjax({data: params}).then(
			function(error, data) {
				app.helper.hideProgress();
				//replace the new list view contents
				jQuery('.settingsPageDiv div').html(data);
				vtUtils.showSelect2ElementView(jQuery('.settingsPageDiv div').find('.pickListSupportedModules'));
				thisInstance.registerListViewEvents();
			}
		);
	},
	
	/**
	 * register change event for source module in add/edit picklist dependency
	 */
	registerSourceModuleChangeEvent : function() {
		var thisInstance = this;
		var container = jQuery('.settingsPageDiv div');
		container.find('.pickListSupportedModules').on('change', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var forModule = currentTarget.val();
                        thisInstance.loadListViewContents(forModule);
		});
	},
	
	/**
	 * register the form submit event
	 */
	registerSubmitEvent : function() {
		var thisInstance = this;
		var form = jQuery('#pickListDependencyForm');
		var dependencyGraph = jQuery('#dependencyGraph');
        var params = {
            submitHandler: function(form) {
                form = jQuery(form);
                try{
                    thisInstance.updateValueMapping(dependencyGraph);
                }catch(e) {
                    bootbox.alert(e.message);
                    return;
                }
                thisInstance.savePickListDependency(form);
            }
        };
        form.vtValidate(params);
	},
	
	/**
	 * register events for picklist dependency
	 */
	registerEvents : function() {
		var thisInstance = this;
        var container = jQuery('.settingsPageDiv div');
        thisInstance.registerCancelButtonClickEvent(container);
		var form = jQuery('#pickListDependencyForm');
		if(form.length > 0) {
			var element = form.find('.dependencyMapping');
			app.helper.showHorizontalScroll(element);
			if(form.find('.editDependency').val() == "true") {
				form.find('[name="sourceModule"],[name="sourceField"],[name="targetField"]').select2('disable');
				thisInstance.registerDependencyGraphEvents();
				thisInstance.registerSubmitEvent();
			} else {
				thisInstance.registerAddViewEvents();
			}
			thisInstance.registerTargetFieldsClickEvent(jQuery('#dependencyGraph'));
		} else {
			thisInstance.registerListViewEvents();
		}
	}
	
});

Settings_PickListDependency_Js('Settings_PickListDependency_List_Js',{},{});
Settings_PickListDependency_Js('Settings_PickListDependency_Edit_Js',{},{});