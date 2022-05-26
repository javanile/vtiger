{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Calendar/views/QuickCreateFollowupAjax.php *}
{strip}
<div class="modal-dialog modelContainer modal-content">
	{assign var=HEADER_TITLE value={vtranslate('LBL_CREATE_FOLLOWUP_EVENT', "Events")}}
	{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
	<form class="form-horizontal followupCreateView" id="followupQuickCreate" name="followupQuickCreate" method="post" action="index.php">
		<div class="modal-body">
			{assign var=RECORD_ID value="{$RECORD_MODEL->get('id')}"}
			{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
			{assign var="timeformat" value=$USER_MODEL->get('hour_format')}
			{assign var="currentDate" value=Vtiger_Date_UIType::getDisplayDateValue('')}
			{assign var="time" value=Vtiger_Time_UIType::getDisplayTimeValue(null)}
			{assign var="currentTimeInVtigerFormat" value=Vtiger_Time_UIType::getDisplayValue($time)}
			{assign var=FOLLOW_UP_LABEL value={vtranslate('LBL_HOLD_FOLLOWUP_ON',"Events")}}
			<input type="hidden" name="module" value="{$MODULE}">
			<input type="hidden" name="action" value="SaveFollowupAjax" />
			<input type="hidden" name="mode" value="createFollowupEvent">
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
			<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
			<input class="dateField" type="hidden" name="date_start" value="{$STARTDATE}" data-date-format="{$dateFormat}" data-fieldinfo="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($STARTDATEFIELDMODEL))}"/>
			{$FIELD_INFO['label'] = {$FOLLOW_UP_LABEL}}

			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-4 fieldLabel" style="padding-top:1%">
						<label class="muted pull-right">
							{$FOLLOW_UP_LABEL}
						</label>
					</div>
					<div class="col-sm-6 fieldValue">
						<div>
							<div class="input-group inputElement" style="margin-bottom: 3px">
							<input type="text" class="dateField form-control" data-fieldname="followup_date_start" data-fieldtype="date" name="followup_date_start" data-date-format="{$dateFormat}"
								value="{$currentDate}" data-rule-required="true" data-rule-greaterThanOrEqualToToday="true"/>
							<span class="input-group-addon"><i class="fa fa-calendar "></i></span>
							</div>
						</div>
						<div>
							<div class="input-group inputElement time" >
								<input type="text" data-format="{$timeformat}" class="timepicker-default form-control" value="{$currentTimeInVtigerFormat}" name="followup_time_start"
								 data-rule-required="true" />
								<span  class="input-group-addon">
									<i  class="fa fa-clock-o"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		{assign var=BUTTON_NAME value={vtranslate('LBL_CREATE', $MODULE)}}
		{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
	</form>
</div>
{/strip}
