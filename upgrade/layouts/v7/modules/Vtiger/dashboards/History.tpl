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
<div class="dashboardWidgetHeader clearfix">
    <div class="title">
        <div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div>
    </div>
    <div class="userList">
        {assign var=CURRENT_USER_ID value=$CURRENT_USER->getId()}
        {if $ACCESSIBLE_USERS|@count gt 1}
            <select class="select2 widgetFilter col-lg-3 reloadOnChange" name="type">
                <option value="all"  selected>{vtranslate('All', $MODULE_NAME)}</option>
                {foreach key=USER_ID from=$ACCESSIBLE_USERS item=USER_NAME}
                    <option value="{$USER_ID}">
                    {if $USER_ID eq $CURRENT_USER_ID} 
                        {vtranslate('LBL_MINE',$MODULE_NAME)}
                    {else}
                        {$USER_NAME}
                    {/if}
                    </option>
                {/foreach}
            </select>
            {else}
                <center>{vtranslate('LBL_MY',$MODULE_NAME)} {vtranslate('History',$MODULE_NAME)}</center>
        {/if}
    </div>
</div>
<div class="dashboardWidgetContent" style="padding-top:15px;">
	{include file="dashboards/HistoryContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

<div class="widgeticons dashBoardWidgetFooter">
    <div class="filterContainer boxSizingBorderBox">
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-sm-12">
                <div class="col-lg-4">
                    <span><strong>{vtranslate('LBL_SHOW', $MODULE_NAME)}</strong></span>
                </div>
                <div class="col-lg-7">
                        {if $COMMENTS_MODULE_MODEL->isPermitted('DetailView')}
                            <label class="radio-group cursorPointer">
                                <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer" value="comments" /> {vtranslate('LBL_COMMENTS', $MODULE_NAME)}
                            </label><br>
                        {/if}
                        <label class="radio-group cursorPointer">
                            <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer" value="updates" /> 
                            <span>{vtranslate('LBL_UPDATES', $MODULE_NAME)}</span>
                        </label><br>
                        <label class="radio-group cursorPointer">
                            <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer" value="all" checked="" /> {vtranslate('LBL_BOTH', $MODULE_NAME)}
                        </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <span class="col-lg-4">
                        <span>
                            <strong>{vtranslate('LBL_SELECT_DATE_RANGE', $MODULE_NAME)}</strong>
                        </span>
                </span>
                <span class="col-lg-7">
                    <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="modifiedtime">
                        <input type="text" class="input-sm form-control" name="start" style="height:30px;"/>
                        <span class="input-group-addon">to</span>
                        <input type="text" class="input-sm form-control" name="end" style="height:30px;"/>
                    </div>
                </span>
            </div>
        </div>
    </div>
    <div class="footerIcons pull-right">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>