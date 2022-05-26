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
<div class="modalContents">
    <div class="modal-dialog basicCreateView">
        <div class='modal-content'>
            <form name="addItemForm" class="form-horizontal" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}" />
                <input type="hidden" name="action" value="SaveAjax" />
                <input type="hidden" name="mode" value="add" />
                <input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}" />
                <input type="hidden" name="pickListValues" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SELECTED_PICKLISTFIELD_ALL_VALUES))}' />
                {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_ITEM_TO', $QUALIFIED_MODULE)}|cat:" "|cat:{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <div class="modal-body">
                    <div class="form-group">
                        <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_ITEM_VALUE',$QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></div>
                        <div class="controls col-sm-3 col-xs-3"><input style="min-width: 220px;" name="newValue" class="form-control select2" data-rule-required="true"/></div>
                    </div>
                    {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
                        <div class="form-group">	
                            <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</div>
                            <div class="controls col-sm-3 col-xs-3">
                                <select class="rolesList form-control" name="rolesSelected[]" multiple style="min-width: 220px" data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
                                    <option value="all" selected>{vtranslate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
                                    {foreach from=$ROLES_LIST item=ROLE}
                                        <option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
                                    {/foreach}
                                </select>	
                            </div>
                        </div>
                    {/if}
                    <div class="form-group">
                        <div class="control-label col-sm-3 col-xs-3">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</div>
                        <div class="controls col-sm-3 col-xs-3">
                            <input type="hidden" name="selectedColor" />
                            <div class="colorPicker">
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$qualifiedName}
            </form>
        </div>
    </div>
</div>
{/strip}