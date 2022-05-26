{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Profiles/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="detailViewContainer full-height">
		<div class="col-lg-12 col-md-12 col-sm-12 col-sm-12 col-xs-12 main-scroll">
			<div class="detailViewTitle form-horizontal" id="profilePageHeader">
				<div class="clearfix row">
					<div class="col-sm-10 col-md-10 col-sm-10">
						<h4>{vtranslate('LBL_PROFILE_VIEW', $QUALIFIED_MODULE)}</h4>
					</div>
					<div class="col-sm-2">
						<div class="btn-group pull-right">
							<button class="btn btn-default  " type="button" onclick='window.location.href = "{$RECORD_MODEL->getEditViewUrl()}"'>{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
						</div>
					</div>
				</div>
				<hr>
				<br>
				<div class="profileDetailView detailViewInfo">
					<div class="row form-group">
						<div class="col-lg-2 col-md-2 col-sm-2 control-label fieldLabel">
							<label>{vtranslate('LBL_PROFILE_NAME', $QUALIFIED_MODULE)}</label>
						</div>
						<div class="fieldValue col-lg-6 col-md-6 col-sm-12"  name="profilename" id="profilename" value="{$RECORD_MODEL->getName()}"><strong>{$RECORD_MODEL->getName()}</strong></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-2 col-md-2 col-sm-2 control-label fieldLabel">
							<label>{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}:</label>
						</div>
						<div class="fieldValue col-lg-6 col-md-6 col-sm-12" name="description" id="description"><strong>{$RECORD_MODEL->getDescription()}</strong></div>
					</div>
					<br>
					{assign var="ENABLE_IMAGE_PATH" value="{vimage_path('Enable.png')}"}
					{assign var="DISABLE_IMAGE_PATH" value="{vimage_path('Disable.png')}"}
					{if $RECORD_MODEL->hasGlobalReadPermission()}
						<div class="row">
							<div class="col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-lg-10 col-md-10 col-sm-10">
								<div>
									<img class="alignMiddle" src="{if $RECORD_MODEL->hasGlobalReadPermission()}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
									&nbsp;{vtranslate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}
									<div class="input-info-addon">
										<i class="fa fa-info-circle"></i>&nbsp;
										<span >{vtranslate('LBL_VIEW_ALL_DESC',$QUALIFIED_MODULE)}</span>
									</div>
									<div>
										<img class="alignMiddle" src="{if $RECORD_MODEL->hasGlobalWritePermission()}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
										&nbsp;{vtranslate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}
										<div class="input-info-addon">
											<i class="fa fa-info-circle"></i>&nbsp;
											<span>{vtranslate('LBL_EDIT_ALL_DESC',$QUALIFIED_MODULE)}</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					{/if}
					<br>
					<div class="row">
						<div class="col-lg-offset-1 col-md-offset-1 col-sm-offset-1 col-lg-10 col-md-10 col-sm-10">
							<table class="table table-bordered">
								<thead>
									<tr class='blockHeader'>
										<th width="27%" style="text-align: left !important">
											{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}
										</th>
										<th width="11%">
											{'LBL_VIEW_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_CREATE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_EDIT'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="11%">
											{'LBL_DELETE_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
										</th>
										<th width="29%" nowrap="nowrap">
											{'LBL_FIELD_AND_TOOL_PRIVILEGES'|vtranslate:$QUALIFIED_MODULE}
										</th>
									</tr>
								</thead>
								<tbody>
									{foreach from=$RECORD_MODEL->getModulePermissions() key=TABID item=PROFILE_MODULE}
										{assign var=IS_RESTRICTED_MODULE value=$RECORD_MODEL->isRestrictedModule($PROFILE_MODULE->getName())}
										<tr>
											{assign var=MODULE_PERMISSION value=$RECORD_MODEL->hasModulePermission($PROFILE_MODULE)}
											<td data-module-name='{$PROFILE_MODULE->getName()}' data-module-status='{$MODULE_PERMISSION}'>
												<img src="{if $MODULE_PERMISSION}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}"/>&nbsp;{$PROFILE_MODULE->get('label')|vtranslate:$PROFILE_MODULE->getName()}
											</td>
											{assign var="BASIC_ACTION_ORDER" value=array(2,3,0,1)}
											{foreach from=$BASIC_ACTION_ORDER item=ACTION_ID}
												{assign var="ACTION_MODEL" value=$ALL_BASIC_ACTIONS[$ACTION_ID]}
												{assign var=MODULE_ACTION_PERMISSION value=$RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_MODEL)}
												<td data-action-state='{$ACTION_MODEL->getName()}' data-moduleaction-status='{$MODULE_ACTION_PERMISSION}' style="text-align: center;">
													{if !$IS_RESTRICTED_MODULE && $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
														<img src="{if $MODULE_ACTION_PERMISSION}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />
													{/if}
												</td>
											{/foreach}
											<td class="textAlignCenter">
												{if ($PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule()) || $PROFILE_MODULE->isUtilityActionEnabled()}
													<button type="button" data-handlerfor="fields" data-togglehandler="{$TABID}-fields" class="btn btn-sm btn-default" style="padding-right: 20px; padding-left: 20px;">
														<i class="fa fa-chevron-down"></i>
													</button>
												{/if}
											</td>
										</tr>
										<tr class="hide">
											<td colspan="6" class="row" style="padding-left: 5%;padding-right: 5%">
												<div class="row" data-togglecontent="{$TABID}-fields" style="display: none">
													{if $PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule()}
														<div class="col-sm-12">
															<label class="pull-left"><strong>{vtranslate('LBL_FIELDS',$QUALIFIED_MODULE)}{if $MODULE_NAME eq 'Calendar'} {vtranslate('LBL_OF', $MODULE_NAME)} {vtranslate('LBL_TASKS', $MODULE_NAME)}{/if}</strong></label>
															<div class="pull-right">
																<span class="mini-slider-control ui-slider" data-value="0">
																	<a style="margin-top: 3px" class="ui-slider-handle"></a>
																</span>
																<span style="margin: 0 20px;">{vtranslate('LBL_INIVISIBLE',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
																<span class="mini-slider-control ui-slider" data-value="1">
																	<a style="margin-top: 3px" class="ui-slider-handle"></a>
																</span>
																<span style="margin: 0 20px;">{vtranslate('LBL_READ_ONLY',$QUALIFIED_MODULE)}</span>&nbsp;&nbsp;
																<span class="mini-slider-control ui-slider" data-value="2">
																	<a style="margin-top: 3px" class="ui-slider-handle"></a>
																</span>
																<span style="margin: 0 14px;">{vtranslate('LBL_WRITE',$QUALIFIED_MODULE)}</span>
															</div>
															<div class="clearfix"></div>
														</div>
														<table class="table table-bordered">
															{assign var=COUNTER value=0}
															{foreach from=$PROFILE_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
																{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->get('displaytype') neq '6'}
																	{assign var="FIELD_ID" value=$FIELD_MODEL->getId()}
																	{if $COUNTER % 3 == 0}
																		<tr>
																	{/if}
																	<td class="col-sm-4">
																		{assign var="DATA_VALUE" value=$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}
																		{if $DATA_VALUE eq 0}
																			<span class="mini-slider-control ui-slider col-sm-1" data-value="0" data-range-input='{$FIELD_ID}' style="width: 0px;">
																				<a style="margin-top: 4px;margin-left: -13px;" class="ui-slider-handle"></a>
																			</span>
																		{elseif $DATA_VALUE eq 1}
																			<span class="mini-slider-control ui-slider col-sm-1" data-value="1" data-range-input='{$FIELD_ID}' style="width: 0px;">
																				<a style="margin-top: 4px;margin-left: -13px;" class="ui-slider-handle"></a>
																			</span>
																		{else}
																			<span class="mini-slider-control ui-slider col-sm-1" data-value="2" data-range-input='{$FIELD_ID}' style="width: 0px;">
																				<a style="margin-top: 4px;margin-left: -13px;" class="ui-slider-handle"></a>
																			</span>
																		{/if}&nbsp;												
																		<span class="col-sm-9" style="padding-right: 0px;">
																			{vtranslate($FIELD_MODEL->get('label'), $PROFILE_MODULE->getName())}&nbsp;
																			{if $FIELD_MODEL->isMandatory()}
																				<span class="redColor">*</span>
																			{/if}
																		</span>
																	</td>
																	{if $smarty.foreach.fields.last OR ($COUNTER+1) % 3 == 0}
																		</tr>
																	{/if}
																	{assign var=COUNTER value=$COUNTER+1}
																{/if}
															{/foreach}
														</table>
													{/if}
												</div>
											</td>
										</tr>
										<tr class="hide">
											<td colspan="6" class="row" style="padding-left: 5%;padding-right: 5%">
												<div class="row" data-togglecontent="{$TABID}-fields" style="display: none">
													<div class="col-sm-12">
														<label class="themeTextColor font-x-large pull-left"><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></label>
													</div>
													<table class="table table-bordered table-striped">
														{assign var=UTILITY_ACTION_COUNT value=0}
														{assign var="ALL_UTILITY_ACTIONS_ARRAY" value=array()}
														{foreach from=$ALL_UTILITY_ACTIONS item=ACTION_MODEL}
															{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
																{assign var="testArray" array_push($ALL_UTILITY_ACTIONS_ARRAY,$ACTION_MODEL)}
															{/if}
														{/foreach}
														{foreach from=$ALL_UTILITY_ACTIONS_ARRAY item=ACTION_MODEL name="actions"}
															{if $smarty.foreach.actions.index % 3 == 0}
																<tr>
															{/if}
															{assign var=ACTION_ID value=$ACTION_MODEL->get('actionid')}
															{assign var=ACTIONNAME_STATUS value=$RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_ID)}
															<td {if $smarty.foreach.actions.last && (($smarty.foreach.actions.index+1) % 3 neq 0)}
																{assign var="index" value=($smarty.foreach.actions.index+1) % 3}
																{assign var="colspan" value=4-$index}
																colspan="{$colspan}"
															{/if} data-action-name='{$ACTION_MODEL->getName()}' data-actionname-status='{$ACTIONNAME_STATUS}'><img class="alignMiddle" src="{if $ACTIONNAME_STATUS}{$ENABLE_IMAGE_PATH}{else}{$DISABLE_IMAGE_PATH}{/if}" />&nbsp;&nbsp;{$ACTION_MODEL->getName()}</td>
															{if $smarty.foreach.actions.last OR ($smarty.foreach.actions.index+1) % 3 == 0}
																</div>
															{/if}
														{/foreach}
													</table>
												</div>
											</td>
										</tr>
									{/foreach}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}