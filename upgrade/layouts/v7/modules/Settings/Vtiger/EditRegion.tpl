{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/TaxAjax.php *}

{strip}
	{assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
	<div class="taxRegionContainer modal-dialog modal-xs">
        <div class="modal-content">
            <form id="editTaxRegion" class="form-horizontal">
                {if $TAX_REGION_ID}
                    {assign var=TITLE value={{vtranslate('LBL_EDIT_REGION', $QUALIFIED_MODULE)}}}
                {else}
                    {assign var=TITLE value={{vtranslate('LBL_ADD_NEW_REGION', $QUALIFIED_MODULE)}}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                <input type="hidden" name="taxRegionId" value="{$TAX_REGION_ID}" />
                <div class="modal-body">
                    <div class="row">
                        <div class="nameBlock">
                            <div class="col-lg-1"></div>
                            <div class="col-lg-3">
                                <label class="pull-right">{vtranslate('LBL_REGION_NAME', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-5">
                                <input class="inputElement" type="text" name="name" placeholder="{vtranslate('LBL_ENTER_REGION_NAME', $QUALIFIED_MODULE)}" value="{$TAX_REGION_MODEL->getName()}" data-rule-required="true" />
                            </div>	
                            <div class="col-lg-3"></div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
	</div>
{/strip}