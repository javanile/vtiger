{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="table-actions calendar-table-actions">
		{if !$SEARCH_MODE_RESULTS}
			<span class="input" >
				<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
			</span>
		{/if}
		{if $LISTVIEW_ENTRY->get('starred') eq 'Yes'}
			{assign var=STARRED value=true}
		{else}
			{assign var=STARRED value=false}
		{/if}
		{if $QUICK_PREVIEW_ENABLED eq 'true'}
			<span>
				<a class="quickView fa fa-eye icon action" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
			</span>
		{/if}
		{if $MODULE_MODEL->isStarredEnabled()}
			<span>
				<a class="markStar fa icon action {if $STARRED} fa-star active {else} fa-star-o{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
			</span>
		{/if}
		{assign var=EDIT_VIEW_URL value={$LISTVIEW_ENTRY->getEditViewUrl()}}
		{if $IS_MODULE_EDITABLE && $EDIT_VIEW_URL && $LISTVIEW_ENTRY->get('taskstatus') neq vtranslate('Held', $MODULE) && $LISTVIEW_ENTRY->get('taskstatus') neq vtranslate('Completed', $MODULE)}
			<span class="fa fa-check icon action markAsHeld" title="{vtranslate('LBL_MARK_AS_HELD', $MODULE)}" onclick="Calendar_Calendar_Js.markAsHeld('{$LISTVIEW_ENTRY->getId()}');"></span>
		{/if}
		{if $IS_CREATE_PERMITTED && $EDIT_VIEW_URL && $LISTVIEW_ENTRY->get('taskstatus') eq vtranslate('Held', $MODULE)}
			<span class="fa fa-flag icon action holdFollowupOn" title="{vtranslate('LBL_HOLD_FOLLOWUP_ON', "Events")}" onclick="Calendar_Calendar_Js.holdFollowUp('{$LISTVIEW_ENTRY->getId()}');"></span>
		{/if}
		<span class="more dropdown action">
			<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
				<i class="fa fa-ellipsis-v icon"></i></span>
			<ul class="dropdown-menu">
				<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{vtranslate('LBL_DETAILS', $MODULE)}</a></li>
					{if $RECORD_ACTIONS}
						{if $RECORD_ACTIONS['edit']}
						<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" data-url="{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}" name="editlink">{vtranslate('LBL_EDIT', $MODULE)}</a></li>
						{/if}
						{if $RECORD_ACTIONS['delete']}
						<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
						{/if}
					{/if}
			</ul>
		</span>

		<div class="btn-group inline-save hide">
			<button class="button btn-success btn-small save" name="save"><i class="fa fa-check"></i></button>
			<button class="button btn-danger btn-small cancel" name="Cancel"><i class="fa fa-close"></i></button>
		</div>
	</div>
{/strip}