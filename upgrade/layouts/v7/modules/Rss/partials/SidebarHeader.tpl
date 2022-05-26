{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div id="appnavigator" class="col-sm-12 col-xs-12 app-switcher-container app-{$SELECTED_MENU_CATEGORY}">
    <a id="menu-toggle" class="menu-toggle" href="#">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>
    <div class="app-trigger">
        <h4 class="active-app-title">{strtoupper(vtranslate($MODULE, $MODULE))}</h4>
        <i class="fa fa-chevron-down arrow-down"></i>
    </div>
</div>
    
{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}