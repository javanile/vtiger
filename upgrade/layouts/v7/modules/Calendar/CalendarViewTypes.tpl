{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Calendar/views/ViewTypes.php *}
{strip}
<div class="sidebar-widget-contents" name='calendarViewTypes'>
	<div id="calendarview-feeds">
		<ul class="list-group feedslist">
		{foreach item=VIEWINFO from=$VIEWTYPES['visible'] name=calendarview}
			<li class="activitytype-indicator calendar-feed-indicator container-fluid" style="background-color: {$VIEWINFO['color']};">
				<span>
					{vtranslate($VIEWINFO['module'], $VIEWINFO['module'])} 
					{if $VIEWINFO['conditions']['name'] neq ''} ({vtranslate($VIEWINFO['conditions']['name'],$MODULE)}) {/if}-
					{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}
				</span>
				<span class="activitytype-actions pull-right">
					<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="{$VIEWINFO['module']}_{$VIEWINFO['fieldname']}{if $VIEWINFO['conditions']['name'] neq ''}_{$VIEWINFO['conditions']['name']}{/if}" data-calendar-feed="{$VIEWINFO['module']}" 
						   data-calendar-feed-color="{$VIEWINFO['color']}" data-calendar-fieldlabel="{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}" 
						   data-calendar-fieldname="{$VIEWINFO['fieldname']}" title="{vtranslate($VIEWINFO['module'],$VIEWINFO['module'])}" data-calendar-type="{$VIEWINFO['type']}" 
						   data-calendar-feed-textcolor="white" data-calendar-feed-conditions='{$VIEWINFO['conditions']['rules']}' />&nbsp;&nbsp;
					<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
					<i class="fa fa-trash deleteCalendarFeed cursorPointer"></i>
				</span>
			</li>
		{/foreach}
		</ul>

		{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='false'}
		{if $ADDVIEWS}
			{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
		{/if}
		<input type="hidden" class="invisibleCalendarViews" value="{$INVISIBLE_CALENDAR_VIEWS_EXISTS}" />
		{*end*}
		<ul class="hide dummy">
			<li class="activitytype-indicator calendar-feed-indicator feed-indicator-template container-fluid">
				<span></span>
				<span class="activitytype-actions pull-right">
					<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="" data-calendar-feed="" 
						   data-calendar-feed-color="" data-calendar-fieldlabel="" 
						   data-calendar-fieldname="" title="" data-calendar-type=""
						   data-calendar-feed-textcolor="white">&nbsp;&nbsp;
					<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
					<i class="fa fa-trash deleteCalendarFeed cursorPointer"></i>
				</span>
			</li>
		</ul>
	</div>
</div>
{/strip}