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
    <div class="relatedContainer">
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        {assign var="RELATED_MODULE_NAME" value=$RELATED_MODULE->get('name')}
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
        <input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
        <div class="relatedHeader ">
            <div class="btn-toolbar row-fluid">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    
                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button onclick="javascript:Vtiger_Detail_Js.triggerSendEmail('index.php?module={$MODULE}&view=MassActionAjax&mode=showComposeEmailForm&step=step1&relatedLoad=true','Emails');" type="button" class="btn addButton btn-default
                                    {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
                                    {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                                    {if ($RELATED_LINK->isPageLoadLink())}
                                        {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                                    {/if}
                                    {if $IS_SELECT_BUTTON neq true}name="composeEmail"{/if}>{if $IS_SELECT_BUTTON eq false}<i class="fa fa-plus"></i>&nbsp;{/if}&nbsp;{$RELATED_LINK->getLabel()}
                            </button>
                        </div>
                    {/foreach}
                    &nbsp;
                </div>
                {assign var=CLASS_VIEW_ACTION value='relatedViewActions'}
                {assign var=CLASS_VIEW_PAGING_INPUT value='relatedViewPagingInput'}
                {assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='relatedViewPagingInputSubmit'}
                {assign var=CLASS_VIEW_BASIC_ACTION value='relatedViewBasicAction'}
                {assign var=PAGING_MODEL value=$PAGING}
                {assign var=RECORD_COUNT value=$RELATED_RECORDS|@count}
                {assign var=PAGE_NUMBER value=$PAGING->get('page')}
                {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
            </div>
        </div>
                    
        <div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
            <div class="bottomscroll-div">
                {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                <table id="listview-table"  class="table listview-table">
                    <thead>
                        <tr class="listViewHeaders">
                            <th class="{$WIDTHTYPE}">
                                <a href="javascript:void(0);" class="noSorting">{vtranslate('LBL_SENDER_NAME')}</a>
                            </th>
                            {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                <th class="{$WIDTHTYPE}">
                                    {if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists'}
                                        <a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</a>
                                    {else}
                                        <a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
                                            {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                                <i class="fa fa-sort {$FASORT_IMAGE}"></i>
                                            {else}
                                                <i class="fa fa-sort customsort"></i>
                                            {/if}&nbsp;&nbsp;
                                            {vtranslate($HEADER_FIELD->get('label')|html_entity_decode, $RELATED_MODULE_NAME)}
                                            &nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}&nbsp;
                                        </a>
                                        {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                            <a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
                                        {/if}
                                    {/if}
                                </th>
                            {/foreach}
                            <th class="{$WIDTHTYPE}">
                                <a href="javascript:void(0);" class="noSorting">{vtranslate('LBL_ORIGIN')}</a>
                            </th>
                        </tr>
                    </thead>
                    {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                        {assign var=EMAIL_FLAG value=$RELATED_RECORD->getEmailFlag()}
                        <tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-emailflag='{$EMAIL_FLAG}' name="emailsRelatedRecord">
                            <td class="{$WIDTHTYPE}">
                                <a>{$RELATED_RECORD->getSenderName($MODULE, $PARENT_RECORD->getId())}</a>
                            </td>
                            {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
                                <td class="{$WIDTHTYPE}">
                                    {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                                        <a>{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                                    {elseif $RELATED_HEADERNAME eq 'access_count'}
                                        {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                                    {elseif $RELATED_HEADERNAME eq 'click_count'}
                                        {$RELATED_RECORD->getClickCountValue($PARENT_RECORD->getId())}
                                    {elseif $RELATED_HEADERNAME eq 'date_start'}
                                        {if $EMAIL_FLAG neq 'SAVED'}
                                            {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                        {/if}
                                    {else if $RELATED_HEADERNAME eq 'time_start'}
                                        {if $EMAIL_FLAG neq 'SAVED'}  
                                            {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}    
                                        {/if}
                                    {else if $HEADER_FIELD->getFieldDataType() eq 'owner'}
                                        {getOwnerName($RELATED_RECORD->get($RELATED_HEADERNAME))}
                                    {else}
                                        {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                    {/if}
                                </td>
                            {/foreach}
                            <td class="{$WIDTHTYPE}">
                                <span class="label {if $EMAIL_FLAG eq 'SAVED'}label-info{else if $EMAIL_FLAG eq 'SENT'}label-success{else}label-warning{/if}">
                                    {vtranslate($EMAIL_FLAG)}
                                </span>
                                <div class="pull-right actions">
                                    <span class="actionImages">
                                            <a name="emailsDetailView" data-id='{$RELATED_RECORD->getId()}'><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="fa fa-bars"></i></a>&nbsp;&nbsp;
                                            {if $RELATED_RECORD->getEmailFlag() eq 'SAVED'}
                                            <a name="emailsEditView"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a>	&nbsp;&nbsp;
                                            {/if}
                                            {if $IS_DELETABLE}
                                            <a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i></a>
                                            {/if}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </table>
            </div>
        </div>
    </div>
{/strip}
