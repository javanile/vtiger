{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
    <div class="table-actions">      
            {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
                <span>
                {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
                
                {if $RECORD_LINK->getIcon() eq 'icon-pencil' }
                      <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} title='{vtranslate('LBL_EDIT', $MODULE)}' onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                      <i class="fa fa-pencil" ></i>
                      </a>
                {/if}
                {if  $RECORD_LINK->getIcon() eq 'icon-trash'}
                    <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} title="{vtranslate('LBL_DELETE', $MODULE)}" onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                    <i class="fa fa-trash" ></i>
                    </a>
                {/if}
                {if !$RECORD_LINK@lastui-sortable}
                    &nbsp;&nbsp;
                {/if}
                </span>
            {/foreach}
    </div>
{/strip}