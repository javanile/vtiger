{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
     <div class="showAllTagContainer hide">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form class="detailShowAllModal">
                    {assign var="TITLE" value="{vtranslate('LBL_ADD_OR_SELECT_TAG',$MODULE,$RECORD_NAME)}"}
                    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE}
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6 selectTagContainer">
                                <div class="form-group">
                                    <label class="control-label">
                                        {vtranslate('LBL_CURRENT_TAGS',$MODULE)}
                                    </label>
                                    <div class="currentTagScroll">
                                        <div class="currentTag multiLevelTagList form-control">
                                            <span class="noTagsPlaceHolder" style="padding:3px;display:none;border:1px solid transparent;color:#999">
                                              {vtranslate('LBL_NO_TAG_EXISTS',$MODULE)}  
                                            </span>
                                            {foreach item=TAG_MODEL from=$TAGS_LIST}
                                                {include file="Tag.tpl"|vtemplate_path:$MODULE }
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">
                                        {vtranslate('LBL_SELECT_FROM_AVAIL_TAG', $MODULE)}
                                    </label>
                                    <div class="dropdown">
                                        <input class="form-control currentTagSelector dropdown-toggle" data-toggle="dropdown" placeholder="{vtranslate('LBL_SELECT_EXISTING_TAG',$MODULE)}" />
                                        <div class="dropdown-menu currentTagMenu">
                                            <div class="scrollable" style="max-height:300px">
                                                <ul style="padding-left:0px;">
                                                {foreach item=TAG_MODEL from=$ALL_USER_TAGS}
                                                    {if array_key_exists($TAG_MODEL->getId(), $TAGS_LIST)}
                                                        {continue}
                                                    {/if} 
                                                    <li class="tag-item list-group-item">
                                                        <a style="margin-left:0px;">
                                                            {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true NO_EDIT=true}
                                                        </a>
                                                    </li>
                                                {/foreach}
                                                <li class="dummyExistingTagElement tag-item list-group-item hide">
                                                    <a style="margin-left:0px;">
                                                        {assign var=TAG_MODEL value=Vtiger_Tag_Model::getCleanInstance()}
                                                        {include file="Tag.tpl"|vtemplate_path:$MODULE NO_DELETE=true NO_EDIT=true}
                                                    </a>
                                                </li>
                                                <li class="tag-item list-group-item">   
                                                    <span class="noTagExistsPlaceHolder" style="padding:3px;color:#999">
                                                       {vtranslate('LBL_NO_TAG_EXISTS',$MODULE)} 
                                                    </span>     
                                                </li>
                                                </ul>
                                           </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div class=" col-lg-6 selectTagContainerborder">
                                <div class="form-group">
                                    <label class="control-label">
                                        {vtranslate('LBL_CREATE_NEW_TAG',$MODULE)}
                                    </label>
                                    <div>
                                        <input name="createNewTag" value="" class="form-control" placeholder="{vtranslate('LBL_ENTER_TAG_NAME',$MODULE)}"/>
                                    </div>
                               </div>
                               <div class="form-group">
                                    <div>
                                        <div class="checkbox">
                                            <label>
                                                <input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
                                                <input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" />
                                                &nbsp; {vtranslate('LBL_SHARE_TAGS',$MODULE)}
                                            </label>
                                        </div>
                                        <div class="pull-right"></div>
                                    </div>
                                </div>
                                            
                                <div class="form-group">
                                    <div class=" vt-default-callout vt-info-callout tagInfoblock">
                                        <h5 class="vt-callout-header">
                                        <span class="fa fa-info-circle"></span>&nbsp; Info </h5>
                                        <div>{vtranslate('LBL_TAG_SEPARATOR_DESC', $MODULE)}</div><br>
                                        <div>{vtranslate('LBL_SHARED_TAGS_ACCESS',$QUALIFIED_MODULE)}</div><br>
                                        <div>{vtranslate('LBL_GOTO_TAGS', $MODULE)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
                </form>
            </div>
        </div>
    </div>
{/strip}