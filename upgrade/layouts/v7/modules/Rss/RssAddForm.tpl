{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Rss/views/ViewTypes.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <div class='modal-dialog' id="rssAddFormUi">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_FEED_SOURCE', $MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <form class="form-horizontal" id="rssAddForm"  method="post" action="index.php" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="col-lg-4 fieldLabel">
                                <label>
                                    {vtranslate('LBL_FEED_SOURCE',$MODULE)}&nbsp;<span class="redColor">*</span>
                                </label>
                            </div>
                            <div class="col-lg-8 fieldValue">
                                <input class="form-control" type="text" id="feedurl" name="feedurl" data-rule-required="true" data-rule-url="true" value="" placeholder="{vtranslate('LBL_ENTER_FEED_SOURCE',$MODULE)}"/>
                            </div>
                        </div>
                    </div>
                </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
        </form>
    </div>
  </div>
{/strip}
