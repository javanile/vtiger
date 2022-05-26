/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Menu_Editor_Js', {}, {

	getContainer : function() {
		return jQuery('#listViewContent');
	},

	registerAddModule : function(container) {
		var thisInstance = this;
		container.on('click', '.menuEditorAddItem', function(e) {
			var element = jQuery(e.currentTarget);
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				view: 'EditAjax',
				mode: 'showAddModule',
				appname: element.data('appname')
			}
			app.helper.showProgress();
			app.request.post({data: params}).then(function(err, data){
				app.helper.hideProgress();
				app.helper.showModal(data, {cb: function(data){
					thisInstance.registerAddModulePreSaveEvents(data);
				}});
			});
		});
	},

	setSaveButtonState : function(container) {
		var appname = container.find('#appname').val();
		if(!container.find('.modulesContainer[data-appname='+appname+']').find('.addModule').length) {
			container.find('[type="submit"]').attr('disabled','disabled');
		} else {
			container.find('[type="submit"]').removeAttr('disabled');
		}
	},

	registerAddModulePreSaveEvents : function(data) {
		var self = this;
		var container = data.find('.addModuleContainer');

		container.on('click', '.addModule', function(e){
			var element = jQuery(e.currentTarget);
			element.toggleClass('selectedModule');
		});

		container.on('click', '.moduleSelection li a', function(){
			var selText = $(this).text();
			var appname = $(this).data('appname');
			$(this).parents('.btn-group').find('.dropdown-toggle').html(selText+'&nbsp;&nbsp; <span class="caret"></span>');
			container.find('.modulesContainer').addClass('hide');
			container.find('.modulesContainer[data-appname='+appname+']').removeClass('hide')
			.find('.addModule').removeClass('selectedModule');
			container.find('#appname').val(appname);
			self.setSaveButtonState(container);
		});

		self.setSaveButtonState(container);

		container.find('[type="submit"]').on('click', function(e) {
			var modulesContainer = container.find('.modulesContainer').not('.hide');
			var modules = modulesContainer.find('.addModule');
			var selectedModules = modules.filter('.selectedModule');
			if(!selectedModules.length) {
				app.helper.showAlertNotification({
					'message' : app.vtranslate('JS_PLEASE_SELECT_A_MODULE')
				});
			} else {
				jQuery(this).attr('disabled','disabled');
				var appname = container.find('#appname').val();
				var sourceModules = [];
				selectedModules.each(function(i, element) {
					var selectedModule = jQuery(element);
					sourceModules.push(selectedModule.data('module'));
				});

				if(sourceModules.length) {
					var params = {
						module: app.getModuleName(),
						parent: app.getParentModuleName(), 
						sourceModules: sourceModules,
						appname: appname,
						action: 'SaveAjax',
						mode: 'addModule'
					};
					app.helper.showProgress();
					app.request.post({data: params}).then(function(err, data) {
						app.helper.showSuccessNotification({message: app.vtranslate('JS_MODULE_ADD_SUCCESS')});
						app.helper.hideProgress();
						window.location.reload();
					});

					app.helper.hideModal();
				}
			}  
		});
	},

	registerRemoveModule : function(container) {
		container.on('click', '.menuEditorRemoveItem', function(e) {
			var element = jQuery(e.currentTarget);
			var parent = element.closest('.modules');
			var params = {
				module: app.getModuleName(),
				parent: app.getParentModuleName(),
				action: 'SaveAjax',
				mode: 'removeModule',
				sourceModule: parent.data('module'),
				appname: parent.closest('.appContainer').data('appname')
			}

			app.helper.showProgress();
			app.request.post({data: params}).then(function(err, data){
				app.helper.hideProgress();
				element.closest('.modules').fadeOut(500, function(){ 
					app.helper.showSuccessNotification({message: app.vtranslate('JS_MODULE_REMOVED')});
					jQuery(this).remove(); 
				});
			});
		});
	},

	registerSortModule : function(container) {
		var sortableElement = container.find('.sortable');
		var thisInstance = this;
		var stopSorting = false;
		var move = false;
		sortableElement.sortable({
			items: '.modules',
			'revert' : true,
			receive: function (event, ui) {
				move = true;
				if (jQuery(ui.item).hasClass("noConnect")) {
					stopSorting = true;
					jQuery(ui.sender).sortable("cancel");
				}
			},
			over : function(event, ui){
				stopSorting = false;
			},
			stop: function(e, ui) {
				var element = jQuery(ui.item);
				var parent = element.closest('.sortable');
				parent.find('.menuEditorAddItem').appendTo(parent);
				var appname = parent.data('appname');
				var moduleSequenceArray = {}
				jQuery.each(parent.find('.modules'),function(i,element) {
					moduleSequenceArray[jQuery(element).data('module')] = ++i;
				});
				var moved = move;
				if(move) {
					move = false;
				}
				if(!stopSorting) {
					thisInstance.saveSequence(moduleSequenceArray, appname, moved);
				} else {
					if(!element.hasClass('noConnect')) {
						thisInstance.saveSequence(moduleSequenceArray, appname);
					} else {
						app.helper.showErrorNotification({message: app.vtranslate('JS_MODULE_NOT_DRAGGABLE')});
					}
				}
			}
		});
		sortableElement.disableSelection();
	},

	saveSequence : function(moduleSequenceArray, appname, move) {
		var params = {
			module: app.getModuleName(),
			parent: app.getParentModuleName(),
			action: 'SaveAjax',
			mode: 'saveSequence',
			sequence: JSON.stringify(moduleSequenceArray),
			appname: appname
		}

		app.helper.showProgress();
		app.request.post({data: params}).then(function(err, data){
			if(move) {
				app.helper.showSuccessNotification({message: app.vtranslate('JS_MODULE_MOVED_SUCCESSFULLY')});
			} else {
				app.helper.showSuccessNotification({message: app.vtranslate('JS_MODULE_SEQUENCE_SAVED')})
			}
			app.helper.hideProgress();
			app.event.trigger('POST.MENU.MOVE', params);
		});
	},

	registerEvents : function() {
		var container = this.getContainer();
		this.registerAddModule(container);
		this.registerRemoveModule(container);
		this.registerSortModule(container);
		var instance = new Settings_Vtiger_Index_Js();
		instance.registerBasicSettingsEvents();
	}
});

window.onload = function() {
	var settingMenuEditorInstance = new Settings_Menu_Editor_Js();
	settingMenuEditorInstance.registerEvents();
};