{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="modal-dialog modal-lg configColumnsContainer">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate('LBL_CONFIG_COLUMNS', $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE|cat:' - '|cat:$CV_MODEL->get('viewname')}
			<form class="form-horizontal configColumnsForm" method="post" action="index.php">
				<input type="hidden" name="module" value="CustomView"/>
				<input type="hidden" name="action" value="SaveAjax"/>
				<input type="hidden" name="mode" value="updateColumns"/>
				<input type="hidden" name="record" value="{$CV_MODEL->getId()}" />
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-6 selectedFieldsContainer">
							<h5>{vtranslate('LBL_SELECTED_FIELDS', $QUALIFIED_MODULE)}</h5>
							<div class="selectedFieldsListContainer">   
								<ul id="selectedFieldsList">
									{foreach item=FIELD_MODEL from=$SELECTED_FIELDS}
										{if $FIELD_MODEL and $FIELD_MODEL->getDisplayType() neq '6'}
											{assign var=FIELD_MODULE_NAME value={$FIELD_MODEL->getModule()->getName()}}
											<li class="item" data-cv-columnname="{$FIELD_MODEL->getCustomViewColumnName()}" data-columnname="{$FIELD_MODEL->get('column')}" data-field-id='{$FIELD_MODEL->getId()}'>
												<span class="dragContainer">
													<img src="{vimage_path('drag.png')}" class="cursorPointerMove" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}">
												</span>
												<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'),$FIELD_MODULE_NAME)}</span>
												<span class="pull-right removeField"><i class="fa fa-times" title="{vtranslate('LBL_REMOVE',$MODULE)}"></i></span>
											</li>   
										{/if}
									{/foreach}
								</ul>
								<li class="item-dummy hide">
									<span class="dragContainer">
										<img src="{vimage_path('drag.png')}" class="cursorPointerMove" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}">
									</span>
									<span class="fieldLabel"></span>
									<span class="pull-right removeField"><i class="fa fa-times"></i></span>
								</li>
							</div>
						</div>
						<div class="col-lg-6 availFiedlsContainer">
							<div class="row">
								<div class="col-lg-10">
									<h5>{vtranslate('LBL_AVAILABLE_FIELDS', $MODULE)}</h5>
									<input type="text" class="inputElement searchAvailFields" placeholder="{vtranslate('LBL_SEARCH_FIELDS', $QUALIFIED_MODULE)}" />
									<div class="panel-group avialFieldsListContainer" id="accordion">
										<div class="panel panel-default" id="avialFieldsList">
											{foreach item=BLOCK_FIELDS key=BLOCK_LABEL from=$RECORD_STRUCTURE name=availFieldsLoop}
												{assign var=RAND_ID value=10|mt_rand:1000}
												<div class="instafilta-section">
													<div id="{$RAND_ID}_accordion" class="availFieldBlock" role="tab">
														<a class="fieldLabel" data-toggle="collapse" data-parent="#accordion" href="#{$RAND_ID}">
															<i class="fa fa-caret-right"></i><span>{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}</span>
														</a>
													</div>
													<div id="{$RAND_ID}" class="panel-collapse collapse">
														<div class="panel-body">
															{foreach item=FIELD_MODEL key=FIELD_NAME from=$BLOCK_FIELDS}
																{assign var=FIELD_MODULE_NAME value={$FIELD_MODEL->getModule()->getName()}}
																{if $FIELD_MODEL->getDisplayType() eq '6'}
																	{continue}
																{/if}
																<div class="instafilta-target item {if array_key_exists($FIELD_MODEL->getCustomViewColumnName(), $SELECTED_FIELDS)}hide{/if}" data-cv-columnname="{$FIELD_MODEL->getCustomViewColumnName()}" data-columnname='{$FIELD_MODEL->get('column')}' data-field-id='{$FIELD_MODEL->getId()}'>
																	<span class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'),$FIELD_MODULE_NAME)}</span>
																</div>
															{/foreach} 
														</div>
													</div>
												</div>
											{/foreach}
											<div class="instafilta-target item-dummy hide">
												<span class="fieldLabel"></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer ">
					<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_UPDATE_LIST')}</strong></button>
					<a href="#" class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
				</div>
			</form>
		</div>
	</div>
{/strip}