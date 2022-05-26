{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <form class="form-horizontal recordEditView padding1per" id="chart_report_step3" method="post" action="index.php">
        <input type="hidden" name="module" value="{$MODULE}" >
        <input type="hidden" name="action" value="ChartSave" >
        <input type="hidden" name="record" value="{$RECORD_ID}" >
        <input type="hidden" name="reportname" value="{Vtiger_Util_Helper::toSafeHTML($REPORT_MODEL->get('reportname'))}" >
        {if $REPORT_MODEL->get('members')}
            <input type="hidden" name="members" value={ZEND_JSON::encode($REPORT_MODEL->get('members'))} />
        {/if}
        <input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" >
        <input type="hidden" name="reports_description" value="{Vtiger_Util_Helper::toSafeHTML($REPORT_MODEL->get('reports_description'))}" >
        <input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" >
        <input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} >
        <input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" >
        <input type="hidden" name="advanced_filter" id="advanced_filter" value="" >
        <input type="hidden" class="step" value="3" >
        <input type="hidden" name='groupbyfield' value={$CHART_MODEL->getGroupByField()} >
        <input type="hidden" name='datafields' value={Zend_JSON::encode($CHART_MODEL->getDataFields())}>
        <input type="hidden" name='charttype' value={$CHART_MODEL->getChartType()}>

        <input type="hidden" name="enable_schedule" value="{$REPORT_MODEL->get('enable_schedule')}">
        <input type="hidden" name="schtime" value="{$REPORT_MODEL->get('schtime')}">
        <input type="hidden" name="schdate" value="{$REPORT_MODEL->get('schdate')}">
        <input type="hidden" name="schdayoftheweek" value={ZEND_JSON::encode($REPORT_MODEL->get('schdayoftheweek'))}>
        <input type="hidden" name="schdayofthemonth" value={ZEND_JSON::encode($REPORT_MODEL->get('schdayofthemonth'))}>
        <input type="hidden" name="schannualdates" value={ZEND_JSON::encode($REPORT_MODEL->get('schannualdates'))}>
        <input type="hidden" name="recipients" value={ZEND_JSON::encode($REPORT_MODEL->get('recipients'))}>
        <input type="hidden" name="specificemails" value={ZEND_JSON::encode($REPORT_MODEL->get('specificemails'))}>
        <input type="hidden" name="schtypeid" value="{$REPORT_MODEL->get('schtypeid')}">

        <div style="border:1px solid #ccc;padding:1%;">
                    <div><h4><strong>{vtranslate('LBL_SELECT_CHART_TYPE',$MODULE)}</strong></h4></div><br>
                    <div>
                        <ul class="nav nav-tabs charttabs" name="charttab" style="text-align:center;font-size:14px;font-weight: bold;margin:0 3%;border:0px">
                            <li class="active marginRight5px" >
                                <a data-type="pieChart" data-toggle="tab">
                                    <div><img src="layouts/v7/skins/images/pie.PNG" style="border:1px solid #ccc;"/></div>
                                    <div class="chartname">{vtranslate('LBL_PIE_CHART', $MODULE)}</div>
                                </a>
                            </li>
                            <li class="marginRight5px">
                                <a data-type="verticalbarChart" data-toggle="tab">
                                    <div><img src="layouts/v7/skins/images/vbar.PNG" style="border:1px solid #ccc;"/></div>
                                    <div class="chartname">{vtranslate('LBL_VERTICAL_BAR_CHART', $MODULE)}</div>
                                </a>
                            </li>
                            <li class="marginRight5px">
                                <a data-type="horizontalbarChart" data-toggle="tab">
                                    <div><img src="layouts/v7/skins/images/hbar.PNG" style="border:1px solid #ccc;"/></div>
                                    <div class="chartname">{vtranslate('LBL_HORIZONTAL_BAR_CHART', $MODULE)}</div>
                                </a>
                            </li>
                            <li class="marginRight5px" >
                                <a data-type="lineChart" data-toggle="tab">
                                    <div><img src="layouts/v7/skins/images/line.PNG" style="border:1px solid #ccc;"/></div>
                                    <div class="chartname">{vtranslate('LBL_LINE_CHART', $MODULE)}</div>
                                </a>
                            </li>
                        </ul>
                        <div class='tab-content contentsBackground' style="height:auto;padding:4%;border:1px solid #ccc; background-color: white;">
                            <br>
                            <div class="row tab-pane active">
                                <div>
                                    <span class="col-lg-4">
                                        <div><span>{vtranslate('LBL_SELECT_GROUP_BY_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
                                        <div class="row">
                                            <select id='groupbyfield' name='groupbyfield' class="validate[required]" data-validation-engine="validate[required]" style='min-width:300px;'></select>
                                        </div>
                                    </span>
                                    <span class="col-lg-2">&nbsp;</span>
                                    <span class="col-lg-4">
                                        <div><span>{vtranslate('LBL_SELECT_DATA_FIELD', $MODULE)}</span><span class="redColor">*</span></div><br>
                                        <div class="row">
                                            <select id='datafields' name='datafields[]' class="validate[required]" data-validation-engine="validate[required]" style='min-width:300px;'>
                                            </select></div>
                                    </span>
                                </div>
                            </div>
                            <br><br>
                            <div class='row alert-info' style="padding: 20px;">
                                <span class='span alert-info'>
                                    <span>
                                        <i class="fa fa-info-circle"></i>&nbsp;&nbsp;&nbsp;
                                        {vtranslate('LBL_PLEASE_SELECT_ATLEAST_ONE_GROUP_FIELD_AND_DATA_FIELD', $MODULE)}
                                        {vtranslate('LBL_FOR_BAR_GRAPH_AND_LINE_GRAPH_SELECT_3_MAX_DATA_FIELDS', $MODULE)}
                                    </span>
                            </div>
                        </div>
                    </div>
                    <div class='hide'>
                        {include file="chartReportHiddenContents.tpl"|vtemplate_path:$MODULE}
                    </div>
        </div>
        <br>
        <div class="modal-overlay-footer border1px clearfix">
            <div class="row clearfix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                    <button type="button" class="btn btn-danger backStep"><strong>{vtranslate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-success" id="generateReport"><strong>{vtranslate('LBL_GENERATE_REPORT',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <a class="cancelLink" onclick="window.history.back()">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            </div>
        </div>
    </form>
{/strip}