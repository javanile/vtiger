{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Roles/views/DeleteAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer">
        {assign var=HEADER_TITLE value={vtranslate('LBL_DELETE_ROLE', $QUALIFIED_MODULE)}|cat:" - "|cat:{$RECORD_MODEL->getName()}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
            <form class="form-horizontal" id="roleDeleteForm" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="action" value="Delete" />
                <input type="hidden" name="record" id="record" value="{$RECORD_MODEL->getId()}" />
                <div name='massEditContent'>
                    <div class="modal-body">
                            <div class="col-sm-5"><div class="control-label fieldLabel pull-right ">{vtranslate('LBL_TRANSFER_TO_OTHER_ROLE',$QUALIFIED_MODULE)}
                                &nbsp;<span class="redColor">*</span></div></div>
                            <div class="input-group fieldValue col-xs-6">
                                <input id="transfer_record" name="transfer_record" type="hidden" value="" class="sourceField" data-rule-required="true">
                                
                                <input id="transfer_record_display" data-rule-required='true' name="transfer_record_display" type="text" class="inputElement" value="">
                                <a href="#" id="clearRole" class="clearReferenceSelection hide cursorPointer" name="clearToEmailField"> X </a>
                                <span class="input-group-addon cursorPointer relatedPopup" data-field="transfer_record" data-action="popup" data-url="{$RECORD_MODEL->getPopupWindowUrl()}&type=Transfer">
                                    <i class="fa fa-search"></i>
                                </span>
                            </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>     
{/strip}



