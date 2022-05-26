/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Settings_Vtiger_List_Js("Settings_Workflows_List_Js", {
    
    triggerCreate : function(url) {
        var selectedModule = jQuery('#moduleFilter').val();
        if(selectedModule.length > 0) {
            url += '&source_module='+selectedModule
        }
        window.location.href = url;
    }
}, {
   
    registerFilterChangeEvent: function () {
        var thisInstance = this;
        var container = this.getListViewContainer();
        container.on('change', '#moduleFilter', function (e) {
            jQuery('#pageNumber').val("1");
            jQuery('#pageToJump').val('1');
            jQuery('#orderBy').val('');
            jQuery("#sortOrder").val('');
            var params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                sourceModule: jQuery(e.currentTarget).val()
            }
            thisInstance.loadListViewRecords(params);
        });
    },
    
    loadListViewRecords : function(urlParams) {
        var self = this;
        var aDeferred = jQuery.Deferred();
        var defParams = this.getDefaultParams();
        if(typeof urlParams == "undefined") {
            urlParams = {};
        }
        urlParams = jQuery.extend(defParams, urlParams);
        app.helper.showProgress();
        app.request.pjax({data:urlParams}).then(function(err, res){
            self.placeListContents(res);
            app.helper.hideProgress();
            jQuery("input[name='workflowstatus']").bootstrapSwitch();
            aDeferred.resolve(res);
		});
        return aDeferred.promise();
    },
    
    registerRowClickEvent : function(){
        var thisInstance = this;
		var listViewContentDiv = this.getListViewContainer();
        
        listViewContentDiv.on('click','.listViewEntries',function(e){
            var elem = jQuery(e.currentTarget);
            var targetElem = jQuery(e.target);
            if(targetElem.closest('.bootstrap-switch').length != 0){
                return false;
            }
			if(targetElem.closest('.deleteRecordButton').length != 0){
				return;
			}
            var recordUrl = elem.data('recordurl');
            if(typeof recordUrl == 'undefined') {
                return;
            }
            
			var postData = thisInstance.getDefaultParams();
			for(var key in postData) {
				if(postData[key]) {
					postData['return'+key] = postData[key];
					delete postData[key];
				} else {
					delete postData[key];
				}
			}
            window.location.href = recordUrl +'&'+ $.param(postData);
        });
    },
   
    getListViewContainer: function () {
        if (this.listViewContainer === false) {
            this.listViewContainer = jQuery('#list-content');
        }
        return this.listViewContainer;
    },
   
    placeListContents : function(contents) {
        var container = this.getListViewContainer();
        container.html(contents);
        this.registerSelect2ForModuleFilter();
    },
    
    registerSelect2ForModuleFilter : function() {
        vtUtils.showSelect2ElementView(jQuery('#moduleFilter'), {
            formatResult: function(result) {
                var element = jQuery(result.element);
                var count = element.data('count');
                if (!count) {
                    count = 0;
                }

                return result.text + "&nbsp; - &nbsp;" + count;
            },
            formatSelection: function(result) {
                var element = jQuery(result.element);
                var count = element.data('count');
                if (!count) {
                    count = 0;
                }

                return result.text
                + "&nbsp;&nbsp;<span class='label-success badge' style='display: inline;'>"
                + count
                + "</span>";
            }
        });
    },
   
    registerEventForChangeWorkflowState: function (listViewContainer) {
        jQuery(listViewContainer).on('switchChange.bootstrapSwitch', "input[name='workflowstatus']", function (e) {
            var currentElement = jQuery(e.currentTarget);
            if(currentElement.val() == 'on'){
                currentElement.attr('value','off');
            } else {
                currentElement.attr('value','on');
            }
            var params = {
                module : app.getModuleName(),
                parent : app.getParentModuleName(),
                'action' : 'SaveAjax',
                'record' : currentElement.data('id'),
                'status' : currentElement.val()
            }
         
            app.request.post({
                data:params
            }).then(function(error,data){
                if(data){
                    app.helper.showSuccessNotification({
                        message : app.vtranslate('JS_WORKFLOWS_STATUS_CHANGED')
                        });
                }
            });
        });
    },
   
    getDefaultParams : function() {
        var container = this.getListViewContainer();
        var pageNumber = container.find('#pageNumber').val();
        var module = this.getModuleName();
        var parent = app.getParentModuleName();
        var params = {
            'module': module,
            'parent': parent,
            'page' : pageNumber,
            'view' : "List",
            'sourceModule' : jQuery('#moduleFilter').val(),
            'search_value' : jQuery('.searchWorkflows').val(),
            'search_key' : jQuery('.searchWorkflows').val()
        }
        return params;
    },
   
    registerSearch : function() {
        var thisInstance = this;
        var container = this.getListViewContainer();
        container.on('keyup', '.searchWorkflows', function(e) {
            if(e.which == 13) {
                thisInstance.loadListViewRecords({page: 1});
            }
        });
    },
    /**
     * Function shows and hide when user enter on a row and leave respectively
     * @returns {undefined}
     */
   registerShowDeleteActionOnHover: function(){
       var listViewContentDiv = this.getListViewContainer();
        listViewContentDiv.on('mouseover','tr.listViewEntries',function(e){
            jQuery(e.currentTarget).find('.deleteRecordButton').css('opacity',0.6);
        }).on('mouseleave','tr.listViewEntries',function(e){
            jQuery(e.currentTarget).find('.deleteRecordButton').css('opacity',0);
        });
   },
    registerEvents: function () {
        var thisInstance = this;
        this._super();
        this.registerRowClickEvent();
        this.registerFilterChangeEvent();
        this.registerDeleteRecordClickEvent();
        var listViewContainer = this.getListViewContainer();
        this.registerShowDeleteActionOnHover();
        if (listViewContainer.length > 0) {
            jQuery("input[name='workflowstatus']").bootstrapSwitch();
            this.registerEventForChangeWorkflowState(listViewContainer);
            this.registerSearch();
            this.registerSelect2ForModuleFilter();
        }
    }
});