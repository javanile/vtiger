/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Reports_Detail_Js",{},{
	advanceFilterInstance : false,
	detailViewContentHolder : false,
	HeaderContentsHolder : false, 
	
	detailViewForm : false,
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('form#detailView');
		}
	},
	
	getRecordId : function(){
		return app.getRecordId();
	},
	
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.editViewPageDiv');
		}
		return this.detailViewContentHolder;
	},
	
	getHeaderContentsHolder : function(){
		if(this.HeaderContentsHolder == false) {
			this.HeaderContentsHolder = jQuery('div.reportsDetailHeader ');
		}
		return this.HeaderContentsHolder;
	},
	
	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		return JSON.stringify(advfilterlist);
	},
		
	registerSaveOrGenerateReportEvent : function(){
		var thisInstance = this;
		jQuery('.generateReport').on('click',function(e){
            e.preventDefault();
			var advFilterCondition = thisInstance.calculateValues();
            var recordId = thisInstance.getRecordId();
            var currentMode = jQuery(e.currentTarget).data('mode');
            var postData = {
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'view' : "SaveAjax",
                'module' : app.getModuleName(),
                'mode' : currentMode
            };
			app.helper.showProgress();
			app.request.post({data:postData}).then(
				function(error,data){
					app.helper.hideProgress();
					thisInstance.getContentHolder().find('#reportContentsDiv').html(data);
					jQuery('.reportActionButtons').addClass('hide');
//					app.helper.showHorizontalScroll(jQuery('#reportDetails'));

					// To get total records count
					var count  = parseInt(jQuery('#updatedCount').val());
					thisInstance.generateReportCount(count);
				}
			);
		});
	},
	
    registerEventsForActions : function() {
      var thisInstance = this;
      jQuery('.reportActions').click(function(e){
        var element = jQuery(e.currentTarget); 
        var href = element.data('href');
        var type = element.attr("name");
        var advFilterCondition = thisInstance.calculateValues();
        var headerContainer = thisInstance.getHeaderContentsHolder();
        if(type.indexOf("Print") != -1){
            var newEle = '<form action='+href+' method="POST" target="_blank">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }else{
            newEle = '<form action='+href+' method="POST">\n\
                    <input type = "hidden" name ="'+csrfMagicName+'"  value=\''+csrfMagicToken+'\'>\n\
                    <input type="hidden" value="" name="advanced_filter" id="advanced_filter" /></form>';
        }
        var ele = jQuery(newEle); 
        var form = ele.appendTo(headerContainer);
        form.find('#advanced_filter').val(advFilterCondition);
        form.submit();
      })  
    },
    
    generateReportCount : function(count){
      var thisInstance = this;  
      var advFilterCondition = thisInstance.calculateValues();
      var recordId = thisInstance.getRecordId();
      
      var reportLimit = parseInt(jQuery("#reportLimit").val());
      
        if(count < reportLimit){
            jQuery('#countValue').text(count);
            jQuery('#moreRecordsText').addClass('hide');
        }else{        
            jQuery('#countValue').html('<img src="layouts/v7/skins/images/loading.gif">');
            var params = {
                'module' : app.getModuleName(),
                'advanced_filter': advFilterCondition,
                'record' : recordId,
                'action' : "DetailAjax",
                'mode': "getRecordsCount"
            };
            jQuery('.generateReport').attr("disabled","disabled");
            app.request.post({data:params}).then(
                function(error,data){
                    jQuery('.generateReport').removeAttr("disabled");
                    var count = parseInt(data);
                    jQuery('#countValue').text(count);
                    if(count > reportLimit)
                        jQuery('#moreRecordsText').removeClass('hide');
                    else
                        jQuery('#moreRecordsText').addClass('hide');
                }
            );
        }
      
    },
	
	registerConditionBlockChangeEvent : function() {
		jQuery('.reportsDetailHeader').find('#groupbyfield,#datafields,[name="columnname"],[name="comparator"]').on('change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.fieldUiHolder').find('[data-value="value"]').on('change input', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery('.deleteCondition').on('click', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
		jQuery(document).on('datepicker-change', function() {
			jQuery('.reportActionButtons').removeClass('hide');
		});
	},
	
	registerEventForModifyCondition : function() {
		jQuery('button[name=modify_condition]').on('click', function(e) {
			var icon =  jQuery(e.currentTarget).find('i');
			var isClassExist = jQuery(icon).hasClass('fa-chevron-right');
			if(isClassExist) {
				jQuery(e.currentTarget).find('i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
				jQuery('#filterContainer').removeClass('hide').show('slow');
			} else {
				jQuery(e.currentTarget).find('i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
				jQuery('#filterContainer').addClass('hide').hide('slow');
			}
			return false;
		});
	},
	
	registerEvents : function(){
		this.registerSaveOrGenerateReportEvent();
        this.registerEventsForActions();
		var container = this.getContentHolder();
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
        this.generateReportCount(parseInt(jQuery("#countValue").text()));
		this.registerConditionBlockChangeEvent();
		this.registerEventForModifyCondition();
	}
});