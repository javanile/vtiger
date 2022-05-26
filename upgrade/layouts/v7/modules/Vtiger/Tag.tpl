{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
 
 <span class="tag {if $ACTIVE eq true} active {/if}" title="{$TAG_MODEL->getName()}" data-type="{$TAG_MODEL->getType()}" data-id="{$TAG_MODEL->getId()}">
    <i class="activeToggleIcon fa {if $ACTIVE eq true} fa-circle-o {else} fa-circle {/if}"></i>
    <span class="tagLabel display-inline-block textOverflowEllipsis" title="{$TAG_MODEL->getName()}">{$TAG_MODEL->getName()}</span>
    {if !$NO_EDIT}
        <i class="editTag fa fa-pencil"></i>
    {/if}
    {if !$NO_DELETE}
        <i class="deleteTag fa fa-times"></i>
    {/if}
</span>