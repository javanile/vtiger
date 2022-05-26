{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}

{strip}
	<div class="modal-dialog createFieldModal modelContainer {if !$IS_FIELD_EDIT_MODE}hide{/if}">
		{if !$IS_FIELD_EDIT_MODE}
			{assign var=TITLE value={vtranslate('LBL_CREATE_CUSTOM_FIELD', $QUALIFIED_MODULE)}}
		{else}
			{assign var=TITLE value={vtranslate('LBL_EDIT_FIELD', $QUALIFIED_MODULE,vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME))}}
		{/if}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
		<div class="modal-content">
			<form class="form-horizontal createCustomFieldForm">
				<input type="hidden" name="fieldid" value="{$FIELD_MODEL->getId()}" />
				<input type="hidden" name="addToBaseTable" value="{$ADD_TO_BASE_TABLE}" />
				<input type="hidden" name="_source" value="{$SOURCE}" />
				<input type="hidden" name="fieldname" value="{$FIELD_MODEL->get('name')}" />
				<input type="hidden" id="headerFieldsCount" value="{$HEADER_FIELDS_COUNT}" />
				<div class="modal-body model-body-scrollenabled">
					{*<!-- To add block lables only for create view, which will be used while double clicking on uitype --> *}
					{if !$IS_FIELD_EDIT_MODE}
						<div class="form-group blockControlGroup hide">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('LBL_SELECT_BLOCK', $QUALIFIED_MODULE)}
							</label>
							<div class="controls col-sm-7">
								<select class="blockList col-sm-9" name="blockid">
									{foreach key=BLOCK_ID item=BLOCK_MODEL from=$ALL_BLOCK_LABELS}
										{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
											{if $BLOCK_MODEL->get('label') == 'LBL_ITEM_DETAILS' && in_array($SELECTED_MODULE_NAME, getInventoryModules())}
												{continue}
											{/if}
											<option value="{$BLOCK_ID}" data-label="{$BLOCK_MODEL->get('label')}">{vtranslate($BLOCK_MODEL->get('label'), $SELECTED_MODULE_NAME)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
						</div> 
					{/if}
					<div class="form-group">
						<label class="control-label fieldLabel col-sm-5">
							{vtranslate('LBL_SELECT_FIELD_TYPE', $QUALIFIED_MODULE)}
						</label>
						<div class="controls col-sm-7">
							<select class="fieldTypesList col-sm-9" name="fieldType" {if $IS_FIELD_EDIT_MODE} disabled="disabled"{/if}>
								{foreach item=FIELD_TYPE from=$ADD_SUPPORTED_FIELD_TYPES}
									{if !$IS_FIELD_EDIT_MODE and $FIELD_TYPE eq 'Relation'} {continue}{/if}
									<option value="{$FIELD_TYPE}" 
											{if ($FIELD_MODEL->getFieldDataTypeLabel() eq $FIELD_TYPE)}selected='selected'{/if}
											{foreach key=TYPE_INFO item=TYPE_INFO_VALUE from=$FIELD_TYPE_INFO[$FIELD_TYPE]}
												data-{$TYPE_INFO}="{$TYPE_INFO_VALUE}"
											{/foreach}>
										{vtranslate($FIELD_TYPE, $QUALIFIED_MODULE)}
									</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label fieldLabel col-sm-5">
							{vtranslate('LBL_LABEL_NAME', $QUALIFIED_MODULE)}
							&nbsp;<span class="redColor">*</span>
						</label>
						<div class="controls col-sm-7">
							<input type="text" class='inputElement col-sm-9' maxlength="50" {if $IS_FIELD_EDIT_MODE}disabled="disabled"{/if} name="fieldLabel" value="{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}" data-rule-required='true' style='width: 75%' />
						</div>
					</div>
					{if !$IS_FIELD_EDIT_MODE}
						<div class="form-group supportedType lengthsupported">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('LBL_LENGTH', $QUALIFIED_MODULE)}
								&nbsp;<span class="redColor">*</span>
							</label>
							<div class="controls col-sm-7">
								<input type="text" name="fieldLength" class="inputElement" value="" data-rule-required='true' 
									data-rule-positive="true" data-rule-WholeNumber='true' data-rule-illegal='true' style='width: 75%'/>
							</div>
						</div>
						<div class="form-group supportedType decimalsupported hide">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('LBL_DECIMALS', $QUALIFIED_MODULE)}
								&nbsp;<span class="redColor">*</span>
							</label>
							<div class="controls col-sm-7">
								<input type="text" name="decimal" class="inputElement" value="" data-rule-required='true' style='width: 75%'/>
							</div>
						</div>
						<div class="form-group supportedType preDefinedValueExists hide">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('LBL_PICKLIST_VALUES', $QUALIFIED_MODULE)}
								&nbsp;<span class="redColor">*</span>
							</label>
							<div class="controls col-sm-7">
								<input type="text" id="picklistUi" class="col-sm-9 select2" name="pickListValues"
									placeholder="{vtranslate('LBL_ENTER_PICKLIST_VALUES', $QUALIFIED_MODULE)}" data-rule-required='true'
									data-rule-picklist='true'/>
							</div>
						</div>
						<div class="form-group supportedType picklistOption hide">
							<label class="control-label fieldLabel col-sm-5">
								&nbsp;
							</label>
							<div class="controls col-sm-7">
								<div class="checkbox row" style="margin-left: 5px;">
									<span class="col-sm-1"><input type="checkbox" name="isRoleBasedPickList" value="1" ></span>
									<span style="margin-left: -10px;">{vtranslate('LBL_ROLE_BASED_PICKLIST',$QUALIFIED_MODULE)}</span>
								</div>
							</div>
						</div>
						<div class="form-group supportedType relationModules hide">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)}
								&nbsp;<span class="redColor">*</span>
							</label>
							<div class="controls col-sm-7">
								<select class="col-sm-6 relationModule" name="relationmodule[]" multiple data-rule-required='true'>
									{foreach item=RELATION_MODULE_NAME from=$FIELD_TYPE_INFO['Relation']['relationModules']}
										<option value="{$RELATION_MODULE_NAME}">{vtranslate($RELATION_MODULE_NAME,$RELATION_MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					{/if}
					{if $FIELD_MODEL->getFieldDataType() != 'reference'}
						{include file=vtemplate_path('DefaultValueUi.tpl', $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL}
					{/if}
					{if $IS_FIELD_EDIT_MODE}
						<div class="form-group">
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate('LBL_SHOW_FIELD', $QUALIFIED_MODULE)}
							</label>
							<div class="controls col-sm-7">
								<input type="hidden" name="presence" value="1"/>
								<label class="checkbox">
									<input type="checkbox" class ='cursorPointer bootstrap-switch' id="fieldPresence" name="presence" {if $FIELD_MODEL->isViewable()} checked {/if}
										{if $FIELD_MODEL->isActiveOptionDisabled()} readonly="readonly" {/if} {if $FIELD_MODEL->isMandatory()} readonly="readonly" {/if}
										data-on-text="Yes" data-off-text="No" value="{$FIELD_MODEL->get('presence')}"/>
								</label>
							</div>
						</div>
					{else}
						<input type="hidden" name="presence" value="2" />
					{/if}
					<div class="well fieldProperty">
						<div class="properties">
							<div class="row">
								<div class="form-group">
									<label class="control-label fieldLabel col-sm-5">
										{vtranslate('LBL_ENABLE_OR_DISABLE_FIELD_PROP',$QUALIFIED_MODULE)}
									</label>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-sm-7">
									<label class="control-label fieldLabel col-sm-10">
										<i class="fa fa-exclamation-circle"></i> &nbsp; {vtranslate('LBL_MANDATORY_FIELD',$QUALIFIED_MODULE)}
									</label>
									<div class="controls col-sm-2">
										<input type="hidden" name="mandatory" value="O"/>
										<label class="checkbox" style="margin-left: 6%;">
											<input type="checkbox" name="mandatory" class="{if $FIELD_MODEL->isMandatoryOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer{/if}" value="M" {if $FIELD_MODEL->isMandatory()} checked="checked" {/if}
												{if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}/>
										</label>
									</div>
								</div>
								<div class="form-group col-sm-6">
									<label class="control-label fieldLabel col-sm-7">
										<i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}
									</label>
									<div class="controls col-sm-5">
										{if $FIELD_MODEL->isQuickCreateOptionDisabled()}
											<input type="hidden" name="quickcreate" value={$FIELD_MODEL->get('quickcreate')} />
										{else}
											<input type="hidden" name="quickcreate" value="1" />
										{/if}
										{assign var="IS_QUICKCREATE_SUPPORTED" value="{$FIELD_MODEL->getModule()->isQuickCreateSupported()}"}
										<input type="hidden" name="isquickcreatesupported" value="{$IS_QUICKCREATE_SUPPORTED}">
										<label class="checkbox" style="margin-left: 9%;">
											<input type="checkbox" class="{if $FIELD_MODEL->isMandatory() || $FIELD_MODEL->isQuickCreateOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer{/if}" name="quickcreate" value="2" {if ($FIELD_MODEL->get('quickcreate') eq '2' || $FIELD_MODEL->isMandatory()) && $IS_QUICKCREATE_SUPPORTED} checked="checked"{/if}
												{if $FIELD_MODEL->isMandatory() || $FIELD_MODEL->isQuickCreateOptionDisabled() }readonly="readonly"{/if}/>
										</label>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-sm-7">
									<label class="control-label fieldLabel col-sm-10">
										<i class="fa fa-key"></i> &nbsp; {vtranslate('LBL_KEY_FIELD_VIEW',$QUALIFIED_MODULE)}
									</label>
									<div class="controls col-sm-2">
										<input type="hidden" name="summaryfield" value="0"/>
										<label class="checkbox" style="margin-left: 6%;">
											<input type="checkbox" class="{if $FIELD_MODEL->isSummaryFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer{/if}" name="summaryfield" value="1" {if $FIELD_MODEL->get('summaryfield') eq '1'}checked="checked"{/if}
												{if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if} />
										</label>
									</div>
								</div>
								<div class="form-group col-sm-6">
									<label class="control-label fieldLabel col-sm-7">
										<i class="fa fa-flag-o"></i> &nbsp; <span>{vtranslate('LBL_HEADER_FIELD',$QUALIFIED_MODULE)}</span>
									</label>
									<div class="controls col-sm-5">
										<input type="hidden" name="headerfield" value="0"/>
										<label class="checkbox" style="margin-left: 9%;">
											<input type="checkbox" class="{if $FIELD_MODEL->isHeaderFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer{/if}" name="headerfield" value="1" {if $FIELD_MODEL->get('headerfield') eq '1'}checked="checked"{/if}
												{if $FIELD_MODEL->isHeaderFieldOptionDisabled() || $IS_NAME_FIELD}readonly="readonly"{/if} />
										</label>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="form-group col-sm-7">
									<label class="control-label fieldLabel col-sm-10">
										<img src="{vimage_path('MassEdit.png')}" height=14 width=14/> &nbsp; {vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}
									</label>
									<div class="controls col-sm-2">
										{if $FIELD_MODEL->isMassEditOptionDisabled()}
											<input type="hidden" name="masseditable" value={$FIELD_MODEL->get('masseditable')} />
										{else}
											<input type="hidden" name="masseditable" value="2" />
										{/if}
										<label class="checkbox" style="margin-left: 6%;">
											<input type="checkbox" class="{if $FIELD_MODEL->isMassEditOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer{/if}" name="masseditable" value="1" {if $FIELD_MODEL->get('masseditable') eq '1'}checked="checked" {/if} 
												{if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if}/>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
			</form>
		</div>
		{if $FIELDS_INFO neq '[]'}
			<script type="text/javascript">
				var uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					var newFieldInfo = {$NEW_FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							},
							getNewFieldInfo: function () {
								if (newFieldInfo['newfieldinfo']) {
									return newFieldInfo['newfieldinfo']
								}
								return false;
							}
						},
					};
				})();
			</script>
		{/if}
	</div>
{/strip}