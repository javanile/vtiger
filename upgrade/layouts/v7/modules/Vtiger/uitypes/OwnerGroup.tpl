{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
	{assign var=ASSIGNED_GROUP_ID value=$FIELD_MODEL->get('name')}
	{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}

	<input type="hidden" name="group_users" id="group_users" value={ZEND_JSON::encode($USER_MODEL->getAllAccessibleGroupUsers())}>

	<select class="inputElement select2" id="group_id" type="ownergroup" data-fieldtype="ownergroup" data-fieldname="{$ASSIGNED_GROUP_ID}" data-name="{$ASSIGNED_GROUP_ID}" name="{$ASSIGNED_GROUP_ID}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} 
		{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
		{if count($FIELD_INFO['validator'])} 
			data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
		{/if}>
		<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
		{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
			<option value="{$OWNER_ID}" data-picklistvalue='{$OWNER_NAME}' {if $VIEW_SOURCE neq 'MASSEDIT' && $FIELD_MODEL->get('fieldvalue') eq $OWNER_ID} selected {/if}
					{if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
				{$OWNER_NAME}
			</option>
		{/foreach}
	</select>
{/strip}
