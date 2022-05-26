{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="modal-dialog modelContainer">
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_CHANGE_OWNER', $MODULE)}}
		<div class="modal-content">
			<form class="form-horizontal"  id="massSave" name="MassEdit" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="MassSave" />
				<input type="hidden" name="viewname" value="{$CVID}" />
				<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
				<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
				{$massEditFields = ["assigned_user_id"=>$MASS_EDIT_FIELD_DETAILS.assigned_user_id]}
				<input type="hidden" id="massEditFieldsNameList" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($massEditFields))}' />
				<div name='massEditContent'>
					<div class="modal-body ">
						<div class="form-group">
							{assign var=FIELD_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()->getField('assigned_user_id')}
							<label class="control-label fieldLabel col-sm-5">
								{vtranslate($FIELD_MODEL->get('label'),$MODULE)}
							</label>
							<div class="controls col-sm-6">
								<input type="hidden" name="assigned_user_id_mass_edit_check" value="on"/>
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$FIELD_MODEL}
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}