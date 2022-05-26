/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Settings_LayoutEditor_Js', {
}, {
	updatedBlockSequence: {},
	reactiveFieldsList: [],
	updatedRelatedList: {'updated': [], 'deleted': []},
	removeModulesArray: false,
	inActiveFieldsList: false,
	updatedBlockFieldsList: [],
	updatedBlocksList: [],
	blockNamesList: [],
	headerFieldsCount: 0,
	maxNumberOfHeaderFields: 5,
	nameFields: [],
	headerFieldsMeta: {},
	getModuleName: function () {
		return 'LayoutEditor';
	},
	/**
	 * Function to set the removed modules array used in related list
	 */
	setRemovedModulesList: function () {
		var thisInstance = this;
		var relatedList = jQuery('#relatedTabOrder');
		var container = relatedList.find('.relatedTabModulesList');
		var removedArray = new Array();
		if (container.find('.RemovedModulesListArray').length > 0) {
			removedArray = JSON.parse(container.find('.RemovedModulesListArray').val());
		}
		thisInstance.removeModulesArray = removedArray;
	},
	/**
	 * Function to set the inactive fields list used to show the inactive fields
	 */
	setInactiveFieldsList: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		if (contents.find('.inActiveFieldsArray').length > 0) {
			thisInstance.inActiveFieldsList = JSON.parse(contents.find('.inActiveFieldsArray').val());
		}
	},
	/**
	 * Function to set the number of header fields
	 */
	setHeaderFieldsCount: function () {
		if (jQuery('#headerFieldsCount').length) {
			this.headerFieldsCount = parseInt(jQuery('#headerFieldsCount').val());
		}
	},
	/**
	 * Function to set name fields
	 */
	setNameFields: function () {
		if (jQuery('#nameFields').length) {
			this.nameFields = JSON.parse(jQuery('#nameFields').val());
		}
	},
	/**
	 * Function to set header fields meta
	 */
	setHeaderFieldsMeta: function () {
		if (jQuery('#headerFieldsMeta').length) {
			this.headerFieldsMeta = JSON.parse(jQuery('#headerFieldsMeta').val());
		}
	},
	/**
	 * Function to regiser the event to make the blocks sortable
	 */
	makeBlocksListSortable: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var table = contents.find('.blockSortable');
		contents.sortable({
			'containment': contents,
			'items': table,
			'revert': true,
			'tolerance': 'pointer',
			'cursor': 'move',
			'update': function (e, ui) {
				thisInstance.updateBlockSequence();
			}
		});
	},
	/**
	 * Function which will update block sequence
	 */
	updateBlockSequence: function () {
		var thisInstance = this;
		app.helper.showProgress();

		var sequence = JSON.stringify(thisInstance.updateBlocksListByOrder());
		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Block';
		params['mode'] = 'updateSequenceNumber';
		params['sequence'] = sequence;
		params['selectedModule'] = jQuery('#selectedModuleName').attr('value');

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					app.helper.showSuccessNotification({'message': app.vtranslate('JS_BLOCK_SEQUENCE_UPDATED')});
				} else {
					app.helper.showErrorNotification({'message': err.message});
				}
			});
	},
	/**
	 * Function which will arrange the sequence number of blocks
	 */
	updateBlocksListByOrder: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		contents.find('.blockSortable:visible').each(function (index, domElement) {
			var blockTable = jQuery(domElement);
			var blockId = blockTable.data('blockId');
			var actualBlockSequence = blockTable.data('sequence');
			var expectedBlockSequence = (index+1);

			if (expectedBlockSequence != actualBlockSequence) {
				blockTable.data('sequence', expectedBlockSequence);
			}
			thisInstance.updatedBlockSequence[blockId] = expectedBlockSequence;
		});
		return thisInstance.updatedBlockSequence;
	},
	/**
	 * Function to regiser the event to make the related modules sortable
	 */
	makeRelatedModuleSortable: function () {
		var thisInstance = this;
		var relatedModulesContainer = jQuery('#relatedTabOrder');
		var modulesList = relatedModulesContainer.find('li.relatedModule');
		relatedModulesContainer.sortable({
			'containment': relatedModulesContainer,
			'items': modulesList,
			'revert': true,
			'tolerance': 'pointer',
			'cursor': 'move',
			'update': function (e, ui) {
				thisInstance.showSaveButton();
			}
		});
	},
	/**
	 * Function which will enable the save button in realted tabs list
	 */
	showSaveButton: function () {
		app.helper.showAlertNotification({'message': app.vtranslate('JS_SAVE_MODULE_SEQUENCE')});
		var relatedList = jQuery('#relatedTabOrder');
		var saveButton = relatedList.find('.saveRelatedList');
		relatedList.find('.saveRelatedListContainer').removeClass('hide');
	},
	/**
	 * Function which will disable the save button in related tabs list
	 */
	disableSaveButton: function () {
		var relatedList = jQuery('#relatedTabOrder');
		var saveButton = relatedList.find('.saveRelatedList');
		relatedList.find('.saveRelatedListContainer').addClass('hide');
	},
	/**
	 * Function to register all the relatedList Events
	 */
	registerRelatedListEvents: function () {
		var thisInstance = this;
		var relatedList = jQuery('#relatedTabOrder');
		var container = relatedList.find('.relatedTabModulesList');
		var allModulesListArray = new Array();
		if (container.find('.ModulesListArray').length > 0) {
			allModulesListArray = JSON.parse(container.find('.ModulesListArray').val());
		}
			
		var ulEle = container.find('ul.relatedModulesList');
		var selectEle = container.find('[name="addToList"]');
		vtUtils.showSelect2ElementView(selectEle, {maximumSelectionSize: 1});
		selectEle.on('change', function () {
			var selectedVal = selectEle.val();
			var selectedOption = selectEle.find('option:selected');
			var moduleLabel = allModulesListArray[selectedVal];
			//remove the element if its already exists
			ulEle.find('.module_'+selectedVal[0]).remove();

			//append li element for the selected module
			var liEle = container.find('.moduleCopy').clone(true, true);
			//Not a custom relation
			if (!selectedOption.is('[data-relation-field-label]')) {
				liEle.find('.deleteRelationShip').remove();
			} else {
				liEle.find('.deleteRelationShip').attr('data-relation-field-label', selectedOption.data('relationFieldLabel'))
						.attr('data-relation-module-label', selectedOption.data('relationModuleLabel'));
			}
			liEle.data('relationId', selectedVal[0]).find('.moduleLabel').text(moduleLabel);
			liEle.find('.moduletranslatedLabel').text(selectedOption.data('moduleTranslatedLabel'));
			ulEle.append(liEle.removeClass('hide moduleCopy').addClass('relatedModule module_'+selectedVal[0]));
			thisInstance.makeRelatedModuleSortable();

			//remove that selected module from the select element
			selectEle.select2('data', []);
			selectEle.find('option[value="'+selectedVal[0]+'"]').remove();

			thisInstance.removeModulesArray.splice(thisInstance.removeModulesArray.indexOf(selectedVal[0]), 1);

			if (!selectEle.find('option').length) {
				jQuery('.hiddenModulesContainer').removeClass('show').addClass('hide');
			}
			thisInstance.showSaveButton();
		})

		//register the event to click on close the related module
		container.find('.close').one('click', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			thisInstance.showSaveButton();
			var liEle = currentTarget.closest('li.relatedModule');
			var relationId = liEle.data('relationId');
			var moduleLabel = liEle.find('.moduleLabel').text();
			liEle.fadeOut('slow').addClass('deleted');
			jQuery('.hiddenModulesContainer').addClass('show').removeClass('hide');
			selectEle.append('<option value="'+relationId+'" data-module-translated-label="'+moduleLabel+'">'+moduleLabel+'</option>');
		})

		//register click event for save related list button
		relatedList.off('click', '.saveRelatedList').on('click', '.saveRelatedList', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.attr('disabled') != 'disabled') {
				thisInstance.disableSaveButton();
				thisInstance.updatedRelatedList['deleted'] = [];
				for (var key in thisInstance.removeModulesArray) {
					thisInstance.updatedRelatedList['deleted'].push(thisInstance.removeModulesArray[key]);
				}
				thisInstance.saveRelatedListInfo();
			}
		});
		container.on('click', '.relationClickAdd', function (e) {
			jQuery('#addRelation').trigger('click');
		});
	},
	/**
	 * Function to save the updated information in related list
	 */
	saveRelatedListInfo: function () {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Relation';
		params['related_info'] = thisInstance.getUpdatedModulesInfo();
		params['sourceModule'] = jQuery('#selectedModuleName').val();

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					app.helper.showSuccessNotification({'message': app.vtranslate('JS_RELATED_INFO_SAVED')});
					aDeferred.resolve(data);
				} else {
					app.helper.showErrorNotification({'message': err.message});
					aDeferred.reject(err);
				}
			});
		return aDeferred.promise();
	},
	/**
	 * Function to get the updates happened with the related modules list
	 */
	getUpdatedModulesInfo: function () {
		var thisInstance = this;
		var relatedList = jQuery('#relatedTabOrder');
		var removedModulesList = relatedList.find('li.relatedModule').filter('.deleted');
		var updatedModulesList = relatedList.find('li.relatedModule').not('.deleted');
		thisInstance.updatedRelatedList['updated'] = [];

		//update deleted related modules list
		removedModulesList.each(function (index, domElement) {
			var relationId = jQuery(domElement).data('relationId');
			thisInstance.updatedRelatedList['deleted'].push(relationId);
		});
		//update the existing related modules list
		updatedModulesList.each(function (index, domElement) {
			var relationId = jQuery(domElement).data('relationId');
			thisInstance.updatedRelatedList['updated'].push(relationId);
		});
		return thisInstance.updatedRelatedList;
	},
	/**
	 * Function to regiser the event to make the fields sortable
	 */
	makeFieldsListSortable: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var table = contents.find('.editFieldsTable');
		table.find('ul[name=sortable1], ul[name=sortable2]').sortable({
			'containment': '#moduleBlocks',
			'cancel': 'li.dummyRow',
			'revert': true,
			'tolerance': 'pointer',
			'cursor': 'move',
			'connectWith': '.connectedSortable',
			'update': function (e, ui) {
				var currentField = ui['item'];
				thisInstance.showSaveFieldSequenceButton();
				thisInstance.createUpdatedBlocksList(currentField);
				// rearrange the older block fields
				if (ui.sender) {
					var olderBlock = ui.sender.closest('.editFieldsTable');
					thisInstance.reArrangeBlockFields(olderBlock);
				}
			}
		});
	},
	/**
	 * Function to show the save button of fieldSequence
	 */
	showSaveFieldSequenceButton: function () {
		var thisInstance = this;
		var layout = jQuery('#detailViewLayout');
		var saveButton = layout.find('.saveFieldSequence');
		if (saveButton.css('opacity') == '0') {
			thisInstance.updatedBlocksList = [];
			thisInstance.updatedBlockFieldsList = [];
			saveButton.css('opacity', '1');
			app.helper.showAlertNotification({'message': app.vtranslate('JS_SAVE_THE_CHANGES_TO_UPDATE_FIELD_SEQUENCE')})
		}
	},
	/**
	 * Function which will hide the saveFieldSequence button
	 */
	hideSaveFieldSequenceButton: function () {
		var layout = jQuery('#detailViewLayout');
		var saveButton = layout.find('.saveFieldSequence');
		saveButton.css('opacity', '0');
	},
	/**
	 * Function to create the blocks list which are updated while sorting
	 */
	createUpdatedBlocksList: function (currentField) {
		var thisInstance = this;
		var block = currentField.closest('.editFieldsTable');
		var updatedBlockId = block.data('blockId');
		if (jQuery.inArray(updatedBlockId, thisInstance.updatedBlocksList) == -1) {
			thisInstance.updatedBlocksList.push(updatedBlockId);
		}
		thisInstance.reArrangeBlockFields(block);
	},
	/**
	 * Function that rearranges fields in the block when the fields are moved
	 * @param <jQuery object> block
	 */
	reArrangeBlockFields: function (block) {
		// 1.get the containers, 2.compare the length, 3.if uneven then move the last element
		var leftSideContainer = block.find('ul[name=sortable1]');
		var rightSideContainer = block.find('ul[name=sortable2]');
		if (leftSideContainer.length < 1 && rightSideContainer.length < 1) {
			var leftSideContainer = block.find('ul[name=unSortable1]');
			var rightSideContainer = block.find('ul[name=unSortable2]');
		}
		var dummyRowElement = jQuery();
		if (leftSideContainer.find('li.dummyRow').length > 0) {
			dummyRowElement = leftSideContainer.find('li.dummyRow').detach();
		} else {
			dummyRowElement = rightSideContainer.find('li.dummyRow').detach();
		}
		if (leftSideContainer.children().length < rightSideContainer.children().length) {
			var lastElementInRightContainer = rightSideContainer.children(':last');
			leftSideContainer.append(lastElementInRightContainer);
		} else if (leftSideContainer.children().length > rightSideContainer.children().length+1) {	//greater than 1
			var lastElementInLeftContainer = leftSideContainer.children(':last');
			rightSideContainer.append(lastElementInLeftContainer);
		}
		if (rightSideContainer.children().length < leftSideContainer.children().length) {
			rightSideContainer.append(dummyRowElement);
		} else {
			leftSideContainer.append(dummyRowElement);
		}
	},
	/**
	 * Function to create the list of updated blocks with all the fields and their sequences
	 */
	createUpdatedBlockFieldsList: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');

		for (var index in thisInstance.updatedBlocksList) {
			var updatedBlockId = thisInstance.updatedBlocksList[index];
			var updatedBlock = contents.find('.block_'+updatedBlockId);
			var firstBlockSortFields = updatedBlock.find('ul[name=sortable1]');
			var editFields = firstBlockSortFields.find('.editFields');
			var expectedFieldSequence = 1;
			editFields.each(function (i, domElement) {
				var fieldEle = jQuery(domElement);
				var fieldId = fieldEle.data('fieldId');
				thisInstance.updatedBlockFieldsList.push({'fieldid': fieldId, 'sequence': expectedFieldSequence, 'block': updatedBlockId});
				expectedFieldSequence = expectedFieldSequence+2;
			});
			var secondBlockSortFields = updatedBlock.find('ul[name=sortable2]');
			var secondEditFields = secondBlockSortFields.find('.editFields');
			var sequenceValue = 2;
			secondEditFields.each(function (i, domElement) {
				var fieldEle = jQuery(domElement);
				var fieldId = fieldEle.data('fieldId');
				thisInstance.updatedBlockFieldsList.push({'fieldid': fieldId, 'sequence': sequenceValue, 'block': updatedBlockId});
				sequenceValue = sequenceValue+2;
			});
		}
	},
	/**
	 * Function to register click event for save button of fields sequence
	 */
	registerFieldSequenceSaveClick: function () {
		var thisInstance = this;
		var layout = jQuery('#detailViewLayout');
		layout.on('click', '.saveFieldSequence', function () {
			thisInstance.hideSaveFieldSequenceButton();
			thisInstance.createUpdatedBlockFieldsList();
			thisInstance.updateFieldSequence();
		});
	},
	/**
	 * Function will save the field sequences
	 */
	updateFieldSequence: function () {
		var thisInstance = this;
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Field';
		params['mode'] = 'move';
		params['updatedFields'] = thisInstance.updatedBlockFieldsList;
		params['selectedModule'] = jQuery('#selectedModuleName').attr('value');

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					app.helper.showSuccessNotification({'message': app.vtranslate('JS_FIELD_SEQUENCE_UPDATED')});
					window.location.reload();
				} else {
					app.helper.showErrorNotification({'message': err.message});
				}
			});
	},
	registerHeaderSummaryDependency: function (container, fieldContainer) {
		var thisInstance = this;
		container.find('input[name="headerfield"], input[name="summaryfield"]').on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			if (typeof currentElement.attr('readonly') != "undefined") {
				return;
			}
			var fieldName = container.find('[name="fieldname"]').val();
			var currentFlagName = currentElement.attr('name');
			var dependentFlagName = 'headerfield';
			if (currentFlagName === dependentFlagName) {
				dependentFlagName = 'summaryfield';
			}
			if (currentElement.is(':checked')) {
				container.find('input[type="checkbox"][name="'+dependentFlagName+'"]')
						.prop('checked', false).attr('readonly', 'readonly')
						.removeClass('cursorPointer').addClass('cursorPointerNotAllowed');
			} else {
				if (dependentFlagName === 'headerfield') {
					if (thisInstance.nameFields.indexOf(fieldName) === -1) {
						container.find('input[type="checkbox"][name="'+dependentFlagName+'"]')
								.removeAttr('readonly', 'readonly').removeClass('cursorPointerNotAllowed')
								.addClass('cursorPointer');
					}
				} else {
					container.find('input[type="checkbox"][name="'+dependentFlagName+'"]')
							.removeAttr('readonly', 'readonly').removeClass('cursorPointerNotAllowed')
							.addClass('cursorPointer');
				}
			}
		}).filter(':checked').trigger('change');
	},
	showFieldEditModel: function (data, blockId, fieldContainer) {
		var thisInstance = this;
		// to show validation message for select2 we need to add id attribute
		data.find('.relationModule').attr('id', 'relationModule');
		//register all select2 Elements
		vtUtils.showSelect2ElementView(data.find('select'));
		data.find('.fieldProperty').removeClass('hide');
		thisInstance.registerHeaderSummaryDependency(data, fieldContainer);
		data.find('input[name="mandatory"]').on('change', function (e) {
			var currentElement = jQuery(e.currentTarget);
			if (typeof currentElement.attr('readonly') != "undefined") {
				return;
			}
			if (currentElement.is(':checked')) {
				if (data.find('input[name="isquickcreatesupported"]').val()) {
					data.find('input[name="quickcreate"]').prop('checked', true).attr('readonly', 'readonly');
					data.find('input[name="quickcreate"]').removeClass('cursorPointer').addClass('cursorPointerNotAllowed');
				}
				data.find('input[name="presence"]').attr('checked', true).attr('readonly', 'readonly');
				data.find('#fieldPresence').bootstrapSwitch('toggleReadonly', true);
			} else {
				if (data.find('input[name="isquickcreatesupported"]').val()) {
					data.find('input[name="quickcreate"]').removeAttr('readonly');
					data.find('input[name="quickcreate"]').removeClass('cursorPointerNotAllowed').addClass('cursorPointer');
				}
				data.find('input[name="presence"]').removeAttr('readonly');
				data.find('#fieldPresence').bootstrapSwitch('toggleReadonly');
			}
		})
		data.find('input[type="checkbox"]').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			if (typeof element.attr('readonly') != "undefined") {
				e.preventDefault();
				return false;
			}
		})

		// this will handle the case of updating the default value in edit/create mode of field for picklist value type
		data.find('input[name="pickListValues"]').on('change', function (e) {
			var element = jQuery(e.currentTarget);
			var defaultField = data.find('[name="fieldDefaultValue"]');
			if (defaultField.length <= 0) {
				defaultField = data.find('[name="fieldDefaultValue[]"]');
			}
			var defaultOptions = defaultField.find('option').removeClass('doestNotExists').addClass('doesNotExists');
			var emptyOption = defaultOptions.filter('[value=" "]').removeClass('doesNotExists');
			var fieldOptions = element.val().split(',');
			var newOptions = '';

			for (var i in fieldOptions) {
				var fieldValue = fieldOptions[i];
				var fieldValueOption = defaultOptions.filter('[value="'+fieldValue+'"]');
				if (fieldValueOption.length <= 0) {
					newOptions += ' <option value="'+fieldValue+'">'+fieldValue+'</option> ';
				} else {
					fieldValueOption.removeClass('doesNotExists');
				}
			}
			defaultOptions.filter('.doesNotExists').remove();
			defaultField.append(newOptions);
			defaultField.trigger("change", false);
		});

		data.find('[name="defaultvalue"]').on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var defaultValueUi = currentTarget.closest('span').find('.defaultValueUi');
			var defaultField = defaultValueUi.find('[name="fieldDefaultValue"]');
			if (defaultField.length <= 0) {
				defaultField = defaultValueUi.find('[name="fieldDefaultValue[]"]');
			}
			if (currentTarget.is(':checked')) {
				defaultValueUi.removeClass('zeroOpacity');
				defaultField.removeAttr('disabled');
				if (defaultField.is('select')) {
					defaultField.select2('enable');
					defaultField.trigger("change", false);
				}
			} else {
				defaultField.attr('disabled', 'disabled');
				if (defaultField.is('select')) {
					defaultField.select2('disable');
				}
				//	defaultField.val('');
				defaultValueUi.addClass('zeroOpacity');
			}
		})

		data.find('[name="presence"]').on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.attr('readonly') == "readonly")
				return;
			if (currentTarget.is(":checked")) {
				data.find('.properties').show('500');
			} else {
				data.find('.properties').hide('500');
			}
		})

		var form = data.find('.createCustomFieldForm');
		form.attr('id', 'createFieldForm');
		var isEditMode = false;
		var formFieldId = form.find('input[name="fieldid"]').val();
		if (formFieldId.length > 0) {
			isEditMode = true;
		}
		var select2params = {tags: [], tokenSeparators: [","]}
		vtUtils.showSelect2ElementView(form.find('[name="pickListValues"]'), select2params);

		this.registerFieldTypeChangeEvent(form);
		data.find('.fieldTypesList').trigger('change');

		var params = {
			submitHandler: function (form) {
				var form = jQuery(form);
				var fieldTypeValue = jQuery('[name="fieldType"]', form).val();
				// In edit view we will not be having this element so we should skip validation
				if (!isEditMode) {
					if (fieldTypeValue == 'Decimal') {
						var decimalFieldLength = jQuery('[name="fieldLength"]', form);
						var decimalFieldLengthValue = parseInt(decimalFieldLength.val());
						var decimalFieldValue = form.find('[name="decimal"]').val();
						var fieldLength = parseInt(64) - parseInt(decimalFieldValue);

						if (decimalFieldLengthValue > fieldLength && !(fieldLength < 0) && fieldLength >= 59) {
							var message = app.vtranslate('JS_LENGTH_SHOULD_BE_LESS_THAN_EQUAL_TO')+' '+fieldLength;
							vtUtils.showValidationMessage(decimalFieldLength, message, {
								position: {
									my: 'bottom left',
									at: 'top left',
									container: form
								}
							});
							return false;
						} else {
							vtUtils.hideValidationMessage(decimalFieldLength);
						}
					}

					if (fieldTypeValue == 'Picklist' || fieldTypeValue == 'MultiSelectCombo') {
						var pickListValueElement = jQuery('#picklistUi', form);
						var pickLisValues = pickListValueElement.val();
						var select2Element = app.getSelect2ElementFromSelect(pickListValueElement);
						var pickListValuesArray = pickLisValues.split(',');
						var pickListValuesArraySize = pickListValuesArray.length;
						var specialChars = /[<\>\"\,\[\]\{\}]/;
						var showValidationParams = {
							position: {
								my: 'bottom left',
								at: 'top left',
								container: form
							}
						};
						for (var i = 0; i < pickListValuesArray.length; i++) {
							if (pickListValuesArray[i].trim() == '') {
								var errorMessage = app.vtranslate('JS_REQUIRED_FIELD');
								vtUtils.showValidationMessage(select2Element, errorMessage, showValidationParams);
								return false;
							}
							if (specialChars.test(pickListValuesArray[i])) {
								var message = app.vtranslate('JS_SPECIAL_CHARACTERS')+" < > \" , [ ] { } "+app.vtranslate('JS_NOT_ALLOWED');
								vtUtils.showValidationMessage(select2Element, message, showValidationParams);
								return false;
							}
						}
						var lowerCasedpickListValuesArray = jQuery.map(pickListValuesArray, function (item, index) {
							return item.toLowerCase();
						});
						var uniqueLowerCasedpickListValuesArray = jQuery.unique(lowerCasedpickListValuesArray);
						var uniqueLowerCasedpickListValuesArraySize = uniqueLowerCasedpickListValuesArray.length;
						var arrayDiffSize = pickListValuesArraySize - uniqueLowerCasedpickListValuesArraySize;
						if (arrayDiffSize > 0) {
							var select2Element = app.getSelect2ElementFromSelect(pickListValueElement);
							var message = app.vtranslate('JS_DUPLICATES_VALUES_FOUND');
							vtUtils.showValidationMessage(select2Element, message, {
								position: {
									my: 'bottom left',
									at: 'top left',
									container: form
								}
							});
							return false;
						}
					}
				}
				var saveButton = form.find(':submit');
				saveButton.attr('disabled', 'disabled');
				if (isEditMode) {
					var saveParams = form.serializeFormData();
					if ((typeof saveParams['fieldDefaultValue[]'] == 'undefined') && (typeof saveParams['fieldDefaultValue'] == 'undefined')) {
						saveParams['fieldDefaultValue'] = '';
					}
					thisInstance.saveFieldDetails(saveParams).then(function (result) {
						app.helper.hideModal();
						var response = result;
						if (response.presence) {
							thisInstance.setFieldDetails(result, fieldContainer);
						} else {
							var block = fieldContainer.closest('.editFieldsTable');
							fieldContainer.fadeOut('slow').remove();
							var fieldId = response.id;
							if (jQuery.isEmptyObject(thisInstance.inActiveFieldsList[blockId])) {
								if (thisInstance.inActiveFieldsList.length == '0') {
									thisInstance.inActiveFieldsList = {};
								}
								thisInstance.inActiveFieldsList[blockId] = {};
								thisInstance.inActiveFieldsList[blockId][fieldId] = response['label'];
							} else {
								thisInstance.inActiveFieldsList[blockId][fieldId] = response['label'];
							}
							thisInstance.reArrangeBlockFields(block);
						}
					}, function () {
						app.helper.hideProgress();
						saveButton.removeAttr('disabled');
						app.helper.showAlertNotification({
							'message': app.vtranslate('JS_MAXIMUM_HEADER_FIELDS_ALLOWED', thisInstance.maxNumberOfHeaderFields)
						});
					});
				} else {
					thisInstance.addCustomField(blockId, form).then(
						function (data) {
							var result = data;
							var params = {};
							if (data) {
								app.helper.hideModal();
								params['message'] = app.vtranslate('JS_CUSTOM_FIELD_ADDED', result['label']);
								app.helper.showSuccessNotification(params);
								var blockId = result['blockid'];
								var blockElement = jQuery('#block_'+blockId);
								var customFieldsCount = blockElement.data('customFieldsCount');
								blockElement.data('customFieldsCount', (customFieldsCount+1));
								thisInstance.showCustomField(result);
							}
						}, function (error) {
							app.helper.hideProgress();
							if (error) {
								var message = error.message;
								vtUtils.showValidationMessage(form.find('[name="fieldLabel"]'), message, {
									position: {
										my: 'bottom left',
										at: 'top left',
										container: form
									}
								});
							} else {
								app.helper.showAlertNotification({
									'message': app.vtranslate('JS_MAXIMUM_HEADER_FIELDS_ALLOWED', thisInstance.maxNumberOfHeaderFields)
								});
							}
							saveButton.removeAttr('disabled');
						}
					);
				}
			}
		}

		form.vtValidate(params);
	},
	/**
	 * Function to register click evnet add custom field button
	 */
	registerAddCustomFieldEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var isPopupShowing = false;
		contents.find('.addCustomField').click(function (e, params) {
			if (typeof params == 'undefined') {
				params = {};
			}
			// Added to prevent multiple clicks event
			if (isPopupShowing) {
				return false;
			}
			var blockElement = jQuery(e.currentTarget).closest('.editFieldsTable');
			var blockId = blockElement.data('blockId');
			var addFieldContainer = contents.find('.createFieldModal').clone(true, true);
			addFieldContainer.removeClass('hide');

			var modalParams = {
				cb: function (data) {
					isPopupShowing = false;
					if (params.fieldTypeSelected) {
						data.find('.fieldTypesList').val(params.fieldTypeSelected).trigger('change');
					}
					if (params.showBlock) {
						data.find('.blockControlGroup').removeClass('hide');
					}
					data.find('.blockList').find('option[value="'+blockId+'"]').attr('selected', 'selected');
					thisInstance.showFieldEditModel(data, blockId);
				}
			};
			isPopupShowing = true;
			app.helper.showModal(addFieldContainer, modalParams);
		});
	},
	/**
	 * Function to create the array of block names list
	 */
	setBlocksListArray: function (form) {
		var thisInstance = this;
		thisInstance.blockNamesList = [];
		var blocksListSelect = form.find('[name="beforeBlockId"]');
		blocksListSelect.find('option').each(function (index, ele) {
			var option = jQuery(ele);
			var label = option.data('label');
			thisInstance.blockNamesList.push(label);
		})
	},
	/**
	 * Function to save the custom field details
	 */
	addCustomField: function (blockId, form) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();

		var params = form.serializeFormData();
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Field';
		params['mode'] = 'add';
		params['sourceModule'] = jQuery('#selectedModuleName').val();
		params['fieldLength'] = parseInt(params['fieldLength']);
		if (params['decimal'])
			params['decimal'] = parseInt(params['decimal']);

		if (!this.isHeaderAllowed() && params.headerfield == true) {
			aDeferred.reject();
		} else {
			this.updateHeaderFieldMeta(params);
			app.request.post({'data': params}).then(
				function (err, data) {
					app.helper.hideProgress();
					if (err === null) {
						var fieldId = data.id;
						var headerFieldValue = data.isHeaderField ? 1 : 0;
						thisInstance.headerFieldsMeta[fieldId] = headerFieldValue;
						aDeferred.resolve(data);
					} else {
						aDeferred.reject(err);
					}
				});
		}
		return aDeferred.promise();
	},
	/**
	 * Function to register change event for fieldType while adding custom field
	 */
	registerFieldTypeChangeEvent: function (form, nameAttrsList) {
		if (typeof nameAttrsList == 'undefined') {
			var nameAttrsList = ['fieldDefaultValue'];
		}

		//special validators while adding new field
		var maxLengthValidator = 'maximumlength';
		var decimalValidator = 'range';

		//register the change event for field types
		form.find('[name="fieldType"]').on('change', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var lengthInput = form.find('[name="fieldLength"]');
			var selectedOption = currentTarget.find('option:selected');
			var maxlengthValue = selectedOption.data('maxlength');
			form.find('[name="fieldLabel"]').attr('data-rule-illegal', "true");

			if (typeof maxlengthValue === 'undefined')
				maxlengthValue = "255";

			//hide all the elements like length, decimal,picklist
			form.find('.supportedType').addClass('hide');
			form.find('[name="defaultvalue"]').removeAttr('readonly');

			if (selectedOption.data('lengthsupported')) {
				form.find('.lengthsupported').removeClass('hide');
				lengthInput.attr('data-rule-'+maxLengthValidator, maxlengthValue);
			}

			if (selectedOption.data('decimalsupported')) {
				var decimalFieldUi = form.find('.decimalsupported');
				decimalFieldUi.removeClass('hide');

				var decimalInput = decimalFieldUi.find('[name="decimal"]');
				var maxFloatingDigits = selectedOption.data('maxfloatingdigits');

				if (typeof maxFloatingDigits != "undefined") {
					decimalInput.attr('data-rule-'+decimalValidator, "[2,"+maxFloatingDigits+"]");
					decimalInput.attr('data-rule-WholeNumber', "true");
					if (selectedOption.val() == 'Currency') {
						var decimalFieldValue = maxFloatingDigits;
						maxlengthValue = maxlengthValue - decimalFieldValue;
						lengthInput.attr('data-rule-'+maxLengthValidator, maxlengthValue);
					} else {
						lengthInput.attr('data-rule-'+maxLengthValidator, maxlengthValue);
					}
				}

				if (selectedOption.data('decimalreadonly')) {
					decimalInput.val(maxFloatingDigits).attr('readonly', true);
				} else {
					decimalInput.removeAttr('readonly').val('');
				}

				if (selectedOption.data('decimalhidden')) {
					decimalInput.val(maxFloatingDigits);
					decimalInput.closest('.form-group').addClass('hide');
				} else {
					decimalInput.val('');
					decimalInput.closest('.form-group').removeClass('hide');
				}
			}

			if (selectedOption.data('predefinedvalueexists')) {
				var pickListUi = form.find('.preDefinedValueExists');
				pickListUi.removeClass('hide');
			}
			if (selectedOption.data('picklistoption')) {
				var pickListOption = form.find('.picklistOption');
				pickListOption.removeClass('hide');
			}

			if (selectedOption.data('isrelation')) {
				form.find('.relationModules').removeClass('hide');
				form.find('.relationType').removeClass('hide');

				form.find('[name="defaultvalue"]').attr('readonly', 'readonly').removeAttr('checked');
				form.find('.defaultValueUi').addClass('zeroOpacity');
			}

			if (form.find('input[type="checkbox"][name="defaultvalue"]').data('defaultDisabled') == "1") {
				form.find('input[type="checkbox"][name="defaultvalue"]').attr('readonly', 'readonly').removeAttr('checked');
			}

			for (var i in nameAttrsList) {
				var nameAttr = nameAttrsList[i];
				var fieldname = form.find('[name="fieldname"]').val();
				var fieldInfo;
				if (fieldname) {
					fieldInfo = uimeta.field.get(fieldname);
				}
				else {
					fieldInfo = uimeta.field.getNewFieldInfo();
				}

				var defaultValueUi = form.find('[name="'+nameAttr+'"]');

				if (defaultValueUi.length <= 0) {
					defaultValueUi = form.find('[name="'+nameAttr+'[]"]');
				}

				var data = jQuery.extend({}, fieldInfo);

				if (typeof data == 'undefined') {
					data = {};
				}

				data.name = nameAttr;
				data.value = defaultValueUi.val();
				if (currentTarget.val() == "MultiSelectCombo") {
					if (data.value != null && data.value.length > 0) {
						data.value = data.value.join('|##|');
					}
				}
				var type = selectedOption.val();
				switch (type) {
					case 'Decimal'	:	type = 'Double';	break;
					case 'Percent'	:	type = 'Percentage';break;
					case 'Checkbox'	:	type = 'Boolean';	break;
					case 'Text'		:	type = 'String';	break;
					case 'TextArea'	:	type = 'Text';		break;

					case 'MultiSelectCombo':type = 'Multipicklist';break;
				}
				data.type = type;

				if (typeof data.picklistvalues == "undefined")
					data.picklistvalues = {};

				if (type == "Multipicklist") {
					delete data.picklistvalues[" "];
				}

				if (type == 'Boolean') {
					if (form.find('input[type="checkbox"][name="' + nameAttr + '"]').is(":checked")) {
						data.value = '1';
					}
				}

				var defaultValueUiContainer = defaultValueUi.closest('.defaultValueUi');
				//based on the field model it will give the respective ui for the field
				var fieldModel = Vtiger_Field_Js.getInstance(data);

				var fieldUi = fieldModel.getUiTypeSpecificHtml();
				if (type == 'Text') {
					fieldUi.css({'width': '75%', 'resize': 'vertical'});
				} else if (type == 'Date' || type == 'Time') {
					fieldUi.css({'width': '82%'});
				} else if (type != 'Boolean') {
					fieldUi.css('width', '75%');
				}

				defaultValueUiContainer.html(fieldUi);
				defaultValueUi = form.find('[name="'+nameAttr+'"]');
				if (type == "Multipicklist") {
					defaultValueUi = form.find('[name="'+nameAttr+'[]"]');
				}

				//Handled Time field UI
				var timeField = defaultValueUiContainer.find('.timepicker-default');
				if (timeField.length > 0) {
					vtUtils.registerEventForTimeFields(timeField);
				}

				//Handled date field UI
				var dateField = defaultValueUiContainer.find('.dateField')
				if (dateField.length > 0) {
					vtUtils.registerEventForDateFields(dateField);
				}

				defaultValueUiContainer.find('[data-rule-required]').removeAttr('data-rule-required');
				if (defaultValueUi.is('select')) {
					//generating random id since validation engine needs it 
					defaultValueUi.attr('id', Math.floor((Math.random() * 10)+1));
					vtUtils.showSelect2ElementView(defaultValueUi);
				}
			}
		})
	},
	/**
	 * Function to add new custom field ui to the list
	 */
	showCustomField: function (result) {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var relatedBlock = contents.find('.block_'+result['blockid']);
		var fieldCopy = contents.find('.newCustomFieldCopy').clone(true, true);
		var fieldContainer = fieldCopy.find('div.marginLeftZero');
		fieldContainer.addClass('opacity editFields').attr('data-field-id', result['id']).attr('data-block-id', result['blockid']).attr('data-field-name', result['name']);
		fieldCopy.find('.deleteCustomField, .saveFieldDetails, .fieldProperties').attr('data-field-id', result['id']);
		fieldCopy.find(".fieldProperties .switch").attr('data-toggle', 'tooltip');

		fieldContainer.find('.fieldTypeLabel').html(result['fieldTypeLabel']);
		if (!result['customField']) {
			fieldCopy.find('.deleteCustomField').remove();
		}
		var block = relatedBlock.find('.blockFieldsList');
		var sortable1 = block.find('ul[name=sortable1]');
		var sortable2 = block.find('ul[name=sortable2]');

		if (sortable1.length < 1 && sortable2.length < 1) {
			var sortable1 = block.find('ul[name=unSortable1]');
			var sortable2 = block.find('ul[name=unSortable2]');
			fieldCopy.find('.dragImage').hide();
		}

		var firstSortableChildren = sortable1.children();
		var secondSortableChildren = sortable2.children();
		if (firstSortableChildren.filter('li.dummyRow').length > 0) {
			firstSortableChildren.filter('li.dummyRow').detach().appendTo(sortable2);
		} else {
			secondSortableChildren.filter('li.dummyRow').detach().appendTo(sortable1);
		}
		firstSortableChildren = sortable1.children();
		secondSortableChildren = sortable2.children();

		var length1 = firstSortableChildren.filter(':not(l1.dummyRow)').length;

		var length2 = secondSortableChildren.filter(':not(l1.dummyRow)').length;
		// Deciding where to add the new field
		if (length1 > length2) {
			sortable2.append(fieldCopy.removeClass('hide newCustomFieldCopy'));
		} else {
			sortable1.append(fieldCopy.removeClass('hide newCustomFieldCopy'));
		}
		var form = fieldCopy.find('.fieldProperties');
		thisInstance.setFieldDetails(result, fieldCopy);
		thisInstance.makeFieldsListSortable();
		vtUtils.enableTooltips();
	},
	/**
	 * Function to set the field info for edit field actions
	 */
	setFieldDetails: function (result, form) {
		var fieldlabelHolder = form.find('.fieldLabel');
		fieldlabelHolder.find('b').html(result['label']);
		var MandatorySymbol = fieldlabelHolder.find('.redColor');
		var mTitle, meTitle, qTitle, sTitle, hTitle;

		if (!result['mandatory']) {
			form.find('.mandatory').addClass('cursorPointer');
			form.find('.mandatory').addClass('disabled');
			MandatorySymbol.remove();
			mTitle = app.vtranslate('JS_MAKE_THIS_FIELD', app.vtranslate('JS_PROP_MANDATORY'));
		} else {
			form.find('.mandatory').addClass('cursorPointer');
			form.find('.mandatory').removeClass('disabled');
			if (MandatorySymbol.length <= 0) {
				fieldlabelHolder.append('<span class="redColor">*</span>');
			}

			mTitle = app.vtranslate('JS_NOT_MAKE_THIS_FIELD', app.vtranslate('JS_PROP_MANDATORY'));
		}

		if (!result['quickcreate']) {
			form.find('.quickCreate').addClass('disabled');
			qTitle = app.vtranslate('JS_SHOW_THIS_FIELD_IN', app.vtranslate('JS_QUICK_CREATE'));
		} else {
			form.find('.quickCreate').removeClass('disabled');
			qTitle = app.vtranslate('JS_HIDE_THIS_FIELD_IN', app.vtranslate('JS_QUICK_CREATE'));
		}

		if (result['isQuickCreateDisabled'] || result['mandatory']) {
			if (result['mandatory'])
				form.find('.quickCreate').addClass('cursorPointerNotAllowed');
			form.find('[data-name="quickcreate"]').attr('readonly', 'readonly');
		} else {
			form.find('.quickCreate').addClass('cursorPointer');
			form.find('[data-name="quickcreate"]').removeAttr('readonly');
		}

		if (!result['isSummaryField']) {
			form.find('.summary').addClass('disabled');
			sTitle = app.vtranslate('JS_SHOW_THIS_FIELD_IN', app.vtranslate('JS_KEY_FIELD'));
		} else {
			form.find('.summary').removeClass('disabled');
			sTitle = app.vtranslate('JS_HIDE_THIS_FIELD_IN', app.vtranslate('JS_KEY_FIELD'));
		}

		if (result['isSummaryFieldDisabled']) {
			form.find('.summary').addClass('cursorPointerNotAllowed');
			form.find('[data-name="summaryfield"]').attr('readonly', 'readonly');
		} else {
			form.find('.summary').addClass('cursorPointer');
			form.find('[data-name="summaryfield"]').removeAttr('readonly');
		}

		if (!result['isHeaderField']) {
			form.find('.header').addClass('disabled');
			hTitle = app.vtranslate('JS_SHOW_THIS_FIELD_IN', app.vtranslate('JS_DETAIL_HEADER'));
		} else {
			form.find('.header').removeClass('disabled');
			hTitle = app.vtranslate('JS_HIDE_THIS_FIELD_IN', app.vtranslate('JS_DETAIL_HEADER'));
		}

		if (result['isHeaderFieldDisabled']) {
			form.find('.header').addClass('cursorPointerNotAllowed');
			form.find('[data-name="headerfield"]').attr('readonly', 'readonly');
		} else {
			form.find('.header').addClass('cursorPointer');
			form.find('[data-name="headerfield"]').removeAttr('readonly');
		}

		if (!result['masseditable']) {
			form.find('.massEdit').addClass('disabled');
			meTitle = app.vtranslate('JS_SHOW_THIS_FIELD_IN', app.vtranslate('JS_MASS_EDIT'));
		} else {
			form.find('.massEdit').removeClass('disabled');
			meTitle = app.vtranslate('JS_HIDE_THIS_FIELD_IN', app.vtranslate('JS_MASS_EDIT'));
		}

		if (result['isMassEditDisabled']) {
			form.find('.massEdit').addClass('cursorPointerNotAllowed');
			form.find('[data-name="masseditable"]').attr('readonly', 'readonly');
		} else {
			form.find('.massEdit').addClass('cursorPointer');
			form.find('[data-name="masseditable"]').removeAttr('readonly');
		}

		if (!result['fieldDefaultValue']) {
			form.find('.defaultValue').addClass('disabled');
			var defaultValueIcon = jQuery('.defaultValueIcon').html();
			var html = '<div class="row defaultValueContent">';
			html += '<span>'+defaultValueIcon+'</span>&nbsp;';
			html += '<span>'+app.vtranslate('JS_DEFAULT_VALUE_NOT_SET')+'</span>';
			html += '</div>';

			form.find('.defaultValue').html(html);
		} else {
			form.find('.defaultValue').removeClass('disabled');
			var defaultValueIcon = jQuery('.defaultValueIcon').html();
			var defaultValue, isObject = true;
			try {
				defaultValue = JSON.parse(result['fieldDefaultValue']);
			} catch (e) {
				isObject = false;
			}

			if (isObject && typeof defaultValue == 'object') {
				var html = '';

				jQuery.each(defaultValue, function (fieldName, value) {
					fieldName = fieldName.toUpperCase();
					html += '<div class="row defaultValueContent">';
					html += '<span>'+defaultValueIcon+'</span>&nbsp;';
					if (value) {
						html += '<span data-defaultvalue-fieldname="'+fieldName+'" data-defaultvalue="'+value+'">'+app.vtranslate('JS_DEFAULT_VALUE')+app.vtranslate("JS_"+fieldName)+" : </span><span>"+value+'</span>';
					} else {
						html += '<span>'+app.vtranslate('JS_DEFAULT_VALUE_NOT_SET')+'</span>';
					}
					html += '</div>';
				});

				form.find('.defaultValue').html(html);
			} else {
				switch (result['type']) {
					case 'multipicklist':	defaultValue = result['fieldDefaultValue'];
											var valueArray = defaultValue.split('|##|');
											var selectedOptionsArray = [];
											for (var i = 0; i < valueArray.length; i++) {
												selectedOptionsArray.push(valueArray[i].trim());
											}
											defaultValue = selectedOptionsArray;
											break;
					case 'phone'		:
					case 'email'		:
					case 'url'			:	defaultValue = result['fieldDefaultValueRaw'];
											break;
					default				:	defaultValue = result['fieldDefaultValue'];
											break;
				}

				var html = '<div class="row defaultValueContent">';
				html += '<span>'+defaultValueIcon+'</span>&nbsp;';
				html += '<span>'+app.vtranslate('JS_DEFAULT_VALUE')+" : </span><span data-defaultvalue='"+defaultValue+"'>"+defaultValue+'</span>';
				html += '</div>';
				form.find('.defaultValue').html(html);
			}
		}

		//To update tooltip content
		form.find('.mandatory').attr('data-original-title', mTitle);
		form.find('.massEdit').attr('data-original-title', meTitle);
		form.find('.quickCreate').attr('data-original-title', qTitle);
		form.find('.summary').attr('data-original-title', sTitle);
		form.find('.header').attr('data-original-title', hTitle);
	},
	/**
	 * Function to register click event for add custom block button
	 */
	registerAddCustomBlockEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		contents.find('.addCustomBlock').click(function (e) {
			var addBlockContainer = contents.find('.addBlockModal').clone(true, true);

			var callBackFunction = function (data) {
				data.find('.addBlockModal').removeClass('hide');
				//register all select2 Elements

				vtUtils.showSelect2ElementView(data.find('select').addClass('select2'));

				var form = data.find('.addCustomBlockForm');
				thisInstance.setBlocksListArray(form);
				var fieldLabel = form.find('[name="label"]');

				var params = {
					submitHandler: function (form) {
						var form = jQuery(form);

						var blockLabelValue = jQuery.trim(fieldLabel.val());
						var specialChars = /[&\<\>\:\'\"\,\_\-]/;
						if (specialChars.test(blockLabelValue)) {
							var errorInfo = app.vtranslate('JS_SPECIAL_CHARACTERS')+" & < > ' \" : , _ - "+app.vtranslate('JS_NOT_ALLOWED');
							vtUtils.showValidationMessage(fieldLabel, errorInfo, {
								position: {
									my: 'bottom left',
									at: 'top left',
									container: form
								}
							});
							return false;
						}
						var formData = form.serializeFormData();
						if (jQuery.inArray(blockLabelValue, thisInstance.blockNamesList) == -1) {
							thisInstance.saveBlockDetails(form).then(
								function (data) {
									var params = {};
									if (data) {
										var result = data;
										thisInstance.displayNewCustomBlock(result);
										thisInstance.updateNewSequenceForBlocks(result['sequenceList']);
										thisInstance.appendNewBlockToBlocksList(result, form);
										thisInstance.makeFieldsListSortable();

										params['message'] = app.vtranslate('JS_CUSTOM_BLOCK_ADDED');
										app.helper.showSuccessNotification(params);
									} else {
										params['message'] = data['message'];
										app.helper.showErrorNotification(params);
									}
								}
							);
							app.helper.hideModal();
						} else {
							var result = app.vtranslate('JS_BLOCK_NAME_EXISTS');
							vtUtils.showValidationMessage(fieldLabel, result, {
								position: {
									my: 'bottom left',
									at: 'top left',
									container: form
								}
							});
							e.preventDefault();
							return;
						}

					}
				};

				form.vtValidate(params);
			};

			var modalParams = {
				cb: callBackFunction
			};

			app.helper.showModal(addBlockContainer, modalParams);
		});
	},
	/**
	 * Function to save the new custom block details
	 */
	saveBlockDetails: function (form) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();

		var params = form.serializeFormData();
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['sourceModule'] = jQuery('#selectedModuleName').val();
		params['action'] = 'Block';
		params['mode'] = 'save';

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					aDeferred.resolve(data);
				} else {
					aDeferred.reject(err);
				}
			});
		return aDeferred.promise();
	},
	/**
	 * Function used to display the new custom block ui after save
	 */
	displayNewCustomBlock: function (result) {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var beforeBlockId = result['beforeBlockId'];
		var beforeBlock = contents.find('.block_'+beforeBlockId);

		var newBlockCloneCopy = contents.find('.newCustomBlockCopy').clone(true, true);
		newBlockCloneCopy.attr('data-block-id', result['id']).find('.blockLabel').append(jQuery('<strong>'+result['label']+'</strong>'));
		newBlockCloneCopy.find('.blockVisibility').attr('data-block-id', result['id']);
		beforeBlock.after(newBlockCloneCopy.removeClass('hide newCustomBlockCopy').addClass('editFieldsTable block_'+result['id']).attr('id', 'block_'+result['id']));
		newBlockCloneCopy.find("#hiddenCollapseBlock").addClass('bootstrap-switch');
		newBlockCloneCopy.find("#hiddenCollapseBlock").attr('name', 'collapseBlock');
		jQuery("input[name='collapseBlock']").bootstrapSwitch();
		jQuery("input[name='collapseBlock']").bootstrapSwitch('handleWidth', '27px');
		jQuery("input[name='collapseBlock']").bootstrapSwitch('labelWidth', '25px');
		newBlockCloneCopy.find('.blockFieldsList').sortable({'connectWith': '.blockFieldsList'});
	},
	/**
	 * Function to update the sequence for all blocks after adding new Block
	 */
	updateNewSequenceForBlocks: function (sequenceList) {
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		jQuery.each(sequenceList, function (blockId, sequence) {
			contents.find('.block_'+blockId).attr('data-sequence', sequence);
		});
	},
	/**
	 * Function to update the block list with the new block label in the clone container
	 */
	appendNewBlockToBlocksList: function (result, form) {
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var hiddenAddBlockModel = contents.find('.addBlockModal');
		var blocksListSelect = hiddenAddBlockModel.find('[name="beforeBlockId"]');

		var optionHtml = '<option value="'+result['id']+'" data-label="'+result['label']+'" >'+result['label']+'</option>';
		var option = jQuery(optionHtml);
		option.attr('data-label', result['label']);
		//blocksListSelect.append(option);
		blocksListSelect.append(optionHtml);
		var hiddenAddFieldModel = contents.find('.createFieldModal');
		var blockListElement = hiddenAddFieldModel.find('.blockList');
		blockListElement.append(option);
	},
	/**
	 * Function to update the block list to remove the deleted custom block label in the clone container
	 */
	removeBlockFromBlocksList: function (blockId) {
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var hiddenAddBlockModel = contents.find('.addBlockModal');
		var blocksListSelect = hiddenAddBlockModel.find('[name="beforeBlockId"]');
		blocksListSelect.find('option[value="'+blockId+'"]').remove();
		var hiddenAddFieldModel = contents.find('.createFieldModal');
		var blockListElement = hiddenAddFieldModel.find('.blockList');
		blockListElement.find('option[value="'+blockId+'"]').remove();
	},
	/**
	 * Function to register the click event for inactive fields list
	 */
	registerInactiveFieldsEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		contents.on('click', 'button.inActiveFields', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var currentBlock = currentTarget.closest('.editFieldsTable');
			var blockId = currentBlock.data('blockId');
			//If there are no hidden fields, show pnotify
			if (jQuery.isEmptyObject(thisInstance.inActiveFieldsList[blockId])) {
				app.helper.showAlertNotification({'message': app.vtranslate('JS_NO_HIDDEN_FIELDS_EXISTS')});
			} else {
				var inActiveFieldsContainer = contents.find('.inactiveFieldsModal').clone(true, true);

				var callBackFunction = function (data) {
					data.find('.inactiveFieldsModal').removeClass('hide');
					thisInstance.reactiveFieldsList = [];
					var form = data.find('.inactiveFieldsForm');
					thisInstance.showHiddenFields(blockId, form);
					//register click event for reactivate button in the inactive fields modal
					data.find('[name="reactivateButton"]').click(function (e) {
						e.preventDefault();
						thisInstance.createReactivateFieldslist(blockId, form);
						thisInstance.reActivateHiddenFields(currentBlock);
						app.helper.hideModal();
					});
				};

				var params = {
					cb: callBackFunction
				};
				app.helper.showModal(inActiveFieldsContainer, params);
			}
		});

	},
	/**
	 * Function to show the list of inactive fields in the modal
	 */
	showHiddenFields: function (blockId, form) {
		var thisInstance = this;
		var fieldCount = 0;
		var curRow;
		jQuery.each(thisInstance.inActiveFieldsList[blockId], function (key, value) {
			if (fieldCount % 3 === 0) {
				curRow = $('<div class="row"></div>').appendTo(form.find('.inActiveList .list'));
				fieldCount = 0;
			}
			var inActiveField = jQuery('<div class="col-sm-4">\n\
											<div class="checkbox">\n\
												<label><input type="checkbox" class="inActiveField" value="'+key+'"/><span class="fieldLabel">'+value+'</span></label>\n\
											</div>\n\
										</div>');
			$(curRow).append(inActiveField);
			fieldCount++;
		});
	},
	/**
	 * Function to create the list of reactivate fields list
	 */
	createReactivateFieldslist: function (blockId, form) {
		var thisInstance = this;
		form.find('.inActiveField').each(function (index, domElement) {
			var element = jQuery(domElement);
			var fieldId = element.val();
			if (element.is(':checked')) {
				delete thisInstance.inActiveFieldsList[blockId][fieldId];
				thisInstance.reactiveFieldsList.push(fieldId);
			}
		});
	},
	/**
	 * Function to unHide the selected fields in the inactive fields modal
	 */
	reActivateHiddenFields: function (currentBlock) {
		var thisInstance = this;
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Field';
		params['mode'] = 'unHide';
		params['selectedModule'] = jQuery('#selectedModuleName').val();
		params['blockId'] = currentBlock.data('blockId');
		params['fieldIdList'] = JSON.stringify(thisInstance.reactiveFieldsList);

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					for (var index in data) {
						thisInstance.showCustomField(data[index]);
					}
					app.helper.showSuccessNotification({'message': app.vtranslate('JS_SELECTED_FIELDS_REACTIVATED')})
				}

			});
	},
	/**
	 * Function to register the click event for delete custom block
	 */
	registerDeleteCustomBlockEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var table = contents.find('.editFieldsTable');
		contents.on('click', 'button.deleteCustomBlock', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var table = currentTarget.closest('div.editFieldsTable');
			var blockId = table.data('blockId');

			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			app.helper.showConfirmationBox({'message': message}).then(
				function (data) {
					thisInstance.deleteCustomBlock(blockId);
				});
		});
	},
	/**
	 * Function to delete the custom block
	 */
	deleteCustomBlock: function (blockId) {
		var thisInstance = this;
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Block';
		params['mode'] = 'delete';
		params['blockid'] = blockId;

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				var params = {};
				if (err === null) {
					thisInstance.removeDeletedBlock(blockId);
					thisInstance.removeBlockFromBlocksList(blockId);
					params['message'] = app.vtranslate('JS_CUSTOM_BLOCK_DELETED');
					app.helper.showSuccessNotification(params);
				} else {
					params['message'] = err.message;
					app.helper.showErrorNotification(params);
				}
			});
	},
	/**
	 * Function to remove the deleted custom block from the ui
	 */
	removeDeletedBlock: function (blockId) {
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var deletedTable = contents.find('.block_'+blockId);
		deletedTable.fadeOut('slow').remove();
	},
	/**
	 * Function to register the click event for delete custom field
	 */
	registerDeleteCustomFieldEvent: function (contents) {
		var thisInstance = this;
		if (typeof contents == 'undefined') {
			contents = jQuery('#layoutEditorContainer').find('.contents');
		}
		contents.find('a.deleteCustomField').click(function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var fieldId = currentTarget.data('fieldId');
			var message = app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE');
			if (currentTarget.data('oneOneRelationship') == "1") {
				message = app.vtranslate('JS_ONE_ONE_RELATION_FIELD_DELETE', currentTarget.data('currentFieldLabel'), currentTarget.data('currentModuleLabel'),
						currentTarget.data('relationFieldLabel'), currentTarget.data('relationModuleLabel'));
			}
			else if (currentTarget.data('relationshipField') == "1") {
				message = app.vtranslate('JS_TAB_FIELD_DELETION', currentTarget.data('relationFieldLabel'), currentTarget.data('relationModuleLabel')
						, currentTarget.data('currentTabLabel'), currentTarget.data('currentModuleLabel'));
			}

			app.helper.showConfirmationBox({'title': app.vtranslate('LBL_WARNING'),
				'message'	: message,
				buttons		:{
								cancel	: {label: 'No', className: 'btn-default confirm-box-btn-pad pull-right'},
								confirm	: {label: app.vtranslate('JS_FIELD_DELETE_CONFIRMATION'), className: 'confirm-box-ok confirm-box-btn-pad btn-primary'}
							 }
					}).then(function (data) {
						thisInstance.deleteCustomField(fieldId).then(
							function (data) {
								var field = currentTarget.closest('li');
								var blockId = field.find('.editFields').data('blockId');
								field.fadeOut('slow').remove();
								var block = jQuery('#block_'+blockId);
								var customFieldsCount = block.data('customFieldsCount');
								block.data('customFieldsCount', (customFieldsCount - 1));
								thisInstance.reArrangeBlockFields(block);
								app.helper.showSuccessNotification({'message': app.vtranslate('JS_CUSTOM_FIELD_DELETED')});
							}, function (error, err) {
							});
					});
		});
	},
	/**
	 * Function to delete the custom field
	 */
	deleteCustomField: function (fieldId) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Field';
		params['mode'] = 'delete';
		params['fieldid'] = fieldId;

		app.request.post({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					if (thisInstance.headerFieldsMeta[fieldId] == 1) {
						thisInstance.headerFieldsCount--;
						thisInstance.headerFieldsMeta[fieldId] = 0;
					}
					aDeferred.resolve(data);
				} else {
					aDeferred.reject();
				}
			});
		return aDeferred.promise();
	},
	updateHeaderFieldMeta: function (params) {
		if (params.hasOwnProperty('headerfield')) {
			if (!params.hasOwnProperty('fieldid')) {
				console.info("params must contain field id : ", params);
			} else {
				var fieldId = params.fieldid;
				var prevHeaderFlagValue = 0;
				if (this.headerFieldsMeta.hasOwnProperty(fieldId)) {
					prevHeaderFlagValue = this.headerFieldsMeta[fieldId];
				}
				if (fieldId == '') {
					prevHeaderFlagValue = 0;
				}
				var curHeaderFlagValue = params.headerfield;

				if (curHeaderFlagValue != prevHeaderFlagValue) {
					if (curHeaderFlagValue == 1) {
						this.headerFieldsCount++;
					} else {
						this.headerFieldsCount--;
					}
				}
				if (fieldId != '') {
					this.headerFieldsMeta[fieldId] = curHeaderFlagValue;
				}
			}
		}
	},
	/**
	 * Function to save all the field details which are changed
	 */
	saveFieldDetails: function (params) {
		var aDeferred = jQuery.Deferred();
		if (typeof params == 'undefined') {
			params = {};
		}

		app.helper.showProgress();

		params['module'] = this.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['action'] = 'Field';
		params['mode'] = 'save';
		params['sourceModule'] = jQuery('#selectedModuleName').val();

		var fieldId = params['fieldid'];
		if (!this.isHeaderAllowed() && params.headerfield == true && this.headerFieldsMeta[fieldId] !== 1) {
			aDeferred.reject();
		} else {
			this.updateHeaderFieldMeta(params);
			app.request.post({'data': params}).then(
				function (err, data) {
					app.helper.hideProgress();
					if (err === null) {
						var params = {};
						params['message'] = app.vtranslate('JS_FIELD_DETAILS_SAVED');
						app.helper.showSuccessNotification(params);
						aDeferred.resolve(data);
					} else {
						aDeferred.reject();
					}
				});
		}
		return aDeferred.promise();
	},
	/**
	 * Function to register the cahnge event for mandatory & default checkboxes in edit field details
	 */
	registerFieldDetailsChange: function (contents) {
		if (typeof contents == 'undefined') {
			contents = jQuery('#layoutEditorContainer').find('.contents');
		}
		contents.on('change', '[name="mandatory"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.attr('readonly') != 'readonly') {
				var form = currentTarget.closest('.fieldDetailsForm');
				var quickcreateEle = form.find('[name="quickcreate"]').filter(':checkbox').not('.optionDisabled');
				var presenceEle = form.find('[name="presence"]').filter(':checkbox').not('.optionDisabled');
				if (currentTarget.is(':checked')) {
					quickcreateEle.attr('checked', true).attr('readonly', 'readonly');
					presenceEle.attr('checked', true).attr('readonly', 'readonly');
				} else {
					quickcreateEle.removeAttr('readonly');
					presenceEle.removeAttr('readonly');
				}
			}
		})

		contents.on('change', '[name="defaultvalue"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var defaultValueUi = currentTarget.closest('span').find('.defaultValueUi');
			var defaultField = defaultValueUi.find('[name="fieldDefaultValue"]');
			if (currentTarget.is(':checked')) {
				defaultValueUi.removeClass('zeroOpacity');
				defaultField.removeAttr('disabled');
				if (defaultField.is('select')) {
					defaultField.trigger("change", false);
				}
			} else {
				defaultField.attr('disabled', 'disabled');
				//	defaultField.val('');
				defaultValueUi.addClass('zeroOpacity');
			}
		})

	},
	/**
	 * Function to register the click event for related modules list tab
	 */
	triggerRelatedModulesTabClickEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		var relatedContainer = contents.find('#relatedTabOrder');
		var relatedTab = contents.find('.relatedListTab');

		relatedTab.click(function (e) {
			thisInstance.showRelatedTabModulesList(relatedContainer);
			var mode = jQuery(e.currentTarget).find('a').data('mode');
			jQuery('.selectedMode').val(mode);
		});
	},
	/**
	 * Function to show the related tab modules list in the tab
	 */
	showRelatedTabModulesList: function (relatedContainer, extraParams) {
		var thisInstance = this;
		if (typeof extraParams == "undefined") {
			extraParams = {};
		}
		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['sourceModule'] = jQuery('#selectedModuleName').val();
		params['view'] = 'Index';
		params['mode'] = 'showRelatedListLayout';
		params['originModule'] = app.getModuleName();
		params = jQuery.extend(params, extraParams);
		app.helper.showProgress();

		app.request.pjax({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					relatedContainer.html(data);
					if (relatedContainer.find('.relatedTabModulesList').length > 0) {
						thisInstance.makeRelatedModuleSortable();
						thisInstance.registerRelatedListEvents();
						thisInstance.setRemovedModulesList();
					}
				}
			});
	},
	/**
	 * Function to get the respective module layout editor through pjax
	 */
	getModuleLayoutEditor: function (selectedModule) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		app.helper.showProgress();

		var params = {};
		params['module'] = thisInstance.getModuleName();
		params['parent'] = app.getParentModuleName();
		params['view'] = 'Index';
		params['sourceModule'] = selectedModule;
		params['showFullContents'] = true;
		params['mode'] = jQuery('.selectedMode').val();

		app.request.pjax({'data': params}).then(
			function (err, data) {
				app.helper.hideProgress();
				if (err === null) {
					aDeferred.resolve(data);
				} else {
					aDeferred.reject();
				}
			});
		return aDeferred.promise();
	},
	getSelectedModuleName: function () {
		return jQuery('#selectedModuleName').val();
	},
	/**
	 * Function to register the change event for layout editor modules list
	 */
	registerModulesChangeEvent: function () {
		var thisInstance = this;
		var container = jQuery('#layoutEditorContainer');
		var contentsDiv = container.closest('.settingsPageDiv');

		vtUtils.showSelect2ElementView(container.find('[name="layoutEditorModules"]'));

		container.on('change', '[name="layoutEditorModules"]', function (e) {
			var currentTarget = jQuery(e.currentTarget);
			var selectedModule = currentTarget.val();

			if (selectedModule == '') {
				return false;
			}

			thisInstance.getModuleLayoutEditor(selectedModule).then(
				function (data) {
					contentsDiv.html(data);
					thisInstance.fieldListTabClicked = false;
					thisInstance.registerEvents();
				}
			);
		});

	},
	registerEditFieldDetailsClick: function (contents) {
		var thisInstance = this;
		if (typeof contents == 'undefined') {
			contents = jQuery('#layoutEditorContainer').find('.contents');
		}
		contents.on('click', '.editFieldDetails', function (e) {
			var element = jQuery(e.currentTarget);
			var container = element.closest('li');
			var fieldId = container.find('.editFields').data('fieldId');
			var blockId = element.closest('.editFieldsTable').data('blockId');
			app.helper.showProgress();

			var params = {};
			params['module'] = thisInstance.getModuleName();
			params['parent'] = app.getParentModuleName();
			params['view'] = 'Index';
			params['mode'] = 'showFieldEdit';
			params['fieldid'] = fieldId;
			params['sourceModule'] = jQuery('#selectedModuleName').val();

			app.request.post({'data': params}).then(
				function (err, data) {
					app.helper.hideProgress();
					if (err === null) {
						var params = {
							cb: function (data) {
								thisInstance.showFieldEditModel(data, blockId, container);
								data.find('[name="fieldType"]').trigger('change');
								jQuery('#fieldPresence').bootstrapSwitch();
								jQuery('#fieldPresence').bootstrapSwitch('handleWidth', '27px');
								jQuery('#fieldPresence').bootstrapSwitch('labelWidth', '25px');

								jQuery('#fieldPresence').on('switchChange.bootstrapSwitch', function (e) {
									jQuery('.fieldProperty').toggleClass('hide');
								});
							}
						};

						app.helper.showModal(data, params);
					}
				});
		});
	},
	registerEventForCollapseBlock: function () {
		jQuery('#moduleBlocks').on('switchChange.bootstrapSwitch', "input[name='collapseBlock']", function (e) {
			var currentElement = jQuery(e.currentTarget);
			if (currentElement.val() == 1) {
				currentElement.attr('value', 0);
			} else {
				currentElement.attr('value', 1);
			}

			var moduleName = app.getModuleName();
			if (moduleName != 'LayoutEditor') {
				moduleName = 'LayoutEditor';
			}

			var params = {
				module: moduleName,
				parent: app.getParentModuleName(),
				sourceModule: jQuery('#selectedModuleName').val(),
				action: 'Block',
				mode: 'save',
				blockid: currentElement.data('blockId'),
				display_status: currentElement.val()
			}

			app.request.post({data: params}).then(function (error, data) {
				if (data) {
					app.helper.showSuccessNotification({
						message: app.vtranslate('JS_STATUS_CHANGED_SUCCESSFULLY')
					});
				}
			});
		});
	},
	/**
	 * Function to register all the events for blocks
	 */
	registerBlockEvents: function () {
		var thisInstance = this;
		thisInstance.makeBlocksListSortable();
		thisInstance.registerAddCustomFieldEvent();
		thisInstance.registerEventForCollapseBlock();
		thisInstance.registerInactiveFieldsEvent();
		thisInstance.registerDeleteCustomBlockEvent();
	},
	/**
	 * Function to register all the events for fields
	 */
	registerFieldEvents: function (contents) {
		var thisInstance = this;
		if (typeof contents == 'undefined') {
			contents = jQuery('#layoutEditorContainer').find('.contents');
		}
		vtUtils.registerEventForDateFields(contents);
		vtUtils.registerEventForTimeFields(contents);
		vtUtils.applyFieldElementsView(contents);

		thisInstance.makeFieldsListSortable();
		thisInstance.registerDeleteCustomFieldEvent(contents);
		thisInstance.registerFieldDetailsChange(contents);
		thisInstance.registerEditFieldDetailsClick(contents);

		contents.find(':checkbox').change(function (e) {
			var currentTarget = jQuery(e.currentTarget);
			if (currentTarget.attr('readonly') == 'readonly') {
				var status = jQuery(e.currentTarget).is(':checked');
				if (!status) {
					jQuery(e.currentTarget).attr('checked', 'checked')
				} else {
					jQuery(e.currentTarget).removeAttr('checked');
				}
				e.preventDefault();
			}
		});
	},
	/*
	 * Function to add clickoutside event on the element - By using outside events plugin
	 * @params element---On which element you want to apply the click outside event
	 * @params callbackFunction---This function will contain the actions triggered after clickoutside event
	 */
	addClickOutSideEvent: function (element, callbackFunction) {
		element.one('clickoutside', callbackFunction);
	},
	isHeaderAllowed: function () {
		return this.headerFieldsCount < this.maxNumberOfHeaderFields;
	},
	/**
	 * Function which will register events to enable or disable field properties like mandatory, Quickcreate, massedit, Summary View
	 */
	registerSwitchActionOnFieldProperties: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		contents.on('click', '.fieldProperties .switch', function (e) {
			var element = jQuery(e.currentTarget);
			if (typeof element.find('i').attr('readonly') != "undefined" ||
					typeof element.find('img').attr('readonly') != "undefined") {
				return;
			}
			var fieldPropertiesHolder = element.closest('.fieldProperties');
			var fieldContatiner = fieldPropertiesHolder.closest('li');
			var fieldId = fieldPropertiesHolder.data('fieldId');
			var fieldPropertyName = element.find('i').data('name');
			var valueToUpdate = element.find('i').data('disable-value');
			var fieldName = fieldContatiner.find('[data-field-name]').data('fieldName');

			if (typeof valueToUpdate === 'undefined') {
				valueToUpdate = element.find('img').data('disable-value');
			}

			if (typeof fieldPropertyName === 'undefined') {
				fieldPropertyName = element.find('img').data('name');
			}

			if (element.hasClass('disabled')) {
				valueToUpdate = element.find('i').data('enableValue');
				if (typeof valueToUpdate == 'undefined')
					valueToUpdate = element.find('img').data('enable-value');
			}

			if (fieldPropertyName === 'headerfield') {
				//name fields are header enabled by default
				if (thisInstance.nameFields.indexOf(fieldName) !== -1) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_NAME_FIELDS_APPEAR_IN_HEADER_BY_DEFAULT')
					});
					return;
				}
			}

			if (fieldPropertyName === 'summaryfield' || fieldPropertyName === 'headerfield') {
				//Field can either be summary or header enabled
				var dependentFieldPropertySelector = fieldPropertyName === 'summaryfield' ? '.header' : '.summary';
				var dependentPropertyElement = jQuery(dependentFieldPropertySelector, fieldPropertiesHolder);
				if (!dependentPropertyElement.hasClass('disabled')) {
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_FIELD_CAN_EITHER_BE_HEADER_OR_SUMMARY_ENABLED')
					});
					return;
				}
			}
			app.helper.showProgress();

			var params = {};
			params[fieldPropertyName] = valueToUpdate;
			if (fieldPropertyName == 'mandatory') {
				//enable quick create if it is mandatory
				params['quickcreate'] = '2';
			}
			params['fieldid'] = fieldId;
			params['fieldname'] = fieldName;

			thisInstance.saveFieldDetails(params).then(
				function (data) {
					app.helper.hideProgress();
					thisInstance.setFieldDetails(data, fieldContatiner);
				},
				function () {
					app.helper.hideProgress();
					app.helper.showAlertNotification({
						'message': app.vtranslate('JS_MAXIMUM_HEADER_FIELDS_ALLOWED', thisInstance.maxNumberOfHeaderFields)
					});
				});
		});
	},
	registerAddCustomField: function () {
		jQuery(".dummyRow .addButton").on('click', function (e) {
			var target = jQuery(e.currentTarget);
			var currentDummyRow = target.closest('.dummyRow');
			var addCustomFieldBtn = currentDummyRow.closest('.editFieldsTable').find('.addCustomField');
			addCustomFieldBtn.trigger('click');
		});
	},
	/*
	 * Function which will disable or enable the field or tab labels depending on the field type that is selected
	 */
	disableOrEnableRealtionFieldTabLabels: function (container) {
		var relationTypeEleSelected = container.find('.relationImages img.selected');
		var fieldsToEnable = relationTypeEleSelected.data('supportedFieldToEnable');
		container.find('.relationFieldTabLabelHolders').addClass('hide');
		var relModule = container.find('[name="relatedModule"]').val();
		for (var index in fieldsToEnable) {
			var fieldNameAttr = fieldsToEnable[index];
			if ((relModule == 'Calendar' || relModule == 'Documents')
					&& fieldNameAttr == 'tabInRelated') {
				continue;
			}

			container.find('[name="'+fieldNameAttr+'"]').closest('.form-group').removeClass('hide');
		}
	},

	fieldListTabClicked: false,
	triggerFieldListTabClickEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');
		contents.find('.detailViewTab').click(function (e) {
			var detailViewLayout = contents.find('#detailViewLayout');
			thisInstance.showFieldsListUI(detailViewLayout, e).then(function (data) {
				if (!thisInstance.fieldListTabClicked) {
					thisInstance.registerBlockEvents();
					thisInstance.registerFieldEvents();
					thisInstance.setInactiveFieldsList();
					thisInstance.setHeaderFieldsCount();
					thisInstance.setHeaderFieldsMeta();
					thisInstance.setNameFields();
					thisInstance.registerAddCustomBlockEvent();
					thisInstance.registerFieldSequenceSaveClick();
					jQuery("input[name='collapseBlock']").bootstrapSwitch();
					jQuery("input[name='collapseBlock']").bootstrapSwitch('handleWidth', '27px');
					jQuery("input[name='collapseBlock']").bootstrapSwitch('labelWidth', '25px');
					thisInstance.registerSwitchActionOnFieldProperties();
					thisInstance.registerAddCustomField();
					app.helper.showVerticalScroll(jQuery('.addFieldTypes'), {'setHeight': '350px'});
					vtUtils.enableTooltips();
					thisInstance.fieldListTabClicked = true;
				}
			});
		});
	},
	showFieldsListUI: function (detailViewLayout, e) {
		var aDeferred = jQuery.Deferred();
		var fieldUiContainer = detailViewLayout.find('.fieldsListContainer');

		var selectedTab = jQuery(e.currentTarget).find('a');
		var mode = selectedTab.data('mode');
		var url = selectedTab.data('url')+'&sourceModule='+jQuery('#selectedModuleName').val()+'&mode='+mode;
		jQuery('.selectedMode').val(mode);

		if (fieldUiContainer.length == 0) {
			app.helper.showProgress();
			app.request.pjax({'url': url}).then(function (error, data) {
				if (error === null) {
					app.helper.hideProgress();
					detailViewLayout.html(data);
					aDeferred.resolve(detailViewLayout);
				} else {
					aDeferred.reject(error);
				}
			});
		} else {
			window.history.pushState('fieldUiContainer', '', url);
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	triggerDuplicationTabClickEvent: function () {
		var thisInstance = this;
		var contents = jQuery('#layoutEditorContainer').find('.contents');

		contents.find('.duplicationTab').click(function (e) {
			var duplicationContainer = contents.find('#duplicationContainer');
			thisInstance.showDuplicationHandlingUI(duplicationContainer, e).then(function (data) {
				var form = jQuery('.duplicateHandlingForm');
				var duplicateHandlingContainer = form.find('.duplicateHandlingContainer');

				var dupliCheckEle = form.find('.duplicateCheck');
				if (dupliCheckEle.length > 0) {
					if (dupliCheckEle.data('currentRule') == 1) {
						dupliCheckEle.bootstrapSwitch('state', false, true);
						duplicateHandlingContainer.removeClass('show').addClass('hide');
					} else {
						dupliCheckEle.bootstrapSwitch('state', true, true);
						duplicateHandlingContainer.removeClass('hide').addClass('show');
					}
					dupliCheckEle.bootstrapSwitch('handleWidth', '43px').bootstrapSwitch('labelWidth', '43px').bootstrapSwitch('size', '86px');
				}

				var fieldsList = form.find('#fieldsList');
				form.off('switchChange.bootstrapSwitch');
				form.on('switchChange.bootstrapSwitch', '.duplicateCheck', function (e, state) {
					if (state == true) {
						duplicateHandlingContainer.removeClass('hide').addClass('show');
						fieldsList.removeAttr('data-validation-engine').attr('data-validation-engine', 'validate[required]');
						form.find('.rule').val('0');
					} else {
						duplicateHandlingContainer.removeClass('show').addClass('hide');
						fieldsList.removeAttr('data-validation-engine');
						fieldsList.val('').trigger('liszt:updated').trigger('change', false);
						form.find('.formFooter').removeClass('show').addClass('hide');
						form.find('.rule').val('1');
						if (dupliCheckEle.data('currentRule') != '1') {
							form.submit();
						}
					}
				});

				form.find('select').on('change', function () {
					form.find('.formFooter').addClass('show').removeClass('hide');
				});

				form.find('.cancelLink').on('click', function () {
					duplicationContainer.html('');
					contents.find('.duplicationTab').trigger('click');
				});
				vtUtils.showSelect2ElementView(form.find('select').addClass('select2'), {maximumSelectionSize: 3});
				vtUtils.enableTooltips();

				var params = {
					submitHandler: function (form) {
						var form = jQuery(form);
						var params = form.serializeFormData();
						if ((typeof params['fieldIdsList[]'] == 'undefined') && (typeof params['fieldIdsList'] == 'undefined')) {
							params['fieldIdsList'] = '';
						}

						app.helper.showProgress();
						app.request.post({'data': params}).then(function (error, data) {
							app.helper.hideProgress();
							if (error == null) {
								var message = app.vtranslate('JS_DUPLICATE_HANDLING_SUCCESS_MESSAGE');
								if (params.rule == 1) {
									message = app.vtranslate('JS_DUPLICATE_CHECK_DISABLED');
								}
								app.helper.showSuccessNotification({'message': message});
								dupliCheckEle.data('currentRule', params.rule);
								form.find('.formFooter').removeClass('show').addClass('hide');
							} else {
								app.helper.showErrorNotification({'message': app.vtranslate('JS_DUPLICATE_HANDLING_FAILURE_MESSAGE')});
							}
						});
						return false;
					}
				}
				form.vtValidate(params);
			});
		});
	},
	showDuplicationHandlingUI: function (duplicationContainer, e) {
		var aDeferred = jQuery.Deferred();
		var duplicateUiContainer = duplicationContainer.find('.duplicateHandlingDiv');

		var selectedTab = jQuery(e.currentTarget).find('a');
		var mode = selectedTab.data('mode');
		var url = selectedTab.data('url')+'&sourceModule='+jQuery('#selectedModuleName').val()+'&mode='+mode;
		jQuery('.selectedMode').val(mode);

		if (duplicateUiContainer.length == 0) {
			app.helper.showProgress();
			app.request.pjax({'url': url}).then(function (error, data) {
				if (error === null) {
					app.helper.hideProgress();
					duplicationContainer.html(data);
					aDeferred.resolve(duplicationContainer);
				} else {
					aDeferred.reject(error);
				}
			});
		} else {
			window.history.pushState('duplicateUiContainer', '', url);
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	/**
	 * register events for layout editor
	 */
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.registerModulesChangeEvent();
		thisInstance.triggerFieldListTabClickEvent();
		thisInstance.triggerRelatedModulesTabClickEvent();
		thisInstance.triggerDuplicationTabClickEvent();

		var selectedTab = jQuery('.selectedTab').val();
		jQuery('#layoutEditorContainer').find('.contents').find('.'+selectedTab).trigger('click');
	}
});

Settings_LayoutEditor_Js('Settings_LayoutEditor_Index_Js', {}, {
	init: function () {
		this.addComponents();
	},
	addComponents: function () {
		this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
	}
});
