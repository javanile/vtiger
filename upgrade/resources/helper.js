/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_Helper_Js",{

	checkServerConfigResponseCache : '',
	/*
	 * Function to get the instance of Mass edit of Email
	 */
	getEmailMassEditInstance : function(){

		var className = 'Emails_MassEdit_Js';
		var emailMassEditInstance = new window[className]();
		return emailMassEditInstance
	},
    /*
	 * function to check server Configuration
	 * returns boolean true or false
	 */

	checkServerConfig : function(module){
		var aDeferred = jQuery.Deferred();
		var actionParams = {
			"action": 'CheckServerInfo',
			'module' : module
		};
		AppConnector.request(actionParams).then(
			function(data) {
				var state = false;
				if(data.result){
					state = true;
				} else {
					state = false;
				}
				aDeferred.resolve(state);
			}
		);
		return aDeferred.promise();
	},
	/*
	 * Function to get Date Instance
	 * @params date---this is the field value
	 * @params dateFormat---user date format
	 * @return date object
	 */

	getDateInstance : function(dateTime,dateFormat, fieldType){
		var dateTimeComponents = dateTime.split(" ");
		var dateComponent = dateTimeComponents[0];
		var timeComponent = dateTimeComponents[1];
        var seconds = '00';

		var splittedDate = dateComponent.split("-");
		if (splittedDate.length > 3) {
            var errorMsg = app.vtranslate("JS_INVALID_DATE");
            throw errorMsg;
        }
		var splittedDateFormat = dateFormat.split("-");
		var year = splittedDate[splittedDateFormat.indexOf("yyyy")];
		var month = splittedDate[splittedDateFormat.indexOf("mm")];
		var date = splittedDate[splittedDateFormat.indexOf("dd")];
        var dateInstance = Date.parse(year+"-"+month+"-"+date);
		if((dateInstance == null) || (year.length != 4) || (month.length > 2) || (date.length > 2)){
			var errorMsg = app.vtranslate("JS_INVALID_DATE");
			throw errorMsg;
		}
                
		if (fieldType == 'date' && typeof timeComponent != 'undefined') {
            var errorMsg = app.vtranslate("JS_INVALID_DATE");
            throw errorMsg;
        }

		//Before creating date object time is set to 00
		//because as while calculating date object it depends system timezone
		if(typeof timeComponent == "undefined"){
			timeComponent = '00:00:00';
		}

        var timeSections = timeComponent.split(':');
        if(typeof timeSections[2] != 'undefined'){
            seconds = timeSections[2];
        }

        //Am/Pm component exits
		if(typeof dateTimeComponents[2] != 'undefined') {
			timeComponent += ' ' + dateTimeComponents[2];
            if(dateTimeComponents[2].toLowerCase() == 'pm' && timeSections[0] != '12') {
                timeSections[0] = parseInt(timeSections[0], 10) + 12;
            }

            if(dateTimeComponents[2].toLowerCase() == 'am' && timeSections[0] == '12') {
                timeSections[0] = '00';
            }
		}

        month = month-1;
		var dateInstance = new Date(year,month,date,timeSections[0],timeSections[1],seconds);
        return dateInstance;
	},
	requestToShowComposeEmailForm : function(selectedId,fieldname,fieldmodule){
		var selectedFields = new Array();
		selectedFields.push(fieldname);
		var selectedIds =  new Array();
		selectedIds.push(selectedId);
		var params = {
			'module' : 'Emails',
            'fieldModule' : fieldmodule,
			'selectedFields[]' : selectedFields,
			'selected_ids[]' : selectedIds,
			'view' : 'ComposeEmail'
		}
		var emailsMassEditInstance = Vtiger_Helper_Js.getEmailMassEditInstance();
		emailsMassEditInstance.showComposeEmailForm(params);
	},

	/*
	 * Function to get the compose email popup
	 */
	getInternalMailer  : function(selectedId,fieldname,fieldmodule){
		var module = 'Emails';
		var cacheResponse = Vtiger_Helper_Js.checkServerConfigResponseCache;
		var  checkServerConfigPostOperations = function (data) {
			if(data == true){
				Vtiger_Helper_Js.requestToShowComposeEmailForm(selectedId,fieldname,fieldmodule);
			} else {
				alert(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
			}
		}
		if(cacheResponse === ''){
			var checkServerConfig = Vtiger_Helper_Js.checkServerConfig(module);
			checkServerConfig.then(function(data){
				Vtiger_Helper_Js.checkServerConfigResponseCache = data;
				checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
			});
		} else {
			checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
		}
	},

	/*
	 * Function to show the confirmation messagebox
	 */
	showConfirmationBox : function(data){
		var aDeferred = jQuery.Deferred();
		var bootBoxModal = bootbox.confirm(data['message'],app.vtranslate('LBL_NO'),app.vtranslate('LBL_YES'), function(result) {
			if(result){
				aDeferred.resolve();
			} else{
				aDeferred.reject();
			}
		});

        bootBoxModal.on('hidden',function(e){
            //In Case of multiple modal. like mass edit and quick create, if bootbox is shown and hidden , it will remove
            // modal open
            if(jQuery('#globalmodal').length > 0) {
                // Mimic bootstrap modal action body state change
                jQuery('body').addClass('modal-open');
            }
        })
		return aDeferred.promise();
	},
    
    /*
	 * Function to show the custom dialogs
	 */
	showCustomDialogBox: function (data) {
        //options are array of objects with label,button class and callback properties
		bootbox.dialog(data['message'], data['options']);
    },

	/*
	 * Function to check Duplication of Account Name
	 * returns boolean true or false
	 */

	checkDuplicateName : function(details) {
		var accountName = details.accountName;
		var recordId = details.recordId;
		var aDeferred = jQuery.Deferred();
		var moduleName = details.moduleName;
		if(typeof moduleName == "undefined"){
			moduleName = app.getModuleName();
		}
		var params = {
		'module' : moduleName,
		'action' : "CheckDuplicate",
		'accountname' : accountName,
		'record' : recordId
		}
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				var result = response['success'];
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	showMessage : function(params){
		if(typeof params.type == "undefined"){
			params.type = 'info';
		}
		params.animation = "show";
		params.title = app.vtranslate('JS_MESSAGE'),
		Vtiger_Helper_Js.showPnotify(params);
	},

	/*
	 * Function to show pnotify message
	 */
	showPnotify : function(customParams) {

		var userParams = customParams;
		if(typeof customParams == 'string') {
			var userParams = {};
			userParams.text = customParams;
		}

		var params = {
			sticker: false,
			delay: '3000',
			type: 'error',
			pnotify_history: false
		}

		if(typeof userParams != 'undefined'){
			var params = jQuery.extend(params,userParams);
		}
		return jQuery.pnotify(params);
	},
    
    /* 
    * Function to add clickoutside event on the element - By using outside events plugin 
    * @params element---On which element you want to apply the click outside event 
    * @params callbackFunction---This function will contain the actions triggered after clickoutside event 
    */ 
    addClickOutSideEvent : function(element, callbackFunction) { 
        element.one('clickoutside',callbackFunction); 
    },
	
	/*
	 * Function to show horizontal top scroll bar 
	 */
	showHorizontalTopScrollBar : function() {
		var container = jQuery('.contentsDiv');
		var topScroll = jQuery('.contents-topscroll',container);
		var bottomScroll = jQuery('.contents-bottomscroll', container);
		
		jQuery('.topscroll-div', container).css('width', jQuery('.bottomscroll-div', container).outerWidth());
		jQuery('.bottomscroll-div', container).css('width', jQuery('.topscroll-div', container).outerWidth());
		
		topScroll.scroll(function(){
			bottomScroll.scrollLeft(topScroll.scrollLeft());
		});
		
		bottomScroll.scroll(function(){
			topScroll.scrollLeft(bottomScroll.scrollLeft());
		});
	},
	
	/*
	 * Function to confirmation modal for recurring events updation and deletion 
	 */
	showConfirmationForRepeatEvents: function (customParams) {
		var aDeferred = jQuery.Deferred();
		var params = {
			module: 'Calendar',
			view: 'RecurringDeleteCheck'
		}
		jQuery.extend(params, customParams);
		var postData = {};
		AppConnector.request(params).then(function (data) {
			var callBackFunction = function (modalContainer) {
				modalContainer.on('click', '.onlyThisEvent', function () {
					postData['recurringEditMode'] = 'current';
					app.hideModalWindow();
					aDeferred.resolve(postData);
				});
				modalContainer.on('click', '.futureEvents', function () {
					postData['recurringEditMode'] = 'future';
					app.hideModalWindow();
					aDeferred.resolve(postData);
				});
				modalContainer.on('click', '.allEvents', function () {
					postData['recurringEditMode'] = 'all';
					app.hideModalWindow();
					aDeferred.resolve(postData);
				});
			}
			app.showModalWindow(data, function (data) {
				if (typeof callBackFunction == 'function') {
					callBackFunction(data);
				}
			})	
		});
		return aDeferred.promise();
	},
    
	rand: function () {
        return Math.floor((Math.random() * 1000) + 1);
	},

	mergeObjects: function (arrayOfObjs) {
		var mergedObj = {};
		jQuery.each(arrayOfObjs, function (i, kv) {
			if (mergedObj.hasOwnProperty(kv.name)) {
				mergedObj[kv.name] = jQuery.makeArray(mergedObj[kv.name]);
				mergedObj[kv.name].push(kv.value);
			}
			else {
				mergedObj[kv.name] = kv.value;
			}
		});
		return mergedObj;
	}

},{});