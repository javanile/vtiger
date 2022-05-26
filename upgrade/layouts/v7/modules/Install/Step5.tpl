{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<form class="form-horizontal" name="step5" method="post" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step6" />
	<input type=hidden name="auth_key" value="{$AUTH_KEY}" />

	<div class="row main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4>{vtranslate('LBL_CONFIRM_CONFIGURATION_SETTINGS','Install')}</h4>
				</div>
				<div class="col-sm-2">
					<a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="pull-right">
						<img src="{'help.png'|vimage_path}" alt="Help-Icon"/>
					</a>
				</div>
			</div>
			<hr>
			{if $DB_CONNECTION_INFO['flag'] neq true}
				<div class="offset2 row" id="errorMessage">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<div class="alert alert-error">
							{$DB_CONNECTION_INFO['error_msg']}
							{$DB_CONNECTION_INFO['error_msg_info']}
						</div>
					</div>
				</div>
			{/if}
			<div class="offset2 row">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_DATABASE_INFORMATION','Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_DATABASE_TYPE','Install')}<span class="no">*</span></td>
								<td>{vtranslate('MySQL','Install')}</td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_DB_NAME','Install')}<span class="no">*</span></td>
								<td>{$INFORMATION['db_name']}</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_URL','Install')}<span class="no">*</span></td>
								<td><a href="#">{$SITE_URL}</a></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_CURRENCY','Install')}<span class="no">*</span></td>
								<td>{$INFORMATION['currency_name']}</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_ADMIN_USER_INFORMATION','Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_USERNAME','Install')}</td>
								<td>{$INFORMATION['admin']}</td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_EMAIL','Install')}<span class="no">*</span></td>
								<td>{$INFORMATION['admin_email']}</td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_TIME_ZONE','Install')}<span class="no">*</span></td>
								<td>{$INFORMATION['timezone']}</td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_DATE_FORMAT','Install')}<span class="no">*</span></td>
								<td>{$INFORMATION['dateformat']}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row offset2">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<div class="button-container">
						<input type="button" class="btn btn-default" value="{vtranslate('LBL_BACK','Install')}" {if $DB_CONNECTION_INFO['flag'] eq true} disabled= "disabled" {/if} name="back"/>
						{if $DB_CONNECTION_INFO['flag'] eq true}
							<input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step6"/>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
</form>