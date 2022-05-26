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
	<span id="shortcut_{$SETTINGS_SHORTCUT->getId()}" data-actionurl="{$SETTINGS_SHORTCUT->getPinUnpinActionUrl()}" class="col-lg-3 contentsBackground well cursorPointer moduleBlock" data-url="{$SETTINGS_SHORTCUT->getUrl()}" style="height: 100px; width: 23.5%;">
		<div>
			<span>
				<b class="themeTextColor">{vtranslate($SETTINGS_SHORTCUT->get('name'),$MODULE)}</b>
			</span>
			<span class="pull-right">
				<button data-id="{$SETTINGS_SHORTCUT->getId()}" title="{vtranslate('LBL_REMOVE',$MODULE)}" type="button" class="unpin close hiden"><i class="fa fa-close"></i></button>
			</span>
		</div>
		<div>
			{if $SETTINGS_SHORTCUT->get('description') && $SETTINGS_SHORTCUT->get('description') neq 'NULL'}
				{vtranslate($SETTINGS_SHORTCUT->get('description'),$MODULE)}
			{/if}
		</div>
	</span>
{/strip}
