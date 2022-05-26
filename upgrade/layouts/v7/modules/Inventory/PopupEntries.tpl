{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Inventory/views/SubProductsPopup.php *}

{strip}
    <input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
    <input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
    <input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
    <input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
    <input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
    <input type="hidden" id="view" value="{$VIEW}"/>
    <input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
    <input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
    <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
    <input type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams" />
    {if (!empty($SUBPRODUCTS_POPUP)) and (!empty($PARENT_PRODUCT_ID))}
        <input type="hidden" id="subProductsPopup" value="{$SUBPRODUCTS_POPUP}" />
        <input type="hidden" id="parentProductId" value="{$PARENT_PRODUCT_ID}" />
    {/if}
    <div class="contents-topscroll">
        <div class="topscroll-div">
            &nbsp;
        </div>
    </div>
    <div class="popupEntriesDiv relatedContents contents-bottomscroll">
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="Inventory_Popup_Js" id="popUpClassName"/>
        {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
        <div class="bottomscroll-div">
            <table class="listview-table table-bordered listViewEntriesTable">
                <thead>
                    <tr class="listViewHeaders">
                        {if $MULTI_SELECT}
                            <th class="{$WIDTHTYPE}">
                                <input type="checkbox"  class="selectAllInCurrentPage" />
                            </th>
                        {else}
                            <th class="{$WIDTHTYPE}">&nbsp;</th>
                        {/if}
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <th class="{$WIDTHTYPE}">
                                <a href="javascript:void(0);" class="listViewContentHeaderValues listViewHeaderValues" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">
                                    {if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}
                                        <i class="fa fa-sort {$FASORT_IMAGE}"></i>
                                    {else}
                                        <i class="fa fa-sort customsort"></i>
                                    {/if}
                                    &nbsp;{vtranslate($LISTVIEW_HEADER->get('label'), $TARGET_MODULE)}&nbsp;
                                </a>
                            </th>
                        {/foreach}
						{if $RELATED_MODULE eq 'Products'}
							<th></th>
						{/if}
                    </tr>
                </thead>
                {if $MODULE_MODEL && $MODULE_MODEL->isQuickSearchEnabled()}
                    <tr class="searchRow">
                        <td class="searchBtn textAlignCenter">
                            <button class="btn btn-success" data-trigger="PopupListSearch">{vtranslate('LBL_SEARCH', $MODULE )}</button>
                        </td>
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <td>
                                {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                                {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME)
                                FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
                            </td>
                        {/foreach}
						{if $RELATED_MODULE eq 'Products'}
							<td></td>
						{/if}
                    </tr>
                {/if}
                {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
                    {assign var="RECORD_DATA" value="{$LISTVIEW_ENTRY->getRawData()}"}
                    <tr class="listViewEntries" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-info='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($LISTVIEW_ENTRY->getRawData()))}'
                        {if $GETURL neq '' } data-url="{$LISTVIEW_ENTRY->$GETURL()|cat:'&sourceModule='|cat:$SOURCE_MODULE}" {/if}  id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
                        {if $MULTI_SELECT}
                            <td class="{$WIDTHTYPE}">
                                <input class="entryCheckBox" type="checkbox" />
                            </td>
                        {else}
                            <td></td>
                        {/if}
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                            <td class="listViewEntryValue textOverflowEllipsis {$WIDTHTYPE}" title="{$RECORD_DATA[$LISTVIEW_HEADERNAME]}">
                                {if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
                                    <a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
                                {else if $LISTVIEW_HEADER->get('uitype') eq '72'}
                                    {assign var=CURRENCY_SYMBOL_PLACEMENT value={$CURRENT_USER_MODEL->get('currency_symbol_placement')}}
                                    {if $CURRENCY_SYMBOL_PLACEMENT eq '1.0$'}
                                        {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}{$LISTVIEW_ENTRY->get('currencySymbol')}
                                    {else}
                                        {$LISTVIEW_ENTRY->get('currencySymbol')}{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                                    {/if}
                                {else}
                                    {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                                {/if}
                            </td>
                        {/foreach}
						{if $RELATED_MODULE eq 'Products'}
							<td class="listViewEntryValue {$WIDTHTYPE}">
								{if $LISTVIEW_ENTRY->get('subProducts') eq true}
									<a class="subproducts"><b>{vtranslate('LBL_SUB_PRODUCTS',$MODULE_NAME)}</b></a>
									<!--<img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid" src="{vimage_path('Products.png')}"/>-->
								{else}
									{vtranslate('NOT_A_BUNDLE',$MODULE_NAME)}
								{/if}
							</td>
						{/if}
                    </tr>
                {/foreach}
            </table>
            <!--added this div for Temporarily -->
            {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                {if $IS_MODULE_DISABLED eq 'true'}
                    <div class="emptyRecordsDiv">{vtranslate('LBL_PRODUCTSMOD_DISABLED', $RELATED_MODULE)}.</div>
                {else}
                    <div class="emptyRecordsDiv">{vtranslate('LBL_NO', $MODULE)} {vtranslate($RELATED_MODULE, $RELATED_MODULE)} {vtranslate('LBL_FOUND', $MODULE)}.</div>
                {/if}
            {/if}
        </div>
    </div>
    {if (!empty($SUBPRODUCTS_POPUP)) and (!empty($PARENT_PRODUCT_ID))}
        <div style="margin-top: 10px; height:50px">
            <div class="pull-right">
                <button type="button" class="btn btn-default" id="backToProducts"><strong>{vtranslate('LBL_BACK_TO_PRODUCTS', $MODULE)}</strong></button>
            </div>
        </div>
    {/if}
    <div id="scroller_wrapper" class="bottom-fixed-scroll">
        <div id="scroller" class="scroller-div"></div>
    </div>
    {if $FIELDS_INFO neq null}
        <script type="text/javascript">
            var popup_uimeta = (function() {
                var fieldInfo = {$FIELDS_INFO};
                return {
                    field: {
                        get: function(name, property) {
                            if (name && property === undefined) {
                                return fieldInfo[name];
                            }
                            if (name && property) {
                                return fieldInfo[name][property]
                            }
                        },
                        isMandatory: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].mandatory;
                            }
                            return false;
                        },
                        getType: function(name) {
                            if (fieldInfo[name]) {
                                return fieldInfo[name].type
                            }
                            return false;
                        }
                    },
                };
            })();
        </script>
    {/if}
{/strip}
