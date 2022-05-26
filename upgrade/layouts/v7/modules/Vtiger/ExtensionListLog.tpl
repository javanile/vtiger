{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
<div class="col-sm-12 col-xs-12 extensionContents">
    <div class="row">
        {if !$MODAL}
            <div class="col-sm-6 col-xs-6">
                <h3 class="module-title pull-left"> {vtranslate($MODULE,$MODULE)} - {vtranslate('LBL_SYNC_LOG', $MODULE)} </h3>
            </div>
            <div class="col-sm-6 col-xs-6">
                <div class="pull-right">
                    <span class="module-title">
                        <h3><a data-url="{$MODULE_MODEL->getExtensionSettingsUrl($SOURCE_MODULE)}" class="btn addButton btn-default settingsPage" type="button" id="Contacts_basicAction_LBL_Sync_Settings"><span aria-hidden="true" class="fa fa-cog"></span> {vtranslate('LBL_SYNC_SETTINGS', $MODULE)}</a></h3>
                    </span>
                </div>
            </div>
        {/if}
    </div>
    <br>
    <div class="row">
        {if !$MODAL}
            <div class="col-sm-6 col-xs-6">
                {if $IS_SYNC_READY}
                    <button class="btn addButton btn-success syncNow" type="button" id="Contacts_basicAction_LBL_Sync_Settings"><span aria-hidden="true" class="fa fa-refresh"></span><strong>&nbsp; {vtranslate('LBL_SYNC_NOW', $MODULE)} </strong></button>
                        {/if}
            </div>
        {/if}
        {if !$MODAL}
            <div class="col-sm-6 col-xs-6">
            {else}
                <div class="col-sm-12 col-xs-12">
        {/if}
                <input type="hidden" name="pageStartRange" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" /> 
	            <input type="hidden" name="pageEndRange" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" /> 
	            <input type="hidden" name="previousPageExist" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" /> 
	            <input type="hidden" name="nextPageExist" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" /> 
                <input type="hidden" name="totalCount" id="totalCount" value="{$TOTAL_RECORD_COUNT}" /> 
	            <input type='hidden' name="pageNumber" value="{$PAGING_MODEL->get('page')}" id='pageNumber'> 
	            <input type='hidden' name="pageLimit" value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'> 
                <input type="hidden" name="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries"> 
	            {assign var=RECORD_COUNT value=$TOTAL_RECORD_COUNT} 
	            {assign var=PAGE_NUMBER value=$PAGING_MODEL->get('page')} 
	            {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true} 
            </div>
        </div>
        <br>
        <div id="table-content" class="table-container">
        <table id="listview-table" class="listview-table table-bordered" align="center">
            <thead>
                <tr class="listViewContentHeader">
                    <th rowspan="2"> {vtranslate('LBL_DATE', $MODULE)} </th>
                    <th rowspan="2"> {vtranslate('LBL_TIME', $MODULE)} </th>
                    <th rowspan="2"> {vtranslate('LBL_MODULE', $MODULE)} </th>
                    <th colspan = "4" > {vtranslate('APPTITLE', $MODULE)} </th>
                    <th colspan = "4" > {vtranslate($MODULE,$MODULE)} </th>
                </tr>
                <tr class="listViewContentHeader">
                    <th> {vtranslate('Created', $MODULE)} </th>
                    <th> {vtranslate('LBL_UPDATED', $MODULE)} </th>
                    <th> {vtranslate('LBL_DELETED', $MODULE)} </th>
                    <th> {vtranslate('LBL_SKIPPED', $MODULE)} </th>
                    <th> {vtranslate('Created', $MODULE)} </th>
                    <th> {vtranslate('LBL_UPDATED', $MODULE)} </th>
                    <th> {vtranslate('LBL_DELETED', $MODULE)} </th>
                    <th> {vtranslate('LBL_SKIPPED', $MODULE)} </th>
                </tr>
            </thead>
            <tbody>
                {foreach item=LOG from=$DATA}
                    <tr>
                        <td>{$LOG['sync_date']} </td>
                        <td>{$LOG['sync_time']} </td>
                        <td>{vtranslate($LOG['module'], $LOG['module'])}</td>
                        <td> <a class="{if $LOG['vt_create_count'] > 0} syncLogDetail extensionLink {/if}" data-type="vt_create" data-id="{$LOG['id']}"> {$LOG['vt_create_count']} </a> </td>
                        <td> <a class="{if $LOG['vt_update_count'] > 0} syncLogDetail extensionLink {/if}" data-type="vt_update" data-id="{$LOG['id']}"> {$LOG['vt_update_count']} </a> </td>
                        <td> <a class="{if $LOG['vt_delete_count'] > 0} syncLogDetail extensionError {/if}" data-type="vt_delete" data-id="{$LOG['id']}"> {$LOG['vt_delete_count']} </a> </td>
                        <td> <a class="{if $LOG['vt_skip_count'] > 0} syncLogDetail extensionError {/if}" data-type="vt_skip" data-id="{$LOG['id']}"> {$LOG['vt_skip_count']} </a></td>
                        <td> <a class="{if $LOG['app_create_count'] > 0} syncLogDetail extensionLink {/if}" data-type="app_create" data-id="{$LOG['id']}"> {$LOG['app_create_count']} </a> </td>
                        <td> <a class="{if $LOG['app_update_count'] > 0} syncLogDetail extensionLink {/if}" data-type="app_update" data-id="{$LOG['id']}"> {$LOG['app_update_count']} </a> </td>
                        <td> <a class="{if $LOG['app_delete_count'] > 0} syncLogDetail extensionError  {/if}" data-type="app_delete" data-id="{$LOG['id']}"> {$LOG['app_delete_count']} </a> </td>
                        <td> <a class="{if $LOG['app_skip_count'] > 0} syncLogDetail extensionError {/if}" data-type="app_skip" data-id="{$LOG['id']}"> {$LOG['app_skip_count']} </a></td>
                    </tr>
                {/foreach}
                {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                    <tr class="emptyRecordsDiv">
                        {assign var=COLSPAN_WIDTH value=12}
                        <td colspan="{$COLSPAN_WIDTH}">
                            <div class="emptyRecordsContent">
                                <center> 
                                    {vtranslate('LBL_NO')} {vtranslate('LBL_SYNC_LOG', $MODULE)} {vtranslate('LBL_FOUND')}. 
                                    {if $IS_SYNC_READY}
                                        <a href="#" class="syncNow"> <span class="blueColor"> {vtranslate('LBL_SYNC_NOW', $MODULE)} </span></a>
                                    {else}
                                        <a href="#" data-url="{$MODULE_MODEL->getExtensionSettingsUrl($SOURCE_MODULE)}" class="settingsPage"> <span class="blueColor"> {vtranslate('LBL_CONFIGURE', $MODULE)} {vtranslate('LBL_SYNC_SETTINGS', $MODULE)} </span></a>
                                    {/if}
                                </center>
                            </div>
                        </td>
                    </tr>
                {/if}
            </tbody>
        </table>
        </div>
    </div>
    <div id="scroller_wrapper" class="bottom-fixed-scroll">
        <div id="scroller" class="scroller-div"></div>
    </div>