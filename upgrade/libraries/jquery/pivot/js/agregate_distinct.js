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

function agregate_distinct(options) {
    "use strict";
    var self = {};
    self.field = options.field;
    self.agregate = function (a, b) {
        var res = {
            type: "fa_distinct"
        };
        if ((!a) || (a.type !== "fa_distinct")) {
            res.repeated = false;
            if (b.type === "fa_distinct") {
                res.val = b.val;
            } else if (typeof b === "object") {
                res.repeated = false;
                if ((typeof b[this.field] === "number") || (typeof b[this.field] === "string")) {
                    res.val = b[this.field];
                } else {
                    res.val = null;
                }
            } else {
                res.val = null;
            }
        } else {
            if (b.type === "fa_distinct") {
                res.repeated = (a.repeated || b.repeated || (a.val !== b.val));
                res.val = a.val;
            } else if (typeof b === "object") {
                if ((typeof b[this.field] === "number") || (typeof b[this.field] === "string")) {
                    res.repeated = (a.repeated || (a.val !== b[this.field]));
                    res.val = a.val;
                } else {
                    res.repeated = (a.repeated || (a.val !== null));
                    res.val = null;
                }
            } else {
                res.repeated = (a.repeated || (a.val !== null));
                res.val = null;
            }
        }

        return res;
    };

    self.getValue = function (a) {
        var res = null;
        if ((a) && (a.type === "fa_distinct")) {
            if (a.repeated) {
                res = "*";
            } else {
                res = a.val;
            }
        }
        return res;
    };
    return self;
}

$.unc.plugins.addAgregate('distinct', agregate_distinct);