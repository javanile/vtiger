{*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
	<div style="padding-left: 15px;">
       <div id="table-content" class="table-container">
		<table id="listview-table"  class="table {if $TASK_LIST eq '0'}listview-table-norecords {else} listview-table{/if} ">
			<thead>
				<tr class="listViewContentHeader">
					<th width="20%">{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</th>
					<th width="30%">{vtranslate('LBL_TASK_TYPE',$QUALIFIED_MODULE)}</th>
					<th>{vtranslate('LBL_TASK_TITLE',$QUALIFIED_MODULE)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$TASK_LIST item=TASK}
					<tr class="listViewEntries">
						<td>
                            <div class="pull-left actions">
								<span class="actionImages">
									<a data-url="{$TASK->getEditViewUrl()}">
										<i class="fa fa-pencil alignMiddle" title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}"></i>
                                    </a>&nbsp;&nbsp;
									<a class="deleteTask" data-deleteurl="{$TASK->getDeleteActionUrl()}">
										<i class="fa fa-trash alignMiddle" title="{vtranslate('LBL_DELETE',$QUALIFIED_MODULE)}"></i>
									</a>
								</span>
                            </div>&nbsp;&nbsp;
                            <input style="opacity: 0;" type="checkbox" data-on-color="success" class="taskStatus" data-statusurl="{$TASK->getChangeStatusUrl()}" {if $TASK->isActive()} checked="" value="on" {else} value="off" {/if} />
                        </td>
                        <td class="listViewEntryValue">{vtranslate($TASK->getTaskType()->getLabel(),$QUALIFIED_MODULE)}</td>
						<td><span class="pull-left">{Vtiger_Util_Helper::toSafeHTML($TASK->getName())}</span></td>
					<tr>
				{/foreach}
                <tr class="listViewEntries hide taskTemplate">
                    <td>
                        <div class="pull-left actions">
                            <span class="actionImages">
                                <a class="editTask">
                                    <i class="fa fa-pencil alignMiddle" ></i>
                                </a>&nbsp;&nbsp;
                                <a class="deleteTaskTemplate">
                                    <i class="fa fa-trash alignMiddle"></i>
                                </a>
                            </span>
                        </div>&nbsp;&nbsp;
                        <input style="opacity: 0;" type="checkbox" data-on-color="success" class="tmpTaskStatus" checked="" value="on"/>
                    </td>
                    <td class="listViewEntryValue taskType"></td>
                    <td><span class="pull-left taskName"></span></td>
                </tr>
			</tbody>
		</table>
		{if empty($TASK_LIST)}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{vtranslate('LBL_NO_TASKS_ADDED',$QUALIFIED_MODULE)}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
        </div>
	</div>
{/strip}