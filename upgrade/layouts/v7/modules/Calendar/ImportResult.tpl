{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="fc-overlay-modal modal-content">
		<div class="overlayHeader">
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$MODULE} - {'LBL_RESULT'|@vtranslate:$MODULE}"}
			<div class ="modal-body" style="padding-left:15px;">
				<input type="hidden" name="module" value="{$MODULE}" />
				{if $ERROR_MESSAGE neq ''}
					<div class="alert-danger"><h4>{$ERROR_MESSAGE}</h4></div>
				{/if}
				<table style=" width:90%; margin-left:5%" cellpadding="5">
					<tr>
						<td valign="top">
							<table cellpadding="5" cellspacing="0" align="center" width="100%" class="table table-borderless">
								<tr>
									<td>{'LBL_TOTAL_EVENTS_IMPORTED'|@vtranslate:$MODULE}</td>
									<td width="10%">:</td>
									<td width="30%">{$SUCCESS_EVENTS}</td>
								</tr>
								<tr>
									<td>{'LBL_TOTAL_EVENTS_SKIPPED'|@vtranslate:$MODULE}</td>
									<td width="10%">:</td>
									<td width="30%">{$SKIPPED_EVENTS}</td>
								</tr>

								<tr>
									<td>{'LBL_TOTAL_TASKS_IMPORTED'|@vtranslate:$MODULE}</td>
									<td width="10%">:</td>
									<td width="30%">{$SUCCESS_TASKS}</td>
								</tr>
								<tr>
									<td>{'LBL_TOTAL_TASKS_SKIPPED'|@vtranslate:$MODULE}</td>
									<td width="10%">:</td>
									<td width="30%">{$SKIPPED_TASKS}</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>   
			</div>
			<div class="modal-overlay-footer border1px clearfix">
				<div class="row clearfix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button class="btn btn-danger" onclick="return Vtiger_Import_Js.undoImport('index.php?module={$MODULE}&view=Import&mode=undoIcalImport');"><strong>{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</strong></button>
						&nbsp;&nbsp;&nbsp;<button class="btn btn-success" onclick="location.href='index.php?module={$MODULE}&view=List'" ><strong>{'LBL_FINISH'|@vtranslate:$MODULE}</strong></button>
					</div>
				</div>
		</div>
	</div>
{/strip}