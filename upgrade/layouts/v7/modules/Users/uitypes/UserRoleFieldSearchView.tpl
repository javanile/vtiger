{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
{assign var=ROLES value=$FIELD_MODEL->getAllRoles()}
{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
<div class="select2_search_div">
	<input type="text" class="listSearchContributor inputElement select2_input_element"/>
	<select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" multiple data-fieldinfo='{$FIELD_INFO|escape}' style="display:none;">
		{foreach item=ROLE_ID key=ROLE_NAME from=$ROLES}
			<option value="{$ROLE_NAME}" {if in_array($ROLE_NAME,$SEARCH_VALUES) && ($ROLE_NAME neq "") } selected{/if}>{$ROLE_NAME}</option>
		{/foreach}
	</select>
</div>
{/strip}