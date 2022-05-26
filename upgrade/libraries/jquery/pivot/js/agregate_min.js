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

function agregate_min(options) {
    "use strict";
    var self = {};
    self.field = options.field;
    self.agregate = function (a, b) {
        var res;
        if ((!a) || (a.type !== "agregate_min")) {
            res = {
                type: "agregate_min",
                min: 0
            };
        } else {
            res = {
                type: "agregate_min",
                min: a.min
            };
        }
        if (b.type === "agregate_min") {
            if(isNaN(b.min) === true) b.min = 0;
            if(b.min != 0 && res.min == 0){
                res.min = b.min;
            } else {
                if(b.min < res.min && b.min != 0)
                    res.min = b.min;
            }
        } else if (typeof b === "object") {
            if (typeof b[this.field] === "number") {
                    res.min = b[this.field];
            } else if (typeof b[this.field] === "string") {
                try {
                    res.min = parseFloat(b[this.field],10);
                } catch (err) {

                }
            }
        }
        return res;
    };

    self.getValue = function (a) {
        var res = null;
        if ((a) && (a.type === "agregate_min")) {
            res = a.min;
        }
        return res;
    };
    return self;
}

$.unc.plugins.addAgregate('min', agregate_min);