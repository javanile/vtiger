{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{include file="Header.tpl"|vtemplate_path:'Install'}
	<div class="container-fluid page-container">
		<div class="row">
			<div class="col-lg-6">
				<div class="logo">
					<img src="{'logo.png'|vimage_path}" alt="Vtiger Logo"/>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="head pull-right">
					<h3> {vtranslate('LBL_MIGRATION_WIZARD', $MODULE)}</h3>
				</div>
			</div>
		</div>
		<div class="row main-container">
			<div class="col-lg-12 inner-container">
				<div class="row">
					<div class="col-lg-10">
						<h4> {vtranslate('LBL_MIGRATION_COMPLETED', $MODULE)} </h4> 
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-lg-5 welcome-image">
						<img src="{vimage_path('migration_screen.png')}" alt="Vtiger Logo" style="width:80%;"/>
					</div>
					<div class="col-lg-1"></div>
					<div class="col-lg-6">
						<br>
						<h5>{vtranslate('LBL_MIGRATION_COMPLETED_SUCCESSFULLY', $MODULE)}</h5><br>
						{vtranslate('LBL_RELEASE_NOTES', $MODULE)}<br>
						{vtranslate('LBL_CRM_DOCUMENTATION', $MODULE)}<br>
						{vtranslate('LBL_TALK_TO_US_AT_FORUMS', $MODULE)}<br>
						{vtranslate('LBL_DISCUSS_WITH_US_AT_BLOGS', $MODULE)}<br><br>
						Connect with us &nbsp;&nbsp;
						<a href="https://www.facebook.com/vtiger" target="_blank"><img src="layouts/v7/skins/images/facebook.png"></a>&nbsp;&nbsp;
						<a href="https://twitter.com/vtigercrm" target="_blank"><img src="layouts/v7/skins/images/twitter.png"></a>&nbsp;&nbsp;
					</div>
				</div>
				<div class="button-container col-lg-12">
					<input type="button" onclick="window.location.href='index.php'" class="btn btn-default btn-primary pull-right" value="{vtranslate('Finish', $MODULE)}" style="margin-left: 0px;"/>
				</div>
			</div>
		</div>
	</div>
{/strip}
