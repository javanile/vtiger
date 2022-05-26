{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="modal-dialog modal-lg installationLog">
		<div class='modal-content'>
			<div class="modal-header" style="background: #596875;color:white;">
				<div class="row">
					<div class="col-lg-11 col-md-11">
						{if $ERROR}
							<input type="hidden" name="installationStatus" value="error" />
							<h3 class="modal-title" style="color: red">{vtranslate('LBL_INSTALLATION_FAILED', $QUALIFIED_MODULE)}</h3>
						{else}
							<input type="hidden" name="installationStatus" value="success" />
							<h3 class="modal-title">{vtranslate('LBL_SUCCESSFULL_INSTALLATION', $QUALIFIED_MODULE)}</h3>
						{/if}
					</div>
					<div class="col-lg-1 col-md-1">
						<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">X</button>
					</div>
				</div>
			</div>
			<div class="modal-body" id="installationLog">
				{if $ERROR}
					<p style="color:red;">{vtranslate($ERROR_MESSAGE, $QUALIFIED_MODULE)}</p>
				{else}
					<div class="row">
						<span class="col-sm-12 col-xs-12 font-x-x-large">{vtranslate('LBL_INSTALLATION_LOG', $QUALIFIED_MODULE)}</span>
					</div>
					<div id="extensionInstallationInfo" class="backgroundImageNone" style="background-color: white;padding: 2%;">
						{if $MODULE_ACTION eq "Upgrade"}
							{$MODULE_PACKAGE->update($TARGET_MODULE_INSTANCE, $MODULE_FILE_NAME)}
						{else}
							{$MODULE_PACKAGE->import($MODULE_FILE_NAME, 'false')}
						{/if}
						{assign var=UNLINK_RESULT value={unlink($MODULE_FILE_NAME)}}
					</div>
				{/if}
			</div>
			<div class="modal-footer">
				<span class="pull-right">
					<button class="btn btn-success" id="importCompleted" onclick="location.reload()">{vtranslate('LBL_OK', $QUALIFIED_MODULE)}</button>
				</span>
			</div>
		</div>
	</div>
{/strip}