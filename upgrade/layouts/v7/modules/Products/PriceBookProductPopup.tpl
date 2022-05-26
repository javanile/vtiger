{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Products/views/PriceBookProductPopup.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<script type="text/javascript" src="{vresource_url('layouts/v7/modules/PriceBooks/resources/PriceBooksPopup.js')}"></script>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$MODULE}
            <form id="popupPage" action="javascript:void(0)">
                <div class="modal-body">
                    <div id="popupPageContainer" class="contentsDiv">
                        <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
                        <input type="hidden" id="module" value="{$MODULE}"/>
                        <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
                        <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
                        <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
                        <input type="hidden" id="url" value="{$GETURL}" />
                        <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
                        <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
                        <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
                        <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
                        <input type="hidden" id="view" value="{$VIEW}"/>
                        <input type="hidden" id="relationId" value="{$RELATION_ID}" />
                        <input type="hidden" id="selectedIds" name="selectedIds">
                        {if !empty($POPUP_CLASS_NAME)}
                            <input type="hidden" id="popUpClassName" value="{$POPUP_CLASS_NAME}"/>
                        {/if}
                            <div id="popupContents" class="">
                                {include file='PriceBookProductPopupContents.tpl'|@vtemplate_path:$PARENT_MODULE}
                            </div>
                        <input type="hidden" class="triggerEventName" value="{$smarty.request.triggerEventName}"/>
                    </div>
                </div>
                <div class = "modal-footer">
                    {if $LISTVIEW_ENTRIES_COUNT neq '0'}
                        <center>
                            <footer>
                                <button class="btn btn-success addProducts" type="submit">
                                    <i class="fa fa-plus"></i>&nbsp;&nbsp;
                                    <strong>{vtranslate('LBL_ADD_TO_PRICEBOOKS', $MODULE)}</strong>
                                </button>
                                <a class="cancelLink" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </footer>
                        </center>
                    {/if}
                </div>
            </form>
        </div>
    </div>
{/strip}
