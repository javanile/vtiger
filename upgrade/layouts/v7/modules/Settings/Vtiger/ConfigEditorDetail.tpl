{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Vtiger/views/ConfigEditorDetail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div  class="detailViewContainer" id="ConfigEditorDetails">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
            {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
            <div class="contents ">
                <div class="clearfix">
                    <h4 class="pull-left">{vtranslate('LBL_CONFIG_EDITOR', $QUALIFIED_MODULE)}</h4>
                    <div class="btn-group pull-right">
                        <button class="btn btn-default editButton" data-url='{$MODEL->getEditViewUrl()}' type="button" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</button>
                    </div>
                </div>
                <hr>
                <br>
                <div class="detailViewInfo">
                    {assign var=FIELD_DATA value=$MODEL->getViewableData()}
                    {foreach key=FIELD_NAME item=FIELD_DETAILS from=$MODEL->getEditableFields()}
                        <div class="row form-group"><div class="col-lg-4 col-md-4 col-sm-4 fieldLabel"><label>{if $FIELD_NAME == 'upload_maxsize'}{if $FIELD_DATA[$FIELD_NAME] gt 5}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,$FIELD_DATA[$FIELD_NAME])}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE,5)}{/if}{else}{vtranslate($FIELD_DETAILS['label'], $QUALIFIED_MODULE)}{/if}</label></div>
                            <div  class="col-lg-8 col-md-8 col-sm-8 fieldValue break-word">
                                <div>
                                    {if $FIELD_NAME == 'default_module'}
                                        {vtranslate($FIELD_DATA[$FIELD_NAME], $FIELD_DATA[$FIELD_NAME])}
                                    {else if $FIELD_DETAILS['fieldType'] == 'checkbox'}
                                        {vtranslate($FIELD_DATA[$FIELD_NAME], $QUALIFIED_MODULE)}
                                        {if $FIELD_NAME == 'email_tracking'}
                                            <div class="input-info-addon"><a class="fa fa-question-circle" data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_PERSONAL_EMAIL_TRACKING_INFO',$QUALIFIED_MODULE)}"></a></div>
                                            {/if}
                                        {else if $FIELD_NAME == 'default_reply_to'}
                                            {vtranslate($FIELD_DATA[$FIELD_NAME])}
                                            <div class="input-info-addon"><a class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" title="{vtranslate('LBL_DEFAULT_REPLY_TO_INFO',$QUALIFIED_MODULE)}"></a></div>
                                        {else}
                                            {$FIELD_DATA[$FIELD_NAME]}
                                        {/if}
                                        {if $FIELD_NAME == 'upload_maxsize'}
                                        &nbsp;{vtranslate('LBL_MB', $QUALIFIED_MODULE)}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>    
            </div>
        </div>
    </div>
{/strip}
