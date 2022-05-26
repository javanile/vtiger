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
	<div class="row">
        <div class="col-lg-9">
            <div class="row">
                <div class="col-lg-2" style="position:relative;top:4px;padding-right: 0px;">
                    {vtranslate('LBL_MODULES_TO_CREATE_RECORD',$QUALIFIED_MODULE)} <span class="redColor">*</span>
                </div>
                <div class="col-lg-10">
                    {assign var=RELATED_MODULES_INFO value=$WORKFLOW_MODEL->getDependentModules()}
                    {assign var=RELATED_MODULES value=$RELATED_MODULES_INFO|array_keys}
                    {assign var=RELATED_MODULE_MODEL_NAME value=$TASK_OBJECT->entity_type}

                    <select class="select2" id="createEntityModule" name="entity_type" data-rule-required="true" style="min-width: 150px;">
                        <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                        {foreach from=$RELATED_MODULES item=MODULE}
                            <option {if $TASK_OBJECT->entity_type eq $MODULE} selected="" {/if} value="{$MODULE}">{vtranslate($MODULE,$MODULE)}</option>
                        {/foreach}	
                    </select>
					{*let not show this message since workflow will never end in loop*}
                    {*<span class='sameModuleError alert alert-danger hide' style='margin-left:20px;margin-bottom:0px'>
                        {vtranslate('LBL_SAME_MODULE_ERROR_MIGHT_END_IN_LOOP',$QUALIFIED_MODULE)}
                    </span>*}
                </div>
            </div>
        </div>
	</div><br>
	<div id="addCreateEntityContainer" style="margin-bottom: 70px;">
		{include file="CreateEntity.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}
