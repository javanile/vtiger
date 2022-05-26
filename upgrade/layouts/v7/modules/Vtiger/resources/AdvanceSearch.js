/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_BasicSearch_Js("Vtiger_AdvanceSearch_Js",{

	//cache will store the search data
	cache : {}

},{
	//container which will store the search elements
	elementContainer : false,
	//instance which represents advance filter
	advanceFilter : false,

	//states whether the validation is registred for filter elements
	filterValidationRegistered : false,

	//contains the filter form element
	filterForm : false,

	/**
	 * Function which will give the container
	 */
	getContainer : function() {
		return this.elementContainer;
	},

	/**
	 *Function which is used to set the continaer
	 *@params : container - element which represent the container
	 *@return current instance
	 */
	setContainer : function(container) {
		this.elementContainer = container;
		return this;
	},

	getFilterForm : function() {
		return jQuery('form[name="advanceFilterForm"]',this.getContainer());
	},
    
    isSearchShown : function() {
        return jQuery('#advanceSearchHolder').hasClass('slideDown');
    },
    
    hideSearch : function() {
        jQuery('#advanceSearchHolder').removeClass('slideDown');
    },
    
    isSearchHidden : function() {
       var advanceSearchHolder = jQuery('#advanceSearchHolder');
       return (advanceSearchHolder.children().length > 0 && (!advanceSearchHolder.hasClass('slideDown'))) ? true : false;
    },
    
    showSearch : function() {
         var advanceSearchHolder = jQuery('#advanceSearchHolder');
         advanceSearchHolder.addClass('slideDown');
    },
    

	/**
	 * Function used to get the advance search ui
	 * @return : deferred promise
	 */
	getAdvanceSearch : function() {
		var aDeferred = jQuery.Deferred();
		var moduleName = app.getModuleName();
		var searchModule = this.getSearchModule();

		//Exists in the cache
		if(searchModule in Vtiger_AdvanceSearch_Js.cache) {
			aDeferred.resolve(Vtiger_AdvanceSearch_Js.cache[searchModule]);
			return aDeferred.promise();
		}
        
        //if you are in settings then module should be vtiger
        if(app.getParentModuleName().length > 0) {
            moduleName = 'Vtiger';
        }
        
        var searchableModulesParams = {
			"module":moduleName,
			"view"	: "BasicAjax",
			"mode"	: "showAdvancedSearch",
			"source_module": searchModule
        };
        
        app.helper.showProgress();
		app.request.post({data:searchableModulesParams}).then(
			function(err, data){
                app.helper.hideProgress();
				//add to cache
				Vtiger_AdvanceSearch_Js.cache[searchModule] = data;
				aDeferred.resolve(data);
			},
			function(error,err){
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},
    
    showAdvanceSearch : function (data) {
        var aDeferred = jQuery.Deferred();
        if(jQuery('#advanceSearchHolder').length >0) {
            jQuery('#advanceSearchHolder').removeClass('slideDown');
            data = jQuery(data).find('#advanceSearchHolder').html();
            jQuery('#advanceSearchHolder').html(data).addClass('slideDown');
        }else{
            app.helper.loadPageOverlay(data).then(function(container){
                jQuery('#advanceSearchHolder').addClass('slideDown');
            });
        }
        aDeferred.resolve();
        return aDeferred.promise();
    },

	/**
	 * Function which intializes search
	 */
	initiateSearch : function() {
        var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		this.getAdvanceSearch().then(
			function(data){
                thisInstance.showAdvanceSearch(data).then(function(){
                    thisInstance.setContainer(jQuery('#advanceSearchContainer'));
                    vtUtils.showSelect2ElementView(thisInstance.getContainer().find('select.select2'));
                    thisInstance.registerEvents();
                    thisInstance.advanceFilter = new Vtiger_SearchAdvanceFilter_Js(jQuery('.filterContainer'));
                    app.helper.showVerticalScroll(jQuery('#searchResults-container'),{'setHeight' : app.helper.getViewHeight()});
                    aDeferred.resolve();
                })
                
			},
			function(error) {
                aDeferred.reject();
			}
		)
        return aDeferred.promise();
	},
    
    getNameFields : function() {
        var form = this.getFilterForm();
        return form.find('[name="labelFields"]').data('value');
    },
    
    selectBasicSearchValue : function() {
      var value = jQuery('.keyword-input').val();
      if(value.length > 0 ) {
          var form = this.getFilterForm();
          var labelFieldList = this.getNameFields();
          if(typeof labelFieldList == 'undefined' || labelFieldList.length == 0) {
              return;
          }
          var anyConditionContainer = form.find('.anyConditionContainer');
          for(var index in labelFieldList){
            var labelFieldName = labelFieldList[index];
            if(index !=0 ) {
                //By default one condition exits , only if you have multiple label fields you have add one more condition
                anyConditionContainer.find('.addCondition').find('button').trigger('click');
            }
            var conditionRow = anyConditionContainer.find('.conditionList').find('.conditionRow:last');
            var fieldSelectElemnt = conditionRow.find('select[name="columnname"]');
            fieldSelectElemnt.find('option[data-field-name="'+ labelFieldName +'"]').attr('selected','selected');
            fieldSelectElemnt.trigger('change').trigger('liszt:updated');

            var comparatorSelectElemnt = conditionRow.find('select[name="comparator"]');
            //select the contains value
            comparatorSelectElemnt.find('option[value="c"]').attr('selected','selected');
            comparatorSelectElemnt.trigger('liszt:updated');

            var valueElement = conditionRow.find('[name="'+labelFieldName+'"]');
            valueElement.val(value);
          }
          
      }
    },

	/**
	 * Function which invokes search
	 */
	search : function() {
		var conditionValues = this.advanceFilter.getValues();
		var module = this.getSearchModule();

		var params = {};
		params.module = module;
        var searchParams = new Array();
        for(var index in conditionValues) {
            var conditionSpecificValues = conditionValues[index]['columns'];
            var conditionSpecificParams = new Array();
            for(var i in conditionSpecificValues) {
                var params1 = new Array();
                var fieldName = conditionSpecificValues[i]['columnname'].split(":")[2];
                params1.push(fieldName);
                params1.push(conditionSpecificValues[i]['comparator']);
                params1.push(conditionSpecificValues[i]['value']);
                conditionSpecificParams.push(params1);
            }
            searchParams.push(conditionSpecificParams);
        }
		params.search_params = JSON.stringify(searchParams);
        params.nolistcache = 1;
		return this._search(params);
	},

	/**
	 * Function which shows search results in proper manner
	 * @params : data to be shown
	 */
	showSearchResults : function(data){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var postLoad = function(data) {
			var blockMsg = jQuery(data).closest('.blockMsg');
			app.showScrollBar(jQuery(data).find('.contents'));
			aDeferred.resolve(data);
		}

		var unblockcd = function(){
			thisInstance.getContainer().remove();
		}

		var html = '<div class="row-fluid">'+
						'<span class="span4 searchHolder" style="width:280px;"></span>'+
						'<span class="span8 filterHolder  marginLeftZero hide"></span>'+
					'</div>';
		var jQhtml = jQuery(html);
		jQuery('.searchHolder',jQhtml).html(data);

		data = jQhtml;

		var params = {};
		params.data = data;
		params.cb = postLoad;
		params.css = {'width':'20%','text-align':'left'};
		params.overlayCss = {'opacity':'0.2'};
		params.unblockcb = unblockcd;
		app.showModalWindow(params);

		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter
	 */
	saveFilter : function(params) {
		var aDeferred = jQuery.Deferred();
		params.source_module = this.getSearchModule();
		params.status = 1;
		params.advfilterlist = JSON.stringify(this.advanceFilter.getValues(false));

		params.module = 'CustomView';
		params.action = 'Save';

		app.request.post({data:params}).then(function(error,data){
			aDeferred.resolve(data);
		})
		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter and show the list view of new custom view
	 */
	saveAndViewFilter : function(params){
        app.helper.showProgress();
		this.saveFilter(params).then(
			function(response){
                app.helper.hideProgress();
				var url = response['listviewurl'];
				window.location.href=url;
			},
			function(error) {

			}
		);
	},
    
    initiateListInstance : function(container)   {
        var listInstance = new Vtiger_AdvanceSearchList_Js();
        listInstance.setListViewContainer(container.find('.moduleResults-container')).setModuleName(this.getSearchModule());
        listInstance.registerEvents();
    },

	

	/**
	 * Function which will perform search and other operaions
	 */
	performSearch : function() {
        var self = this;
		this.search().then(function(data){
            if(jQuery('#searchResults-container').find('.searchResults').length > 0)  {
                jQuery('#searchResults-container').find('.searchResults').html(data);
            }else{
                jQuery('#advanceSearchContainer').append(data);
            }
            self.initiateListInstance(jQuery('.searchResults'));
            self.registerShowFiler();
            self.hideSearch();
		});
	},

	/**
	 * Function which will perform the validation for the advance filter fields
	 * @return : deferred promise - resolves if validation succeded if not failure
	 */
	performValidation : function() {
		var thisInstance = this;
		this.formValidationDeferred = jQuery.Deferred();
        thisInstance.formValidationDeferred.resolve();
		
		var controlForm = this.getFilterForm();
		var validationDone = function(form, status){
			if(status) {
				thisInstance.formValidationDeferred.resolve();
			}else{
				thisInstance.formValidationDeferred.reject();
			}
		}
		//To perform validation registration only once
		if(!this.filterValidationRegistered){
			this.filterValidationRegistered = true;
			controlForm.validationEngine({
				'onValidationComplete' : validationDone
			});
		}
		//This will trigger the validation
		controlForm.submit();
		return this.formValidationDeferred.promise();
	},
    
    advanceSearchTriggerIntiatorHandler  : function () {
        var self = this;
        if(this.isSearchShown()){
            this.hideSearch();
            return;
        }
        if(this.isSearchHidden()) {
            this.showSearch();
            return;
        }
        this.initiateSearch().then(function() {
            self.selectBasicSearchValue();
        });
    },
    
    /**
	 * Function which will register the show filer invocation
	 */
	registerShowFiler : function() {
		var thisInstance = this;
		jQuery('#showFilter').on('click',function(e){
			thisInstance.showAdvanceSearch();
		});
	},

	/**
	 * Function which will register events
	 */
	registerEvents : function() {
		var thisInstance = this;
		var container = this.getContainer();
        
		container.on('change','#searchModuleList', function(e){
			var selectElement = jQuery(e.currentTarget);
			var selectedModuleName = selectElement.val();

			thisInstance.setSearchModule(selectedModuleName);

			thisInstance.initiateSearch().then(function(){
                thisInstance.selectBasicSearchValue();
            });
		});

		jQuery('#advanceSearchButton').on('click', function(e){
			var searchModule = thisInstance.getSearchModule();
               //If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}
			thisInstance.performValidation().then(
				function(){
					 thisInstance.performSearch();
				},
				function(){

				}
			);
		});

		jQuery('#advanceIntiateSave').on('click', function(e){
			var currentElement = jQuery(e.currentTarget);
			currentElement.addClass('hide');
			var actionsContainer = currentElement.closest('.actions');
			jQuery('input[name="viewname"]',actionsContainer).removeClass('hide').addClass('slideRight');
			jQuery('#advanceSave').removeClass('hide');
		});

		jQuery('#advanceSave').on('click',function(e){
			var actionsContainer = jQuery(e.currentTarget).closest('.actions');
			var filterNameField = jQuery('input[name="viewname"]',actionsContainer);
			var value = filterNameField.val();
			if(value.length <= 0) {
                vtUtils.showValidationMessage(filterNameField, app.vtranslate('JS_REQUIRED_FIELD'), {
                    position: {
                        my: 'bottom left',
                        at: 'top left',
                        container: container.closest('.data')
                    }
                });
				return;
			}

			var searchModule = thisInstance.getSearchModule();
			//If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList'))
						.validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}

			thisInstance.performValidation().then(function(){
				var params = {};
				params.viewname = value;
				thisInstance.saveAndViewFilter(params);
			});
		});

		//DO nothing on submit of filter form
		this.getFilterForm().on('submit',function(e){
			e.preventDefault();
		})

		//To set the search module with the currently selected values.
		this.setSearchModule(jQuery('#searchModuleList').val());
	}
})
