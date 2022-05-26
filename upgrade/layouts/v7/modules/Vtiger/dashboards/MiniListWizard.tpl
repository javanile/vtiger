{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
{if $WIZARD_STEP eq 'step1'}
	<div id="minilistWizardContainer" class='modelContainer modal-dialog'>
		<div class="modal-content"> 
			{assign var=HEADER_TITLE value={vtranslate('LBL_MINI_LIST', $MODULE)}|cat:" "|cat:{vtranslate($MODULE, $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<form class="form-horizontal" method="post" action="javascript:;">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="MassSave" />

				<table class="table no-border">
					<tbody>
						<tr>
							<td class="col-lg-1"></td>
							<td class="fieldLabel col-lg-4"><label class="pull-right">{'LBL_SELECT_MODULE'|vtranslate}</label></td>
							<td class="fieldValue col-lg-5">
								<select name="module" style="width: 100%">
									<option></option>
									{assign var=TRANSLATED_MODULES_NAMES value=[]}
									{foreach from=$MODULES item=MODULE_MODEL key=MODULE_NAME}
										{$TRANSLATED_MODULE_NAMES.$MODULE_NAME = {vtranslate($MODULE_NAME, $MODULE_NAME)}}
										<option value="{$MODULE_NAME}">{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
									{/foreach}
								</select>
							</td>
							<td class="col-lg-4"></td>
						</tr>
						<tr>
							<td class="col-lg-1"></td>
							<td class="fieldLabel col-lg-4"><label class="pull-right">{'LBL_FILTER'|vtranslate}</label></td>
							<td class="fieldValue col-lg-5">
								<select name="filterid" style="width: 100%">
									<option></option>
								</select>
							</td>
							<td class="col-lg-4"></td>
						</tr>
						<tr>
							<td class="col-lg-1"></td>
							<td class="fieldLabel col-lg-4"><label class="pull-right">{'LBL_EDIT_FIELDS'|vtranslate}</label></td>
							<td class="fieldValue col-lg-5">
								<select name="fields" size="2" multiple="true" style="width: 100%">
									<option></option>
								</select>
							</td>
							<td class="col-lg-4"></td>
						</tr>
				   </tbody>
				   <input type="hidden" id="translatedModuleNames" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($TRANSLATED_MODULE_NAMES))}'>
				</table>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{elseif $WIZARD_STEP eq 'step2'}
	<option></option>
	{foreach from=$ALLFILTERS item=FILTERS key=FILTERGROUP}
		<optgroup label="{$FILTERGROUP}">
			{foreach from=$FILTERS item=FILTER key=FILTERNAME}
				<option value="{$FILTER->getId()}">{$FILTER->get('viewname')}</option>
			{/foreach}
		</optgroup>
	{/foreach}
{elseif $WIZARD_STEP eq 'step3'}
	<option></option>
	{foreach from=$LIST_VIEW_CONTROLLER->getListViewHeaderFields() item=FIELD key=FIELD_NAME}
		<option value="{$FIELD_NAME}">{vtranslate($FIELD->getFieldLabelKey(),$SELECTED_MODULE)}</option>
	{/foreach}
{/if}
{/strip}