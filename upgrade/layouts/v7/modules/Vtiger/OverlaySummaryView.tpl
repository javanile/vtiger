{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}


<input type="hidden" id="recordId" value="{$RECORD->getId()}"/>
{if $FIELDS_INFO neq null}
    <script type="text/javascript">
        var related_uimeta = (function() {
            var fieldInfo = {$FIELDS_INFO};
            return {
                field: {
                    get: function(name, property) {
                        if (name && property === undefined) {
                            return fieldInfo[name];
                        }
                        if (name && property) {
                            return fieldInfo[name][property]
                        }
                    },
                    isMandatory: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].mandatory;
                        }
                        return false;
                    },
                    getType: function(name) {
                        if (fieldInfo[name]) {
                            return fieldInfo[name].type
                        }
                        return false;
                    }
                },
            };
        })();
    </script>
{/if}

<div class='fc-overlay-modal overlayDetail'>
    <div class = "modal-content" style = "overflow: hidden">
        <div class="overlayDetailHeader col-lg-12 col-md-12 col-sm-12">
            <div class="col-lg-9 col-md-9 col-sm-9">
                {include file="DetailViewHeaderTitle.tpl"|vtemplate_path:$MODULE_NAME MODULE_MODEL=$MODULE_MODEL RECORD=$RECORD}
            </div>
            <div class = "col-lg-3 col-md-3 col-sm-3">
                {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWBASIC']}
                    {if $MODULE_NAME eq 'Documents' && $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_VIEW_FILE'}
                        <button class="btn btn-success" id="{$MODULE_NAME}_detailView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DETAIL_VIEW_BASIC_LINK->getLabel())}"
                                onclick="{$DETAIL_VIEW_BASIC_LINK->getUrl()}"
                                data-filelocationtype="{$DETAIL_VIEW_BASIC_LINK->geszzt('filelocationtype')}"
                                data-filename="{$DETAIL_VIEW_BASIC_LINK->get('filename')}">{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}
                        </button>
                    {/if}
                {/foreach}
                <button class="btn btn-success moreDetailsButton" value = "{$RECORD->getDetailViewUrl()}">{vtranslate('LBL_DETAILS',$MODULE_NAME)}</button>
                <button class="btn btn-success editRelatedRecord" value = "{$RECORD->getEditViewUrl()}">{vtranslate('LBL_EDIT',$MODULE_NAME)}</button>
                <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                <span aria-hidden="true" class='fa fa-close'></span>
                </button>
            </div>
        </div>
        <div class='modal-body'>
            <div class = "detailViewContainer">                
                {include file='DetailViewSummaryContents.tpl'|@vtemplate_path:$MODULE_NAME}
            </div>
        </div>
    </div>
</div>