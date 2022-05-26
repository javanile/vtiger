{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'>
	<tr>
		<td align='center'>
			<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
				<table border='0' cellpadding='5' cellspacing='0' width='98%'>
					<tbody>
						<tr>
							<td rowspan='2' width='11%'><img src="{vimage_path('denied.gif')}" ></td>
							<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'>
								<span class='genHeaderSmall'>
									{assign var=SINGLE_MODULE value="SINGLE_$MODULE"}
									<span class='genHeaderSmall'>{vtranslate($SINGLE_MODULE, $MODULE)} {vtranslate('CANNOT_CONVERT', $MODULE)}
									<br>
										<ul> {vtranslate('LBL_FOLLOWING_ARE_POSSIBLE_REASONS', $MODULE)} :
											<li>{vtranslate('LBL_LEADS_FIELD_MAPPING_INCOMPLETE', $MODULE)}</li>
											<li>{vtranslate('LBL_MANDATORY_FIELDS_ARE_EMPTY', $MODULE)}</li>
											{if $EXCEPTION}
											<li>{$EXCEPTION}</li>
											{/if}
										</ul>
									</span>
								</span>
							</td>
						</tr>
						<tr>
							<td class='small' align='right' nowrap='nowrap'>
				{if $CURRENT_USER->isAdminUser()}
					<a href='index.php?parent=Settings&module=Leads&view=MappingDetail'>{vtranslate('LBL_LEADS_FIELD_MAPPING', $MODULE)}</a><br>
				{/if}
					<a href='javascript:window.history.back();'>{vtranslate('LBL_GO_BACK', $MODULE)}</a><br>
				</td>
			</tr>
		</tbody>
		</table>
	</div>
		</td>
	</tr>
		</td>
	</tr>
</table>