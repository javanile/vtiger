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
{assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
{if $FILE_LOCATION_TYPE_FIELD eq NULL}
    {assign var=DOCUMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Documents')}
    {assign var=FILE_LOCATION_TYPE_FIELD value=$DOCUMENTS_MODULE_MODEL->getField('filelocationtype')}
{/if}
{assign var=IS_INTERNAL_LOCATION_TYPE value=$FILE_LOCATION_TYPE_FIELD->get('fieldvalue') neq 'E'}
{assign var=IS_EXTERNAL_LOCATION_TYPE value=$FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}

{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}

<div class="fileUploadContainer">
	{if $IS_EXTERNAL_LOCATION_TYPE}
		<input type="text" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}"
            value="{if $IS_EXTERNAL_LOCATION_TYPE}{$FIELD_VALUE}{/if}" {if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}' {/if} 
            {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
            {if count($FIELD_INFO['validator'])} 
                data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
            {/if}
            />
	{else}
        <div class="fileUploadBtn btn btn-primary">
            <span><i class="fa fa-laptop"></i> {vtranslate('LBL_UPLOAD', $MODULE)}</span>
            <input type="file" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}"
                value="{if $IS_INTERNAL_LOCATION_TYPE} {$FIELD_VALUE} {/if}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} {if $IS_INTERNAL_LOCATION_TYPE && !empty($FIELD_VALUE)} style="width:86px;" {/if} 
                {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                {if count($FIELD_INFO['validator'])} 
                    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                {/if}
                />
        </div>
	{/if}
	<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}hide{/if}">
		<div class="uploadedFileSize"></div>
		<div class="uploadedFileName">
			{if $IS_INTERNAL_LOCATION_TYPE && !empty($FIELD_VALUE)}
				[{$FIELD_VALUE}]
			{/if}
		</div>
		<div class="uploadFileSizeLimit redColor">
			{vtranslate('LBL_MAX_UPLOAD_SIZE',$MODULE)}&nbsp;<span class="maxUploadSize" data-value="{$MAX_UPLOAD_LIMIT_BYTES}">{$MAX_UPLOAD_LIMIT_MB}{vtranslate('MB',$MODULE)}</span>
		</div>
	</div>
</div>
{/strip}