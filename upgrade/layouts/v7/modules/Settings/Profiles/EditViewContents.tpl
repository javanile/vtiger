{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Profiles/views/EditAjax.php *}

{if $SHOW_EXISTING_PROFILES}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<div class="form-group row">
		<div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
			<strong>{vtranslate('LBL_COPY_PRIVILEGES_FROM',"Settings:Roles")}</strong>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-6">
			<select class="select2" id="directProfilePriviligesSelect" placeholder="{vtranslate('LBL_CHOOSE_PROFILES',$QUALIFIED_MODULE)}">
				<option></option>
				{foreach from=$ALL_PROFILES item=PROFILE}
					{if $PROFILE->isDirectlyRelated() eq false}
						<option value="{$PROFILE->getId()}" {if $RECORD_ID eq $PROFILE->getId()} selected="" {/if} >{$PROFILE->getName()}</option>
					{/if}
				{/foreach}
			</select>
		</div>
	</div>
{/if}
<input type="hidden" name="viewall" value="0" />
<input type="hidden" name="editall" value="0" />
{if $RECORD_MODEL && $RECORD_MODEL->getId() && empty($IS_DUPLICATE_RECORD)}
	{if $RECORD_MODEL->hasGlobalReadPermission() || $RECORD_MODEL->hasGlobalWritePermission()}
		<div class="form-group row">
			<div class="col-lg-3 col-md-3 col-sm-3 fieldLabel"></div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<label class="control-label">
					<input type="hidden" name="viewall" value="0" />
					<label class="control-label">
						<input class="listViewEntriesCheckBox" type="checkbox" name="viewall" {if $RECORD_MODEL->hasGlobalReadPermission()}checked="true"{/if} style="top: -2px;" />
						&nbsp;{vtranslate('LBL_VIEW_ALL',$QUALIFIED_MODULE)}&nbsp;
					</label>
					<span style="margin-left: 10px">
						<i class="fa fa-info-circle" title="{vtranslate('LBL_VIEW_ALL_DESC',$QUALIFIED_MODULE)}"></i>
					</span>
				</label>
				<br>
				<label class="control-label">
					<input type="hidden" name="editall" value="0" />
					<label class="control-label">
						<input class="listViewEntriesCheckBox" type="checkbox" name="editall" {if $RECORD_MODEL->hasGlobalReadPermission()}checked="true"{/if} style="top: -2px;"/>
						&nbsp;{vtranslate('LBL_EDIT_ALL',$QUALIFIED_MODULE)}&nbsp;
					</label>
					<span style="margin-left: 15px">
						<i class="fa fa-info-circle" title="{vtranslate('LBL_EDIT_ALL_DESC',$QUALIFIED_MODULE)}"></i>
					</span>
				</label>
				<br><br>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-3 col-md-3 col-sm-3 fieldLabel"></div>
			<div class="col-lg-7 col-md-7 col-sm-7">
				<div class="alert alert-warning" style="width:85%">
					{vtranslate('LBL_GLOBAL_PERMISSION_WARNING',$QUALIFIED_MODULE)}
				</div>
			</div>
	{/if}
{/if}
<div class="row col-lg-12 col-md-12 col-sm-12">
	<div class=" col-lg-10 col-md-10 col-sm-10">
		<h5>{vtranslate('LBL_EDIT_PRIVILEGES_OF_THIS_PROFILE',$QUALIFIED_MODULE)}</h5><br>
	</div>
