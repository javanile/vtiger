{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
   <div class="modal-dialog">
      <div id="convertPotentialContainer" class='modelContainer modal-content'>
         {assign var=PROJECT_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Project')}
         {if !$CONVERT_POTENTIAL_FIELDS['Project']}
            <input type="hidden" id="convertPotentialErrorTitle" value="{vtranslate('LBL_CONVERT_ERROR_TITLE',$MODULE)}"/>
            <input id="converPotentialtError" class="convertPotentialError" type="hidden" value="{vtranslate('LBL_CONVERT_POTENTIALS_ERROR',$MODULE)}"/>
         {else}
            {assign var=HEADER_TITLE value={vtranslate('LBL_CONVERT_POTENTIAL', $MODULE)}|cat:" "|cat:{$RECORD->getName()}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="convertPotentialForm" method="post" action="index.php">
               <input type="hidden" name="module" value="{$MODULE}"/>
               <input type="hidden" name="view" value="SaveConvertPotential"/>
               <input type="hidden" name="record" value="{$RECORD->getId()}"/>
               <input type="hidden" name="modules" value=''/>
               <div class="modal-body accordion container-fluid" id="potentialAccordion">
                  {foreach item=MODULE_FIELD_MODEL key=MODULE_NAME from=$CONVERT_POTENTIAL_FIELDS}
                     <div class="row">
                        <div class="col-lg-1"></div>
                        <div class="col-lg-10 moduleContent" style="border:1px solid #CCC;">
                           <div class="accordion-group convertPotentialModules">
                              <div class="header accordion-heading">
                                 <div data-parent="#potentialAccordion" data-toggle="collapse" class="accordion-toggle moduleSelection" href="#{$MODULE_NAME}_FieldInfo">
                                    <h5>
                                       <input id="{$MODULE_NAME}Module" class="convertPotentialModuleSelection alignBottom" data-module="{vtranslate($MODULE_NAME,$MODULE_NAME)}" value="{$MODULE_NAME}" type="checkbox" {if $MODULE_NAME eq 'Project'} checked="" {/if}/>
                                       {assign var=SINGLE_MODULE_NAME value="SINGLE_$MODULE_NAME"}
                                       &nbsp;&nbsp;&nbsp;{vtranslate('LBL_CREATE', $MODULE)}&nbsp;{vtranslate($SINGLE_MODULE_NAME, $MODULE_NAME)}
                                    </h5>
                                 </div>
                              </div>
                              <div id="{$MODULE_NAME}_FieldInfo" class="{$MODULE_NAME}_FieldInfo accordion-body collapse fieldInfo {if $CONVERT_POTENTIAL_FIELDS['Project'] && $MODULE_NAME == "Project"} in {/if}">
                                 <hr>
                                 {foreach item=FIELD_MODEL from=$MODULE_FIELD_MODEL}
                                     <div class="row">
                                         <div class="fieldLabel col-lg-4">
                                             <label class='muted pull-right'>
                                                 {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}&nbsp;
                                                 {if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if} 
                                             </label>
                                         </div>
                                         <div class="fieldValue col-lg-8">
                                             {include file=$FIELD_MODEL->getUITypeModel()->getTemplateName()|@vtemplate_path}
                                         </div>
                                     </div>
                                     <br>
                                 {/foreach}
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-1"></div>
                     </div>
                     <br>
                  {/foreach}
                  <div class="defaultFields">
                     <div class="row">
                        <div class="col-lg-1"></div>
                        <div class="col-lg-10" style="border:1px solid #CCC;">
                           <div style="margin-top:20px;margin-bottom: 20px;">
                              <div class="row">
                                 {assign var=FIELD_MODEL value=$ASSIGN_TO}
                                 <div class="fieldLabel col-lg-4">
                                    <label class='muted pull-right'>
                                       {vtranslate($FIELD_MODEL->get('label'), $MODULE_NAME)}&nbsp;
                                       <span class="redColor">*</span> 
                                    </label>
                                 </div>
                                 <div class="fieldValue col-lg-8">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-1"></div>
                     </div>
                  </div>
               </div>
               {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
            </form>
         {/if}
      </div>
   </div>
{/strip}