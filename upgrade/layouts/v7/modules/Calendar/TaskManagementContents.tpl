{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{assign var=MODULE_MODEL value= Vtiger_Module_Model::getInstance($MODULE)}
	{assign var=SELECTED_PICKLIST_FIELDMODEL value= Vtiger_Field_Model::getInstance('taskstatus', $MODULE_MODEL)}
	{assign var=PICKLIST_COLOR_MAP value= Settings_Picklist_Module_Model::getPicklistColorMap($SELECTED_PICKLIST_FIELDMODEL->getName())}
	<style type="text/css">
		{foreach item=PICKLIST_COLOR key=PICKLIST_KEY_ID from=$PICKLIST_COLOR_MAP}
			{assign var=PICKLIST_TEXT_COLOR value= Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)}
			.picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY_ID} {
				background-color: {$PICKLIST_COLOR};
				color: {$PICKLIST_TEXT_COLOR}; 
			}
		{/foreach}
	</style>
	{foreach key=RECORDID item=RECORD_MODEL from=$TASKS}
		<div class="entries ui-draggable">
			{assign var=RECORD_BASIC_INFO value = $RECORD_MODEL->get('basicInfo')}
			<div class="task clearfix" data-recordid="{$RECORD_MODEL->get('id')}" data-priority="{$PRIORITY}" data-basicinfo='{json_encode($RECORD_BASIC_INFO)}' style="border-left:4px solid {$COLORS[$PRIORITY]}">
				{assign var=STATUS value=$RECORD_MODEL->get('status')}
				<div class="task-status pull-left">
					<input class='statusCheckbox' type="checkbox" name="taskstatus" {if $STATUS eq "Completed"} checked disabled {/if}/>
				</div>
				<div class='task-body clearfix'>
					<div class="taskSubject pull-left {if $STATUS eq "Completed"} textStrike {/if} textOverflowEllipsis" style='width:70%;'>
						<a class="quickPreview" data-id="{$RECORDID}" title="{$RECORD_MODEL->get('subject')}">{$RECORD_MODEL->get('subject')}</a>
					</div>
					{assign var=SELECTED_PICKLISTFIELD_ALL_VALUES value= Vtiger_Util_Helper::getPickListValues('taskstatus')}
					{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$SELECTED_PICKLISTFIELD_ALL_VALUES}
						{if $PICKLIST_VALUE == $RECORD_MODEL->get('status')}
							<div class="more pull-right taskStatus picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY}">
								{$RECORD_MODEL->get('status')}
							</div>
						{/if}
					{/foreach}
				</div>
				<div class='other-details clearfix'>
					<div class="pull-left drag-task">
						<img class="cursorPointerMove" src="{vimage_path('drag.png')}" />&nbsp;&nbsp;
					</div>
					{if $RECORD_MODEL->get('sendnotification') eq 1}
						<i class='notificationEnabled fa fa-bell'></i>&nbsp;&nbsp;
					{/if}

					<div class="task-details">
						<span class='taskDueDate'>
							<i class="fa fa-calendar"></i>&nbsp;<span style="vertical-align: middle">{Vtiger_Date_UIType::getDisplayDateValue($RECORD_MODEL->get('due_date'))}</span>
						</span>

						{assign var=RELATED_PARENT value = $RECORD_BASIC_INFO['parent_id']}
						{assign var=RELATED_CONTACT value = $RECORD_BASIC_INFO['contact_id']}

						{if !empty($RELATED_PARENT)}
							<span class='related_account' style='margin-left: 8px;'>
								{assign var=RELATED_PARENT_MODULE value=$RELATED_PARENT['module']}
								<span style="font-size: 12px;">{Vtiger_Module_Model::getModuleIconPath($RELATED_PARENT_MODULE)}&nbsp;</span>
								<span class="recordName textOverflowEllipsis" style="vertical-align: middle">
									<a class="quickPreview" href="index.php?module={$RELATED_PARENT_MODULE}&view=Detail&record={$RELATED_PARENT['id']}"  data-id="{$RELATED_PARENT['id']}" title="{$RELATED_PARENT['display_value']}">{$RELATED_PARENT['display_value']}</a>
								</span>
							</span>
						{/if}
						{if !empty($RELATED_CONTACT['id'])}
							<span class='related_contact' style='margin-left: 8px;'>
								<span style="font-size: 12px;">{Vtiger_Module_Model::getModuleIconPath('Contacts')}&nbsp;</span>
								<span class="recordName textOverflowEllipsis" style="vertical-align: middle">
									<a class="quickPreview" href="index.php?module={$RELATED_CONTACT['module']}&view=Detail&record={$RELATED_CONTACT['id']}" data-id="{$RELATED_CONTACT['id']}" title="{$RELATED_CONTACT['display_value']}">{$RELATED_CONTACT['display_value']}</a>
								</span>
							</span>
						{/if}
					</div>
					<div class="more pull-right cursorPointer task-actions">
						<a href="#" class="quickTask" id="taskPopover"><i class="fa fa-pencil-square-o icon"></i></a>&nbsp;&nbsp;
						<a href="#" class="taskDelete"><i class="fa fa-trash icon"></i></a>
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	{if $PAGING_MODEL->get("nextPageExists") eq true}
		<div class="row moreButtonBlock">
			<button class="btn btn-default moreRecords" style="width:100%;"> {vtranslate("LBL_MORE",$MODULE)} </button>
		</div>
	{/if}

{/strip}