{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Profiles/views/DeleteAjax.php *}

{strip}
	<div class="modal-dialog modelContainer">
		{assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_PROFILE', $QUALIFIED_MODULE)}|cat:" - "|cat:{$RECORD_MODEL->getName()}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<div class="modal-content">
			<form class="form-horizontal" id="DeleteModal" name="AddComment" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="Delete" />
				<input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />
				<div name='massEditContent'>
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_TRANSFER_ROLES_TO_PROFILE',$QUALIFIED_MODULE)}</label>
							<div class="controls fieldValue col-xs-6">
								<select id="transfer_record" name="transfer_record" class="select2 col-xs-9">
									<optgroup label="{vtranslate('LBL_PROFILES', $QUALIFIED_MODULE)}">
										{foreach from=$ALL_RECORDS item=PROFILE_MODEL}
											{assign var=PROFILE_ID value=$PROFILE_MODEL->get('profileid')}
											{if $PROFILE_ID neq $RECORD_MODEL->getId()}
												<option value="{$PROFILE_ID}">{$PROFILE_MODEL->get('profilename')}</option>
											{/if}
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}


