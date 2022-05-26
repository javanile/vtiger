{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Vtiger/views/MergeRecord.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="fc-overlay-modal">
    <form class="form-horizontal" name="massMerge" method="post" action="index.php">
        <div class="overlayHeader">
            {assign var=TITLE value="{{vtranslate('LBL_MERGE_RECORDS_IN', $MODULE)}|cat:' > '|cat:{vtranslate($MODULE,$MODULE)}}"}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
        </div>
        <div class="overlayBody">
            <div class="container-fluid modal-body">
                <div class="row">
                    <div class="col-lg-12">
                            <input type="hidden" name=module value="{$MODULE}" />
                            <input type="hidden" name="action" value="ProcessDuplicates" />
                            <input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />
                            <div class="well well-sm" style="margin-bottom:8px">
                                {vtranslate('LBL_MERGE_RECORDS_DESCRIPTION',$MODULE)}
                            </div>
                            <div class="datacontent">
                                <table class="table table-bordered">
                                    <thead class='listViewHeaders'>
                                    <th>
                                        {vtranslate('LBL_FIELDS', $MODULE)}
                                    </th>
                                    {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                        <th>
                                            <div class="checkbox">
                                                <label>
                                                <input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name="primaryRecord"/>
                                                &nbsp; {vtranslate('LBL_RECORD')} <a href="{$RECORD->getDetailViewUrl()}" target="_blank" style="color: #15c;">#{$RECORD->getId()}</a>
                                                </label>
                                            </div>
                                        </th>
                                    {/foreach}
                                    </thead>
                                    {foreach item=FIELD from=$FIELDS}
                                        {if $FIELD->isEditable()}
                                        <tr>
                                            <td>
                                                {vtranslate($FIELD->get('label'), $MODULE)}
                                            </td>
                                            {foreach item=RECORD from=$RECORDMODELS name=recordList}
                                                <td>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input {if $smarty.foreach.recordList.index eq 0}checked="checked"{/if} type=radio name="{$FIELD->getName()}"
                                                            data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}"/>
                                                             &nbsp; {$RECORD->getDisplayValue($FIELD->getName())}
                                                        </label>
                                                   </div>
                                                </td>
                                            {/foreach}
                                        </tr>
                                        {/if}
                                    {/foreach}
                                </table>
                             </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overlayFooter">
            {assign var=BUTTON_NAME value=vtranslate('LBL_MERGE',$MODULE)}
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </div>
    </form>
</div>
