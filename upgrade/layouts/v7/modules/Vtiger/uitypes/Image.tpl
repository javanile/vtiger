{*<!--
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
********************************************************************************/
-->*}

{strip}
	{if !is_array($IMAGE_DETAILS)}
		{assign var=IMAGE_DETAILS value=$RECORD_STRUCTURE_MODEL->getRecord()->getImageDetails()}
	{/if}
	{if $MODULE_NAME eq 'Webforms'}
		<input type="text" readonly="" />
	{else}
		{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
		{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
		<div class="fileUploadContainer text-left">
			<div class="fileUploadBtn btn btn-primary">
				<span><i class="fa fa-laptop"></i> {vtranslate('LBL_UPLOAD', $MODULE)}</span>
				<input type="file" class="inputElement {if $MODULE eq 'Products'}multi max-6{/if} {if $FIELD_MODEL->get('fieldvalue') and $FIELD_INFO["mandatory"] eq true} ignore-validation {/if}" name="{$FIELD_MODEL->getFieldName()}[]" value="{$FIELD_MODEL->get('fieldvalue')}"
					{if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if} 
					{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
					{if count($FIELD_INFO['validator'])} 
						data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
					{/if} />
			</div>

			<div class="uploadedFileDetails {if $IS_EXTERNAL_LOCATION_TYPE}hide{/if}">
				<div class="uploadedFileSize"></div>
				<div class="uploadedFileName">
					{if !empty($FIELD_VALUE) && !$smarty.request['isDuplicate']}
						[{$FIELD_MODEL->getDisplayValue($FIELD_VALUE)}]
					{/if}
				</div>
			</div>
		</div>
		{if $FIELD_MODEL->getFieldDataType() eq 'image' || $FIELD_MODEL->getFieldDataType() eq 'file'}
			{if $MODULE neq 'Products'}
                            <div class='redColor'>
				{vtranslate('LBL_NOTE_EXISTING_ATTACHMENTS_WILL_BE_REPLACED', $MODULE)}
                            </div>
                        {/if}
		{/if}
		{if $MODULE eq 'Products'}<div id="MultiFile1_wrap_list" class="MultiFile-list"></div>{/if}

		{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
			<div class="row" style="margin-top:5px;">
				{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
					<span class="col-lg-6" name="existingImages"><img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}" width="400" height="250" ></span>
					<span class="col-lg-3">
						<span class="row">
							<span class="col-lg-11">[{$IMAGE_INFO.name}]</span>
							<span class="col-lg-1"><input type="button" id="file_{$ITER}" value="{vtranslate('LBL_DELETE','Vtiger')}" class="imageDelete"></span>
						</span>
					</span>
				{/if}
			</div>
		{/foreach}
	{/if}
{/strip}