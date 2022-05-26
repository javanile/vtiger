{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
    {assign var=show_report_scheduled value=true}
    <div class="row">
        <div>
            <label><input type="checkbox" {if $show_report_scheduled eq false} disabled="disabled" {/if} {if $show_report_scheduled eq true and $SCHEDULEDREPORTS->get('scheduleid') neq ''} checked="checked" {/if} value="{if $SCHEDULEDREPORTS->get('scheduleid') neq ''}true{/if}" name='enable_schedule' style="margin-top: 0px !important;"> &nbsp;
                <strong>{vtranslate('LBL_SCHEDULE_REPORTS',$MODULE)}</strong>
            </label>
        </div>
    </div>
    {if $show_report_scheduled eq true}
        <div id="scheduleBox" class='row well contentsBackground {if $SCHEDULEDREPORTS->get('scheduleid') eq ''} hide {/if}'>
            <div class='col-lg-12' style="padding:5px 0px;">
                <div class='col-lg-3' style='position:relative;top:5px;'>{vtranslate('LBL_RUN_REPORT', $MODULE)}</div>
                <div class='col-lg-4'>
                    {assign var=scheduleid value=$SCHEDULEDREPORTS->get('scheduleid')}
                    <select class='select2 inputElement col-lg-3' id='schtypeid' name='schtypeid' style="width: 280px;">
                        <option value="1" {if $scheduleid eq 1}selected{/if}>{vtranslate('LBL_DAILY', $MODULE)}</option>
                        <option value="2" {if $scheduleid eq 2}selected{/if}>{vtranslate('LBL_WEEKLY', $MODULE)}</option>
                        <option value="5" {if $scheduleid eq 5}selected{/if}>{vtranslate('LBL_SPECIFIC_DATE', $QUALIFIED_MODULE)}</option>
                        <option value="3" {if $scheduleid eq 3}selected{/if}>{vtranslate('LBL_MONTHLY_BY_DATE', $MODULE)}</option>
                        <option value="4" {if $scheduleid eq 4}selected{/if}>{vtranslate('LBL_YEARLY', $MODULE)}</option>
                    </select>
                </div>
            </div>

            {* show weekdays for weekly option *}
            <div class='col-lg-12 {if $scheduleid neq 2} hide {/if}' id='scheduledWeekDay' style='padding:5px 0px;'>
                <div class='col-lg-3' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}</div>
                <div class='col-lg-4'>
                    {assign var=dayOfWeek value=Zend_Json::decode($SCHEDULEDREPORTS->get('schdayoftheweek'))}
                    <select style='width:280px;' multiple class='select2'  name='schdayoftheweek' data-rule-required="true" id='schdayoftheweek'>
                        <option value="7" {if is_array($dayOfWeek) && in_array('7', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY0', 'Calendar')}</option>
                        <option value="1" {if is_array($dayOfWeek) && in_array('1', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY1', 'Calendar')}</option>
                        <option value="2" {if is_array($dayOfWeek) && in_array('2', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY2', 'Calendar')}</option>
                        <option value="3" {if is_array($dayOfWeek) && in_array('3', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY3', 'Calendar')}</option>
                        <option value="4" {if is_array($dayOfWeek) && in_array('4', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY4', 'Calendar')}</option>
                        <option value="5" {if is_array($dayOfWeek) && in_array('5', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY5', 'Calendar')}</option>
                        <option value="6" {if is_array($dayOfWeek) && in_array('6', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY6', 'Calendar')}</option>
                    </select>
                </div>
            </div>

            {* show month view by dates *}
            <div class='col-lg-12 {if $scheduleid neq 3} hide {/if}' id='scheduleMonthByDates' style="padding:5px 0px;">
                <div class='col-lg-3' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}</div>
                <div class='col-lg-4'>
                    {assign var=dayOfMonth value=Zend_Json::decode($SCHEDULEDREPORTS->get('schdayofthemonth'))}
                    <select style="width: 280px !important;" multiple class="select2 col-lg-6" data-rule-required="true"  name='schdayofthemonth' id='schdayofthemonth' >
                        {section name=foo loop=31}
                            <option value={$smarty.section.foo.iteration} {if is_array($dayOfMonth) && in_array($smarty.section.foo.iteration, $dayOfMonth)}selected{/if}>{$smarty.section.foo.iteration}</option>
                        {/section}
                    </select>
                </div>
            </div>
            {* show specific date *}
            <div class='col-lg-12 {if $scheduleid neq 5} hide {/if}' id='scheduleByDate' style="padding:5px 0px;">
                <div class='col-lg-3' style='position:relative;top:5px;'>{vtranslate('LBL_CHOOSE_DATE', $MODULE)}</div>
                <div class='col-lg-2'>
                    <div class="input-group inputElement date" style="margin-bottom: 3px">
                        {assign var=specificDate value=Zend_Json::decode($SCHEDULEDREPORTS->get('schdate'))}
                        {if $specificDate[0] neq ''} {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])} {/if}
                        <input style='width: 185px;' type="text" class="dateField form-control" id="schdate" name="schdate" value="{$specificDate1}" data-date-format="{$CURRENT_USER->date_format}" data-rule-required="true" />
                        <span class="input-group-addon"><i class="fa fa-calendar "></i></span>
                    </div>
                </div>
            </div>
            {* show month view by anually *}
            <div class='col-lg-12 {if $scheduleid neq 4} hide {/if}' id='scheduleAnually' style='padding:5px 0px;'>
                <div class='col-lg-3' style='position:relative;top:5px;'>
                    {vtranslate('LBL_SELECT_MONTH_AND_DAY', $MODULE)}
                </div>
                <div class='col-lg-5'>
                    <div id='annualDatePicker'></div>
                </div>
                <div class='col-lg-3'>
                    <div style='padding-bottom:5px;'>{vtranslate('LBL_SELECTED_DATES', $MODULE)}</div>
                    <div>
                        <input type=hidden id=hiddenAnnualDates value='{$SCHEDULEDREPORTS->get('schannualdates')}' />
                        {assign var=ANNUAL_DATES value=Zend_Json::decode($SCHEDULEDREPORTS->get('schannualdates'))}
                        <select multiple class="select2 inputElement col-lg-3" id='annualDates' name='schannualdates' data-rule-required="true"  data-date-format="{$CURRENT_USER->date_format}">
                            {foreach item=DATES from=$ANNUAL_DATES}
                                <option value="{$DATES}" selected>{$DATES}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>

            {* show time for all other than Hourly option*}


            <div class='col-lg-12' id='scheduledTime' style='padding:5px 0px 10px 0px;'>
                <div class='col-lg-3' style='position:relative;top:5px;'>
                    {vtranslate('LBL_AT_TIME', $MODULE)}<span class="redColor">*</span>
                </div>
                <div class='col-lg-2' id='schtime'>
                    <div class='input-group inputElement time'>
						<input type='text' class='timepicker-default form-control ui-timepicker-input' data-format='{$CURRENT_USER->get('hour_format')}' name='schtime' value="{$SCHEDULEDREPORTS->get('schtime')}" data-rule-required="true" data-rule-time="true" />
							<span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                    </div>
                </div>
            </div>
            {* show all the users,groups,roles and subordinat roles*}
            <div class='col-lg-12' id='recipientsList' style='padding:5px 0px 10px 0px;'>
                <div class='col-lg-3' style='position:relative;top:5px;'>
                    {vtranslate('LBL_SELECT_RECIEPIENTS', $MODULE)}<span class="redColor">*</span>
                </div>
                <div class='col-lg-4'>
                    {assign var=ALL_ACTIVEUSER_LIST value=$CURRENT_USER->getAccessibleUsers()}
                    {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENT_USER->getAccessibleGroups()}
                    {assign var=recipients value=Zend_Json::decode($SCHEDULEDREPORTS->get('recipients'))}
                    <select multiple class="select2 col-lg-6" id='recipients' name='recipients' data-rule-required="true" style="width: 280px !important;">
                        <optgroup label="{vtranslate('LBL_USERS')}">
                            {foreach key=USER_ID item=USER_NAME from=$ALL_ACTIVEUSER_LIST}
                                {assign var=USERID value="USER::{$USER_ID}"}
                                <option value="{$USERID}" {if is_array($recipients) && in_array($USERID, $recipients)} selected {/if} data-picklistvalue= '{$USER_NAME}'> {$USER_NAME} </option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('LBL_GROUPS')}">
                            {foreach key=GROUP_ID item=GROUP_NAME from=$ALL_ACTIVEGROUP_LIST}
                                {assign var=GROUPID value="GROUP::{$GROUP_ID}"}
                                <option value="{$GROUPID}" {if is_array($recipients) && in_array($GROUPID, $recipients)} selected {/if} data-picklistvalue= '{$GROUP_NAME}'>{$GROUP_NAME}</option>
                            {/foreach}
                        </optgroup>
                        <optgroup label="{vtranslate('Roles', 'Roles')}">
                            {foreach key=ROLE_ID item=ROLE_OBJ from=$ROLES}
                                {assign var=ROLEID value="ROLE::{$ROLE_ID}"}
                                <option value="{$ROLEID}" {if is_array($recipients) && in_array($ROLEID, $recipients)} selected {/if} data-picklistvalue= '{$ROLE_OBJ->get('rolename')}'>{$ROLE_OBJ->get('rolename')}</option>
                            {/foreach}
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class='col-lg-12' id='specificemailsids' style='padding:5px 0px 10px 0px;'>
                <div class='col-lg-3' style='position:relative;top:5px;'>
                    {vtranslate('LBL_SPECIFIC_EMAIL_ADDRESS', $MODULE)}
                </div>
                <div class='col-lg-4'>
                    {assign var=specificemailids value=Zend_Json::decode($SCHEDULEDREPORTS->get('specificemails'))}
                    <input id="specificemails" style="width: 281px !important;" class="col-lg-6 inputElement" type="text" value="{$specificemailids}" name="specificemails" data-validation-engine="validate[funcCall[Vtiger_MultiEmails_Validator_Js.invokeValidation]]"></input>
                </div>
            </div>
            {if $TYPE neq 'Chart'}
                <div class='col-lg-12' id='fileformat' style='padding:5px 0px 10px 0px;'>
                    <div class='col-lg-3' style='position:relative;top:5px;'>
                        {vtranslate('LBL_FILE_FORMAT', $MODULE)}
                    </div>
                    <div class='col-lg-2'>
                        <select class="select2 inputElement" id='fileformat' name='fileformat' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" >
                            <option value="CSV" {if $SCHEDULEDREPORTS->get('fileformat') eq 'CSV'} selected {/if} data-picklistvalue= 'CSV'>CSV</option>
                            <option value="XLS" {if $SCHEDULEDREPORTS->get('fileformat') eq 'XLS'} selected {/if} data-picklistvalue= 'XLS'>Excel</option>
                        </select>
                    </div>
                </div>
            {/if}
            {if $SCHEDULEDREPORTS->get('next_trigger_time')}
                <div class="col-lg-12" style="padding:5px 0px 10px 0px;">
                    <div class='col-lg-3'>
                        <span class=''>{vtranslate('LBL_NEXT_TRIGGER_TIME', $MODULE)}</span>
                    </div>
                    <div class='col-lg-5'>
                        {$SCHEDULEDREPORTS->getNextTriggerTimeInUserFormat()}<span>&nbsp;({$CURRENT_USER->time_zone})</span>
                    </div>
                </div>
            {/if}
        </div>
    {/if}
{/strip}