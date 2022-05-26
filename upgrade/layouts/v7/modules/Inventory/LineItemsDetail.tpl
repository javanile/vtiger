{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Inventory/views/Detail.php *}

{assign var=ITEM_DETAILS_BLOCK value=$BLOCK_LIST['LBL_ITEM_DETAILS']}
{assign var=LINEITEM_FIELDS value=$ITEM_DETAILS_BLOCK->getFields()}

{assign var=COL_SPAN1 value=0}
{assign var=COL_SPAN2 value=0}
{assign var=COL_SPAN3 value=2}
{assign var=IMAGE_VIEWABLE value=false}
{assign var=PRODUCT_VIEWABLE value=false}
{assign var=QUANTITY_VIEWABLE value=false}
{assign var=PURCHASE_COST_VIEWABLE value=false}
{assign var=LIST_PRICE_VIEWABLE value=false}
{assign var=MARGIN_VIEWABLE value=false}
{assign var=COMMENT_VIEWABLE value=false}
{assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=false}
{assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=false}
{assign var=SH_PERCENT_VIEWABLE value=false}
{assign var=DISCOUNT_AMOUNT_VIEWABLE value=false}
{assign var=DISCOUNT_PERCENT_VIEWABLE value=false}

{if $LINEITEM_FIELDS['image']}
    {assign var=IMAGE_VIEWABLE value=$LINEITEM_FIELDS['image']->isViewable()}
{if $IMAGE_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['productid']}
    {assign var=PRODUCT_VIEWABLE value=$LINEITEM_FIELDS['productid']->isViewable()}
{if $PRODUCT_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['quantity']}
    {assign var=QUANTITY_VIEWABLE value=$LINEITEM_FIELDS['quantity']->isViewable()}
{if $QUANTITY_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['purchase_cost']}
    {assign var=PURCHASE_COST_VIEWABLE value=$LINEITEM_FIELDS['purchase_cost']->isViewable()}
{if $PURCHASE_COST_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['listprice']}
    {assign var=LIST_PRICE_VIEWABLE value=$LINEITEM_FIELDS['listprice']->isViewable()}
{if $LIST_PRICE_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['margin']}
    {assign var=MARGIN_VIEWABLE value=$LINEITEM_FIELDS['margin']->isViewable()}
{if $MARGIN_VIEWABLE}{assign var=COL_SPAN3 value=($COL_SPAN3)+1}{/if}
{/if}
{if $LINEITEM_FIELDS['comment']}
    {assign var=COMMENT_VIEWABLE value=$LINEITEM_FIELDS['comment']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['discount_amount']}
    {assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['discount_amount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['discount_percent']}
    {assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['discount_percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnS_H_Percent']}
    {assign var=SH_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountAmount']}
    {assign var=DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isViewable()}
{/if}
{if $LINEITEM_FIELDS['hdnDiscountPercent']}
    {assign var=DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isViewable()}
{/if}

<input type="hidden" class="isCustomFieldExists" value="false">

{assign var=FINAL_DETAILS value=$RELATED_PRODUCTS.1.final_details}
<div class="details block">
    <div class="lineItemTableDiv">
        <table class="table table-bordered lineItemsTable" style = "margin-top:15px">
            <thead>
            <th colspan="{$COL_SPAN1}" class="lineItemBlockHeader">
                {assign var=REGION_LABEL value=vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}
                {if $RECORD->get('region_id') && $LINEITEM_FIELDS['region_id'] && $LINEITEM_FIELDS['region_id']->isViewable()}
                    {assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel($RECORD->get('region_id'))}
                    {if $TAX_REGION_MODEL}
                        {assign var=REGION_LABEL value="{vtranslate($LINEITEM_FIELDS['region_id']->get('label'), $MODULE_NAME)} : {$TAX_REGION_MODEL->getName()}"}
                    {/if}
                {/if}
                {$REGION_LABEL}
            </th>
            <th colspan="{$COL_SPAN2}" class="lineItemBlockHeader">
                {assign var=CURRENCY_INFO value=$RECORD->getCurrencyInfo()}
                {vtranslate('LBL_CURRENCY', $MODULE_NAME)} : {vtranslate($CURRENCY_INFO['currency_name'],$MODULE_NAME)}({$CURRENCY_INFO['currency_symbol']})
            </th>
            <th colspan="{$COL_SPAN3}" class="lineItemBlockHeader">
                {vtranslate('LBL_TAX_MODE', $MODULE_NAME)} : {vtranslate($FINAL_DETAILS.taxtype, $MODULE_NAME)}
            </th>
            </thead>
            <tbody>
                <tr>
                    {if $IMAGE_VIEWABLE}
                        <td class="lineItemFieldName">
                            <strong>{vtranslate({$LINEITEM_FIELDS['image']->get('label')},$MODULE)}</strong>
                        </td>
                    {/if}
                    {if $PRODUCT_VIEWABLE}
                        <td class="lineItemFieldName">
                            <span class="redColor">*</span><strong>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}

                    {if $QUANTITY_VIEWABLE}
                        <td class="lineItemFieldName">
                            <strong>{vtranslate({$LINEITEM_FIELDS['quantity']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    {if $PURCHASE_COST_VIEWABLE}
                        <td class="lineItemFieldName">
                            <strong>{vtranslate({$LINEITEM_FIELDS['purchase_cost']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    {if $LIST_PRICE_VIEWABLE}
                        <td style="white-space: nowrap;">
                            <strong>{vtranslate({$LINEITEM_FIELDS['listprice']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    <td class="lineItemFieldName">
                        <strong class="pull-right">{vtranslate('LBL_TOTAL',$MODULE_NAME)}</strong>
                    </td>
                    {if $MARGIN_VIEWABLE}
                        <td class="lineItemFieldName">
                            <strong class="pull-right">{vtranslate({$LINEITEM_FIELDS['margin']->get('label')},$MODULE_NAME)}</strong>
                        </td>
                    {/if}
                    <td class="lineItemFieldName">
                        <strong class="pull-right">{vtranslate('LBL_NET_PRICE',$MODULE_NAME)}</strong>
                    </td>
                </tr>
                {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
                    <tr>
                        {if $IMAGE_VIEWABLE}
                            <td style="text-align:center;">
                                <img src='{$LINE_ITEM_DETAIL["productImage$INDEX"]}' height="42" width="42">
                            </td>
                        {/if}

                        {if $PRODUCT_VIEWABLE}
                            <td>
                                <div>
                                    {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                        {$LINE_ITEM_DETAIL["productName$INDEX"]}
                                    {else}
                                        <h5><a class="fieldValue" href="index.php?module={$LINE_ITEM_DETAIL["entityType$INDEX"]}&view=Detail&record={$LINE_ITEM_DETAIL["hdnProductId$INDEX"]}" target="_blank">{$LINE_ITEM_DETAIL["productName$INDEX"]}</a></h5>
                                        {/if}
                                </div>
                                {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                    <div class="redColor deletedItem">
                                        {if empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                                            {vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
                                        {else}
                                            {vtranslate('LBL_THIS',$MODULE)} {$LINE_ITEM_DETAIL["entityType$INDEX"]} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
                                        {/if}
                                    </div>
                                {/if}
                                <div>
                                    {$LINE_ITEM_DETAIL["subprod_names$INDEX"]}
                                </div>
                                {if $COMMENT_VIEWABLE && !empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                                    <div>
                                        {decode_html($LINE_ITEM_DETAIL["comment$INDEX"])|nl2br}
                                    </div>
                                {/if}
                            </td>
                        {/if}

                        {if $QUANTITY_VIEWABLE}
                            <td>
                                {$LINE_ITEM_DETAIL["qty$INDEX"]}
                            </td>
                        {/if}

                        {if $PURCHASE_COST_VIEWABLE}
                            <td>
                                {$LINE_ITEM_DETAIL["purchaseCost$INDEX"]}
                            </td>
                        {/if}

                        {if $LIST_PRICE_VIEWABLE}
                            <td style="white-space: nowrap;">
                                <div>
                                    {$LINE_ITEM_DETAIL["listPrice$INDEX"]}
                                </div>
                                {if $ITEM_DISCOUNT_AMOUNT_VIEWABLE || $ITEM_DISCOUNT_PERCENT_VIEWABLE}
                                    <div>
                                        {assign var=DISCOUNT_INFO value="{if $LINE_ITEM_DETAIL["discount_type$INDEX"] == 'amount'} {vtranslate('LBL_DIRECT_AMOUNT_DISCOUNT',$MODULE_NAME)} = {$LINE_ITEM_DETAIL["discountTotal$INDEX"]}
									{elseif $LINE_ITEM_DETAIL["discount_type$INDEX"] == 'percentage'} {$LINE_ITEM_DETAIL["discount_percent$INDEX"]} % {vtranslate('LBL_OF',$MODULE_NAME)} {$LINE_ITEM_DETAIL["productTotal$INDEX"]} = {$LINE_ITEM_DETAIL["discountTotal$INDEX"]}
									{/if}"}
                                        (-)&nbsp; <strong><a href="javascript:void(0)" class="individualDiscount inventoryLineItemDetails" tabindex="0" role="tooltip" id ="example" data-toggle="popover" data-trigger="focus" title="{vtranslate('LBL_DISCOUNT',$MODULE_NAME)}" data-content="{$DISCOUNT_INFO}">{vtranslate('LBL_DISCOUNT',$MODULE_NAME)}</a> : </strong>
                                    </div>
                                {/if}
                                <div>
                                    <strong>{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} :</strong>
                                </div>
                                {if $FINAL_DETAILS.taxtype neq 'group'}
                                    <div class="individualTaxContainer">
                                        {assign var=INDIVIDUAL_TAX_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT', $MODULE_NAME)} = {$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}<br /><br />{foreach item=tax_details from=$LINE_ITEM_DETAIL['taxes']}{if $LINEITEM_FIELDS["{$tax_details['taxname']}"]}{$tax_details['taxlabel']} : \t{$tax_details['percentage']}%  {vtranslate('LBL_OF',$MODULE_NAME)}  {if $tax_details['method'] eq 'Compound'}({/if}{$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}{if $tax_details['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$tax_details['compoundon']}{if $FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']} + {$FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']}{/if}{/foreach}){/if} = {$tax_details['amount']}<br />{/if}{/foreach}<br /><br />{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$LINE_ITEM_DETAIL["taxTotal$INDEX"]}"}
                                        (+)&nbsp;<strong><a href="javascript:void(0)" class="individualTax inventoryLineItemDetails" tabindex="0" role="tooltip" id="example" title ="{vtranslate('LBL_TAX',$MODULE_NAME)}" data-trigger ="focus" data-toggle ="popover" data-content="{$INDIVIDUAL_TAX_INFO}">{vtranslate('LBL_TAX',$MODULE_NAME)} </a> : </strong>
                                    </div>
                                {/if}
                            </td>
                        {/if}

                        <td>
                            <div align = "right">{$LINE_ITEM_DETAIL["productTotal$INDEX"]}</div>
                            {if $ITEM_DISCOUNT_AMOUNT_VIEWABLE || $ITEM_DISCOUNT_PERCENT_VIEWABLE}
                                <div align = "right">{$LINE_ITEM_DETAIL["discountTotal$INDEX"]}</div>           
                            {/if}
                            <div align = "right">{$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}</div>
                            {if $FINAL_DETAILS.taxtype neq 'group'}
                                <div align = "right">{$LINE_ITEM_DETAIL["taxTotal$INDEX"]}</div>
                            {/if}
                        </td>
                        {if $MARGIN_VIEWABLE}
                            <td><div align = "right">{$LINE_ITEM_DETAIL["margin$INDEX"]}</div></td>
							{/if}
                        <td>
                            <div align = "right">{$LINE_ITEM_DETAIL["netPrice$INDEX"]}</div>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <table class="table table-bordered lineItemsTable">
        <tr>
            <td width="83%">
                <div class="pull-right">
                    <strong>{vtranslate('LBL_ITEMS_TOTAL',$MODULE_NAME)}</strong>
                </div>
            </td>
            <td>
                <span class="pull-right">
                    <strong>{$FINAL_DETAILS["hdnSubTotal"]}</strong>
                </span>
            </td>
        </tr>
        {if $DISCOUNT_AMOUNT_VIEWABLE || $DISCOUNT_PERCENT_VIEWABLE}
            <tr>
                <td width="83%">
                    <div align="right">
                        {assign var=FINAL_DISCOUNT_INFO value="{vtranslate('LBL_FINAL_DISCOUNT_AMOUNT',$MODULE_NAME)} = {if $DISCOUNT_PERCENT_VIEWABLE && $FINAL_DETAILS['discount_type_final'] == 'percentage'} {$FINAL_DETAILS['discount_percentage_final']}	% {vtranslate('LBL_OF',$MODULE_NAME)} {$FINAL_DETAILS['hdnSubTotal']} = {/if}{$FINAL_DETAILS['discountTotal_final']}"}
                        (-)&nbsp;<strong><a class="inventoryLineItemDetails" href="javascript:void(0)" id="finalDiscount" tabindex="0" role="tooltip" data-trigger ="focus" data-placement="left" data-toggle = "popover" title= "{vtranslate('LBL_OVERALL_DISCOUNT',$MODULE_NAME)}" data-content="{$FINAL_DISCOUNT_INFO}">{vtranslate('LBL_OVERALL_DISCOUNT',$MODULE_NAME)}</a></strong>
                    </div>
                </td>
                <td>
                    <div align="right">
                        {$FINAL_DETAILS['discountTotal_final']}
                    </div>

                </td>
            </tr>
        {/if}
        {if $SH_PERCENT_VIEWABLE}
            <tr>
                <td width="83%">
                    <div align="right">
                        {assign var=CHARGES_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['totalAfterDiscount']}<br /><br />{foreach key=CHARGE_ID item=CHARGE_INFO from=$SELECTED_CHARGES_AND_ITS_TAXES} {if $CHARGE_INFO['deleted']}({strtoupper(vtranslate('LBL_DELETED',$MODULE_NAME))}){/if} {$CHARGE_INFO['name']} {if $CHARGE_INFO['percent']}: {$CHARGE_INFO['percent']}% {vtranslate('LBL_OF',$MODULE_NAME)} {$FINAL_DETAILS['totalAfterDiscount']}{/if} = {$CHARGE_INFO['amount']}<br />{/foreach}<br /><h5>{vtranslate('LBL_CHARGES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS['shipping_handling_charge']}</h5>"}
                        (+)&nbsp;<strong><a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" id="example" data-trigger="focus" data-placement ="left"  data-toggle="popover" title={vtranslate('LBL_CHARGES',$MODULE_NAME)} data-content="{$CHARGES_INFO}">{vtranslate('LBL_CHARGES',$MODULE_NAME)}</a></strong>
                    </div>
                </td>
                <td>
                    <div align="right">
                        {$FINAL_DETAILS["shipping_handling_charge"]}
                    </div>
                </td>
            </tr>
        {/if}
        <tr>
            <td width="83%">
                <div align="right">
                    <strong>{vtranslate('LBL_PRE_TAX_TOTAL', $MODULE_NAME)} </strong>
                </div>
            </td>
            <td>
                <div align="right">
                    {$FINAL_DETAILS["preTaxTotal"]}
                </div>
            </td>
        </tr>
        {if $FINAL_DETAILS.taxtype eq 'group'}
            <tr>
                <td width="83%">
                    <div align="right">
                        {assign var=GROUP_TAX_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['totalAfterDiscount']}<br /><br />{foreach item=tax_details from=$FINAL_DETAILS['taxes']}{$tax_details['taxlabel']} : \t{$tax_details['percentage']}% {vtranslate('LBL_OF',$MODULE_NAME)} {if $tax_details['method'] eq 'Compound'}({/if}{$FINAL_DETAILS['totalAfterDiscount']}{if $tax_details['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$tax_details['compoundon']}{if $FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']} + {$FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']}{/if}{/foreach}){/if} = {$tax_details['amount']}<br />{/foreach}<br />{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['tax_totalamount']}"}
                        (+)&nbsp;<strong><a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" id="finalTax" data-trigger ="focus" data-placement ="left" title = "{vtranslate('LBL_TAX',$MODULE_NAME)}" data-toggle ="popover" data-content="{$GROUP_TAX_INFO}">{vtranslate('LBL_TAX',$MODULE_NAME)}</a></strong>
                    </div>
                </td>
                <td>
                    <div align="right">
                        {$FINAL_DETAILS['tax_totalamount']}
                    </div>
                </td>
            </tr>
        {/if}
        {if $SH_PERCENT_VIEWABLE}
            <tr>
                <td width="83%">
                    <div align="right">
                        {assign var=CHARGES_TAX_INFO value="{vtranslate('LBL_CHARGES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS["shipping_handling_charge"]}<br /><br />{foreach key=CHARGE_ID item=CHARGE_INFO from=$SELECTED_CHARGES_AND_ITS_TAXES}{if $CHARGE_INFO['taxes']}{if $CHARGE_INFO['deleted']}({strtoupper(vtranslate('LBL_DELETED',$MODULE_NAME))}){/if} {$CHARGE_INFO['name']}<br />{foreach item=CHARGE_TAX_INFO from=$CHARGE_INFO['taxes']}&emsp;{$CHARGE_TAX_INFO['name']}: &emsp;{$CHARGE_TAX_INFO['percent']}% {vtranslate('LBL_OF',$MODULE_NAME)} {if $CHARGE_TAX_INFO['method'] eq 'Compound'}({/if}{$CHARGE_INFO['amount']} {if $CHARGE_TAX_INFO['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$CHARGE_TAX_INFO['compoundon']}{if $CHARGE_INFO['taxes'][$COMPOUND_TAX_ID]['name']} + {$CHARGE_INFO['taxes'][$COMPOUND_TAX_ID]['name']}{/if}{/foreach}){/if} = {$CHARGE_TAX_INFO['amount']}<br />{/foreach}<br />{/if}{/foreach}\r\n{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['shtax_totalamount']}"}
                        (+)&nbsp;<strong><a class="inventoryLineItemDetails" tabindex="0" role="tooltip" title = "{vtranslate('LBL_TAXES_ON_CHARGES',$MODULE_NAME)}" data-trigger ="focus" data-placement ="left" data-toggle="popover"  href="javascript:void(0)" id="taxesOnChargesList" data-content="{$CHARGES_TAX_INFO}">
                                {vtranslate('LBL_TAXES_ON_CHARGES',$MODULE_NAME)} </a></strong>
                    </div>
                </td>
                <td>
                    <div align="right">
                        {$FINAL_DETAILS["shtax_totalamount"]}
                    </div>
                </td>
            </tr>
        {/if}
        <tr>
            <td width="83%">
                <div align="right">
                    {assign var=DEDUCTED_TAXES_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS["totalAfterDiscount"]}<br /><br />{foreach key=DEDUCTED_TAX_ID item=DEDUCTED_TAX_INFO from=$FINAL_DETAILS['deductTaxes']}{if $DEDUCTED_TAX_INFO['selected'] eq true}{$DEDUCTED_TAX_INFO['taxlabel']}: \t{$DEDUCTED_TAX_INFO['percentage']}%  = {$DEDUCTED_TAX_INFO['amount']}\r\n{/if}{/foreach}\r\n\r\n{vtranslate('LBL_DEDUCTED_TAXES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS['deductTaxesTotalAmount']}"}
                    (-)&nbsp;<strong><a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" id="deductedTaxesList" data-trigger="focus" data-toggle="popover" title = "{vtranslate('LBL_DEDUCTED_TAXES',$MODULE_NAME)}" data-placement ="left" data-content="{$DEDUCTED_TAXES_INFO}">
                            {vtranslate('LBL_DEDUCTED_TAXES',$MODULE_NAME)} </a></strong>
                </div>
            </td>
            <td>
                <div align="right">
                    {$FINAL_DETAILS['deductTaxesTotalAmount']}
                </div>
            </td>
        </tr>
        <tr>
            <td width="83%">
                <div align="right">
                    <strong>{vtranslate('LBL_ADJUSTMENT',$MODULE_NAME)}</strong>
                </div>
            </td>
            <td>
                <div align="right">
                    {$FINAL_DETAILS["adjustment"]}
                </div>
            </td>
        </tr>
        <tr>
            <td width="83%">
                <div align="right">
                    <strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE_NAME)}</strong>
                </div>
            </td>
            <td>
                <div align="right">
                    {$FINAL_DETAILS["grandTotal"]}
                </div>
            </td>
        </tr>
        {if $MODULE_NAME eq 'Invoice' or $MODULE_NAME eq 'PurchaseOrder'}
            <tr>
                <td width="83%">
                    {if $MODULE_NAME eq 'Invoice'}
                        <div align="right">
                            <strong>{vtranslate('LBL_RECEIVED',$MODULE_NAME)}</strong>
                        </div>
                    {else}
                        <div align="right">
                            <strong>{vtranslate('LBL_PAID',$MODULE_NAME)}</strong>
                        </div>
                    {/if}
                </td>
                <td>
                    {if $MODULE_NAME eq 'Invoice'}
                        <div align="right">
                            {if $RECORD->getDisplayValue('received')}
                                {$RECORD->getDisplayValue('received')}
                            {else}
                                0
                            {/if}
                        </div>
                    {else}
                        <div align="right">
                            {if $RECORD->getDisplayValue('paid')}
                                {$RECORD->getDisplayValue('paid')}
                            {else}
                                0
                            {/if}
                        </div>
                    {/if}
                </td>
            </tr>
            <tr>
                <td width="83%">
                    <div align="right">
                        <strong>{vtranslate('LBL_BALANCE',$MODULE_NAME)}</strong>
                    </div>
                </td>
                <td>
                    <div align="right">
                        {if $RECORD->getDisplayValue('balance')}
                            {$RECORD->getDisplayValue('balance')}
                        {else}0
                        {/if}
                    </div>
                </td>
            </tr>
        {/if}
    </table>
</div>