/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("PriceBooks_Detail_Js",{},{
	
	listPriceUpdateContainer : false,
	
	/**
	 * Function to get listPrice update container
	 */
	getListPriceUpdateContainer : function(){
		return this.listPriceUpdateContainer;
	},
	
	/**
	 * Function to registerevent for updatelistprice in modal window on click of save
	 */
	registerEventforUpdateListPrice : function(){
		var thisInstance = this;
		var form = jQuery('#listPriceUpdate');
        var params = {
            submitHandler: function(form) {
                form = jQuery(form);
                var idList = new Array();
                var relid = form.find('input[name="relid"]').val();
                var listPriceVal = form.find('input[name="currentPrice"]').val();
                idList.push({'id' : relid,'price' : listPriceVal});
                var relatedListInstance = thisInstance.getRelatedController();
                var relation = relatedListInstance.addRelations(idList, false);
                app.helper.hideModal();
                relation.done(function (){
                    relatedListInstance.loadRelatedList()
                });
            }
        };
        form.vtValidate(params);
	},
	/**
	 * Function to show listprice update form
	 */
	showListPriceUpdate : function(data){
        var self = this;
		app.helper.showModal(data, {cb: function(){
            self.registerEventforUpdateListPrice();
        }});
	},
	
	/**
	 * Function to get listprice edit form
	 */
	getListPriceEditForm : function(requestUrl){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var listPriceContainer = this.getListPriceUpdateContainer();
		if(listPriceContainer != false){
			aDeferred.resolve(listPriceContainer);
		}else{
			app.request.post({url: requestUrl}).then(
				function(error,data){
					thisInstance.listPriceUpdateContainer = data;
					aDeferred.resolve(data);
				},
				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			);
		}
		return aDeferred.promise();
	},
	
	/**
	 * function to register event for editing the list price in related list 
	 */
	
	registerEventForEditListPrice : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click', 'a.editListPrice', function(e){
			e.stopPropagation();
			var elem = jQuery(e.currentTarget);
			var requestUrl = elem.data('url');
			thisInstance.getListPriceEditForm(requestUrl).then(
				function(data){
					var relid = elem.data('relatedRecordid');
					var listPrice = elem.data('listPrice');
					var form = jQuery(data);
					form.find('input[name="relid"]').val(relid);
					form.find('input[name="currentPrice"]').val(listPrice);
					thisInstance.showListPriceUpdate(form);
				},
				function(error,err){

				}
			);
		});
	},

	/**
	 * Function to register events
	 */
	registerEvents : function(){
		this._super();
		this.registerEventForEditListPrice();
	}
})