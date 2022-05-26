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
	<div class="row">
		<div class="col-sm-2 col-xs-2"><strong>{vtranslate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong></div>
	</div><br>
	<div>
		<button type="button" class="btn btn-default" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
	</div><br>
	<div class="conditionsContainer" id="save_fieldvaluemapping" style="margin-bottom: 70px;">
		{assign var=FIELD_VALUE_MAPPING value=ZEND_JSON::decode($TASK_OBJECT->field_value_mapping)}
		<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{Vtiger_Util_Helper::toSafeHTML($TASK_OBJECT->field_value_mapping)}' />
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
            <div class="row conditionRow" style="margin-bottom: 15px;">
                <div class="cursorPointer col-sm-1 col-xs-1">
                    <center> <i class="alignMiddle deleteCondition fa fa-trash" style="position: relative; top: 4px;"></i> </center>
				</div>
                
				<div class="col-sm-3 col-xs-3">
					<select name="fieldname" class="select2" style="min-width: 250px" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
						<option></option>
                        {foreach from=$RECORD_STRUCTURE  item=FIELDS}
                            {foreach from=$FIELDS item=FIELD_MODEL}
                                {if (!($FIELD_MODEL->get('workflow_fieldEditable') eq true)) or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))}
                                    {continue}
                                {/if}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                            {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
                                {assign var=FIELD_MODULE_MODEL value=$FIELD_MODEL->getModule()}
                                <option value="{$FIELD_MODEL->get('workflow_columnname')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('workflow_columnname')}selected=""{/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" 
                                        {if ($FIELD_MODULE_MODEL->get('name') eq 'Events') and ($FIELD_NAME eq 'recurringtype')}
                                        {assign var=PICKLIST_VALUES value=Calendar_Field_Model::getReccurencePicklistValues()}
                                        {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                    {/if}
                                    data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}' >
                                        {vtranslate($FIELD_MODEL->get('workflow_columnlabel'), $SOURCE_MODULE)}
							</option>
						{/foreach}
                        {/foreach}
					</select>
				</div>
                    
				<div class="fieldUiHolder col-sm-4 col-xs-4">
					<input type="text" class="getPopupUi inputElement" readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</div>
			</div>
		{/foreach}
		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
		</div><br>
        <div class="row basicAddFieldContainer hide" style="margin-bottom: 15px;">
            <div class="cursorPointer col-sm-1 col-xs-1">
                <center> <i class="alignMiddle deleteCondition fa fa-trash" style="position: relative; top: 4px;"></i> </center>
			</div>
			<div class="col-sm-3 col-xs-3">
				<select name="fieldname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}" style="min-width: 250px">
					<option></option>
                     {foreach from=$RECORD_STRUCTURE  item=FIELDS}
                        {foreach from=$FIELDS item=FIELD_MODEL}
                            {if (!($FIELD_MODEL->get('workflow_fieldEditable') eq true))  or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))}
                                {continue}
                            {/if}
						{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                        {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
                            {assign var=FIELD_MODULE_MODEL value=$FIELD_MODEL->getModule()}
                            <option value="{$FIELD_MODEL->get('workflow_columnname')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" 
                                    {if ($FIELD_MODULE_MODEL->get('name') eq 'Events') and ($FIELD_NAME eq 'recurringtype')}
                                    {assign var=PICKLIST_VALUES value=Calendar_Field_Model::getReccurencePicklistValues()}
                                    {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                {/if}
                                data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}' >
                                    {vtranslate($FIELD_MODEL->get('workflow_columnlabel'), $SOURCE_MODULE)}
						</option>
					{/foreach}
                    {/foreach}
				</select>
			</div>
			<div class="fieldUiHolder col-sm-4 col-xs-4">
				<input type="text" class="inputElement" readonly="" name="fieldValue" value="" />
				<input type="hidden" name="valuetype" value="rawtext" />
			</div>
		</div>
		{/strip}
