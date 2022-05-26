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
    <div class="col-sm-6 col-lg-6 col-md-6">
        <div class="record-header clearfix">
            <div class="hidden-sm hidden-xs recordImage bghelpdesk app-{$SELECTED_MENU_CATEGORY}">
				<div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
            </div>
            <div class="recordBasicInfo">
                <div class="info-row">
                    <h4>
                        <span class="recordLabel pushDown" title="{$RECORD->getName()}">
                            {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                                {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                                {if $FIELD_MODEL->getPermissions()}
                                    <span class="{$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                                {/if}
                            {/foreach}
                        </span>
                    </h4>
                </div>
                {include file="DetailViewHeaderFieldsView.tpl"|vtemplate_path:$MODULE}
                {*
                {assign var=PRIORITY value=$RECORD->get('ticketpriorities')}
                {if !empty($PRIORITY)}
                    <div class="info-row row">
                         {assign var=FIELD_MODEL value=$MODULE_MODEL->getField('ticketpriorities')}
                         <div class="col-lg-7 fieldLabel">
                        <span class="muted" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('ticketpriorities')}"> 
                        {$RECORD->getDisplayValue('ticketpriorities')}
                         </span>
                         </div>
                    </div>
                {/if}
                *}
            </div>
        </div>
    </div>
{/strip}