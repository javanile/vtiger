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
<div class = "quickPreview">
    <div class='quick-preview-modal modal-content'>
        <div class='modal-body'>
            <div class="quickPreviewModuleHeader row">
                <div class = "col-lg-10">
                    <div class="row qp-heading">
                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <div class="record-header clearfix">
                                <div class="hidden-sm hidden-xs recordImage">
                                    <div class="name"><span class='fa fa-bar-chart'></span></div>
                                </div>
                                <div class="recordBasicInfo">
                                    <div class="info-row">
                                        <h4>
                                            <span class="recordLabel pushDown" title="">
                                                {$REPORT_MODEL->get('reportname')}
                                            </span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class = "col-lg-2 pull-right">
                    <button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
                </div>
            </div>
            <div class="quickPreviewActions clearfix">
                <div class="btn-group pull-left">
                </div>
            </div>
            <div class="quickPreviewSummary">
                <input type='hidden' name='charttype' value="{$CHART_TYPE}" />
                <input type='hidden' name='data' value='{Vtiger_Functions::jsonEncode($DATA)}' />
                <input type='hidden' name='clickthrough' value="{$CLICK_THROUGH}" />
                <br>
                <div style="margin:0px 20px;">
                    <div class='border1px' style="padding:30px;">
                        <div id='chartcontent' name='chartcontent' style="min-height:400px;" data-mode='Reports'></div>
                        <br>
                    </div>
                </div>
                <br>
            </div>
            <br>
        </div>
    </div>
</div>
{if $CHART_TYPE eq 'pieChart'}
    {assign var=CLASS_NAME value='Report_Piechart_Js'}
{else if $CHART_TYPE eq 'verticalbarChart'}
    {assign var=CLASS_NAME value='Report_Verticalbarchart_Js'}
{else if $CHART_TYPE eq 'horizontalbarChart'}
    {assign var=CLASS_NAME value='Report_Horizontalbarchart_Js'}
{else}
    {assign var=CLASS_NAME value='Report_Linechart_Js'}
{/if}

<script type="text/javascript">
    {$CLASS_NAME}('Vtiger_ChartReportWidget_{$RECORD_ID}',{}, {
        init: function () {
            this._super(jQuery(".quickPreviewSummary"));
        }
    });

    var i = new Vtiger_ChartReportWidget_{$RECORD_ID}();
    jQuery('.quickPreviewSummary').trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
</script>