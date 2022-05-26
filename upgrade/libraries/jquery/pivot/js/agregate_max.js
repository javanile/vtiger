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

function agregate_max(options) {
    "use strict";
    var self = {};
    self.field = options.field;
    self.agregate = function (a, b) {
        var res;
        if ((!a) || (a.type !== "agregate_max")) {
            res = {
                type: "agregate_max",
                max: 0
            };
        } else {
            res = {
                type: "agregate_max",
                max: a.max
            };
        }
        if (b.type === "agregate_max") {
            if(isNaN(b.max) === true) b.max = 0;
            if (b.max > res.max) {
                res.max = b.max;
            }
        } else if (typeof b === "object") {
            if (typeof b[this.field] === "number") {
                if (b[this.field] > res.max) {
                    res.max = b[this.field];
                }
            } else if (typeof b[this.field] === "string") {
                try {
                    var max = parseFloat(b[this.field],10);
                    if (max > res.max) {
                        res.max = max;
                    }
                } catch (err) {

                }
            }
        }
        return res;
    };

    self.getValue = function (a) {
        var res = null;
        if ((a) && (a.type === "agregate_max")) {
            res = a.max;
        }
        return res;
    };
    return self;
}

$.unc.plugins.addAgregate('max', agregate_max);