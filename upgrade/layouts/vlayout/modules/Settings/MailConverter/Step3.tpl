{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div class="span9 addMailBoxBlock">
	<form class="form-horizontal" id="ruleSave" method="post" name="step3">
	    <input type="hidden" name="module" value="{$MODULE_NAME}" />
	    <input type="hidden" name="parent" value="Settings" />
	    <input type="hidden" name="action" value="SaveRule" />
	    <input type="hidden" name="scannerId" value="{$SCANNER_ID}" />
	    <input type="hidden" name="record" value="{$RECORD_ID}" />
		<div class="addMailBoxStep">
	    {assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFiels()}
	    {foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
		<div class="control-group">
		    <div class="control-label">
                        <label>
                            {vtranslate($FIELD_MODEL->get('label'),$QUALIFIED_MODULE)}
                        </label>
                    </div>
		    <div class="controls" style="font-size: 10pt;">
			{assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
			{if $FIELD_DATA_TYPE eq 'picklist'}
			    {assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPickListValues()}
			    {if $FIELD_NAME eq 'subject'}
				<select name="subjectop" class="chzn-select" style="min-width:220px;float: left;">
                                    <option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
				    {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
					<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('subjectop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
				    {/foreach}
				</select>&nbsp;&nbsp;
				<input type="text" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" style="margin-left: 10px;" />
			    {elseif $FIELD_NAME eq 'body'}
				<select name="bodyop" class="chzn-select" style="min-width:220px;float: left;">
                                    <option value="" {if $RECORD_MODEL->get('bodyop') eq ""}selected{/if}>{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
				    {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
					<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('bodyop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
				    {/foreach}
				</select>&nbsp;&nbsp;
                <br><br>
				<textarea name="{$FIELD_MODEL->getName()}" class="boxSizingBorderBox" style="width:85%">{$RECORD_MODEL->get($FIELD_NAME)}</textarea>
			    {else}
				<select id="actions" name="action1" class="select2" style="min-width:220px">
				    {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
					<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get($FIELD_NAME) eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
				    {/foreach}
				</select>
			    {/if}
			{elseif $FIELD_DATA_TYPE eq 'radio'}
			    {assign var=RADIO_OPTIONS value=$FIELD_MODEL->getRadioOptions()}
                            {foreach key=RADIO_NAME item=RADIO_VALUE from=$RADIO_OPTIONS}
                                <label class="radio inline">
                                    <input class="radio" type="radio" name="{$FIELD_MODEL->getName()}" value="{$RADIO_NAME}" 
				    {if $DEFAULT_MATCH eq $RADIO_NAME} checked {/if} />
				{$RADIO_VALUE}
			    </label>
			{/foreach}
		    {elseif $FIELD_DATA_TYPE eq 'email'}
			<input type="text" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" data-validation-engine="validate[funcCall[Vtiger_Email_Validator_Js.invokeValidation]]"/>
		    {else}
			<input type="text" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
		    {/if}	
		</div>
	    </div>	
	{/foreach}
	<div id="assignedToBlock">
	    <div class="control-label">
		<label>
		    {vtranslate('Assigned To')}
		</label>
	    </div>
	    <div class="controls">
		<select class="chzn-select" id="assignedTo" name="assignedTo">
		    <optgroup label="{vtranslate('LBL_USERS')}">
			{assign var=USERS value=$USER_MODEL->getAccessibleUsersForModule($MODULE_NAME)}
			{foreach key=OWNER_ID item=OWNER_NAME from=$USERS}
			    <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
				{$OWNER_NAME}
			    </option>
			{/foreach}
		    </optgroup>
		    <optgroup label="{vtranslate('LBL_GROUPS')}">
			{assign var=GROUPS value=$USER_MODEL->getAccessibleGroups()}	
			{foreach key=OWNER_ID item=OWNER_NAME from=$GROUPS}
			    <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
				{$OWNER_NAME}
			    </option>
			{/foreach}
		    </optgroup>
		</select>
	    </div>
	</div>
	</div>
	<div class="pull-right" style="margin-top: 55px;">
	    <button class="btn btn-danger backStep" type="button" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>&nbsp;&nbsp;
	    <button class="btn btn-success" onclick="javascript:Settings_MailConverter_Edit_Js.thirdStep()"><strong>{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}</strong></button>
	    <a class="cancelLink" type="reset" onclick="javascript:window.history.go(-3);">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
	</div>
    </form>

</div>
{/strip}