{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="SendEmailFormStep2 modal-dialog modal-lg" name="emailPreview">
		<div class="modal-content">
			<input type="hidden" name="parentRecord" value="{$PARENT_RECORD}"/>
			<input type="hidden" name="recordId" value="{$RECORD_ID}"/>
			<form class="form-horizontal" id="massEmailForm" method="post" action="index.php" enctype="multipart/form-data" name="massEmailForm">
				{assign var=HEADER_TITLE value={vtranslate('SINGLE_Emails', $MODULE)}|cat:" "|cat:{vtranslate('LBL_INFO', $MODULE)}}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="modal-body" id='emailPreviewScroll'>
					<div class="row">
						<div class="col-lg-6">
							<div class="row email-info-row">
								<span class="col-lg-12">
									<span class="col-lg-4">
										<span class="pull-right">{vtranslate('LBL_FROM',$MODULE)}</span>
									</span>
									<span class="col-lg-8">
										<span class="row-fluid">{$FROM}</span>
									</span>
								</span>
							</div>
							<div class="row email-info-row">
								<span class="col-lg-12">
									<span class="col-lg-4">
										<span class="pull-right">{vtranslate('LBL_TO',$MODULE)}</span>
									</span>
									<span class="col-lg-8">
										{if empty($TO)}
											{assign var=TO value=array()}
										{/if}
										{assign var=TO_EMAILS value=","|implode:$TO}
										<span class="row-fluid">
											<span class="col-sm-10 paddingLeft0"><p class="textOverflowEllipsis">{$TO_EMAILS}</p></span>
											{if $TO|@count > 1}
												<span class="col-sm-2">
													<a href="#" data-toggle="dropdown" style="text-transform: lowercase;">{vtranslate('LBL_MORE',$MODULE)}</a>
													<ul class="dropdown-menu" style="padding:3px 6px; max-height:200px;" id="toAddressesDropdown">
														{foreach item=TO_ADDRESS from=$TO}
															<li>{$TO_ADDRESS}</li>
														{/foreach}
													</ul>
												</span>
											{/if}
										</span>
									</span>
								</span>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="email-preview-toolbar pull-right">
								{if $RECORD->getEmailFlag() neq 'SAVED'}
									<button type="button" name="previewReply" class="btn btn-sm btn-default" data-mode="emailReply">
										{vtranslate('LBL_REPLY',$MODULE)}
									</button>
									{if count($TO) > 1 || !empty($CC) || !empty($BCC)}
										<button type="button" name="previewReplyAll" class="btn btn-sm btn-default" data-mode="emailReplyAll">
											{vtranslate('LBL_REPLY_ALL',$MODULE)}
										</button>
									{/if}
								{/if}
								<button type="button" name="previewForward" class="btn btn-sm btn-default" data-mode="emailForward">
									{vtranslate('LBL_FORWARD',$MODULE)}
								</button>
								{if $RECORD->getEmailFlag() eq 'SAVED'}
									<button type="button" name="previewEdit" class="btn btn-sm btn-default" data-mode="emailEdit">
										{vtranslate('LBL_EDIT',$MODULE)}
									</button>
								{/if}
								<button type="button" name="previewPrint" class="btn btn-sm btn-default" data-mode="previewPrint">
									{vtranslate('LBL_PRINT',$MODULE)}
								</button>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-8">
							{if !empty($CC)}
								<div class="row email-info-row">
									<span class="col-lg-12">
										<span class="col-lg-3">
											<span class="pull-right">{vtranslate('LBL_CC',$MODULE)}</span>
										</span>
										<span class="col-lg-9">
											<span class="row-fluid">
												{$CC}
											</span>
										</span>
									</span>
								</div>
							{/if}
							{if !empty($BCC)}
								<div class="row hide email-info-row">
									<span class="col-lg-12">
										<span class="col-lg-3">
											<span class="pull-right">{vtranslate('LBL_BCC',$MODULE)}</span>
										</span>
										<span class="col-lg-9">
											<span class="row-fluid">
												{$BCC}
											</span>
										</span>
									</span>
								</div>
							{/if}

							<div class="row email-info-row">
								<span class="col-lg-12">
									<span class="col-lg-3">
										<span class="pull-right">{vtranslate('LBL_SUBJECT',$MODULE)}</span>
									</span>
									<span class="col-lg-9">
										{$RECORD->get('subject')}
									</span>
								</span>
							</div>

							<div class="row email-info-row">
								<span class="col-lg-12">
									<span class="col-lg-3">
										<span class="pull-right">{vtranslate('LBL_ATTACHMENT',$MODULE)}</span>
									</span>
									<span class="col-lg-9">
										{if count($RECORD->getAttachmentDetails()) le 0}
											{vtranslate('LBL_NO_ATTACHMENTS',$MODULE)}
										{else}
											{foreach item=ATTACHMENT_DETAILS from=$RECORD->getAttachmentDetails()}
												<i class="fa fa-download"></i>&nbsp;
												<a	{if array_key_exists('docid',$ATTACHMENT_DETAILS)} 
														href="index.php?module=Documents&action=DownloadFile&record={$ATTACHMENT_DETAILS['docid']}&fileid={$ATTACHMENT_DETAILS['fileid']}" 
													{else} 
														href="index.php?module=Emails&action=DownloadFile&attachment_id={$ATTACHMENT_DETAILS['fileid']}" 
													{/if}>{$ATTACHMENT_DETAILS['attachment']}</a>&nbsp;&nbsp; 
											{/foreach}
										{/if}
									</span>
								</span>
							</div>
						</div>
					</div>
					<textarea style="display:none;" id="iframeDescription">{$RECORD->get('description')}</textarea>
					<div class="row email-info-row">
						<div class="col-lg-2" style="padding-right:10px;">
							<div class="pull-right">{vtranslate('LBL_DESCRIPTION',$MODULE)}</div>
						</div>
						<div class="col-lg-10">
							<div class="email-body-preview">
								<iframe frameBorder="0" scrolling='yes' id="emailPreviewIframe" style='width: 100%; overflow-y:visible; height:100%;'>
								</iframe>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<span class="col-lg-12" style="text-align: center;">
							<span class="muted">
								{if $RECORD->get('email_flag') eq "SAVED"}
									<small><em>{vtranslate('LBL_DRAFTED_ON',$MODULE)}</em></small>
									<span><small><em>&nbsp;{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('createdtime'))}</em></small></span>
								{else}
									<small><em>{vtranslate('LBL_SENT_ON',$MODULE)}</em></small>
									{assign var="SEND_TIME" value=$RECORD->get('date_start')|@cat:' '|@cat:$RECORD->get('time_start')}
									<span><small><em>&nbsp;{Vtiger_Util_Helper::formatDateTimeIntoDayString($SEND_TIME)}</em></small></span>
								{/if}
							</span>
						</span>
					</div>
					<div class="row">
						<span class="col-lg-12" style="text-align: center;">
							<span><strong> {vtranslate('LBL_OWNER',$MODULE)} : {getOwnerName($RECORD->get('assigned_user_id'))}</strong></span>
						</span>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
