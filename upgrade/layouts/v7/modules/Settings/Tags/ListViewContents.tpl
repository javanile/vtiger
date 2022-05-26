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
    {include file="ListViewContents.tpl"|vtemplate_path:'Settings:Vtiger'}

    <div id="editTagContainer" class="hide modal-dialog modelContainer">
        <input type="hidden" name="id" value="" />
        {assign var="HEADER_TITLE" value={vtranslate('LBL_EDIT_TAG', $QUALIFIED_MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
            <div class="editTagContents col-lg-12 modal-body">
                <div class='col-lg-4'></div>
                <div class='col-lg-8'>
                    <input type="text" name="tagName" class='inputElement' value=""/>
                    <div class="checkbox">
                        <label>
                            <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                            <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" style="vertical-align: text-top;"/>
                            &nbsp; {vtranslate('LBL_SHARE_TAGS',$MODULE)}
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer col-lg-12">
                <center>
                    <button {if $BUTTON_ID neq null} id="{$BUTTON_ID}" {/if} class="btn btn-success saveTag" type="submit" name="saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                    <a href="#" class="cancelLink cancelSaveTag" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                </center>
            </div>
        </div>
    </div>
{/strip}