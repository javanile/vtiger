{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<div class="editViewPageDiv " id="editViewContent">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
			<div class="contents">
				<form id="ConfigEditorForm" class="form-horizontal" data-detail-url="{$MODEL->getDetailViewUrl()}" method="POST">
					{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
					{assign var=FIELD_VALIDATION  value=['HELPDESK_SUPPORT_EMAIL_ID'	=> 'data-rule-email="true"',
																	'upload_maxsize'	=> 'data-rule-range=[1,5] data-rule-positive="true" data-rule-wholeNumber="true"',
																'history_max_viewed'	=> 'data-rule-range=[1,5] data-rule-positive="true" data-rule-wholeNumber="true"',
															'listview_max_textlength'	=> 'data-rule-range=[1,100] data-rule-positive="true" data-rule-wholeNumber="true"',
															'list_max_entries_per_page'	=> 'data-rule-range=[1,100] data-rule-positive="true" data-rule-wholeNumber="true"']}

					<div>
						<h4>{vtranslate('LBL_CONFIG_EDITOR', $QUALIFIED_MODULE)}</h4>
					</div>
					<hr>
					<br>
					<div class="detailViewInfo">
						{assign var=FIELD_DATA value=$MODEL->getViewableData()}
						{foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
							<div class="row form-group">
								<div class="col-lg-4 control-label fieldLabel">
									<label>{if $FIELD_NAME == 'upload_maxsize'}{if $FIELD_DATA[$FIELD_NAME] gt 5}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,$FIELD_DATA[$FIELD_NAME])}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,5)}{/if}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}{/if}</label>
								</div>
								<div  class="{$WIDTHTYPE}  col-lg-4 input-group">
									{if $FIELD_DETAILS['fieldType'] == 'picklist'}

										<select class="select2-container inputElement select2 col-lg-11" name="{$FIELD_NAME}" >
											{foreach key=optionName item=optionLabel from=$MODEL->getPicklistValues($FIELD_NAME)}
												{if $FIELD_NAME != 'default_module' && $FIELD_NAME != 'default_reply_to'}
													<option {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{vtranslate($optionLabel, $QUALIFIED_MODULE)}</option>
												{else if $FIELD_NAME == 'default_reply_to'}
													<option value="{$optionName}" {if $optionName == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{vtranslate($optionName)}</option>
												{else}
													<option value="{$optionName}" {if $optionLabel == $FIELD_DATA[$FIELD_NAME]} selected {/if}>{vtranslate($optionLabel, $optionLabel)}</option>
												{/if}
											{/foreach}
										</select>
										{if $FIELD_NAME == 'default_reply_to'}
											<div class="input-group-addon input-select-addon"><i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_DEFAULT_REPLY_TO_INFO',$QUALIFIED_MODULE)}"></i></div>
										{/if}

									{else if $FIELD_NAME == 'USE_RTE'}
										<input type="hidden" name="{$FIELD_NAME}" value="false" />
										<div class=" "> <input type="checkbox" name="{$FIELD_NAME}" value="true" {if $FIELD_DATA[$FIELD_NAME] == 'true'} checked {/if} /></div>
										{else if $FIELD_NAME == 'email_tracking'}
										<input type="hidden" name="{$FIELD_NAME}" value="No" />
										<input type="checkbox" name="{$FIELD_NAME}" value="Yes" {if $FIELD_DATA[$FIELD_NAME] == "Yes"} checked {/if} />
										<div class="input-info-addon"> <i class="fa fa-question-circle"  data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_PERSONAL_EMAIL_TRACKING_INFO',$QUALIFIED_MODULE)}"></i>
										</div>
									{else}
										<div class=" input-group inputElement"> 
											<input type="text" class="inputElement "  name="{$FIELD_NAME}" data-rule-required="true" {if $FIELD_VALIDATION[$FIELD_NAME]} {$FIELD_VALIDATION[$FIELD_NAME]} {/if} value="{$FIELD_DATA[$FIELD_NAME]}" />
											{if $FIELD_NAME == 'upload_maxsize'}
												<div class="input-group-addon">{vtranslate('LBL_MB', $QUALIFIED_MODULE)}</div>
											{/if}
										</div>
									{/if}
								</div>
							</div>
						{/foreach}
					</div>
					<div class='modal-overlay-footer clearfix'>
						<div class=" row clearfix">
							<div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
								<button type='submit' class='btn btn-success saveButton'  >{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
								<a class='cancelLink' type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}

