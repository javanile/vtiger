{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="listViewPageDiv">
		<div class="row">
			<div class="col-lg-12">
				<div class="col-lg-8">
					<h4 class="searchModuleHeader">{vtranslate($MODULE, $MODULE)}</h4>
					<input type="hidden" name="search_module" value="{$MODULE}"/>
				</div>
				<div class="col-lg-4" style="margin-top: 10px;">
					<div class="pull-right" >
						<input type="hidden" name="pageNumber" value="{$PAGE_NUMBER}">
						<input type="hidden" name="recordsCount" value="{$RECORDS_COUNT}">
						<span class="pageNumbersText" style="padding-right:5px">
							{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_to', $MODULE)} {$PAGING_MODEL->getRecordEndRange()} {vtranslate('LBL_OF',$MODULE)} {$RECORDS_COUNT}
						</span>
						<a href="#" class="previousPageButton navigationButton verticalAlignMiddle" data-start='{$PAGING_MODEL->getRecordStartRange()-$PAGING_MODEL->getPageLimit()}' {if !$PAGING_MODEL->isPrevPageExists()}disabled=""{/if}>
							<i class="fa fa-caret-left"></i>&nbsp;&nbsp;
						</a>
						<a href="#" class="nextPageButton navigationButton verticalAlignMiddle" data-start='{$PAGING_MODEL->getRecordEndRange()}' {if !$PAGING_MODEL->isNextPageExists()} disabled=""{/if}>
							<i class="fa fa-caret-right"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			{include file="ListViewContents.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
		</div>
	</div>
{/strip}