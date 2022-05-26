{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}

<div>
	{foreach from=$ACTIVITIES key=INDEX item=ACTIVITY}
	<div>
		<div class='pull-left'>
			{if $ACTIVITY->get('activitytype') == 'Task'}
				<image src="{vimage_path('Tasks.png')}" width="24px"/>&nbsp;&nbsp;
			{else}
				<image src="{vimage_path('Calendar.png')}" width="24px" />&nbsp;&nbsp;
			{/if}
		</div>
		<div>
			<div class='pull-left' style='margin-top:5px'>
				{assign var=PARENT_ID value=$ACTIVITY->get('parent_id')}
				{assign var=CONTACT_ID value=$ACTIVITY->get('contact_id')}
				<a href="{$ACTIVITY->getDetailViewUrl()}">{$ACTIVITY->get('subject')}</a>{if $PARENT_ID} {vtranslate('LBL_FOR')} {$ACTIVITY->getDisplayValue('parent_id')}{else if $CONTACT_ID} {vtranslate('LBL_FOR')} {$ACTIVITY->getDisplayValue('contact_id')}{/if}
			</div>
				{assign var=START_DATE value=$ACTIVITY->get('date_start')}
				{assign var=START_TIME value=$ACTIVITY->get('time_start')}
				
				{assign var=DUE_DATE value=$ACTIVITY->get('due_date')}
				{assign var=DUE_TIME value=$ACTIVITY->get('time_end')}
			<p class='pull-right muted' style='margin-top:5px;padding-right:5px;'><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")} {vtranslate('LBL_TO')} {Vtiger_Util_Helper::formatDateTimeIntoDayString("$DUE_DATE $DUE_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$START_DATE $START_TIME")}</small></p>
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
	</div>
	{foreachelse}
		<span class="noDataMsg">
                    {assign var=REQUEST_NAME value={getPurifiedSmartyParameters('name')}}
			{if $REQUEST_NAME eq 'OverdueActivities'}
				{vtranslate('LBL_NO_OVERDUE_ACTIVITIES', $MODULE_NAME)}
			{else}
				{vtranslate('LBL_NO_SCHEDULED_ACTIVITIES', $MODULE_NAME)}
			{/if}
		</span>
	{/foreach}

{if $PAGING->get('nextPageExists') eq 'true'}
	<div class='pull-right' style='margin-top:5px;padding-right:5px;'>
        <a href="javascript:;" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING->getNextPage()}">{vtranslate('LBL_MORE')}...</a>
        <br />
        <br />
        <br />
        <br />
	</div>
{else}
    <br />
    <br />
    <br />
    <br />
{/if}
</div>