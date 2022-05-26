{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Users/views/TransferOwnership.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer"'>
        {assign var=HEADER_TITLE value={vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
        <form class="form-horizontal" id="transferOwner" method="post">
            <input type="hidden" name="module" value="{$MODULE}">
            <input type="hidden" name="action" value="SaveAjax">
            <input type="hidden" name="mode" value="transferOwner">
            <div name='massEditContent'>
                <div class="modal-body">
                    <div class="form-group">
                       <label class="control-label fieldLabel col-sm-5">{vtranslate('LBL_TRANSFER_OWNERSHIP_TO_USER', $MODULE)}</label>
                       <div class="controls fieldValue col-xs-6">
                            <select class="select2" name="record">
                                {foreach from=$USERS_MODEL key=USER_ID item=USER_MODEL}
                                    <option value="{$USER_ID}">{$USER_MODEL->getDisplayName()}</option>
                                {/foreach}
                            </select>
                       </div>
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
        </form>
    </div>
            </div>     
{/strip}

