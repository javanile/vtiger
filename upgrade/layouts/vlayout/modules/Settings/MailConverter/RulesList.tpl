{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="listViewPageDiv">
	<h3>{vtranslate($MODULE,$QUALIFIED_MODULE)}</h3>
	<hr>
	{if !$RECORD_EXISTS}
	    <div class="mailConveterDesc">
		<center> <br /><br />
		    <div>
			{vtranslate('LBL_MAILCONVERTER_DESCRIPTION', $QUALIFIED_MODULE)}
		    </div>
		    <img src="layouts/vlayout/skins/images/MailConverter.png" alt="Mail Converter"> <br /><br />
		    <a onclick="window.location.href = 'index.php?module=MailConverter&parent=Settings&view=Edit&mode=step1&create=new';"><u class="cursorPointer" style="font-size:12pt;">{vtranslate('LBL_CREATE_MAILBOX_NOW', $QUALIFIED_MODULE)}</u></a>
			<br><br>
		</center>
	    </div>
	{else}
	    <input type="hidden" id="scannerId" value="{$SCANNER_ID}"/>
	    <div>
		<div class="span5">
		    <label class="span2" id="mailBoxLabel">{vtranslate('LBL_MAILBOX', $QUALIFIED_MODULE)}</label>
		    <div class="mailBoxDropdownWrapper">
			<select class="mailBoxDropdown">
			    {foreach item=SCANNER from=$MAILBOXES}
				<option value="{$SCANNER['scannerid']}" {if $SCANNER_ID eq $SCANNER['scannerid']}selected{/if}>{$SCANNER['scannername']}</option>
			    {/foreach}
			</select>
		    </div>
		</div>
		<div class="span4" id="mailConverterStats">
		    {if $CRON_RECORD_MODEL->isEnabled()}
			{if $CRON_RECORD_MODEL->hadTimedout}
			    {vtranslate('LBL_LAST_SCAN_TIMED_OUT',$QUALIFIED_MODULE_NAME)}.
			{elseif $CRON_RECORD_MODEL->getLastEndDateTime() neq ''}
			    {vtranslate('LBL_LAST_SCAN_AT',$QUALIFIED_MODULE_NAME)}
			    {$CRON_RECORD_MODEL->getLastEndDateTime()}
			    <br />
			    {vtranslate('LBL_FOLDERS_SCANNED',$QUALIFIED_MODULE_NAME)}&nbsp;:&nbsp;
			{foreach from=$FOLDERS_SCANNED item=FOLDER}<strong>{$FOLDER}&nbsp;&nbsp;</strong>{/foreach}
		    {/if}
		{/if}
	    </div>
        <div class="btn-toolbar pull-right">
          <div class="btn-group">
              <button class="btn dropdown-toggle" data-toggle="dropdown">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE_NAME)}&nbsp;
                  <span class="caret"></span>
              </button>
            <ul class="dropdown-menu">
                {foreach item=LINK from=$RECORD->getRecordLinks()}
                <li>
                    <a style="text-shadow: none" 
                       {if strpos($LINK->getUrl(), 'javascript:')===0} href='javascript:void(0);' onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
                    {else} href={$LINK->getUrl()} {/if}>{vtranslate($LINK->getLabel(),$QUALIFIED_MODULE)}
                    </a>
                </li>
                {/foreach}
            </ul>
          </div>
          <div class="btn-group">
            <button class="btn addButton" onclick="javascript:Settings_MailConverter_List_Js.checkMailBoxMaxLimit('index.php?module=MailConverter&parent=Settings&action=CheckMailBoxMaxLimit&mode=step1&create=new');">
                <i class="icon-plus"></i>&nbsp;<strong>{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE_NAME)}</strong>
            </button>
          </div>
        </div>
        <div class="row-fluid">
            <div id="mailConverterBody" class="span12">
                <div class="row-fluid">
                    <div class="span2" id="addRuleButton">
                        <button class="btn addButton" {if stripos($SCANNER_MODEL->getCreateRuleRecordUrl(), 'javascript:')===0} onclick='{$SCANNER_MODEL->getCreateRuleRecordUrl()|substr:strlen("javascript:")}' 
                            {else} onclick='window.location.href="{$SCANNER_MODEL->getCreateRuleRecordUrl()}"' {/if}><i class="icon-plus"></i>&nbsp;&nbsp;<strong>{vtranslate('LBL_ADD_RULE',$QUALIFIED_MODULE)}</strong></button>	
                    </div> 
                    <div class="row-fluid padding-bottom1per">
                        <div class="pull-right" id="mailConverterDragIcon"><i class="icon-info-sign"></i>&nbsp;&nbsp;{vtranslate('LBL_DRAG_AND_DROP_BLOCK_TO_PRIORITISE_THE_RULE',$QUALIFIED_MODULE)}</div>
                    </div>
                        <div class="clearfix"></div>
                </div><br>
                <div id="rulesList">
                {assign var=RULE_COUNT value=1}
                {foreach from=$RULE_MODELS_LIST item=RULE_MODEL}
                    <div class="row-fluid padding-bottom1per rule" data-id="{$RULE_MODEL->get('ruleid')}" data-blockid="block_{$RULE_MODEL->get('ruleid')}">
                    {include file="Rule.tpl"|@vtemplate_path:$QUALIFIED_MODULE RULE_COUNT=$RULE_COUNT}
                    </div>
                    {assign var=RULE_COUNT value=$RULE_COUNT+1}
                {/foreach}
                </div>
            </div>
        </div>
    </div>
    {/if}
{/strip}
