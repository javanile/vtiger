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
	<div class="col-sm-12 col-xs-12 content-area" id="importModules">
		<div class="row">
			<div class="col-sm-4 col-xs-4">
				<div class="row">
					<div class="col-sm-8 col-xs-8">
						<input type="text" id="searchExtension" class="extensionSearch form-control" placeholder="{vtranslate('Search for an extension..', $QUALIFIED_MODULE)}"/>
					</div>
				</div>
			</div>
		</div>
		<br>
		<div class="contents row">
			<div class="col-sm-12 col-xs-12" id="extensionContainer">
				{include file='ExtensionModules.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</div>
		</div>

		{include file="CardSetupModals.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}