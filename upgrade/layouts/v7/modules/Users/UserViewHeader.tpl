{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Users/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
<div class="detailViewContainer">
    <div class="col-sm-12 col-xs-12">
        <div class="detailViewTitle" id="userPageHeader">
            <div class = "row">
                <div class="col-md-5">
                    <div class="col-md-5 recordImage" style="height: 50px;width: 50px;">
                        {assign var=NOIMAGE value=0}
                        {foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
                            {if !empty($IMAGE_INFO.path) && !empty($IMAGE_INFO.orgname)}
                                <img height="100%" width="100%"src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}">
                            {else}
                                {assign var=NOIMAGE value=1}
                            {/if}
                        {/foreach}
                        {if $NOIMAGE eq 1}
                            <div class="name">
                                <span style="font-size:24px;">
                                    <strong> {$RECORD->getName()|substr:0:2} </strong>
                                </span>
                            </div>
                        {/if}
                    </div>
                    <span class="font-x-x-large" style="margin:5px;font-size:24px">
                        {$RECORD->getName()}
                    </span>
                </div>
                <div class="pull-right col-md-7 detailViewButtoncontainer">
                    <div class="btn-group pull-right">
                        {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                            <button class="btn btn-default {if $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_EDIT'}{/if}" id="{$MODULE}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                                    {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                        onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
                                    {else}
                                        onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
                                    {/if}>
                               {vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE)}
                            </button>
                        {/foreach}
                        {if $DETAILVIEW_LINKS['DETAILVIEW']|@count gt 0}
                            <button class="btn btn-default" data-toggle="dropdown" href="javascript:void(0);">
                                {vtranslate('LBL_MORE', $MODULE)}&nbsp;<i class="caret"></i>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                {foreach item=DETAIL_VIEW_LINK from=$DETAILVIEW_LINKS['DETAILVIEW']}
                                    {if $DETAIL_VIEW_LINK->getLabel() eq "Delete"}
                                        {if $CURRENT_USER_MODEL->isAdminUser() && $CURRENT_USER_MODEL->getId() neq $RECORD->getId()}
                                            <li id="{$MODULE}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                            <a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE)}</a>
                                        {/if}
                                    {else}
                                        <li id="{$MODULE}_detailView_moreAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_LINK->getLabel())}">
                                            <a href={$DETAIL_VIEW_LINK->getUrl()} >{vtranslate($DETAIL_VIEW_LINK->getLabel(), $MODULE)}</a>
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
        <hr/>
        <div class="detailview-content userPreferences container-fluid">
            {assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
            <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
            <div class="details row">
{/strip}