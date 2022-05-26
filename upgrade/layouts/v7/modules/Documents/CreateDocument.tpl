{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
<div class="modal-dialog modelContainer">
	<div class="modal-content" style="width:675px;">
	{assign var=HEADER_TITLE value={vtranslate('LBL_NEW_DOCUMENT', $MODULE)}}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<div class="modal-body">
		<div class="uploadview-content container-fluid">
			<div id="create">
				<form class="form-horizontal recordEditView" name="upload" method="post" action="index.php">
					{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
						<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
					{/if}
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="document_source" value="Vtiger" />
					<input type="hidden" name='service' value="{$STORAGE_SERVICE}" />
					<input type="hidden" name='type' value="{$FILE_LOCATION_TYPE}" />
					{if $RELATION_OPERATOR eq 'true'}
						<input type="hidden" name="relationOperation" value="{$RELATION_OPERATOR}" />
						<input type="hidden" name="sourceModule" value="{$PARENT_MODULE}" />
						<input type="hidden" name="sourceRecord" value="{$PARENT_ID}" />
						{if $RELATION_FIELD_NAME}
							<input type="hidden" name="{$RELATION_FIELD_NAME}" value="{$PARENT_ID}" /> 
						{/if}
					{/if}

					<table class="massEditTable table no-border">
						<tr>
							{assign var="FIELD_MODEL" value=$FIELD_MODELS['notes_title']}
							<td class="fieldLabel col-lg-2">
								<label class="muted pull-right">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
								</label>
							</td>
							<td class="fieldValue col-lg-4" colspan="3">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</td>
						</tr>

						<tr>
							{if $FILE_LOCATION_TYPE eq 'W'}
								<input type="hidden" name='filelocationtype' value="I" />
								{assign var="FIELD_MODEL" value=$FIELD_MODELS['notecontent']}
								{if $FIELD_MODELS['notecontent']}
									<td class="fieldLabel col-lg-2">
										<label class="muted pull-right">
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
											{if $FIELD_MODEL->isMandatory() eq true}
												<span class="redColor">*</span>
											{/if}
										</label>
									</td>
									<td class="fieldValue col-lg-4" colspan="3">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
									</td>
								{/if}
							{else if $FILE_LOCATION_TYPE eq 'E'}
								<input type="hidden" name='filelocationtype' value="E" />
								{assign var="FIELD_MODEL" value=$FIELD_MODELS['filename']}
								<td class="fieldLabel col-lg-2">
									<label class="muted pull-right">
										{vtranslate('LBL_FILE_URL', $MODULE)}&nbsp;
										<span class="redColor">*</span>
									</label>
								</td>
								<td class="fieldValue col-lg-4" colspan="3">
									<input type="text" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}"
									value="{$FIELD_MODEL->get('fieldvalue')}" data-rule-required="true" data-rule-url="true"/>
								</td>
							{/if}
						</tr>

						<tr>
							{assign var="FIELD_MODEL" value=$FIELD_MODELS['assigned_user_id']}
							<td class="fieldLabel col-lg-2">
								<label class="muted pull-right">
									{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
									{if $FIELD_MODEL->isMandatory() eq true}
										<span class="redColor">*</span>
									{/if}
								</label>
							</td>
							<td class="fieldValue col-lg-4">
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
							</td>

							{assign var="FIELD_MODEL" value=$FIELD_MODELS['folderid']}
							{if $FIELD_MODELS['folderid']}
								<td class="fieldLabel col-lg-2">
									<label class="muted pull-right">
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
										{if $FIELD_MODEL->isMandatory() eq true}
											<span class="redColor">*</span>
										{/if}
									</label>
								</td>
								<td class="fieldValue col-lg-4">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
								</td>
							{/if}
						</tr>
						<tr>
							{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title"}
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS}
								{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && $FIELD_MODEL->isQuickCreateEnabled()}
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var="referenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="referenceListCount" value=count($referenceList)}
									{if $FIELD_MODEL->get('uitype') eq "19"}
										{if $COUNTER eq '1'}
											<td></td><td></td></tr><tr>
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
									</tr><tr>
										{assign var=COUNTER value=1}
									{else}
										{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<td class='fieldLabel col-lg-2'>
										{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
											{if $isReferenceField eq "reference"}
												{if $referenceListCount > 1}
													{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
													{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
													{if !empty($REFERENCED_MODULE_STRUCT)}
														{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
													{/if}
													<span class="pull-right">
														<select style="width:150px;" class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
															{foreach key=index item=value from=$referenceList}
																<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
															{/foreach}
														</select>
													</span>
												{else}
													<label class="muted pull-right">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}</label>
												{/if}
											{else if $FIELD_MODEL->get('uitype') eq '83'}
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
												{if $TAXCLASS_DETAILS}
													{assign 'taxCount' count($TAXCLASS_DETAILS)%2}
													{if $taxCount eq 0}
														{if $COUNTER eq 2}
															{assign var=COUNTER value=1}
														{else}
															{assign var=COUNTER value=2}
														{/if}
													{/if}
												{/if}
											{else}
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
											{/if}
											{if $isReferenceField neq "reference"}</label>{/if}
									</td>
									{if $FIELD_MODEL->get('uitype') neq '83'}
										<td class="fieldValue col-lg-4" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</td>
									{/if}
								{/if}
							{/foreach}
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
	{assign var=BUTTON_NAME value={vtranslate('LBL_CREATE', $MODULE)}}
	{assign var=BUTTON_ID value="js-create-document"}
	{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
{/strip}
