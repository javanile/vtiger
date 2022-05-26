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
	{assign var=CHARGE_ID value=$CHARGE_MODEL->getId()}
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	{assign var=CHARGE_FORMAT value=$CHARGE_MODEL->get('format')}
	{if $CHARGE_FORMAT eq 'Percent'}
		{assign var=IS_PERCENT_FORMAT value=true}
	{else}
		{assign var=IS_PERCENT_FORMAT value=false}
	{/if}
	<input type="hidden" value={$WIDTHTYPE} id="widthHeight">
	<div class="chargeModalContainer modal-dialog modal-xs">
        <div class="modal-content">
            <form id="editCharge" class="form-horizontal">
                {if !empty($CHARGE_ID)}
                    {assign var=TITLE value={vtranslate('LBL_EDIT_CHARGE', $QUALIFIED_MODULE)}}
                {else}
                    {assign var=TITLE value={vtranslate('LBL_ADD_NEW_CHARGE', $QUALIFIED_MODULE)}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                
                <input type="hidden" name="chargeid" value="{$CHARGE_ID}" />
                <div class="modal-body" id="scrollContainer">
                    <div class="">
                        
                        <div class="block row nameContainer">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3"><label class="pull-right">{vtranslate('LBL_CHARGE_NAME', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label></div>
                            <div class="col-lg-5">
                                <input class="inputElement" type="text" name="name" placeholder="{vtranslate('LBL_ENTER_CHARGE_NAME', $QUALIFIED_MODULE)}" value="{$CHARGE_MODEL->getName()}" data-rule-required="true" data-prompt-position="bottomLeft" />
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                            
                        <div class="row block formatContainer">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3"><label class="pull-right">{vtranslate('LBL_CHARGE_FORMAT', $QUALIFIED_MODULE)}</label></div>
                            <div class="col-lg-5">
                                <label class="span radio-group" id="flat"><input type="radio" name="format" class="input-medium" {if !$IS_PERCENT_FORMAT OR !$CHARGE_ID}checked{/if} value="Flat" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_DIRECT_PRICE', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                                <label class="span radio-group" id="percent"><input type="radio" name="format" class="input-medium" {if $IS_PERCENT_FORMAT}checked{/if} value="Percent" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_PERCENT', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                            </div>
                            <div class="col-lg-3"></div>
                        </div>
                            
                        <div class="row block typeContainer">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_CHARGE_TYPE', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <label class="span radio-group" id="fixed"><input type="radio" name="type" class="input-medium" {if $CHARGE_MODEL->get('type') eq 'Fixed' OR !$CHARGE_ID}checked{/if} value="Fixed" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_FIXED', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                                <label class="span radio-group" id="variable"><input type="radio" name="type" class="input-medium" {if $CHARGE_MODEL->get('type') eq 'Variable'}checked{/if} value="Variable" />&nbsp;&nbsp;<span class="radio-label">{vtranslate('LBL_VARIABLE', $QUALIFIED_MODULE)}</span></label>&nbsp;&nbsp;
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                            
                        <div class="row block chargeValueContainer {if $CHARGE_MODEL->get('type') eq 'Variable'}hide{/if}">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_CHARGE_VALUE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <div class="input-group">
                                    {assign var=CHARGE_VALUE value="{if $CHARGE_MODEL->getValue()}{number_format({$CHARGE_MODEL->getValue()}, getCurrencyDecimalPlaces(),'.','')}{else}0{/if}"}
                                     <span style="height:30px;width:30px;" class="input-group-addon percentIcon pull-left {if !$IS_PERCENT_FORMAT}hide{/if}">%</span>
                                    <input class="inputEle input-medium" type="text" name="value" placeholder="{vtranslate('LBL_ENTER_CHARGE_VALUE', $QUALIFIED_MODULE)}" value="{$CHARGE_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true"{else}data-rule-PositiveNumber="true"{/if} />
                                </div>
                            </div>
                            <div class="col-lg-3"></div>
                        </div>

                        <div class="row block regionsContainer {if $CHARGE_MODEL->get('type') neq 'Variable'}hide{/if}" style="padding: 0px 70px 0px 40px;">
                            <table class="table table-bordered regionsTable">
                                <tr>
                                    <th class="{$WIDTHTYPE}" style="width:60%;"><strong>{vtranslate('LBL_REGIONS', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="{$WIDTHTYPE}" style="text-align: center; width:40%;"><strong>{vtranslate('LBL_CHARGE_VALUE', $QUALIFIED_MODULE)}<span class="percentIcon {if !$IS_PERCENT_FORMAT}hide{/if}">&nbsp;(%)</span></strong></th>
                                </tr>
                                <tr>
                                    <td class="{$WIDTHTYPE}">
                                        <label>{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label></label>
                                    </td>
                                    <td class="{$WIDTHTYPE}" style="text-align: center;">
                                        <input class="inputElement input-medium" type="text" name="defaultValue" value="{$CHARGE_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true"{else}data-rule-PositiveNumber="true"{/if} />
                                    </td>
                                </tr>
                                {assign var=i value=0}
                                {foreach item=REGIONS_INFO name=i from=$CHARGE_MODEL->getSelectedRegions()}
                                    <tr>
                                        <td class="regionsList {$WIDTHTYPE}">
                                            <span class="deleteRow close" style="float:left;">×</span>&nbsp;
                                            <select id="{$i}" data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" name="regions[{$i}][list]" class="regions select2 columns span3" multiple="" data-rule-required="true" style="width:90%;">'
                                                {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                    {assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
                                                    <option value="{$TAX_REGION_ID}" {if in_array($TAX_REGION_ID, $REGIONS_INFO['list'])}selected{/if}>{$TAX_REGION_MODEL->getName()}</option>
                                                {/foreach}
                                            </select>
                                        </td>
                                        <td class="{$WIDTHTYPE}" style="text-align: center;">
                                            {assign var=REGION_VALUE value="{if $CHARGE_MODEL->getValue()}{number_format({$REGIONS_INFO['value']}, getCurrencyDecimalPlaces(),'.','')}{else}0{/if}"}
                                            <input class="inputElement valuesList input-medium" type="text" name="regions[{$i}][value]" value="{$REGION_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true"{else}data-rule-PositiveNumber="true"{/if} />
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
                            <br><br>
                            <div><i class="fa fa-info-circle"></i> {vtranslate('LBL_TAX_BRACKETS_DESC', $QUALIFIED_MODULE)}</div>
                            <br><br>
                        </div>
                        
                        <div class="row block">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3"><label class="pull-right">{vtranslate('LBL_IS_TAXABLE', $QUALIFIED_MODULE)}</label></div>
                            <div class="col-lg-7">
                                <input type="hidden" name="istaxable" value="0" />
                                <label>
                                    <input type="checkbox" name="istaxable" value="1" class="isTaxable alignBottom" {if $CHARGE_MODEL->get('istaxable') eq 1 OR !$CHARGE_ID} checked {/if} />
                                    &nbsp;&nbsp;<span>{vtranslate('LBL_ENABLE_TAXES_FOR_CHARGE', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                                
                        <div class="row block taxContainer {if $CHARGE_MODEL->get('istaxable') neq 1 AND $CHARGE_ID}hide{/if}">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_SELECT_TAX', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-7">
                                <div class="">
                                    <select data-placeholder="{vtranslate('LBL_SELECT_TAXES', $QUALIFIED_MODULE)}" id="selectTax" class="select2 columns inputEle" multiple="" name="taxes" data-rule-required="true">
                                        {foreach key=TAX_ID item=CHARGE_TAX_MODEL from=$CHARGE_TAXES}
                                            {if $CHARGE_TAX_MODEL->isDeleted() eq false}
                                                <option value="{$TAX_ID}" {if !empty($SELECTED_TAXES) && in_array($TAX_ID, $SELECTED_TAXES)}selected=""{/if}>{$CHARGE_TAX_MODEL->getName()} ({$CHARGE_TAX_MODEL->getTax()}%)</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="">({vtranslate('LBL_SELECT_TAX_DESC', $QUALIFIED_MODULE)})</div>
                            </div>
                            <div class="col-lg-1"></div>
                        </div>
                            
                        <div style="padding: 0px 40px;"><i class="fa fa-info-circle"></i> {vtranslate('LBL_CHARGE_STORE_DISC', $QUALIFIED_MODULE)} ({Vtiger_Functions::getCurrencyName(CurrencyField::getDBCurrencyId())})</div>
                        <br><br>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
	</div>
{/strip}