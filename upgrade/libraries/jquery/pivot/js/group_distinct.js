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

function grp_distinct(options) {
    "use strict";
    var self = {};
    self.values = [];
    self.names = {};
    self.fieldtype = "number";
    self.field = options.field;
    if (typeof options.sort === "undefined") {
        self.sort = "ASC";
    } else {
        self.sort = options.sort.toUpperCase();
    }
    if (typeof options.params !== "undefined") {
        self.params = options.params;
    } else {
        self.params = null;
    }
    if (typeof options.showAll !== "undefined") {
        self.showAll = options.showAll;
    } else {
        self.showAll = false;
    }

    self.CalculateValue = function (R) {
        var V = "";
        var res;
        if (typeof R[this.field] === "function") {
            V = R[this.field](this.params);
        } else if (typeof R[this.field] === "number") {
            V = R[this.field].toString();
        } else if (typeof R[this.field] === "string") {
            V = R[this.field];
            this.fieldtype = "string";
        }

        if (typeof V !== "string") {
            V = "";
        }

        if (typeof this.names[V] !== "undefined") {
            res = this.names[V];
        } else {
            res = this.values.push(V) - 1;
            this.names[V] = res;
        }
        if(this.sort === "MONTH_ORDER" || this.sort === "WEEK_ORDER") {
            var mOrder = [];
            if(this.sort === "MONTH_ORDER")
                mOrder = ["January","February","March", "April", "May", "June","July","August","September","October","November","December",""];
            else if(this.sort === "WEEK_ORDER") {
                for(var i =1; i <= 53; i++) {
                    mOrder.push('Week '+i);
                }
                mOrder.push('');
            }
            var sortedArray = new Object();
            var count = 0;
            var objectKeys = Object.keys(this.names);
            for (var i = 0; i < mOrder.length; i++) {
                if ($.inArray(mOrder[i], objectKeys) != -1) {
                    sortedArray[mOrder[i]] = count;
                    count++;
                }
            }
            this.names = sortedArray;
        }
        return res;
    };

    self.getStringValue = function (idx) {
        return this.values[idx];
    };

    self.DisplayValues = function (UsedValues) {
        var res;
        var i;

        if (this.showAll) {
            res = [];
            for (i = 0; i < this.values.length; i++) {
                res.push(i.toString());
            }
        } else {
            res = UsedValues.slice(0);
        }

        var self = this;
        if(self.sort === "MONTH_ORDER" || self.sort === "WEEK_ORDER") {
            var mOrder = [];
            if(self.sort === "MONTH_ORDER")
                mOrder = ["January","February","March", "April", "May", "June","July","August","September","October","November","December",""];
            else if(self.sort === "WEEK_ORDER") {
                for(var i = 1; i <= 53; i++) {
                    mOrder.push('Week '+i);
                }
                mOrder.push('');
            }
            var sortedArray = new Array();
            for (var i = 0; i < mOrder.length; i++) {
                if ($.inArray(mOrder[i], self.values) != -1) {
                    sortedArray.push(mOrder[i]);
                }
            }
            self.values = sortedArray;
        }
        if(self.sort !== "DATE_ORDER") {
            if (this.fieldtype === "string") {
                res = res.sort(function (a, b) {
                    var res = 0;
                    if(self.sort !== "MONTH_ORDER" && self.sort !== "WEEK_ORDER") {
                        if (self.values[a] < self.values[b]) {
                            res = -1;
                        }
                        if (self.values[a] > self.values[b]) {
                            res = 1;
                        }
                    } else {
                        res = a-b;
                    }
                    return res;
                });
            } else {
                res = res.sort(function (a, b) {
                    var aa = parseFloat(self.values[a]);
                    var bb = parseFloat(self.values[b]);
                    var res = 0;
                    if (aa < bb) {
                        res = -1;
                    }
                    if (aa > bb) {
                        res = 1;
                    }
                    return res;
                });
            }
        }

        if (this.sort === "DESC") {
            res = res.reverse();
        }

        return res;
    };
    
    return self;
}

$.unc.plugins.addGrouper('distinct', grp_distinct);