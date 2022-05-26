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
<div id="massEditContainer" class='fc-overlay-modal modal-content'>
    <form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
        <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="action" value="MassSave" />
            <input type="hidden" name="viewname" value="{$CVID}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
            <div>
                <header class="overlayHeader" style='flex:0 0 auto;'>
                    {assign var=TITLE value="{vtranslate('LBL_MASS_EDITING',$MODULE)}"}
                    {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                </header>
                <div class='modal-body' style="margin-bottom:60px">
                    <div class='datacontent editViewContents'>
                        {include file="partials/EditViewContents.tpl"|@vtemplate_path:$MODULE}
                    </div>
                </div>
                <footer class='modal-footer overlayFooter'>
                   <center>
                       <button type='submit' class='btn btn-success saveButton'>{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
                       <a class='cancelLink' data-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                   </center>
               </footer>
            </div>
    </form>
</div>
{/strip}