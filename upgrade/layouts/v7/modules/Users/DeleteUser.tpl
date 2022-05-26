{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Users/views/DeleteUser.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer">
        {assign var=HEADER_TITLE value={vtranslate('Transfer records to user', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
        <div class="modal-content">
        <form class="form-horizontal" id="deleteUser" name="deleteUser" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="userid" value="{$USERID}" />
            <div name='massEditContent'>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label fieldLabel col-sm-5">{vtranslate('User to be deleted', $MODULE)}</label>
                        <label class="control fieldValue col-sm-5" style="padding-top: 6PX;">{$DELETE_USER_NAME}</label>
                    </div>
                        
                    <div class="form-group">
                       <label class="control-label fieldLabel col-sm-5">{vtranslate('Transfer records to user', $MODULE)}</label>
                       <div class="controls fieldValue col-xs-6">
                           <select class="select2 {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="tranfer_owner_id" data-validation-engine="validate[ required]" >
                               {foreach item=USER_MODEL key=USER_ID from=$USER_LIST}
                                   <option value="{$USER_ID}" >{$USER_MODEL->getName()}</option>
                               {/foreach}
                           </select>
                       </div>
                    </div>
                                
                    {if !$PERMANENT}        
                        <div class="form-group">
                            <label class="control-label fieldLabel col-sm-4"></label>
                                <div class="controls fieldValue col-sm-8">
                                    <input type="checkbox" name="deleteUserPermanent" value="1" >
                                    &nbsp;&nbsp;{vtranslate('LBL_DELETE_USER_PERMANENTLY',$MODULE)}
                                    &nbsp;&nbsp;<i class="fa fa-question-circle" data-toggle="tooltip"  data-placement="right" title="{vtranslate('LBL_DELETE_USER_PERMANENTLY_INFO',$MODULE)}"></i>
                                </div>
                        </div>
                    {/if}
                </div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
        </form>
    </div>
            </div>     
{/strip}

