/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_ModuleManager_ExtensionStore_Js', {
}, {
    
    /**
    * Function to get import module step1 params
    */
   getExtensionStoreStep1Params : function(){
       var params = {
                   'module' : app.getModuleName(),
                   'parent' : app.getParentModuleName(),
                   'view' : 'ExtensionStore',
               };
       return params;
   },

    /**
    * Function to register raty
    */
   registerRaty : function() {
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
    * Function to get import module with respect to view
    */
   getImportModuleStepView : function(params){
       var aDeferred = jQuery.Deferred();
       var progressIndicatorElement = jQuery.progressIndicator({
               'position' : 'html',
               'blockInfo' : {
                       'enabled' : true
               }
       });

       AppConnector.request(params).then(
               function(data) {
                       progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                       aDeferred.resolve(data);
               },
               function(error) {
                       progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                       aDeferred.reject(error);
               }
       );
       return aDeferred.promise();
   },
   
   registerEventsForStep1 : function(container){
       var thisInstance = this;
       jQuery(container).on('keydown','#searchExtension',function(e){
            var currentTarget = jQuery(e.currentTarget);
            var code = e.keyCode;
            if(code == 13){
                var searchTerm = currentTarget.val();
                
                var params = {
                    'module' : app.getModuleName(),
                    'parent' : app.getParentModuleName(),
                    'view' : 'ExtensionStore',
                    'mode' : 'searchExtension',
                    'searchTerm' : searchTerm
                };

                var progressIndicatorElement = jQuery.progressIndicator({
                        'position' : 'html',
                        'blockInfo' : {
                                'enabled' : true
                        }
                });
                AppConnector.request(params).then(
                    function(data) {
                            jQuery('#extensionContainer').html(data);
                            thisInstance.registerRaty();
                            progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    },
                    function(error) {
                            progressIndicatorElement.progressIndicator({'mode' : 'hide'});
                    }
                );
            }
        });
        
        jQuery(container).on('click','.extensionDetails', function(e){
            var element  = jQuery(e.currentTarget);
            var extensionContainer = element.closest('.extension_container');
            var extensionId = extensionContainer.find('[name="extensionId"]').val();
            var moduleAction = extensionContainer.find('[name="moduleAction"]').val(); 
            var extensionName = extensionContainer.find('[name="extensionName"]').val();
            var params = {
                    'module' : app.getModuleName(),
                    'parent' : app.getParentModuleName(),
                    'view' : 'ExtensionStore',
                    'mode' : 'extensionDetail',
                    'extensionId' : extensionId,
                    'moduleAction' : moduleAction,
                    'extensionName' : extensionName
            };
            thisInstance.getImportModuleStepView(params).then(function(data){
                    var detailContentsHolder = jQuery('.contentsDiv');
                    detailContentsHolder.html(data);
                    thisInstance.registerEventsForImportModuleStep2(detailContentsHolder);
            });
        });
        
         jQuery(container).on('click','#installLoader', function(e){
             console.log('install loader click');
             var extensionLoaderModal = jQuery(container).find('.extensionLoader').clone(true, true);
             extensionLoaderModal.removeClass('hide');
             
             var callBackFunction = function(data) {
                 
             };
             app.showModalWindow(extensionLoaderModal,function(data) {
                if(typeof callBackFunction == 'function') {
                    callBackFunction(data);
                }
            }, {'width':'1000px'});
         });
   },
   
   /**
    * Function to register event related to Import extrension Modules in step2
    */
   registerEventsForImportModuleStep2 : function(container){
           var thisInstance = this;
           var container = jQuery(container);
           app.showScrollBar(jQuery('div.scrollableTab'), {'width': '100%', 'height':'400px'});
           this.registerRaty();
           slider =  jQuery('#imageSlider').bxSlider({
                   auto: true,
                   pause: 1000,
                   randomStart : true,
                   autoHover: true
           });
           jQuery("#screenShots").on('click',function() { 
               slider.reloadSlider();
           });
           
           container.find('#declineExtension').on('click',function(){
                var params = thisInstance.getExtensionStoreStep1Params();
                thisInstance.getImportModuleStepView(params).then(function(data){
                var detailContentsHolder = jQuery('.contentsDiv');
                detailContentsHolder.html(data);
                        thisInstance.registerEventForStep1();
                });
            });
   },
        
    /**
    * Function to register event for step1 of import module
    */
   registerEventForStep1 : function(){
           this.registerRaty();
           var detailContentsHolder = jQuery('.contentsDiv');
           app.showScrollBar(jQuery('.extensionDescription'), {'height':'120px','width':'100%','railVisible': true});
           this.registerEventsForStep1(detailContentsHolder);
   },
        
    registerEvents : function(){
        this.registerEventForStep1();
    }
});