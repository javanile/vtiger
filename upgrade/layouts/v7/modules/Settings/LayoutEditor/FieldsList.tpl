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
	{assign var=IS_SORTABLE value=$SELECTED_MODULE_MODEL->isSortableAllowed()}
	{assign var=ALL_BLOCK_LABELS value=[]}

	<div class="row fieldsListContainer" style="padding:1% 0">
		<div class="col-sm-6">
			<button class="btn btn-default addButton addCustomBlock" type="button">
				<i class="fa fa-plus"></i>&nbsp;&nbsp;
				{vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}
			</button>
		</div>
		<div class="col-sm-6">
			{if $IS_SORTABLE}
				<span class="pull-right">
					<button class="btn btn-success saveFieldSequence" type="button" style="opacity:0;margin-right:0px;">
						{vtranslate('LBL_SAVE_LAYOUT', $QUALIFIED_MODULE)}
					</button>
				</span>
			{/if}
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div id="moduleBlocks" style="margin-top:17px;">
				{foreach key=BLOCK_LABEL_KEY item=BLOCK_MODEL from=$BLOCKS}
					{assign var=IS_BLOCK_SORTABLE value=$SELECTED_MODULE_MODEL->isBlockSortableAllowed($BLOCK_LABEL_KEY)}
					{assign var=FIELDS_LIST value=$BLOCK_MODEL->getLayoutBlockActiveFields()}
					{assign var=BLOCK_ID value=$BLOCK_MODEL->get('id')}
					{if $BLOCK_LABEL_KEY neq 'LBL_INVITE_USER_BLOCK'}
						{$ALL_BLOCK_LABELS[$BLOCK_ID] = $BLOCK_MODEL}
					{/if}
					<div id="block_{$BLOCK_ID}" class="editFieldsTable block_{$BLOCK_ID} marginBottom10px border1px {if $IS_BLOCK_SORTABLE} blockSortable{/if}" data-block-id="{$BLOCK_ID}" data-sequence="{$BLOCK_MODEL->get('sequence')}" style="background: white;"
						 data-custom-fields-count="{$BLOCK_MODEL->getCustomFieldsCount()}">
						<div class="col-sm-12">
							<div class="layoutBlockHeader row">
								<div class="blockLabel col-sm-3 padding10 marginLeftZero" style="word-break: break-all;">
									{if $IS_BLOCK_SORTABLE}
										<img class="cursorPointerMove" src="{vimage_path('drag.png')}" />&nbsp;&nbsp;
									{/if}
									<strong class="translatedBlockLabel">{vtranslate($BLOCK_LABEL_KEY, $SELECTED_MODULE_NAME)}</strong>
								</div>
								<div class="col-sm-9 padding10 marginLeftZero">
									<div class="blockActions" style="float:right !important;">
										<span>
											<i class="fa fa-info-circle" title="{vtranslate('LBL_COLLAPSE_BLOCK_DETAIL_VIEW', $QUALIFIED_MODULE)}"></i>&nbsp; {vtranslate('LBL_COLLAPSE_BLOCK', $QUALIFIED_MODULE)}&nbsp;
											<input style="opacity: 0;" type="checkbox" 
													{if $BLOCK_MODEL->isHidden()} checked value='0' {else} value='1' {/if} class ='cursorPointer bootstrap-switch' name="collapseBlock" 
													data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" data-on-color="primary" data-block-id="{$BLOCK_MODEL->get('id')}"/>
										</span>
										&nbsp;
										{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
											<button class="btn btn-default addButton btn-sm addCustomField" type="button">
												<i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
											</button>&nbsp;&nbsp;
										{/if}
										{if $BLOCK_MODEL->isActionsAllowed()}
											<button class="inActiveFields addButton btn btn-default btn-sm">{vtranslate('LBL_SHOW_HIDDEN_FIELDS', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
											{if $BLOCK_MODEL->isCustomized()}
												<button class="deleteCustomBlock addButton btn btn-default btn-sm">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</button>
											{/if}
										{/if}
									</div>
								</div>
							</div>
						</div>
						{assign var=IS_FIELDS_SORTABLE value=$SELECTED_MODULE_MODEL->isFieldsSortableAllowed($BLOCK_LABEL_KEY)}
						<div class="blockFieldsList {if $IS_FIELDS_SORTABLE} blockFieldsSortable {/if} row">
							<ul name="{if $IS_FIELDS_SORTABLE}sortable1{else}unSortable1{/if}" class="connectedSortable col-sm-6">
								{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									{if $smarty.foreach.fieldlist.index % 2 eq 0}
										<li>
											<div class="row border1px">
												<div class="col-sm-4">
													<div class="opacity editFields marginLeftZero" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" 
														 data-sequence="{$FIELD_MODEL->get('sequence')}" data-field-name="{$FIELD_MODEL->get('name')}" 
														 >
														<div class="row">
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<span class="col-sm-1">&nbsp;
																{if $IS_FIELDS_SORTABLE}
																	<img src="{vimage_path('drag.png')}" class="cursorPointerMove" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																{/if}
															</span>
															<div class="col-sm-9" style="word-wrap: break-word;">
																<div class="fieldLabelContainer row">
																	<span class="fieldLabel">
																		<b>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</b>
																		&nbsp;{if $IS_MANDATORY}<span class="redColor">*</span>{/if}
																	</span><br>
																	<span class="pull-right" style="opacity:0.6;">
																		{vtranslate($FIELD_MODEL->getFieldDataTypeLabel(),$QUALIFIED_MODULE)}
																	</span>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-8 fieldPropertyContainer">
													<div class="row " style="padding: 10px 0px;">
														{assign var=M_FIELD_TITLE value={vtranslate('LBL_MAKE_THIS_FIELD', $QUALIFIED_MODULE, vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE))}}
														{assign var=Q_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE))}}
														{assign var=M_E_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE))}}
														{assign var=S_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE))}}
														{assign var=H_FIELD_TITLE value={vtranslate('LBL_SHOW_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_DETAIL_HEADER',$QUALIFIED_MODULE))}}
														{assign var=NOT_M_FIELD_TITLE value={vtranslate('LBL_NOT_MAKE_THIS_FIELD', $QUALIFIED_MODULE, vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE))}}
														{assign var=NOT_Q_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE))}}
														{assign var=NOT_M_E_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE))}}
														{assign var=NOT_S_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE))}}
														{assign var=NOT_H_FIELD_TITLE value={vtranslate('LBL_HIDE_THIS_FIELD_IN', $QUALIFIED_MODULE, vtranslate('LBL_DETAIL_HEADER',$QUALIFIED_MODULE))}}
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="fieldProperties col-sm-10" data-field-id="{$FIELD_MODEL->get('id')}">
															<span class="mandatory switch text-capitalize {if (!$IS_MANDATORY)}disabled{/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_MANDATORY} title="{$NOT_M_FIELD_TITLE}" {else} title="{$M_FIELD_TITLE}" {/if}>
																<i class="fa fa-exclamation-circle" data-name="mandatory" 
																	data-enable-value="M" data-disable-value="O"
																	{if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}
																	></i>&nbsp;{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}
															</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
															{assign var=IS_QUICK_EDIT_ENABLED value=$FIELD_MODEL->isQuickCreateEnabled()}
															<span class="quickCreate switch {if (!$IS_QUICK_EDIT_ENABLED)}disabled{/if} 
																	{if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY } cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_QUICK_EDIT_ENABLED} title="{$NOT_Q_FIELD_TITLE}" {else} title="{$Q_FIELD_TITLE}" {/if}>
																<i class="fa fa-plus" data-name="quickcreate" 
																	data-enable-value="2" data-disable-value="1"
																	{if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY }readonly="readonly"{/if}
																	title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}
															</span><br><br>
															{assign var=IS_MASS_EDIT_ENABLED value=$FIELD_MODEL->isMassEditable()}
															<span class="massEdit switch {if (!$IS_MASS_EDIT_ENABLED)} disabled {/if} 
																	{if $FIELD_MODEL->isMassEditOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_MASS_EDIT_ENABLED} title="{$NOT_M_E_FIELD_TITLE}" {else} title="{$M_E_FIELD_TITLE}" {/if}>
																<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" 
																	 data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" 
																	 {if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if} height=14 width=14 
																	 />&nbsp;{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}
															</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
															{assign var=IS_HEADER_FIELD value=$FIELD_MODEL->isHeaderField()}
															<span class="header switch {if (!$IS_HEADER_FIELD)} disabled {/if} 
																	{if $FIELD_MODEL->isHeaderFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_HEADER_FIELD} title="{$NOT_H_FIELD_TITLE}" {else} title="{$H_FIELD_TITLE}" {/if}>
																<i class="fa fa-flag-o" data-name="headerfield" 
																	data-enable-value="1" data-disable-value="0"
																	{if $FIELD_MODEL->isHeaderFieldOptionDisabled()}readonly="readonly"{/if}
																	title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}
															</span><br><br>
															{assign var=IS_SUMMARY_VIEW_ENABLED value=$FIELD_MODEL->isSummaryField()}
															<span class="summary switch {if (!$IS_SUMMARY_VIEW_ENABLED)} disabled {/if} 
																	{if $FIELD_MODEL->isSummaryFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_SUMMARY_VIEW_ENABLED} title="{$NOT_S_FIELD_TITLE}" {else} title="{$S_FIELD_TITLE}" {/if}>
																<i class="fa fa-key" data-name="summaryfield" 
																	data-enable-value="1" data-disable-value="0"
																	{if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if}
																	title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}
															</span><br><br>
															<div class="defaultValue col-sm-12 {if !$FIELD_MODEL->hasDefaultValue()}disabled{/if} 
																 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} cursorPointerNotAllowed {/if}">
																{assign var=DEFAULT_VALUE value=$FIELD_MODEL->getDefaultFieldValueToViewInV7FieldsLayOut()}
																{if $DEFAULT_VALUE}
																	{if is_array($DEFAULT_VALUE)}
																		{foreach key=DEFAULT_FIELD_NAME item=DEFAULT_FIELD_VALUE from=$DEFAULT_VALUE}
																			<div class="row">
																				<span><img src="{vimage_path('DefaultValue.png')}"
																							{if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																							{if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if}
																							data-name="defaultValueField" height=14 width=14 /></span>&nbsp;
																					{if $DEFAULT_FIELD_VALUE}
																						{assign var=DEFAULT_FIELD_NAME value=$DEFAULT_FIELD_NAME|upper}
																					<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)}
																						{vtranslate("LBL_$DEFAULT_FIELD_NAME",$QUALIFIED_MODULE)} : </span>
																					<span data-defaultvalue-fieldname="{$DEFAULT_FIELD_NAME}" data-defaultvalue="{$DEFAULT_FIELD_VALUE}">{$DEFAULT_FIELD_VALUE}</span>
																				{else}
																					{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}
																				{/if}
																			</div>
																		{/foreach}
																	{else}
																		<div class="row">
																			<span>
																				<img src="{vimage_path('DefaultValue.png')}"
																					 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																					 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE|strip_tags}" {/if}
																					 data-name="defaultValueField" height=14 width=14 />
																			</span>&nbsp;
																			<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} : </span>
																			<span data-defaultvalue="{$DEFAULT_VALUE|strip_tags}">{$DEFAULT_VALUE|strip_tags}</span>
																		</div>
																	{/if}
																{else}
																	<div class="row">
																		<span>
																			<img src="{vimage_path('DefaultValue.png')}"
																				 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																				 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if}
																				 data-name="defaultValueField" height=14 width=14 />
																		</span>&nbsp;
																		<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																	</div>
																{/if}
															</div>
														</div>
														<span class="col-sm-2 actions">
															{if $FIELD_MODEL->isEditable()}
																<a href="javascript:void(0)" class="editFieldDetails">
																	<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
															{if $FIELD_MODEL->isCustomField() eq 'true'}
																<a href="javascript:void(0)" class="deleteCustomField pull-right" data-field-id="{$FIELD_MODEL->get('id')}"
																	data-one-one-relationship="{$FIELD_MODEL->isOneToOneRelationField()}" data-relationship-field="{$FIELD_MODEL->isRelationShipReponsibleField()}"
																	{if $FIELD_MODEL->isOneToOneRelationField()}
																		{assign var=ONE_ONE_RELATION_FIELD_LABEL value=$FIELD_MODEL->getOneToOneRelationField()->get('label')}
																		{assign var=ONE_ONE_RELATION_MODULE_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getModuleName()}
																		{assign var=ONE_ONE_RELATION_FIELD_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getName()}
																		data-relation-field-label="{$ONE_ONE_RELATION_FIELD_LABEL}" 
																		data-relation-module-label="{vtranslate($ONE_ONE_RELATION_MODULE_NAME,$ONE_ONE_RELATION_MODULE_NAME)}"
																		data-current-field-label ="{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}"
																		data-current-module-label="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}"
																		data-field-name="{$ONE_ONE_RELATION_FIELD_NAME}"
																	{/if}
																	{if $FIELD_MODEL->isRelationShipReponsibleField()}
																		{assign var=RELATION_MODEL value=$FIELD_MODEL->getRelationShipForThisField()}

																		data-relation-field-label="{vtranslate($FIELD_MODEL->get('label'),$RELATION_MODEL->getRelationModuleName())}" 
																		data-relation-module-label="{vtranslate($RELATION_MODEL->getRelationModuleName(),$RELATION_MODEL->getRelationModuleName())}"
																		data-current-module-label="{vtranslate($RELATION_MODEL->getParentModuleName(),$RELATION_MODEL->getParentModuleName())}"
																		data-current-tab-label="{vtranslate($RELATION_MODEL->get('label'), $RELATION_MODEL->getRelationModuleName())}"
																	{/if} >
																	<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
														</span>
													</div>
												</div>
											</div>
										</li>
									{/if}
								{/foreach}
								{if count($FIELDS_LIST)%2 eq 0 }
									{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
										<li class="row dummyRow">
											<span class="dragUiText col-sm-8">
												{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
											</span>
											<span class="col-sm-4" style="margin-top: 7%;margin-left: -15%;">
												<button class="btn btn-default btn-sm addButton"><i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</button>
											</span>
										</li>
									{/if}
								{/if}
							</ul>
							<ul name="{if $IS_FIELDS_SORTABLE}sortable2{else}unSortable2{/if}" class="connectedSortable col-sm-6">
								{foreach item=FIELD_MODEL from=$FIELDS_LIST name=fieldlist1}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									{if $smarty.foreach.fieldlist1.index % 2 neq 0}
										<li>
											<div class="row border1px">
												<div class="col-sm-4">
													<div class="opacity editFields marginLeftZero" data-block-id="{$BLOCK_ID}" data-field-id="{$FIELD_MODEL->get('id')}" 
														 data-sequence="{$FIELD_MODEL->get('sequence')}" data-field-name="{$FIELD_MODEL->get('name')}"
														 >
														<div class="row" >
															{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
															<span class="col-sm-1">&nbsp;
																{if $FIELD_MODEL->isEditable() && $IS_FIELDS_SORTABLE}
																	<img src="{vimage_path('drag.png')}" class="cursorPointerMove" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
																{/if}
															</span>
															<div class="col-sm-9" style="word-wrap: break-word;">
																<div class="fieldLabelContainer row">
																	<span class="fieldLabel">
																		<b>{vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}</b>
																		{if $IS_MANDATORY}&nbsp;<span class="redColor">*</span>{/if}
																	</span><br>
																	<span class="pull-right" style="opacity:0.6;">
																		{vtranslate($FIELD_MODEL->getFieldDataTypeLabel(),$QUALIFIED_MODULE)}
																	</span>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-8 fieldPropertyContainer">
													<div class="row " style="padding: 10px 0px;">
														{assign var=IS_MANDATORY value=$FIELD_MODEL->isMandatory()}
														<div class="fieldProperties col-sm-10" data-field-id="{$FIELD_MODEL->get('id')}">
															<span class="mandatory switch text-capitalize {if (!$IS_MANDATORY)}disabled{/if} {if $FIELD_MODEL->isMandatoryOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_MANDATORY} title="{$NOT_M_FIELD_TITLE}" {else} title="{$M_FIELD_TITLE}" {/if}>
																<i class="fa fa-exclamation-circle" data-name="mandatory" 
																	data-enable-value="M" data-disable-value="O"
																	{if $FIELD_MODEL->isMandatoryOptionDisabled()}readonly="readonly"{/if}
																	></i>&nbsp;{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}
															</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
															{assign var=IS_QUICK_EDIT_ENABLED value=$FIELD_MODEL->isQuickCreateEnabled()}
															<span class="quickCreate switch {if (!$IS_QUICK_EDIT_ENABLED)}disabled{/if} 
																	{if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY } cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_QUICK_EDIT_ENABLED} title="{$NOT_Q_FIELD_TITLE}" {else} title="{$Q_FIELD_TITLE}" {/if}>
																<i class="fa fa-plus" data-name="quickcreate" 
																	data-enable-value="2" data-disable-value="1"
																	{if $FIELD_MODEL->isQuickCreateOptionDisabled() || $IS_MANDATORY }readonly="readonly"{/if}
																	title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}
															</span><br><br>
															{assign var=IS_MASS_EDIT_ENABLED value=$FIELD_MODEL->isMassEditable()}
															<span class="massEdit switch {if (!$IS_MASS_EDIT_ENABLED)} disabled {/if} 
																	{if $FIELD_MODEL->isMassEditOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_MASS_EDIT_ENABLED} title="{$NOT_M_E_FIELD_TITLE}" {else} title="{$M_E_FIELD_TITLE}" {/if}>
																<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" 
																	 data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" 
																	 {if $FIELD_MODEL->isMassEditOptionDisabled()}readonly="readonly"{/if} height=14 width=14 
																	 />&nbsp;{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}
															</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
															{assign var=IS_HEADER_FIELD value=$FIELD_MODEL->isHeaderField()}
															<span class="header switch {if (!$IS_HEADER_FIELD)} disabled {/if} 
																	{if $FIELD_MODEL->isHeaderFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_HEADER_FIELD} title="{$NOT_H_FIELD_TITLE}" {else} title="{$H_FIELD_TITLE}" {/if}>
																<i class="fa fa-flag-o" data-name="headerfield" 
																	data-enable-value="1" data-disable-value="0"
																	{if $FIELD_MODEL->isHeaderFieldOptionDisabled()}readonly="readonly"{/if}
																	title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}
															</span><br><br>
															{assign var=IS_SUMMARY_VIEW_ENABLED value=$FIELD_MODEL->isSummaryField()}
															<span class="summary switch {if (!$IS_SUMMARY_VIEW_ENABLED)} disabled {/if} 
																	{if $FIELD_MODEL->isSummaryFieldOptionDisabled()} cursorPointerNotAllowed {else} cursorPointer {/if}"
																	data-toggle="tooltip" {if $IS_SUMMARY_VIEW_ENABLED} title="{$NOT_S_FIELD_TITLE}" {else} title="{$S_FIELD_TITLE}" {/if}>
																<i class="fa fa-key" data-name="summaryfield" 
																	data-enable-value="1" data-disable-value="0"
																	{if $FIELD_MODEL->isSummaryFieldOptionDisabled()}readonly="readonly"{/if}
																	title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>&nbsp;{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}
															</span><br><br>
															<div class="defaultValue col-sm-12 {if !$FIELD_MODEL->hasDefaultValue()}disabled{/if} 
																 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} cursorPointerNotAllowed {/if}">
																{assign var=DEFAULT_VALUE value=$FIELD_MODEL->getDefaultFieldValueToViewInV7FieldsLayOut()}
																{if $DEFAULT_VALUE}
																	{if is_array($DEFAULT_VALUE)}
																		{foreach key=DEFAULT_FIELD_NAME item=DEFAULT_FIELD_VALUE from=$DEFAULT_VALUE}
																			<div class="row defaultValueContent">
																				<span><img src="{vimage_path('DefaultValue.png')}"
																							{if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																							{if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if}
																							data-name="defaultValueField" height=14 width=14 /></span>&nbsp;
																					{if $DEFAULT_FIELD_VALUE}
																						{assign var=DEFAULT_FIELD_NAME value=$DEFAULT_FIELD_NAME|upper}
																					<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)}
																						{vtranslate("LBL_$DEFAULT_FIELD_NAME",$QUALIFIED_MODULE)} : </span>
																					<span data-defaultvalue-fieldname="{$DEFAULT_FIELD_NAME}" data-defaultvalue="{$DEFAULT_FIELD_VALUE}">{$DEFAULT_FIELD_VALUE}</span>
																				{else}
																					{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}
																				{/if}
																			</div>
																		{/foreach}
																	{else}
																		<div class="row defaultValueContent">
																			<span>
																				<img src="{vimage_path('DefaultValue.png')}" height=14 width=14 
																					 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																					 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE|strip_tags}" {/if}>
																			</span>&nbsp;
																			<span>{vtranslate('LBL_DEFAULT_VALUE',$QUALIFIED_MODULE)} : </span>
																			<span data-defaultvalue="{$DEFAULT_VALUE|strip_tags}">{$DEFAULT_VALUE|strip_tags}</span>
																		</div>
																	{/if}
																{else}
																	<div class="row defaultValueContent">
																		<span>
																			<img src="{vimage_path('DefaultValue.png')}"
																				 {if $FIELD_MODEL->isDefaultValueOptionDisabled()} readonly="readonly" {/if}
																				 {if $FIELD_MODEL->hasDefaultValue()} title="{$DEFAULT_VALUE}" {/if}
																				 data-name="defaultValueField" height=14 width=14 />
																		</span>&nbsp;
																		<span>{vtranslate('LBL_DEFAULT_VALUE_NOT_SET',$QUALIFIED_MODULE)}</span>
																	</div>
																{/if}
															</div>
														</div>
														<span class="col-sm-2 actions">
															{if $FIELD_MODEL->isEditable()}
																<a href="javascript:void(0)" class="editFieldDetails">
																	<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
															{if $FIELD_MODEL->isCustomField() eq 'true'}
																<a href="javascript:void(0)" class="deleteCustomField pull-right" data-field-id="{$FIELD_MODEL->get('id')}"
																	data-one-one-relationship="{$FIELD_MODEL->isOneToOneRelationField()}" data-relationship-field="{$FIELD_MODEL->isRelationShipReponsibleField()}"
																	{if $FIELD_MODEL->isOneToOneRelationField()}
																		{assign var=ONE_ONE_RELATION_FIELD_LABEL value=$FIELD_MODEL->getOneToOneRelationField()->get('label')}
																		{assign var=ONE_ONE_RELATION_MODULE_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getModuleName()}
																		{assign var=ONE_ONE_RELATION_FIELD_NAME value=$FIELD_MODEL->getOneToOneRelationField()->getName()}
																		data-relation-field-label="{$ONE_ONE_RELATION_FIELD_LABEL}" 
																		data-relation-module-label="{vtranslate($ONE_ONE_RELATION_MODULE_NAME,$ONE_ONE_RELATION_MODULE_NAME)}"
																		data-current-field-label ="{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}"
																		data-current-module-label="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}"
																		data-field-name="{$ONE_ONE_RELATION_FIELD_NAME}"
																	{/if}
																	{if $FIELD_MODEL->isRelationShipReponsibleField()}
																		{assign var=RELATION_MODEL value=$FIELD_MODEL->getRelationShipForThisField()}

																		data-relation-field-label="{vtranslate($FIELD_MODEL->get('label'),$RELATION_MODEL->getRelationModuleName())}" 
																		data-relation-module-label="{vtranslate($RELATION_MODEL->getRelationModuleName(),$RELATION_MODEL->getRelationModuleName())}"
																		data-current-module-label="{vtranslate($RELATION_MODEL->getParentModuleName(),$RELATION_MODEL->getParentModuleName())}"
																		data-current-tab-label="{vtranslate($RELATION_MODEL->get('label'), $RELATION_MODEL->getRelationModuleName())}"
																	{/if} >
																	<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
																</a>
															{/if}
														</span>
													</div>
												</div>
											</div>
										</li>
									{/if}
								{/foreach}
								{if count($FIELDS_LIST)%2 neq 0 }
									{if $BLOCK_MODEL->isAddCustomFieldEnabled()}
										<li class="row dummyRow">
											<span class="dragUiText col-sm-8">
												{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
											</span>
											<span class="col-sm-4" style="margin-top: 7%;margin-left: -15%;">
												<button class="btn btn-default btn-sm addButton"><i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</button>
											</span>
										</li>
									{/if}
								{/if}
							</ul>
						</div>
					</div>
				{/foreach}
			</div>
		</div>
	</div>
	<input type="hidden" class="inActiveFieldsArray" value='{Vtiger_Functions::jsonEncode($IN_ACTIVE_FIELDS)}' />
	<input type="hidden" id="headerFieldsCount" value="{$HEADER_FIELDS_COUNT}">
	<input type="hidden" id="nameFields" value='{Vtiger_Functions::jsonEncode($SELECTED_MODULE_MODEL->getNameFields())}'>
	<input type="hidden" id="headerFieldsMeta" value='{Vtiger_Functions::jsonEncode($HEADER_FIELDS_META)}'>

	<div id="" class="newCustomBlockCopy hide marginBottom10px border1px blockSortable" data-block-id="" data-sequence="">
		<div class="layoutBlockHeader">
			<div class="col-sm-3 blockLabel padding10 marginLeftZero" style="word-break: break-all;">
				<img class="alignMiddle" src="{vimage_path('drag.png')}" />&nbsp;&nbsp;
			</div>
			<div class="col-sm-9 padding10 marginLeftZero">
				<div class="blockActions" style="float: right !important;">
					<span>
						<i class="fa fa-info-circle" title="{vtranslate('LBL_COLLAPSE_BLOCK_DETAIL_VIEW', $QUALIFIED_MODULE)}"></i>&nbsp; {vtranslate('LBL_COLLAPSE_BLOCK', $QUALIFIED_MODULE)}&nbsp;
						<input style="opacity: 0;" type="checkbox" 
								{if $BLOCK_MODEL->isHidden()} checked value='0' {else} value='1' {/if} class ='cursorPointer' id="hiddenCollapseBlock" name="" 
								data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" data-on-color="primary" data-block-id="{$BLOCK_MODEL->get('id')}"/>
					</span>&nbsp;
					<button class="btn btn-default addButton addCustomField" type="button">
						<i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}
					</button>&nbsp;&nbsp;
					<button class="inActiveFields addButton btn btn-default btn-sm">{vtranslate('LBL_SHOW_HIDDEN_FIELDS', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
					<button class="deleteCustomBlock addButton btn btn-default btn-sm">{vtranslate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</button>
				</div>
			</div>
		</div>
		<div class="blockFieldsList row blockFieldsSortable">
			<ul class="connectedSortable col-sm-6 ui-sortable"name="sortable1">
				<li class="row dummyRow">
					<span class="dragUiText col-sm-8">
						{vtranslate('LBL_ADD_NEW_FIELD_HERE',$QUALIFIED_MODULE)}
					</span>
					<span class="col-sm-4" style="margin-top: 7%;margin-left: -15%;">
						<button class="btn btn-default btn-sm addButton"><i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD',$QUALIFIED_MODULE)}</button>
					</span>
				</li>
			</ul>
			<ul class="connectedSortable col-sm-6 ui-sortable" name="sortable2"></ul>
		</div>
	</div>

	<li class="newCustomFieldCopy hide">
		<div class="row border1px">
			<div class="col-sm-4">
				<div class="marginLeftZero" data-field-id="" data-sequence="">
					<div class="row">
						<span class="col-sm-1">&nbsp;
							{if $IS_SORTABLE}
								<img src="{vimage_path('drag.png')}" class="dragImage" border="0" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
							{/if}
						</span>
						<div class="col-sm-9" style="word-wrap: break-word;">
							<div class="fieldLabelContainer row">
								<span class="fieldLabel">
									<b></b>
									&nbsp;
								</span>
								<div>
									<span class="pull-right fieldTypeLabel" style="opacity:0.6;"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-8 fieldPropertyContainer">
				<div class="row " style="padding:10px 0px">
					<div class="fieldProperties col-sm-10" data-field-id="">
						<span class="mandatory switch text-capitalize">
							<i class="fa fa-exclamation-circle" data-name="mandatory" 
								data-enable-value="M" data-disable-value="O" 
								title="{vtranslate('LBL_MANDATORY',$QUALIFIED_MODULE)}"></i>
							&nbsp;{vtranslate('LBL_PROP_MANDATORY',$QUALIFIED_MODULE)}
						</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<span class="quickCreate switch">
							<i class="fa fa-plus" data-name="quickcreate" 
								data-enable-value="2" data-disable-value="1"
								title="{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}"></i>
							&nbsp;{vtranslate('LBL_QUICK_CREATE',$QUALIFIED_MODULE)}
						</span><br><br>
						<span class="massEdit switch" >
							<img src="{vimage_path('MassEdit.png')}" data-name="masseditable" 
								 data-enable-value="1" data-disable-value="2" title="{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}" height=14 width=14 
								 />&nbsp;{vtranslate('LBL_MASS_EDIT',$QUALIFIED_MODULE)}
						</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<span class="header switch">
							<i class="fa fa-flag-o" data-name="headerfield" 
								data-enable-value="1" data-disable-value="0" 
								title="{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}"></i>
							&nbsp;{vtranslate('LBL_HEADER',$QUALIFIED_MODULE)}
						</span><br><br>
						<span class="summary switch">
							<i class="fa fa-key" data-name="summaryfield" 
								data-enable-value="1" data-disable-value="0" 
								title="{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}"></i>
							&nbsp;{vtranslate('LBL_KEY_FIELD',$QUALIFIED_MODULE)}
						</span><br><br>
						<div class="defaultValue col-sm-12">
						</div>
					</div>
					<span class="col-sm-2 actions">
						<a href="javascript:void(0)" class="editFieldDetails">
							<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}"></i>
						</a>
						<a href="javascript:void(0)" class="deleteCustomField pull-right">
							<i class="fa fa-trash" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"></i>
						</a>
					</span>
				</div>
			</div>
		</div>
	</li>

	<div class="modal-dialog modal-content addBlockModal hide">
		{assign var=HEADER_TITLE value={vtranslate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal addCustomBlockForm">
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label fieldLabel col-sm-5">
						<span>{vtranslate('LBL_BLOCK_NAME', $QUALIFIED_MODULE)}</span>
						<span class="redColor">*</span>
					</label>
					<div class="controls col-sm-6">
						<input type="text" name="label" class="col-sm-3 inputElement" data-rule-required='true' style='width: 75%'/>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label fieldLabel col-sm-5">
						{vtranslate('LBL_ADD_AFTER', $QUALIFIED_MODULE)}
					</label>
					<div class="controls col-sm-6">
						<select class="col-sm-9" name="beforeBlockId">
							{foreach key=BLOCK_ID item=BLOCK_MODEL from=$ALL_BLOCK_LABELS}
								<option value="{$BLOCK_ID}" data-label="{$BLOCK_MODEL->get('label')}">{vtranslate($BLOCK_MODEL->get('label'), $SELECTED_MODULE_NAME)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>
	<div class="hide defaultValueIcon">
		<img src="{vimage_path('DefaultValue.png')}" height=14 width=14>
	</div>
	{assign var=FIELD_INFO value=$CLEAN_FIELD_MODEL->getFieldInfo()}
	{include file=vtemplate_path('FieldCreate.tpl','Settings:LayoutEditor') FIELD_MODEL=$CLEAN_FIELD_MODEL IS_FIELD_EDIT_MODE=false}
	<div class="modal-dialog inactiveFieldsModal hide">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>{vtranslate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</h3>
		</div>
		<div class="modal-content">
			<form class="form-horizontal inactiveFieldsForm">
				<div class="modal-body">
					<div class="inActiveList row">
						<div class="col-sm-1"></div>
						<div class="list col-sm-10"></div>
						<div class="col-sm-1"></div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="pull-right cancelLinkContainer">
						<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
					</div>
					<button class="btn btn-success" type="submit" name="reactivateButton">
						<strong>{vtranslate('LBL_REACTIVATE', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
			</form>
		</div>
	</div>
	<div class="ps-scrollbar-y" style="height: 60px;">
	</div>
	{if $FIELDS_INFO neq '[]'}
		<script type="text/javascript">
			var uimeta = (function() {
				var fieldInfo = {$FIELDS_INFO};
				var newFieldInfo = {$NEW_FIELDS_INFO};
				return {
					field: {
						get: function(name, property) {
							if(name && property === undefined) {
								return fieldInfo[name];
							}
							if(name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType : function(name){
							if(fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						},
						getNewFieldInfo : function() {
							if(newFieldInfo['newfieldinfo']){
								return newFieldInfo['newfieldinfo']
							}
							return false;
						}
					},
				};
			})();
		</script>
	{/if}
{/strip}
