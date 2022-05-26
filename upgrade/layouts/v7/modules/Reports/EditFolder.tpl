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
    <div id="addFolderContainer" class="modal-dialog" style='min-width:350px;'>
        <div class='modal-content'>
			{assign var=FOLDER_ID value=$FOLDER_MODEL->getId()}
			{assign var=FOLDER_NAME value={Vtiger_Util_Helper::tosafeHTML(vtranslate($FOLDER_MODEL->getName(), $MODULE))}}
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}}
			{if $FOLDER_ID}
				{assign var=HEADER_TITLE value="{vtranslate('LBL_EDIT_FOLDER', $MODULE)}: {$FOLDER_NAME}"}
			{/if}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal contentsBackground" id="addFolder" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="action" value="Folder" />
                <input type="hidden" name="mode" value="save" />
                <input type="hidden" name="folderid" value="{$FOLDER_MODEL->getId()}" />
                <div class="modal-body">
                    <div class="form-group">
                        <label for="foldername" class="col-sm-4 control-label">{vtranslate('LBL_FOLDER_NAME', $MODULE)}<span class="redColor">*</span></label>
                        <div class="col-sm-7">
                            <input id="foldername" name="foldername" class="form-control col-lg-12" data-rule-required="true" type="text" value="{$FOLDER_NAME}"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="col-sm-4 control-label">{vtranslate('LBL_FOLDER_DESCRIPTION', $MODULE)}</label>
                        <div class="col-sm-7">
                            <textarea name="description" class="form-control col-sm-12" rows="3" placeholder="{vtranslate('LBL_WRITE_YOUR_DESCRIPTION_HERE', $MODULE)}">{vtranslate($FOLDER_MODEL->getDescription(), $MODULE)}</textarea>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}