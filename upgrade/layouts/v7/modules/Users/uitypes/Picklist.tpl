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
{strip}
	{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	{assign var=PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getFieldName() eq 'defaulteventstatus'}
		{assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
		{assign var=EVENTSTATUS_FIELD_MODEL value=$EVENT_MODULE->getField('eventstatus')}
		{assign var=PICKLIST_VALUES value=$EVENTSTATUS_FIELD_MODEL->getPicklistValues()}
	{else if $FIELD_MODEL->getFieldName() eq 'defaultactivitytype'}
		{assign var=EVENT_MODULE value=Vtiger_Module_Model::getInstance('Events')}
		{assign var=ACTIVITYTYPE_FIELD_MODEL value=$EVENT_MODULE->getField('activitytype')}
		{assign var=PICKLIST_VALUES value=$ACTIVITYTYPE_FIELD_MODEL->getPicklistValues()}
	{/if}

	<select data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="picklist" class="inputElement select2 {if $OCCUPY_COMPLETE_WIDTH} row {/if}" type="picklist" name="{$FIELD_MODEL->getFieldName()}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}' 
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if count($FIELD_INFO['validator'])} 
				data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
			{/if}
			>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			 {if $PICKLIST_NAME eq ' ' and ($FIELD_MODEL->get('name') eq 'currency_decimal_separator' or $FIELD_MODEL->get('name') eq 'currency_grouping_separator')}
				{assign var=PICKLIST_VALUE value=vtranslate('Space', $MODULE)}
				{assign var=OPTION_VALUE value='&nbsp;'}
			 {else}
				{assign var=OPTION_VALUE value=Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}
			 {/if}
			 <option value="{$OPTION_VALUE}" {if decode_html($FIELD_MODEL->get('fieldvalue')) eq decode_html($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
		{/foreach}
	</select>
{/strip}