/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Vtiger_Widget_Js',{

	widgetPostLoadEvent : 'Vtiget.Dashboard.PostLoad',
	widgetPostRefereshEvent : 'Vtiger.Dashboard.PostRefresh',
    widgetPostResizeEvent : 'Vtiger.DashboardWidget.PostResize',

	getInstance : function(container, widgetName, moduleName) {
		if(typeof moduleName == 'undefined') {
			moduleName = app.getModuleName();
		}
		var widgetClassName = widgetName;
		var moduleClass = window[moduleName+"_"+widgetClassName+"_Widget_Js"];
		var fallbackClass = window["Vtiger_"+widgetClassName+"_Widget_Js"];
		var basicClass = Vtiger_Widget_Js;
		if(typeof moduleClass != 'undefined') {
			var instance = new moduleClass(container);
		}else if(typeof fallbackClass != 'undefined') {
			var instance = new fallbackClass(container);
		} else {
			var instance = new basicClass(container);
		}
		return instance;
	}
},{

	container : false,
	plotContainer : false,

	init : function (container) {
		this.setContainer(jQuery(container));
		this.registerWidgetPostLoadEvent(container);
		this.registerWidgetPostRefreshEvent(container);
        this.registerWidgetPostResizeEvent(container); 
	},

	getContainer : function() {
		return this.container;
	},

	setContainer : function(element) {
		this.container = element;
		return this;
	},

	isEmptyData : function() {
		var container = this.getContainer();
		return (container.find('.noDataMsg').length > 0) ? true : false;
	},

	getUserDateFormat : function() {
		return jQuery('#userDateFormat').val();
	},


	getPlotContainer : function(useCache) {
		if(typeof useCache == 'undefined'){
			useCache = false;
		}
		if(this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = container.find('.widgetChartContainer');
		}
		return this.plotContainer;
	},

	restrictContentDrag : function(){
		this.getContainer().on('mousedown.draggable', function(e){
			var element = jQuery(e.target);
			var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
            var isResizeElement = element.is(".gs-resize-handle") ? true : false;
			if(isHeaderElement || isResizeElement){
				return;
			}
			//Stop the event propagation so that drag will not start for contents
			e.stopPropagation();
		})
	},

	convertToDateRangePicketFormat : function(userDateFormat) {
		if(userDateFormat == 'yyyy-mm-dd') {
			return 'yyyy-MM-dd';
		}else if( userDateFormat == 'mm-dd-yyyy') {
			return 'MM-dd-yyyy';
		}else if(userDateFormat == 'dd-mm-yyyy') {
			return 'dd-MM-yyyy';
		}
	},

	loadChart : function() {

	},

	positionNoDataMsg : function() {
		var container = this.getContainer();
		var widgetContentsContainer = container.find('.dashboardWidgetContent');
        widgetContentsContainer.height(container.height()- 50);
		var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
		noDataMsgHolder.position({
				'my' : 'center center',
				'at' : 'center center',
				'of' : widgetContentsContainer
		})
	},
    
    postInitializeCalls : function() {},

	//Place holdet can be extended by child classes and can use this to handle the post load
	postLoadWidget : function() {
		if(!this.isEmptyData()) {
			this.loadChart();
            this.postInitializeCalls();
		}else{
			//this.positionNoDataMsg();
		}
		this.registerFilter();
		this.registerFilterChangeEvent();
		this.restrictContentDrag();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        widgetContent.css({height: widgetContent.height()-40});
	},
    
	postResizeWidget : function() {
		if(!this.isEmptyData()) {
			this.loadChart();
            this.postInitializeCalls();
		}else{
			//this.positionNoDataMsg();
		}
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        widgetContent.css({height: widgetContent.height()-40});
	},

	postRefreshWidget : function() {
		if(!this.isEmptyData()) {
			this.loadChart();
            this.postInitializeCalls();
		}else{
//			this.positionNoDataMsg();
		}
	},

	getFilterData : function() {
		return {};
	},

	refreshWidget : function() {
		var parent = this.getContainer();
		var element = parent.find('a[name="drefresh"]');
		var url = element.data('url');

        var contentContainer = parent.find('.dashboardWidgetContent');
		var params = {};
        params.url = url;
		var widgetFilters = parent.find('.widgetFilter');
		if(widgetFilters.length > 0) {
			params.url = url;
			params.data = {};
			widgetFilters.each(function(index, domElement){
				var widgetFilter = jQuery(domElement);
                //Filter unselected checkbox, radio button elements
                if((widgetFilter.is(":radio") || widgetFilter.is(":checkbox")) && !widgetFilter.is(":checked")){
                    return true;
                }
				if(widgetFilter.is('.dateRange')){
					var name = widgetFilter.attr('name');
                    var start = widgetFilter.find('input[name="start"]').val();
                    var end = widgetFilter.find('input[name="end"]').val();
                    if(start.length <= 0 || end.length <= 0  ){
                        return true;
                    } 
                    
					params.data[name] = {};
					params.data[name].start = start;
					params.data[name].end = end;
				}else{
					var filterName = widgetFilter.attr('name');
					var filterValue = widgetFilter.val();
					params.data[filterName] = filterValue;
				}
			});
		}
		var filterData = this.getFilterData();
		if(! jQuery.isEmptyObject(filterData)) {
			if(typeof params == 'string') {
				url = params;
				params = {};
				params.url = url;
				params.data = {};
			}
			params.data = jQuery.extend(params.data, this.getFilterData())
		}
		
		//Sending empty object in data results in invalid request
		if(jQuery.isEmptyObject(params.data)) {
			delete params.data;
		}
		
		app.helper.showProgress();
		app.request.post(params).then(
			function(err,data){
                app.helper.hideProgress();
				
				if(contentContainer.closest('.mCustomScrollbar').length) {
					contentContainer.mCustomScrollbar('destroy');
					contentContainer.html(data);
					var adjustedHeight = parent.height()-100;
					app.helper.showVerticalScroll(contentContainer,{'setHeight' : adjustedHeight});
				}else {
					contentContainer.html(data);
				}
                
                /**
                 * we are setting default height in DashBoardWidgetContents.tpl
                 * need to overwrite based on resized widget height 
                 */ 
                var widgetChartContainer = contentContainer.find(".widgetChartContainer");
                if(widgetChartContainer.length > 0){
                    widgetChartContainer.css("height",parent.height() - 60);
                }
				contentContainer.trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
			}
		);
	},

	registerFilter : function() {
		var thisInstance = this;
		var container = this.getContainer();
		var dateRangeElement = container.find('.input-daterange');
		if(dateRangeElement.length <= 0) {
			return;
		}
		
		dateRangeElement.addClass('dateField');
		
		var pickerParams = {
            format : thisInstance.getUserDateFormat(),
        };
		vtUtils.registerEventForDateFields(dateRangeElement, pickerParams);
		
        dateRangeElement.on("changeDate", function(e){
           var start = dateRangeElement.find('input[name="start"]').val();
           var end = dateRangeElement.find('input[name="end"]').val();
           if(start != '' && end != '' && start !== end){
               container.find('a[name="drefresh"]').trigger('click');
           }
        });
		dateRangeElement.attr('data-date-format',thisInstance.getUserDateFormat());
	},

	registerFilterChangeEvent : function() {
		this.getContainer().on('change', '.widgetFilter, .reloadOnChange', function(e) {
			var target = jQuery(e.currentTarget);
			if(target.hasClass('dateRange')) {
				var start = target.find('input[name="start"]').val();
				var end = target.find('input[name="end"]').val();
				if(start == '' || end == '') return false;
			}
			
			var widgetContainer = target.closest('li');
			widgetContainer.find('a[name="drefresh"]').trigger('click');
		})
	},

	registerWidgetPostLoadEvent : function(container) {
		var thisInstance = this;
		container.off(Vtiger_Widget_Js.widgetPostLoadEvent).on(Vtiger_Widget_Js.widgetPostLoadEvent, function(e) {
			thisInstance.postLoadWidget();
		})
	},

	registerWidgetPostRefreshEvent : function(container) {
		var thisInstance = this;
		container.on(Vtiger_Widget_Js.widgetPostRefereshEvent, function(e) {
			thisInstance.postRefreshWidget();
		});
	},
    
    registerWidgetPostResizeEvent : function(container){
        var thisInstance = this;
		container.on(Vtiger_Widget_Js.widgetPostResizeEvent, function(e) { 
			thisInstance.postResizeWidget();
		});
    },
    
    openUrl : function(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }
});


