{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="sidebar-menu sidebar-menu-full">
		<div class="module-filters" id="module-filters">
			<div class="sidebar-container lists-menu-container">
				<div class="sidebar-header clearfix">
					<h5 class="pull-left">{vtranslate('LBL_FOLDERS', $MODULE)}</h5>
					<button id="createFilter" onclick='Reports_List_Js.triggerAddFolder("index.php?module=Reports&view=EditFolder");' class="btn btn-default pull-right sidebar-btn" title="{vtranslate('LBL_ADD_NEW_FOLDER', $MODULE)}">
						<div class="fa fa-plus" aria-hidden="true"></div>
					</button> 
				</div>
				<hr>
				<div>
					<input class="search-list" type="text" placeholder="{vtranslate('LBL_SEARCH_FOR_FOLDERS',$MODULE)}">
				</div>
				<div class="menu-scroller mCustomScrollBox" data-mcs-theme="dark">
					<div class="mCustomScrollBox mCS-light-2 mCSB_inside" tabindex="0">
						<div class="mCSB_container" style="position:relative; top:0; left:0;">
							<div class="list-menu-content">
								<div class="list-group">
									<ul class="lists-menu">
										<li style="font-size:12px;" class="listViewFilter" >
											<a href="#" class='filterName' data-filter-id="All"><i class="fa fa-folder foldericon"></i>&nbsp;{vtranslate('LBL_ALL_REPORTS', $MODULE)}</a>
										</li>
										{foreach item=FOLDER from=$FOLDERS name="folderview"}
											<li style="font-size:12px;" class="listViewFilter {if $smarty.foreach.folderview.iteration gt 5} filterHidden hide{/if}" >
												{assign var=VIEWNAME value={vtranslate($FOLDER->getName(),$MODULE)}}
												<a href="#" class='filterName' data-filter-id={$FOLDER->getId()}><i class="fa fa-folder foldericon"></i>&nbsp;{if {$VIEWNAME|strlen > 50} }{$VIEWNAME|substr:0:45}..{else}{$VIEWNAME}{/if}</a> 
												<div class="pull-right">
													{assign var="FOLDERID" value=$FOLDER->get('folderid')}
													<span class="js-popover-container">
														<span class="fa fa-angle-down" data-id="{$FOLDERID}" data-deletable="true" data-editable="true" rel="popover" data-toggle="popover" data-deleteurl="{$FOLDER->getDeleteUrl()}" data-editurl="{$FOLDER->getEditUrl()}" data-toggle="dropdown" aria-expanded="true"></span>
													</span>
												</div>
											</li>
										{/foreach}
										<li style="font-size:12px;" class="listViewFilter" >
											<a href="#" class='filterName' data-filter-id="shared"><i class="fa fa-folder foldericon"></i>&nbsp;{vtranslate('LBL_SHARED_REPORTS', $MODULE)}</a>
										</li>
									</ul>

									<div id="filterActionPopoverHtml">
										<ul class="listmenu hide" role="menu">
											<li role="presentation" class="editFilter">
												<a role="menuitem"><i class="fa fa-pencil-square-o"></i>&nbsp;{vtranslate('LBL_EDIT',$MODULE)}</a>
											</li>
											<li role="presentation" class="deleteFilter">
												<a role="menuitem"><i class="fa fa-trash"></i>&nbsp;{vtranslate('LBL_DELETE',$MODULE)}</a>
											</li>
										</ul>
									</div>
									<h5 class="toggleFilterSize" data-more-text="{vtranslate('LBL_MORE',$MODULE)}.." data-less-text="{vtranslate('LBL_LESS',$MODULE)}.."> 
										{if $smarty.foreach.folderview.iteration gt 5}
											{vtranslate('LBL_MORE',$MODULE)}..
										{/if}
									</h5>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}