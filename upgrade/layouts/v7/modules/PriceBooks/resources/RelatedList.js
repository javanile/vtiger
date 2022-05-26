/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("PriceBooks_RelatedList_Js",{},{
	
	/**
	 * Function to handle the popup show
	 */
	showSelectRelationPopup : function(){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.showPopup(this.getPopupParams(), function(responseString){
				var responseData = JSON.parse(responseString);
				thisInstance.addRelations(responseData).then(
					function(error, data){
                        thisInstance.loadRelatedList();
					}
				);
			}
		);
		return aDeferred.promise();
	},
	/**
	 * Function to get params for show event invocation
	 */
	getPopupParams : function(){
		var relatedModuleName = this.relatedModulename;
		if (jQuery.inArray(relatedModuleName, ["Products", "Services", "PriceBooks"]) == -1) {
			return this._super();
		}
		var parameters = {
			'module' : relatedModuleName,
			'src_module' :this.parentModuleName ,
			'src_record' : this.parentRecordId,
			 'view' : "PriceBookProductPopup",
			 'src_field' : 'priceBookRelatedList',
			'multi_select' : true,
            'relationId' : this.getSelectedTabElement().data('relationId')
		}
		if(this.parentModuleName === relatedModuleName){
			parameters['view']= "Popup";
		}
		return parameters;
	},
	/**
	 * Function to handle the adding relations between parent and child window
	 */
	addRelations : function(idList, added){
        if (typeof(added)==='undefined') added = true;
        var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var sourceRecordId = this.parentRecordId;
		var sourceModuleName = this.parentModuleName;
		var relatedModuleName = this.relatedModulename;
        var relationId = this.getSelectedTabElement().data('relationId');

		var params = {};
		params['mode'] = "addListPrice";
		params['module'] = sourceModuleName;
		params['action'] = 'RelationAjax';
		
		params['related_module'] = relatedModuleName;
		params['src_record'] = sourceRecordId;
		params['relinfo'] = JSON.stringify(idList);
		if(jQuery.inArray(relatedModuleName, ["Products", "Services"]) == -1){
			var relatedRecords = new Array();
			jQuery.each(idList, function(id) {
				relatedRecords.push(id);
			});
			params['related_record_list'] = JSON.stringify(relatedRecords);
			params['relationId'] = relationId;
			params['mode'] = "addRelation";
		}
		app.helper.showProgress();
		app.request.post({data: params}).then(
            function(responseData){
                var relatedIdList = Object.keys(idList);
                if(added) {
                    thisInstance.updateRelatedRecordsCount(relationId,relatedIdList,true);
                }
                app.helper.hideProgress();
                aDeferred.resolve(responseData);
            }
        );
		return aDeferred.promise();
	}
})