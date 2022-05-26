{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="editViewPageDiv mailBoxEditDiv viewContent">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<input type="hidden" id="create" value="{$CREATE}" />
			<input type="hidden" id="recordId" value="{$RECORD_ID}" />
			<input type="hidden" id="step" value="{$STEP}" />
			<h4>
				{if $CREATE eq 'new'}
					{vtranslate('LBL_ADDING_NEW_MAILBOX', $QUALIFIED_MODULE)}
				{else}
					{vtranslate('LBL_EDIT_MAILBOX', $QUALIFIED_MODULE)}
				{/if}
			</h4>
			<hr>
			<div class="editViewContainer" style="padding-left: 2%;padding-right: 2%">
				<div class="row">
					{assign var=BREADCRUMB_LABELS value = ["step1" => "MAILBOX_DETAILS", "step2" => "SELECT_FOLDERS"]}
					{if $CREATE eq 'new'}
						{append var=BREADCRUMB_LABELS index=step3 value=ADD_RULES}
					{/if}
					{include file="BreadCrumbs.tpl"|vtemplate_path:$QUALIFIED_MODULE BREADCRUMB_LABELS=$BREADCRUMB_LABELS MODULE=$QUALIFIED_MODULE}
				</div>
				<div class="clearfix"></div>
{/strip}