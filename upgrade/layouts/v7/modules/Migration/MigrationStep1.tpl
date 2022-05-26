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
					<img src="{'logo.png'|vimage_path}"/>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="head pull-right">
					<h3>{vtranslate('LBL_MIGRATION_WIZARD', $MODULE)}</h3>
				</div>
			</div>
		</div>
		<div class="row main-container">
			<div class="col-lg-12 inner-container">
				<div id="running" class="alignCenter">
					<br><br><br><br><br>
					<h4> {vtranslate('LBL_WAIT',$MODULE)} </h4><br>
					<img src="{vimage_path('install_loading.gif')}"/>
					<h5> {vtranslate('LBL_INPROGRESS',$MODULE)} </h5>
				</div>
				<div id="success" class="hide">
					<div class="row">
						<div class="col-lg-10">
							<h4> {vtranslate('LBL_DATABASE_CHANGE_LOG',$MODULE)} </h4>
						</div>
					</div><hr>
				</div>
				<div id="showDetails" class="hide" style="max-height: 350px; overflow: auto; padding: 10px; border: 1px solid #ddd;"></div><br>
				<div id="nextButton" class="button-container col-lg-12 hide">
					<form action='index.php' method="POST">
						<input type="hidden" name="module" id="module" value="Migration">
						<input type="hidden" name="view" id="view" value="Index">
						<input type="hidden" name="mode" value="step2">
						<input type="submit" class="btn btn-default btn-primary pull-right" value="{vtranslate('Next', $MODULE)}"/>
					</form>
				</div>
			</div>
		</div>
	</div>
{/strip}