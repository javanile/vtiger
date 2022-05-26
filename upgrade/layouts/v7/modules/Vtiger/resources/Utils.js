/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

var vtUtils = {

        weekDaysArray : {Sunday : 0,Monday : 1, Tuesday : 2, Wednesday : 3,Thursday : 4, Friday : 5, Saturday : 6},

    /**
	 * Function which will show the select2 element for select boxes . This will use select2 library
	 */
	showSelect2ElementView : function(selectElement, params) {
		if(typeof params == 'undefined') {
			params = {};
		}

		var data = selectElement.data();
		if(data != null) {
			params = jQuery.extend(data,params);
		}

        // Fix to eliminate flicker happening on list view load
        var ele = jQuery(selectElement);
        if(ele.hasClass("listSearchContributor")){
            ele.closest(".select2_search_div").find(".select2_input_element").remove();
            ele.show();
        }

		// Sort DOM nodes alphabetically in select box.
		if (typeof params['customSortOptGroup'] != 'undefined' && params['customSortOptGroup']) {
			jQuery('optgroup', selectElement).each(function(){
				var optgroup = jQuery(this);
				var options  = optgroup.children().toArray().sort(function(a, b){
					var aText = jQuery(a).text();
					var bText = jQuery(b).text();
					return aText < bText ? 1 : -1;
				});
				jQuery.each(options, function(i, v){
					optgroup.prepend(v);
				});
			});
			delete params['customSortOptGroup'];
		}

		//formatSelectionTooBig param is not defined even it has the maximumSelectionSize,
		//then we should send our custom function for formatSelectionTooBig
		if(typeof params.maximumSelectionSize != "undefined" && typeof params.formatSelectionTooBig == "undefined") {
			var limit = params.maximumSelectionSize;
			//custom function which will return the maximum selection size exceeds message.
			var formatSelectionExceeds = function(limit) {
					return app.vtranslate('JS_YOU_CAN_SELECT_ONLY')+' '+limit+' '+app.vtranslate('JS_ITEMS');
			}
			params.formatSelectionTooBig = formatSelectionExceeds;
		}
        if(selectElement.attr('multiple') != 'undefined' && typeof params.closeOnSelect == 'undefined') {
            params.closeOnSelect = false;
		}
        selectElement.select2(params)
					 .on("open", function(e) {
						 var element = jQuery(e.currentTarget);
						 var instance = element.data('select2');
						 instance.dropdown.css('z-index',1000002);
					 }).on("select2-open", function(e) {
						 var element = jQuery(e.currentTarget);
						 var instance = element.data('select2');
						 instance.dropdown.css('z-index',1000002);
					 });
        //validator should not validate select2 text inputs
        selectElement.select2("container").find('input.select2-input').addClass('ignore-validation');
		if(typeof params.maximumSelectionSize != "undefined") {
			vtUtils.registerChangeEventForMultiSelect(selectElement,params);
		}
		return selectElement;
	},

    /**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */
	registerChangeEventForMultiSelect :  function(selectElement,params) {
		if(typeof selectElement == 'undefined') {
			return;
		}

		var limit = params.maximumSelectionSize;
		selectElement.on('change',function(e){
			var instance = jQuery(e.currentTarget).data('select2');
			var data = instance.data();
			if (jQuery.isArray(data) && data.length >= limit ) {
				instance.updateResults();
            }
		});
	},

    /**
     * Function register datepicker for dateField elements
     * @param {jQuery} parent
     */
    registerEventForDateFields : function(parent, params) {
        var element;
		if (parent.hasClass('dateField') && !parent.hasClass('ignore-ui-registration')) {
            element = parent;
        } else {
            element = jQuery('.dateField:not(ignore-ui-registration)', parent);
        }

		if(typeof params == 'undefined') {
			params = {};
		}

        var parentDateElement = element.parent();
        jQuery('.input-group-addon',parentDateElement).on('click',function(e){
            var elem = jQuery(e.currentTarget);
            elem.parent().find('.dateField').focus();
        });

        var userDateFormat = app.getDateFormat();
        var calendarType = element.data('calendarType');
        if(element.length > 0){
            jQuery(element).each(function(index, Elem){
                element = jQuery(Elem);
                 if(calendarType == "range"){
                    //Default first day of the week
                    var defaultFirstDay = jQuery('#start_day').val();
                    element.dateRangePicker({
                        startOfWeek: defaultFirstDay.toLowerCase(),
                        format: userDateFormat.toUpperCase(),
                        separator: ',',
                        showShortcuts: true,
                        autoClose : false,
                        duration : 500
                    });
                }else{
                    var elementDateFormat = element.data('dateFormat');
                    if(typeof elementDateFormat !== 'undefined') {
                        userDateFormat = elementDateFormat;
                    }
					var defaultPickerParams = {
                        autoclose: true,
                        todayBtn: "linked",
                        format: userDateFormat,
                        todayHighlight: true,
						clearBtn : true
                    };
					jQuery.extend(defaultPickerParams, params);
                    element.datepicker(defaultPickerParams);

					if(element.hasClass('input-daterange')){
						element = element.find('input');
					}
                }   
            });
        }
    },

    /**
	 * Function which will register time fields
	 * @params : container - jquery object which contains time fields with class timepicker-default or itself can be time field
	 *			 registerForAddon - boolean value to register the event for Addon or not
	 *			 params  - params for the  plugin
	 * @return : container to support chaining
	 */
	registerEventForTimeFields : function(container, registerForAddon, params) {
		if(typeof container === 'undefined') {
			container = jQuery('body');
		}
		if(typeof registerForAddon === 'undefined'){
			registerForAddon = true;
		}

		container = jQuery(container);

		if (container.hasClass('timepicker-default')) {
            var element = container;
        } else {
            var element = container.find('.timepicker-default');
        }

		if(registerForAddon === true){
			var parentTimeElem = element.closest('.time');
			jQuery('.input-group-addon',parentTimeElem).on('click',function(e){
				var elem = jQuery(e.currentTarget);
				elem.closest('.time').find('.timepicker-default').focus();
			});
		}

		if(typeof params === 'undefined') {
			params = {};
		}

		var timeFormat = element.data('format');
		if(timeFormat == '24') {
			timeFormat = 'H:i';
		} else {
			timeFormat = 'h:i A';
		}
		var defaultsTimePickerParams = {
			'timeFormat' : timeFormat,
			'className'  : 'timePicker'
		};
		var params = jQuery.extend(defaultsTimePickerParams, params);

        if(element.length) {
            element.timepicker(params);
        }

		return container;
	},

    /**
     * Function to change view of edited elements related to selected Plugin
     * @param {type} elementsContainer
     * @returns {undefined}
     */
    applyFieldElementsView : function(container){
        this.showSelect2ElementView(container.find('select.select2'));
        this.registerEventForDateFields(container.find('.dateField').not('.ignore-ui-registration'));
        this.registerEventForTimeFields(container.find('.timepicker-default'));
    },

    showQtip : function(element,message,customParams) {
        if(typeof customParams === 'undefined') {
            customParams = {};
        }
        var qtipParams =  {
            content: {
                text: message
            },
            show: {
                event: 'Vtiger.Qtip.ShowMesssage'
            },
            hide: {
                event: 'Vtiger.Qtip.HideMesssage'
            }
        };
        jQuery.extend(qtipParams,customParams);

        element.qtip(qtipParams);
        element.trigger('Vtiger.Qtip.ShowMesssage');
    },

    hideQtip : function(element) {
        element.trigger('Vtiger.Qtip.HideMesssage');
    },

	linkifyStr : function(str) {
		var options = {'TLDs':267};
		return anchorme.js(str,options);
	},

	htmlSubstring : function(content, maxlength) {
		var m, r = /<([^>\s]*)[^>]*>/g,
			stack = [],
			lasti = 0,
			result = '';

		//for each tag, while we don't have enough characters
		while ((m = r.exec(content)) && maxlength) {
			//get the text substring between the last tag and this one
			var temp = content.substring(lasti, m.index).substr(0, maxlength);
			//append to the result and count the number of characters added
			result += temp;
			maxlength -= temp.length;
			lasti = r.lastIndex;

			if (content) {
				result += m[0];
				if (m[1].indexOf('/') === 0) {
					//if this is a closing tag, then pop the stack (does not account for bad html)
					stack.pop();
				} else if (m[1].lastIndexOf('/') !== m[1].length - 1) {
					//if this is not a self closing tag then push it in the stack
					stack.push(m[1]);
				}
			}
		}

		//add the remainder of the string, if needed (there are no more tags in here)
		result += content.substr(lasti, maxlength);

		//fix the unclosed tags
		while (stack.length) {
			var unclosedtag = stack.pop();
			if(jQuery.inArray(unclosedtag,['br']) == -1){
				result += '</' + unclosedtag + '>';
			}
		}
		return result;
	},

    showValidationMessage : function(element,message,params) {
        if(element.hasClass('select2')) {
            element = app.helper.getSelect2FromSelect(element);
        }

        if(typeof params === 'undefined') {
            params = {};
        }

        var validationTooltipParams = {
            position: {
                my: 'bottom left',
                at: 'top left'
            },
            style: {
                classes: 'qtip-red qtip-shadow'
            }
        };

        jQuery.extend(validationTooltipParams,params);
        this.showQtip(element,message,validationTooltipParams);
        element.addClass('input-error');
    },

    hideValidationMessage : function(element) {
        if(element.hasClass('select2')) {
            element = app.helper.getSelect2FromSelect(element);
        }
        //should hide even message displyed by vtValidate
        element.trigger('Vtiger.Validation.Hide.Messsage');
        this.hideQtip(element);
        element.removeClass('input-error');
    },

    getMomentDateFormat : function() {
        var dateFormat = app.getDateFormat();
        return dateFormat.toUpperCase();
    },

    getMomentTimeFormat : function() {
        var hourFormat = app.getHourFormat();
        var timeFormat = 'HH:mm';
        if(hourFormat === 12) {
            timeFormat = 'hh:mm A';
        }
        return timeFormat;
    },

    getMomentCompatibleDateTimeFormat : function() {
        return this.getMomentDateFormat() + ' ' + this.getMomentTimeFormat();
    },

    convertFileSizeInToDisplayFormat : function(fileSizeInBytes) {
		 var i = -1;
		var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
		do {
			fileSizeInBytes = fileSizeInBytes / 1024;
			i++;
		} while (fileSizeInBytes > 1024);

		return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];

	},

    enableTooltips : function(options) {
		if(typeof options == 'undefined') {
			options = {};
		}

        jQuery(function () {
            jQuery('[data-toggle="tooltip"]').tooltip(options);
        });
    }

}
