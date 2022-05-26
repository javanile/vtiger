/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_CustomView_Js",{
},{
	contianer : false,

	advanceFilterInstance : false,

	columnListSelect2Element : false,

	columnSelectElement : false,

	reIntialize : function () {
		this.contianer = false;
		this.columnListSelect2Element = false;
		this.advanceFilterInstance = false;
		this.columnSelectElement = false;
	},

	getContainer : function() {
		if(this.container == false) {
			this.container = jQuery('#filterContainer');
		}
		return this.container
	},

	getColumnListSelect2Element : function() {
		if(this.columnListSelect2Element == false){
			this.columnListSelect2Element = jQuery('#s2id_viewColumnsSelect');
		}
		return this.columnListSelect2Element;
	},

	/**
	 * Function to get the view columns selection element
	 * @return : jQuery object of view columns selection element
	 */
	getColumnSelectElement : function() {
		if(this.columnSelectElement == false) {
			this.columnSelectElement = jQuery('#viewColumnsSelect');
		}
		return this.columnSelectElement;
	},

	/**
	 * Function to regiser the event to make the columns list sortable
	 */
	makeColumnListSortable : function() {
		var select2Element = this.getColumnListSelect2Element();
		//TODO : peform the selection operation in context this might break if you have multi select element in advance filter
		//The sorting is only available when Select2 is attached to a hidden input field.
		var chozenChoiceElement = select2Element.find('ul.select2-choices');
		chozenChoiceElement.sortable({
				'containment': chozenChoiceElement,
				start: function() { },
				update: function() {}
			});
	},

	/**
	 * Function which will arrange the chosen element choices in order
	 */
	arrangeSelectChoicesInOrder : function() {
		var contentsContainer = this.getContainer();
		var chosenElement = this.getColumnListSelect2Element();
		var choicesContainer = chosenElement.find('ul.select2-choices');
		var choicesList = choicesContainer.find('li.select2-search-choice');
		var columnListSelectElement = this.getColumnSelectElement();
		var selectedOptions = columnListSelectElement.find('option:selected');
		var selectedOrder = JSON.parse(jQuery('input[name="columnslist"]', contentsContainer).val());

		for(var index=selectedOrder.length ; index > 0 ; index--) {
			var selectedValue = selectedOrder[index-1];
			var value = selectedValue.replace("'", "&#39;");
			var option = selectedOptions.filter('[value="'+value+'"]');
			choicesList.each(function(choiceListIndex,element){
				var liElement = jQuery(element);
				if(liElement.find('div').html() == option.html()){
					choicesContainer.prepend(liElement);
					return false;
				}
			});
		}
	},

	/**
	 * Function which will get the selected columns with order preserved
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function() {
		var columnListSelectElement = this.getColumnSelectElement();
		var select2Element = this.getColumnListSelect2Element();

		var selectedValuesByOrder = new Array();
		var selectedOptions = columnListSelectElement.find('option:selected');

		var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
		orderedSelect2Options.each(function(index,element){
			var chosenOption = jQuery(element);
			selectedOptions.each(function(optionIndex, domOption){
				var option = jQuery(domOption);
				if(option.html() == chosenOption.html()) {
					selectedValuesByOrder.push(option.val());
					return false;
				}
			});
		});
		return selectedValuesByOrder;
	},

	doOperation : function (url) {
		var aDeferred = new jQuery.Deferred();
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(error,data){
			app.helper.hideProgress();
			aDeferred.resolve(data);
		});

		return aDeferred.promise();
	}, 

	showCreateFilter : function(data){
		var self = this;
		self.reIntialize();
		app.helper.loadPageContentOverlay(data).then(function(data){
			data.find('.data').css('height','100%');
			var Options= {
			autoExpandScrollbar: true,
			scrollInertia: 200,
			autoHideScrollbar: true,

			mouseWheel: {
				enable: true,
				preventDefault: true,
				scrollAmount: 50
			}
		};
			app.helper.showVerticalScroll(jQuery('.customview-content '), Options);
			self.advanceFilterInstance = new Vtiger_AdvanceFilter_Js(data.find('.filterConditionsDiv'));
			self.registerFilterCreateEvents();
		});
	},

	saveFilter : function() {
		var aDeferred = jQuery.Deferred();
		var formElement = jQuery("#CustomView");
		var formData = formElement.serializeFormData();
        
		app.helper.showProgress();

		app.request.post({'data':formData}).then(
			function(error,data){
               if(error === null){
				app.helper.hideProgress();
				window.onbeforeunload = null;
				aDeferred.resolve(data);
				}
				else{
					app.helper.hideProgress();
					aDeferred.reject();
					app.helper.showErrorNotification({'message': app.vtranslate('JS_VIEW_ALREADY_EXISTS')});
				}
			}
		);
		return aDeferred.promise();
	},

	saveAndViewFilter : function(){
		this.saveFilter().then(function (response) {
			if (typeof response != "undefined") {
				app.helper.showSuccessNotification({'message':app.vtranslate('JS_LIST_SAVED')});
				var appName = app.getAppName();
				var url = response['listviewurl']+'&app='+appName;
				window.location.href = url;
			} else {
				app.helper.showErrorNotification({message: app.vtranslate('JS_FAILED_TO_SAVE')});
			}
		});
	},

	isAllUsersSelected : function() {
		var memberList = jQuery('#memberList').val();
		return (memberList != null && (memberList.indexOf('All::Users') != -1)) ? true : false
	},

	registerOnlyAllUsersInSharedList : function(){
		var self = this;
		jQuery('#memberList').on('change',function(e){
			var element = jQuery(e.currentTarget);
			if(self.isAllUsersSelected()){
				element.find('option').not('[value="All::Users"]').prop('disabled',true);
				element.select2('val',['All::Users']);
				element.select2('close');
			}else{
				element.find('option').removeProp('disabled');
			}
		});
	},

	/**
	 * Function which will register the select2 elements for columns selection
	 */
	registerSelect2ElementForColumnsSelection : function() {
		var selectElement = this.getColumnSelectElement();
		vtUtils.showSelect2ElementView(selectElement,{maximumSelectionSize: 15});
	},

	registerFilterCreateEvents : function() {
		var self = this;
		self.registerSelect2ElementForColumnsSelection();
		this.arrangeSelectChoicesInOrder();
		this.makeColumnListSortable();
		this.registerToogleShareList();
		this.registerOnlyAllUsersInSharedList();
		var customViewForm = jQuery('#CustomView');

		if(customViewForm.length > 0) {
			customViewForm.vtValidate({
				submitHandler : function(form){
					var form = jQuery(form); 
						  var selectElement = form.find('#viewColumnsSelect'); 
						  var mandatoryFieldsList = JSON.parse(jQuery('#mandatoryFieldsList').val()); 
						  var selectedOptions = selectElement.val(); 
						  var mandatoryFieldsMissing = true; 
						  for(var i=0; i<selectedOptions.length; i++) { 
						if(jQuery.inArray(selectedOptions[i], mandatoryFieldsList) >= 0) { 
							mandatoryFieldsMissing = false; 
								  break; 
						} 
					} 
						  if(mandatoryFieldsMissing){ 
						app.helper.showErrorNotification({message:"Select atleast one mandatory value."}); 
							  return false; 
					} 
					//handled advanced filters saved values.
					var advfilterlist = self.advanceFilterInstance.getValues();
					jQuery('#advfilterlist').val(JSON.stringify(advfilterlist));

					var selectValueElements = self.getColumnSelectElement().select2('data');
					var selectedValues = [];
					for(i=0; i<selectValueElements.length; i++) {
						selectedValues.push(selectValueElements[i].id);
					}
					var selectValues = JSON.stringify(selectedValues);
					jQuery('input[name="columnslist"]', self.getContainer()).val(selectValues);
					var allUsersStatusEle = jQuery('#allUsersStatusValue');
					if(self.isAllUsersSelected() && (jQuery('[data-toogle-members]').is(":checked"))){
						allUsersStatusEle.val(allUsersStatusEle.data('public'));
					}else{
						allUsersStatusEle.val(allUsersStatusEle.data('private'));
					}
					self.saveAndViewFilter();
					return false;
				}
			});
		}
	},

	registerToogleShareList : function() {
		jQuery('[data-toogle-members]').on('change',function(e){
			var element = jQuery(e.currentTarget);
			if(element.is(':checked')){
				jQuery('#memberList').addClass('fadeInx').data('rule-required',true);                
			}
			else {
				jQuery('#memberList').removeClass('fadeInx').data('rule-required',false);
			}
		});
	},

	registerEvents : function() {
		var self = this;
		jQuery(document).on('post.CreateFilter.click',function(e,params){
			self.doOperation(params.url).then(function(data){
				self.showCreateFilter(data);
				var form = jQuery('#CustomView');
				app.helper.registerLeavePageWithoutSubmit(form);
				app.helper.registerModalDismissWithoutSubmit(form);
			})
		});

		jQuery(document).on('post.DeleteFilter.click',function(e,params){
			var target = jQuery(e.target);
			app.helper.showConfirmationBox({'message': app.vtranslate('LBL_LIST_DELETE_CONFIRMATION')}).then(
				function(){
					app.helper.showProgress();
					app.request.post({'url':params.url}).then(function(){
						app.helper.hideProgress();
						target.trigger('post.DeletedFilter');
						// moduleFiltersId is Default All Filter Id
						var moduleFiltersId = jQuery('.module-filters input[name=allCvId]').val();
							jQuery(".listViewFilter ").find('.filterName').each(function(key, ele){
								var filterId = jQuery(ele).data('filter-id');
								if(filterId == moduleFiltersId){
									jQuery(ele).trigger('click');
									return false;
								}
							});
					});
				},
				function(){
				}
			);
		});

		jQuery(document).on('post.ToggleDefault.click',function(e,params){
			var target = jQuery(e.target);
			var url = target.data('url');
			var currentValue = target.data('isDefault');
			var params = {};
			params.url = url;
			params.data = {};
			if(currentValue) {
				params.data.setdefault = '0';
			}else{
				params.data.setdefault = '1';
			}
			app.request.post(params).then(function(error,data){
				target.trigger('post.ToggleDefault.saved',data);
			})
		});
	}
});