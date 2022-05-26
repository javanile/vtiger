{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-{$SELECTED_MENU_CATEGORY}">
	<div class="row" title="{strtoupper(vtranslate("LBL_CALENDAR",$MODULE))}">
		<span class="app-indicator-icon fa fa-calendar"></span>
	</div>
</div>

{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}