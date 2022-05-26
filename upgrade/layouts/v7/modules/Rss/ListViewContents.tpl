{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Rss/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="listViewContentDiv" id="listViewContents">
		<div class="col-sm-12 col-xs-12">
			{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
			<div class="essentials-toggle" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
				<span class="essentials-toggle-marker fa {if $LEFTPANELHIDE eq '1'}fa-chevron-right{else}fa-chevron-left{/if} cursorPointer"></span>
			</div>
			<input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}" />
			<div class="listViewEntriesDiv">
				<span class="listViewLoadingImageBlock hide modal" id="loadingListViewModal">
					<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
					<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
				</span>
				<div class="feedContainer">
					{if $RECORD}
						<input id="recordId" type="hidden" value="{$RECORD->getId()}">
						<div class="row-fluid detailViewButtoncontainer">
							<span class="btn-toolbar pull-right">
								<span class="btn-group">
									<button id="deleteButton" class="btn btn-default">&nbsp;{vtranslate('LBL_DELETE', $MODULE)}</button>
									<button id="makeDefaultButton" class="btn btn-default">&nbsp;{vtranslate('LBL_SET_AS_DEFAULT', $MODULE)}</button>
								</span>
							</span>
							<span class="row-fluid" id="rssFeedHeading">
								<h3> {vtranslate('LBL_FEEDS_LIST_FROM',$MODULE)} : {$RECORD->getName()} </h3>
							</span>
						</div>
						<div class="table-container feedListContainer" style="overflow: auto;"> 
							{include file='RssFeedContents.tpl'|@vtemplate_path:$MODULE}
						</div>
					{else}
						<table class="table-container emptyRecordsDiv">
							<tbody>
								<tr>
									<td>
										{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
										{vtranslate('LBL_NO')} {vtranslate($MODULE, $MODULE)} {vtranslate('LBL_FOUND')}. {vtranslate('LBL_CREATE')}<a class="rssAddButton" href="#" data-href="{$QUICK_LINKS['SIDEBARLINK'][0]->getUrl()}">&nbsp;{vtranslate($SINGLE_MODULE, $MODULE)}</a>
									</td>
								</tr>
							</tbody>
						</table>
					{/if}
				</div>
			</div>
			<br>
			<div class="feedFrame">
			</div>
		</div>
		<div id="scroller_wrapper" class="bottom-fixed-scroll">
			<div id="scroller" class="scroller-div"></div>
		</div>
	</div>
{/strip}
