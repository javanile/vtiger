{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Potentials/views/MappingDetail.php *}

{strip}
    <div class="potentialsFieldMappingListPageDiv">
        <div class="col-sm-12 col-xs-12">
            <div class="row settingsHeader">
                <span class="col-sm-12">
                    <span class="pull-right">
                        {foreach item=LINK_MODEL from=$MODULE_MODEL->getDetailViewLinks()}
                            <button type="button" class="btn btn-default" onclick={$LINK_MODEL->getUrl()}>{vtranslate($LINK_MODEL->getLabel(), $QUALIFIED_MODULE)}</button>
                        {/foreach}
                    </span>
                </span>
            </div>
            <div class="contents table-container" id="detailView">
                <table class="table listview-table" id="listview-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</th>
                            <th>{vtranslate('LBL_FIELD_TYPE', $QUALIFIED_MODULE)}</th>
                            <th>{vtranslate('LBL_MAPPING_WITH_OTHER_MODULES', $QUALIFIED_MODULE)}</th>
                        </tr>
                        <tr>
                            <th width="10%">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                            {foreach key=key item=LABEL from=$MODULE_MODEL->getHeaders()}
                                <th width="30%">{vtranslate($LABEL, $LABEL)}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach key=MAPPING_ID item=MAPPING from=$MODULE_MODEL->getMapping()}
                            <tr class="listViewEntries" data-cfmid="{$MAPPING_ID}">
                                <td> 
                                    {if $MAPPING['editable'] eq 1}
                                        {foreach item=LINK_MODEL from=$MODULE_MODEL->getMappingLinks()}
                                            <div class="table-actions">
                                                <span class="actionImages">
                                                    <a onclick={$LINK_MODEL->getUrl()}><i title="{vtranslate($LINK_MODEL->getLabel(), $MODULE)}" class="fa fa-trash alignMiddle"></i></a>
                                                </span>
                                            </div>
                                        {/foreach}
                                    {/if}
                                </td>
                                <td>{vtranslate({$MAPPING['Potentials']['label']}, 'Potentials')}</td>
                                <td>{vtranslate($MAPPING['Potentials']['fieldDataType'], $QUALIFIED_MODULE)}</td>
                                <td>{vtranslate({$MAPPING['Project']['label']}, 'Project')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        <div id="scroller_wrapper" class="bottom-fixed-scroll">
            <div id="scroller" class="scroller-div"></div>
        </div>
    </div>
{/strip}