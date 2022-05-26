/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js('Vtiger_AdvanceSearchList_Js',{},{

    addComponents : function() {
        this.addModuleSpecificComponent('Pagination');
        this.intializeComponents();
    },
    
    getDefaultParams : function () {
        var defaultParams = this._super();
        defaultParams.nolistcache = 1;
        defaultParams.view = 'ListAjax';
        defaultParams.mode = 'showSearchResults';
        defaultParams.parent = '';
        defaultParams._onlyContents = true;
        return defaultParams;
    },
    
    loadFilter: function(id, loadParams) {
		if (typeof loadParams == 'undefined') loadParams = {};
        
		var params = {
			module: this.getModuleName(), 
			view  : 'ListAjax',
			viewname : id
		}
        
        // Added to handle remove sorting
        var mode = loadParams.mode;
        loadParams.mode = 'showSearchResults';
        loadParams.listMode = mode;
        loadParams.parent = '';
        params = jQuery.extend(params, loadParams);
        this.loadListViewRecords(params);
    },
    
    registerEditLink : function() {
        jQuery('#searchResults-container').on('click', 'a[name="editlink"]', function(e)  {
			var element = jQuery(e.currentTarget);
			var url = element.data('url');
			var listInstance = Vtiger_List_Js.getInstance();
			var postData = listInstance.getDefaultParams();
			postData['view'] = app.view();
			var recordId = app.getRecordId();
			if(!recordId) {
				recordId = jQuery('[name="record"]').val();
			}
			if(recordId && typeof recordId != "undefined") {
				postData['record'] = recordId;
			}
			if(postData['module'] == 'Workflows' && postData['view'] == 'Edit') {
				postData['mode'] = 'V7Edit';
			}
			for(var key in postData) {
				if(postData[key]) {
					postData['return'+key] = postData[key];
					delete postData[key];
				} else {
					delete postData[key];
				}
			}
			e.preventDefault();
			e.stopPropagation();
			window.location.href = url +'&'+ $.param(postData);
		});
	},
   
    registerDeleteRecordClickEvent :function(){
		var thisInstance = this;
        jQuery('#searchResults-container').off('click');
        jQuery('#searchResults-container').on('click', '.deleteRecordButton', function(e){
            var elem = jQuery(e.currentTarget);
			var parent = elem;
			var params = {};

            var originalDropDownMenu = elem.closest('.dropdown-menu').data('original-menu');
			if(originalDropDownMenu && typeof originalDropDownMenu != 'undefined') {
				parent = app.helper.getDropDownmenuParent(originalDropDownMenu);

				var moduleName = jQuery('#searchModuleList').val();
				if(moduleName && typeof moduleName != 'undefined') {
					params['module'] = moduleName;
				}
			}

            var recordId = parent.closest('tr').data('id');
			thisInstance.deleteRecord(recordId, params);
//			e.stopPropagation();
		});
	},
    
    registerDropdownPosition :function() {
        if(jQuery('.searchResults').height() <= 450){
            jQuery('.searchResults').css('padding-bottom',"100px");
        };
       var container= jQuery('.searchResults');
        jQuery('.table-actions').on('click', '.dropdown', function (e) {
            var containerTarget = jQuery(this).closest(container);
            var dropdown = jQuery(e.currentTarget);
             if(dropdown.find('[data-toggle]').length <=0){ 
 		                return; 
 		            } 
            var dropdown_menu = dropdown.find('.dropdown-menu');

            var dropdownStyle = dropdown_menu.find('li a');
            dropdownStyle.css('padding', "0 6px", 'important');

            var fixed_dropdown_menu = dropdown_menu.clone(true);
            fixed_dropdown_menu.data('original-menu',dropdown_menu); 
            dropdown_menu.css('position', 'relative');
            dropdown_menu.css('display', 'none');
            var currtargetTop;
            var currtargetLeft;
            var dropdownBottom;
            var ftop = 'auto';
            var fbottom = 'auto';
                var ctop = jQuery(container).offset().top;
                currtargetTop = dropdown.offset().top - ctop + 30 + dropdown.height();
                currtargetLeft = dropdown.offset().left;
                dropdownBottom = jQuery('.searchResults').height() - currtargetTop + 40 + dropdown.height();
            
            var windowBottom = jQuery(window).height() - dropdown.offset().top;
            if (windowBottom < 250 ) {
                ftop = 'auto';
                fbottom = dropdownBottom + 'px';
            }
            else {
                ftop = currtargetTop + 'px';
                fbottom = "auto";
            }
                fixed_dropdown_menu.css({
                'display': 'block',
                'position': 'absolute',
                'top': ftop,
                'left': currtargetLeft + 'px',
                'bottom': fbottom
            }).appendTo(containerTarget);
           
            dropdown.on('hidden.bs.dropdown', function () {
                dropdown_menu.removeClass('invisible');
                fixed_dropdown_menu.remove();
            });
        }); 
    },
	
	getListSearchParams : function(includeStarFilters) {
		var searchParams = JSON.parse(jQuery('#searchResults-container').find('[name="currentSearchParams"]').val());
		for(var index in searchParams) {
			if(isNaN(index)) {
				delete searchParams[index];
			}
		}
		if(includeStarFilters) {
            searchParams = this.addStarSearchParams(searchParams);
        }
		return searchParams;
	},
});