{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{if !empty($CUSTOM_VIEWS)}
		{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE LISTVIEW_HEADERS=$RELATED_HEADERS}
		<div class="relatedContainer">
			{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
			{assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
			<input type="hidden" name="emailEnabledModules" value=true />
			<input type="hidden" id="view" value="{$VIEW}" />
			<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
			<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
			<input type="hidden" value="{$ORDER_BY}" id="orderBy">
			<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
			<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
			<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
			<input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
			<input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
			<input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids={ZEND_JSON::encode($SELECTED_IDS)} />
			<input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids={ZEND_JSON::encode($EXCLUDED_IDS)} />
			<input type="hidden" id="recordsCount" name="recordsCount" />
			<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
			<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
			<input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>

			<div class="relatedHeader">
				<div class="btn-toolbar row">
					<div class="col-lg-5 col-md-5 col-sm-5 btn-toolbar">
						{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
							<div class="btn-group">
								{assign var=DROPDOWNS value=$RELATED_LINK->get('linkdropdowns')}
								{if count($DROPDOWNS) gt 0}
									<div class="btn-group">
										<a class="btn dropdown-toggle" href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="200" data-close-others="false" style="width:20px;height:18px;">
											<img title="{$RELATED_LINK->getLabel()}" alt="{$RELATED_LINK->getLabel()}" src="{vimage_path("{$RELATED_LINK->getIcon()}")}">
										</a>
										<ul class="dropdown-menu">
											{foreach item=DROPDOWN from=$DROPDOWNS}
												<li><a id="{$RELATED_MODULE_NAME}_relatedlistView_add_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DROPDOWN['label'])}" class="{$RELATED_LINK->get('linkclass')}" href='javascript:void(0)' data-documentType="{$DROPDOWN['type']}" data-url="{$DROPDOWN['url']}" data-name="{$RELATED_MODULE_NAME}" data-firsttime="{$DROPDOWN['firsttime']}"><i class="icon-plus"></i>&nbsp;{vtranslate($DROPDOWN['label'], $RELATED_MODULE_NAME)}</a></li>
											{/foreach}
										</ul>
									</div>
								{else}
									{assign var=IS_SEND_EMAIL_BUTTON value={$RELATED_LINK->get('_sendEmail')}}
									{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
									<button type="button" module="{$RELATED_MODULE_NAME}"  class="btn addButton btn-default
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} {if $IS_SEND_EMAIL_BUTTON eq true} sendEmail {/if}"
										{if $IS_SELECT_BUTTON eq true} data-moduleName="{$RELATED_LINK->get('_module')->get('name')}"{/if}
										{if ($RELATED_LINK->isPageLoadLink())}
											{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
											{if $IS_SEND_EMAIL_BUTTON neq true}data-url="{$RELATED_LINK->getUrl()}"{/if}
										{elseif $IS_SEND_EMAIL_BUTTON eq true}
											onclick="{$RELATED_LINK->getUrl()}"
										{/if}
										{if ($IS_SELECT_BUTTON neq true) && ($IS_SEND_EMAIL_BUTTON neq true)}name="addButton"{/if}
										{if $IS_SEND_EMAIL_BUTTON eq true} disabled="disabled" {/if}>{if ($IS_SELECT_BUTTON neq true) && ($IS_SEND_EMAIL_BUTTON neq true)}<i class="fa fa-plus"></i>{/if}&nbsp;&nbsp;{$RELATED_LINK->getLabel()}</button>
								{/if}
							</div>
						{/foreach}&nbsp;
					</div>
					<div class='col-lg-4 col-md-4 col-sm-4'>
						<span class="customFilterMainSpan">
							{if $CUSTOM_VIEWS|@count gt 0}
								<select id="recordsFilter" class="select2 col-lg-8" data-placeholder="{vtranslate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE_NAME)}">
									<option></option>
									{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
										<optgroup label=' {if $GROUP_LABEL eq 'Mine'} &nbsp; {else if} {vtranslate($GROUP_LABEL, $RELATED_MODULE_NAME)} {/if}' >
											{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
												<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" value="{$CUSTOM_VIEW->get('cvid')}" class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {vtranslate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}{else}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								</select>
							{else}
								<input type="hidden" value="0" id="customFilter" />
							{/if}
						</span>
					</div>
					{assign var=CLASS_VIEW_ACTION value='relatedViewActions'}
					{assign var=CLASS_VIEW_PAGING_INPUT value='relatedViewPagingInput'}
					{assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='relatedViewPagingInputSubmit'}
					{assign var=CLASS_VIEW_BASIC_ACTION value='relatedViewBasicAction'}
					{assign var=PAGING_MODEL value=$PAGING}
					{assign var=RECORD_COUNT value=$RELATED_RECORDS|@count}
					{assign var=PAGE_NUMBER value=$PAGING->get('page')}
					{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
				</div>
			</div>
			<div class='col-lg-12 col-md-12 col-sm-12'>
				{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
				<div class="hide messageContainer" style = "height:30px;">
					<center><a id="selectAllMsgDiv" href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($RELATED_MODULE_NAME ,$RELATED_MODULE_NAME)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a></center>
				</div>
				<div class="hide messageContainer" style = "height:30px;">
					<center><a id="deSelectAllMsgDiv" href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a></center>
				</div>
			</div>
			<div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
				<div class="bottomscroll-div">
					{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
					<table id="listview-table"  class="table listview-table">
						<thead>
							<tr class="listViewHeaders">
								<th width="4%" style="padding-left: 12px;">
									<input type="checkbox" id="listViewEntriesMainCheckBox"/>
								</th>
								<th style="min-width:100px">
								</th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th class="nowrap">
										<a href="javascript:void(0);" class="listViewContentHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
											{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<i class="fa fa-sort {$FASORT_IMAGE}"></i>
											{else}
												<i class="fa fa-sort customsort"></i>
											{/if}
											&nbsp;
											{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}
											&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}&nbsp;
										</a>
										{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
											<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
										{/if}
									</th>
								{/foreach}
								<th class="nowrap">
									<a href="javascript:void(0);" class="listViewContentHeaderValues noSorting">{vtranslate('Status', $RELATED_MODULE_NAME)}</a>
								</th>
							</tr>
							<tr class="searchRow">
								<th></th>
								<th class="inline-search-btn">
									<button class="btn btn-success btn-sm" data-trigger="relatedListSearch">{vtranslate("LBL_SEARCH",$MODULE)}</button>
								</th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th>
										{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
										{else}
											{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
											{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS[$HEADER_FIELD->getName()] USER_MODEL=$USER_MODEL}
											<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$HEADER_FIELD->getName()]['comparator']}">
										{/if}
									</th>
								{/foreach}
								<th></th>
							</tr>
						</thead>
						{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
							<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
								<td width="4%" class="{$WIDTHTYPE}">
									<input type="checkbox" value="{$RELATED_RECORD->getId()}" class="listViewEntriesCheckBox"/>
								</td>
								<td style="width:100px">
									<span class="actionImages">
										<a name="relationEdit" data-url="{$RELATED_RECORD->getEditViewUrl()}" href="javascript:void(0)"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i></a> &nbsp;&nbsp;
										{if $IS_DELETABLE}
											<a class="relationDelete"><i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i></a>
										{/if}
									</span>
								</td>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
									{assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
									<td class="{$WIDTHTYPE} relatedListEntryValues" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
										<span class="value textOverflowEllipsis">
											{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
												<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
											{elseif $HEADER_FIELD->get('uitype') eq '71' or $HEADER_FIELD->get('uitype') eq '72'}
												{assign var=CURRENCY_SYMBOL value=Vtiger_RelationListView_Model::getCurrencySymbol($RELATED_RECORD->get('id'), $HEADER_FIELD)}
												{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME))}
												{if $HEADER_FIELD->get('uitype') eq '72'}
													{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
												{/if}
												{if Users_Record_Model::getCurrentUserModel()->get('currency_symbol_placement') eq '$1.0'}
													{$CURRENCY_SYMBOL}{$CURRENCY_VALUE}
												{else}
													{$CURRENCY_VALUE}{$CURRENCY_SYMBOL}
												{/if}
											{else if $HEADER_FIELD->getFieldDataType() eq 'picklist'}
												<span {if !empty($RELATED_LIST_VALUE)} class="picklist-color picklist-{$HEADER_FIELD->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}" {/if}> {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} </span>
											{else}
												{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
											{/if}
										</span>
									</td>
								{/foreach}
								<td class="{$WIDTHTYPE}" nowrap>
									<span class="currentStatus more dropdown action">
										<span class="statusValue dropdown-toggle" data-toggle="dropdown">{vtranslate($RELATED_RECORD->get('status'),$MODULE)}&nbsp;</span>
										<a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-arrow-down alignMiddle editRelatedStatus"></i></a>
										<ul class="dropdown-menu dropdown-menu-right">
											{foreach key=STATUS_ID item=STATUS from=$STATUS_VALUES}
												<li id="{$STATUS_ID}" data-status="{vtranslate($STATUS, $MODULE)}">
													<a>{vtranslate($STATUS, $MODULE)}</a>
												</li>
											{/foreach}
										</ul>
									</span>
								</td>
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			var related_uimeta = (function () {
				var fieldInfo = {$RELATED_FIELDS_INFO};
				return {
					field: {
						get: function (name, property) {
							if (name && property === undefined) {
								return fieldInfo[name];
							}
							if (name && property) {
								return fieldInfo[name][property]
							}
						},
						isMandatory: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].mandatory;
							}
							return false;
						},
						getType: function (name) {
							if (fieldInfo[name]) {
								return fieldInfo[name].type
							}
							return false;
						}
					},
				};
			})();
		</script>
	{else}
		{include file='RelatedList.tpl'|@vtemplate_path}
	{/if}
{/strip}
