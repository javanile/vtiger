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
{assign var=SHARED_USER_INFO value= Zend_Json::encode($SHAREDUSERS_INFO)}
{assign var=CURRENT_USER_ID value= $CURRENTUSER_MODEL->getId()}
<input type="hidden" id="sharedUsersInfo" value= {Zend_Json::encode($SHAREDUSERS_INFO)} />
<div class="sidebar-widget-contents" name='calendarViewTypes'>
	<div id="calendarview-feeds">
		<ul class="list-group feedslist">
			<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$CURRENT_USER_ID]['color']};">
				<span>
					{vtranslate('LBL_MINE',$MODULE)}
				</span>
				<span class="activitytype-actions pull-right">
					<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$CURRENT_USER_ID}" data-calendar-feed="Events" 
						   data-calendar-feed-color="{$SHAREDUSERS_INFO[$CURRENT_USER_ID]['color']}" data-calendar-fieldlabel="{vtranslate('LBL_MINE',$MODULE)}" 
						   data-calendar-userid="{$CURRENT_USER_ID}" data-calendar-group="false" data-calendar-feed-textcolor="white">&nbsp;&nbsp;
					<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
				</span>
			</li>
			{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='false'}
			{foreach key=ID item=USER from=$SHAREDUSERS}
				{if $SHAREDUSERS_INFO[$ID]['visible'] != '0'}
					<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$ID]['color']};">
						<span class="userName textOverflowEllipsis" title="{$USER}">
							{$USER}
						</span>
						<span class="activitytype-actions pull-right">
							<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$ID}" data-calendar-feed="Events" 
								   data-calendar-feed-color="{$SHAREDUSERS_INFO[$ID]['color']}" data-calendar-fieldlabel="{$USER}" 
								   data-calendar-userid="{$ID}" data-calendar-group="false" data-calendar-feed-textcolor="white">&nbsp;&nbsp;
							<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
							<i class="fa fa-trash deleteCalendarFeed cursorPointer"></i>
						</span>
					</li>
				{else}
					{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
				{/if}
			{/foreach}
			{foreach key=ID item=GROUP from=$SHAREDGROUPS}
				{if $SHAREDUSERS_INFO[$ID]['visible'] != '0'}
					<li class="activitytype-indicator calendar-feed-indicator" style="background-color: {$SHAREDUSERS_INFO[$ID]['color']};">
						<span class="userName textOverflowEllipsis" title="{$GROUP}">
							{$GROUP}
						</span>
						<span class="activitytype-actions pull-right">
							<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="Events_{$ID}" data-calendar-feed="Events" 
								   data-calendar-feed-color="{$SHAREDUSERS_INFO[$ID]['color']}" data-calendar-fieldlabel="{$GROUP}" 
								   data-calendar-userid="{$ID}" data-calendar-group="true" data-calendar-feed-textcolor="white">&nbsp;&nbsp;
							<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
							<i class="fa fa-trash deleteCalendarFeed cursorPointer"></i>
						</span>
					</li>
				{else}
					{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
				{/if}
			{/foreach}
		</ul>
		<ul class="hide dummy">
			<li class="activitytype-indicator calendar-feed-indicator feed-indicator-template">
				<span></span>
				<span class="activitytype-actions pull-right">
					<input class="toggleCalendarFeed cursorPointer" type="checkbox" data-calendar-sourcekey="" data-calendar-feed="Events" 
					data-calendar-feed-color="" data-calendar-fieldlabel="" 
					data-calendar-userid="" data-calendar-group="" data-calendar-feed-textcolor="white">&nbsp;&nbsp;
					<i class="fa fa-pencil editCalendarFeedColor cursorPointer"></i>&nbsp;&nbsp;
					<i class="fa fa-trash deleteCalendarFeed cursorPointer"></i>
				</span>
			</li>
		</ul>
		<input type="hidden" class="invisibleCalendarViews" value="{$INVISIBLE_CALENDAR_VIEWS_EXISTS}" />
	</div>
</div>
{/strip}
