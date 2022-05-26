{literal}
<md-sidenav class="md-sidenav-left" md-component-id="left">
    <md-toolbar class="app-menu md-locked-open">
        <div class="user-details">
            <md-list-item class="md-1-line" style="margin:10px 0px">
            {/literal}
            <img src="../../{$TEMPLATE_WEBPATH}/resources/images/default_1.png" class="md-avatar" alt="user">
            {literal}
                <div class="md-list-item-text">
                    <small>{{userinfo.first_name + " "}}{{userinfo.last_name}}</small>
                    <h5 style="margin: 0px;">{{userinfo.email}}</h5>
                </div>
            </md-list-item>
        </div>
        <div class="app-dropdown">
            <md-select ng-model="selectedApp" aria-label="app_menu">
                <md-option ng-repeat="app in apps" ng-value="app" ng-click="setSelectedApp(app)">{{app}}</md-option>
            </md-select>
        </div>
    </md-toolbar>

    <md-list class="sidenav-module-list">
        <md-list-item ng-click="navigationToggle(); loadList('Events');" md-ink-ripple class="md-1-line">
            <span style="font-size:14px;" class="vicon-calendar"></span> &nbsp; 
            <span class="vmodule-name">Events</span>
        </md-list-item>
        <md-list-item ng-click="navigationToggle(); loadList('Calendar');" md-ink-ripple class="md-1-line">
            <span style="font-size:14px;" class="vicon-calendar"></span> &nbsp; 
            <span class="vmodule-name">Tasks</span>
        </md-list-item>
        <md-divider></md-divider>
        <md-list-item ng-click="navigationToggle();loadList(module.name);" class="md-1-line" ng-click="module.label" ng-repeat="module in menus[selectedApp]">
            <span style="font-size: 14px;" class="vicon-{{module.name | lowercase | nospace}}"></span> &nbsp; 
            <span class="vmodule-name">{{module.label}}</span>
        </md-list-item>
    </md-list>
    <md-divider></md-divider>
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
{/literal}
