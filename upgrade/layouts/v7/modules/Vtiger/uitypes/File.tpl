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
	{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="fileUploadContainer text-left">
		<div class="fileUploadBtn btn btn-sm btn-primary">
			<span><i class="fa fa-laptop"></i> {vtranslate('LBL_ATTACH_FILES', $MODULE)}</span>
			<input type="file" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" class="inputElement {if $MODULE eq 'ModComments'} multi {/if} " maxlength="6" name="{if $MODULE eq 'ModComments'}{$FIELD_MODEL->getFieldName()}[]{else}{$FIELD_MODEL->getFieldName()}{/if}"
					value="{$FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} 
					{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
					{if count($FIELD_INFO['validator'])} 
						data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
					{/if}
					/>
		</div>&nbsp;&nbsp;
		<span class="uploadFileSizeLimit fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_MAX_UPLOAD_SIZE',$MODULE)} {$MAX_UPLOAD_LIMIT_MB} {vtranslate('MB',$MODULE)}">
			<span class="maxUploadSize" data-value="{$MAX_UPLOAD_LIMIT_BYTES}"></span>
		</span>
		<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}hide{/if}">
			<div class="uploadedFileSize"></div>
			<div class="uploadedFileName">
				{if !empty($FIELD_VALUE) && !$smarty.request['isDuplicate']}
					[{$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}]
				{/if}
			</div>
		</div>
	</div>
		{literal}
			<script>
				jQuery(document).ready(function() {
					var fileElements = jQuery('input[type="file"]',jQuery(this).data('fieldinfo') == 'file');
					fileElements.on('change',function(e) {
						var element = jQuery(this);
						var fileSize = e.target.files[0].size;
						var maxFileSize = element.closest('form').find('.maxUploadSize').data('value');
						if(fileSize > maxFileSize) {
							alert(app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE'));
							element.val(null);
						}
					});
				});
			</script>
		{/literal}
{/strip}
