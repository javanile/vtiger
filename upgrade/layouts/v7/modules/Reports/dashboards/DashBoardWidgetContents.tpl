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
{assign var=CHART_DATA value=ZEND_JSON::decode($DATA)}
<input class="yAxisFieldType" type="hidden" value="{$YAXIS_FIELD_TYPE}" />
{assign var=CHART_VALUES value=$CHART_DATA['values']}
{if !empty($CHART_VALUES)}
    <input type='hidden' name='charttype' value="{$CHART_TYPE}" />
    <input type='hidden' class="widgetData" name='data' value='{$DATA}' /> 
    <input type='hidden' name='clickthrough' value="{$CLICK_THROUGH}" />
    <div style="margin:0px 10px;">
        <div>
            <div name='chartcontent' class="widgetChartContainer" style="height:245px;min-width:300px; margin: 0 auto">
            <br>
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
            {$CLASS_NAME}('Vtiger_ChartReportWidget_{$RECORD_ID}-{$WIDGET_ID}_Widget_Js',{},{ 
                init : function() {
                        this._super(jQuery("#{$RECORD_ID}-{$WIDGET_ID}"));
                    }
            }); 
        </script>
        <div class="noClickThroughMsg">
            {if $CLICK_THROUGH neq 'true'}
                <div class='row' style="padding:1px">
                    <span class='col-lg-2 offset3'> &nbsp;</span>
                    <span class='span alert-info'>
                        <i class="icon-info-sign"></i>
                        {vtranslate('LBL_CLICK_THROUGH_NOT_AVAILABLE', $MODULE)}
                    </span>
                </div>
                <br><br>
            {/if}
        </div>
    </div>
{else}
	<span class="noDataMsg" style="position: relative; top: 115.5px; left: 119px;">
		{vtranslate('LBL_NO')} {vtranslate($PRIMARY_MODULE, $MODULE)} {vtranslate('LBL_MATCHED_THIS_CRITERIA')}
	</span>
{/if}