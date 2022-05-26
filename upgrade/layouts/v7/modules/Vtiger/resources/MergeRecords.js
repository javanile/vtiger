/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class('Vtiger_MergeRecords_Js',{},{
    
    showMergeUI : function(params) {
        var self = this;
        var records = params.records;
        if(typeof records == "object") {
            records = records.join(',');
        }
        var defaultPrams = {
            'module' : app.module(),
            'view' : 'MergeRecord',
            'records' : records
        }
        app.helper.showProgress();
        app.request.get({'data':defaultPrams}).then(function(error,data){
            app.helper.hideProgress();
            if(data) {
                app.helper.loadPageContentOverlay(data).then(function(container){
                    self.registerUIEvents(container);
                });
            }
        });
    },
    
    save : function(form){
        var aDeferred = jQuery.Deferred();
        var formData = form.serializeFormData();
        app.helper.showProgress();
        app.request.post({'data':formData}).then(function(error,data){
            app.helper.hideProgress();
			if (error === null) {
				jQuery('.vt-notification').remove();
				app.helper.hidePageContentOverlay();
				app.event.trigger('post.MergeRecords',formData);
				aDeferred.resolve();
			} else {
				app.event.trigger('post.save.failed', error);
				aDeferred.resolve();
			}
        })
        return aDeferred.promise();
    },
    
    registerUIEvents : function(container) {
        var self = this;
        
        // Adding Scroll 
        var offset = container.find('.modal-body .datacontent').offset();
        var viewPortHeight = $(window).height()-60;
		if (offset) {
			viewPortHeight = (viewPortHeight-offset['top']);
		}
        var params = {
                        setHeight:viewPortHeight+'px'
                    };
        app.helper.showVerticalScroll(container.find('.modal-body .datacontent'), params);
        
        container.find('[name="primaryRecord"]').on('change', function(event) {
            var id = jQuery(event.currentTarget).val();
            container.find('[data-id='+id+']').prop('checked',true);
		});
        
        container.find('form').on('submit',function(e){
            e.preventDefault();
            var form = jQuery(e.currentTarget);
            self.save(form);
        })
    },
    
    registerListener : function() {
        var self = this;
        app.event.on('Request.MergeRecords.show',function(event,params){
			var vtigerInstance = Vtiger_Index_Js.getInstance();
			vtigerInstance.registerEventForPostSaveFail();
            self.showMergeUI(params);
        })
    },
    
    registerEvents : function(){
        this.registerListener();
    }
})