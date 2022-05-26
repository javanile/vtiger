{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="listViewPageDiv" id="listViewContent">
		<div class="col-sm-12 col-xs-12 full-height">
			<div id="listview-actions" class="listview-actions-container">
				<div class = "row">
					<div class="btn-group col-md-2"></div>
					<div class='col-md-7' style="padding-top: 5px;text-align: center;">
						<div class="btn-group userFilter" style="text-align: center;">
							<button class="btn btn-default btn-primary" id="activeUsers" data-searchvalue="Active">
								{vtranslate('LBL_ACTIVE_USERS', $MODULE)}
							</button>
							<button class="btn btn-default" id="inactiveUsers" data-searchvalue="Inactive">
								{vtranslate('LBL_INACTIVE_USERS', $MODULE)}
							</button>
						</div>
					</div>
					<div class="col-md-3">
						{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
						{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
					</div>
				</div>
				<div class="list-content">
{/strip}
