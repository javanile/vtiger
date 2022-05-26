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

{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

<div class="commentDiv cursorPointer">
	<div class="singleComment">
		<input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
		<div class="commentInfoHeader" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
			{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
			{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
			<div class="col-lg-12">
				<div class="media" {if $COMMENT->get('is_private')}style="background: #fff9ea;"{/if}>
					<div class="media-left title" id="{$COMMENT->getId()}">
						{assign var=CREATOR_NAME value=$COMMENT->getCommentedByName()}
						<div class="col-lg-2 recordImage commentInfoHeader" style ="width:50px; height:50px; font-size: 30px;" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto = "{$COMMENT->get('related_to')}">
							{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
							{if !empty($IMAGE_PATH)}
								<img src="{$IMAGE_PATH}" width="100%" height="100%" align="left">
							{else}
								<div class="name" style="font-size: 30px;"><span><strong> {$CREATOR_NAME|mb_substr:0:2|escape:"html"} </strong></span></div>
							{/if}
						</div>
					</div>
					<div class="media-body">
						<div class="comment" style="line-height:1;">
							<span class="creatorName" >
								{$CREATOR_NAME}
							</span>&nbsp; 
							<span class="commentTime text-muted cursorDefault">
								<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small>
							</span>
							<div class="">
								<span class="commentInfoContent">
									{nl2br($COMMENT->get('commentcontent'))}
								</span>
							</div>
							<br>
							<div class="commentActionsContainer">
								<span class="commentActions">
									{if $CHILDS_ROOT_PARENT_MODEL}
										{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
									{/if}
									{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
									{if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
										<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a href="javascript:void(0)" class="cursorPointer viewThread">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
											</a>
										</span>
										<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
											<a href="javascript:void(0)" class="cursorPointer hideThread">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
											</a>
										</span>
									{elseif $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
										<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
											<a href="javascript:void(0)" class="cursorPointer viewThread">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
											</a>
										</span>
										<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a href="javascript:void(0)" class="cursorPointer hideThread">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
												<img class="alignMiddle" src="{vimage_path('arrowdown.png')}" />
											</a>
										</span>
									{/if}
									<span class="commemntActionsubblock" >
										{if $CHILDS_ROOT_PARENT_MODEL}
											{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
										{/if}
										{if $IS_CREATABLE}
											{if $CHILD_COMMENTS_COUNT}<span>&nbsp;|&nbsp;</span>{/if}
											<a href="javascript:void(0);" class="cursorPointer replyComment feedback" style="color: blue;">
												{vtranslate('LBL_REPLY',$MODULE_NAME)}
											</a>
										{/if}
										{if $CURRENTUSER->getId() eq $COMMENT->get('userid') && $IS_EDITABLE}
											{if $IS_CREATABLE}&nbsp;&nbsp;&nbsp;{/if}
											<a href="javascript:void(0);" class="cursorPointer editComment feedback" style="color: blue;">
												{vtranslate('LBL_EDIT',$MODULE_NAME)}
											</a>
										{/if}
									</span>
								</span>
							</div>
						</div>
					</div>
					<hr>
				</div>
			</div>
		</div>
	</div>
</div>
