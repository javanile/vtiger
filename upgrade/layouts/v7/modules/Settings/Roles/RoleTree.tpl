{*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************}
{strip}
<ul>
{foreach from=$ROLE->getChildren() item=CHILD_ROLE}
    <li data-role="{$CHILD_ROLE->getParentRoleString()}" data-roleid="{$CHILD_ROLE->getId()}">
        <div class="toolbar-handle">
            {if $smarty.request.type == 'Transfer'}
                {assign var="SOURCE_ROLE_SUBPATTERN" value='::'|cat:$SOURCE_ROLE->getId()}
                {if strpos($CHILD_ROLE->getParentRoleString(), $SOURCE_ROLE_SUBPATTERN) !== false}
                    <a style="white-space: nowrap" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-default" disabled data-toggle="tooltip" data-placement="top" ><span class="muted">{$CHILD_ROLE->getName()}</span></a>
                {else}
                    <a style="white-space: nowrap" href="" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-default roleEle" data-toggle="tooltip" data-placement="top" >{$CHILD_ROLE->getName()}</a>
                {/if}
            {else}
                <a style="white-space: nowrap" href="{$CHILD_ROLE->getEditViewUrl()}" data-url="{$CHILD_ROLE->getEditViewUrl()}" class="btn btn-default draggable droppable" data-toggle="tooltip" data-placement="top" data-animation="true" title="{vtranslate('LBL_CLICK_TO_EDIT_OR_DRAG_TO_MOVE',$QUALIFIED_MODULE)}">{$CHILD_ROLE->getName()}</a>
            {/if}
            {if $smarty.request.view != 'Popup'}
                <div class="toolbar">
                    &nbsp;<a href="{$CHILD_ROLE->getCreateChildUrl()}" data-url="{$CHILD_ROLE->getCreateChildUrl()}" title="{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}"><span class="fa fa-plus-circle"></span></a>
                    &nbsp;<a data-id="{$CHILD_ROLE->getId()}" href="javascript:;" data-url="{$CHILD_ROLE->getDeleteActionUrl()}" data-action="modal" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}"><span class="fa fa-trash"></span></a>
                </div>
            {/if}
        </div>

        {assign var="ROLE" value=$CHILD_ROLE}
        {include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
    </li>
{/foreach}
</ul>
{/strip}