{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/SharingAccess/views/Index.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}

{strip}
<div class="listViewPageDiv " id="sharingAccessContainer">
    <div class="col-sm-12 col-xs-12">
        <form name="EditSharingAccess" action="index.php" method="post" class="form-horizontal" id="EditSharingAccess">
            <input type="hidden" name="module" value="SharingAccess" />
            <input type="hidden" name="action" value="SaveAjax" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" class="dependentModules" value='{ZEND_JSON::encode($DEPENDENT_MODULES)}' />
            <br>
            <div class="contents">
                <table class="table table-bordered table-condensed sharingAccessDetails marginBottom50px">
                    <colgroup>
                        <col width="20%">
                        <col width="15%">
                        <col width="15%">
                        <col width="20%">
                        <col width="10%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="blockHeader">
                            <th>
                                {vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}
                            </th>
                            {foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
                                <th>
                                    {$ACTION_MODEL->getName()|vtranslate:$QUALIFIED_MODULE}
                                </th>
                            {/foreach}
                            <th nowrap="nowrap">{'LBL_ADVANCED_SHARING_RULES'|vtranslate:$QUALIFIED_MODULE}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-module-name="Calendar">
                            <td>{'SINGLE_Calendar'|vtranslate:'Calendar'}</td>
                            <td class="">
                                <center><div><input type="radio" disabled="disabled" /></div></center>
                            </td>
                            <td class="">
                                <center><div><input type="radio" disabled="disabled" /></div></center>
                            </td>
                            <td class="">
                                <center><div><input type="radio" disabled="disabled" /></div></center>
                            </td>
                            <td class="">
                                <center><div><input type="radio" checked="true" disabled="disabled" /></div></center>
                            </td>
                            <td>
                                <div class="row">
                                    <span class="col-sm-4">&nbsp;</span>
                                    <span class="col-sm-4">
                                        <button type="button" class="btn btn-sm btn-default vtButton arrowDown row-fluid" disabled="disabled" style="padding-right: 20px; padding-left: 20px;">
                                            <i class="fa fa-chevron-down"></i>
                                        </button>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                            <tr data-module-name="{$MODULE_MODEL->get('name')}">
                                <td>
                                    {$MODULE_MODEL->get('label')|vtranslate:$MODULE_MODEL->getName()}
                                </td>
                                {foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
                                <td class="">
                                    {if $ACTION_MODEL->isModuleEnabled($MODULE_MODEL)}
                                    <center>
                                        <div><input type="radio" name="permissions[{$TABID}]" data-action-state="{$ACTION_MODEL->getName()}" value="{$ACTION_ID}"{if $MODULE_MODEL->getPermissionValue() eq $ACTION_ID}checked="true"{/if}></div>
                                    </center>
                                    {/if}
                                </td>
                                {/foreach}
                                <td class="triggerCustomSharingAccess">
                                    <div class="row">
                                        <span class="col-sm-4">&nbsp;</span>
                                        <span class="col-sm-4">
                                            <button type="button" class="btn btn-sm btn-default vtButton" data-handlerfor="fields" data-togglehandler="{$TABID}-rules" style="padding-right: 20px; padding-left: 20px;">
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div class='modal-overlay-footer clearfix saveSharingAccess hide'>
                <div class="row clearfix">
                    <div class=' textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                        <button class="btn btn-success saveButton" name="saveButton" type="submit">{vtranslate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
{/strip}
