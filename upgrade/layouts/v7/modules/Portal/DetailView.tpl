{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Portal/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="listViewPageDiv">
		<div class="container-fluid">
			<div class="row">
				<div class="col-lg-7">
				</div>
				<div class="col-lg-2" style="padding-top: 14px">
					<div class="pull-right">
						<label>
							{vtranslate('LBL_BOOKMARKS_LIST', $MODULE)}
						</label>
					</div>
				</div>
				<div class="col-lg-3" style="padding-top: 10px">
					<select class="inputElement select2" id="bookmarksDropdown" name="bookmarksList">
						{foreach item=RECORD from=$RECORDS_LIST}
							<option value="{$RECORD['id']}" {if $RECORD['id'] eq $RECORD_ID}selected{/if}>{$RECORD['portalname']}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="row">
				<span class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
					<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
					<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
				</span>
				<br>
				{if substr($URL, 0, 8) neq 'https://'}
					<div id="portalDetailViewHttpError" class="">
						<div class="col-lg-12">{vtranslate('HTTP_ERROR', $MODULE)}</div>
					</div>
				{/if}
				<br>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<iframe src="{if substr($URL, 0, 4) neq 'http'}//{/if}{$URL}" frameborder="1" height="600" scrolling="auto" width="100%" style="border: solid 2px; border-color: #dddddd;"></iframe>
				</div>
			</div>
		</div>
	</div>
{/strip}
