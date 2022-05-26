{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Picklist/views/IndexAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog">
        <div class='modal-content'>
            {assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_PICKLIST_ITEMS', $QUALIFIED_MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form id="deleteItemForm" class="form-horizontal" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
                <input type="hidden" name="action" value="SaveAjax" />
                <input type="hidden" name="mode" value="remove" />
                <input type="hidden" name="picklistName" value="{$FIELD_MODEL->get('name')}" />
                <div class="modal-body tabbable">
                    <div class="form-group">
                        <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_ITEMS_TO_DELETE',$QUALIFIED_MODULE)}</div>
                        <div class="controls col-sm-4 col-xs-4">
                            <select class="select2 form-control" multiple="" id="deleteValue" name="delete_value[]" >
                                {foreach from=$SELECTED_PICKLISTFIELD_EDITABLE_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
                                    <option {if in_array($PICKLIST_VALUE,$FIELD_VALUES)} selected="" {/if} value="{$PICKLIST_VALUE_KEY}">{vtranslate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
                                {/foreach}
                            </select>
                            <input id="pickListValuesCount" type="hidden" value="{count($SELECTED_PICKLISTFIELD_EDITABLE_VALUES)+count($SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES)}"/>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_REPLACE_IT_WITH',$QUALIFIED_MODULE)}</div>
                        <div class="controls  col-sm-4 col-xs-4">
                            <select id="replaceValue" name="replace_value" class="select2 form-control" data-validation-engine="validate[required]">
                                {foreach from=$SELECTED_PICKLISTFIELD_EDITABLE_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
                                    {if !(in_array($PICKLIST_VALUE, $FIELD_VALUES))}
                                        <option value="{$PICKLIST_VALUE_KEY}">{vtranslate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
                                    {/if}
                                {/foreach}
                                {foreach from=$SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES key=PICKLIST_VALUE_KEY item=PICKLIST_VALUE}
                                    {if !(in_array($PICKLIST_VALUE, $FIELD_VALUES))}
                                        <option value="{$PICKLIST_VALUE_KEY}">{vtranslate($PICKLIST_VALUE,$SOURCE_MODULE)}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {if $SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES}
                        <br>
                        <div class="form-group">
                            <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_NON_EDITABLE_PICKLIST_VALUES',$QUALIFIED_MODULE)}</div>
                            <div class="controls col-sm-4 col-xs-4 nonEditableValuesDiv">
                                <ul class="nonEditablePicklistValues" style="list-style-type: none;">
                                {foreach from=$SELECTED_PICKLISTFIELD_NON_EDITABLE_VALUES key=NON_EDITABLE_VALUE_KEY item=NON_EDITABLE_VALUE}
                                    <li>{vtranslate($NON_EDITABLE_VALUE,$SOURCE_MODULE)}</li>
                                {/foreach}
                                </ul>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="modal-footer">
                    <center>
                        <button class="btn btn-danger" type="submit" name="saveButton"><strong>{vtranslate('LBL_DELETE', $MODULE)}</strong></button>
                        <a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </center>
                </div>
            </form>
        </div>
    </div>
{/strip}
