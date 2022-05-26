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
    <div id="searchResults-container">
        <div class="row">
            <div class="col-lg-12 clearfix">
                <div class="pull-right overlay-close" >
                    <button type="button" class="close" aria-label="Close" data-target='#overlayPage' data-dismiss="modal">
                        <span aria-hidden="true" class='fa fa-close'></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div id="advanceSearchHolder" class="row">
                <div class="col-lg-2 col-md-1 hidden-xs hidden-sm">&nbsp;</div>
                <div id="advanceSearchContainer" class="col-lg-8 col-md-10 col-sm-12 col-xs-12">
                    <div class="row">
                        <div class="searchModuleComponent">
                            <div class="col-lg-12 col-md-12">
                                <div class="pull-left" style="margin-right:10px;font-size:18px;">{vtranslate('LBL_SEARCH_IN',$MODULE)}</div>
                                <select class="select2 col-lg-3" id="searchModuleList" data-placeholder="{vtranslate('LBL_SELECT_MODULE')}">
                                    <option></option>
                                    {foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
                                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-lg-12">
                            <div class="filterElements well filterConditionContainer" id="searchContainer" style="height: auto;">
                                <form name="advanceFilterForm" method="POST">
                                    {if $SOURCE_MODULE eq 'Home'}
                                        <div class="textAlignCenter well contentsBackground">{vtranslate('LBL_PLEASE_SELECT_MODULE',$MODULE)}</div>
                                    {else}
                                        <input type="hidden" name="labelFields" {if !empty($SOURCE_MODULE_MODEL)}  data-value='{ZEND_JSON::encode($SOURCE_MODULE_MODEL->getNameFields())}' {/if} />
                                        {include file='AdvanceFilter.tpl'|@vtemplate_path}
                                    {/if}	
                                </form>
                            </div>
                        </div>
                    </div>
                                <div class="container-fluid">
                                <div class="row"> 
                                    <div class="col-lg-4 col-md-4 col-sm-4">&nbsp;</div> 
                        <div class="actions  col-lg-8 col-md-8 col-sm-8">
                            <div class="btn-toolbar">
                                <div class="btn-group">
                                    <button class="btn btn-success" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}  type="submit"><strong>{vtranslate('LBL_SEARCH', $MODULE)}</strong></button>
                                </div>
                                <div class="btn-group ">
                                    {if $SAVE_FILTER_PERMITTED}
                                        <button class="btn btn-success hide pull-right" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                                        <button class="btn btn-success" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceIntiateSave"><strong>{vtranslate('LBL_SAVE_AS_FILTER', $MODULE)}</strong></button>
                                        <input class="hide inputElement" type="text" value="" name="viewname"/>
                                    {/if}
                                </div>
                            </div>

                        </div>
                    </div>
                                </div>
                    <div>&nbsp;</div>
                </div>
                  <div class="col-lg-2 col-md-1 hidden-xs hidden-sm">&nbsp;</div>
            </div>
       </div>
        <div class="searchResults">
        </div>
</div>