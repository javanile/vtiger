{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
   <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
{/if}
{include file='DetailViewBlockView.tpl'|@vtemplate_path:'Vtiger' RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
<div class="block block_LBL_INVITE_USER_BLOCK">
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
    {assign var="IS_HIDDEN" value=false}
    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}

    <div>
        <h4>{vtranslate('LBL_INVITE_USER_BLOCK',{$MODULE_NAME})}</h4>
    </div>
    <hr>

    <div class="blockData">
        <table class="table detailview-table no-border">
            <tbody>
                <tr>
                    <td class="fieldLabel {$WIDTHTYPE}">
                        <span class="muted">{vtranslate('LBL_INVITE_USERS', $MODULE_NAME)}</span>
                    </td>
                    <td class="fieldValue {$WIDTHTYPE}">
                        {foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
                            {if in_array($USER_ID,$INVITIES_SELECTED)}
                                {$USER_NAME} - {vtranslate($INVITEES_DETAILS[$USER_ID],$MODULE)}
                                <br>
                            {/if}
                        {/foreach}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
{/strip}