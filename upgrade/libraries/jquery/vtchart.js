/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

(function( $ ) {
    
    var vtChart = function() {
        this.element = false;
        
        this.init = function(element,data,options) {
           vtChart.prototype.init.call(this,element,data,options);
        }
        
        this.triggerClick = function(data) {
            this.element.trigger('vtchartClick',data);
        }
    }
    
    $.fn.vtchart = function(options) {
        var data = [];
        for (var i=0, l=arguments.length; i<l; i++) {
                data.push(arguments[i]);
        }
		if($.isFunction(vtJqPlotInterface))
            vtChart.prototype = new vtJqPlotInterface();
        else
            console.log('supported interface not found!');
           
        return this.each(function(index,element){
            var jQElement = jQuery(element).empty(); /* Clear any existing content to avoid overlapping redraw */
            var instance = new vtChart();
            instance.init(jQElement,data[0],data[1]);
        });
    }
    
})( jQuery );
