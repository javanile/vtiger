Calendar_Calendar_Js('Calendar_SharedCalendar_Js', {

	calendarViewContainer : false

}, {

	getCalendarViewContainer : function() {
		if(!Calendar_SharedCalendar_Js.calendarViewContainer.length) {
			Calendar_SharedCalendar_Js.calendarViewContainer = jQuery('#sharedcalendar');
		}
		return Calendar_SharedCalendar_Js.calendarViewContainer;
	},

	getFeedRequestParams : function(start,end,feedCheckbox) {
		var dateFormat = 'YYYY-MM-DD';
		var startDate = start.format(dateFormat);
		var endDate = end.format(dateFormat);
		return {
			'start' : startDate,
			'end' : endDate,
			'type' : feedCheckbox.data('calendarFeed'),
			'userid' : feedCheckbox.data('calendarUserid'),
			'group' : feedCheckbox.data('calendarGroup'),
			'color' : feedCheckbox.data('calendarFeedColor'),
			'textColor' : feedCheckbox.data('calendarFeedTextcolor')
		};
	},

	removeEvents : function(feedCheckbox) {
		var userId = feedCheckbox.data('calendarUserid');
		this.getCalendarViewContainer().fullCalendar('removeEvents', 
		function(eventObj) {
			return parseInt(userId) === parseInt(eventObj.userid);
		});
	},

	_colorize : function(feedCheckbox) {
		var thisInstance = this;
		var sourcekey = feedCheckbox.data('calendarSourcekey');
		var color = feedCheckbox.data('calendarFeedColor');
		if(color === '' || typeof color === 'undefined') {
			color = app.storage.get(sourcekey);
			if(!color) {
				color = thisInstance.getRandomColor();
				app.storage.set(sourcekey, color);
			}
			feedCheckbox.data('calendarFeedColor',color);
			feedCheckbox.closest('.calendar-feed-indicator').css({'background-color':color});
		}
	},

	colorizeFeed : function(feedCheckbox) {
		this._colorize(feedCheckbox);
		this.assignFeedTextColor(feedCheckbox);
	},

	registerAddUserCalendarViewActions : function(modalContainer) {
		this.registerColorEditorEvents(modalContainer);
	},

	showAddUserCalendarView : function() {
		var thisInstance = this;
		var params = {
			module : app.getModuleName(),
			view : 'UserCalendarViews',
			mode : 'addUserCalendar'
		};
		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,data) {
			app.helper.hideProgress();
			if(!e) {
				if(jQuery(data).find('select[name="usersList"] > option').length) {
					app.helper.showModal(data,{
						'cb' : function(modalContainer) {
							thisInstance.registerAddUserCalendarViewActions(modalContainer);
						}
					});
				} else {
					app.helper.showErrorNotification({
						'message' : app.vtranslate('JS_NO_CALENDAR_VIEWS_TO_ADD')
					});
				}
			} else {
				console.log("network error : ",e);
			}
		});
	},

	showAddCalendarFeedEditor : function() {
		this.showAddUserCalendarView();
	},

	registerUserChangeEvent : function(modalContainer) {
		var thisInstance = this;
		var calendarFeedList = jQuery('#calendarview-feeds > ul.feedslist');
		modalContainer.find('select[name="usersList"]').on('change', 
		function() {
			var currentUserId = jQuery(this).val();
			var currentColor = thisInstance.getRandomColor();
			var feedCheckbox = calendarFeedList.find('input[data-calendar-userid="'+currentUserId+'"]');
			if(feedCheckbox.length) {
				currentColor = feedCheckbox.data('calendarFeedColor');
			}
			modalContainer.find('.selectedColor').val(currentColor);
			modalContainer.find('.calendarColorPicker').ColorPickerSetColor(currentColor);
		});
	},

	saveFeedSettings : function(modalContainer) {
		var thisInstance = this;
		var selectedType = modalContainer.find('.selectedType');
		var selectedUserId = selectedType.val();
		var selectedUserName = selectedType.data('typename');
		var calendarGroup = selectedType.data('calendarGroup');
		var selectedColor = modalContainer.find('.selectedColor').val();
		var editorMode = modalContainer.find('.editorMode').val();

		var params = {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'addUserCalendar',
			selectedUser : selectedUserId,
			selectedColor : selectedColor
		};

		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e) {
			if(!e) {
				var calendarFeedList = jQuery('#calendarview-feeds > ul.feedslist');
				var message = app.vtranslate('JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY');
				if(editorMode === 'create') {
					var feedIndicatorTemplate = jQuery('#calendarview-feeds').find('ul.dummy > li.feed-indicator-template');
					feedIndicatorTemplate.removeClass('.feed-indicator-template');
					var newFeedIndicator = feedIndicatorTemplate.clone(true,true);
					newFeedIndicator.find('span:first').text(selectedUserName);
					var newFeedCheckbox = newFeedIndicator.find('.toggleCalendarFeed');
					newFeedCheckbox.attr('data-calendar-sourcekey','Events_'+selectedUserId).
					attr('data-calendar-feed','Events').
					attr('data-calendar-fieldlabel',selectedUserName).
					attr('data-calendar-userid',selectedUserId).
					attr('data-calendar-group',calendarGroup).
					attr('checked','checked');
					calendarFeedList.append(newFeedIndicator);
					message = app.vtranslate('JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY');
				}

				var contrast = app.helper.getColorContrast(selectedColor);
				var textColor = (contrast === 'dark') ? 'white' : 'black';
				var feedCheckbox = calendarFeedList.find('input[data-calendar-userid="'+selectedUserId+'"]');
				feedCheckbox.data('calendarFeedColor',selectedColor).
				data('calendarFeedTextcolor',textColor);
				var feedIndicator = feedCheckbox.closest('.calendar-feed-indicator');
				feedIndicator.css({'background-color':selectedColor,'color':textColor});
				thisInstance.refreshFeed(feedCheckbox);

				app.helper.hideProgress();
				app.helper.hideModal();
				app.helper.showSuccessNotification({'message':message});
			} else {
				console.log("error : ",e);
			}
		});

	},

	registerColorEditorSaveEvent : function(modalContainer) {
		var thisInstance = this;
		modalContainer.find('[name="saveButton"]').on('click', function() {
			jQuery(this).attr('disabled','disabled');
			var usersList = modalContainer.find('select[name="usersList"]');
			var selectedUser = usersList.find('option:selected');
			var selectedType = modalContainer.find('.selectedType');
			selectedType.val(usersList.val()).data(
				'typename',
				selectedUser.text()
			).data(
				'calendarGroup',
				selectedUser.data('calendarGroup')
			);
			thisInstance.saveFeedSettings(modalContainer);
		});        
	},

	registerColorEditorEvents : function(modalContainer,feedIndicator) {
		var thisInstance = this;
		var editorMode = modalContainer.find('.editorMode').val();

		var colorPickerHost = modalContainer.find('.calendarColorPicker');
		var selectedColor = modalContainer.find('.selectedColor');
		thisInstance.initializeColorPicker(colorPickerHost, {}, function(hsb, hex, rgb) {
			var selectedColorCode = '#'+hex;
			selectedColor.val(selectedColorCode);
		});

		thisInstance.registerUserChangeEvent(modalContainer);

		var usersList = modalContainer.find('select[name="usersList"]');
		if(editorMode === 'edit') {
			var feedCheckbox = feedIndicator.find('input[type="checkbox"].toggleCalendarFeed');
			usersList.select2('val',feedCheckbox.data('calendarUserid'));
		}
		usersList.trigger('change');

		thisInstance.registerColorEditorSaveEvent(modalContainer);
	},

	showColorEditor : function(feedIndicator) {
		var thisInstance = this;
		var params = {
			module : app.getModuleName(),
			view : 'UserCalendarViews',
			mode : 'editUserCalendar'
		};
		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,data) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(data,{
					'cb' : function(modalContainer) {
						thisInstance.registerColorEditorEvents(modalContainer,feedIndicator);
					}
				});
			} else {
				console.log("network error : ",e);
			}
		});
	},

	getFeedDeleteParameters : function(feedCheckbox) {
		return {
			module: 'Calendar',
			action: 'CalendarUserActions',
			mode : 'deleteUserCalendar',
			userid : feedCheckbox.data('calendarUserid')
		};
	},

	getDefaultCalendarView : function() {
		return 'month';
	},

	initializeCalendar : function() {
		var calendarConfigs = this.getCalendarConfigs();
		this.getCalendarViewContainer().fullCalendar(calendarConfigs);
		this.performPostRenderCustomizations();
	},

	registerEvents : function() {
		this._super();
	}
});