{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Products/views/SubProductQuantityUpdate.php *}

<div id="quantityUpdateContainer" class="modal-dialog modal-sm">
	<div class="modal-content">
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_EDIT_QUANTITY', $MODULE)}}
		<form class="form-horizontal" id="quantityUpdate" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="action" value="RelationAjax" />
			<input type="hidden" name="src_record" value="{$RECORD_ID}" />
			<input type="hidden" name="relid" value="{$REL_ID}"/> 
			<div class="modal-body">
				<div class="row">
					<span class="col-lg-6"><label for="quantityEdit" class="pull-right" style="margin-top: 5px;">{vtranslate('LBL_EDIT_QUANTITY', $MODULE)}</label></span>
					<span class="col-lg-6">
						<input id="quantityEdit" data-rule-positiveExcludingZero=true data-rule-positive=true class="form-control" type="text" name="quantity" value="{$CURRENT_QTY}">
					</span>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>	
	</div>
</div>