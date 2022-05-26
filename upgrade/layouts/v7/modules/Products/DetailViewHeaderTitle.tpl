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
    <div class="col-sm-6 col-lg-6 col-md-6">
        <div class="record-header clearfix">
                {assign var=IMAGE_DETAILS value=$RECORD->getImageDetails()}
            <div class="hidden-sm hidden-xs recordImage bgproducts app-{$SELECTED_MENU_CATEGORY}" {if $IMAGE_DETAILS|@count gt 1}style = "display:block"{/if}>
                {foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
	               {if !empty($IMAGE_INFO.path)}
	                {if $IMAGE_DETAILS|@count eq 1}
	                    <img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" width="100%" height="100%" align="left"><br>
	                {else if $IMAGE_DETAILS|@count eq 2}
	                    <span><img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" width="50%" height="100%" align="left"></span>
	                {else if $IMAGE_DETAILS|@count eq 3}
	                    <span><img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" {if $ITER eq 0 or $ITER eq 1}width="50%" height = "50%"{/if}{if $ITER eq 2}width="100%" height="50%"{/if} align="left"></span>
	                {else if $IMAGE_DETAILS|@count eq 4 or $IMAGE_DETAILS|@count gt 4}
	                    {if $ITER gt 3}{break}{/if}
	                    <span><img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}"width="50%" height="50%" align="left"></span>
	                {/if}
	               {else}
	                  <img src="{vimage_path('summary_Products.png')}" class="summaryImg"/>
	               {/if}
	        {/foreach}
			{if empty($IMAGE_DETAILS)}
				<div class="name"><span><strong>{$MODULE_MODEL->getModuleIcon()}</strong></span></div>
			{/if}
            </div>

            <div class="recordBasicInfo">
                <div class="info-row">
                    <h4>
                        <span class="recordLabel pushDown" title="{$RECORD->getName()}">
                            {foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}
                                {assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
                                {if $FIELD_MODEL->getPermissions()}
                                    <span class="{$NAME_FIELD}">{$RECORD->get($NAME_FIELD)}</span>&nbsp;
                                {/if}
                            {/foreach}
                        </span>
                    </h4>
                </div>
                {include file="DetailViewHeaderFieldsView.tpl"|vtemplate_path:$MODULE}
                
                {*
                <div class="info-row row">
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField('product_no')}
                    <div class="col-lg-7 fieldLabel">
                        <span class="product_no" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('product_no')}">
                            {$RECORD->getDisplayValue("product_no")}
                        </span>
                    </div>
                </div>

                <div class="info-row row">
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField('discontinued')}
                    <div class="col-lg-7 fieldLabel">
                        <span class="discontinued" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {if $RECORD->get('discontinued') eq 1} Active {else} Inactive {/if}">{if $RECORD->get('discontinued') eq 1} Active {else} Inactive {/if}</span>
                    </div>
                </div>

                <div class="info-row row">
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField('qtyinstock')}
                    <span class="value col-lg-6 recordLabel pushDown {$FIELD_MODEL->get('name')}" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('qtyinstock')}">{$RECORD->get('qtyinstock')}</span>
                    
                    {if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $FIELD_MODEL->get('uitype') neq 69}
                        <span class="hide edit col-lg-6">
                           <input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' data-value="{$FIELD_VALUE}" />
                        </span>
                    {/if}
                </div>
                
                <div class="info-row row">
                    {assign var=FIELD_MODEL value=$MODULE_MODEL->getField('productcategory')}
                    <div class="col-lg-7 fieldLabel">
                        <span class="productcategory" title="{vtranslate($FIELD_MODEL->get('label'),$MODULE)} : {$RECORD->get('productcategory')}">
                            {$RECORD->getDisplayValue("productcategory")}
                        </span>
                    </div>
                </div>
                *}
            </div>
        </div>
    </div>
{/strip}