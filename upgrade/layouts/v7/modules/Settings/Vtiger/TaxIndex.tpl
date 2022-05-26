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
	<div class="col-lg-12 col-md-12 col-sm-12" id="TaxCalculationsContainer">
		<div class="editViewHeader">
			<h4>{vtranslate('LBL_TAX_CALCULATIONS', $QUALIFIED_MODULE)}</h4>
		</div>
		<hr>
		<br>
		<div class="contents tabbable clearfix">
			<ul class="nav nav-tabs layoutTabs massEditTabs">
				<li class="tab-item taxesTab active"><a data-toggle="tab" href="#taxes"><strong>{vtranslate('LBL_TAXES', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="tab-item chargesTab"><a data-toggle="tab" href="#charges"><strong>{vtranslate('LBL_CHARGES_AND ITS_TAXES', $QUALIFIED_MODULE)}</strong></a></li>
				<li class="tab-item taxRegionsTab"><a data-toggle="tab" href="#taxRegions"><strong>{vtranslate('LBL_TAX_REGIONS', $QUALIFIED_MODULE)}</strong></a></li>
			</ul>
			<div class="tab-content layoutContent padding20 overflowVisible">
				<div class="tab-pane active" id="taxes">
					<div class="col-lg-3 col-md-3 col-sm-3"></div>
					<div class="col-lg-6">
						{assign var=CREATE_TAX_URL value=$TAX_RECORD_MODEL->getCreateTaxUrl()}
						{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
						<div class="marginBottom10px">
							<button type="button" class="btn btn-default addTax addButton btn-default module-buttons" data-url="{$CREATE_TAX_URL}" data-type="0">
								<i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}
							</button>
						</div>
						<table class="table table-bordered inventoryTaxTable">
							<tr>
								<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</strong></th>
								<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</strong></th>
								<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_CALCULATION', $QUALIFIED_MODULE)}</strong></th>
								<th class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</strong></th>
								<th class="{$WIDTHTYPE}" colspan="2"><strong>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</strong></th>
							</tr>
							{foreach item=PRODUCT_SERVICE_TAX_MODEL from=$PRODUCT_AND_SERVICES_TAXES}
								<tr class="opacity" data-taxid="{$PRODUCT_SERVICE_TAX_MODEL->get('taxid')}" data-taxtype="{$PRODUCT_SERVICE_TAX_MODEL->getType()}">
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}"><span class="taxLabel" style="width:120px">{$PRODUCT_SERVICE_TAX_MODEL->getName()}</span></td>
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}"><span class="taxType">{$PRODUCT_SERVICE_TAX_MODEL->getTaxType()}</span></td>
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}"><span class="taxMethod">{$PRODUCT_SERVICE_TAX_MODEL->getTaxMethod()}</span></td>
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}"><span class="taxPercentage">{$PRODUCT_SERVICE_TAX_MODEL->getTax()}%</span></td>
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}"><input type="checkbox" class="editTaxStatus" {if !$PRODUCT_SERVICE_TAX_MODEL->isDeleted()}checked{/if} /></td>
									<td style="border-left:none;border-right:none;" class="{$WIDTHTYPE}">
										<div class="pull-right actions">
											<a class="editTax cursorPointer" data-url="{$PRODUCT_SERVICE_TAX_MODEL->getEditTaxUrl()}"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil alignMiddle"></i></a>&nbsp;
										</div>
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
				<div class="tab-pane" id="charges"></div>
				<div class="tab-pane" id="taxRegions"></div>
			</div>
		</div>
	</div>
{/strip}