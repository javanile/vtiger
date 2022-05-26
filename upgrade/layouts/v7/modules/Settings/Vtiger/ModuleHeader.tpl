{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
	<div class="col-sm-12 col-xs-12 module-action-bar clearfix coloredBorderTop">
		<div class="module-action-content clearfix">
			<div class="col-lg-7 col-md-7">
				{if $USER_MODEL->isAdminUser()}
					<a title="{vtranslate('Home', $MODULE)}" href='index.php?module=Vtiger&parent=Settings&view=Index'>
						<h4 class="module-title pull-left text-uppercase">{vtranslate('LBL_HOME', $MODULE)} </h4>
					</a>
					&nbsp;<span class="fa fa-angle-right pull-left {if $VIEW eq 'Index' && $MODULE eq 'Vtiger'} hide {/if}" aria-hidden="true" style="padding-top: 12px;padding-left: 5px; padding-right: 5px;"></span>
				{/if}
				{if $MODULE neq 'Vtiger' or $smarty.request.view neq 'Index'}
					{if $ACTIVE_BLOCK['block']}
						<span class="current-filter-name filter-name pull-left">
							{vtranslate($ACTIVE_BLOCK['block'], $QUALIFIED_MODULE)}&nbsp;
							<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;
						</span>
					{/if}
					{if $MODULE neq 'Vtiger'}
						{assign var=ALLOWED_MODULES value=","|explode:'Users,Profiles,Groups,Roles,Webforms,Workflows'}
						{if $MODULE_MODEL and $MODULE|in_array:$ALLOWED_MODULES}
							{if $MODULE eq 'Webforms'}
								{assign var=URL value=$MODULE_MODEL->getListViewUrl()}
							{else}
								{assign var=URL value=$MODULE_MODEL->getDefaultUrl()}
							{/if}
							{if $URL|strpos:'parent' eq ''}
								{assign var=URL value=$URL|cat:'&parent='|cat:$smarty.request.parent}
							{/if}
						{/if}
						<span class="current-filter-name settingModuleName filter-name pull-left">
							{if $smarty.request.view eq 'Calendar'}
								{if $smarty.request.mode eq 'Edit'}
									<a href="{"index.php?module="|cat:$smarty.request.module|cat:'&parent='|cat:$smarty.request.parent|cat:'&view='|cat:$smarty.request.view}">
										{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}
									</a>&nbsp;
									<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;
									{vtranslate('LBL_EDITING', $MODULE)} :&nbsp;{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_OF',$QUALIFIED_MODULE)}&nbsp;{$USER_MODEL->getName()}
								{else}
									{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}&nbsp;<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;{$USER_MODEL->getName()}
								{/if}
							{else if $smarty.request.view neq 'List' and $smarty.request.module eq 'Users'}
								{if $smarty.request.view eq 'PreferenceEdit'}
									<a href="{"index.php?module="|cat:$smarty.request.module|cat:'&parent='|cat:$smarty.request.parent|cat:'&view=PreferenceDetail&record='|cat:$smarty.request.record}">
										{vtranslate($ACTIVE_BLOCK['block'], $QUALIFIED_MODULE)}&nbsp;
									</a>
									<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;
									{vtranslate('LBL_EDITING', $MODULE)} :&nbsp;{$USER_MODEL->getName()}
								{else if $smarty.request.view eq 'Edit' or $smarty.request.view eq 'Detail'}
									<a href="{$URL}">
									{if $smarty.request.extensionModule}{$smarty.request.extensionModule}{else}{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}{/if}&nbsp;
									</a>
									<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;
									{if $smarty.request.view eq 'Edit'}
										{if $RECORD}
											{vtranslate('LBL_EDITING', $MODULE)} :&nbsp;{$RECORD->getName()}
										{else}
											{vtranslate('LBL_ADDING_NEW', $MODULE)}
										{/if}
									{else}
										{$RECORD->getName()}
									{/if}
								{else}
									{$USER_MODEL->getName()}
								{/if}
							{else if $URL and $URL|strpos:$smarty.request.view eq ''}
								<a href="{$URL}">
								{if $smarty.request.extensionModule}
									{$smarty.request.extensionModule}
								{else}
									{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}
								{/if}
								</a>&nbsp;
								<span class="fa fa-angle-right" aria-hidden="true"></span>&nbsp;
								{if $RECORD}
									{if $smarty.request.view eq 'Edit'}
										{vtranslate('LBL_EDITING', $MODULE)} :&nbsp;
									{/if}
									{$RECORD->getName()}
								{/if}
							{else}
								&nbsp;{if $smarty.request.extensionModule}{$smarty.request.extensionModule}{else}{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}{/if}
							{/if}
						</span>
					{else}
						{if $smarty.request.view eq 'TaxIndex'}
							{assign var=SELECTED_MODULE value='LBL_TAX_MANAGEMENT'}
						{elseif $smarty.request.view eq 'TermsAndConditionsEdit'}
							{assign var=SELECTED_MODULE value='LBL_TERMS_AND_CONDITIONS'}
						{else}
							{assign var=SELECTED_MODULE value=$ACTIVE_BLOCK['menu']}
						{/if}
						<span class="current-filter-name filter-name pull-left" style='width:50%;'><span class="display-inline-block">{vtranslate({$PAGETITLE}, $QUALIFIED_MODULE)}</span></span>
					{/if}
				{/if}
			</div>
			<div class="col-lg-5 col-md-5 pull-right">
				<div id="appnav" class="navbar-right">
					<ul class="nav navbar-nav">
						{foreach item=BASIC_ACTION from=$MODULE_BASIC_ACTIONS}
							<li>
								{if $BASIC_ACTION->getLabel() == 'LBL_IMPORT'}
									<button id="{$MODULE}_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}" type="button" class="btn addButton btn-default module-buttons" 
										{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
											onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
										{else} 
											onclick="Vtiger_Import_Js.triggerImportAction('{$BASIC_ACTION->getUrl()}')"
										{/if}>
										<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>&nbsp;&nbsp;
										{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
									</button>
								{else}
									<button type="button" class="btn addButton btn-default module-buttons" 
										id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($BASIC_ACTION->getLabel())}"
										{if stripos($BASIC_ACTION->getUrl(), 'javascript:')===0}
											onclick='{$BASIC_ACTION->getUrl()|substr:strlen("javascript:")};'
										{else} 
											onclick='window.location.href="{$BASIC_ACTION->getUrl()}"'
										{/if}>
										<div class="fa {$BASIC_ACTION->getIcon()}" aria-hidden="true"></div>
										&nbsp;&nbsp;{vtranslate($BASIC_ACTION->getLabel(), $MODULE)}
									</button>
								{/if}
							</li>
						{/foreach}
						{if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
							{if empty($QUALIFIEDMODULE)} 
								{assign var=QUALIFIEDMODULE value=$MODULE}
							{/if}
							<li>
								<div class="settingsIcon">
									<button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_SETTINGS', $MODULE)}">
										<span class="fa fa-wrench" aria-hidden="true"></span>&nbsp; <span class="caret"></span>
									</button>
									<ul class="detailViewSetting dropdown-menu">
										{foreach item=SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
											<li id="{$MODULE}_setings_lisview_advancedAction_{$SETTING->getLabel()}">
												<a	{if stripos($SETTING->getUrl(), 'javascript:') === 0}
														onclick='{$SETTING->getUrl()|substr:strlen("javascript:")};'
													{else}
														onclick='window.location.href="{$SETTING->getUrl()}"'
													{/if}>
													{vtranslate($SETTING->getLabel(), $QUALIFIEDMODULE)}</a>
											</li>
										{/foreach}
									</ul>
								</div>
							</li>
						{/if}

						{assign var=RESTRICTED_MODULE_LIST value=['Users', 'EmailTemplates']}
						{if $LISTVIEW_LINKS['LISTVIEWBASIC']|@count gt 0 and !in_array($MODULE, $RESTRICTED_MODULE_LIST)}
							{if empty($QUALIFIED_MODULE)} 
								{assign var=QUALIFIED_MODULE value='Settings:'|cat:$MODULE}
							{/if}
							{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
								{if $MODULE eq 'Users'} {assign var=LANGMODULE value=$MODULE} {/if}
								<li>
									<button class="btn btn-default addButton module-buttons"
										id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" 
										{if $MODULE eq 'Workflows'}
											onclick='Settings_Workflows_List_Js.triggerCreate("{$LISTVIEW_BASICACTION->getUrl()}&mode=V7Edit")'
										{else}
											{if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}
												onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'
											{else}
												onclick='window.location.href = "{$LISTVIEW_BASICACTION->getUrl()}"'
											{/if}
										{/if}>
										{if $MODULE eq 'Tags'}
											<i class="fa fa-plus"></i>&nbsp;&nbsp;
											{vtranslate('LBL_ADD_TAG', $QUALIFIED_MODULE)}
										{else}
											{if $LISTVIEW_BASICACTION->getIcon()}
												<i class="{$LISTVIEW_BASICACTION->getIcon()}"></i>&nbsp;&nbsp;
											{/if}
											{vtranslate($LISTVIEW_BASICACTION->getLabel(), $QUALIFIED_MODULE)}
										{/if}
									</button>
								</li>
							{/foreach}
						{/if}
					</ul>
				</div>
			</div>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>
{/strip}
