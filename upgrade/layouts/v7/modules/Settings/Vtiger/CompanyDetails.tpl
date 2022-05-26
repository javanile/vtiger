{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Vtiger/views/CompanyDetails.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}

{strip}

	<div class=" col-lg-12 col-md-12 col-sm-12">
		<input type="hidden" id="supportedImageFormats" value='{ZEND_JSON::encode(Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)}' />
		{*<div class="blockData" >
		<h3>{vtranslate('LBL_COMPANY_DETAILS', $QUALIFIED_MODULE)}</h3>
		{if $DESCRIPTION}<span style="font-size:12px;color: black;"> - &nbsp;{vtranslate({$DESCRIPTION}, $QUALIFIED_MODULE)}</span>{/if}
		</div>
		<hr>*}
		<div class="clearfix">
			<div class="btn-group pull-right editbutton-container">
				<button id="updateCompanyDetails" class="btn btn-default ">{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
			</div>
		</div>
		{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
		<div id="CompanyDetailsContainer" class=" detailViewContainer {if !empty($ERROR_MESSAGE)}hide{/if}" >
			<div class="block">
				<div>
					<h4>{vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							<tr>
								<td class="fieldLabel">
									<div class="companyLogo">
										{if $MODULE_MODEL->getLogoPath()}
											<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" style="max-width:700px;"/>
										{else}
											{vtranslate('LBL_NO_LOGO_EDIT_AND_UPLOAD', $QUALIFIED_MODULE)}
										{/if}
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<br>
			<div class="block">
				<div>
					<h4>{vtranslate('LBL_COMPANY_INFORMATION',$QUALIFIED_MODULE)}</h4>
				</div>
				<hr>
				<div class="blockData">
					<table class="table detailview-table no-border">
						<tbody>
							{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
								{if $FIELD neq 'logoname' && $FIELD neq 'logo' }
									<tr>
										<td class="{$WIDTHTYPE} fieldLabel" style="width:25%"><label >{vtranslate($FIELD,$QUALIFIED_MODULE)}</label></td>
										<td class="{$WIDTHTYPE}" style="word-wrap:break-word;">
											{if $FIELD eq 'address'} {decode_html($MODULE_MODEL->get($FIELD))|nl2br} {else} {decode_html($MODULE_MODEL->get($FIELD))} {/if}
										</td>
									</tr>
								{/if}
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>


		<div class="editViewContainer">
			<form class="form-horizontal {if empty($ERROR_MESSAGE)}hide{/if}" id="updateCompanyDetailsForm" method="post" action="index.php" enctype="multipart/form-data">
				<input type="hidden" name="module" value="Vtiger" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="CompanyDetailsSave" />
				<div class="form-group companydetailsedit">
					<label class="col-sm-2 fieldLabel control-label"> {vtranslate('LBL_COMPANY_LOGO',$QUALIFIED_MODULE)}</label>
					<div class="fieldValue col-sm-5" >
						<div class="company-logo-content">
							<img src="{$MODULE_MODEL->getLogoPath()}" class="alignMiddle" style="max-width:700px;"/>
							<br><hr>
							<input type="file" name="logo" id="logoFile" />
						</div>
						<br>
						<div class="alert alert-info" >
							{vtranslate('LBL_LOGO_RECOMMENDED_MESSAGE',$QUALIFIED_MODULE)}
						</div>
					</div>
				</div>

				{foreach from=$MODULE_MODEL->getFields() item=FIELD_TYPE key=FIELD}
					{if $FIELD neq 'logoname' && $FIELD neq 'logo' }
						<div class="form-group companydetailsedit">
							<label class="col-sm-2 fieldLabel control-label ">
								{vtranslate($FIELD,$QUALIFIED_MODULE)}{if $FIELD eq 'organizationname'}&nbsp;<span class="redColor">*</span>{/if}
							</label>
							<div class="fieldValue col-sm-5">
								{if $FIELD eq 'address'}
									<textarea class="form-control col-sm-6 resize-vertical" rows="2" name="{$FIELD}">{$MODULE_MODEL->get($FIELD)}</textarea>
								{else if $FIELD eq 'website'}
									<input type="text" class="inputElement" data-rule-url="true" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
								{else}
									<input type="text" {if $FIELD eq 'organizationname'} data-rule-required="true" {/if} class="inputElement" name="{$FIELD}" value="{$MODULE_MODEL->get($FIELD)}"/>
								{/if}
							</div>
						</div>
					{/if}
				{/foreach}

				<div class="modal-overlay-footer clearfix">
					<div class="row clearfix">
						<div class="textAlignCenter col-lg-12 col-md-12 col-sm-12">
							<button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
							<a class="cancelLink" data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
					</div>
				</div>
			</form>
		</div>
</div>
</div>
{/strip}
