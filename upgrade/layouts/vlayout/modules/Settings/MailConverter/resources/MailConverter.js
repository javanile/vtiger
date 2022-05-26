/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class('Settings_MailConverter_Index_Js',{
	
	mailConverterInstance : false,

	triggerRuleEdit : function(url){
		AppConnector.request(url).then(function(data){
			var callBackFunction = function(data) {
				var mcInstance = Settings_MailConverter_Index_Js.mailConverterInstance;
				mcInstance.saveRuleEvent();
				mcInstance.setAssignedTo();
				jQuery("#actions").change();
				jQuery('#ruleSave').validationEngine(app.validationEngineOptions);
			}
			app.showModalWindow(data,function(data){
				if(typeof callBackFunction == 'function'){
					callBackFunction(data);
				}
			});
		});
	},
	
	triggerDeleteRule : function(currentElement,url){
		var deleteElement = jQuery(currentElement);
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		Vtiger_Helper_Js.showConfirmationBox({
			'message' : message
		}).then(
			function(e) {
				AppConnector.request(url).then(function(data){
					if(data.result){
						var closestBlock = deleteElement.closest('[data-blockid]');
						var nextBlocks = closestBlock.nextAll('[data-blockid]');
						if(nextBlocks.length > 0){
							jQuery.each(nextBlocks,function(i,element) {
								var currentSequenceElement = jQuery(element).find('.sequenceNumber');
								var updatedNumber = parseInt(currentSequenceElement.text())-1;
								currentSequenceElement.text(updatedNumber);
							});	
						}
						closestBlock.remove();
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: data.result,
							animation: 'show',
							type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}

				});
			});
	}
},{
	
	registerSortableEvent : function() {
		var thisInstance = this;
		var sequenceList = {};
		var container = jQuery( "#rulesList");
		container.sortable({
			'revert' : true,
			handle : '.blockHeader',
			start: function (event, ui) {
				ui.placeholder.height(ui.helper.height());
			},
			update: function(e, ui ) {
				
				jQuery('[data-blockid]',container).each(function(i){
					sequenceList[++i] = jQuery(this).data('id');
				});
				var params = {
					sequencesList : JSON.stringify(sequenceList),
					module : app.getModuleName(),
					parent : app.getParentModuleName(),
					action : 'UpdateSequence',
					scannerId : jQuery('#scannerId').val()
				}
				AppConnector.request(params).then(function(data) {
					if(typeof data.result != 'undefined'){
						jQuery('[data-blockid]',container).each(function(i){
							jQuery(this).find('.sequenceNumber').text(++i);
						});
						app.hideModalWindow();
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: data.result,
							animation: 'show',
							type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				});
			}
		});
	},
	
	saveRuleEvent : function() {
		var thisInstance = this;
		jQuery('#ruleSave').on('submit',function(e){
			var form = jQuery(e.currentTarget);
			var validationResult = form.validationEngine('validate');
            var progressIndicatorElement = jQuery.progressIndicator({
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
			if(validationResult == true) {
				var params = form.serializeFormData();
                app.hideModalWindow();
				AppConnector.request(params).then(function(data) {
                    progressIndicatorElement.progressIndicator({
                        'mode' : 'hide'
                    })
					if(typeof data.result != 'undefined') {
						var params = {
							module : app.getModuleName(),
							parent : app.getParentModuleName(),
							scannerId : jQuery('[name="scannerId"]',form).val(),
							record : data.result.id,
							view : 'RuleAjax'
						}
						thisInstance.getRule(params);
						
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: data.result.message,
							animation: 'show',
							type: 'success'
						};
						Vtiger_Helper_Js.showPnotify(params);
					}
				});
			}
			e.preventDefault();
		});
	},
	
	setAssignedTo : function() {
		jQuery("#actions").change(function() {
			var selectedAction = jQuery("#actions").val();
			if(!(selectedAction=="CREATE_HelpDesk_FROM" || selectedAction=="CREATE_Leads_SUBJECT" || selectedAction=="CREATE_Contacts_SUBJECT" || selectedAction=="CREATE_Accounts_SUBJECT")) {
				jQuery("#assignedTo").val("");
				jQuery("#assignedToBlock").hide();
			} else {
				jQuery("#assignedToBlock").show();
			}
		});
	},
	
	openMailBox : function() {
		jQuery(".mailBoxDropdown").change(function() {
			var id = jQuery(".mailBoxDropdown option:selected").val();
			var path = "index.php?parent=" + app.getParentModuleName() + "&module=" + app.getModuleName() + "&view=List&record=" + id;
			window.location.assign(path);
		});
	},
	
	disableFolderSelection : function() {
		var checked = jQuery("input[type=checkbox][name=folders]:checked").length >= 2;     
		jQuery("input[type=checkbox][name=folders]").not(":checked").attr("disabled", checked);
	    
		jQuery("input[type=checkbox][name=folders]").click(function() {
			var checked = jQuery("input[type=checkbox][name=folders]:checked").length >= 2;     
			jQuery("input[type=checkbox][name=folders]").not(":checked").attr("disabled", checked);
		});
	},
	
	getRule : function(params) {
         var progressIndicatorElement = jQuery.progressIndicator({ 
                'position' : 'html',
                'blockInfo' : {
                    'enabled' : true
                }
            });
		var ruleId = params.record;
		AppConnector.request(params).then(function(data){
             progressIndicatorElement.progressIndicator({
                        'mode' : 'hide'
                    })
			var currentBlock = jQuery('[data-blockid="block_'+ruleId+'"]')
			if(currentBlock.length > 0){
				var previousValue = currentBlock.prevAll('[data-blockid]').first().find('.sequenceNumber').text();
				if(previousValue == '') {
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
	
	registerEvents : function() {
		this.registerSortableEvent();
		this.openMailBox();
		this.setAssignedTo();
		this.disableFolderSelection();
	}
});

//On Page Load
jQuery(document).ready(function() {
	var mcInstance = Settings_MailConverter_Index_Js.mailConverterInstance  = new Settings_MailConverter_Index_Js();
	mcInstance.registerEvents();
});