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
{*<section>
<div>
{include file="Toolbar.tpl"|mobile_templatepath:$MODULE scripts=$_scripts}
{include file="SideMenu.tpl"|mobile_templatepath:$MODULE scripts=$_scripts}
</div>
</section>*}
{literal}
    <md-toolbar layout="row">
        <div class="md-toolbar-tools actionbar">
            <md-button ng-click="navigationToggle()" class="md-icon-button" aria-label="side-menu-open">
                <i class="mdi mdi-menu actionbar-icon"></i>
            </md-button>
            <h2>{{pageTitle}}</h2>
            <span flex></span>
            <md-button class="md-icon-button" aria-label="global-search">
                <i class="mdi mdi-magnify  actionbar-icon"></i>
            </md-button>
        </div>
    </md-toolbar>
    <section layout="row" flex>
        <md-sidenav class="md-sidenav-left" md-component-id="left">
            <md-toolbar class="app-menu md-locked-open">
                <!--div class="md-toolbar-tools">
                    <md-button ng-click="navigationToggle()" class="md-icon-button" aria-label="side-menu-close">
                        <i class="mdi mdi-arrow-left actionbar-icon"></i>
                    </md-button>
                </div-->
                <div class="user-details">
                    <md-list-item class="md-1-line">
                    {/literal}
                    <img src="../../{$TEMPLATE_WEBPATH}/resources/images/default_1.png" class="md-avatar" alt="user">
                    {literal}
                        <div class="md-list-item-text">
                            <div>{{userinfo.first_name + " "}}{{userinfo.last_name}}</div>
							<h5 style="margin: 0px;">{{userinfo.email}}</h5>
                        </div>
                    </md-list-item>
                </div>
                <md-input-container class="app-dropdown">
                    <md-select ng-model="selectedApp" aria-label="app_menu">
                        <md-option ng-repeat="app in apps" ng-value="app">{{app}}</md-option>
                    </md-select>
                </md-input-container>
            </md-toolbar>

            <md-list class="sidenav-module-list">
                <md-list-item ng-click="navigationToggle();setPageTitle('Dashboard')" class="md-1-line">
                    <span class="vicon-grid"></span> &nbsp; 
                    <span class="vmodule-name">Dashboard</span>
                </md-list-item>
                <md-list-item ng-click="navigationToggle();loadList(module.name)" class="md-1-line" ng-repeat="module in menus[selectedApp]">
                    <span class="vicon-{{module.name | lowercase | nospace}}"></span> &nbsp; 
                    <span class="vmodule-name">{{module.label}}</span>
                </md-list-item>
            </md-list>
            <md-divider ></md-divider>
            <md-list>
                <md-list-item md-ink-ripple class="md-1-line">
                    <div class="md-list-item-text">
                        <a href="#" class="logout-link" ng-click="logout();"><span class="mdi mdi-power"></span>&nbsp; Logout</a>
                    </div>
                </md-list-item>
                <md-list-item class="md-1-line">
                    <div class="md-list-item-text">
                        &nbsp; 
                    </div>
                </md-list-item>
            </md-list>

        </md-sidenav>

        <div flex layout-padding class="list-content">
            <!-- Home Content -->
        </div>
    </section>

</div>
</section>
{/literal}
{include file="../Footer.tpl"}
