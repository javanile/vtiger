{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
   <div class="editViewPageDiv">
      <div class="col-sm-12 col-xs-12" id="EditView">
         <form name="EditWorkflow" action="index.php" method="post" id="workflow_edit" class="form-horizontal">
            {assign var=WORKFLOW_MODEL_OBJ value=$WORKFLOW_MODEL->getWorkflowObject()}
            <input type="hidden" name="record" value="{$RECORDID}" id="record" />
            <input type="hidden" name="module" value="Workflows" />
            <input type="hidden" name="action" value="SaveWorkflow" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" name="returnsourcemodule" value="{$RETURN_SOURCE_MODULE}" />
            <input type="hidden" name="returnpage" value="{$RETURN_PAGE}" />
            <input type="hidden" name="returnsearch_value" value="{$RETURN_SEARCH_VALUE}" />
            <div class="editViewHeader">
               <div class='row'>
                  <div class="col-lg-12 col-md-12 col-lg-pull-0">
                     <h4>{vtranslate('LBL_BASIC_INFORMATION', $QUALIFIED_MODULE)}</h4>
                  </div>
               </div>
            </div>
            <hr style="margin-top: 0px !important;">
            <div class="editViewBody">
                <div class="editViewContents" style="text-align: center; ">
                  <div class="form-group">
                     <label for="name" class="col-sm-3 control-label">
                        {vtranslate('LBL_WORKFLOW_NAME', $QUALIFIED_MODULE)}
                        <span class="redColor">*</span>
                     </label>
                     <div class="col-sm-5 controls">
                        <input class="form-control" id="name"  name="workflowname" value="{$WORKFLOW_MODEL_OBJ->workflowname}" data-rule-required="true">
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="name" class="col-sm-3 control-label">
                        {vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}
                     </label>
                     <div class="col-sm-5 controls">
                        <textarea class="form-control" name="summary" id="summary">{$WORKFLOW_MODEL->get('summary')}</textarea>
                     </div>
                  </div>
                  <div class="form-group">
                        <label for="module_name" class="col-sm-3 control-label">
                           {vtranslate('LBL_TARGET_MODULE', $QUALIFIED_MODULE)}
                        </label>
                     <div class="col-sm-5 controls">
                         {if $MODE eq 'edit'}
                             <div class="pull-left">
                                <input type='text' disabled='disabled' class="inputElement" value="{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
                                <input type='hidden' id="module_name" name='module_name' value="{$MODULE_MODEL->get('name')}" >
                             </div>
                         {else}
                             <select class="select2 col-sm-6 pull-left" id="module_name" name="module_name" required="true" data-placeholder="Select Module..." style="text-align: left">
                                 {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                                     {assign var=TARGET_MODULE_NAME value=$MODULE_MODEL->getName()}
                                     {assign var=SINGLE_MODULE value="SINGLE_$TARGET_MODULE_NAME"}
                                     <option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}
                                         data-create-label="{vtranslate($SINGLE_MODULE, $TARGET_MODULE_NAME)} {vtranslate('LBL_CREATION', $QUALIFIED_MODULE)}"
                                         data-update-label="{vtranslate($SINGLE_MODULE, $TARGET_MODULE_NAME)} {vtranslate('LBL_UPDATED', $QUALIFIED_MODULE)}"
                                         >
                                         {if $MODULE_MODEL->getName() eq 'Calendar'}
                                             {vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
                                         {else}
                                             {vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
                                         {/if}
                                     </option>
                                 {/foreach}
                             </select>
                         {/if}
                     </div>
                  </div>
                  <div class="form-group">
                     <label for="status" class="col-sm-3 control-label">
                        {vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}
                     </label>
                     <div class="col-sm-5 controls">
                        <div class="pull-left">
                            <span style="margin-right: 10px;">
                               <input name="status" type="radio" value="active" {if $WORKFLOW_MODEL_OBJ->status eq '1'} checked="" {/if}>&nbsp;
                               <span>{vtranslate('Active', $QUALIFIED_MODULE)}</span>
                            </span>
                            <span style="margin-right: 10px;">
                               <input name="status" type="radio" value="inActive" {if $WORKFLOW_MODEL_OBJ->status eq '0' or empty($WORKFLOW_MODEL_OBJ)} checked="" {/if}>&nbsp;
                               <span>{vtranslate('InActive', $QUALIFIED_MODULE)}</span>
                            </span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="editViewHeader">
               <div class='row'>
                  <div class="col-lg-12 col-md-12 col-lg-pull-0">
                     <h4>{vtranslate('LBL_WORKFLOW_TRIGGER', $QUALIFIED_MODULE)}</h4>
                  </div>
               </div>
            </div>
            <hr style="margin-top: 0px !important;">
            <div class="editViewBody">
               <div class="editViewContents" style="padding-bottom: 0px;">
                    {include file='WorkFlowTrigger.tpl'|@vtemplate_path:$QUALIFIED_MODULE}	
               </div>
            </div>
            <div id="workflow_condition">
            </div>
			<div class="modal-overlay-footer clearfix">
				<div class="row clearfix">
					<div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
						<button type='submit' class='btn btn-success saveButton' >{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
						<a class='cancelLink' href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</div>
				</div>
			</div>
         </form>
      </div>
   </div>
{/strip}