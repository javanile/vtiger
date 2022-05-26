{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<div id="detailviewhtml">
    <div class='fc-overlay-modal modal-content' style="height:100vh;">
        <div class='overlayHeader'>
            {assign var="TITLE" value={vtranslate($TYPE, $MODULE, vtranslate($MODULE, $MODULE))}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
        </div>

        <div class='modal-body'>
            <div class="row">
                <div class="col-sm-8 col-xs-8"></div>
                <div class="col-sm-4 col-xs-4">
                    <a id="downloadCsv" href="index.php?module={$SOURCE_MODULE}&view=ExportExtensionLog&logid={$LOG_ID}&type={$TYPE}" type="button" class="btn addButton btn-default downloadCsv pull-right">
                        <span class="fa fa-download" aria-hidden="true"></span> {vtranslate('LBL_DOWNLOAD_AS_CSV', $MODULE)}
                    </a>
                </div>
            </div>
            <br>
            <div class='datacontent' style="max-height: 450px">
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th> {vtranslate('LBL_SOURCE_MODULE' ,$MODULE)} </th>
                                    <th> {vtranslate('LBL_RECORD_NAME' ,$MODULE)} </th>
                                        {if $TYPE eq 'vt_skip' or $TYPE eq 'app_skip'}
                                        <th class="remove"> {vtranslate('LBL_REASON' ,$MODULE)} </th>
                                        {/if}
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$DATA item=LOG}
                                    {if $TYPE neq 'vt_delete' and $TYPE neq 'app_delete'}
                                        {assign var=RECORD_LINK value=$LOG['link']}
                                    {/if}
                                    <tr>
                                        <td> {$LOG['module']} </td>
                                        <td>
                                            {if !empty($RECORD_LINK)}
                                                <a class="extensionLink" href="{$RECORD_LINK}" target="_blank">{$LOG['name']}</a>
                                            {else}
                                                {$LOG['name']}
                                            {/if}
                                        </td>
                                        {if $LOG['error']}
                                            <td> {$LOG['error']} </td>
                                        {/if}
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>