Vtiger_Widget_Js('Vtiger_KeyMetrics_Widget_Js', {}, {
    postLoadWidget: function() {
		this._super();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
		widgetContent.css({height: widgetContent.height()-40});
	},

	postResizeWidget: function () {
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
		var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
		var adjustedHeight = this.getContainer().height() - 20;
		widgetContent.css({height: adjustedHeight});
		slimScrollDiv.css({height: adjustedHeight});
	}
});

Vtiger_Widget_Js('Vtiger_TopPotentials_Widget_Js', {}, {
    
   postLoadWidget: function() {
		this._super();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
		widgetContent.css({height: widgetContent.height()-40});
	}
});

Vtiger_Widget_Js('Vtiger_History_Widget_Js', {}, {

	postLoadWidget: function() {
		this._super();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
		widgetContent.css({height: widgetContent.height()-40});
        //this.initSelect2Elements(widgetContent);
		this.registerLoadMore();
	},
    
    postResizeWidget: function() {
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height()-100;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
	},
        
	initSelect2Elements : function(widgetContent) {
		var container = widgetContent.closest('.dashboardWidget');
		var select2Elements = container.find('.select2');
		if(select2Elements.length > 0 && jQuery.isArray(select2Elements)) {
			select2Elements.each(function(index, domElement){
				domElement.chosen();
			});
		}else{
			select2Elements.chosen();
		}
	},

	postRefreshWidget: function() {
		this._super();
		this.registerLoadMore();
	},

	registerLoadMore: function() {
		var thisInstance  = this;
		var parent = thisInstance.getContainer();
		var contentContainer = parent.find('.dashboardWidgetContent');

		var loadMoreHandler = contentContainer.find('.load-more');
		loadMoreHandler.off('click');
		loadMoreHandler.click(function(){
			var parent = thisInstance.getContainer();
			var element = parent.find('a[name="drefresh"]');
			var url = element.data('url');
			var params = url;

			var widgetFilters = parent.find('.widgetFilter');
			if(widgetFilters.length > 0) {
				params = { url: url, data: {}};
				widgetFilters.each(function(index, domElement){
					var widgetFilter = jQuery(domElement);
					//Filter unselected checkbox, radio button elements
					if((widgetFilter.is(":radio") || widgetFilter.is(":checkbox")) && !widgetFilter.is(":checked")){
						return true;
					}
					
					if(widgetFilter.is('.dateRange')) {
						var name = widgetFilter.attr('name');
						var start = widgetFilter.find('input[name="start"]').val();
						var end = widgetFilter.find('input[name="end"]').val();
						if(start.length <= 0 || end.length <= 0  ){
							return true;
						} 

						params.data[name] = {};
						params.data[name].start = start;
						params.data[name].end = end;
					} else {
						var filterName = widgetFilter.attr('name');
						var filterValue = widgetFilter.val();
						params.data[filterName] = filterValue;
					}
				});
			}

			var filterData = thisInstance.getFilterData();
			if(! jQuery.isEmptyObject(filterData)) {
				if(typeof params == 'string') {
					params = { url: url, data: {}};
				}
				params.data = jQuery.extend(params.data, thisInstance.getFilterData())
			}

			// Next page.
			params.data['page'] = loadMoreHandler.data('nextpage');

            app.helper.showProgress();
			app.request.post(params).then(function(err,data){
				app.helper.hideProgress();
				loadMoreHandler.parent().parent().replaceWith(jQuery(data).html());
				thisInstance.registerLoadMore();
			}, function(){
				app.helper.hideProgress();
			});
		});
	}

});


