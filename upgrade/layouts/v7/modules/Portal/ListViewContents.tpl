{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Portal/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<input type="hidden" id="pageNumber" value="{$CURRENT_PAGE}">
	<input type="hidden" id="totalCount" value="{$PAGING_INFO['recordCount']}" />
	<input type="hidden" id="recordsCount" value="{$PAGING_INFO['recordCount']}"/>
	<input type="hidden" id="selectedIds" name="selectedIds" />
	<input type="hidden" id="excludedIds" name="excludedIds" />
	<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}" />
	<input type="hidden" id="pageStartRange" value="{$PAGING_INFO['startSequence']}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_INFO['endSequence']}" />
	<input type="hidden" id="previousPageExist" {if $CURRENT_PAGE neq 1}value="1"{/if} />
	<input type="hidden" id="nextPageExist" value="{$PAGING_INFO['nextPageExists']}" />
	<input type="hidden" id="pageLimit" value="{$PAGING_INFO['pageLimit']}" />
	<input type="hidden" id="noOfEntries" value="{$NO_OF_ENTRIES}" />
	<input type="hidden" value="{$COLUMN_NAME}" name="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" name="sortOrder">
	{include file="modules/Portal/ListViewActions.tpl"}
	<div id="selectAllMsgDiv" class="hide" style = "background:gold;height:20px">
		<center><a href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a></center>
	</div>
	<div id="deSelectAllMsgDiv" class="hide" style = "background:gold;height:20px">
		<center><a href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></center>
	</div>
	<div class="contents-topscroll noprint">
		<div class="topscroll-div">
			&nbsp;
		</div>
	</div>
	<div class="col-md-12 col-sm-12 col-xs-12 col-lg-12 listViewContentDiv" id="listViewContents">
		<div id="table-content" class="table-container">
			{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
			<table id="listview-table" class="table listview-table portal-table">
				<thead>
					<tr class="listViewContentHeader">
						<th>
				<div class="table-actions" style="margin-left:0px !important;">
					<span class="input">
						<input class="listViewEntriesMainCheckBox" type="checkbox">
					</span>
				</div>
				</th>
				<th>
					<a href="#" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq 'portalname'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="portalname">
						{if $COLUMN_NAME eq 'portalname'}
							<i class="fa fa-sort {$FASORT_IMAGE}"></i>
						{else}
							<i class="fa fa-sort customsort"></i>
						{/if}
						&nbsp;{vtranslate('LBL_BOOKMARK_NAME', $MODULE)}&nbsp;
					</a>
					{if $COLUMN_NAME eq 'portalname'}
						<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
						{/if}
				</th>
				<th>
					<a href="#" class="listViewContentHeaderValues"
					   data-nextsortorderval="{if $COLUMN_NAME eq 'portalurl'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="portalurl">
						{if $COLUMN_NAME eq 'portalurl'}
							<i class="fa fa-sort {$FASORT_IMAGE}"></i>
						{else}
							<i class="fa fa-sort customsort"></i>
						{/if}
						&nbsp;{vtranslate('LBL_BOOKMARK_URL', $MODULE)}&nbsp;
					</a>
					{if $COLUMN_NAME eq 'portalurl'}
						<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
						{/if}
				</th>
				<th>
					<a href="#" class="listViewContentHeaderValues"
					   data-nextsortorderval="{if $COLUMN_NAME eq 'createdtime'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="createdtime">
						{if $COLUMN_NAME eq 'createdtime'}
							<i class="fa fa-sort {$FASORT_IMAGE}"></i>
						{else}
							<i class="fa fa-sort customsort"></i>
						{/if}
						&nbsp;{vtranslate('LBL_CREATED_ON', $MODULE)}&nbsp;
					</a>
					{if $COLUMN_NAME eq 'createdtime'}
						<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
					{/if}
				</th>
				</tr>
				</thead>
				<tbody class="overflow-y">
					{foreach item=LISTVIEW_ENTRY key=RECORD_ID from=$LISTVIEW_ENTRIES}
						<tr class="listViewEntries" data-id="{$RECORD_ID}" data-recordurl="index.php?module=Portal&view=Detail&record={$RECORD_ID}">
							<td class="listViewRecordActions">
								<div class="table-actions">
									<span class="input" >
										<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
									</span>
									<span class="more dropdown action">
										<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
											<i class="fa fa-ellipsis-v icon"></i>
										</span>
										<ul class="dropdown-menu" style="top:auto;bottom:30%;" data-id="{$RECORD_ID}">
											<li><a href="javascript:void(0);" class="editPortalRecord" >{vtranslate('LBL_EDIT', $MODULE)}</a></li>
											<li><a href="javascript:void(0);" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
										</ul>
									</span>
								</div>
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
								<a href="index.php?module=Portal&view=Detail&record={$RECORD_ID}" sl-processed="1">{$LISTVIEW_ENTRY->get('portalname')}</a>
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
								<a class="urlField cursorPointer" href="{if substr($LISTVIEW_ENTRY->get('portalurl'), 0, 4) neq 'http'}//{/if}{$LISTVIEW_ENTRY->get('portalurl')}" target="_blank" sl-processed="1">{$LISTVIEW_ENTRY->get('portalurl')}</a>
							</td>
							<td class="listViewEntryValue {$WIDTHTYPE}" nowrap>{$LISTVIEW_ENTRY->get('createdtime')}</td>
						</tr>
					{/foreach}
					{if $PAGING_INFO['recordCount'] eq '0'}
						<tr class="emptyRecordsDiv">
							{assign var=COLSPAN_WIDTH value={count($LISTVIEW_HEADERS)}+1}
							<td colspan="{$COLSPAN_WIDTH}">
								<div class="emptyRecordsContent">
									{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
									{vtranslate('LBL_NO')} {vtranslate('LBL_BOOKMARKS', $MODULE)} {vtranslate('LBL_FOUND')}. {vtranslate('LBL_CREATE')}&nbsp;
									<a class="addBookmark" style="color:blue;">{vtranslate('LBL_BOOKMARK', $MODULE)}</a>
								</div>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
	</div>
{/strip}
