{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}
<div class="row">
    {include file='PopupNavigation.tpl'|vtemplate_path:$MODULE}
</div>
<div class="row">
    <div class="col-md-12">
        <input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
        <input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
        <input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
        <input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
        <input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
        <input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
        <input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
        <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
		{if $GETURL}<input type="hidden" id="getUrl" value="{$GETURL}" />{/if}
        <input type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams" />
        <div class="contents-topscroll">
            <div class="topscroll-div">
                &nbsp;
            </div>
        </div>
        <div class="popupEntriesDiv relatedContents">
            <input type="hidden" value="{$ORDER_BY}" id="orderBy">
            <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
            {if $SOURCE_MODULE eq "Emails"}
                {if $MODULE neq 'Documents'}
                    <input type="hidden" value="Vtiger_EmailsRelatedModule_Popup_Js" id="popUpClassName"/>
                {/if}
            {/if}
            {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
            <div class="popupEntriesTableContainer {if $MODULE eq 'EmailTemplates'} emailTemplatesPopupTableContainer{/if}">
                <table class="listview-table table-bordered listViewEntriesTable">
                <thead>
                    <tr class="listViewHeaders">
                        {if $MULTI_SELECT}
                            <th class="{$WIDTHTYPE}">
                                <input type="checkbox"  class="selectAllInCurrentPage" />
                            </th>
                        {elseif $MODULE neq 'EmailTemplates'}
                            <th class="{$WIDTHTYPE}">&nbsp;</th>
                        {/if}
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                        <th class="{$WIDTHTYPE}">
                            <a href="javascript:void(0);" class="listViewContentHeaderValues listViewHeaderValues {if $LISTVIEW_HEADER->get('name') eq 'listprice'} noSorting {/if}" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}">
                                {if $ORDER_BY eq $LISTVIEW_HEADER->get('name')}
                                    <i class="fa fa-sort {$FASORT_IMAGE}"></i>
                                {else}
                                    <i class="fa fa-sort customsort"></i>
                                {/if}
                                &nbsp;{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}&nbsp;
                            </a>
                        </th>
                        {/foreach}
                    </tr>
                </thead>
                {if $MODULE_MODEL && $MODULE_MODEL->isQuickSearchEnabled()}
                    <tr class="searchRow">
                        <td class="textAlignCenter">
                            <button class="btn btn-success" data-trigger="PopupListSearch">{vtranslate('LBL_SEARCH', $MODULE )}</button>
                        </td>
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <td>
                            {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                            {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME)
                            FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
                            </td>
                        {/foreach}
                    </tr>
                {/if}
                {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
                    {assign var="RECORD_DATA" value="{$LISTVIEW_ENTRY->getRawData()}"}
                    <tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" {if $MODULE eq 'EmailTemplates'} data-name="{$RECORD_DATA['subject']}" data-info="{$LISTVIEW_ENTRY->get('body')}" {else} data-name="{$LISTVIEW_ENTRY->getName()}" data-info='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($LISTVIEW_ENTRY->getRawData()))}' {/if}
                    {if $GETURL neq ''} data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if}  id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
                    {if $MULTI_SELECT}
                        <td class="{$WIDTHTYPE}">
                            <input class="entryCheckBox" type="checkbox" />
                        </td>
                    {elseif $MODULE neq 'EmailTemplates'}
                        <td></td>
                    {/if}
                    {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                    {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                    {assign var=LISTVIEW_ENTRY_VALUE value=$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                    <td class="listViewEntryValue value textOverflowEllipsis {$WIDTHTYPE}" title="{$RECORD_DATA[$LISTVIEW_HEADERNAME]}">
                        {if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
                            <a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
                        {else if $LISTVIEW_HEADER->get('uitype') eq '72'}
                            {assign var=CURRENCY_SYMBOL_PLACEMENT value={$CURRENT_USER_MODEL->get('currency_symbol_placement')}}
                            {if $CURRENCY_SYMBOL_PLACEMENT eq '1.0$'}
                                {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}{$LISTVIEW_ENTRY->get('currencySymbol')}
                            {else}
                                {$LISTVIEW_ENTRY->get('currencySymbol')}{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                            {/if}
                        {else if $LISTVIEW_HEADERNAME eq 'listprice'}
                            {CurrencyField::convertToUserFormat($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), null, true, true)}
                        {else if $LISTVIEW_HEADER->getFieldDataType() eq 'picklist'}
                            <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="picklist-color picklist-{$LISTVIEW_HEADER->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADERNAME))}" {/if}> {$LISTVIEW_ENTRY_VALUE} </span>
                        {else if $LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist'}
                            {assign var=MULTI_RAW_PICKLIST_VALUES value=explode('|##|',$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADERNAME))}
                            {assign var=MULTI_PICKLIST_VALUES value=explode(',',$LISTVIEW_ENTRY_VALUE)}
                            {foreach item=MULTI_PICKLIST_VALUE key=MULTI_PICKLIST_INDEX from=$MULTI_RAW_PICKLIST_VALUES}
                                <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="picklist-color picklist-{$LISTVIEW_HEADER->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen(trim($MULTI_PICKLIST_VALUE))}" {/if}> {trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])} </span>
                            {/foreach}
                        {else}
                            {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                        {/if}
                    </td>
                    {/foreach}
                </tr>
                {/foreach}
            </table>
        </div>

            <!--added this div for Temporarily -->
        {if $LISTVIEW_ENTRIES_COUNT eq '0'}
            <div class="row">
                <div class="emptyRecordsDiv">
                    {if $IS_MODULE_DISABLED eq 'true'}    
                        {vtranslate($RELATED_MODULE, $RELATED_MODULE)}
                        {vtranslate('LBL_MODULE_DISABLED', $RELATED_MODULE)}
                    {else}
                        {vtranslate('LBL_NO', $MODULE)} {vtranslate($RELATED_MODULE, $RELATED_MODULE)} {vtranslate('LBL_FOUND', $MODULE)}.
                    {/if}
                </div>
            </div>
        {/if}
        {if $FIELDS_INFO neq null}
            <script type="text/javascript">
                var popup_uimeta = (function() {
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
    </div>
</div>
{/strip}
