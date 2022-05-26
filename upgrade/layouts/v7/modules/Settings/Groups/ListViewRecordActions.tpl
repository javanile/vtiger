{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************} 

<div class="table-actions">
{foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
    {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
    {if $RECORD_LINK->getIcon() eq 'icon-pencil' }
        <span>
            <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                <i class="fa fa-pencil" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
            </a>
        </span>
    {/if}
    {if  $RECORD_LINK->getIcon() eq 'icon-trash'}
        <span>
            <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                <i class="fa fa-trash" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}" ></i>
            </a>
        </span>
    {/if}
{/foreach}
</div>
