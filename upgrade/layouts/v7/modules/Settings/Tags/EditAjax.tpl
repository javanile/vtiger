{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="modal-dialog modal-content">
        {assign var="HEADER_TITLE" value={vtranslate('LBL_ADD_NEW_TAG', $QUALIFIED_MODULE)}}
		<form id="addTagSettings" method="POST">
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
			<div class="modal-body">
				<div class="row-fluid">
					<div class="form-group">
						<label class="control-label">
							{vtranslate('LBL_CREATE_NEW_TAG',$MODULE)}
						</label>
						<div>
							<input name="createNewTag" value="" data-rule-required = "true" class="form-control" placeholder="{vtranslate('LBL_CREATE_NEW_TAG',$MODULE)}"/>
						</div>
					</div>
					<div class="form-group">
						<div>
							<div class="checkbox">
								<label>
									<input type="hidden" name="visibility" value="{Vtiger_Tag_Model::PRIVATE_TYPE}"/>
									<input type="checkbox" name="visibility" value="{Vtiger_Tag_Model::PUBLIC_TYPE}" />
									&nbsp; {vtranslate('LBL_SHARE_TAGS',$MODULE)}
								</label>
							</div>
							<div class="pull-right"></div>
						</div>
					</div>
					<div class="form-group">
						<div class=" vt-default-callout vt-info-callout tagInfoblock">
							<h5 class="vt-callout-header">
							<span class="fa fa-info-circle"></span>&nbsp; Info </h5>
							<div>{vtranslate('LBL_TAG_SEPARATOR_DESC', $MODULE)}</div><br>
							<div>{vtranslate('LBL_SHARED_TAGS_ACCESS',$QUALIFIED_MODULE)}</div>
						</div>
					</div>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
		</form>
	</div>
{/strip}
