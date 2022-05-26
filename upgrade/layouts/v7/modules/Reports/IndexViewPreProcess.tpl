{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    {include file="modules/Vtiger/partials/Topbar.tpl"}

    <div class="container-fluid app-nav">
        <div class="row">
            {include file="modules/Reports/partials/SidebarHeader.tpl"}
            {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
</nav>
<div class="clearfix main-container">
    <div>
        <div class="editViewPageDiv viewContent">
            <div class="reports-content-area">
{/strip}