{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Rss/views/ViewTypes.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="sidebar-menu quickWidgetContainer">
    {assign var=val value=1}
    {foreach item=SIDEBARWIDGET key=index from=$QUICK_LINKS['SIDEBARWIDGET']}
    <div class="module-filters">    
        <div class="sidebar-container lists-menu-container">
            <div class="sidebar-header clearfix">
                <h5 class="pull-left">{vtranslate($SIDEBARWIDGET->getLabel(), $MODULE)}</h5>
                <button class="btn btn-default pull-right sidebar-btn rssAddButton" title="{vtranslate('LBL_FEED_SOURCE',$MODULE)}">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
            <hr>
            <div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
                <div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
                    <div class="mCSB_container" style="position:relative; top:0; left:0;">
                        <div class="list-menu-content">
                            <ul class="lists-menu widgetContainer" data-url="{$SIDEBARWIDGET->getUrl()}">
                                {assign var="RSS_MODULE_MODEL" value=Vtiger_Module_Model::getInstance($MODULE)}
                                {assign  var="RSS_SOURCES" value=$RSS_MODULE_MODEL->getRssSources()}
                                {foreach item=recordsModel from=$RSS_SOURCES}
                                    <li>
                                        <a href="#" class="rssLink filter-name" data-id={$recordsModel->getId()} data-url="{$recordsModel->get('rssurl')}" title="{decode_html($recordsModel->getName())}">{decode_html($recordsModel->getName())}</a>
                                    </li>
                                    {foreachelse}
                                        <li class="noRssFeeds" style="text-align:center">{vtranslate('LBL_NO_RECORDS', $MODULE)}
                                        </li>
                                    {/foreach}

                             </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
     {/foreach}
</div>
</div>

