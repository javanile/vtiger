{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

<form class="form-horizontal" name="step4" method="post" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step5" />

	<div class="row main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4>{vtranslate('LBL_SYSTEM_CONFIGURATION', 'Install')} </h4>
				</div>
				<div class="col-sm-2">
					<a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="pull-right">
						<img src="{'help.png'|vimage_path}" alt="Help-Icon"/>
					</a>
				</div>
			</div>
			<hr>
			<div class="row hide" id="errorMessage"></div>
			<div class="row">
				<div class="col-sm-6">
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_DATABASE_INFORMATION', 'Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_DATABASE_TYPE', 'Install')}<span class="no">*</span></td>
								<td>
									{vtranslate('MySQL', 'Install')}
									{if function_exists('mysqli_connect')}
										<input type="hidden" value="mysqli" name="db_type">
									{else}
										<input type="hidden" value="mysql" name="db_type">
									{/if}
								</td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_HOST_NAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" value="{$DB_HOSTNAME}" name="db_hostname"></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_USERNAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" value="{$DB_USERNAME}" name="db_username"></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_PASSWORD','Install')}</td>
								<td><input type="password" value="{$DB_PASSWORD}" name="db_password"></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_DB_NAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" value="{$DB_NAME}" name="db_name"></td>
							</tr>
							<tr>
								<td colspan="2">
									<label>
										<input type="checkbox" name="create_db"/>
										<span>{vtranslate('LBL_CREATE_NEW_DB','Install')}</span>
									</label>
								</td>
							</tr>
							<tr class="hide" id="root_user">
								<td>{vtranslate('LBL_ROOT_USERNAME', 'Install')}<span class="no">*</span></td>
								<td><input type="text" value="" name="db_root_username"></td>
							</tr>
							<tr class="hide" id="root_password">
								<td>{vtranslate('LBL_ROOT_PASSWORD', 'Install')}</td>
								<td><input type="password" value="" name="db_root_password"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-sm-6">
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_SYSTEM_INFORMATION','Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_CURRENCIES','Install')}<span class="no">*</span></td>
								<td>
									<select name="currency_name" class="select2" style="width:220px;">
										{foreach key=CURRENCY_NAME item=CURRENCY_INFO from=$CURRENCIES}
											<option value="{$CURRENCY_NAME}" {if $CURRENCY_NAME eq 'USA, Dollars'} selected {/if}>{$CURRENCY_NAME} ({$CURRENCY_INFO.1})</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2">{vtranslate('LBL_ADMIN_INFORMATION', 'Install')}</th></tr>
						</thead>
						<tbody>
							<tr>
								<td>{vtranslate('LBL_USERNAME', 'Install')}</td>
								<td>admin<input type="hidden" name="{$ADMIN_NAME}" value="admin" /></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_PASSWORD', 'Install')}<span class="no">*</span></td>
								<td><input type="password" value="{$ADMIN_PASSWORD}" name="password" /></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_RETYPE_PASSWORD', 'Install')} <span class="no">*</span></td>
								<td><input type="password" value="{$ADMIN_PASSWORD}" name="retype_password" />
									<span id="passwordError" class="no"></span></td>
							</tr>
							<tr>
								<td>{vtranslate('First Name', 'Install')}</td>
								<td><input type="text" value="" name="firstname" /></td>
							</tr>
							<tr>
								<td>{vtranslate('Last Name', 'Install')} <span class="no">*</span></td>
								<td><input type="text" value="{$ADMIN_LASTNAME}" name="lastname" /></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_EMAIL','Install')} <span class="no">*</span></td>
								<td><input type="text" value="{$ADMIN_EMAIL}" name="admin_email"></td>
							</tr>
							<tr>
								<td>{vtranslate('LBL_DATE_FORMAT','Install')} <span class="no">*</span></td>
								<td>
									<select class="select2" style="width:220px;" name="dateformat">
										<option value="mm-dd-yyyy">mm-dd-yyyy</option>
										<option value="dd-mm-yyyy">dd-mm-yyyy</option>
										<option value="yyyy-mm-dd">yyyy-mm-dd</option>
									</select>
								</td>
							</tr>
							<tr>
								<td>
									{vtranslate('LBL_TIME_ZONE','Install')} <span class="no">*</span>
								</td>
								<td>
									<select class="select2" name="timezone" style="width:300px;">
										{foreach item=TIMEZONE from=$TIMEZONES}
											<option value="{$TIMEZONE}" {if $TIMEZONE eq 'America/Los_Angeles'}selected{/if}>{vtranslate($TIMEZONE, 'Users')}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<div class="button-container">
						<input type="button" class="btn btn-default" value="{vtranslate('LBL_BACK','Install')}" name="back"/>
						<input type="button" class="btn btn-large btn-primary" value="{vtranslate('LBL_NEXT','Install')}" name="step5"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>