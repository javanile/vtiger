{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="block fieldBlockContainer">
		<form class="form-horizontal" id="mailBoxEditView" name="step1">
			{assign var=FIELDS value=$MODULE_MODEL->getFields()}
			{if empty($RECORD_ID)}
				{assign var=RECORD_EXISTS value=false}
			{else}
				{assign var=RECORD_EXISTS value=true}
			{/if}

			{if $RECORD_EXISTS}
				<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}" />
				<input type="hidden" name="scannerOldName" value="{$RECORD_MODEL->getName()}" />
			{/if}
			<div class="addMailBoxStep">
				<div class="row">
					<table class="table editview-table no-border">
						<tbody>
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
								{if !$RECORD_MODEL->isFieldEditable($FIELD_MODEL)}
									{continue}
								{/if}
								<tr>
									<td class="fieldLabel control-label" style="width:25%;">
										<label>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}{if $FIELD_MODEL->isMandatory()} <span class="redColor">*</span>{/if}</label>
									</td>
									<td style="word-wrap:break-word;">
										{assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
										{if $FIELD_DATA_TYPE eq 'password'}
											<input class="fieldValue inputElement" type="password" name="{$FIELD_MODEL->getName()}" {if $RECORD_EXISTS} value="{$RECORD_MODEL->get($FIELD_NAME)}" {/if}
												{if $FIELD_MODEL->isMandatory()}data-validation-engine="validate[required]"{/if} />
										{elseif $FIELD_DATA_TYPE eq 'boolean'}
											{assign var=RECORD_ID value=$RECORD_MODEL->getId()}
											<input type="hidden" name="{$FIELD_MODEL->getName()}" value="0" />
											<input type="checkbox" name="{$FIELD_MODEL->getName()}" {if ($RECORD_MODEL->get($FIELD_MODEL->getName()) eq '1') || (empty($RECORD_ID))}checked{/if} />
										{elseif $FIELD_DATA_TYPE eq 'picklist'}
											{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPickListValues()}
											{assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}
											{if $FIELD_MODEL->getName() eq 'time_zone' && empty($FIELD_VALUE)}
												{assign var=FIELD_VALUE value=" "}
											{/if}
											<select name="{$FIELD_MODEL->getName()}" class="select2 fieldValue inputElement"
												{* to show dropdown above *}
												{if $FIELD_MODEL->getName() eq 'time_zone'}
													data-dropdownCssClass="select2-drop-above"
												{/if}
												>
												{if $FIELD_MODEL->getName() eq 'time_zone'}
													{* since in time zone its array of value and key, since there will mutiple areas with same time_zone *}
													{foreach item=PICKLIST_VALUE key=PICKLIST_KEY from=$PICKLIST_VALUES}
														<option value="{$PICKLIST_KEY}" {if $FIELD_VALUE eq $PICKLIST_KEY} selected {/if} >{vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE)}</option>
													{/foreach}
												{else}
													{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
														<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get($FIELD_NAME) eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
													{/foreach}
												{/if}
											</select>
										{elseif $FIELD_DATA_TYPE eq 'radio'}
											{assign var=RADIO_OPTIONS value=$FIELD_MODEL->getRadioOptions()}
											{foreach key=RADIO_NAME item=RADIO_VALUE from=$RADIO_OPTIONS}
												<label class="radioOption inline">
													<input class="radioOption" type="radio" name="{$FIELD_MODEL->getName()}" value="{$RADIO_NAME}" 
														{if $RECORD_EXISTS}
															{if $RECORD_MODEL->get($FIELD_NAME) eq $RADIO_NAME} checked {/if}
														{else}
															{if $RADIO_NAME eq 'imap4' || $RADIO_NAME eq 'ssl' || $RADIO_NAME eq 'novalidate-cert'} checked {/if} 
														{/if} />&nbsp;
													{$RADIO_VALUE}
												</label>&nbsp;&nbsp;&nbsp;&nbsp;
											{/foreach}
										{else}
											<input type="text" class="fieldValue inputElement" name="{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->isMandatory()}data-validation-engine="validate[required]"{/if} value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="border1px modal-overlay-footer clearfix">
				<div class="row clearfix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-lg-12 ">
						<button type="submit" class="btn btn-success nextStep" onclick="javascript:Settings_MailConverter_Edit_Js.firstStep()">{vtranslate('LBL_NEXT',$MODULE)}</button>&nbsp;&nbsp;
						<a type="reset" class="cancelLink cursorPointer" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL',$MODULE)}</a>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
</div>
{/strip}