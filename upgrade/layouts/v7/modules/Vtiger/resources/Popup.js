/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class("Vtiger_Popup_Js",{

    getInstance: function(module){
		if(!module || typeof module == 'undefined') {
			var module = app.getModuleName();
		}
		var className = jQuery('#popUpClassName').val();
		if(typeof className != 'undefined'){
			var moduleClassName = className;
		}else{
			var moduleClassName = module+"_Popup_Js";
		}
		var fallbackClassName = Vtiger_Popup_Js;
	    if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new fallbackClassName();
		}
	    return instance;
	}

},{
    
    //holds the event name that child window need to trigger
	eventName : '',
	popupPageContentsContainer : false,
	sourceModule : false,
	sourceRecord : false,
	sourceField : false,
	multiSelect : false,
	relatedParentModule : false,
	relatedParentRecord : false,

    getView : function(){
	    var view = jQuery('#view',this.getPopupPageContainer()).val();
	    if(view == '') {
		    view = 'PopupAjax';
	    } else {
		    view = view+'Ajax';
	    }
	    return view;
	},
    
    isMultiSelectMode : function() {
		if(this.multiSelect == false){
			this.multiSelect = jQuery('#multi_select',this.getPopupPageContainer());
		}
		var value = this.multiSelect.val();
		if(value) {
			return value;
		}
		return false;
	},

	/**
	 * Function to get source module
	 */
	getSourceModule : function(){
		if(this.sourceModule == false){
			this.sourceModule = jQuery('#parentModule',this.getPopupPageContainer()).val();
		}
		return this.sourceModule;
	},

	/**
	 * Function to get source record
	 */
	getSourceRecord : function(){
		if(this.sourceRecord == false){
			this.sourceRecord = jQuery('#sourceRecord',this.getPopupPageContainer()).val();
		}
		return this.sourceRecord;
	},

	/**
	 * Function to get source field
	 */
	getSourceField : function(){
		if(this.sourceField == false){
			this.sourceField = jQuery('#sourceField',this.getPopupPageContainer()).val();
		}
		return this.sourceField;
	},

	/**
	 * Function to get related parent module
	 */
	getRelatedParentModule : function(){
		if(this.relatedParentModule == false){
			this.relatedParentModule = jQuery('#relatedParentModule',this.getPopupPageContainer()).val();
		}
		return this.relatedParentModule;
	},
	/**
	 * Function to get related parent id
	 */
	getRelatedParentRecord : function(){
		if(this.relatedParentRecord == false){
			this.relatedParentRecord = jQuery('#relatedParentId',this.getPopupPageContainer()).val();
		}
		return this.relatedParentRecord;
	},

	/**
	 * Function to get Search key
	 */
	getSearchKey : function(){
		return jQuery('#searchableColumnsList',this.getPopupPageContainer()).val();
	},

	/**
	 * Function to get Search value
	 */
	getSearchValue : function(){
		return jQuery('#searchvalue',this.getPopupPageContainer()).val();
	},

	/**
	 * Function to get Order by
	 */
	getOrderBy : function(){
		return jQuery('#orderBy',this.getPopupPageContainer()).val();
	},

	/**
	 * Function to get Sort Order
	 */
	getSortOrder : function(){
			return jQuery("#sortOrder",this.getPopupPageContainer()).val();
	},

	/**
	 * Function to get Page Number
	 */
	getPageNumber : function(){
		return jQuery('#pageNumber',this.getPopupPageContainer()).val();
	},
    
    getRelationId : function (){
        return jQuery('#relationId',this.getPopupPageContainer()).val();
    },
    
    
    
    getPopupPageContainer : function(){
		if(this.popupPageContentsContainer == false) {
			this.popupPageContentsContainer = jQuery('#popupPageContainer');
		}
		return this.popupPageContentsContainer;

	},
    
    getPopupContents : function(){
        return jQuery("#popupContents");
    },
    
    setEventName : function(eventName) {
		this.eventName = eventName;
	},

	getEventName : function() {
		return this.eventName;
	},
    
    getModuleName : function() {
        return this.getPopupPageContainer().find('#module').val();
    },
    
    /**
	 * Function to get complete params
	 */
	getCompleteParams : function(){
		var params = {};
		params['view'] = this.getView();
		params['src_module'] = this.getSourceModule();
		params['src_record'] = this.getSourceRecord();
		params['src_field'] = this.getSourceField();
		params['search_key'] =  this.getSearchKey();
		params['search_value'] =  this.getSearchValue();
		params['orderby'] =  this.getOrderBy();
		params['sortorder'] =  this.getSortOrder();
		params['page'] = this.getPageNumber();
		params['related_parent_module'] = this.getRelatedParentModule();
		params['related_parent_id'] = this.getRelatedParentRecord();
		params['module'] = this.getModuleName();
        params.search_params = JSON.stringify(this.getPopupListSearchParams());
		if(this.isMultiSelectMode()) {
			params['multi_select'] = true;
		}
        params['relationId'] = this.getRelationId();

		// Carry forward meta (LineItem Pricebook Popup > Search)
		var getUrl = this.getPopupPageContainer().find('#getUrl');
		if (getUrl.length) params['get_url'] = getUrl.val();

		return params;
	},
    
    
    getPopupListSearchParams : function(){
            var listViewPageDiv = jQuery('div.popupEntriesDiv');
            var listViewTable = listViewPageDiv.find('.listViewEntriesTable');
            var searchParams = new Array();
            var currentSearchParams = new Array();
            if(jQuery('#currentSearchParams').val())
                currentSearchParams = JSON.parse(jQuery('#currentSearchParams').val());
                listViewTable.find('.listSearchContributor').each(function(index,domElement){
                    var searchInfo = new Array();
                    var searchContributorElement = jQuery(domElement);
                    var fieldName = searchContributorElement.attr('name');
                    var fieldInfo = searchContributorElement.data('fieldinfo');
                    if(fieldName in currentSearchParams) {
                        delete currentSearchParams[fieldName];
                    }

                var searchValue = searchContributorElement.val();

                if(typeof searchValue == "object") {
                    if(searchValue == null) {
                    searchValue = "";
                    }else{
                        searchValue = searchValue.join(',');
                    }
                }
                searchValue = searchValue.trim();
                if(searchValue.length <=0 ) {
                //continue
                    return true;
                }
                var searchOperator = 'c';
                if(fieldInfo.type == "date" || fieldInfo.type == "datetime") {
                    searchOperator = 'bw';
                }else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
                || fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
                fieldInfo.type == "picklist") {
                searchOperator = 'e';
            }
            searchInfo.push(fieldName);
            searchInfo.push(searchOperator);
            searchInfo.push(searchValue);  
            searchParams.push(searchInfo);
        });
        for(var i in currentSearchParams) {
            var fieldName = currentSearchParams[i]['fieldName'];
            var searchValue = currentSearchParams[i]['searchValue'];
            var searchOperator = currentSearchParams[i]['comparator'];
            if(fieldName== null || fieldName.length <=0 ){
                continue;
            }
            var searchInfo = new Array();
            searchInfo.push(fieldName);
            searchInfo.push(searchOperator);
            searchInfo.push(searchValue);
            searchParams.push(searchInfo);
        }
        return new Array(searchParams);        
    },
    
    /**
	 * Function to get Page Records
	 */
	getPageRecords : function(params){
		var aDeferred = jQuery.Deferred();
                app.helper.showProgress();
		Vtiger_BaseList_Js.getPageRecords(params).then(
            function(data){
                jQuery('#popupContents').html(data);
                vtUtils.applyFieldElementsView(jQuery('#popupContents'));
                aDeferred.resolve(data);
            }
        );
		return aDeferred.promise();
	},
    
    /**
	 * Function to handle next page navigation
	 */
	nextPageHandler : function(){
		var aDeferred = jQuery.Deferred();
        var popupContainer = this.getPopupPageContainer();
		var pageLimit = jQuery('#pageLimit',popupContainer).val();
		var noOfEntries = jQuery('#noOfEntries',popupContainer).val();
		if(noOfEntries == pageLimit){
			var pageNumber = jQuery('#pageNumber',popupContainer).val();
			var nextPageNumber = parseInt(pageNumber) + 1;
			var pagingParams = {
					"page": nextPageNumber
				}
			var completeParams = this.getCompleteParams();
			jQuery.extend(completeParams,pagingParams);
			this.getPageRecords(completeParams).then(
				function(data){
					jQuery('#pageNumber',popupContainer).val(nextPageNumber);
					aDeferred.resolve(data);
				}
			);
		}
		return aDeferred.promise();
	},
    
    /**
	 * Function to handle Previous page navigation
	 */
	previousPageHandler : function(){
		var aDeferred = jQuery.Deferred();
        var popupContainer = this.getPopupPageContainer();
		var pageNumber = jQuery('#pageNumber',popupContainer).val();
		var previousPageNumber = parseInt(pageNumber) - 1;
		if(pageNumber > 1){
			var pagingParams = {
				"page": previousPageNumber
			}
			var completeParams = this.getCompleteParams();
			jQuery.extend(completeParams,pagingParams);
			this.getPageRecords(completeParams).then(
				function(data){
					jQuery('#pageNumber',popupContainer).val(previousPageNumber);
					aDeferred.resolve(data);
				}
			);
		}
		return aDeferred.promise();
	},
    
    /**
	 * Function to handle search event
	 */
	searchHandler : function(){
		var aDeferred = jQuery.Deferred();
		var completeParams = this.getCompleteParams();
		completeParams['page'] = 1;
		this.getPageRecords(completeParams).then(
			function(data){
				aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},
    
    /**
	 * Function to update Pagining status
	 */
	updatePagination : function(){
        var popupContainer = this.getPopupPageContainer();
        app.helper.hideProgress();
		var previousPageExist = jQuery('#previousPageExist',popupContainer).val();
		var nextPageExist = jQuery('#nextPageExist',popupContainer).val();
		var previousPageButton = jQuery('#PreviousPageButton',popupContainer);
		var nextPageButton = jQuery('#NextPageButton',popupContainer);
		var listViewEntriesCount = jQuery('#noOfEntries',popupContainer).val();
		var pageStartRange = jQuery('#pageStartRange',popupContainer).val();
		var pageEndRange = jQuery('#pageEndRange',popupContainer).val();
		var totalNumberOfRecords = jQuery('.totalNumberOfRecords',popupContainer);
		var pageNumbersTextElem = jQuery('.pageNumbersText',popupContainer);         
		
        if(previousPageExist !== ""){
			previousPageButton.removeClass('disabled');
		} else if(previousPageExist === "") {
			previousPageButton.addClass('disabled');
		}

		if((nextPageExist !== "")){
			nextPageButton.removeClass('disabled');
		} else if((nextPageExist === "")) {
			nextPageButton.addClass('disabled');
		}
		
		if(listViewEntriesCount !== 0){
			var pageNumberText = pageStartRange+" "+app.vtranslate('to')+" "+pageEndRange;
			pageNumbersTextElem.html(pageNumberText);
			totalNumberOfRecords.removeClass('hide');
		} else {
			pageNumbersTextElem.html("<span>&nbsp;</span>");
			if(!totalNumberOfRecords.hasClass('hide')){
				totalNumberOfRecords.addClass('hide');
			}
		}
        
        this.registerPostPopupLoadEvents();
	},
    
    done : function(result, eventToTrigger){
        var event = "post.popupSelection.click";
        if(typeof eventToTrigger !== 'undefined'){
            event = eventToTrigger;
        }
        if(typeof event == 'function') {
            event(JSON.stringify(result));
        } else {
            app.event.trigger(event, JSON.stringify(result));
        }
        app.helper.hidePopup();
    },
    
    showPopup : function(params,eventToTrigger,callback) {
        // we should hide all existing popup's
        app.helper.hidePopup();
        app.helper.showProgress();
        app.request.post({"data":params}).then(function(err,data) {
            app.helper.hideProgress();
            if(err === null) {
                var options = {};
                if(typeof callback != 'undefined') {
                    options.cb = callback;
                }
                app.helper.showPopup(data,options);
                app.event.trigger("post.Popup.Load",{"eventToTrigger":eventToTrigger, 'module':params.module});
            }
        });
    },
    
    getListViewEntries: function(e){
        e.preventDefault();
        var preEvent = jQuery.Event('pre.popupSelect.click');
        app.event.trigger(preEvent);
        if(preEvent.isDefaultPrevented()){
            return;
        }
		var thisInstance = this;
		var row  = jQuery(e.currentTarget);
		var dataUrl = row.data('url');
		if(typeof dataUrl != 'undefined'){
			dataUrl = dataUrl+'&currency_id='+jQuery('#currencyId').val();
            
		    app.request.post({"url":dataUrl}).then(
			function(err,data){
                            for(var id in data){
				    if(typeof data[id] == "object"){
					var recordData = data[id];
				    }
				}
                thisInstance.done(data,thisInstance.getEventName());
			});
                         e.preventDefault();
		} else {
		    var id = row.data('id');
		    var recordName = row.attr('data-name');
			var recordInfo = row.data('info');
			var referenceModule = jQuery('#popupPageContainer').find('#module').val();
		    var response ={};
		    response[id] = {'name' : recordName,'info' : recordInfo, 'module' : referenceModule};
            thisInstance.done(response,thisInstance.getEventName());
            e.preventDefault();
		}
	},
    

	registerEventForListViewEntryClick : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.off('click', '.listViewEntries');
		popupPageContentsContainer.on('click','.listViewEntries',function(e){
            thisInstance.getListViewEntries(e);
		});
	},
    
    /**
     * Function to register event for Search
     */
    registerEventForSearch : function(){
        var thisInstance = this;
        var popupContainer = this.getPopupPageContainer();
        popupContainer.on('click','#popupSearchButton',function(e){
            jQuery('#totalPageCount',popupContainer).text("");
            thisInstance.searchHandler().then(function(data){
                jQuery('#pageNumber',popupContainer).val(1);
                jQuery('#pageToJump',popupContainer).val(1);
                thisInstance.updatePagination();
            });
        });
    },
    
    /**
	 * Function to handle Sort
	 */
	sortHandler : function(headerElement){
		var aDeferred = jQuery.Deferred();
		//Listprice column should not be sorted so checking for class noSorting
		if(headerElement.hasClass('noSorting')){
			return;
		}
		var fieldName = headerElement.data('columnname');
		var sortOrderVal = headerElement.data('nextsortorderval');
		var sortingParams = {
			"orderby" : fieldName,
			"sortorder" : sortOrderVal
		}
		var completeParams = this.getCompleteParams();
		jQuery.extend(completeParams,sortingParams);
		this.getPageRecords(completeParams).then(
			function(data){
				aDeferred.resolve(data);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
    
    /**
     * Function to register event for Sorting
     * @returns {undefined}
     */
    registerEventForSort : function(){
        var thisInstance = this;
        var popupPageContentsContainer = this.getPopupPageContainer();
        popupPageContentsContainer.on('click','.listViewHeaderValues',function(e){
                var element = jQuery(e.currentTarget);
                thisInstance.sortHandler(element).then(function(data){
                        thisInstance.updatePagination();
                });
        });
    },
    
    /**
	 * Function to register event for popup list Search
	 */
	registerEventForPopupListSearch : function(){
		var thisInstance = this;
        var popupPageContainer = this.getPopupPageContainer();
        popupPageContainer.on('click','[data-trigger="PopupListSearch"]',function(e){
            jQuery('#searchvalue').val("");
            jQuery('#totalPageCount').text("");
			thisInstance.searchHandler().then(function(data){
				jQuery('#pageNumber').val(1);
				jQuery('#pageToJump').val(1);
				thisInstance.updatePagination();
			});
        }).on('keypress',function(e){
			var code = e.keyCode || e.which;
			if(code == 13){
				var element = popupPageContainer.find('[data-trigger="PopupListSearch"]');
				jQuery(element).trigger('click');
			}
		});
	},
	
	pageJump : function() {
		var thisInstance = this;
		var popupContainer = thisInstance.getPopupPageContainer();
		var element = popupContainer.find('#totalPageCount');
		var totalPageNumber = element.text();
		var pageCount;
		
		if(totalPageNumber === ""){
			var totalCountElem = popupContainer.find('#totalCount');
			var totalRecordCount = totalCountElem.val();
			if(totalRecordCount !== '') {
				var recordPerPage = popupContainer.find('#pageLimit').val();
				if(recordPerPage === '0') recordPerPage = 1;
				pageCount = Math.ceil(totalRecordCount/recordPerPage);
				if(pageCount === 0){
					pageCount = 1;
				}
				element.text(pageCount);
				return;
			}

			thisInstance.getPageCount().then(function(data){
				var pageCount = data.page;
				totalCountElem.val(data.numberOfRecords);
				if(pageCount === 0){
					pageCount = 1;
				}
				element.text(pageCount);
			});
		}
	},
	
	pageJumpOnSubmit : function(element) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var popupContainer = this.getPopupPageContainer();
		var currentPageElement = jQuery('#pageNumber', popupContainer);
		var currentPageNumber = parseInt(currentPageElement.val());
		var newPageNumber = parseInt(jQuery('#pageToJump',popupContainer).val());
		var totalPages = parseInt(jQuery('#totalPageCount', popupContainer).text());
		
		if(newPageNumber > totalPages){
			var message = app.vtranslate('JS_PAGE_NOT_EXIST');
			app.helper.showErrorNotification({'message':message})
			return aDeferred.reject();
		}

		if(newPageNumber === currentPageNumber){
			var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER')+" "+newPageNumber;
			app.helper.showAlertNotification({'message': message});
			return aDeferred.reject();
		}
		
		var urlParams = thisInstance.getCompleteParams();
		urlParams['page'] = newPageNumber;
		this.getPageRecords(urlParams).then(
			function(data){
				jQuery('.btn-group', popupContainer).removeClass('open');
				jQuery('#pageNumber',popupContainer).val(newPageNumber);
				aDeferred.resolve(data);
			}
		);
		return aDeferred.promise();
	},
	
    /**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var params = this.getCompleteParams();
		params['view'] = 'PopupAjax';
		params['mode'] = 'getPageCount';
		return params;
	},
	
	/**
	 * Function to get page count and total number of records in list
	 */
	getPageCount : function(){
		var aDeferred = jQuery.Deferred();
		var pageCountParams = this.getPageJumpParams();
		var params = {
			"type" : "GET",
			"data" : pageCountParams
		}
		
		app.request.get(params).then(
			function(err, data) {
				var response;
				if(typeof data !== "object"){
					response = JSON.parse(data);
				} else{
					response = data;
				}
				aDeferred.resolve(response);
			}
		);
		return aDeferred.promise();
	},
	
	totalNumOfRecords : function (currentEle) {
		var thisInstance = this;
		var popupContainer = thisInstance.getPopupPageContainer();
		var totalRecordsElement = popupContainer.find('#totalCount');
		var totalNumberOfRecords = totalRecordsElement.val();
		currentEle.addClass('hide');

		if(totalNumberOfRecords === '') {
			thisInstance.getPageCount().then(function(data){
				totalNumberOfRecords = data.numberOfRecords;
				totalRecordsElement.val(totalNumberOfRecords);
				popupContainer.find('ul#listViewPageJumpDropDown #totalPageCount').text(data.page);
				thisInstance.showPagingInfo();
			});
		}else{
			thisInstance.showPagingInfo();
		}
	},
	
	showPagingInfo : function(){
		var thisInstance = this;
		var popupContainer = thisInstance.getPopupPageContainer();
		var totalNumberOfRecords = jQuery('#totalCount', popupContainer).val();
		var pageNumberElement = jQuery('.pageNumbersText', popupContainer);
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange.trim()+" "+app.vtranslate('of')+" "+totalNumberOfRecords;
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries', popupContainer).val());
		
		if(listViewEntriesCount !== 0){
			jQuery('.pageNumbersText', popupContainer).html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText', popupContainer).html("");
		}
	},
	
	initializePaginationEvents : function() {
		var thisInstance = this;
		var paginationObj = new Vtiger_Pagination_Js;
		var popupContainer = thisInstance.getPopupPageContainer();
		paginationObj.initialize(popupContainer);
		
		app.event.on(paginationObj.nextPageButtonClickEventName, function(){
			thisInstance.nextPageHandler().then(function(data){
				var pageNumber = popupContainer.find('#pageNumber').val();
				popupContainer.find('#pageToJump').val(pageNumber);
				thisInstance.updatePagination();
                thisInstance.handleCheckBoxSelection();
				thisInstance.registerToRemoveEmailFieldClickAttr();
                                thisInstance.registerPostSelectionActions();
			});
		});
		
		app.event.on(paginationObj.previousPageButtonClickEventName, function(){
			thisInstance.previousPageHandler().then(function(data){
				var pageNumber = popupContainer.find('#pageNumber').val();
				popupContainer.find('#pageToJump').val(pageNumber);
				thisInstance.updatePagination();
                thisInstance.handleCheckBoxSelection();
				thisInstance.registerToRemoveEmailFieldClickAttr();
                                thisInstance.registerPostSelectionActions();
			});
		});
		
		app.event.on(paginationObj.pageJumpButtonClickEventName, function(event, currentEle){
			thisInstance.pageJump();
                        thisInstance.registerPostSelectionActions();
		});
		
		app.event.on(paginationObj.totalNumOfRecordsButtonClickEventName, function(event, currentEle){
			thisInstance.totalNumOfRecords(currentEle);
		});
		
		app.event.on(paginationObj.pageJumpSubmitButtonClickEvent, function(event, currentEle){
			thisInstance.pageJumpOnSubmit().then(function(data){
				thisInstance.updatePagination();
                thisInstance.handleCheckBoxSelection();
				thisInstance.registerToRemoveEmailFieldClickAttr();
                thisInstance.registerPostSelectionActions();
			});
		});
	},
    
    
   /**
	* Function to read selection
	*/
	readSelectedIds : function(decode){
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = 'SelectedIdsData';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes) ) {
			var selectedIds = new Array();
			this.writeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if(decode == true){
			if(typeof selectedIds == 'object'){
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
    
    /**
	 * Function to get selected recordIds from selection.
     */
	getSelectedRecordIds : function(){
			var thisInstance = this;
			var recordIds = new Array();
			var selectedData = thisInstance.readSelectedIds();
			for(var data in selectedData){
				if(typeof selectedData[data] == "object"){
					var id = selectedData[data]['id'];
					recordIds.push(id);
				}
			}
			return recordIds;
	},

    /**
     * Function to write selection
	 */
	writeSelectedIds : function(selectedIds){
		jQuery('#selectedIds').data('SelectedIdsData',selectedIds);	
	},
    
    registerSelectButton : function(){
		var popupPageContentsContainer = this.getPopupPageContainer();
		var thisInstance = this;
		popupPageContentsContainer.on('click','button.select', function(e){
			var selectedRecordDetails = {};
			var recordIds = new Array();
			var dataUrl;
			var selectedData = thisInstance.readSelectedIds();
			for(var data in selectedData){
				if(typeof selectedData[data] == "object"){
					var id = selectedData[data]['id'];
					recordIds.push(id);
					var name = selectedData[data]['name'];
					dataUrl = selectedData[data]['url'];
					selectedRecordDetails[id] = {'name' : name};
				}
			}
			var jsonRecorIds = JSON.stringify(recordIds);
			if(Object.keys(selectedRecordDetails).length <= 0) {
				alert(app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD'));
			}else{
				if(typeof dataUrl != 'undefined'){
				    dataUrl = dataUrl+'&idlist='+jsonRecorIds+'&currency_id='+jQuery('#currencyId').val();
				    app.request.get({'url':dataUrl}).then(
					function(error , data){
//						for(var id in data){
//						    if(typeof data[id] == "object"){
//							var recordData = data[id];
//						    }
//						}
                        var recordData = data;
                        var recordDataLength = Object.keys(recordData).length;
                        if(recordDataLength == 1){
							recordData = recordData[0];
						}
						thisInstance.done(recordData, thisInstance.getEventName());
						e.preventDefault();
					},
					function(error,err){

					}
				);
				}else{
				    thisInstance.done(selectedRecordDetails, thisInstance.getEventName());
				}
			}
		});
	},

	selectAllHandler : function(e){
		var thisInstance = this;
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('input.entryCheckBox', tableElement).prop('checked',true);
			var selectedId = thisInstance.readSelectedIds();
			var recordIds = thisInstance.getSelectedRecordIds();
			jQuery('input.entryCheckBox').each(function(index, checkBoxElement){
				var checkBoxJqueryObject = jQuery(checkBoxElement);
				var row = checkBoxJqueryObject.closest('tr');
				var data = row.data();
                if(thisInstance.getView() == 'EmailsRelatedModulePopup' || thisInstance.getView() == 'EmailsRelatedModulePopupAjax'){
					var emailFields = jQuery(row).find('.emailField');
					data.email = emailFields;
				}
				if(!(jQuery.inArray(row.data('id'), recordIds) !== -1)){
					selectedId.push(data);
				}
			});
			thisInstance.writeSelectedIds(selectedId);			
		}else {
			jQuery('input.entryCheckBox', tableElement).removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
			jQuery('input.entryCheckBox').each(function(index, checkBoxElement){
				var selectedId = thisInstance.readSelectedIds();
				var recordIds = thisInstance.getSelectedRecordIds();
				var checkBoxJqueryObject = jQuery(checkBoxElement);
				var row = checkBoxJqueryObject.closest('tr');
				selectedId.splice(jQuery.inArray(row.data('id'), recordIds), 1);
				thisInstance.writeSelectedIds(selectedId);			
			});
			
		}
	},

	registerEventForSelectAllInCurrentPage : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('change','input.selectAllInCurrentPage',function(e){
			thisInstance.selectAllHandler(e);
                        thisInstance.registerPostSelectionActions();
		});
	},
    
    checkBoxChangeHandler : function(e){
		var elem = jQuery(e.currentTarget);
		var parentElem = elem.closest('tr');
		if(elem.is(':checked')){
			parentElem.addClass('highlightBackgroundColor');

		}else{
			parentElem.removeClass('highlightBackgroundColor');
		}
	},

	/**
	 * Function to register event for entry checkbox change
	 */
	registerEventForCheckboxChange : function(){
		var thisInstance = this;
		var popupPageContentsContainer = this.getPopupPageContainer();
		popupPageContentsContainer.on('click','input.entryCheckBox',function(e){
			e.stopPropagation();
			var checkBoxJqueryObject = jQuery(e.currentTarget);
			var row = checkBoxJqueryObject.closest('tr');
			var data = row.data();
            if(thisInstance.getView() == 'EmailsRelatedModulePopup' || thisInstance.getView() == 'EmailsRelatedModulePopupAjax'){
				var emailFields = jQuery(row).find('.emailField');
				data.email = emailFields;
			}
			var selectedId = thisInstance.readSelectedIds();
			if(checkBoxJqueryObject.is(':checked')){
				selectedId.push(data);
				thisInstance.writeSelectedIds(selectedId);
                                thisInstance.registerPostSelectionActions();
			}else{
				var recordIds= thisInstance.getSelectedRecordIds();
				selectedId.splice(jQuery.inArray(row.data('id'),recordIds), 1);
				thisInstance.writeSelectedIds(selectedId);
                                thisInstance.registerPostSelectionActions();
			}
            thisInstance.checkBoxChangeHandler(e);
		});
	},
    
        registerPostSelectionActions : function(){
            var selectedIds = this.getSelectedRecordIds();
            var selectionButton = jQuery('#popupContents').find('.select');
            if(selectedIds.length > 0){
                selectionButton.removeAttr("disabled");
            }else if(selectedIds.length == 0){
                selectionButton.attr("disabled", "disabled");
            }
        },
    
    /**
	 * Function to handle CheckBoxSelection after navigation
	 */
	handleCheckBoxSelection : function(){
		var thisInstance=this;
		var recordIds= thisInstance.getSelectedRecordIds();
		var selectedAll = true;
		jQuery('input.entryCheckBox').each(function(index, checkBoxElement){
			var checkBoxJqueryObject = jQuery(checkBoxElement);
			var parentElem = checkBoxJqueryObject.closest('tr');
			var row = checkBoxJqueryObject.closest('tr');
			var id = row.data('id');
			if((jQuery.inArray(id,recordIds))!== -1){
				checkBoxJqueryObject.prop('checked',true);
			}else{
				selectedAll = false;
			}
		});
		if(selectedAll === true){
            jQuery('.selectAllInCurrentPage').prop('checked',true);
		}
	},
        
    registerPostPopupLoadEvents : function(){
        var popupContainer = jQuery('#popupModal');
        var Options= {
            axis:"yx",
            setHeight:"400px", // Without height, it will not know where to start
            scrollInertia: 200
        };
        app.helper.showVerticalScroll(popupContainer.find('.popupEntriesDiv'), Options);
        
        // For Email Templates popup
        var popupContainer = jQuery('.popupModal');
        if(popupContainer.length != 0) {
            var Options= {
                axis:"yx",
                scrollInertia: 200
            };
            app.helper.showVerticalScroll(popupContainer.find('.popupEntriesDiv'), Options);
        }
    },

	registerToRemoveEmailFieldClickAttr : function() {
		jQuery('#popupContents').find('a.emailField').removeAttr('onclick');
	},

	registerEvents: function(){
		this.registerEventForListViewEntryClick();
		this.registerEventForSearch();
		this.registerEventForSort();
		this.registerEventForPopupListSearch();

		//For Pagination
		this.initializePaginationEvents();
		//END

		this.registerToRemoveEmailFieldClickAttr();
		//for record selection
		this.registerEventForSelectAllInCurrentPage();
		this.registerSelectButton();
		this.registerEventForCheckboxChange();
	}
});
        
jQuery(document).ready(function() {
	app.event.on("post.Popup.Load",function(event,params){
        vtUtils.applyFieldElementsView(jQuery('.myModal'));

		var popupInstance = Vtiger_Popup_Js.getInstance(params.module);
        var eventToTrigger = params.eventToTrigger;
        if(typeof eventToTrigger != "undefined"){
            popupInstance.setEventName(params.eventToTrigger);
        }
        popupInstance.registerEvents();
        popupInstance.registerPostPopupLoadEvents();
    });
});
