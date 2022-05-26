/*
    Copyright 2013 Uniclau S.L. (www.uniclau.com)
    
    This file is part of jbPivot.

    jbPivot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    jbPivot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with jbPivot.  If not, see <http://www.gnu.org/licenses/>.
 */

function agregate_average(options) {
    "use strict";
    var self = {};
    self.options = $.extend({}, options);
    self.agregate = function (a, b) {
        var res;
        if ((!a) || (a.type !== "agregate_average")) {
            res = {
                type: "agregate_average",
                sum: 0,
                count: 0
            };
        } else {
            res = {
                type: "agregate_average",
                sum: a.sum,
                count: a.count
            };
        }
        if (b.type === "agregate_average") {
            if(isNaN(b.sum) === true) b.sum = 0;
            res.sum += b.sum;
            res.count += b.count;
        } else if (typeof b === "object") {
            res.count++;
            if (typeof b[this.options.field] === "number") {
                res.sum += b[this.options.field];
            } else if (typeof b[this.options.field] === "string") {
                try {
                    res.sum += parseFloat(b[this.options.field], 10);
                } catch (err) {

                }
            }
        }
        return res;
    };

    self.getValue = function (a) {
        var res = null;
        if ((a) && (a.type === "agregate_average") && (a.count > 0)) {
            var v = a.sum / a.count;
            res = v;
        }
        return res;
    };
    return self;
}

$.unc.plugins.addAgregate('average', agregate_average);