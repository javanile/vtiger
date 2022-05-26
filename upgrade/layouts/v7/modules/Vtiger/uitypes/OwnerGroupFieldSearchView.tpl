{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
	{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
	{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}

	{if $FIELD_MODEL->get('uitype') eq '52' || $FIELD_MODEL->get('uitype') eq '77'}
		{assign var=ALL_ACTIVEGROUP_LIST value=array()}
	{else}
		{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
	{/if}

	{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}
	<div class="select2_search_div">
		<input type="text" class="listSearchContributor inputElement select2_input_element"/>
		<input type="hidden" name="group_users" id="group_users" value={ZEND_JSON::encode($USER_MODEL->getAllAccessibleGroupUsers())}>
		<select class="select2 listSearchContributor {$ASSIGNED_USER_ID}"name="{$ASSIGNED_USER_ID}" multiple id="group_id" style="display:none">
			{if count($ALL_ACTIVEGROUP_LIST) gt 0}
				{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
					<option value="{$OWNER_NAME}" data-picklistvalue= '{$OWNER_NAME}' {if in_array(trim($OWNER_NAME),$SEARCH_VALUES)} selected {/if}
							{if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
						{$OWNER_NAME}
					</option>
				{/foreach}
			{/if}
		</select>
	</div>
{/strip}