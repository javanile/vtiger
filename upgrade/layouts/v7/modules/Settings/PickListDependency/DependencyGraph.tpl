{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/PickListDependency/views/Edit.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="row">
        <div class="col-sm-12 col-xs-12 accordion">
            <span><i class="icon-info-sign alignMiddle"></i>&nbsp;{vtranslate('LBL_CONFIGURE_DEPENDENCY_INFO', $QUALIFIED_MODULE)}&nbsp;&nbsp;</span>
            <a class="cursorPointer accordion-heading accordion-toggle" data-toggle="collapse" data-target="#dependencyHelp">{vtranslate('LBL_MORE', $QUALIFIED_MODULE)}..</a>
            <div id="dependencyHelp" class="accordion-body collapse">
                <ul><br><li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_1', $QUALIFIED_MODULE)}</li><br>
                    <li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_2', $QUALIFIED_MODULE)}</li><br>
                    <li>{vtranslate('LBL_CONFIGURE_DEPENDENCY_HELP_3', $QUALIFIED_MODULE)}&nbsp;
                        <span class="selectedCell" style="padding: 4px;">{vtranslate('Selected Values', $QUALIFIED_MODULE)}</span></li>
                </ul>
            </div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-sm-2 col-xs-2">
            <div class="btn-group">
                <button class="btn btn-default sourceValues" type="button">{vtranslate('LBL_SELECT_SOURCE_VALUES', $QUALIFIED_MODULE)}</button>
            </div>
        </div>
        <div class="col-sm-10 col-xs-10">
            <div class="btn-group">
                <button class="btn btn-default selectAllValues" type="button">{vtranslate('LBL_SELECT_ALL_VALUES', $QUALIFIED_MODULE)}</button>
                <button class="btn btn-default unSelectAllValues" type="button">{vtranslate('LBL_UNSELECT_ALL_VALUES', $QUALIFIED_MODULE)}</button>
            </div>
        </div>
    </div>
    <br>
    {assign var=SELECTED_MODULE value=$RECORD_MODEL->get('sourceModule')}
    {assign var=SOURCE_FIELD value=$RECORD_MODEL->get('sourcefield')}
    {assign var=MAPPED_SOURCE_PICKLIST_VALUES value=array()}
    {assign var=MAPPED_TARGET_PICKLIST_VALUES value=[]}
    {foreach item=MAPPING from=$MAPPED_VALUES}
        {assign var=value value=array_push($MAPPED_SOURCE_PICKLIST_VALUES, $MAPPING['sourcevalue'])}
        {$MAPPED_TARGET_PICKLIST_VALUES[$MAPPING['sourcevalue']] = $MAPPING['targetvalues']}
    {/foreach}
    {assign var=DECODED_MAPPED_SOURCE_PICKLIST_VALUES value=array_map('decode_html', $MAPPED_SOURCE_PICKLIST_VALUES)}
    <input type="hidden" class="allSourceValues" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SOURCE_PICKLIST_VALUES))}' />

    <div class="row depandencyTable" style="padding-right: 10px;">
        <div class="col-sm-2 col-xs-2" style="padding-right: 0px;">
            <table class="listview-table table-bordered table-condensed" style="width: 100%; border-collapse:collapse;">
                <thead>
                    <tr class="blockHeader"><th>{$RECORD_MODEL->getSourceFieldLabel()}</th></tr>
                </thead>
                <tbody>
                    {foreach item=TARGET_VALUE from=$TARGET_PICKLIST_VALUES name=targetValuesLoop}
                        <tr>
                            {if $smarty.foreach.targetValuesLoop.index eq 0}
                                <td class="tableHeading" style="border: none;">
                                    {$RECORD_MODEL->getTargetFieldLabel()}
                                </td>
                            </tr>
                        {else}
                        <td style="border: none;">&nbsp;</td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="col-sm-10 col-xs-10 dependencyMapping">
            <table class="listview-table table-bordered pickListDependencyTable" style="width:auto;">
                <thead>
                    <tr class="blockHeader">
                        {foreach key=SOURCE_PICKLIST_VALUE item=TRANSLATED_SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
                            <th data-source-value="{$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_PICKLIST_VALUE]}" style="width:160px;
                                {if !empty($MAPPED_VALUES) and !in_array($SOURCE_PICKLIST_VALUE, $DECODED_MAPPED_SOURCE_PICKLIST_VALUES)} display: none; {/if}">
                                {$TRANSLATED_SOURCE_PICKLIST_VALUE}
                            </th>
                        {/foreach}
                    </tr>
                </thead>
                <tbody>
                    {foreach key=TARGET_VALUE item=TRANSLATED_TARGET_VALUE from=$TARGET_PICKLIST_VALUES}
                        <tr>
                            {foreach key=SOURCE_PICKLIST_VALUE item=TRANSLATED_SOURCE_PICKLIST_VALUE from=$SOURCE_PICKLIST_VALUES}
                                {assign var=targetValues value=$MAPPED_TARGET_PICKLIST_VALUES[$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_PICKLIST_VALUE]]}
                                {assign var=IS_SELECTED value=false}
                                {if empty($targetValues) || in_array($TARGET_VALUE, $targetValues)}
                                    {assign var=IS_SELECTED value=true}
                                {/if}
                                <td	data-source-value='{$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_PICKLIST_VALUE]}' data-target-value='{$SAFEHTML_TARGET_PICKLIST_VALUES[$TARGET_VALUE]}'
                                    class="{if $IS_SELECTED}selectedCell {else}unselectedCell {/if} targetValue picklistValueMapping cursorPointer"
                                    {if !empty($MAPPED_VALUES) && !in_array($SOURCE_PICKLIST_VALUE, $DECODED_MAPPED_SOURCE_PICKLIST_VALUES)}style="display: none;" {/if}>
                                    {if $IS_SELECTED}
                                        <i class="fa fa-check pull-left"></i>
                                    {/if}
                                    {$TRANSLATED_TARGET_VALUE}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-dialog modal-lg sourcePicklistValuesModal modalCloneCopy hide">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_SELECT_SOURCE_PICKLIST_VALUES', $QUALIFIED_MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="modal-body">
                <table  class="table table-condensed table-borderless" cellspacing="0" cellpadding="5">
                    <tr>
                        {foreach key=SOURCE_VALUE item=TRANSLATED_SOURCE_VALUE from=$SOURCE_PICKLIST_VALUES name=sourceValuesLoop}
                            {if $smarty.foreach.sourceValuesLoop.index % 3 == 0}
                            </tr><tr>
                            {/if}
                            <td>
                                <label>
                                    <input type="checkbox" class="sourceValue {$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_VALUE]}"
                                           data-source-value="{$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_VALUE]}" value="{$SAFEHTML_SOURCE_PICKLIST_VALUES[$SOURCE_VALUE]}" 
                                           {if empty($MAPPED_VALUES) || in_array($SOURCE_VALUE, $DECODED_MAPPED_SOURCE_PICKLIST_VALUES)} checked {/if}/>
                                    &nbsp;{$TRANSLATED_SOURCE_VALUE}
                                </label>
                            </td>
                        {/foreach}
                    </tr>
                </table>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
        </div>
    </div>
{/strip}
