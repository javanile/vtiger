{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/PriceBooks/views/Detail.php *}

{strip}
	{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
	{if $RELATED_MODULE_NAME neq 'Products' && $RELATED_MODULE_NAME neq 'Services'}
		{include file='RelatedList.tpl'|vtemplate_path:'Vtiger'}
	{else}
		<div class="relatedContainer">
			<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
			<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
			<input type="hidden" value="{$ORDER_BY}" id="orderBy">
			<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
			<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
			<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
			<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
			<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>

			{include file="partials/RelatedListHeader.tpl"|vtemplate_path:$RELATED_MODULE_NAME}
			<div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
				<div class="bottomscroll-div">
					{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
					<table id="listview-table" class="table listview-table">
						<thead>
							<tr class="listViewHeaders">
								<th style="min-width:100px">
								</th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th nowrap {if $HEADER_FIELD@last} {/if}>
										<a href="javascript:void(0);" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('name')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}
											{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<i class="fa fa-sort {$FASORT_IMAGE}"></i>
											{else}
												<i class="fa fa-sort customsort"></i>
											{/if}
											&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}<img class="{$SORT_IMAGE}">{/if}&nbsp;
										</a>
										{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
											<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
										{/if}
									</th>
								{/foreach}
							</tr>
							<tr class="searchRow">
								<th class="inline-search-btn">
									<button class="btn btn-success btn-sm" data-trigger="relatedListSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
								</th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th>
										{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
										{else}
											{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
											{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME)
											FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS[$HEADER_FIELD->getName()] USER_MODEL=$USER_MODEL}
											<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$HEADER_FIELD->getName()]['comparator']}">
										{/if}
									</th>
								{/foreach}
							</tr>
						</thead>
						{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
							{assign var=BASE_CURRENCY_DETAILS value=$RELATED_RECORD->getBaseCurrencyDetails()}
							<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
								<td style="width:100px">
									<span class="actionImages">
										{if (!empty($RELATED_HEADERS['listprice']) || !empty($RELATED_HEADERS['unit_price']))}
											{if !empty($RELATED_HEADERS['listprice'])}
												{assign var="LISTPRICE" value=$RELATED_RECORD->get('listprice')}
											{/if}
										{/if}
										<a href="javascript:void(0);" data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}"
											class="editListPrice cursorPointer" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a> &nbsp;&nbsp;
										<a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i></a>
									</span>
								</td>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
									<td nowrap class="{$WIDTHTYPE}">
										{if $HEADER_FIELD->get('name') == 'listprice'}
											{assign var="LISTPRICE" value=$RELATED_RECORD->get($HEADER_FIELD->get('name'))}
											{CurrencyField::appendCurrencySymbol($LISTPRICE, $PARENT_RECORD_CURRENCY_SYMBOL)}
										{else if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
											<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
										{elseif $HEADER_FIELD->get('uitype') eq '71' or $HEADER_FIELD->get('uitype') eq '72'}
											{assign var=CURRENCY_SYMBOL value=Vtiger_RelationListView_Model::getCurrencySymbol($RELATED_RECORD->get('id'), $HEADER_FIELD)}
											{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME))}
											{if $HEADER_FIELD->get('uitype') eq '72'}
												{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
											{/if}
											{CurrencyField::appendCurrencySymbol($CURRENCY_VALUE, $CURRENCY_SYMBOL)}
										{else}
											{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
										{/if}
										{if $HEADER_FIELD@last}
											</td>
										{/if}
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
			<script type="text/javascript">
				var related_uimeta = (function() {
					var fieldInfo = {$RELATED_FIELDS_INFO};
					return {
						field: {
							get: function(name, property) {
								if(name && property === undefined) {
									return fieldInfo[name];
								}
								if(name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory : function(name){
								if(fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType : function(name){
								if(fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							}
						},
					};
				})();
			</script>
		</div>
	{/if}
{/strip}
