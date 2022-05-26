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

function formatter_default() {
    "use strict";
    var self = {};
    self.format = function (value) {
        var V = "";
        try {
            V = value.toString();
        } catch (Error) {

        }
        return V;
    };
    return self;
}

$.unc.plugins.addFormatter('default', formatter_default);