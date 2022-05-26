{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}' />
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{if $BLOCK_LABEL_KEY neq 'LBL_CALENDAR_SETTINGS'}
			{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
			{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
			<div class="block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
				{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
				{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
				<div>
					<h4>{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							{assign var=COUNTER value=0}
							<tr>
								{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
									{assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
									{if !$FIELD_MODEL->isViewableInDetailView()}
										{continue}
									{/if}
									{if $FIELD_MODEL->getName() eq 'theme' or $FIELD_MODEL->getName() eq 'rowheight'}
										{continue}
									{/if}
									{if $FIELD_MODEL->get('uitype') eq "83"}
										{foreach item=tax key=count from=$TAXCLASS_DETAILS}
											{if $COUNTER eq 2}
												</tr><tr>
												{assign var="COUNTER" value=1}
											{else}
												{assign var="COUNTER" value=$COUNTER+1}
											{/if}
											<td class="fieldLabel {$WIDTHTYPE}">
												<span class='muted'>{vtranslate($tax.taxlabel, $MODULE)}(%)</span>
											</td>
											<td class="fieldValue {$WIDTHTYPE}">
												<span class="value textOverflowEllipsis" data-field-type="{$FIELD_MODEL->getFieldDataType()}" >
													{if $tax.check_value eq 1}
														{$tax.percentage}
													{else}
														0
													{/if} 
												</span>
											</td>
										{/foreach}
									{else if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
										{if $COUNTER neq 0}
											{if $COUNTER eq 2}
												</tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										<td class="fieldLabel {$WIDTHTYPE}"><span class="muted">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</span></td>
										<td class="fieldValue {$WIDTHTYPE}">
											<div id="imageContainer" width="300" height="200">
												{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
													{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
														<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="300" height="200">
													{/if}
												{/foreach}
											</div>
										</td>
										{assign var=COUNTER value=$COUNTER+1}
									{else}
										{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19" or $fieldDataType eq 'reminder' or $fieldDataType eq 'recurrence'}
											{if $COUNTER eq '1'}
												<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td></tr><tr>
												{assign var=COUNTER value=0}
											{/if}
										{/if}
										{if $COUNTER eq 2}
											</tr><tr>
											{assign var=COUNTER value=1}
										{else}
											{assign var=COUNTER value=$COUNTER+1}
										{/if}
										<td class="fieldLabel {$WIDTHTYPE} textOverflowEllipsis" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->getName() eq 'description' or $FIELD_MODEL->get('uitype') eq '69'} style='width:8%'{/if}>
											<span class="muted">
												{if $MODULE_NAME eq 'Documents' && $FIELD_MODEL->get('label') eq "File Name" && $RECORD->get('filelocationtype') eq 'E'}
													{vtranslate("LBL_FILE_URL",{$MODULE_NAME})}
												{else}
													{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
												{/if}
												{if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
													({$BASE_CURRENCY_SYMBOL})
												{/if}
											</span>
										</td>
										<td class="fieldValue {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $fieldDataType eq 'reminder' or $fieldDataType eq 'recurrence'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>

											{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
											{if $fieldDataType eq 'multipicklist'}
												{assign var=FIELD_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
											{else}
												{assign var=FIELD_DISPLAY_VALUE value=Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}
											{/if}

											<span class="value textOverflowEllipsis" data-field-type="{$FIELD_MODEL->getFieldDataType()}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_MODEL->get('uitype') eq '21'} style="white-space:normal;" {/if} {if $fieldDataType eq 'email'}title='{$FIELD_MODEL->get('fieldvalue')}'{/if} >
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
											</span>
											{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true'}
												<span class="hide edit pull-left">
													{if $fieldDataType eq 'multipicklist'}
														<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
													{else}
														<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
													{/if}
												</span>
												<span class="action pull-right"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
											{/if}
										</td>
									{/if}

									{if $FIELD_MODEL_LIST|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" and $FIELD_MODEL->get('uitype') neq "69" and $FIELD_MODEL->get('uitype') neq "105"}
										<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
									{/if}
								{/foreach}
								{* adding additional column for odd number of fields in a block *}
								{if $FIELD_MODEL_LIST|@end eq true and $FIELD_MODEL_LIST|@count neq 1 and $COUNTER eq 1}
									<td class="fieldLabel {$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
								{/if}
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<br>
		{/if}
	{/foreach}
{/strip}