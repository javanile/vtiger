{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div style="min-height: 600px;">
		<table border="0" cellpadding="5" cellspacing="0" width="100%" style="margin-top: 100px;">
			<tr>
				<td align="center">
					<div style="border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 40%; position: relative;padding-right: 3px;">
						<table border="0" cellpadding="5" cellspacing="0" width="98%">
							<tbody>
								<tr>
									<td rowspan="2"><img src="{vimage_path("denied.gif")}" style="margin: 10px;"></td>
									<td width="80%" style="border-bottom: 1px solid rgb(204, 204, 204);">
										<span class="genHeaderSmall"><b>{vtranslate($EXTENSION_LABEL, $QUALIFIED_MODULE)}</b> {ucfirst(strtolower(vtranslate('LBL_EXTENSION_NOT_COMPATABLE', $QUALIFIED_MODULE)))}</span>
									</td>
								</tr>
								<tr>
									<td class="small" align="right" nowrap="nowrap">
										<a href="index.php?module=ExtensionStore&parent=Settings&view=ExtensionStore">{vtranslate('LBL_GO_BACK', $MODULE)}</a><br>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</td>
			</tr>
		</table>
		{include file="CardSetupModals.tpl"|@vtemplate_path:$QUALIFIED_MODULE QUALIFIED_MODULE=$QUALIFIED_MODULE}
	</div>
{strip}