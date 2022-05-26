/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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
		return emailMassEditInstance;
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
			var helperInstance = new Vtiger_Helper_Js;
			var checkServerConfig = helperInstance.checkServerConfig(module);
			checkServerConfig.then(function(data){
				Vtiger_Helper_Js.checkServerConfigResponseCache = data;
				checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
			});
		} else {
			checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
		}
	},
},{
    init : function() {
        this._initNotificationDefaults();
		this.registerPostOverLayPageContentHideEvent();
    },

	registerPostOverLayPageContentHideEvent : function() {
		var self = this;
		jQuery('#overlayPageContent').on('hidden.bs.modal', function() {
			self.hidePageContentOverlay().then(function() {
				app.event.trigger('post.overlayPageContent.hide', jQuery('#overlayPageContent'));
			});
		});
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
        if(splittedDate.length > 3) {
            var errorMsg = app.vtranslate("JS_INVALID_DATE");
            throw errorMsg;
        }
		var splittedDateFormat = dateFormat.split("-");
		var year = splittedDate[splittedDateFormat.indexOf("yyyy")];
		var month = splittedDate[splittedDateFormat.indexOf("mm")];
		var date = splittedDate[splittedDateFormat.indexOf("dd")];
        var dateInstance = Date.parse(year+"-"+month+"-"+date);
		if(isNaN(dateInstance) || (dateInstance == null) || (year.length != 4) || (month.length > 2) || (date.length > 2)){
			var errorMsg = app.vtranslate("JS_INVALID_DATE");
			throw errorMsg;
		}

        if(fieldType == 'date' && typeof timeComponent != 'undefined') {
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
		app.request.post({data:actionParams}).then(
			function(err,data) {
				var state = false;
				if(err === null){
					state = true;
				} else {
					state = false;
				}
				aDeferred.resolve(state);
			}
		);
		return aDeferred.promise();
	},

    showConfirmationBox: function(data) {
        var aDeferred = jQuery.Deferred();
                var buttonsInfo, title;
                if((typeof data.buttons == "object") && (Object.keys(data.buttons).length > 0)){
                    buttonsInfo = data.buttons;
                }else{
                    buttonsInfo = {
				cancel: {
					label: 'No',
					className : 'btn-default confirm-box-btn-pad pull-right'
				},
				confirm: {
					label: 'Yes',
					className : 'confirm-box-ok confirm-box-btn-pad btn-primary'
				}
                                }
                }
                if(typeof data.title != "undefined"){
                    title = data.title;
                }else{
                    title = '';
                }
		bootbox.confirm({
            title : title,
			buttons: buttonsInfo,
			message: data['message'],
            htmlSupportEnable: data.hasOwnProperty('htmlSupportEnable') ? data['htmlSupportEnable'] : true,
			callback: function(result) {
				if (result) {
					aDeferred.resolve();
				} else {
					aDeferred.reject();
				}
			}
		});
		
        return aDeferred.promise();
    },
    showAlertBox: function(data, cb) {
        var message = data['message'];
        if (typeof cb == 'function') {
            bootbox.alert(message, cb);
        }
        else {
            var aDeferred = jQuery.Deferred();
            bootbox.alert(message, function(result) {
                if (result) {
                    aDeferred.resolve();
                } else {
                    aDeferred.reject();
                }
            });
            return aDeferred.promise();
        }
    },
    
    defaultScrollParams : function(){
       return  {
            height: '',
            railVisible:false,
            alwaysVisible:true
        };
    },

    showScroll : function(container, params) {
        if(typeof params == "undefined") {
            params = {};
        }
        var params = jQuery.extend(this.defaultScrollParams(), params);
        container.slimScroll(params);
    },
    
    showHorizontalScroll : function(element, options) {
		if(typeof options == 'undefined') {
			options = {};
		}
		var params = {
			horizontalScroll: true,
			theme: "dark-thick",
			advanced: {
				autoExpandHorizontalScroll:true
			}
		}
		if(typeof options != 'undefined'){
			var params = jQuery.extend(params,options);
		}
		return element.mCustomScrollbar(params);
	},
    showVerticalScroll : function(element, options) {
		if(typeof options == 'undefined') {
			options = {};
		}
		var params = {
			theme: "dark-thick",
			advanced: {
				autoExpandHorizontalScroll:true,
                setTop: 0
			}
		};
		if(typeof options != 'undefined'){
			var params = jQuery.extend(params,options);
		}
		return element.mCustomScrollbar(params);
	},
        
    showBothAxisScroll : function(element, options){
        if(typeof options == 'undefined') {
            options = {};
        }
        
        var params = {
            scrollbarPosition : 'inside',
            alwaysShowScrollbar : 2,
            theme: "dark-thick",
            axis:"xy",
            live : true
        };
        if(typeof options != 'undefined'){
            var params = jQuery.extend(params,options);
        }
        return element.mCustomScrollbar(params);
    },
    defaultModalParams : function() {
        return  {
            backdrop:true,
            show:true,
            keyboard: false
        };
    },

    loadPageOverlay : function(data, params) {
        var overlayPage = jQuery('#overlayPage');
        var aDeferred = new jQuery.Deferred();
        if(typeof params == "undefined") {
            params = {};
        }
        
        var defaultParams = this.defaultModalParams();
        params = jQuery.extend(defaultParams,params);
        var max_height = (jQuery(window).height()) - ($('.global-nav').height());
        
        $('#overlayPage').one('shown.bs.modal',function(){
            if(!params.hasOwnProperty('ignoreScroll') && params['ignoreScroll'] != true) {
                var scrollParams = {
                    height: '',
                    railVisible:true,
                    alwaysVisible:true,
                    postition:"outside"
                };
                app.helper.showVerticalScroll(overlayPage.find('.modal-body'), scrollParams);
            }
            aDeferred.resolve(overlayPage.find('.data'));
        });
        
        $('#overlayPage').one('hidden.bs.modal',function(){    
            // Added to hide custom arrow added for taskmanagement
            overlayPage.find('.arrow').removeClass("show");
            overlayPage.find('.data').html('');
            $('#overlayPage');
        });
        
        jQuery(window).one('resize',function(){
            var max_height = (jQuery(window).height()) - ($('.global-nav').height()) - 60;
            $('#overlayPage').
            css('max-height',max_height).find('.data').css('max-height',max_height);
        });
        
        overlayPage.find('.data').html(data);
        $('#overlayPage').
            css('max-height',max_height).find('.modal-body').css('max-height',max_height);
        overlayPage.modal(params);
        return aDeferred.promise();
    },

    hidePageOverlay : function() {
        $('#overlayPage').modal('hide');
    },

    loadPageContentOverlay : function(data, params) {
        var aDeferred = new jQuery.Deferred();
        var defaultParams = this.defaultModalParams();
        params = jQuery.extend(defaultParams,params);
        
        var overlayPageContent = $('#overlayPageContent');
        
        if(jQuery(".content-area").length && jQuery(".content-area").hasClass('full-width')|| (jQuery('.settingsgroup').length === 0 && jQuery('#modules-menu').length === 0)){
           overlayPageContent.addClass('full-width');
        }
        var alreadyShown = false;
        if(overlayPageContent.hasClass('in')) {
            alreadyShown = true;
        }
        overlayPageContent.one('shown.bs.modal',function(){
            aDeferred.resolve($('#overlayPageContent'));
        });
               
        overlayPageContent.one('hidden.bs.modal',function(){
            overlayPageContent.find('.data').html('');
        })
        
        overlayPageContent.find('.data').html(data);
        vtUtils.applyFieldElementsView(overlayPageContent);
        overlayPageContent.modal(params);
        if(alreadyShown) {
            aDeferred.resolve(jQuery('#overlayPageContent'));
        }
        return aDeferred.promise();
    },

    hidePageContentOverlay : function() {
        var aDeferred = new jQuery.Deferred();
        var overlayPageContent = $('#overlayPageContent');
        overlayPageContent.one('hidden.bs.modal', function() {
            overlayPageContent.find('.data').html('');
            aDeferred.resolve();
        })
        $('#overlayPageContent').modal('hide');
        return aDeferred.promise();
    },

    loadHelpPageOverlay : function(data, params) {
        var aDeferred = new jQuery.Deferred();
        var defaultParams = this.defaultModalParams();
        
        params = jQuery.extend(defaultParams,params);
        var helpOverlayPageContent = jQuery('#helpPageOverlay');
        
        //first hide helpoverlay if already shown
        if(helpOverlayPageContent.hasClass('in')) {
            this.hideHelpPageOverlay();
        }
        
        var cb = params.cb;
        if(typeof cb != "function") {
            cb = function(){};
        }
        helpOverlayPageContent.one('shown.bs.modal', function () {
            aDeferred.resolve(helpOverlayPageContent);
        });
        $('#helpPageOverlay').html(data).modal(params);
        vtUtils.applyFieldElementsView(helpOverlayPageContent);
        cb(helpOverlayPageContent);
        return aDeferred.promise();
    },
    
    hideHelpPageOverlay : function() {
        var aDeferred = new jQuery.Deferred();
        var overlayPageContent = $('#helpPageOverlay');
        overlayPageContent.one('hidden.bs.modal', function(){
            overlayPageContent.find('.data').html('');
            aDeferred.resolve();
        })
        $('#helpPageOverlay').modal('hide'); 
        return aDeferred.promise();
    },

    showModal : function(content,params) {
        // we should hide all existing modal's
        this.hideModal();
        if(typeof params === "undefined") {
            params = {};
        }
        var defaultParams = this.defaultModalParams();
        params = jQuery.extend(defaultParams,params);

        var cb = params.cb;
        var container = jQuery('.myModal');
		
		container.on('hidden.bs.modal',function() {
			container.html('');
			window.onbeforeunload = null;
		});
		
        if(typeof cb === "function") {
            container.off('shown.bs.modal');
            //This event is fired when the modal has been made visible to the user
            container.on('shown.bs.modal', function () {
                cb(container);
            });
        }

        container.html(content).modal(params);
        vtUtils.applyFieldElementsView(container);
        return container;
    },

    hideModal : function(){
		var aDeferred = new jQuery.Deferred();
		var container = jQuery('.myModal');
        container.one('hidden.bs.modal', function(){
            aDeferred.resolve();
        })
		$('.myModal').modal('hide'); 
        return aDeferred.promise();
    },

    showInfoMessage : function(message) {
        $('#messageBar').html('<div class="alert alert-info">\n\
                                    <a href="#" class="close" data-dismiss="alert">&times;</a>\n\
                                    <strong>'+message+'</strong>\n\
                                </div>');
    },

    showErrorMessage : function(message) {
        $('#messageBar').html('<div class="alert alert-danger">\n\
                                    <a href="#" class="close" data-dismiss="alert">&times;</a>\n\
                                    <strong>'+message+'</strong>\n\
                                </div>');
    },

    showProgress : function(message) {
        var messageBar = jQuery('#messageBar');
        messageBar.removeClass('hide');
        var messageHTML='';
        if(message !== undefined) {
            messageHTML = '<div class="message"><span>'+message+'</span></div>';
        }
        messageBar.html('<div style="text-align:center;position:fixed;top:50%;left:40%;"><img src="'+app.vimage_path('loading.gif')+'">'+ messageHTML +'</div>');
    },

    hideProgress : function() {
         var messageBar = jQuery('#messageBar');
         messageBar.addClass('hide');
    },

    getSelect2FromSelect : function(selectEle){
        if(typeof selectEle == "undefined") {
            return jQuery({});
        }
        var select2 = selectEle.data('select2');
        if(typeof select2 == "undefined") {
            return jQuery({});
        }
        return jQuery('#'+select2.containerId);
    },
    
     /* 
    * Function to add clickoutside event on the element - By using outside events plugin 
    * @params element---On which element you want to apply the click outside event 
    * @params callbackFunction---This function will contain the actions triggered after clickoutside event 
    */ 
    addClickOutSideEvent : function(element, callbackFunction) { 
        element.one('clickoutside',callbackFunction); 
    },
    
    _initNotificationDefaults : function() {
        if(jQuery('.module-action-bar').length) {
            this.ERROR_DELAY = 1000;
            var defaultSettings = {
                    'element' : jQuery('body'),
                    'offset' : {
                        'x' : 50,
                        'y' : 92 + jQuery('.module-action-bar').offset().top
                    },
                    'z_index' : 10003,
                    'template' : '<div data-notify="container" class="col-xs-11 col-sm-3 vt-notification vt-notification-{0}" role="alert">' +
                                    '<div class="notificationHeader">'+
                                        '<button type="button" aria-hidden="true" class="close pull-right" data-notify="dismiss">×</button>' +
                                        '<span data-notify="icon"></span> ' +
                                        '<span data-notify="title">{1}</span> ' +
                                    '</div>'+
                                    '<div data-notify="message">{2}</div>' +
                                    '<div class="progress" data-notify="progressbar">' +
                                        '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
                                    '</div>' +
                                    '<a href="{3}" target="{4}" data-notify="url"></a>' +
                                '</div>',
                    'delay' : 600,
                    'animate' : 'null'
                }
            jQuery.notifyDefaults(defaultSettings);
        }
    },
    
    showAlertNotification : function(options,settings) {
        
        var defaultOptions = {
            'icon' : 'fa fa-exclamation-triangle',
            'title' : app.vtranslate('JS_ALERT')
        }        
        options = jQuery.extend(defaultOptions,options);
        jQuery.notify(options,settings);
    },
    
    showErrorNotification : function (options, settings) {
        var defaultOptions = {
            'icon' : 'fa fa-exclamation-circle',
            'title' : app.vtranslate('JS_ERROR')
        }

        var defaultSettings = {
            'delay' : this.ERROR_DELAY
        }
        options = jQuery.extend(defaultOptions, options);
        settings = jQuery.extend(defaultSettings, settings);
        jQuery.notify(options,settings);
    },
    
    showSuccessNotification : function(options, settings) {
        var defaultOptions = {
            'icon' : 'fa fa-check-circle',
            'title' : app.vtranslate('JS_SUCCESS')
        }
        options = jQuery.extend(defaultOptions, options);
        jQuery.notify(options,settings);
    },
    
	rand : function() {
        return Math.floor((Math.random() * 1000) + 1);
	},
    
    getColorContrast: function(hexcolor) {
        hexcolor = hexcolor.slice(1);
		var r = parseInt(hexcolor.substr(0,2),16);
		var g = parseInt(hexcolor.substr(2,2),16);
		var b = parseInt(hexcolor.substr(4,2),16);
		var yiq = ((r*299)+(g*587)+(b*114))/1000;
		return (yiq >= 128) ? 'light' : 'dark';
	},
    
    /*
	 * Function to show the custom dialogs
	 */
    showCustomDialogBox : function(data){
        //options are array of objects with label,button class and callback properties
        bootbox.dialog(data);
    },
    
    /**
	 * Function to decode the encoded htmlentities values
	 */
	getDecodedValue : function(value) {
		return jQuery('<div></div>').html(value).text();
	},
    
    initializeColorPicker : function(element,customParams,onChangeFunc) {
        var params = {
			flat : true,
			onChange : onChangeFunc
		};
		if(typeof customParams !== 'undefined') {
			params = jQuery.extend(params,customParams);
		}
		element.ColorPicker(params);
    },
    
    getRandomColor : function() {
        return '#'+(0x1000000+(Math.random())*0xffffff).toString(16).substr(1,6);
    },
    
    // https://github.com/kvz/phpjs/blob/master/functions/array/array_diff.js
    array_diff : function(arr1) {
        // discuss at: http://phpjs.org/functions/array_diff/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        // improved by: Sanjoy Roy
        // revised by: Brett Zamir (http://brett-zamir.me)
        // example 1: array_diff(['Kevin', 'van', 'Zonneveld'], ['van', 'Zonneveld']);
        // returns 1: {0:'Kevin'}
        var retArr = {},
            argl = arguments.length,
            k1 = '',
            i = 1,
            k = '',
            arr = {};
        arr1keys: for (k1 in arr1) {
            for (i = 1; i < argl; i++) {
                arr = arguments[i];
                for (k in arr) {
                    if (arr[k] === arr1[k1]) {
                        // If it reaches here, it was found in at least one array, so try next value
                        continue arr1keys;
                    }
                }
            retArr[k1] = arr1[k1];
            }
        }
        return retArr;
    },

    array_merge : function() {
        // discuss at: http://phpjs.org/functions/array_merge/
        // original by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: Nate
        // bugfixed by: Brett Zamir (http://brett-zamir.me)
        // input by: josh
        // example 1: arr1 = {"color": "red", 0: 2, 1: 4}
        // example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
        // example 1: array_merge(arr1, arr2)
        // returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
        // example 2: arr1 = []
        // example 2: arr2 = {1: "data"}
        // example 2: array_merge(arr1, arr2)
        // returns 2: {0: "data"}
        var args = Array.prototype.slice.call(arguments),
            argl = args.length,
            arg,
            retObj = {},
            k = '',
            argil = 0,
            j = 0,
            i = 0,
            ct = 0,
            toStr = Object.prototype.toString,
            retArr = true;
        for (i = 0; i < argl; i++) {
            if (toStr.call(args[i]) !== '[object Array]') {
                retArr = false;
                break;
            }
        }
        if (retArr) {
            retArr = [];
            for (i = 0; i < argl; i++) {
                retArr = retArr.concat(args[i]);
            }
            return retArr;
        }
        for (i = 0, ct = 0; i < argl; i++) {
            arg = args[i];
            if (toStr.call(arg) === '[object Array]') {
                for (j = 0, argil = arg.length; j < argil; j++) {
                    retObj[ct++] = arg[j];
                }
            } else {
                for (k in arg) {
                    if (arg.hasOwnProperty(k)) {
                            if (parseInt(k, 10) + '' === k) {
                                retObj[ct++] = arg[k];
                            } else {
                                retObj[k] = arg[k];
                            }
                        }
                }
            }
        }
        return retObj;
    },

    array_search: function(needle, haystack, argStrict) {
        //  discuss at: http://phpjs.org/functions/array_search/
        // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //    input by: Brett Zamir (http://brett-zamir.me)
        // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
        //  depends on: array
        //        test: skip
        //   example 1: array_search('zonneveld', {firstname: 'kevin', middle: 'van', surname: 'zonneveld'});
        //   returns 1: 'surname'
        //   example 2: ini_set('phpjs.return_phpjs_arrays', 'on');
        //   example 2: var ordered_arr = array({3:'value'}, {2:'value'}, {'a':'value'}, {'b':'value'});
        //   example 2: var key = array_search(/val/g, ordered_arr); // or var key = ordered_arr.search(/val/g);
        //   returns 2: '3'

        var strict = !!argStrict,
        key = '';

        if (haystack && typeof haystack === 'object' && haystack.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
            return haystack.search(needle, argStrict);
        }
        if (typeof needle === 'object' && needle.exec) { // Duck-type for RegExp
            if (!strict) { // Let's consider case sensitive searches as strict
                var flags = 'i' + (needle.global ? 'g' : '') +
                        (needle.multiline ? 'm' : '') +
                        (needle.sticky ? 'y' : ''); // sticky is FF only
                needle = new RegExp(needle.source, flags);
            }
            for (key in haystack) {
                if (needle.test(haystack[key])) {
                    return key;
                }
            }
            return false;
        }
        for (key in haystack) {
            if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
                return key;
            }
        }
        return false;
    },
    getCookie : function(c_name) {
		var c_value = document.cookie;
        var c_start = c_value.indexOf(" " + c_name + "=");
        if (c_start == -1) {
            c_start = c_value.indexOf(c_name + "=");
        }
        if (c_start == -1) {
            c_value = null;
        } else {
            c_start = c_value.indexOf("=", c_start) + 1;
            var c_end = c_value.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = c_value.length;
            }
            c_value = unescape(c_value.substring(c_start, c_end));
        }
        return c_value;
	},

	setCookie : function(c_name,value,exdays) {
		var exdate = new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	},
    
     getViewHeight: function(){
        var winHeight= jQuery(window).height()-50;
        return winHeight;
    },
    
    showPopup : function(content,params) {
        if(typeof params === "undefined") {
            params = {};
        }
        var defaultParams = app.helper.defaultModalParams();
        params = jQuery.extend(defaultParams,params);

        var cb = params.cb;
        if(jQuery('#popupModal').length) return;
        var container = jQuery('<div id="popupModal" class="modal"></div>');
		
		container.on('hidden.bs.modal',function() {
			container.html('').remove();
		});
		
        if(typeof cb === "function") {
            container.off('shown.bs.modal');
            //This event is fired when the modal has been made visible to the user
            container.on('shown.bs.modal', function () {
                cb(container);
            });
        }

        container.html(content).modal(params);
        vtUtils.applyFieldElementsView(container);
        return container;
    },
    
    hidePopup : function() {
        jQuery('#popupModal').modal("hide");
    },
    
    /*
	 * Function to confirmation modal for recurring events updation and deletion 
	 */
	showConfirmationForRepeatEvents : function(customParams) {
        var self = this;
		var aDeferred = jQuery.Deferred();
        if(typeof customParams === 'undefined') {
            customParams = {};
        }
		var params = {
			module : 'Calendar',
			view : 'RecurringDeleteCheck'
		};
		jQuery.extend(params, customParams);
		var postData = {};
        self.showProgress();
		app.request.get({'data' : params}).then(function(e,data) {
            self.hideProgress();
			var callback = function(modalContainer) {
				modalContainer.on('click', '.onlyThisEvent', function() {
					postData['recurringEditMode'] = 'current';
					self.hidePopup();
					aDeferred.resolve(postData);
				});
				modalContainer.on('click', '.futureEvents', function() {
					postData['recurringEditMode'] = 'future';
					self.hidePopup();
					aDeferred.resolve(postData);
				});
				modalContainer.on('click', '.allEvents', function() {
					postData['recurringEditMode'] = 'all';
					self.hidePopup();
					aDeferred.resolve(postData);
				});
			};            
            self.showPopup(data, {
                'cb' : callback
            });
		});
		return aDeferred.promise();
	},
        
        equalWidth : function(group){
            broadest = 0;
            group.each(function () {
                var thisWidth = jQuery(this).width();
                if (thisWidth > broadest) {
                    broadest = thisWidth;
                }
            });
            group.width(broadest);
        },
        
        fixedBottomListViewScroll : function(scrollwrap){
            scrollwrap = jQuery(scrollwrap);
            //The element should be relative to calculate offset
            scrollwrap.css({position: "relative", top: '-15px'});
            var winHeight = jQuery(window).innerHeight();
            var viewportOffset = scrollwrap.offset().top - jQuery(window).scrollTop();
            var view = jQuery('[name="view"]').val();
            if (viewportOffset >= winHeight) {
                if(view == "Popup"){
                    scrollwrap.css({position: "fixed", top: 'auto', bottom: '15px'});
                }else if(view == "List"){
                    scrollwrap.css({position: "fixed", top: 'auto', bottom: 0});
                }
            } else {
                scrollwrap.css({position: "relative", 'top': '-15px', bottom: 'auto'});
            }
        },
        
        dynamicListViewHorizontalScroll : function(){
            var thisInstance = this;
            this.equalWidth(jQuery(".sticky-wrap, #scroller_wrapper"));
            var stickyWrapWidth = jQuery('.sticky-wrap').width();
            jQuery('.sticky-wrap').css("width", stickyWrapWidth-3);
            this.equalWidth(jQuery("#listview-table, #scroller"));
            var tableHeight = jQuery('.sticky-wrap').height();
            var winHeight = jQuery(window).innerHeight();

            jQuery("#scroller_wrapper").scroll(function () {
                jQuery(".sticky-wrap").scrollLeft(jQuery("#scroller_wrapper").scrollLeft());
            });
            jQuery(".sticky-wrap").scroll(function () {
                jQuery("#scroller_wrapper").scrollLeft(jQuery(".sticky-wrap").scrollLeft());
            });
            jQuery(window).scroll(function () {
               thisInstance.fixedBottomListViewScroll(jQuery("#scroller_wrapper"));
            });
            
            if(tableHeight > winHeight-142){
                thisInstance.fixedBottomListViewScroll(jQuery("#scroller_wrapper"));
            }
	},
        
        dynamicPopupViewHorizontalScroll : function(){
            var thisInstance = this;
            this.equalWidth(jQuery(".popupEntriesDiv, #scroller_wrapper"));
            this.equalWidth(jQuery(".listview-table, #scroller"));
            var tableHeight = jQuery('.popupEntriesDiv').height();
            var winHeight = jQuery('.modal-body').innerHeight();

            jQuery("#scroller_wrapper").scroll(function () {
                jQuery(".popupEntriesDiv").scrollLeft(jQuery("#scroller_wrapper").scrollLeft());
            });
            jQuery(".popupEntriesDiv").scroll(function () {
                jQuery("#scroller_wrapper").scrollLeft(jQuery(".popupEntriesDiv").scrollLeft());
            });
            jQuery(window).scroll(function () {
               thisInstance.fixedBottomListViewScroll(jQuery("#scroller_wrapper"));
            });
            
            if(tableHeight > winHeight-142){
                thisInstance.fixedBottomListViewScroll(jQuery("#scroller_wrapper"));
            }
	},
    
    /**
     * Function to check if the browser is ie
     */
    isMSIE : function() {
        return navigator.userAgent.indexOf("MSIE ") > 0;
    },
    
    /**
     * Function to check newer version of ie 
     */
    isTrident : function() {
        return (/Trident/).test(navigator.userAgent);
    },
    
    /**
     * Function to check if the browser in mozilla
     */
    isMozilla: function() {
        var ua = navigator.userAgent;
        ua = ua.toLowerCase();
        return (/mozilla/).test(ua) || (/firefox/).test(ua);
    },
    
    /**
     * Function to check if the browser is webkit
     */
    isWebkit: function() {
        var ua = navigator.userAgent;
        ua = ua.toLowerCase();
        return (/webkit/).test(ua) || (/chrom(e|ium)/).test(ua);
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
		app.request.post({data: params}).then(
			function(err, data) {
				var result = data.success;
				if(result == true) {
					aDeferred.reject(data);
				} else {
					aDeferred.resolve(data);
				}
			}
		);
		return aDeferred.promise();
	},
	
	registerLeavePageWithoutSubmit: function(form) {
		var initialFormData = form.serialize();
		window.onbeforeunload = function(e) {
			if (initialFormData != form.serialize() && form.data('submit') != "true") {
				return app.vtranslate("JS_CHANGES_WILL_BE_LOST");
	}
		};
	},
	
	registerModalDismissWithoutSubmit: function(form) {
		var initialFormData = form.serialize();
		jQuery('.modal .close,.modal .cancelLink').click(function(e) {
			if (initialFormData != form.serialize() && form.data('submit') != "true") {
				app.helper.showConfirmationBox({'message' : app.vtranslate("JS_CHANGES_WILL_BE_LOST") +' '+ app.vtranslate('JS_WISH_TO_PROCEED')}).then(function(){
					window.onbeforeunload = null;
					if(form.closest('#overlayPageContent').length > 0) {
						app.helper.hidePageContentOverlay();
					}else {
						app.helper.hideModal();
					}
				});
				return false;
			}
		});
	},
	
    getDropDownmenuParent: function ($this) {
        var selector = $this.attr('data-target');

        if (!selector) {
            selector = $this.attr('href');
            selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
        };

        var $parent = selector && $(selector);

        return $parent && $parent.length ? $parent : $this.parent();
    }
});

function VtError(params) {
	this.name	= 'VtError';
	this.stack	= (new Error()).stack;
	this.title	= params.title || app.vtranslate('JS_ERROR');
	this.message= params.message || '';
}
VtError.prototype = Object.create(Error.prototype);
VtError.prototype.constructor = VtError;