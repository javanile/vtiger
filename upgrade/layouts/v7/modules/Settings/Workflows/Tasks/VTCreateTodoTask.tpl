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
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_TITLE',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                <div class="col-sm-8 col-xs-8">
                    <input data-rule-required="true" class="inputElement" name="todo" type="text" value="{$TASK_OBJECT->todo}" />
                    {$SHOWN_FIELDS_LIST['subject'] = 'subject'}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-8 col-xs-8">
                    <textarea class="inputElement" name="description" style="height: inherit;">{$TASK_OBJECT->description}</textarea>
                    {$SHOWN_FIELDS_LIST['description'] = 'description'}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-5 col-xs-5">
                    {assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('taskstatus')->getPickListValues()}
                    <select name="status" class="select2">
                        {foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
                            <option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
                        {/foreach}
                    </select>
                </div>
                {$SHOWN_FIELDS_LIST['taskstatus'] = 'taskstatus'}
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_PRIORITY',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-5 col-xs-5">
                    {assign var=PRIORITY_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('taskpriority')->getPickListValues()}
                    <select name="priority" class="select2">
                        {foreach  from=$PRIORITY_PICKLIST_VALUES item=PRIORITY_PICKLIST_VALUE key=PRIORITY_PICKLIST_KEY}
                            <option value="{$PRIORITY_PICKLIST_KEY}" {if $PRIORITY_PICKLIST_KEY eq $TASK_OBJECT->priority} selected="" {/if}>{$PRIORITY_PICKLIST_VALUE}</option>
                        {/foreach}
                    </select>
                </div>
                {$SHOWN_FIELDS_LIST['taskpriority'] = 'taskpriority'}
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
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_TIME',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-3 col-xs-3" >
                    <div class="input-group time">
                        {if $TASK_OBJECT->time neq ''}
                            {assign var=TIME value=$TASK_OBJECT->time}
                        {/if}
                        <input type="text" class="timepicker-default inputElement" value="{$TIME}" name="time" />
                        <span  class="input-group-addon">
                            <i  class="fa fa-clock-o"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_DUE_DATE',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-2 col-xs-2">
                    <div class="row">
                        <div class="col-sm-8 col-xs-8">
                            <input class="inputElement" type="text" name="days" value="{$TASK_OBJECT->days}">&nbsp;
                        </div>
                        <span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-2 marginLeftZero">
                    <select class="select2" name="direction" style="width: 100%;">
                        <option {if $TASK_OBJECT->direction eq 'after'}selected=""{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
                        <option {if $TASK_OBJECT->direction eq 'before'}selected=""{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
                    </select>
                </div>
                <span class="col-sm-6 col-xs-6">
                    <div class="row">
                        <div class="col-sm-6 col-xs-6">
                            <select class="select2" name="datefield" style="width: 100%;">
                                {foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
                                    <option {if $TASK_OBJECT->datefield eq $DATETIME_FIELD->get('name')}selected{/if} value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'), $DATETIME_FIELD->getModuleName())}</option>
                                {/foreach}
                            </select>&nbsp;
                        </div>
                        <div class="col-sm-6 col-xs-6" style="vertical-align: super; word-wrap: break-word; padding: 0px;">({vtranslate('LBL_THE_SAME_VALUE_IS_USED_FOR_START_DATE',$QUALIFIED_MODULE)})</div>
                    </div>
                </span>
                {$SHOWN_FIELDS_LIST['date_start'] = 'date_start'}
                {$SHOWN_FIELDS_LIST['due_date'] = 'due_date'}
            </div>
            <div class="row form-group">
                <div class="col-sm-2 col-xs-2">{vtranslate('LBL_SEND_NOTIFICATION',$QUALIFIED_MODULE)}</div>
                <div class="col-sm-6 col-xs-6">
                    <input  type="checkbox" name="sendNotification" value="true" {if $TASK_OBJECT->sendNotification}checked{/if} />
                </div>
                {$SHOWN_FIELDS_LIST['sendnotification'] = 'sendnotification'}
            </div>
            {assign var=QUALIFIED_MODULE value='Calendar'}
            {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($QUALIFIED_MODULE)}
            {if !empty($TASK_OBJECT->todo)}
                {assign var=FIELD_MODELS value=$RELATED_MODULE_MODEL->getFields()}
                {foreach from=$FIELD_MODELS item=FIELD_MODEL}
                    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
                    {if !in_array($FIELD_NAME, $SHOWN_FIELDS_LIST) && $FIELD_MODEL->getDisplayType() != '3' && ($FIELD_MODEL->isMandatory() || !empty($TASK_OBJECT->$FIELD_NAME)) && $FIELD_MODEL->getFieldDataType() != 'reference' && $FIELD_MODEL->getFieldDataType() != 'multireference'}
                        {assign var="test" value=$FIELD_MODEL->set('fieldvalue', $TASK_OBJECT->$FIELD_NAME)}
                        <div class="row form-group">
                            <div class="col-sm-2 col-xs-2">{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}</div>
                            <div class="col-sm-6 col-xs-6">{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL USER_MODEL=Users_Record_Model::getCurrentUserModel()}</div>
                        </div>
                    {/if}
                {/foreach}
            {else}
                {assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
                {foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
                    {if !in_array($MANDATORY_FIELD_MODEL->get('name'), $SHOWN_FIELDS_LIST) && $MANDATORY_FIELD_MODEL->getDisplayType() != '3' && $MANDATORY_FIELD_MODEL->getFieldDataType() != 'reference' && $MANDATORY_FIELD_MODEL->getFieldDataType() != 'multireference'}
                        {assign var=FIELD_NAME value=$MANDATORY_FIELD_MODEL->get('name')}
                        {assign var="test" value=$MANDATORY_FIELD_MODEL->set('fieldvalue', $TASK_OBJECT->$FIELD_NAME)}
                        <div class="row form-group">
                            <div class="col-sm-2 col-xs-2">{vtranslate($MANDATORY_FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}<span class="redColor">*</span></div>
                            <div class="col-sm-6 col-xs-6">{include file=vtemplate_path($MANDATORY_FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$MANDATORY_FIELD_MODEL USER_MODEL=Users_Record_Model::getCurrentUserModel()}</div>
                        </div>
                    {/if}
                {/foreach}
            {/if}
        </div>
	</div>
{/strip}
