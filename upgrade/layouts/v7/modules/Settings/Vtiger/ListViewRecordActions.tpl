{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/TaxAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="table-actions" style = "width:60px">
    {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
        {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
        <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};
                if (event.stopPropagation){ldelim}
        event.stopPropagation();{rdelim} else{ldelim}
        event.cancelBubble = true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
            <i class="{if $RECORD_LINK->getLabel() eq 'LBL_EDIT'}fa fa-pencil{else if $RECORD_LINK->getLabel() eq 'LBL_DELETE'}fa fa-trash{else}{$RECORD_LINK->getIcon()}{/if}" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
        </a>
        {if !$RECORD_LINK@last}
            &nbsp;&nbsp;
        {/if}
    {/foreach}
</div>
