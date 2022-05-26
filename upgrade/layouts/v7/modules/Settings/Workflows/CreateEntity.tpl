{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Workflows/views/CreateEntity.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{$TASK_OBJECT->field_value_mapping}' />
<input type="hidden" value="{if $TASK_ID}{$TASK_OBJECT->reference_field}{else}{$REFERENCE_FIELD_NAME}{/if}" name='reference_field' id='reference_field' />
<div class="conditionsContainer" id="save_fieldvaluemapping">
	{if $RELATED_MODULE_MODEL_NAME neq '' && getTabid($RELATED_MODULE_MODEL_NAME)}
		<div>
			<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
		</div><br>
		{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($TASK_OBJECT->entity_type)}
		{assign var=FIELD_VALUE_MAPPING value=ZEND_JSON::decode($TASK_OBJECT->field_value_mapping)}
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			{assign var=SELECTED_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FIELD_MAP['fieldname'])}
			{if empty($SELECTED_FIELD_MODEL)}
				{continue}
			{/if}
			{assign var=SELECTED_FIELD_MODEL_FIELD_TYPE value=$SELECTED_FIELD_MODEL->getFieldDataType()}
			<div class="row conditionRow form-group">
				<span class="col-lg-4">
					<select name="fieldname" class="select2" style="min-width: 250px" {if $SELECTED_FIELD_MODEL->isMandatory() || ($DISABLE_ROW eq 'true') } disabled="" {/if} >
						<option value="none"></option>
						{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$FIELD_MODEL->get('name')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('name')} {if $FIELD_MODEL->isMandatory()}{assign var=MANDATORY_FIELD value=true} {else} {assign var=MANDATORY_FIELD value=false} {/if}{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
								{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}{if $SELECTED_FIELD_MODEL->isMandatory() and $FIELD_MODEL->getName() neq 'assigned_user_id'}<span class="redColor">*</span>{/if}
							</option>	
						{/foreach}
					</select>
				</span>
				<span>
					<input name="modulename" type="hidden"
						{if $FIELD_MAP['modulename'] eq $SOURCE_MODULE} value="{$SOURCE_MODULE}" {/if}
						{if $FIELD_MAP['modulename'] eq $RELATED_MODULE_MODEL_NAME} value="{$RELATED_MODULE_MODEL_NAME}" {/if} 
					/>
				</span>
				<span class="fieldUiHolder col-lg-4">
					<input type="text" class="getPopupUi inputElement" {if ($DISABLE_ROW eq 'true')} disabled=""{/if} readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</span>
				{if $MANDATORY_FIELD neq true}
					<span class="cursorPointer col-lg-1">
						<i class="alignMiddle deleteCondition fa fa-trash"></i>
					</span>
				{/if}
			</div>
		{/foreach}

		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
	{else}
		{if $RELATED_MODULE_MODEL}
			<div>
				<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
			</div><br>
			{assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
			{foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
				{if in_array($SOURCE_MODULE, $MANDATORY_FIELD_MODEL->getReferenceList())}
					{continue}
				{/if}
				<div class="row conditionRow form-group">
					<span class="col-lg-4">
						<select name="fieldname" class="select2" disabled="" style="min-width: 250px">
							<option value="none"></option>
							{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" {if $FIELD_MODEL->get('name') eq $MANDATORY_FIELD_MODEL->get('name')} {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
									{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}<span class="redColor">*</span>
								</option>	
							{/foreach}
						</select>
					</span>
					<span>
						{if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')}
							<input type="hidden" name="modulename" value="{$RELATED_MODULE_MODEL->get('name')}" />
						{else}
							<input type="hidden" name="modulename" value="{$SOURCE_MODULE}" />
						{/if}
					</span>
					<span class="fieldUiHolder col-lg-4">
						<input type="text" class="getPopupUi inputElement" name="fieldValue" value="" />
						<input type="hidden" name="valuetype" value="rawtext" />
					</span>
				</div>
			{/foreach}
			{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
		{/if}
	{/if}
</div><br>
{if $RELATED_MODULE_MODEL}
	<div class="row form-group basicAddFieldContainer hide">
		<span class="col-lg-4">
			<select name="fieldname" style="min-width: 250px">
				<option value="none">{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}</option>
				{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{if !$FIELD_MODEL->isMandatory()}
					<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
						{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}
					</option>
					{/if}
				{/foreach}
			</select>
		</span>
		<span>
			<input type="hidden" name="modulename" value="{$SOURCE_MODULE}" />
		</span>
		<span class="fieldUiHolder col-lg-4">
			<input type="text" class="inputElement" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" value="rawtext" />
		</span>
		<span class="cursorPointer col-lg-1">
			<i class="alignMiddle deleteCondition fa fa-trash"></i>
		</span>
	</div>
{/if}
