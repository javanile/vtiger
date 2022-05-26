/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Reports_Detail_Js("Reports_ChartDetail_Js", {
	
	/**
	 * Function used to display message when there is no data from the server
	 */
	displayNoDataMessage: function () {
		$('#chartcontent').html('<div>' + app.vtranslate('JS_NO_CHART_DATA_AVAILABLE') + '</div>').css(
				{'text-align': 'center', 'position': 'relative', 'top': '100px'});
	},
	
	/**
	 * Function returns if there is no data from the server
	 */
	isEmptyData: function () {
		var jsonData = jQuery('input[name=data]').val();
		var data = JSON.parse(jsonData);
		var values = data['values'];
		if (jsonData == '' || values == '') {
			return true;
		}
		return false;
	}
	
}, {
	
	/**
	 * Function returns instance of the chart type
	 */
	getInstance: function () {
		var chartType = jQuery('input[name=charttype]').val();
		var chartClassName = chartType.toLowerCase().replace(/\b[a-z]/g, function(letter) {
			return letter.toUpperCase();
		});
		var chartClass = window["Report_" + chartClassName + "_Js"];

		var instance = false;
		if (typeof chartClass != 'undefined')
			instance = new chartClass();
		return instance;
	},
	
	registerSaveOrGenerateReportEvent: function () {
		var thisInstance = this;
		jQuery('.generateReportChart').on('click', function (e) {
			var advFilterCondition = thisInstance.calculateValues();
			var recordId = thisInstance.getRecordId();
			var currentMode = jQuery(e.currentTarget).data('mode');
			var groupByField = jQuery('#groupbyfield').val();
			var dataField = jQuery('#datafields').val();
			if(dataField == null || dataField == '') {
				vtUtils.showValidationMessage(jQuery('#datafields').parent().find('.select2-choices'), app.vtranslate('JS_REQUIRED_FIELD'));
				return false;
			} else {
				vtUtils.hideValidationMessage(jQuery('#datafields').parent().find('.select2-choices'));
			}
			
			if(groupByField == null || groupByField == "") {
				vtUtils.showValidationMessage(jQuery('#groupbyfield').parent().find('.select2-container'), app.vtranslate('JS_REQUIRED_FIELD'));
				return false;
			} else {
				vtUtils.hideValidationMessage(jQuery('#groupbyfield').parent().find('.select2-container'));
			}
			
			var postData = {
				'advanced_filter': advFilterCondition,
				'record': recordId,
				'view': "ChartSaveAjax",
				'module': app.getModuleName(),
				'mode': currentMode,
				'charttype': jQuery('input[name=charttype]').val(),
				'groupbyfield': groupByField,
				'datafields': dataField
			};

			var reportChartContents = thisInstance.getContentHolder().find('#reportContentsDiv');
			app.helper.showProgress();
			e.preventDefault();
			app.request.post({data: postData}).then(
					function (error, data) {
						app.helper.hideProgress();
						reportChartContents.html(data);
						thisInstance.registerEventForChartGeneration();
						jQuery('.reportActionButtons').addClass('hide');
					}
			);
		});
	},
	
	registerEventForChartGeneration: function () {
		var thisInstance = this;
		try {
			thisInstance.getInstance();	// instantiate the object and calls init function
			jQuery('#chartcontent').trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
		} catch (error) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return;
		}
	},
	
        savePinToDashBoard : function(customParams) {
            var element = jQuery('button.pinToDashboard');
            var recordId = this.getRecordId();
            var primarymodule = jQuery('input[name="primary_module"]').val();
            var widgetTitle = 'ChartReportWidget_' + primarymodule + '_' + recordId;
            var params = {
                    module: 'Reports',
                    action: 'ChartActions',
                    mode: 'pinChartToDashboard',
                    reportid: recordId,
                    title: widgetTitle
            };
            params = jQuery.extend(params, customParams);
            app.request.post({data: params}).then(function (error,data) {
                    if (data.duplicate) {
                            var params = {
                                    message: app.vtranslate('JS_CHART_ALREADY_PINNED_TO_DASHBOARD', 'Reports')
                            };
                            app.helper.showSuccessNotification(params);
                    } else {
                            var message = app.vtranslate('JS_CHART_PINNED_TO_DASHBOARD', 'Reports');
                            app.helper.showSuccessNotification({message:message});
                            element.find('i').removeClass('vicon-pin');
                            element.find('i').addClass('vicon-unpin');
                            element.removeClass('dropdown-toggle').removeAttr('data-toggle');
                            element.attr('title', app.vtranslate('JSLBL_UNPIN_CHART_FROM_DASHBOARD'));
                    }
            });
        },
	
	registerEventForPinChartToDashboard: function () {
		var thisInstance = this;
		jQuery('button.pinToDashboard').on('click', function (e) {
			var element = jQuery(e.currentTarget);
			var recordId = thisInstance.getRecordId();
			var pinned = element.find('i').hasClass('vicon-pin');
			if(pinned) {
                                if(element.is('[data-toggle]')){
                                    return;
                                }else{
                                    thisInstance.savePinToDashBoard();
                                }
			} else {
				var params = {
					module: 'Reports',
					action: 'ChartActions',
					mode: 'unpinChartFromDashboard',
					reportid: recordId
				};
				app.request.post({data: params}).then(function (error,data) {
					if(data.unpinned) {
						var message = app.vtranslate('JS_CHART_REMOVED_FROM_DASHBOARD', 'Reports');
						app.helper.showSuccessNotification({message:message});
						element.find('i').removeClass('vicon-unpin');
						element.find('i').addClass('vicon-pin');
                                                if(element.data('dashboardTabCount') >1) {
                                                    element.addClass('dropdown-toggle').attr('data-toggle','dropdown');
                                                }
						element.attr('title', app.vtranslate('JSLBL_PIN_CHART_TO_DASHBOARD'));
					}
				});
			}
		});
                
                jQuery('button.pinToDashboard').closest('.btn-group').find('.dashBoardTab').on('click',function(e){
                    var dashBoardTabId = jQuery(e.currentTarget).data('tabId');
                    thisInstance.savePinToDashBoard({'dashBoardTabId':dashBoardTabId});
                });
	},
	
	registerEvents: function () {
		this._super();
		this.registerEventForChartGeneration();
		Reports_ChartEdit3_Js.registerFieldForChosen();
		Reports_ChartEdit3_Js.initSelectValues();
		this.registerEventForPinChartToDashboard();
		var chartEditInstance = new Reports_ChartEdit3_Js();
		chartEditInstance.lineItemCalculationLimit();
	}
});


