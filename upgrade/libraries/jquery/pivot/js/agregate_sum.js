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

function agregate_sum(options) {
    "use strict";
    var self = {};
    self.field = options.field;
    self.agregate = function (a, b) {
        var res;
        if ((!a) || (a.type !== "agregate_sum")) {
            res = {
                type: "agregate_sum",
                sum: 0
            };
        } else {
            res = {
                type: "agregate_sum",
                sum: a.sum
            };
        }
        if (b.type === "agregate_sum") {
            if(isNaN(b.sum) === true) b.sum = 0;
            res.sum += b.sum;
        } else if (typeof b === "object") {
            if (typeof b[this.field] === "number") {
                res.sum += b[this.field];
            } else if (typeof b[this.field] === "string") {
                try {
                    res.sum += parseFloat(b[this.field],10);
                } catch (err) {

                }
            }
        }
        return res;
    };

    self.getValue = function (a) {
        var res = null;
        if ((a) && (a.type === "agregate_sum")) {
            var multiplier = Math.pow(10, 2);
            res = Math.round(a.sum * multiplier) / multiplier;
        }
        return res;
    };
    return self;
}

$.unc.plugins.addAgregate('sum', agregate_sum);