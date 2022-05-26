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
<div class="pull-right">
<b>
{if $CRON_RECORD_MODEL->isDisabled() }{vtranslate('LBL_DISABLED',$QUALIFIED_MODULE_NAME)}{/if}
    {if $CRON_RECORD_MODEL->isRunning() }{vtranslate('LBL_RUNNING',$QUALIFIED_MODULE_NAME)}{/if}
    {if $CRON_RECORD_MODEL->isEnabled()}
        {if $CRON_RECORD_MODEL->hadTimedout}
            {vtranslate('LBL_LAST_SCAN_TIMED_OUT',$QUALIFIED_MODULE_NAME)}.
        {elseif $CRON_RECORD_MODEL->getLastEndDateTime() neq ''}
            {vtranslate('LBL_LAST_SCAN_AT',$QUALIFIED_MODULE_NAME)}
            {$CRON_RECORD_MODEL->getLastEndDateTime()}
            &
            {vtranslate('LBL_TIME_TAKEN',$QUALIFIED_MODULE_NAME)}:
            {$CRON_RECORD_MODEL->getTimeDiff()}
            {vtranslate('LBL_SHORT_SECONDS',$QUALIFIED_MODULE_NAME)}
        {else}

        {/if}
{/if}
</b>
</div>