Vtiger_Pie_Widget_Js('Report_Piechart_Js', {}, {
	
	postInitializeCalls: function () {
		var thisInstance = this;
		var clickThrough = jQuery('input[name=clickthrough]', this.getContainer()).val();
		if (clickThrough != '') {
			thisInstance.getContainer().off('vtchartClick').on('vtchartClick', function (e, data) {
				if (data.url)
					thisInstance.openUrl(data.url);
			});
		}
	},
	
	postLoadWidget: function () {
		if (!Reports_ChartDetail_Js.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
		this.postInitializeCalls();
		this.restrictContentDrag();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
		if (widgetContent.length) {
			if (!jQuery('input[name=clickthrough]', this.getContainer()).val()) {
				var adjustedHeight = this.getContainer().height() - 50;
				app.helper.showVerticalScroll(widgetContent, {'height': adjustedHeight});
			}
			widgetContent.css({height: widgetContent.height() - 100});
		}
	},
	
	positionNoDataMsg: function () {
		Reports_ChartDetail_Js.displayNoDataMessage();
	},
	
	getPlotContainer: function (useCache) {
		if (typeof useCache == 'undefined') {
			useCache = false;
		}
		if (this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = jQuery('div[name="chartcontent"]', container);
		}
		return this.plotContainer;
	},
	
	init: function (parent) {
		if (parent) {
			this._super(parent);
		} else {
			this._super(jQuery('#reportContentsDiv'));
		}
	},
	
	generateData: function () {
		if (Reports_ChartDetail_Js.isEmptyData()) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return false;
		}

		var jsonData = jQuery('input[name=data]', this.getContainer()).val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		for (var i in values) {
			chartData[i] = [];
			chartData[i].push(data['labels'][i]);
			chartData[i].push(values[i]);
		}
		return {'chartData': chartData,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'data_type'	: data['data_type'],
			'title': data['graph_label']};
	},
	
	generateLinks: function () {
		var jData = jQuery('input[name=data]', this.getContainer()).val();
		var statData = JSON.parse(jData);
		var links = statData['links'];
		return links;
	}

});

