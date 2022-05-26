{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/PriceBooks/views/ListPriceUpdate.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class="modal-dialog modelContainer modal-content modal-md" id="listPriceUpdateContainer">
        {assign var=HEADER_TITLE value={vtranslate('LBL_EDIT_LIST_PRICE', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
		<form class="form-horizontal" id="listPriceUpdate" method="post" action="index.php">
			<div class="modal-body">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="action" value="RelationAjax" />
                <input type="hidden" name="src_record" value="{$PRICEBOOK_ID}" />
                <input type="hidden" name="relid" value="{$REL_ID}" />
                <div class="form-group">
                    <label class="col-sm-4 control-label">{vtranslate('LBL_EDIT_LIST_PRICE',$MODULE)} <span class="redColor">*</span>&nbsp;</label>
                    <div class="controls col-sm-4">
                        <input type="text" name="currentPrice" value="{$CURRENT_PRICE}" data-rule-required="true" class="inputElement" data-rule-currency="true"
                               data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' />
                    </div>
                </div>
			</div>
			{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
		</form>
        </div>
    </div>
{/strip}