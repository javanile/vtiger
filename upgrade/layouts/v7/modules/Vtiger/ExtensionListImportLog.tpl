{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*********************************************************************************/
-->*}

<script type="text/javascript" src="{vresource_url('layouts/v7/modules/Vtiger/resources/ExtensionCommon.js')}"></script>

<div class='fc-overlay-modal modal-content'>
    <div class="overlayHeader">
        {assign var=TITLE value={vtranslate('LBL_IMPORT_RESULTS_GOOGLE',$MODULE)}}
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
    </div>
    <div class="modal-body" style = "margin-bottom:450px">
        {include file="ExtensionListLog.tpl"|vtemplate_path:$MODULE}
    </div>
</div>

