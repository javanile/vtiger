{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Picklist/views/IndexAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {if !empty($NO_PICKLIST_FIELDS) }
        <label style="padding-top: 40px;"> <b>
                {vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)} {vtranslate('NO_PICKLIST_FIELDS',$QUALIFIED_NAME)}. &nbsp; 
                {if !empty($CREATE_PICKLIST_URL)}
                    <a href="{$CREATE_PICKLIST_URL}">{vtranslate('LBL_CREATE_NEW',$QUALIFIED_NAME)}</a>
                {/if}
            </b>
        </label>
    {else}
        <div class="row form-group">
			<div class="col-lg-3 col-md-3 col-sm-3 control-label fieldLabel">
                <label class="fieldLabel"><strong>{vtranslate('LBL_SELECT_PICKLIST_IN',$QUALIFIED_MODULE)}&nbsp;{vtranslate($SELECTED_MODULE_NAME,$QUALIFIED_MODULE)}</strong></label>
            </div>
            <div class="col-sm-3 col-xs-3 fieldValue">
                <select class="select2 inputElement" id="modulePickList" name="modulePickList">
                    {foreach key=PICKLIST_FIELD item=FIELD_MODEL from=$PICKLIST_FIELDS}
                        <option value="{$FIELD_MODEL->getId()}" {if $DEFAULT_FIELD eq $FIELD_MODEL->getName()} selected {/if}>{vtranslate($FIELD_MODEL->get('label'),$SELECTED_MODULE_NAME)}</option>
                    {/foreach}
                </select>
            </div>
        </div><br>
    {/if}
{/strip}
