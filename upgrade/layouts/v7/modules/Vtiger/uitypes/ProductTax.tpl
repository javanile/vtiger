{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
********************************************************************************/
-->*}

{strip}
	{assign var="tax_count" value=1}
	{foreach item=tax key=count from=$TAXCLASS_DETAILS}
		{if $tax.check_value eq 1}
			{assign var=check_value value="checked"}
			{assign var=show_value value="visible"}
		{else}
			{assign var=check_value value=""}
			{assign var=show_value value="hidden"}
		{/if}

		{if $tax_count gt 1}
			<td class="fieldLabel alignMiddle{if $PULL_RIGHT} pull-right{/if}">{*pull-right required in Quick create only*}
				<label class="">
				{/if}
				<span class="taxLabel alignBottom">{vtranslate($tax.taxlabel, $MODULE)}<span class="paddingLeft10px">(%)</span></span>
				<span style="padding-left: 10px;"><input style="top: 3px;" type="checkbox" name="{$tax.check_name}" id="{$tax.check_name}" class="taxes" data-tax-name={$tax.taxname} {$check_value}></span>
			</label>
		</td>
		<td class="fieldValue">
			{if $tax.type eq 'Fixed'}
				<input type="text" id="{$tax.taxname}" class="inputElement{if $show_value eq "hidden"} hide {else} show {/if}" name="{$tax.taxname}" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true" />
			{else}
				<div class="{if $show_value eq "hidden"}hide{/if}" id="{$tax.taxname}" style="width:70%;">
					<div class="regionsList">
						<table class="table table-bordered themeTableColor">
							<tr>
								<td class="{$WIDTHTYPE}" style="width:70%">
									<label>{vtranslate('LBL_DEFAULT', $QUALIFIED_MODULE)}</label>
								</td>
								<td class="{$WIDTHTYPE}" style="text-align: center; width:30%;">
									<input class="inputElement" type="text" name="{$tax.taxname}_defaultPercentage" value="{$tax.percentage}" data-rule-required="true" data-rule-inventory_percentage="true" style="width: 80px;" />
								</td>
							</tr>
							{assign var=i value=0}
							{foreach item=REGIONS_INFO name=i from=$tax.regions}
								<tr>
									<td>
										{foreach item=TAX_REGION_ID from=$REGIONS_INFO['list']}
											{assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel({$TAX_REGION_ID})}
											<input type="hidden" name="{$tax.taxname}_regions[{$i}][list][]" value="{$TAX_REGION_MODEL->getId()}" />
											<span class="label label-info displayInlineBlock" style="margin: 2px 1px;">{$TAX_REGION_MODEL->getName()}</span>
										{/foreach}
									</td>
									<td class="{$WIDTHTYPE}" style="text-align: center;">
										<input class="inputElement" type="text" name="{$tax.taxname}_regions[{$i}][value]" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true" style="width: 80px;" />
									</td>
								</tr>
								{assign var=i value=$i+1}
							{/foreach}
						</table>
					</div>
				</div>
			{/if}
		</td>
		{assign var="tax_count" value=$tax_count+1}
		{if $COUNTER eq 2}
		</tr><tr>
			{assign var="COUNTER" value=1}
		{else}
			{assign var="COUNTER" value=$COUNTER+1}
		{/if}
	{/foreach}
{strip}