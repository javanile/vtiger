/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Vtiger_ListSidebar_Js',{},{
    
    
    registerFilterSeach : function () {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.find('.search-list').on('keyup',function(e){
            var element = jQuery(e.currentTarget);
            var val = element.val().toLowerCase();
            filters.find('.toggleFilterSize').removeClass('hide');
            jQuery('li.listViewFilter').each(function(){
                var filterEle = jQuery(this);
                var filterName = filterEle.find('a.filterName').html();
                var listsMenu = filterEle.closest('ul.lists-menu');
                if(typeof filterName != 'undefined') {
                    filterName = filterName.toLowerCase();
                    if(filterName.indexOf(val) === -1){
                        filterEle.addClass('filter-search-hide').removeClass('filter-search-show');    
                        if(listsMenu.find('li.listViewFilter').filter(':visible').length == 0) {
                            listsMenu.closest('.list-group').addClass('hide');
                        }
                        if(jQuery('#module-filters').find('ul.lists-menu li').filter(':visible').length == 0) {
                            jQuery('#module-filters').find('.noLists').removeClass('hide');
                        }
                    }else{
                        if(val) {
                            listsMenu.closest('.list-group').find('.toggleFilterSize').addClass('hide');
                        }
                        filterEle.removeClass('filter-search-hide').addClass('filter-search-show');
                        listsMenu.closest('.list-group').removeClass('hide');
                        jQuery('#module-filters').find('.noLists').addClass('hide');
                    }
                }
            });
        })
    },
    
	registerFilters: function() {
		var self = this;
        var filters = jQuery('.module-filters').not('.module-extensions');
        var scrollContainers = filters.find(".scrollContainer");
        // applying scroll to filters, tags & extensions
        jQuery.each(scrollContainers,function(key,scroll){
            var scroll = jQuery(scroll);
            var listcontentHeight = scroll.find(".list-menu-content").height();
            scroll.css("height",listcontentHeight);
            scroll.perfectScrollbar({});
        })
        
        this.registerFilterSeach();
        filters.on('click','.listViewFilter', function(e){
			e.preventDefault();
            var targetElement = jQuery(e.target);
            if(targetElement.is('.dropdown-toggle') || targetElement.closest('ul').hasClass('dropdown-menu') ) return;
            var element = jQuery(e.currentTarget);
            var el = jQuery('a[data-filter-id]',element);
            self.getParentInstance().resetData();
            self.unMarkAllFilters();
            self.unMarkAllTags();
            el.closest('li').addClass('active');
            self.getParentInstance().filterClick = true;
            self.getParentInstance().loadFilter(el.data('filter-id'), {'page' : ''});
			var filtername = jQuery('a[class="filterName"]',element).text();
			jQuery('.module-action-content').find('.filter-name').html('&nbsp;&nbsp;<span class="fa fa-angle-right" aria-hidden="true"></span>').text(filtername);
        });
        
        jQuery('#createFilter').on('click',function(e){
            var element = jQuery(e.currentTarget);
            element.trigger('post.CreateFilter.click',{'url':element.data('url')});
        });
        
        filters.on('click','li.editFilter,li.duplicateFilter',function(e){
            var element = jQuery(e.currentTarget);
            if(typeof element.data('url') == "undefined") return;
            element.trigger('post.CreateFilter.click',{'url':element.data('url')});
        });
        
        filters.on('click','li.deleteFilter',function(e){
            var element = jQuery(e.currentTarget);
            if(typeof element.data('url') == "undefined") return;
            element.trigger('post.DeleteFilter.click',{'url':element.data('url')});
        });
        
        filters.on('click','li.toggleDefault',function(e){
            var element = jQuery(e.currentTarget);
            element.trigger('post.ToggleDefault.click',{'url':element.data('url')});
        });
        
        filters.on('post.DeletedFilter',function(e){
            var element = jQuery(e.target);
            var popoverId = element.closest('.popover').attr('id');
            var ele = jQuery('.list-group' ).find("[aria-describedby='" + popoverId + "']");
            ele.closest('.listViewFilter').remove();
            element.closest('.popover').remove();
        });
        
        filters.on('post.ToggleDefault.saved',function(e,params){
            var element = jQuery(e.target);
            var popoverId = element.closest('.popover').attr('id');
            var ele = jQuery('.list-group').find("[aria-describedby='" + popoverId + "']");
            if (params.isdefault === "1") {
                element.data('isDefault', true);
                var check = element.closest('.popover').find('.toggleDefault i').removeAttr('class').addClass('fa fa-check-square-o');
                var class1 = ele.closest('[rel="popover"]').removeAttr('toggleClass').attr('toggleClass', 'fa fa-check-square-o');
                element.closest('.popover').html($(".popover-content").html()).css("padding", "10px");
            }

            else {
                element.data('isDefault', false);
                var check = element.closest('.popover').find('.toggleDefault i').removeAttr('class').addClass('fa fa-square-o');
                var class1 = ele.closest('[rel="popover"]').removeAttr('toggleClass').attr('toggleClass', 'fa fa-square-o');
                element.closest('.popover').html($(".popover-content").html()).css("padding", "10px");
            }
        });
        
        filters.find('.toggleFilterSize').on('click',function(e){
            var currentTarget = jQuery(e.currentTarget);
            currentTarget.closest('.list-group').find('li.filterHidden').toggleClass('hide');
            if(currentTarget.closest('.list-group').find('li.filterHidden').hasClass('hide')) {
                currentTarget.html(currentTarget.data('moreText'));
            }else{
                currentTarget.html(currentTarget.data('lessText'));
            }
        })
        
        app.event.on('ListViewFilterLoaded', function(event, container, params) {
			// TODO - Update pagination...
		});
	},
    
    loadListView : function(viewId, params){
        this.getParentInstance().resetData();
        this.getParentInstance().loadFilter(viewId, params);
    },
    
    unMarkAllFilters : function() {
        jQuery('.listViewFilter').removeClass('active');
    },
    
    unMarkAllTags : function() {
        var container = jQuery('#listViewTagContainer');
        container.find('.tag').removeClass('active').find('i.activeToggleIcon').removeClass('fa-circle-o').addClass('fa-circle');
    },
    
    registerPopOverContent: function () {
        var element = jQuery(".list-group");
        var contentEle = jQuery('#filterActionPopoverHtml').clone();
        contentEle.find('.listmenu').removeClass('hide');
        var editEle = contentEle.find('.editFilter');
        var deleteEle = contentEle.find('.deleteFilter');
        var duplEle = contentEle.find('.duplicateFilter');
        var toggleEle = contentEle.find('.toggleDefault');

        jQuery.each(element.find('[rel="popover"]'), function (i, ele) {
            editEle.attr('data-url', jQuery(ele).data('editurl'));
            deleteEle.attr('data-url', jQuery(ele).data('deleteurl'));
            duplEle.attr('data-url', jQuery(ele).data('default'));
            toggleEle.attr('data-url', jQuery(ele).data('defaulttoggle'));
            toggleEle.attr('data-is-default', jQuery(ele).data('is-default'));
            toggleEle.attr('data-filter-id', jQuery(ele).data('filter-id'));
            contentEle.find('.toggleDefault i').attr('class', jQuery(ele).attr('toggleClass'));
             editEle.attr('data-id', jQuery(ele).data('id'));
            deleteEle.attr('data-id', jQuery(ele).data('id'));
            
            if(jQuery(ele).data('ismine') === false){
                contentEle.find('.editFilter').css("display", "none");
                contentEle.find('.deleteFilter').css("display","none");
            }
            if (!jQuery(ele).data('editable')) {
                contentEle.find('.editFilter').remove();
            } else {
                contentEle.find('.editFilter').removeClass('disabled');
            }
            if (!jQuery(ele).data('deletable')) {
                contentEle.find('.deleteFilter').remove();
            } else {
                contentEle.find('.deleteFilter').removeClass('disabled');
            } 
            var options = {
                html: true,
                placement: 'left',
                template: '<div class="popover" style="top: 0; position:absolute; z-index:0; margin-top:5px"><div class="popover-content"></div></div>',
                content: contentEle.html(),
                container: jQuery('#module-filters')
            };
            
            jQuery(ele).popover(options);
            
            jQuery('html').on('click', function (e) {
                var elements = jQuery('.activePopover');
                if(elements.length <= 0 ){
                    return;
                } else if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('[data-toggle="popover"]').length === 0
                        && $(e.target).parents('.popover.in').length === 0) {
                    elements.popover('hide').removeClass('rotate').removeClass("activePopover");
                }
            });
            
            jQuery('.js-popover-container').on('click', function(e){
                var currentElement = jQuery(e.currentTarget).find('[data-toggle]');
                if(jQuery('.popover').hasClass('in')) {
                    currentElement.addClass('rotate');
                    currentElement.addClass('activePopover');
                }else {
                    currentElement.removeClass('rotate');
                    currentElement.removeClass('activePopover');
                }
                if (jQuery('.popover', '#module-filters').length > 1) { 
                    var popoverId = jQuery('.popover', '#module-filters').attr('id');
                    var ele = jQuery('.list-group').find("[aria-describedby='" + popoverId + "']");
                    ele.removeClass('rotate');
                    jQuery('.popover', '#module-filters').first().popover('hide');
                }
            e.stopPropagation();
        });
        });
         
    },
    
    
    registerTagClick : function() {
        var self = this;
        var container = jQuery('#listViewTagContainer');
        container.on('click', '.tag', function(e) {
            var eventTriggerSourceElement = jQuery(e.target);
            //if edit icon is clicked then we dont have to load the tag
            if(eventTriggerSourceElement.is('.editTag')) {
                return;
            }
            var element = jQuery(e.currentTarget);
            var tagId = element.data('id');
            var viewId = container.data('viewId');
            
            self.unMarkAllFilters();
            self.unMarkAllTags();
            element.addClass('active');
            element.find('i.activeToggleIcon').removeClass('fa-circle').addClass('fa-circle-o');
            var listSearchParams = new Array();
            listSearchParams[0] = new Array();
            var tagSearchParams = new Array();
            tagSearchParams.push('tags');
            tagSearchParams.push('e');
            tagSearchParams.push(tagId);
            listSearchParams[0].push(tagSearchParams);
            var params = {};
            params.search_params = ''; 
            params.tag_params = JSON.stringify(listSearchParams);
            params.tag = tagId;
            params.page = '';
            self.loadListView(viewId, params);
        });
        
        container.on('click', '.moreTags', function(e){
            container.find('.moreListTags').removeClass('hide');
            jQuery(e.currentTarget).addClass('hide');
        });
    },
    registerEvents : function() {
        this.registerFilters();
        this.registerTagClick();
        this.registerPopOverContent();
//        var listInstance = new Vtiger_List_Js();
//        listInstance.registerDynamicDropdownPosition("lists-menu", "list-menu-content");

        app.event.on('Vtiger.Post.MenuToggle', function() {
            if(!jQuery('.sidebar-essentials').hasClass('hide')) {
                var filters = jQuery('.module-filters').not('.module-extensions');
                var scrollContainers = filters.find(".scrollContainer");
                jQuery.each(scrollContainers,function(key,scroll){
                    var scroll = jQuery(scroll);
                    var listcontentHeight = scroll.find(".list-menu-content").height();
                    scroll.css("height",listcontentHeight);
                    scroll.perfectScrollbar('update');
                });
            }
        });
    }
});
