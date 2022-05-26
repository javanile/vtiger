<?php /* Smarty version Smarty-3.1.7, created on 2022-05-25 17:46:33
         compiled from "/var/www/html/includes/runtime/../../layouts/v7/modules/Install/Step3.tpl" */ ?>
<?php /*%%SmartyHeaderCode:106193824628e5d69aad855-33343502%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '62d0f4ed8b0911d3ef4fc47193095cb67655a7e8' => 
    array (
      0 => '/var/www/html/includes/runtime/../../layouts/v7/modules/Install/Step3.tpl',
      1 => 1588595432,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '106193824628e5d69aad855-33343502',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SYSTEM_PREINSTALL_PARAMS' => 0,
    'CONFIG_NAME' => 0,
    'INFO' => 0,
    'PHP_INI_CURRENT_SETTINGS' => 0,
    'DIRECTIVE' => 0,
    'PHP_INI_RECOMMENDED_SETTINGS' => 0,
    'VALUE' => 0,
    'FAILED_FILE_PERMISSIONS' => 0,
    'FILE_NAME' => 0,
    'FILE_PATH' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_628e5d69ae1a9',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_628e5d69ae1a9')) {function content_628e5d69ae1a9($_smarty_tpl) {?>

<form class="form-horizontal" name="step3" method="get" action="index.php">
	<input type=hidden name="module" value="Install" />
	<input type=hidden name="view" value="Index" />
	<input type=hidden name="mode" value="Step4" />

	<div class="main-container">
		<div class="inner-container">
			<div class="row">
				<div class="col-sm-10">
					<h4><?php echo vtranslate('LBL_INSTALL_PREREQUISITES','Install');?>
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
			<div class="row">
				<div class="row offset2">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<div class=" pull-right">
							<div class="button-container">
								<a href ="#">
									<input type="button" class="btn btn-default" value="<?php echo vtranslate('LBL_RECHECK','Install');?>
" id='recheck'/>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="row offset2">
					<div class="col-sm-2"></div>
					<div class="col-sm-8">
						<table class="config-table">
							<tr>
								<th><?php echo vtranslate('LBL_PHP_CONFIGURATION','Install');?>
</th>
								<th><?php echo vtranslate('LBL_REQUIRED_VALUE','Install');?>
</th>
								<th><?php echo vtranslate('LBL_PRESENT_VALUE','Install');?>
</th>
							</tr>
							<?php  $_smarty_tpl->tpl_vars['INFO'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['INFO']->_loop = false;
 $_smarty_tpl->tpl_vars['CONFIG_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SYSTEM_PREINSTALL_PARAMS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['INFO']->key => $_smarty_tpl->tpl_vars['INFO']->value){
$_smarty_tpl->tpl_vars['INFO']->_loop = true;
 $_smarty_tpl->tpl_vars['CONFIG_NAME']->value = $_smarty_tpl->tpl_vars['INFO']->key;
?>
								<tr>
									<td><?php echo vtranslate($_smarty_tpl->tpl_vars['CONFIG_NAME']->value,'Install');?>
</td>
									<td>
										<?php if ($_smarty_tpl->tpl_vars['INFO']->value[1]==1){?> 
											<?php echo vtranslate('LBL_YES','Install');?>
 
										<?php }else{ ?> 
											<?php echo $_smarty_tpl->tpl_vars['INFO']->value[1];?>
 
										<?php }?>
									</td>
									<td <?php if ($_smarty_tpl->tpl_vars['INFO']->value[2]==false){?> class="no" >
											<?php if (!isset($_smarty_tpl->tpl_vars['CONFIG_NAME'])) $_smarty_tpl->tpl_vars['CONFIG_NAME'] = new Smarty_Variable(null);if ($_smarty_tpl->tpl_vars['CONFIG_NAME']->value = 'LBL_PHP_VERSION'){?>
												<?php echo $_smarty_tpl->tpl_vars['INFO']->value[0];?>

											<?php }else{ ?>
												<?php echo vtranslate('LBL_NO','Install');?>

											<?php }?>
										<?php }elseif(($_smarty_tpl->tpl_vars['INFO']->value[2]==true&&$_smarty_tpl->tpl_vars['INFO']->value[1]===true)){?> > 
											<?php echo vtranslate('LBL_YES','Install');?>
 
										<?php }else{ ?> > 
											<?php echo $_smarty_tpl->tpl_vars['INFO']->value[0];?>
 
										<?php }?>
									</td>
								</tr>
							<?php } ?>
						</table>
						<?php if ($_smarty_tpl->tpl_vars['PHP_INI_CURRENT_SETTINGS']->value){?>
							<table class="config-table">
								<tr>
									<th colspan="3"><?php echo vtranslate('LBL_PHP_RECOMMENDED_SETTINGS','Install');?>
</th>
								</tr>
								<?php  $_smarty_tpl->tpl_vars['VALUE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['VALUE']->_loop = false;
 $_smarty_tpl->tpl_vars['DIRECTIVE'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PHP_INI_CURRENT_SETTINGS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['VALUE']->key => $_smarty_tpl->tpl_vars['VALUE']->value){
$_smarty_tpl->tpl_vars['VALUE']->_loop = true;
 $_smarty_tpl->tpl_vars['DIRECTIVE']->value = $_smarty_tpl->tpl_vars['VALUE']->key;
?>
									<tr>
										<td><?php echo $_smarty_tpl->tpl_vars['DIRECTIVE']->value;?>
</td><td><?php echo $_smarty_tpl->tpl_vars['PHP_INI_RECOMMENDED_SETTINGS']->value[$_smarty_tpl->tpl_vars['DIRECTIVE']->value];?>
</td><td class="no"><?php echo $_smarty_tpl->tpl_vars['VALUE']->value;?>
</td>
									</tr>
								<?php } ?>
							</table>
						<?php }?>
						<?php if ($_smarty_tpl->tpl_vars['FAILED_FILE_PERMISSIONS']->value){?>
							<table class="config-table">
								<tr>
									<th colspan="2"><?php echo vtranslate('LBL_READ_WRITE_ACCESS','Install');?>
</th>
								</tr>
								<?php  $_smarty_tpl->tpl_vars['FILE_PATH'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FILE_PATH']->_loop = false;
 $_smarty_tpl->tpl_vars['FILE_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['FAILED_FILE_PERMISSIONS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FILE_PATH']->key => $_smarty_tpl->tpl_vars['FILE_PATH']->value){
$_smarty_tpl->tpl_vars['FILE_PATH']->_loop = true;
 $_smarty_tpl->tpl_vars['FILE_NAME']->value = $_smarty_tpl->tpl_vars['FILE_PATH']->key;
?>
									<tr>
										<td nowrap><?php echo $_smarty_tpl->tpl_vars['FILE_NAME']->value;?>
 (<?php echo str_replace("./",'',$_smarty_tpl->tpl_vars['FILE_PATH']->value);?>
)</td><td class="no"><?php echo vtranslate('LBL_NO','Install');?>
</td>
									</tr>
								<?php } ?>
							</table>
						<?php }?>
					</div>
				</div>
			</div>
			<div class="row offset2">
				<div class="col-sm-2"></div>
				<div class="col-sm-8">
					<div class="button-container">
						<input type="button" class="btn btn-default" value="<?php echo vtranslate('LBL_BACK','Install');?>
" name="back"/>
						<input type="button" class="btn btn-large btn-primary" value="<?php echo vtranslate('LBL_NEXT','Install');?>
" name="step4"/>
					</div>
				</div>
			</div>
		</div>
	</div>
</form><?php }} ?>