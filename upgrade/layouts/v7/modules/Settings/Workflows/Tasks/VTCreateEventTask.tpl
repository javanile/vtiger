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
	{assign var=SHOWN_FIELDS_LIST value=array()}
	<div class="row" style="margin-bottom: 70px;">
        <div class="col-sm-9 col-xs-9">
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_EVENT_NAME',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-sm-9 col-xs-9">
                    <input data-rule-required="true" class="inputElement" name="eventName" type="text" value="{$TASK_OBJECT->eventName}" />
                    {$SHOWN_FIELDS_LIST['subject'] = 'subject'}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-9 col-xs-9">
                    <textarea class="inputElement" style="height: inherit;" name="description">{$TASK_OBJECT->description}</textarea>
                    {$SHOWN_FIELDS_LIST['description'] = 'description'}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-5 col-xs-5">
                    {assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('eventstatus')->getPickListValues()}
                    <select name="status" class="select2">
                        {foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
                            <option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
                        {/foreach}
                    </select>
                </div>
                {$SHOWN_FIELDS_LIST['eventstatus'] = 'eventstatus'}
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_TYPE',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-5 col-xs-5">
                    {assign var=EVENTTYPE_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitytype')->getPickListValues()}
                    <select name="eventType" class="select2">
                        {foreach  from=$EVENTTYPE_PICKLIST_VALUES item=EVENTTYPE_PICKLIST_VALUE key=EVENTTYPE_PICKLIST_KEY}
                            <option value="{$EVENTTYPE_PICKLIST_KEY}" {if $EVENTTYPE_PICKLIST_KEY eq $TASK_OBJECT->eventType} selected="" {/if}>{$EVENTTYPE_PICKLIST_VALUE}</option>
                        {/foreach}
                    </select>
                </div>
                {$SHOWN_FIELDS_LIST['activitytype'] = 'activitytype'}
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-5 col-xs-5">
                    <select name="assigned_user_id" class="select2">
                        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
                        {foreach from=$ASSIGNED_TO key=LABEL item=ASSIGNED_USERS_LIST}
                            <optgroup label="{vtranslate($LABEL,$QUALIFIED_MODULE)}">
                                {foreach from=$ASSIGNED_USERS_LIST item=ASSIGNED_USER key=ASSIGNED_USER_KEY}
                                    <option value="{$ASSIGNED_USER_KEY}" {if $ASSIGNED_USER_KEY eq $TASK_OBJECT->assigned_user_id} selected="" {/if}>{$ASSIGNED_USER}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                        <optgroup label="{vtranslate('LBL_SPECIAL_OPTIONS')}">
                                <option value="copyParentOwner" {if $TASK_OBJECT->assigned_user_id eq 'copyParentOwner'} selected="" {/if}>{vtranslate('LBL_PARENT_OWNER')}</option>
                        </optgroup>
                    </select>
                </div>
                {$SHOWN_FIELDS_LIST['assigned_user_id'] = 'assigned_user_id'}
            </div>
            <div class="row form-group">
                {if $TASK_OBJECT->startTime neq ''}
                    {assign var=START_TIME value=$TASK_OBJECT->startTime}
                {/if}
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_START_TIME',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-3 col-xs-3" >
                    <div class="input-group time">
                        {if $TASK_OBJECT->time neq ''}
                            {assign var=TIME value=$TASK_OBJECT->time}
                        {/if}
                        <input type="text" class="timepicker-default inputElement" data-format="{$timeFormat}" value="{$START_TIME}" name="startTime" />
                        <span  class="input-group-addon">
                            <i  class="fa fa-clock-o"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_START_DATE',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-2 col-xs-2">
                    <div class="row">
                        <div class="col-sm-8 col-xs-8">
                            <input class="inputElement" type="text" value="{$TASK_OBJECT->startDays}" name="startDays" 
                                   data-rule-WholeNumber="true">&nbsp;
                        </div>
                        <span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
                    </div>
                </div>
                <span class="col-sm-2 col-xs-2">
                    <select class="select2" name="startDirection" style="width: 100%">
                        <option  {if $TASK_OBJECT->startDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
                        <option {if $TASK_OBJECT->startDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
                    </select>
                </span>
                <span class="col-sm-6 col-xs-6">
                    <select class="select2" name="startDatefield">
                        {foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
                            <option {if $TASK_OBJECT->startDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'), $DATETIME_FIELD->getModuleName())}</option>
                        {/foreach}
                    </select>
                </span>
                {$SHOWN_FIELDS_LIST['date_start'] = 'date_start'}
            </div>
            <div class="row form-group">
                {if $TASK_OBJECT->endTime neq ''}
                    {assign var=END_TIME value=$TASK_OBJECT->endTime}
                {/if}
                <span class="col-sm-2 col-xs-2">{vtranslate('LBL_END_TIME',$QUALIFIED_MODULE)}</span>
                <div class="col-sm-3 col-xs-3" >
                    <div class="input-group time">
                        {if $TASK_OBJECT->time neq ''}
                            {assign var=TIME value=$TASK_OBJECT->time}
                        {/if}
                        <input type="text" class="timepicker-default inputElement" value="{$END_TIME}" name="endTime" />
                        <span  class="input-group-addon">
                            <i  class="fa fa-clock-o"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_END_DATE',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-2 col-xs-2">
                    <div class="row">
                        <div class="col-sm-8 col-xs-8">
                            <input class="inputElement" type="text" value="{$TASK_OBJECT->endDays}" name="endDays" 
                                   data-rule-WholeNumber="true" >&nbsp;
                        </div>
                        <span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-2">
                    <select class="select2" name="endDirection" style="width: 100%;">
                        <option  {if $TASK_OBJECT->endDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
                        <option {if $TASK_OBJECT->endDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
                    </select>
                </div>
                <span class="col-sm-6 col-xs-6">
                    <select class="select2" name="endDatefield">
                        {foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
                            <option {if $TASK_OBJECT->endDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'), $DATETIME_FIELD->getModuleName())}</option>
                        {/foreach}
                    </select>
                </span>
                {$SHOWN_FIELDS_LIST['due_date'] = 'due_date'}
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_ENABLE_REPEAT',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-6 col-xs-6">
                    <input type="checkbox" name="recurringcheck" {if $TASK_OBJECT->recurringcheck eq 'on'}checked{/if} />
                </div>
                {$SHOWN_FIELDS_LIST['recurringtype'] = 'recurringtype'}
            </div>
            <div class="row form-group">
                <span class="col-sm-2 col-xs-2">&nbsp;</span>
                <div class="col-sm-10 col-xs-10">
                    <div>
                        {assign var=QUALIFIED_MODULE value='Events'}
                        <div class="{if $TASK_OBJECT->recurringcheck eq 'on'}show{else}hide{/if}" id="repeatUI">
                            <div class="row form-group">
                                <div class="col-sm-2 col-xs-2">
                                    <span class="alignMiddle" style="line-height: 30px;">{vtranslate('LBL_REPEATEVENT', $QUALIFIED_MODULE )}</span>
                                </div>
                                <div class="col-sm-2 col-xs-2">
                                    <select class="select2" name="repeat_frequency" style="width: 100%;">
                                        {for $FREQUENCY = 1 to 14}
                                        <option value="{$FREQUENCY}" {if $FREQUENCY eq $TASK_OBJECT->repeat_frequency}selected{/if}>{$FREQUENCY}</option>
                                        {/for}
                                    </select>
                                </div>
                                <div class="col-sm-2 col-xs-2">
                                    <select class="select2" name="recurringtype" id="recurringType" style="width: 100%;">
                                        <option value="Daily" {if $TASK_OBJECT->recurringtype eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $QUALIFIED_MODULE)}</option>
                                        <option value="Weekly" {if $TASK_OBJECT->recurringtype eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $QUALIFIED_MODULE)}</option>
                                        <option value="Monthly" {if $TASK_OBJECT->recurringtype eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $QUALIFIED_MODULE)}</option>
                                        <option value="Yearly" {if $TASK_OBJECT->recurringtype eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $QUALIFIED_MODULE)}</option>
                                    </select>
                                </div>
                                <div class="col-sm-1 col-xs-1">
                                    <span class="alignMiddle" style="line-height: 30px;">{vtranslate('LBL_UNTIL', $QUALIFIED_MODULE)}</span>
                                </div>
                                <div class="col-sm-4 col-xs-4">
                                    <span class="input-group date">
                                        <input type="text" id="calendar_repeat_limit_date" class="dateField inputElement" name="calendar_repeat_limit_date" data-date-format="{$dateFormat}"
                                               value="{$REPEAT_DATE}" data-rule-date="true"/>
                                        <span class="input-group-addon"><i class="fa fa fa-calendar"></i></span>
                                    </span>
                                </div>
                            </div>
                            <div class="row form-group {if $TASK_OBJECT->recurringtype eq 'Weekly'}show{else}hide{/if}" id="repeatWeekUI">
                                <div class="col-sm-1 col-xs-1"><input name="sun_flag" value="sunday" {if $TASK_OBJECT->sun_flag eq "sunday"}checked{/if} type="checkbox"/>{vtranslate('LBL_SM_SUN', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="mon_flag" value="monday" {if $TASK_OBJECT->mon_flag eq "monday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_MON', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="tue_flag" value="tuesday" {if $TASK_OBJECT->tue_flag eq "tuesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_TUE', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="wed_flag" value="wednesday" {if $TASK_OBJECT->wed_flag eq "wednesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_WED', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="thu_flag" value="thursday" {if $TASK_OBJECT->thu_flag eq "thursday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_THU', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="fri_flag" value="friday" {if $TASK_OBJECT->fri_flag eq "friday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_FRI', $QUALIFIED_MODULE)}</div>
                                <div class="col-sm-1 col-xs-1"><input name="sat_flag" value="saturday" {if $TASK_OBJECT->sat_flag eq "saturday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_SAT', $QUALIFIED_MODULE)}</div>
                            </div>
                            <div class="{if $TASK_OBJECT->recurringtype eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI">
                                <div class="row form-group">
                                    <div class="col-sm-1 col-xs-1"><input type="radio" id="repeatDate" name="repeatMonth" checked value="date" {if $TASK_OBJECT->repeatMonth eq 'date'} checked {/if}/></div>
                                    <div class="col-sm-1 col-xs-1"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
                                    <div class="col-sm-2 col-xs-2"><input type="text" id="repeatMonthDate" class="inputElement" name="repeatMonth_date" data-rule-RepeatMonthDate="true" value="{$TASK_OBJECT->repeatMonth_date}"/></div>
                                    <div class="col-sm-6 col-xs-6 alignMiddle">{vtranslate('LBL_DAY_OF_THE_MONTH', $QUALIFIED_MODULE)}</div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="row form-group" id="repeatMonthDayUI">
                                    <div class="col-sm-1 col-xs-1"><input type="radio" id="repeatDay" name="repeatMonth" value="day" {if $TASK_OBJECT->repeatMonth eq 'day'} checked {/if}/></div>
                                    <div class="col-sm-1 col-xs-1"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
                                    <div class="col-sm-2 col-xs-2">
                                        <select id="repeatMonthDayType" class="select2" name="repeatMonth_daytype">
                                            <option value="first" {if $TASK_OBJECT->repeatMonth_daytype eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $QUALIFIED_MODULE)}</option>
                                            <option value="last" {if $TASK_OBJECT->repeatMonth_daytype eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $QUALIFIED_MODULE)}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2 col-xs-2">
                                        <select id="repeatMonthDay" class="select2" name="repeatMonth_day">
                                            <option value=1 {if $TASK_OBJECT->repeatMonth_day eq 1} selected {/if}>{vtranslate('LBL_DAY1', $QUALIFIED_MODULE)}</option>
                                            <option value=2 {if $TASK_OBJECT->repeatMonth_day eq 2} selected {/if}>{vtranslate('LBL_DAY2', $QUALIFIED_MODULE)}</option>
                                            <option value=3 {if $TASK_OBJECT->repeatMonth_day eq 3} selected {/if}>{vtranslate('LBL_DAY3', $QUALIFIED_MODULE)}</option>
                                            <option value=4 {if $TASK_OBJECT->repeatMonth_day eq 4} selected {/if}>{vtranslate('LBL_DAY4', $QUALIFIED_MODULE)}</option>
                                            <option value=5 {if $TASK_OBJECT->repeatMonth_day eq 5} selected {/if}>{vtranslate('LBL_DAY5', $QUALIFIED_MODULE)}</option>
                                            <option value=6 {if $TASK_OBJECT->repeatMonth_day eq 6} selected {/if}>{vtranslate('LBL_DAY6', $QUALIFIED_MODULE)}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($QUALIFIED_MODULE)}
            {if !empty($TASK_OBJECT->eventName)}
                {assign var=FIELD_MODELS value=$RELATED_MODULE_MODEL->getFields()}
                {foreach from=$FIELD_MODELS item=FIELD_MODEL}
                    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
                    {if !in_array($FIELD_NAME, $SHOWN_FIELDS_LIST) && $FIELD_MODEL->getDisplayType() != '3' && ($FIELD_MODEL->isMandatory() || !empty($TASK_OBJECT->$FIELD_NAME)) && $FIELD_MODEL->getFieldDataType() != 'reference' && $FIELD_MODEL->getFieldDataType() != 'multireference'}
                        {assign var="test" value=$FIELD_MODEL->set('fieldvalue', $TASK_OBJECT->$FIELD_NAME)}
                        <div class="row-fluid padding-bottom1per">
                            <span class="span2">{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}</span>
                            <span class="span6">{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL USER_MODEL=Users_Record_Model::getCurrentUserModel()}</span>
                        </div>
                    {/if}
                {/foreach}
            {else}
                {assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
                {foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
                    {if !in_array($MANDATORY_FIELD_MODEL->get('name'), $SHOWN_FIELDS_LIST) && $MANDATORY_FIELD_MODEL->getDisplayType() != '3' && $MANDATORY_FIELD_MODEL->getFieldDataType() != 'reference' && $MANDATORY_FIELD_MODEL->getFieldDataType() != 'multireference'}
                        {assign var=FIELD_NAME value=$MANDATORY_FIELD_MODEL->get('name')}
                        {assign var="test" value=$MANDATORY_FIELD_MODEL->set('fieldvalue', $TASK_OBJECT->$FIELD_NAME)}
                        <div class="row-fluid padding-bottom1per">
                            <span class="span2">{vtranslate($MANDATORY_FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}<span class="redColor">*</span></span>
                            <span class="span6">{include file=vtemplate_path($MANDATORY_FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$MANDATORY_FIELD_MODEL USER_MODEL=Users_Record_Model::getCurrentUserModel()}</span>
                        </div>
                    {/if}
                {/foreach}
            {/if}
        </div>
	</div>
{/strip}
