{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<form id="redirectForm" method="post" action="{$REQUEST_URL}" enctype="multipart/form-data">
		{foreach key=FIELD_NAME item=FIELD_VALUE from=$REQUEST_DATA}
			{if $FIELD_NAME eq 'returnrelatedModule'}
				{assign var=FIELD_NAME value=returnrelatedModuleName}
			{/if}
			{if is_array($FIELD_VALUE)}
				{foreach key=KEY item=VALUE from=$FIELD_VALUE}
					{if is_array($VALUE)}
						{assign var=VALUE value=Zend_Json::encode($VALUE)}
						<input type="hidden" name="{$FIELD_NAME}[{$KEY}]" value='{$VALUE}'>
					{else}
						<input type="hidden" name="{$FIELD_NAME}[{$KEY}]" value="{htmlentities($VALUE)}">
					{/if}
				{/foreach}
			{else if $FIELD_NAME eq 'notecontent'}
				<input type="hidden" name="{$FIELD_NAME}" value='{decode_html($FIELD_VALUE)}' >
			{else}
				<input type="hidden" name="{$FIELD_NAME}" value="{htmlentities($FIELD_VALUE)}">
			{/if}
		{/foreach}
	</form>

	{literal}
		<script type="text/javascript" src="libraries/jquery/jquery.min.js"></script>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#redirectForm').submit();
			});
		</script>
	{/literal}
{/strip}