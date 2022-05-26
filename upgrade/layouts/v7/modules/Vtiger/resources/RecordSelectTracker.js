/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_RecordSelectTracker_Js",{
    getInstance : function(){
        var recordSelectTrackerObj = new Vtiger_RecordSelectTracker_Js;
        return recordSelectTrackerObj;
    },
},
{    
    selectedIds : [],
    selectAllMode: false,
    excludedIds : [],
    cvId : '',
    
    setCvId : function (cvid){
       this.cvId = cvid;
    },
    
    registerRowCheckListener : function(){
        var thisInstance = this;
        jQuery(document).on('Post.ListRow.Checked', 'tr.listViewEntries',function(e,args){ 
            if(thisInstance.selectAllMode){
                thisInstance.excludedIds.splice( $.inArray(args.id, thisInstance.excludedIds), 1 );
            }
            else{
                if(jQuery.inArray(args.id, thisInstance.selectedIds) == -1){
                    thisInstance.selectedIds.push(args.id);
                }
            }
        });
        jQuery(document).on('Post.ListRow.UnChecked', 'tr.listViewEntries',function(e,args){          
            if(thisInstance.selectAllMode){
                if(jQuery.inArray(args.id, thisInstance.excludedIds) == -1){
                    thisInstance.excludedIds.push(args.id);
                }
            }
            else{
                thisInstance.selectedIds.splice( $.inArray(args.id, thisInstance.selectedIds),1);
            }
        });
    },
    
    registerListSelectAllListener: function(){
        var thisInstance = this;
        jQuery(document).on('Post.ListSelectAll',function(e,args){
            thisInstance.selectedIds = [];
            thisInstance.selectAllMode = args.mode;
            thisInstance.cvId = args.cvId;
        });   
        jQuery(document).on('Post.ListDeSelectAll',function(e,args){
            thisInstance.selectAllMode = args.mode;
            thisInstance.clearList();
        }); 
    },
    
            getSelectedAndExcludedIds: function(jsonDecode) {
                var selectedIds = this.getSelectedIds();
                if (selectedIds == undefined || selectedIds == null || selectedIds.length == 0) {
                    return false;
                }
                if (this.selectAllMode != true && jsonDecode) {
                    selectedIds = JSON.stringify(selectedIds)
                }
                var params = {
                    'selected_ids': selectedIds,
                    'excluded_ids': this.getExcludedIds(jsonDecode),
                    'viewname': this.getCvid()
                }
                return params;
            },
    
    getSelectedIds : function(){
        if (this.selectAllMode == true) {
            return  'all';
        }
        return this.selectedIds;
    },
    
    getCvid : function(){
        return this.cvId;
    },
    
    getExcludedIds : function(jsonDecode){
        if(jsonDecode){
            return JSON.stringify(this.excludedIds)
        }
        return this.excludedIds;
    },
    
    getSelectAllMode: function(){
        return this.selectAllMode;
    },
    
    clearList : function(){
        this.selectedIds = []; 
        this.excludedIds = [];
        this.selectAllMode = false;
        this.cvId = '';
        
    },
    
    registerEvents : function() {
        this.registerRowCheckListener();
        this.registerListSelectAllListener();
        
    },
});
