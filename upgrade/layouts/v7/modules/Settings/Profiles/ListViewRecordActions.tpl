{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
<!--LIST VIEW RECORD ACTIONS-->

<div class="table-actions">
	{assign var=RECORD_LINKS value=$LISTVIEW_ENTRY->getRecordLinks()}
	{assign var=RECORD_LINK_URL value=[]}
	{foreach item=RECORD_LINK key=key from=$LISTVIEW_ENTRY->getRecordLinks()}
		{$RECORD_LINK_URL.$key = $RECORD_LINK->getUrl()}
	{/foreach}

	<span>
		<a href="{$RECORD_LINK_URL[1]}" title="{vtranslate('LBL_DUPLICATE', $MODULE)}"><i class="fa fa-copy"></i></a>
	</span>
	<span class="more dropdown action">
		<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
			<i class="fa fa-ellipsis-v icon"></i></span>
		<ul class="dropdown-menu">
			<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="{$RECORD_LINK_URL[0]}" title="{vtranslate('LBL_EDIT', $MODULE)}">{vtranslate('LBL_EDIT', $MODULE)}</a></li>
			<li> <a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0)" onclick="{$RECORD_LINK_URL[2]}" title="{vtranslate('LBL_DELETE', $MODULE)}">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
		</ul>
	</span>
</div>
{/strip}