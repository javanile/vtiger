{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="filePreview container-fluid">
                <div class="modal-header row">
                    <div class="filename {if $FILE_PREVIEW_NOT_SUPPORTED neq 'yes'} col-lg-8 {else} col-lg-11 {/if}">
                        <h3 style="margin-top:0px;"><b>{$FILE_NAME}</b></h3>
                    </div>
                    {if $FILE_PREVIEW_NOT_SUPPORTED neq 'yes'}
                        <div class="col-lg-3">
                            <a class="btn btn-default btn-small pull-right" href="{$DOWNLOAD_URL}">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}</a>
                        </div>
                    {/if}
                    <div class="col-lg-1">
                        <button data-dismiss="modal" class="close pull-right" title="close"> 
                            <span aria-hidden="true" class='fa fa-close'></span></button>
                    </div>
                </div>
                <div class="modal-body row" style="height:550px;">
                    {if $FILE_PREVIEW_NOT_SUPPORTED eq 'yes'}
                        <div class="well" style="height:100%;">
                            <center>
                                <b>{vtranslate('LBL_PREVIEW_NOT_AVAILABLE',$MODULE_NAME)}</b>
                                <br><br><br>
                                <a class="btn btn-default btn-large" href="{$DOWNLOAD_URL}">{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}</a>
                                <br><br><br><br>
                                <div class='span11 offset1 alert-info' style="padding:10px">
                                    <span class='span offset1 alert-info'>
                                        <i class="icon-info-sign"></i>
                                        {vtranslate('LBL_PREVIEW_SUPPORTED_FILES',$MODULE_NAME)}
                                    </span>
                                </div>
                                <br>
                            </center>
                        </div>
                    {else}
                        {if $BASIC_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;">
                                <pre>
                                    {$FILE_CONTENTS}
                                </pre>
                            </div>
                        {else if $OPENDOCUMENT_FILE_TYPE eq 'yes'}
                            <iframe id="viewer" src="libraries/jquery/Viewer.js/#../../../{$DOWNLOAD_URL}" width="100%" height="100%" allowfullscreen webkitallowfullscreen></iframe>
                        {else if $PDF_FILE_TYPE eq 'yes'}
                            <iframe id='viewer' src="libraries/jquery/pdfjs/web/viewer.html?file={$SITE_URL}/{$FILE_PATH}" height="100%" width="100%"></iframe>
                        {else if $IMAGE_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;width:100%;float:left;background-image: url({$DOWNLOAD_URL});background-color: #EEEEEE;background-position: center 25%;background-repeat: no-repeat;display: block; background-size: contain;"></div>
                        {else if $AUDIO_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;width:100%;float:left;background-color: #EEEEEE;background-position: center 25%;background-repeat: no-repeat;display: block;text-align: center;">
                                <div style="display: inline-block;margin-top : 10%;">
                                    <audio controls>
                                        <source src="{$SITE_URL}/{$DOWNLOAD_URL}" type="{$FILE_TYPE}">
                                    </audio>
                                </div>
                            </div>
                        {else if $VIDEO_FILE_TYPE eq 'yes'}
                            <div style="overflow:auto;height:100%;">
                                <link href="libraries/jquery/video-js/video-js.css" rel="stylesheet">
                                <script src="libraries/jquery/video-js/video.js"></script>
                                <video class="video-js vjs-default-skin" controls preload="auto" {literal}data-setup="{'techOrder': ['flash', 'html5']}" {/literal}width="100%" height="100%">
                                    <source src="{$SITE_URL}/{$DOWNLOAD_URL}" type='{$FILE_TYPE}' />
                                </video>
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/strip}
