{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE LISTVIEW_HEADERS=$RELATED_HEADERS}
<div class="relatedContainer">
    {assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
    {assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
    <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
    <input type="hidden" value="{$ORDER_BY}" id="orderBy">
    <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
    <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
    <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
    <input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
    <input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
    <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
    <input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
    <input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>
    <div class="relatedHeader">
        <div class="btn-toolbar row">
            <div class="col-lg-6 col-md-6 col-sm-6 btn-toolbar">
                <div class="row">
                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}

						{if $RELATED_LINK->get('linkmodule') eq 'Documents'}
                            <div class="col-sm-3" style="width:22%;">
                                {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                                {* setting button module attribute to Events or Calendar based on link label *}
                                {assign var=LINK_LABEL value={$RELATED_LINK->get('linklabel')}}
                                {if $RELATED_LINK->get('_linklabel') === '_add_event'}
                                    {assign var=RELATED_MODULE_NAME value='Events'}
                                {elseif $RELATED_LINK->get('_linklabel') === '_add_task'}
                                    {assign var=RELATED_MODULE_NAME value='Calendar'}
                                {/if}
                                <button type="button" module="{$RELATED_MODULE_NAME}"  class="btn addButton btn-default
                                    {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
                                    {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                                    {if ($RELATED_LINK->isPageLoadLink())}
                                    {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                                    data-url="{$RELATED_LINK->getUrl()}"
                                    {/if}
                                {if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}&nbsp;{$RELATED_LINK->getLabel()}</button>
                            </div>
                            {/if}
                            
                            {if $RELATED_LINK->getLabel() eq 'Vtiger'}
								{if $IS_CREATE_PERMITTED}
									<div class="col-sm-3">
										<div class="dropdown">
											<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
												<span class="fa fa-plus" title="{vtranslate('LBL_NEW_DOCUMENT', $MODULE)}"></span>&nbsp;&nbsp;{vtranslate('LBL_NEW_DOCUMENT', $RELATED_MODULE_NAME)}&nbsp; <span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
												<li class="dropdown-header"><i class="fa fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', $RELATED_MODULE_NAME)}</li>
												<li id="VtigerAction">
													<a href="javascript:Documents_Index_Js.uploadTo('Vtiger',{$PARENT_ID},'{$MODULE}')">
														<img style="  margin-top: -3px;margin-right: 4%;" title="Vtiger" alt="Vtiger" src="layouts/v7/skins//images/Vtiger.png">
														{vtranslate('LBL_TO_SERVICE', $RELATED_MODULE_NAME, {vtranslate('LBL_VTIGER', $RELATED_MODULE_NAME)})}
													</a>
												</li>
												<li role="separator" class="divider"></li>
												<li class="dropdown-header"><i class="fa fa-link"></i> {vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $RELATED_MODULE_NAME)}</li>
												<li id="shareDocument"><a href="javascript:Documents_Index_Js.createDocument('E',{$PARENT_ID},'{$MODULE}')">&nbsp;<i class="fa fa-external-link"></i>&nbsp;&nbsp; {vtranslate('LBL_FROM_SERVICE', $RELATED_MODULE_NAME, {vtranslate('LBL_FILE_URL', $RELATED_MODULE_NAME)})}</a></li>
												<li role="separator" class="divider"></li>
												<li id="createDocument"><a href="javascript:Documents_Index_Js.createDocument('W',{$PARENT_ID},'{$MODULE}')"><i class="fa fa-file-text"></i> {vtranslate('LBL_CREATE_NEW', $RELATED_MODULE_NAME, {vtranslate('SINGLE_Documents', $RELATED_MODULE_NAME)})}</a></li>
											</ul>
										</div>
									</div>
								{/if}
                            {/if}
                            
                    {/foreach}
                </div>&nbsp;
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

    {if $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS}
        <div class="row-fluid" data-module="{$MODULE}">
            {assign var=IS_VIEWABLE value=$PARENT_RECORD->isBundleViewable()}
            <input type="hidden" class="isShowBundles" value="{$IS_VIEWABLE}">
            <label class="showBundlesInInventory checkbox"><input type="checkbox" {if $IS_VIEWABLE}checked{/if} value="{$IS_VIEWABLE}">&nbsp;&nbsp;{vtranslate('LBL_SHOW_BUNDLE_IN_INVENTORY', $MODULE)}</label>
        </div>
    {/if}
    
    <div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
    <div class="bottomscroll-div">
        {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
        <table id="listview-table"  class="table listview-table">
            <thead>
                <tr class="listViewHeaders">
                    <th style="min-width:100px">
                    </th>
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                    {* hide time_start,time_end columns in the list as they are merged with with Start Date and End Date fields *}
                        {if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end'}
                            <th class="nowrap" style="width:15px">
                        {else}
                        <th class="nowrap">
                            {if $HEADER_FIELD->get('column') eq "access_count" or $HEADER_FIELD->get('column') eq "idlists"}
                                <a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
                            {else}
                                <a href="javascript:void(0);" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
                                    {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                    <i class="fa fa-sort {$FASORT_IMAGE}"></i>
                                    {else}
                                        <i class="fa fa-sort customsort"></i>
                                    {/if}
                                    &nbsp;
                                    {vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
                                    &nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}&nbsp;
                                </a>
                                {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                   <a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
                                {/if}
                            {/if}
                        {/if}
                        </th>
                    {/foreach}
                </tr>
                <tr class="searchRow">
                        <th class="inline-search-btn">
                            <button class="btn btn-success btn-sm" data-trigger="relatedListSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
                        </th>
                            {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                <th>
                                    {if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->get('column') eq 'folderid' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
                                    {else}    
                                        {assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
                                        {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME)
                                        FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS[$HEADER_FIELD->getName()] USER_MODEL=$USER_MODEL}
                                        <input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$HEADER_FIELD->getName()]['comparator']}">
                                    {/if}
                                </th>
                            {/foreach}
                </tr>
            </thead>
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                <tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' 
                                        {if $RELATED_MODULE_NAME eq 'Calendar'}
						data-recurring-enabled='{$RELATED_RECORD->isRecurringEnabled()}'
                                            {assign var=DETAILVIEWPERMITTED value=isPermitted($RELATED_MODULE->get('name'), 'DetailView', $RELATED_RECORD->getId())}
                                            {if $DETAILVIEWPERMITTED eq 'yes'}
                            data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                                            {/if}
                                        {else}
                        data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'
                                        {/if}>
                    <td style="width:100px">&nbsp;&nbsp;&nbsp;
                        <span class="actionImages">
                            <a name="relationEdit" data-url="{$RELATED_RECORD->getEditViewUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a> &nbsp;&nbsp;
                            {if $IS_DELETABLE}
                            <a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i></a>&nbsp;&nbsp;
                            {/if}
                            {assign var=RECORD_ID value=$RELATED_RECORD->getId()}
                            {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
                            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                                <a name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)"><i title="{vtranslate('LBL_VIEW_FILE', $RELATED_MODULE_NAME)}" class="fa fa-picture-o alignMiddle"></i></a>&nbsp;&nbsp;
                            {/if}
                            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                                <a name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}" onclick="event.stopImmediatePropagation();"><i title="{vtranslate('LBL_DOWNLOAD_FILE', $RELATED_MODULE_NAME)}" class="fa fa-download alignMiddle"></i></a>
                            {/if}
                        </span>
                        
                    </td>
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                        {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
                        {assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
                        {assign var=IS_DOCUMENT_SOURCE_FIELD value=0}
                        {if $RELATED_MODULE->get('name') eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
                            {if $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Vtiger' || $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Google Drive' || $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Dropbox'}
                                {assign var=IS_DOCUMENT_SOURCE_FIELD value=1}
                            {/if}
                        {/if}
                       <td class="{$WIDTHTYPE} relatedListEntryValues " data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap style="width:inherit;">
                            {if $RELATED_MODULE->get('name') eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
								<center>{$RELATED_RECORD->get($RELATED_HEADERNAME)}</center>
                            {else}
                            <span class= "value textOverflowEllipsis">
                                {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                                     <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                                {elseif $RELATED_HEADERNAME eq 'access_count'}
                                    {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                                {elseif $RELATED_HEADERNAME eq 'time_start' or $RELATED_HEADERNAME eq 'time_end'}
                                {elseif $RELATED_MODULE_NAME eq 'PriceBooks' AND ($RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price')}
                                    {if $RELATED_HEADERNAME eq 'listprice'}
                                        {assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                    {/if}
                                    {CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                    {elseif $HEADER_FIELD->get('uitype') eq '71' or $HEADER_FIELD->get('uitype') eq '72'}
                                        {assign var=CURRENCY_SYMBOL value=Vtiger_RelationListView_Model::getCurrencySymbol($RELATED_RECORD->get('id'), $HEADER_FIELD)}
                                        {assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME))}
                                        {if $HEADER_FIELD->get('uitype') eq '72'}
                                            {assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                        {/if}
                                        {if Users_Record_Model::getCurrentUserModel()->get('currency_symbol_placement') eq '$1.0'}
                                            {$CURRENCY_SYMBOL}{$CURRENCY_VALUE}
                                        {else}
                                            {$CURRENCY_VALUE}{$CURRENCY_SYMBOL}
                                        {/if}
                                        {if $RELATED_HEADERNAME eq 'listprice'}
                                            {assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                        {/if}
                                    {else if $HEADER_FIELD->getFieldDataType() eq 'picklist'}
                                        <span {if !empty($RELATED_LIST_VALUE)} class="picklist-color picklist-{$HEADER_FIELD->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}" {/if}> {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} </span>
                                    {else}
                                        {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                    {/if}
                                </span>
                                    {/if}
                            </td>
                    {/foreach}
                </tr>
            {/foreach}
        </table>
    </div>
</div>
    <script type="text/javascript">
        var related_uimeta = (function() {
            var fieldInfo  = {$RELATED_FIELDS_INFO};
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
</div>
{/strip}
{literal}
    <script type="text/javascript">
        jQuery(function() {
            if(typeof Documents_Index_Js !== 'function') {
                jQuery("body").append('<script type="text/javascript" src="layouts/v7/modules/Documents/resources/Documents.js"><\/script>');
            }
        });
    </script>
{/literal}