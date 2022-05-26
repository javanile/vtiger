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
<div class="taxRegionsContainer">
	<div class="tab-pane active">
		<div class="tab-content overflowVisible">
			{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
			<div class="col-lg-4 marginLeftZero textOverflowEllipsis">
				<div class="marginBottom10px">
					<button type="button" class="btn btn-default addRegion addButton module-buttons" data-url="?module=Vtiger&parent=Settings&view=TaxAjax&mode=editTaxRegion" data-type="1">
                        <i class="fa fa-plus"></i>&nbsp;&nbsp;{vtranslate('LBL_ADD_NEW_REGION', $QUALIFIED_MODULE)}</button>
				</div>
				<table class="table table-bordered taxRegionsTable" style="table-layout: fixed">
					<tr>
						<th class="{$WIDTHTYPE}" colspan="2">
                            <strong>{vtranslate('LBL_AVAILABLE_REGIONS', $QUALIFIED_MODULE)}</strong>
						</th>
					<tr>

					{foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
						{assign var=TAX_REGION_NAME value=$TAX_REGION_MODEL->getName()}
						<tr class="opacity" data-key-name="{$TAX_REGION_NAME}" data-key="{$TAX_REGION_NAME}">
							<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none;">
								<span class="taxRegionName">{$TAX_REGION_NAME}</span>
							</td>
							<td class="{$WIDTHTYPE}" style="border-right:none;border-left:none">
								<div class="pull-right actions">
									<a class="editRegion" data-url='{$TAX_REGION_MODEL->getEditRegionUrl()}'><i title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}" class="fa fa-pencil alignMiddle"></i></a>&nbsp;&nbsp;
									<a class="deleteRegion" data-url='{$TAX_REGION_MODEL->getDeleteRegionUrl()}'><i title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}" class="fa fa-trash alignMiddle"></i></a>
								</div>
							</td>
						</tr>
					{/foreach}
				</table>
			</div>
			<div class="col-lg-2">&nbsp;</div>
			<div class="col-lg-7">
				<br><br><br>
				<div class="">
					<div class="col-lg-1"><i class="fa fa-info-circle"></i></div>
					<div class="col-lg-11">{vtranslate('LBL_TAX_REGION_DESC', $QUALIFIED_MODULE)}</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}