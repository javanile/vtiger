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
    <div class="row">
        <div class="col-lg-9">
            <div class="row form-group">
                <div class="col-lg-6">
                    <div class="row">
                        <div style="margin-top: 7px" class="col-lg-4">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</div>&nbsp;&nbsp;
                        <div class="col-lg-6">
                            <select style="min-width: 150px" id="task-fieldnames" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {$ALL_FIELD_OPTIONS}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="row">
                        <div style="margin-top: 7px" class="col-lg-3">{vtranslate('LBL_ADD_TIME',$QUALIFIED_MODULE)}</div>&nbsp;&nbsp;
                        <div class="col-lg-8">
                            <select style="min-width: 150px" id="task_timefields" class="select2" data-placeholder={vtranslate('LBL_SELECT_OPTIONS',$QUALIFIED_MODULE)}>
                                <option></option>
                                {foreach from=$META_VARIABLES item=META_VARIABLE_KEY key=META_VARIABLE_VALUE}
                                    <option value="${$META_VARIABLE_KEY}">{vtranslate($META_VARIABLE_VALUE,$QUALIFIED_MODULE)}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-lg-2">{vtranslate('LBL_MESSAGE', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-8">
                    <textarea name="content" class="inputElement fields" style="height: inherit;">{$TASK_OBJECT->content}</textarea>
                </div>
            </div>
        </div>
    </div>
{/strip}
