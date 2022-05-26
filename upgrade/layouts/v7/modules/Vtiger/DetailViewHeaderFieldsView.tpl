{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
<form id="headerForm" method="POST">
    {assign var=FIELDS_MODELS_LIST value=$MODULE_MODEL->getFields()}
    {foreach item=FIELD_MODEL from=$FIELDS_MODELS_LIST}
        {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
        {assign var=FIELD_NAME value={$FIELD_MODEL->getName()}}
        {if $FIELD_MODEL->isHeaderField() && $FIELD_MODEL->isActiveField() && $RECORD->get($FIELD_NAME) && $FIELD_MODEL->isViewable()}
            {assign var=FIELD_MODEL value=$FIELD_MODEL->set('fieldvalue', $RECORD->get({$FIELD_NAME}))}
            <div class="info-row row headerAjaxEdit td">
                <div class="col-lg-7 fieldLabel">
                    {assign var=DISPLAY_VALUE value="{$FIELD_MODEL->getDisplayValue($RECORD->get($FIELD_NAME))}"}
                    <span class="{$FIELD_NAME} value" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {strip_tags($DISPLAY_VALUE)}">
                        {include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path:$MODULE_NAME FIELD_MODEL=$FIELD_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
                    </span>
                    {if $FIELD_MODEL->isEditable() eq 'true' && $LIST_PREVIEW neq 'true' && $IS_AJAX_ENABLED eq 'true'}
                        <span class="hide edit">
                            {if $FIELD_DATA_TYPE eq 'multipicklist'}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                            {else}
                                <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$FIELD_MODEL->getFieldDataType()}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_MODEL->get('fieldvalue')}" />
                            {/if}    
                        </span>
                        <span class="action">
                            <a href="#" onclick="return false;" class="editAction fa fa-pencil"></a>
                        </span>
                    {/if}
                </div>
            </div>
        {/if}
    {/foreach}
</form>
{/strip}