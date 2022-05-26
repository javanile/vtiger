/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_List_Js("Reports_List_Js",{

	listInstance : false,
	
	addReport : function(url){
		var listInstance = Reports_List_Js.getInstance();
		window.location.href=url+'&folder='+listInstance.getCurrentCvId();
	},

	triggerAddFolder : function(url) {
		app.helper.showProgress();
		app.request.get({url: url}).then(
			function(error, data) {
				app.helper.hideProgress();
				var callback = function(data) {
					var addFolderForm = jQuery('#addFolder');
					addFolderForm.vtValidate({
						submitHandler: function(addFolderForm) {
							var formData = jQuery(addFolderForm).serializeFormData();
							app.request.post({data:formData}).then(function(error,data){
								if(error == null){
									app.helper.hideModal();
									app.helper.showSuccessNotification({message:data.message});
									location.reload(true);
								} else {
									app.helper.showErrorNotification({'message' : error.message});
								}
							});
						},
                        validationMeta : false
					});
				}
				var params = {};
				params.cb = callback
				app.helper.showModal(data, params);
			}
		)
	},

	massDelete : function(url) {
		var listInstance = app.controller();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var searchParams = JSON.stringify(listInstance.getListSearchParams());
			var cvId = listInstance.getCurrentCvId();

			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
					var deleteURL = url+'&viewname='+cvId+'&selected_ids='+selectedIds+'&excluded_ids='+excludedIds+'&search_params='+searchParams;
					var deleteMessage = app.vtranslate('JS_RECORDS_ARE_GETTING_DELETED');
					app.helper.showProgress(deleteMessage);
					app.request.post({url:deleteURL}).then(function(error,data) {
							app.helper.hideProgress();
							if(data){
								app.helper.showSuccessNotification({message:app.vtranslate(data)});
								listInstance.massActionPostOperations(data);
							} else {
								app.helper.showErrorNotification({message: error.message});
								listInstance.massActionPostOperations(error);
							}
						});
				},
				function(error, err){
				}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},

	massMove : function(url){
		var listInstance = app.controller();
		var validationResult = listInstance.checkListRecordSelected();
		if(validationResult != true){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname" : cvId,
				"search_params" : JSON.stringify(listInstance.getListSearchParams())
			};
			var params = {
				"url":url,
				"data" : postData
			};
			app.helper.showProgress();
			app.request.post(params).then(function(error,data) {
				app.helper.hideProgress();
				var callBackFunction = function(data){
					var reportsListInstance = new Reports_List_Js();
					reportsListInstance.moveReports().then(function(data){
						if(data){
							if(data.success){
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:data.message});
									listInstance.massActionPostOperations(data);
							}else{
								if(data.denied){
									var messageText= data.message+": "+data.denied;
								}else{
									messageText= data.message;
								}
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:messageText});
								listInstance.massActionPostOperations(data);
							}
						}
					});
				};
				var params = {};
				params.cb = callBackFunction;
				app.helper.showModal(data,params);
			});
		} else{
			listInstance.noRecordSelectedAlert();
		}

	}

},{


	init : function() {
        this.addComponents();
    },

	folderSubmit : function(){
		var aDeferred = jQuery.Deferred();
		var addFolderForm = jQuery('#addFolder');
		addFolderForm.vtValidate({
			submitHandler:function(form,event){
				event.preventDefault();
				var formData = jQuery(form).serializeFormData();
				app.request.post({data:formData}).then(function(error,data){
					aDeferred.resolve(data);
				});
			},
            validationMeta : false
		});
		return aDeferred.promise();
	},

	moveReports : function(){
		var aDeferred = jQuery.Deferred();
		jQuery('#moveReports').on('submit',function(e){
			var formData = jQuery(e.currentTarget).serializeFormData();
			app.helper.showProgress();
			app.request.post({data:formData}).then(function(error,data){
					app.helper.hideProgress();
					aDeferred.resolve(data);
				}
			);
			e.preventDefault();
		});
		return aDeferred.promise();
	},

	updateCustomFilter : function (info){
		var folderId = info.folderId;
		var customFilter =  jQuery("#customFilter");
		var constructedOption = this.constructOptionElement(info);
		var optionId = 'filterOptionId_'+folderId;
		var optionElement = jQuery('#'+optionId);
		if(optionElement.length > 0){
			optionElement.replaceWith(constructedOption);
			customFilter.trigger("liszt:updated");
		} else {
			customFilter.find('#foldersBlock').append(constructedOption).trigger("liszt:updated");
		}
	},

	constructOptionElement : function(info){
		return '<option data-editable="'+info.isEditable+'" data-deletable="'+info.isDeletable+'" data-editurl="'+info.editURL+'" data-deleteurl="'+info.deleteURL+'" class="filterOptionId_'+info.folderId+' filterOptionsLabel" id="filterOptionId_'+info.folderId+'" value="'+info.folderId+'" data-id="'+info.folderId+'">'+info.folderName+'</option>';

	},

	/*
	 * Function to perform the operations after the mass action
	 */
	massActionPostOperations : function(data){
		var thisInstance = this;
		var cvId = this.getCurrentCvId();
        this.clearList();
		if(data){
			var module = app.getModuleName();
			app.request.post({url:'index.php?module='+module+'&view=List&viewname='+cvId}).then(function(error,data) {
				jQuery('#recordsCount').val('');
				jQuery('#totalPageCount').text('');
				app.helper.hideProgress();
				var listViewContainer = thisInstance.getListViewContainer();
				listViewContainer.html(data);
				vtUtils.showSelect2ElementView(listViewContainer.find('select.select2'));
				jQuery('#deSelectAllMsg').trigger('click');
				thisInstance.updatePagination();
			});
		} else {
			app.helper.hideProgress();
			app.helper.showErrorNotification({message:app.vtranslate('JS_LBL_PERMISSION')});
		}
	},
	
	/*
	 * function to delete the folder
	 */
	deleteFolder : function(event,url){
		var thisInstance =this;
		app.request.get({url:url}).then(function(error,data){
			if(data.success) {
				var chosenOption = jQuery(event.currentTarget).closest('.select2-result-selectable');
				var selectOption = thisInstance.getSelectOptionFromChosenOption(chosenOption);
				selectOption.remove();
				var customFilterElement = thisInstance.getFilterSelectElement();
				customFilterElement.trigger("liszt:updated");
				var defaultCvid = "All";
				customFilterElement.select2("val", defaultCvid);
				customFilterElement.trigger('change');
			} else {
				app.helper.hideProgress();
				app.helper.showErrorMessage({message:data.error.message});
			}
		});
	},
	
	/*
	 * Function to register the click event for edit filter
	 */
	registerEditFilterClickEvent : function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		listViewFilterBlock.on('mouseup','li i.editFilter',function(event){
                        thisInstance.getFilterSelectElement().data('select2').close();
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
			var editUrl = currentOptionElement.data('editurl');
			Reports_List_Js.triggerAddFolder(editUrl);
			event.stopPropagation();
		});
	},

	/*
	 * Function to register the click event for delete filter
	 */
	registerDeleteFilterClickEvent: function(){
		var thisInstance = this;
		var listViewFilterBlock = this.getFilterBlock();
		//used mouseup event to stop the propagation of customfilter select change event.
		listViewFilterBlock.on('mouseup','li i.deleteFilter',function(event){
			// To close the custom filter Select Element drop down
			thisInstance.getFilterSelectElement().data('select2').close();
			var liElement = jQuery(event.currentTarget).closest('.select2-result-selectable');
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
					var currentOptionElement = thisInstance.getSelectOptionFromChosenOption(liElement);
					var deleteUrl = currentOptionElement.data('deleteurl');
					thisInstance.deleteFolder(event,deleteUrl);
				},
				function(error, err){
				}
			);
			event.stopPropagation();
		});
	},


	registerEditOverlayContent: function(){
		jQuery('li.reportEdit a').on('click',function(e){
			var url = jQuery(e.currentTarget).data('url');
			app.request.pjax({url: url}).then(function(error, data) {
				 jQuery('#listViewContent').html(data);
            });
		});
	},
	
	registerEventToShowQuickPreview: function() {
        var self = this;
        var listViewPageDiv = self.getListViewContainer();
        listViewPageDiv.on('click', '.quickView', function(e) {
            var element = listViewPageDiv.find(e.currentTarget);
            var row = element.closest('.listViewEntries');
            var recordId = row.data('id');
            self.showQuickPreviewForId(recordId);
        });
        
    },
	showQuickPreviewForId: function(recordId) {
        var self = this;
        var params = {};
        var moduleName = self.getModuleName();
            params['module'] = moduleName;
        params['record'] = recordId;
        params['view'] = 'ListViewQuickPreview';
        params['navigation'] = 'true';
        
        app.helper.showProgress();
        app.request.get({data: params}).then(function(err, response) {
            app.helper.hideProgress();
            jQuery('#helpPageOverlay').css({"width":"550px","box-shadow":"-8px 0 5px -5px lightgrey",'height':'100vh','background':'white'});
            app.helper.loadHelpPageOverlay(response);
            var params = {
                setHeight: "100%",
                alwaysShowScrollbar: 2,
                autoExpandScrollbar: true,
                setTop: 0,
                scrollInertia: 70,
                mouseWheel: {preventDefault: true}
            };
            app.helper.showVerticalScroll(jQuery('.quickPreview .modal-body'), params);
        });
    },
    
    savePinToDashboard : function(element, customParams) {
        var listInstance = Reports_List_Js.getInstance();
        var primarymodule = jQuery(element).data('primemodule');
        var recordId = jQuery(element).data('recordid');
        var widgetTitle = 'ChartReportWidget_' + primarymodule + '_' + recordId;
        var params = {
                module: 'Reports',
                action: 'ChartActions',
                mode: 'pinChartToDashboard',
                reportid: recordId,
                title: widgetTitle
        };
        params = jQuery.extend(params, customParams);
        app.request.post({data: params}).then(function (error,data) {
                if (data.duplicate) {
                        var params = {
                                message: app.vtranslate('JS_CHART_ALREADY_PINNED_TO_DASHBOARD', 'Reports')
                        };
                        app.helper.showSuccessNotification(params);
                        listInstance.massActionPostOperations(data);
                } else {
                        var message = app.vtranslate('JS_CHART_PINNED_TO_DASHBOARD', 'Reports');
                        app.helper.showSuccessNotification({message:message});
                        element.removeClass('vicon-pin');
                        element.addClass('vicon-unpin');
                        element.removeAttr('data-toggle');
                        element.attr('title', app.vtranslate('JSLBL_UNPIN_CHART_FROM_DASHBOARD'));
                }
        });
    },
    
	
	registerEventForPinChartToDashboard: function () {
		var thisInstance = this;
       
        this.getListViewContainer().on('click','.pinToDashboard',function (e) {
			var element = jQuery(e.currentTarget);
			var recordId = jQuery(element).data('recordid');
			var pinned = element.hasClass('vicon-pin');
			if(pinned) {
				if(element.is('[data-toggle]')){
                                    return;
                                }else{
                                    thisInstance.savePinToDashboard(element);
                                }
			} else {
				var params = {
					module: 'Reports',
					action: 'ChartActions',
					mode: 'unpinChartFromDashboard',
					reportid: recordId
				};
				app.request.post({data: params}).then(function (error,data) {
					if(data.unpinned) {
						var message = app.vtranslate('JS_CHART_REMOVED_FROM_DASHBOARD', 'Reports');
						app.helper.showSuccessNotification({message:message});
						element.removeClass('vicon-unpin');
                                                element.addClass('vicon-pin');
                                                if(element.data('dashboardTabCount') > 1) {
                                                    element.attr('data-toggle','dropdown');
                                                }
						element.attr('title', app.vtranslate('JSLBL_PIN_CHART_TO_DASHBOARD'));
					}
				});
			}
		});
             
        
            jQuery('html').on('click','.dashBoardTab', function(e){
                    var element = jQuery(e.currentTarget);
                    var params = {'dashBoardTabId': element.data('tabId')};
                    var originalDropDownMenu = element.closest('.dropdown-menu').data('original-menu');
                    var parent = app.helper.getDropDownmenuParent(originalDropDownMenu);
                    thisInstance.savePinToDashboard(parent.find('.pinToDashboard'), params);
                })
	},
	
	registerFolderEditEvent:function(){
		jQuery("#module-filters").on('click', '.editFilter',function(e){
			var url = jQuery(e.currentTarget).data('url');
			app.request.get({url:url}).then(function(error,data){
				var callBackFunction = function(data){
					var reportsListInstance = new Reports_List_Js();
					reportsListInstance.folderSubmit().then(function(data){
						if(data){
							if(data.success){
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:data.message});
							}else{
								if(data.denied){
									var messageText= data.message+": "+data.denied;
								}else{
									messageText= data.message;
								}
								app.helper.hideModal();
								app.helper.showSuccessNotification({message:messageText});
							}
						}
					});
				};
				var params = {};
				params.cb = callBackFunction;
				app.helper.showModal(data,params);
			});
		});
	},
	
	registerFolderDeleteEvent:function(){
		jQuery("#module-filters").on('click', '.deleteFilter',function(e){
            var element = jQuery(e.target);
			var url = jQuery(e.currentTarget).data('url');
			var folderId = jQuery(e.currentTarget).data('id');
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			app.helper.showConfirmationBox({'message' : message}).then(function(e) {
				app.request.post({url:url}).then(function(error,data){
					if(data.success){
						app.helper.showSuccessNotification({"message":data.message});
						jQuery('.filterName[data-filter-id="'+folderId+'"]').closest(".listViewFilter").remove();
						jQuery('.filterName[data-filter-id="All"]').closest(".listViewFilter").click();
					} else {
						app.helper.showErrorNotification({"message":data.message});
					}
                element.closest('.popover').remove();
                });
			},
			function(error, err){
				//Do nothing
			});
		});
	},
	
	markFolderAsActive : function() {
		var folder = jQuery('[name="folder"]').val();
		jQuery('.filterName[data-filter-id="'+folder+'"]').closest(".listViewFilter").addClass('active');
	},
	
	getCurrentCvId : function() {
		return jQuery('.listViewFilter.active').find('.filterName').data("filter-id");
	},
	
	registerInlineEdit : function(currentTrElement) {
		//do nothing as inline edit not there for reports
	},
    
    registerFolderchange : function(){
        jQuery(".listViewFilter.active").find('.foldericon').removeClass('fa-folder').addClass('fa-folder-open');
        jQuery(".listViewFilter").click(function (e) {
          jQuery(".listViewFilter").find('.foldericon').removeClass('fa-folder-open').addClass('fa-folder');
            var element = jQuery(e.currentTarget);
            var value = element.find('.foldericon');
            if (value.hasClass('fa-folder')) {
                jQuery(value.removeClass('fa-folder').addClass('fa-folder-open'));
            }
        });
    },

	registerFolderScroll : function() {
		app.helper.showVerticalScroll(jQuery('.list-menu-content'), {
			setHeight: 450,
			autoExpandScrollbar: true,
			scrollInertia: 200,
			autoHideScrollbar: true
		});
	},

	registerEvents : function(){
        this._super();
        this.registerEditOverlayContent();
        this.registerEventForPinChartToDashboard();
        this.registerFolderEditEvent();
        this.registerFolderDeleteEvent();
        this.markFolderAsActive();
        this.registerFolderchange();
        this.registerFolderScroll();
	}
});
