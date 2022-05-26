{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
	<div class="settingsIndexPage col-lg-12 col-md-12 col-sm-12">
		<div><h4>{vtranslate('LBL_SUMMARY',$MODULE)}</h4></div>
		<hr>
		<div class="row">
			<span class="col-lg-4 col-md-4 col-sm-4 settingsSummary">
				<a href="index.php?module=Users&parent=Settings&view=List">
					<h2 class="summaryCount">{$USERS_COUNT}</h2> 
					<p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_ACTIVE_USERS',$MODULE)}</p> 
				</a>
			</span>
			<span class="col-lg-4 col-md-4 col-sm-4 settingsSummary">
				<a href="index.php?module=Workflows&parent=Settings&view=List&parentblock=LBL_AUTOMATION">
					<h2 class="summaryCount">{$ACTIVE_WORKFLOWS}</h2> 
					<p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_WORKFLOWS_ACTIVE',$MODULE)}</p> 
				</a>
			</span>
			<span class="col-lg-4 col-md-4 col-sm-4 settingsSummary">
				<a href="index.php?module=ModuleManager&parent=Settings&view=List">
					<h2 class="summaryCount">{$ACTIVE_MODULES}</h2> 
					<p class="summaryText" style="margin-top:20px;">{vtranslate('LBL_MODULES',$MODULE)}</p>
				</a>
			</span>
		</div>
		<br><br>&nbsp;
		<h4>{vtranslate('LBL_SETTINGS_SHORTCUTS',$MODULE)}</h4>
		<hr>
		<div id="settingsShortCutsContainer" style="min-height: 500px;">
			<div class="col-lg-12">
				{assign var=COUNTER value=0}
				{foreach item=SETTINGS_SHORTCUT from=$SETTINGS_SHORTCUTS name=shortcuts}
					{if $COUNTER eq 4}
						</div><div class="col-lg-12">
						{assign var=COUNTER value=1}
					{else}
						{assign var=COUNTER value=$COUNTER+1}
					{/if}
					{include file='SettingsShortCut.tpl'|@vtemplate_path:$MODULE}
				{/foreach}
			</div>
		</div>
	</div>
{/strip}