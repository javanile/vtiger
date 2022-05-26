/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Settings_Vtiger_List_Js("Settings_Users_List_Js",{
	showTransferOwnershipForm : function() {
		var thisInstance = this;
		app.helper.showProgress();

		var data = {
			'module' : app.getModuleName(),
			'view' : 'TransferOwnership'
		};

		app.request.post({'data':data}).then(function(err, response) {
			app.helper.hideProgress();
			if(err === null) {
				app.helper.showModal(response);
				thisInstance.registerTransferOwnership();
			}else {
				app.helper.showErrorNotification({"message":err.message});
			}
		});
	},

	registerTransferOwnership : function() {
		jQuery('#transferOwner').on('submit',function(e) {
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			app.helper.showProgress();
			app.helper.hideModal();
			var params = form.serializeFormData();

			app.request.post({'data':params}).then(function(err, response) {
				app.helper.hideProgress();
				if(err === null) {
					app.helper.showSuccessNotification({'message': response.message});
					var listInstance = new Settings_Users_List_Js;
					var listParams = listInstance.getListViewParams();
					listInstance.loadListViewRecords(listParams);
				} else {
					app.helper.showErrorNotification({'message':err.message});
				}
			});
		});
	},


	triggerDeleteUser : function(deleteUserUrl, deletePermanently) {
		if(typeof deletePermanently !== 'undefined') {
			deleteUserUrl += '&mode=permanent';
		}

		var checkActiveUsersState = jQuery('#listViewContent').find('.userFilter > button:first');
		if(checkActiveUsersState.hasClass('btn-primary')){
			var message = app.vtranslate('LBL_DELETE_USER_CONFIRMATION');
		} else {
			var message = app.vtranslate('LBL_DELETE_USER_PERMANENT_CONFIRMATION');
		}
		app.helper.showConfirmationBox({'message' : message}).then(function(data) {
				app.request.post({'url':deleteUserUrl}).then(
				function(err, data){
					if(err === null){
						app.helper.showModal(data, {
							cb : function(){
								vtUtils.enableTooltips();
							}
						});
						var form = jQuery("#deleteUser");
						form.on('submit', function(e){
							e.preventDefault();
							Settings_Users_List_Js.deleteUser(form, deletePermanently);
						});
					}else {
						app.helper.showErrorNotification({'message': err.message});
					}
				});
			}
		);
	},

	deleteUser: function (form, deletePermanently){
		var userid = form.find('[name="userid"]').val();
		var transferUserId = form.find('[name="tranfer_owner_id"]').val();
		app.helper.showProgress();
		var data = {
				'module': app.getModuleName(),
				'action' : "DeleteAjax",
				'transfer_user_id' : transferUserId,
				'userid' : userid
			};

		if(typeof deletePermanently !== 'undefined') {
			data['mode'] = 'permanent';
			jQuery('#inactiveUsers').removeClass('btn-primary');
			jQuery('#activeUsers').addClass('btn-primary');
		}else {
			data['permanent'] = jQuery('[name="deleteUserPermanent"]:checked', form).val();
		}

		app.request.post({'data' : data}).then(
			function(err, data) {
				app.helper.hideProgress();
				if(err === null){
					app.helper.showSuccessNotification({'message': data.message});
					app.helper.hideModal();
					var listInstance = new Settings_Users_List_Js;
					var listParams = listInstance.getListViewParams();
					listInstance.loadListViewRecords(listParams);
				}else {
					app.helper.showErrorNotification({'message': err.message});
				}
			}
		);
	},

	triggerLogin : function(recordId){
		var params = {
			data : {
				'module': app.getModuleName(),
				'action' : "ListAjax",
				'mode' : 'signInAsUser',
				'userid' : recordId
			}
		};

		var message = app.vtranslate('LBL_SIGN_IN_AS_USER');
		app.helper.showConfirmationBox({'message' : message}).then(function(data) {
			app.request.post(params).then(
				function(err, data){
					if(err === null){
						window.location.href = data;
					}else {
						app.helper.showErrorNotification({'message': err.message});
					}
			});
		});
	},

	/*
	*Function to restore Inactive User
	*@param userId, event
	*/
	restoreUser : function(userId) {
		app.helper.showConfirmationBox({
			'message' : app.vtranslate('LBL_RESTORE_CONFIRMATION')
		}).then( function() {
			var params = {
				data : {
					'module': app.getModuleName(),
					'action' : "SaveAjax",
					'userid' : userId,
					'mode' : 'restoreUser'
				}
			};
			app.helper.showProgress();

			app.request.post(params).then(
				function(err, response) {
					if(err === null){
						app.helper.showSuccessNotification({'message' : response.message});
						jQuery('#activeUsers').trigger('click');
					} else {
						app.helper.showErrorNotification({message: err.message});
					}
				}
			);
		});
	},

	triggerChangePassword : function (url, module){
		app.request.get({'url' :url}).then(
			function(err, data) {
				if(err === null) {
					app.helper.showModal(data);
					var form = jQuery('#changePassword');

					form.on('submit',function(e){
						e.preventDefault();
					});

					var params = {
						submitHandler: function(form) {
							form = jQuery(form);
							var new_password  = form.find('[name="new_password"]');
							var confirm_password = form.find('[name="confirm_password"]');
							var old_password  = form.find('[name="old_password"]');
							var userid = form.find('[name="userid"]').val();

							if(new_password.val() === confirm_password.val()){
								var params = {
									'data' : {
										'module': app.getModuleName(),
										'action' : "SaveAjax",
										'mode' : 'savePassword',
										'old_password' : old_password.val(),
										'new_password' : new_password.val(),
										'userid' : userid
									}
								};

								app.request.post(params).then(
									function(err, data) {
										if(err == null){
											app.helper.hideModal();
											var successMessage = app.vtranslate(data.message);
											app.helper.showSuccessNotification({"message":successMessage});
										}else{
											app.helper.showErrorNotification({"message":err});	
											return false;
										}
									}
								);
							} else {
								var errorMessage = app.vtranslate('JS_PASSWORD_MISMATCH_ERROR');
								app.helper.showErrorNotification({"message":errorMessage});
								return false;
							}
						}
					};
					form.vtValidate(params);
				}else {
					app.helper.showErrorNotification({'message': err.message});
				}
			}
		);
	},

	triggerChangeUsername: function (url) {
		app.helper.showProgress(app.vtranslate('JS_PLEASE_WAIT'));

		app.request.post({'url' : url}).then(function (err, data) {
			app.helper.hideProgress();
			if(err === null) {
				var callback = function (data) {
					var form = data.find('#changeUsername');

					var params = {
						submitHandler : function(form) {
							var form = jQuery(form);
							var new_password = form.find('[name="new_password"]');
							var confirm_password = form.find('[name="confirm_password"]');
							if (new_password.val() !== confirm_password.val()) {

								var params = {
									position: {
										my: 'bottom left',
										at: 'top left',
										container : form
									},
								};
								vtUtils.showValidationMessage(new_password, app.vtranslate('JS_REENTER_PASSWORDS'), params);
								vtUtils.showValidationMessage(confirm_password, app.vtranslate('JS_REENTER_PASSWORDS'), params);
								return false;
							}else {
								vtUtils.hideValidationMessage(new_password);
								vtUtils.hideValidationMessage(confirm_password);
							}

							Settings_Users_List_Js.changeUserName(form);
						}
					};

					form.vtValidate(params);
				};
				var params = {
					cb : callback
				};
				app.helper.showModal(data, params);
			}
		});
	},

	changeUserName: function (form) {
		var newUsername = form.find('[name="new_username"]');
		var new_password = form.find('[name="new_password"]');
		var confirm_password = form.find('[name="confirm_password"]');
		var userid = form.find('[name="userid"]');

		app.helper.showProgress(app.vtranslate('JS_PLEASE_WAIT'));

		var params = {
			module: app.getModuleName(),
			action: 'SaveAjax',
			mode: 'changeUsername',
			newUsername: newUsername.val(),
			newPassword: new_password.val(),
			confirmPassword: confirm_password.val(),
			userid: userid.val()
		};
		vtUtils.hideValidationMessage(newUsername);

		app.request.post({'data' : params}).then(function (err, data) {
			app.helper.hideProgress();

			if(err === null) {
				app.helper.showSuccessNotification({'message' : app.vtranslate(data)});
				app.helper.hideModal();
				location.reload();
			}else {
				var params = {
					position: {
						my: 'bottom left',
						at: 'top left',
						container : form
					},
				};
				vtUtils.showValidationMessage(newUsername, app.vtranslate(err.message), params);
				return false;
			}
		});
	}
},{

	/*
	 *Function to filter Active and Inactive users from Users List View
	 */
	registerUserStatusToggleEvent : function() {
		jQuery('#activeUsers, #inactiveUsers').on('click', function(e) {
			var currentEle = jQuery(e.currentTarget);
			//If it is already selected then you dont need to load again
			if(currentEle.hasClass('btn-primary')) {
				return;
			}

			app.helper.showProgress();
			if(currentEle.attr('id') === 'activeUsers') {
				jQuery('#inactiveUsers').removeClass('btn-primary');
			}else {
				jQuery('#activeUsers').removeClass('btn-primary');
			}
			currentEle.addClass('btn-primary');

			var listInstance = new Settings_Users_List_Js;
			var listParams = listInstance.getListViewParams();
			listParams['search_value'] = currentEle.data('searchvalue');
			//To clear the search params while switching between active and inactive users 
			listParams.search_params = {};
			listInstance.loadListViewRecords(listParams);
		});
	},

	/**
	 * Function to get Page Jump Params
	 */
	getPageJumpParams : function(){
		var params = this.getDefaultParams();
		params['view'] = "ListAjax";
		params['mode'] = "getPageCount";
		params['search_key'] = 'status',
		params['operator'] = 'e';
		params['search_value'] = jQuery('.userFilter').find('button.btn-primary').data('searchvalue');
		return params;
	},

	getListViewParams : function() {
		var self = this;
		var listParams = {
			'module' : app.getModuleName(),
			'view' : 'List',
			'parent' : app.getParentModuleName(),
			'search_key' : 'status',
			'operator' : 'e',
			'search_value' : self.getListViewContainer().find('.userFilter').find('button.btn-primary').data('searchvalue')
		};
		return listParams;
	},

	loadListViewRecords : function(urlParams) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var defParams = thisInstance.getDefaultParams();
		if(typeof urlParams == "undefined") {
			urlParams = {};
		}

		if(typeof urlParams.search_params == "undefined") {
			urlParams.search_params = JSON.stringify(this.getListSearchParams(false));
		}

		urlParams = jQuery.extend(defParams, urlParams);
		var usersListParams = thisInstance.getListViewParams();
		jQuery.extend(usersListParams, urlParams);
		app.helper.showProgress();

		app.request.get({data:usersListParams}).then(function(err, res){
			aDeferred.resolve(res);
			var container = thisInstance.getListViewContainer();
			container.find('#listview-actions div.list-content').html(res);
			thisInstance.updatePagination();
			app.event.trigger('post.listViewFilter.click', container.find('.searchRow'));
			thisInstance.registerDynamicDropdownPosition('table-actions', 'listview-table');
			app.helper.hideProgress();
		});
		return aDeferred.promise();
	},

	getListSearchParams : function(includeStarFilters) {
		if(typeof includeStarFilters == "undefined") {
			includeStarFilters = true;
		}
		var listViewPageDiv = this.getListViewContainer();
		var listViewTable = listViewPageDiv.find('.searchRow');
		var searchParams = new Array();
		var currentSearchParams = new Array();
		if(listViewPageDiv.find('#currentSearchParams').val()) {
			currentSearchParams = JSON.parse(listViewPageDiv.find('#currentSearchParams').val());
		}
		var listSearchParams = new Array();
		listSearchParams[0] = new Array();


		listViewTable.find('.listSearchContributor').each(function(index,domElement){
			var searchInfo = new Array();
			var searchContributorElement = jQuery(domElement);
			var fieldName = searchContributorElement.attr('name');
			var fieldInfo = uimeta.field.get(fieldName);

			/**
			 *  If we have any related record fields in the list, then list search will not work.
			 *  Because, uimeta will only hold field info of current module not all related modules
			 */
			if(typeof fieldInfo == 'undefined'){
				fieldInfo = searchContributorElement.data("fieldinfo");
			}

			if(fieldName in currentSearchParams) {
				delete currentSearchParams[fieldName];
				if(fieldName == 'first_name') {
					//To add both lastname and email for search , since all those three are combined to show in list view 
					var combinedFields = new Array('last_name','email1');

					for (var index in combinedFields) {
						delete currentSearchParams[combinedFields[index]];
					}
				}
			}

			if('starred' in currentSearchParams) {
				delete currentSearchParams['starred'];
			}

			var searchValue = searchContributorElement.val();

			if(typeof searchValue == "object") {
				if(searchValue == null) {
					searchValue = "";
				}else{
					searchValue = searchValue.join(',');
				}
			}
			//for Account owner field no value will be emtpty string so we are avoiding trimming
			if(fieldName != 'is_owner') {
				searchValue = searchValue.trim();
			}
			if(searchValue.length <=0 ) {
				//continue
				return true;
			}
			var searchOperator = 'c';
			if(fieldInfo.type == "date" || fieldInfo.type == "datetime") {
				searchOperator = 'bw';
			}else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
				|| fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
				fieldInfo.type == "picklist") {
				searchOperator = 'e';
			}
			var storedOperator = searchContributorElement.parent().parent().find('.operatorValue').val();
			if(storedOperator) {
				searchOperator = storedOperator;
				storedOperator = false;
			}
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);


			if(fieldName == 'first_name') {
				listSearchParams[1] = new Array();
				listSearchParams[1].push(searchInfo);
				//To add both lastname and email for search , since all those three are combined to show in list view 
				var combinedFields = new Array('last_name','email1');

				for (var index in combinedFields) {
					var searchInfo = new Array();
					searchInfo.push(combinedFields[index]);
					searchInfo.push(searchOperator);
					searchInfo.push(searchValue);

					listSearchParams[1].push(searchInfo);
				}

			}else{
				searchParams.push(searchInfo);
			}
		});
		for(var i in currentSearchParams) {
			var fieldName = currentSearchParams[i]['fieldName'];
			var searchValue = currentSearchParams[i]['searchValue'];
			var searchOperator = currentSearchParams[i]['comparator'];
			if(fieldName== null || fieldName.length <=0 ){
				continue;
			}
			var searchInfo = new Array();
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			searchParams.push(searchInfo);
		}
		if(searchParams.length > 0) {
			listSearchParams[0] = searchParams;
		}
		if(includeStarFilters) {
			listSearchParams = this.addStarSearchParams(listSearchParams);
		}

		return listSearchParams;
	},

	registerEvents : function() {
		this._super();
		this.registerUserStatusToggleEvent();
		this.registerListViewSearch();
		this.registerPostListLoadListener();
		this.registerListViewSort();
		this.registerRemoveListViewSort();
		this.registerDynamicDropdownPosition('table-actions', 'listview-table');
		this.registerEditLink();
	}
});
