/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

Vtiger_Index_Js("Settings_ExtensionStore_ExtensionStore_Js", {
    showPopover : function(e) {
        var ele = jQuery(e);
        var options = {
            placement : ele.data('position'),
            trigger   : 'hover'
        };
        ele.popover(options);
    }
}, {
    /**
     * Function to get import module index params
     */
    getImportModuleIndexParams: function() {
        var params = {
            'module': app.getModuleName(),
            'parent': app.getParentModuleName(),
            'view': 'ExtensionStore'
        };
        return params;
    },
    /**
     * Function to get import module with respect to view
     */
    getImportModuleStepView: function(params) {
        var aDeferred = jQuery.Deferred();
        app.helper.showProgress();
        app.request.post({data: params}).then(
                function(error, data) {
                    app.helper.hideProgress();
                    if(error) {
                        aDeferred.reject(error);
                    }
                    aDeferred.resolve(data);
                }
        );
        return aDeferred.promise();
    },
    /**
     * Function to register raty
     */
    registerRaty: function() {
        jQuery('.rating').raty({
            score: function() {
                return this.getAttribute('data-score');
            },
            readOnly: function() {
                return this.getAttribute('data-readonly');
            }
        });
    },
    /**
     * Function to register event for index of import module
     */
    registerEventForIndexView: function() {
        this.registerRaty();
        var detailContentsHolder = jQuery('.contentsDiv');
        app.helper.showScroll(jQuery('.extensionDescription'), {'height': '120px', 'width': '100%'});
        this.registerEventsForExtensionStore(detailContentsHolder);
    },
    
    getContainer : function() {
        return jQuery('.contentsDiv');
    },
    /**
     * Function to register event related to Import extrension Modules in index
     */
    registerEventsForExtensionStore: function(container) {
        var thisInstance = this;
        thisInstance.registerShowCardInfoEvent();
        jQuery(container).find('.installExtension, .installPaidExtension').on('click', function(e) {
            thisInstance.installExtension(e);
        });

        jQuery('#logintoMarketPlace').off().on('click', function(e) {
            var loginAccountModal = jQuery(container).find('.loginAccount').clone(true, true);
            loginAccountModal.removeClass('hide');
            app.helper.showProgress();

            var callBackFunction = function(data) {
                app.helper.hideProgress();
                jQuery(data).find('[name="signUp"]').on('click', function(e) {
                    var signUpAccountModal = jQuery(container).find('.signUpAccount').clone(true, true);
                    signUpAccountModal.removeClass('hide');

                    var callBackSignupFunction = function(data) {
                        app.helper.hideModal();
                        var form = data.find('.signUpForm');
                        var params = {
                            submitHandler : function(form){
                                var form = jQuery(form);
                                var password = form.find('input[name=password]').val();
                                var confirmPassword = form.find('input[name=confirmPassword]').val();
                                if(password !== confirmPassword) {
                                    app.helper.showErrorNotification({"message": app.vtranslate('JS_PASSWORDS_MISMATCH')});
                                    return false;
                                }
                                var formData = form.serializeFormData();
                                app.helper.showProgress();
                                app.request.post({'data':formData}).then(
                                        function(error,data) {
                                            app.helper.hideProgress();
                                            app.helper.hidePopup();
                                            if(error) {
                                                app.helper.showErrorNotification({"message": error});
                                                return false;
                                            }else{
                                                location.reload();
                                            }
                                        }
                                );
                            }
                        };
                        
                        form.vtValidate(params);
                    };
                    app.helper.showPopup(signUpAccountModal,{cb:callBackSignupFunction});
                });
                
                jQuery(data).find('#forgotPasswordLink').on('click',function(){
                    var forgotPasswordModal = jQuery(container).find('.forgotPasswordModal').clone(true, true);
                    forgotPasswordModal.removeClass('hide');
                    
                    var forgotPasswordCallback = function(data){
                        app.helper.hideModal();
                        var forgotPasswordForm = data.find('.forgotPassword');
                        
                        var params = {
                            submitHandler : function(form){
                                var formData = jQuery(form).serializeFormData();
                                app.helper.showProgress();
                                app.request.post({data:formData}).then(
                                        function(error,data) {
                                            app.helper.hideProgress();
                                            app.helper.hidePopup();
                                            if(error) {
                                                app.helper.showErrorNotification({"message": error});
                                                return false;
                                            }else{
                                                app.helper.showSuccessNotification({"message": data.message});
                                                return true;
                                            }
                                        }
                                );
                            }
                        };
                        forgotPasswordForm.vtValidate(params);
                    };
                    app.helper.showPopup(forgotPasswordModal,{cb:forgotPasswordCallback});
                });

                var form = jQuery(data).find('.loginForm');
                var params = {
                    submitHandler : function(form){
                        var form = jQuery(form);
                        var formData = form.serializeFormData();
                        var savePassword = form.find('input[name="savePassword"]:checked').length;
                        if (savePassword) {
                            formData["savePassword"] = true;
                        } else {
                            formData["savePassword"] = false;
                        }
                        app.helper.showProgress();
                        app.request.post({data:formData}).then(
                                function(error,data) {
                                    app.helper.hideProgress();
                                    if(error){
                                        app.helper.showErrorNotification({"message": error});
                                    }else{
                                        app.helper.hideModal();
                                        location.reload();
                                    }
                                }
                        );
                    }
                };
                form.vtValidate(params);
            };
            
            app.helper.showModal(loginAccountModal,{cb:callBackFunction});
        });
        
        jQuery('#setUpCardDetails').off().on('click',function(e) {
            var element = jQuery(e.currentTarget);
            var setUpCardModal = jQuery(container).find('.setUpCardModal').clone(true, true);
            setUpCardModal.removeClass('hide');
            var callback = function(data) {
                thisInstance.registerSetupCardDetailEvent(data,element);
            };
            app.helper.showModal(setUpCardModal, {cb:callback});
        });

        jQuery(container).off('click', '.oneclickInstallFree, .oneclickInstallPaid');
        jQuery(container).on('click', '.oneclickInstallFree, .oneclickInstallPaid', function(e) {
            var element = jQuery(e.currentTarget);
            var extensionContainer = element.closest('.extension_container');
            var extensionId = extensionContainer.find('[name="extensionId"]').val();
            var moduleAction = extensionContainer.find('[name="moduleAction"]').val();
            var extensionName = extensionContainer.find('[name="extensionName"]').val();

            if(element.hasClass('loginRequired')){
                var loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_INSTALLING_EXTENSION');
                app.helper.showErrorNotification({"message": loginError});
                return false;
            }
            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'ExtensionStore',
                'mode': 'oneClickInstall',
                'extensionId': extensionId,
                'moduleAction': moduleAction,
                'extensionName': extensionName
            };

            if (element.hasClass('oneclickInstallPaid')) {
                var trial = element.data('trial');
                if (!trial) {
                    var customerCardId = jQuery(container).find('[name="customerCardId"]').val();
                    if (customerCardId.length == 0) {
                        var cardSetupError = app.vtranslate('JS_PLEASE_SETUP_CARD_DETAILS_TO_INSTALL_THIS_EXTENSION');
                        app.helper.showErrorNotification({"message": cardSetupError});
                        return false;
                    }
                } else {
                    params['trial'] = trial;
                }
            }
            
            app.helper.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_INSTALL')+'?</b>'}).then(function(){
                thisInstance.getImportModuleStepView(params).then(function(installationLogData) {
                    var callBackFunction = function(data) {
                        var installationStatus = jQuery(data).find('[name="installationStatus"]').val();

                        if (installationStatus == "success") {
                            if (!trial) {
                                element.closest('span').html('<span class="alert alert-info">' + app.vtranslate('JS_INSTALLED') + '</span>');
                                extensionContainer.find('[name="moduleAction"]').val(app.vtranslate('JS_INSTALLED'));
                            } else if ((element.hasClass('oneclickInstallPaid')) && trial) {
                                thisInstance.updateTrialStatus(true, extensionName).then(function(data) {
                                    if (data.success) {
                                        element.closest('span').prepend('<span class="alert alert-info">' + app.vtranslate('JS_TRIAL_INSTALLED') + '</span> &nbsp; &nbsp;');
                                        element.remove();
                                    }
                                });
                            } else if ((element.hasClass('oneclickInstallPaid')) && (!trial)) {
                                thisInstance.updateTrialStatus(false, extensionName).then(function(data) {
                                    if (data.success) {
                                        element.closest('span').html('<span class="alert alert-info">' + app.vtranslate('JS_INSTALLED') + '</span>');
                                        extensionContainer.find('[name="moduleAction"]').val(app.vtranslate('JS_INSTALLED'));
                                    }
                                });
                            }
                        }
                    };

                    var modalData = {
                        cb: callBackFunction
                    };
                    app.helper.showModal(installationLogData, modalData);
                });
            });
        });

        jQuery(container).on('click', '#installLoader', function(e) {
            var extensionLoaderModal = jQuery(container).find('.extensionLoader').clone(true, true);
            extensionLoaderModal.removeClass('hide');

            app.showModalWindow(extensionLoaderModal);
        });
    },
    
    
    registerEventForSearchExtension : function(container) {
      var thisInstance = this; 
      container.on('keydown', '#searchExtension', function(e) {
            var currentTarget = jQuery(e.currentTarget);
            if (e.which === 13) {
                var searchTerm = jQuery.trim(currentTarget.val());
                if(!searchTerm) {
                    alert(app.vtranslate('JS_PLEASE_ENTER_SOME_VALUE'));
                    currentTarget.focus();
                    return false;
                }
                var params = {
                    'module': app.getModuleName(),
                    'parent': app.getParentModuleName(),
                    'view': 'ExtensionStore',
                    'mode': 'searchExtension',
                    'searchTerm': searchTerm,
                    'type': 'Extension'
                };

                app.helper.showProgress();
                app.request.post({data: params}).then(
                    function(error, data) {
                        app.helper.hideProgress();
                        jQuery('#extensionContainer').html(data);
                        thisInstance.registerEventForIndexView();
                    }
                );
            }
        });  
    },
    updateTrialStatus: function(trialStatus, extensionName) {
        var trialParams = {
            'module': app.getModuleName(),
            'parent': app.getParentModuleName(),
            'action': 'Basic',
            'mode': 'updateTrialMode',
            'extensionName': extensionName
        };
        if (trialStatus) {
            trialParams['trial'] = 1;
        } else {
            trialParams['trial'] = 0;
        }
        this.getImportModuleStepView(trialParams).then(function(data) {
            return data;
        });
    },
    installExtension: function(e) {
        var thisInstance = this;
        var element = jQuery(e.currentTarget);
        thisInstance.ExtensionDetails(element);
    },
    /**
     * Function to download Extension
     */
    ExtensionDetails: function(element) {
        var thisInstance = this;
        var extensionContainer = element.closest('.extension_container');
        var extensionId = extensionContainer.find('[name="extensionId"]').val();
        var moduleAction = extensionContainer.find('[name="moduleAction"]').val();
        var extensionName = extensionContainer.find('[name="extensionName"]').val();
        var params = {
            'module': app.getModuleName(),
            'parent': app.getParentModuleName(),
            'view': 'ExtensionStore',
            'mode': 'detail',
            'extensionId': extensionId,
            'moduleAction': moduleAction,
            'extensionName': extensionName
        };

        this.getImportModuleStepView(params).then(function(data) {
            var detailContentsHolder = jQuery('.contentsDiv');
            detailContentsHolder.html(data);
            thisInstance.registerEventsForExtensionStoreDetail(detailContentsHolder);
        });
    },
    /**
     * Function to register event related to Import extrension Modules in detail
     */
    registerEventsForExtensionStoreDetail: function(container) {
        var container = jQuery(container);
        var thisInstance = this;
        this.registerRaty();
        jQuery('.carousel').carousel({
            interval: 3000
        });

        container.find('#installExtension').on('click', function(e) {
            var element = jQuery(e.currentTarget);
            if(element.hasClass('loginRequired')){
                var loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_INSTALLING_EXTENSION');
                app.helper.showErrorNotification({"message": loginError});
                return false;
            }
            
            if(element.hasClass('setUpCard')){
                var paidError = app.vtranslate('JS_PLEASE_SETUP_CARD_DETAILS_TO_INSTALL_EXTENSION');
                app.helper.showErrorNotification({"message": paidError});
                return false;
            }
            
            app.helper.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_INSTALL')+'?</b>'}).then(function(){
				var extensionId = jQuery('[name="extensionId"]').val();
				var targetModule = jQuery('[name="targetModule"]').val();
				var moduleType = jQuery('[name="moduleType"]').val();
				var moduleAction = jQuery('[name="moduleAction"]').val();
				var fileName = jQuery('[name="fileName"]').val();

				var params = {
					'module': app.getModuleName(),
					'parent': app.getParentModuleName(),
					'view': 'ExtensionStore',
					'mode': 'installationLog',
					'extensionId': extensionId,
					'moduleAction': moduleAction,
					'targetModule': targetModule,
					'moduleType': moduleType,
					'fileName': fileName
				}

				thisInstance.getImportModuleStepView(params).then(function(installationLogData) {
					var callBackFunction = function(data) {
						var installationStatus = jQuery(data).find('[name="installationStatus"]').val();
						if (installationStatus == "success") {
							jQuery('#installExtension').remove();
							jQuery('#launchExtension').removeClass('hide');
							jQuery('.writeReview').removeClass('hide');
						}
						app.helper.showScroll(jQuery('#installationLog'), {'height': '150px'});
					};
					var modalData = {
						cb: callBackFunction
					};
					app.helper.showModal(installationLogData, modalData);
				});
            });
            
        });

        container.find('#uninstallModule').on('click', function(e) {
            var element = jQuery(e.currentTarget);
            var extensionName = container.find('[name="targetModule"]').val();
            if(element.hasClass('loginRequired')){
                var loginError = app.vtranslate('JS_PLEASE_LOGIN_TO_MARKETPLACE_FOR_UNINSTALLING_EXTENSION');
                app.helper.showErrorNotification({"message": loginError});
                return false;
            }
            
            app.helper.showConfirmationBox({message:'<b>'+app.vtranslate('JS_ARE_YOU_SURE_UNINSTALL')+'?</b>'}).then(function(){
                var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'action': 'Basic',
                'mode': 'uninstallExtension',
                'extensionName': extensionName
                };

                app.helper.showProgress();
                app.request.post({data: params}).then(function(error, data) {
                    if (!error) {
                        app.helper.hideProgress();
                        container.find('#declineExtension').trigger('click');
                    }
                });
            });
        });

        container.find('#declineExtension').on('click', function() {
            var params = thisInstance.getImportModuleIndexParams();
            thisInstance.getImportModuleStepView(params).then(function(data) {
                var detailContentsHolder = jQuery('.contentsDiv');
                detailContentsHolder.html(data);
                thisInstance.registerEventForIndexView();
            });
        });

        container.off().on('click', '.writeReview', function(e) {
            var customerReviewModal = jQuery(container).find('.customerReviewModal').clone(true, true);
            customerReviewModal.removeClass('hide');

            var callBackFunction = function(data) {
                var form = data.find('.customerReviewForm');
                form.find('.rating').raty();
                var params = {
                    submitHandler: function(form) {
                        var form = jQuery(form);
                        if(this.numberOfInvalids() > 0) {
                            return false;
                        }
                        var review = form.find('[name="customerReview"]').val();
                        var listingId = form.find('[name="extensionId"]').val();
                        var rating = form.find('[name="score"]').val();
                        var params = {
                            'module': app.getModuleName(),
                            'parent': app.getParentModuleName(),
                            'action': 'Basic',
                            'mode': 'postReview',
                            'comment': review,
                            'listing': listingId,
                            'rating': rating
                        }
                        app.helper.showProgress();
                        app.request.post({data: params}).then(function(error, result) {
                            app.helper.hideModal();
                            if (!error) {
                                if (result) {
                                    var html = '<div class="row" style="margin: 8px 0 15px;">' +
                                                '<div class="col-sm-3 col-xs-3">'+
                                                    '<div data-score="' + rating + '" class="rating" data-readonly="true"></div>'+
                                                    '<div>'+result.Customer.firstname + ' ' + result.Customer.lastname + '</div>'+
                                                    '<div class="muted">'+(result.createdon).substring(4) +'</div>'+
                                                 '</div>'+
                                                 '<div class="col-sm-9 col-xs-9">'+ result.comment+'</div>'+
                                                '</div><hr>';
                                    container.find('.customerReviewContainer').append(html);
                                    thisInstance.registerRaty();
                                }
                                app.helper.hideProgress();
                            }else{
                                app.helper.hideProgress();
                                app.helper.showErrorNotification({"message":error});
                                return false;
                            }
                        });
                    }
                };
                form.vtValidate(params);
            }
            
            var params = {};
            params.cb = callBackFunction;

            app.helper.showModal(customerReviewModal,params);
        });
    },
    
   registerSetupCardDetailEvent : function(modalData,element) {
        var thisInstance = this;
        var container = thisInstance.getContainer();
        jQuery(modalData).on('click', '[name="resetButton"]', function(e) {
            jQuery(modalData).find('[name="cardNumber"],[name="expMonth"],[name="expYear"],[name="cvccode"]').val('');
        });
        var form = modalData.find('.setUpCardForm');
        var params = {
            submitHandler: function(form) {
                var form = jQuery(form);
                // to Prevent submit if already submitted
                form.find("button[name='saveButton']").attr("disabled","disabled");
                if(this.numberOfInvalids() > 0) {
                    return false;
                }
                
                var formData = form.serializeFormData();
                app.helper.showProgress();
                app.request.post({data: formData}).then(
                        function(error, result) {
                            if (!error) {
                                jQuery(container).find('[name="customerCardId"]').val(result.id);
                                app.helper.hideProgress();
                                jQuery(container).find('.viewCardInfoModal').find('.cardNumber').html(result['number']);
                                var expiryDate = result['expmonth']+'-'+result['expyear'];
                                jQuery(container).find('.viewCardInfoModal').find('.expiryDate').html(expiryDate);
                                if(typeof element !== 'undefined') {
                                    element.html(app.vtranslate('JS_UPDATE_CARD_DETAILS'));
                                    element.attr('id','updateCardDetails');
                                }
                                thisInstance.registerShowCardInfoEvent();
                                app.helper.hidePopup();
                                app.helper.hideModal();
                                app.helper.showSuccessNotification({"message": app.vtranslate('JS_CARD_DETAILS_UPDATED')});
                            } else {
                                app.helper.hideProgress();
                                app.helper.showErrorNotification({"message": error});
                                form.find('.saveButton').removeAttr('disabled');
                                return false;
                            }
                        }
                );
            }
        };
        form.vtValidate(params);
   }, 
   
   registerShowCardInfoEvent : function(){
       var thisInstance = this;
       var container =  thisInstance.getContainer();
       jQuery('#updateCardDetails').off().on('click',function(e){
            var cardInfoModal = jQuery(container).find('.viewCardInfoModal').clone(true, true);
            cardInfoModal.removeClass('hide');
            app.helper.showProgress();
            
            
            var callBackFunction = function(data){
                data.on('click','.updateBtn',function(){
                    var setupcardModal = jQuery(container).find('.setUpCardModal').clone(true,true);
                    setupcardModal.removeClass('hide');
                    app.helper.hideModal();
                    app.helper.showPopup(setupcardModal);
                    thisInstance.registerSetupCardDetailEvent(setupcardModal);    
                });
            };
            
            app.helper.showModal(cardInfoModal);
            app.helper.hideProgress();
            if (typeof callBackFunction == 'function') {
                callBackFunction(cardInfoModal);
            }
        });
   },
   
    registerExtensionTabs : function(container) {
        var thisInstance = this;
        container.on('click', '.extensionTab', function(e){
            var element = jQuery(e.currentTarget);
            var params = {
                    'module': app.getModuleName(),
                    'parent': app.getParentModuleName(),
                    'view': 'ExtensionStore',
                    'mode': 'getExtensionByType',
                    'type': element.data('type')
            };

            app.helper.showProgress();
            app.request.post({data: params}).then(
                function(error, data) {
                    jQuery('.extensionTab').removeClass('active').removeClass('btn-primary');
                    element.addClass('active').addClass('btn-primary');
                    app.helper.hideProgress();
                    jQuery('#extensionContainer').html(data);
                    thisInstance.registerEventForIndexView();
                }
            );
        });    
    },
    
    registerEvents: function() {
        var container = jQuery('.contentsDiv');
        this._super();
        this.registerEventForIndexView();
        this.registerEventForSearchExtension(container);
        this.registerExtensionTabs(container);
    }
});

jQuery(document).ready(function() {
    var settingExtensionStoreInstance = new Settings_ExtensionStore_ExtensionStore_Js();
    var mode = jQuery('[name="mode"]').val();
    if(mode == 'detail'){
        settingExtensionStoreInstance.registerEventsForExtensionStoreDetail(jQuery('.contentsDiv'));
    }
});
