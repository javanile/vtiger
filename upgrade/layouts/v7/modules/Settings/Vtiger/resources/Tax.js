/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Settings_Vtiger_TaxIndex_Js",{
    updateSelectElement : function(selectedRegions, skippedRegions, regionsElement){
        var allRegions = jQuery('.taxRegionElements');
        var prevSelectedRegions = regionsElement.val();
        regionsElement.html(allRegions.clone().html());

        for(var index in selectedRegions) {
			if (jQuery.inArray(selectedRegions[index], skippedRegions) == '-1') {
				regionsElement.find("option[value='"+selectedRegions[index]+"']").remove();
			}
        }

        if(prevSelectedRegions) {
            regionsElement.select2("val", prevSelectedRegions);
        }
    }
},{

	init : function () {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	},

    //Stored history of TaxName and duplicate check result
	duplicateCheckCache : {},
    taxContainer : false,
    
    getContainer : function(){
        if(this.taxContainer == false){
            this.taxContainer =  jQuery("#TaxCalculationsContainer");
        }
        return this.taxContainer;
    },
    
    
    /**
	 * This function will show the model for Add/Edit tax
	 */
	editTax : function(url, currentTrElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		
		app.helper.showProgress();
		app.request.post({url:url}).then(
			function(err,data) {
                app.helper.hideProgress();
                if(err == null){
                    var callBackFunction = function(data) {
                        //cache should be empty when modal opened 
                        thisInstance.duplicateCheckCache = {};
                        var form = jQuery('#editTax');
                        app.helper.showVerticalScroll(jQuery('#scrollContainer'), {setHeight:'80%'});
                        thisInstance.registerUpdateRegionsElementEvent();
						jQuery('.regions').trigger('change');

                        form.find('#simple').click(function(e) {
                            form.find('.compoundOnContainer').addClass('hide');
                            form.find('.dedcutedTaxDesc').addClass('hide');
                            form.find('.taxTypeContainer').removeClass('hide').show();
                            form.find('#compoundOn').select2('val', null);

                            var container = form.find('.taxTypeContainer');
                            var taxType = jQuery(':checked', container).val();
                            if (taxType == 'Variable') {
                                form.find('.taxValueContainer').addClass('hide');
                                form.find('.regionsContainer').removeClass('hide').show();
                            }
                        });

                        form.find('#compound').click(function(e) {
                            form.find('.dedcutedTaxDesc').addClass('hide');
                            form.find('.taxTypeContainer').removeClass('hide').show();
                            form.find('.compoundOnContainer').removeClass('hide').show();

                            var container = form.find('.taxTypeContainer');
                            var taxType = jQuery(':checked', container).val();
                            if (taxType == 'Variable') {
                                form.find('.taxValueContainer').addClass('hide');
                                form.find('.regionsContainer').removeClass('hide').show();
                            }
                        });

                        form.find('#deducted').click(function(e) {
                            form.find('.compoundOnContainer').addClass('hide');
                            form.find('.taxTypeContainer').addClass('hide');
                            form.find('.regionsContainer').addClass('hide');
                            form.find('.dedcutedTaxDesc').removeClass('hide').show();
                            form.find('.taxValueContainer').removeClass('hide').show();
                            form.find('#compoundOn').select2('val', null);
                        });

                        form.find('#fixed').click(function(e) {
                            form.find('.regionsContainer').addClass('hide');
                            form.find('.taxValueContainer').removeClass('hide').show();
                        });

                        form.find('#variable').click(function(e) {
                            form.find('.taxValueContainer').addClass('hide');
                            form.find('.regionsContainer').removeClass('hide').show();
                        });

                        form.on('click', '.deleteRow', function(e) {
                            var element = jQuery(e.currentTarget);
                            element.parent().parent().remove();
                        });

                        form.find('.addNewTaxBracket').click(function(e) {
                            e.preventDefault(e);

                            var taxRegionsTable = form.find('.regionsTable');
                            var taxRegionsCount = form.find('.regionsCount').val();
                            var widthHeight = form.find('#widthHeight').val();
                            var taxBracketBlock = jQuery('<tr>\n\
                                <td class="regionsList '+widthHeight+'">\n\
                                    <div class="deleteRow close" style="float:left;margin-top:3px">×</div>&nbsp;&nbsp\n\
                                    <select id="'+taxRegionsCount+'" data-placeholder="'+app.vtranslate('JS_SELECT_REGIONS')+'" name="regions['+taxRegionsCount+'][list]" class="regions select2 inputElement" multiple="" data-rule-required="true" style="width: 90%;">'
                                        +form.find('.taxRegionElements').html().toString()+'\n\
                                    </select>\n\
                                </td>\n\
                                <td class="'+widthHeight+'" style="text-align: center;">\n\
                                    <input class="inputElement" type="text" name="regions['+taxRegionsCount+'][value]" class="input-medium" value="" data-rule-required="true" data-rule-inventory_percentage="true" />\n\
                                </td>\n\
                            </tr>');

                            var selectedRegions = [];
                            var regionElements = jQuery('.regionsTable select.regions');
                            jQuery.each(regionElements, function(index, element) {
                                var selectedValues = jQuery(element).val();
                                if (jQuery.isArray(selectedValues)) {
                                    for (var i=0; i<selectedValues.length; i++) {
                                        selectedRegions.push(selectedValues[i]);
                                    }
                                }
                            });

                            for(var index in selectedRegions) {
                                taxBracketBlock.find("option[value='"+selectedRegions[index]+"']").remove();
                            }

                            form.find('.regionsCount').val(parseInt (taxRegionsCount) + 1);
                            taxRegionsTable.append(taxBracketBlock);
                            vtUtils.showSelect2ElementView(taxRegionsTable.find('tr:last td.regionsList select.select2'));
                        });

                        var params = {
                            submitHandler : function(form){
                                var form = jQuery(form);
                                thisInstance.saveTaxDetails(form, currentTrElement);
                            }
                        }
                        form.vtValidate(params);
                        
                        form.submit(function(e) {
                            e.preventDefault();
                        })
                    }
                    
                    app.helper.showModal(data, {cb:callBackFunction});
                }
                
			}
		);
		return aDeferred.promise();
	},
    
    /*
	 * Function to check Duplication of Tax Name
	 */
	checkDuplicateTaxName : function(details) {
		var aDeferred = jQuery.Deferred();
		var taxName = details.taxlabel;
		var taxId = details.taxid;
		var moduleName = app.getModuleName();
		var params = {
			'module' : moduleName,
			'parent' : app.getParentModuleName(),
			'action' : 'TaxAjax',
			'mode' : 'checkDuplicateTaxName',
			'taxlabel' : taxName,
			'taxid' : taxId,
			'type' : details.type
		}
		app.request.post({data:params}).then(function(err,data) {
            if(err == null){
                var result = data['success'];
                if(result == true) {
					aDeferred.reject(data);
				} else {
					aDeferred.resolve(data);
				}
            } else {
                aDeferred.reject();
            }
        });
		return aDeferred.promise();
	},
    
    /*
	 * Function to validate the TaxName to avoid duplicates
	 */
	validateTaxName : function(data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var taxName = data.taxlabel;
		var form = jQuery('#editTax');
		var taxLabelElement = form.find('[name="taxlabel"]');

		if(!(taxName in thisInstance.duplicateCheckCache)) {
			thisInstance.checkDuplicateTaxName(data).then(
				function(data){
					thisInstance.duplicateCheckCache[taxName] = data['success'];
					aDeferred.resolve();
				},
				function(data, err){
					thisInstance.duplicateCheckCache[taxName] = data['success'];
					thisInstance.duplicateCheckCache['message'] = data['message'];
					aDeferred.reject(data);
				}
			);
		} else {
			if(thisInstance.duplicateCheckCache[taxName] == true){
				var result = thisInstance.duplicateCheckCache['message'];
				aDeferred.reject();
			} else {
				aDeferred.resolve();
			}
		}
		return aDeferred.promise();
	},
    
    /*
	 * Function to Save the Tax Details
	 */
	saveTaxDetails : function(form, currentTrElement) {
		var thisInstance = this;
		var params = form.serializeFormData();

		if(typeof params == 'undefined' ) {
			params = {};
		}
		thisInstance.validateTaxName(params).then(
			function(data) {
                app.helper.showProgress();
                params.module = app.getModuleName();
                params.parent = app.getParentModuleName();
                params.action = 'TaxAjax';
                app.request.post({data:params}).then(
                    function(err,data) {
                        app.helper.hideModal();
                        app.helper.hideProgress();
                        //Adding or update the tax details in the list
                        if(form.find('[name="taxid"]').val() == "") {
                            thisInstance.addTaxDetails(data);
                        } else {
                            thisInstance.updateTaxDetails(data, currentTrElement);
                        }
                        //show notification after tax details saved
                        var params = {
                            message: app.vtranslate('JS_TAX_SAVED_SUCCESSFULLY')
                        };
                        app.helper.showSuccessNotification(params);
                    }
                );
            },
            function(err){
                app.helper.hideModal();
                app.helper.showErrorNotification({"message":err["message"]});
            }
		);
	},
    
    
    addTaxDetails : function(details) {
		var container = this.getContainer();

		//Based on tax type, we will add the tax details row
		if(details.type == '0') {
			var taxTable = jQuery('.inventoryTaxTable', container);
		} else {
			var taxTable = jQuery('.shippingTaxTable', container);
		}

		var isChecked = '';
		if (details.deleted != 1) {
			isChecked = 'checked';
		}

		var trElementForTax =
				jQuery('<tr class="opacity" data-taxid="'+details.taxid+'" data-taxtype="'+details.type+'">\n\
					<td style="border-left: none;border-right: none;" '+details.row_type+'"><span class="taxLabel">'+details.taxlabel+'</span></td>\n\
					<td style="border-left: none;border-right: none;" '+details.row_type+'"><span class="taxType">'+details.taxType+'</span></td>\n\
					<td style="border-left: none;border-right: none;" '+details.row_type+'"><span class="taxMethod">'+details.method+'</span></td>\n\
					<td style="border-left: none;border-right: none;" '+details.row_type+'"><span class="taxPercentage">'+details.percentage+'%</span></td>\n\
					<td style="border-left: none;border-right: none;" '+details.row_type+'"><input class="editTaxStatus" type="checkbox" '+isChecked+' >\n\
					</td><td style="border-left: none;border-right: none;">\n\
						<div class="pull-right actions">\n\
							<a class="editTax cursorPointer" data-url="'+details._editurl+'">\n\
								<i class="fa fa-pencil alignMiddle" title="'+app.vtranslate('JS_EDIT')+'"></i>\n\
							</a>\n\
						</div>\n\</td></tr>');
		taxTable.append(trElementForTax);
	},
    
    /*
	 * Function to update the tax details in the list after edit
	 */
	updateTaxDetails : function(data, currentTrElement) {
		currentTrElement.find('.taxLabel').text(data['taxlabel']);
		currentTrElement.find('.taxPercentage').text(data['percentage']+'%');
		currentTrElement.find('.taxType').text(data['taxType']);
		currentTrElement.find('.taxMethod').text(data['method']);
		if(data['deleted'] == '0') {
			currentTrElement.find('.editTaxStatus').attr('checked', 'true');
		} else {
			currentTrElement.find('.editTaxStatus').removeAttr('checked');
		}
	},

    /**
	 * Function to register the click event for charges list tab
	 */
	registerChargesClickEvent : function() {
		var thisInstance = this;
        var taxContainer = this.getContainer();
		var contents = taxContainer.find('.contents');
		var relatedContainer = contents.find('#charges');
		var relatedTab = contents.find('.chargesTab');
		relatedTab.click(function() {
			if(relatedContainer.find('.chargesContainer').length > 0) {

			} else {
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['view'] = 'TaxIndex';
				params['mode'] = 'showChargesAndItsTaxes';

				app.request.post({data:params}).then(function(err,data) {
                    relatedContainer.html(data);
                    
                    if(contents.find('.chargesContainer').length > 0) {
                            
                            contents.find('.addCharge').click(function(e) {
								var addChargeButton = jQuery(e.currentTarget);
								thisInstance.editCharge(addChargeButton.data('url'));
							});
                            
                            contents.on('click', '.editCharge', function(e) {
								var editChargeButton = jQuery(e.currentTarget);
								var currentTrElement = editChargeButton.closest('tr');
								thisInstance.editCharge(editChargeButton.data('url'), currentTrElement);
							});
                        
							contents.find('.addChargeTax').click(function(e) {
								var addTaxButton = jQuery(e.currentTarget);
								var createTaxUrl = addTaxButton.data('url')+'&type='+addTaxButton.data('type');
								thisInstance.editTax(createTaxUrl);
							});

							contents.on('click', '.editChargeTax', function(e) {
								var editTaxButton = jQuery(e.currentTarget);
								var currentTrElement = editTaxButton.closest('tr');
								thisInstance.editTax(editTaxButton.data('url'), currentTrElement);
							});
						}
                });
			}
		});
	},

    editCharge : function(url, currentTrElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;

		app.helper.showProgress();

		app.request.post({url:url}).then(
			function(err,data) {
				var callBackFunction = function(data) {
					//cache should be empty when modal opened
					thisInstance.duplicateCheckCache = {};
					var form = jQuery('#editCharge');
					thisInstance.registerUpdateRegionsElementEvent();
					jQuery('.regions').trigger('change');
					var container = jQuery('#scrollContainer');

					form.find('#flat').click(function(e) {
						form.find('.percentIcon').addClass('hide');

						jQuery('[name="value"]', container).data('ruleInventory_percentage',false).data('rulePositivenumber',true);
                                                jQuery('[name="defaultValue"]', container).data('ruleInventory_percentage',false).data('rulePositivenumber',true);
                                                
						var valuesObjsList = jQuery('.valuesList', container);
						jQuery.each(valuesObjsList, function(index, element) {
                                                    jQuery(element).data('ruleInventory_percentage',false).data('rulePositivenumber',true);
						});
					});

					form.find('#percent').click(function(e) {
						form.find('.percentIcon').removeClass('hide');

						jQuery('[name="value"]', container).data('ruleInventory_percentage',true).data('rulePositivenumber',true);
                                                jQuery('[name="defaultValue"]', container).data('ruleInventory_percentage',true).data('rulePositivenumber',true);
                                                
						var valuesObjsList = jQuery('.valuesList', container);
						jQuery.each(valuesObjsList, function(index, element) {
                                                        jQuery(element).data('ruleInventory_percentage',true).data('rulePositivenumber',true);
						});
					});

					form.find('#fixed').click(function(e) {
						form.find('.regionsContainer').addClass('hide');
						form.find('.chargeValueContainer').removeClass('hide').show();
					});

					form.find('#variable').click(function(e) {
						form.find('.chargeValueContainer').addClass('hide');
						form.find('.regionsContainer').removeClass('hide').show();
					});

					form.find('.isTaxable').click(function(e) {
						var element = jQuery(e.currentTarget);
						var isChecked = element.is(':checked');
						if (isChecked) {
							form.find('.taxContainer').removeClass('hide').show();
						} else {
							form.find('.taxContainer').addClass('hide');
						}
					});

					form.on('click', '.deleteRow', function(e) {
						var element = jQuery(e.currentTarget);
						element.parent().parent().remove();
					});

					form.find('.addNewTaxBracket').click(function(e) {
						e.preventDefault(e);

						var taxRegionsTable = form.find('.regionsTable');
						var taxRegionsCount = form.find('.regionsCount').val();
						var widthHeight = form.find('#widthHeight').val();

						var selectedFormat = jQuery(':checked', form.find('.formatContainer')).val();
						var validator = 'PositiveNumber';
						if (selectedFormat == 'Percent') {
							validator = 'inventory_percentage';
						}

						var taxBracketBlock = jQuery('<tr>\n\
							<td class="regionsList '+widthHeight+'">\n\
								<span class="deleteRow close" style="float:left;margin-top:3px;">×</span>&nbsp;&nbsp;\n\
								<select id="'+taxRegionsCount+'" data-placeholder="'+app.vtranslate('JS_SELECT_REGIONS')+'" name="regions['+taxRegionsCount+'][list]" class="select2 regions columns span3" multiple="" data-rule-required="true" style="width:90%;">'
									+form.find('.taxRegionElements').html().toString()+'\n\
								</select>\n\
							</td>\n\
							<td class="'+widthHeight+'" style="text-align: center;">\n\
								<input class="inputElement valuesList" type="text" name="regions['+taxRegionsCount+'][value]" class="input-medium" value="" data-rule-required="true" data-rule-'+validator+'="true"/>\n\
							</td>\n\
						</tr>');

						var selectedRegions = [];
						var regionElements = jQuery('.regionsTable select.regions');
						jQuery.each(regionElements, function(index, element) {
							var selectedValues = jQuery(element).val();
							if (jQuery.isArray(selectedValues)) {
								for (var i=0; i<selectedValues.length; i++) {
									selectedRegions.push(selectedValues[i]);
								}
							}
						});

						for(var index in selectedRegions) {
							taxBracketBlock.find("option[value='"+selectedRegions[index]+"']").remove();
						}

						form.find('.regionsCount').val(parseInt (taxRegionsCount) + 1);
						taxRegionsTable.append(taxBracketBlock);
                        vtUtils.showSelect2ElementView(taxRegionsTable.find('tr:last td.regionsList select.select2'));
					});


                    var params = {
                        submitHandler : function(form){
                            var form = jQuery(form);
                            thisInstance.saveChargeDetails(form, currentTrElement);
                        }
                    }

                    form.vtValidate(params);

					form.submit(function(e) {
						e.preventDefault();
					});
				}

				app.helper.hideProgress();
                app.helper.showModal(data,{cb:callBackFunction});
				
			},
			function(error) {
				//TODO : Handle error
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
    
    saveChargeDetails : function(form, currentTrElement) {
		var thisInstance = this;
		var params = form.serializeFormData();
		if(typeof params == 'undefined' ) {
			params = {};
		}

		thisInstance.validateChargeName(params).then(function(data) {
			app.helper.showProgress();

			params.module = app.getModuleName();
			params.parent = app.getParentModuleName();
			params.action = 'TaxAjax';
			params.mode	  = 'saveCharge';
			app.request.post({data:params}).then(
				function(err,data) {
					app.helper.hideProgress();
                    app.helper.hideModal();
					if(form.find('[name="chargeid"]').val() == "") {
						thisInstance.addChargeDetails(data);
					} else {
						var result = data;
						currentTrElement.find('.chargeName').text(result['name']);
						currentTrElement.find('.chargeValue').text(result['value']);
						currentTrElement.find('.chargeIsTaxable').text(result['isTaxable']);
						currentTrElement.find('.chargeTaxes').text(result['selectedTaxes']);
					}

					var params = {
						message: app.vtranslate('JS_INVENTORY_CHARGE_SAVED_SUCCESSFULLY')
					};
                    app.helper.showSuccessNotification(params);
				}
			);
		});
	},
    
    addChargeDetails : function(details) {
		var container = jQuery('#TaxCalculationsContainer');
		var trElementForCharge =
				jQuery('<tr class="opacity">\n\
					<td style="border-left: none;border-right: none;" class="'+details.row_type+'"><span class="chargeName" style="width:100px;">'+details.name+'</span></td>\n\
					<td style="border-left: none;border-right: none;" class="'+details.row_type+'"><span class="chargeValue" style="width:105px;">'+details.value+'</span></td>\n\
					<td style="border-left: none;border-right: none;" class="'+details.row_type+'"><span class="chargeIsTaxable">'+details.isTaxable+'</span></td>\n\
					<td style="border-left: none;border-right: none;" class="'+details.row_type+'"><span class="chargeTaxes span2">'+details.selectedTaxes+'</span></td>\n\
					<td style="border-left: none;border-right: none;" class="'+details.row_type+'">\n\
						<div class="pull-right actions">\n\
							<a class="editCharge cursorPointer" data-url="'+details._editurl+'">\n\
								<i class="fa fa-pencil alignBottom" title="'+app.vtranslate('JS_EDIT')+'"></i>\n\
							</a>\n\
						</div>\n\
					</td></tr>');

		jQuery('.inventoryChargesTable', container).append(trElementForCharge);
	},
    
    validateChargeName : function(data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var chargeName = data.name;
		var form = jQuery('#editCharge');
		var chargeNameElement = form.find('[name="name"]');

		if(!(chargeName in thisInstance.duplicateCheckCache)) {
			thisInstance.checkDuplicateChargeName(data).then(
				function(data){
					thisInstance.duplicateCheckCache[chargeName] = data['success'];
					aDeferred.resolve(data);
				},
				function(data, err){
					thisInstance.duplicateCheckCache[chargeName] = data['success'];
					thisInstance.duplicateCheckCache['message'] = data['message'];
                    app.helper.showErrorNotification({message:data['message']});
					aDeferred.reject(data);
				}
			);
		} else {
			if(thisInstance.duplicateCheckCache[chargeName] == true){
				var result = thisInstance.duplicateCheckCache['message'];
                app.helper.showErrorNotification({message:result});
				aDeferred.reject();
			} else {
				aDeferred.resolve();
			}
		}
		return aDeferred.promise();
	},

	checkDuplicateChargeName : function(details) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module'	: app.getModuleName(),
			'parent'	: app.getParentModuleName(),
			'action'	: 'TaxAjax',
			'mode'		: 'checkDuplicateInventoryCharge',
			'name'		: details.name,
			'chargeid'	: details.chargeid
		}

		app.request.post({data:params}).then(function(err,data) {
            if(err == null){
                var response = data;
                var result = response['success'];
                if(result == true) {
                    aDeferred.reject(response);
                } else {
                    aDeferred.resolve(response);
                }
            } else {
                aDeferred.reject();
            }
        });
		return aDeferred.promise();
	},
    
    registerUpdateRegionsElementEvent : function() {
		jQuery('.regionsTable').on('change', '.regions', function(e) {
			var currentElement = e.currentTarget;
			var selectedRegions = [];
			var regionElements = jQuery('.regionsTable select.regions');

			jQuery.each(regionElements, function(index, element) {
				var selectedValues = jQuery(element).val();
				if (jQuery.isArray(selectedValues)) {
					for (var i=0; i<selectedValues.length; i++) {
						selectedRegions.push(selectedValues[i]);
					}
				}
			});

			jQuery.each(regionElements, function(index, element) {
				if (element != currentElement) {
					var skippedValues = jQuery(element).val();
					if (!jQuery.isArray(skippedValues)) {
						skippedValues = [];
					}
					Settings_Vtiger_TaxIndex_Js.updateSelectElement(selectedRegions, skippedValues, jQuery(element));
				}
			});
		});
    },
    
    /**
	 * Function to register the click event for regions list tab
	 */
	registerRegionsClickEvent : function() {
		var thisInstance = this;
        var taxContainer = this.getContainer();
		var contents = taxContainer.find('.contents');
		var relatedContainer = contents.find('#taxRegions');
		var relatedTab = contents.find('.taxRegionsTab');
		relatedTab.click(function() {
			if(relatedContainer.find('.taxRegionsContainer').length > 0) {

			} else {
				var params = {};
				params['module'] = app.getModuleName();
				params['parent'] = app.getParentModuleName();
				params['view'] = 'TaxIndex';
				params['mode'] = 'showTaxRegions';

				app.request.post({data:params}).then(function(err,data) {
                    relatedContainer.html(data);
                    contents.find('.addRegion').click(function(e) {
                        var addRegionButton = jQuery(e.currentTarget);
                        thisInstance.editRegion(addRegionButton.data('url'));
                    });
                    
                    contents.on('click', '.editRegion', function(e) {
                        var editRegionButton = jQuery(e.currentTarget);
                        var currentTrElement = editRegionButton.closest('tr');
                        thisInstance.editRegion(editRegionButton.data('url'), currentTrElement);
                    });
                    
                    contents.on('click', '.deleteRegion', function(e) {
                        var deleteRegionButton = jQuery(e.currentTarget);
                        var currentTrElement = deleteRegionButton.closest('tr');
                        thisInstance.deleteRegion(deleteRegionButton.data('url'), currentTrElement);
                    });
                    
                });
			}
		});
	},
    
    deleteRegion : function(url, currentElement) {
		var message = app.vtranslate('JS_DELETE_REGION_DESC');
		app.helper.showConfirmationBox({'message' : message}).then(function(e) {    
            app.helper.showProgress();
            app.request.post({url:url}).then(function(err,data){
                app.helper.hideProgress();
                if(err == null) {
                    currentElement.remove();
                    var params = {
                        message: app.vtranslate('JS_TAX_REGION_DELETED_SUCCESSFULLY')
                    };
                    app.helper.showSuccessNotification(params);
                } else {
                    var  params = {
                        message : app.vtranslate('JS_LBL_PERMISSION')
                    }
                    app.helper.showErrorNotification(params);
                }
            });
        });
	},
    
    editRegion : function(url, currentTrElement) {
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;

		app.helper.showProgress();

		app.request.post({url:url}).then(
			function(err,data) {
                if(err == null){
                    var callBackFunction = function(data) {
                        var form = jQuery('#editTaxRegion');
                        //cache should be empty when modal opened
                        thisInstance.duplicateCheckCache = {};
                        
                        var params = {
                            submitHandler : function(form){
                                var form = jQuery(form);
                                thisInstance.saveRegionDetails(form, currentTrElement);
                            }
                        }
                        form.vtValidate(params);
                        form.submit(function(e) {
                            e.preventDefault();
                        })
                    }
                    app.helper.hideProgress();
                    app.helper.showModal(data,{cb:callBackFunction});
                }else {
                    aDeferred.reject(err);
                }
            });
        return aDeferred.promise();
    },
    
    saveRegionDetails : function(form, currentTrElement) {
		var thisInstance = this;
		var params = form.serializeFormData();

		if(typeof params == 'undefined' ) {
			params = {};
		}
		thisInstance.validateRegionName(params).then(
            function(data) {
                app.helper.showProgress();

                params.module = app.getModuleName();
                params.parent = app.getParentModuleName();
                params.action = 'TaxAjax';
                params.mode   = 'saveTaxRegion';
                app.request.post({data:params}).then(
                    function(err,data) {
                        app.helper.hideProgress();
                        app.helper.hideModal();

                        if(form.find('[name="taxRegionId"]').val() == "") {
                            thisInstance.addRegionDetails(data);
                        } else {
                            currentTrElement.find('.taxRegionName').text(data['name']);
                        }

                        var params = {
                            message: app.vtranslate('JS_TAX_REGION_SAVED_SUCCESSFULLY')
                        };
                        app.helper.showSuccessNotification(params);
                    }
                );
            },
            function(data,err){
                app.helper.hideModal();
                app.helper.showErrorNotification({message:data["message"]});
            }
        );
	},
    
    validateRegionName : function(data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var regionName = data.name;
		var form = jQuery('#editTaxRegion');
		var regionNameElement = form.find('[name="name"]');

		if(!(regionName in thisInstance.duplicateCheckCache)) {
			thisInstance.checkDuplicateRegionName(data).then(
				function(data){
					thisInstance.duplicateCheckCache[regionName] = true;
					thisInstance.duplicateCheckCache['message'] = app.vtranslate('JS_LBL_TAX_REGION_EXIST');
					aDeferred.resolve(data); 
				},
				function(data, err){
					thisInstance.duplicateCheckCache[regionName] = data['success'];
					thisInstance.duplicateCheckCache['message'] = data['message'];
					aDeferred.reject(data);
				}
			);
		} else {
			if(thisInstance.duplicateCheckCache[regionName] == true){
				aDeferred.reject({message:thisInstance.duplicateCheckCache['message']});
			} else {
				aDeferred.resolve();
			}
		}
		return aDeferred.promise();
	},
    
    checkDuplicateRegionName : function(details) {
		var aDeferred = jQuery.Deferred();
		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'TaxAjax',
			'mode'	 : 'checkDuplicateTaxRegion',
			'taxRegionName'	: details.name,
			'taxRegionId'	: details.taxRegionId
		}

		app.request.post({data:params}).then(function(err,data) {
            if(err == null){
                var result = data['success'];
                if(result == true) {
                    aDeferred.reject(data);
                } else {
                    aDeferred.resolve(data);
                }
            }else {
                aDeferred.reject();
            }
        });
		return aDeferred.promise();
	},
    
    addRegionDetails : function(details) {
		var container = this.getContainer();

		var trElementForRegion =
				jQuery('<tr class="opacity" data-key-name="'+details.name+'" data-key="'+details.name+'">\n\
					<td style="border-right:none;border-left:none;;" class="textAlignLeft '+details.row_type+'">\n\
						<span class="taxRegionName">'+details.name+'</span>\n\
					</td>\n\
					<td style="border-right:none;border-left:none" class="'+details.row_type+'">\n\
						<div class="pull-right actions">\n\
							<a class="editRegion" data-url="'+details._editurl+'">\n\
								<i class="fa fa-pencil alignMiddle" title="'+app.vtranslate('JS_EDIT')+'"></i>\n\
							</a>\n\
							<a class="deleteRegion" data-url="'+details._deleteurl+'">\n\
								<i class="fa fa-trash alignMiddle" title="'+app.vtranslate('JS_DELETE')+'"></i>\n\
							</a>\n\
						</div>\n\
					</td></tr>');

		jQuery('.taxRegionsTable', container).append(trElementForRegion);
	},
    
    /*
	 * Function to update tax status as enabled or disabled
	 */
	updateTaxStatus : function(currentTarget) {
		var aDeferred = jQuery.Deferred();

		var currentTrElement = currentTarget.closest('tr');
		var taxId = currentTrElement.data('taxid');
		var taxType = currentTrElement.data('taxtype');
		var deleted = currentTarget.is(':checked') ? 0 : 1;

		app.helper.showProgress();

		var params = {
			'module' : app.getModuleName(),
			'parent' : app.getParentModuleName(),
			'action' : 'TaxAjax',
			'mode'	: 'updateTaxStatus',
			'taxid' : taxId,
			'type' : taxType,
			'deleted' : deleted
		}

		app.request.post({data:params}).then(
			function(err,data) {
                if(err == null){
                    app.helper.hideProgress();
                    aDeferred.resolve(data);
                }else {
                    app.helper.hideProgress();
    				aDeferred.reject(err);
                }
			});
		return aDeferred.promise();
	},
    
    /*
	 * Function to register all actions in the Tax List
	 */
	registerActions : function() {
		var thisInstance = this;
		var container = this.getContainer();

        //register click event for Add New Tax button
		container.find('.addTax').click(function(e) {
			var addTaxButton = jQuery(e.currentTarget);
			var createTaxUrl = addTaxButton.data('url')+'&type='+addTaxButton.data('type');
			thisInstance.editTax(createTaxUrl);
		});

		//register event for edit tax icon
		container.on('click', '.editTax', function(e) {
			var editTaxButton = jQuery(e.currentTarget);
			var currentTrElement = editTaxButton.closest('tr');
			thisInstance.editTax(editTaxButton.data('url'), currentTrElement);
		});
        
        //register event for checkbox to change the tax Status
		container.on('click', '.editTaxStatus', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			
			thisInstance.updateTaxStatus(currentTarget).then(
				function(data){
					var params = {};
					if(currentTarget.is(':checked')) {
						params.message = app.vtranslate('JS_TAX_ENABLED');
					} else {
						params.message = app.vtranslate('JS_TAX_DISABLED');
					}
                    app.helper.showSuccessNotification(params);
				},
				function(error){
					//TODO: Handle Error
				}
			);
		});

        container.on('click', '[name="default_tax_mode"]', function(e) {
			var currentTarget = jQuery(e.currentTarget);
			var value = currentTarget.val();
			var prevSelectedValue = jQuery('#selectedTaxMode').val();

			if(value != prevSelectedValue) {
				var params = {
					'module'		: app.getModuleName(),
					'parent'		: app.getParentModuleName(),
					'action'		: 'TaxAjax',
					'mode'			: 'updateDefaultTaxMode',
					'defaultTaxMode': value
				}
				app.request.post({data:params}).then(function(err,data) {
                    if(err == null){
                        jQuery('#selectedTaxMode').val(value);
                        var selectedType = currentTarget.parent().find('.mode').text();
                        app.helper.showSuccessNotification({
                            "message":app.vtranslate('JS_DEFAULT_TAX_MODE_SET_AS') + selectedType
                        });
                    } else {
                        app.helper.showErrorNotification({
                            "message": err
                        });
                    }
                });
            }
		});
	},
    
    
    registerEvents :  function(){
        this.registerChargesClickEvent();
        this.registerRegionsClickEvent();
        this.registerActions();
    }
});