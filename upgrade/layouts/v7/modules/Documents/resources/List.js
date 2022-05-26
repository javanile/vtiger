/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger_List_Js("Documents_List_Js", {
    
    massMove : function(url) {
        var self = new Documents_List_Js();
        self.massMove(url);
    }
    
}, {
    
    registerSearchEvent : function(container) {
        container.find('#searchFolders').on('keydown', function(e) {
            if(e.keyCode === 13) {
                e.preventDefault();
            }
        });
        
        container.find('#searchFolders').on('keyup', function() {
            var searchKey = jQuery(this).val();
            searchKey = searchKey.toLowerCase();
            jQuery('.folder', container).removeClass('selectedFolder');
            container.find('#foldersList').find('.folder').removeClass('hide');
            container.find('#foldersList').find('.folder').filter(function() {
                var currentElement = jQuery(this);
                var folderName = currentElement.find('.foldername').text();
                folderName = folderName.toLowerCase();
                var status = folderName.indexOf(searchKey);
                if(status === -1) return true;
                return false;
            }).addClass('hide');
        });
    },
    
    registerFolderSelectionEvent : function(container) {
        jQuery('.folder', container).on('click', function() {
            jQuery('.folder', container).removeClass('selectedFolder');
            var currentSelection = jQuery(this);
            currentSelection.addClass('selectedFolder');
            var folderId = currentSelection.data('folderId');
            jQuery('input[name="folderid"]').val(folderId);
        });
    },
    
    registerMoveDocumentsEvent : function(container) {
        var self = this;
        container.find('#moveDocuments').on('submit', function(e) {
            e.preventDefault();
            if(container.find('.folder').filter('.selectedFolder').length) {
                var formData = jQuery(e.currentTarget).serializeFormData();
                app.helper.showProgress();
                app.request.post({'data':formData}).then(function(e,res) {
                    app.helper.hideProgress();
                    if(!e) {
                        app.helper.showSuccessNotification({
                            'message' : res.message
                        });
                    } else {
                        app.helper.showErrorNotification({
                            'message' : app.vtranslate('JS_OPERATION_DENIED')
                        });
                    }
                    app.helper.hideModal();
                    self.loadListViewRecords();
                });
            } else {
                app.helper.showAlertNotification({
                    'message' : app.vtranslate('JS_SELECT_A_FOLDER')
                });
            }
        });
    },
    
    registerMoveDocumentsEvents : function(container) {
        this.registerSearchEvent(container);
        this.registerFolderSelectionEvent(container);
        this.registerMoveDocumentsEvent(container);
    },
    
    massMove : function(url) {
        var self = this;
        var listInstance = Vtiger_List_Js.getInstance();
		var validationResult = listInstance.checkListRecordSelected();
		if(!validationResult){
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			var cvId = listInstance.getCurrentCvId();
			var postData = {
				"selected_ids":selectedIds,
				"excluded_ids" : excludedIds,
				"viewname" : cvId
			};

            if(app.getModuleName() === 'Documents'){
                var defaultparams = listInstance.getDefaultParams();
                postData['folder_id'] = defaultparams['folder_id'];
                postData['folder_value'] = defaultparams['folder_value'];
            }
			var params = {
				"url":url,
				"data" : postData
			};
            
            app.helper.showProgress();
            app.request.get(params).then(function(e,res) {
                app.helper.hideProgress();
                if(!e && res) {
                    app.helper.showModal(res, {
                        'cb' : function(modalContainer) {
                            self.registerMoveDocumentsEvents(modalContainer);
                        }
                    });
                }
            });
		} else{
			listInstance.noRecordSelectedAlert();
		}
    },
    
    unMarkAllFilters : function() {
        jQuery('.listViewFilter').removeClass('active');
    },
    
    unMarkAllTags : function() {
        var container = jQuery('#listViewTagContainer');
        container.find('.tag').removeClass('active').find('i.activeToggleIcon').removeClass('fa-circle-o').addClass('fa-circle');
    },
    
    unMarkAllFolders : function() {
        jQuery('.documentFolder').removeClass('active');
        jQuery('.documentFolder').find('i').removeClass('fa-folder-open')
        .addClass('fa-folder');
    },
    
    registerFoldersClickEvent : function() {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.on('click', '.documentFolder',function(e) {
            var targetElement = jQuery(e.target);
            if(targetElement.is('.dropdown-toggle') || targetElement.closest('ul').hasClass('dropdown-menu') ) return;
            var element = jQuery(e.currentTarget);
            var el = jQuery('a[data-filter-id]',element);
            self.resetData();
            self.unMarkAllFilters();
            self.unMarkAllTags();
            self.unMarkAllFolders();
            el.closest('li').addClass('active');
            el.closest('li').find('i').removeClass('fa-folder').addClass('fa-folder-open');
            
            self.loadFilter(jQuery('input[name="allCvId"]').val(), {
                folder_id : 'folderid',
                folder_value : el.data('folderName')
            });
            
			var filtername = jQuery('a[class="filterName"]',element).text();
			jQuery('.module-action-content').find('.filter-name')
            .html('&nbsp;&nbsp;<span class="fa fa-chevron-right" aria-hidden="true"></span> ').text(filtername);
        });
    },
    
    registerFiltersClickEvent : function() {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.on('click', '.listViewFilter', function() {
            self.unMarkAllFolders();
        });
    },
    
    addFolderToList : function(folderDetails) {
        var html = ''+
        '<li style="font-size:12px;" class="documentFolder">'+
            '<a class="filterName" href="javascript:void(0);" data-filter-id="'+folderDetails.folderid+'" data-folder-name="'+folderDetails.folderName+'" title="'+folderDetails.folderDesc+'">'+
                '<i class="fa fa-folder"></i> '+
                '<span class="foldername">'+folderDetails.folderName+'</span>'+
            '</a>'+
            '<div class="dropdown pull-right">'+
                '<span class="fa fa-caret-down dropdown-toggle" data-toggle="dropdown" aria-expanded="true"></span>'+
                '<ul class="dropdown-menu dropdown-menu-right vtDropDown" role="menu">'+
					'<li class="editFolder " data-folder-id="'+folderDetails.folderid+'">'+
						'<a role="menuitem" ><i class="fa fa-pencil-square-o"></i>&nbsp;Edit</a>'+
					'</li>'+
                    '<li class="deleteFolder" data-deletable="1" data-folder-id="'+folderDetails.folderid+'">'+
                        '<a role="menuitem"><i class="fa fa-trash"></i>&nbsp;Delete</a>'+
                    '</li>'+
                '</ul>'+
            '</div>'+
        '</li>';
        jQuery('#folders-list').append(html).find('.documentFolder:last').find('.foldername').text(folderDetails.folderName);
    },
    
    registerAddFolderModalEvents : function(container) {
        var self = this;
        var addFolderForm = jQuery('#addDocumentsFolder');
        addFolderForm.vtValidate({
            submitHandler: function(form) {
                var formData = addFolderForm.serializeFormData();
                app.helper.showProgress();
                app.request.post({'data': formData}).then(function(e, res) {
                    app.helper.hideProgress();
                    if (!e) {
                        app.helper.hideModal();
                        app.helper.showSuccessNotification({
                            'message': res.message
                        });
                        var folderDetails = res.info;
                        self.addFolderToList(folderDetails);
                    }
                    if (e) {
                        app.helper.showErrorNotification({
                            'message': e
                        });
                    }
                });
            }
        });
    },
    
    registerAddFolderEvent : function() {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.find('#createFolder').on('click', function() {
            var params = {
                'module' : app.getModuleName(),
                'view' : 'AddFolder'
            };
            app.helper.showProgress();
            app.request.get({'data':params}).then(function(e,res) {
                app.helper.hideProgress();
                if(!e) {
                    app.helper.showModal(res, {
                        'cb' : function(modalContainer) {
                            self.registerAddFolderModalEvents(modalContainer);
                        }
                    });
                }
            });
        });
    },
    
    registerFoldersSearchEvent : function() {
        var filters = jQuery('#module-filters');
        filters.find('.search-folders').on('keyup', function(e) {
            var element = jQuery(e.currentTarget);
            var val = element.val().toLowerCase();
            jQuery('li.documentFolder', filters).each(function(){
                var filterEle = jQuery(this);
                var folderName = filterEle.find('.foldername').text();
                folderName = folderName.toLowerCase();
                if(folderName.indexOf(val) === -1){
                    filterEle.addClass('hide');    
                } else {
                    filterEle.removeClass('hide');
                }
            });
			
			if(jQuery('li.documentFolder', filters).not('.hide').length > 0) {
				jQuery('#folders-list', filters).find('.noFolderText').hide();
			}else {
				jQuery('#folders-list', filters).find('.noFolderText').show();
			}
        });
    },
    
    registerDeleteFolderEvent : function() {
        var filters = jQuery('#module-filters');
        filters.on('click','li.deleteFolder',function(e) {
            var element = jQuery(e.currentTarget);
            
            var deletable = element.data('deletable');
            if(deletable == '1') {
                app.helper.showConfirmationBox({
                    'message' : app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE')
                }).then(function() {
                    var folderId = element.data('folderId');
                    var params = {
                        module : app.getModuleName(),
                        mode  : 'delete',
                        action : 'Folder',
                        folderid : folderId
                    };
                    app.helper.showProgress();
                    app.request.post({'data' : params}).then(function(e,res) {
                        app.helper.hideProgress();
                        if(!e) {
                            filters.find('.documentFolder').filter(function() {
                                var currentTarget = jQuery(this);
                                if(currentTarget.find('a.filterName').data('filterId') == folderId) {
                                    return true;
                                }
                                return false;
                            }).remove();
                            app.helper.showSuccessNotification({
                                'message' : res.message
                            });
                        }
                    });
                });
            } else {
                app.helper.showAlertNotification({
                    'message' : app.vtranslate('JS_FOLDER_IS_NOT_EMPTY')
                });
            }
        });
    },
    
    updateFolderInList : function(folderDetails) {
        jQuery('#folders-list').find('a.filterName[data-filter-id="'+folderDetails.folderid+'"]')
                .attr('title', folderDetails.folderDesc)
                .find('.foldername').text(folderDetails.folderName);
    },
    
    registerEditFolderModalEvents : function(container) {
        var self = this;
        container.find('#addDocumentsFolder').on('submit', function(e) {
            e.preventDefault();
            var formData = jQuery(this).serializeFormData();
            app.helper.showProgress();
            app.request.post({'data':formData}).then(function(e,res) {
                app.helper.hideProgress();
                if(!e) {
                    app.helper.hideModal();
                    app.helper.showSuccessNotification({
                        'message' : res.message
                    });
                    var folderDetails = res.info;
                    self.updateFolderInList(folderDetails);
                } else {
                    app.helper.showAlertNotification({
                        'message' : e
                    });
                }
            });
        });
    },
    
    registerFolderEditEvent : function() {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.on('click','li.editFolder',function(e) {
            var element = jQuery(e.currentTarget);
            var folderId = element.data('folderId');
            var params = {
                'module' : app.getModuleName(),
                'view' : 'AddFolder',
                'folderid' : folderId,
                'mode' : 'edit'
            };
            app.helper.showProgress();
            app.request.get({'data':params}).then(function(e,res) {
                app.helper.hideProgress();
                if(!e) {
                    app.helper.showModal(res, {
                        'cb' : function(modalContainer) {
                            self.registerEditFolderModalEvents(modalContainer);
                        }
                    });
                }
            });
        });
    },
    
    registerRowDoubleClickEvent: function () {
        return true;
    },

	getDefaultParams: function() {
		var search_value = jQuery('.sidebar-menu').find('.documentFolder.active').find('.filterName').data('folder-name');
		var customParams = {
			'folder_id' : 'folderid',
			'folder_value' : search_value
		};
		var params = this._super();
		if(search_value){
			jQuery.extend(params,customParams);
		}
		return params;
	},
    
    registerEvents: function() {
        this._super();
        
        this.registerFoldersClickEvent();
        this.registerAddFolderEvent();
        this.registerFoldersSearchEvent();
        this.registerFolderEditEvent();
        this.registerDeleteFolderEvent();
        this.registerFiltersClickEvent();
		
		//To make folder non-deletable if a document is uploaded
		app.event.on('post.documents.save', function(event, data){
			var folderid = data.folderid;
			var folder = jQuery('#folders-list').find('[data-folder-id="'+folderid+'"]').filter('.deleteFolder');
			if(folder.length) {
				folder.attr('data-deletable', '0');
			}
		})
    }
});