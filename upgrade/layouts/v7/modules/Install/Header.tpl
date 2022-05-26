{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	<!DOCTYPE html>
	<html>
		<head>
			<title>{vtranslate($PAGETITLE, $MODULE_NAME)}</title>
			<link rel="SHORTCUT ICON" href="layouts/v7/skins/images/favicon.ico">
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/bootstrap.min.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/todc-bootstrap.min.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/font-awesome/css/font-awesome.min.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'/>
			<link type='text/css' rel='stylesheet' href='libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css'/>
			<link type='text/css' rel='stylesheet' href='layouts/v7/lib/vt-icons/style.css'/>

			{if strpos($V7_THEME_PATH,".less")!== false}
				<link type="text/css" rel="stylesheet/less" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
			{else}
				<link type="text/css" rel="stylesheet" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
			{/if}

			{foreach key=index item=cssModel from=$STYLES}
				<link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
			{/foreach}

			{* For making pages - print friendly *}
			<style type="text/css">
				@media print {
				.noprint { display:none; }
			}
			</style>

			<script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
			<script type="text/javascript">
				var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}" };
				{if $EXTENSION_MODULE}
					var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
				{/if}
				var _USERMETA;
				{if $CURRENT_USER_MODEL}
					_USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}" };
				{/if}
			</script>
		</head>
		 {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
		<body style="font-size: 13px !important;" data-skinpath="{Vtiger_Theme::getBaseThemePath()}" data-language="{$LANGUAGE}" data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}" data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
			data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}" data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}">
			<div id="page">
				<div id="pjaxContainer" class="hide noprint"></div>
{/strip}