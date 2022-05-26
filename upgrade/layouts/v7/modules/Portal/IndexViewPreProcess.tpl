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
			{include file="modules/Portal/SidebarHeader.tpl"}
			{include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
		</div>
	</div>
	</nav>
	<div id='overlayPageContent' class='fade modal overlayPageContent content-area overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>
		<div class="data">
		</div>
		<div class="modal-dialog">
		</div>
	</div>  
	<div class="main-container main-container-{$MODULE}">
		<div id="modnavigator" class="module-nav">
			<div class="hidden-xs hidden-sm mod-switcher-container">
				{include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
			</div>
		</div>
		<div class="listViewPageDiv content-area full-width" id="listViewContent">
{/strip}