{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Groups/views/Edit.php *}

{strip}
	<div class="editViewPageDiv">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="editViewContainer">
				<form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal">
					<input type="hidden" name="module" value="Groups">
					<input type="hidden" name="action" value="Save">
					<input type="hidden" name="parent" value="Settings">
					<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}">
					<input type="hidden" name="mode" value="{$MODE}">
					<h4>
						{if !empty($MODE)}
							{vtranslate('LBL_EDITING', $MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)} - {$RECORD_MODEL->getName()}
						{else}
							{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)}
						{/if}
					</h4>
					<hr>
					<br>
					<div class="editViewBody">
						<div class="form-group row">
							<label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
								{vtranslate('LBL_GROUP_NAME', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span>
							</label>
							<div class="fieldValue col-lg-9 col-md-9 col-sm-9">
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-12">
										<input class="inputElement" type="text" name="groupname" value="{$RECORD_MODEL->getName()}" data-rule-required="true">
									</div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
								{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
							</label>
							<div class="fieldValue col-lg-9 col-md-9 col-sm-9">
								<div class="row">
									<div class="col-lg-6 col-md-6 col-sm-12">
										<input class="inputElement" type="text" name="description" id="description" value="{$RECORD_MODEL->getDescription()}" />
									</div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
								{vtranslate('LBL_GROUP_MEMBERS', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span>
							</label>
							<div class="fieldValue col-lg-9 col-md-9 col-sm-9">
								<div class="row">
									{assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers()}
									<div class="col-lg-6 col-md-6 col-sm-6">
										<select id="memberList" class="select2 inputElement" multiple="true" name="members[]" data-rule-required="true" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
											{foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
												<optgroup label="{vtranslate({$GROUP_LABEL}, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
													{foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
														{if $MEMBER->getName() neq $RECORD_MODEL->getName()}
															<option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{trim($MEMBER->getName())}</option>
														{/if}
													{/foreach}
												</optgroup>
											{/foreach}
										</select>
									</div>
									<div class="groupMembersColors col-lg-3 col-md-3 col-sm-6">
										<ul class="liStyleNone">
											<li class="Users textAlignCenter">{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</li>
											<li class="Groups textAlignCenter">{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}</li>
											<li class="Roles textAlignCenter">{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}</li>
											<li class="RoleAndSubordinates textAlignCenter">{vtranslate('LBL_ROLEANDSUBORDINATE', $QUALIFIED_MODULE)}</li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class='modal-overlay-footer clearfix'>
						<div class="row clearfix">
							<div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
								<button type='submit' class='btn btn-success saveButton' type="submit" >{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
								<a class='cancelLink' data-dismiss="modal" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}