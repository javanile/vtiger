<?php /* Smarty version Smarty-3.1.7, created on 2022-05-25 17:46:36
         compiled from "/var/www/html/includes/runtime/../../layouts/v7/modules/Install/Step4.tpl" */ ?>
<?php /*%%SmartyHeaderCode:379828372628e5d6c767563-97027652%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '0ac8f8e4c827bd140ce005cab26742ee54a0074b' => 
    array (
      0 => '/var/www/html/includes/runtime/../../layouts/v7/modules/Install/Step4.tpl',
      1 => 1588595432,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '379828372628e5d6c767563-97027652',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'DB_HOSTNAME' => 0,
    'DB_USERNAME' => 0,
    'DB_PASSWORD' => 0,
    'DB_NAME' => 0,
    'CURRENCIES' => 0,
    'CURRENCY_NAME' => 0,
    'CURRENCY_INFO' => 0,
    'ADMIN_NAME' => 0,
    'ADMIN_PASSWORD' => 0,
    'ADMIN_LASTNAME' => 0,
    'ADMIN_EMAIL' => 0,
    'TIMEZONES' => 0,
    'TIMEZONE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_628e5d6c79c34',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_628e5d6c79c34')) {function content_628e5d6c79c34($_smarty_tpl) {?>

<form class="form-horizontal" name="step4" method="post" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step5" />

	<div class="row main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4><?php echo vtranslate('LBL_SYSTEM_CONFIGURATION','Install');?>
 </h4>
				</div>
				<div class="col-sm-2">
					<a href="https://wiki.vtiger.com/vtiger6/" target="_blank" class="pull-right">
						<img src="<?php echo vimage_path('help.png');?>
" alt="Help-Icon"/>
					</a>
				</div>
			</div>
			<hr>
			<div class="row hide" id="errorMessage"></div>
			<div class="row">
				<div class="col-sm-6">
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2"><?php echo vtranslate('LBL_DATABASE_INFORMATION','Install');?>
</th></tr>
						</thead>
						<tbody>
							<tr>
								<td><?php echo vtranslate('LBL_DATABASE_TYPE','Install');?>
<span class="no">*</span></td>
								<td>
									<?php echo vtranslate('MySQL','Install');?>

									<?php if (function_exists('mysqli_connect')){?>
										<input type="hidden" value="mysqli" name="db_type">
									<?php }else{ ?>
										<input type="hidden" value="mysql" name="db_type">
									<?php }?>
								</td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_HOST_NAME','Install');?>
<span class="no">*</span></td>
								<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['DB_HOSTNAME']->value;?>
" name="db_hostname"></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_USERNAME','Install');?>
<span class="no">*</span></td>
								<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['DB_USERNAME']->value;?>
" name="db_username"></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_PASSWORD','Install');?>
</td>
								<td><input type="password" value="<?php echo $_smarty_tpl->tpl_vars['DB_PASSWORD']->value;?>
" name="db_password"></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_DB_NAME','Install');?>
<span class="no">*</span></td>
								<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['DB_NAME']->value;?>
" name="db_name"></td>
							</tr>
							<tr>
								<td colspan="2">
									<label>
										<input type="checkbox" name="create_db"/>
										<span><?php echo vtranslate('LBL_CREATE_NEW_DB','Install');?>
</span>
									</label>
								</td>
							</tr>
							<tr class="hide" id="root_user">
								<td><?php echo vtranslate('LBL_ROOT_USERNAME','Install');?>
<span class="no">*</span></td>
								<td><input type="text" value="" name="db_root_username"></td>
							</tr>
							<tr class="hide" id="root_password">
								<td><?php echo vtranslate('LBL_ROOT_PASSWORD','Install');?>
</td>
								<td><input type="password" value="" name="db_root_password"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-sm-6">
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2"><?php echo vtranslate('LBL_SYSTEM_INFORMATION','Install');?>
</th></tr>
						</thead>
						<tbody>
							<tr>
								<td><?php echo vtranslate('LBL_CURRENCIES','Install');?>
<span class="no">*</span></td>
								<td>
									<select name="currency_name" class="select2" style="width:220px;">
										<?php  $_smarty_tpl->tpl_vars['CURRENCY_INFO'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['CURRENCY_INFO']->_loop = false;
 $_smarty_tpl->tpl_vars['CURRENCY_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['CURRENCIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['CURRENCY_INFO']->key => $_smarty_tpl->tpl_vars['CURRENCY_INFO']->value){
$_smarty_tpl->tpl_vars['CURRENCY_INFO']->_loop = true;
 $_smarty_tpl->tpl_vars['CURRENCY_NAME']->value = $_smarty_tpl->tpl_vars['CURRENCY_INFO']->key;
?>
											<option value="<?php echo $_smarty_tpl->tpl_vars['CURRENCY_NAME']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['CURRENCY_NAME']->value=='USA, Dollars'){?> selected <?php }?>><?php echo $_smarty_tpl->tpl_vars['CURRENCY_NAME']->value;?>
 (<?php echo $_smarty_tpl->tpl_vars['CURRENCY_INFO']->value[1];?>
)</option>
										<?php } ?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="config-table input-table">
						<thead>
							<tr><th colspan="2"><?php echo vtranslate('LBL_ADMIN_INFORMATION','Install');?>
</th></tr>
						</thead>
						<tbody>
							<tr>
								<td><?php echo vtranslate('LBL_USERNAME','Install');?>
</td>
								<td>admin<input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['ADMIN_NAME']->value;?>
" value="admin" /></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_PASSWORD','Install');?>
<span class="no">*</span></td>
								<td><input type="password" value="<?php echo $_smarty_tpl->tpl_vars['ADMIN_PASSWORD']->value;?>
" name="password" /></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_RETYPE_PASSWORD','Install');?>
 <span class="no">*</span></td>
								<td><input type="password" value="<?php echo $_smarty_tpl->tpl_vars['ADMIN_PASSWORD']->value;?>
" name="retype_password" />
									<span id="passwordError" class="no"></span></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('First Name','Install');?>
</td>
								<td><input type="text" value="" name="firstname" /></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('Last Name','Install');?>
 <span class="no">*</span></td>
								<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['ADMIN_LASTNAME']->value;?>
" name="lastname" /></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_EMAIL','Install');?>
 <span class="no">*</span></td>
								<td><input type="text" value="<?php echo $_smarty_tpl->tpl_vars['ADMIN_EMAIL']->value;?>
" name="admin_email"></td>
							</tr>
							<tr>
								<td><?php echo vtranslate('LBL_DATE_FORMAT','Install');?>
 <span class="no">*</span></td>
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
									<?php echo vtranslate('LBL_TIME_ZONE','Install');?>
 <span class="no">*</span>
								</td>
								<td>
									<select class="select2" name="timezone" style="width:300px;">
										<?php  $_smarty_tpl->tpl_vars['TIMEZONE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['TIMEZONE']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['TIMEZONES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['TIMEZONE']->key => $_smarty_tpl->tpl_vars['TIMEZONE']->value){
$_smarty_tpl->tpl_vars['TIMEZONE']->_loop = true;
?>
											<option value="<?php echo $_smarty_tpl->tpl_vars['TIMEZONE']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['TIMEZONE']->value=='America/Los_Angeles'){?>selected<?php }?>><?php echo vtranslate($_smarty_tpl->tpl_vars['TIMEZONE']->value,'Users');?>
</option>
										<?php } ?>
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
						<input type="button" class="btn btn-default" value="<?php echo vtranslate('LBL_BACK','Install');?>
" name="back"/>
						<input type="button" class="btn btn-large btn-primary" value="<?php echo vtranslate('LBL_NEXT','Install');?>
" name="step5"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</form><?php }} ?>