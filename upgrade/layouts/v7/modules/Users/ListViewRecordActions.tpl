{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="table-actions">
		<span class="more dropdown action">
			<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
				<i title="{vtranslate("LBL_MORE_OPTIONS",$MODULE)}" class="fa fa-ellipsis-v icon"></i>
			</span>
			<ul class="dropdown-menu">
				{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
					{if Users_Privileges_Model::isPermittedToChangeUsername($LISTVIEW_ENTRY->getId())}
						<li><a onclick="Settings_Users_List_Js.triggerChangeUsername('{$LISTVIEW_ENTRY->getChangeUsernameUrl()}');">{vtranslate('LBL_CHANGE_USERNAME', $MODULE)}</a></li>
					{/if}
					<li><a onclick="Settings_Users_List_Js.triggerChangePassword('{$LISTVIEW_ENTRY->getChangePwdUrl()}');">{vtranslate('LBL_CHANGE_PASSWORD', $MODULE)}</a></li>
					{if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->get('status') eq 'Active'}
						<li><a href="{$LISTVIEW_ENTRY->getEditViewUrl()}&parentblock=LBL_USER_MANAGEMENT" name="editlink">{vtranslate('LBL_EDIT', $MODULE)}</a></li>
					{/if}
				{/if}
				{if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->getId() != $USER_MODEL->getId()}
					{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
						<li>
							<a href='javascript:Settings_Users_List_Js.triggerDeleteUser("{$LISTVIEW_ENTRY->getDeleteUrl()}")'>{vtranslate("LBL_REMOVE_USER",$MODULE)}</i></a>
						</li>
					{else}
						<li>
							<a onclick="Settings_Users_List_Js.restoreUser({$LISTVIEW_ENTRY->getId()}, event);">{vtranslate("LBL_RESTORE_USER",$MODULE)}</a>
						</li>
						<li>
							<a href='javascript:Settings_Users_List_Js.triggerDeleteUser("{$LISTVIEW_ENTRY->getDeleteUrl()}", "true")'>{vtranslate("LBL_REMOVE_USER",$MODULE)}</i></a>
						</li>
					{/if}
				{/if}
			</ul>
		</span>
	</div>
{/strip}