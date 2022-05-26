/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Vtiger_FindDuplicates_Js",{
    
    massDeleteRecords : function(url) {
		var listInstance = app.controller();
        var fields = jQuery('#duplicateSearchFields').val();
        var ignoreEmpty = jQuery('#ignoreEmpty').val();
        url += '&mode=FindDuplicates&fields=' + fields + '&ignoreEmpty=' + ignoreEmpty;
		listInstance.performMassDeleteRecords(url);
	}
    
},{
   
   
    /**
     * Function to update selected checkboxes after pagination
     */
    updateSelectedCheckboxes : function() {
        var thisInstance = this;
        var selectedIds = thisInstance.readMergeSelectedIds();
        if(typeof selectedIds != 'undefined') {
            jQuery('[name=mergeRecord]').each( function(index,element) {
                var id = "id" + jQuery(element).data('id');
                if(selectedIds[id]){
                    jQuery(this).attr('checked', true);
                }
            });
        }
    },
    
    loadListViewRecords : function(params) {        
        var self = this;
        var aDeferred = jQuery.Deferred();
        var container = this.getListViewContainer();
        if(typeof params == "undefined") {
            params = {};
        }
        if(typeof params.page == "undefined"){
            params.page = jQuery('#pageNumber').val();
        }
            
		var fields = jQuery('#duplicateSearchFields').val();
		var moduleName = app.module();
		var ignoreEmpty = jQuery('#ignoreEmpty').val();
		var url = 'index.php?module='+moduleName+'&view=FindDuplicates&fields='+fields+'&ignoreEmpty='+ignoreEmpty+'&page='+params.page;
        app.helper.showProgress();
        app.request.pjax({'url':url}).then(function(error,data) {
            app.helper.hideProgress();
            var selectDeselectContainer = container.find('.select-deselect-container').html();
            jQuery('#listViewContent').html(data);
            container.find('.select-deselect-container').html(selectDeselectContainer);
            self.markSelectedIdsCheckboxes();
            self.updateSelectedCheckboxes();
            aDeferred.resolve();
        });
        return aDeferred.promise();
    },
    
    loadFilter : function(cvid) {
		window.location.href = 'index.php?module='+this.getModuleName()+'&view=List&viewname='+cvid;
		return;
    },

	/**
	 * Function registers event for merge button
	 */
	registerMergeRecordEvent : function(cb) {
		var thisInstance = this;
        var container = this.getListViewContainer();
		container.on('click','input[name="merge"]', function(e) {
			var element = jQuery(e.currentTarget);
			var groupName = element.data('group');
			var mergeRecordsCheckBoxes = jQuery('input[name="mergeRecord"]:checked');
            var mergeSelectedIds = thisInstance.readMergeSelectedIds();
            var mergeSelectedIdsLength = Object.keys(mergeSelectedIds).length;
			if(mergeRecordsCheckBoxes.length < 2 && mergeSelectedIdsLength < 2) {
				app.helper.showErrorNotification({message : app.vtranslate('JS_SELECT_ATLEAST_TWO_RECORD_FOR_MERGING')});
				return false;
			} else {
				var count = 0;
				var records = [];
				var stop = false;
                
                mergeRecordsCheckBoxes.each(function(key, obj) {
                    var ele = jQuery(obj);
                    if(ele.data('group') != groupName) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_RECORDS_TO_MERGE_FROM_SAME_GROUP')});
                        stop = true;
                        return false;
                    }
                });

                if(!stop){
                    if(!thisInstance.checkArrayValue(mergeSelectedIds)) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_RECORDS_TO_MERGE_FROM_SAME_GROUP')});
                        stop = true;
                        return false;
                    } else {
                        for (var x in mergeSelectedIds) {
                            var id = x.replace('id','');
                            records.push(id);
                            count++;
                        }
                    }
                }
				if(stop) return false;
				if(count > 3) {
					app.helper.showErrorNotification({message: app.vtranslate('JS_ALLOWED_TO_SELECT_MAX_OF_THREE_RECORDS')});
					return false;
				}
                app.event.trigger('Request.MergeRecords.show',{'records':records});
                
			}
		});
	},
    
    /** 
    * Function to check if all the values in array are same 
    * @param <array> array 
    * @retrun <boolean> true or false 
    */ 
    checkArrayValue : function(array) { 
        var first; 
        for(first in array) {  
            first = array[first]; 
            break; 
        } 
        for(var x in array) { 
            if(array[x] != first) 
                return false; 
        } 
        return true; 
    },
    
    /**
     * Function to read the selected merge ids
     */
    readMergeSelectedIds : function(decode){
		var cvId = this.getCurrentCvId();
		var selectedIdsElement = jQuery('#mergeSelectedIds');
		var selectedIdsDataAttr = cvId+'Selectedids';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		if (!(selectedIdsDataAttr in selectedIdsElementDataAttributes) ) {
			var selectedIds = new Array();
			this.writeMergeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if(decode == true){
			if(typeof selectedIds == 'object'){
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
    
    writeMergeSelectedIds : function(selectedIds){
		var cvId = this.getCurrentCvId();
		jQuery('#mergeSelectedIds').data(cvId+'Selectedids',selectedIds);
	},
    
    removeMergeSelectedIds : function() {
        var cvId = this.getCurrentCvId();
		jQuery('#mergeSelectedIds').removeData(cvId+'Selectedids');
    },
    
     /**
	 * Function  to register click event for merge check check box.
	 */
	registerMergeCheckBoxClickEvent : function(){
		var listViewPageDiv = this.getListViewContainer();
		var thisInstance = this;
		listViewPageDiv.on('click','[name=mergeRecord]',function(e){
			var selectedIds = thisInstance.readMergeSelectedIds();
            if(typeof selectedIds  == 'undefined') {
                var selectedIds = [];
            }
			var elem = jQuery(e.currentTarget);
			if(elem.is(':checked')){
                var id = elem.data('id');
                if(typeof selectedIds["id"+id] == 'undefined') {
                    var searchFields = JSON.parse(jQuery('#duplicateSearchFields').val());
                    var value = [];
                    for (var x in searchFields) {
                        var dataValue = elem.closest('.listViewEntries').find('[name='+searchFields[x]+']').data('value');
                        var dataValue = jQuery.trim(dataValue);
                        if(isNaN(dataValue)) {
                            value.push(dataValue.toLowerCase());
                        } else {
                            value.push(dataValue);
                        }
                    }
					selectedIds["id"+id] = JSON.stringify(value); // added id to avoid extra undefined values
				}
			} else {
                var id = elem.data('id')
                delete selectedIds["id"+id];
			}
			thisInstance.writeMergeSelectedIds(selectedIds);
		});
	},
    
    registerPostMergeListerEvent : function () {
        var self = this;
        app.event.on('post.MergeRecords',function(e,data){
            self.removeMergeSelectedIds();
            self.loadListViewRecords();
        });
    },

	/**
	 * Function registers various events for duplicate search
	 */
	registerEvents : function() {
		this.registerMergeRecordEvent();
		this.registerListViewMainCheckBoxClickEvent();
        var recordSelectTrackerObj = this.getRecordSelectTrackerInstance();
        recordSelectTrackerObj.registerEvents();
		this.registerCheckBoxClickEvent();
        
		this.registerSelectAllClickEvent();
		this.registerDeSelectAllClickEvent();
        this.registerPostMergeListerEvent();
        this.registerMergeCheckBoxClickEvent();
		
		//To Register Pagination Events
		this.initializePaginationEvents();
	},

	/**
	 * Function returns current view name for the module
	 */
	getCurrentCvId : function(){
		return jQuery('#viewName').val();
	},

	/**
	 * Function gets the record count
	 */
	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
        var module = app.getModuleName();
        var parent = app.getParentModuleName();
        var fields = jQuery('#duplicateSearchFields').val();
        var ignoreEmpty = jQuery('#ignoreEmpty').val();
        var postData = {
            "module": module, "parent": parent,
            "view": "FindDuplicatesAjax", "mode": "getRecordsCount",
            "fields": fields, "ignoreEmpty":ignoreEmpty
        }
        app.request.get({'data':postData}).then(
            function(error,data) {
                aDeferred.resolve(data);
            }
        );
		return aDeferred.promise();
	}
});
