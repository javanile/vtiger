{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/TaxAjax.php *}

{strip}
	{assign var=TAX_MODEL_EXISTS value=true}
	{assign var=TAX_ID value=$TAX_RECORD_MODEL->getId()}
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	{if empty($TAX_ID)}
		{assign var=TAX_MODEL_EXISTS value=false}
	{/if}
	<div class="taxModalContainer modal-dialog modal-xs">
        <div class="modal-content">
            <form id="editTax" class="form-horizontal" method="POST">
                {if $TAX_MODEL_EXISTS}
                    {assign var=TITLE value={vtranslate('LBL_EDIT_TAX', $QUALIFIED_MODULE)}}
                {else}
                    {assign var=TITLE value={vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                    
                <input type="hidden" name="taxid" value="{$TAX_ID}" />
                <input type="hidden" name="type" value="{$TAX_TYPE}" />
                <div class="modal-body" id="scrollContainer">
                    <div class="">   
                        <div class="block nameBlock row">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <input class="inputElement" type="text" name="taxlabel" placeholder="{vtranslate('LBL_ENTER_TAX_NAME', $QUALIFIED_MODULE)}" value="{$TAX_RECORD_MODEL->getName()}" data-rule-required="true" data-prompt-position="bottomLeft" />
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                            
                        <div class="block statusBlock row">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" name="deleted" value="1" />
                                <label>
                                    <input type="checkbox" name="deleted" value="0" class="taxStatus" {if $TAX_RECORD_MODEL->isDeleted() eq 0 OR !$TAX_ID} checked {/if} />
                                    <span>&nbsp;&nbsp;{vtranslate('LBL_TAX_STATUS_DESC', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                        
                        {if $TAX_MODEL_EXISTS eq false}
                            <div class="block taxCalculationBlock row">
                                <div class="col-lg-1"></div>
                                <div class="col-lg-3">
                                    <label class="pull-right">{vtranslate('LBL_TAX_CALCULATION', $QUALIFIED_MODULE)}</label>
                                </div>
                                <div class="col-lg-7">
                                    <label class="span radio-group" id="simple"><input type="radio" name="method" class="input-medium" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Simple' OR !$TAX_ID}checked{/if} value="Simple" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_SIMPLE', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                                    <label class="span radio-group" id="compound"><input type="radio" name="method" class="input-medium" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Compound'}checked{/if} value="Compound" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_COMPOUND', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                                    {if $TAX_TYPE neq 1}
                                        <label class="span radio-group" id="deducted"><input type="radio" name="method" class="input-medium" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Deducted'}checked{/if} value="Deducted" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_DEDUCTED', $QUALIFIED_MODULE)}</span></label>
                                    {/if}
                                </div>
                                <div class="col-lg-1"></div>
                            </div>
                        {else}
                            <input type="hidden" name="method" value="{$TAX_RECORD_MODEL->getTaxMethod()}" />
                        {/if}
                        
                        <div class="block compoundOnContainer row {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Compound'}hide{/if}">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_COMPOUND_ON', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <div class="">
                                    {assign var=SELECTED_SIMPLE_TAXES value=$TAX_RECORD_MODEL->getTaxesOnCompound()}
                                    <select data-placeholder="{vtranslate('LBL_SELECT_SIMPLE_TAXES', $QUALIFIED_MODULE)}" id="compoundOn" class="select2 inputEle" multiple="" name="compoundon" data-rule-required="true">
                                        {foreach key=SIMPLE_TAX_ID item=SIMPLE_TAX_MODEL from=$SIMPLE_TAX_MODELS_LIST}
                                            <option value="{$SIMPLE_TAX_ID}" {if !empty($SELECTED_SIMPLE_TAXES) && in_array($SIMPLE_TAX_ID, $SELECTED_SIMPLE_TAXES)}selected=""{/if}>{$SIMPLE_TAX_MODEL->getName()} ({$SIMPLE_TAX_MODEL->getTax()}%)</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                                    
                        <div class="block taxTypeContainer row {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Deducted'}hide{/if}">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_TAX_TYPE', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <label class="span radio-group" id="fixed"><input type="radio" name="taxType" class="input-medium" {if $TAX_RECORD_MODEL->getTaxType() eq 'Fixed' OR !$TAX_ID}checked{/if} value="Fixed" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_FIXED', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                                <label class="span radio-group" id="variable"><input type="radio" name="taxType" class="input-medium" {if $TAX_RECORD_MODEL->getTaxType() eq 'Variable'}checked{/if} value="Variable" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_VARIABLE', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                            
                        <div class="block taxValueContainer row {if $TAX_RECORD_MODEL->getTaxType() eq 'Variable'}hide{/if}">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <div class="input-group" style="min-height:30px;">
                                    <span class="input-group-addon">%</span>
                                    <input class="inputElement" type="text" name="percentage" placeholder="{vtranslate('LBL_ENTER_TAX_VALUE', $QUALIFIED_MODULE)}" value="{$TAX_RECORD_MODEL->getTax()}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                </div>
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                                
                        <div class="control-group dedcutedTaxDesc {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Deducted'}hide{/if}">
                            <div style="text-align:center;"><i class="fa fa-info-circle"></i> {vtranslate('LBL_DEDUCTED_TAX_DISC', $QUALIFIED_MODULE)}</div><br><br>
                        </div>
                        
                        <div class="block regionsContainer row {if $TAX_RECORD_MODEL->getTaxType() neq 'Variable'}hide{/if}" style="padding: 0px 40px;">
                            <table class="table table-bordered regionsTable">
                                <tr>
                                    <th class="{$WIDTHTYPE}" style="width:70%;"><strong>{vtranslate('LBL_REGIONS', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="{$WIDTHTYPE}" style="text-align: center; width:30%;"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}&nbsp;(%)</strong></th>
                                </tr>
                                <tr>
                                    <td class="{$WIDTHTYPE}">
                                        <label>{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                                    </td>
                                    <td class="{$WIDTHTYPE}" style="text-align: center;">
                                        <input class="inputElement smallInputBox input-medium" type="text" name="defaultPercentage" value="{$TAX_RECORD_MODEL->getTax()}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                    </td>
                                </tr>
                                {assign var=i value=0}
                                {foreach item=REGIONS_INFO name=i from=$TAX_RECORD_MODEL->getRegionTaxes()}
                                    <tr>
                                        <td class="regionsList {$WIDTHTYPE}">
                                            <div class="deleteRow close" style="float:left;margin-top:2px;">×</div>&nbsp;&nbsp;
                                            <select id="{$i}" data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" name="regions[{$i}][list]" class="regions select2 inputElement" multiple="" data-rule-required="true" style="width: 90%;">
                                                {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                    {assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
                                                    <option value="{$TAX_REGION_ID}" {if in_array($TAX_REGION_ID, $REGIONS_INFO['list'])}selected{/if}>{$TAX_REGION_MODEL->getName()}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td class="{$WIDTHTYPE}" style="text-align: center;">
                                            <input class="inputElement" type="text" name="regions[{$i}][value]" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                        </td>
                                    </tr>
                                    {assign var=i value=$i+1}
                                {/foreach}
                                <input type="hidden" class="regionsCount" value="{$i}" />
                            </table>
                            
                            <span class="addNewTaxBracket"><a href="#"><u>{vtranslate('LBL_ADD_TAX_BRACKET', $QUALIFIED_MODULE)}</u></a>
                                <select class="taxRegionElements hide">
                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                        <option value="{$TAX_REGION_MODEL->getId()}">{$TAX_REGION_MODEL->getName()}</option>
                                    {/foreach}
                                </select>
                            </span>
                                <br>
                                <br>
                            <div><i class="fa fa-info-circle"></i> {vtranslate('LBL_TAX_BRACKETS_DESC', $QUALIFIED_MODULE)}</div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}
