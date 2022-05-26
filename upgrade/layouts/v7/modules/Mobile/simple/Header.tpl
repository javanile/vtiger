{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

<!doctype html>
<html ng-app="mobileapp">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title>Vtiger</title>
		<link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon"> 
		<link rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-material.min.css">
		<!--link rel="stylesheet" href="https://fonts.googleapis.com/css?family=RobotoDraft:300,400,500,700,400italic"-->

		{* Include desired styles injected *}
		{if $_styles}
			{foreach item=_style from=$_styles}
				<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/{$_style}">
			{/foreach}
		{/if}
		{* End *}
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/libs/md-icons/css/materialdesignicons.min.css">
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/libs/md-datepicker/angular-datepicker.min.css">
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/libs/Vtiger-icons/style.css">
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/css/application.css">
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/css/style.css">
		{*Date-time-picker*}
		<link type="text/css" rel="stylesheet" href="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-material-datetimepicker/css/material-datetimepicker.min.css">

		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/jquery/date.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/jquery/jquery2.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-touch.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-animate.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-aria.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-material.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-touch.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/jquery/purl.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/md-datepicker/angular-datepicker.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/md-datepicker/angular-clockpicker.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/Vtiger/js/application.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/Vtiger/js/Vtiger.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/Vtiger/js/Utils.js"></script>

		{*moment-js*}
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/moment/moment.min.js"></script>
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/moment/moment-timezone.min.js"></script>
		{*Date-time-picker*}
		<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/resources/libs/angularjs/angular-material-datetimepicker/js/angular-material-datetimepicker.js"></script>

		{* Include desired script injected *}
		{if $_scripts}
			{foreach item=_script from=$_scripts}
				<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/{$_script}"></script>
			{/foreach}
		{/if}
		{* End *}

	</head>
	{literal}
		<body ng-controller="VtigerBodyController" ng-init="init();" ng-cloak md-theme="{{dynamicTheme}}" md-theme-watch>
	{/literal}
