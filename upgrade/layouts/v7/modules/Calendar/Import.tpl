{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Calendar/views/Import.php *}

<div class="modal-dialog">	
	<div class="modal-content">
		<form method="POST" action="index.php" enctype="multipart/form-data" id="ical_import" name="ical_import">
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_IMPORT_RECORDS', $MODULE)}}
			<div class="modal-body">
				<div class='form-group' style = "height:50px">
					<input type="hidden" value="{$MODULE}" name="module">	
					<input type="hidden" value="Import" name="view">
					<input type="hidden" value="importResult" name="mode">
					<h5><label class ="col-lg-4 textAlignRight" for="import_file" style = "padding-top:9px">{vtranslate('LBL_IMPORT_RECORDS', $MODULE)}</label></h5>
					<div class = "col-lg-6">
						<div class="fileUploadBtn btn btn-primary">
							<span><i class="fa fa-laptop"></i> {vtranslate('Select from My Computer', $MODULE)}</span>
							<input type="file" name="import_file" id="import_file" onchange="Vtiger_Import_Js.checkFileType(event)" data-file-formats="ics"/>
						</div>
						<div id="importFileDetails" class="padding10"></div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<center>
					<button class="btn btn-success" type="submit" name="saveButton" onclick="return Calendar_List_Js.import()" >{vtranslate('LBL_IMPORT', $MODULE)}</button>
					&nbsp;&nbsp;<a class='cancelLink' data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				</center>
			</div>
		</form>
	</div>
</div>