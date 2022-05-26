{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="col-lg-6 col-md-6 col-sm-6">
		<div class="record-header clearfix">
			<div class="hidden-sm hidden-xs recordImage bgvendors app-{$SELECTED_MENU_CATEGORY}">
				<div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
			</div>
			<div class="recordBasicInfo">
				<div class="info-row">
					<h4>
						<span class="recordLabel pushDown" title="{$RECORD->getName()}">
							{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
								{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
								{if $FIELD_MODEL->getPermissions()}
									<span class="{$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
								{/if}
							{/foreach}
						</span>
					</h4>
				</div>
				{include file="DetailViewHeaderFieldsView.tpl"|vtemplate_path:$MODULE}

				{*
				<div class="info-row row">
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField('website')}
					<div class="col-lg-7 fieldLabel">
						<span class="website" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('website')}">
							{$RECORD->getDisplayValue("website")}
						</span>
					</div>
				</div>

				<div class="info-row row">
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField('email')}
					<div class="col-lg-7 fieldLabel">
						<span class="email" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('email')}">
							{$RECORD->getDisplayValue("email")}
						</span>
					</div>
				</div>

				<div class="info-row row">
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField('phone')}
					<div class="col-lg-7 fieldLabel">
						<span class="phone" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('phone')}">
							{$RECORD->getDisplayValue("phone")}
						</span>
					</div>
				</div>
				*}
			</div>
		</div>
	</div>
{/strip}