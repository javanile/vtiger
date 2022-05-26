{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
    <div class="recentActivitiesContainer" id="updates">
        <input type="hidden" id="updatesCurrentPage" value="{$PAGING_MODEL->get('page')}"/>
        <div class='history'>
            {if !empty($RECENT_ACTIVITIES)}
                <ul class="updates_timeline">
                    {foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
                        {assign var=PROCEED value= TRUE}
                        {if ($RECENT_ACTIVITY->isRelationLink()) or ($RECENT_ACTIVITY->isRelationUnLink())}
                            {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                            {if !($RELATION->getLinkedRecord())}
                                {assign var=PROCEED value= FALSE}
                            {/if}
                        {/if}
                        {if $PROCEED}
                            {if $RECENT_ACTIVITY->isCreate()}
                                <li>
                                    <time class="update_time cursorDefault">
                                        <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getParent()->get('createdtime'))}">
                                            {Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getParent()->get('createdtime'))}
                                        </small>
                                    </time>
                                    {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                                        <div class="update_icon bg-info">
                                            <i class='update_image vicon-vtigeruser'></i>
                                        </div>
                                    {else}
                                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                            {if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
                                                <div class="update_icon">
                                                    <img class="update_image" src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" >
                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    <div class="update_info">
                                        <h5>
                                            <span class="field-name">{$RECENT_ACTIVITY->getModifiedBy()->getName()}</span> {vtranslate('LBL_CREATED', $MODULE_NAME)}
                                        </h5>
                                    </div>
                                </li>
                            {else if $RECENT_ACTIVITY->isUpdate()}
                                <li>
                                    <time class="update_time cursorDefault">
                                        <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getActivityTime())}">
                                            {Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}
                                        </small>
                                    </time>
                                    {assign var=USER_MODEL value=$RECENT_ACTIVITY->getModifiedBy()}
                                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                                        <div class="update_icon bg-info">
                                            <i class='update_image vicon-vtigeruser'></i>
                                        </div>
                                    {else}
                                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                            {if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
                                                <div class="update_icon">
                                                    <img class="update_image" src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" >
                                                </div>
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    <div class="update_info">
                                        <div> 
                                            <h5>
                                                <span class="field-name">{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()} </span> {vtranslate('LBL_UPDATED', $MODULE_NAME)}
                                            </h5>
                                        </div>
                                        {foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
                                            {if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewable() && $FIELDMODEL->getFieldInstance()->getDisplayType() neq '5'}
                                                <div class='font-x-small updateInfoContainer textOverflowEllipsis'>
                                                    <div class='update-name'><span class="field-name">{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</span>
                                                        {if $FIELDMODEL->get('prevalue') neq '' && $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && ($FIELDMODEL->get('postvalue') eq '0' || $FIELDMODEL->get('prevalue') eq '0'))}
                                                            <span> &nbsp;{vtranslate('LBL_CHANGED')}</span>
                                                        </div>
                                                        <div class='update-from'><span class="field-name">{vtranslate('LBL_FROM')}</span>&nbsp;
                                                            <em style="white-space:pre-line;" title="{strip_tags({Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))})}">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</em>
                                                        </div>
                                                    {else if $FIELDMODEL->get('postvalue') eq '' || ($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                        &nbsp;(<del>{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('prevalue'))))}</del> ) {vtranslate('LBL_IS_REMOVED')}</div>
                                                    {else if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                    &nbsp;{vtranslate('LBL_UPDATED')}</div>
                                                {else}
                                                &nbsp;{vtranslate('LBL_CHANGED')}</div>
                                            {/if}
                                            {if $FIELDMODEL->get('postvalue') neq '' && !($FIELDMODEL->getFieldInstance()->getFieldDataType() eq 'reference' && $FIELDMODEL->get('postvalue') eq '0')}
                                                <div class="update-to"><span class="field-name">{vtranslate('LBL_TO')}</span>&nbsp;<em style="white-space:pre-line;">{Vtiger_Util_Helper::toVtiger6SafeHTML($FIELDMODEL->getDisplayValue(decode_html($FIELDMODEL->get('postvalue'))))}</em>
                                                </div>
                                            {/if}
                                            </div>
                                        {/if}
                                    {/foreach}
                                    </div>
                                </li>

                            {else if ($RECENT_ACTIVITY->isRelationLink() || $RECENT_ACTIVITY->isRelationUnLink())}
                                {assign var=RELATED_MODULE value= $RELATION->getLinkedRecord()->getModuleName()}
                                <li>
                                    <time class="update_time cursorDefault">
                                        <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATION->get('changedon'))}">
                                            {Vtiger_Util_Helper::formatDateDiffInStrings($RELATION->get('changedon'))} </small>
                                    </time>
									<div class="update_icon bg-info-{$RELATED_MODULE|strtolower}">
										{if {$RELATED_MODULE|strtolower eq 'modcomments'}}
											{assign var="VICON_MODULES" value="vicon-chat"}
											<i class="update_image {$VICON_MODULES}"></i>
										{else}
											<span class="update_image">{Vtiger_Module_Model::getModuleIconPath($RELATED_MODULE)}</span>
										{/if}
									</div>
                                    <div class="update_info">
                                        <h5>
                                            {assign var=RELATION value=$RECENT_ACTIVITY->getRelationInstance()}
                                           <span class="field-name">
                                                {vtranslate($RELATION->getLinkedRecord()->getModuleName(), $RELATION->getLinkedRecord()->getModuleName())}
                                            </span>&nbsp; 
                                            <span>
                                                {if $RECENT_ACTIVITY->isRelationLink()}
                                                    {vtranslate('LBL_LINKED', $MODULE_NAME)}
                                                {else}
                                                    {vtranslate('LBL_UNLINKED', $MODULE_NAME)}
                                                {/if}
                                            </span>
                                        </h5>
                                        <div class='font-x-small updateInfoContainer textOverflowEllipsis'>
                                            <span>
                                                {if $RELATION->getLinkedRecord()->getModuleName() eq 'Calendar'}
                                                    {if isPermitted('Calendar', 'DetailView', $RELATION->getLinkedRecord()->getId()) eq 'yes'}
                                                        {assign var=PERMITTED value=1}
                                                    {else}
                                                        {assign var=PERMITTED value=0}
                                                    {/if}
                                                {else}
                                                    {assign var=PERMITTED value=1}
                                                {/if}
                                                {if $PERMITTED}
                                                    {if $RELATED_MODULE eq 'ModComments'}
                                                        {$RELATION->getLinkedRecord()->getName()}
                                                    {else}
                                                        {assign var=DETAILVIEW_URL value=$RELATION->getRecordDetailViewUrl()}
                                                        {if $DETAILVIEW_URL}<a {if stripos($DETAILVIEW_URL, 'javascript:') === 0}onclick{else}href{/if}='{$DETAILVIEW_URL}'>{/if}
                                                            <strong>{$RELATION->getLinkedRecord()->getName()}</strong>
                                                            {if $DETAILVIEW_URL}</a>{/if}
                                                        {/if}
                                                    {/if}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            {else if $RECENT_ACTIVITY->isRestore()}
                            {/if}
                        {/if}
                    {/foreach}
                    {if $PAGING_MODEL->isNextPageExists()}
                        <li id='more_button'>
                            <div class='update_icon' id="moreLink">
                                <button type="button" class="btn btn-success moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</button>
                            </div>
                        </li>
                    {/if}
                </ul>
            {else}
                <div class="summaryWidgetContainer">
                    <p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
                </div>
            {/if}
        </div>
    </div>
{/strip}
