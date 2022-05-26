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
    <input type="hidden" name="conditions" id="advanced_filter" value='' />
    <input type="hidden" id="olderConditions" value='{ZEND_JSON::encode($WORKFLOW_MODEL->get('conditions'))}' />
    <input type="hidden" name="filtersavedinnew" value="{$WORKFLOW_MODEL->get('filtersavedinnew')}" />
    <div class="editViewHeader">
        <div class='row'>
           <div class="col-lg-12 col-md-12 col-lg-pull-0">
              <h4>{vtranslate('LBL_WORKFLOW_CONDITION', $QUALIFIED_MODULE)}</h4>
           </div>
        </div>
    </div>
    <hr style="margin-top: 0px !important;">
    <div class="editViewBody">
       <div class="editViewContents" style="padding-bottom: 0px;">
          <div class="form-group">
             <div class="col-sm-12">
                 {if $IS_FILTER_SAVED_NEW == false}
					<div class="alert alert-info">
						{vtranslate('LBL_CREATED_IN_OLD_LOOK_CANNOT_BE_EDITED',$QUALIFIED_MODULE)}
					</div>
					<div class="row">
						<span class="col-sm-6"><input type="radio" name="conditionstype" class="alignMiddle" checked=""/>&nbsp;&nbsp;<span class="alignMiddle">{vtranslate('LBL_USE_EXISTING_CONDITIONS',$QUALIFIED_MODULE)}</span></span>
						<span class="col-sm-6"><input type="radio" id="enableAdvanceFilters" name="conditionstype" class="alignMiddle recreate"/>&nbsp;&nbsp;<span class="alignMiddle">{vtranslate('LBL_RECREATE_CONDITIONS',$QUALIFIED_MODULE)}</span></span>
					</div><br>
				{/if}
                 <div id="advanceFilterContainer"  class="conditionsContainer {if $IS_FILTER_SAVED_NEW == false} zeroOpacity {/if}">
                     <div class="col-sm-12">
                         <div class="table table-bordered" style="padding: 5%">
                             {include file='AdvanceFilter.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE}
                         </div>
                     </div>
                     {include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE EXECUTION_CONDITION=$WORKFLOW_MODEL->get('execution_condition')}
                 </div>
             </div>
          </div>
       </div>
    </div>        
    <div class="editViewHeader">
        <div class='row'>
           <div class="col-lg-12 col-md-12 col-lg-pull-0">
              <h4>{vtranslate('LBL_WORKFLOW_ACTIONS', $QUALIFIED_MODULE)}</h4>
            </div>
        </div>
    </div>
    <hr style="margin-top: 0px !important;">
    <div class="editViewBody" id="workflow_action" style="padding-bottom: 15px;">
        <div style="padding-left: 15px;">
            <div class="btn-group">
               <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" type="button" aria-expanded="true">
                  <strong>{vtranslate('LBL_ADD_TASK',$QUALIFIED_MODULE)}</strong>&nbsp;&nbsp;
                  <span class="caret"></span>
               </button>
               <ul class="dropdown-menu" role="menu">
                    {foreach from=$TASK_TYPES item=TASK_TYPE}
                        <li><a class="cursorPointer" data-url="index.php{$TASK_TYPE->getV7EditViewUrl()}&for_workflow={$RECORD}">{vtranslate($TASK_TYPE->get('label'),$QUALIFIED_MODULE)}</a></li>
                    {/foreach}
               </ul>
            </div>
        </div>
        <div id="taskListContainer" style="min-height: 250px;">
           {include file='TasksList.tpl'|@vtemplate_path:$QUALIFIED_MODULE}	
        </div>
    </div>
{/strip}
