{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Events/views/AddCalendarEvent.php *}
    
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}
    
    <div class="modal-dialog addCalendarEventModal">
        <div class="modal-content">
            <form class="form-horizontal recordEditView" id="QuickCreate" name="QuickCreate" method="post" action="index.php">        
                <div class="modal-body">
                    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
                        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
                    {/if}
                    {if $MODULE eq 'Events'}
                        <input type="hidden" name="calendarModule" value="Events">
                        {if !empty($PICKIST_DEPENDENCY_DATASOURCE_EVENT)}
                            <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE_EVENT)}' />
                        {/if}
                    {/if}
                    <input type="hidden" name="module" value="{$MODULE}" />
                    <input type="hidden" name="action" value="SaveAjax" />
                    {if $MODE eq 'edit' && $RECORD_ID}
                        <input type="hidden" name="record" value="{$RECORD_ID}" />
                        <input type="hidden" name="mode" value="{$MODE}" />
                    {/if}
                    <input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
                    <input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
                    <div class="addCalendarEventContents">
                        
                        <div>
                            {assign var="FIELD_MODEL" value=$FIELDS['subject']}
                            <div style="margin-left: 7%; width: 93%;">
                                {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
                                {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
                                {assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
                                <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
                                {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()} readonly {/if} {if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}  
                                {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                                {foreach item=VALIDATOR from=$FIELD_INFO["validator"]}
                                    {assign var=VALIDATOR_NAME value=$VALIDATOR["name"]}
                                    data-rule-{$VALIDATOR_NAME} = "true" 
                                {/foreach}
                                placeholder="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" />
                            </div>
                        </div>
                        <div style="display: inline-flex; margin-left: 5%; padding: 8px;">
                            <div class="hide">
                                <label class="muted pull-right">
                                    All Day
                                </label>
                                <input type="checkbox" />
                            </div>
                            <div style="width: 100%; display: inherit;">
                                <span style="width: 150px;">
                                    {assign var="FIELD_MODEL" value=$FIELDS['date_start']}
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </span>
                                <span class="muted" style="width: 60px; line-height: 61px;text-align: center;">
                                    TO
                                </span>
                                <span style="width: 150px;">
                                    {assign var="FIELD_MODEL" value=$FIELDS['due_date']}
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </span>
                            </div>
                        </div>
                        
                        <table class="massEditTable table no-border">
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['activitytype']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['eventstatus']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {if $FIELDS['taskpriority']}
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['taskpriority']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {/if}
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['assigned_user_id']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {if $FIELDS['location']}
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['location']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {/if}
                            {if $FIELDS['visibility']}
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['visibility']}
                                <td class="fieldLabel col-lg-4">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>
                                <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {/if}
                            {if $FIELDS['parent_id']}
                            <tr>
                                {assign var="FIELD_MODEL" value=$FIELDS['parent_id']}
                                {assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
                                {assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
                                {assign var="refrenceListCount" value=count($refrenceList)}
                                {*<td class="fieldLabel col-lg-2">
                                    <label class="muted pull-right">
                                        {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                        {if $FIELD_MODEL->isMandatory() eq true}
                                            <span class="redColor">*</span>
                                        {/if}
                                    </label>
                                </td>*}
                                <td class="fieldValue col-lg-4">
                                    <select class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
                                        {foreach key=index item=value from=$refrenceList}
                                            <option value="{$value}">{vtranslate($value, $value)}</option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="fieldValue col-lg-6">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                </td>
                            </tr>
                            {/if}
                            {assign var=HARDCODED_FIELDS value=','|explode:"subject,date_start,due_date,activitytype,eventstatus,taskpriority,assigned_user_id,location,visibility,parent_id"}
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
                                {if $FIELD_MODEL->isMandatory() && !in_array($FIELD_NAME,$HARDCODED_FIELDS)}
                                <tr>
                                    <td class="fieldLabel col-lg-4">
                                        <label class="muted pull-right">
                                            {vtranslate($FIELD_MODEL->get('label'), $MODULE)}&nbsp;
                                            {if $FIELD_MODEL->isMandatory() eq true}
                                                <span class="redColor">*</span>
                                            {/if}
                                        </label>
                                    </td>
                                    <td class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                    </td>
                                </tr>
                                {/if}
                            {/foreach}
                        </table>
                    </div>
                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </form>
        </div>
            {if $FIELDS_INFO neq null}
                <script type="text/javascript">
                    var quickcreate_uimeta = (function() {
                        var fieldInfo  = {$FIELDS_INFO};
                        return {
                            field: {
                                get: function(name, property) {
                                    if(name && property === undefined) {
                                        return fieldInfo[name];
                                    }
                                    if(name && property) {
                                        return fieldInfo[name][property]
                                    }
                                },
                                isMandatory : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].mandatory;
                                    }
                                    return false;
                                },
                                getType : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].type
                                    }
                                    return false;
                                }
                            },
                        };
                    })();
                </script>
            {/if}
    </div>
{/strip}