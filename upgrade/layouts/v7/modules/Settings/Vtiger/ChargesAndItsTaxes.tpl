{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/TaxIndex.php *}

{strip}
<div class="chargesContainer">
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	{assign var=CREATE_TAX_URL value=$TAX_RECORD_MODEL->getCreateTaxUrl()}

	<div class="col-lg-6">
		<div class="marginBottom10px">
			<button type="button" class="btn btn-default addCharge addButton module-buttons" data-url="{Inventory_Charges_Model::getCreateChargeUrl()}" data-type="1">
                <i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_NEW_CHARGE', $QUALIFIED_MODULE)}</button>
		</div>
		<table class="table table-bordered inventoryChargesTable">
			<tr>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_CHARGE_NAME', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_VALUE', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_IS_TAXABLE', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}" colspan="2"><strong>{vtranslate('LBL_TAXES', $QUALIFIED_MODULE)}</strong></th>
			</tr>
			{foreach item=CHARGE_MODEL from=$CHARGE_MODELS_LIST}
				<tr class="opacity" data-charge-id="{$CHARGE_MODEL->getId()}">
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="chargeName" style="width:100px;">{$CHARGE_MODEL->getName()}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="chargeValue" style="width:105px;">{$CHARGE_MODEL->getDisplayValue()}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="chargeIsTaxable">{if $CHARGE_MODEL->isTaxable()}{vtranslate('LBL_YES', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_NO', $QUALIFIED_MODULE)}{/if}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none">
						<span class="chargeTaxes" style="width:100px;">
							{assign var=TAXES value=''}
							{foreach item=TAX_MODEL from=$CHARGE_MODEL->getSelectedTaxes()}
								{assign var=TAXES value="{$TAXES}, {$TAX_MODEL->getName()}"}
							{/foreach}
							{trim($TAXES, ', ')}
						</span>
					</td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none">
						<div class="pull-right actions">
							<a class="editCharge cursorPointer" data-url="{$CHARGE_MODEL->getEditChargeUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil alignMiddle"></i></a>
						</div>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>

	<div class="col-lg-6">
		<div class="marginBottom10px">
			<button type="button" class="btn btn-default addChargeTax addButton module-buttons" data-url="{$CREATE_TAX_URL}" data-type="1">
                <i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_NEW_TAX_FOR_CHARGE', $QUALIFIED_MODULE)}</button>
		</div>
		<table class="table table-bordered shippingTaxTable">
			<tr>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_CALCULATION', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</strong></th>
				<th class="{$WIDTHTYPE}" colspan="2"><strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
			</tr>
			{foreach item=CHARGE_TAX_MODEL from=$CHARGE_TAXES}
				<tr class="opacity" data-taxid="{$CHARGE_TAX_MODEL->get('taxid')}" data-taxtype="{$CHARGE_TAX_MODEL->getType()}">
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="taxLabel" style="width:150px">{$CHARGE_TAX_MODEL->getName()}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="taxType">{$CHARGE_TAX_MODEL->getTaxType()}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="taxMethod">{$CHARGE_TAX_MODEL->getTaxMethod()}</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><span class="taxPercentage">{$CHARGE_TAX_MODEL->getTax()}%</span></td>
					<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none"><input type="checkbox" class="editTaxStatus" {if !$CHARGE_TAX_MODEL->isDeleted()}checked{/if} /></td>
					<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}">
						<div class="pull-right actions">
							<a class="editChargeTax cursorPointer" data-url="{$CHARGE_TAX_MODEL->getEditTaxUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil alignMiddle"></i></a>
						</div>
					</td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>
{/strip}
