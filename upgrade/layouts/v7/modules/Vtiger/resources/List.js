/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Vtiger_List_Js", {
	listInstance: false,
	filterClick: false,
	getInstance: function () {
		if (Vtiger_List_Js.listInstance == false) {
			var module = app.getModuleName();
			var parentModule = app.getParentModuleName();
			if (parentModule == 'Settings') {
				var moduleClassName = parentModule + "_" + module + "_List_Js";
				if (typeof window[moduleClassName] == 'undefined') {
					moduleClassName = module + "_List_Js";
				}
				var fallbackClassName = parentModule + "_Vtiger_List_Js";
				if (typeof window[fallbackClassName] == 'undefined') {
					fallbackClassName = "Vtiger_List_Js";
				}
			} else {
				moduleClassName = module + "_List_Js";
				fallbackClassName = "Vtiger_List_Js";
			}
			if (typeof window[moduleClassName] != 'undefined') {
				var instance = new window[moduleClassName]();
			} else {
				var instance = new window[fallbackClassName]();
			}
			Vtiger_List_Js.listInstance = instance;
			return instance;
		}
		return Vtiger_List_Js.listInstance;
	},
	triggerMassEdit: function (url) {
		var listInstance = window.app.controller();
		var selectedRecordCount = listInstance.getSelectedRecordCount();
		if (selectedRecordCount > 500) {
			app.helper.showErrorNotification({message: app.vtranslate('JS_MASS_EDIT_LIMIT')});
			return;
		}
		app.event.trigger('post.listViewMassEdit.click', url);
		var params = listInstance.getListSelectAllParams(true);
		if (params) {
			app.helper.showProgress();
			app.request.get({url: url, data: params}).then(function (error, data) {
				var overlayParams = {'backdrop': 'static', 'keyboard': false};
				app.helper.loadPageContentOverlay(data, overlayParams).then(function (container) {
					app.event.trigger('post.listViewMassEdit.loaded', container);
				})
				app.helper.hideProgress();
			});
		}
		else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerSendSms: function (massActionUrl, module) {
		var listInstance = app.controller();
		var validationResult = listInstance.checkListRecordSelected();
		if (validationResult != true) {
			app.helper.showProgress();
			app.helper.checkServerConfig(module).then(function (data) {
				app.helper.hideProgress();
				if (data == true) {
					Vtiger_List_Js.triggerMassAction(massActionUrl);
				} else {
					app.helper.showAlertBox({message: app.vtranslate('JS_SMS_SERVER_CONFIGURATION')})
				}
			});
		}
		else {
			listInstance.noRecordSelectedAlert();
		}

	},
	getListViewInstance: function () {
		return window.app.controller();
	},
	massDeleteRecords: function (url, instance) {
		var listInstance = app.controller();
		listInstance.performMassDeleteRecords(url);
	},
	triggerExportAction: function (exportActionUrl) {
		var listInstance = window.app.controller();
		listInstance.performExportAction(exportActionUrl);
	},
	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail: function (massActionUrl, module, params) {
		var listInstance = window.app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(false);
		if (listSelectParams) {
			var postData = listInstance.getDefaultParams();
			delete postData.module;
			delete postData.view;
			delete postData.parent;
			jQuery.extend(postData, listSelectParams);
			var data = app.convertUrlToDataParams(massActionUrl);
			jQuery.extend(postData, data);
			if (params) {
				jQuery.extend(postData, params);
			}
			Vtiger_Index_Js.showComposeEmailPopup(postData);
		}
		else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerTransferOwnership: function (massActionUrl) {
		var listInstance = window.app.controller();
		var listSelectParams = listInstance.getListSelectAllParams();
		if (listSelectParams) {
			app.helper.showProgress();
			app.request.get({'url': massActionUrl}).then(
					function (error, data) {
						app.helper.hideProgress();
						if (data) {
							var callback = function (data) {
								var chagneOwnerForm = jQuery('#changeOwner');
								chagneOwnerForm.vtValidate({
									submitHandler: function (form) {
										listInstance.transferOwnershipSave(jQuery(form));
										return false;
									}
								});
							}
							var params = {};
							params.cb = callback
							app.helper.showModal(data, params);
						}
					}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerMassAction: function (massActionUrl) {

		var listInstance = window.app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(true);
		if (listSelectParams) {
			var postData = listInstance.getDefaultParams();
			delete postData.module;
			delete postData.view;
			delete postData.parent;
			var data = app.convertUrlToDataParams(massActionUrl);
			postData = jQuery.extend(postData, data);
			postData = jQuery.extend(postData, listSelectParams);
			app.helper.showProgress();
			app.request.get({'data': postData}).then(
					function (err, data) {
						app.helper.hideProgress();
						if (data) {
							app.helper.showModal(data, {'cb': function (modal) {
									if (postData.mode === "showAddCommentForm") {
										var vtigerInstance = Vtiger_Index_Js.getInstance();
										vtigerInstance.registerMultiUpload();
									}
									app.event.trigger('post.listViewMassAction.loaded', modal);
								}
							});
						}
					}
			);
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	showDuplicateSearchForm: function (url) {
		app.helper.showProgress();
		app.request.get({'url': url}).then(function (error, data) {
			if (data) {
				app.helper.hideProgress();
				app.helper.showModal(data, {'cb': function (container) {
						container.find('form').vtValidate({})
					}});
			}
		})
	},
	triggerMergeRecord: function () {
		var listInstance = window.app.controller();
		listInstance.performMergeRecords();
	},
	triggerAddStar: function () {
		var listInstance = app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(true)
		if (listSelectParams) {
			listInstance.massStarSave({'value': 1});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerRemoveStar: function () {
		var listInstance = app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(true)
		if (listSelectParams) {
			listInstance.massStarSave({'value': 0});
		} else {
			listInstance.noRecordSelectedAlert();
		}
	},
	triggerPreviewForRecord: function (recordId, appName) {
		var listInstance = Vtiger_List_Js.getInstance();
		listInstance.showQuickPreviewForId(recordId, appName, '');
		return false;
	},
	triggerAddTag: function () {
		var listInstance = app.controller();
		var listInstance = app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(true);
		if (listSelectParams) {
			listInstance.showAllTags(jQuery('.main-container'));
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},
	triggerRemoveTag: function (tagId) {
		var listInstance = app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(true);
		if (listSelectParams) {
			listInstance.massRemoveTag(tagId);
		} else {
			listInstance.noRecordSelectedAlert();
		}

	},
	/**
	 * Function to show the content of a file in an iframe
	 * @param {type} e
	 * @param {type} recordId
	 * @returns {undefined}
	 */
	previewFile: function (e, recordId, attachmentId) {
		e.stopPropagation();
		if (recordId) {
			var params = {
				module: 'ModComments',
				view: 'FilePreview',
				record: recordId,
				attachmentid: attachmentId
			};
			app.request.post({data: params}).then(function (err, res) {
				app.helper.showModal(res);
				jQuery('.filePreview .preview-area').height(jQuery(window).height() - 143);
			});
		}
	}
}, {
	//contains the List View element.
	listViewContainer: false,
	_moduleName: false,
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addModuleSpecificComponent('CustomView');
		this.addModuleSpecificComponent('ListSidebar');
		this.addIndexComponent();
		this.addComponent('Vtiger_MergeRecords_Js');
		this.addModuleSpecificComponent('Pagination');
		this.addComponent('Vtiger_Tag_Js');
},
	addIndexComponent: function () {
		this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
	},
	getListViewContainer: function () {
		if (this.listViewContainer === false) {
			this.listViewContainer = jQuery('#listViewContent');
		}
		return this.listViewContainer;
	},
	setListViewContainer: function (container) {
		this.listViewContainer = container;
		return this;
	},
	recordSelectTrackerInstance: false,
	getRecordSelectTrackerInstance: function () {
		if (this.recordSelectTrackerInstance === false) {
			this.recordSelectTrackerInstance = Vtiger_RecordSelectTracker_Js.getInstance();
			this.recordSelectTrackerInstance.setCvId(this.getCurrentCvId());
		} else {
			this.recordSelectTrackerInstance.setCvId(this.getCurrentCvId());
		}
		return this.recordSelectTrackerInstance;
	},
	getCurrentCvId: function () {
		var listViewContainer = this.getListViewContainer();
		return listViewContainer.find('[name="cvid"]').val();
	},
	getModuleName: function () {
		if (this._moduleName != false) {
			return this._moduleName;
		}
		return app.module();
	},
	setModuleName: function (module) {
		this._moduleName = module;
		return this;
	},
	/*
	 * Function to return alerts if no records selected.
	 */
	noRecordSelectedAlert: function () {
		return app.helper.showAlertBox({message: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD')});
	},
	registerRemoveListViewSort: function () {
		var listViewContainer = this.getListViewContainer();
		var thisInstance = this;

		listViewContainer.on('click', '.removeSorting', function (e) {
			var cvId = thisInstance.getCurrentCvId();
			thisInstance.loadFilter(cvId, {'mode': 'removeSorting'});
		});
	},
	performMassDeleteRecords: function (url) {
		var listInstance = this;
		var params = {};
		var paramArray = url.slice(url.indexOf('?') + 1).split('&');
		for (var i = 0; i < paramArray.length; i++) {
			var param = paramArray[i].split('=');
			params[param[0]] = param[1];
		}
		var listSelectParams = listInstance.getListSelectAllParams(true);
		listSelectParams = jQuery.extend(listSelectParams, params);
		if (listSelectParams) {
			var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
			app.helper.showConfirmationBox({'message': message}).then(function (e) {
				listSelectParams['module'] = app.getModuleName();
				listSelectParams['action'] = 'MassDelete';
				listSelectParams['search_params'] = JSON.stringify(listInstance.getListSearchParams());
				app.helper.showProgress();
				app.request.post({data: listSelectParams}).then(
						function (error, result) {
							app.helper.hideProgress();
							if (error) {
								app.helper.showErrorNotification();
								return;
							}
							listInstance.clearList();
							listInstance.getPageCount().then(function (data) {
								var pageCount = parseInt(data.page);
								var container = listInstance.getListViewContainer();
								var currentPageElement = container.find('#pageNumber');
								var currentPageNumber = parseInt(currentPageElement.val());
								var params = {};
								if (currentPageNumber > pageCount) {
									params = {'page': 1};
								}
								listInstance.loadListViewRecords(params);
							});
						}
				);
			});
		}
		else {
			listInstance.noRecordSelectedAlert();
		}
	},
	performExportAction: function (url) {
		var listInstance = this;
		var listViewContainer = this.getListViewContainer();
		var pageNumber = listViewContainer.find('#pageNumber').val();
		var postData = listInstance.getDefaultParams();

		var params = app.convertUrlToDataParams(url);
		postData = jQuery.extend(postData, params);
		var listSelectAllParams = listInstance.getListSelectAllParams(true);
		listSelectAllParams['search_params'] = JSON.stringify(listInstance.getListSearchParams());
		postData = jQuery.extend(postData, listSelectAllParams);

		app.helper.showProgress();
		app.request.get({data: postData}).then(function (error, data) {
			app.helper.loadPageContentOverlay(data).then(function (container) {
				container.find('form#exportForm').on('submit', function () {
					jQuery(this).find('button[type="submit"]').attr('disabled', 'disabled');
					app.helper.hidePageContentOverlay();
				});
			});
			app.helper.hideProgress();
		});
	},
	registerListViewSort: function () {
		var listViewContainer = this.getListViewContainer();
		var thisInstance = this;
		listViewContainer.on('click', '.listViewContentHeaderValues', function (e) {
			var fieldName = jQuery(e.currentTarget).data('columnname');
			var sortOrderVal = jQuery(e.currentTarget).data('nextsortorderval');
			if (sortOrderVal === 'ASC') {
				jQuery('i', e.currentTarget).addClass('fa-sort-asc');
			} else {
				jQuery('i', e.currentTarget).addClass('fa-sort-desc');
			}
			listViewContainer.find('[name="sortOrder"]').val(sortOrderVal);
			listViewContainer.find('[name="orderBy"]').val(fieldName);
			var cvId = thisInstance.getCurrentCvId();
			thisInstance.loadListViewRecords();
		});
	},
	// To clear sorting information before changing Custom View
	resetData: function () {
		var self = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.find('#pageNumber').val("1");
		listViewContainer.find('#pageToJump').val('1');
		listViewContainer.find('#orderBy').val('');
		listViewContainer.find("#sortOrder").val('');
		listViewContainer.find('#currentSearchParams').val(JSON.stringify(new Array()));
		listViewContainer.find('#currentTagParams').val(JSON.stringify(new Array()));
		listViewContainer.find('[name="tag"]').val('');
		listViewContainer.find('[name="list_headers"]').val('');
		var recordTrackerInstance = self.getRecordSelectTrackerInstance();
		recordTrackerInstance.clearList();
	},
	loadFilter: function (id, loadParams) {
		if (typeof loadParams == 'undefined')
			loadParams = {};

		var params = {
			module: this.getModuleName(),
			view: app.view(),
			viewname: id
		}
		params = jQuery.extend(params, loadParams);
		this.loadListViewRecords(params);
	},
	loadListViewRecords: function (urlParams) {
		var self = this;
		var aDeferred = jQuery.Deferred();
		var defParams = this.getDefaultParams();
		if (typeof urlParams == "undefined") {
			urlParams = {};
		}
		if (typeof urlParams.search_params == "undefined") {
			urlParams.search_params = JSON.stringify(this.getListSearchParams(false));
		}
		urlParams = jQuery.extend(defParams, urlParams);
		app.helper.showProgress();

		if (urlParams['view'] == 'ListAjax') {
			app.request.post({data: urlParams}).then(function (err, res) {
				aDeferred.resolve(res);
				self.postLoadListViewRecords(res);
			});
		} else {
			app.request.pjax({data: urlParams}).then(function (err, res) {
				aDeferred.resolve(res);
				self.postLoadListViewRecords(res);
			});
		}
		return aDeferred.promise();
	},
	postLoadListViewRecords: function (res) {
		var self = this;
		self.placeListContents(res);
		app.event.trigger('post.listViewFilter.click', jQuery('.searchRow'));
		app.helper.hideProgress();
		self.markSelectedIdsCheckboxes();
		self.registerDynamicListHeaders();
		self.registerPostLoadListViewActions();
	},
	placeListContents: function (contents) {
		var container = this.getListViewContainer();
		container.html(contents);
	},
	getDefaultParams: function () {
		var container = this.getListViewContainer();
		var pageNumber = container.find('#pageNumber').val();
		var module = this.getModuleName();
		var parent = app.getParentModuleName();
		var cvId = this.getCurrentCvId();
		var orderBy = container.find('[name="orderBy"]').val();
		var sortOrder = container.find('[name="sortOrder"]').val();
		var appName = container.find('#appName').val();
		var params = {
			'module': module,
			'parent': parent,
			'page': pageNumber,
			'view': "List",
			'viewname': cvId,
			'orderby': orderBy,
			'sortorder': sortOrder,
			'app': appName
		}
		params.search_params = JSON.stringify(this.getListSearchParams());
		params.tag_params = JSON.stringify(this.getListTagParams());
		params.nolistcache = (container.find('#noFilterCache').val() == 1) ? 1 : 0;
		params.starFilterMode = container.find('.starFilter li.active a').data('type');
		params.list_headers = container.find('[name="list_headers"]').val();
		params.tag = container.find('[name="tag"]').val();
		return params;
	},
	getListTagParams: function () {
		var listViewPageDiv = this.getListViewContainer();
		var currentTagParams = new Array();
		var tagParams = new Array();

		if (listViewPageDiv.find('#currentTagParams').val()) {
			currentTagParams = JSON.parse(listViewPageDiv.find('#currentTagParams').val());
		}

		for (var i in currentTagParams) {
			var fieldName = currentTagParams[i]['fieldName'];
			var searchValue = currentTagParams[i]['searchValue'];
			var searchOperator = currentTagParams[i]['comparator'];
			if (fieldName == null || fieldName.length <= 0) {
				continue;
			}
			var tagInfo = new Array();
			tagInfo.push(fieldName);
			tagInfo.push(searchOperator);
			tagInfo.push(searchValue);
			tagParams.push(tagInfo);
		}
		if (tagParams.length > 0) {
			var listTagParams = new Array(tagParams);
		} else {
			var listTagParams = new Array();
		}
		return listTagParams;
	},
	isInteger: function (num) {
		return !isNaN(num);
	},
	getListSearchParams: function (includeStarFilters) {
		if (typeof includeStarFilters == "undefined") {
			includeStarFilters = true;
		}
		var listViewPageDiv = this.getListViewContainer();
		var listViewTable = listViewPageDiv.find('.searchRow');
		var searchParams = new Array();
		var currentSearchParams = new Array();
		if (listViewPageDiv.find('#currentSearchParams').val()) {
			currentSearchParams = JSON.parse(listViewPageDiv.find('#currentSearchParams').val());
		}

		if (this.filterClick) {
			return;
		}
		listViewTable.find('.listSearchContributor').each(function (index, domElement) {
			var searchInfo = new Array();
			var searchContributorElement = jQuery(domElement);
			var fieldName = searchContributorElement.attr('name');
			var fieldInfo = uimeta.field.get(fieldName);

			/**
			 *  If we have any related record fields in the list, then list search will not work.
			 *  Because, uimeta will only hold field info of current module not all related modules
			 */
			if (typeof fieldInfo == 'undefined') {
				fieldInfo = searchContributorElement.data("fieldinfo");
			}

			if (currentSearchParams != null) {
				if (typeof fieldName != 'undefined') {
					if (fieldName in currentSearchParams) {
						delete currentSearchParams[fieldName];
					}
				}

				if ('starred' in currentSearchParams) {
					delete currentSearchParams['starred'];
				}
			}

			var searchValue = searchContributorElement.val();

			if (typeof searchValue == "object") {
				if (searchValue == null) {
					searchValue = "";
				} else {
					searchValue = searchValue.join(',');
				}
			}
			searchValue = searchValue.trim();
			if (searchValue.length <= 0) {
				//continue
				return true;
			}
			var searchOperator = 'c';
			if (fieldInfo.type == "date" || fieldInfo.type == "datetime") {
				searchOperator = 'bw';
			} else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
					|| fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
					fieldInfo.type == "picklist") {
				searchOperator = 'e';
			}
			var storedOperator = searchContributorElement.parent().parent().find('.operatorValue').val();
			if (storedOperator) {
				searchOperator = storedOperator;
				storedOperator = false;
			}
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);

			searchParams.push(searchInfo);
		});
		for (var i in currentSearchParams) {
//			Number.isInteger(parseInt(i)) (Previously Used which is not supported by IE.)
//			http://codereview.stackexchange.com/questions/101484/simple-function-to-verify-if-a-number-is-integer
//			http://stackoverflow.com/questions/26482645/number-isintegerx-which-is-created-can-not-work-in-ie
			if (!this.isInteger(parseInt(i))) {
				continue;
			}
			var fieldName = currentSearchParams[i]['fieldName'];
			var searchValue = currentSearchParams[i]['searchValue'];
			var searchOperator = currentSearchParams[i]['comparator'];
			if (fieldName == null || fieldName.length <= 0) {
				continue;
			}
			var searchInfo = new Array();
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			searchParams.push(searchInfo);
		}
		if (searchParams.length > 0) {
			var listSearchParams = new Array(searchParams);
		} else {
			var listSearchParams = new Array();
		}
		if (includeStarFilters) {
			listSearchParams = this.addStarSearchParams(listSearchParams);
		}
		return listSearchParams;
	},
	addStarSearchParams: function (listSearchParams) {
		var listViewContainer = this.getListViewContainer();
		var activeStarFilter = listViewContainer.find('.starFilter li.active').find('a').data('type');
		if (activeStarFilter == "starred") {
			if (typeof listSearchParams[0] == "undefined") {
				listSearchParams[0] = new Array();
			}
			var starSearchParams = new Array();
			starSearchParams.push('starred');
			starSearchParams.push('e');
			starSearchParams.push('1');
			listSearchParams[0].push(starSearchParams);
		}
		else if (activeStarFilter == "unstarred") {
			if (typeof listSearchParams[0] == "undefined") {
				listSearchParams[0] = new Array();
			}
			var unStarSearchParams = new Array();
			unStarSearchParams.push('starred');
			unStarSearchParams.push('e');
			unStarSearchParams.push('0');
			listSearchParams[0].push(unStarSearchParams);
		}
		return listSearchParams;
	},
	pageJump: function () {
		var thisInstance = this;
		var listViewContainer = thisInstance.getListViewContainer();
		var element = listViewContainer.find('#totalPageCount');
		var totalPageNumber = element.text();
		var pageCount;

		if (totalPageNumber === "") {
			var totalCountElem = listViewContainer.find('#totalCount');
			var totalRecordCount = totalCountElem.val();
			if (totalRecordCount !== '') {
				var recordPerPage = listViewContainer.find('#pageLimit').val();
				if (recordPerPage === '0')
					recordPerPage = 1;
				pageCount = Math.ceil(totalRecordCount / recordPerPage);
				if (pageCount === 0) {
					pageCount = 1;
				}
				element.text(pageCount);
				return;
			}

			thisInstance.getPageCount().then(function (data) {
				var pageCount = data.page;
				totalCountElem.val(data.numberOfRecords);
				if (pageCount === 0) {
					pageCount = 1;
				}
				element.text(pageCount);
			});
		}
	},
	pageJumpOnSubmit: function (element) {
		var thisInstance = this;
		var container = this.getListViewContainer();
		var currentPageElement = container.find('#pageNumber');
		var currentPageNumber = parseInt(currentPageElement.val());
		var newPageNumber = parseInt(container.find('#pageToJump').val());
		var totalPages = parseInt(container.find('#totalPageCount').text());
		if (newPageNumber > totalPages) {
			var message = app.vtranslate('JS_PAGE_NOT_EXIST');
			app.helper.showErrorNotification({'message': message})
			return;
		}

		if (newPageNumber === currentPageNumber) {
			var message = app.vtranslate('JS_YOU_ARE_IN_PAGE_NUMBER') + " " + newPageNumber;
			app.helper.showAlertNotification({'message': message});
			return;
		}

		var urlParams = thisInstance.getPagingParams();
		urlParams['page'] = newPageNumber;
		thisInstance.loadListViewRecords(urlParams).then(function (data) {
			element.closest('.btn-group ').removeClass('open');
		});
	},
	/**
	 * Function to register Inline Edit
	 * @param {type} currentTrElement
	 * @returns {undefined}
	 */
	registerInlineEdit: function (currentTrElement) {
		var self = this;
		currentTrElement.addClass('edited');
		var tdElements = jQuery('.listViewEntryValue', currentTrElement);
		currentTrElement.addClass('edited');
		jQuery('.action', currentTrElement).addClass('hide');
		jQuery('.inline-save', currentTrElement).removeClass('hide');

		for (var i = 0; i < tdElements.length; i++) {
			var fieldData = [];
			var tdElement = jQuery(tdElements[i]);
			var editElement = jQuery('.edit', tdElement);
			var valueElement = jQuery('.fieldValue', tdElement);
			var moduleName = this.getModuleName();
			var fieldName = tdElement.data("name");

			//ignore other related modules fields
			if (fieldName.match(/\((\w+)\s\;\s\((\w+)\)\s(\w+)\)/g) && fieldName.match(/\((\w+)\s\;\s\((\w+)\)\s(\w+)\)/g).length) {
				continue;
			}
			var fieldType = tdElement.data("field-type");
			var fieldBasicInfo = new Array();
			if (typeof uimeta !== "undefined") {
				fieldBasicInfo = uimeta.field.get(fieldName);
			}

			//In advance search uimeta will not have selected module's fields info. So checking in adv_search_uimeta
			if ((typeof fieldBasicInfo == "undefined" || fieldBasicInfo.length <= 0) && (typeof adv_search_uimeta !== "undefined")) {
				fieldBasicInfo = adv_search_uimeta.field.get(fieldName);
			}

			var value = jQuery.trim(valueElement.text());
			//adding string,text,url,currency in customhandling list as string will be textlengthchecked
			var customHandlingFields = ['owner', 'ownergroup', 'picklist', 'multipicklist', 'reference', 'string', 'url', 'currency', 'text', 'email'];
			if (jQuery.inArray(fieldType, customHandlingFields) !== -1) {
				value = tdElement.data('rawvalue');
			}

			fieldData["value"] = value;
			jQuery.extend(fieldData, fieldBasicInfo);

			// For non editable fields
			if (editElement.length <= 0) {
				continue;
			}

			// if element is already edited
			if (editElement.is(':visible')) {
				continue;
			}

			var fieldObject = new Vtiger_Field_Js.getInstance(fieldData, moduleName);
			var fieldModel = fieldObject.getUiTypeModel();

			if (jQuery('input', editElement).length === 0) {
				editElement.append(fieldModel.getUi());
			}

			// for reference fields, actual value will be ID but we need to show related name of that ID
			if (fieldType === 'reference') {
				if (value !== 0) {
					jQuery('input[name="' + fieldName + '"]', editElement).prop('value', jQuery.trim(valueElement.text()));
				}
			}

			valueElement.addClass('hide');
			editElement.removeClass('hide');
			if (fieldType === 'picklist') {
				self.registerEventForPicklistDependencySetup(currentTrElement);
			}
		}
		app.event.trigger('post.listViewInlineEdit.click', currentTrElement);
	},
	registerEventForPicklistDependencySetup: function (container) {
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]');
		if (picklistDependcyElemnt.length <= 0) {
			return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if (sourcePicklists.length <= 0) {
			return;
		}

		var sourcePickListNames = "";
		for (var i = 0; i < sourcePicklists.length; i++) {
			if (i != sourcePicklists.length - 1)
				sourcePickListNames += '[name="' + sourcePicklists[i] + '"],';
			else
				sourcePickListNames += '[name="' + sourcePicklists[i] + '"]';
		}
		var sourcePickListElements = container.find(sourcePickListNames);

		sourcePickListElements.on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];

			if (typeof targetObjectForSelectedSourceValue == 'undefined') {
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap, function (targetPickListName, targetPickListValues) {
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if (typeof targetPickListMap == "undefined") {
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[name="' + targetPickListName + '"]', container);
				if (targetPickList.length <= 0) {
					return;
				}

				var listOfAvailableOptions = targetPickList.data('availableOptions');
				if (typeof listOfAvailableOptions == "undefined") {
					listOfAvailableOptions = jQuery('option', targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				var targetOptions = new jQuery();
				var optionSelector = [];
				optionSelector.push('');
				for (var i = 0; i < targetPickListMap.length; i++) {
					optionSelector.push(targetPickListMap[i]);
				}

				jQuery.each(listOfAvailableOptions, function (i, e) {
					var picklistValue = jQuery(e).val();
					if (jQuery.inArray(picklistValue, optionSelector) != -1) {
						targetOptions = targetOptions.add(jQuery(e));
					}
				})
				var targetPickListSelectedValue = '';
				var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
				if (targetPickListMap.length == 1) {
					var targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present.
				}
				if ((targetPickListName == 'group_id' || targetPickListName == 'assigned_user_id') && currentElement.val() == '') {
					return false;
				}
				targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");
			})
		});

		//To Trigger the change on load
		sourcePickListElements.trigger('change');
	},
	validateAndSaveInlineEdit: function (currentTrElement) {
		var listViewContainer = this.getListViewContainer();
		var thisInstance = this;
		var tdElements = jQuery('.listViewEntryValue', currentTrElement);
		var record = currentTrElement.data('id');
		var values = {};
		for (var i = 0; i < tdElements.length; i++) {
			var tdElement = jQuery(tdElements[i]);
			var newValueElement = jQuery('.inputElement', tdElement);
			var fieldName = tdElement.data("name");
			values[fieldName] = thisInstance.getInlineEditedFieldValue(tdElement, newValueElement);
		}

		var params = {
			'ignore': ".listSearchContributor,input[type='hidden']",
			submitHandler: function (form) {
				// NOTE : hack added, submit was getting triggered for 2nd and 3rd click on save, need to debug this.
				if (this.numberOfInvalids() > 0) {
					this.form();
					return false;
				}
				var params = {
					'module': thisInstance.getModuleName(),
					'action': 'SaveAjax',
					'record': record
				};
				var params = jQuery.extend(values, params);
				app.helper.showProgress();
				jQuery('.inline-save', currentTrElement).find('button').attr('disabled', 'disabled');
				app.request.post({data: params}).then(function (err, result) {
					if (result) {
						jQuery('.vt-notification').remove();
						jQuery('.inline-save', currentTrElement).find('button').removeAttr('disabled');
						var params = {};
						thisInstance.loadListViewRecords(params).then(function (data) {
							thisInstance.toggleInlineEdit(currentTrElement);
							app.helper.hideProgress();
							app.helper.showSuccessNotification({"message": ''});
							//Register Event to show quick preview for reference field.
							app.event.trigger('onclick.referenceField.quickPreview', currentTrElement);
						});
					} else {
						app.helper.hideProgress();
						app.event.trigger('post.save.failed', err);
						jQuery('.inline-save', currentTrElement).find('button').removeAttr('disabled');
						return false;
					}
				});
				return false;  // blocks regular submit since you have ajax
			}
		};
		validateAndSubmitForm(listViewContainer.find('#listedit'), params);
	},
	/**
	 * Function to register Save Event on Inline Edit
	 * @returns {undefined}
	 */
	registerInlineEditSaveEvent: function () {
		var thisInstance = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.on('click', '.save', function (e) {
			e.preventDefault();
			var currentTarget = jQuery(e.currentTarget);
			var currentTrElement = currentTarget.closest('tr');
			thisInstance.validateAndSaveInlineEdit(currentTrElement);
		});
	},
	/**
	 * Function  to register Cancel event on Inline Edit
	 * @returns {undefined}
	 */
	registerInlineEditCancelEvent: function () {
		var thisInstance = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.on('click', '.cancel', function (e) {
			e.preventDefault();
			var currentTarget = jQuery(e.currentTarget);
			var currentTrElement = currentTarget.closest('tr');
			thisInstance.toggleInlineEdit(currentTrElement);
			currentTrElement.removeClass('edited');

			//negating adjusted height
			var $table = jQuery('#listview-table');
			var tableContainer = $table.closest('.table-container');
			tableContainer.height(tableContainer.height() - 28);
			tableContainer.perfectScrollbar('update');
		});
	},
	/**
	 * Function to get value from Edited field 
	 * @param {type} fieldElement
	 * @param {type} inputElement
	 * @return value
	 */
	getInlineEditedFieldValue: function (fieldElement, inputElement) {
		var value = null;
		var fieldType = fieldElement.data('field-type');
		var picklistTypes = ['owner', 'picklist', 'ownergroup', 'currencyList'];
		if (jQuery.inArray(fieldType, picklistTypes) !== -1) {
			value = jQuery(".inputElement.select2", fieldElement).find(":selected").val();
		} else if (fieldType === "reference") {
			value = inputElement.data('value');
		} else if (fieldType === "multipicklist") {
			var selectedOptions = jQuery(".inputElement.select2", fieldElement).find(":selected");
			value = [];
			for (var i = 0; i < selectedOptions.length; i++) {
				var option = jQuery(selectedOptions.get(i));
				value.push(option.val());
			}
		} else if (fieldType == 'boolean') {
			if (fieldElement.find('input:checkbox').is(':checked')) {
				value = 1;
			} else {
				value = 0;
			}
		} else {
			value = inputElement.val();
		}
		return value;
	},
	/**
	 * Function to Change List record from Inline Edit View to Normal View
	 * @param {type} record
	 * @returns {undefined}
	 */
	toggleInlineEdit: function (record) {
		jQuery('.inline-save', record).addClass('hide');
		jQuery('.action', record).removeClass('hide');
		jQuery('.edit', record).empty();
		jQuery('.edit', record).addClass('hide');
		jQuery('.fieldValue', record).removeClass('hide');
		record.removeClass('edited');
	},
	searchModuleNames: function (params) {
		var aDeferred = jQuery.Deferred();

		if (typeof params.module == 'undefined') {
			params.module = this.getModuleName();
		}

		if (typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if (typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if (record.length) {
				params.base_record = record.val();
			} else if (recordId) {
				params.base_record = recordId;
			} else if (app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if (editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}

		app.request.get({data: params}).then(
				function (err, res) {
					aDeferred.resolve(res);
				},
				function (error) {
					//TODO : Handle error
					aDeferred.reject();
				}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams: function (element) {
		var tdElement = jQuery(element).closest('td');
		var params = {};
		var referenceModuleElement = jQuery('input[name="referenceModule"]', tdElement).length ?
				jQuery('input[name="referenceModule"]', tdElement) : jQuery('input.referenceModule', tdElement);
		var searchModule = referenceModuleElement.val();
		params.search_module = searchModule;
		return params;
	},
	/*
	 * Function to register the list view row click event
	 */
	registerRowClickEvent: function () {
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContainer();

		// added to stop the link functunality for few milli seconds
		listViewContentDiv.on('click', '.listViewEntries a', function (e) {
			var currentAElement = jQuery(e.currentTarget);
			var href = currentAElement.attr('href');
			var target = jQuery(e.target);
			if (!target.hasClass('js-reference-display-value')) {
				// Redirect only after 500 milliseconds
				if (!currentAElement.data('timer') && typeof href != 'undefined') {
					currentAElement.data('timer', setTimeout(function () {
						window.location = href;
					}, 500));
				}
				e.preventDefault();
			}
			e.stopPropagation();
		});

		// Single click event - detail view
		listViewContentDiv.on('click', '.listViewEntries', function (e) {
			var target = jQuery(e.target);
			if (!target.hasClass('js-reference-display-value')) {
				setTimeout(function () {
					var editedLength = jQuery('.listViewEntries.edited').length;
					if (editedLength === 0) {
						var selection = window.getSelection().toString();
						if (selection.length == 0) {
							var target = jQuery(e.target, jQuery(e.currentTarget));
							if (target.closest('td').is('td:first-child'))
								return;
							if (target.closest('tr').hasClass('edited'))
								return;
							if (jQuery(e.target).is('input[type="checkbox"]'))
								return;
							var elem = jQuery(e.currentTarget);
							var recordUrl = elem.data('recordurl');
							if (typeof recordUrl == 'undefined') {
								return;
							}
							window.location.href = recordUrl;
						}
					}
				}, 300);
			}
		});
	},
	/*
	 * Function to register the list view row double click event
	 */
	registerRowDoubleClickEvent: function () {
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContainer();

		// Double click event - ajax edit
		listViewContentDiv.on('dblclick', '.listViewEntries', function (e) {
			if (listViewContentDiv.find('#isExcelEditSupported').val() == 'no') {
				return;
			}

			var currentTrElement = jQuery(e.currentTarget);
			// added to unset the time out set for <a> tags 
			var rows = currentTrElement.find('a');
			rows.each(function (i, elem) {
				if (jQuery(elem).data('timer')) {
					clearTimeout(jQuery(elem).data('timer'));
					jQuery(elem).data('timer', null);
				}
				;
			});
			var editedLength = jQuery('.listViewEntries.edited').length;
			if (editedLength === 0) {
				var currentTrElement = jQuery(e.currentTarget);
				var target = jQuery(e.target, jQuery(e.currentTarget));
				if (target.closest('td').is('td:first-child'))
					return;
				if (target.closest('tr').hasClass('edited'))
					return;
				thisInstance.registerInlineEdit(currentTrElement);
			}
		});
	},
	/*
	 * Funtion to register events for reference field actions
	 */
	registerFieldActionEvents: function (editedRow) {
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		vtigerInstance.registerAutoCompleteFields(editedRow);
		vtigerInstance.registerClearReferenceSelectionEvent(editedRow);
		vtigerInstance.referenceModulePopupRegisterEvent(editedRow);
	},
	registerInlineSaveOnEnterEvent: function (editedRow) {
		editedRow.find('.inputElement:not(textarea)').on('keyup', function (e) {
			var ignoreList = ['reference', 'picklist', 'multipicklist', 'owner'];
			var fieldType = jQuery(e.target).closest('.listViewEntryValue').data('fieldType');
			if (ignoreList.indexOf(fieldType) !== -1)
				return;
			(e.keyCode || e.which) === 13 && editedRow.find('button.save').trigger('click');
		});
	},
	/**
	 * Function to register all list view record actions
	 * @returns {undefined}
	 */
	registerListViewBasicActions: function () {
		var thisInstance = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.on('click', '.table-actions .inlineEdit', function (e) {
			var editedLength = jQuery('.listViewEntries.edited').length;
			if (editedLength === 0) {
				var currentTrElement = jQuery(e.currentTarget).parents('tr');
				thisInstance.registerInlineEdit(currentTrElement);
			}
		});
		this.registerInlineEditSaveEvent();
		this.registerInlineEditCancelEvent();

		app.event.on('post.listViewInlineEdit.click', function (event, editedRow) {
			vtUtils.applyFieldElementsView(editedRow);
			thisInstance.registerFieldActionEvents(editedRow);

			//height adjustment to accomodate inline edit container
			var $table = jQuery('#listview-table');
			var tableContainer = $table.closest('.table-container');
			tableContainer.height(tableContainer.height() + 28);
			tableContainer.perfectScrollbar('update');
			thisInstance.registerInlineSaveOnEnterEvent(editedRow);
		});

		var isOwnerChanged = false;
		app.event.on('post.listViewMassEdit.loaded', function (e, container) {
			app.event.trigger('post.listViewInlineEdit.click', container);
			var offset = container.find('.modal-body .datacontent').offset();
			var viewPortHeight = $(window).height() - 60;

			var params = {
				setHeight: (viewPortHeight - offset['top']) + 'px'
			};

			container.find('[name="assigned_user_id"]').on('click', function() {
				isOwnerChanged = true;
			});
			app.helper.showVerticalScroll(container.find('.modal-body .datacontent'), params);
			var editInstance = Vtiger_Edit_Js.getInstance();
			editInstance.registerBasicEvents(container);
			var form_original_data = $("#massEdit").serialize();
			$('#massEdit').on('submit', function (event) {
				thisInstance.saveMassedit(event, form_original_data, isOwnerChanged);
				isOwnerChanged = false;
			});
			app.helper.registerLeavePageWithoutSubmit($("#massEdit"));
			app.helper.registerModalDismissWithoutSubmit($("#massEdit"));
		});

		app.event.on('post.listViewMassAction.loaded', function (e, container) {
			jQuery('#phoneFormatWarningPop').popover();
			jQuery('#massSave').vtValidate({
				// Note : JQuery Validator is not working with multi file upload fields
				ignore: "input[type='file'].multi",
				submitHandler: function (form) {
					var domForm = jQuery(form);
					var formData = jQuery(form).serializeFormData();

					var formData = new FormData(domForm[0]);
					if (Vtiger_Index_Js.files) {
						formData.append("filename", Vtiger_Index_Js.files);
						delete Vtiger_Index_Js.files;
					}
					var params = {
						url: "index.php",
						type: "POST",
						data: formData,
						processData: false,
						contentType: false
					};

					app.helper.showProgress();
					app.request.post(params).then(function (err, data) {
						app.helper.hideProgress();
						app.helper.hideModal();
						if (jQuery(form).find('[name="module"]').val() == 'SMSNotifier') {
							var statusDetails = data.statusdetails;
							var status = statusDetails.status;
							if (status == 'Failed') {
								var errorMsg = statusDetails.statusmessage + '<br>' + app.vtranslate('JS_PHONEFORMAT_ERROR');
								app.helper.showErrorNotification({'title': status, 'message': errorMsg});
							} else {
								var msg = statusDetails.statusmessage;
								app.helper.showSuccessNotification({'title': status, 'message': msg});
							}
						}
						app.event.trigger('post.listViewMassEditSave');
						if (err) {
							return;
						}
					});
					return false;
				}
			});
		});
	},
	/**
	 * Function to register the list view row search event
	 */
	registerListViewSearch: function () {
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click', '[data-trigger="listSearch"]', function (e) {
			e.preventDefault();
			var params = {
				'page': '1'
			}
			thisInstance.loadListViewRecords(params).then(
				function (data) {
					//To unmark the all the selected ids
					jQuery('#deSelectAllMsgDiv').trigger('click');
				},
				function (textStatus, errorThrown) {
				}
			);
		});

		//floatThead change event object has undefined keyCode, using keyup instead
		var prevSearchValues = [];
		listViewPageDiv.on('keyup', '.listSearchContributor', function (e) {
			var element = jQuery(e.currentTarget);
			var fieldName = element.attr('name');
			var searchValue = element.val();
			if (e.keyCode == 13 && prevSearchValues[fieldName] !== searchValue) {
				e.preventDefault();
				var element = jQuery(e.currentTarget);
				var parentElement = element.closest('tr');
				var searchTriggerElement = parentElement.find('[data-trigger="listSearch"]');
				searchTriggerElement.trigger('click');
				prevSearchValues[fieldName] = searchValue;
			}
		});

		listViewPageDiv.on('datepicker-change', '.dateField', function (e) {
			var element = jQuery(e.currentTarget);
			element.trigger('change');
		});
	},
	saveMassedit: function (event, form_original_data, isOwnerChanged) {
		event.preventDefault();
		var form = $('#massEdit');
		var form_new_data = form.serialize();
		app.helper.showProgress();
		if (form_new_data !== form_original_data || isOwnerChanged) {
			var originalData = app.convertUrlToDataParams(form_original_data);
			var newData = app.convertUrlToDataParams(form_new_data);

			for (var key in originalData) {
				if ((form.find('[name="' + key + '"]').is("select")
						|| form.find('[name="' + key + '"]').is("input[type='checkbox']"))
						&& (originalData[key] == newData[key])) {
					delete newData[key];
				}
			}

			if (!newData['assigned_user_id'] && isOwnerChanged) {
				newData['assigned_user_id'] = originalData['assigned_user_id'];
			}

			var form_update_data = '';
			for (var key in newData) {
				form_update_data += key + '=' + newData[key] + '&';
			}
			form_update_data = form_update_data.slice(0, -1);
			app.request.post({data: form_update_data}).then(function (err, data) {
				app.helper.hideProgress();
				if (data) {
					jQuery('.vt-notification').remove();
					app.helper.hidePageContentOverlay();
					window.onbeforeunload = null;
					app.event.trigger('post.listViewMassEditSave');
				} else {
					app.event.trigger('post.save.failed', err);
				}
			});
		} else {
			app.helper.hideProgress();
			app.helper.showAlertBox({'message': app.vtranslate('NONE_OF_THE_FIELD_VALUES_ARE_CHANGED_IN_MASS_EDIT')});
		}
	},
	markSelectedIdsCheckboxes: function () {
		var self = this;

		var recordSelectTrackerObj = self.getRecordSelectTrackerInstance();
		var selectAllMode = recordSelectTrackerObj.getSelectAllMode();

		var excludedIds = recordSelectTrackerObj.getExcludedIds();
		var excludedIdsAreEmpty = self.checkIdsAreEmpty(excludedIds);
		var selectedIds = recordSelectTrackerObj.getSelectedIds();

		var currentViewId = self.getCurrentCvId();
		var recordTackerCvId = recordSelectTrackerObj.getCvid();
		var rows = jQuery('tr.listViewEntries');

		if (selectAllMode == true) {
			jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').addClass('show');
			//jQuery(".listViewEntriesMainCheckBox").prop('checked', true);
			if (this.isStarFilterMode()) {
				rows = this.getStarRecordRows();
			} else if (this.isUnStarFilterMode()) {
				rows = this.getUnStarRecordRows();
			}
			if (excludedIdsAreEmpty) {
				rows.each(function (i, elem) {
					jQuery(elem).find(".listViewEntriesCheckBox").prop('checked', true);
				});
			}
			else {
				rows.each(function (i, elem) {
					var rowId = $(elem).data('id');
					jQuery(elem).find('.listViewEntriesCheckBox').prop('checked', true);

					for (var j = 0; j < excludedIds.length; j++) {
						var excludedRecordIdValue = excludedIds[j];
						if (excludedRecordIdValue == rowId) {
							jQuery('.listViewEntriesCheckBox[value="' + excludedRecordIdValue + '"]').prop('checked', false);
							jQuery(".listViewEntriesMainCheckBox").prop('checked', false);
						}
					}
				});
			}
		} else {
			var isEmpty = self.checkIdsAreEmpty(selectedIds);
			if (!isEmpty) {
				rows.each(function (i, elem) {
					var rowId = $(elem).data('id');
					for (var j = 0; j < selectedIds.length; j++) {
						var selectedRecordIdValue = selectedIds[j];
						if (selectedRecordIdValue == rowId) {
							jQuery('.listViewEntriesCheckBox[value="' + selectedRecordIdValue + '"]').prop('checked', true);
						}
					}
				});
				var listViewPageDiv = self.getListViewContainer();
				if (listViewPageDiv.find('.listViewEntriesCheckBox').not(":checked").length == 0) {
					listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", true)
				} else {
					listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", false)
				}
			}
		}
	},
	checkIdsAreEmpty: function (val) {
		return (val == undefined || val == null || val.length <= 0) ? true : false;
	},
	performMergeRecords: function () {
		var self = this;
		var selectedIds = self.readSelectedIds();
		if (selectedIds.length > 3) {
			app.helper.showErrorNotification({message: app.vtranslate('JS_ALLOWED_TO_SELECT_MAX_OF_THREE_RECORDS')});
			return;
		}
		if (selectedIds.length < 2) {
			app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING')});
			return;
		}

		app.event.trigger('Request.MergeRecords.show', {records: selectedIds});
		app.event.one('post.MergeRecords', function (e) {
			self.clearList();
			self.loadListViewRecords();
		})
	},
	clearList: function () {
		var recordSelectTracker = this.getRecordSelectTrackerInstance();
		recordSelectTracker.clearList();
	},
	getListSelectAllParams: function (jsonDecode) {
		var self = this;
		var recordSelectTrackerInstance = self.getRecordSelectTrackerInstance();
		var params = recordSelectTrackerInstance.getSelectedAndExcludedIds(jsonDecode);
		params.search_params = JSON.stringify(self.getListSearchParams());
		return params;
	},
	registerCheckBoxClickEvent: function () {
		var self = this;
		var listViewPageDiv = self.getListViewContainer();
		var recordSelectTrackerInstance = self.getRecordSelectTrackerInstance();

		listViewPageDiv.on('click', '.listViewEntriesCheckBox', function (e) {
			var element = listViewPageDiv.find(e.currentTarget);
			var row = element.closest('.listViewEntries');

			if (element.is(':checked')) {
				row.trigger('Post.ListRow.Checked', {"id": row.data('id')});
				self.registerPostLoadListViewActions();
				if (recordSelectTrackerInstance.selectAllMode) {
					var excludedIds = recordSelectTrackerInstance.getExcludedIds();
					if (self.checkIdsAreEmpty(excludedIds)) {
						listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", true);
					}
				}
			} else {
				row.trigger('Post.ListRow.UnChecked', {"id": row.data('id')});
				self.registerPostLoadListViewActions();
				if (recordSelectTrackerInstance.selectAllMode) {
					listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", false)
				}
			}
			if (listViewPageDiv.find('.listViewEntriesCheckBox').not(":checked").length == 0) {
				listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", true)
			} else {
				listViewPageDiv.find('.listViewEntriesMainCheckBox').prop("checked", false)
			}
		});

	},
	readSelectedIds: function (jsonDecode) {
		var recordTracker = this.getRecordSelectTrackerInstance();
		var selectedIds = recordTracker.getSelectedIds();
		if (jsonDecode) {
			if (typeof selectedIds == 'object') {
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
	readExcludedIds: function (jsonDecode) {
		var recordTracker = this.getRecordSelectTrackerInstance();
		var excludedIds = recordTracker.getExcludedIds();
		if (jsonDecode) {
			return JSON.stringify(excludedIds);
		}
		return excludedIds;
	},
	getSelectAllMode: function () {
		var recordTracker = this.getRecordSelectTrackerInstance();
		return recordTracker.getSelectAllMode();
	},
	showSelectAllMsgDiv: function () {
		jQuery("#deSelectAllMsgDiv").closest('div.messageContainer').removeClass('show');
		jQuery("#deSelectAllMsgDiv").closest('div.messageContainer').addClass('hide');
		jQuery("#selectAllMsgDiv").closest('div.messageContainer').addClass("show");
	},
	showDeSelectAllMsgDiv: function () {
		jQuery('#selectAllMsgDiv').closest('div.messageContainer').removeClass("show");
		jQuery('#selectAllMsgDiv').closest('div.messageContainer').addClass("hide");
		jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').addClass('show');
	},
	deSelectAllWithNoMessage: function () {
		jQuery('#selectAllMsgDiv').closest('div.messageContainer').removeClass("show");
		jQuery('#selectAllMsgDiv').closest('div.messageContainer').addClass("hide");
		jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').removeClass("show");
		jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').addClass("hide");
	},
	showSelectAll: function () {
		var self = this;
		app.helper.showProgress();
		self.getRecordsCount().then(function (res) {
			self.showSelectAllMsgDiv();
			jQuery('#totalRecordsCount').text(res['count']);
			app.helper.hideProgress();
		})
	},
	registerListViewMainCheckBoxClickEvent: function () {
		var self = this;
		var listViewPageDiv = this.getListViewContainer();
		listViewPageDiv.on('click', '.listViewEntriesMainCheckBox', function (e) {
			e.stopPropagation();
			var element = jQuery(e.currentTarget);
			if (element.is(':checked')) {
				var rows = listViewPageDiv.find('tr.listViewEntries');
				if (self.isStarFilterMode()) {
					rows = self.getStarRecordRows();
				} else if (self.isUnStarFilterMode()) {
					rows = self.getUnStarRecordRows();
				}
				rows.find('.listViewEntriesCheckBox').each(function (e) {
					jQuery(this).prop('checked', true);
					var row = jQuery(this).closest('.listViewEntries');
					row.trigger('Post.ListRow.Checked', {"id": row.data('id')});
					self.registerPostLoadListViewActions();
				});
				if (self.getSelectAllMode() == true) {
					self.markSelectedIdsCheckboxes();
				} else {
					// If it is not select all mode then only do this
					self.showSelectAll();
				}
			}
			else {
				if (self.getSelectAllMode() == true) {
					self.showDeSelectAllMsgDiv();
				}
				else {
					self.deSelectAllWithNoMessage();
				}
				jQuery('.listViewEntriesCheckBox').each(function (e) {
					jQuery(this).prop('checked', false);
					var row = jQuery(this).closest('.listViewEntries');
					row.trigger('Post.ListRow.UnChecked', {"id": row.data('id')});
					self.registerPostLoadListViewActions();
				});
			}
		});
	},
	registerSelectAllClickEvent: function () {
		var self = this;
		var listViewPageDiv = this.getListViewContainer();
		listViewPageDiv.on('click', '#selectAllMsgDiv', function (e) {
			self.showDeSelectAllMsgDiv();
			var cvId = self.getCurrentCvId();
			listViewPageDiv.trigger('Post.ListSelectAll', {"mode": true, "cvId": cvId});
		});
		self.markSelectedIdsCheckboxes();
	},
	registerDeSelectAllClickEvent: function () {
		var self = this;
		var listViewPageDiv = this.getListViewContainer();
		listViewPageDiv.on('click', '#deSelectAllMsgDiv', function (e) {
			jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').removeClass('show');
			jQuery('#deSelectAllMsgDiv').closest('div.messageContainer').addClass("hide");
			listViewPageDiv.trigger('Post.ListDeSelectAll', {"mode": false});
			self.registerPostLoadListViewActions();
			jQuery('.listViewEntriesMainCheckBox').prop('checked', false);
			jQuery('.listViewEntriesCheckBox').each(function (e) {
				jQuery(this).prop('checked', false);
			});
		});
	},
	getRecordsCount: function () {
		var aDeferred = jQuery.Deferred();
		var self = this;
		var cvId = self.getCurrentCvId();
		var module = this.getModuleName();
		var parent = app.getParentModuleName();
		var defaultParams = this.getDefaultParams();

		var postData = {
			"module": module,
			"parent": parent,
			"view": "ListAjax",
			"viewname": cvId,
			"mode": "getRecordsCount"
		};
		postData = jQuery.extend(defaultParams, postData);
		var params = {};
		params.data = postData;
		app.request.get(params).then(
				function (err, response) {
					aDeferred.resolve(response);
				}
		);
		return aDeferred.promise();
	},
	transferOwnershipSave: function (form) {
		var listInstance = window.app.controller();
		var listSelectParams = listInstance.getListSelectAllParams(false);
		if (listSelectParams) {
			var formData = form.serializeFormData();
			var data = jQuery.extend(formData, listSelectParams);
			app.helper.showProgress();
			app.request.post({'data': data}).then(function (err, data) {
				app.helper.hideProgress();
				if (err == null) {
					jQuery('.vt-notification').remove();
					app.helper.hideModal();
					listInstance.loadListViewRecords().then(function (e) {
						listInstance.clearList();
						app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY')});
					});
				} else {
					app.event.trigger('post.save.failed', err);
					jQuery(form).find("button[name='saveButton']").removeAttr('disabled');
				}
			});
		}
	},
	registerStarToggle: function () {
		var self = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.on('click', '.markStar', function (e) {
			var element = jQuery(e.currentTarget);
			if (element.hasClass('processing'))
				return;
			element.addClass('processing');

			var record = element.closest('tr.listViewEntries').data('id');
			var params = {};
			params.module = self.getModuleName();
			params.action = 'SaveStar';
			params.record = record;
			if (element.hasClass('active')) {
				params.value = 0;
				element.removeClass('fa-star').addClass('fa-star-o');
			} else {
				params.value = 1;
				element.removeClass('fa-star-o').addClass('fa-star');
			}
			element.toggleClass('active');
			params._timeStampNoChangeMode = true;
			app.request.post({data: params}).then(function (err, data) {
				if (data) {
					if (params.value == 0) {
						element.attr("title", app.vtranslate('JS_NOT_STARRED'));
					} else {
						element.attr("title", app.vtranslate('JS_STARRED'));
					}
				}
				element.removeClass('processing');
			})
			if(element.hasClass('active')){
				app.helper.showSuccessNotification({'message':app.vtranslate('JS_FOLLOW_RECORD')});
			} else {
				app.helper.showSuccessNotification({'message':app.vtranslate('JS_UNFOLLOW_RECORD')});
			}
		});
	},
	massStarSave: function (params) {
		var self = this;
		var listInstance = window.app.controller();
		var selectedRecordCount = listInstance.getSelectedRecordCount();
		params.module = this.getModuleName();
		params.action = 'SaveStar';
		var cvId = this.getCurrentCvId();
		params.viewname = cvId;
		params.search_params = JSON.stringify(self.getListSearchParams());
		params = jQuery.extend(params, self.getListSelectAllParams(false));
		params._timeStampNoChangeMode = true;
		app.helper.showProgress();
		app.request.post({data: params}).then(function (err, data) {
			app.helper.hideProgress();
			self.loadListViewRecords().then(function (e) {
				self.clearList();
				 if(params.value == 1){
                    app.helper.showSuccessNotification({'message':(app.vtranslate('JS_FOLLOWING')) +' '+ selectedRecordCount +' '+ app.vtranslate(params.module)});
                } else {
					app.helper.showSuccessNotification({'message':app.vtranslate('JS_UNFOLLOWING') +' '+ selectedRecordCount +' '+ app.vtranslate(params.module)}); 
                }
			});
		})
	},
	getStarRecordRows: function () {
		var listViewContainer = this.getListViewContainer();
		return listViewContainer.find('tr.listViewEntries .markStar.fa-star .active').closest('tr');
	},
	getUnStarRecordRows: function () {
		var listViewContainer = this.getListViewContainer();
		return listViewContainer.find('tr.listViewEntries .markStar .fa-star-o').closest('tr');
	},
	isStarFilterMode: function () {
		var filterType = jQuery('.starFilter li.active').find('a').data('type');
		if (filterType == "starred") {
			return true;
		}
		return false;
	},
	isUnStarFilterMode: function () {
		var filterType = jQuery('.starFilter li.active').find('a').data('type');
		if (filterType == "unstarred") {
			return true;
		}
		return false;
	},
	selectRecordsDependingOnStarFilter: function () {
		if (this.isStarFilterMode()) {
			this.getStarRecordRows().find('.listViewEntriesCheckBox').trigger('click');
		} else if (this.isUnStarFilterMode()) {
			this.getUnStarRecordRows().find('.listViewEntriesCheckBox').trigger('click');
		} else {
			jQuery('tr.listViewEntries').find('.listViewEntriesCheckBox').trigger('click');
		}
	},
	registerStarUnstarFilter: function () {
		var self = this;
		var listViewContainer = this.getListViewContainer();
		listViewContainer.on('click', '.starFilter li', function (e) {
			var element = jQuery(e.currentTarget);
			jQuery('.starFilter li').removeClass('active');
			element.addClass('active');

			// We need to clear all the check boxes before changing the mode
			jQuery('tr.listViewEntries').find('.listViewEntriesCheckBox').prop('checked', false);
			self.clearList();

			self.selectRecordsDependingOnStarFilter();
			self.showSelectAll();
		});
	},
	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams: function () {
		var params = this.getDefaultParams();
		params['view'] = "ListAjax";
		params['mode'] = "getPageCount";

		return params;
	},
	getPagingParams: function () {
		var thisInstance = this;
		var params = {
			"orderby": jQuery('#orderBy').val(),
			"sortorder": jQuery("#sortOrder").val(),
			"viewname": thisInstance.getCurrentCvId()
		};
		return params;
	},
	/**
	 * Function to get page count and total number of records in list
	 */
	getPageCount: function () {
		var aDeferred = jQuery.Deferred();
		var pageCountParams = this.getPageJumpParams();
		var params = {
			"type": "GET",
			"data": pageCountParams
		}

		app.request.get(params).then(
				function (err, data) {
					var response;
					if (typeof data !== "object") {
						response = JSON.parse(data);
					} else {
						response = data;
					}
					aDeferred.resolve(response);
				}
		);
		return aDeferred.promise();
	},
	registerDeleteRecordClickEvent: function () {
		var thisInstance = this;
		jQuery('#page').on('click', '.deleteRecordButton', function (e) {
			var elem = jQuery(e.currentTarget);
			var parent = elem;
			var params = {};

			var originalDropDownMenu = elem.closest('.dropdown-menu').data('original-menu');
			if (originalDropDownMenu && typeof originalDropDownMenu != 'undefined') {
				parent = app.helper.getDropDownmenuParent(originalDropDownMenu);

				var moduleName = jQuery('#searchModuleList').val();
				if (moduleName && typeof moduleName != 'undefined') {
					params['module'] = moduleName;
				}
			}

			var recordId = parent.closest('tr').data('id');
			thisInstance.deleteRecord(recordId, params);
//			e.stopPropagation();
		});
	},
	_deleteRecord: function (recordId, extraParams) {
		var thisInstance = this;
		var module = app.getModuleName();
		var postData = {
			"data": {
				"module": module,
				"action": "DeleteAjax",
				"record": recordId,
				"parent": app.getParentModuleName(),
				"viewname": this.getCurrentCvId()

			}
		};

		if (typeof extraParams === 'undefined') {
			extraParams = {};
		}
		jQuery.extend(postData.data, extraParams);

		app.helper.showProgress();
		app.request.post(postData).then(
			function (err, data) {
				if (err == null) {
					app.helper.hideProgress();
					thisInstance.loadListViewRecords();
				} else {
					app.helper.hideProgress();
					app.helper.showErrorNotification({message: app.vtranslate(err.message)})
				}
			});
	},
	deleteRecord: function (recordId, extraParams) {
		var thisInstance = this;
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		app.helper.showConfirmationBox({'message': message}).then(function () {
			thisInstance._deleteRecord(recordId, extraParams);
		});
	},
	totalNumOfRecords_performingAsyncAction: false,
	totalNumOfRecords: function (currentEle) {
		var thisInstance = this;
		var listViewContainer = thisInstance.getListViewContainer();
		var totalRecordsElement = listViewContainer.find('#totalCount');
		var totalNumberOfRecords = totalRecordsElement.val();
		if (thisInstance.totalNumOfRecords_performingAsyncAction) {
			return;
		}
		currentEle.find('.showTotalCountIcon').addClass('hide');
		if (totalNumberOfRecords === '') {
			thisInstance.totalNumOfRecords_performingAsyncAction = true;
			thisInstance.getPageCount().then(function (data) {
				currentEle.addClass('hide');
				totalNumberOfRecords = data.numberOfRecords;
				totalRecordsElement.val(totalNumberOfRecords);
				listViewContainer.find('ul#listViewPageJumpDropDown #totalPageCount').text(data.page);
				thisInstance.showPagingInfo();
				thisInstance.totalNumOfRecords_performingAsyncAction = false;
			});
		} else {
			currentEle.addClass('hide');
			thisInstance.showPagingInfo();
		}
	},
	showPagingInfo: function () {
		var thisInstance = this;
		var listViewContainer = thisInstance.getListViewContainer();
		var totalNumberOfRecords = jQuery('#totalCount', listViewContainer).val();
		var pageNumberElement = jQuery('.pageNumbersText', listViewContainer);
		var pageRange = pageNumberElement.text();
		var newPagingInfo = pageRange.trim() + " " + app.vtranslate('of') + " " + totalNumberOfRecords + "  ";
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries', listViewContainer).val());

		if (listViewEntriesCount !== 0) {
			jQuery('.pageNumbersText', listViewContainer).html(newPagingInfo);
		} else {
			jQuery('.pageNumbersText', listViewContainer).html("");
		}
	},
	registerEventToShowQuickPreview: function () {
		var self = this;
		var listViewPageDiv = self.getListViewContainer();
		listViewPageDiv.on('click', '.quickView', function (e) {
			var element = listViewPageDiv.find(e.currentTarget);
			var app = element.data('app');
			var row = element.closest('.listViewEntries');
			var recordId = row.data('id');
			self.showQuickPreviewForId(recordId, app);
		});

	},
	registerMoreRecentUpdatesClickEvent: function (container, recordId) {
		container.find('.moreRecentUpdates').on('click', function () {
			var recentUpdateURL = "index.php?view=Detail&mode=showRecentActivities&page=1&module=" + app.getModuleName() + "&record=" + recordId + "&tab_label=LBL_UPDATES";
			window.location.href = recentUpdateURL;
		});
	},
	registerNextRecordClickEvent: function (container) {
		var self = this;
		container.find('#quickPreviewNextRecordButton').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var nextRecordId = element.data('record');
			var appName = element.data('app');
			self.showQuickPreviewForId(nextRecordId, appName);
		});
	},
	registerPreviousRecordClickEvent: function (container) {
		var self = this;
		container.find('#quickPreviewPreviousRecordButton').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var prevRecordId = element.data('record');
			var appName = element.data('app');
			self.showQuickPreviewForId(prevRecordId, appName);
		});
	},
	showQuickPreviewForId: function (recordId, appName) {
		var self = this;
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		vtigerInstance.showQuickPreviewForId(recordId, self.getModuleName(), appName);
	},
	registerDynamicListHeaders: function () {
		var self = this;
		var listViewContainer = this.getListViewContainer();
		if (jQuery('#filterListColumns').length > 0) {
			jQuery('#filterListColumns').instaFilta({
				targets: '.listColumnFilter .list-group-item',
				sections: '.listColumnFilter .block-item',
				hideEmptySections: true,
				beginsWith: false,
				caseSensitive: false,
				typeDelay: 0
			});
		}
		jQuery('.listColumnFilter .dropdown-menu').on('click', function (e) {
			e.stopPropagation();
		});
		var params = {
			setHeight: ''
		};
		app.helper.showVerticalScroll(jQuery('.viewColumnsList'), params);

		jQuery('.viewColumnsList').on('click', 'input[type="checkbox"]', function (e, params) {
			var listHeaderFieldEle = $('input[name="list_headers"]');
			var listHeaderFields = JSON.parse(listHeaderFieldEle.val());
			if ($(this).is(':checked')) {
				listHeaderFields = app.helper.array_merge(listHeaderFields, [$(this).val()]);
			} else {
				var index = app.helper.array_search($(this).val(), listHeaderFields);
				delete listHeaderFields[index];
			}
			listHeaderFieldEle.val(JSON.stringify(listHeaderFields));
		});

		jQuery('#updateListing').on('click', function (e, params) {
			if (typeof params === "undefined" || !params.noFilterLoad) {
				self.loadListViewRecords();
			}
		});
	},
	/*
	 * Function to check whether atleast one record is checked
	 */
	checkListRecordSelected: function () {
		var selectedIds = this.readSelectedIds();
		if (typeof selectedIds == 'object' && selectedIds.length <= 0) {
			return true;
		}
		return false;
	},
	/**
	 * Function to update Pagining status
	 */
	updatePagination: function () {
		var listViewContainer = this.getListViewContainer();
		var previousPageExist = jQuery('#previousPageExist', listViewContainer).val();
		var nextPageExist = jQuery('#nextPageExist', listViewContainer).val();
		var previousPageButton = jQuery('#PreviousPageButton', listViewContainer);
		var nextPageButton = jQuery('#NextPageButton', listViewContainer);
		var pageJumpButton = jQuery('#PageJump', listViewContainer);
		var listViewEntriesCount = parseInt(jQuery('#noOfEntries', listViewContainer).val());
		var pageStartRange = parseInt(jQuery('#pageStartRange', listViewContainer).val());
		var pageEndRange = parseInt(jQuery('#pageEndRange', listViewContainer).val());
		var pages = jQuery('#totalPageCount', listViewContainer).text();
		var totalNumberOfRecords = jQuery('.totalNumberOfRecords', listViewContainer);
		var pageNumbersTextElem = jQuery('.pageNumbersText', listViewContainer);
		var currentPageNumber = jQuery('#pageNumber', listViewContainer).val();
		jQuery('#pageToJump', listViewContainer).val(currentPageNumber);

		if (pages > 1) {
			pageJumpButton.removeAttr('disabled');
		}
		if (previousPageExist !== "") {
			previousPageButton.removeAttr('disabled');
		} else if (previousPageExist === "") {
			previousPageButton.attr("disabled", "disabled");
		}

		if ((nextPageExist !== "")) {
			nextPageButton.removeAttr('disabled');
		} else if ((nextPageExist === "") || (pages === 1)) {
			nextPageButton.attr("disabled", "disabled");
		}

		if (listViewEntriesCount !== 0) {
			var pageNumberText = pageStartRange + " " + app.vtranslate('to') + " " + pageEndRange;
			pageNumbersTextElem.html(pageNumberText);
			totalNumberOfRecords.removeClass('hide');
		} else {
			pageNumbersTextElem.html("<span>&nbsp;</span>");
			if (!totalNumberOfRecords.hasClass('hide')) {
				totalNumberOfRecords.addClass('hide');
			}
		}

	},
	initializePaginationEvents: function () {
		var thisInstance = this;
		var paginationObj = this.getComponentInstance('Vtiger_Pagination_Js');
		var listViewContainer = thisInstance.getListViewContainer();
		paginationObj.initialize(listViewContainer);

		app.event.on(paginationObj.nextPageButtonClickEventName, function () {
			var pageLimit = listViewContainer.find('#pageLimit').val();
			var noOfEntries = listViewContainer.find('#noOfEntries').val();
			var nextPageExist = listViewContainer.find('#nextPageExist').val();
			var pageNumber = listViewContainer.find('#pageNumber').val();
			var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
			if (noOfEntries === pageLimit && nextPageExist) {
				var urlParams = {};
				listViewContainer.find("#pageNumber").val(nextPageNumber);
				thisInstance.loadListViewRecords(urlParams);
			}
		});

		app.event.on(paginationObj.previousPageButtonClickEventName, function () {
			var pageNumber = listViewContainer.find('#pageNumber').val();
			var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;

			if (pageNumber > 1) {
				var urlParams = {};
				listViewContainer.find('#pageNumber').val(previousPageNumber);
				thisInstance.loadListViewRecords(urlParams);
			}
		});

		app.event.on(paginationObj.pageJumpButtonClickEventName, function (event, currentEle) {
			thisInstance.pageJump();
		});

		app.event.on(paginationObj.totalNumOfRecordsButtonClickEventName, function (event, currentEle) {
			thisInstance.totalNumOfRecords(currentEle);
		});

		app.event.on(paginationObj.pageJumpSubmitButtonClickEvent, function (event, currentEle) {
			thisInstance.pageJumpOnSubmit(currentEle);
		});
	},
	saveTag: function (callerParams) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module': app.getModuleName(),
			'action': 'TagCloud',
			'mode': 'saveTags'
		};
		var cvId = this.getCurrentCvId();
		params.viewname = cvId;
		params.search_params = JSON.stringify(this.getListSearchParams());
		params = jQuery.extend(params, this.getListSelectAllParams(false));

		var params = jQuery.extend(params, callerParams);
		app.request.post({'data': params}).then(
				function (error, data) {
					if (error == null) {
						aDeferred.resolve(data);
					} else {
						aDeferred.reject(data);
					}
				}
		);
		return aDeferred.promise();
	},
	constructTagElement: function (params) {
		var tagElement = jQuery(jQuery('#dummyTagElement').html()).clone(true);
		tagElement.attr('data-id', params.id).attr('data-type', params.type);
		tagElement.find('.tagLabel').text(params.name);
		return tagElement
	},
	addTagToList: function (tagElement) {
		var container = jQuery('#listViewTagContainer');
		var tagsListCount = parseInt(container.data('listTagCount'));
		if (container.find('>.tag').length < tagsListCount) {
			container.append(tagElement);
		} else {
			container.find('.moreListTags').append(tagElement);
		}
		var moreTagTriggerEle = container.find('.moreTagCount');
		var moreTagCount = parseInt(moreTagTriggerEle.text());
		moreTagCount++;
		moreTagTriggerEle.text(moreTagCount);
		if (moreTagTriggerEle.hasClass('hide') && moreTagCount > 0 && (!container.find('.moreListTags').hasClass('hide'))) {
			moreTagTriggerEle.removeClass('hide');
		}

	},
	checkAndAddTagsToList: function (tagEleList) {
		var self = this;
		var container = jQuery('#listViewTagContainer');
		tagEleList.each(function (index, domEle) {
			var tagEle = jQuery(domEle);
			var tagId = tagEle.data('id');
			if (container.find('[data-id="' + tagId + '"]').length <= 0) {
				// No delete option from list view
				tagEle.find(".deleteTag").remove();
				self.addTagToList(tagEle);
			}
		})

	},
	addToExistingTagSelector: function (tagEle) {
		var showAllTagContainer = jQuery('.showAllTagContainer');
		var existingTagMenu = showAllTagContainer.find('.currentTagMenu');
		if (existingTagMenu.find('.tag[data-id="' + tagEle.data('id') + '"]').length <= 0) {
			var clonedTagEle = tagEle;
			clonedTagEle.find('.editTag').remove();
			var dummyTagListElement = existingTagMenu.find('.dummyExistingTagElement').clone(true).removeClass('dummyExistingTagElement');
			dummyTagListElement.find('.tag').replaceWith(clonedTagEle);
			dummyTagListElement.removeClass('hide').appendTo(existingTagMenu.find('ul'));
		}
	},
	showAllTags: function (container) {
		var self = this;
		var recordParams = {};
		var cvId = this.getCurrentCvId();
		recordParams.viewname = cvId;
		recordParams.search_params = JSON.stringify(this.getListSearchParams());
		recordParams = jQuery.extend(recordParams, this.getListSelectAllParams(false));

		app.event.one('post.MassTag.save', function (e, modalContainerClone, data) {
			var newlySelectedTags = modalContainerClone.find('.currentTag .tag');
			self.checkAndAddTagsToList(newlySelectedTags);
			var newlyAddedTags = data['new'];
			for (var tagId in newlyAddedTags) {
				var newTagParams = newlyAddedTags[tagId];
				newTagParams.id = tagId;
				var newTagEle = self.constructTagElement(newTagParams);
				self.addTagToList(newTagEle.clone(true));
				self.addToExistingTagSelector(newTagEle.clone(true));
			}
			app.helper.showSuccessNotification({"message": ''});
		});

		app.event.trigger('Request.MassTag.show', container, recordParams);
	},
	massRemoveTag: function (tagId) {
		var self = this;
		var tagElement = jQuery("#listViewTagContainer").find('[data-id="' + tagId + '"]');
		var tagName = tagElement.find('.tagLabel').text();
		var message = app.vtranslate('JS_REMOVE_MASS_TAG_WARNING', tagName);
		app.helper.showConfirmationBox({'message': message}).then(function () {
			var tagInstance = self.getComponentInstance('Vtiger_Tag_Js');
			var deleteParams = {};
			var cvId = self.getCurrentCvId();
			deleteParams.search_params = JSON.stringify(self.getListSearchParams());
			deleteParams = jQuery.extend(deleteParams, self.getListSelectAllParams(false));
			deleteParams.viewname = cvId;

			var saveTagList = {};
			var tagIdsToDelete = new Array();
			var tagIdsToAdd = new Array();
			var existingTagIdList = new Array();

			tagIdsToDelete.push(tagId);
			saveTagList['existing'] = existingTagIdList;
			saveTagList['new'] = tagIdsToAdd;
			saveTagList['deleted'] = tagIdsToDelete;
			deleteParams['tagsList'] = saveTagList;

			tagInstance.saveTag(deleteParams).then(function (data) {
				self.clearList();
				self.loadListViewRecords().then(function (e) {
					app.helper.showSuccessNotification({"message": ''});
				});
			});
		})

	},
	registerEditLink: function () {
		jQuery('#page').on('click', 'a[name="editlink"]', function (e) {
			var element = jQuery(e.currentTarget);
			var url = element.data('url');
			var listInstance = Vtiger_List_Js.getInstance();
			var postData = listInstance.getDefaultParams();
			for (var key in postData) {
				if (postData[key]) {
					postData['return' + key] = postData[key];
					delete postData[key];
				} else {
					delete postData[key];
				}
			}
			e.preventDefault();
			e.stopPropagation();
			window.location.href = url + '&' + $.param(postData);
		});
	},
	registerDynamicDropdownPosition: function (container, relativeto) {
		if (typeof jQuery.fn.sadropdown === 'function') {
			jQuery('.' + container).find('.dropdown-toggle').sadropdown({
				relativeTo: '.' + relativeto
			});
		}
	},
	registerPostLoadListViewActions: function () {
		var self = this;
		var recordSelectTrackerObj = self.getRecordSelectTrackerInstance();

		var selectedIds = recordSelectTrackerObj.getSelectedIds();
		if (selectedIds != '') {
			self.enableListViewActions();
		} else {
			self.disableListViewActions();
		}
		self.registerDynamicDropdownPosition();
		self.registerDropdownPosition();

	},
	enableListViewActions: function () {
		jQuery('.btn-group.listViewActionsContainer').find('button').removeAttr('disabled');
		jQuery('.btn-group.listViewActionsContainer').find('li').removeClass('hide');
	},
	disableListViewActions: function () {
		jQuery('.btn-group.listViewActionsContainer').find('button').attr('disabled', "disabled");
		jQuery('.btn-group.listViewActionsContainer').find('.dropdown-toggle').removeAttr("disabled");
		jQuery('.btn-group.listViewActionsContainer').find('li').addClass('hide');
		var selectFreeRecords = jQuery('.btn-group.listViewActionsContainer').find('li.selectFreeRecords');
		selectFreeRecords.removeClass('hide');
		if (selectFreeRecords.length == 0) {
			jQuery('.btn-group.listViewActionsContainer').find('.dropdown-toggle').attr('disabled', "disabled");
		}
	},
	/*
	 * Function to register the click event of email field
	 */
	registerEmailFieldClickEvent: function () {
		var listViewContentDiv = this.getListViewContainer();
		listViewContentDiv.on('click', '.emailField', function (e) {
			e.stopPropagation();
		});
	},
	/*
	 * Function to register the click event of phone field
	 */
	registerPhoneFieldClickEvent: function () {
		var listViewContentDiv = this.getListViewContainer();
		listViewContentDiv.on('click', '.phoneField', function (e) {
			e.stopPropagation();
		});
	},
	registerConfigureColumnsEvents: function () {
		var thisInstance = this;
		var listViewContentDiv = this.getListViewContainer();
		listViewContentDiv.on('click', '.listColumnFilter', function (e) {
			if (jQuery(e.currentTarget).hasClass('disabled')) {
				return false;
			}
			var params = {
				module: app.module(),
				view: 'ListAjax',
				mode: 'ShowListColumnsEdit',
				source_module: app.module(),
				cvid: thisInstance.getCurrentCvId()
			};

			var callback = function (container) {
				var selectedFieldsList = jQuery('#selectedFieldsList');
				var selectedFieldsListContainer = container.find('.selectedFieldsListContainer');
				var availFieldsList = jQuery('#avialFieldsList');
				var availFieldsListContainer = container.find('.avialFieldsListContainer');

				//register ui events
				app.helper.showVerticalScroll(availFieldsListContainer,
						{setHeight: '200', advanced: {updateOnSelectorChange: 'true'}});
				app.helper.showVerticalScroll(selectedFieldsListContainer,
						{setHeight: '250', advanced: {updateOnSelectorChange: 'true'}});

				selectedFieldsList.sortable({
					start: function (e, ui) {
						if (!ui.item.hasClass('active')) {
							ui.item.addClass('active');
						}
					},
					stop: function (e, ui) {
						ui.item.removeClass('active');
					}
				});

				container.find('.searchAvailFields').instaFilta({
					onFilterComplete: function (macthedItems) {
						if (macthedItems.length > 0) {
							jQuery.each(macthedItems, function (i, ele) {
								var parent = jQuery(ele).closest('.instafilta-section');
								var availFieldBlock = parent.find('.availFieldBlock');
								if (availFieldBlock.find('i').hasClass('fa-caret-right')) {
									availFieldBlock.find('a[data-parent="#accordion"]').trigger('click');
								}
							});
						}
					}
				});

				availFieldsListContainer.on('click', '.availFieldBlock a[data-parent="#accordion"]', function (e) {
					var target = jQuery(e.currentTarget);
					var closestItag = target.find('i');
					if (closestItag.hasClass('fa-caret-right')) {
						closestItag.removeClass('fa-caret-right').addClass('fa-caret-down');
					} else {
						closestItag.removeClass('fa-caret-down').addClass('fa-caret-right');
					}

					if (params && params.autoIconChangeForOthers) {
						return true;
					}
				});

				//remove selected field event
				selectedFieldsList.on('click', '.removeField', function (e) {
					var selectedFieldsEles = selectedFieldsList.find('.item');
					if (selectedFieldsEles.length <= 1) {
						app.helper.showErrorNotification({message: app.vtranslate('Atleast one field should be selected')});
						return false;
					}
					var ele = jQuery(e.currentTarget);
					var sourceFieldEle = ele.parent('.item');
					var targetFieldEle = availFieldsListContainer.find('.item[data-cv-columnname="' + sourceFieldEle.attr('data-cv-columnname') + '"]');
					targetFieldEle.removeClass('hide');
					sourceFieldEle.remove();
				});

				//add available field to selected list
				availFieldsList.on('click', '.item', function (e) {
					var selectedFieldsEles = selectedFieldsList.find('.item');
					if (selectedFieldsEles.length > 15) {
						app.helper.showErrorNotification({message: app.vtranslate('JS_ADD_MAX_15_ITEMS')});
						return false;
					}
					var sourceFieldEle = jQuery(e.currentTarget);
					var targetFieldEle = selectedFieldsListContainer.find('.item-dummy').clone();
					targetFieldEle.removeClass('hide item-dummy').addClass('item');
					targetFieldEle.attr('data-cv-columnname', sourceFieldEle.attr('data-cv-columnname'));
					targetFieldEle.attr('data-columnname', sourceFieldEle.attr('data-columnname'));
					targetFieldEle.attr('data-field-id', sourceFieldEle.attr('data-field-id'));
					targetFieldEle.find('.fieldLabel').html(sourceFieldEle.find('.fieldLabel').html());
					targetFieldEle.appendTo(selectedFieldsList);
					sourceFieldEle.addClass('hide');
				});

				var configColumnsForm = container.find('.configColumnsForm');
				var params = {
					submitHandler: function (form) {
						var formData = jQuery(form).serializeFormData();
						var columnsList = [];
						var selectedFieldEles = selectedFieldsList.find('.item');
						jQuery.each(selectedFieldEles, function (i, e) {
							var ele = jQuery(e);
							columnsList.push(ele.attr('data-cv-columnname'));
						});

						formData.source_module = app.module();
						formData.columnslist = JSON.stringify(columnsList);
						app.helper.showProgress();
						app.request.post({data: formData}).then(function (err, res) {
							app.helper.hideProgress();
							if (err) {
								app.helper.showErrorNotification({"message": err});
								return false;
							} else {
								app.helper.showSuccessNotification({message: res.message});
								app.helper.hideModal();
								var appName = app.getAppName();
								var url = res['listviewurl'] + '&app=' + appName;
								window.location.href = url;
							}
						});
					}
				};

				configColumnsForm.vtValidate(params);
			}

			app.helper.showProgress();
			app.request.post({data: params}).then(function (err, res) {
				app.helper.hideProgress();
				if (!err) {
					app.helper.showModal(jQuery(res), {cb: callback});
				}
			});

		});
	},
	/**
	 * Function to get field value of closeset element from sourceElement
	 * @param {type} fieldName
	 * @param {type} sourceElement
	 * @returns {unresolved}
	 */
	getFieldValue: function (fieldName, sourceElement) {
		var closestContextEle = sourceElement.closest('tr');
		return closestContextEle.find('[name="' + fieldName + '"]').val();
	},
	/**
	 * Function to register event to handle stickyhead for window scroll action
	 * @returns {undefined}
	 */
	registerEventToHandleStickyHeadOnWindowScroll: function () {
		var thisInstance = this;
		jQuery(window).scroll(function () {
			var container = thisInstance.getListViewContainer();
			var stickyHeader = container.find('.sticky-thead');
			if (stickyHeader.css('display') == "block") {
				var select2Elem = jQuery(stickyHeader).find('select.select2');
				if (select2Elem.length > 1) {
					jQuery(select2Elem).each(function (key, value) {
						var element = jQuery(value);
						var parentTableHeader = element.closest('th');
						parentTableHeader.find('div.select2-container').remove();
						vtUtils.showSelect2ElementView(element);
					});
				} else {
					var parentTableHeader = select2Elem.closest('th');
					parentTableHeader.find('div.select2-container').remove();
					vtUtils.showSelect2ElementView(select2Elem);
				}
			}
		});
	},
	registerPostListLoadListener: function () {
		var self = this;
		app.event.on('post.listViewFilter.click', function (event, searchRow) {
			if (searchRow) {
				vtUtils.applyFieldElementsView(searchRow);
				self.filterClick = false;
			}
			self.registerFloatingThead();
		});
	},

	registerEvents: function () {
		var thisInstance = this;
		this._super();
		this.registerListViewSort();
		this.registerRemoveListViewSort();
		this.registerRowClickEvent();
		this.registerRowDoubleClickEvent();
		this.registerListViewBasicActions();
		this.registerListViewSearch();
		this.registerDeleteRecordClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeSelectAllClickEvent();
		this.registerListViewMainCheckBoxClickEvent();
		this.registerStarToggle();
		this.registerStarUnstarFilter();
		this.registerDynamicListHeaders();
		this.registerEditLink();
		this.registerEmailFieldClickEvent();
		this.registerPhoneFieldClickEvent();
		this.registerDynamicDropdownPosition();
		this.registerDropdownPosition();
		this.registerConfigureColumnsEvents();
		var recordSelectTrackerObj = this.getRecordSelectTrackerInstance();
		recordSelectTrackerObj.registerEvents();

		this.registerPostListLoadListener();
		this.registerPostLoadListViewActions();

		app.event.on('post.listViewMassEditSave', function () {
			var urlParams = {};
			thisInstance.loadListViewRecords(urlParams);
			thisInstance.clearList();
		});
		this.registerEventToShowQuickPreview();
		//reference fields quick preview handled in Vtiger.js
//        this.registerEventToHandleStickyHeadOnWindowScroll();
//        if(jQuery('#listViewContent').find('table.listview-table').length){
//            if(jQuery('.sticky-wrap').length == 0){
//                stickyheader.controller();
//                var container = this.getListViewContainer();
//                container.find('.sticky-thead').addClass('listview-table');
//                app.helper.dynamicListViewHorizontalScroll();
//            }
//        }
//      
		//floatTHead, some timeout so correct height can be caught for computations
		setTimeout(function () {
			thisInstance.registerFloatingThead();
		}, 10);

		app.event.on('Vtiger.Post.MenuToggle', function () {
			thisInstance.reflowList();
		});

		this.registerDynamicDropdownPosition();
		this.registerHeaderReflowOnListSearchSelections();

		this.registerDropdownPosition();
		//For Pagination
		thisInstance.initializePaginationEvents();
		//END
	},
	registerHeaderReflowOnListSearchSelections: function () {
		var listViewContentDiv = this.getListViewContainer();
		var thisInstance = this;
		var $table = jQuery('#listview-table');
		listViewContentDiv.on('select2-selecting select2-close select2-removed',
				'.listSearchContributor',
				function () {
					thisInstance.reflowList($table);
				});
	},
	registerDropdownPosition: function (container) {
		if (typeof container === "undefined") {
			container = "#page";
		}
		jQuery('.table-actions').on('click', '.dropdown', function (e) {
			var containerTarget = jQuery(this).closest(container);
			var content = jQuery(this).closest(".dropdown");
			var dropdown = jQuery(e.currentTarget);
			if (dropdown.find('[data-toggle]').length <= 0) {
				return;
			}
			var dropdown_menu = dropdown.find('.dropdown-menu');

			var dropdownStyle = dropdown_menu.find('li a');
			dropdownStyle.css('padding', "0 6px", 'important');

			var fixed_dropdown_menu = dropdown_menu.clone(true);
			fixed_dropdown_menu.data('original-menu', dropdown_menu);
			dropdown_menu.css('position', 'relative');
			dropdown_menu.css('display', 'none');
			var currtargetTop;
			var currtargetLeft;
			var dropdownBottom;
			var ftop = 'auto';
			var fbottom = 'auto';

			if (container === "#page") {
				currtargetTop = dropdown.offset().top + dropdown.height();
				currtargetLeft = dropdown.offset().left;
				dropdownBottom = jQuery(window).height() - currtargetTop + dropdown.height();

			}
			var windowBottom = jQuery(window).height() - dropdown.offset().top;
			if (windowBottom < 250) {
				ftop = 'auto';
				fbottom = dropdownBottom + 'px';
			}
			else {
				ftop = currtargetTop + 'px';
				fbottom = "auto";
			}
			fixed_dropdown_menu.css({
				'display': 'block',
				'position': 'absolute',
				'top': ftop,
				'left': currtargetLeft + 'px',
				'bottom': fbottom
			}).appendTo(containerTarget);

			$('#table-content').scroll(function () {
				var tTop;
				var cBottom = $('#table-content').height() - content.position().top;
				var tBottom;
				if (cBottom < 250) {
					tTop = "auto";
					tBottom = dropdown.height();
				}
				else {
					tTop = dropdown.height();
					tBottom = "auto";
				}
				if (content.hasClass('open')) {
					fixed_dropdown_menu.css({
						'display': 'block',
						'top': tTop,
						'position': 'absolute',
						'bottom': tBottom,
						'left': 0,
						'z-index': 100
					}).appendTo(content);
				}
				else {
					dropdown_menu.css('display', 'none');
				}
			});

			dropdown.on('hidden.bs.dropdown', function () {
				dropdown_menu.removeClass('invisible');
				fixed_dropdown_menu.remove();
				jQuery('.listViewEntries').removeClass('dropDownOpen');
			});
		});
		jQuery('.listViewEntries').mouseleave(function (e) {
			var currentDropDown = jQuery(e.currentTarget).find('.dropdown');
			setTimeout(function () {
				if (jQuery('.dropdown-menu:hover').length == 0) {
					if (currentDropDown.hasClass('open')) {
						jQuery(e.currentTarget).find('.dropdown').trigger('click');
					}
					jQuery(e.currentTarget).removeClass('dropDownOpen');
				} else {
					jQuery(e.currentTarget).addClass('dropDownOpen');
				}
			}, 50);
		});
	},
	getListViewContentHeight: function () {
		var windowHeight = jQuery(window).height();
		//list height should be 76% of window height
		var listViewContentHeight = windowHeight * 0.76103500761035;
		var listViewTable = jQuery('#listview-table');
		if (listViewTable.length) {
			if (!listViewTable.find('tr.emptyRecordsDiv').length) {
				var listTableHeight = jQuery('#listview-table').height();
				if (listTableHeight < listViewContentHeight) {
					listViewContentHeight = listTableHeight + 3;
				}
			}
		}
		return listViewContentHeight + 'px';
	},
	getListViewContentWidth: function () {
		return '100%';
	},
	reflowList: function () {
		if (typeof $.fn.perfectScrollbar !== 'function' || typeof $.fn.floatThead !== 'function') {
			return;
		}
		var $table = jQuery('#listview-table');
		if (!$table.length)
			return;
		var height = this.getListViewContentHeight();
		var width = this.getListViewContentWidth();
		var tableContainer = $table.closest('.table-container');
		tableContainer.css({
			'position': 'relative',
			'height': height,
			'width': width
		});
		tableContainer.perfectScrollbar('update');
		$table.floatThead('reflow');
	},
	registerFloatingThead: function () {
		if (typeof $.fn.perfectScrollbar !== 'function' || typeof $.fn.floatThead !== 'function') {
			return;
		}
		var $table = jQuery('#listview-table');
		if (!$table.length)
			return;
		var height = this.getListViewContentHeight();
		var width = this.getListViewContentWidth();
		var tableContainer = $table.closest('.table-container');
		tableContainer.css({
			'position': 'relative',
			'height': height,
			'width': width
		});

		tableContainer.perfectScrollbar({
			'wheelPropagation': true
		});

		$table.floatThead({
			scrollContainer: function ($table) {
				return $table.closest('.table-container');
			}
		});
	},
	getSelectedRecordCount: function () {
		var count = 0;
		var selectedRecords = this.readSelectedIds();
		if (selectedRecords) {
			if (selectedRecords != 'all') {
				count = selectedRecords.length;
			} else {
				var excludedIdsCount = this.readExcludedIds().length;
				var totalRecords = jQuery('#totalRecordsCount').text();
				count = totalRecords - excludedIdsCount;
			}
		}

		return count;
	}
});