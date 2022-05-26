{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{if !empty($EMAIL_FIELDS_LIST)}
		<div class="modal-dialog modal-sm">
			<div class="model-content">
				<form class="form-horizontal" method="post" action="index.php" id="recipientsForm">
					{assign var=HEADER_TITLE value={vtranslate('LBL_RECIPIENT_PREFS', $MODULE)}}
					{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

					<div class="modal-body" id="multiEmailContainer">
						<input type="hidden" name="module" value="{$MODULE}"/>
						<input type="hidden" name="action" value="RecipientPreferencesSaveAjax"/>
						<input type="hidden" name="source_module" value={$SOURCE_MODULE} />
						<div class='' style="padding-left:20px;">
							{counter start=0 skip=1 assign="count"}
							{foreach item=FIELDS_INFO key=EMAIL_MODULE_ID from=$EMAIL_FIELDS_LIST}
								{assign var=MODULE_NAME value={getTabname($EMAIL_MODULE_ID)}}
								<div class="">
									{foreach item=EMAIL_FIELD key =EMAIL_FIELD_NAME from=$FIELDS_INFO}
										<label class="checkbox">
											<input type="checkbox" class="emailField" name="selectedFields[{$count}]" value='{ZEND_JSON::encode(['field_id'=> $EMAIL_FIELD->getId(),'module_id'=>$EMAIL_MODULE_ID])}' {if $EMAIL_FIELD->get('isPreferred')}checked="true"{/if}/>
											&nbsp; {vtranslate($EMAIL_FIELD->get('label'), $EMAIL_FIELD->getModuleName())}{if !empty($EMAIL_VALUE)}({$EMAIL_VALUE}) {/if}
										</label>
										{counter}
									{/foreach}
								</div>
							{/foreach}
						</div>
					</div>
					<div class='modal-footer'>
						<center>
							<button class="btn btn-success savePreference" type="submit" name="savePreference"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</center>
					</div>
				</form>
			</div>
		</div>
	{else}
		<div class="modal-dialog modal-sm">
			<div class="model-content">
				<div class="modal-header" style="border-bottom: none;">
					<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
				</div>
				<div class="modal-body"><div class="padding20">{vtranslate('LBL_PLEASE_ADD_EMAIL_FIELDS',$MODULE)}</div></div>
			</div>
		</div>
	{/if}
{/strip}

