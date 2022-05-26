/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Settings_Vtiger_Edit_Js",{},{
    
    registerEvents : function() {
        this._super();
        //Register events for settings side menu (Search and collapse open icon )
        var instance = new Settings_Vtiger_Index_Js(); 
        instance.registerBasicSettingsEvents();
    }
})