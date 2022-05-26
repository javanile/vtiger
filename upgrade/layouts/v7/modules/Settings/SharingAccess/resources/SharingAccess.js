/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class('Settings_Sharing_Access_Js', {}, {

	contentTable : false,
	contentsContainer : false,
	
	init : function() {
		this.setContentTable('.sharingAccessDetails').setContentContainer('#sharingAccessContainer');
	},
	
	setContentTable : function(element) {
		if(element instanceof jQuery){
			this.contentTable = element;
			return this;
		}
		this.contentTable = jQuery(element);
		return this;
	},

	setContentContainer : function(element) {
		if(element instanceof jQuery){
			this.contentsContainer = element;
			return this;
		}
		this.contentsContainer = jQuery(element);
		return this;
	},

	getContentTable : function() {
		return this.contentTable;
	},

	getContentContainer : function() {
		return this.contentsContainer;
	},

	getCustomRuleContainerClassName : function(parentModuleName) {
		return parentModuleName+'CustomRuleList';
	},

	showCustomRulesNextToElement : function(parentElement, rulesListElement) {
		var moduleName = parentElement.data('moduleName')
		var trElementForRuleList = jQuery('<tr class="'+this.getCustomRuleContainerClassName(moduleName)+'"><td class="customRuleContainer row-fluid" colspan="6"></td></tr>');
		jQuery('td',trElementForRuleList).append(rulesListElement);
		jQuery('.ruleListContainer', trElementForRuleList).css('display', 'none');
		parentElement.after(trElementForRuleList).addClass('collapseRow');
		jQuery('.ruleListContainer', trElementForRuleList).slideDown('slow');
	},
	
	/*
	 * function to get custom rules data based on the module
	 * @params: forModule.
	 */
	getCustomRules : function(forModule) {
		var aDeferred = jQuery.Deferred();
		var params = {}
		params['for_module'] = forModule;
		params['module'] = app.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'IndexAjax';
		params['mode'] = 'showRules';
		
		app.request.get({'data' : params}).then(
			function(err, data) {
				if(err === null) {
					aDeferred.resolve(data);
				}else {
					//TODO : Handle error
					aDeferred.reject(error);
				}
			});
		return aDeferred.promise();
	},

	save : function(data) {
		var aDeferred = jQuery.Deferred();

		app.helper.showProgress();
		if(typeof data == 'undefined') {
			data = {};
		}
		app.request.post({'data' : data}).then(
			function(err, data){
				app.helper.hideProgress();
				if(err == null) {
					aDeferred.resolve(data);
				}else {
					aDeferred.reject();
				}
			});

		return aDeferred.promise();
	},
	

	
	/*
	 * function to Save the Custom Rule
	 */
	saveCustomRule : function(form, e) {
		var thisInstance = this;
		var data = form.serializeFormData();

		if(typeof data == 'undefined' ) {
			data = {};
		}
		
		app.helper.showProgress();
		data.module = app.getModuleName();
		data.parent = app.getParentModuleName();
		data.action = 'IndexAjax';
		data.mode = 'saveRule';

		app.request.post({'data' : data}).then(
			function(err, data) {
				app.helper.hideProgress();
				if(err === null) {
					app.helper.hideModal();
					thisInstance.displaySaveCustomRuleResponse(data);
					var moduleName = jQuery('[name="for_module"]', form).val();
					thisInstance.loadCustomRulesList(moduleName);
				}
			}
		);
	},
	
	/*
	 * function to load the CustomRules List for the module after save the custom rule
	 */
	loadCustomRulesList : function(moduleName) {
		var thisInstance = this;
		var contentTable = this.getContentTable();
		
		thisInstance.getCustomRules(moduleName).then(
			function(data){
				var customRuleListContainer = jQuery('.'+thisInstance.getCustomRuleContainerClassName(moduleName),contentTable);
				customRuleListContainer.find('td.customRuleContainer').html(data);
			},
			function(error){
				//TODO: Handle Error
			}
		);
	},
	
	/*
	 * Function to display the SaveCustomRule response message
	 */
	displaySaveCustomRuleResponse : function(data) {
		var success = data['success'];
		var params;
		if(success) {
			params = app.vtranslate('JS_CUSTOM_RULE_SAVED_SUCCESSFULLY');
		} else {
			params = app.vtranslate('JS_CUSTOM_RULE_SAVING_FAILED');
		}
		app.helper.showSuccessNotification({'message' : params})
	},
	
	editCustomRule : function(url) {
		var thisInstance = this;
		app.helper.showProgress();
		
		app.request.get({'url' : url}).then(function(err, data){
			app.helper.hideProgress();
			if(err === null) {
				var params = {
					cb : function(modalContainer){
							var form = jQuery('#editCustomRule');

							form.on('submit', function(e) {
								//To stop the submit of form
								e.preventDefault();
								var formElement = jQuery(e.currentTarget);
								thisInstance.saveCustomRule(formElement, e);
							})
						}
				};
				app.helper.showModal(data, params);
			}
		});
	},
	
	/*
	 * function to delete Custom Rule from the list
	 * @params: deleteElement.
	 */
	deleteCustomRule : function(deleteElement) {
		var deleteUrl = deleteElement.data('url');
		var currentRow = deleteElement.closest('tr.customRuleEntries');
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		app.helper.showConfirmationBox({'message' : message}).then(function(data) {
			app.request.post({'url' : deleteUrl}).then(
				function(err, data){
					if(err === null){
						currentRow.fadeOut('slow');
						var customRuleTable = currentRow.closest('table .customRuleTable');
						//after delete the custom rule, update the sequence number of existing rules
						var nextRows = currentRow.nextAll('tr.customRuleEntries');
						if(nextRows.length > 0){
							jQuery.each(nextRows,function(i,element) {
								var currentSequenceElement = jQuery(element).find('.sequenceNumber');
								var updatedNumber = parseInt(currentSequenceElement.text())-1;
								currentSequenceElement.text(updatedNumber);
							});	
						}
						currentRow.remove();
						var customRuleEntries = customRuleTable.find('.customRuleEntries');
						//if there are no custom rule entries, we have to hide headers also and show the empty message div
						if(customRuleEntries.length < 1) {
							customRuleTable.find('.customRuleHeaders').fadeOut('slow').remove();
							customRuleTable.parent().find('.recordDetails').removeClass('hide');
							customRuleTable.addClass('hide');
						}
					}else{
						app.helper.showSuccessNotification({'message' : err.message});
					}
				});
			},
			function(error, err){
			}
		);
	},
	
	/*
	 * function to register click event for radio buttons
	 */
	registerSharingAccessEdit : function() {
		var contentContainer = this.getContentContainer();
		contentContainer.one('click','input:radio', function(e){
			contentContainer.find('.saveSharingAccess').removeClass('hide');
		});
	},
	
	/*
	 * Function to register change event for dependent modules privileges
	 */
	registerDependentModulesPrivilegesChange : function() {
		var thisInstance = this;
		var container = thisInstance.getContentContainer();
		var contentTable = this.getContentTable();
		var modulesList = JSON.parse(container.find('.dependentModules').val());
		
		jQuery.each(modulesList, function(moduleName, dependentList) {
			var dependentPrivilege = contentTable.find('[data-module-name="'+moduleName+'"]').find('[data-action-state="Private"]');
			dependentPrivilege.change(function(e) {
				var currentTarget = jQuery(e.currentTarget);
				if(currentTarget.is(':checked')) {
					var message = app.vtranslate('JS_DEPENDENT_PRIVILEGES_SHOULD_CHANGE');
					app.helper.showAlertBox({'message': message});
					jQuery.each(dependentList, function(index, module) {
						contentTable.find('[data-module-name="'+module+'"]').find('[data-action-state="Private"]').prop('checked', true);
					})
				}
			})
		})
	},
	
	registerEvents : function() {
		var thisInstance = this;
		var contentTable = this.getContentTable();
		var contentContainer = this.getContentContainer();
		thisInstance.registerSharingAccessEdit();
		thisInstance.registerDependentModulesPrivilegesChange();
		
		contentTable.on('click', 'td.triggerCustomSharingAccess', function(e){
			var element = jQuery(e.currentTarget);
			var trElement = element.closest('tr');
			var moduleName = trElement.data('moduleName');
			var customRuleListContainer = jQuery('.'+thisInstance.getCustomRuleContainerClassName(moduleName),contentTable);
			
			if(customRuleListContainer.length > 0) {
				if(customRuleListContainer.css('display') === 'none') {
					customRuleListContainer.show();
					jQuery('.ruleListContainer', customRuleListContainer).slideDown('slow');
					trElement.addClass('collapseRow');
					element.find('button i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
				}else{
					jQuery('.ruleListContainer', customRuleListContainer).slideUp('slow', function(e) {
						customRuleListContainer.css('display', 'none');
					});
					element.find('button i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
					trElement.removeClass('collapseRow');
				}
				return;
			}
			
			app.helper.showProgress();
			
			thisInstance.getCustomRules(moduleName).then(
				function(data){
					app.helper.hideProgress();
					thisInstance.showCustomRulesNextToElement(trElement, data);
					element.find('button i').removeClass('fa-chevron-down').addClass('fa-chevron-up');
			});
		});

		contentTable.on('click', 'button.addCustomRule' , function(e) {
			var button = jQuery(e.currentTarget);
			thisInstance.editCustomRule(button.data('url'));
		})

		contentTable.on('click', '.edit', function(e){
			var editElement = jQuery(e.currentTarget);
			var editUrl = editElement.data('url');
			thisInstance.editCustomRule(editUrl);
		});
		
		contentTable.on('click', '.delete', function(e){
			var deleteElement = jQuery(e.currentTarget);
			thisInstance.deleteCustomRule(deleteElement);
		});
		
		contentContainer.on('submit', '#EditSharingAccess', function(e){
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			var data = form.serializeFormData();
			thisInstance.save(data).then(
				function(data) {
					contentContainer.find('.saveSharingAccess').addClass('hide');
					thisInstance.registerSharingAccessEdit();
					app.helper.showSuccessNotification({'message' : app.vtranslate('JS_NEW_SHARING_RULES_APPLIED_SUCCESSFULLY')});
				},
				function(error,err){
				}
			);
		});
		//scrollbar is not needed for shaing access	
	}
});

Settings_Sharing_Access_Js('Settings_SharingAccess_Index_Js',{},{
    init : function() {
        this._super();
        this.addComponents();
    },

    addComponents : function() {
            this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
    },
    
});
