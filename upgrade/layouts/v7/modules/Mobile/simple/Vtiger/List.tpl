{*<!--
/*************************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
**************************************************************************************/
-->*}
{include file="../Header.tpl" scripts=$_scripts}

<section layout="row" flex class="content-section" ng-controller="{$_controller}">
    {include file="../Vtiger/Toolbar.tpl"}
    {include file="../Vtiger/SideMenu.tpl"}
    {literal}
        <md-button ng-click="listViewCreateEvent()" class="md-fab md-primary float-button md-fab-bottom-right" aria-label="addnew">
            <i class="mdi mdi-plus"></i>
        </md-button>
        <div flex class="list-content">
            <div class="list-filters" layout="row" flex>
                <div flex="100" class="change-filter">
                    <md-button class="filter-btn" aria-label="notifications">
                        <i class="mdi mdi-filter-outline"></i>
                    </md-button>
                    <md-input-container class="current-filter">
                        <md-select ng-model="selectedFilter" aria-label="filter" ng-change="changeFilter()">
                            <md-optgroup label="Mine" aria-label="Mine">
                                <md-option ng-repeat="filter in filters.Mine track by filter.id" ng-value="filter.id" aria-label="{{filter.name}}">{{filter.name}}</md-option>
                            </md-optgroup>
                            <md-optgroup label="Shared" aria-label="Shared">
                                <md-option ng-repeat="filter in filters.Shared track by filter.id" ng-value="filter.id" aria-label="{{filter.name}}">{{filter.name}}</md-option>
                            </md-optgroup>
                        </md-select>
                    </md-input-container>
                </div>
                <!--div flex="50" class="sort-filter" ng-if="records.length">
                    <md-button class="filter-btn" aria-label="notifications">
                        <i class="mdi mdi-sort"></i>
                    </md-button>
                    <md-input-container class="current-sort-field">
                        <md-select ng-model="orderBy" aria-label="sortfield" placeholder="Sort" ng-change="changeSort(orderBy)">
                            <md-option ng-repeat="nameField in nameFields track by $index" ng-value="nameField.name" aria-label="nameField.name">{{nameField.label}}</md-option>
                            <md-option ng-repeat="header in headers track by $index" ng-value="header.name" aria-label="nameField.name">{{header.label}}</md-option>
                        </md-select>
                    </md-input-container>
                </div>-->
            </div>
            <div layout="column" layout-fill layout-align="top center" ng-if="records.length">
                <md-list class="records-list">
                    <md-list-item class="md-3-line" data-record-id="{{record.id}}" aria-label="row+{{record.id}}" ng-model="showActions" md-swipe-right="showActions=false;$event.stopPropagation();" md-swipe-left="showActions=true;$event.stopPropagation();" ng-click="gotoDetailView(record.id)" ng-repeat="record in records">
                        <div class="md-list-item-text">
                            <h3>
                                <span ng-repeat="label in headers">
                                    <span  ng-repeat="name in nameFields" ng-if="label.name === name">{{record[label.name] + " "}}</span>
                                </span>
                            </h3>
                            <p class="header-fields" ng-repeat="header in headers" ng-if="headerIndex(nameFields,header.name)== -1">
                                {{record[header.name]}}
                            </p>  
                        </div>
                        <div class="actions-slider animate-show" ng-show="showActions" ng-swipe-right="hideRecordActions();" ng-animate="{enter: 'animate-enter', leave: 'animate-leave'}">
                            <div class="button-wrap" flex layout="row">
                                <div flex layout='column'>
                                    <md-button class="list-action-edit md-icon-button"  aria-label="list-action-edit" ng-click="listViewEditEvent($event, record.id);$event.stopPropagation();">
                                        <span><i class="mdi mdi-pencil"></i></span>
                                    </md-button>
                                </div>
                                <div flex layout='column'>
                                    <md-button class="list-action-delete md-icon-button"  aria-label="list-action-delete" ng-click="showConfirmDelete($event, record.id);$event.stopPropagation();">
                                        <span><i class="mdi mdi-delete"></i></span>
                                    </md-button>
                                </div>
                            </div>
                        </div>
                        <md-divider ></md-divider>
                    </md-list-item>
                    <md-list-item class="md-1-line load-more-link" >
                        <div ng-click="loadMoreRecords()" ng-show="moreRecordsExists">
                            Load more
                        </div>
                    </md-list-item>
                </md-list>

            </div>
            <div class="no-records-message" ng-if="!records.length">
                <div class="no-records">No Records Found</div>
            </div>
            <div flex></div>
        </div>
    </section>
{/literal}
{include file="../Footer.tpl"}
