{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<div class="container-fluid">
	<div class="row">
		{assign var=HEADER_TITLE value={vtranslate('LBL_DUPLICATE')|cat:' '|cat:vtranslate($MODULE, $MODULE)}}
		<h3>
			<div class="col-lg-7">
				{$HEADER_TITLE}
			 </div>
			<div class="col-lg-5">
				<div class="alert alert-static">
					<span class="fa fa-info-circle icon"></span>
					<span class="message">{vJsTranslate('JS_ALLOWED_TO_SELECT_MAX_OF_THREE_RECORDS',$MODULE)}</span>
				</div>
			</div>
		</h3>
	</div>
	<div class="row">
		<div class="col-lg-1">
			{if $LISTVIEW_ENTRIES_COUNT > 0}
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS}
					<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger pull-left" 
						{if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}>
							<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong>
					</button>
				{/foreach}
			{/if}
		</div>
		<div class="col-lg-11">
			<div class="col-lg-1">
				&nbsp;
			</div>
			<div class="col-lg-9 select-deselect-container" >
				<div class="hide messageContainer" style = "height:30px;">
					<center><a id="selectAllMsgDiv" href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a></center>
				</div>
				<div class="hide messageContainer" style = "height:30px;">
					<center><a id="deSelectAllMsgDiv" href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></center>
				</div>
			</div>
			{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
			{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
		</div>
	</div>
</div>