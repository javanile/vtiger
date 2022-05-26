{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Products/views/MoreCurrenciesList.php *}

<div id="currency_class" class="multiCurrencyEditUI modelContainer">
	<div class = "modal-dialog modal-lg">
		<div class = "modal-content">
			{assign var=TITLE value="{vtranslate('LBL_PRICES',$MODULE)}"}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
			<div class="multiCurrencyContainer">
				<div class = "currencyContent">
					<div class = "modal-body">
						<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table listViewEntriesTable">
							<thead class="detailedViewHeader">
							<th>{vtranslate('LBL_CURRENCY',$MODULE)}</th>
							<th>{vtranslate('LBL_PRICE',$MODULE)}</th>
							<th>{vtranslate('LBL_CONVERSION_RATE', 'Products')}</th>
							<th>{vtranslate('LBL_RESET_PRICE',$MODULE)}</th>
							<th>{vtranslate('LBL_BASE_CURRENCY',$MODULE)}</th>
							</thead>
							{foreach item=price key=count from=$PRICE_DETAILS}
								<tr data-currency-id={$price.curname}>
									{if $price.check_value eq 1 || $price.is_basecurrency eq 1}
										{assign var=check_value value="checked"}
										{assign var=disable_value value=""}
									{else}
										{assign var=check_value value=""}
										{assign var=disable_value value="disabled=true"}
									{/if}

									{if $price.is_basecurrency eq 1}
										{assign var=base_cur_check value="checked"}
									{else}
										{assign var=base_cur_check value=""}
									{/if}
									<td>
										<div class="row col-lg-12">
											<div class="col-lg-10 currencyInfo"  style = "padding-left:5px">
												<span class="pull-left currencyName" >{$price.currencylabel|@getTranslatedCurrencyString} (<span class='currencySymbol'>{$price.currencysymbol}</span>)</span>
											</div>
											<div class="col-lg-2">
												<span><input type="checkbox" name="cur_{$price.curid}_check" id="cur_{$price.curid}_check" class="pull-right enableCurrency" {$check_value}></span>
											</div>
										</div>
									</td>
									<td>
										<div>
											<input {$disable_value} type="text" size="10" class="col-lg-9 form-control convertedPrice" data-rule-currency ="true" name="{$price.curname}" id="{$price.curname}" value="{$price.curvalue}" data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
										</div>
									</td>
									<td>
										<div>
											<input readonly="" type="text" size="10" class="col-lg-9 form-control conversionRate" name="cur_conv_rate{$price.curid}" value="{$price.conversionrate}">
										</div>
									</td>
									<td>
										<div class = "textAlignCenter">
											<button {$disable_value} type="button" class="btn btn-default currencyReset" id="cur_reset{$price.curid}" value="{vtranslate('LBL_RESET',$MODULE)}"><i class = "fa fa-refresh"></i>&nbsp;&nbsp;{vtranslate('LBL_RESET',$MODULE)}</button>
										</div>
									</td>
									<td>
										<div class="textAlignCenter">
											<input {$disable_value} style = "vertical-align:middle" type="radio" class="baseCurrency" id="base_currency{$price.curid}" name="base_currency_input" value="{$price.curname}" {$base_cur_check} />
										</div>
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>
	</div>
</div>