</div>
<div class="row">
	<div class="col-lg-1 col-md-1 col-sm-1"></div>
	<div class=" col-lg-10 col-md-10 col-sm-10">
		<table class="table table-bordered profilesEditView">
			<thead>
				<tr class="blockHeader">
					<th width="25%" style="text-align: left !important">
						<label><input class="verticalAlignMiddle" checked="true" type="checkbox" id="mainModulesCheckBox" style="top: -2px;"/>&nbsp;
							{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}
						</label>
					</th>
					<th class='textAlignCenter' width="14%">
						<label><input class="verticalAlignMiddle" type="checkbox" {if empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {/if} id="mainAction4CheckBox" style="top: -2px;" />&nbsp;
							{'LBL_VIEW_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
						</label>
					</th>
					<th class='textAlignCenter' width="14%">
						<label><input class="verticalAlignMiddle" {if empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {/if} type="checkbox" id="mainAction7CheckBox" style="top: -2px;"/>&nbsp;
							{'LBL_CREATE'|vtranslate:$QUALIFIED_MODULE}
						</label>
					</th>
					<th class='textAlignCenter' width="14%">
						<label><input class="verticalAlignMiddle" {if empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {/if} type="checkbox" id="mainAction1CheckBox" style="top: -2px;" />&nbsp;
							{'LBL_EDIT'|vtranslate:$QUALIFIED_MODULE}
						</label>
					</th>
					<th class='textAlignCenter' width="14%">
						<label><input class="verticalAlignMiddle" checked="true" type="checkbox" id="mainAction2CheckBox" style="top: -2px;" />&nbsp;
							{'LBL_DELETE_PRVILIGE'|vtranslate:$QUALIFIED_MODULE}
						</label>
					</th>
					<th class='textAlignCenter verticalAlignMiddleImp' width="28%;" nowrap="nowrap">
						{'LBL_FIELD_AND_TOOL_PRIVILEGES'|vtranslate:$QUALIFIED_MODULE}
					</th>
				</tr>
			</thead>
			<tbody>
				{assign var=PROFILE_MODULES value=$RECORD_MODEL->getModulePermissions()}
				{foreach from=$PROFILE_MODULES key=TABID item=PROFILE_MODULE}
					{assign var=MODULE_NAME value=$PROFILE_MODULE->getName()}
					{if $MODULE_NAME neq 'Events'}
						{assign var=IS_RESTRICTED_MODULE value=$RECORD_MODEL->isRestrictedModule($MODULE_NAME)}
						<tr>
							<td class="verticalAlignMiddleImp">
								<input class="modulesCheckBox" type="checkbox" name="permissions[{$TABID}][is_permitted]" data-value="{$TABID}" data-module-state="" {if $RECORD_MODEL->hasModulePermission($PROFILE_MODULE)}checked="true"{else} data-module-unchecked="true" {/if}> {$PROFILE_MODULE->get('label')|vtranslate:$PROFILE_MODULE->getName()}
							</td>
							{assign var="BASIC_ACTION_ORDER" value=array(2,3,0,1)}
							{foreach from=$BASIC_ACTION_ORDER item=ORDERID}
								<td class="textAlignCenter verticalAlignMiddleImp">
									{assign var="ACTION_MODEL" value=$ALL_BASIC_ACTIONS[$ORDERID]}
									{assign var=ACTION_ID value=$ACTION_MODEL->get('actionid')}
									{if !$IS_RESTRICTED_MODULE && $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
										<input class="action{$ACTION_ID}CheckBox" type="checkbox" name="permissions[{$TABID}][actions][{$ACTION_ID}]" data-action-state="{$ACTION_MODEL->getName()}" {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTION_MODEL)}checked="true"{elseif empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {else} data-action{$ACTION_ID}-unchecked="true"{/if}></td>
									{/if}
								</td>
							{/foreach}
							<td class="textAlignCenter">
								{if ($PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule() && $PROFILE_MODULE->isProfileLevelUtilityAllowed()) || $PROFILE_MODULE->isUtilityActionEnabled()}
									<button type="button" data-handlerfor="fields" data-togglehandler="{$TABID}-fields" class="btn btn-default btn-sm" style="padding-right: 20px; padding-left: 20px;">
										<i class="fa fa-chevron-down"></i>
									</button>
								{/if}
							</td>
						</tr>
						<tr class="hide">
							<td colspan="6" class="row" style="padding-left: 5%;padding-right: 5%">
								<div class="row" data-togglecontent="{$TABID}-fields" style="display: none">
									{if $PROFILE_MODULE->getFields() && $PROFILE_MODULE->isEntityModule() }
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
												<span style="margin: 0 20px;">{vtranslate('LBL_WRITE',$QUALIFIED_MODULE)}</span>
											</div>
											<div class="clearfix"></div>
										</div>
										<table class="table table-bordered no-border">
											{assign var=COUNTER value=0}
											{foreach from=$PROFILE_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
												{assign var='FIELD_ID' value=$FIELD_MODEL->getId()}
												{if $FIELD_MODEL->isActiveField() && $FIELD_MODEL->get('uitype') != '83' && $FIELD_MODEL->get('displaytype') neq '6'}
													{if $COUNTER % 3 == 0}
														<tr>
													{/if}
													<td >
														<!-- Field will be locked iff that field is non editable or Mandatory or UIType 70.
														 But, we can't set emailoptout field to either Mandatory/non-editable/uitype70
														-->
														{assign var="FIELD_LOCKED" value=true}
														{if $FIELD_NAME neq 'emailoptout'}
															{assign var="FIELD_LOCKED" value=$RECORD_MODEL->isModuleFieldLocked($PROFILE_MODULE, $FIELD_MODEL)}
														{/if}
														<input type="hidden" name="permissions[{$TABID}][fields][{$FIELD_ID}]" data-range-input="{$FIELD_ID}" value="{$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}" readonly="true">
														<div class="mini-slider-control editViewMiniSlider pull-left" data-locked="{$FIELD_LOCKED}" data-range="{$FIELD_ID}" data-value="{$RECORD_MODEL->getModuleFieldPermissionValue($PROFILE_MODULE, $FIELD_MODEL)}"></div>
														<div class="pull-left">
															{vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}&nbsp;{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
														</div>
													</td>
													{if $smarty.foreach.fields.last OR ($COUNTER+1) % 3 == 0}
														</tr>
													{/if}
												{assign var=COUNTER value=$COUNTER+1}
												{else if $FIELD_MODEL->get('displaytype') eq '6' || $FIELD_MODEL->getName() eq 'adjusted_amount'}
													<input type='hidden' name='permissions[{$TABID}][fields][{$FIELD_ID}]' value='2' />
												{/if}
											{/foreach}
										</table>
										{if $MODULE_NAME eq 'Calendar'}
											{assign var=EVENT_MODULE value=$PROFILE_MODULES[16]}
											{assign var=COUNTER value=0}
											<label class="pull-left"><strong>{vtranslate('LBL_FIELDS', $QUALIFIED_MODULE)} {vtranslate('LBL_OF', $EVENT_MODULE->getName())} {vtranslate('LBL_EVENTS', $EVENT_MODULE->getName())}</strong></label>
											<table class="table table-bordered">
												{foreach from=$EVENT_MODULE->getFields() key=FIELD_NAME item=FIELD_MODEL name="fields"}
													{if $FIELD_MODEL->isActiveField()}
														{assign var="FIELD_ID" value=$FIELD_MODEL->getId()}
														{if $COUNTER % 3 == 0}
															<tr>
															{/if}
															<td>
																{assign var="FIELD_LOCKED" value=$RECORD_MODEL->isModuleFieldLocked($EVENT_MODULE, $FIELD_MODEL)}
																<input type="hidden" name="permissions[16][fields][{$FIELD_ID}]" data-range-input="{$FIELD_ID}" value="{$RECORD_MODEL->getModuleFieldPermissionValue($EVENT_MODULE, $FIELD_MODEL)}" readonly="true">
																<div class="mini-slider-control editViewMiniSlider pull-left" data-locked="{$FIELD_LOCKED}" data-range="{$FIELD_ID}" data-value="{$RECORD_MODEL->getModuleFieldPermissionValue($EVENT_MODULE, $FIELD_MODEL)}"></div>
																<div class="pull-left">
																	{vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}&nbsp;{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if} 
																</div>
															</td>
															{if $smarty.foreach.fields.last OR ($COUNTER+1) % 3 == 0}
															</tr>
														{/if}
														{assign var=COUNTER value=$COUNTER+1}
													{/if}
												{/foreach}
											</table>
										{/if}
									{/if}
								</div>
							</td>
						</tr>
						<tr class="hide {$PROFILE_MODULE->getName()}_ACTIONS">
							{assign var=UTILITY_ACTION_COUNT value=0}
							{assign var="ALL_UTILITY_ACTIONS_ARRAY" value=array()}
							{foreach from=$ALL_UTILITY_ACTIONS item=ACTION_MODEL}
								{if $ACTION_MODEL->isModuleEnabled($PROFILE_MODULE)}
									{assign var="testArray" array_push($ALL_UTILITY_ACTIONS_ARRAY,$ACTION_MODEL)}
								{/if}
							{/foreach}
							{if $ALL_UTILITY_ACTIONS_ARRAY}
								<td colspan="6" class="row" style="padding-left: 5%;padding-right: 5%;">
									<div class="row" data-togglecontent="{$TABID}-fields" style="display: none">
										<div class="col-sm-12">
											<label class="pull-left">
												<strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong>
											</label>
										</div>
										<table class="table table-bordered">
											<tr>
												{foreach from=$ALL_UTILITY_ACTIONS_ARRAY item=ACTION_MODEL name="actions"}
													{assign var=ACTIONID value=$ACTION_MODEL->get('actionid')}
													<td {if $smarty.foreach.actions.last && (($smarty.foreach.actions.index+1) % 3 neq 0)}
														{assign var="index" value=($smarty.foreach.actions.index+1) % 3}
														{assign var="colspan" value=4-$index}
														colspan="{$colspan}"
														{/if}>
														<input type="checkbox" {if empty($RECORD_ID) && empty($IS_DUPLICATE_RECORD)} checked="true" {/if} name="permissions[{$TABID}][actions][{$ACTIONID}]" {if $RECORD_MODEL->hasModuleActionPermission($PROFILE_MODULE, $ACTIONID)}checked="true"{/if} data-action-name='{$ACTION_MODEL->getName()}' data-action-tool='{$TABID}'> {vtranslate($ACTION_MODEL->getName(), $QUALIFIED_MODULE)}
													</td>
													{if ($smarty.foreach.actions.index+1) % 3 == 0}
														</tr><tr>
													{/if}
												{/foreach}
											</tr>
										</table>
									</div>
								</td>
							{/if}
						</tr>
					{/if}
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
