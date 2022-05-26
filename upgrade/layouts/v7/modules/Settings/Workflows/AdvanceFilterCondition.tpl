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
<div class="row conditionRow" style="margin-bottom: 10px;">
	<span class="col-lg-4 col-md-4 col-sm-4">
		<select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="columnname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
			<option value="none"></option>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				<optgroup label='{vtranslate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}'>
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
                    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
					{if !empty($COLUMNNAME_API)}
						{assign var=columnNameApi value=$COLUMNNAME_API}
					{else}
						{assign var=columnNameApi value=getCustomViewColumnName}
					{/if}
					<option value="{$FIELD_MODEL->$columnNameApi()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
					{if decode_html($FIELD_MODEL->$columnNameApi()) eq $CONDITION_INFO['columnname']}
						{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
						{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
						{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
						selected="selected"
					{/if}
					{if ($MODULE_MODEL->get('name') eq 'Events') and ($FIELD_NAME eq 'recurringtype')}
						{assign var=PICKLIST_VALUES value = Calendar_Field_Model::getReccurencePicklistValues()}
						{$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
					{/if}
                    {if ($MODULE_MODEL->get('name') eq 'Documents') and ($FIELD_NAME eq 'filelocationtype' or $FIELD_NAME eq 'folderid' or $FIELD_NAME eq 'filename')}
                        {if $FIELD_NAME eq 'filelocationtype'}
                            {assign var=PICKLIST_VALUES value = $FIELD_MODEL->getFileLocationType()}
                            {$FIELD_INFO['type'] = 'picklist'}
                            {assign var=FIELD_TYPE value='picklist'}
                            {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                        {else if $FIELD_NAME eq 'folderid'}
                            {assign var=PICKLIST_VALUES value = $FIELD_MODEL->getDocumentFolders()}
                            {$FIELD_INFO['type'] = 'picklist'}
                            {assign var=FIELD_TYPE value='picklist'}
                            {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                        {else if $FIELD_NAME eq 'filename'}
                            {$FIELD_INFO['type'] = 'string'}
                            {assign var=FIELD_TYPE value='string'}
                        {/if}
                    {/if}
					data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}' 
                    {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}>
					{if $SELECTED_MODULE_NAME neq $MODULE_MODEL->get('name')} 
						({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
					{else}
						{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}
					{/if}
				</option>
				{/foreach}
				</optgroup>
			{/foreach}
		</select>
	</span>
	<span class="conditionComparator col-lg-3 col-md-3 col-sm-3">
		<select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="comparator" data-placeholder="{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}">
			 <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
			{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
			{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
				<option value="{$ADVANCE_FILTER_OPTION}" {if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']} selected {/if}>
					{vtranslate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}
				</option>
			{/foreach}
		</select>
	</span>
	<span class="col-lg-4 col-md-4 col-sm-4  fieldUiHolder">
		<input name="{if $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')}{/if}" data-value="value" class="inputElement col-lg-12" type="text" value="{$CONDITION_INFO['value']|escape}" />
	</span>
	<span class="hide">
		<!-- TODO : see if you need to respect CONDITION_INFO condition or / and  -->
		{if empty($CONDITION)}
			{assign var=CONDITION value="and"}
		{/if}
		<input type="hidden" name="column_condition" value="{$CONDITION}" />
	</span>
    <span class="col-lg-1" style="line-height: 30px;">
		<i class="deleteCondition glyphicon glyphicon-trash cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}"></i>
	</span>
</div>
{/strip}