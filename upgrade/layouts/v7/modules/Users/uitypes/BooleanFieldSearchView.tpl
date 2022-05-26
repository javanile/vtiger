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
    {assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
    {assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
    {assign var=CHECKED_VALUE value="1"}
    {assign var=UNCHECKED_VALUE value="0"}
    
    {if $FIELD_MODEL->get('name') eq 'is_admin'}
        {assign var=CHECKED_VALUE value="on"}
        {assign var=UNCHECKED_VALUE value="off"}
    {elseif $FIELD_MODEL->get('name') eq 'is_owner'}
        {assign var=UNCHECKED_VALUE value=' '}
    {/if}
    <div class="">
    <select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" style="width:90px;" data-fieldinfo='{$FIELD_INFO|escape}'>
        <option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
        <option value="{$CHECKED_VALUE}" {if $SEARCH_VALUES eq $CHECKED_VALUE} selected{/if}>{vtranslate('LBL_YES',$MODULE)}</option>
        <option value="{$UNCHECKED_VALUE}" {if $SEARCH_VALUES eq $UNCHECKED_VALUE} selected{/if}>{vtranslate('LBL_NO',$MODULE)}</option>
    </select>
    </div>
{/strip}