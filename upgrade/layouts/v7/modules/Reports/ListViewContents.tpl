{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="col-sm-12 col-xs-12 ">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div class="essentials-toggle" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
			<span class="essentials-toggle-marker fa {if $LEFTPANELHIDE eq '1'}fa-chevron-right{else}fa-chevron-left{/if} cursorPointer"></span>
		</div>
		<input type="hidden" name="view" id="view" value="{$VIEW}" />
		<input type="hidden" name="cvid" value="{$VIEWID}" />
		<input type="hidden" name="pageStartRange" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
		<input type="hidden" name="pageEndRange" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
		<input type="hidden" name="previousPageExist" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
		<input type="hidden" name="nextPageExist" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
		<input type="hidden" name="Operator" id="Operator" value="{$OPERATOR}" />
		<input type="hidden" name="totalCount" id="totalCount" value="{$LISTVIEW_COUNT}" />
		<input type='hidden' name="pageNumber" value="{$PAGE_NUMBER}" id='pageNumber'>
		<input type='hidden' name="pageLimit" value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
		<input type="hidden" name="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
		<input type="hidden" name="currentSearchParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams" />
		<input type="hidden" name="noFilterCache" value="{$NO_SEARCH_PARAMS_CACHE}" id="noFilterCache" >
		<input type="hidden" name="orderBy" value="{$ORDER_BY}" id="orderBy">
		<input type="hidden" name="sortOrder" value="{$SORT_ORDER}" id="sortOrder">
		<input type="hidden" name="list_headers" value='{$LIST_HEADER_FIELDS}'/>
		<input type="hidden" name="tag" value="{$CURRENT_TAG}" />
		<input type="hidden" name="folder_id" value="{$FOLDER_ID}" />
		<input type="hidden" name="folder_value" value="{$FOLDER_VALUE}" />
		<input type="hidden" name="folder" value="{$VIEWNAME}" />
		{if !$SEARCH_MODE_RESULTS}
			{include file="ListViewActions.tpl"|vtemplate_path:$MODULE}
		{/if}
		<div id="table-content" class="table-container">
			<form name='list' id='listedit' action='' onsubmit="return false;">
				<table id="listview-table"  class="table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords {/if} listview-table">
					<thead>
						<tr class="listViewContentHeader">
							<th>
								{if !$SEARCH_MODE_RESULTS}
									<div class="table-actions">
										<div class="dropdown" style="float:left;margin-left:6px;">
											<span class="input dropdown-toggle" title="{vtranslate('LBL_CLICK_HERE_TO_SELECT_ALL_RECORDS',$MODULE)}" data-toggle="dropdown">
												<input class="listViewEntriesMainCheckBox" type="checkbox">
											</span>
										</div>
									</div>
								{elseif $SEARCH_MODE_RESULTS}
									{vtranslate('LBL_ACTIONS',$MODULE)}
								{/if}
							</th>
							{assign var="LISTVIEW_HEADERS" value=$LISTVIEW_MODEL->getListViewHeadersForVtiger7({$VIEWNAME})}
							{foreach item=LISTVIEW_HEADER key=LISTVIEW_HEADER_KEY from=$LISTVIEW_HEADERS}
								<th {if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY} nowrap="nowrap" {/if}>
									<a href="#" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER_KEY}">
										{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}
											<i class="fa fa-sort {$FASORT_IMAGE}"></i>
										{else}
											<i class="fa fa-sort customsort"></i>
										{/if}
										&nbsp;{vtranslate($LISTVIEW_HEADERS[$LISTVIEW_HEADER_KEY]['label'],$MODULE)}&nbsp;
									</a>
									{if $COLUMN_NAME eq $LISTVIEW_HEADER_KEY}
										<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
									{/if}
								</th>
							{/foreach}
						</tr>

						{if $MODULE_MODEL->isQuickSearchEnabled() && !$SEARCH_MODE_RESULTS}
							<tr class="searchRow">
								<th class="inline-search-btn">
									<div class="table-actions">
										<button class="btn btn-success btn-sm" data-trigger="listSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
									</div>
								</th>
								{foreach item=LISTVIEW_HEADER key=LISTVIEW_HEADER_KEY from=$LISTVIEW_HEADERS}
									<th>
										{assign var="DATA_TYPE" value=$LISTVIEW_HEADER['type']}
										{if $DATA_TYPE == 'string'}
											<div class="row-fluid">
												<input type="text" name="{$LISTVIEW_HEADER_KEY}" class="listSearchContributor inputElement" value="{$SEARCH_DETAILS[$LISTVIEW_HEADER_KEY]['searchValue']}" data-fieldinfo='{$FIELD_INFO|escape}'/>
											</div>
										{elseif $DATA_TYPE == 'picklist'}
											{assign var=PICKLIST_VALUES value=Reports_Field_Model::getPicklistValueByField($LISTVIEW_HEADER_KEY)}
											{assign var=SEARCH_VALUES value=explode(',',$SEARCH_DETAILS[$LISTVIEW_HEADER_KEY]['searchValue'])}
											<div class="row-fluid">
												<select class="select2 listSearchContributor report-type-select" name="{$LISTVIEW_HEADER_KEY}" multiple data-fieldinfo='{$FIELD_INFO|escape}'>
													{foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
														{if $PICKLIST_LABEL eq 'Chart'}
															{assign var="ICON_CLASS" value='fa fa-pie-chart'}
														{elseif $PICKLIST_LABEL eq 'Detail'}
															{assign var="ICON_CLASS" value='vicon-detailreport'}
														{/if}
														<option value="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "") } selected{/if} {if $LISTVIEW_HEADER_KEY eq 'reporttype'}class='{$ICON_CLASS}'{/if}>{$PICKLIST_LABEL}</option>
													{/foreach}
												</select>
											</div>
										{/if}
										<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$LISTVIEW_HEADER_KEY]['comparator']}">
									</th>
								{/foreach}
							</tr>
						{/if}
					</thead>
					<tbody class="overflow-y">
						{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
							<tr class="listViewEntries" data-id='{$LISTVIEW_ENTRY->getId()}' data-recordUrl='{$LISTVIEW_ENTRY->getDetailViewUrl()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
								<td class = "listViewRecordActions">
									{include file="ListViewRecordActions.tpl"|vtemplate_path:$MODULE }
								</td>
								{foreach item=LISTVIEW_HEADER key=LISTVIEW_HEADER_KEY  from=$LISTVIEW_HEADERS}
									{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER_KEY}
									{assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADER_KEY)}
									{assign var=LISTVIEW_ENTRY_VALUE value=$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
									<td class="listViewEntryValue" data-name="{$LISTVIEW_HEADERNAME}" title="{$LISTVIEW_ENTRY_RAWVALUE}" data-rawvalue="{$LISTVIEW_ENTRY_RAWVALUE}" data-field-type="">
										<span class="fieldValue">
											<span class="value textOverflowEllipsis">
												{if $LISTVIEW_HEADERNAME eq 'reporttype'}
													{if $LISTVIEW_ENTRY_VALUE eq 'summary' || $LISTVIEW_ENTRY_VALUE eq 'tabular'}
														<center title="{vtranslate('LBL_DETAIL_REPORT', $MODULE)}"><span class='vicon-detailreport' style="font-size:17px;"></span></center>
													{elseif $LISTVIEW_ENTRY_VALUE eq 'chart'}
														<center title="{vtranslate('LBL_CHART_REPORT', $MODULE)}"><span class='fa fa-pie-chart fa-2x' style="font-size:1.7em;"></span></center>
													{/if}
												{else if $LISTVIEW_HEADERNAME eq 'primarymodule'}
													{Vtiger_Util_Helper::tosafeHTML(decode_html(vtranslate($LISTVIEW_ENTRY_VALUE, $LISTVIEW_ENTRY_VALUE)))}
												{else if $LISTVIEW_HEADERNAME eq 'foldername'}
													{Vtiger_Util_Helper::tosafeHTML(vtranslate($LISTVIEW_ENTRY_VALUE, $MODULE))}
												{else}
													{Vtiger_Util_Helper::tosafeHTML($LISTVIEW_ENTRY_VALUE)}
												{/if}
											</span>
										</span>
										</span>
									</td>
								{/foreach}
							</tr>
						{/foreach}
						{if $LISTVIEW_ENTRIES_COUNT eq '0'}
							<tr class="emptyRecordsDiv">
								{assign var=COLSPAN_WIDTH value={count($LISTVIEW_HEADERS)}+1}
								<td colspan="{$COLSPAN_WIDTH}">
									<div class="emptyRecordsDiv">
										<div class="emptyRecordsContent">
											{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
											{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}.{if $IS_MODULE_EDITABLE} <a href="{$MODULE_MODEL->getCreateRecordUrl()}"> {vtranslate('LBL_CREATE')} </a> {if Users_Privileges_Model::isPermitted($MODULE, 'Import') && $LIST_VIEW_MODEL->isImportEnabled()} {vtranslate('LBL_OR', $MODULE)} <a style="color:blue" href="#" onclick="return Vtiger_Import_Js.triggerImportAction()"> {vtranslate('LBL_IMPORT', $MODULE)} </a>{vtranslate($MODULE, $MODULE)}{else}{vtranslate($SINGLE_MODULE, $MODULE)}{/if}{/if}
										</div>
									</div>
								</td>
							</tr>
						{/if}
					</tbody>
				</table>
			</form>
		</div>
		<div id="scroller_wrapper" class="bottom-fixed-scroll">
			<div id="scroller" class="scroller-div"></div>
		</div>
	</div>
{/strip}