Vtiger_Barchat_Widget_Js('Report_Verticalbarchart_Js', {}, {
	
	postInitializeCalls: function () {
		var thisInstance = this;
		var clickThrough = jQuery('input[name=clickthrough]', this.getContainer()).val();
		if (clickThrough != '') {
			thisInstance.getContainer().off('vtchartClick').on('vtchartClick', function (e, data) {
				if (data.url)
					thisInstance.openUrl(data.url);
			});
		}
	},
	
	postLoadWidget: function () {
		if (!Reports_ChartDetail_Js.isEmptyData()) {
			this.loadChart();
		} else {
			this.positionNoDataMsg();
		}
		this.postInitializeCalls();
		this.restrictContentDrag();
		var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
		if (widgetContent.length) {
			if (!jQuery('input[name=clickthrough]', this.getContainer()).val()) {
				var adjustedHeight = this.getContainer().height() - 50;
				app.helper.showVerticalScroll(widgetContent, {'height': adjustedHeight});
			}
			widgetContent.css({height: widgetContent.height() - 100});
		}
	},
	
	positionNoDataMsg: function () {
		Reports_ChartDetail_Js.displayNoDataMessage();
	},
	
	getPlotContainer: function (useCache) {
		if (typeof useCache == 'undefined') {
			useCache = false;
		}
		if (this.plotContainer == false || !useCache) {
			var container = this.getContainer();
			this.plotContainer = jQuery('div[name="chartcontent"]', container);
		}
		return this.plotContainer;
	},
	
	init: function (parent) {
		if (parent) {
			this._super(parent);
		} else {
			this._super(jQuery('#reportContentsDiv'));
		}
	},
	
	generateChartData: function () {
		if (Reports_ChartDetail_Js.isEmptyData()) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return false;
		}

		var jsonData = jQuery('input[name=data]', this.getContainer()).val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		if (data['type'] == 'singleBar') {
			chartData[0] = [];
			for (var i in values) {
				var multiValue = values[i];
				for (var j in multiValue) {
					chartData[0].push(multiValue[j]);
					if (multiValue[j] > yMaxValue)
						yMaxValue = multiValue[j];
				}
			}
		} else {
			for (var i in values) {
				var multiValue = values[i];
				var info = [];
				for (var j in multiValue) {
					if (typeof chartData[j] != 'undefined') {
						chartData[j].push(multiValue[j]);
					} else {
						chartData[j] = [];
						chartData[j].push(multiValue[j]);
					}
					if (multiValue[j] > yMaxValue)
						yMaxValue = multiValue[j];
				}
			}
		}
		yMaxValue = yMaxValue + (yMaxValue * 0.15);

		return {'chartData': chartData,
			'yMaxValue': yMaxValue,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'data_type'	: data['data_type'],
			'title': data['graph_label']
		};
	},
	
	generateLinks: function () {
		var jData = jQuery('input[name=data]', this.getContainer()).val();
		var statData = JSON.parse(jData);
		var links = statData['links'];
		return links;
	}
});

Report_Verticalbarchart_Js('Report_Horizontalbarchart_Js', {}, {
	
	generateChartData: function () {
		if (Reports_ChartDetail_Js.isEmptyData()) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return false;
		}
		var jsonData = jQuery('input[name=data]', this.getContainer()).val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		if (data['type'] == 'singleBar') {
			for (var i in values) {
				var multiValue = values[i];
				chartData[i] = [];
				for (var j in multiValue) {
					chartData[i].push(multiValue[j]);
					chartData[i].push(parseInt(i) + 1);
					if (multiValue[j] > yMaxValue) {
						yMaxValue = multiValue[j];
					}
				}
			}
			chartData = [chartData];
		} else {
			chartData = [];
			for (var i in values) {
				var multiValue = values[i];
				for (var j in multiValue) {
					if (typeof chartData[j] != 'undefined') {
						chartData[j][i] = [];
						chartData[j][i].push(multiValue[j]);
						chartData[j][i].push(parseInt(i) + 1);
					} else {
						chartData[j] = []
						chartData[j][i] = [];
						chartData[j][i].push(multiValue[j]);
						chartData[j][i].push(parseInt(i) + 1);
					}

					if (multiValue[j] > yMaxValue) {
						yMaxValue = multiValue[j];
					}
				}
			}
		}
		yMaxValue = yMaxValue + (yMaxValue * 0.15);

		return {'chartData': chartData,
			'yMaxValue': yMaxValue,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'data_type'	: data['data_type'],
			'title': data['graph_label']
		};

	},
	
	loadChart: function () {
		var data = this.generateChartData();
		var chartOptions = {
			renderer: 'horizontalbar'
		};
		if (this.data['links'])
			chartOptions.links = this.data['links'];
		this.getPlotContainer().vtchart(data, chartOptions);
		jQuery('table.jqplot-table-legend').css('width', '95px');
	}
});


Report_Verticalbarchart_Js('Report_Linechart_Js', {}, {
	
	generateData: function () {
		if (Reports_ChartDetail_Js.isEmptyData()) {
			Reports_ChartDetail_Js.displayNoDataMessage();
			return false;
		}

		var jsonData = jQuery('input[name=data]', this.getContainer()).val();
		var data = this.data = JSON.parse(jsonData);
		var values = data['values'];

		var chartData = [];
		var yMaxValue = 0;

		for (var i in values) {
			var value = values[i];
			for (var j in value) {
				if (typeof chartData[j] != 'undefined') {
					chartData[j].push(value[j]);
				} else {
					chartData[j] = []
					chartData[j].push(value[j]);
				}
			}
		}
		yMaxValue = yMaxValue + yMaxValue * 0.15;

		return {'chartData': chartData,
			'yMaxValue': yMaxValue,
			'labels': data['labels'],
			'data_labels': data['data_labels'],
			'data_type'	: data['data_type'],
			'title': data['graph_label']
		};
	},
	loadChart: function () {
		var data = this.generateData();
		var chartOptions = {
			renderer: 'linechart'
		};
		if (this.data['links'])
			chartOptions.links = this.data['links'];
		this.getPlotContainer().vtchart(data, chartOptions);
		jQuery('table.jqplot-table-legend').css('width', '95px');
	}
});
