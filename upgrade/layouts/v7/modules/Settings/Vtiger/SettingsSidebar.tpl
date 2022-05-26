{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="settings-sidebar" id="settings_sidebar" >
		<div class="sidebar-container lists-menu-container">
			<h3 class="lists-header">
				<a style="color: white; cursor: default;" href="index.php?module=Vtiger&parent=Settings&view=Index">
					{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a>
			</h3>
			<hr>
			<div class="settings-menu">
				{foreach item=MENU from=$SETTINGS_MENUS}
					<div class="col-sm-12 settings-flip show_hide" style="width:100% !important">
						<span class="col-sm-10 col-xs-10" style="font-size: 18px;color: #fff">
							{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}
						</span>
						<span class="col-sm- col-xs-2">
							<i class="fa fa-chevron-down"></i> 
						</span>
					</div>
					<div class="col-sm-12 settings-menu-items slidingDiv">
						{foreach item=MENUITEM from=$MENU->getMenuItems()}
							<a href="{$MENUITEM->getUrl()}" data-id="{$MENUITEM->getId()}" data-menu-item="true" >{vtranslate($MENUITEM->get('name'), $QUALIFIED_MODULE)}</a><br>
						{/foreach}
					</div>
				{/foreach}
			</div>
		</div>
	</div>
{/strip}
