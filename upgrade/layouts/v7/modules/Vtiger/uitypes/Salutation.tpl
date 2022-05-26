{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{if $SALUTATION_FIELD_MODEL}
		{assign var=PICKLIST_VALUES value=$SALUTATION_FIELD_MODEL->getPicklistValues()}
		{assign var="SALUTATION_VALIDATOR" value=$SALUTATION_FIELD_MODEL->getValidator()}
		<select class="inputElement select2" style="width:78px;" name="{$SALUTATION_FIELD_MODEL->get('name')}" >
			{if $SALUTATION_FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_NONE', $MODULE)}</option>{/if}
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
				<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($SALUTATION_FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
			{/foreach}
		</select>&nbsp;
	{/if}

	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
	{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
			class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}"
			{if $SALUTATION_FIELD_MODEL} style="width:120px;" {/if} 
			{if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'} readonly {/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} 
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if count($FIELD_INFO['validator'])} 
				data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
			{/if}
	/>
	{* TODO - Handler Ticker Symbol field *}
{/strip}