{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Calendar/views/RecurringDeleteCheck.php *}

{strip}
<!--Confirmation modal for updating Recurring Events-->
{assign var=MODULE value="Calendar"}
<div class="modal-dialog modelContainer modal-content" style='min-width:350px;'>
    {assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_RECURRING_EVENT', $MODULE)}}
    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
    <div class="modal-body">
        <div class="container-fluid">
            <div class="row" style="padding: 1%;padding-left: 3%;">{vtranslate('LBL_DELETE_RECURRING_EVENTS_INFO', $MODULE)}</div>
            <div class="row" style="padding: 1%;">
                <span class="col-sm-12">
                    <span class="col-sm-4">
                        <button class="btn btn-default onlyThisEvent" style="width : 150px">{vtranslate('LBL_ONLY_THIS_EVENT', $MODULE)}</button>
                    </span>
                    <span class="col-sm-8">{vtranslate('LBL_ONLY_THIS_EVENT_DELETE_INFO', $MODULE)}</span>
                </span>
            </div>
            <div class="row" style="padding: 1%;">
                <span class="col-sm-12">
                    <span class="col-sm-4">
                        <button class="btn btn-default futureEvents" style="width : 150px">{vtranslate('LBL_FUTURE_EVENTS', $MODULE)}</button>
                    </span>
                    <span class="col-sm-8">{vtranslate('LBL_FUTURE_EVENTS_DELETE_INFO', $MODULE)}</span>
                </span>
            </div>
            <div class="row" style="padding: 1%;">
                <span class="col-sm-12">
                    <span class="col-sm-4">
                        <button class="btn btn-default allEvents" style="width : 150px">{vtranslate('LBL_ALL_EVENTS', $MODULE)}</button>
                    </span>
                    <span class="col-sm-8">{vtranslate('LBL_ALL_EVENTS_DELETE_INFO', $MODULE)}</span>
                </span>
            </div>
        </div>
    </div>
</div>
<!--Confirmation modal for updating Recurring Events-->
{/strip}