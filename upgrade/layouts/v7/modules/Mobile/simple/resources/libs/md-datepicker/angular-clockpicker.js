angular.module("ui.clockpicker", [])
        .controller("ClockPickerController", function ($scope) {
        })
        .directive("clockpicker", function ($compile) {
            return {
                restrict: "EA",
                templateUrl: "template/clockpicker.html",
                scope: {
                    time12format: "=",
                    appliedname: '=',
                    time24format: '=',
                    frequired: '=ngRequired',
                    model: '=ngModel'
                },
                controller: function ($scope) {
                    $scope.hourOptions = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                    $scope.minuteOptions = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];
                    $scope.periodOptions = ['AM', 'PM'];
                    $scope.selectionMode = true;
                    $scope.fieldName = $scope.appliedname;
                    $scope.oldDisplayTime = $scope.time12format;
                    $scope.oldSaveTime = $scope.time24format;
                    $scope.clockpicker = false;

                    $scope.get24hrsTimebyDate = function (date) {
                        hh = (date.getHours() < 10 ? '0' : '') + date.getHours().toString(),
                                mm = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes().toString();
                        ss = (date.getSeconds() < 10 ? '0' : '') + date.getSeconds().toString();
                        return hh + ':' + mm + ':' + ss;
                    };
                    $scope.get12hrsTimebyDate = function (date) {
                        h = date.getHours();
                        hh = h;
                        period = "AM";
                        if (h > 12) {
                            hh = parseInt(h) - 12;
                            period = "PM";
                        }
                        hh = (hh < 10 ? '0' : '') + hh.toString(),
                                mm = (date.getMinutes() < 10 ? '0' : '') + date.getMinutes().toString();
                        return hh + ':' + mm + ' ' + period;
                    };
                    $scope.get24hrsTimeby12hrsString = function (datestr) {
                        var chours = Number((datestr.match(/^(\d+)/) !== null) ? datestr.match(/^(\d+)/)[1] : 0);
                        var cminutes = Number((datestr.match(/:(\d+)/) !== null) ? datestr.match(/:(\d+)/)[1] : 0);
                        var AMPM = datestr.match(/\s(.*)$/)[1];
                        if (chours < 12 && AMPM == "PM") {
                            chours = chours + 12;
                        }
                        if (chours == 12 && AMPM == "AM") {
                            chours = chours - 12;
                        }
                        hh = (chours < 10 ? '0' : '') + chours,
                                mm = (cminutes < 10 ? '0' : '') + cminutes;
                        return hh + ':' + mm + ':00';
                    };
                    $scope.format24hrsTime = function (datestr) {
                        var chours = Number((datestr.match(/^(\d+)/) !== null) ? datestr.match(/^(\d+)/)[1] : 0);
                        var cminutes = Number((datestr.match(/:(\d+)/) !== null) ? datestr.match(/:(\d+)/)[1] : 0);
                        hh = (chours < 10 ? '0' : '') + chours,
                                mm = (cminutes < 10 ? '0' : '') + cminutes;
                        return hh + ':' + mm + ':00';
                    };
                    $scope.get12hrsTimeby24hrsString = function (datestr) {
                        var chours = Number((datestr.match(/^(\d+)/) !== null) ? datestr.match(/^(\d+)/)[1] : 0);
                        var cminutes = Number((datestr.match(/:(\d+)/) !== null) ? datestr.match(/:(\d+)/)[1] : 0);
                        var period = "AM";
                        if (chours > 12) {
                            chours = parseInt(chours) - 12;
                            period = "PM";
                        }
                        hh = (chours < 10 ? '0' : '') + chours,
                                mm = (cminutes < 10 ? '0' : '') + cminutes;
                        return hh + ':' + mm + ' ' + period;
                    };
                    $scope.format12hrsTime = function (datestr) {
                        var chours = Number((datestr.match(/^(\d+)/) !== null) ? datestr.match(/^(\d+)/)[1] : 0);
                        var cminutes = Number((datestr.match(/:(\d+)/) !== null) ? datestr.match(/:(\d+)/)[1] : 0);
                        var AMPM = datestr.match(/\s(.{2})$/)[1];
                        hh = (chours < 10 ? '0' : '') + chours,
                                mm = (cminutes < 10 ? '0' : '') + cminutes;
                        return hh + ':' + mm + ' ' + AMPM;
                    };
                    $scope.get12HourNumberby12hrString = function (datestr) {
                        chours = Number((datestr.match(/^(\d+)/) !== null) ? datestr.match(/^(\d+)/)[1] : 0);
                        if (chours > 12) {
                            chours = parseInt(chours) - 12;
                        }
                        return chours;
                    };
                    $scope.getMinutesby12hrString = function (datestr) {
                        cminutes = Number((datestr.match(/:(\d+)/) !== null) ? datestr.match(/:(\d+)/)[1] : 0);
                        return cminutes;
                    };
                    $scope.getPeriodby12hrsString = function (datestr) {
                        var period = (datestr.match(/\s(.{2})$/)[1]).toUpperCase();
                        if (period !== "AM" && period !== "PM") {
                            period = "AM";
                        }
                        return period;
                    };

                    currentDate = new Date();
                    $scope.currentTime = $scope.get12hrsTimebyDate(currentDate);
                    $scope.currentTime24 = $scope.get24hrsTimebyDate(currentDate);
                    $scope.finalSaveTime = $scope.format24hrsTime($scope.time24format ? $scope.time24format : $scope.currentTime24);
                    $scope.finalDisplayTime = $scope.format12hrsTime($scope.time12format ? $scope.time12format : $scope.currentTime);
                    $scope.hour = $scope.get12HourNumberby12hrString($scope.finalDisplayTime);
                    $scope.minute = $scope.getMinutesby12hrString($scope.finalDisplayTime);
                    $scope.period = $scope.getPeriodby12hrsString($scope.finalDisplayTime);

                    var toggleOnSelection = false;

                    var currentIndexMin = function () {
                        for (var j = 0; j < $scope.minuteOptions.length; j++) {
                            if ($scope.minuteOptions[j] == $scope.minute)
                                return j;
                            else
                                return $scope.minute;
                        }
                    };
                    var currentIndexHr = function () {
                        for (var i = 0; i < $scope.hourOptions.length; i++) {
                            if ($scope.hourOptions[i] == $scope.hour)
                                return i;
                        }
                    };

                    $scope.selectHourValue = function (value) {
                        $scope.hour = value;
                        if (toggleOnSelection) {
                            $scope.selectionMode = !$scope.selectionMode;
                        }
                        $scope.showSelectedDate();
                    };
                    $scope.selectMinValue = function (value) {
                        $scope.minute = value;
                        if (toggleOnSelection) {
                            $scope.selectionMode = !$scope.selectionMode;
                        }
                        $scope.showSelectedDate();
                    };
                    $scope.selectPeriod = function (value) {
                        $scope.period = value;
                        $scope.showSelectedDate();
                    };
                    $scope.showSelectedDate = function () {
                        selectedTime = $scope.hour + ":" + $scope.minute + " " + $scope.period;
                        $scope.finalDisplayTime = $scope.format12hrsTime(selectedTime);
                        $scope.finalSaveTime = $scope.get24hrsTimeby12hrsString($scope.finalDisplayTime);
                    };

                    $scope.lineStyleHr = function () {
                        var angle = "rotate(" + (currentIndexHr() * 30 - 180) + "deg)";
                        return "transform: " + angle + "; -webkit-transform: " + angle;
                    };
                    $scope.lineStyleMin = function () {
                        var angle = "rotate(" + (currentIndexMin() * 6 - 180) + "deg)";
                        return "transform: " + angle + "; -webkit-transform: " + angle;
                    };

                    $scope.cancelpicker = function () {
                        $scope.clockpicker = false;
                        $scope.finalDisplayTime = $scope.oldDisplayTime;
                        $scope.finalSaveTime = $scope.oldSaveTime;
                    };
                    $scope.okpicker = function () {
                        $scope.clockpicker = false;
                        $scope.model = $scope.finalSaveTime;
                    };
                    $scope.toggleClockpicker = function () {
                        $scope.clockpicker = ($scope.clockpicker) ? false : true;
                    };
                }
            };
        }
        )

        .run(["$templateCache", function ($templateCache) {
                $templateCache.put("template/clockpicker.html",
                        "\n" +
                        "<div>" +
                        "<input class='time-input' name='fieldName' ng-model='model' type='hidden' ng-required='frequired'>" +
                        "<div class='time-input-shadow' ng-click='toggleClockpicker()'>{{finalDisplayTime}}</div>" +
                        "<div class='ui-clockpicker' ng-show='clockpicker'>\n" +
                        "  <md-toolbar><div class='ui-clockpicker-selection'>\n" +
                        "    {{finalDisplayTime}}\n" +
                        "  </div></md-toolbar>\n" +
                        "     <ol class='ui-clockpicker-period'>\n" +
                        "       <li ng-repeat='periodOption in periodOptions' " +
                        "         ng-class='{selected: period == periodOption }' " +
                        "         ng-click='selectPeriod(periodOption)'>{{periodOption}}</li>\n" +
                        "       <li class='set-current'><md-button type='button'> Current Time </md-button></li>\n" +
                        "     </ol>\n" +
                        "  <div class='ui-clockpicker-selector' ng-class='{minute: !selectionMode}'>\n" +
                        "  <div class='clock-wrap'>\n" +
                        "   <div class='ui-clockpicker-origin'></div>" +
                        "   <div class='ui-clockpicker-hourline' style='{{lineStyleHr()}}'>"+
                        "   <div class='lineH'></div>"+
                        "    </div>" +
                        "     <div class='ui-clockpicker-minline' style='{{lineStyleMin()}}'>"+
                        "   <div class='lineM'></div>"+
                        "     </div>" +
                        "      <ol class='ui-clockpicker-min'>\n" +
                        "       <li ng-repeat='option in minuteOptions' " +
                        "         ng-class='{selected: minute == option }' " +
                        "         ng-click='selectMinValue(option)'>{{option}}</li>\n" +
                        "     </ol>\n" +
                        "     <ol class='ui-clockpicker-hour'>\n" +
                        "       <li ng-repeat='option in hourOptions' " +
                        "         ng-class='{selected: hour == option}' " +
                        "         ng-click='selectHourValue(option)'>{{option}}</li>\n" +
                        "     </ol>\n" +
                        "     </div>\n" +
                        "  </div>\n" +
                        "  <div layout='row' layout-sm='row' layout-align='center center' layout-wrap>\n" +
                        "  <div flex='60'><md-button class='md-mini md-primary' type='button' ng-click='cancelpicker()'> Cancel </md-button></div>\n" +
                        "  <div flex='40'><md-button class='md-mini md-primary' type='button' ng-click='okpicker()'> OK </md-button></div>\n" +
                        "  </div>\n" +
                        "</div>\n" +
                        "</div>\n" +
                        "");
            }]);