Vtiger_Widget_Js('Vtiger_Funnel_Widget_Js',{},{

    postInitializeCalls: function() {
        var thisInstance = this;
        this.getPlotContainer(false).off('vtchartClick').on('vtchartClick',function(e,data){
            if(data.url)
                thisInstance.openUrl(data.url);
        });
    },
    
    generateLinks : function() {
        var data = this.getContainer().find('.widgetData').val();
        var parsedData = JSON.parse(data);
        var linksData = [];
        for(var index in parsedData) {
            var newData = {};
            var itemDetails = parsedData[index];
            newData.name = itemDetails[0];
            newData.links = itemDetails[3];
            linksData.push(newData);
        }
        return linksData;
    },

	loadChart : function() {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
        var chartOptions = {
            renderer:'funnel',
            links: this.generateLinks()
        };
        this.getPlotContainer(false).vtchart(data,chartOptions);
	}
    
});



Vtiger_Widget_Js('Vtiger_Pie_Widget_Js',{},{

	/**
	 * Function which will give chart related Data
	 */
	generateData : function() {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		for(var index in data) {
			var row = data[index];
			var rowData = [row.last_name, parseFloat(row.amount), row.id];
			chartData.push(rowData);
		}
		return {'chartData':chartData};
	},
    
    generateLinks : function() {
        var jData = this.getContainer().find('.widgetData').val();
        var statData = JSON.parse(jData);
        var links = [];
        for(var i = 0; i < statData.length ; i++){
            links.push(statData[i]['links']);
        }
        return links;
    },
    
    postInitializeCalls: function() {
        var thisInstance = this;
        this.getPlotContainer(false).off('vtchartClick').on('vtchartClick',function(e,data){
            if(data.url)
                thisInstance.openUrl(data.url);
        });
    },

	loadChart : function() {
		var chartData = this.generateData();
        var chartOptions = {
            renderer:'pie',
            links: this.generateLinks()
        };
        this.getPlotContainer(false).vtchart(chartData,chartOptions);
	}
});


