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
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
{if (!$FIELD_NAME)}
  {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
{/if}
<div class="input-group inputElement" style="margin-bottom: 3px">
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="dateField form-control {if $IGNOREUIREGISTRATION}ignore-ui-registration{/if}" data-fieldname="{$FIELD_NAME}" data-fieldtype="date" name="{$FIELD_NAME}" data-date-format="{$dateFormat}"
    value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
    {if $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if}
    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
    {if count($FIELD_INFO['validator'])}
        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
    {/if}  data-rule-date="true" />
<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
</div>
{/strip}
