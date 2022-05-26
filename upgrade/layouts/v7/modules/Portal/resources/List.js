/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_List_Js("Portal_List_Js", {
    getDefaultParams: function () {
        var params = {
            'module': app.getModuleName(),
            'view': 'List',
            'page': jQuery('#pageNumber').val(),
            'orderby': jQuery('[name="orderBy"]').val(),
            'sortorder': jQuery('[name="sortOrder"]').val(),
            'search_value': jQuery('#alphabetValue').val()
        }
        return params;
    },
    editBookmark: function (params) {
        app.request.get({data: params}).then(function (err, data) {
            var callBackFunction = function (data) {
                Portal_List_Js.saveBookmark();
            };
            app.helper.showModal(data, params);
            if (typeof callBackFunction == 'function') {
                callBackFunction(data);
            }
        });
    },
    saveBookmark: function () {
        var form = jQuery('#saveBookmark');
        jQuery('#saveBookmark').on('submit', function (e) {
            e.preventDefault();
            form.find('[type="submit"]').attr('disabled', true);
            //Added to avoid multiple submit
        });
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                var params = form.serializeFormData();
                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        var url = Portal_List_Js.getDefaultParams();
                        Portal_List_Js.loadListViewContent(url);
                    }
                });
            }
        };
        form.vtValidate(params);

    },
    massDeleteRecords: function () {
        var listInstance = app.controller();
        var deleteURL = 'index.php?module=' + app.getModuleName() + '&action=MassDelete';
        listInstance.performMassDeleteRecords(deleteURL);
    },
    loadListViewContent: function (url) {
        var thisInstance = Portal_List_Js.getInstance();
        thisInstance.loadListViewRecords(url);
    },
    updatePagination: function () {
        var previousPageExist = jQuery('#previousPageExist').val();
        var nextPageExist = jQuery('#nextPageExist').val();
        var previousPageButton = jQuery('#PreviousPageButton');
        var nextPageButton = jQuery('#nextPageButton');
        var listViewEntriesCount = parseInt(jQuery('#noOfEntries').val());
        var pageStartRange = parseInt(jQuery('#pageStartRange').val());
        var pageEndRange = parseInt(jQuery('#pageEndRange').val());
        var pages = jQuery('#totalPageCount').text();
        var totalNumberOfRecords = jQuery('.totalNumberOfRecords');
        var pageNumbersTextElem = jQuery('.pageNumbersText');
        var currentPage = parseInt(jQuery('#pageNumber').val());

        jQuery('#pageToJump').val(currentPage);
        if (previousPageExist != "") {
            previousPageButton.removeAttr('disabled');
        } else if (previousPageExist == "") {
            previousPageButton.attr("disabled", "disabled");
        }
        if ((nextPageExist != "") && (pages > 1)) {
            nextPageButton.removeAttr('disabled');
        } else if ((nextPageExist == "") || (pages == 1)) {
            nextPageButton.attr("disabled", "disabled");
        }
        if (listViewEntriesCount != 0) {
            var pageNumberText = pageStartRange + " " + app.vtranslate('to') + " " + pageEndRange;
            pageNumbersTextElem.html(pageNumberText);
            totalNumberOfRecords.removeClass('hide');
        } else {
            pageNumbersTextElem.html("<span>&nbsp;</span>");
            if (!totalNumberOfRecords.hasClass('hide')) {
                totalNumberOfRecords.addClass('hide');
            }
        }
    }
}, {
    registerAddBookmark: function () {
        jQuery('.addBookmark').on('click', function () {
            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'EditAjax'
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    registerEditBookmark: function () {
        var container = this.getListViewContainer();
        jQuery('body').on('click', '.editPortalRecord', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('ul').data('id');
            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'EditAjax',
                'record': id
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    registerDeleteBookmark: function () {
        jQuery('body').on('click','.deleteRecord', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('ul').data('id');
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            app.helper.showConfirmationBox({'message': message}).then(function (e) {
                var params = {
                    'module': app.getModuleName(),
                    'parent': app.getParentModuleName(),
                    'action': 'DeleteAjax',
                    'record': id
                };
                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        var url = Portal_List_Js.getDefaultParams();
                        Portal_List_Js.loadListViewContent(url);
                    }
                });
            });
        });
    },
    registerListViewSort: function () {
        var container = this.getListViewContainer();
        container.on('click', '.listViewContentHeaderValues', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var orderBy = currentTarget.data('columnname');
            var sortOrder = currentTarget.data('nextsortorderval');
            if (sortOrder === 'ASC') {
                jQuery('i', e.currentTarget).addClass('fa-sort-asc');
            } else {
                jQuery('i', e.currentTarget).addClass('fa-sort-desc');
            }
            var url = Portal_List_Js.getDefaultParams();
            container.find('[name="sortOrder"]').val(sortOrder);
            container.find('[name="orderBy"]').val(orderBy);
            url['orderby'] = orderBy;
            url['sortorder'] = sortOrder;
            Portal_List_Js.loadListViewContent(url);
        });
    },
    
    registerRowClickEvent: function () {
        var container = this.getListViewContainer();
        container.on('click', '.listViewEntries', function (e) {
            var selection = window.getSelection().toString();
            if (selection.length == 0) {
                if (jQuery(e.target, jQuery(e.currentTarget)).is(':first-child'))
                    return;
                if (jQuery(e.target).is('input[type="checkbox"]'))
                    return;
                var elem = jQuery(e.currentTarget);
                var recordUrl = elem.data('recordurl');
                if (typeof recordUrl == 'undefined') {
                    return;
                }
                window.location.href = recordUrl;
            }
        });
    },
    registerRemoveSortingPortal: function () {
        var container = this.getListViewContainer();
        container.on('click', '.removeSortingPortal', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var params = {
                'module': app.getModuleName(),
                'view': 'List',
                'page': jQuery('#pageNumber').val(),
                'mode': 'removeSorting'
            }
            Portal_List_Js.loadListViewContent(params);
        });
    },
    loadListViewRecords: function (url) {
        var aDeferred = jQuery.Deferred();
        var defaultUrl = Portal_List_Js.getDefaultParams();
        if (!jQuery.isEmptyObject(url)) {
            jQuery.extend(defaultUrl, url);
        }
        app.helper.showProgress();
        app.request.pjax({data: defaultUrl}).then(function (error, data) {
            app.helper.hideProgress();
            if (error === null) {
                aDeferred.resolve(data);
                app.helper.hideModal();
                jQuery('#listViewContent').html(data);
                app.event.trigger('post.listViewFilter.click');
            }
            Portal_List_Js.updatePagination();
        });
        return aDeferred.promise();
    },
    getRecordsCount: function () {
        var aDeferred = jQuery.Deferred();
        var module = this.getModuleName();
        var defaultParams = this.getDefaultParams();

        var postData = {
            "module": module,
            "view": "ListAjax",
            "mode": "getRecordCount"
        };
        postData = jQuery.extend(defaultParams, postData);
        var params = {};
        params.data = postData;
        app.request.get(params).then(
                function (err, response) {
                    aDeferred.resolve(response);
                }
        );
        return aDeferred.promise();
    },
    enableListViewActions : function(){
        jQuery('.listViewActionsContainer').find('button').removeAttr('disabled');
        jQuery('.listViewActionsContainer').find('li').removeClass('hide');
    },
    
    disableListViewActions : function(){
        jQuery('.listViewActionsContainer').find('.dropdown-toggle').removeAttr("disabled");
        jQuery('.listViewActionsContainer').find('li.selectFreeRecords').removeClass('hide');
    },
    registerEvents: function () {
        this._super();
        this.registerAddBookmark();
        this.registerEditBookmark();
        this.registerDeleteBookmark();
    }
});