Vtiger_Widget_Js('Vtiger_Barchat_Widget_Js',{},{

	generateChartData : function() {
		var container = this.getContainer();
		var jData = container.find('.widgetData').val();
		var data = JSON.parse(jData);
		var chartData = [];
		var xLabels = new Array();
		var yMaxValue = 0;
		for(var index in data) {
			var row = data[index];
			row[0] = parseFloat(row[0]);
			xLabels.push(app.getDecodedValue(row[1]));
			chartData.push(row[0]);
			if(parseInt(row[0]) > yMaxValue){
				yMaxValue = parseInt(row[0]);
			}
		}
        // yMaxValue Should be 25% more than Maximum Value
		yMaxValue = yMaxValue + 2 + (yMaxValue/100)*25;
		return {'chartData':[chartData], 'yMaxValue':yMaxValue, 'labels':xLabels};
	},
    
    generateLinks : function() {
        var container = this.getContainer();
        var jData = container.find('.widgetData').val();
        var statData = JSON.parse(jData);
        var links = [];
        for(var i = 0; i < statData.length ; i++){
            links.push(statData[i]['links']);
        }
        return links;
    },
    
    postInitializeCalls : function() {
        var thisInstance = this;
        this.getPlotContainer(false).off('vtchartClick').on('vtchartClick',function(e,data){
            if(data.url)
                thisInstance.openUrl(data.url);
        });
    },

	loadChart : function() {
		var data = this.generateChartData();
        var chartOptions = {
            renderer:'bar',
            links: this.generateLinks()
        };
        this.getPlotContainer(false).vtchart(data,chartOptions);
	}
    
});

