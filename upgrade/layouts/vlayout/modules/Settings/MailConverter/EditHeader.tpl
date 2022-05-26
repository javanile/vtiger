{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
    <div class="listViewPageDiv">
	<input type="hidden" id="create" value="{$CREATE}" />
	<input type="hidden" id="recordId" value="{$RECORD_ID}" />
	<input type="hidden" id="step" value="{$STEP}" />
	<h3>
	    {if $CREATE eq 'new'}
			{vtranslate('LBL_ADDING_NEW_MAILBOX',$QUALIFIED_MODULE)}
	    {else}
			{vtranslate('LBL_EDIT_MAILBOX',$QUALIFIED_MODULE)}
	    {/if}
	</h3>
	<hr>

	<div>
            <ul class="crumbs" style="margin-left: 65px;margin-top:10px;">
                <li class="first step"  style="z-index:9" id="step1">
                    <a>
                        <span class="stepNum">1</span>
                        <span class="stepText">{vtranslate('MAILBOX_DETAILS',$QUALIFIED_MODULE)}</span>
                    </a>
                </li>
                <li style="z-index:8" class="step" id="step2">
                    <a>
                        <span class="stepNum">2</span>
                        <span class="stepText">{vtranslate('SELECT_FOLDERS',$QUALIFIED_MODULE)}</span>
                    </a>
                </li>
                {if $CREATE eq 'new'}
				    <li class="step last" style="z-index:7" id="step3">
						<a>
						    <span class="stepNum">3</span>
						    <span class="stepText">{vtranslate('ADD_RULES',$QUALIFIED_MODULE)}</span>
						</a>
				    </li>
				{/if}
            </ul>
        </div>
	<div class="clearfix"></div>
    </div>
{/strip}