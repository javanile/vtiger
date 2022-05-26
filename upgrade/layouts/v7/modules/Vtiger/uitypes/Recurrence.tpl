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
	<div class="row" style="width:540px;">
		<div style="float: left;margin-top: 7px; padding-left:15px;">
			{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes' && !$smarty.request.isDuplicate}
				<input type="hidden" class="recurringEdit" value="true" />
			{/if}
			<input type="checkbox" name="recurringcheck" data-field-id= '{$FIELD_MODEL->get('id')}' value="" {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}checked{/if}/>&nbsp;&nbsp;
		</div>
		<div class="" id="repeatUI" style="visibility: {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}visible{else}collapse{/if};">
			<div>
				<span>
					<span class="alignMiddle">{vtranslate('LBL_REPEATEVENT', $MODULE)}&nbsp;&nbsp;</span>
					<select class="select2 input-mini" name="repeat_frequency">
						{for $FREQUENCY = 1 to 14}
							<option value="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['repeat_frequency']}selected{/if}>{$FREQUENCY}</option>
						{/for}
					</select>
				</span>
				<span>
					<select class="select2 input-medium" style="width:100px;margin-left: 10px;" name="recurringtype" id="recurringType">
						<option value="Daily" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
						<option value="Weekly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
						<option value="Monthly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
						<option value="Yearly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
					</select>
				</span>
				<span>
					<span class="alignMiddle displayInlineBlock">&nbsp;&nbsp;{vtranslate('LBL_UNTIL', $MODULE)}</span>
					<span class="input-group date pull-right inputElement">
						<input type="text" id="calendar_repeat_limit_date" class="dateField input-small form-control" name="calendar_repeat_limit_date" data-date-format="{$USER_MODEL->get('date_format')}" 
							   value="{if $RECURRING_INFORMATION['recurringcheck'] neq 'Yes'}{$TOMORROWDATE}{elseif $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}{$RECURRING_INFORMATION['recurringenddate']}{/if}" 
							   data-rule-date="true" data-rule-required="true"/>
						<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					</span>
				</span>
			</div>
			<div class="row {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'}show{else}hide{/if}"  id="repeatWeekUI" style="margin:10px 0;">
				<span class="col-lg-2">
					<span class="medium" style="padding-left:23px">{ucwords(vtranslate('LBL_ON', $MODULE))}</span>
				</span>
				<span class="col-lg-10">
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="sun_flag" value="sunday" {$RECURRING_INFORMATION['week0']} type="checkbox"/>{vtranslate('LBL_SM_SUN', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="mon_flag" value="monday" {$RECURRING_INFORMATION['week1']} type="checkbox">{vtranslate('LBL_SM_MON', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="tue_flag" value="tuesday" {$RECURRING_INFORMATION['week2']} type="checkbox">{vtranslate('LBL_SM_TUE', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="wed_flag" value="wednesday" {$RECURRING_INFORMATION['week3']} type="checkbox">{vtranslate('LBL_SM_WED', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="thu_flag" value="thursday" {$RECURRING_INFORMATION['week4']} type="checkbox">{vtranslate('LBL_SM_THU', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="fri_flag" value="friday" {$RECURRING_INFORMATION['week5']} type="checkbox">{vtranslate('LBL_SM_FRI', $MODULE)}</label>
					<label class="checkbox" style="margin-left: 30px;display: inline;"><input name="sat_flag" value="saturday" {$RECURRING_INFORMATION['week6']} type="checkbox">{vtranslate('LBL_SM_SAT', $MODULE)}</label>
				</span>
			</div>
			<div class="{if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI" style="margin-top:10px;"RCa>
				<div class="row">
					<span class="col-lg-4">
						<span class="pull-right">
							<input type="radio" id="repeatDate" data-field-id= '{$FIELD_MODEL->get('id')}' name="repeatMonth" checked value="date" {if $RECURRING_INFORMATION['repeatMonth'] eq 'date'} checked {/if}/>
							<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
						</span>	
					</span>
					<span class="col-lg-8">
						<input type="text" id="repeatMonthDate" data-field-id= '{$FIELD_MODEL->get('id')}' class="input-mini" style="width: 50px;" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{if $RECURRING_INFORMATION['repeatMonth_date'] eq ''}2{else}{$RECURRING_INFORMATION['repeatMonth_date']}{/if}"/>
						<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_DAY_OF_THE_MONTH', $MODULE)}</span>
					</span>
					<div class="clearfix"></div>
				</div>

				<div class="row" id="repeatMonthDayUI" style="margin-top: 10px;">
					<span class="col-lg-4">
						<span class="pull-right">
							<input type="radio" id="repeatDay" data-field-id= '{$FIELD_MODEL->get('id')}' name="repeatMonth" value="day" {if $RECURRING_INFORMATION['repeatMonth'] eq 'day'} checked {/if}/>
							<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
						</span>	
					</span>
					<span class="col-lg-2">
						<select id="repeatMonthDayType" class="select2" name="repeatMonth_daytype" style="width: 90px;">
							<option value="first" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $MODULE)}</option>
							<option value="last" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $MODULE)}</option>
						</select>
					</span>
					<span class="col-lg-6 margin0">
						<select id="repeatMonthDay" class="select2" name="repeatMonth_day" style="width: 120px;">
							<option value=0 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 0} selected {/if}>{vtranslate('LBL_DAY0', $MODULE)}</option>
							<option value=1 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 1} selected {/if}>{vtranslate('LBL_DAY1', $MODULE)}</option>
							<option value=2 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 2} selected {/if}>{vtranslate('LBL_DAY2', $MODULE)}</option>
							<option value=3 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 3} selected {/if}>{vtranslate('LBL_DAY3', $MODULE)}</option>
							<option value=4 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 4} selected {/if}>{vtranslate('LBL_DAY4', $MODULE)}</option>
							<option value=5 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 5} selected {/if}>{vtranslate('LBL_DAY5', $MODULE)}</option>
							<option value=6 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 6} selected {/if}>{vtranslate('LBL_DAY6', $MODULE)}</option>
						</select>
					</span>
				</div>
			</div>
		</div>
	</div>
{/strip}