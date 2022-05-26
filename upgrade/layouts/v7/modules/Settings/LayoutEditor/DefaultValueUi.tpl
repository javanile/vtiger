{*<!--
/*+****************************************************************************
* The contents of this file are subject to the vtiger CRM Commercial License
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Code is vtiger.
* All Rights Reserved. Copyright (C) vtiger.
*******************************************************************************/
-->*}

{strip}
	{if $FIELD_MODEL->isDefaultValueOptionDisabled() neq "true"}
		<div class="form-group">
			<label class="control-label fieldLabel col-sm-5">
				<img src="{vimage_path('DefaultValue.png')}" height=14 width=14/> &nbsp; {vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}
			</label>
			<div class="controls col-sm-7">
				<div class="defaultValueUi">
					{if !$NAME_ATTR}
						{assign var=NAME_ATTR value="fieldDefaultValue"}
					{/if}
					{if $DEFAULT_VALUE eq false && !$IS_SET}
						{assign var=DEFAULT_VALUE value=$FIELD_MODEL->get('defaultvalue')}
					{/if}

					{if $FIELD_MODEL->getFieldDataType() eq "picklist"}
						{if !is_array($PICKLIST_VALUES)}
							{assign var=PICKLIST_VALUES value=$FIELD_INFO.picklistvalues}
						{/if}
						{if !$DEFAULT_VALUE}
							{assign var=DEFAULT_VALUE value=$FIELD_MODEL->get('defaultvalue')}
						{/if}
						{assign var=DEFAULT_VALUE value={decode_html($DEFAULT_VALUE)}}
						<select class="col-sm-9 select2" name="{$NAME_ATTR}">
							{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
								<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if $DEFAULT_VALUE eq $PICKLIST_NAME} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
							{/foreach}
						</select>
					{elseif $FIELD_MODEL->getFieldDataType() eq "multipicklist"}
						{if !is_array($PICKLIST_VALUES)}
							{assign var=PICKLIST_VALUES value=$FIELD_INFO.picklistvalues}
						{/if}
						{assign var="FIELD_VALUE_LIST" value=explode(' |##| ', $DEFAULT_VALUE)}
						<select multiple class="col-sm-9 select2" name="{$NAME_ATTR}[]" >
							{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
								<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $SELECTED_MODULE_NAME)}</option>
							{/foreach}
						</select>
					{elseif $FIELD_MODEL->getFieldDataType() eq "boolean"}
						<input type="hidden" name="{$NAME_ATTR}" value="" />
						<input type="checkbox" name="{$NAME_ATTR}" value="1" {if $DEFAULT_VALUE eq 'on' or $DEFAULT_VALUE eq 1} checked {/if} />
					{elseif $FIELD_MODEL->getFieldDataType() eq "time"}
						<div class="input-group time">
							<input type="text" class="timepicker-default inputElement" data-format="{$USER_MODEL->get('hour_format')}" data-toregister="time" value="{$DEFAULT_VALUE}" name="{$NAME_ATTR}"  style='width: 75%'/>
							<span class="input-group-addon cursorPointer">
								<i class="fa fa-times"></i>
							</span>
						</div>
					{elseif $FIELD_MODEL->getFieldDataType() eq "date"}
						<div class="input-group date">
							{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
							<input type="text" class="inputElement dateField" name="{$NAME_ATTR}" data-toregister="date" data-date-format="{$USER_MODEL->get('date_format')}" 
								value="{$FIELD_MODEL->getEditViewDisplayValue($DEFAULT_VALUE)}" style='width: 75%'/>
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
						</div>
					{elseif $FIELD_MODEL->getFieldDataType() eq "percentage"}
						<div class="input-group" style='width: 75%'>
							<input type="number" class="form-control" name="{$NAME_ATTR}"
								value="{$DEFAULT_VALUE}"  step="any"/>
							<span class="input-group-addon">%</span>
						</div>
					{elseif $FIELD_MODEL->getFieldDataType() eq "currency"}
						<div class="input-group">
							<span class="input-group-addon">{$USER_MODEL->get('currency_symbol')}</span>
							<input type="text" class="inputElement" name="{$NAME_ATTR}"
								value="{$FIELD_MODEL->getEditViewDisplayValue($DEFAULT_VALUE, true)}"
								data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' style='width: 75%'/>
						</div>
					{else if $FIELD_MODEL->getFieldName() eq "terms_conditions" && $FIELD_MODEL->get('uitype') == 19}
						{assign var=INVENTORY_TERMS_AND_CONDITIONS_MODEL value= Settings_Vtiger_MenuItem_Model::getInstance("INVENTORYTERMSANDCONDITIONS")}
						<a href="{$INVENTORY_TERMS_AND_CONDITIONS_MODEL->getUrl()}" target="_blank">{vtranslate('LBL_CLICK_HERE_TO_EDIT', $QUALIFIED_MODULE)}</a>
					{else if $FIELD_MODEL->getFieldDataType() eq "text"}
						<textarea class="input-lg col-sm-4" name="{$NAME_ATTR}"  style="resize: vertical">{$DEFAULT_VALUE}</textarea>
					{else}
						<input type="text" class="inputElement col-sm-3" name="{$NAME_ATTR}" value="{$DEFAULT_VALUE}" style='width: 75%'/>
					{/if}
				</div>
			</div>
		</div>
	{/if}
{/strip}