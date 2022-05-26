/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Index_Js("Settings_Vtiger_Index_Js",{

	showMessage : function(customParams){
		var params = {};
		params.animation = "show";
		params.type = 'info';
		params.title = app.vtranslate('JS_MESSAGE');

		if(typeof customParams != 'undefined') {
			var params = jQuery.extend(params,customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	}


},{
	registerDeleteShortCutEvent : function(shortCutBlock) {
		var thisInstance = this;
		if(typeof shortCutBlock == 'undefined') {
			var shortCutBlock = jQuery('.moduleBlock');
		};
		shortCutBlock.find('.unpin').on('click',function(e) {
			var actionEle = jQuery(e.currentTarget);
			var closestBlock = actionEle.closest('.moduleBlock');
			var fieldId = actionEle.data('id');
			var shortcutBlockActionUrl = closestBlock.data('actionurl');
			var actionUrl = shortcutBlockActionUrl+'&pin=false';
			app.request.post({'url':actionUrl}).then(function(err, data) {
				if(err === null) {
					closestBlock.remove();
					thisInstance.registerSettingShortCutAlignmentEvent();
					var menuItemId = '#'+fieldId+'_menuItem';
					var shortCutActionEle = jQuery(menuItemId);
					var imagePath = shortCutActionEle.data('pinimageurl');
					shortCutActionEle.attr('src',imagePath).data('action','pin');
					app.helper.showSuccessNotification({'message':app.vtranslate('JS_SUCCESSFULLY_UNPINNED')});
				}
			});
			e.stopPropagation();
		});
	},

	registerPinUnpinShortCutEvent : function() {
		var thisInstance = this;
		var widget = jQuery('#accordion');
		widget.on('click','.pinUnpinShortCut',function(e){
			var shortCutActionEle = jQuery(e.currentTarget);
			var url = shortCutActionEle.data('actionurl');
			var shortCutElementActionStatus = shortCutActionEle.data('action');
			if(shortCutElementActionStatus == 'pin'){
				var actionUrl = url+'&pin=true';
			} else {
				actionUrl = url+'&pin=false';
			}
			var progressIndicatorElement = jQuery.progressIndicator({
				'blockInfo' : {
				'enabled' : true
				}
			});
			app.request.post({'url':actionUrl}).then(function(err, data) {
				if(data.SUCCESS == 'OK') {
					if (shortCutElementActionStatus == 'pin') {
						var imagePath = shortCutActionEle.data('unpinimageurl');
						var unpinTitle = shortCutActionEle.data('unpintitle');
						shortCutActionEle.attr('src',imagePath).data('action','unpin').attr('title',unpinTitle);
						var shortCutsMainContainer = jQuery('#settingsShortCutsContainer').find('.col-lg-12:last-child');
						if (shortCutsMainContainer.length > 0) {
							var url = 'module=Vtiger&parent=Settings&view=IndexAjax&mode=getSettingsShortCutBlock&fieldid='+shortCutActionEle.data('id');
							app.request.post({url:url}).then(function(err, data){
								var newBlock = jQuery(data).appendTo(shortCutsMainContainer);
								thisInstance.registerSettingShortCutAlignmentEvent();
								thisInstance.registerDeleteShortCutEvent(newBlock);
							});
						}
						progressIndicatorElement.progressIndicator({'mode' : 'hide'});
						app.helper.showSuccessNotification({'message':app.vtranslate('JS_SUCCESSFULLY_PINNED')});
					} else {
						var imagePath = shortCutActionEle.data('pinimageurl');
						var pinTitle = shortCutActionEle.data('pintitle');
						shortCutActionEle.attr('src',imagePath).data('action','pin').attr('title',pinTitle);
						jQuery('#shortcut_'+shortCutActionEle.data('id')).remove();
						thisInstance.registerSettingShortCutAlignmentEvent();
						progressIndicatorElement.progressIndicator({'mode' : 'hide'});
						app.helper.showSuccessNotification({'message':app.vtranslate('JS_SUCCESSFULLY_UNPINNED')});
					}
				}
			});
			e.preventDefault();
		});
	},

	registerSettingsShortcutClickEvent : function() {
		jQuery('#settingsShortCutsContainer').on('click','.moduleBlock',function(e){
			var url = jQuery(e.currentTarget).data('url');
			window.location.href = url;
		});
	},

	registerSettingShortCutAlignmentEvent : function() {
		jQuery('#settingsShortCutsContainer').find('.moduleBlock').removeClass('marginLeftZero');
		jQuery('#settingsShortCutsContainer').find('.moduleBlock:nth-child(4n+1)').addClass('marginLeftZero');
	},

	registerWidgetsEvents : function() {
		var widgets = jQuery('div.widgetContainer');
		widgets.on({
			shown: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement')
				var imagePath = imageEle.data('downimage');
				imageEle.attr('src',imagePath);
			},
			hidden: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('rightimage');
				imageEle.attr('src',imagePath);
			}
		});
	},

	registerAddShortcutDragDropEvent : function() {
		var thisInstance = this;

		jQuery( ".menuItemLabel" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		jQuery( "#settingsShortCutsContainer" ).droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".menuItemLabel",
			drop: function( event, ui ) {
				var actionElement = ui.draggable.find('.pinUnpinShortCut');
				var pinStatus = actionElement.data('action');
				if(pinStatus === 'unpin') {
					app.helper.showSuccessNotification({'message':app.vtranslate('JS_SHORTCUT_ALREADY_ADDED')});
				} else {
					actionElement.trigger('click');
				}
			}
		});
	},

	registerEventForShowOrHideSettingsLinks: function () {
		jQuery('.slidingDiv').hide();
		jQuery('.show_hide').click(function (e) {
			jQuery(this).next(".slidingDiv").slideToggle('fast');
		});
	},

	registerAccordionClickEvent : function() {
		function toggleChevron(e) {
			$(e.target)
				.prev('.app-nav')
				.find("i.indicator")
				.toggleClass('fa-chevron-down fa-chevron-right');
		}
		$('#accordion').on('hidden.bs.collapse', toggleChevron);
		$('#accordion').on('shown.bs.collapse', toggleChevron);
	},

	registerBasicSettingsEvents : function() {
			this.registerAccordionClickEvent();
			this.registerFilterSearch();
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
	},

	registerFilterSearch : function () {
		var settings = jQuery('.settingsgroup');
			jQuery('.search-list').instaFilta({
				targets: '.menuItemLabel',
				sections : '.instaSearch',
				markMatches: true,
				onFilterComplete: function(matchedItems) {
					if(jQuery('.search-list').val().length <= 0){
						jQuery('.instaSearch').find('.widgetContainer').closest('.panel-collapse').filter('.in').removeClass('in');
						jQuery('.instaSearch').find('.indicator').removeClass('fa-chevron-down').addClass('fa-chevron-right');
						return;
					}
					jQuery('.instaSearch').find('[data-instafilta-hide="false"]').closest('.panel-collapse').filter(':not(.in)').addClass('in').height('');
					jQuery('.instaSearch').filter(':visible').find('[data-instafilta-hide="false"]').parents('.instaSearch').find('.indicator').removeClass('fa-chevron-right').addClass('fa-chevron-down');
				}
			});
	},

	registerEvents: function() {
		this._super();
		this.registerSettingsShortcutClickEvent();
		this.registerDeleteShortCutEvent();
		this.registerWidgetsEvents();
		this.registerPinUnpinShortCutEvent();
		this.registerAddShortcutDragDropEvent();
		this.registerSettingShortCutAlignmentEvent();
		this.registerBasicSettingsEvents();
	}

});
