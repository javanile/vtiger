{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="modal-dialog modal-lg">
		<div id="accountHierarchyContainer" class="modelContainer modal-content" style='min-width:750px'>
			{assign var=HEADER_TITLE value={vtranslate('LBL_SHOW_ACCOUNT_HIERARCHY', $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<div class="modal-body">
				<div id ="hierarchyScroll" style="margin-right: 8px;">
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								{foreach item=HEADERNAME from=$ACCOUNT_HIERARCHY['header']}
									<th>{vtranslate($HEADERNAME, $MODULE)}</th>
									{/foreach}
							</tr>
						</thead>
						{foreach item=ENTRIES from=$ACCOUNT_HIERARCHY['entries']}
							<tbody>
								<tr>
									{foreach item=LISTFIELDS from=$ENTRIES}
										<td>{$LISTFIELDS}</td>
									{/foreach}
								</tr>
							</tbody>
						{/foreach}
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<div class="pull-right cancelLinkContainer">
					<button class="btn btn-primary" type="reset" data-dismiss="modal"><strong>{vtranslate('LBL_CLOSE', $MODULE)}</strong></button>
				</div>
			</div>
		</div>
	</div>
{/strip}