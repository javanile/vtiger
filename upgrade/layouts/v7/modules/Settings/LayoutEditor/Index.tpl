{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/LayoutEditor/views/Index.php *}

{strip}
	<div class="container-fluid main-scroll" id="layoutEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<input class="selectedTab" type="hidden" value="{$SELECTED_TAB}">
		<input class="selectedMode" type="hidden" value="{$MODE}">
		<input type="hidden" id="selectedModuleLabel" value="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}" />
		<div class="widget_header row">
			<label class="col-sm-2 textAlignCenter" style="padding-top: 7px;">
				{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)}
			</label>
			<div class="col-sm-6">
				<select class="select2 col-sm-6" name="layoutEditorModules">
					<option value=''>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
					{foreach item=MODULE_NAME key=TRANSLATED_MODULE_NAME from=$SUPPORTED_MODULES}
						<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>
							{$TRANSLATED_MODULE_NAME}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
		<br>
		<br>
		{if $SELECTED_MODULE_NAME}
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs marginBottom10px">
					{assign var=URL value="index.php?module=LayoutEditor&parent=Settings&view=Index"}
					<li class="{if $SELECTED_TAB eq 'detailViewTab'}active {/if}detailViewTab"><a data-toggle="tab" href="#detailViewLayout" data-url="{$URL}" data-mode="showFieldLayout"><strong>{vtranslate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong></a></li>
					<li class="{if $SELECTED_TAB eq 'relatedListTab'}active {/if}relatedListTab"><a data-toggle="tab" href="#relatedTabOrder" data-url="{$URL}" data-mode="showRelatedListLayout"><strong>{vtranslate('LBL_RELATION_SHIPS', $QUALIFIED_MODULE)}</strong></a></li>
					<li class="{if $SELECTED_TAB eq 'duplicationTab'}active {/if}duplicationTab"><a data-toggle="tab" href="#duplicationContainer" data-url="{$URL}" data-mode="showDuplicationHandling"><strong>{vtranslate('LBL_DUPLICATE_HANDLING', $QUALIFIED_MODULE)}</strong></a></li>
				</ul>
				<div class="tab-content layoutContent themeTableColor overflowVisible">
					<div class="tab-pane{if $SELECTED_TAB eq 'detailViewTab'} active{/if}" id="detailViewLayout">
						{if $SELECTED_TAB eq 'detailViewTab'}
							{include file=vtemplate_path('FieldsList.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
					<div class="tab-pane {if $SELECTED_TAB eq 'relatedListTab'} active{/if}" id="relatedTabOrder">
						{if $SELECTED_TAB eq 'relatedListTab'}
							{include file=vtemplate_path('RelatedList.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
					<div class="tab-pane{if $SELECTED_TAB eq 'duplicationTab'} active{/if}" id="duplicationContainer">
						{if $SELECTED_TAB eq 'duplicationTab'}
							{include file=vtemplate_path('DuplicateHandling.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}