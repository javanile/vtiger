/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger.Class('Documents_Index_Js', {

	fileObj : false,
	referenceCreateMode : false,
	referenceFieldName : '',

	getInstance : function() {
		return new Documents_Index_Js();
	},

	uploadTo : function(service,parentId,relatedModule,referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.uploadTo(service,parentId,relatedModule);
	},

	createDocument : function(type,parentId,relatedModule,referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.createDocument(type,parentId,relatedModule);
	},

}, {

	detectReferenceCreateMode : function(referenceFieldName) {
		if(typeof referenceFieldName !== 'undefined') {
			Documents_Index_Js.referenceCreateMode = true;
			Documents_Index_Js.referenceFieldName = referenceFieldName;
		} else {
			Documents_Index_Js.referenceCreateMode = false;
			Documents_Index_Js.referenceFieldName = '';
		}
	},

	getFile : function() {
		return Documents_Index_Js.fileObj;
	},

	setFile : function(file) {
		Documents_Index_Js.fileObj = file;
	},

	isRelatedList : function() {
		var relatedModuleNameContainer = jQuery('.relatedContainer').find('.relatedModuleName');
		return relatedModuleNameContainer.length && relatedModuleNameContainer.val() === 'Documents';
	},

	reloadListView : function() {
		var activeFolderEle = jQuery("#folders-list").find('li.documentFolder.active');
		var params = {};
		if(activeFolderEle.length) {
			var activeFolderName = activeFolderEle.find('.filterName').data('folderName');
			params ={
				"folder_id" : 'folderid',
				"folder_value" : activeFolderName
			};
		}

		var list = Vtiger_List_Js.getInstance();
		list.loadListViewRecords(params);
	},

	reloadRelatedListView : function() {
		var parentId = jQuery('#recordId').val();
		var parentModule = app.getModuleName();
		var relatedModuleName = jQuery('.relatedModuleName').val();
		var selectedRelatedTabElement = jQuery('div.related-tabs').find('li').filter('.active');
		var relatedList = Vtiger_RelatedList_Js.getInstance(parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
		relatedList.loadRelatedList();
	},

	isDocumentsSummaryWidgetAvailable : function() {
		return jQuery('.widgetContainer_documents').length;
	},

	reloadSummaryWidget : function() {
		var detailInstance = Vtiger_Detail_Js.getInstance();
		detailInstance.loadWidget(jQuery('.widgetContainer_documents'));
	},

	reloadList : function() {
		if(app.getModuleName() === 'Documents' && app.view() === 'List') {
			this.reloadListView();
		} else if(this.isRelatedList()) {
			this.reloadRelatedListView();
		} else if(this.isDocumentsSummaryWidgetAvailable()) {
			this.reloadSummaryWidget();
		}
	},

	_upload : function(form,extraData) {
		var aDeferred = jQuery.Deferred();
		var formData = new FormData(form[0]);
		var file = this.getFile();
		if(file) {
			if(typeof extraData === 'object') {
				jQuery.each(extraData, function(name,value) {
					formData.append(name,value);
				});
			}
			//append file
			var fileName = form.find('input[type="file"]').attr('name');
			formData.append(fileName,file);

			var params = {
				url: "index.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false
			};
			app.helper.showProgress();
			app.request.post(params).then(function(e,res) {
				app.helper.hideProgress();
				if(!e) {
					aDeferred.resolve(res);
				} else {
					aDeferred.reject(e);
				}
			});
		} else {
			aDeferred.reject();
		}
		return aDeferred.promise();
	},

	uploadFileToVtiger : function(container) {
		var self = this;
		var file = this.getFile();
		if(!file) {
			app.helper.showErrorNotification({
				'message' : app.vtranslate('JS_PLEASE_SELECT_A_FILE')
			});
			return;
		}
		var extraData = {
			'filelocationtype' : 'I'
		};
		if(file) {
			extraData['notes_title'] = container.find('form').find('[name="notes_title"]').val();
		}

		this._upload(container.find('form'),extraData).then(function(data) {
			app.helper.showSuccessNotification({
				'message' : app.vtranslate('JS_UPLOAD_SUCCESSFUL')
			});
			app.helper.hideModal();
			self.reloadList();
			var form = container.find('form');
			var folderid = form.find('[name="folderid"]').val();
			app.event.trigger('post.documents.save', {'folderid' : folderid});

			//reference create handling
			if(Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
				self.postQuickCreateSave(data);
			}
		}, function(e) {
			app.helper.showErrorNotification({'message' : app.vtranslate('JS_UPLOAD_FAILED')});
		});
	},

	postQuickCreateSave: function (data) {
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		var container = vtigerInstance.getParentElement(jQuery('[name="'+Documents_Index_Js.referenceFieldName+'"]'));
		var module = vtigerInstance.getReferencedModuleName(container);
		var params = {};
		params.name = data._recordLabel;
		params.id = data._recordId;
		params.module = module;
		vtigerInstance.setReferenceFieldValue(container, params);

		var tdElement = vtigerInstance.getParentElement(container.find('[value="' + module + '"]'));
		var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
		var fieldElement = tdElement.find('input[name="' + sourceField + '"]');
		vtigerInstance.autoFillElement = fieldElement;
		var parentModule = jQuery('.editViewContents [name=module]').val();
		if (parentModule != "Events") {
			vtigerInstance.postRefrenceSearch(params, container);
		}
		tdElement.find('input[class="sourceField"]').trigger(Vtiger_Edit_Js.postReferenceQuickCreateSave, {'data': data});
	},

	showFileDetails : function(container) {
		var fileObj = this.getFile();
		if(fileObj) {
			var fileName = fileObj.name;
			var fileSize = fileObj.size;
			fileSize = vtUtils.convertFileSizeInToDisplayFormat(fileSize);
			container.find('.fileDetails').text(fileName + ' (' + fileSize + ')');
			var fileParts = fileName.split('.');
			var fileType = fileParts[fileParts.length - 1];
			container.find('[name="notes_title"]').val(fileName.replace('.'+fileType, ''));
		}
	},

	registerFileDragDropEvent : function(container) {
		var self = this;
		var dragDropElement = container.find("#dragandrophandler");
		dragDropElement.on('dragenter', function(e) {
			e.stopPropagation();
			e.preventDefault();
			jQuery(this).addClass('dragdrop-solid');
		}).on('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		jQuery(document).on('dragenter', function(e) {
			e.stopPropagation();
			e.preventDefault();
		}).on('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
			dragDropElement.removeClass('dragdrop-solid');
		}).on('drop', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		dragDropElement.on('drop', function(e) {
			e.preventDefault();

			jQuery(this).removeClass('dragdrop-solid');
			jQuery(this).addClass('dragdrop-dotted');

			var fileObj = e.originalEvent.dataTransfer.files;
			var file = fileObj[0];
			if(self.fileSizeCheck(container, file)) {
				self.setFile(file);
				container.find('input[name="filename"]').val(null);
				self.showFileDetails(container);
			} else {
				app.helper.showAlertNotification({
					'message' : app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
				});
			}
		});
	},

	getMaxUploadLimit : function(container) {
		return container.find('input[name="max_upload_limit"]').val() || 0;
	},

	fileSizeCheck : function(container, file) {
		var maxUploadLimitInBytes = this.getMaxUploadLimit(container);
		return file.size <= maxUploadLimitInBytes;
	},

	registerFileChangeEvent : function(container) {
		var self = this;
		jQuery('input[type="file"]',container).on('change', function(e) {
			var file = e.target.files[0];
			if(self.fileSizeCheck(container, file)) {
				self.setFile(file);
				self.showFileDetails(container);
			} else {
				app.helper.showAlertNotification({
					'message' : app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
				});
			}
		});
	},

	registerFileHandlingEvents : function(container) {
		this.registerFileChangeEvent(container);
		this.registerFileDragDropEvent(container);
		container.find('input[type="file"]').addClass('ignore-validation');
		vtUtils.enableTooltips();
	},

	updateDirectoryMeta : function(folderId,tab,backwardNavigation) {
		backwardNavigation = (typeof backwardNavigation == "undefined") ? false : true;
		var currentDirElement = jQuery('input[name="currentDir"]',tab);
		var parentDirElement = jQuery('input[name="parentDir"]',tab);
		var currentDir = currentDirElement.val();
		var parentDir = parentDirElement.val();
		if(!backwardNavigation) {
			parentDirElement.val(currentDir);
			currentDirElement.val(folderId);
			jQuery('.browseBack',tab).removeAttr('disabled');
			jQuery('.gotoRoot',tab).removeAttr('disabled');
		} else {
			currentDirElement.val(folderId);
			parentDirElement.val(parentDir);
		}
	},

	loadTab : function(tab) {
		var self = this;
		var url = tab.data('url');
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				tab.html(resp);
				vtUtils.applyFieldElementsView(tab);
			} else {
				console.log("error while loading tab : ",e);
			}
		});
		tab.data('tabLoaded',true);
	},

	registerActiveTabEvent : function(container) {
		var self = this;
		jQuery('.tab-pane',container).on('Documents.Upload.Tab.Active', function() {
			var currentTab = jQuery(this);
			if(!currentTab.data('tabLoaded')) {
				self.loadTab(currentTab);
			}
		});
	},

	registerUploadDocumentEvents : function(container) {
		var self = this;
		container.find('form').vtValidate({
			'submitHandler' : function() {
				self.uploadFileToVtiger(container);
				return false;
			}
		});
		self.registerQuickCreateEvents(container);
		this.registerFileHandlingEvents(container);
	},

	showUploadToVtigerModal : function(parentId,relatedModule) {
		var self = this;
		var url = 'index.php?module=Documents&view=QuickCreateAjax&service=Vtiger&operation=UploadToVtiger&type=I';
		if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
			url += '&relationOperation=true&sourceModule='+relatedModule+'&sourceRecord='+parentId;
		}
		var relationField = jQuery('div.related-tabs').find('li').filter('.active').data('relatedfield');
		if (relationField && parentId) {
			url += '&'+relationField+"="+parentId;
		}		
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(resp, {
					'cb' : function(modalContainer) {
						self.registerUploadDocumentEvents(modalContainer);
						self.applyScrollToModal(modalContainer);
						self.registerQuickCreateEvents(modalContainer);
					}
				});
			}
		});
	},

	applyScrollToModal : function(modalContainer) {
		app.helper.showVerticalScroll(modalContainer.find('.modal-body').css('max-height', '415px'), 
		{'autoHideScrollbar': true});
	},

	uploadTo : function(service,parentId,relatedModule) {
		this.setFile(false);
		this.showUploadToVtigerModal(parentId,relatedModule);
	},

	registerFileSelectionHandler : function(container) {
		jQuery('.file', container).on('click',function() {
			if(typeof prevSelection !== 'undefined') {
				prevSelection.removeClass('selectedFile');
			}
			jQuery(this).addClass('selectedFile');
			prevSelection = jQuery(this);
		});
	},

	_createDocument : function(form) {
		var self = this;
		var noteContentElement = form.find('#Documents_editView_fieldName_notecontent_popup');
		if(noteContentElement.length) {
			var noteContent = CKEDITOR.instances.Documents_editView_fieldName_notecontent_popup.getData()
			noteContentElement.val(noteContent);
		}
		var formData = form.serialize();
		app.helper.showProgress();
		app.request.post({'data':formData}).then(function(e,res) {
			app.helper.hideProgress();
			if (e === null) {
				jQuery('.vt-notification').remove();
				app.helper.hideModal();
				app.helper.showSuccessNotification({
					'message' : app.vtranslate('JS_DOCUMENT_CREATED')
				});
				self.reloadList();
				var folderid = form.find('[name="folderid"]').val();
				app.event.trigger('post.documents.save', {'folderid' : folderid});

				//reference create handling
				if (Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
					self.postQuickCreateSave(res);
				}
			} else {
				app.event.trigger('post.save.failed', e);
			}
		});
	},

	registerCreateDocumentEvent : function(container) {
		var self = this;
		jQuery('#js-create-document', container).on('click', function() {
			var form = container.find('form'); 
			if(form.valid()) {
				self._createDocument(form);
			}
		});
	},

	applyEditor : function(element) {
		var cke = new Vtiger_CkEditor_Js();
		cke.loadCkEditor(element, {'height' : 200});
	},

	registerCreateDocumentModalEvents : function(container) {
		container.find('form').vtValidate();
		if(container.find('input[name="type"]').val() === 'W') {
			container.find('.modelContainer').css('width','750px');
			//change id of text area to workaround multiple instances of ckeditor on same element
			this.applyEditor(
				container.find('#Documents_editView_fieldName_notecontent')
				.attr('id','Documents_editView_fieldName_notecontent_popup')
			);
		}
		this.registerCreateDocumentEvent(container);
	},

	createDocument : function(type,parentId,relatedModule) {
		var self = this;
		var url = 'index.php?module=Documents&view=QuickCreateAjax&operation=CreateDocument&type='+type;
		if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
			url += '&relationOperation=true&sourceModule='+relatedModule+'&sourceRecord='+parentId;
		}
		var relationField = jQuery('div.related-tabs').find('li').filter('.active').data('relatedfield');
		if (relationField && parentId) {
			url += '&'+relationField+"="+parentId;
		}
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(resp, {
					'cb' : function(modalContainer) {
						self.registerCreateDocumentModalEvents(modalContainer);
						self.registerQuickCreateEvents(modalContainer);
						self.applyScrollToModal(modalContainer);
					}
				});
			}
		});
	},

	registerQuickCreateEvents : function(container) {
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		vtigerInstance.registerReferenceCreate(container);
		vtigerInstance.registerPostReferenceEvent(container);
		vtigerInstance.referenceModulePopupRegisterEvent(container);
		vtigerInstance.registerClearReferenceSelectionEvent(container);
		vtigerInstance.registerAutoCompleteFields(container);
		app.helper.registerModalDismissWithoutSubmit(container.find('form'));
		var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName('Documents');
		moduleInstance.registerEventForPicklistDependencySetup(container);

		app.event.on('post.documents.save', function(event, data){
			var relatedTabs = jQuery('div.related-tabs');
			if(relatedTabs.length > 0){
				var tabElement = jQuery('div.related-tabs').find('li.active');
				var relatedModuleName = jQuery('.relatedModuleName').val();
				var relatedInstance = new Vtiger_RelatedList_Js(app.getRecordId(), app.getModuleName(), tabElement, relatedModuleName);
				var relatedTab = relatedInstance.selectedRelatedTabElement;
				relatedInstance.updateRelatedRecordsCount(relatedTab.data('relation-id'));
			}
		});
	}

});
