{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/List.js')}"></script>
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/SearchList.js')}"></script>
	<div id="searchResults-container" class="modal-body" style="padding:0!important">
		<div class="col-lg-12 clearfix">
			<div class="pull-right overlay-close">
				<button type="button" class="close" aria-label="Close" data-target="#overlayPage" data-dismiss="modal">
					<span aria-hidden="true" class="fa fa-close"></span>
				</button>
			</div>
		</div>
		<div class="searchResults">
			<input type="hidden" value="{$SEARCH_VALUE|escape:"html"}" id="searchValue">
			<div class="scrollableSearchContent">
				<div class="container-fluid moduleResults-container">
					<input type="hidden" name="groupStart" value="{$GROUP_START}" class="groupStart"/>
					{assign var=NORECORDS value=false}
					{foreach key=MODULE item=LISTVIEW_MODEL from=$MATCHING_RECORDS}
						{assign var=RECORDS_COUNT value=$LISTVIEW_MODEL->recordsCount}
						{assign var=PAGING_MODEL value=$LISTVIEW_MODEL->pagingModel}
						{assign var=LISTVIEW_HEADERS value=$LISTVIEW_MODEL->listViewHeaders}
						{assign var=LISTVIEW_ENTRIES value=$LISTVIEW_MODEL->listViewEntries}
						{assign var=MODULE_MODEL value=$LISTVIEW_MODEL->getModule()}
						{assign var=QUICK_PREVIEW_ENABLED value=$MODULE_MODEL->isQuickPreviewEnabled()}
						{include file="ModuleSearchResults.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
						<br>
					{/foreach}
					{if !$MATCHING_RECORDS}
						<div class="emptyRecordsDiv">
							<div class="emptyRecordsContent">
								{vtranslate("LBL_NO_RECORDS_FOUND")}
							</div>
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}