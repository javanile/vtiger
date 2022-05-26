{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <div class="modal-dialog modelContainer">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="{vtranslate('LBL_ADD_DASHBOARD')}"}
        <div class="modal-content">
            <form id="AddDashBoardTab" name="AddDashBoardTab" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}"/>
                <input type="hidden" name="action" value="DashBoardTab"/>
                <input type="hidden" name="mode" value="addTab"/>
                <div class="modal-body clearfix">
                    <div class="col-lg-5">
                        <label class="control-label pull-right marginTop5px">
                            {vtranslate('LBL_TAB_NAME',$MODULE)}&nbsp;<span class="redColor">*</span>
                        </label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" name="tabName" data-rule-required="true" size="25" class="inputElement" maxlength='30'/>
                    </div>
                    <div class="col-lg-12" style='margin-top: 10px; padding: 5px;'>
                        <div class="alert-info">
                            <center>
                                <i class="fa fa-info-circle"></i>&nbsp;&nbsp;
                                {vtranslate('LBL_MAX_CHARACTERS_ALLOWED_DASHBOARD', $MODULE)}
                            </center></div>
                    </div>
                </div>
                {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
            </form>
        </div>
    </div>
{/strip}
