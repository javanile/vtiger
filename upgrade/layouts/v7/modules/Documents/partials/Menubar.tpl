{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{if $smarty.request.view eq 'Detail'}
<div id="modules-menu" class="modules-menu">    
    <ul>
        <li class="active">
            <a href="{$MODULE_MODEL->getListViewUrl()}">
				{$MODULE_MODEL->getModuleIcon()}
                <span>{$MODULE}</span>
            </a>
        </li>
    </ul>
</div>
{/if}