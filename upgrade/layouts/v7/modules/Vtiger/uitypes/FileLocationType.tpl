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
{assign var=FIELD_VALUES value=$FIELD_MODEL->getFileLocationType()}
<select class="select2" name="{$FIELD_MODEL->getFieldName()}">
{foreach item=TYPE key=KEY from=$FIELD_VALUES}
	<option value="{$KEY}" {if $FIELD_MODEL->get('fieldvalue') eq $KEY} selected {/if}>{vtranslate($TYPE, $MODULE)}</option>
{/foreach}
</select>
{/strip}