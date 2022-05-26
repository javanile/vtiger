/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ('License'); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class('Settings_MailConverter_Index_Js', {
	mailConverterInstance: false,
	triggerRuleEdit: function (url) {
		app.request.get({url:url}).then(function (err, data) {
			var callBackFunction = function (data) {
				var mcInstance = Settings_MailConverter_Index_Js.mailConverterInstance;
				app.helper.showVerticalScroll(jQuery('.addMailBoxStep'), {setHeight:'450px'});
				mcInstance.saveRuleEvent();
				mcInstance.setAssignedTo();
				jQuery('#actions').trigger('change');
			}
			app.helper.showModal(data, {cb:callBackFunction});
		});
	},

	triggerDeleteRule: function (currentElement, url) {
		var deleteElement = jQuery(currentElement);
		app.helper.showConfirmationBox({'message': app.vtranslate('LBL_DELETE_CONFIRMATION')}).then(function () {
			app.request.post({url:url}).then(function (err, data) {
				if (data) {
					var closestBlock = deleteElement.closest('[data-blockid]');
					var nextBlocks = closestBlock.nextAll('[data-blockid]');

					if (nextBlocks.length > 0) {
						jQuery.each(nextBlocks, function (i, element) {
							var currentSequenceElement = jQuery(element).find('.sequenceNumber');
							var updatedNumber = parseInt(currentSequenceElement.text()) - 1;
							currentSequenceElement.text(updatedNumber);
						});
					}

					closestBlock.remove();
					app.helper.showSuccessNotification({'message':data});
				}
			});
		});
	}
}, {
	registerSortableEvent: function () {
		var thisInstance = this;
		var sequenceList = {};
		var container = jQuery('#rulesList');
		container.sortable({
			revert	: true,
			handle	: '.ruleHead',
			start	: function (event, ui) {
						ui.placeholder.height(ui.helper.height());
					},
			update	: function (e, ui) {
						jQuery('[data-blockid]', container).each(function (i) {
							sequenceList[++i] = jQuery(this).data('id');
						});

						var params = {
							sequencesList	: JSON.stringify(sequenceList),
							module			: app.getModuleName(),
							parent			: app.getParentModuleName(),
							action			: 'UpdateSequence',
							scannerId		: jQuery('#scannerId').val()
						}

						app.request.post({data:params}).then(function (err, data) {
							if (typeof data != 'undefined') {
								jQuery('[data-blockid]', container).each(function (i) {
									jQuery(this).find('.sequenceNumber').text(++i);
								});

								app.helper.hideModal();
								app.helper.showSuccessNotification({'message':data});
							}
						});
					}
		});
	},

	saveRuleEvent: function () {
		var thisInstance = this;
		var form = jQuery('#ruleSave');
		var params = {
			submitHandler: function (form) {
				var form = jQuery(form);
				form.find('[name="saveButton"]').attr('disabled', 'disabled');
				app.helper.showProgress();
				var params = form.serializeFormData();
				app.request.post({data:params}).then(function (err, data) {
					app.helper.hideProgress();
					app.helper.hideModal();
					if (typeof data != 'undefined') {
						var params = {
							module: app.getModuleName(),
							parent: app.getParentModuleName(),
							scannerId: jQuery('[name="scannerId"]', form).val(),
							record: data.id,
							view: 'RuleAjax'
						}
						thisInstance.getRule(params);
						app.helper.showSuccessNotification({'message':data.message});
					}
				});
			}
		}
		form.vtValidate(params);

		form.submit(function (e) {
			e.preventDefault();
		});
	},

	getRule: function (params) {
		app.helper.showProgress();
		var ruleId = params.record;
		app.request.get({data:params}).then(function (err, data) {
			app.helper.hideProgress();
			var currentBlock = jQuery('[data-blockid="block_'+ruleId+'"]')
			if (currentBlock.length > 0) {
				var previousValue = currentBlock.prevAll('[data-blockid]').first().find('.sequenceNumber').text();
				if (previousValue == '') {
					previousValue = 0;
				}
				currentBlock.html(data);
				currentBlock.find('.sequenceNumber').text(parseInt(previousValue)+1)
			} else {
				var lastBlockValue = jQuery('[data-blockid]').size();
				jQuery('#rulesList').append('<div class="row-fluid padding-bottom1per" data-blockid="block_'+ruleId+'">'+data+'</div>');
				jQuery('[data-blockid="block_'+ruleId+'"]').find('.sequenceNumber').text(parseInt(lastBlockValue)+1);
			}
		});
	},

	setAssignedTo: function () {
		jQuery('#actions').on('change', function () {
			var selectedAction = jQuery('#actions').val();
			if (!(selectedAction == 'CREATE_HelpDesk_FROM'
					|| selectedAction == 'CREATE_Leads_SUBJECT'
					|| selectedAction == 'CREATE_Contacts_SUBJECT'
					|| selectedAction == 'CREATE_Accounts_SUBJECT')) {
				jQuery('#assignedTo').val('');
				jQuery('#assignedToBlock').hide();
			} else {
				jQuery('#assignedToBlock').show();
			}
		});
	},

	openMailBox: function () {
		jQuery('.mailBoxDropdown').change(function () {
			var id = jQuery('.mailBoxDropdown option:selected').val();
			var path = 'index.php?parent='+app.getParentModuleName()+'&module='+app.getModuleName()+'&view=List&record='+id;
			window.location.assign(path);
		});
	},

	disableFolderSelection: function () {
		var checked = jQuery('input[type=checkbox][name=folders]:checked').length >= 2;
		jQuery('input[type=checkbox][name=folders]').not(':checked').attr('disabled', checked);

		jQuery('input[type=checkbox][name=folders]').click(function () {
			var checked = jQuery('input[type=checkbox][name=folders]:checked').length >= 2;
			jQuery('input[type=checkbox][name=folders]').not(':checked').attr('disabled', checked);
		});
	},

	registerEvents: function () {
		this.registerSortableEvent();
		this.openMailBox();
		this.setAssignedTo();
		this.disableFolderSelection();
		jQuery('#actions').trigger('change');
	}
});

//On Page Load
jQuery(document).ready(function () {
	var mcInstance = Settings_MailConverter_Index_Js.mailConverterInstance = new Settings_MailConverter_Index_Js();
	mcInstance.registerEvents();
});
