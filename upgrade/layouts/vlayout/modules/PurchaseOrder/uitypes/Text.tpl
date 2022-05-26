{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
{if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}
    <textarea class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
{else}
    <textarea class="row-fluid {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
    {$FIELD_MODEL->get('fieldvalue')}</textarea>
    {assign var=VIEW_NAME value={getPurifiedSmartyParameters('view')}}
    {if $VIEW_NAME neq 'Detail'}
		{if $FIELD_NAME eq "bill_street"}
			<div>
				<a class="cursorPointer" name="copyAddress" data-target="shipping">{vtranslate('LBL_COPY_SHIPPING_ADDRESS', $MODULE)}</a> <br>
                <a class="cursorPointer" name="copyCompanyAddress" data-target="bill">{vtranslate('LBL_COPY_COMPANY_ADDRESS', $MODULE)}</a>
			</div>
		{else if $FIELD_NAME eq "ship_street"}
			<div>
				<a class="cursorPointer" name="copyAddress" data-target="billing">{vtranslate('LBL_COPY_BILLING_ADDRESS', $MODULE)}</a> <br>
               <a class="cursorPointer" name="copyCompanyAddress" data-target="ship">{vtranslate('LBL_COPY_COMPANY_ADDRESS', $MODULE)}</a>
			</div>
		{/if}
    {/if}
{/if}
{/strip}