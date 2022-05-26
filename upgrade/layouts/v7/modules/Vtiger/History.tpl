{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    <div class="HistoryContainer">
        <div class="historyButtons btn-group" role="group" aria-label="...">
            <button type="button" class="btn btn-default" onclick='Vtiger_Detail_Js.showUpdates(this);'>
                {vtranslate("LBL_UPDATES",$MODULE_NAME)}
            </button>
        </div>
        
        <div class='data-body'>
        </div>
    </div>
    
{/strip}