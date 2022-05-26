{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<form id="editTask" name="editTask" method="post" action="index.php" onsubmit="return false;">
		<input type="hidden" id="sourceModule" name="module" value="{$MODULE}">
		<div class="popover-body container-fluid">
			<div class='fields' style='padding-top:10px;'>
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$EDITABLE_FIELDS}            
						<div class='field row'>
							<div class='fieldLabel pull-left col-lg-5' style='position:relative;top:2px;'>
								{if $FIELD_MODEL->getFieldDataType() eq "reference"}
									{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
									{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
									{if $REFERENCE_LIST_COUNT > 1}
										{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
										{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
										{if !empty($REFERENCED_MODULE_STRUCT)}
											{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
										{/if}
										<div class="clearfix">
											{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											<select id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="select2 inputElement referenceModulesList streched">
												{foreach key=index item=value from=$REFERENCE_LIST}
													<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
												{/foreach}
											</select>
										</div>
									{else}
										<label class="muted marginRight10px">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
									{/if}
								{else}
									<label> {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if} </label>
								{/if}
							</div>
							<div class='fieldValue col-lg-7'>
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</div>
						</div>
						<br>
					{/foreach}

			</div>
		</div>
		<div class="popover-footer">
			<center>
				<button class="btn btn-success popoverSave" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<a href="#" class="cancelLink popoverClose" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</center>
		</div>
	</form>
{/strip}