{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    
    <div class="{if $DIRECT_ADD_RELATION_LOAD} relatedTabModulesList{/if}">
        <div id="addRelationContainer">
            <div>
                <div>
                    <form class="form-horizontal" id="addRelationForm">
                        <div style="border:1px solid #ccc;padding:3%">
                            <div class="widget_header row">
                                <div class="col-sm-8">
                                    <h4>{vtranslate('LBL_ADDING_RELATIONSHIP', $QUALIFIED_MODULE,vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME))}</h4>
                                </div>
                            </div>
                            <hr>
                            <div class="form-group">
                                <label class="control-label fieldLabel col-sm-3">
                                    <span>{vtranslate('LBL_SELECTED_RELATED_MODULE', $QUALIFIED_MODULE)}</span>
                                </label>
                                <div class="controls col-sm-9">
                                    {assign var=TRANSLATED_SOURCE_MODULE_NAME value={vtranslate($SELECTED_MODULE_NAME, $SELECTED_MODULE_NAME)}}
                                    {assign var=SINGULAR_MODULE_NAMES value=[$SELECTED_MODULE_NAME => $TRANSLATED_SOURCE_MODULE_NAME]}
                                    <select class="select2 col-sm-3" name="relatedModule">
                                        {foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
                                            {assign var=TRANSLATED_NAME value={vtranslate($MODULE_NAME, $MODULE_NAME)}}
                                            {$SINGULAR_MODULE_NAMES.$TRANSLATED_NAME = {vtranslate("SINGLE_$MODULE_NAME", $MODULE_NAME)}}
                                            <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_RELATED_MODULE_NAME}selected="selected"{/if}>
                                                {$TRANSLATED_NAME}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <span class="control-label fieldLabel col-sm-3">
                                    {vtranslate('LBL_SELECTED_RELATION_TYPE', $QUALIFIED_MODULE)}
                                </span>
                                <div class="controls col-sm-9">
                                    <div class="relationImages row">
                                        <a href="javascript:void(0);" class="col-sm-3">
                                            <label class="radio-group cursorPointer">
                                                <input type="radio" name="1-1" checked="checked">
                                                <h5>{vtranslate('1-1',$QUALIFIED_MODULE)}</h5>
                                            </label>
                                            <img class="selected" src="{vimage_path('1-1.png')}" width="110" height="80" data-value="1:1" 
                                                 data-supported-field-to-enable='["fieldInPrimary","fieldInRelated"]' 
                                                     data-non-supported-modules='[]' 
                                                 data-supported-modules='[]' />
                                        </a>
                                        {if $SELECTED_MODULE_NAME neq 'Calendar' && $SELECTED_MODULE_NAME neq 'Events'}
                                            <a href="javascript:void(0);" class="col-sm-3">
                                                <label class="radio-group cursorPointer">
                                                    <input name="1-M" type="radio">
                                                    <h5>{vtranslate('1-N',$QUALIFIED_MODULE)}</h5>
                                                </label>
                                               <img src="{vimage_path('1-N.png')}" width="110" height="80" data-value="1:N" 
                                                    data-supported-field-to-enable='["fieldInRelated","tabInPrimary"]'
                                                    data-non-supported-modules='["Calendar","Documents"]'
                                                    data-supported-modules='[]' />
                                           </a>
                                            <a href="javascript:void(0);" class="col-sm-3">
                                                <label class="radio-group cursorPointer">
                                                    <input type="radio" name="M-1">
                                                    <h5>{vtranslate('N-1',$QUALIFIED_MODULE)}</h5>
                                                </label>
                                               <img src="{vimage_path('N-1.png')}" width="110" height="80" data-value="N:1" 
                                                    data-supported-field-to-enable='["fieldInPrimary","tabInRelated"]' 
                                                    data-non-supported-modules='["Calendar","Documents"]' 
                                                    data-supported-modules='[]' />
                                           </a>
                                            <a href="javascript:void(0);" class="col-sm-3">
                                                <label class="radio-group cursorPointer">
                                                    <input type="radio" name="M-M">
                                                    <h5>{vtranslate('N-N',$QUALIFIED_MODULE)}</h5>
                                                </label>
                                                    <img src="{vimage_path('N-N.png')}" width="110" height="80" data-value="N:N" 
                                                     data-supported-field-to-enable='["tabInPrimary", "tabInRelated"]'
                                                     data-non-supported-modules='{Vtiger_Functions::jsonEncode($N2N_UNSUPPORTED_MODULES)}'
                                                     data-supported-modules= '[]'/>
                                            </a>
                                        {/if}
                                    </div>
                                    <div class="row" style="padding-top:10px;">
                                        <div class="col-sm-3 textOverflowEllipsis" title="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}">
                                            <img src="{vimage_path('Square.png')}" />
                                            &nbsp;&nbsp;
                                            {vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}
                                        </div>
                                        <div class="col-sm-3">
                                            <img src="{vimage_path('Circle.png')}" />
                                            &nbsp;&nbsp;
                                            {vtranslate('LBL_RELATED_MODULE',$QUALIFIED_MODULE)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" class="singularModuleNames" value='{ZEND_JSON::encode($SINGULAR_MODULE_NAMES)}'>
                            <div class="form-group fieldInPrimary relationFieldTabLabelHolders">
                                <span class="control-label fieldLabel col-sm-3">
                                    <span class="primaryFieldLabel break-word">
                                        {vtranslate('LBL_FIELD_NAME_TEXT', $QUALIFIED_MODULE,
                                                      vtranslate($SELECTED_RELATED_MODULE_NAME,$SELECTED_RELATED_MODULE_NAME),
                                                      vtranslate("SINGLE_$SELECTED_MODULE_NAME",$SELECTED_MODULE_NAME))}
                                    </span>
                                </span>
                                <div class="controls col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" name="fieldInPrimary" value="" data-rule-required="true" data-rule-illegal="true" class="inputElement"/>
                                        </div>
                                        <span class="primaryFieldHelpText" data-toggle="tooltip" 
                                             title="{vtranslate('LBL_FILED_NAME_HELP_TEXT',$QUALIFIED_MODULE,
                                                            vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME),
                                                            vtranslate("SINGLE_$SELECTED_RELATED_MODULE_NAME",$SELECTED_RELATED_MODULE_NAME))}">
                                            <i class="fa fa-info-circle alignMiddle paddingTop10"></i>&nbsp;
                                       </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group tabInPrimary relationFieldTabLabelHolders">
                                <span class="control-label fieldLabel col-sm-3">
                                    <span class="primaryTabLabel break-word">
                                        {vtranslate('LBL_TAB_NAME_TEXT', $QUALIFIED_MODULE,
                                                    vtranslate($SELECTED_RELATED_MODULE_NAME,$SELECTED_RELATED_MODULE_NAME),
                                                    vtranslate("SINGLE_$SELECTED_MODULE_NAME",$SELECTED_MODULE_NAME))}
                                    </span>
                                </span>
                                <div class="controls col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" class="inputElement" name="tabInPrimary" value="" data-rule-required="true" data-rule-illegal="true" />
                                        </div>
                                        <span class="primaryTabHelpText" data-toggle="tooltip" 
                                             title="{vtranslate('LBL_TAB_NAME_HELP_TEXT',$QUALIFIED_MODULE,
                                                            vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME),
                                                            vtranslate("SINGLE_$SELECTED_RELATED_MODULE_NAME",$SELECTED_RELATED_MODULE_NAME))}">
                                            <i class="fa fa-info-circle alignMiddle paddingTop10"></i>&nbsp;
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group fieldInRelated relationFieldTabLabelHolders">
                                <span class="control-label fieldLabel col-sm-3">
                                    <span class="relatedFieldLabel break-word">
                                        {vtranslate('LBL_FIELD_NAME_TEXT', $QUALIFIED_MODULE,
                                                    vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME),
                                                    vtranslate("SINGLE_$SELECTED_RELATED_MODULE_NAME",$SELECTED_RELATED_MODULE_NAME))}
                                    </span>
                                </span>
                                <div class="controls col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" name="fieldInRelated" value="" class="inputElement" data-rule-required="true" data-rule-illegal="true" />
                                        </div>
                                        <span class="relatedFieldLabelHelText" data-toggle="tooltip" 
                                             title="{vtranslate('LBL_FILED_NAME_HELP_TEXT',$QUALIFIED_MODULE,
                                                            vtranslate($SELECTED_RELATED_MODULE_NAME,$SELECTED_RELATED_MODULE_NAME),
                                                            vtranslate("SINGLE_$SELECTED_MODULE_NAME",$SELECTED_MODULE_NAME))}">
                                            <i class="fa fa-info-circle alignMiddle paddingTop10"></i>&nbsp;
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group tabInRelated relationFieldTabLabelHolders">
                                <span class="control-label fieldLabel col-sm-3 ">
                                    <span class="relatedTabLabel break-word">
                                        {vtranslate('LBL_TAB_NAME_TEXT', $QUALIFIED_MODULE,
                                                        vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME),
                                                        vtranslate("SINGLE_$SELECTED_RELATED_MODULE_NAME",$SELECTED_RELATED_MODULE_NAME))}
                                    </span>
                                </span>
                                <div class="controls col-sm-9">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <input type="text" name="tabInRelated" value="" data-rule-required="true" class="inputElement" data-rule-illegal="true" />
                                        </div>
                                        <span class="relatedTabHelpTextLabel" data-toggle="tooltip" 
                                             title="{vtranslate('LBL_TAB_NAME_HELP_TEXT',$QUALIFIED_MODULE,
                                                            vtranslate($SELECTED_RELATED_MODULE_NAME,$SELECTED_RELATED_MODULE_NAME),
                                                            vtranslate("SINGLE_$SELECTED_MODULE_NAME",$SELECTED_MODULE_NAME))}">
                                            <i class="fa fa-info-circle alignMiddle paddingTop10"></i>&nbsp;
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='modal-overlay-footer clearfix'>
                            <div class="row clearfix">
                                <div class='textAlignCenter col-lg-12 col-md-12 col-sm-12 '>
                                    <button type='submit' class='btn btn-success saveButton' >{vtranslate('LBL_SAVE', $MODULE)}</button>&nbsp;&nbsp;
                                    <a class='cancelLink' type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
{/strip}