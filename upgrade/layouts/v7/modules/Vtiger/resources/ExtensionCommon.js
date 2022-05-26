/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Vtiger_ExtensionCommon_Js",{}, {
    
    init : function() {
        this.addComponents();
    },
    
    addComponents : function() {
        
    },
    
    getListContainer : function() {
        var container = jQuery('.listViewPageDiv');
        if(app.getParentModuleName() === 'Settings') {
            container = jQuery('.settingsPageDiv');
        }
        return container;
    },
    
    registerLogDetailClickEvent : function(container) {
        var extensionInstance = new Vtiger_Extension_Js();
        container.on('click', '.syncLogDetail', function(e) {
            var element = jQuery(e.currentTarget);
            var params = {
                module: extensionInstance.getExtensionModule(),
                view : 'Index',
                mode : 'showLogDetail',
                logid : element.data('id'),
                logtype: element.data('type')
            }
            app.request.post({data: params}).then(function(error, data){
                app.helper.loadPageContentOverlay(data);
				app.helper.showVerticalScroll(jQuery('#detailviewhtml .datacontent'), {'autoHideScrollbar': true});
            });
        });
    },
    
    registerAjaxEvents : function(container) {
        container.on('click', '.navigationLink', function(e) {
            var element = jQuery(e.currentTarget);
            var url = element.data('url');
            
            if(!url) {
                return;
            }
            
            var params = {
                url : url
            }
            app.helper.showProgress();
            app.request.pjax(params).then(function(error, data){
                app.helper.hideProgress();
                if(data) {
                    container.html(data);
                }
            });
        });
    },
    
    registerSettingsMenuClickEvent : function(container) {
        container.on('click', '.settingsPage', function(e) {
            var element = jQuery(e.currentTarget);
            var url = element.data('url');
            if(!url) {
                return;
            }
            
            var params = {
                url : url
            }
            app.helper.showProgress();
            app.request.pjax(params).then(function(error, data){
                app.helper.hideProgress();
                if(data) {
                    container.html(data);
                    vtUtils.applyFieldElementsView(container);
                }
            });
        });
    },
    
    registerSettingsFormSubmitEvent : function(container) {
        container.on('submit', '#settingsForm', function(e) {
            e.preventDefault();
            var form = jQuery('#settingsForm');
            form.vtValidate({onsubmit : false});
            if(form.valid()) {
                form.get(0).submit();
            }
        });
    },
    
    getListUrlParams : function() {
        var extensionInstance = new Vtiger_Extension_Js();
        var params = {
            'module' : app.getModuleName(),
            'view' : 'Extension',
            'extensionModule' : extensionInstance.getExtensionModule(),
            'extensionView' : 'Index',
            'mode' : 'showLogs'
        }
        
        return params;
    },
    
    loadListView : function(params, container) {
        var params = {
            data : params
        }
        app.helper.showProgress();
        app.request.pjax(params).then(function(error, data){
            app.helper.hideProgress();
            if(data) {
                container.html(data);
            }
        });
    },
    
    pageJump : function(container) {
		var element = container.find('#totalPageCount');
		var totalPageNumber = element.text();
		var pageCount;
		
		if(totalPageNumber === ""){
			var totalCountElem = container.find('#totalCount');
			var totalRecordCount = totalCountElem.val();
			if(totalRecordCount !== '') {
				var recordPerPage = container.find('#pageLimit').val();
				if(recordPerPage === '0') recordPerPage = 1;
				pageCount = Math.ceil(totalRecordCount/recordPerPage);
				if(pageCount === 0){
					pageCount = 1;
				}
				element.text(pageCount);
				return;
			}
		}
	},
	
	pageJumpOnSubmit : function(container) {
		var thisInstance = this;
		
		var currentPageElement = container.find('#pageNumber');
		var currentPageNumber = parseInt(currentPageElement.val());
		var newPageNumber = parseInt(container.find('#pageToJump').val());
		var totalPages = parseInt(container.find('#totalPageCount').text());

		if(newPageNumber > totalPages){
			var message = app.vtranslate('JS_PAGE_NOT_EXIST');
			app.helper.showErrorNotification({'message':message})
			return;
		}

		if(newPageNumber === currentPageNumber){
			var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER')+" "+ newPageNumber;
			app.helper.showAlertNotification({'message': message});
			return;
		}

        var params = thisInstance.getListUrlParams();
        params.page = newPageNumber;
		thisInstance.loadListView(params, container);
	},
    
    totalNumOfRecords : function (currentEle, container) {
		var thisInstance = this;
		var totalRecordsElement = container.find('#totalCount');
		var totalNumberOfRecords = totalRecordsElement.val();
		currentEle.addClass('hide');

		if(totalNumberOfRecords === '') {
			thisInstance.getPageCount().then(function(data){
				totalNumberOfRecords = data.numberOfRecords;
				totalRecordsElement.val(totalNumberOfRecords);
				container.find('ul#listViewPageJumpDropDown #totalPageCount').text(data.page);
				thisInstance.showPagingInfo(container);
			});
		}else{
			thisInstance.showPagingInfo(container);
		}
	},
    
    showPagingInfo : function(container){
		var totalNumberOfRecords = container.find('#totalCount').val();
		var pageNumberElement = container.find('.pageNumbersText');
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange.trim()+" "+app.vtranslate('of')+" "+totalNumberOfRecords+"  ";
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
		
		if(listViewEntriesCount !== 0){
			container.find('.pageNumbersText').html(newPagingInfo);
		} else {
			container.find('.pageNumbersText').html("");
		}
	},
    
    registerPaginationEvents : function(container) {
		var thisInstance = this;
		var paginationObj = new Vtiger_Pagination_Js();
		paginationObj.initialize(container);
		
		app.event.on(paginationObj.nextPageButtonClickEventName, function(){
			var pageLimit = container.find('#pageLimit').val();
            var noOfEntries = container.find('#noOfEntries').val();
            var nextPageExist = container.find('#nextPageExist').val();
            var pageNumber = container.find('#pageNumber').val();
			var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
            
            if(noOfEntries === pageLimit && nextPageExist){
                var params = thisInstance.getListUrlParams();
                params.page = nextPageNumber;
                
				thisInstance.loadListView(params, container);
			}
		});
		
		app.event.on(paginationObj.previousPageButtonClickEventName, function(){
			var pageNumber = container.find('#pageNumber').val();
			var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
            
			if(pageNumber > 1) {
                var params = thisInstance.getListUrlParams();
                params.page = previousPageNumber;
                
				thisInstance.loadListView(params, container);
			}
		});
		
		app.event.on(paginationObj.pageJumpButtonClickEventName, function(event, currentEle){
			thisInstance.pageJump(container);
		});
		
		app.event.on(paginationObj.totalNumOfRecordsButtonClickEventName, function(event, currentEle){
			thisInstance.totalNumOfRecords(currentEle, container);
		});
		
		app.event.on(paginationObj.pageJumpSubmitButtonClickEvent, function(event, currentEle){
			thisInstance.pageJumpOnSubmit(container);
		});
	},
    
    registerCRMSettingEvents : function() {
        if(app.getParentModuleName() === 'Settings') {
            var settingsInstance = new Settings_Vtiger_Index_Js();
            settingsInstance.registerBasicSettingsEvents();
        }
    },
    
    registerEvents : function(container) {
        if(typeof(container) == 'undefined'){
            container = this.getListContainer();
        }
        this.registerLogDetailClickEvent(container);
        this.registerAjaxEvents(container);
        this.registerSettingsMenuClickEvent(container);
        this.registerSettingsFormSubmitEvent(container);
        this.registerPaginationEvents(container);
        this.registerCRMSettingEvents();
    }
});