{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
<div class="sidebar-menu sidebar-menu-full">
    <div class="module-filters" id="module-filters">
        <div class="sidebar-container lists-menu-container">
            <div class="sidebar-header clearfix">
                <h5 class="pull-left">Lists </h5>
                <button id="createFilter" data-url="{CustomView_Record_Model::getCreateViewUrl($MODULE)}" class="btn btn-default pull-right sidebar-btn">
                    <span class="fa fa-plus" aria-hidden="true"></span>
                </button> 
            </div>
            <hr>
            <div>
                <input class="search-list" type="text" placeholder="Search for List">
            </div>
            <div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
                <div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
                    <div class="mCSB_container" style="position:relative; top:0; left:0;">
                        <div class="list-menu-content">
                                {if $CUSTOM_VIEWS && count($CUSTOM_VIEWS) > 0}
                                    {foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
                                    {if $GROUP_LABEL neq 'Mine' && $GROUP_LABEL neq 'Shared'}
                                        {continue}
                                     {/if}
                                    <div class="list-group">   
                                        <h6 class="lists-header {if count($GROUP_CUSTOM_VIEWS) <=0} hide {/if}" >
                                            {if $GROUP_LABEL eq 'Mine'}
                                                {vtranslate('LBL_MY_LIST',$MODULE)}
                                            {else}
                                                {vtranslate('LBL_SHARED_LIST',$MODULE)}
                                            {/if}
                                        </h6>
										<input type="hidden" name="allCvId" value="{CustomView_Record_Model::getAllFilterByModule($MODULE)->get('cvid')}" />
                                        <ul class="lists-menu">
                                        {foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS name="customView"}
                                            <li style="font-size:12px;" class='listViewFilter {if $VIEWID eq $CUSTOM_VIEW->getId() && ($CURRENT_TAG eq '') && !$FOLDER_VALUE} active{/if} {if $smarty.foreach.customView.iteration gt 5} filterHidden hide{/if} '>
                                                {assign var=VIEWNAME value={vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}} 
                                                <a class="filterName" href="javascript:;" data-filter-id="{$CUSTOM_VIEW->getId()}">{if {$VIEWNAME|strlen > 40} } {$VIEWNAME|substr:0:40|@escape:'html'}..{else}{$VIEWNAME|@escape:'html'}{/if}</a> 
                                                    <div class=" pull-right">
                                                        <span class="js-popover-container">
                                                    <span class="fa fa-angle-down" rel="popover" data-toggle="popover" aria-expanded="true" 
                                                        {if $CUSTOM_VIEW->isMine() and $CUSTOM_VIEW->get('viewname') neq 'All'}
                                                            data-deletable="{if $CUSTOM_VIEW->isDeletable()}true{else}false{/if}" data-editable="{if $CUSTOM_VIEW->isEditable()}true{else}false{/if}" 
                                                            {if $CUSTOM_VIEW->isEditable()}  data-editurl="{$CUSTOM_VIEW->getEditUrl()}{/if}" {if $CUSTOM_VIEW->isDeletable()} data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}"{/if}
                                                            {/if}
                                                          toggleClass="fa {if $CUSTOM_VIEW->isDefault()}fa-check-square-o{else}fa-square-o{/if}" data-filter-id="{$CUSTOM_VIEW->getId()}" 
                                                          data-is-default="{$CUSTOM_VIEW->isDefault()}" data-defaulttoggle="{$CUSTOM_VIEW->getToggleDefaultUrl()}" data-default="{$CUSTOM_VIEW->getDuplicateUrl()}" data-isMine="{if $CUSTOM_VIEW->isMine()}true{else}false{/if}">
                                                    </span>
                                                        </span>                                               
                                                    </div>
                                                        </li>
                                            {/foreach}
                                        </ul>
                                            

                                        <div class='clearfix'> 
                                            <a class="toggleFilterSize" data-more-text="Show more" data-less-text="Show less"> 
                                                {if $smarty.foreach.customView.iteration gt 5}
                                                    {vtranslate('LBL_SHOW_MORE',Vtiger)}
                                                {/if}
                                            </a>
                                        </div>
                                     </div>
                                    {/foreach}
                                    <div id="filterActionPopoverHtml">
                                        <ul class="listmenu hide" role="menu">
                                            <li role="presentation" class="editFilter">
                                                <a role="menuitem"><i class="fa fa-pencil-square-o"></i>&nbsp;{vtranslate('LBL_EDIT',$MODULE)}</a>
                                            </li>
                                            <li role="presentation" class="deleteFilter ">
                                                <a role="menuitem"><i class="fa fa-trash"></i>&nbsp;{vtranslate('LBL_DELETE',$MODULE)}</a>
                                            </li>
                                            <li role="presentation" class="duplicateFilter">
                                                <a role="menuitem" ><i class="fa fa-files-o"></i>&nbsp;{vtranslate('LBL_DUPLICATE',$MODULE)}</a>
                                                </li>
                                            <li role="presentation" class="toggleDefault" >
                                                    <a role="menuitem" >
                                                    <i data-check-icon="fa-check-square-o" data-uncheck-icon="fa-square-o"></i>&nbsp;{vtranslate('LBL_DEFAULT',$MODULE)}
                                                    </a>
                                                </li>
                                            </ul>
                                    </div>
                                {/if}
                                <div class="list-group hide noLists">
                                    <h6 class="lists-header"><center> {vtranslate('LBL_NO')} {vtranslate('Lists')} {vtranslate('LBL_FOUND')} ... </center></h6>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
                                
            <div class="list-menu-content">
                <div class="list-group">
                    <div class="sidebar-header clearfix">
                        <h5 class="pull-left">{vtranslate('LBL_FOLDERS',$MODULE)}</h5>
                        <button id="createFolder" class="btn btn-default pull-right sidebar-btn">
                            <span class="fa fa-plus" aria-hidden="true"></span>
                        </button>
                    </div>
                    <hr>
                    <div>
                        <input class="search-folders" type="text" placeholder="Search for Folders">
                    </div>
                    <div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
                        <div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
                            <div class="mCSB_container" style="position:relative; top:0; left:0;">
                                <ul id="folders-list" class="lists-menu">
                                {foreach item="FOLDER" from=$FOLDERS}
                                     {assign var=FOLDERNAME value={vtranslate($FOLDER->get('foldername'), $MODULE)}} 
                                    <li style="font-size:12px;" class='documentFolder {if $FOLDER_VALUE eq $FOLDER->getName()} active{/if}'>
                                        <a class="filterName" href="javascript:void(0);" data-filter-id="{$FOLDER->get('folderid')}" data-folder-name="{$FOLDER->get('foldername')}" title="{$FOLDERNAME}">
                                            <i class="fa {if $FOLDER_VALUE eq $FOLDER->getName()}fa-folder-open{else}fa-folder{/if}"></i> 
                                            <span class="foldername">{if {$FOLDERNAME|strlen > 40} } {$FOLDERNAME|substr:0:40|@escape:'html'}..{else}{$FOLDERNAME|@escape:'html'}{/if}</span>
                                        </a>
                                        {if $FOLDER->getName() neq 'Default' && $FOLDER->getName() neq 'Google Drive' && $FOLDER->getName() neq 'Dropbox'}
                                            <div class="dropdown pull-right">
                                                <span class="fa fa-caret-down dropdown-toggle" data-toggle="dropdown" aria-expanded="true"></span>
                                                <ul class="dropdown-menu dropdown-menu-right vtDropDown" role="menu">
                                                    <li class="editFolder " data-folder-id="{$FOLDER->get('folderid')}">
                                                        <a role="menuitem" ><i class="fa fa-pencil-square-o"></i>&nbsp;Edit</a>
                                                    </li>
                                                    <li class="deleteFolder " data-deletable="{!$FOLDER->hasDocuments()}" data-folder-id="{$FOLDER->get('folderid')}">
                                                        <a role="menuitem" ><i class="fa fa-trash"></i>&nbsp;Delete</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        {/if}
                                    </li>
                                {/foreach}
                                <li class="noFolderText" style="display: none;">
                                    <h6 class="lists-header"><center> 
                                        {vtranslate('LBL_NO')} {vtranslate('LBL_FOLDERS', $MODULE)} {vtranslate('LBL_FOUND')} ... 
                                    </center></h6>    
                                </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
            
        </div>
    </div>
    <div class="module-filters">
        <div class="sidebar-container lists-menu-container">
            <h4 class="lists-header">
                {vtranslate('LBL_TAGS', $MODULE)}
            </h4>
            <hr>
            <div class="menu-scroller mCustomScrollBox">
                <div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
                    <div class="mCSB_container" style="position:relative; top:0; left:0;">
                        <div class="list-menu-content">
                            <div id="listViewTagContainer" class="multiLevelTagList" 
                            {if $ALL_CUSTOMVIEW_MODEL} data-view-id="{$ALL_CUSTOMVIEW_MODEL->getId()}" {/if}
                            data-list-tag-count="{Vtiger_Tag_Model::NUM_OF_TAGS_LIST}">
                                {foreach item=TAG_MODEL from=$TAGS name=tagCounter}
                                    {assign var=TAG_LABEL value=$TAG_MODEL->getName()}
                                    {assign var=TAG_ID value=$TAG_MODEL->getId()}
                                    {if $smarty.foreach.tagCounter.iteration gt Vtiger_Tag_Model::NUM_OF_TAGS_LIST}
                                        {break}
                                    {/if}
                                    {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true ACTIVE= $CURRENT_TAG eq $TAG_ID}
                                {/foreach}
                                <div> 
                                    <a class="moreTags {if (count($TAGS) - Vtiger_Tag_Model::NUM_OF_TAGS_LIST) le 0} hide {/if}">
                                        <span class="moreTagCount">{count($TAGS) - Vtiger_Tag_Model::NUM_OF_TAGS_LIST}</span>
                                        &nbsp;{vtranslate('LBL_MORE',$MODULE)|strtolower}
                                    </a>
                                    <div class="moreListTags hide">
                                {foreach item=TAG_MODEL from=$TAGS name=tagCounter}
                                    {if $smarty.foreach.tagCounter.iteration le Vtiger_Tag_Model::NUM_OF_TAGS_LIST}
                                        {continue}
                                    {/if}
                                    {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true ACTIVE= $CURRENT_TAG eq $TAG_ID}
                                {/foreach}
                                     </div>
                                </div>
                            </div>
                            {include file="AddTagUI.tpl"|vtemplate_path:$MODULE RECORD_NAME="" TAGS_LIST=array()}
                        </div>
                        <div id="dummyTagElement" class="hide">
                            {assign var=TAG_MODEL value=Vtiger_Tag_Model::getCleanInstance()}
                            {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true}
                        </div>
                        <div id="editTagContainer" class="hide">
                            <input type="hidden" name="id" value="" />
                            <div class="editTagContents">
                                <div>
                                    <input type="text" name="tagName" class="createtag" value="" style="width:100%" />
                                </div>
                                <div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                                            <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" />
                                            &nbsp; {vtranslate('LBL_MAKE_PUBLIC',$MODULE)}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-mini btn-success saveTag" style="width:50%;float:left">
                                    <center> <i class="fa fa-check"></i> </center>
                                </button>
                                <button class="btn btn-mini btn-danger cancelSaveTag" style="width:50%">
                                    <center> <i class="fa fa-close"></i> </center>
                                </button>
                            </div>
                        </div>
                   </div>
               </div>
            </div>
        </div>
     </div>
</div>