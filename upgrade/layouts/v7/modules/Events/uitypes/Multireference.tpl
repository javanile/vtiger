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
	{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
	{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
	{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="referencefield-wrapper">
		{if {$REFERENCE_LIST_COUNT} eq 1}
			<input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}" />
		{/if}
		{if {$REFERENCE_LIST_COUNT} gt 1}
			{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
			{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
			{if !empty($REFERENCED_MODULE_STRUCT)}
				{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
			{/if}
			{if in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
				<input name="popupReferenceModule" type="hidden" value="{$REFERENCED_MODULE_NAME}" />
			{else}
				<input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}" />
			{/if}
		{/if}
		<input name="{$FIELD_NAME}" type="hidden" value="{$FIELD_MODEL->get('fieldvalue')}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' data-fieldinfo='{$FIELD_INFO}' data-multiple='true'/>
		<div class="input-group">
			<input id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display" data-fieldname="{$FIELD_NAME}" data-fieldtype="reference" type="text" 
				class="marginLeftZero autoComplete inputElement" 
				value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}" 
				data-fieldinfo='{$FIELD_INFO}' data-fieldtype="multireference" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
				{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
				/>
			<span class="input-group-addon relatedPopup cursorPointer" title="{vtranslate('LBL_SELECT', $MODULE)}" style="height:auto;width: 30px;">
				<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
			</span>

			<input type="hidden" name="relatedContactInfo" data-value='{json_encode($RELATED_CONTACTS, $smarty.const.JSON_HEX_APOS)}' />
		</div>
		<!-- Show the add button only if it is edit view  -->
		{if $smarty.request.view eq 'Edit'}
			<span class="createReferenceRecord cursorPointer clearfix" title="{vtranslate('LBL_CREATE', $MODULE)}">
				<i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="fa fa-plus"></i>
			</span>
		{/if}
	</div>
{/strip}
