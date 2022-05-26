/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger.Class('Vtiger_Index_Js', {
	files: [],
	hideNC: true,

	getInstance : function() {
		return new Vtiger_Index_Js();
	},

	/**
	 * Function to show the content of a file in an iframe
	 * @param {type} e
	 * @param {type} recordId
	 * @returns {undefined}
	 */
	previewFile: function (e, recordId, attachmentId) {
		e.stopPropagation();
		if (recordId) {
			var params = {
				module: 'ModComments',
				view: 'FilePreview',
				record: recordId,
				attachmentid: attachmentId
			};
			var modalParams = {
				cb: function (modalContainer) {
					modalContainer.find('.viewer').zoomer();
				},
				'ignoreScroll' : true
			};
			app.request.post({data: params}).then(function (err, res) {
				app.helper.showModal(res, modalParams);
			});
		}
	},

	/**
	 * Function to show email preview in popup
	 */
	showEmailPreview : function(recordId, parentId) {
		var popupInstance = Vtiger_Popup_Js.getInstance();
		var params = {};
		params['module'] = "Emails";
		params['view'] = "ComposeEmail";
		params['mode'] = "emailPreview";
		params['record'] = recordId;
		params['parentId'] = parentId;
		params['relatedLoad'] = true;

		var callback = function(data){
			emailPreviewClass = app.getModuleSpecificViewClass('EmailPreview','Vtiger');
			_controller = new window[emailPreviewClass]();
			_controller.registerEventsForActionButtons();
			var descriptionContent = data.find('#iframeDescription').val();
			var frameElement = jQuery("#emailPreviewIframe")[0].contentWindow.document;
			frameElement.open();
			frameElement.close();
			jQuery('#emailPreviewIframe').contents().find('html').html(descriptionContent);
			jQuery("#emailPreviewIframe").height(jQuery('#emailPreviewIframe').contents().find('html').height());
			jQuery('#emailPreviewIframe').contents().find('html').find('a').on('click', function(e) {
				e.preventDefault();
				var url = jQuery(e.currentTarget).attr('href');
				window.open(url, '_blank');
			});
		}
		popupInstance.showPopup(params,null,callback);
	},

	/**
	 * Function to show compose email popup based on number of
	 * email fields in given module,if email fields are more than
	 * one given option for user to select email for whom mail should
	 * be sent,or else straight away open compose email popup
	 * @params : accepts params object
	 *
	 * @cb: callback function to recieve the child window reference.
	 */

	showComposeEmailPopup : function(params, cb){
		var currentModule = "Emails";
		app.helper.showProgress();
		app.helper.checkServerConfig(currentModule).then(function(data){
			if(data == true){
				app.request.post({data:params}).then(function(err,data){
					if(err === null){
						data = jQuery(data);
						var form = data.find('#SendEmailFormStep1');
						var emailFields = form.find('.emailField');
						var length = emailFields.length;
						var emailEditInstance = new Emails_MassEdit_Js();

						var prefsNeedToUpdate = form.find('#prefsNeedToUpdate').val();
						if(prefsNeedToUpdate && length > 1) {
							app.helper.hideProgress();
							app.helper.showModal(data);
							emailEditInstance.registerEmailFieldSelectionEvent(cb);
							return true;
						}

						if(length > 1) {
							var saveRecipientPref = form.find('#saveRecipientPrefs').is(':checked');
							if(saveRecipientPref) {
								var params = form.serializeFormData();
								emailEditInstance.showComposeEmailForm(params).then(function(response) {
								jQuery(document).on('shown.bs.modal', function() {
									if (typeof cb === 'function') cb(response);
								});
							});
							}else {
								app.helper.hideProgress();
								app.helper.showModal(data);
								emailEditInstance.registerEmailFieldSelectionEvent(cb);
							}
						}else{
							emailFields.attr('checked','checked');
							var params = form.serialize();
							emailEditInstance.showComposeEmailForm(params).then(function(response) {
								jQuery(document).on('shown.bs.modal', function() {
									if (typeof cb === 'function') cb(response);
								});
							});
						}
					}
				});
			} else {
				app.helper.showAlertBox({'message':app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION')});
			}
		});
	},

	showRecipientPreferences: function (module) {
		var params = {
			module: module,
			view: "RecipientPreferences",
		};

		var callback = function (data) {
			var form = jQuery(data).find('#recipientsForm');
			if (form.find('#multiEmailContainer').height() > 300) {
				app.helper.showVerticalScroll(form.find('#multiEmailContainer'),{
					setHeight: '300px',
					autoHideScrollbar: false,
				});
			}

			form.on('submit', function (e) {
				e.preventDefault();
				form.find('.savePreference').attr('disabled', true);
				var params = form.serialize();
				app.helper.hideModal();
				app.helper.showProgress();
				app.request.post({"data":params}).then(function (err,data) {
					if (err == null) {
						app.helper.hideProgress();
						app.helper.showSuccessNotification({"message":''});
					} else {
						app.helper.showErrorNotification({"message":''});
					}
				});
			});
		}

		app.helper.showProgress();
		app.request.post({"data":params}).then(function (err,data) {
			if(err == null){
				app.helper.hideProgress();
				app.helper.showModal(data,{"cb":callback});
			}
		});
	},

	/**
	 * Function to show record address in Google Map
	 * @param {type} e
	 * @returns {undefined}
	 */
	showMap : function(e) {
		var currentElement = jQuery(e);
		var params1 = {
			'module' : 'Google',
			'action' : 'MapAjax',
			'mode' : 'getLocation',
			'recordid' : currentElement.data('record'),
			'source_module' : currentElement.data('module')
		};
		app.request.post({"data":params1}).then(function(error,response) {
			var result = JSON.parse(response);
			var address = result.address;
			var location = jQuery.trim((address).replace(/\,/g," "));
			if(location == '' || location == null) {
				app.helper.showAlertNotification({message:app.vtranslate('Please add address information to view on map')});
				return false;
			} else {
				var params = {
					'module' : 'Google',
					'view' : 'Map',
					'mode' : 'showMap',
					'viewtype' : 'detail',
					'record' : currentElement.data('record'),
					'source_module' : currentElement.data('module')
				};
				var popupInstance = Vtiger_Popup_Js.getInstance();
				popupInstance.showPopup(params, '', function(data) {
					var mapInstance = new Google_Map_Js();
					mapInstance.showMap(data);
				});
			}
		});
	},

	/**
	 * Function registers event for Calendar Reminder popups
	 */
	registerActivityReminder : function() {
		var activityReminderInterval = app.getActivityReminderInterval();
		if(activityReminderInterval != '') {
			var cacheActivityReminder = app.storage.get('activityReminder', 0);
			var currentTime = new Date().getTime()/1000;
			var nextActivityReminderCheck = app.storage.get('nextActivityReminderCheckTime', 0);
			//If activity Reminder Changed, nextActivityReminderCheck should reset
			if(activityReminderInterval != cacheActivityReminder) {
				nextActivityReminderCheck = 0;
			}
			if(currentTime >= nextActivityReminderCheck) {
				Vtiger_Index_Js.requestReminder();
			} else {
				var nextInterval = nextActivityReminderCheck - currentTime;
				setTimeout(function() {Vtiger_Index_Js.requestReminder()}, nextInterval*1000);
			}
		}
	},

	/**
	 * Function request for reminder popups
	 */
	requestReminder : function() {
		var activityReminder = app.getActivityReminderInterval();
		if(!activityReminder) {
			return;
		}
		var currentTime = new Date().getTime()/1000;
		//requestReminder function should call after activityreminder popup interval
		setTimeout(function() {Vtiger_Index_Js.requestReminder()}, activityReminder*1000);
		app.storage.set('activityReminder', activityReminder);
		//setting next activity reminder check time
		app.storage.set('nextActivityReminderCheckTime', currentTime + parseInt(activityReminder));

		app.request.post({
			'data' : {
				'module' : 'Calendar',
				'action' : 'ActivityReminder',
				'mode' : 'getReminders'
			}
		}).then(function(e, res) {
			if(!res.hasOwnProperty('result')) {
				for(i=0; i< res.length; i++) {
					var record = res[i];
					if(typeof record == 'object') {
						Vtiger_Index_Js.showReminderPopup(record);
					}
				}
			}
		});
	},

	/**
	 * Function display the Reminder popup
	 */
	showReminderPopup : function(record) {
		var notifyParams = {
			'title' : record.activitytype + ' - ' +
					'<a target="_blank" href="index.php?module=Calendar&view=Detail&record='+record.id+'">'+record.subject+'</a>&nbsp;&nbsp;'+
					'<i id="reminder-postpone-'+record.id+'" title="'+app.vtranslate('JS_POSTPONE')+'" class="cursorPointer fa fa-clock-o"></i>',
			'message' : '<div class="col-sm-12">'+
							'<div class="row">'+
								'<div class="col-sm-12 font13px">'+
									app.vtranslate('JS_START_DATE_TIME') + ' : ' + record.date_start+
								'</div>'+
								'<div class="col-sm-12 font13px">'+
									app.vtranslate('JS_END_DATE_TIME') + ' : ' + record.due_date+
								'</div>'+
							'</div>'+
						'</div>'
		};
		var settings = {
			'element' : 'body', 
			'type' : 'danger', 
			'delay' : 0
		};

		jQuery.notify(notifyParams, settings);
		jQuery('#reminder-postpone-'+record.id).on('click', function(e) {
			jQuery(e.currentTarget).closest('.notificationHeader').find('[data-notify="dismiss"]').trigger('click');
			app.request.post({
				'data' : {
					'module' : 'Calendar',
					'action' : 'ActivityReminder',
					'mode' : 'postpone',
					'record' : record.id
				}
			}).then(function(e,res) {});
		});
		jQuery('#reminder-postpone-'+record.id).closest('[data-notify="container"]').draggable({'containment' : 'body'});
	}

}, {
	 _SearchIntiatedEventName : 'VT_SEARCH_INTIATED',
	usernames : [],
	userList : {},
	autoFillElement : false,

	init : function() {
		this.addComponents();
	},

	addComponents : function() {
		this.addComponent('Vtiger_BasicSearch_Js');
	},

	registerListEssentialsToggleEvent : function() {
		jQuery('.main-container').on('click', '.essentials-toggle', function() {
			jQuery('.sidebar-essentials').toggleClass('hide');
			jQuery(".content-area").toggleClass("full-width");
			var params = {
				'module' : 'Users',
				'action' : 'IndexAjax',
				'mode' : 'toggleLeftPanel',
				'showPanel' : +jQuery('.sidebar-essentials').hasClass('hide')
			}
			app.request.post({data: params});
			if(jQuery('.sidebar-essentials').hasClass('hide')) {
				jQuery('.essentials-toggle-marker').removeClass('fa-chevron-left')
						.addClass('fa-chevron-right');
			} else {
				jQuery('.essentials-toggle-marker').removeClass('fa-chevron-right')
						.addClass('fa-chevron-left');
			}
			app.event.trigger("Vtiger.Post.MenuToggle");
		});
	},

	registerModuleQtips : function() {
		jQuery('.module-qtip').qtip({
			position: {
				my: 'left center',
				at: 'center right',
				adjust: {
					y: 1
				}
			},
			style: {
				classes: 'qtip-dark qtip-shadow module-name-tooltip'
			},
			show: {
				delay: 500
			}
		});
	},

	registerEvents: function() {
		this.registerMenuToggle();
		this.registerGlobalSearch();
		this.registerAppTriggerEvent();
		this.registerModuleQtips();
		this.registerListEssentialsToggleEvent();
		this.registerAdvanceSeachIntiator();
		this.registerQuickCreateEvent();
		this.registerQuickCreateSubMenus();
		this.registerPostQuickCreateEvent();
		this.registerEventForTaskManagement();
		this.registerFileChangeEvent();
		this.registerMultiUpload();
		this.registerHoverEventOnAttachment();
		//this.addBodyScroll();
		this.modulesMenuScrollbar();
		this.modulesMenuClearIconTitle();
		Vtiger_Index_Js.registerActivityReminder();
		//reference preview event registeration
		this.registerReferencePreviewEvent();
		this.registerEventForPostSaveFail();

		vtUtils.enableTooltips();
	},

	addBodyScroll: function () {
		app.helper.showVerticalScroll(
				$("body"),
				{
					setHeight: $(window).height() - 92,
					theme: "inset-dark",
					alwaysShowScrollbar: 2,
					autoExpandScrollbar: true,
					live: "on",
					setTop: 0,
					scrollInertia: 70,
					mouseWheel:{ preventDefault: true }

				}
		);
	},
	registerEventForTaskManagement : function(){
		var globalNav = jQuery('.global-nav');
		globalNav.on("click",".taskManagement",function(e){
			if(jQuery("#taskManagementContainer").length > 0){
				app.helper.hidePageOverlay();
				return false;
			}

			var params = {
				'module' : 'Calendar',
				'view' : 'TaskManagement',
				'mode' : 'showManagementView'
			}
			app.helper.showProgress();
			app.request.post({"data":params}).then(function(err,data){
				if(err === null){
					app.helper.loadPageOverlay(data,{'ignoreScroll' : true,'backdrop': 'static'}).then(function(){
						app.helper.hideProgress();
						$('#overlayPage').find('.data').css('height','100vh');

						var taskManagementPageOffset = jQuery('.taskManagement').offset();
						$('#overlayPage').find(".arrow").css("left",taskManagementPageOffset.left+13);
						$('#overlayPage').find(".arrow").addClass("show");

						vtUtils.showSelect2ElementView($('#overlayPage .data-header').find('select[name="assigned_user_id"]'),{placeholder:"User : All"});
						vtUtils.showSelect2ElementView($('#overlayPage .data-header').find('select[name="taskstatus"]'),{placeholder:"Status : All"});
						var js = new Vtiger_TaskManagement_Js();
						js.registerEvents();
					});
				}else{
					app.helper.showErrorNotification({"message":err});
				}
			});
		});
	},

	registerPostQuickCreateEvent : function(){
		var thisInstance = this;

		app.event.on("post.QuickCreateForm.show",function(event,form){
			form.find('#goToFullForm').on('click', function(e) {
				window.onbeforeunload = true;
				var form = jQuery(e.currentTarget).closest('form');
				var editViewUrl = jQuery(e.currentTarget).data('editViewUrl');
				if (typeof goToFullFormCallBack != "undefined") {
					goToFullFormCallBack(form);
				}
				thisInstance.quickCreateGoToFullForm(form, editViewUrl);
			});
		});
	},

	/**
	 * Function to navigate from quickcreate to editView Fullform
	 * @param accepts form element as parameter
	 */
	quickCreateGoToFullForm: function(form, editViewUrl) {
		var formData = form.serializeFormData();
		//As formData contains information about both view and action removed action and directed to view
		delete formData.module;
		delete formData.action;
		delete formData.picklistDependency;
		var formDataUrl = jQuery.param(formData);
		var completeUrl = editViewUrl + "&" + formDataUrl;
		window.location.href = completeUrl;
	},

	registerQuickCreateSubMenus : function() {
		jQuery("#quickCreateModules").on("click",".quickCreateModuleSubmenu",function(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			jQuery(e.currentTarget).closest('.dropdown').toggleClass('open');
		});
	},

	/**
	 * Function to register Quick Create Event
	 * @returns {undefined}
	 */
	registerQuickCreateEvent : function (){
		var thisInstance = this;
		jQuery("#quickCreateModules").on("click",".quickCreateModule",function(e,params){
			var quickCreateElem = jQuery(e.currentTarget);
			var quickCreateUrl = quickCreateElem.data('url');
			var quickCreateModuleName = quickCreateElem.data('name');
			if (typeof params === 'undefined') {
				params = {};
			}
			if (typeof params.callbackFunction === 'undefined') {
				params.callbackFunction = function(data, err) {
					//fix for Refresh list view after Quick create
					var parentModule=app.getModuleName();
					var viewname=app.view();
					if((quickCreateModuleName == parentModule) && (viewname=="List")){
							var listinstance = app.controller();
							listinstance.loadListViewRecords(); 
					}
				};
			}
			app.helper.showProgress();
			thisInstance.getQuickCreateForm(quickCreateUrl,quickCreateModuleName,params).then(function(data){
				app.helper.hideProgress();
				var callbackparams = {
					'cb' : function (container){
						thisInstance.registerPostReferenceEvent(container);
						app.event.trigger('post.QuickCreateForm.show',form);
						app.helper.registerLeavePageWithoutSubmit(form);
						app.helper.registerModalDismissWithoutSubmit(form);
					},
					backdrop : 'static',
					keyboard : false
					}

				app.helper.showModal(data, callbackparams);
				var form = jQuery('form[name="QuickCreate"]');
				var moduleName = form.find('[name="module"]').val();
				app.helper.showVerticalScroll(jQuery('form[name="QuickCreate"] .modal-body'), {'autoHideScrollbar': true});

				var targetInstance = thisInstance;
				var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
				if(typeof(moduleInstance.quickCreateSave) === 'function'){
					targetInstance = moduleInstance;
					targetInstance.registerBasicEvents(form);
				}

				vtUtils.applyFieldElementsView(form);
				targetInstance.quickCreateSave(form,params);
			});
		});
	},

	/**
	 * Function to register quick create tab events
	 */
	registerQuickcreateTabEvents : function(form) {
		var thisInstance = this;
		var tabElements = form.closest('.modal-content').find('.nav.nav-pills , .nav.nav-tabs').find('a');

		//This will remove the name attributes and assign it to data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnHide = function(tabElement) {
			var container = jQuery(tabElement.attr('data-target'));

			container.find('[name]').each(function(index, element) {
				element = jQuery(element);
				element.attr('data-element-name', element.attr('name')).removeAttr('name');
			});
		};

		//This will add the name attributes and get value from data-element-name . We are doing this to avoid
		//Multiple element to send as in calendar
		var quickCreateTabOnShow = function(tabElement) {
			var container = jQuery(tabElement.attr('data-target'));

			container.find('[data-element-name]').each(function(index, element) {
				element = jQuery(element);
				element.attr('name', element.attr('data-element-name')).removeAttr('data-element-name');
			});
		};

		tabElements.on('shown.bs.tab', function(e) {
			var previousTab = jQuery(e.relatedTarget);
			var currentTab = jQuery(e.currentTarget);

			quickCreateTabOnHide(previousTab);
			quickCreateTabOnShow(currentTab);

			if(form.find('[name="module"]').val()=== 'Calendar') {
				var sourceModule = currentTab.data('source-module');
				form.find('[name="calendarModule"]').val(sourceModule);
				var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName('Calendar');
				moduleInstance.registerEventForPicklistDependencySetup(form);
			}

			//while switching tabs we have to show scroll bar
			//thisInstance.showQuickCreateScrollBar(form);
			//while switching tabs we have to clear the invalid fields list
			//form.data('jqv').InvalidFields = [];
		});

		//remove name attributes for inactive tab elements
		quickCreateTabOnHide(tabElements.closest('li').filter(':not(.active)').find('a'));
	},

	/**
	 * Register Quick Create Save Event
	 * @param {type} form
	 * @returns {undefined}
	 */
	quickCreateSave : function(form,invokeParams){
		var params = {
			submitHandler: function(form) {
				// to Prevent submit if already submitted
				jQuery("button[name='saveButton']").attr("disabled","disabled");
				if(this.numberOfInvalids() > 0) {
					return false;
				}
				var formData = jQuery(form).serialize();
				app.request.post({data:formData}).then(function(err,data){
					app.helper.hideProgress();
					if(err === null) {
						jQuery('.vt-notification').remove();
						app.event.trigger("post.QuickCreateForm.save",data,jQuery(form).serializeFormData());
						app.helper.hideModal();
						var message = typeof formData.record !== 'undefined' ? app.vtranslate('JS_RECORD_UPDATED'):app.vtranslate('JS_RECORD_CREATED');
						app.helper.showSuccessNotification({"message":message},{delay:4000});
						invokeParams.callbackFunction(data, err);
						//To unregister onbefore unload event registered for quickcreate
						window.onbeforeunload = null;
					}else{
						app.event.trigger('post.save.failed', err);
						jQuery("button[name='saveButton']").removeAttr('disabled');
					}
				});
			},
			validationMeta: quickcreate_uimeta
		};
		form.vtValidate(params);
	},

	/**
	 * Function to get Quick Create Form
	 * @param {type} url
	 * @param {type} moduleName
	 * @returns {unresolved}
	 */
	getQuickCreateForm: function(url, moduleName, params) {
		var aDeferred = jQuery.Deferred();
		var requestParams = app.convertUrlToDataParams(url);
		jQuery.extend(requestParams, params.data);
		app.request.post({data:requestParams}).then(function(err,data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},

	registerMenuToggle : function(){
		jQuery("#menu-toggle").on('click', function(e) {
			e.preventDefault();
			$("#modnavigator").toggleClass('hide');
			$(".content-area").toggleClass("full-width");
			var params = {
				'module' : 'Users',
				'action' : 'IndexAjax',
				'mode' : 'toggleLeftPanel',
				'showPanel' : +jQuery("#modnavigator").hasClass('hide')
			}
			app.request.post({data: params});
			app.event.trigger("Vtiger.Post.MenuToggle");
		});
	},

	registerAppTriggerEvent : function() {
		jQuery('.app-menu').removeClass('hide');
		var toggleAppMenu = function(type) {
			var appMenu = jQuery('.app-menu');
			var appNav = jQuery('.app-nav');
			appMenu.appendTo('#page');
			appMenu.css({
				'top' : appNav.offset().top + appNav.height(),
				'left' : 0
			});
			if(typeof type === 'undefined') {
				type = appMenu.is(':hidden') ? 'show' : 'hide';
			}
			if(type == 'show') {
				appMenu.show(200, function() {});
			} else {
				appMenu.hide(200, function() {});
			}
		};

		jQuery('.app-trigger, .app-icon, .app-navigator').on('click',function(e){
			e.stopPropagation();
			toggleAppMenu();
		});

		jQuery('html').on('click', function() {
			toggleAppMenu('hide');
		});

		jQuery(document).keyup(function (e) {
			if (e.keyCode == 27) {
				if(!jQuery('.app-menu').is(':hidden')) {
					toggleAppMenu('hide');
				}
			}
		});

		jQuery('.app-modules-dropdown-container').hover(function(e) {
			var dropdownContainer = jQuery(e.currentTarget);
			jQuery('.dropdown').removeClass('open');
			if(dropdownContainer.length) {
				if(dropdownContainer.hasClass('dropdown-compact')) {
					dropdownContainer.find('.app-modules-dropdown').css('top', dropdownContainer.position().top - 8);
				} else {
					dropdownContainer.find('.app-modules-dropdown').css('top', '');
				}
				dropdownContainer.addClass('open').find('.app-item').addClass('active-app-item');
			}
		}, function(e) {
			var dropdownContainer = jQuery(e.currentTarget);
			dropdownContainer.find('.app-item').removeClass('active-app-item');
			setTimeout(function() {
				if(dropdownContainer.find('.app-modules-dropdown').length && !dropdownContainer.find('.app-modules-dropdown').is(':hover') && !dropdownContainer.is(':hover')) {
					dropdownContainer.removeClass('open');
				}
			}, 500);

		});

		jQuery('.app-item').on('click', function() {
			var url = jQuery(this).data('defaultUrl');
			if(url) {
				window.location.href = url;
			}
		});

		jQuery(window).resize(function() {
			jQuery(".app-modules-dropdown").mCustomScrollbar("destroy");
			app.helper.showVerticalScroll(jQuery(".app-modules-dropdown").not('.dropdown-modules-compact'), {
				setHeight: $(window).height(),
				autoExpandScrollbar: true
			});
			jQuery('.dropdown-modules-compact').each(function() {
				var element = jQuery(this);
				var heightPer = parseFloat(element.data('height'));
				app.helper.showVerticalScroll(element, {
					setHeight: $(window).height()*heightPer - 3,
					autoExpandScrollbar: true,
					scrollbarPosition: 'outside'
				});
			});
		});
		app.helper.showVerticalScroll(jQuery(".app-modules-dropdown").not('.dropdown-modules-compact'), {
			setHeight: $(window).height(),
			autoExpandScrollbar: true,
			scrollbarPosition: 'outside'
		});
		jQuery('.dropdown-modules-compact').each(function() {
			var element = jQuery(this);
			var heightPer = parseFloat(element.data('height'));
			app.helper.showVerticalScroll(element, {
				setHeight: $(window).height()*heightPer - 3,
				autoExpandScrollbar: true,
				scrollbarPosition: 'outside'
			});
		});
	},

	registerGlobalSearch : function() {
		var thisInstance = this;
		jQuery('.search-link .keyword-input').on('keypress',function(e){
			if(e.which == 13) {

				var element = jQuery(e.currentTarget);
				var searchValue = element.val();
				var data = {};
				data['searchValue'] = searchValue;
				element.trigger(thisInstance._SearchIntiatedEventName,data);
			}
		});
	},

	registerAdvanceSeachIntiator : function () {
		jQuery('#adv-search').on('click',function(e){
			var advanceSearchInstance = new Vtiger_AdvanceSearch_Js();
			advanceSearchInstance.advanceSearchTriggerIntiatorHandler();
//			advanceSearchInstance.initiateSearch().then(function() {
//				advanceSearchInstance.selectBasicSearchValue();
//			});
		});
	},

	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields : function(container) {
		var thisInstance = this;
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var searchValue = request.term;
				var params = thisInstance.getReferenceSearchParams(inputElement);
				params.module = app.getModuleName();
				if (jQuery('#QuickCreate').length > 0) {
					params.module = container.find('[name="module"]').val();
				}
				params.search_value = searchValue;
				if(params.search_module && params.search_module!= 'undefined') {
					thisInstance.searchModuleNames(params).then(function(data){
						var reponseDataList = new Array();
						var serverDataFormat = data;
						if(serverDataFormat.length <= 0) {
								jQuery(inputElement).val('');
								serverDataFormat = new Array({
										'label' : 'No Results Found',
										'type'	: 'no results'
								});
						}
						for(var id in serverDataFormat){
								var responseData = serverDataFormat[id];
								reponseDataList.push(responseData);
						}
						response(reponseDataList);
					});
				} else {
					jQuery(inputElement).val('');
					serverDataFormat = new Array({
						'label' : 'No Results Found',
						'type'	: 'no results'
					});
					response(serverDataFormat);
				}
			},
			'select' : function(event, ui ){
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if(typeof selectedItemData.type != 'undefined' && selectedItemData.type=="no results"){
						return false;
				}
				var element = jQuery(this);
				var parent = element.closest('td');
				if(parent.length == 0){
					parent = element.closest('.fieldValue');
				}
				var sourceField = parent.find('.sourceField');
				selectedItemData.record = selectedItemData.id;
				selectedItemData.source_module = parent.find('input[name="popupReferenceModule"]').val();
				selectedItemData.selectedName = selectedItemData.label;
				var fieldName = sourceField.attr("name");
				parent.find('input[name="'+fieldName+'"]').val(selectedItemData.id);
				element.attr("value",selectedItemData.id);
				element.data("value",selectedItemData.id);
				parent.find('.clearReferenceSelection').removeClass('hide');
				parent.find('.referencefield-wrapper').addClass('selected');
				element.attr("disabled","disabled");
				//trigger reference field selection event
				sourceField.trigger(Vtiger_Edit_Js.referenceSelectionEvent,selectedItemData);
				//trigger post reference selection
				sourceField.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent,{'data':selectedItemData});
			}
		});
	},

	/**
	 * Function to register clear reference selection event
	 * @param <jQUery> container
	 */
	registerClearReferenceSelectionEvent : function(container) {
		container.off('click', '.clearReferenceSelection');
		container.on('click', '.clearReferenceSelection',function(e){
			e.preventDefault();
			var element = jQuery(e.currentTarget);
			var parentTdElement = element.closest('td');
			if(parentTdElement.length == 0){
				parentTdElement = element.closest('.fieldValue');
			}
			var inputElement = parentTdElement.find('.inputElement');
			var fieldName = parentTdElement.find('.sourceField').attr("name");

			parentTdElement.find('.referencefield-wrapper').removeClass('selected');
			inputElement.removeAttr("disabled").removeAttr('readonly');
			inputElement.attr("value","");
			inputElement.data('value','');
			inputElement.val("");
			parentTdElement.find('input[name="'+fieldName+'"]').val("");
			element.addClass('hide');
			element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
		});
	},

	/**
	 * Function which will register event for create of reference record
	 * This will allow users to create reference record from edit view of other record
	 */
	registerReferenceCreate : function(container) {
		var thisInstance = this;
		container.on('click','.createReferenceRecord', function(e) {
			var element = jQuery(e.currentTarget);
			var controlElementTd = thisInstance.getParentElement(element);
			thisInstance.referenceCreateHandler(controlElementTd);
		});
	},

	/**
	 * Funtion to register popup search event for reference field
	 * @param <jQuery> container
	 */
	referenceModulePopupRegisterEvent : function(container) {
		var thisInstance = this;
		container.off('click', '.relatedPopup');
		container.on("click",'.relatedPopup',function(e) {
			thisInstance.openPopUp(e);
		});
		container.on('change','.referenceModulesList',function(e){
			var element = jQuery(e.currentTarget);
			var closestTD = thisInstance.getParentElement(element).next();
			var popupReferenceModule = element.val();
			var referenceModuleElement = jQuery('input[name="popupReferenceModule"]', closestTD).length ? 
			jQuery('input[name="popupReferenceModule"]', closestTD) : jQuery('input.popupReferenceModule', closestTD);
			var prevSelectedReferenceModule = referenceModuleElement.val();
			referenceModuleElement.val(popupReferenceModule);

			//If Reference module is changed then we should clear the previous value
			if(prevSelectedReferenceModule != popupReferenceModule) {
					closestTD.find('.clearReferenceSelection').trigger('click');
			}
		});
	},

	/**
	 * Function to open popup list modal
	 */
	openPopUp : function(e) {
		var thisInstance = this;
		var parentElem = thisInstance.getParentElement(jQuery(e.target));

		var params = this.getPopUpParams(parentElem);
		params.view = 'Popup';

		var isMultiple = false;
		if(params.multi_select) {
				isMultiple = true;
		}

		var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);

		var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
		sourceFieldElement.trigger(prePopupOpenEvent);

		if(prePopupOpenEvent.isDefaultPrevented()) {
				return ;
		}
		var popupInstance = Vtiger_Popup_Js.getInstance();

		app.event.off(Vtiger_Edit_Js.popupSelectionEvent);
		app.event.one(Vtiger_Edit_Js.popupSelectionEvent,function(e,data) {
			var responseData = JSON.parse(data);
			var dataList = new Array();
			jQuery.each(responseData, function(key, value){
				var counter = 0;
				for(var valuekey in value){
					if(valuekey == 'name') continue;
					if(typeof valuekey == 'object') continue;
//					var referenceModule = value[valuekey].module;
//					if(typeof referenceModule == "undefined") {
//						referenceModule = value.module;
//					}
//					if(parentElem.find('[name="popupReferenceModule"]').val() != referenceModule) continue;
//					
					var data = {
						'name' : value.name,
						'id' : key
					}
					if(valuekey == 'info') {
						data['name'] = value.name;
					}
					dataList.push(data);
					if(!isMultiple && counter === 0) {
						counter++;
						thisInstance.setReferenceFieldValue(parentElem, data);
					}
				}
			});

			if(isMultiple) {
				sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
			}
			sourceFieldElement.trigger(Vtiger_Edit_Js.postReferenceSelectionEvent,{'data':responseData});
		});
		popupInstance.showPopup(params,Vtiger_Edit_Js.popupSelectionEvent,function() {});
	},

	/**
	 * Functions changes the value of max upload size variable
	 * @param {type} container
	 * @returns {unresolved}
	 */
	getMaxiumFileUploadingSize: function (container) {
		//TODO : get it from the server
		return container.find('.maxUploadSize').data('value');
	},

	/**
	 * Function display file size in kb,mb,gb etc
	 */
	convertFileSizeInToDisplayFormat: function (fileSizeInBytes) {
		var i = -1;
		var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
		do {
			fileSizeInBytes = fileSizeInBytes / 1024;
			i++;
		} while (fileSizeInBytes > 1024);

		return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];

	},

	/**
	 * Function will trigger whenever customer filename got added or changed
	 * @returns {undefined}
	 */
	registerFileChangeEvent: function () {
		 var thisInstance = this;
					var container = jQuery('body');
			Vtiger_Index_Js.files = '';
			container.on('change', 'input[name="filename[]"],input[name="imagename[]"]', function(e){
				if(e.target.type == "text") return false;

				var files_uploaded=[];
				var fileSize = 0;
				jQuery.each(e.target.files,function(key,element){
					files_uploaded[key] = element;
					fileSize += Number(element['size']);
				});


				Vtiger_Index_Js.files = files_uploaded;
				var element = container.find('input[name="filename[]"],input[name="imagename[]"]');
				//ignore all other types than file 
				if(element.attr('type') != 'file'){
						return ;
				}
				var uploadFileSizeHolder = element.closest('.fileUploadContainer').find('.uploadedFileSize');
				var maxFileSize = thisInstance.getMaxiumFileUploadingSize(container);
				if(fileSize > maxFileSize) {
					alert(app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE'));
					var removeFileLinks = jQuery('.MultiFile-remove');
					jQuery(removeFileLinks[removeFileLinks.length - 1]).click();
				} else {
					if(container.length > 1){
						jQuery('div.fieldsContainer').find('form#I_form').find('input[name="filename"]').css('width','80px');
						jQuery('div.fieldsContainer').find('form#W_form').find('input[name="filename"]').css('width','80px');
					} else {
						container.find('input[name="filename[]"]').css('width','80px');
					}
				}
		});
	},


	/**
	 * Will register multiple file upload plugin
	 * @returns {undefined}
	 * Reference: http://www.fyneworks.com/jquery/multifile/
	 */
	registerMultiUpload: function () {
		var indexInstance = Vtiger_Index_Js.getInstance();
		if (jQuery('input[type="file"].multi').is(":visible")) { //if the container is visible on the page
			jQuery('input[type="file"]').MultiFile();
			indexInstance.registerHoverEventOnAttachment();
		} else {
			setTimeout(indexInstance.registerMultiUpload, 50); //wait 50 ms, then try again
		}
	},

	//removed toggle class for quickcreate

	/**
	 * Function register on mouseover and mouseout events
	 * @returns {undefined}
	 */
	registerHoverEventOnAttachment: function () {
		jQuery('body').on('mouseover', '.filePreview', function (e) {
			jQuery(e.currentTarget).closest('div').find('a[name="downloadfile"] i').removeClass('hide').css('display','block');
		}).on('mouseout', '.filePreview', function (e) {
			jQuery(e.currentTarget).closest('div').find('a[name="downloadfile"] i').addClass('hide');
		});
	},
	/*
	 * Function to get reference select popup parameters
	 */
	getPopUpParams : function(container) {
		var params = {};
		var sourceModule = app.getModuleName();
		var editTaskContainer = jQuery('[name="editTask"]');
		if(editTaskContainer.length > 0){
				sourceModule = editTaskContainer.find('#sourceModule').val();
		}
		var quickCreateConatiner = jQuery('[name="QuickCreate"]');
		if(quickCreateConatiner.length!=0){
				sourceModule = quickCreateConatiner.find('input[name="module"]').val();
		}
		var searchResultContainer = jQuery('#searchResults-container');
		if(searchResultContainer.length) {
			sourceModule = jQuery('select#searchModuleList').val();
		}
		var popupReferenceModuleElement = jQuery('input[name="popupReferenceModule"]',container).length ? 
		jQuery('input[name="popupReferenceModule"]',container) : jQuery('input.popupReferenceModule',container);
		var popupReferenceModule = popupReferenceModuleElement.val();
		var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField',container);
		}
		var sourceField = sourceFieldElement.attr('name');
		var sourceRecordElement = jQuery('input[name="record"]');
		var sourceRecordId = '';
		var recordId = app.getRecordId();
		if(sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
		} else if(recordId) {
			sourceRecordId = recordId;
		} else if(app.view() == 'List') {
			var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
			if(editRecordId) {
				sourceRecordId = editRecordId;
			}
		}

		if(searchResultContainer.length) {
			sourceRecordId = searchResultContainer.find('tr.listViewEntries.edited').data('id')
		}

		var isMultiple = false;
		if(sourceFieldElement.data('multiple') == true) {
			isMultiple = true;
		}

		// TODO : Need to recheck. We don't have reference field module name if that module is disabled
		if(typeof popupReferenceModule == "undefined"){
			popupReferenceModule = "undefined";
		}

		var params = {
			'module' : popupReferenceModule,
			'src_module' : sourceModule,
			'src_field' : sourceField,
			'src_record' : sourceRecordId
		}

		if(isMultiple) {
			params.multi_select = true ;
		}
		return params;
	},

	/*
	 * Function to set reference field value
	 */
	setReferenceFieldValue : function(container, params) {
		var sourceField = container.find('input.sourceField').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var sourceFieldDisplay = sourceField+"_display";
		var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
		var popupReferenceModuleElement = container.find('input[name="popupReferenceModule"]').length ? container.find('input[name="popupReferenceModule"]') : container.find('input.popupReferenceModule');
		var popupReferenceModule = popupReferenceModuleElement.val();
		var selectedName = params.name;
		var id = params.id;

		if (id && selectedName) {
			if(!fieldDisplayElement.length) {
				fieldElement.attr('value',id);
				fieldElement.data('value', id);
				fieldElement.val(selectedName);
			} else {
				fieldElement.val(id);
				fieldElement.data('value', id);
				fieldDisplayElement.val(selectedName);
				if(selectedName) {
					fieldDisplayElement.attr('readonly', 'readonly');
				} else {
					fieldDisplayElement.removeAttr("readonly");
				}
			}

			if(selectedName) {
				fieldElement.parent().find('.clearReferenceSelection').removeClass('hide');
				fieldElement.parent().find('.referencefield-wrapper').addClass('selected');
			}else {
				fieldElement.parent().find('.clearReferenceSelection').addClass('hide');
				fieldElement.parent().find('.referencefield-wrapper').removeClass('selected');
			}
			fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});
		}
	},

	/*
	 * Function to get referenced module name
	 */
	getReferencedModuleName : function(parentElement) {
		var referenceModuleElement = jQuery('input[name="popupReferenceModule"]',parentElement).length ? 
		jQuery('input[name="popupReferenceModule"]',parentElement) : jQuery('input.popupReferenceModule',parentElement);
		return referenceModuleElement.val();
	},

	/*
	 * Function to show quick create modal while creating from reference field
	 */
	referenceCreateHandler : function(container) {
		var thisInstance = this;
		var postQuickCreateSave = function(data) {
			var module = thisInstance.getReferencedModuleName(container);
			var params = {};
			params.name = data._recordLabel;
			params.id = data._recordId;
			params.module = module;
			thisInstance.setReferenceFieldValue(container, params);

			var tdElement = thisInstance.getParentElement(container.find('[value="'+ module +'"]'));
			var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
			var fieldElement = tdElement.find('input[name="'+sourceField+'"]');
			thisInstance.autoFillElement = fieldElement;
			thisInstance.postRefrenceSearch(params, container);

			tdElement.find('input[class="sourceField"]').trigger(Vtiger_Edit_Js.postReferenceQuickCreateSave, {'data' : data});
		}

		var referenceModuleName = this.getReferencedModuleName(container);
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
		if(quickCreateNode.length <= 0) {
			var notificationOptions = {
				'title' : app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED')
			}
			app.helper.showAlertNotification(notificationOptions);
		}
		quickCreateNode.trigger('click',[{'callbackFunction':postQuickCreateSave}]);
	},

	/**
	* Function to get reference search params
	*/
	getReferenceSearchParams : function(element){
		var tdElement = this.getParentElement(element);
		var params = {};
		var referenceModuleElement = jQuery('input[name="popupReferenceModule"]',tdElement).length ? 
		jQuery('input[name="popupReferenceModule"]',tdElement) : jQuery('input.popupReferenceModule',tdElement);
		var searchModule = referenceModuleElement.val();
		params.search_module = searchModule;
		return params;
	},

	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if(typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if(record.length) {
				params.base_record = record.val();
			} else if(recordId) {
				params.base_record = recordId;
			} else if(app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if(editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}
		app.request.get({data:params}).then(
			function(err, res){
				aDeferred.resolve(res);
			},
			function(error){
				//TODO : Handle error
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	/*
	 * Function to get Field parent element
	 */
	getParentElement : function(element) {
		var parent = element.closest('td');
		// added to support from all views which may not be table format
		if(parent.length === 0) {
			parent = element.closest('.td').length ? 
			element.closest('.td') : element.closest('.fieldValue');
		}
		return parent;
	},

	getUserNameForId : function(id) {
		for(var key in userList) {
			if(userList[key] === id) {
				return key;
			}
		}
		return null;
	},

	modulesMenuScrollbar : function(){
		app.helper.showVerticalScroll(jQuery("#modnavigator #modules-menu"),{autoHideScrollbar:true});
	},

	modulesMenuClearIconTitle: function() {
		jQuery('#modules-menu i').removeAttr('title');
		jQuery('#modules-menu .custom-module').removeAttr('title');
	},

	registerChangeTemplateEvent: function (container, recordId) {
		var sourceModule = container.find('#sourceModuleName').val();
		var thisInstance = this;
		var select = container.find('#fieldList');
		select.on("change", function () {
			var templateId = select.val();
			thisInstance.showQuickPreviewForId(recordId, sourceModule, app.getAppName(), templateId);
		});
	},

	registerMoreRecentUpdatesClickEvent: function (container, recordId) {
		var moduleName = container.find('#sourceModuleName').val();
		container.find('.moreRecentUpdates').on('click', function () {
			var recentUpdateURL = "index.php?view=Detail&mode=showRecentActivities&page=1&module=" + moduleName + "&record=" + recordId + "&tab_label=LBL_UPDATES";
			window.location.href = recentUpdateURL;
		});
	},

	registerNavigationEvents: function (container) {
		this.registerNextRecordClickEvent(container);
		this.registerPreviousRecordClickEvent(container);
	},

	registerNextRecordClickEvent: function(container){
		var self = this;
		container.find('#quickPreviewNextRecordButton').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var nextRecordId = element.data('record') || element.data('id');
			var moduleName = container.find('#sourceModuleName').val();
			var appName = element.data('app');
			var templateId, fieldList = container.find('#fieldList');
			if(fieldList.length) {
				templateId = fieldList.val();
			}
			self.showQuickPreviewForId(nextRecordId, moduleName, appName, templateId, false, 'navigation');
		});
	},

	registerPreviousRecordClickEvent: function(container){
		var self = this;
		container.find('#quickPreviewPreviousRecordButton').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var prevRecordId = element.data('record') || element.data('id');
			var moduleName = container.find('#sourceModuleName').val();
			var appName = element.data('app');
			var templateId, fieldList = container.find('#fieldList');
			if(fieldList.length) {
				templateId = fieldList.val();
			}
			self.showQuickPreviewForId(prevRecordId, moduleName, appName, templateId, false, 'navigation');
		});
	},

	_showInventoryQuickPreviewForId: function (recordId, moduleName, templateId, isReference, mode) {
		var thisInstance = this;
		var params = {};
		if(typeof moduleName === 'undefined') {
			moduleName = app.module();
		}
		params['module'] = moduleName;
		params['record'] = recordId;
		params['view'] = 'RecordQuickPreview';
		if(isReference == true){
			params['navigation'] = 'false';
		}
		else{
		params['navigation'] = 'true';
		}

		if (templateId) {
			params['templateid'] = templateId;
		}

		if(mode) {
			params['preview_mode'] = mode;
		}

		app.helper.showProgress();
		app.request.get({data: params}).then(function (err, response) {
			app.helper.hideProgress();
			if (templateId && mode != 'navigation') {
				jQuery('#pdfViewer').html(response);
				return;
			}
			var params = {
				cb: function () {
					thisInstance.registerChangeTemplateEvent(jQuery('#helpPageOverlay'), recordId);
					thisInstance.registerNavigationEvents(jQuery('#helpPageOverlay'));
				}
			};
			jQuery('#helpPageOverlay').css({"width": "870px", "box-shadow": "-8px 0 5px -5px lightgrey", 'height': '100vh', 'background': 'white'});
			app.helper.loadHelpPageOverlay(response, params);
			var params = {
				setHeight: "100%",
				alwaysShowScrollbar: 2,
				autoExpandScrollbar: true,
				setTop: 0,
				scrollInertia: 70,
				mouseWheel: {preventDefault: true}
			};
			app.helper.showVerticalScroll(jQuery('.quickPreview .modal-body'), params);
		});
	},

	_showQuickPreviewForId: function (recordId, moduleName, appName, isReference) {
		var self = this;
		var params = {};
		if (typeof moduleName === 'undefined') {
			moduleName = app.module();
		}
		params['module'] = moduleName;
		params['record'] = recordId;
		params['view'] = 'RecordQuickPreview';
		if(isReference === true){
			params['navigation'] = 'false';
		}
		else{
			params['navigation'] = 'true';
		}
		params['app'] = appName;

		app.helper.showProgress();
		app.request.get({data: params}).then(function (err, response) {
			app.helper.hideProgress();
			jQuery('#helpPageOverlay').css({"width": "550px", "box-shadow": "-8px 0 5px -5px lightgrey", 'height': '100vh', 'background': 'white'});
			var callBack = function(container){
				self.registerMoreRecentUpdatesClickEvent(container,recordId);
				//Register Navigation Events
				self.registerNavigationEvents(container);
			};
			app.helper.loadHelpPageOverlay(response, {
				'cb' : callBack
			});
			var params = {
				setHeight: "100%",
				alwaysShowScrollbar: 2,
				autoExpandScrollbar: true,
				setTop: 0,
				scrollInertia: 70,
				mouseWheel: {preventDefault: true}
			};
			app.helper.showVerticalScroll(jQuery('.quickPreview .modal-body'), params);
		});
	},

	isInventoryModule : function(moduleName) {
		var inventoryModules = jQuery('#inventoryModules').val();
		return inventoryModules.indexOf(moduleName) !== -1;
	},

	showQuickPreviewForId: function(recordId, moduleName, appName, templateId, isReference, mode) {
		var self = this;
		if(self.isInventoryModule(moduleName)) {
			self._showInventoryQuickPreviewForId(recordId, moduleName, templateId, isReference, mode);
		} else {
			self._showQuickPreviewForId(recordId, moduleName, appName, isReference);
		}
	},

	registerReferencePreviewEvent : function() {
		var self = this;
		var view = app.view();
		jQuery('body').on('click', '.js-reference-display-value', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var currentTarget = jQuery(this);
			if(currentTarget.closest('#popupPageContainer').length) {
				return; //no action in reference selection popup
			}
			var href = currentTarget.attr('href');
			if(view === 'List') {
				if(currentTarget.data('timer')) {
					//if list view single click has set a time, clear it
					clearTimeout(currentTarget.data('timer'));
					currentTarget.data('timer', null);
				}
				//perform show preview only after 500ms in list view to support double click edit action
				if (!currentTarget.data('preview-timer') && typeof href != 'undefined') {
					currentTarget.data('preview-timer', setTimeout(function () {
						 var data = app.convertUrlToDataParams(href);
						 self.showQuickPreviewForId(data.record, data.module, app.getAppName(),'',true);
						 currentTarget.data('preview-timer',null);
					}, 500));
				}
			} else {
				var data = app.convertUrlToDataParams(href);
				self.showQuickPreviewForId(data.record, data.module, app.getAppName(),'',true);
			}
		});

		if(view === 'List') {
			/*
			* when reference display value is double clicked in list view, 
			* should initiate inline edit instead of showing preview
			*/
			jQuery('body').on('dblclick', '.js-reference-display-value', function(e) {
				e.preventDefault();
				var currentTarget = jQuery(this);
				if (currentTarget.data('preview-timer')) {
					clearTimeout(currentTarget.data('preview-timer'));
					currentTarget.data('preview-timer', null);
				};
			});
		}
	},

	registerPostReferenceEvent : function(container) {
		var thisInstance = this;

		container.find('.sourceField').on(Vtiger_Edit_Js.postReferenceSelectionEvent,function(e,result){
			var dataList = result.data;
			var element = jQuery(e.currentTarget);

			if(typeof element.data('autofill') != 'undefined') {
				thisInstance.autoFillElement = element;
				if(typeof(dataList.id) == 'undefined'){
					thisInstance.postRefrenceComplete(dataList, container);
				}else {
					thisInstance.postRefrenceSearch(dataList, container);
				}
			}
		});
	},

	postRefrenceComplete : function(data, container){
		var thisInstance = this;
		if(!data)
			return ;

		jQuery.each(data, function(id, value){
			thisInstance.fillReferenceFieldValue(value, container);
		});
	},

	getRelatedFieldElements: function(container, autoFillData){
		var parentElems = {};
		if (autoFillData) {
			var field = container.find('#'+autoFillData.fieldname+'_display').closest('td');
			parentElems['parent_id'] = field;
		}
		return parentElems;
	},

	fillReferenceFieldValue : function(data, container){
		var thisInstance = this;
		var autoFillElement = this.autoFillElement;
		var autoFillData = autoFillElement.data('autofill');
		var completedValues = [];
		for(var index in autoFillData){
			var value = autoFillData[index];
			var referenceContainer = thisInstance.getRelatedFieldElements(container, value); 
			jQuery.each(data, function(datakey, datavalue){
				for(var name in datavalue){
					if(typeof datavalue[name] == 'object'){
						var key = name;
						var dataList = {
								'name': datavalue[key].name,
								'id' : datavalue[key].id
						}

						if(value.module == datavalue[key].module){
								var autoFillElement = thisInstance.autoFillElement;
								var autoFillData = value;
								var popupReferenceModuleElement = autoFillElement.parent().parent().find('[name=popupReferenceModule]').length ? 
										autoFillElement.parent().parent().find('[name=popupReferenceModule]') : autoFillElement.parent().parent().find('.popupReferenceModule');
								var module = popupReferenceModuleElement.val();
								var elementName = autoFillElement.attr('name');
								var selectedName = container.find('#'+elementName+'_display').val();
								var message = app.vtranslate('JS_OVERWRITE_AUTOFILL_MSG1')+' '+app.vtranslate('SINGLE_'+autoFillData.module)+" "+app.vtranslate('JS_OVERWRITE_AUTOFILL_MSG2')+" "+app.vtranslate('SINGLE_'+module)+' ('+selectedName+') '+app.vtranslate('SINGLE_'+autoFillData.module)+" ?";
								var parentId = container.find('[name='+autoFillData.fieldname+']').val();

								if(parentId != dataList.id && parentId) {
										if(jQuery.inArray(datavalue[key].module, completedValues) === -1) {
											completedValues.push(datavalue[key].module);
											thisInstance.confirmAndFillDetails(referenceContainer[key], dataList, message);
										}		 
								} else {
										thisInstance.setReferenceFieldValue(referenceContainer[key], dataList);
								}
						}
					}
				}
			});
		}
	},

	confirmAndFillDetails : function(container, data, message) {
		var thisInstance = this;
		app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
						thisInstance.setReferenceFieldValue(container, data);
				},
				function(error, err){
				}
		);
	},

	/**
	 * Function to show duplication notification
	 */
	registerEventForPostSaveFail : function() {
		app.event.on('post.save.failed', function (e, err) {
			jQuery('.vt-notification').remove();
			var options = {
				message: err.message
			};
			if (err.title) {
				options['title'] = err.title;
			}
			var settings = {
				'delay': 0
			};
			app.helper.showErrorNotification(options, settings);
		});
	},

	postRefrenceSearch: function(resultData, container){
		var thisInstance = this;
		var module;
		if(!resultData.module) {
			var autoFillElement = this.autoFillElement;
			var popupReferenceModuleElement = autoFillElement.parent().parent().find('[name=popupReferenceModule]').length ? 
				autoFillElement.parent().parent().find('[name=popupReferenceModule]') : autoFillElement.parent().parent().find('.popupReferenceModule');
			module = popupReferenceModuleElement.val();
		}else {
			module = resultData.module;
		}
		if(!resultData.id)
			return;

		var params = {
				module: module,
				action: 'RelationAjax',
				mode: 'getRelatedRecordInfo',
				id: resultData.id
			};

		app.request.post({'data' : params}).then(function(err, data){
			if(err == null){
				thisInstance.postRefrenceComplete(data, container);
			}
		});
	}
});
