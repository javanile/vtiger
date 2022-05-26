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
<script type="text/javascript" src="../../{$TEMPLATE_WEBPATH}/Vtiger/js/Edit.js"></script>
{literal}

<form name="editForm" id="field-edit-form" ng-submit="saveThisRecord(editForm)" ng-controller="VtigerEditController">
    <header md-page-header fixed-top>
        <md-toolbar>
            <div class="md-toolbar-tools actionbar">
                <md-button ng-click="gobacktoUrl()" class="md-icon-button" aria-label="side-menu-open">
                    <i class="mdi mdi-window-close actionbar-icon"></i>
                </md-button>
                <h2 ng-if="record" flex>Edit</h2>
                <h2 ng-if="!record" flex>Create</h2>
                <span flex></span>
                <md-button type="submit" class="md-icon-button" aria-label="notifications">
                    <i class="mdi mdi-check actionbar-icon"></i>
                </md-button>
            </div>
        </md-toolbar>
    </header>
    <section layout="row" flex class="content-section">
        <div layout="column" class="edit-content" layout-fill layout-align="top center">
            <md-list class="fields-list">
                <md-list-item ng-repeat="field in fieldsData" class="md-1-line" ng-if="field.editable">
                    <div class="md-list-item-text field-row" ng-switch="field.type.name">
                        <md-input-container ng-switch-when="string">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.raw" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw" ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <md-input-container ng-switch-when="phone">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.raw" type="phone" aria-label="{{field.name}}" ng-required="field.mandatory">
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-if="editForm[field.name].$error.required"  ng-message="required"> Mandatory Field.</div>
                            </div>                 
                        </md-input-container>
                        <!--*************PICKLIST UI***********************************-->
                        <md-input-container ng-switch-when="picklist" ng-hide="(field.name == 'activitytype' || field.name == 'eventstatus') && module =='Calendar'">
                            <div class="input-group-addon">
                                <label ng-if="field.name == 'taskstatus'">Task Status</label>
                                <label ng-if="field.name == 'eventstatus'">Event Status</label>
                                <label ng-if="field.name != 'taskstatus' && field.name != 'eventstatus'">{{field.label}}</label>
                                <md-select name="{{field.name}}" ng-model="field.raw" aria-label="{{field.label}}" ng-required="field.mandatory">
                                    <md-option ng-value="opt.value" ng-repeat="opt in field.type.picklistValues">{{opt.label}}</md-option>
                                </md-select>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--*************PICKLIST UI***********************************-->
                        <md-input-container ng-switch-when="metricpicklist">
                            <div class="input-group-addon">
                                <label ng-if="field.name == 'taskstatus'">Task Status</label>
                                <label ng-if="field.name == 'eventstatus'">Event Status</label>
                                <label ng-if="field.name != 'taskstatus' && field.name != 'eventstatus'">{{field.label}}</label>
                                <md-select name="{{field.name}}" ng-model="field.raw" aria-label="{{field.label}}" ng-required="field.mandatory">
                                    <md-option ng-value="opt.value" ng-repeat="opt in field.type.picklistValues">{{opt.label}}</md-option>
                                </md-select>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--*************Owner UI***********************************-->
                        <md-input-container ng-switch-when="owner">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <md-select name="{{field.name}}" ng-model="field.raw" aria-label="{{field.label}}">
                                    <md-optgroup label="Users" aria-label="Users">
                                        <md-option ng-value="user_id" ng-repeat="(user_id, user) in field.type.picklistValues.users">{{user}}</md-option>
                                    </md-optgroup>
                                    <md-optgroup label="Groups" aria-label="Groups">
                                        <md-option ng-value="group_id" ng-repeat="(group_id, group) in field.type.picklistValues.groups">{{group}}</md-option>
                                    </md-optgroup>
                                </md-select>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--****************Reference Picklist*******************************-->
                        <div ng-switch-when="reference" style="padding-bottom: 16px;">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <md-autocomplete name="{{field.name}}" flex
                                                ng-model="field.raw"
                                                md-search-text="field.valueLabel"
                                                md-items="item in getMatchedReferenceFields(field.valueLabel, field)"
                                                md-selected-item-change="setReferenceFieldValue(item, field)"
                                                md-item-text="item.label"
                                                md-min-length="3"
                                                md-input-name="{{field.name}}">
                                    <md-item-template>
                                        <span md-highlight-text="field.valueLabel">{{item.label}}</span>
                                    </md-item-template>
                                    <md-not-found>
                                        No matches found for "{{field.valueLabel}}".
                                    </md-not-found>
                               </md-autocomplete>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error" ng-if="searchForm.autocompleteField.$touched">
                                <div ng-message="required">You <b>must</b> have a favorite fruit.</div>
                            </div>
                        </div>
                        <!--****************Multi Select Picklist*******************************-->
                        <md-input-container ng-switch-when="multipicklist">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <md-chips name="{{field.name}}" ng-model="field.valuelabel" md-autocomplete-snap md-require-match>
                                    <md-autocomplete aria-label="{{field.name}}"
                                                     md-input-name="field.name"
                                                     md-search-text="field.valuelabel"
                                                     md-items="item in querySearch2(field.picklist)"
                                                     md-item-text="item">
                                        <span md-highlight-text="fruitsobj.searchText">{{item.display}}</span>
                                    </md-autocomplete>
                                    <md-chip-template>
                                        <span> {{$chip['display']}} </span>
                                    </md-chip-template>
                                </md-chips>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--*************Date Field UI***********************************-->
                        <md-input-container ng-switch-when="date">
                            <div class="input-group-addon">
                                <label ng-if="field.name != 'date_start'">{{field.label}}</label>
                                <label ng-if="field.name == 'date_start'">Start Date</label>
                                <div layout="row">
                                    <span class="mdi mdi-calendar editIcon"></span>
                                    <div flex="90">
                                        <input name="{{field.name}}" type="date" aria-label="Date Field UI" ng-model="field.raw">
                                    </div>
                                </div>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--*************Time Field UI***********************************-->
                        <md-input-container  class="date-input-container" ng-switch-when="time" ng-hide="field.name == 'time_end' && module =='Calendar'">
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <div layout="row" class="input-group-addon" flex>
                                    <span class="mdi mdi-clock editIcon"></span>
                                    <div flex="90">
                                        <input name="{{field.name}}" mdc-datetime-picker ng-if="userinfo.hour_format == '12'" date="false" time="true" type="text" format="hh:mm a" short-time="true" ng-model="field.raw" aria-label="{{field.label}}" ng-required="field.mandatory" placeholder="Time">
                                        <input name="{{field.name}}" mdc-datetime-picker ng-if="userinfo.hour_format == '24'" date="false" time="true" type="text" format="HH:mm" short-time="false" ng-model="field.raw" aria-label="{{field.label}}" ng-required="field.mandatory" placeholder="Time">
                                    </div>
                                </div>
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw"  ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--*************Checkbox /Boolean Box UI *********************-->
                        <md-input-container ng-switch-when="boolean">
                            <md-checkbox name="{{field.name}}" class="md-primary edit-checkbox" name="{{field.name}}" ng-model="field.raw" aria-label="{{field.name}}"  ng-required="field.mandatory">
                                {{field.label}}
                            </md-checkbox>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory" ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        <!--************* TEXT AREA *********************-->
                        <md-input-container ng-switch-when="text">
                            <label>{{field.label}}</label>
                            <textarea name="{{field.name}}" ng-model="field.raw" rows="4" md-select-on-focus></textarea>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory" ng-message="required"> Mandatory Field.</div>
                            </div>
                        </md-input-container>
                        
                        <!--*************Default text to be changed Later**********************-->
                        <md-input-container ng-switch-when="image">
                            <h5>Upload image from web verion.</h5>
                        </md-input-container>
                        <md-input-container ng-switch-default>
                            <div class="input-group-addon">
                                <label>{{field.label}}</label>
                                <input name="{{field.name}}" ng-model="field.raw" type="text" aria-label="{{field.name}}" ng-required="field.mandatory">
                            </div>
                            <div ng-messages="editForm.{{field.name}}.$error">
                                <div ng-show="field.mandatory && !field.raw" ng-message="required"> Mandatory Field.</div>
                            </div>                                
                        </md-input-container>
                   </div>
                </md-list-item>
            </md-list>
        </div>
    </section>
</form>
{/literal}
