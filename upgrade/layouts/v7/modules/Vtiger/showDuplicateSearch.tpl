{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Vtiger/views/MassActionAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="findDuplicate">
            <input type='hidden' name='module' value='{$MODULE}' />
            <input type='hidden' name='view' value='FindDuplicates' />
            
            {assign var=HEADER_TITLE value={vtranslate('LBL_MATCH_CRITERIA', $MODULE)}}
            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="modal-body">
                <div class="form-group">
                    <label class="col-lg-3 control-label">{vtranslate('LBL_MATCH_FIELDS', $MODULE)}</label>
                    <div class="col-lg-8">
                        <select id="fieldList" class="select2 form-control" multiple="true" name="fields[]"
							data-rule-required="true">
							{foreach from=$FIELDS item=FIELD}
								{if $FIELD->isViewableInDetailView()}
									<option value="{$FIELD->getName()}">{vtranslate($FIELD->get('label'), $MODULE)}</option>
								{/if}
							{/foreach}
						</select> 
                    </div>
                </div>    
                <div class="form-group">
                    <div class="col-lg-3">&nbsp;</div>
                    <div class="col-lg-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" checked="checked" name="ignoreEmpty"/> &nbsp;{vtranslate('LBL_IGNORE_EMPTY_VALUES',$MODULE)}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE BUTTON_NAME={vtranslate('LBL_FIND_DUPLICATES',$MODULE)}}
        </form>
    </div>
</div>
