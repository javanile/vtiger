{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="detailViewContainer col-lg-12 col-md-12 col-sm-12" id="importModules">
		<div class="widget_header row col-lg-12 col-md-12 col-sm-12">
			<h4>{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}</h4>
		</div>
		<form class="form-horizontal" id="importUserModule" name="importUserModule" action='index.php' method="POST" enctype="multipart/form-data">
			<input type="hidden" name="module" value="ModuleManager" />
			<input type="hidden" name="moduleAction" value="Import"/>
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="view" value="ModuleImport" />
			<input type="hidden" name="mode" value="importUserModuleStep2" />
			<div class="contents">
				<div class="row col-lg-12 col-md-12 col-sm-12" style="margin-top:3%">
					<div class="col-lg-1 col-md-1 col-sm-1">&nbsp;</div>
					<div class="col-lg-10 col-md-10 col-sm-10">
						<div class="alert alert-danger">
							{vtranslate('LBL_DISCLAIMER_FOR_IMPORT_FROM_ZIP', $QUALIFIED_MODULE)}
						</div>
						<div>
							<input type="checkbox" name="acceptDisclaimer" /> &nbsp;&nbsp;<b>{vtranslate('LBL_ACCEPT_WITH_THE_DISCLAIMER', $QUALIFIED_MODULE)}</b>
						</div>
						<div style="margin-top: 15px; display: none;">
							<span name="proceedInstallation" class="fileUploadBtn btn btn-primary">
								<span><i class="fa fa-laptop"></i> {vtranslate('Select from My Computer', $MODULE)}</span>
								<input type="file" name="moduleZip" id="moduleZip" size="80px" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
										data-validator={Zend_Json::encode([['name'=>'UploadModuleZip']])} />
							</span>
							<span id="moduleFileDetails" style="margin-left: 15px;"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-overlay-footer clearfix">
				<div class="row clearfix">
					<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
						<button class="btn btn-success saveButton" disabled="disabled" type="submit" name="importFromZip"><strong>{vtranslate('LBL_IMPORT', $MODULE)}</strong></button>&nbsp;&nbsp;
						<a class="cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}
