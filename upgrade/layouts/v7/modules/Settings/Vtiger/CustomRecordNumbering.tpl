{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class=" detailViewContainer col-lg-12 col-md-12 col-sm-12">
		<form id="EditView" method="POST">
			<div class="blockData">
				<div class="clearfix">
					<div class="btn-group pull-right">
						<button type="button" class="btn addButton btn-default" name="updateRecordWithSequenceNumber">{vtranslate('LBL_UPDATE_MISSING_RECORD_SEQUENCE', $QUALIFIED_MODULE)}</button>
					</div>
					<div>
						<h4>{vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}</h4>
					</div>
				</div>
				<hr>
				<br>
				<div class="table form-horizontal no-border" id="customRecordNumbering" >
					{assign var=DEFAULT_MODULE_DATA value=$DEFAULT_MODULE_MODEL->getModuleCustomNumberingData()}
					{assign var=DEFAULT_MODULE_NAME value=$DEFAULT_MODULE_MODEL->getName()}
					{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
					<div class="row form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
							<label>{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
						</div>
						<div class="fieldValue {$WIDTHTYPE}">
							<div class=" col-lg-4 col-md-4 col-sm-4">
								<select class="select2 inputElement " name="sourceModule">
									{foreach key=index item=MODULE_MODEL from=$SUPPORTED_MODULES}
										{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
										<option value={$MODULE_NAME} {if $MODULE_NAME eq $DEFAULT_MODULE_NAME} selected {/if}>
											{vtranslate($MODULE_NAME, $MODULE_NAME)}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
							<label >{vtranslate('LBL_USE_PREFIX', $QUALIFIED_MODULE)}</label>
						</div>
						<div class="fieldValue {$WIDTHTYPE}">
							<div class=" col-lg-4 col-md-4 col-sm-4">
								<input type="text" id="prefix" class="inputElement" value="{$DEFAULT_MODULE_DATA['prefix']}" data-old-prefix="{$DEFAULT_MODULE_DATA['prefix']}" name="prefix" />
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
							<label>
								<b>{vtranslate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}</b>&nbsp;<span class="redColor">*</span>
							</label>
						</div>
						<div class="fieldValue {$WIDTHTYPE}">
							<div class=" col-lg-4 col-md-4 col-sm-4">
								<input type="text" value="{$DEFAULT_MODULE_DATA['sequenceNumber']}" class="inputElement " id="sequence"
									data-old-sequence-number="{$DEFAULT_MODULE_DATA['sequenceNumber']}" data-rule-required = "true" data-rule-positive="true" data-rule-wholeNumber="true" name="sequenceNumber"/>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class='modal-overlay-footer clearfix'>
				<div class="row clearfix">
					<div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
						<button class="btn btn-success saveButton" type="submit" disabled="disabled">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
						<a class='cancelLink' href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}