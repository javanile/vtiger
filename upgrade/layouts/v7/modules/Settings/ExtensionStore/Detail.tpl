{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="col-lg-12 col-sm-12 content-area detailViewInfo extensionDetails extensionWidgetContainer" style='margin-top:0px;'>
		{if !($ERROR)}
			<input type="hidden" name="mode" value="{$smarty.request.mode}" />
			<input type="hidden" name="extensionId" value="{$EXTENSION_ID}" />
			<input type="hidden" name="targetModule" value="{$EXTENSION_DETAIL->get('name')}" />
			<input type="hidden" name="moduleAction" value="{$MODULE_ACTION}" />
			<div class="row contentHeader extension_header" style="margin-bottom: 10px;">
				<div class="col-sm-6 col-xs-6" style="margin-bottom: 5px;">
					<div style="margin-bottom: 5px;">
						<span class="font-x-x-large">{$EXTENSION_DETAIL->get('name')}</span>&nbsp;
						<span class="muted">{vtranslate('LBL_BY', $QUALIFIED_MODULE)}&nbsp;{$AUTHOR_INFO['firstname']}&nbsp;{$AUTHOR_INFO['lastname']}</span>
					</div>
					{assign var=ON_RATINGS value=$EXTENSION_DETAIL->get('avgrating')}
					<div>
						<span data-score="{$ON_RATINGS}" class="rating " data-readonly="true"></span>
						<span class="">{if $ON_RATINGS}({$ON_RATINGS} {vtranslate('LBL_RATINGS', $QUALIFIED_MODULE)}){/if}</span>
					</div>
				</div>
				<div class="col-sm-6 col-xs-6">
					<div class="pull-right extensionDetailActions">
						<span style="margin: 5px;">
							<a class="btn btn-default" id="declineExtension"><i class="fa fa-chevron-left"></i> {vtranslate('LBL_BACK', $MODULE)}</a>&nbsp;
						</span>
						<span style="margin: 5px">
							{if ($MODULE_ACTION eq 'Installed')}
								<button class="btn btn-danger {if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)}authenticated {else} loginRequired{/if}" type="button" style="margin-right: 6px;" id="uninstallModule"><strong>{vtranslate('LBL_UNINSTALL', $QUALIFIED_MODULE)}</strong></button>
							{else}
								{if $EXTENSION_DETAIL->get('isprotected') && $IS_PRO && ($EXTENSION_DETAIL->get('price') gt 0)}
									<button class="btn btn-info {if (!$CUSTOMER_PROFILE['CustomerCardId'])} setUpCard{/if}{if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)} authenticated {else} loginRequired{/if}" type="button" id="installExtension"><strong>{vtranslate('LBL_BUY',$QUALIFIED_MODULE)}${$EXTENSION_DETAIL->get('price')}</strong></button>
								{elseif (!$EXTENSION_DETAIL->get('isprotected')) && ($EXTENSION_DETAIL->get('price') gt 0)}
									<button class="btn btn-info {if (!$CUSTOMER_PROFILE['CustomerCardId'])} setUpCard{/if}{if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)} authenticated {else} loginRequired{/if}" type="button" id="installExtension"><strong>{vtranslate('LBL_BUY',$QUALIFIED_MODULE)}${$EXTENSION_DETAIL->get('price')}</strong></button>
								{elseif !$EXTENSION_DETAIL->get('isprotected') && (($EXTENSION_DETAIL->get('price') eq 0) || ($EXTENSION_DETAIL->get('price') eq 'Free'))}
									<button class="btn btn-success {if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)}authenticated {else} loginRequired{/if}" type="button" id="installExtension"><strong>{vtranslate($MODULE_ACTION, $QUALIFIED_MODULE)}</strong></button>
								{elseif $EXTENSION_DETAIL->get('isprotected') && $IS_PRO && (($EXTENSION_DETAIL->get('price') eq 0) || ($EXTENSION_DETAIL->get('price') eq 'Free'))}
									<button class="btn btn-success {if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)}authenticated {else} loginRequired{/if}" type="button" id="installExtension"><strong>{vtranslate($MODULE_ACTION, $QUALIFIED_MODULE)}</strong></button>
								{/if}
							{/if}
						</span>
						<span style="margin: 5px;">
							{if $MODULE_ACTION eq 'Installed'}
								{assign var=LAUNCH_URL value=$EXTENSION_MODULE_MODEL->getExtensionLaunchUrl()}
							{/if}
							<button class="btn btn-info {if $MODULE_ACTION eq 'Installed'}{if $EXTENSION_MODULE_MODEL->get('extnType') eq 'language'}hide{/if}{else}hide{/if}" type="button" id="launchExtension" onclick="location.href='{$LAUNCH_URL}'"><strong>{vtranslate('LBL_LAUNCH', $QUALIFIED_MODULE)}</strong></button>
						</span>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			<div class="tabbable-panel">
				<div class="tabbable-line margin0px" style="padding-bottom: 20px;">
					<ul id="extensionTab" class="nav nav-tabs" style="margin-bottom: 0px; padding-bottom: 0px;text-align: left;">
						<li class="active"><a href="#description" data-toggle="tab"><strong>{vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}</strong></a></li>
						<li class="divider-vertical"></li>
						<li><a href="#CustomerReviews" data-toggle="tab"><strong>{vtranslate('LBL_CUSTOMER_REVIEWS', $QUALIFIED_MODULE)}</strong></a></li>
						<li class="divider-vertical"></li>
						<li><a href="#Author" data-toggle="tab"><strong>{vtranslate('LBL_PUBLISHER', $QUALIFIED_MODULE)}</strong></a></li>
					</ul>
					<div class="tab-content boxSizingBorderBox" style="background-color: #fff; padding: 20px; margin-top: 10px;">
						<div class="tab-pane active" id="description">
							<div style="width:90%;padding: 0px 5%;">
								<div class="row">
									<div class="col-sm-2 col-xs-2">&nbsp;</div>
									<div class="col-sm-8 col-xs-8">
										<div id="imageSlider" class="carousel slide" data-ride="carousel">
											<!-- Indicators -->
											<ol class="carousel-indicators">
												{foreach $SCREEN_SHOTS as $key=>$SCREEN_SHOT name=screen}
													<li data-target="#imageSlider" data-slide-to="{$smarty.foreach.screen.index}" {if $smarty.foreach.screen.index == 0}class="active" {/if}></li>
												{/foreach}
											</ol>

											<!-- Wrapper for slides -->
											<div class="carousel-inner" role="listbox">
												{foreach $SCREEN_SHOTS as $key=>$SCREEN_SHOT name=screen}
													<div class="item {if $smarty.foreach.screen.index == 0} active {/if}">
														<img src="{$SCREEN_SHOT->get('screenShotURL')}" >
													</div>
												{/foreach}
											</div>

											<!-- Controls -->
											<a class="left carousel-control" href="#imageSlider" role="button" data-slide="prev">
												<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
												<span class="sr-only"></span>
											</a>
											<a class="right carousel-control" href="#imageSlider" role="button" data-slide="next">
												<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
												<span class="sr-only"></span>
											</a>
										</div>
									</div>
									<div class="col-sm-2 col-xs-2">&nbsp;</div>
								</div>
							</div>
							<br>
							<div class="scrollableTab" style="text-align: left;">
								<p>{$EXTENSION_DETAIL->get('description')}</p>
								<p></p>
							</div>
						</div>
						<div class="tab-pane" id="CustomerReviews">
							<div class="row boxSizingBorderBox" style="padding-bottom: 15px;">
								<div class="col-sm-6 col-xs-6">
									<div class="pull-left">
										<div style="font-size: 55px; line-height:50px; margin-right: 20px;">{$ON_RATINGS}</div>
									</div>
									<div class="pull-left">
										<span data-score="{$ON_RATINGS}" class="rating" data-readonly="true"></span>
										<div>out of 5</div>
										<div>({count($CUSTOMER_REVIEWS)} Reviews)</div>
									</div>
								</div>
								{if ($REGISTRATION_STATUS) and ($PASSWORD_STATUS)}
									<div class="col-sm-6 col-xs-6">
										<div class="pull-right">
											<button type="button" class="writeReview margin0px pull-right {if $MODULE_ACTION neq 'Installed'} hide{/if}">{vtranslate('LBL_WRITE_A_REVIEW', $QUALIFIED_MODULE)}</button>
										</div>
									</div>
								{/if}
							</div><hr>
							<div class="scrollableTab">
								<div class="customerReviewContainer" style="">
									{foreach $CUSTOMER_REVIEWS as $key=>$CUSTOMER_REVIEW}
										<div class="row" style="margin: 8px 0 15px;">
											<div class="col-sm-3 col-xs-3">
												{assign var=ON_RATINGS value=$CUSTOMER_REVIEW['rating']}
												<div data-score="{$ON_RATINGS}" class="rating" data-readonly="true"></div>
												{assign var=CUSTOMER_INFO value= $CUSTOMER_REVIEW['customer']}
												<div>
													{assign var=REVIEW_CREATED_TIME value=$CUSTOMER_REVIEW['createdon']|replace:'T':' '}
													{$CUSTOMER_INFO['firstname']}&nbsp;{$CUSTOMER_INFO['lastname']}
												</div>
												<div class="muted">{Vtiger_Util_Helper::formatDateTimeIntoDayString($REVIEW_CREATED_TIME)|substr:4}</div>
											</div>
											<div class="col-sm-9 col-xs-9">{$CUSTOMER_REVIEW['comment']}</div>
										</div>
										<hr>
									{/foreach}
								</div>
							</div>
						</div>
						<div class="tab-pane" id="Author">
							<div class="scrollableTab">
								<div class="row extension_header">
									<div class="col-sm-6 col-xs-6">
										{if !empty($AUTHOR_INFO['company'])}
											<div class="font-x-x-large authorInfo">{$AUTHOR_INFO['company']}</div>
										{else}
											<div class="font-x-x-large authorInfo">{$AUTHOR_INFO['firstname']}&nbsp;{$AUTHOR_INFO['lastname']}</div>
										{/if}
											<div class="authorInfo">{$AUTHOR_INFO['phone']}</div>
											<div class="authorInfo">{$AUTHOR_INFO['email']}</div>
											<div class="authorInfo"><a href="{$AUTHOR_INFO['website']}" target="_blank">{$AUTHOR_INFO['website']}</a></div>
									</div>
									<div class="col-sm-6 col-xs-6"> &nbsp; </div>
								 </div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-dialog customerReviewModal hide">
				<div class="modal-content">
					<div class="modal-header contentsBackground">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h3>{vtranslate('LBL_CUSTOMER_REVIEW', $QUALIFIED_MODULE)}</h3>
					</div>
					<form class="form-horizontal customerReviewForm">
						<input type="hidden" name="extensionId" value="{$EXTENSION_ID}" />
						<div class="modal-body">
							<div class="form-group">
								<span class="control-label col-sm-2 col-xs-2">
									{vtranslate('LBL_REVIEW', $QUALIFIED_MODULE)}
								</span>
								<div class="controls col-sm-4 col-xs-4">
									<textarea class="form-control" name="customerReview" data-rule-required="true"></textarea>
								</div>
							</div>
							<div class="form-group">
								<span class="control-label col-sm-2 col-xs-2">
									{vtranslate('LBL_RATE_IT', $QUALIFIED_MODULE)}
								</span>
								<div class="controls col-sm-4 col-xs-4">
									<div class="rating"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<div class="row">
								<div class="col-sm-12 col-xs-12">
									<div class="pull-right">
										<div class="pull-right cancelLinkContainer" style="margin-top:0px;">
											<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
										</div>
										<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
									</div>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		{else}
			<div class="row">
				<div class="col-sm-12 col-xs-12">
					{$ERROR_MESSAGE}
				</div>
			</div>
		{/if}
	</div>

    {include file="CardSetupModals.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
    
{/strip}
