{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Calendar/views/Calendar.php *}
{strip}
	<div class="modal-dialog modal-lg calendarSettingsContainer">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate('LBL_CALENDAR_SETTINGS', $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			{assign var=TRANSLATION_MODULE value="Users"}
			<div class="modal-body">
				<form class="form-horizontal" id="CalendarSettings" name="CalendarSettings" method="post" action="index.php">
					<input type="hidden" name="module" value="Users" />
					<input type="hidden" name="action" value="SaveCalendarSettings" />
					<input type="hidden" name="record" value="{$RECORD}" />
					<input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}' />
					<input type=hidden name="sourceView" />
					<div>
						<div style="margin-left: 20px;">
							{foreach item="FIELD_MODEL" from=$RECORD_STRUCTURE['LBL_CALENDAR_SETTINGS']}
								{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
								{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
								{if $FIELD_NAME eq 'callduration'}
									{assign var=CALL_DURATION_MODEL value=$FIELD_MODEL}
								{elseif $FIELD_NAME eq 'othereventduration'}
									{assign var=EVENT_DURATION_MODEL value=$FIELD_MODEL}
								{elseif $FIELD_NAME eq 'hour_format'}
									{assign var=HOUR_FORMAT_VALUE value=$FIELD_MODEL->get('fieldvalue')}
								{elseif $FIELD_NAME eq 'defaulteventstatus'}
									{assign var=DEFAULT_EVENT_STATUS_MODEL value=$FIELD_MODEL}
								{elseif $FIELD_NAME eq 'defaultactivitytype'}
									{assign var=DEFAULT_ACTIVITY_TYPE_MODEL value=$FIELD_MODEL}
								{elseif $FIELD_NAME eq 'hidecompletedevents'}
									{assign var=HIDE_COMPLETED_EVENTS_MODEL value=$FIELD_MODEL}
								{/if}
								{if $FIELD_NAME neq 'callduration' && $FIELD_NAME neq 'othereventduration' && $FIELD_NAME neq 'defaulteventstatus' && $FIELD_NAME neq 'defaultactivitytype' && $FIELD_NAME neq 'hidecompletedevents'}
									<div class="form-group">
										<label class="fieldLabel col-lg-4 col-sm-4 col-xs-4">{vtranslate($FIELD_MODEL->get('label'),$TRANSLATION_MODULE)}</label>
										<div class="fieldValue col-lg-8 col-sm-8 col-xs-8">
											{if $FIELD_NAME == 'hour_format' || $FIELD_NAME == 'activity_view'}
												{foreach key=ID item=LABEL from=$FIELD_MODEL->getPicklistValues()}
													{if $LABEL neq 'This Year' }
														<input type="radio" value="{$ID}" {if $FIELD_VALUE eq $ID}checked=""{/if} name="{$FIELD_NAME}" class="alignTop" />&nbsp;{vtranslate($LABEL,$MODULE)}&nbsp;{if $FIELD_NAME eq 'hour_format'}{vtranslate('LBL_HOUR',$MODULE)}{/if}&nbsp;&nbsp;&nbsp;
													{/if}
												{/foreach}
											{elseif $FIELD_NAME eq 'start_hour'}
												{assign var=DECODED_DAYS_STARTS value=ZEND_JSON::decode($DAY_STARTS)}
												{assign var=PICKLIST_VALUES value=$DECODED_DAYS_STARTS['hour_format'][$HOUR_FORMAT_VALUE][$FIELD_NAME]}
												<select class="select2" style="min-width: 150px;" name="{$FIELD_NAME}">
													{foreach key=ID item=LABEL from=$PICKLIST_VALUES}
														<option value="{$ID}" {if $FIELD_VALUE eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}</option>
													{/foreach}
												</select>
											{else}
												<select class="select2" name="{$FIELD_NAME}" {if $FIELD_NAME eq 'time_zone'} style="min-width: 350px" {else} style="min-width: 150px" {/if}>
													{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
													{foreach key=ID item=LABEL from=$FIELD_MODEL->getPicklistValues()}
														<option value="{$ID}" {if $FIELD_VALUE eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}</option>
													{/foreach}
												</select>
											{/if}
										</div>
									</div>
								{/if}
							{/foreach}
							{*For consisitent picklist values betweeen event status field and default event status fields*}
							{assign var=EVENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Events')}
							{assign var=EVENT_STATUS_MODEL value=$EVENTS_MODULE_MODEL->getField('eventstatus')}
							{assign var=ACTIVITY_TYPE_MODEL value=$EVENTS_MODULE_MODEL->getField('activitytype')}
							<div class="form-group">
								<label class="fieldLabel col-lg-4 col-sm-4 col-xs-4">{vtranslate('LBL_DEFAULT_STATUS_TYPE',$MODULE)}</label>
								<div class="fieldValue col-lg-8 col-sm-8 col-xs-8">
									<span class="alignMiddle">{vtranslate('LBL_STATUS',$MODULE)}</span>&nbsp;&nbsp;
									<select class="select2" style="min-width: 133px" name="{$DEFAULT_EVENT_STATUS_MODEL->get('name')}">
										<option value="{vtranslate('LBL_SELECT_OPTION',$MODULE)}">{vtranslate('LBL_SELECT_OPTION',$MODULE)}</option>
										{foreach key=ID item=LABEL from=$EVENT_STATUS_MODEL->getPicklistValues()}
											<option value="{$ID}" {if $DEFAULT_EVENT_STATUS_MODEL->get('fieldvalue') eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}</option>
										{/foreach}
									</select>&nbsp;&nbsp;&nbsp;
									<span class="alignMiddle">{vtranslate('LBL_TYPE',$MODULE)}</span>&nbsp;&nbsp;
									<select class="select2" style="min-width: 133px" name="{$DEFAULT_ACTIVITY_TYPE_MODEL->get('name')}">
										<option value="{vtranslate('LBL_SELECT_OPTION','Vtiger')}">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
										{foreach key=ID item=LABEL from=$ACTIVITY_TYPE_MODEL->getPicklistValues()}
											<option value="{$ID}" {if $DEFAULT_ACTIVITY_TYPE_MODEL->get('fieldvalue') eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="fieldLabel col-lg-4 col-sm-4 col-xs-4">{vtranslate('LBL_DEFAULT_EVENT_DURATION',$MODULE)}</label>
								<div class="fieldValue col-lg-8 col-sm-8 col-xs-8">
									<span class="alignMiddle">{vtranslate('LBL_CALL',$MODULE)}</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									<select class="select2" name="{$CALL_DURATION_MODEL->get('name')}">
										{foreach key=ID item=LABEL from=$CALL_DURATION_MODEL->getPicklistValues()}
											<option value="{$ID}" {if $CALL_DURATION_MODEL->get('fieldvalue') eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}&nbsp;{vtranslate('LBL_MINUTES',$MODULE)}</option>
										{/foreach}
									</select>&nbsp;&nbsp;&nbsp;
									<span class="alignMiddle">{vtranslate('LBL_OTHER_EVENTS',$MODULE)}</span>&nbsp;&nbsp;
									<select class="select2" name="{$EVENT_DURATION_MODEL->get('name')}">
										{foreach key=ID item=LABEL from=$EVENT_DURATION_MODEL->getPicklistValues()}
											<option value="{$ID}" {if $EVENT_DURATION_MODEL->get('fieldvalue') eq $ID} selected="" {/if}>{vtranslate($LABEL,$MODULE)}&nbsp;{vtranslate('LBL_MINUTES',$MODULE)}</option>
										{/foreach}
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="fieldLabel col-lg-4 col-sm-4 col-xs-4">{vtranslate($HIDE_COMPLETED_EVENTS_MODEL->get('label'),$MODULE)}</label>
								<div class="fieldValue col-lg-8 col-sm-8 col-xs-8">
									{include file=vtemplate_path($HIDE_COMPLETED_EVENTS_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$HIDE_COMPLETED_EVENTS_MODEL FIELD_NAME='hidecompletedevents'}
								</div>
							</div>
							{assign var=SHARED_TYPE value=$SHAREDTYPE}
							<div class="form-group">
								<label class="fieldLabel col-lg-4">{vtranslate('LBL_CALENDAR_SHARING',$MODULE)}</label>
								<div class="fieldValue col-lg-8 col-sm-8 col-xs-8" style="margin-top: -8px; padding-left: 35px;">
									<label class="radio inline"><input type="radio" value="private"{if $SHARED_TYPE == 'private'} checked="" {/if} name="sharedtype" />&nbsp;{vtranslate('Private',$MODULE)}&nbsp;</label>
									<label class="radio inline"><input type="radio" value="public" {if $SHARED_TYPE == 'public'} checked="" {/if} name="sharedtype" />&nbsp;{vtranslate('Public',$MODULE)}&nbsp;</label>
									<label class="radio inline"><input type="radio" value="selectedusers" {if $SHARED_TYPE == 'selectedusers'} checked="" {/if} data-sharingtype="selectedusers" name="sharedtype" id="selectedUsersSharingType" />&nbsp;{vtranslate('Selected Users',$MODULE)}</label><br><br>
									<select class="select2 row {if $SHARED_TYPE != 'selectedusers'} hide {/if}" id="selectedUsers" name="sharedIds[]" multiple="" data-placeholder="{vtranslate('LBL_SELECT_USERS',$MODULE)}">
										{foreach key=ID item=USER_MODEL from=$ALL_USERS}
											{if $ID neq $CURRENTUSER_MODEL->get('id')}
												<option value="{$ID}" {if array_key_exists($ID, $SHAREDUSERS)} selected="" {/if}>{vtranslate($USER_MODEL->getName(),$MODULE)}</option>
											{/if}
										{/foreach}
									</select>
								</div>
								</div>
							<br>
						</div>
					</div>
				</form>
			</div>
			{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
		</div>
	</div>
{/strip}