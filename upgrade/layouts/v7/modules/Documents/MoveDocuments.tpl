{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Documents/views/MoveDocuments.php *}
{strip}
    <div class="modal-dialog modelContainer modal-content">
        {assign var=HEADER_TITLE value={vtranslate('LBL_SELECT_A_FOLDER_TO_MOVE', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal contentsBackground" id="moveDocuments" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="MoveDocuments" />
			<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)} />
			<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)} />
			<input type="hidden" name="viewname" value="{$VIEWNAME}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="folder_id" value="{$FOLDER_ID}" />
            <input type="hidden" name="folder_value" value="{$FOLDER_VALUE}" />
            <input type="hidden" name="folderid" value="">
			<div class="modal-body">
				<div class="container-fluid">
                    <div class="row">
                        <span class="col-md-6">
                            <div class="input-group">
                            <input id="searchFolders" class="form-control" type="text" placeholder="{vtranslate('LBL_SEARCH', $MODULE)}">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            </div>
                        </span>
                    </div>
                    <div id="foldersList" class="row" style="font-size: 120%;padding:1%;margin-top: 1%;">
                        <span class="col-md-12">
                            {foreach item=FOLDER from=$FOLDERS}
                                <div class="folder cursorPointer" data-folder-id="{$FOLDER->getId()}" style="padding:1%;">
                                    <i class="fa fa-folder"></i>&nbsp;
                                    <span class="foldername">{$FOLDER->getName()}</span>
                                </div>
                            {/foreach}
                        </span>
					</div>
                    
				</div>
			</div>
            {assign var=BUTTON_NAME value={vtranslate('LBL_MOVE', $MODULE)}}
            {assign var=BUTTON_ID value="js-move-documents"}
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}
