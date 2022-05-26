{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Vtiger/views/OutgoingServerDetail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="detailViewContainer" id="OutgoingServerDetails">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="clearfix">
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<h3 style="margin-top: 0px;">{vtranslate('LBL_OUTGOING_SERVER', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
					<div class="btn-group pull-right">
						<button class="btn btn-default editButton" data-url='{$MODEL->getEditViewUrl()}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
			<div>
				{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
				<div class="block">
					<div>
						<h4>{vtranslate('LBL_MAIL_SERVER_SMTP', $QUALIFIED_MODULE)}</h4>
					</div>
					<hr>
					<table class="table editview-table no-border">
						<tbody>
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel"style="width:25%" ><label>{vtranslate('LBL_SERVER_NAME', $QUALIFIED_MODULE)}</label></td>
								<td class="{$WIDTHTYPE} fieldValue"><span>{$MODEL->get('server')}</span></td>
							</tr>
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel" ><label>{vtranslate('LBL_USER_NAME', $QUALIFIED_MODULE)}</label></td>
								<td class="{$WIDTHTYPE} fieldValue" ><span>{$MODEL->get('server_username')}</span></td>
							</tr>
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel"><label>{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}</label></td>
								<td class="{$WIDTHTYPE}" style="border-left: none;">
									<span class="password">{if $MODEL->get('server_password') neq ''}******{/if}&nbsp;</span>
								</td>
							</tr>
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel"><label>{vtranslate('LBL_FROM_EMAIL', $QUALIFIED_MODULE)}</label></td>
								<td class="{$WIDTHTYPE} fieldValue"><span>{$MODEL->get('from_email_field')}</span></td>
							</tr>
							<tr>
								<td class="{$WIDTHTYPE} fieldLabel"><label>{vtranslate('LBL_REQUIRES_AUTHENTICATION', $QUALIFIED_MODULE)}</label></td>
								<td class="{$WIDTHTYPE} fieldValue"><span>{if $MODEL->isSmtpAuthEnabled()}{vtranslate('LBL_YES', $QUALIFIED_MODULE)} {else}{vtranslate('LBL_NO', $QUALIFIED_MODULE)}{/if}</span></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
{/strip}
