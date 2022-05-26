{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	<div class="mailConverterRuleBlock">
		<div class="details border1px">
			<div class="ruleHead modal-header" style="cursor: move; min-height: 30px; padding: 10px 0px;">
				<strong>
					<img class="alignMiddle" src="{vimage_path('white-drag.png')}" style="margin-left: 10px;" />&nbsp;&nbsp;{vtranslate('LBL_RULE', $QUALIFIED_MODULE)}&nbsp;<span class="sequenceNumber">{$RULE_COUNT}</span>&nbsp;:&nbsp;{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}
					<div class="pull-right" style="padding-right: 10px;">
						{foreach from=$RULE_MODEL->getRecordLinks() item=ACTION_LINK}
							<span {if stripos($ACTION_LINK->getUrl(), 'javascript:')===0}
								onclick='{$ACTION_LINK->getUrl()|substr:strlen("javascript:")}'
								{else}
									onclick='window.location.href = "{$ACTION_LINK->getUrl()}"'
								{/if}>
								<i title="{vtranslate($ACTION_LINK->get('linklabel'), $MODULE)}" class="{$ACTION_LINK->get('linkicon')} alignMiddle cursorPointer"></i>
							</span>&nbsp;&nbsp;
						{/foreach}
					</div>
				</strong>
			</div>
			<fieldset class="marginTop10px">
				<strong class="marginLeft10px">{vtranslate('LBL_CONDITIONS', $QUALIFIED_MODULE)}</strong>
				<hr>
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
					<div class="col-lg-12 padding10">
						<div class="col-lg-1"></div>
						<div class="col-lg-3 fieldLabel"><label>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}</label></div>
						<div class="col-lg-7 fieldValue">
							{if $FIELD_NAME neq 'action' && $FIELD_NAME neq 'assigned_to'}
								{assign var=FIELD_VALUE value=$RULE_MODEL->get($FIELD_NAME)}
								{if $FIELD_NAME eq 'matchusing'}
									{assign var=FIELD_VALUE value=vtranslate('LBL_ANY_CONDITIONS', $QUALIFIED_MODULE)}
									{if $RULE_MODEL->get('matchusing') eq 'AND'}
										{assign var=FIELD_VALUE value=vtranslate('LBL_ALL_CONDITIONS', $QUALIFIED_MODULE)}
									{/if}
								{else if $FIELD_NAME eq 'subject'}
									{vtranslate($RULE_MODEL->get('subjectop'))}&nbsp;&nbsp;&nbsp;
								{else if $FIELD_NAME eq 'body'}
									{vtranslate($RULE_MODEL->get('bodyop'))}&nbsp;&nbsp;&nbsp;
								{/if}
								{$FIELD_VALUE}
							{/if}
						</div>
					</div>
				{/foreach}
				{assign var=ASSIGNED_TO_RULES_ARRAY value=array('CREATE_HelpDesk_FROM', 'CREATE_Leads_SUBJECT', 'CREATE_Contacts_SUBJECT', 'CREATE_Accounts_SUBJECT')}
				{if in_array($RULE_MODEL->get('action'), $ASSIGNED_TO_RULES_ARRAY)}
					<div class="col-lg-12 padding10">
						<div class="col-lg-1"></div>
						<div class="col-lg-3 fieldLabel"><label>{vtranslate('Assigned To')}</label></div>
						<div class="col-lg-7 fieldValue">{$RULE_MODEL->get('assigned_to')}</div>
					</div>
				{/if}
			</fieldset>
			<hr>
			<fieldset class="marginTop10px">
				<strong class="marginLeft10px">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>
				<hr>
				<div class="col-lg-12 padding10" style="padding-bottom: 10px;">
					<div class="col-lg-1"></div>
					<div class="col-lg-3 fieldLabel"><label>{vtranslate('action', $QUALIFIED_MODULE)}</label></div>
					<div class="col-lg-7 fieldValue">{vtranslate($RULE_MODEL->get('action'), $QUALIFIED_MODULE)}</small></div>
				</div>
			</fieldset>
		</div>
	</div>
	<br>
{/strip}
