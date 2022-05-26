<?php /* Smarty version Smarty-3.1.7, created on 2022-05-25 17:46:27
         compiled from "/var/www/html/includes/runtime/../../layouts/v7/modules/Vtiger/JSResources.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2028494443628e5d6308eaf2-98516080%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'aa7699830e465b0bcf4a6bde4404889d8eb3e837' => 
    array (
      0 => '/var/www/html/includes/runtime/../../layouts/v7/modules/Vtiger/JSResources.tpl',
      1 => 1588595432,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2028494443628e5d6308eaf2-98516080',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SCRIPTS' => 0,
    'jsModel' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_628e5d6309972',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_628e5d6309972')) {function content_628e5d6309972($_smarty_tpl) {?>
<script type="text/javascript" src="layouts/v7/lib/jquery/purl.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/select2/select2.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.class.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.js"></script><script type="text/javascript" src="layouts/v7/lib/todc/js/bootstrap.min.js"></script><script type="text/javascript" src="libraries/jquery/jstorage.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-validation/jquery.validate.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.slimscroll.min.js"></script><script type="text/javascript" src="libraries/jquery/jquery.ba-outside-events.min.js"></script><script type="text/javascript" src="libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js"></script><script type="text/javascript" src="libraries/jquery/multiplefileupload/jquery_MultiFile.js"></script><script type="text/javascript" src="resources/jquery.additions.js"></script><script type="text/javascript" src="layouts/v7/lib/bootstrap-notify/bootstrap-notify.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/websockets/reconnecting-websocket.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery-play-sound/jquery.playSound.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mousewheel.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/autoComplete/jquery.textcomplete.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.js"></script><script type="text/javascript" src="libraries/jquery/jquery-visibility.min.js"></script><script type="text/javascript" src="layouts/v7/lib/momentjs/moment.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/daterangepicker/moment.min.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/daterangepicker/jquery.daterangepicker.js"></script><script type="text/javascript" src="layouts/v7/lib/jquery/jquery.timeago.js"></script><script type="text/javascript" src="libraries/jquery/ckeditor/ckeditor.js"></script><script type="text/javascript" src="libraries/jquery/ckeditor/adapters/jquery.js"></script><script type='text/javascript' src='layouts/v7/lib/anchorme_js/anchorme.min.js'></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/Class.js');?>
"></script><script type='text/javascript' src="<?php echo vresource_url('layouts/v7/resources/helper.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/resources/application.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/Utils.js');?>
"></script><script type='text/javascript' src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/validation.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/lib/bootbox/bootbox.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/Base.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/Vtiger.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Calendar/resources/TaskManagement.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Import/resources/Import.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Emails/resources/EmailPreview.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/Base.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Google/resources/Settings.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Vtiger/resources/CkEditor.js');?>
"></script><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/modules/Documents/resources/Documents.js');?>
"></script><?php  $_smarty_tpl->tpl_vars['jsModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['jsModel']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SCRIPTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['jsModel']->key => $_smarty_tpl->tpl_vars['jsModel']->value){
$_smarty_tpl->tpl_vars['jsModel']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['jsModel']->key;
?><script type="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getType();?>
" src="<?php echo vresource_url($_smarty_tpl->tpl_vars['jsModel']->value->getSrc());?>
"></script><?php } ?><script type="text/javascript" src="<?php echo vresource_url('layouts/v7/resources/v7_client_compat.js');?>
"></script><!-- Added in the end since it should be after less file loaded --><script type="text/javascript" src="libraries/bootstrap/js/less.min.js"></script><!-- Enable tracking pageload time --><script type="text/javascript">var _REQSTARTTIME = "<?php echo $_SERVER['REQUEST_TIME'];?>
";jQuery(document).ready(function() { window._PAGEREADYAT = new Date(); });
		jQuery(window).load(function() {
			window._PAGELOADAT = new Date();
			window._PAGELOADREQSENT = false;
			// Transmit the information to server about page render time now.
			if (typeof _REQSTARTTIME != 'undefined') {
				// Work with time converting it to GMT (assuming _REQSTARTTIME set by server is also in GMT)
				var _PAGEREADYTIME = _PAGEREADYAT.getTime() / 1000.0; // seconds
				var _PAGELOADTIME = _PAGELOADAT.getTime() / 1000.0;    // seconds
				var data = { page_request: _REQSTARTTIME, page_ready: _PAGEREADYTIME, page_load: _PAGELOADTIME };
				data['page_xfer'] = (_PAGELOADTIME - _REQSTARTTIME).toFixed(3);
				data['client_tzoffset']= -1*_PAGELOADAT.getTimezoneOffset()*60;
				data['client_now'] = JSON.parse(JSON.stringify(new Date()));
				if (!window._PAGELOADREQSENT) {
					// To overcome duplicate firing on Chrome
					window._PAGELOADREQSENT = true;
				}
			}
		});</script><?php }} ?>