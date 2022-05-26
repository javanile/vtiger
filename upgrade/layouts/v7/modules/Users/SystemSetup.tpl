{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<html lang="en">
		<head>
			<title>Vtiger CRM Setup</title>
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link REL="SHORTCUT ICON" HREF="layouts/v7/skins/images/favicon.ico">
			<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap.css" type="text/css" media="screen" />
			<link rel="stylesheet" href="libraries/bootstrap/css/bootstrap-responsive.css" type="text/css" media="screen">
			<style type="text/css">
				{literal}
					body{
						font-family: "Lucida Grande", Tahoma, Verdana;
						background: #F4FAFC;
						color : #555;
						font-size: 13px;
						min-height: 98%;
					}
					p{
						font-family: "Lucida Grande", Tahoma,Verdana;
						font-size: 14px;
					}
					.offset2{
						position: relative; 
						left: 17% !important;
					}
					div{
						-webkit-box-sizing: border-box; /* Safari/Chrome, other WebKit */
						-moz-box-sizing: border-box;/* Firefox, other Gecko */
						box-sizing: border-box; /* Opera/IE 8+ */
					}
					hr{
						border: 1px solid #bbb;
						border-bottom-color: #eee;
						border-right: 0;
						border-left: 0;
						margin-top: 13px;
					}
					.page-container{
						width:70%;
						margin: 10px auto;
						min-width: 1100px;
					}
					.main-container{
						background: #fff;
						border: 1px solid #ddd;
						border-radius: 3px;
						box-shadow: 0 0 5px #ccc;
						min-height: 375px;
						*padding: 0 15px;
					}
					.logo{
						padding: 15px 0 ;
					}

					.inner-container{
						padding: 15px 2%;
						*padding: 15px 0 0;
					}

					.group-container{
						background: #eee;
						border: 1px solid #ddd;
						border-radius: 3px;
						margin-bottom: 25px; 
						padding: 15px;
						position: relative;
						height: 215px !important;
						*margin: 0px !important;
						*padding: 5px 0;
					}
					.selectedContainer{
						background: #D9E9EE;
					}
					.unSelectedContainer{
						background: #eee;
					}

					.group-container > .row-fluid > .span9{
						min-height: 162px;
					}
					.group-heading input{
						display: inline-block;
						float: left;
					}
					.group-heading h4{
						color: #2c69a7;
						text-shadow: 0px 0px 1px #BBB;
						display: inline-block;
						font-size: 16px;
						vertical-align: bottom;
						margin-left: 10px\9;
						margin-top: 8px\9;
					}
					.group-heading .basicChkbox{
						float:right;
						width:24px;
						height:24px;
						background: url("layouts/v7/skins/images/check_radio_sprite.png") no-repeat 0 0;
						opacity: 0.65;
					}
					.group-desc{
						padding-bottom:9px;
						min-height: 130px;
					}
					.module_list{
						background: #146bae;
						border-radius: 3px 0;
						right:0;
						bottom: 0;
						position: absolute;
						color: #fff;
						font-weight: bold;
						padding: 3px 10px;
						text-shadow: 0 0 1px #000;
						z-index: 605;
					}
					.module_list a{
						cursor: pointer;
						text-decoration: none;
						color: #fff;
					}
					.reveal_modules{
						position: absolute;
						height:0;
						width: 0;
						z-index: 600;
						background: #000;
						opacity: 0;
						top: 100%;
						left:100%;
						bottom: 0;
						right:0;
						color: #fff;
						visibility: hidden;
						border-radius: 3px;
					}
					/** temporary**/
					.reveal_modules > div{
						padding: 15px 20px;
					}
					/****/
					.group-container p{
						display: inline-block;
					}
					.reveal_modules > div p{
						margin: 0  30px 3px 0;
						vertical-align: top;
						display: inline-block;
					}
					.button-container a{
						text-decoration: none;
					}
					.button-container{
						float: right;
					}
					.button-container .btn{
						margin-left: 15px;
						min-width: 100px;
						font-weight: bold;
					}
					/* hide the checkboxes and radios */
					input[type="checkbox"],input[type="radio"]
					{
						opacity: 0;
						position: absolute;
						height: 24px;
						width: 24px;
						margin:0;
						margin-top: 2px\9;
					}
					label{
						display: inline-block;
					}
					/* Inline div for placeholder to the checkbox*/
					.chkbox, .radiobtn {
						display: inline;
						margin-right: 20px\9;
					}
					/* Fix for IE */
					:root .chkbox, :root .radiobtn {
						margin-right: 0px\9;
					}
					/* we use generated content to create a placeholder for the checkbox*/
					input[type="checkbox"] + div::before,
					input[type="radio"] + div::before
					{
						content: "";
						display: inline-block;
						width: 24px;
						height: 24px;
						margin-right: 6px;
						background: url("layouts/v7/skins/images/check_radio_sprite.png") no-repeat 0 -24px;
						vertical-align: middle;
					}
					/* hover checkbox (unselected state only) */
					input[type="checkbox"]:not(:checked):hover + div::before
					{
						background-position: 0 -96px;
					}
					/* selected checkbox */
					input[type="checkbox"]:checked + div::before
					{
						background-position: 0 0;
					}
				{/literal}
			</style>
		</head>

		<body>
			<script type="text/javascript" src="libraries/jquery/jquery.min.js"></script>
			<script>
				{literal}
					jQuery(document).ready(function () {
						var timeout;
						jQuery('.module_list').mouseenter(function (e) {
							thisDiv = jQuery(this);
							timeout = window.setTimeout(function () {
								hoverHeight = thisDiv.parent(".group-container").height() + 30;
								hoverWidth = thisDiv.parent(".group-container").width() + 30;
								thisDiv.siblings('div.reveal_modules').css({'visibility': 'visible', opacity: '0.9'});
								thisDiv.siblings('div.reveal_modules').stop(true, true).animate({left: '0', top: '0', height: hoverHeight, width: hoverWidth}, 300, function () {
									jQuery(this).css({'z-index': 610});
								});
							}, 250);
						}).parent('div.group-container').mouseleave(function (e) {
							jQuery(this).children('div.reveal_modules').stop(true, true).animate({left: '100%', top: '100%', height: 0, width: 0, opacity: 0}, 200, function () {
								jQuery(this).css({'z-index': 600, 'visibility': 'hidden'});
							});
							timeout = window.clearTimeout(timeout);
						});
						jQuery(".selectAll").click(function () {
							if (jQuery(this).is(':checked')) {
								jQuery("input[type='checkbox']").attr('checked', true);
								jQuery('.group-container').not(jQuery('.js-basePack')).removeClass('unSelectedContainer').addClass('selectedContainer');

							} else {
								jQuery("input[type='checkbox']").attr('checked', false);
								jQuery('.group-container').not(jQuery('.js-basePack')).removeClass('selectedContainer').addClass('unSelectedContainer');
							}
						});
						jQuery("input[type='checkbox']").not(jQuery('.selectAll')).click(function () {
							if (jQuery(this).is(':checked')) {
								jQuery(this).closest('.group-container').removeClass('unSelectedContainer').addClass('selectedContainer');
							} else {
								jQuery(this).closest('.group-container').removeClass('selectedContainer').addClass('unSelectedContainer');
								jQuery(".selectAll").attr('checked', false);
							}
							if (jQuery("input[type='checkbox']:checked").size() === jQuery("input[type='checkbox']").size() - 1) {
								jQuery(".selectAll").attr('checked', true);
							}
						});
					});
				{/literal}
			</script>
			<div class="container-fluid page-container">
				<div class="row-fluid">
					<div class="span6">
						<div class="logo">
							<img src="test/logo/vtiger-crm-logo.png" alt="Vtiger Logo"/>
						</div>
					</div>
					<div class="span6"></div>
				</div>
				<div class="row-fluid main-container">
					<div class="span12 inner-container">
						<div class="row-fluid">
							<div class="span8">
								<h3 style="display: inline-block;float:left;"> What would you like to use Vtiger CRM for? </h3> 
								<p style="display: inline-block;float:left;margin-top:4px;">&nbsp;&nbsp;(Select features you want to enable)</p>
							</div>
							<div class="span4">
								<div class="pull-right"><label for="checkbox-1" style="vertical-align:bottom;margin:0;margin-top:8px\9;margin-left:10px\9;font-size:15px;"><strong> Select All</strong></label> <input type="checkbox" name="selectAll" class="selectAll"/><div class="chkbox"></div></div>
							</div>
						</div>
						<hr>
						<form id="SystemSetup" class="form" method="POST" action="index.php?module=Users&action=SystemSetupSave">
							{assign var=COUNTER value=0}
							{foreach from=$PACKAGES_LIST key=PACKAGE_NAME item=PACKAGE_INFO}
								{if $COUNTER eq 0 || $COUNTER mod 2 eq 1}
									<div class="row-fluid">
									{/if}
									<div class="group-container {if $COUNTER eq 0} span12 selectedContainer js-basePack {else} span6 {/if}">
										<div class="module_list"><a> View Modules </a></div>
										<div class="row-fluid">
											<div class="{if $COUNTER eq 0} span2 {else} span3 {/if}">
												<img src="layouts/v7/skins/images/{$PACKAGE_INFO.imageName}" alt="Vtiger Logo"/>
											</div>
											<div class="{if $COUNTER eq 0} span10 {else} span9 {/if}">
												<div class="group-heading">

													<h4>{$PACKAGE_INFO.label}</h4>
													{if $COUNTER neq 0}
														<div class="pull-right">
															<input type="checkbox" name="packages[{$PACKAGE_NAME}]"/><div class="chkbox"></div>
														</div>
													{else}
														<div class="basicChkbox"></div>
													{/if}
												</div>
												<div class="group-desc">
													<br>
													<p>{$PACKAGE_INFO.description}</p>
												</div>
											</div>
										</div>
										{if $COUNTER eq 0}		
											<div class="row-fluid">
												<div class="offset2 span10">
													<input type="hidden" name="packages[{$PACKAGE_NAME}]" value="on"/>
													<strong> This feature will be installed by default </strong></label>
												</div>
											</div>
										{/if}
										<div class="reveal_modules">
											<div>
												{* Vtranslate did not supported here *}
												<strong>These Modules will be enabled for this feature</strong><hr>
												<div>	
													{assign var=ITEMCOUNTER value=0}
													{foreach from=$PACKAGE_INFO.modules item=MODULE_NAME}
														<p style="width:25%">{$MODULE_NAME}</p>
														{assign var=ITEMCOUNTER value=$ITEMCOUNTER+1}
														{if $ITEMCOUNTER eq 3}
															<br>
															{assign var=ITEMCOUNTER value=0}
														{/if}
													{/foreach}
												</div>
											</div>
										</div>
									</div>
									{assign var=COUNTER value=$COUNTER+1}
									{if $COUNTER eq 0 || $COUNTER mod 2 eq 1}
									</div>
								{/if}
							{/foreach}
							<div class="row-fluid">
								<div class="span12">
								</div>
							</div>
							<div class="row-fluid">
								<div class="span9">
									<strong>Note: </strong> You can Enable/Disable modules from module manager later   
								</div>
								<div class="span3">
									<div class="button-container">
										<button type="submit" class="btn btn-large btn-primary next" value="Next"> Next </button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</body>
	</html>  
{/strip}