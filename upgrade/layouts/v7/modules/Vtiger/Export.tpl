{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/Export.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="fc-overlay-modal modal-content">
		<form id="exportForm" class="form-horizontal" method="post" action="index.php">
			<input type="hidden" name="module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="action" value="ExportData" />
			<input type="hidden" name="viewname" value="{$VIEWID}" />
			<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
			<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
			<input type="hidden" id="page" name="page" value="{$PAGE}" />
			<input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
			<input type="hidden" name="operator" value="{$OPERATOR}" />
			<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
			<input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
			<input type="hidden" name="orderby" value="{$ORDER_BY}" />
			<input type="hidden" name="sortorder" value="{$SORT_ORDER}" />
			<input type="hidden" name="tag_params" value='{Zend_JSON::encode($TAG_PARAMS)}' />
			{if $SOURCE_MODULE eq 'Documents'}
				<input type="hidden" name="folder_id" value="{$FOLDER_ID}"/>
				<input type="hidden" name="folder_value" value="{$FOLDER_VALUE}"/>
			{/if}
			<div class="overlayHeader">
				{assign var=TITLE value="{vtranslate('LBL_EXPORT_RECORDS',$MODULE)}"}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
			</div>

			<div class="modal-body" style="margin-bottom:250px">
				<div class="datacontent row">
					<div class="col-lg-3"></div>
					<div class="col-lg-6">
						<div class="well exportContents">
							{if $SOURCE_MODULE eq 'Calendar'}
								<br><div><b>{vtranslate('LBL_EXPORT_FORMAT',$MODULE)}</b></div><br>
								<div style="margin-left: 50px;">
									<div>
										<input type="radio" name="type" value="csv" id="csv" onchange="Calendar_Edit_Js.handleFileTypeChange();" checked="checked" />
										<label style="font-weight:normal" for="csv">&nbsp;&nbsp;{vtranslate('csv', $MODULE)}</label>
									</div>
									<div>
										<input type="radio" name="type" value="ics" id="ics" onchange="Calendar_Edit_Js.handleFileTypeChange();"/>
										<label style="font-weight:normal" for="ics">&nbsp;&nbsp;{vtranslate('ics', $MODULE)}</label>
									</div>
								</div>
							{/if}

							<br><div><b>{vtranslate('LBL_EXPORT_DATA',$MODULE)}</b></div><br>
							<div style="margin-left: 50px;">
								<div>
									<input type="radio" name="mode" value="ExportSelectedRecords" id="group1" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if} style="margin:2px 0 -4px" />
									<label style="font-weight:normal" for="group1">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}</label>
									{if empty($SELECTED_IDS)}&nbsp; <span style="color:red">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
									<input type="hidden" class="isSelectedRecords" value="{if $SELECTED_IDS}1{else}0{/if}" >
								</div>
								<br>
								<div>
									<input type="radio" name="mode" value="ExportCurrentPage" id="group2" style="margin:2px 0 -4px" />
									<label style="font-weight:normal" for="group2">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}</label>
								</div>
								<br>
								<div>
									<input type="radio" name="mode" value="ExportAllData" id="group3" {if empty($SELECTED_IDS)} checked="checked" {/if} style="margin:2px 0 -4px" />
									<label style="font-weight:normal" for="group3">&nbsp;&nbsp;{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}</label>
								</div>
								{if $MULTI_CURRENCY}
									<br>
									<div class="row"> 
										<div class="col-lg-8 col-md-8 col-lg-pull-0"><strong>{vtranslate('LBL_EXPORT_LINEITEM_CURRENCY',$MODULE)}:&nbsp;</strong>
											<i style="position:relative;top:4px;" class="icon-question-sign" data-toggle="tooltip" title="{vtranslate('LBL_EXPORT_CURRENCY_TOOLTIP_TEXT',$MODULE)}"></i>
										</div>
									</div>
									<br>
									<div class="row">
										<div class="col-lg-1 col-md-1 col-lg-pull-0"><input type="radio" name="selected_currency" value="UserCurrency" checked="checked"/></div>
										<div> {vtranslate('LBL_EXPORT_USER_CURRENCY',$MODULE)}&nbsp;</div>
									</div>
									<br>
									<div class="row">
										<div class="col-lg-1 col-md-1 col-lg-pull-0"><input type="radio" name="selected_currency" value="RecordCurrency"/></div>
										<div>{vtranslate('LBL_EXPORT_RECORD_CURRENCY',$MODULE)}&nbsp;</div>
									</div>
								{/if}
							</div>
							<br>
						</div>
					</div>
					<div class="col-lg-3"></div>
				</div>
			</div>
			<div class="modal-overlay-footer clearfix">
				<div class="row clearfix">
					<div class=" textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
						<div><button type="submit" class="btn btn-success btn-lg">{vtranslate('LBL_EXPORT', 'Vtiger')}&nbsp;{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</button>
							&nbsp;&nbsp;&nbsp;<a class="cancelLink" data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}