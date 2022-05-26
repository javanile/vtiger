/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Emails_MassEdit_Js",{},{

	init: function () {
		this.preloadData = new Array();
	}, 

	ckEditorInstance : false,
	massEmailForm : false,
	saved : "SAVED",
	sent : "SENT",
	attachmentsFileSize : 0,
	documentsFileSize : 0,

	getPreloadData : function() {
		return this.preloadData;
	},

	setPreloadData : function(dataInfo){
		this.preloadData = dataInfo;
		return this;
	},

	/*
	 * Function to get the Mass Email Form
	 */
	getMassEmailForm : function(){
		if(this.massEmailForm == false){
			this.massEmailForm = jQuery("#massEmailForm");
		}
		return this.massEmailForm;
	},

	/**
	 * Function to get ckEditorInstance
	 */
	getckEditorInstance : function(){
		if(this.ckEditorInstance == false){
			this.ckEditorInstance = new Vtiger_CkEditor_Js();
		}
		return this.ckEditorInstance;
	},

	/**
	 * function to display the email form
	 * return UI
	 */
	showComposeEmailForm : function(params){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		app.request.post({data:params}).then(function(err,data){
			app.helper.hideProgress();
			if(err == null) {
				var modalContainer = app.helper.showModal(data, {cb: function(){
					 thisInstance.registerEvents();
				}});
				return aDeferred.resolve(modalContainer);
			}
		});
		return aDeferred.promise();
	},

	/**
	 * function to call the registerevents of send Email step1
	 */
	registerEmailFieldSelectionEvent : function(callBack){
		var thisInstance = this;
		var selectEmailForm = jQuery("#SendEmailFormStep1");
		selectEmailForm.on('submit',function(e){
			e.preventDefault();
			var form = jQuery(e.currentTarget);

			var checkedEmails = form.find('.emailField:checked').length;
			if (checkedEmails < 1) {
				app.helper.showErrorNotification({message: app.vtranslate("JS_PLEASE_SELECT_ATLEAST_ONE_OPTION")});
				return false;
			}

			//added check to handle save recipient email preferences
			var saveRecipientPref = form.find('#saveRecipientPrefs').is(':checked');
			if (saveRecipientPref) {
				var selectedFieldEles = form.find('.emailField').filter(':checked');
				var selectedFields = new Array();
				jQuery.each(selectedFieldEles, function (i, ele) {
					selectedFields.push(JSON.parse(jQuery(ele).val()));
				});

				var params = {
					module: app.getModuleName(),
					action: "RecipientPreferencesSaveAjax",
					selectedFields: selectedFields,
					source_module: form.find('input[name="source_module"]').val()
				};
				app.request.post({"data":params});
			}

			var params = form.serialize();
			app.helper.showProgress();
			app.helper.hideModal().then(function(e){
				thisInstance.showComposeEmailForm(params).then(function(response){
					if (typeof callBack === 'function') {
						callBack(response);
					}
				});
			});
		});
	},

	 registerPreventFormSubmitEvent : function(){
		var form = this.getMassEmailForm();
		form.on('submit',function(e){
			e.preventDefault();
		}).on('keypress',function(e){
			if(e.which == 13){
				e.preventDefault();
			}
		});
	},

	/**
	 * Function to register select Email Template click event
	 * @returns {undefined} 
	 */
	registerSelectEmailTemplateEvent : function(){
		var thisInstance = this;
		jQuery("#selectEmailTemplate").on("click",function(e){
			var url = "index.php?"+jQuery(e.currentTarget).data('url');
			var postParams = app.convertUrlToDataParams(url);
			app.request.post({data:postParams}).then(function(err,data){
				if(err === null){
					jQuery('.popupModal').remove();
					var ele = jQuery('<div class="modal popupModal"></div>');
					ele.append(data);
					jQuery('body').append(ele);

					thisInstance.showpopupModal();
					app.event.trigger("post.Popup.Load",{"eventToTrigger":"post.EmailTemplateList.click"})
				}
			});
		});
	},

	showpopupModal : function(){
		var thisInstance = this;
		vtUtils.applyFieldElementsView(jQuery('.popupModal'));
		jQuery('.popupModal').modal();
		jQuery('.popupModal').on('shown.bs.modal', function() {
			jQuery('.myModal').css('opacity', .5);
			jQuery('.myModal').unbind();
		});

		jQuery('.popupModal').on('hidden.bs.modal', function() {
			this.remove();
			jQuery('.myModal').css('opacity', 1);
			jQuery('.myModal').removeData("modal").modal(app.helper.defaultModalParams());
			jQuery('.myModal').bind();
		});
	},

	registerSaveDraftOrSendEmailEvent : function(){
		var thisInstance = this;
		var form = this.getMassEmailForm();
		form.on('click','#sendEmail, #saveDraft',function(e){
			var targetName = jQuery(e.currentTarget).attr('name');
			if(targetName === 'savedraft'){
				jQuery('#flag').val(thisInstance.saved);
			} else {
				jQuery('#flag').val(thisInstance.sent);
			}
			var params = {
				submitHandler: function(form) {
					form = jQuery(form);
					app.helper.hideModal();
					app.helper.showProgress();
					if (CKEDITOR.instances['description']) {
						form.find('#description').val(CKEDITOR.instances['description'].getData());
					}

					var data = new FormData(form[0]);
					var postParams = {
						data:data,
						// jQuery will set contentType = multipart/form-data based on data we are sending
						contentType:false,
						// we don’t want jQuery trying to transform file data into a huge query string, we want raw data to be sent to server
						processData:false 
					};
					app.request.post(postParams).then(function(err,data){
						app.helper.hideProgress();
						var ele = jQuery(data);
						var success = ele.find('.mailSentSuccessfully');
						if(success.length <= 0){
							app.helper.showModal(data);
						} else {
							app.event.trigger('post.mail.sent',data);
						}
					});
				}
			};
			form.vtValidate(params);
		});
	},

	/*
	 * Function to register the events for bcc and cc links
	 */
	registerCcAndBccEvents : function(){
		var thisInstance = this;
		jQuery('#ccLink').on('click',function(e){
			jQuery('.ccContainer').removeClass("hide");
			jQuery(e.currentTarget).hide();
		});
		jQuery('#bccLink').on('click',function(e){
			jQuery('.bccContainer').removeClass("hide");
			jQuery(e.currentTarget).hide();
		});
	},

	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields : function(container) {
		var thisInstance = this;
		var lastResults = [];
		container.find('#emailField').select2({
			minimumInputLength: 3,
			closeOnSelect : false,

			tags : [],
			tokenSeparators: [","],

			ajax : {
				'url' : 'index.php?module=Emails&action=BasicAjax',
				'dataType' : 'json',
				'data' : function(term,page){
					 var data = {};
					 data['searchValue'] = term;
					 return data;
				},
				'results' : function(data){
					var finalResult = [];
					var results = data.result;
					var resultData = new Array();
					for(var moduleName in results) {
						var moduleResult = [];
						moduleResult.text = moduleName;

						var children = new Array();
						for(var recordId in data.result[moduleName]) {
							var emailInfo = data.result[moduleName][recordId];
							for (var i in emailInfo) {
								var childrenInfo = [];
								childrenInfo.recordId = recordId;
								childrenInfo.id = emailInfo[i].value;
								childrenInfo.text = emailInfo[i].label;
								children.push(childrenInfo);
							}
						}
						moduleResult.children = children;
						resultData.push(moduleResult);
					}
					finalResult.results = resultData;
					lastResults = resultData;
					return finalResult;
				},
				transport : function(params) {
					return jQuery.ajax(params);
				}
			},
			createSearchChoice : function(term) {
				//checking for results if there is any if not creating as value
				if(lastResults.length == 0) {
					return { id: term, text: term };
				}
			},
			escapeMarkup: function(m) {
				// Do not escape HTML in the select options text
				return m;
			},

		}).on("change", function (selectedData) {
			var addedElement = selectedData.added;
			if (typeof addedElement != 'undefined') {
				var data = {
					'id' : addedElement.recordId,
					'name' : addedElement.text,
					'emailid' : addedElement.id
				}
				thisInstance.addToEmails(data);
				if (typeof addedElement.recordId != 'undefined') {
					thisInstance.addToEmailAddressData(data);
					thisInstance.appendToSelectedIds(addedElement.recordId);
				}

				var preloadData = thisInstance.getPreloadData();
				var emailInfo = {
					'id' : addedElement.id
				}
				if (typeof addedElement.recordId != 'undefined') {
					emailInfo['text'] = addedElement.text;
					emailInfo['recordId'] = addedElement.recordId;
				} else {
					emailInfo['text'] = addedElement.id;
				}
				preloadData.push(emailInfo);
				thisInstance.setPreloadData(preloadData);
			}

			var removedElement = selectedData.removed;
			if (typeof removedElement != 'undefined') {
				var data = {
					'id' : removedElement.recordId,
					'name' : removedElement.text,
					'emailid' : removedElement.id
				}
				thisInstance.removeFromEmails(data);
				if (typeof removedElement.recordId != 'undefined') {
					thisInstance.removeFromSelectedIds(removedElement.recordId);
					thisInstance.removeFromEmailAddressData(data);
				}

				var preloadData = thisInstance.getPreloadData();
				var updatedPreloadData = [];
				for(var i in preloadData) {
					var preloadDataInfo = preloadData[i];
					var skip = false;
					if (removedElement.id == preloadDataInfo.id) {
						skip = true;
					}
					if (skip == false) {
						updatedPreloadData.push(preloadDataInfo);
					}
				}
				thisInstance.setPreloadData(updatedPreloadData);
			}
		});

		container.find('#emailField').select2("container").find("ul.select2-choices").sortable({
			containment: 'parent',
			start: function(){
				container.find('#emailField').select2("onSortStart");
			},
			update: function(){
				container.find('#emailField').select2("onSortEnd");
			}
		});

		var toEmailNamesList = JSON.parse(container.find('[name="toMailNamesList"]').val());
		var toEmailInfo = JSON.parse(container.find('[name="toemailinfo"]').val());
		var toEmails = container.find('[name="toEmail"]').val();
		var toFieldValues = Array();
		if (toEmails.length > 0) {
			toFieldValues = toEmails.split(',');
		}

		var preloadData = thisInstance.getPreloadData();
		if (typeof toEmailInfo != 'undefined') {
			for(var key in toEmailInfo) {
				if (toEmailNamesList.hasOwnProperty(key)) {
					for (var i in toEmailNamesList[key]) {
						var emailInfo = [];
						var emailId = toEmailNamesList[key][i].value;
						var emailInfo = {
							'recordId' : key,
							'id' : emailId,
							'text' : toEmailNamesList[key][i].label+' <b>('+emailId+')</b>'
						}
						preloadData.push(emailInfo);
						if (jQuery.inArray(emailId, toFieldValues) != -1) {
							var index = toFieldValues.indexOf(emailId);
							if (index !== -1) {
								toFieldValues.splice(index, 1);
							}
						}
					}
				}
			}
		}
		if (typeof toFieldValues != 'undefined') {
			for(var i in toFieldValues) {
				var emailId = toFieldValues[i];
				var emailInfo = {
					'id' : emailId,
					'text' : emailId
				}
				preloadData.push(emailInfo);
			}
		}
		if (typeof preloadData != 'undefined') {
			thisInstance.setPreloadData(preloadData);
			container.find('#emailField').select2('data', preloadData);
		}

	},

	removeFromEmailAddressData : function(mailInfo) {
		var mailInfoElement = this.getMassEmailForm().find('[name="toemailinfo"]');
		var previousValue = JSON.parse(mailInfoElement.val());
		var elementSize = previousValue[mailInfo.id].length;
		var emailAddress = mailInfo.emailid;
		var selectedId = mailInfo.id;
		//If element length is not more than two delete existing record.
		if(elementSize < 2){
			delete previousValue[selectedId];
		} else {
			// Update toemailinfo hidden element value
			var newValue;
			var reserveValue = previousValue[selectedId];
			delete previousValue[selectedId];
			//Remove value from an array and return the resultant array
			newValue = jQuery.grep(reserveValue, function(value) {
				return value != emailAddress;
			});
			previousValue[selectedId] = newValue;
			//update toemailnameslist hidden element value
		}
		mailInfoElement.val(JSON.stringify(previousValue));
	},

	removeFromSelectedIds : function(selectedId) {
		var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
		var previousValue = JSON.parse(selectedIdElement.val());
		var mailInfoElement = this.getMassEmailForm().find('[name="toemailinfo"]');
		var mailAddress = JSON.parse(mailInfoElement.val());
		var elements = mailAddress[selectedId];
		var noOfEmailAddress = elements.length; 

		//Don't remove id from selected_ids if element is having more than two email id's
		if(noOfEmailAddress < 2){
			var updatedValue = [];
			for (var i in previousValue) {
				var id = previousValue[i];
				var skip = false;
				if (id == selectedId) {
					skip = true;
				}
				if (skip == false) {
					updatedValue.push(id);
				}
			}
			selectedIdElement.val(JSON.stringify(updatedValue));
		}
	},

	removeFromEmails : function(mailInfo){
		var toEmails = this.getMassEmailForm().find('[name="to"]');
		var previousValue = JSON.parse(toEmails.val());

		var updatedValue = [];
		for (var i in previousValue) {
			var email = previousValue[i];
			var skip = false;
			if (email == mailInfo.emailid) {
				skip = true;
			}
			if (skip == false) {
				updatedValue.push(email);
			}
		}
		toEmails.val(JSON.stringify(updatedValue));
	},

	addToEmails : function(mailInfo){
		var toEmails = this.getMassEmailForm().find('[name="to"]');
		var value = JSON.parse(toEmails.val());
		if(value == ""){
			value = new Array();
		}
		value.push(mailInfo.emailid);
		toEmails.val(JSON.stringify(value));
	},

	addToEmailAddressData : function(mailInfo) {
		var mailInfoElement = this.getMassEmailForm().find('[name="toemailinfo"]');
		var existingToMailInfo = JSON.parse(mailInfoElement.val());
		 if(typeof existingToMailInfo.length != 'undefined') {
			existingToMailInfo = {};
		} 
		//If same record having two different email id's then it should be appended to
		//existing email id
		 if(existingToMailInfo.hasOwnProperty(mailInfo.id) === true){
			var existingValues = existingToMailInfo[mailInfo.id];
			var newValue = new Array(mailInfo.emailid);
			existingToMailInfo[mailInfo.id] = jQuery.merge(existingValues,newValue);
		} else {
			existingToMailInfo[mailInfo.id] = new Array(mailInfo.emailid);
		}
		mailInfoElement.val(JSON.stringify(existingToMailInfo));
	},

	appendToSelectedIds : function(selectedId) {
		var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
		var previousValue = '';
		if(JSON.parse(selectedIdElement.val()) != '') {
			previousValue = JSON.parse(selectedIdElement.val());
			//If value doesn't exist then insert into an array
			if(jQuery.inArray(selectedId,previousValue) === -1){
				previousValue.push(selectedId);
			}
		} else {
			previousValue = new Array(selectedId);
		}
		selectedIdElement.val(JSON.stringify(previousValue));

	},

	checkHiddenStatusofCcandBcc : function(){
		var ccLink = jQuery('#ccLink');
		var bccLink = jQuery('#bccLink');
		if(ccLink.is(':hidden') && bccLink.is(':hidden')){
			ccLink.closest('div.row').addClass('hide');
		}
	},

	loadCkEditor : function(textAreaElement){
		var ckEditorInstance = this.getckEditorInstance();
		ckEditorInstance.loadCkEditor(textAreaElement);
	},

	setAttachmentsFileSizeByElement : function(element){
		this.attachmentsFileSize += element.get(0).files[0].size;
	},

	setAttachmentsFileSizeBySize : function(fileSize){
		this.attachmentsFileSize += parseFloat(fileSize);
	},

	getAttachmentsFileSize : function(){
		return this.attachmentsFileSize;
	},
	setDocumentsFileSize : function(documentSize){
		this.documentsFileSize += parseFloat(documentSize);
	},
	getDocumentsFileSize : function(){
		return this.documentsFileSize;
	},

	getTotalAttachmentsSize : function(){
		return parseFloat(this.getAttachmentsFileSize())+parseFloat(this.getDocumentsFileSize());
	},

	getMaxUploadSize : function(){
		return jQuery('#maxUploadSize').val();
	},

	removeAttachmentFileSizeByElement : function(element) {
		this.attachmentsFileSize -= element.get(0).files[0].size;
	},

	removeDocumentsFileSize : function(documentSize){
		this.documentsFileSize -= parseFloat(documentSize);
	},

	removeAttachmentFileSizeBySize : function(fileSize) {
		this.attachmentsFileSize -= parseFloat(fileSize);
	},

	/**
	 * Function to calculate upload file size
	 */
	calculateUploadFileSize : function(){
		var thisInstance = this;
		var composeEmailForm = this.getMassEmailForm();
		var attachmentsList = composeEmailForm.find('#attachments');
		var attachments = attachmentsList.find('.customAttachment');
		jQuery.each(attachments,function(){
			var element = jQuery(this);
			var fileSize = element.data('fileSize');
			var fileType = element.data('fileType');
			if(fileType == "file"){
				thisInstance.setAttachmentsFileSizeBySize(fileSize);
			} else if(fileType == "document"){
				thisInstance.setDocumentsFileSize(fileSize);
			}
		})
	},

	 setReferenceFieldValue : function(container,object){
		var thisInstance = this;
		var preloadData = thisInstance.getPreloadData();

		var emailInfo = {
			'recordId' : object.id,
			'id' : object.emailid,
			'text' : object.name+' <b>('+object.emailid+')</b>'
		}
		preloadData.push(emailInfo);
		thisInstance.setPreloadData(preloadData);
		container.find('#emailField').select2('data', preloadData);

		var toEmailField = container.find('.sourceField');
		var toEmailFieldExistingValue = toEmailField.val();
		var toEmailFieldNewValue;
		if(toEmailFieldExistingValue != ""){
			toEmailFieldNewValue = toEmailFieldExistingValue+","+object.emailid;
		} else {
			toEmailFieldNewValue = object.emailid;
		}
		toEmailField.val(toEmailFieldNewValue);
	},

	fileAfterSelectHandler : function(element, value, master_element){
		var thisInstance = this;
		var mode = jQuery('[name="emailMode"]').val();
		var existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());
		element = jQuery(element);
		thisInstance.setAttachmentsFileSizeByElement(element);
		var totalAttachmentsSize = thisInstance.getTotalAttachmentsSize();
		var maxUploadSize = thisInstance.getMaxUploadSize();
		if(totalAttachmentsSize > maxUploadSize){
			app.helper.showAlertBox({message:app.vtranslate('JS_MAX_FILE_UPLOAD_EXCEEDS')});
			this.removeAttachmentFileSizeByElement(jQuery(element));
			master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
		}else if((mode != "") && (existingAttachment != "")){
			var pattern = /\\/;
			var fileuploaded = value;
			jQuery.each(existingAttachment,function(key,value){
				if((value['attachment'] == fileuploaded) && !(value.hasOwnProperty( "docid"))){
					var errorMsg = app.vtranslate("JS_THIS_FILE_HAS_ALREADY_BEEN_SELECTED")+fileuploaded;
					app.helper.showAlertBox({message:app.vtranslate(errorMsg)});
					thisInstance.removeAttachmentFileSizeByElement(jQuery(element),value);
					master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
					return false;
				}
			})
		}
		return true;
	},

	 registerEventsForToField: function() {
		 var thisInstance = this;
		this.getMassEmailForm().on('click','.selectEmail',function(e){
			var moduleSelected = jQuery('.emailModulesList').select2('val');
			var parentElem = jQuery(e.target).closest('.toEmailField');
			var sourceModule = jQuery('[name=module]').val();
			var params = {
				'module' : moduleSelected,
				'src_module' : sourceModule,
				'view': 'EmailsRelatedModulePopup'
			}
			var popupInstance =Vtiger_Popup_Js.getInstance();
			popupInstance.showPopup(params, function(data){
					var responseData = JSON.parse(data);

					for(var id in responseData){
						var data = {
							'name' : responseData[id].name,
							'id' : id,
							'emailid' : responseData[id].email
						}
						thisInstance.setReferenceFieldValue(parentElem, data);
						thisInstance.addToEmailAddressData(data);
						thisInstance.appendToSelectedIds(id);
						thisInstance.addToEmails(data);
					}
				},'relatedEmailModules');
		});

		this.getMassEmailForm().on('click','[name="clearToEmailField"]',function(e){
			var element = jQuery(e.currentTarget);
			element.closest('div.toEmailField').find('.sourceField').val('');
			thisInstance.getMassEmailForm().find('[name="toemailinfo"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="selected_ids"]').val(JSON.stringify(new Array()));
			thisInstance.getMassEmailForm().find('[name="to"]').val(JSON.stringify(new Array()));

			var preloadData = [];
			thisInstance.setPreloadData(preloadData);
			thisInstance.getMassEmailForm().find('#emailField').select2('data', preloadData);
		});

	 },

	registerBrowseCrmEvent : function(){
		var thisInstance = this;
		jQuery('#browseCrm').on('click',function(e){
			var url = jQuery(e.currentTarget).data('url');
			var postParams = app.convertUrlToDataParams("index.php?"+url);

			app.request.post({"data":postParams}).then(function(err,data){
				jQuery('.popupModal').remove();
				var ele = jQuery('<div class="modal popupModal"></div>');
				ele.append(data);
				jQuery('body').append(ele);
				thisInstance.showpopupModal();
				app.event.trigger("post.Popup.Load",{"eventToTrigger":"post.DocumentsList.click"});
			});
		});
	},


	getDocumentAttachmentElement : function(selectedFileName,id,selectedFileSize){
		return '<div class="MultiFile-label"><a href="#" class="removeAttachment cursorPointer" data-id='+id+' data-file-size='+selectedFileSize+'>x </a><span>'+selectedFileName+'</span></div>';
	},

	/**
	 * Function to check whether selected document 
	 * is already an existing attachment
	 * @param expects document id to check
	 * @return true if present false if not present
	 */
	checkIfExisitingAttachment : function(selectedDocumentId){
		var documentExist;
		var documentPresent;
		var mode = jQuery('[name="emailMode"]').val();
		var selectedDocumentIds = jQuery('#documentIds').val();
		var existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());
		if((mode != "") && (existingAttachment != "")){
			jQuery.each(existingAttachment,function(key,value){
				if(value.hasOwnProperty( "docid")){
					if(value['docid'] == selectedDocumentId){
						documentExist = 1;
						return false;
					} 
				}
			})
			if(selectedDocumentIds != ""){
				selectedDocumentIds = JSON.parse(selectedDocumentIds);
			}
			if((documentExist == 1) || (jQuery.inArray(selectedDocumentId,selectedDocumentIds) != '-1')){
				documentPresent = 1;
			} else {
				documentPresent = 0;
			}
		} else if(selectedDocumentIds != ""){
			selectedDocumentIds = JSON.parse(selectedDocumentIds);
			if((jQuery.inArray(selectedDocumentId,selectedDocumentIds) != '-1')){
				documentPresent = 1;
			} else {
				documentPresent = 0;
			}
		}
		if(documentPresent == 1){
			var errorMsg = app.vtranslate("JS_THIS_DOCUMENT_HAS_ALREADY_BEEN_SELECTED");
			app.helper.showErrorNotification({message: errorMsg});
			return true;
		} else {
			return false;
		}
	},

	writeDocumentIds :function(selectedDocumentId){
		var thisInstance = this;
		var newAttachment;
		var selectedDocumentIds = jQuery('#documentIds').val();
		if(selectedDocumentIds != ""){
			selectedDocumentIds = JSON.parse(selectedDocumentIds);
			var existingAttachment = thisInstance.checkIfExisitingAttachment(selectedDocumentId);
			if(!existingAttachment){
				newAttachment = 1;
			} else {
				newAttachment = 0;
			}
		} else {
			var existingAttachment = thisInstance.checkIfExisitingAttachment(selectedDocumentId);
			if(!existingAttachment){
				newAttachment = 1;
				var selectedDocumentIds = new Array();
			}
		}
		if(newAttachment == 1){
			selectedDocumentIds.push(selectedDocumentId);
			jQuery('#documentIds').val(JSON.stringify(selectedDocumentIds));
			return true;
		} else {
			return false;
		}
	},

	removeDocumentIds : function(removedDocumentId){
		var documentIdsContainer = jQuery('#documentIds');
		var documentIdsArray = JSON.parse(documentIdsContainer.val());
		documentIdsArray.splice( jQuery.inArray('"'+removedDocumentId+'"', documentIdsArray), 1 );
		documentIdsContainer.val(JSON.stringify(documentIdsArray));
	},

	registerRemoveAttachmentEvent : function(){
		var thisInstance = this;
		this.getMassEmailForm().on('click','.removeAttachment',function(e){
			var currentTarget = jQuery(e.currentTarget);
			var id = currentTarget.data('id');
			var fileSize = currentTarget.data('fileSize');
			currentTarget.closest('.MultiFile-label').remove();
			thisInstance.removeDocumentsFileSize(fileSize);
			thisInstance.removeDocumentIds(id);
			if (jQuery('#attachments').is(':empty')){
				jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
			}
		});
	},

	registerEventForRemoveCustomAttachments : function() {
		var thisInstance = this;
		var composeEmailForm = this.getMassEmailForm();
		jQuery('[name="removeAttachment"]').on('click',function(e){
			var attachmentsContainer = composeEmailForm.find('[ name="attachments"]');
			var attachmentsInfo = JSON.parse(attachmentsContainer.val());
			var element = jQuery(e.currentTarget);
			var imageContainer = element.closest('div.MultiFile-label');
			var imageContainerData = imageContainer.data();
			var fileType = imageContainerData['fileType'];
			var fileSize = imageContainerData['fileSize'];
			var fileId = imageContainerData['fileId'];
			if(fileType == "document"){
				thisInstance.removeDocumentsFileSize(fileSize);
			} else if(fileType == "file"){
				thisInstance.removeAttachmentFileSizeBySize(fileSize);
			}
			jQuery.each(attachmentsInfo,function(index,attachmentObject){
				if((typeof attachmentObject != "undefined") && (attachmentObject.fileid == fileId)){
					attachmentsInfo.splice(index,1);
				}
			});
			attachmentsContainer.val(JSON.stringify(attachmentsInfo));
			imageContainer.remove();
		});
	},

	registerEvents : function(){
		var thisInstance = this;
		var container = this.getMassEmailForm();
		if(container.length > 0){
			this.registerPreventFormSubmitEvent();
			this.registerCcAndBccEvents();
			this.registerAutoCompleteFields(container);
			jQuery("#multiFile").MultiFile({
				list: '#attachments',
				'afterFileSelect' : function(element, value, master_element){
					var masterElement = master_element;
					var newElement = jQuery(masterElement.current);
					newElement.addClass('removeNoFileChosen');
					thisInstance.fileAfterSelectHandler(element, value, master_element);
				},
				'afterFileRemove' : function(element, value, master_element){
					if (jQuery('#attachments').is(':empty')){
						jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
					}
					thisInstance.removeAttachmentFileSizeByElement(jQuery(element));
				}
			});
			this.registerRemoveAttachmentEvent();
			this.registerBrowseCrmEvent();
			this.calculateUploadFileSize();
			this.registerSaveDraftOrSendEmailEvent();
			var isCkeditorApplied = jQuery('#description').data('isCkeditorApplied');
			if(isCkeditorApplied != true && jQuery('#description').length > 0){
				this.loadCkEditor(jQuery('#description').data('isCkeditorApplied',true));
			}
			this.registerSelectEmailTemplateEvent();
			this.registerEventsForToField();
			this.registerEventForRemoveCustomAttachments();

			app.event.on("post.DocumentsList.click",function(event, data){
				var responseData = JSON.parse(data);
				jQuery('.popupModal').modal('hide');
				for(var id in responseData){
					selectedDocumentId = id;
					var selectedFileName = responseData[id].info['filename'];
					var selectedFileSize = responseData[id].info['filesize'];
					var response = thisInstance.writeDocumentIds(selectedDocumentId)
					if(response){
						var attachmentElement = thisInstance.getDocumentAttachmentElement(selectedFileName,id,selectedFileSize);
						//TODO handle the validation if the size exceeds 5mb before appending.
						jQuery(attachmentElement).appendTo(jQuery('#attachments'));
						jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
						thisInstance.setDocumentsFileSize(selectedFileSize);
					}
				}
			});

			jQuery('#emailTemplateWarning .alert-warning .close').click(function(e){
				e.preventDefault();
				e.stopPropagation();
				jQuery('#emailTemplateWarning').addClass('hide');
			});

			app.event.on("post.EmailTemplateList.click",function(event, data){
				var responseData = JSON.parse(data);
				jQuery('.popupModal').modal('hide');

				var ckEditorInstance = thisInstance.getckEditorInstance();

				for(var id in responseData){
					var data = responseData[id];
					ckEditorInstance.loadContentsInCkeditor(data['info']);
					//fill subject
					jQuery('#subject').val(data['name']);
					var selectedTemplateBody = responseData[id].info;
				}
				var sourceModule = jQuery('[name=source_module]').val();
				var tokenDataPair = selectedTemplateBody.split('$');
				var showWarning = false;
				for (var i=0; i<tokenDataPair.length; i++) {
					var module = tokenDataPair[i].split('-');
					var pattern = /^[A-z]+$/;
					if(pattern.test(module[0])) {
						if(!(module[0] == sourceModule.toLowerCase() || module[0] == 'users' || module[0] == 'custom')) {
							showWarning = true;
						}
					}
				}
				if(showWarning) {
					jQuery('#emailTemplateWarning').removeClass('hide');
				} else {
					jQuery('#emailTemplateWarning').addClass('hide');
				}
			});
			var params = {
				setHeight:(jQuery(window).height() - container.find('.modal-header').height() - container.find('.modal-footer').height() - 100)+'px'
			};
			app.helper.showVerticalScroll(container.find('.modal-body'), params);
		}
	}
});