Vtiger_Widget_Js('Vtiger_MultiBarchat_Widget_Js',{

	/**
	 * Function which will give char related Data like data , x labels and legend labels as map
	 */
	getCharRelatedData : function() {
		var container = this.getContainer();
		var data = container.find('.widgetData').val();
		var users = new Array();
		var stages = new Array();
		var count = new Array();
		for(var i=0; i<data.length;i++) {
			if($.inArray(data[i].last_name, users) == -1) {
				users.push(data[i].last_name);
			}
			if($.inArray(data[i].sales_stage, stages) == -1) {
				stages.push(data[i].sales_stage);
			}
		}

		for(j in stages) {
			var salesStageCount = new Array();
			for(i in users) {
				var salesCount = 0;
				for(var k in data) {
					var userData = data[k];
					if(userData.sales_stage == stages[j] && userData.last_name == users[i]) {
						salesCount = parseInt(userData.count);
						break;
					}
				}
				salesStageCount.push(salesCount);
			}
			count.push(salesStageCount);
		}
		return {
			'data' : count,
			'ticks' : users,
			'labels' : stages
		}
	},
    
    postInitializeCalls : function() {
        var thisInstance = this;
        this.getPlotContainer(false).off('vtchartClick').on('vtchartClick',function(e,data){
            if(data.url)
                thisInstance.openUrl(data.url);
        });
    },
    
	loadChart : function(){
		var chartRelatedData = this.getCharRelatedData();
        var chartOptions = {
            renderer:'multibar',
            links:chartRelatedData.links
        };
        this.getPlotContainer(false).data('widget-data',JSON.stringify(this.getCharRelatedData()));
        this.getPlotContainer(false).vtchart(chartRelatedData,chartOptions);
	}

});

// NOTE Widget-class name camel-case convention
Vtiger_Widget_Js('Vtiger_MiniList_Widget_Js', {
    
    registerMoreClickEvent : function(e) {
        var moreLink = jQuery(e.currentTarget);
        var linkId = moreLink.data('linkid');
        var widgetId = moreLink.data('widgetid');
        var currentPage = jQuery('#widget_'+widgetId+'_currentPage').val();
        var nextPage = parseInt(currentPage) + 1;
        var params = {
            'module' : app.getModuleName(),
            'view' : 'ShowWidget',
            'name' : 'MiniList',
            'linkid' : linkId,
            'widgetid' : widgetId,
            'content' : 'data',
            'currentPage' : currentPage
        }
        app.request.post({"data":params}).then(function(err,data) {
            var htmlData = jQuery(data);
            var htmlContent = htmlData.find('.miniListContent');
            moreLink.parent().before(htmlContent);
            jQuery('#widget_'+widgetId+'_currentPage').val(nextPage);
            var moreExists = htmlData.find('.moreLinkDiv').length;
            if(!moreExists) {
                moreLink.parent().remove();
            }
        });
    }
    
}, {
	postLoadWidget: function() {
        app.helper.hideModal();
        this.restrictContentDrag();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
        widgetContent.css({height: widgetContent.height()-40});
	},
    
    postResizeWidget: function() {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height()-100;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
	}
});

Vtiger_Widget_Js('Vtiger_TagCloud_Widget_Js',{},{

	postLoadWidget : function() {
		this._super();
		this.registerTagCloud();
		this.registerTagClickEvent();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
        widgetContent.css({height: widgetContent.height()-40});
	},

	registerTagCloud : function() {
		jQuery('#tagCloud').find('a').tagcloud({
			size: {
			  start: parseInt('12'),
			  end: parseInt('30'),
			  unit: 'px'
			},
			color: {
			  start: "#0266c9",
			  end: "#759dc4"
			}
		});
	},

	registerChangeEventForModulesList : function() {
		jQuery('#tagSearchModulesList').on('change',function(e) {
			var modulesSelectElement = jQuery(e.currentTarget);
			if(modulesSelectElement.val() == 'all'){
				jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
			} else{
				jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
				var selectedOptionValue = modulesSelectElement.val();
				jQuery('[name="tagSearchModuleResults"]').filter(':not(#'+selectedOptionValue+')').addClass('hide');
			}
		});
	},

	registerTagClickEvent : function(){
		var thisInstance = this;
		var container = this.getContainer();
		container.on('click','.tagName',function(e) {
			var tagElement = jQuery(e.currentTarget);
			var tagId = tagElement.data('tagid');
			var params = {
				'module' : app.getModuleName(),
				'view' : 'TagCloudSearchAjax',
				'tag_id' : tagId,
				'tag_name' : tagElement.text()
			}
			app.request.post({"data":params}).then(
				function(err,data) {
                    app.helper.showModal(data);
                    vtUtils.applyFieldElementsView(jQuery(".myModal"));
					thisInstance.registerChangeEventForModulesList();
				}
			)
		});
	},

	postRefreshWidget : function() {
		this._super();
		this.registerTagCloud();
	},

	postResizeWidget: function () {
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
		var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
		var adjustedHeight = this.getContainer().height() - 20;
		widgetContent.css({height: adjustedHeight});
		slimScrollDiv.css({height: adjustedHeight});
	}
});

