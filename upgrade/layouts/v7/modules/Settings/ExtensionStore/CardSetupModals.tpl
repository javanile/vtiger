{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

<!-- Setup card detals form start--> 
<div class="modal-dialog setUpCardModal hide">
	<div class="modal-content">
		{assign var=HEADER_TITLE value={vtranslate('LBL_SETUP_CARD', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal setUpCardForm">
			<input type="hidden" name="customerId" value="{$CUSTOMER_PROFILE['id']}" />
			<input type="hidden" name="customerCardId" value="{$CUSTOMER_PROFILE['CustomerCardId']}" />
			<input type="hidden" name="module" value="ExtensionStore" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Basic" />
			<input type="hidden" name="mode" value="updateCardDetails" />
			<div class="modal-body">
				<div class="form-group">
					<span class="control-label col-sm-3 col-xs-3">
						{vtranslate('LBL_CARD_NUMBER', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-5 col-xs-5">
						<input class="col-sm-8 col-xs-8 inputElement" type="text" placeholder="{vtranslate('LBL_CARD_NUMBER_PLACEHOLDER', $QUALIFIED_MODULE)}" name="cardNumber" value="" data-rule-required="true" data-rule-WholeNumber="true"/>
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-3 col-xs-3">
						{vtranslate('LBL_EXPIRY_DATE', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-9 col-xs-9"> 
						<input class="inputElement" style="width: 50px;" placeholder="mm" type="text" name="expMonth" value="" data-rule-required="true" data-mask="99" />
						&nbsp;-&nbsp;
						<input class="inputElement" style="width: 50px;" placeholder="yyyy" type="text" name="expYear" value="" data-rule-required="true" data-mask="9999" />
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-3 col-xs-3">
						{vtranslate('LBL_SECURITY_CODE', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-9 col-xs-9">
						<input class="inputElement" style="width: 50px;" type="text" name="cvccode" value="" data-rule-required="true" data-mask="999"/>
						&nbsp;&nbsp;
						<span class="fa fa-info-circle" id="helpSecurityCode" onmouseover="Settings_ExtensionStore_ExtensionStore_Js.showPopover(this)" data-title="{vtranslate('LBL_WHAT_IS_SECURITY_CODE', $QUALIFIED_MODULE)}" data-content="{vtranslate('LBL_SECURITY_CODE_HELP_CONTENT', $QUALIFIED_MODULE)}" data-position="right"></span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row">
					<div class="col-sm-3 col-xs-3">
						<span class="pull-left"><button class="btn btn-danger" type="button" name="resetButton"><strong>{vtranslate('LBL_RESET', $QUALIFIED_MODULE)}</strong></button></span>
					</div>
					<div class="col-sm-9 col-xs-9">
						<div class="pull-right">
							<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
								<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<button class="btn btn-success saveButton" type="submit" name="saveButton" style="padding: 5px 12px;"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- Setup card detals form end-->

<!-- View card detals start-->
<div class="modal-dialog viewCardInfoModal hide">
	<div class="modal-content">
		{assign var=HEADER_TITLE value={vtranslate('Card Information', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<div class="modal-body">
			<div class="row marginBottom10px">
				<div class="col-sm-3 col-xs-3">
					{vtranslate('LBL_CARD_NUMBER', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm-4 col-xs-4 cardNumber">{$CUSTOMER_CARD_INFO['number']}</div>
			</div>
			<div class="row marginBottom10px">
				<div class="col-sm-3 col-xs-3">
					{vtranslate('LBL_EXPIRY_DATE', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm-4 col-xs-4 expiryDate">{$CUSTOMER_CARD_INFO['expmonth']}&nbsp;-&nbsp;{$CUSTOMER_CARD_INFO['expyear']}</div>
			</div>
			<div class="row marginBottom10px">
				<div class="col-sm-3 col-xs-3 securityCode">
					{vtranslate('LBL_SECURITY_CODE', $QUALIFIED_MODULE)}
				</div>
				<div class="col-sm-4 col-xs-4">***</div>
			</div>
		</div>
		<div class="modal-footer">
			<div class="row-fluid">
				<div class="pull-right">
					<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
						<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</div>
					<button class="btn btn-success updateBtn">{vtranslate('Update', $MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- view card information end -->

<!-- Signup form start-->
<div class="modal-dialog signUpAccount hide">
	<div class="modal-content">
		{assign var=HEADER_TITLE value={vtranslate('LBL_SIGN_UP_FOR_FREE', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal signUpForm">
			<input type="hidden" name="module" value="ExtensionStore" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Basic" />
			<input type="hidden" name="userAction" value="signup" />
			<input type="hidden" name="mode" value="registerAccount" />
			<div class="modal-body col-md-offset-2">
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_EMAIL_ADDRESS', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="text" name="emailAddress" data-rule-required="true" data-rule-email="true"/>
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_FIRST_NAME', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="text" name="firstName" data-rule-required="true" />
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_LAST_NAME', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="text" name="lastName" data-rule-required="true" />
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_COMPANY_NAME', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="text" name="companyName" data-rule-required="true" />
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="password" name="password" data-rule-required="true" />
					</div>
				</div>
				<div class="form-group">
					<span class="control-label col-sm-4">
						{vtranslate('LBL_CONFIRM_PASSWORD', $QUALIFIED_MODULE)}
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="password" name="confirmPassword" data-rule-required="true"/>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row-fluid">
					<span class="col-sm-6">&nbsp;
					</span>
					<span class="col-sm-6">
						<div class="pull-right">
							<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
								<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<button class="btn btn-success" name="saveButton"><strong>{vtranslate('LBL_REGISTER', $QUALIFIED_MODULE)}</strong></button>
						</div>
					</span>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- Signup form end-->

<!-- Login form start-->
<div class="modal-dialog loginAccount hide">
	<div class="modal-content">
		{assign var=HEADER_TITLE value={vtranslate('LBL_MARKETPLACE_LOGIN', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal loginForm">
			<input type="hidden" name="module" value="ExtensionStore" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Basic" />
			<input type="hidden" name="userAction" value="login" />
			<input type="hidden" name="mode" value="registerAccount" />
			<div class="modal-body col-md-offset-2">
				<div class="form-group">
					<span class="control-label col-sm-3 fieldLabel">
						{vtranslate('LBL_EMAIL', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-5">
						{if $REGISTRATION_STATUS}
							<input class="inputElement" type="hidden" name="emailAddress" value="{$USER_NAME}" />
							<span class="control-label"><span class="pull-left">{$USER_NAME}</span></span>
							{else}
							<input class="inputElement" type="text" name="emailAddress" data-rule-required="true" data-rule-email="true" />
						{/if}
					</div>
				</div>
				<div class="form-group">
					<span class="control-label fieldLabel col-sm-3">
						{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="password" name="password" data-rule-required="true" />
						<br>
						<br>
						<label style="font-weight:normal;"><input type="checkbox" name="savePassword" />&nbsp;&nbsp;{vtranslate('LBL_REMEMBER_ME', $QUALIFIED_MODULE)}</label>
						<br>
						<br>
						<a href="#" id="forgotPasswordLink" style="color: #15c !important">{vtranslate('LBL_FORGOT_PASSWORD', $QUALIFIED_MODULE)} ?</a>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row-fluid">
					<span class="col-sm-8">
						{if !$REGISTRATION_STATUS}
							<a class="pull-left" href="#" name="signUp">{vtranslate('LBL_CREATE_AN_ACCOUNT', $QUALIFIED_MODULE)}</a>
						{else}&nbsp;
						{/if}
					</span>
					<span class="col-sm-4">
						<div class="pull-right">
							<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
								<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<button class="btn btn-success" name="saveButton" type="submit"><strong>{vtranslate('LBL_LOGIN', $QUALIFIED_MODULE)}</strong></button>
						</div>
					</span>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- Login form end -->

<!-- forgot password form -->
<div class="modal-dialog forgotPasswordModal hide">
	<div class="modal-content">
		{assign var=HEADER_TITLE value={vtranslate('LBL_FORGOT_PASSWORD', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal forgotPassword" method="POST">
			<input type="hidden" name="module" value="ExtensionStore" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="Basic" />
			<input type="hidden" name="mode" value="forgotPassword" />
			<div class="modal-body col-md-offset-1">
				<div class="form-group">
					<span class="control-label col-sm-5">
						{vtranslate('LBL_ENTER_REGISTERED_EMAIL', $QUALIFIED_MODULE)}
						<span class="redColor">*</span>
					</span>
					<div class="controls col-sm-5">
						<input class="inputElement" type="text" name="emailAddress" data-rule-required="true" data-rule-email="true" /></div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="row-fluid">
					<div class="pull-right">
						<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
							<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
						<button class="btn btn-success okBtn" type="submit" style="padding: 5px 12px;">{vtranslate('LBL_OK', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<!-- forgot password form end -->
{if $LOADER_REQUIRED}
	<div class="modal extensionLoader hide">
		<div class="modal-header contentsBackground">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>{vtranslate('LBL_INSTALL_EXTENSION_LOADER', $QUALIFIED_MODULE)}</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<p>{vtranslate('LBL_TO_CONTINUE_USING_EXTENSION_STORE', $QUALIFIED_MODULE)}<a href="https://marketplace.vtiger.com/loaderfiles/{$LOADER_INFO['loader_file']}">{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</a>{vtranslate('LBL_COMPATIABLE_EXTENSION', $QUALIFIED_MODULE)}</p>
			</div>
			<div class="row-fluid">
				<p>{vtranslate('LBL_MORE_DETAILS_ON_INSTALLATION', $QUALIFIED_MODULE)}<a onclick=window.open("http://community.vtiger.com/help/vtigercrm/php/extension-loader.html")>{vtranslate('LBL_READ_HERE', $QUALIFIED_MODULE)}</a></p>
			</div>
		</div>
		<div class="modal-footer">
			<div class="row-fluid">
				<div class="pull-right">
					<div class="pull-right cancelLinkContainer" style="margin-top:5px;">
						<button class="btn btn-success" data-dismiss="modal">{vtranslate('LBL_OK', $QUALIFIED_MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}