/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Documents_Edit_Js", {
    file : false
} ,{

	INTERNAL_FILE_LOCATION_TYPE : 'I',
	EXTERNAL_FILE_LOCATION_TYPE : 'E',
    
	getMaxiumFileUploadingSize : function(container) {
		//TODO : get it from the server
		return container.find('.maxUploadSize').data('value');
	},

	isFileLocationInternalType : function(fileLocationElement) {
		if(fileLocationElement.val() == this.INTERNAL_FILE_LOCATION_TYPE) {
			return true;
		}
		return false;
	},

	isFileLocationExternalType : function(fileLocationElement) {
		if(fileLocationElement.val() == this.EXTERNAL_FILE_LOCATION_TYPE) {
			return true;
		}
		return false;
	},

	convertFileSizeInToDisplayFormat : function(fileSizeInBytes) {
		 var i = -1;
		var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
		do {
			fileSizeInBytes = fileSizeInBytes / 1024;
			i++;
		} while (fileSizeInBytes > 1024);

		return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];

	},

	registerFileLocationTypeChangeEvent : function(container) {
		var thisInstance = this;
		container.on('change', 'select[name="filelocationtype"],input[name="filelocationtype"]', function(e){
                    var fileLocationTypeElement = container.find('select[name="filelocationtype"]');
            var fileNameElement = container.find('[name="filename"]');
            jQuery(fileNameElement).removeClass('input-error').trigger('Vtiger.Validation.Hide.Messsage');
            var dragDropElement = fileNameElement.closest('table').find('#dragandrophandler');
            var replaceElement = fileNameElement;
			if(thisInstance.isFileLocationInternalType(fileLocationTypeElement)) {
				var newFileNameElement = jQuery(
                    '<div class="fileUploadBtn btn btn-primary">'+
                        '<span><i class="fa fa-laptop"></i>&nbsp;'+
                            app.vtranslate("JS_UPLOAD")+
                        '</span>'+
                        '<input type="file"/>'+
                    '</div>'
                );
                if(dragDropElement.length) dragDropElement.removeClass('hide');
			}else{
				var newFileNameElement = jQuery('<input type="text" data-rule-url="true" data-rule-required="true"/>');
                if(dragDropElement.length) dragDropElement.addClass('hide');
                replaceElement = fileNameElement.closest('.fileUploadBtn');
			}
			var oldElementAttributeList = fileNameElement.get(0).attributes;
			
			for(var index=0; index<oldElementAttributeList.length; index++) {
				var attributeObject = oldElementAttributeList[index];
				//Dont update the type attribute
				if(attributeObject.name=='type' || attributeObject.name == 'value' || attributeObject.name == 'style'){
					continue;
				}
                if(newFileNameElement.hasClass('fileUploadBtn')) {
                    newFileNameElement.find('input[type="file"]').attr(attributeObject.name, attributeObject.value);
                } else {
                    newFileNameElement.attr(attributeObject.name, attributeObject.value);
                }
			}
            
			replaceElement.replaceWith(newFileNameElement);
            
			var fileNameElementTd = newFileNameElement.closest('td');
            if(thisInstance.isFileLocationExternalType(fileLocationTypeElement)){
                fileNameElementTd.prev('td.fieldLabel').empty().append('<label class="">'+app.vtranslate('JS_EXTERNAL_FILE_URL')+'&nbsp;'+'<span class="redColor">*</span></label>');
            } else {
                fileNameElementTd.prev('td.fieldLabel').empty().append('<label class="">'+app.vtranslate('JS_FILE_NAME')+'&nbsp;</label>');
            }
            
			var uploadFileDetails = fileNameElementTd.find('.uploadedFileDetails');
			if(thisInstance.isFileLocationExternalType(fileLocationTypeElement)) {
				uploadFileDetails.addClass('hide').removeClass('show');
			}else{
				uploadFileDetails.addClass('show').removeClass('hide');
			}
		});
        if(container.find('input[name="filelocationtype"]').val() == 'E'){
            jQuery('select[name="filelocationtype"],input[name="filelocationtype"]').trigger('change');
        }
	},

	registerFileElementChangeEvent : function(container) {
		var thisInstance = this;
		container.on('change', 'input[name="filename"]', function(e){
            vtUtils.hideValidationMessage(container.find('input[name="filename"]'));
            if(e.target.type == "text") return false;
            Documents_Edit_Js.file = e.target.files[0];
            var element = container.find('[name="filename"]');
			//ignore all other types than file 
			if(element.attr('type') != 'file'){
				return ;
			}
			var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
			var fileSize = e.target.files[0].size;
            var fileName = e.target.files[0].name;
			var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
			if(fileSize > maxFileSize) {
				alert(app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE'));
				element.val('');
				uploadFileSizeHolder.text('');
			}else{
                if(container.length > 1){
                    jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width','80px');
                    jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width','80px');
                } else {
                    container.find('input[name="filename"]').css('width','80px');
                }
				uploadFileSizeHolder.text(fileName+' '+thisInstance.convertFileSizeInToDisplayFormat(fileSize));
			}

		});
	},
	/**
	 * Function to register event for ckeditor for description field
	 */
	registerEventForCkEditor : function(container){
		var form = this.getForm();
        if(typeof container != 'undefined'){
            form = container;
        }
		var noteContentElement = form.find('[name="notecontent"]');
		if(noteContentElement.length > 0){
			noteContentElement.removeAttr('data-validation-engine').addClass('ckEditorSource');
			var ckEditorInstance = new Vtiger_CkEditor_Js();
			ckEditorInstance.loadCkEditor(noteContentElement);
		}
	},
    
    quickCreatePreSave : function(form) {
        var textAreaElement = form.find('textarea[name="notecontent"]');
        if(textAreaElement.length){
            var notecontent = CKEDITOR.instances.Documents_editView_fieldName_notecontent.getData();
            textAreaElement.val(notecontent);
        }
    },
    
    /**
     * Function to save the quickcreate module
     * @param accepts form element as parameter
     * @return returns deferred promise
     */
    quickCreateSave: function(forms) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        forms.each(function(index,domEle){
            var form = jQuery(domEle);
            var params = {};
            params.submitHandler = function(domForm){
                var form = jQuery(domForm);
                thisInstance.quickCreatePreSave(form);
                var contentsContainer = form;
                //To handle tabs in quickcreate
                if(form.find('div.active').length){
                    contentsContainer = form.find('div.active');
                }
                //Using formData object to send data to server as a multipart/form-data form submit 
                var formData = new FormData(form[0]);
                var fileLocationTypeElement = form.find('[name="filelocationtype"]');
                if(Documents_Edit_Js.file != false && thisInstance.isFileLocationInternalType(fileLocationTypeElement)){
                    formData.append("filename", Documents_Edit_Js.file);
                    if(!contentsContainer.find('input[name="notes_title"]').length){
                       formData.append('notes_title',Documents_Edit_Js.file.name);
                    }
                    delete Documents_Edit_Js.file;
                } else{
                    if(!contentsContainer.find('input[name="notes_title"]').length){
                       var filename = contentsContainer.find('input[name="filename"]').val();
                       formData.append('notes_title',filename);
                    }
                }

                // to Prevent submit if already submitted
                
                if(this.numberOfInvalids() > 0) {
                    return false;
                }
                var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
                app.event.trigger(e);
                if(e.isDefaultPrevented()) {
                    return false;
                }
                contentsContainer.find("button[name='saveButton']").attr("disabled",true);
                if(formData){
                    var params = {
                            url: "index.php",
                            type: "POST",
                            data: formData,
                            processData: false,
                            contentType: false
                         };

                    app.request.post(params).then(function(err,data){
                        app.event.trigger("post.QuickCreateForm.save",data);
                        if(err === null){
                            app.helper.hideModal();
                            aDeferred.resolve(data);
                        }
                    });
                }
                return false;
            }
            form.vtValidate(params);
        });
        return aDeferred.promise();
    },  
    
    documentsQuickCreateConfig : function(container){
		var form = this.getForm();
        if(form.attr('id') != 'EditView' && container.attr('id') != 'overlayPageContent'){
            var externalDocContentsElement = container.find('#EQuickCreateContent');
            var fileNameElement = externalDocContentsElement.find('[name="filename"]');
			var newFileNameElement = jQuery('<input type="text" data-rule-url="true" data-rule-required="true"/>');
			var oldElementAttributeList = fileNameElement.get(0).attributes;
			for(var index=0; index<oldElementAttributeList.length; index++) {
				var attributeObject = oldElementAttributeList[index];
				//Dont update the type attribute
				if(attributeObject.name=='type' || attributeObject.name == 'value' || attributeObject.name == 'style'){
					continue;
				}
				var value = attributeObject.value
				if(attributeObject.name=='data-fieldinfo') {
					value = JSON.parse(value);
					value['type'] = 'url';
                    value['mandatory'] = true;
					value = JSON.stringify(value);
				}
				newFileNameElement.attr(attributeObject.name, value);
			}
            externalDocContentsElement.find('.fileUploadContainer').replaceWith(newFileNameElement);
			var fileNameElementTd = newFileNameElement.closest('td');
            fileNameElementTd.prev('td.fieldLabel').empty().append('<label class="muted pull-right"><span class="redColor">*</span>'+app.vtranslate('JS_EXTERNAL_FILE_URL')+'</label>');
			var uploadFileDetails = fileNameElementTd.find('.uploadedFileDetails');
				uploadFileDetails.addClass('hide').removeClass('show');

            var webDocContentsElement = container.find('#WQuickCreateContent');
            app.helper.showVerticalScroll(webDocContentsElement, {
                  'setHeight': '450px'
            });
        }
    },
    
    handleDragDropEvents : function(container){
        var thisInstance = this;
        var dragDropElement = container.find("#dragandrophandler");
        dragDropElement.on('dragenter', function (e) {
            e.stopPropagation();
            e.preventDefault();
            jQuery(this).removeClass('dragdrop-dotted');
            jQuery(this).addClass('dragdrop-solid');
        });
        dragDropElement.on('dragover', function (e) {
             e.stopPropagation();
             e.preventDefault();
        });
        jQuery(document).on('dragenter', function (e) {
            e.stopPropagation();
            e.preventDefault();
        });
        jQuery(document).on('dragover', function (e) {
          e.stopPropagation();
          e.preventDefault();
          dragDropElement.removeClass('dragdrop-solid');
          dragDropElement.addClass('dragdrop-dotted');
        });
        jQuery(document).on('drop', function (e) {
            e.stopPropagation();
            e.preventDefault();
        });
        dragDropElement.on('drop', function (e) {
             e.preventDefault();
             jQuery(this).removeClass('dragdrop-solid');
             jQuery(this).addClass('dragdrop-dotted');
             var fileObj = e.originalEvent.dataTransfer.files;
             Documents_Edit_Js.file = fileObj[0];
             var element = container.find('input[name="filename"]');
             element.val(null);
             var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
			var fileSize = Documents_Edit_Js.file.size;
            var fileName = Documents_Edit_Js.file.name;
			var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
			if(fileSize > maxFileSize) {
				alert(app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE'));
				element.val('');
				uploadFileSizeHolder.text('');
			}else{
                //hide the no file chosen msg for internal and webdoc types
                if(container.length > 1){
                    jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width','80px');
                    jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width','80px');
                }
                else{
                    container.find('input[name="filename"]').css('width','80px');
                }
				uploadFileSizeHolder.text(fileName+'  '+thisInstance.convertFileSizeInToDisplayFormat(fileSize));
			}
        });
    },
    
   registerCustomValidationForFileElement : function(container) {
        var thisInstance = this;
        app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function(e, data) {
            var form = container;
            if(container.length > 1)
                form = jQuery('div.fieldsContainer').find('div.active').find('form');
            // file is required only for internal document type
            if(Documents_Edit_Js.file == false && (form.attr('id') == 'I_form' || form.data('type') == 'I')){
                var msg = app.vtranslate('JS_PLEASE_SELECT_A_FILE');
                var params = {};
                params.position = {
	                my: 'bottom left',
	                at: 'top left',
	                container:form
	            };
                vtUtils.showValidationMessage(form.find('input[name="filename"]'),msg,params);
                e.preventDefault();
            }else{
                vtUtils.hideValidationMessage(form.find('input[name="filename"]'));
            }
            
            thisInstance.quickCreatePreSave(form);
        });
   },
    
    registerBasicEvents : function(container) {
        this._super(container);
        this.registerFileLocationTypeChangeEvent(container);
            this.registerFileElementChangeEvent(container);
            this.registerEventForCkEditor(container);
            this.documentsQuickCreateConfig(container);
            this.handleDragDropEvents(container);
            this.registerCustomValidationForFileElement(container);
    },
});


