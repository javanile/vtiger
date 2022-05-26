{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{assign var=APP_ARRAY value=Vtiger_MenuStructure_Model::getAppMenuList()}
	<div class="modal-dialog modal-lg addModuleContainer">
		<div class="modal-content">
			<input id="appname" type="hidden" name="appname" value="{$SELECTED_APP_NAME}" />
			<div class="modal-header" >
				<div class="clearfix">
					<div class="pull-right">
						<button type="button" class="close" aria-label="Close" data-dismiss="modal" style="color: inherit;">
							<span aria-hidden="true" class='fa fa-close'></span>
						</button>
					</div>
					<div class="btn-group">
						{assign var=APP_SELECTED_LABEL value="LBL_SELECT_`$SELECTED_APP_NAME`_MODULES"}
						<h4 class="pull-left textOverflowEllipsis" style="word-break: break-all;max-width: 95%;">{vtranslate($APP_SELECTED_LABEL, $QUALIFIED_MODULE)}&nbsp;&nbsp;</h4>  
					</div>
				</div>
			</div>
			<div class="modal-body form-horizontal">
				{foreach item=APP_NAME from=$APP_ARRAY}
					{assign var=HIDDEN_MODULES value=Settings_MenuEditor_Module_Model::getHiddenModulesForApp($APP_NAME)}
					<div class="row modulesContainer {if $APP_NAME neq $SELECTED_APP_NAME} hide {/if}" data-appname="{$APP_NAME}">
						<div class="col-lg-12 col-md-12 col-sm-12">
							{if count($HIDDEN_MODULES) gt 0}
								{foreach item=MODULE_NAME from=$HIDDEN_MODULES}
									<span class="btn-group" style="margin-bottom: 10px; margin-left: 25px; margin-right: -15px;">
										<buttton class="btn addButton btn-default module-buttons addModule" data-module="{$MODULE_NAME}" style="text-transform: inherit;margin-right:15px">{vtranslate($MODULE_NAME, $MODULE_NAME)}&nbsp;&nbsp;
											<i class="fa fa-plus"></i>
										</buttton>
									</span>
								{/foreach}
							{else}
								<h5>
									<center>
										{vtranslate('LBL_NO', $QUALIFIED_MODULE)} {vtranslate('LBL_MODULES', $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND', $QUALIFIED_MODULE)}.</h4>
									</center>
								</h5>
							{/if}
						</div>
					</div>
				{/foreach}
			</div>
			{include file="ModalFooter.tpl"|vtemplate_path:$QUALIFIED_MODULE}
		</div>
	</div>
{/strip}