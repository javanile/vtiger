{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Reports/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
        <div class="reportsDetailHeader">
            <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
            <input type="hidden" id="reportLimit" value="{$REPORT_LIMIT}" />
            <form id="detailView" onSubmit="return false;">
                <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
                {include file="DetailViewActions.tpl"|vtemplate_path:$MODULE}
                <br>
                <div class=''>
                    {assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}
                    <button class="btn btn-default" name="modify_condition" data-val="{$filterConditionNotExists}">
                        <strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
                        <i class="fa {if $filterConditionNotExists eq true} fa-chevron-right {else} fa-chevron-down {/if}"></i>
                    </button>
                </div>
                <br>
                <div id="filterContainer" class="filterElements filterConditionsDiv {if $filterConditionNotExists eq true} hide {/if}">
                    <input type="hidden" id="recordId" value="{$RECORD_ID}" />
                    {assign var=RECORD_STRUCTURE value=array()}
                    {assign var=PRIMARY_MODULE_LABEL value=vtranslate($PRIMARY_MODULE, $PRIMARY_MODULE)}
                    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRIMARY_MODULE_RECORD_STRUCTURE}
                        {assign var=PRIMARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $PRIMARY_MODULE)}
                        {assign var=key value="$PRIMARY_MODULE_LABEL $PRIMARY_MODULE_BLOCK_LABEL"}
                        {if $LINEITEM_FIELD_IN_CALCULATION eq false && $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
                            {* dont show the line item fields block when Inventory fields are selected for calculations *}
                        {else}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/if}
                    {/foreach}
                    {foreach key=MODULE_LABEL item=SECONDARY_MODULE_RECORD_STRUCTURE from=$SECONDARY_MODULE_RECORD_STRUCTURES}
                        {assign var=SECONDARY_MODULE_LABEL value=vtranslate($MODULE_LABEL, $MODULE_LABEL)}
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SECONDARY_MODULE_RECORD_STRUCTURE}
                            {assign var=SECONDARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $MODULE_LABEL)}
                            {assign var=key value="$SECONDARY_MODULE_LABEL $SECONDARY_MODULE_BLOCK_LABEL"}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/foreach}
                    {/foreach}
                    <div class="filterConditionContainer">
                        {include file='AdvanceFilter.tpl'|@vtemplate_path RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
                    </div>
                    <div class="row">
                        <div class="textAlignCenter hide reportActionButtons">
                            <button class="btn btn-default generateReport" data-mode="generate" value="{vtranslate('LBL_GENERATE_NOW',$MODULE)}"/>
                                <strong>{vtranslate('LBL_GENERATE_NOW',$MODULE)}</strong>
                            </button>&nbsp;
                            {if $REPORT_MODEL->isEditableBySharing()}
                                <button class="btn btn-success generateReport" data-mode="save" value="{vtranslate('LBL_SAVE',$MODULE)}"/>
                                    <strong>{vtranslate('LBL_SAVE',$MODULE)}</strong>
                                </button>
                            {/if}
                        </div>
                    </div>
                    <br>
                </div>
            </form>
        </div>
        <div id="reportContentsDiv">
{/strip}