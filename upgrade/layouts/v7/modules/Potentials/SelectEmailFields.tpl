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
    <div id="sendEmailContainer" class="modal-dialog">
        <form class="form-horizontal" id="SendEmailFormStep1" method="post" action="index.php">
            <div class="modal-content">
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_SELECT_EMAIL_IDS', $MODULE)}}
                <div class="modal-body">
                    <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)} />
                    <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)} />
                    <input type="hidden" name="viewname" value="{$VIEWNAME}" />
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="view" value="ComposeEmail"/>
                    <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
                    <input type="hidden" name="operator" value="{$OPERATOR}" />
                    <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
                    {if $SEARCH_PARAMS}
                        <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
                    {/if}
                    <input type="hidden" name="fieldModule" value={$SOURCE_MODULE} />
                       <input type="hidden" name="to" value='{ZEND_JSON::encode($TO)}' />
                    <input type="hidden" name="source_module" value="{$SELECTED_EMAIL_SOURCE_MODULE}" />
                    {if !empty($PARENT_MODULE)}
                        <input type="hidden" name="sourceModule" value="{$PARENT_MODULE}" />
                        <input type="hidden" name="sourceRecord" value="{$PARENT_RECORD}" />
                        <input type="hidden" name="parentModule" value="{$RELATED_MODULE}" />
                    {/if}
                    <input type="hidden" name="prefsNeedToUpdate" id="prefsNeedToUpdate" value="{$PREF_NEED_TO_UPDATE}" />
                    <div id="multiEmailContainer" style="padding-left:20px;">
                        {if $EMAIL_FIELDS_INFO}
                            {if $RECORDS_COUNT > 1}
                                <input type="hidden" name="emailSource" value="ListView" />
                                    {counter start=0 skip=1 assign="count"}
                                    {foreach item=EMAIL_FIELDS_INFO key=EMAIL_MODULE from=$EMAIL_FIELDS}
                                        {foreach item=EMAIL_FIELD key=EMAIL_FIELD_NAME from=$EMAIL_FIELDS_INFO}
                                            <label class="checkbox" style="padding-left: 7%;">
                                                <input type="checkbox" class="emailField" name="selectedFields[{$count}]" data-moduleName="{$EMAIL_FIELD->getModule()->getName()}" value='{Vtiger_Functions::jsonEncode(['field' => $EMAIL_FIELD_NAME, 'field_id' => $EMAIL_FIELD->getId(), 'module_id' => $EMAIL_FIELD->getModule()->getId(), 'basefield' => $EMAIL_FIELD->get('baseRefField')])}' {if $EMAIL_FIELD->get('isPreferred')}checked="true"{/if}/>
                                                &nbsp;{vtranslate($EMAIL_FIELD->get('label'), $EMAIL_MODULE)}
                                            </label>
                                            {counter}
                                        {/foreach}
                                    {/foreach}
                                        {else}
                                            {counter start=0 skip=1 assign="count"}
                                            {foreach item=EMAIL_MODULE_INFO key=RECORD_ID from=$EMAIL_FIELDS_INFO}
                                                {assign var=RECORD_LABEL value={decode_html(textlength_check(Vtiger_Util_Helper::getRecordName($RECORD_ID)))}}
                                                {if $RECORDS_COUNT > 1}<h4>{$RECORD_LABEL}</h4>{/if}
                                                    <div style="{if $RECORDS_COUNT > 1}padding-left: 3%;{/if}">
                                                        {foreach item=EMAIL_FIELDS key=EMAIL_MODULE from=$EMAIL_MODULE_INFO}
                                                            <h5>{vtranslate('SINGLE_'|cat:$EMAIL_MODULE, $EMAIL_MODULE)}</h5>
                                                            {foreach item=EMAIL_FIELD  key=EMAIL_VALUE from=$EMAIL_FIELDS}
                                                                <label class="checkbox" style="padding-left: {if $RECORDS_COUNT > 1}10{else}7{/if}%;padding-top: 1%; font-weight:normal;">
                                                                    <input type="checkbox" class="emailField" name="selectedFields[{$count}]" data-moduleName="{$EMAIL_MODULE}" value='{Vtiger_Functions::jsonEncode(['record'=>$RECORD_ID,'field_value'=>$EMAIL_VALUE,'record_label'=>$RECORD_LABEL|cat:':'|cat:vtranslate('SINGLE_'|cat:$EMAIL_MODULE,$EMAIL_MODULE),'field_id'=> $EMAIL_FIELD->getId(),'module_id'=>$EMAIL_FIELD->getModule()->getId()])}' {if $EMAIL_FIELD->get('isPreferred')}checked="true"{/if}/>
                                                                    &nbsp;&nbsp;&nbsp;{$EMAIL_VALUE}<span class="muted">&nbsp;-{vtranslate($EMAIL_FIELD->get('label'), $SOURCE_MODULE)}</span>
                                                               </label>
                                                               {counter}
                                                            {/foreach}
                                                        {/foreach}
                                                    </div>
                                            {/foreach}
                            {/if}
                        {/if}   
                    </div>
                    {if $RELATED_LOAD eq true}
                        <input type="hidden" name="relatedLoad" value={$RELATED_LOAD} />
                    {/if}
                </div>
            <div class="preferenceDiv" style="padding: 0px 0px 10px 35px;">
                <label class="checkbox displayInlineBlock">
                    <input type="checkbox" name="saveRecipientPrefs" id="saveRecipientPrefs" {if $RECIPIENT_PREF_ENABLED}checked="true"{/if}/>&nbsp;&nbsp;&nbsp;
                    {vtranslate('LBL_REMEMBER_MY_PREF',$MODULE)}&nbsp;&nbsp;
                </label>
                <i class="fa fa-info-circle" title="{vtranslate('LBL_EDIT_EMAIL_PREFERENCE_TOOLTIP', $MODULE)}"></i>
            </div>
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE BUTTON_NAME={vtranslate('LBL_SELECT', $MODULE)}}
         </div>
       </form>
    </div>
{/strip}

