{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
<div class="module-action-bar clearfix">
    <div class="module-action-content clearfix row">
        <div class="col-lg-4 col-md-4 col-sm-4">
                <h3 class="module-title pull-left">&nbsp;{vtranslate($MODULE, $MODULE)}&nbsp;</h3>
            <div>
                <p class="current-filter-name pull-left"><span class="fa fa-chevron-right" aria-hidden="true"></span>&nbsp;{$VIEW}&nbsp;</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div id="messageBar" class="hide">
            </div>
        </div>
        <span class="col-lg-4 col-md-4 col-sm-4">
            <div id="appnav" class="navbar-right">
                <ul class="nav navbar-nav">
                    {foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']}
                        <li><button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" type="button" class="btn addButton btn-default module-buttons" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0}  onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}>
                                <div class="fa fa-plus"></div>&nbsp;&nbsp;
                               {vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}
                            </button>
                        </li>
                    {/foreach}

                    <li>
                    {if $LISTVIEW_LINKS['LISTVIEWSETTING']|@count gt 0}
                        <div class="settingsIcon">
                            <button type="button" class="btn btn-default module-buttons dropdown-toggle" data-toggle="dropdown">
                                <span class="fa fa-wrench" aria-hidden="true" title="{vtranslate('LBL_SETTINGS', $MODULE)}"></span>&nbsp; <span class="caret"></span>
                            </button>
                            <ul class="listViewSetting dropdown-menu">
                                {foreach item=LISTVIEW_SETTING from=$LISTVIEW_LINKS['LISTVIEWSETTING']}
                                    {if $LISTVIEW_SETTING->get('isActionLink')}
                                        <li><a href="javascript:void(0)" id="{$LISTVIEW_SETTING->getLabel()}" class="{$LISTVIEW_SETTING->get('linkclass')}" data-url="{$LISTVIEW_SETTING->getUrl()}">{vtranslate($LISTVIEW_SETTING->getLabel(),$MODULE)}</a></li>
                                    {else}
                                        <li><a href={$LISTVIEW_SETTING->getUrl()}>{vtranslate($LISTVIEW_SETTING->getLabel(), $MODULE)}</a></li>
                                    {/if}
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    </li>
                </ul>
            </div>
        </span>
    </div>
</div>     
{/strip}