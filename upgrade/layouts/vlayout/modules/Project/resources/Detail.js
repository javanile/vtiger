/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Project_Detail_Js",{},{
	
	detailViewRecentTicketsTabLabel : 'Trouble Tickets',
	detailViewRecentTasksTabLabel : 'Project Tasks',
	detailViewRecentMileStonesLabel : 'Project Milestones',
	
	/**
	 * Function to register event for create related record
	 * in summary view widgets
	 */
	registerSummaryViewContainerEvents : function(summaryViewContainer){
		this._super(summaryViewContainer);
		this.registerStatusChangeEventForWidget();
		this.registerEventForAddingModuleRelatedRecordFromSummaryWidget();
	},
	
	/**
	* Function to get records according to ticket status
	*/
	registerStatusChangeEventForWidget : function(){
		var thisInstance = this;
		jQuery('[name="ticketstatus"],[name="projecttaskstatus"],[name="projecttaskprogress"]').on('change',function(e){
            var picklistName = this.name;
			var statusCondition = {};
			var params = {};
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
			var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
			var recordId = thisInstance.getRecordId();
			var module = app.getModuleName();
			var selectedStatus = currentElement.find('option:selected').text();
			if(selectedStatus != "Select Status" && referenceModuleName == "HelpDesk"){
				statusCondition['vtiger_troubletickets.status'] = selectedStatus;
				params['whereCondition'] = statusCondition;
			} else if(selectedStatus != app.vtranslate('JS_LBL_SELECT_STATUS') && referenceModuleName == "ProjectTask" && picklistName == 'projecttaskstatus'){
				statusCondition['vtiger_projecttask.projecttaskstatus'] = selectedStatus;
				params['whereCondition'] = statusCondition;
			}
            else if(selectedStatus != app.vtranslate('JS_LBL_SELECT_PROGRESS') && referenceModuleName == "ProjectTask" && picklistName == 'projecttaskprogress'){
				statusCondition['vtiger_projecttask.projecttaskprogress'] = selectedStatus;
				params['whereCondition'] = statusCondition;
			}
			
			params['record'] = recordId;
			params['view'] = 'Detail';
			params['module'] = module;
			params['page'] = widgetDataContainer.find('[name="page"]').val();
			params['limit'] = widgetDataContainer.find('[name="pageLimit"]').val();
			params['relatedModule'] = referenceModuleName;
			params['mode'] = 'showRelatedRecords';
			AppConnector.request(params).then(
				function(data) {
					widgetDataContainer.html(data);
				}
			);
	   })
	},
	
	/**
	 * Function to add module related record from summary widget
	 */
	registerEventForAddingModuleRelatedRecordFromSummaryWidget : function(){
		var thisInstance = this;
		jQuery('#createProjectMileStone,#createProjectTask').on('click',function(e){
			var currentElement = jQuery(e.currentTarget);
			var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
			var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
			var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
			var quickcreateUrl = currentElement.data('url');
			var parentId = thisInstance.getRecordId();
			var quickCreateParams = {};
			var relatedField = currentElement.data('parentRelatedField');
			var moduleName = currentElement.closest('.widget_header').find('[name="relatedModule"]').val();
			var relatedParams = {};
			relatedParams[relatedField] = parentId;
			
			var postQuickCreateSave = function(data) {
				thisInstance.postSummaryWidgetAddRecord(data,currentElement);
				if(referenceModuleName == "ProjectTask"){
					thisInstance.loadModuleSummary();
				}
			}
			
			if(typeof relatedField != "undefined"){
				quickCreateParams['data'] = relatedParams;
			}
			quickCreateParams['noCache'] = true;
			quickCreateParams['callbackFunction'] = postQuickCreateSave;
			var progress = jQuery.progressIndicator();
			var headerInstance = new Vtiger_Header_Js();
			headerInstance.getQuickCreateForm(quickcreateUrl, moduleName,quickCreateParams).then(function(data){
				headerInstance.handleQuickCreateData(data,quickCreateParams);
				progress.progressIndicator({'mode':'hide'});
			});
		})
	},
	
	/**
	 * Function to load module summary of Projects
	 */
	loadModuleSummary : function(){
		var summaryParams = {};
		summaryParams['module'] = app.getModuleName();
		summaryParams['view'] = "Detail";
		summaryParams['mode'] = "showModuleSummaryView";
		summaryParams['record'] = jQuery('#recordId').val();
		
		AppConnector.request(summaryParams).then(
			function(data) {
				jQuery('.summaryView').html(data);
			}
		);
	},
	
	registerEvents : function(){
		var detailContentsHolder = this.getContentHolder();
		var thisInstance = this;
		this._super();
		
		detailContentsHolder.on('click','.moreRecentMilestones', function(){
			var recentMilestonesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentMileStonesLabel);
			recentMilestonesTab.trigger('click');
		});
		
		detailContentsHolder.on('click','.moreRecentTickets', function(){
			var recentTicketsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTicketsTabLabel);
			recentTicketsTab.trigger('click');
		});
		
		detailContentsHolder.on('click','.moreRecentTasks', function(){
			var recentTasksTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTasksTabLabel);
			recentTasksTab.trigger('click');
		});
	}
})