{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{if $REPORT_TYPE eq 'ChartEdit'}
    {include file="EditChartHeader.tpl"|vtemplate_path:$MODULE}
{else}
    {include file="EditHeader.tpl"|vtemplate_path:$MODULE}
{/if}
<div class="reportContents">
    <form class="form-horizontal recordEditView" id="report_step1" method="post" action="index.php">
		<input type="hidden" name="mode" value="step2" />
        <input type="hidden" name="module" value="{$MODULE}" />
        <input type="hidden" name="view" value="{$VIEW}" />
        <input type="hidden" class="step" value="1" />
        <input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
        <input type="hidden" name="record" value="{$RECORD_ID}" />
        <input type=hidden id="relatedModules" data-value='{ZEND_JSON::encode($RELATED_MODULES)}' />
        <div style="border:1px solid #ccc;padding:4%;">
            <div class="row">
                <div class="form-group">
                    <label class="col-lg-3 control-label textAlignLeft">{vtranslate('LBL_REPORT_NAME',$MODULE)}<span class="redColor">*</span></label>
                    <div class="col-lg-4">
                        <input type="text" class="inputElement" data-rule-required="true" name="reportname" value="{$REPORT_MODEL->get('reportname')}"/>
                    </div>
                </div>
            </div>
            <div class="row">		
                <div class="form-group">
                    <label class="col-lg-3 control-label textAlignLeft">{vtranslate('LBL_REPORT_FOLDER',$MODULE)}<span class="redColor">*</span></label>
                    <div class="col-lg-4">
                        <select class="select2 col-lg-12 inputElement" name="folderid" data-rule-required="true">
                            {foreach item=REPORT_FOLDER from=$REPORT_FOLDERS}
                                <option value="{$REPORT_FOLDER->getId()}" 
                                        {if $REPORT_FOLDER->getId() eq $REPORT_MODEL->get('folderid')}
                                            selected=""
                                        {/if}
                                        >{vtranslate($REPORT_FOLDER->getName(), $MODULE)}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label class="col-lg-3 control-label textAlignLeft">{vtranslate('PRIMARY_MODULE',$MODULE)}<span class="redColor">*</span></label>
                    <div class="col-lg-4">
                        <select class="select2-container select2 col-lg-12 inputElement" id="primary_module" name="primary_module" data-rule-required="true"
                                {if $RECORD_ID and $REPORT_MODEL->getPrimaryModule() and $IS_DUPLICATE neq true and $REPORT_TYPE eq "ChartEdit"} disabled="disabled"{/if}>
                            {foreach key=RELATED_MODULE_KEY item=RELATED_MODULE from=$MODULELIST}
                                <option value="{$RELATED_MODULE_KEY}" {if $REPORT_MODEL->getPrimaryModule() eq $RELATED_MODULE_KEY } selected="selected" {/if}>
                                    {vtranslate($RELATED_MODULE_KEY,$RELATED_MODULE_KEY)}
                                </option>
                            {/foreach}
                        </select>
                        {if $RECORD_ID and $REPORT_MODEL->getPrimaryModule() and $IS_DUPLICATE neq true and $REPORT_TYPE eq "ChartEdit"}
                            <input type="hidden" name="primary_module" value="{$REPORT_MODEL->getPrimaryModule()}" />
                        {/if}
                    </div>
                </div>	
            </div>
            <div class="row">
                <div class="form-group">
                    <label class="col-lg-3 control-label textAlignLeft">{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}&nbsp;({vtranslate('LBL_MAX',$MODULE)}&nbsp;2)</label>
                    <div class="col-lg-4">
                        {assign var=SECONDARY_MODULES_ARR value=explode(':',$REPORT_MODEL->getSecondaryModules())}
                        {assign var=PRIMARY_MODULE value=$REPORT_MODEL->getPrimaryModule()}

                        {if $PRIMARY_MODULE eq ''}
                            {foreach key=PARENT item=RELATED from=$RELATED_MODULES name=relatedlist}
                                {if $smarty.foreach.relatedlist.index eq 0}
                                    {assign var=PRIMARY_MODULE value=$PARENT}
                                {/if}
                            {/foreach}
                        {/if}
                        {assign var=PRIMARY_RELATED_MODULES value=$RELATED_MODULES[$PRIMARY_MODULE]}
                        <select class="select2-container col-lg-12 inputElement" id="secondary_module" multiple name="secondary_modules[]" data-placeholder="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}"
                                {if $RECORD_ID and $REPORT_MODEL->getSecondaryModules() and $IS_DUPLICATE neq true and $REPORT_TYPE eq "ChartEdit"} disabled="disabled"{/if}>
                            {foreach key=PRIMARY_RELATED_MODULE  item=PRIMARY_RELATED_MODULE_LABEL from=$PRIMARY_RELATED_MODULES}
                                <option {if in_array($PRIMARY_RELATED_MODULE,$SECONDARY_MODULES_ARR)} selected="" {/if} value="{$PRIMARY_RELATED_MODULE}">{$PRIMARY_RELATED_MODULE_LABEL}</option>
                            {/foreach}
                        </select>
                        {if $RECORD_ID and $REPORT_MODEL->getSecondaryModules() and $IS_DUPLICATE neq true and $REPORT_TYPE eq "ChartEdit"}
                            <input type="hidden" name="secondary_modules[]" value="{$REPORT_MODEL->getSecondaryModules()}" />
                        {/if}
                    </div>
                </div>	
            </div>
            <div class="row">
                <div class="form-group">
                    <label class="col-lg-3 control-label textAlignLeft">{vtranslate('LBL_DESCRIPTION',$MODULE)}</label>
                    <div class="col-lg-4">
                        <textarea type="text" cols="50" rows="3" class="inputElement" name="description">{$REPORT_MODEL->get('description')}</textarea>
                    </div>
                </div>	
            </div>
            <div class='row'>
                <div class='form-group'>
                    <label class='col-lg-3 control-label textAlignLeft'>{vtranslate('LBL_SHARE_REPORT',$MODULE)}</label>
                    <div class='col-lg-4'>
                        <select id="memberList" class="col-lg-12 select2-container select2 members " multiple="true" name="members[]" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $MODULE)}">
                            {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                <optgroup label="{$GROUP_LABEL}">
                                    {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                        <option value="{$MEMBER->getId()}"  data-member-type="{$GROUP_LABEL}" {if isset($SELECTED_MEMBERS_GROUP[$GROUP_LABEL][$MEMBER->getId()])}selected="true"{/if}>{$MEMBER->getName()}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>	
            {include file="ScheduleReport.tpl"|@vtemplate_path:$MODULE}	
        </div>
        <div class="border1px modal-overlay-footer clearfix">
            <div class="row clearfix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-lg-12 ">
                    <button class="btn btn-success nextStep" type="submit">{vtranslate('LBL_NEXT',$MODULE)}</button>&nbsp;&nbsp;
                    <a type="reset" onclick='window.history.back();' class="cancelLink cursorPointer">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            </div>
        </div>
    </form>