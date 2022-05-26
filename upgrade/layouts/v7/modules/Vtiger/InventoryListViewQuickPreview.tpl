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
<div class = "quickPreview" style = "height:100%; overflow-y:auto">
    <input type="hidden" id = "nextRecordId" value ="{$NEXT_RECORD_ID}">
    <input type="hidden" id = "previousRecordId" value ="{$PREVIOUS_RECORD_ID}">

    <div class='{if $DETAIL_PREVIEW eq 'true'}modal-dialog modal-lg{else}fc-overlay-modal{/if}}'>
        <div class = "modal-content">
            {if $DETAIL_PREVIEW eq 'true'}
                {assign var=TITLE value={$RECORD->getName()}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
            {else}
                <div class="overlayHeader">
                    {assign var=TITLE value={$RECORD->getName()}}
                    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                </div>
            {/if}
            <form class="form-horizontal" method="POST">
                <input type="hidden" name="module" id= "targetModule" value="{$MODULE}"/>
                <input type="hidden" name="sourceRecords" id = "sourceRecords" value='{$SOURCE_RECORDS}'/>
                <input type="hidden" name="action" value="{$ACTION_HANDLER}"/>
                <input type="hidden" name="sourceModuleName" id = "sourceModuleName" value="{$SOURCE_MODULE}" />

                <div class='modal-body' style="padding:0 0 0 0">
                    <div class = "col-lg-12 col-md-12 col-sm-12" style = "margin-top:10px">
                        <div class = "form-group">
                            <div class = "col-lg-4 col-md-4 col-sm-4" style = "text-align:right">
                                <h4><label for="templateId">{vtranslate('Change Template',$MODULE)}</label></h4>
                            </div>
                            <div class="selectContainer col-lg-6 col-md-6 col-sm-6">
                                <select id="fieldList" name = "templateId" id="templateId" class="select2">
                                    {foreach from=$RELATED_TPLS item=TPL} 
                                        {if $TPL->isDefault()}
                                            <option value="{$TPL->get('id')}">
                                                {vtranslate($TPL->get('name'),$MODULE)}
                                            </option>
                                        {/if}
                                    {/foreach}
                                    {foreach from=$RELATED_TPLS item=TPL} 
                                        {if !$TPL->isDefault()}
                                            <option value="{$TPL->get('id')}">
                                                {vtranslate($TPL->get('name'),$MODULE)}
                                            </option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-lg-2 btn-group pull-right col-lg-push-1">
                                {if $DETAIL_ENABLED}
                                    <button class="btn btn-success btn-sm" style ="margin-right:10px">{vtranslate('LBL_DETAILS',$MODULE)}</button>
                                {/if}
                                {if $NAVIGATION}
                                    <button class="btn btn-default btn-sm" id="quickPreviewPreviousRecordButton" data-id = '{$PREVIOUS_RECORD_ID}' {if empty($PREVIOUS_RECORD_ID)} disabled="disabled"{else} onclick="return false"{/if} >
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button class="btn btn-default btn-sm" id="quickPreviewNextRecordButton" data-id = '{$NEXT_RECORD_ID}' {if empty($NEXT_RECORD_ID)} disabled="disabled"{else} onclick="return false"{/if}>
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                {/if}
                            </div>
                        </div>
                    </div>

                    <div id = "pdfViewer">
                        {include file='PDFViewer.tpl'|@vtemplate_path:$SOURCE_MODULE PDF_PATH=$PDF_PATH}
                    </div>
                </div>
                <div class="modal-footer {if $DETAIL_PREVIEW eq 'false'}overlayFooter{/if}">
                    {if $DETAIL_PREVIEW eq 'false'}
                        <footer class='pull-left'>
                        {/if}
                        {if $INCLUDE_BROWSER_SUPPORT_MSG}
                            <p class="redColor">{vtranslate('LBL_NOTE_ABOUT_PRINT',$MODULE)}</p>
                        {/if}
                        <button class="btn btn-default" name="editAndExportBtn" id="editAndExportBtn" type="button">
                            <strong>{vtranslate('LBL_CUSTOMIZE',$MODULE)}</strong>
                        </button>
                        <button class="btn btn-success" name="exportPDF" id="exportPDF" type="submit">
                            <strong>{vtranslate('LBL_SAVE_AS_PDF',$MODULE)}</strong>
                        </button>
                        <button class="btn btn-success" name="emailWithPDF" id="emailWithPDF" type="button">
                            <strong>{vtranslate('LBL_EMAIL_WITH_PDF',$MODULE)}</strong>
                        </button>
                        <button class="btn btn-success" id="listTplsPrintBtn" type="button">
                            <strong>{vtranslate('LBL_PRINT',$MODULE)}</strong>
                        </button>
                        {if $DETAIL_PREVIEW eq 'false'}
                        </footer>
                    {/if}
                </div>
            </form>
        </div>
    </div>
</div>