/* Notebook Widget */
Vtiger_Widget_Js('Vtiger_Notebook_Widget_Js', {

}, {

	// Override widget specific functions.
	postLoadWidget: function() {
		this.reinitNotebookView();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
        //widgetContent.css({height: widgetContent.height()-40});
	},
    
    postResizeWidget: function() {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height()-100;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
        widgetContent.find('.dashboard_notebookWidget_viewarea').css({height:adjustedHeight});
	},
    
    postRefreshWidget : function() {
        this.reinitNotebookView();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height()-50;
        app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
    },
    
	reinitNotebookView: function() {
		var self = this;
		jQuery('.dashboard_notebookWidget_edit', this.container).click(function(){
			self.editNotebookContent();
		});
		jQuery('.dashboard_notebookWidget_save', this.container).click(function(){
			self.saveNotebookContent();
		});
	},

	editNotebookContent: function() {
		jQuery('.dashboard_notebookWidget_text', this.container).show();
		jQuery('.dashboard_notebookWidget_view', this.container).hide();
	},

	saveNotebookContent: function() {
		var self = this;
		var refreshContainer = this.container.find('.refresh');
		var textarea = jQuery('.dashboard_notebookWidget_textarea', this.container);

		var url = this.container.data('url');
		var params = url + '&content=true&mode=save&contents=' + textarea.val();

		app.helper.showProgress();
		app.request.post({"url":params}).then(function(err,data) {
            app.helper.hideProgress();
			var parent = self.getContainer();
			var widgetContent = parent.find('.dashboardWidgetContent');
			widgetContent.mCustomScrollbar('destroy');
			widgetContent.html(data);
			var adjustedHeight = parent.height() - 50;
			app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight});
			
			self.reinitNotebookView();
		});
	},
	
	refreshWidget : function() {
		var parent = this.getContainer();
		var element = parent.find('a[name="drefresh"]');
		var url = element.data('url');

        var contentContainer = parent.find('.dashboardWidgetContent');
		var params = {};
        params.url = url;
		
		app.helper.showProgress();
		app.request.post(params).then(
			function(err,data){
                app.helper.hideProgress();
				
				if(contentContainer.closest('.mCustomScrollbar').length) {
					contentContainer.mCustomScrollbar('destroy');
					contentContainer.html(data);
					var adjustedHeight = parent.height()-50;
					app.helper.showVerticalScroll(contentContainer,{'setHeight' : adjustedHeight});
				}else {
					contentContainer.html(data);
				}
                
				contentContainer.trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
			}
		);
	},
});

Vtiger_History_Widget_Js('Vtiger_OverdueActivities_Widget_Js', {}, {

	registerLoadMore: function() {
		var thisInstance  = this;
		var parent = thisInstance.getContainer();
        parent.off('click', 'a[name="history_more"]'); 
        parent.on('click','a[name="history_more"]', function(e) {
			var parent = thisInstance.getContainer();
            var element = jQuery(e.currentTarget);
            var type = parent.find("[name='type']").val();
			var url = element.data('url');
			var params = url+'&content=true&type='+type;
            app.request.post({"url":params}).then(function(err,data) {
                element.parent().remove();
				var widgetContent = jQuery('.dashboardWidgetContent', parent);
				var dashboardWidgetData = parent.find('.dashboardWidgetContent .dashboardWidgetData');
				var scrollTop = dashboardWidgetData.height() * dashboardWidgetData.length - 100;
				widgetContent.mCustomScrollbar('destroy');
                parent.find('.dashboardWidgetContent').append(data);
				
				var adjustedHeight = parent.height()-100;
				app.helper.showVerticalScroll(widgetContent,{'setHeight' : adjustedHeight, 'setTop' : scrollTop+'px'});
				
            });
		});
	}

});

Vtiger_OverdueActivities_Widget_Js('Vtiger_CalendarActivities_Widget_Js', {}, {});
