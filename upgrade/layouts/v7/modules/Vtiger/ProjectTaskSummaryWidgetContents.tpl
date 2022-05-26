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
{strip}
	{foreach item=HEADER from=$RELATED_HEADERS}
		{if $HEADER->get('label') eq "Project Task Name"}
			{assign var=TASK_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
		{elseif $HEADER->get('label') eq "Progress"}
			{assign var=TASK_PROGRESS_HEADER value=vtranslate($HEADER->get('label'),$MODULE_NAME)}
		{elseif $HEADER->get('label') eq "Status"}
			{assign var=TASK_STATUS_HEADER value=vtranslate($HEADER->get('label'),$MODULE_NAME)}
		{/if}
	{/foreach}
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		{assign var=PERMISSIONS value=Users_Privileges_Model::isPermitted($RELATED_MODULE, 'EditView', $RELATED_RECORD->get('id'))}
		<div class="recentActivitiesContainer">
			<ul class="unstyled">
				<li>
					<div>
						<div class="textOverflowEllipsis width27em">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('projecttaskname')}">
								<strong>{$RELATED_RECORD->getDisplayValue('projecttaskname')}</strong>
							</a>
						</div>
						<div class="row">
							{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ProjectTask')}
							{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskprogress')}
							{if $FIELD_MODEL->isViewableInDetailView()}
							<div class="col-lg-6">
								<div class="row">
									<span class="col-lg-6">{$TASK_PROGRESS_HEADER} :</span>
									{if $PERMISSIONS && $FIELD_MODEL->isEditable()}
										<span class="col-lg-6">
											<div class="dropdown pull-left">
												<a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</span>&nbsp;<b class="caret"></b></a>
												<ul class="dropdown-menu widgetsList" data-recordid="{$RELATED_RECORD->getId()}" data-fieldname="projecttaskprogress" 
                                                    data-old-value="{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}" data-mandatory="{$FIELD_MODEL->isMandatory()}">
													{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
													<li class="editTaskDetails emptyOption"><a>{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}</a></li>
													{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
														<li class="editTaskDetails"><a>{$PICKLIST_VALUE}</a></li>
													{/foreach}
												</ul>
											</div>
										</span>
									{else}
										<span class="col-lg-7"><strong>&nbsp;{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</strong></span>
									{/if}
								</div>
							</div>
							{/if}
							{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskstatus')}
							{if $FIELD_MODEL->isViewableInDetailView()}
							<div class="col-lg-6">
								<div class="row">
									<span class="col-lg-6">{$TASK_STATUS_HEADER} :</span>
									{if $PERMISSIONS && $FIELD_MODEL->isEditable()}
										<span class="col-lg-6 nav nav-pills">
											<div class="dropdown pull-left">
												<a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}</span>&nbsp;<b class="caret"></b></a>
												<ul class="dropdown-menu widgetsList pull-right" data-recordid="{$RELATED_RECORD->getId()}" data-fieldname="projecttaskstatus" 
													data-old-value="{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}" data-mandatory="{$FIELD_MODEL->isMandatory()}" style="max-height: 200px; left: -64px;">
													{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
													<li class="editTaskDetails emptyOption" value=""><a>{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}</a></li>
													{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
														<li class="editTaskDetails" value="{$PICKLIST_VALUE}"><a>{$PICKLIST_VALUE}</a></li>
													{/foreach}
												</ul>
											</div>
										</span>
									{else}
										<span class="col-lg-7"><strong>&nbsp;{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}</strong></span>
									{/if}
								</div>
							</div>
							{/if}
						</div>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="">
			<div class="pull-right">
				<a class="moreRecentTasks cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}