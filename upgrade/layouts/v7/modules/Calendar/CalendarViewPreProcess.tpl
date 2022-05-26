{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{include file="modules/Vtiger/partials/Topbar.tpl"}
{strip}
<div class="container-fluid app-nav">
	<div class="row">
		{include file="modules/Calendar/partials/SidebarHeader.tpl"}
		{include file="CalendarHeader.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
</nav>
	<div id='overlayPageContent' class='fade modal overlayPageContent content-area overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>
		<div class="data">
		</div>
		<div class="modal-dialog">
		</div>
	</div>
	<div class="main-container">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div id="modnavigator" class="module-nav calendar-navigator clearfix">
			<div class="hidden-xs hidden-sm mod-switcher-container">
				{include file="modules/Calendar/partials/Sidebar.tpl"}
			</div>
		</div>
		<div id="sidebar-essentials" class="sidebar-essentials {if $LEFTPANELHIDE eq '1'} hide {/if}">
			{include file="partials/SidebarEssentials.tpl"|vtemplate_path:$MODULE}
		</div>
		<div class="CalendarViewPageDiv content-area {if $LEFTPANELHIDE eq '1'} full-width {/if}" id="CalendarViewContent">
{/strip}
