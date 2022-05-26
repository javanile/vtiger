{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
    {foreach item=RECORD from=$RECORD_MODELS}
        <table class="table table-bordered" id="SCANNER_{$RECORD->getId()}"> 
            <thead>
                <tr>
					<th class="blockHeader" colspan="4">
						<span class="font-x-large">{$RECORD->getName()}</span>
					<div class="pull-right btn-group">
						<button class="btn dropdown-toggle" data-toggle="dropdown">
							{vtranslate('Actions',$QUALIFIED_MODULE)}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu pull-right">
							{foreach item=LINK from=$RECORD->getRecordLinks()}
								<li> <a style="text-shadow: none" {if strpos($LINK->getUrl(), 'javascript:')===0} href='javascript:void(0);' onclick='{$LINK->getUrl()|substr:strlen("javascript:")};'
										{else} href={$LINK->getUrl()} {/if}>{vtranslate($LINK->getLabel(),$QUALIFIED_MODULE)}</a>
							   </li>
							{/foreach}
						</ul>
					</div>
					</th>
                </tr>
            </thead>
            <tbody>
                {assign var=FIELDS value=$RECORD->getDetailViewFields()}
                <tr>
                {assign var=COUNTER value=0}
                {foreach key=FIELDNAME item=FIELD_MODEL from=$FIELDS}
                    {if $COUNTER%2 eq 0 AND $COUNTER neq 0}
                        </tr>
                        <tr>
                    {/if}
                    <td class="fieldLabel">
						<strong>{vtranslate($FIELD_MODEL->get('label'),$QUALIFIED_MODULE)}</strong>
                    </td>
                    <td class="fieldValue">
						{assign var=DISPLAY_VALUE value=$RECORD->getDisplayValue($FIELDNAME)}
                        {if $FIELDNAME eq 'password'}
                            ******
						{elseif $FIELDNAME eq 'markas' && !empty($DISPLAY_VALUE)}	
							{vtranslate('LBL_MARK_MESSAGE_AS',$QUALIFIED_MODULE)}&nbsp;{vtranslate($RECORD->getDisplayValue($FIELDNAME),$QUALIFIED_MODULE)}
						{elseif $FIELDNAME eq 'searchfor' || $FIELDNAME eq 'timezone' }	
							{vtranslate($RECORD->getDisplayValue($FIELDNAME),$QUALIFIED_MODULE)}
                        {else}
                            {$DISPLAY_VALUE}
                        {/if}
                    </td>
                    {assign var=COUNTER value=$COUNTER+1}
					{if $FIELD_MODEL@last}
						<td></td>
						<td></td>
					{/if}	
                {/foreach}
                </tr>
            </tbody>
        </table>
    {/foreach}
</div>
{/strip}
