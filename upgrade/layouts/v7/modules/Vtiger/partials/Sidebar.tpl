{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
	<div id="sidebar" class="col-lg-3">
		<div id="appnavigator" class="row app-nav">
			<div class="col-sm-12 col-xs-12 app-switcher-container app-{$SELECTED_MENU_CATEGORY}">
				<a id="menu-toggle" class="menu-toggle" href="#">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<div class="app-trigger">
					<h4 class="active-app-title">{vtranslate("LBL_$SELECTED_MENU_CATEGORY",$MODULE)}</h4>
					<i class="fa fa-chevron-down arrow-down"></i>
				</div>
			</div>
		</div>

		<div id="modnavigator" class="row module-nav">
			<div class="hidden-xs hidden-sm mod-switcher-container">
				{include file="modules/Vtiger/partials/Menubar.tpl"}
				{include file="modules/Vtiger/partials/SidebarEssentials.tpl"}
			</div>
		</div>
		<div class="app-menu hide" id="app-menu">
			<div class="container-fluid">
				<div class="row moduleList">
					{assign var=HOME_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Home')}
					<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
						<div class="menu-item" style="background: #C5EFF7;" data-url="{$HOME_MODULE_MODEL->getDefaultUrl()}">Home</div>
					</div>
					{assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Calendar')}
					<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
						<div class="menu-item" style="background:#C8F7C5;" data-url="{$CALENDAR_MODULE_MODEL->getDefaultUrl()}">Calendar</div>
					</div>
					{assign var=REPORT_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Reports')}
					<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
						<div class="menu-item" style="background: #FDE3A7;"data-url="{$REPORT_MODULE_MODEL->getDefaultUrl()}">Reports</div>
					</div>
					{if $USER_MODEL->isAdminUser()}
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
							<div class="menu-item" style="background: #ECF0F1;" data-url="index.php?module=Vtiger&parent=Settings&view=Index">{vtranslate('LBL_CRM_SETTINGS',$MODULE)}</div>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
							<div class="menu-item" style="background: #A2DED0;">Integrations</div>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-4 col-xs-12">
							<div class="menu-item" style="background: #fdff8e;">Market Place</div>
						</div>
					{/if}
				</div>
				<hr/>
				<div class="row app-list">
					{assign var=APP_GROUPED_MENU value=$MENU_STRUCTURE->getMenuGroupedByParent()}
					{assign var=APP_LIST value=$MENU_STRUCTURE->getMore()}
					{foreach key=APP_NAME item=APP_MOD_LIST from=$APP_LIST}
						{if $APP_NAME eq 'ANALYTICS'} {continue}{/if}
						<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
							{foreach item=APP_MENU_MODEL from=$APP_GROUPED_MENU.$APP_NAME}
								{assign var=FIRST_MENU_MODEL value=$APP_MENU_MODEL}
								{break}
							{/foreach}
							<div class="menu-item app-item app-{$APP_NAME}" data-app-name="{$APP_NAME}"  data-default-url="{$FIRST_MENU_MODEL->getDefaultUrl()}">
								<span class="fa {$APP_IMAGE_MAP.$APP_NAME}"></span>
								<div>{vtranslate("LBL_$APP_NAME")}</div>
							</div>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
	</div>
{/strip}