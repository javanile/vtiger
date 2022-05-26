{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	{if $IMAP_ERROR || $CONNECTION_ERROR}
		<div class="block">
			<strong>
				{if $IMAP_ERROR}
					{$IMAP_ERROR}
				{else if $CONNECTION_ERROR}
					{vtranslate('LBL_CONNECTION_ERROR', $QUALIFIED_MODULE)}
				{/if}
			</strong>
		</div>
		<br>
	{/if}
	<div class="addMailBoxBlock">
		<div class="row col-lg-12 padding-bottom1per">
			<div id="mailConverterDragIcon"><i class="icon-info-sign"></i>&nbsp;&nbsp;{vtranslate('TO_CHANGE_THE_FOLDER_SELECTION_DESELECT_ANY_OF_THE_SELECTED_FOLDERS', $QUALIFIED_MODULE)}</div>
		</div>
		<br>
		<br>
		<form class="form-horizontal" id="mailBoxEditView" name="step2">
			<div class="block">
				<div class="addMailBoxStep row" style="margin-top: 10px; margin-bottom: 10px;">
					{foreach key=FOLDER item=SELECTED from=$FOLDERS}
						<div class="col-lg-3">
							<label>
								<input type="checkbox" name="folders" value="{$FOLDER}" {if $SELECTED eq 'checked'}checked{/if}>
								<span>&nbsp;&nbsp;{$FOLDER}</span>
							</label>
						</div>
					{/foreach}
				</div>
				<div class="border1px modal-overlay-footer clearfix">
					<div class="row clearfix">
						<div class="textAlignCenter col-lg-12 col-md-12 col-lg-12 ">
							<button class="btn btn-danger backStep" type="button" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
							<button class="btn btn-success" onclick="javascript:Settings_MailConverter_Edit_Js.secondStep()">
								<strong>
									{if $CREATE eq 'new'}{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}{/if}
								</strong>
							</button>
							<a class="cancelLink" type="reset" onclick="javascript:window.history.go(-2);">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
</div>
{/strip}