{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{include file="partials/EditViewContents.tpl"|@vtemplate_path:'Vtiger'}
	<div name='editContent'>
		<div class='fieldBlockContainer' data-block="{$BLOCK_LABEL}">
			<h4 class='fieldBlockHeader'>{vtranslate('LBL_INVITE_USER_BLOCK', $MODULE)}</h4>
			<hr>
			<table class="table table-borderless">
				<tr>
					<td class="fieldLabel alignMiddle">{vtranslate('LBL_INVITE_USERS', $MODULE)}</td>
					<td class="fieldValue">
						<select id="selectedUsers" class="select2 inputElement" multiple name="selectedusers[]">
							{foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
								{if $USER_ID eq $CURRENT_USER->getId()}
									{continue}
								{/if}
								<option value="{$USER_ID}" {if in_array($USER_ID,$INVITIES_SELECTED)}selected{/if}>
									{$USER_NAME}
								</option>
							{/foreach}
						</select>
					</td>
					<td></td><td></td>
				</tr>
			</table>
			<input type="hidden" name="recurringEditMode" value="" />
			<!--Confirmation modal for updating Recurring Events-->
			{assign var=MODULE value="Calendar"}
			<div class="modal-dialog modelContainer recurringEventsUpdation modal-content hide" style='min-width:350px;'>
				{assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_RECURRING_EVENT', $MODULE)}}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="modal-body">
					<div class="container-fluid">
						<div class="row" style="padding: 1%;padding-left: 3%;">{vtranslate('LBL_EDIT_RECURRING_EVENTS_INFO', $MODULE)}</div>
						<div class="row" style="padding: 1%;">
							<span class="col-sm-12">
								<span class="col-sm-4">
									<button class="btn btn-default onlyThisEvent" style="width : 150px">{vtranslate('LBL_ONLY_THIS_EVENT', $MODULE)}</button>
								</span>
								<span class="col-sm-8">{vtranslate('LBL_ONLY_THIS_EVENT_EDIT_INFO', $MODULE)}</span>
							</span>
						</div>
						<div class="row" style="padding: 1%;">
							<span class="col-sm-12">
								<span class="col-sm-4">
									<button class="btn btn-default futureEvents" style="width : 150px">{vtranslate('LBL_FUTURE_EVENTS', $MODULE)}</button>
								</span>
								<span class="col-sm-8">{vtranslate('LBL_FUTURE_EVENTS_EDIT_INFO', $MODULE)}</span>
							</span>
						</div>
						<div class="row" style="padding: 1%;">
							<span class="col-sm-12">
								<span class="col-sm-4">
									<button class="btn btn-default allEvents" style="width : 150px">{vtranslate('LBL_ALL_EVENTS', $MODULE)}</button>
								</span>
								<span class="col-sm-8">{vtranslate('LBL_ALL_EVENTS_EDIT_INFO', $MODULE)}</span>
							</span>
						</div>
					</div>
				</div>
			</div>
			<!--Confirmation modal for updating Recurring Events--> 
		</div>
	</div>
{/strip}