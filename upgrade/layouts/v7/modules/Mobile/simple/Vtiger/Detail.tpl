{*<!--
/*************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Commercial
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
**************************************************************************************/
-->*}
{include file="../Header.tpl" scripts=$_scripts}
    <section class="detail-content-wrapper" ng-controller="{$_controller}">
{literal}
        <header md-page-header fixed-top>
            <md-toolbar>
                <div class="md-toolbar-tools actionbar">
                    <md-button ng-click="gobacktoUrl()" class="md-icon-button" aria-label="side-menu-open">
                        <i class="mdi mdi-arrow-left actionbar-icon"></i>
                    </md-button>
                    <h2 flex>{{pageTitle}}</h2>
                    <span flex></span>
                    <md-button class="md-icon-button" ng-if="module != 'Invoice' && module != 'SalesOrder' && module != 'Quotes' && module != 'PurchaseOrder'" ng-click="detailViewEditEvent();" aria-label="global-search">
                         <i class="mdi mdi-pencil actionbar-icon"></i>
                    </md-button>
                </div>
            </md-toolbar>
        </header>
        <md-content style="padding-top: 56px;">
            <div layout="row" style="width: 100%">
                <span style="margin: 10px 10px;" class="letter-avatar">{{record_label | limitTo:1:0}}</span>
                <span style="margin-top: 15px;">{{record_label}}</span>
            </div>
            <md-tabs md-dynamic-height md-border-bottom style="width:100%">
                <md-tab label="Details" style="width:50%">
                    <div flex class="detail-content" style="height:78vh; overflow: scroll;">
                        <div layout="column" layout-fill layout-align="top center" ng-if="fields.length">
                            <md-list style="padding: 0px;" class="fields-list" ng-controller="InlineEditorController"> <!-- infinite-scroll='loadMoreRecords()' infinite-scroll-distance='10'-->
                                <md-list-item class="md-2-line" style="padding: 0px 5px;" ng-repeat="field in recordData">
                                    <div class="md-list-item-text field-row">
                                        <div layout="row" flex>
                                            <div flex="50">
                                                <p style="text-align:right; margin-right: 10px; opacity: 0.9; color:black; font-size:14px; line-height:25px; font-weight: 400;" class="field-label">{{field.label}}
                                                </p>
                                            </div>
                                            <div flex="50">
                                                <p ng-class="{'value-empty' : !field.value || field.value==='' || field.value==='--None' || field.value==0} ">
                                                    {{field.value}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </md-list-item>
                            </md-list>
                            <md-list ng-if="lineitems && (module == 'Invoice' || module == 'Quotes' || module == 'SalesOrder' || module == 'PurchaseOrder')">
                                <md-subheader style="margin:0px; padding:0px; background:beige;">Item Details</md-subheader>
                                <md-list-item class="md-2-line" ng-repeat="item in lineitems">
                                    <div layout="column" style="width: 100%;">
                                        <p style="opacity:0.8; color:#0099FF; font-size: 14px; margin: 4px 0px;">{{item.product_name}}</p>
                                        <i style="color:grey; font-size: 11px;">{{item.comment}}</i>
                                        <div layout="row" style="opacity: 0.9;">
                                            <p style="font-size: 12px; margin: 4px 0px;">{{item.quantity}} * {{item.listprice}}</p>
                                        </div>
                                        <div layout="column" flex style="opacity: 0.9">
                                            <p ng-if="item.discount_amount" style="font-size: 12px; margin: 4px 0px;" flex="100">(-) Discount Amount : {{item.discount_amount}}</p>
                                            <p ng-if="item.discount_percent"  style="font-size: 12px; margin: 4px 0px;" flex="100">(-) Discount Percentage : {{item.discount_percent}} (%)</p>
                                        </div>
                                        <div layout="row" style="text-align: right; color:darkgreen;">
                                            <p style="font-size: 12px; margin: 4px 0px; width: 100%;">{{item.netPrice}}</p>
                                        </div>
                                    </div>
                                    <md-divider></md-divider>
                                </md-list-item>
                                <md-list-item layout="column" style="font-size: 13px;">
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">Items Total</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.sub_total}}</p>
                                    </div>
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">(-) Overall Discount</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.group_discount}}</p>
                                    </div>
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">Total After Discount</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.totalAfterDiscount}}</p>
                                    </div>
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">Pre Tax Total</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.pre_tax_total}}</p>
                                    </div>
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">(+) Tax</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.total_tax}}</p>
                                    </div>
                                    <div layout="row" style="width:100%">
                                        <p flex="50" style="text-align: left;">Adjustment</p>
                                        <p flex="50" style="text-align: right; color:darkgreen;">{{lineItemsSummary.adjustment}}</p>
                                    </div> 
                                    <div layout="row" style="width:100%; color:#0099FF;">
                                        <p flex="50" style="text-align: left;">Grand Total</p>
                                        <p flex="50" style="text-align: right;">{{lineItemsSummary.grand_total}}</p>
                                    </div>
                                </md-list-item>
                            </md-list>
                        </div>
                        <div class="no-records-message" ng-if="!fields.length">
                            <div class="no-records">No Fields Found</div>
                        </div>
                        <div flex></div>
                    </div>
                </md-tab>
                <md-tab label="Related">
                    <div ng-if="relatedModules" style="height:75vh; overflow: scroll;">
                        <md-list-item ng-repeat="(label, info) in relatedModules" ng-click="showRelatedList(info.relatedModule)">
                            <p style="font-size: 13px;"><span style="font-size: 12px; color:#FF4068" class="vicon-{{info.relatedModule | lowercase | nospace}}"></span> &nbsp;  {{label}}</p>
                            <p style="text-align:right; color:#0099FF">{{info.count}}</p>
                            <md-divider></md-divider>
                        </md-list-item>
                    </div>
                </md-tab>
            </md-tabs>
        </md-content>
    </section>
{/literal}
