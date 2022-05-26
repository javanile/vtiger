{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/SharingAccess/views/IndexAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {assign var=RULE_MODEL_EXISTS value=true}
    {assign var=RULE_ID value=$RULE_MODEL->getId()}
    {if empty($RULE_ID)}
        {assign var=RULE_MODEL_EXISTS value=false}
    {/if}
    <div class="modal-dialog modelContainer"'>
        {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_CUSTOM_RULE_TO', $QUALIFIED_MODULE)}|cat:" "|cat:{vtranslate($MODULE_MODEL->get('name'), $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
            <form class="form-horizontal" id="editCustomRule" method="post">
                <input type="hidden" name="for_module" value="{$MODULE_MODEL->get('name')}" />
                <input type="hidden" name="record" value="{$RULE_ID}" />
                <div name='massEditContent'>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label fieldLabel col-sm-5">{vtranslate($MODULE_MODEL->get('name'), $MODULE)}&nbsp;{vtranslate('LBL_OF', $MODULE)}</label>
                            <div class="controls fieldValue col-xs-6">
                                <select class="select2 col-sm-9" name="source_id">
                                    {foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                        <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                <option value="{$MEMBER->getId()}"
                                                {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->getSourceMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
                                                {$MEMBER->getName()}
                                            </option>
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</label>
                        <div class="controls fieldValue col-xs-6">
                            <select class="select2 col-sm-9" name="target_id">
                                {foreach from=$ALL_RULE_MEMBERS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                    <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}">
                                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                            <option value="{$MEMBER->getId()}"
                                            {if $RULE_MODEL_EXISTS}{if $RULE_MODEL->getTargetMember()->getId() == $MEMBER->getId()}selected{/if}{/if}>
                                            {$MEMBER->getName()}
                                        </option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_WITH_PERMISSIONS', $QUALIFIED_MODULE)}</label>
                    <div class="controls fieldValue col-sm-5" style="margin-left: 3%;">
                        <label class="radio">
                            <input type="radio" value="0" name="permission" {if $RULE_MODEL_EXISTS} {if $RULE_MODEL->isReadOnly()} checked {/if} {else} checked {/if}/>&nbsp;{vtranslate('LBL_READ', $QUALIFIED_MODULE)}&nbsp;
                        </label>
                        <label class="radio">
                            <input type="radio" value="1" name="permission" {if $RULE_MODEL->isReadWrite()} checked {/if} />&nbsp;{vtranslate('LBL_READ_WRITE', $QUALIFIED_MODULE)}&nbsp;
                        </label>
                    </div>
                </div>
            </div>
        </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </form>
</div>
</div>     
{/strip}
