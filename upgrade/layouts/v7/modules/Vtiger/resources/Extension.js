/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Vtiger_Extension_Js",{}, {
    
    
    // Protected Globals
	// Copy meta to avoid runtime tampering.
	_module  : _EXTENSIONMETA.module,
	_view    : _EXTENSIONMETA.view,

    getExtensionModule : function() {
        return this._module;
    },
    
    getExtensionView : function() {
        return this._view;
    },
    
    init : function() {
        this.addComponents();
    },
    
    // To clear sorting information before changing Custom View
    resetData : function() {
		var listInstance = Vtiger_List_Js.getInstance();
        var container = listInstance.getListViewContainer();
        container.find('#pageNumber').val("1");
        container.find('#pageToJump').val('1');
        container.find('#orderBy').val('');
        container.find("#sortOrder").val('');
    },
    
    loadFilter: function(id, mode) {
		if (typeof mode == 'undefined') mode = false;
		var url = 'index.php?module='+app.getModuleName()+'&view=List'+
				  '&viewname='+id+'&mode='+mode;
        window.location.href = url;
    },
	
    addComponents : function() {
        this.addModuleSpecificComponent('CustomView');
        this.addModuleSpecificComponent('ListSidebar');
        this.addComponent('Vtiger_Index_Js');
        this.addComponent(this.getExtensionModule() + "_" + this.getExtensionView() + "_Js");
    },
    
    registerEvents : function() {
//        if(jQuery('#listViewContent').find('table.listview-table').length){
//            if(jQuery('.sticky-wrap').length == 0){
//                stickyheader.controller();
//				var listInstance = new Vtiger_List_Js.getInstance();
//                var container = listInstance.getListViewContainer();
//                container.find('.sticky-thead').addClass('listview-table');
//                app.helper.dynamicListViewHorizontalScroll();
//            }
//        }

		if(window.hasOwnProperty('Vtiger_List_Js')) {
			var listInstance = new Vtiger_List_Js();
			setTimeout(function(){
				listInstance.registerFloatingThead();
			}, 10);

			app.event.on('Vtiger.Post.MenuToggle', function() {
				listInstance.reflowList();
			});
			listInstance.registerDynamicDropdownPosition();
		}
    }
});