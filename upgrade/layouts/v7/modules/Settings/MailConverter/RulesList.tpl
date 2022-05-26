{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="listViewContentDiv col-lg-12">
		<h4>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h4>
		<hr>
		{if !$RECORD_EXISTS}
			<div class="mailConveterDesc">
				<center><br><br>
					<div>{vtranslate('LBL_MAILCONVERTER_DESCRIPTION', $QUALIFIED_MODULE)}</div>
					<img src="{vimage_path('MailConverter.png')}" alt="Mail Converter"><br><br>
					<a onclick="window.location.href='{$MODULE_MODEL->getCreateRecordUrl()}'" style="color: #15c !important;"><u class="cursorPointer" style="font-size:12pt;">{vtranslate('LBL_CREATE_MAILBOX_NOW', $QUALIFIED_MODULE)}</u></a>
					<br><br>
				</center>
			</div>
		{else}
			<input type="hidden" id="scannerId" value="{$SCANNER_ID}"/>
			<div class="col-lg-12">
				<div class="col-lg-4 mailBoxDropdownWrapper" style="padding-left: 0px;">
					<select class="mailBoxDropdown select2" style="max-width: 300px; min-width: 200px;">
						{foreach item=SCANNER from=$MAILBOXES}
							<option value="{$SCANNER['scannerid']}" {if $SCANNER_ID eq $SCANNER['scannerid']}selected{/if}>{$SCANNER['scannername']}</option>
						{/foreach}
					</select>
				</div>
				<div class="col-lg-4" id="mailConverterStats">
					{if $CRON_RECORD_MODEL->isEnabled()}
						{if $CRON_RECORD_MODEL->hadTimedout()}
							{vtranslate('LBL_LAST_SCAN_TIMED_OUT', $QUALIFIED_MODULE_NAME)}.
						{elseif $CRON_RECORD_MODEL->getLastEndDateTime() neq ''}
							{vtranslate('LBL_LAST_SCAN_AT', $QUALIFIED_MODULE_NAME)}
							{$CRON_RECORD_MODEL->getLastEndDateTime()}
							<br />
							{vtranslate('LBL_FOLDERS_SCANNED', $QUALIFIED_MODULE_NAME)}&nbsp;:&nbsp;
							{foreach from=$FOLDERS_SCANNED item=FOLDER}<strong>{$FOLDER}&nbsp;&nbsp;</strong>{/foreach}
						{/if}
					{/if}
				</div>
				<div class="col-lg-4" style="padding-right: 0px;">
					<div class="btn-group pull-right">
						<button class="btn btn-default addButton" id="addRuleButton" title="{vtranslate('LBL_DRAG_AND_DROP_BLOCK_TO_PRIORITISE_THE_RULE', $QUALIFIED_MODULE)}"
							{if stripos($SCANNER_MODEL->getCreateRuleRecordUrl(), 'javascript:')===0}
								onclick='{$SCANNER_MODEL->getCreateRuleRecordUrl()|substr:strlen("javascript:")}' 
							{else}
								onclick='window.location.href="{$SCANNER_MODEL->getCreateRuleRecordUrl()}"'
							{/if}>
							<i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_RULE', $QUALIFIED_MODULE)}
						</button>
						<button href="javascript:void(0);" data-toggle="dropdown" class="btn btn-default" style="margin-left: 4px;">
							{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE_NAME)}&nbsp;<i class="caret"></i>
						</button>
						<ul class="dropdown-menu pull-right">
							{foreach item=LINK from=$RECORD->getRecordLinks()}
								<li>
									<a {if strpos($LINK->getUrl(), 'javascript:')===0} href='javascript:void(0);' onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'{else}href={$LINK->getUrl()}{/if}>
										{vtranslate($LINK->getLabel(), $QUALIFIED_MODULE)}
									</a>
								</li>
							{/foreach}
						</ul>
					</div>
				</div>
			</div>	
			<br>
			<div id="mailConverterBody" class="col-lg-12">
				<br>
				<div id="rulesList">
					{if count($RULE_MODELS_LIST)}
						{assign var=RULE_COUNT value=1}
						{assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
						{foreach from=$RULE_MODELS_LIST item=RULE_MODEL}
							<div class="row-fluid padding-bottom1per rule" data-id="{$RULE_MODEL->get('ruleid')}" data-blockid="block_{$RULE_MODEL->get('ruleid')}">
								{include file="Rule.tpl"|@vtemplate_path:$QUALIFIED_MODULE RULE_COUNT=$RULE_COUNT}
							</div>
							{assign var=RULE_COUNT value=$RULE_COUNT+1}
						{/foreach}
					{else}
						<div class="details border1px" style="text-align: center; min-height: 200px; padding-top: 100px;">
							{vtranslate('LBL_NO_RULES', $QUALIFIED_MODULE)}
						</div>
					{/if}
				</div>
			</div>
		{/if}
{/strip}
