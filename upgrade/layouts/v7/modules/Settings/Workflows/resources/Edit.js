/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Settings_Vtiger_Edit_Js("Settings_Workflows_Edit_Js", {
}, {
    workFlowsContainer: false,
    advanceFilterInstance: false,
    ckEditorInstance: false,
    fieldValueMap: false,
    workFlowsActionContainer : false,
   
    /**
     * Function to get the container which holds all the workflow elements
     * @return jQuery object
     */
    getContainer: function () {
       return this.workFlowsContainer;
    },
   
    /**
     * Function to get the container which holds all the workflow elements
     * @return jQuery object
     */
    getActionContainer: function () {
       return this.workFlowsActionContainer;
    },
   
    /**
     * Function to set the reports container
     * @params : element - which represents the workflow container
     * @return : current instance
     */
    setContainer: function (element) {
       this.workFlowsContainer = element;
       return this;
    },
   
    /**
    * Function to set the reports step1 container
    * @params : element - which represents the reports step1 container
    * @return : current instance
    */
    setActionContainer : function(element) {
            this.workFlowsActionContainer = element;
            return this;
    },
    
    calculateValues : function(){
        //handled advanced filters saved values.
        var enableFilterElement = jQuery('#enableAdvanceFilters');
        if(enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
                jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
        } else {
                jQuery('[name="filtersavedinnew"]').val("6");
                var advfilterlist = this.advanceFilterInstance.getValues();
                jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
        }
    },
    
    checkExpressionValidation : function(form) {
        var params = {
            'module' : app.module(),
            'parent' : app.getParentModuleName(),
            'action' : 'ValidateExpression',
            'mode' : 'ForWorkflowEdit'
        };
        var serializeForm = form.serializeFormData();
        params = jQuery.extend(serializeForm, params);
        
        app.request.post({'data' : params}).then(function(error, data) {
            if(error == null) {
                form.get(0).submit();
            } else {
                jQuery(form).find('button.saveButton').removeAttr('disabled');
                app.helper.showErrorNotification({'message' : app.vtranslate('LBL_EXPRESSION_INVALID')});
            }
        });
    },
    
    /*
     * Function to register the click event for next button
     */
    registerFormSubmitEvent : function() {
        var self = this;
        var form = jQuery('#workflow_edit');
        var params = {
            submitHandler: function(form) {
                if(jQuery('[name="workflow_trigger"]').val() == '6' && jQuery('#schtypeid').val() == '3') {
                    if(jQuery('#schdayofweek').val().length <= 0) {
                        app.helper.showErrorNotification({'message':'Please Select atleast one value'});
                        return false;
                    }
                }
                var form = jQuery(form);
                self.calculateValues();
				window.onbeforeunload = null;
                jQuery(form).find('button.saveButton').attr('disabled', 'disabled');
                
                self.checkExpressionValidation(form);
                //form.get(0).submit();
                return false;
            }
        };
        form.vtValidate(params);
    },
	

    getPopUp: function (container) {
      var thisInstance = this;
      if (typeof container == 'undefined') {
         container = thisInstance.getContainer();
      }
      var isPopupShowing = false;
      container.on('click', '.getPopupUi', function (e) {
         // Added to prevent multiple clicks event
         if(isPopupShowing) {
             return false;
         }
         var fieldValueElement = jQuery(e.currentTarget);
         var fieldValue = fieldValueElement.val();
         var fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
         var valueType = fieldUiHolder.find('[name="valuetype"]').val();
         if (valueType == '' || valueType == 'null') {
            valueType = 'rawtext';
         }
         var conditionsContainer = fieldValueElement.closest('.conditionsContainer');
         var conditionRow = fieldValueElement.closest('.conditionRow');

         var clonedPopupUi = conditionsContainer.find('.popupUi').clone(true, true).removeClass('hide').removeClass('popupUi').addClass('clonedPopupUi');
         clonedPopupUi.find('select').addClass('select2');
         clonedPopupUi.find('.fieldValue').val(fieldValue);
         clonedPopupUi.find('.fieldValue').removeClass('hide');
         if (fieldValueElement.hasClass('date')) {
            clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
            var dataFormat = fieldValueElement.data('date-format');
            if (valueType == 'rawtext') {
               var value = fieldValueElement.val();
            } else {
               value = '';
            }
            var clonedDateElement = '<input type="text" style="width: 30%;" class="dateField fieldValue inputElement" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >'
            clonedPopupUi.find('.fieldValueContainer div').prepend(clonedDateElement);
         } else if (fieldValueElement.hasClass('time')) {
            clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
            if (valueType == 'rawtext') {
               var value = fieldValueElement.val();
            } else {
               value = '';
            }
            var clonedTimeElement = '<input type="text" style="width: 30%;" class="timepicker-default fieldValue inputElement" value="' + value + '" data-input="true" >'
            clonedPopupUi.find('.fieldValueContainer div').prepend(clonedTimeElement);
         } else if (fieldValueElement.hasClass('boolean')) {
            clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
            if (valueType == 'rawtext') {
               var value = fieldValueElement.val();
            } else {
               value = '';
            }
            var clonedBooleanElement = '<input type="checkbox" style="width: 30%;" class="fieldValue inputElement" value="' + value + '" data-input="true" >';
            clonedPopupUi.find('.fieldValueContainer div').prepend(clonedBooleanElement);

            var fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
            if (value == 'true:boolean' || value == '') {
               clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
            } else {
               clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
            }
         }
         var callBackFunction = function (data) {
            isPopupShowing = false;
            data.find('.clonedPopupUi').removeClass('hide');
            var moduleNameElement = conditionRow.find('[name="modulename"]');
            if (moduleNameElement.length > 0) {
               var moduleName = moduleNameElement.val();
               data.find('.useFieldElement').addClass('hide');
               jQuery(data.find('[name="' + moduleName + '"]').get(0)).removeClass('hide');
            }          
            thisInstance.postShowModalAction(data, valueType);
            thisInstance.registerChangeFieldEvent(data);
            thisInstance.registerSelectOptionEvent(data);
            thisInstance.registerPopUpSaveEvent(data, fieldUiHolder);
            thisInstance.registerRemoveModalEvent(data);
            data.find('.fieldValue').filter(':visible').trigger('focus');
         }
         conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);
         jQuery('.clonedPopupUi').on('shown', function () {
            if (typeof callBackFunction == 'function') {
               callBackFunction(jQuery('.clonedPopupUi', conditionsContainer));
            }
         });
         isPopupShowing = true;
         app.helper.showModal(jQuery('.clonedPopUp', conditionsContainer).find('.clonedPopupUi'), {cb: callBackFunction});
      });
   },
   
   registerRemoveModalEvent: function (data) {
      data.on('click', '.closeModal', function (e) {
         data.modal('hide');
      });
   },
   
   registerPopUpSaveEvent: function (data, fieldUiHolder) {
      jQuery('[name="saveButton"]', data).on('click', function (e) {
         var valueType = jQuery('select.textType', data).val();

         fieldUiHolder.find('[name="valuetype"]').val(valueType);
         var fieldValueElement = fieldUiHolder.find('.getPopupUi');
         if (valueType != 'rawtext') {
            fieldValueElement.addClass('ignore-validation');
         } else {
            fieldValueElement.removeClass('ignore-validation');
         }
         var fieldType = data.find('.fieldValue').filter(':visible').attr('type');
         var fieldValue = data.find('.fieldValue').filter(':visible').val();
         //For checkbox field type, handling fieldValue
         if (fieldType == 'checkbox') {
            if (data.find('.fieldValue').filter(':visible').is(':checked')) {
               fieldValue = 'true:boolean';
            } else {
               fieldValue = 'false:boolean';
            }
         }
         fieldValueElement.val(fieldValue);
         data.modal('hide');
      });
   },
   
   registerSelectOptionEvent: function (data) {
      jQuery('.useField,.useFunction', data).on('change', function (e) {
         var currentElement = jQuery(e.currentTarget);
         var newValue = currentElement.val();
         var oldValue = data.find('.fieldValue').filter(':visible').val();
         var textType = currentElement.closest('.clonedPopupUi').find('select.textType').val();
         if (currentElement.hasClass('useField')) {
            //If it is fieldname mode then we need to allow only one field
            if (oldValue != '' && textType != 'fieldname') {
               var concatenatedValue = oldValue + ' ' + newValue;
            } else {
               concatenatedValue = newValue;
            }
         } else {
            concatenatedValue = oldValue + newValue;
         }
         data.find('.fieldValue').val(concatenatedValue);
         currentElement.val('').select2("val", '');
      });
   },
   registerChangeFieldEvent: function (data) {
      jQuery('.textType', data).on('change', function (e) {
         var valueType = jQuery(e.currentTarget).val();
         var useFieldContainer = jQuery('.useFieldContainer', data);
         var useFunctionContainer = jQuery('.useFunctionContainer', data);
         var uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
         jQuery('.fieldValue', data).hide();
         jQuery('[data-' + uiType + ']', data).show();
         if (valueType == 'fieldname') {
            useFieldContainer.removeClass('hide');
            useFunctionContainer.addClass('hide');
         } else if (valueType == 'expression') {
            useFieldContainer.removeClass('hide');
            useFunctionContainer.removeClass('hide');
         } else {
            useFieldContainer.addClass('hide');
            useFunctionContainer.addClass('hide');
         }
         jQuery('.helpmessagebox', data).addClass('hide');
         jQuery('#' + valueType + '_help', data).removeClass('hide');
         data.find('.fieldValue').val('');
      });
   },
   postShowModalAction: function (data, valueType) {
      if (valueType == 'fieldname') {
         jQuery('.useFieldContainer', data).removeClass('hide');
         jQuery('.textType', data).val(valueType).trigger('change');
      } else if (valueType == 'expression') {
         jQuery('.useFieldContainer', data).removeClass('hide');
         jQuery('.useFunctionContainer', data).removeClass('hide');
         jQuery('.textType', data).val(valueType).trigger('change');
      }
      jQuery('#' + valueType + '_help', data).removeClass('hide');
      var uiType = jQuery('.textType', data).find('option:selected').data('ui');
      jQuery('.fieldValue', data).hide();
      jQuery('[data-' + uiType + ']', data).show();
   },
   registerEventForShowModuleFilterCondition: function () {
      var thisInstance = this;
      jQuery('#module_name').on('change', function (e) {
         var currentElement = jQuery(e.currentTarget);
         var selectedOption = currentElement.find('option:selected');
         jQuery('#workflowTriggerCreate').html(selectedOption.data('create-label'));
         jQuery('#workflowTriggerUpdate').html(selectedOption.data('update-label'));
         var params = {
            'module': 'Workflows',
            'parent': 'Settings',
            'view': 'EditAjax',
            'mode': 'getWorkflowConditions',
            'record': jQuery("input[name='record']").val(),
            'module_name': currentElement.val()
         }
         
         app.helper.showProgress();
         app.request.get({data: params}).then(function (error, data) {
            app.helper.hideProgress();
            jQuery('#workflow_condition').html(data);
            var advanceFilterContainer = jQuery('#advanceFilterContainer');
            vtUtils.applyFieldElementsView(jQuery('#workflow_condition'));
            thisInstance.advanceFilterInstance = Workflows_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', advanceFilterContainer));
            thisInstance.getPopUp(advanceFilterContainer);
            
            //Workflows actions
            thisInstance.setActionContainer(jQuery('#workflow_action'));
            thisInstance.registerEditTaskEvent();
            thisInstance.registerTaskStatusChangeEvent();
            thisInstance.registerTaskDeleteEvent();
            jQuery(".taskStatus").bootstrapSwitch();
            
			app.helper.registerLeavePageWithoutSubmit(jQuery('#workflow_edit'));
         });
      });
      jQuery('#module_name').trigger('change');
   },
   
   //Workflow action related api's
   registerEditTaskEvent: function () {
      var thisInstance = this;
      var container = this.getActionContainer();
      container.on('click', '[data-url]', function (e) {
         var currentElement = jQuery(e.currentTarget);
         var url = currentElement.data('url') + '&module_name=' + jQuery('#module_name').val();
         app.helper.showProgress();
         app.request.get({url:url}).then(function (error, data) {
            app.helper.hideProgress();
            app.helper.loadPageContentOverlay(data).then(function(container) {
                var container = jQuery(container);
                var viewPortHeight = $(window).height();

                var params = {
                    setHeight:(viewPortHeight-jQuery('.app-fixed-navbar').height()-container.find('.modal-header').height())+'px'
                };
                app.helper.showVerticalScroll(container.find('.modal-body.editTaskBody'), params);
				thisInstance.registerVTCreateTodoTaskEvents();
				var taskType = jQuery('#taskType').val();
				var functionName = 'register' + taskType + 'Events';
				if (typeof thisInstance[functionName] != 'undefined') {
				   thisInstance[functionName].apply(thisInstance);
				}
				thisInstance.registerSaveTaskSubmitEvent(taskType);
				thisInstance.registerFillTaskFieldsEvent();
				thisInstance.registerCheckSelectDateEvent();
            });
         });
      });
      container.on('click', '.editTask', function (e) {
          var currentElement = jQuery(e.currentTarget);
          var params = {
              module: 'Workflows',
              parent: 'Settings',
              view: 'EditV7Task',
              type: currentElement.data('taskType'),
              module_name: jQuery('#module_name').val()
          }
          var parentElement = currentElement.closest('tr');
          var taskData = parentElement.find('.taskData').val();
          if(taskData) {
              params.taskData = taskData;
          }
          app.helper.showProgress();
          app.request.post({data:params}).then(function (error, data) {
            app.helper.hideProgress();
            app.helper.loadPageContentOverlay(data).then(function(container) {
                var overlayPageContent = $('#overlayPageContent');
                overlayPageContent.css('margin-left', '230px');
                var viewPortHeight = $(window).height();
                var params = {
                    setHeight:(viewPortHeight-jQuery('.app-fixed-navbar').height()-container.find('.modal-header').height())+'px'
                };
                app.helper.showVerticalScroll(container.find('.modal-body.editTaskBody'), params);
                thisInstance.registerVTCreateTodoTaskEvents();
                var taskType = jQuery('#taskType').val();
                var functionName = 'register' + taskType + 'Events';
                if (typeof thisInstance[functionName] != 'undefined') {
                   thisInstance[functionName].apply(thisInstance);
                }
                thisInstance.registerSaveTaskSubmitEvent(taskType);
                thisInstance.registerFillTaskFieldsEvent();
                thisInstance.registerCheckSelectDateEvent();
            });  
         });
      });
      container.on('click', '.deleteTaskTemplate', function (e) {
          var currentElement = jQuery(e.currentTarget);
          var parentElement = currentElement.closest('tr');
          var tableDiv = jQuery('#table-content');
          var table = tableDiv.find('#listview-table');
          parentElement.remove();
          var visibleRows = table.find('tbody').find('tr:visible');
          if(visibleRows.length == 0) {
              tableDiv.find('.emptyRecordsDiv').removeClass('hide');
          }
      });
   },
   registerCheckSelectDateEvent: function () {
      jQuery('[name="check_select_date"]').on('change', function (e) {
         if (jQuery(e.currentTarget).is(':checked')) {
            jQuery('#checkSelectDateContainer').removeClass('hide').addClass('show');
         } else {
            jQuery('#checkSelectDateContainer').removeClass('show').addClass('hide');
         }
      });
   },
   /**
    * Function to add event on signature popover
    */
   registerTooltipEventForSignatureField: function () {
      jQuery("#signaturePopover").on('mouseover', function (e) {
         jQuery('#signaturePopover').popover({
            'html': true
         });
      });
   },
   registerSaveTaskSubmitEvent: function (taskType) {
      var thisInstance = this;
      var form = jQuery('#saveTask');
      var params = {
            submitHandler: function(form) {
                var form = jQuery(form);
                // to Prevent submit if already submitted
                jQuery("button[name='saveButton']", form).attr("disabled","disabled");
                var record = jQuery('#record').val();
                if(!record) {
                    var preSaveActionFunctionName = 'preSave' + taskType;
                    if (typeof thisInstance[preSaveActionFunctionName] != 'undefined') {
                       thisInstance[preSaveActionFunctionName].apply(thisInstance, [taskType]);
                    }
                    var params = form.serializeFormData();
                    var clonedParams = jQuery.extend({}, params);
                    clonedParams.action ='ValidateExpression';
                    clonedParams.mode ='ForTaskEdit';
                    app.request.post({'data' : clonedParams}).then(function(error, data){
                        if(error != null) {
                            app.helper.showErrorNotification({'message' : app.vtranslate('LBL_EXPRESSION_INVALID')});
                            return;
                        }
                        app.helper.hidePageContentOverlay();
                        if(!params.tmpTaskId) {
                            params.tmpTaskId = thisInstance.getUniqueNumber();
                        }
                        var templateData = $('<input>').attr({
                                               type: 'hidden',
                                               name: 'tasks[]'
                                           }).addClass('taskData').val(JSON.stringify(params));
                        var tableDiv = jQuery('#table-content');
                        var table = tableDiv.find('#listview-table');
                        var tableBody = table.find('tbody');
                        var taskTemplate = tableBody.find('.taskTemplate').clone(true,true);
                        taskTemplate.removeClass('hide taskTemplate');
                        taskTemplate.find('.taskType').text(app.vtranslate(params.taskType));
                        taskTemplate.find('.taskName').text(params.summary);
                        taskTemplate.find('.editTask').data('taskType', params.taskType);
                        taskTemplate.append(templateData);
                        taskTemplate.addClass('tmpTaskId-'+params.tmpTaskId);
                        if(params.active == 'false') {
                            taskTemplate.find('.tmpTaskStatus').val('off').prop('checked', false);
                        }
                        taskTemplate.find('.tmpTaskStatus').addClass('taskStatus').bootstrapSwitch();
                        tableDiv.find('.emptyRecordsDiv').addClass('hide');
                        if(table.find('.tmpTaskId-'+params.tmpTaskId).length != 0) {
                            table.find('.tmpTaskId-'+params.tmpTaskId).replaceWith(taskTemplate);
                        } else {
                            tableBody.append(taskTemplate);
                        }
                    });
                    
                } else {
                   var preSaveActionFunctionName = 'preSave' + taskType;
                   if (typeof thisInstance[preSaveActionFunctionName] != 'undefined') {
                      thisInstance[preSaveActionFunctionName].apply(thisInstance, [taskType]);
                   }
                   form.find('[name="saveButton"]').attr('disabled', 'disabled');
                   var params = form.serializeFormData();
                   app.helper.showProgress();
                   app.request.post({data:params}).then(function (error, data) {
                        app.helper.hideProgress();
                        if (data) {
                           thisInstance.getTaskList();
                           app.helper.hidePageContentOverlay();
                        } else {
                            app.helper.showErrorNotification({'message' : app.vtranslate('LBL_EXPRESSION_INVALID')});
                            form.find('[name="saveButton"]').removeAttr('disabled');
                        }  
                   });
                }
            },
            ignore : ".ignore-validation"
      };
      form.vtValidate(params);
   },
   
   getUniqueNumber : function() {
        var date = new Date();
        var components = [
            date.getYear(),
            date.getMonth(),
            date.getDate(),
            date.getHours(),
            date.getMinutes(),
            date.getSeconds(),
            date.getMilliseconds()
        ];

        var id = components.join("");
        
        return id;
   },
   
   VTUpdateFieldsTaskCustomValidation: function () {
      return this.checkDuplicateFieldsSelected();
   },
   VTCreateEntityTaskCustomValidation: function () {
      return this.checkDuplicateFieldsSelected();
   },
   VTCreateEventTaskCustomValidation: function () {
      return this.checkStartAndEndDate();
   },
   checkStartAndEndDate: function () {
      var form = jQuery('#saveTask');
      var params = form.serializeFormData();
      var result = true;
      if (params['taskType'] == 'VTCreateEventTask' && params['startDatefield'] == params['endDatefield']) {
         if (params['startDirection'] == params['endDirection']) {
            if (params['startDays'] > params['endDays'] && params['endDirection'] == 'after') {
               result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
               return result;
            } else if (params['startDays'] < params['endDays'] && params['endDirection'] == 'before') {
               result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
               return result;
            } else if (params['startDays'] == params['endDays'] && params['startDirection'] == params['endDirection'] && params['endTime'] < params['startTime']) {
               result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
               return result;
            }
         }
      }
      return result;
   },
   checkDuplicateFieldsSelected: function () {
      var selectedFieldNames = jQuery('#save_fieldvaluemapping').find('.conditionRow').find('[name="fieldname"]');
      var result = true;
      var failureMessage = app.vtranslate('JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE');
      jQuery.each(selectedFieldNames, function (i, ele) {
         var fieldName = jQuery(ele).attr("value");
         var taskType = jQuery('#taskType').val();
         if (taskType == "VTUpdateFieldsTask") {
            var fields = jQuery('[data-workflow_columnname="' + fieldName + '"]').not(':hidden');
         } else {
            var fields = jQuery('[name="' + fieldName + '"]').not(':hidden');
         }
         if (fields.length > 1) {
            result = failureMessage;
            return false;
         }
      });
      return result;
   },
   preSaveVTUpdateFieldsTask: function (tasktype) {
      var values = this.getValues(tasktype);
      jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
   },
   preSaveVTCreateEntityTask: function (tasktype) {
      var values = this.getValues(tasktype);
      jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
   },
   preSaveVTEmailTask: function (tasktype) {
      var textAreaElement = jQuery('#content');
      //To keep the plain text value to the textarea which need to be
      //sent to server
      textAreaElement.val(CKEDITOR.instances['content'].getData());
   },
   /**
    * Function to check if the field selected is empty field
    * @params : select element which represents the field
    * @return : boolean true/false
    */
   isEmptyFieldSelected: function (fieldSelect) {
      var selectedOption = fieldSelect.find('option:selected');
      //assumption that empty field will be having value none
      if (selectedOption.val() == 'none') {
         return true;
      }
      return false;
   },
   getVTCreateEntityTaskFieldList: function () {
      return new Array('fieldname', 'value', 'valuetype', 'modulename');
   },
   getVTUpdateFieldsTaskFieldList: function () {
      return new Array('fieldname', 'value', 'valuetype');
   },
   getValues: function (tasktype) {
      var thisInstance = this;
      var conditionsContainer = jQuery('#save_fieldvaluemapping');
      var fieldListFunctionName = 'get' + tasktype + 'FieldList';
      if (typeof thisInstance[fieldListFunctionName] != 'undefined') {
         var fieldList = thisInstance[fieldListFunctionName].apply()
      }

      var values = [];
      var conditions = jQuery('.conditionRow', conditionsContainer);
      conditions.each(function (i, conditionDomElement) {
         var rowElement = jQuery(conditionDomElement);
         var fieldSelectElement = jQuery('[name="fieldname"]', rowElement);
         var valueSelectElement = jQuery('[data-value="value"]', rowElement);
         //To not send empty fields to server
         if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
            return true;
         }
         var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
         var fieldType = fieldDataInfo.type;
         var rowValues = {};
         if (fieldType == 'owner') {
            for (var key in fieldList) {
               var field = fieldList[key];
               if (field == 'value' && valueSelectElement.is('select')) {
                  rowValues[field] = valueSelectElement.find('option:selected').val();
               } else {
                  rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
               }
            }
         } else if (fieldType == 'picklist' || fieldType == 'multipicklist') {
            for (var key in fieldList) {
               var field = fieldList[key];
               if (field == 'value' && valueSelectElement.is('input')) {
                  var commaSeperatedValues = valueSelectElement.val();
                  var pickListValues = valueSelectElement.data('picklistvalues');
                  var valuesArr = commaSeperatedValues.split(',');
                  var newvaluesArr = [];
                  for (i = 0; i < valuesArr.length; i++) {
                     if (typeof pickListValues[valuesArr[i]] != 'undefined') {
                        newvaluesArr.push(pickListValues[valuesArr[i]]);
                     } else {
                        newvaluesArr.push(valuesArr[i]);
                     }
                  }
                  var reconstructedCommaSeperatedValues = newvaluesArr.join(',');
                  rowValues[field] = reconstructedCommaSeperatedValues;
               } else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist') {
                  rowValues[field] = valueSelectElement.val();
               } else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist') {
                  var value = valueSelectElement.val();
                  if (value == null) {
                     rowValues[field] = value;
                  } else {
                     rowValues[field] = value.join(',');
                  }
               } else {
                  rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
               }
            }

         } else if (fieldType == 'text') {
            for (var key in fieldList) {
               var field = fieldList[key];
               if (field == 'value') {
                  rowValues[field] = rowElement.find('textarea').val();
               } else {
                  rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
               }
            }
         } else {
            for (var key in fieldList) {
               var field = fieldList[key];
               if (field == 'value') {
                  rowValues[field] = valueSelectElement.val();
               } else {
                  rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
               }
            }
         }
         if (jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
            rowValues['valuetype'] = 'rawtext';
         }

         values.push(rowValues);
      });
      return values;
   },
   
   getTaskList: function () {
      var params = {
         module: app.getModuleName(),
         parent: app.getParentModuleName(),
         view: 'TasksList',
         record: jQuery('[name="record"]').val()
      }
      app.helper.showProgress();
      app.request.get({data:params}).then(function (error, data) {
         jQuery('#taskListContainer').html(data);
         app.helper.hideProgress();
         jQuery(".taskStatus").bootstrapSwitch();
      });
   },
   
   /**
    * Function to get ckEditorInstance
    */
   getckEditorInstance: function () {
      if (this.ckEditorInstance == false) {
         this.ckEditorInstance = new Vtiger_CkEditor_Js();
      }
      return this.ckEditorInstance;
   },
   registerTaskStatusChangeEvent: function () {
      var container = this.getActionContainer();
      container.on('change', '.taskStatus', function (e) {
         var currentStatusElement = jQuery(e.currentTarget);
         var url = currentStatusElement.data('statusurl');
         if (currentStatusElement.is(':checked')) {
            url = url + '&status=true';
         } else {
            url = url + '&status=false';
         }
         app.helper.showProgress();
         app.request.post({data:url}).then(function (error, data) {
            if (data.result == "ok") {
               app.helper.showSuccessNotification({message: 'JS_STATUS_CHANGED_SUCCESSFULLY'})
            }
            app.helper.hideProgress();
         });
         e.stopImmediatePropagation();
      });
   },
   registerTaskDeleteEvent: function () {
      var thisInstance = this;
      var container = this.getActionContainer();
      container.on('click', '.deleteTask', function (e) {
         var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
         app.helper.showConfirmationBox({
            'message': message
         }).then( 
            function () {
               var currentElement = jQuery(e.currentTarget);
               var deleteUrl = currentElement.data('deleteurl');
               app.helper.showProgress();
               app.request.post({url: deleteUrl}).then(function (error, data) {
                  app.helper.hideProgress();
                  if (data == 'ok') {
                     thisInstance.getTaskList();
                     app.helper.showSuccessNotification({message: app.vtranslate('JS_TASK_DELETED_SUCCESSFULLY')});
                  }
               });
            });
      });
   },
   registerFillTaskFromEmailFieldEvent: function () {
      jQuery('#saveTask').on('change', '#fromEmailOption', function (e) {
         var currentElement = jQuery(e.currentTarget);
         var inputElement = currentElement.closest('.row').find('.fields');
         inputElement.val(currentElement.val());
      })
   },
   registerFillTaskFieldsEvent: function () {
      jQuery('#saveTask').on('change', '.task-fields', function (e) {
         var currentElement = jQuery(e.currentTarget);
         var inputElement = currentElement.closest('.row').find('.fields');
         if (currentElement.hasClass('overwriteSelection')) {
            inputElement.val(currentElement.val());
         } else {
            var oldValue = inputElement.val();
            var newValue = oldValue + currentElement.val();
            inputElement.val(newValue);
         }
      });
   },
   registerFillMailContentEvent: function () {
      jQuery('#task-fieldnames,#task_timefields,#task-templates,#task-emailtemplates').change(function (e) {
         var textarea = CKEDITOR.instances.content;
         var value = jQuery(e.currentTarget).val();
         if (textarea != undefined) {
            textarea.insertHtml(value);
         } else if (jQuery('textarea[name="content"]')) {
            var textArea = jQuery('textarea[name="content"]');
            textArea.insertAtCaret(value);
         }
      });
   },
   registerVTEmailTaskEvents: function () {
      var textAreaElement = jQuery('#content');
      var ckEditorInstance = this.getckEditorInstance();
      ckEditorInstance.loadCkEditor(textAreaElement);
      this.registerFillMailContentEvent();
      this.registerTooltipEventForSignatureField();
      this.registerFillTaskFromEmailFieldEvent();
      this.registerCcAndBccEvents();
   },
   registerVTCreateTodoTaskEvents: function () {
      vtUtils.registerEventForTimeFields(jQuery('#saveTask'));
   },
   registerVTUpdateFieldsTaskEvents: function () {
      var thisInstance = this;
      this.registerAddFieldEvent();
      this.registerDeleteConditionEvent();
      this.registerFieldChange();
      this.fieldValueMap = false;
      if (jQuery('#fieldValueMapping').val() != '') {
         this.fieldValueReMapping();
      }
      var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
      jQuery.each(fields, function (i, field) {
         thisInstance.loadFieldSpecificUi(jQuery(field));
      });
      this.getPopUp(jQuery('#saveTask'));
   },
   registerVTPushNotificationTaskEvents: function () {
      this.registerFillMailContentEvent();
   },
   registerAddFieldEvent: function () {
      jQuery('#addFieldBtn').on('click', function (e) {
         var newAddFieldContainer = jQuery('.basicAddFieldContainer').clone(true, true).removeClass('basicAddFieldContainer hide').addClass('conditionRow');
         jQuery('select', newAddFieldContainer).addClass('select2');
         jQuery('#save_fieldvaluemapping').append(newAddFieldContainer);
         vtUtils.showSelect2ElementView(newAddFieldContainer.find('.select2'));
      });
   },
   registerDeleteConditionEvent: function () {
      jQuery('#saveTask').on('click', '.deleteCondition', function (e) {
         jQuery(e.currentTarget).closest('.conditionRow').remove();
      })
   },
   /**
    * Function which will register field change event
    */
   registerFieldChange: function () {
      var thisInstance = this;
      jQuery('#saveTask').on('change', 'select[name="fieldname"]', function (e) {
         var selectedElement = jQuery(e.currentTarget);
         if (selectedElement.val() != 'none') {
            var conditionRow = selectedElement.closest('.conditionRow');
            var moduleNameElement = conditionRow.find('[name="modulename"]');
            if (moduleNameElement.length > 0) {
               var selectedOptionFieldInfo = selectedElement.find('option:selected').data('fieldinfo');
               var type = selectedOptionFieldInfo.type;
               if (type == 'picklist' || type == 'multipicklist') {
                  var moduleName = jQuery('#createEntityModule').val();
                  moduleNameElement.find('option[value="' + moduleName + '"]').attr('selected', true);
                  moduleNameElement.trigger('change');
                  moduleNameElement.select2("disable");
               }
            }
            thisInstance.loadFieldSpecificUi(selectedElement);
         }
      });
   },
   getModuleName: function () {
      return app.getModuleName();
   },
   getFieldValueMapping: function () {
      var fieldValueMap = this.fieldValueMap;
      if (fieldValueMap != false) {
         return fieldValueMap;
      } else {
         return '';
      }
   },
   fieldValueReMapping: function () {
      var object = JSON.parse(jQuery('#fieldValueMapping').val());
      var fieldValueReMap = {};

      jQuery.each(object, function (i, array) {
         fieldValueReMap[array.fieldname] = {};
         var values = {}
         jQuery.each(array, function (key, value) {
            values[key] = value;
         });
         fieldValueReMap[array.fieldname] = values
      });
      this.fieldValueMap = fieldValueReMap;
   },
   loadFieldSpecificUi: function (fieldSelect) {
      var selectedOption = fieldSelect.find('option:selected');
      var row = fieldSelect.closest('div.conditionRow');
      var fieldUiHolder = row.find('.fieldUiHolder');
      var fieldInfo = selectedOption.data('fieldinfo');
      var fieldValueMapping = this.getFieldValueMapping();
      var fieldValueMappingKey = fieldInfo.name;
      var taskType = jQuery('#taskType').val();
      if (taskType == "VTUpdateFieldsTask") {
         fieldValueMappingKey = fieldInfo.workflow_columnname;
		 if (fieldValueMappingKey === undefined || fieldValueMappingKey === null){
			fieldValueMappingKey = selectedOption.val();
		 }
      }
      if (fieldValueMapping != '' && typeof fieldValueMapping[fieldValueMappingKey] != 'undefined') {
         fieldInfo.value = fieldValueMapping[fieldValueMappingKey]['value'];
         fieldInfo.workflow_valuetype = fieldValueMapping[fieldValueMappingKey]['valuetype'];
      } else {
         fieldInfo.workflow_valuetype = 'rawtext';
      }
      
      if(fieldInfo.type == 'reference' || fieldInfo.type == 'multireference') {
          fieldInfo.referenceLabel = fieldUiHolder.find('[name="referenceValueLabel"]').val();
          fieldInfo.type = 'string';
      }
      
      var moduleName = this.getModuleName();

      var fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
      this.fieldModelInstance = fieldModel;
      var fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

      //remove validation since we dont need validations for all eleements
      // Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
      var fieldName = fieldModel.getName();
      if (fieldModel.getType() == 'multipicklist') {
         fieldName = fieldName + "[]";
      }
      fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-workflow_columnname', fieldInfo.workflow_columnname);
      fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-workflow_columnname', fieldInfo.workflow_columnname);
      fieldSpecificUi.filter('[name="valuetype"]').addClass('ignore-validation');
      fieldSpecificUi.find('[name="valuetype"]').addClass('ignore-validation');

      //If the workflowValueType is rawtext then only validation should happen
      var workflowValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
      if (workflowValueType != 'rawtext' && typeof workflowValueType != 'undefined') {
         fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('ignore-validation');
         fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('ignore-validation');
      }

      fieldUiHolder.html(fieldSpecificUi);

      if (fieldSpecificUi.is('input.select2')) {
         var tagElements = fieldSpecificUi.data('tags');
         var params = {tags: tagElements, tokenSeparators: [","]}
         vtUtils.showSelect2ElementView(fieldSpecificUi, params)
      } else if (fieldSpecificUi.is('select')) {
         if (fieldSpecificUi.hasClass('select2')) {
            vtUtils.showSelect2ElementView(fieldSpecificUi)
         } else {
            vtUtils.showSelect2ElementView(fieldSpecificUi);
         }
      } else if (fieldSpecificUi.is('input.dateField')) {
         var calendarType = fieldSpecificUi.data('calendarType');
         if (calendarType == 'range') {
            var customParams = {
               calendars: 3,
               mode: 'range',
               className: 'rangeCalendar',
               onChange: function (formated) {
                  fieldSpecificUi.val(formated.join(','));
               }
            }
            app.registerEventForDatePickerFields(fieldSpecificUi, false, customParams);
         } else {
            app.registerEventForDatePickerFields(fieldSpecificUi);
         }
      }
      return this;
   },
   /**
    * Functiont to get the field specific ui for the selected field
    * @prarms : fieldSelectElement - select element which will represents field list
    * @return : jquery object which represents the ui for the field
    */
   getFieldSpecificUi: function (fieldSelectElement) {
      var fieldModel = this.fieldModelInstance;
      return  jQuery(fieldModel.getUiTypeSpecificHtml())
   },
   registerVTCreateEventTaskEvents: function () {
      vtUtils.registerEventForTimeFields(jQuery('#saveTask'));
      this.registerRecurrenceFieldCheckBox();
      this.repeatMonthOptionsChangeHandling();
      this.registerRecurringTypeChangeEvent();
      this.registerRepeatMonthActions();
   },
   registerVTCreateEntityTaskEvents: function () {
      this.registerChangeCreateEntityEvent();
      this.registerVTUpdateFieldsTaskEvents();
   },
   registerChangeCreateEntityEvent: function () {
      var thisInstance = this;
      jQuery('#createEntityModule').on('change', function (e) {
         var relatedModule = jQuery(e.currentTarget).val();
         var module_name = jQuery('#module_name').val();
         if( relatedModule == module_name ) {
             jQuery(e.currentTarget).closest('.taskTypeUi').find('.sameModuleError').removeClass('hide');
         } else{
             jQuery(e.currentTarget).closest('.taskTypeUi').find('.sameModuleError').addClass('hide');
         }
         var params = {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            view: 'CreateEntity',
            relatedModule: jQuery(e.currentTarget).val(),
            for_workflow: jQuery('[name="for_workflow"]').val(),
            module_name: jQuery('#module_name').val()
         }
         app.helper.showProgress();
         app.request.get({data:params}).then(function (error, data) {
            app.helper.hideProgress();
            var createEntityContainer = jQuery('#addCreateEntityContainer');
            createEntityContainer.html(data);
            vtUtils.showSelect2ElementView(createEntityContainer.find('.select2'));
            thisInstance.registerAddFieldEvent();
            thisInstance.fieldValueMap = false;
            if (jQuery('#fieldValueMapping').val() != '') {
               thisInstance.fieldValueReMapping();
            }
            var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
            jQuery.each(fields, function (i, field) {
               thisInstance.loadFieldSpecificUi(jQuery(field));
            });
         });
      });
   },
   /**
    * Function which will register change event on recurrence field checkbox
    */
   registerRecurrenceFieldCheckBox: function () {
      var thisInstance = this;
      jQuery('#saveTask').find('input[name="recurringcheck"]').on('change', function (e) {
         var element = jQuery(e.currentTarget);
         var repeatUI = jQuery('#repeatUI');
         if (element.is(':checked')) {
            repeatUI.removeClass('hide');
         } else {
            repeatUI.addClass('hide');
         }
      });
   },
   /**
    * Function which will register the change event for recurring type
    */
   registerRecurringTypeChangeEvent: function () {
      var thisInstance = this;
      jQuery('#recurringType').on('change', function (e) {
         var currentTarget = jQuery(e.currentTarget);
         var recurringType = currentTarget.val();
         thisInstance.changeRecurringTypesUIStyles(recurringType);

      });
   },
   /**
    * Function which will register the change event for repeatMonth radio buttons
    */
   registerRepeatMonthActions: function () {
      var thisInstance = this;
      jQuery('#saveTask').find('input[name="repeatMonth"]').on('change', function (e) {
         //If repeatDay radio button is checked then only select2 elements will be enable
         thisInstance.repeatMonthOptionsChangeHandling();
      });
  },
   /**
    * Function which will change the UI styles based on recurring type
    * @params - recurringType - which recurringtype is selected
    */
   changeRecurringTypesUIStyles: function (recurringType) {
      var thisInstance = this;
      if (recurringType == 'Daily' || recurringType == 'Yearly') {
         jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
         jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
      } else if (recurringType == 'Weekly') {
         jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
         jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
      } else if (recurringType == 'Monthly') {
         jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
         jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
      }
   },
   /**
    * This function will handle the change event for RepeatMonthOptions
    */
   repeatMonthOptionsChangeHandling: function () {
      //If repeatDay radio button is checked then only select2 elements will be enable
      if (jQuery('#repeatDay').is(':checked')) {
         jQuery('#repeatMonthDate').attr('disabled', true);
         jQuery('#repeatMonthDayType').select2("enable");
         jQuery('#repeatMonthDay').select2("enable");
      } else {
         jQuery('#repeatMonthDate').removeAttr('disabled');
         jQuery('#repeatMonthDayType').select2("disable");
         jQuery('#repeatMonthDay').select2("disable");
      }
   },
   checkHiddenStatusofCcandBcc: function () {
      var ccLink = jQuery('#ccLink');
      var bccLink = jQuery('#bccLink');
      if (ccLink.is(':hidden') && bccLink.is(':hidden')) {
         ccLink.closest('div.row').addClass('hide');
      }
   },
   /*
    * Function to register the events for bcc and cc links
    */
   registerCcAndBccEvents: function () {
      var thisInstance = this;
      jQuery('#ccLink').on('click', function (e) {
         var ccContainer = jQuery('#ccContainer');
         ccContainer.removeClass('hide');
         var taskFieldElement = ccContainer.find('select.task-fields');
         vtUtils.showSelect2ElementView(taskFieldElement);
         jQuery(e.currentTarget).hide();
         thisInstance.checkHiddenStatusofCcandBcc();
      });
      jQuery('#bccLink').on('click', function (e) {
         var bccContainer = jQuery('#bccContainer');
         bccContainer.removeClass('hide');
         var taskFieldElement = bccContainer.find('select.task-fields');
         vtUtils.showSelect2ElementView(taskFieldElement);
         jQuery(e.currentTarget).hide();
         thisInstance.checkHiddenStatusofCcandBcc();
      });
   },
   
	/**
	 * Function to register event for scheduled workflows UI
	 */
	registerEventForScheduledWorkflow : function() {
		var thisInstance = this;
		jQuery('input[name="workflow_trigger"]').on('click', function(e) {
			var element = jQuery(e.currentTarget);
			var scheduleBoxContainer = jQuery('#scheduleBox');
            var recurrenceBoxContainer = jQuery('.workflowRecurrenceBlock');
            if(element.is(':checked') && element.val() == '6') {
                scheduleBoxContainer.removeClass('hide');
                recurrenceBoxContainer.addClass('hide');
            } else if(element.is(':checked') && element.val() == '3') {
                recurrenceBoxContainer.removeClass('hide');
                recurrenceBoxContainer.find('input[type="radio"]').click();
                scheduleBoxContainer.addClass('hide');
            } else {
                scheduleBoxContainer.addClass('hide');
                recurrenceBoxContainer.addClass('hide');
            }
		});
		vtUtils.registerEventForTimeFields('#schtime', true);
		vtUtils.registerEventForDateFields(jQuery('#scheduleByDate'));
        jQuery(".weekDaySelect").bind("mousedown", function(e) {
            e.metaKey = true;
        }).selectable();
        jQuery( ".weekDaySelect" ).on( "selectableselected selectableunselected", function( event, ui ) {
            var inputElement = jQuery('#schdayofweek');
            var weekDaySelect = jQuery('.weekDaySelect');
            var selectedArray = new Array();
            weekDaySelect.find('.ui-selected').each(function(){
                var value = jQuery(this).data('value');
                selectedArray.push(value);
            });
            var selected = selectedArray.join(',');
            inputElement.val(selected);
        });
		var currentYear = new Date().getFullYear();
		jQuery('#annualDatePicker').datepick({autoSize: true, multiSelect:100,monthsToShow: [1,2],
				minDate: '01/01/'+currentYear, maxDate: '12/31/'+currentYear,
				yearRange: currentYear+':'+currentYear,
				onShow : function() {
					//Hack to remove the year
					thisInstance.removeYearInAnnualWorkflow();
				},
				onSelect : function(dates) {
					var datesInfo = [];
					var values = [];
					var html='';
					// reset the annual dates
					var annualDatesEle = jQuery('#annualDates');
					thisInstance.updateAnnualDates(annualDatesEle);
					for(index in dates) {
						var date = dates[index];
						datesInfo.push({
								id:thisInstance.DateToYMD(date),
								text:thisInstance.DateToYMD(date)
							});
						values.push(thisInstance.DateToYMD(date));
						html += '<option selected value='+thisInstance.DateToYMD(date)+'>'+thisInstance.DateToYMD(date)+'</option>';
					}
					annualDatesEle.append(html);
					annualDatesEle.trigger("change");
				}
			});
			var annualDatesEle = jQuery('#annualDates');
			thisInstance.updateAnnualDates(annualDatesEle);
			annualDatesEle.trigger("change");
	},

	removeYearInAnnualWorkflow : function() {
		setTimeout(function() {
			var year = jQuery('.datepick-month.first').find('.datepick-month-year').get(1);
			jQuery(year).hide();
			var monthHeaders = $('.datepick-month-header');
			jQuery.each(monthHeaders, function( key, ele ) {
				var header = jQuery(ele);
				var str = header.html().replace(/[\d]+/, '');
				header.html(str);
			});
		},100);
	},

	updateAnnualDates : function(annualDatesEle) {
		annualDatesEle.html('');
		var annualDatesJSON = jQuery('#hiddenAnnualDates').val();
		if(annualDatesJSON) {
			var hiddenDates = '';
			var annualDates = JSON.parse(annualDatesJSON);
			for(j in annualDates) {
				hiddenDates += '<option selected value='+annualDates[j]+'>'+annualDates[j]+'</option>';
			}
			annualDatesEle.html(hiddenDates);
		}
	},

	DateToYMD : function (date) {
        var year, month, day;
        year = String(date.getFullYear());
        month = String(date.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(date.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        return year + "-" + month + "-" + day;
    },

	registerEventForChangeInScheduledType : function() {
		var thisInstance = this;
		jQuery('#schtypeid').on('change', function(e){
			var element = jQuery(e.currentTarget);
			var value = element.val();

			thisInstance.showScheduledTime();
			thisInstance.hideScheduledWeekList();
			thisInstance.hideScheduledMonthByDateList();
			thisInstance.hideScheduledSpecificDate();
			thisInstance.hideScheduledAnually();

			if(value == '1') {	//hourly
				thisInstance.hideScheduledTime();
			} else if(value == '3') {	//weekly
				thisInstance.showScheduledWeekList();
			} else if(value == '4') {	//specific date
				thisInstance.showScheduledSpecificDate();
			} else if(value == '5') {	//monthly by day
				thisInstance.showScheduledMonthByDateList();
			} else if(value == '7') {
				thisInstance.showScheduledAnually();
			}
		});
	},

	hideScheduledTime : function() {
		jQuery('#scheduledTime').addClass('hide');
	},

	showScheduledTime : function() {
		jQuery('#scheduledTime').removeClass('hide');
	},

	hideScheduledWeekList : function() {
		jQuery('#scheduledWeekDay').addClass('hide');
	},

	showScheduledWeekList : function() {
		jQuery('#scheduledWeekDay').removeClass('hide');
	},

	hideScheduledMonthByDateList : function() {
		jQuery('#scheduleMonthByDates').addClass('hide');
	},

	showScheduledMonthByDateList : function() {
		jQuery('#scheduleMonthByDates').removeClass('hide');
	},

	hideScheduledSpecificDate : function() {
		jQuery('#scheduleByDate').addClass('hide');
	},

	showScheduledSpecificDate : function() {
		jQuery('#scheduleByDate').removeClass('hide');
	},

	hideScheduledAnually : function() {
		jQuery('#scheduleAnually').addClass('hide');
	},

	showScheduledAnually : function() {
		jQuery('#scheduleAnually').removeClass('hide');
	},
    
    registerEventForChangeWorkflowState: function () {
        var editViewContainer = this.getEditViewContainer();
        var thisInstance = this;
        jQuery(editViewContainer).on('switchChange.bootstrapSwitch', ".taskStatus", function (e) {
           var currentElement = jQuery(e.currentTarget);
           var status = 'true';
           if(currentElement.val() == 'on'){
               status = 'false';
               currentElement.attr('value','off');
           } else {
               currentElement.attr('value','on');
           }
           if(currentElement.data('statusurl')) {
               var url = currentElement.data('statusurl') + "&status=" + status;
               app.helper.showProgress();
               app.request.post({url:url}).then(function(error,data){
                   app.helper.hideProgress();
                   if(data){
                      app.helper.showSuccessNotification({message : app.vtranslate('JS_TASK_STATUS_CHANGED')});
                      thisInstance.getTaskList();
                   }
               });
           } else {
               var parent = currentElement.closest('.listViewEntries');
               var taskElement = parent.find('.taskData');
               var taskData = JSON.parse(taskElement.val());
               taskData.active = status;
               taskElement.val(JSON.stringify(taskData));
               app.helper.showSuccessNotification({message : app.vtranslate('JS_TASK_STATUS_CHANGED')});
           }
        });
    },
    
    registerEnableFilterOption : function() {
        var editViewContainer = this.getEditViewContainer();
		editViewContainer.on('change','[name="conditionstype"]',function(e) {
			var advanceFilterContainer = jQuery('#advanceFilterContainer');
			var currentRadioButtonElement = jQuery(e.currentTarget);
			if(currentRadioButtonElement.hasClass('recreate')){
				if(currentRadioButtonElement.is(':checked')){
					advanceFilterContainer.removeClass('zeroOpacity');
					advanceFilterContainer.find('.conditionList').find('[name="columnname"]').find('optgroup:first option:first').attr('selected','selected').trigger('change');
				}
			} else {
				advanceFilterContainer.addClass('zeroOpacity');
			}
		});
	},
	
	addComponents : function() {
        this._super();
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	},
   
    registerEvents: function () {
        this.registerEventForShowModuleFilterCondition();
        this.registerFormSubmitEvent();
        this.registerEnableFilterOption();
		this.registerEventForScheduledWorkflow();
		this.registerEventForChangeInScheduledType();
        this.registerEventForChangeWorkflowState();
    }
});

//http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery
jQuery.fn.extend({
	insertAtCaret: function(myValue) {
		return this.each(function(i) {
			if (document.selection) {
				//For browsers like Internet Explorer
				this.focus();
				var sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			} else if (this.selectionStart || this.selectionStart == '0') {
				//For browsers like Firefox and Webkit based
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	}
});