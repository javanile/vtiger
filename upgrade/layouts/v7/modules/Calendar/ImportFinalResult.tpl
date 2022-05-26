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
			<input type="hidden" name="module" value="{$MODULE}" />
			<div class="modal-body" style="margin-bottom: 20px;">
				<h5>
					<div class="col-lg-12 textAlignCenter">{'LBL_LAST_IMPORT_UNDONE'|@vtranslate:$MODULE}</div>
				</h5>
			</div>
			<div class="modal-overlay-footer clearfix">
				<div class="row clearfix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button class="btn btn-success" onclick="location.href='index.php?module={$MODULE}&view=List'" ><strong>{'LBL_FINISH'|@vtranslate:$MODULE}</strong></button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}