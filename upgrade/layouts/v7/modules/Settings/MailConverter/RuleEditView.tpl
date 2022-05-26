{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="modelContainer modal-dialog modal-xs" style="width: 600px;">
		<div class="modal-content">
			<form class="form-horizontal" id="ruleSave" method="post" action="index.php">
				{if $RECORD_ID}
					{assign var=TITLE value={vtranslate('LBL_EDIT_RULE', $QUALIFIED_MODULE)}}
				{else}
					{assign var=TITLE value={vtranslate('LBL_ADD_RULE', $QUALIFIED_MODULE)}}
				{/if}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveRule" />
				<input type="hidden" name="scannerId" value="{$SCANNER_ID}" />
				<input type="hidden" name="record" value="{$RECORD_ID}" />
				<div class="addMailBoxStep modal-body">
					{assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
					<table class="table editview-table no-border">
						<tbody>
							{assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
								<tr class="row">
									<td class="col-lg-2 control-label"><label class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}</label>
									<td class="col-lg-4">
										{assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
										{if $FIELD_DATA_TYPE eq 'picklist'}
											{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPickListValues()}
											{if $FIELD_NAME eq 'subject'}
												<select name="subjectop" class="select2 fieldValue inputElement">
													<option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
													{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
														<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('subjectop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
													{/foreach}
												</select>&nbsp;&nbsp;
											{elseif $FIELD_NAME eq 'body'}
												<select name="bodyop" class="select2 fieldValue inputElement">
													<option value="" {if $RECORD_MODEL->get('bodyop') eq ""}selected{/if}>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
													{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
														<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('bodyop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
													{/foreach}
												</select>
												<br><br>
												<textarea name="{$FIELD_MODEL->getName()}" class="form-control col-sm-12" style="padding: 3px 8px;">{$RECORD_MODEL->get($FIELD_NAME)}</textarea>
											{else}
												<select id="actions" name="action1" class="select2 fieldValue inputElement">
													{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
														<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get($FIELD_NAME) eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
													{/foreach}
												</select>
											{/if}
										{elseif $FIELD_DATA_TYPE eq 'radio'}
											{assign var=RADIO_OPTIONS value=$FIELD_MODEL->getRadioOptions()}
											{foreach key=RADIO_NAME item=RADIO_VALUE from=$RADIO_OPTIONS}
												<label class="radioOption inline">
													<input class="radioOption" type="radio" name="{$FIELD_MODEL->getName()}" value="{$RADIO_NAME}" {if $RECORD_MODEL->get($FIELD_NAME) eq $RADIO_NAME} checked {/if} />
													{$RADIO_VALUE}
												</label>&nbsp;&nbsp;&nbsp;&nbsp;
											{/foreach}
										{elseif $FIELD_DATA_TYPE eq 'email'}
											<input type="text" class="fieldValue inputElement" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" data-validation-engine="validate[funcCall[Vtiger_Email_Validator_Js.invokeValidation]]"/>
										{else}
											<input type="text" class="fieldValue inputElement" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
										{/if}
									</td>
									<td class="col-lg-4">
										{if $FIELD_NAME eq 'subject'}
											<input type="text" class="fieldValue inputElement" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" />
										{/if}
									</td>
								</tr>
							{/foreach}
							<tr class="row" id="assignedToBlock">
								<td class="col-lg-2 control-label"><label class="fieldLabel">{vtranslate('Assigned To')}</label></td>
								<td class="col-lg-4">
									<select class="select2 fieldValue inputElement" id="assignedTo" name="assignedTo">
										<optgroup label="{vtranslate('LBL_USERS')}">
											{assign var=USERS value=$USER_MODEL->getAccessibleUsersForModule($MODULE_NAME)}
											{foreach key=OWNER_ID item=OWNER_NAME from=$USERS}
												<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
													{$OWNER_NAME}
												</option>
											{/foreach}
										</optgroup>
										<optgroup label="{vtranslate('LBL_GROUPS')}">
											{assign var=GROUPS value=$USER_MODEL->getAccessibleGroups()}	
											{foreach key=OWNER_ID item=OWNER_NAME from=$GROUPS}
												<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
													{$OWNER_NAME}
												</option>
											{/foreach}
										</optgroup>
									</select>
								</td>
								<td class="col-lg-4"></td>
							</tr>
						</tbody>
					</table>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
{/strip}
