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
    <div class="mailConverterRuleBlock"> 
	<div class="blockHeader ruleHead" style="cursor: move;">
	    <div colspan="4">
		<img class="alignMiddle" src="{vimage_path('drag.png')}" style="margin-left: 10px;" />&nbsp;&nbsp;{vtranslate('LBL_RULE',$QUALIFIED_MODULE)}&nbsp;<span class="sequenceNumber">{$RULE_COUNT}</span>&nbsp;:&nbsp;{vtranslate($RULE_MODEL->get('action'),$QUALIFIED_MODULE)}
		<div class="pull-right">
		    {foreach from=$RULE_MODEL->getRecordLinks() item=ACTION_LINK}
			<a {if stripos($ACTION_LINK->getUrl(), 'javascript:')===0} onclick='{$ACTION_LINK->getUrl()|substr:strlen("javascript:")}' 
										   {else} onclick='window.location.href="{$ACTION_LINK->getUrl()}"' {/if}><i title="{vtranslate($ACTION_LINK->get('linklabel'), $MODULE)}" class="{$ACTION_LINK->get('linkicon')} alignMiddle cursorPointer"></i></a>&nbsp;&nbsp;
				{/foreach}
				</div>	
			    </div>
			</div>
			<div>
			    <fieldset>
				<legend class="mailConverterRuleLegend"><div style="margin-left: 20px;">{vtranslate('LBL_CONDITIONS', $QUALIFIED_MODULE)}</div></legend>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_FROM',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span3" style="margin-left: 17px;">&nbsp;{$RULE_MODEL->get('fromaddress')}</div>
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_TO',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span5" style="margin-left: 17px;">&nbsp;{$RULE_MODEL->get('toaddress')}</div>
				</div>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_CC',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span3" style="margin-left: 17px;">&nbsp;{$RULE_MODEL->get('cc')}</div>
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_BCC',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span5" style="margin-left: 17px;">&nbsp;{$RULE_MODEL->get('bcc')}</div>
				</div>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_SUBJECT',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span10"><p class="pull-left"><small><strong>{vtranslate($RULE_MODEL->get('subjectop'))}</strong></small></p>&nbsp;&nbsp;&nbsp;{$RULE_MODEL->get('subject')}</div>
				</div>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_BODY',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span10"><p class="pull-left"><small><strong>{vtranslate($RULE_MODEL->get('bodyop'))}</strong></small></p>&nbsp;&nbsp;&nbsp;{$RULE_MODEL->get('body')}</div>
				</div>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_MATCH',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span10"><small>{if $RULE_MODEL->get('matchusing') eq 'AND'}{vtranslate('LBL_ALL_CONDITIONS',$QUALIFIED_MODULE)}{else}{vtranslate('LBL_ANY_CONDITIONS',$QUALIFIED_MODULE)}{/if}</small></div>
				</div>
				{assign var=ASSIGNED_TO_RULES_ARRAY value=array('CREATE_HelpDesk_FROM', 'CREATE_Leads_SUBJECT', 'CREATE_Contacts_SUBJECT', 'CREATE_Accounts_SUBJECT')}
				{if in_array($RULE_MODEL->get('action'), $ASSIGNED_TO_RULES_ARRAY)}
				    <div class="span12 row-fluid">
					<div class="span2 rightAligned"><strong>{vtranslate('Assigned To')}</strong></div>
					<div class="span10"><small>{$RULE_MODEL->get('assigned_to')}</small></div>
				    </div>
				{/if}
			    </fieldset>
			    <fieldset style="margin-top: 10px;">
				<legend class="mailConverterRuleLegend"><div style="margin-left: 20px;">{vtranslate('action', $QUALIFIED_MODULE)}</div></legend>
				<div class="span12 row-fluid">
				    <div class="span2 rightAligned"><strong>{vtranslate('LBL_ACTION',$QUALIFIED_MODULE)}</strong></div>
				    <div class="span10"><small>{vtranslate($RULE_MODEL->get('action'),$QUALIFIED_MODULE)}</small></div>
				</div>
			    </fieldset>
			</div>
		    </div>
		    <div class="clearfix"></div>				
{/strip}							
