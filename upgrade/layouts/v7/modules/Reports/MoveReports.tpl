{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	<div id="moveReportsContainer" class='modal-dialog'>
		<div class="modal-header">
			<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
			<h4>{vtranslate('LBL_MOVE_REPORT', $MODULE)}</h4>
		</div>
		<div class="modal-content">
			<form class="form-horizontal contentsBackground" id="moveReports" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="MoveReports" />
				<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)} />
				<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)} />
				<input type="hidden" name="viewname" value="{$VIEWNAME}" />
				<input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />
				<div class="modal-body">
					<div class="form-group">
						<label for="inputEmail3" class="col-sm-4 control-label">{vtranslate('LBL_FOLDERS_LIST', $MODULE)}<span class="redColor">*</span></label>
						<div>
							<select class="select2 col-sm-6 " name="folderid">
								<optgroup label="{vtranslate('LBL_FOLDERS', $MODULE)}">
									{foreach item=FOLDER from=$FOLDERS}
										<option value="{$FOLDER->getId()}">{vtranslate($FOLDER->getName(), $MODULE)}</option>
									{/foreach}
								</optgroup>
							</select>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}
