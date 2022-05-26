{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
<div class="container-fluid" id="importModules">
    <div class="widget_header row-fluid">
            <h3>{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}</h3>
    </div><hr>
    <div class="contents">
        <div class="row-fluid" style="margin-top: 5%">
            <div class="span1">&nbsp;</div>
            <div class="span10">
                <div class="alert alert-danger">
                    {vtranslate('LBL_DISCLAIMER_FOR_IMPORT_FROM_ZIP', $QUALIFIED_MODULE)}
                </div>
                <div style="margin-top: 28px;">
                    <input type="checkbox" name="acceptDisclaimer" /> &nbsp;&nbsp;<b>{vtranslate('LBL_ACCEPT_WITH_THE_DISCLAIMER', $QUALIFIED_MODULE)}</b>
                </div>
            </div>
            <div class="span1">&nbsp;</div>
        </div>
        <div  class="row-fluid">
            <div class="span1">&nbsp;</div>
            <div class="span10">
                <form class="form-horizontal" id="importUserModule" name="importUserModule" action='index.php' method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="module" value="ModuleManager" />
                    <input type="hidden" name="moduleAction" value="Import"/>
                    <input type="hidden" name="parent" value="Settings" />
                    <input type="hidden" name="view" value="ModuleImport" />
                    <input type="hidden" name="mode" value="importUserModuleStep2" />
                    <div class="row-fluid" name="proceedInstallation">
                        <span class="span6">
                            <input type="file" name="moduleZip" id="moduleZip" size="80px" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
                                   data-validator={Zend_Json::encode([['name'=>'UploadModuleZip']])} />
                        </span>
                        <span class="span6">
                            <span class="pull-right">
                                <div class=" pull-right cancelLinkContainer">
                                    <a class="cancelLink" href="index.php?module=ExtensionStore&parent=Settings&view=ExtensionImport&mode=step1">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
                                </div>
                                <button class="btn btn-success" disabled="disabled" type="submit" name="importFromZip"><strong>{vtranslate('LBL_IMPORT', $MODULE)}</strong></button>
                            </span>
                        </span>
                    </div>
                </form>
           </div>
           <div class="span1">&nbsp;</div>
        </div>
    </div>
</div>
{/strip}