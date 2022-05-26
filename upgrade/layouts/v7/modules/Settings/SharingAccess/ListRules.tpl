{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/SharingAccess/views/IndexAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="ruleListContainer">
        <div class="title row">
            <div class="rulehead col-sm-6">
                <!-- Check if the module should the for module to get the translations-->
                <strong>{vtranslate('LBL_SHARING_RULE', $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_FOR', $MODULE)}&nbsp;
                        {if $FOR_MODULE == 'Accounts'}{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{vtranslate($FOR_MODULE, $MODULE)}{/if} :</strong>
            </div>
            <div class="col-sm-6">
                <div class="pull-right">
                    <button class="btn btn-sm btn-default addButton addCustomRule" type="button" data-url="{$MODULE_MODEL->getCreateRuleUrl()}">
                      <i class="fa fa-plus"></i> &nbsp;&nbsp;{vtranslate('LBL_ADD_CUSTOM_RULE', $QUALIFIED_MODULE)}
                    </button>
                </div>
            </div>
        </div>
        <hr>	
            <div class="contents">
                {if $RULE_MODEL_LIST}
                    <table class="table table-bordered table-condensed customRuleTable">
                        <thead>
                            <tr class="customRuleHeaders">
                                <th>{vtranslate('LBL_RULE_NO', $QUALIFIED_MODULE)}</th>
                                <!-- Check if the module should the for module to get the translations -->
                                <th>{if $FOR_MODULE == 'Accounts'}{vtranslate($FOR_MODULE, $QUALIFIED_MODULE)}{else}{vtranslate($FOR_MODULE, $MODULE)}{/if}
                                        &nbsp;{vtranslate('LBL_OF', $MODULE)}</th>
                                <th>{vtranslate('LBL_CAN_ACCESSED_BY', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</th>
                            </tr>
                        </thead>
                            <tbody>
                                {foreach item=RULE_MODEL key=RULE_ID from=$RULE_MODEL_LIST name="customRuleIterator"}
                                    <tr class="customRuleEntries">
                                        <td class="sequenceNumber">
                                            {$smarty.foreach.customRuleIterator.index + 1}
                                        </td>
                                        <td>
                                            <a href="{$RULE_MODEL->getSourceDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getSourceMemberName(), $QUALIFIED_MODULE)}::{$RULE_MODEL->getSourceMember()->getName()}</a>
                                        </td>
                                        <td>
                                            <a href="{$RULE_MODEL->getTargetDetailViewUrl()}">{vtranslate('SINGLE_'|cat:$RULE_MODEL->getTargetMemberName(), $QUALIFIED_MODULE)}::{$RULE_MODEL->getTargetMember()->getName()}</a>
                                        </td>
                                        <td>
                                            {if $RULE_MODEL->isReadOnly()}
                                                {vtranslate('Read Only', $QUALIFIED_MODULE)}
                                            {else}
                                                {vtranslate('Read Write', $QUALIFIED_MODULE)}
                                            {/if}

                                            <div class="table-actions pull-right">
                                                <span><a href="javascript:void(0);" class="edit" data-url="{$RULE_MODEL->getEditViewUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a></span>
                                                &nbsp;<span><a href="javascript:void(0);" class="delete" data-url="{$RULE_MODEL->getDeleteActionUrl()}"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa fa-trash"></i></a></span>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                    </table>
                    <div class="recordDetails hide">
                        <p class="textAlignCenter">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
                    </div>
                {else}
                    <div class="recordDetails">
                        <p class="textAlignCenter">{vtranslate('LBL_CUSTOM_ACCESS_MESG', $QUALIFIED_MODULE)}.<!--<a href="">{vtranslate('LBL_CLICK_HERE', $QUALIFIED_MODULE)}</a>&nbsp;{vtranslate('LBL_CREATE_RULE_MESG', $QUALIFIED_MODULE)}--></p>
                    </div>
                {/if}
            </div>
	</div>
{/strip}
