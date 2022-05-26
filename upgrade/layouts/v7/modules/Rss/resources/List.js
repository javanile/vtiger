/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Rss_List_Js",{},{ 
    /**
     * Function get the height of the document
     * @return <integer> height
     */
    getDocumentHeight : function() {
        return jQuery(document).height();
    },
    
    registerRssAddButtonClickEvent : function() {
        var thisInstance = this;
        jQuery('.rssAddButton').on('click', function() {
            thisInstance.showRssAddForm();
        })
    },
    
    /**
     * Function show rssAddForm model
     */
    showRssAddForm : function() {
        var thisInstance = this;
        thisInstance.getRssAddFormUi().then(function(data) {
            var resetPasswordUi = jQuery('.rssAddFormContainer').find('#rssAddFormUi');
            if(resetPasswordUi.length > 0){
                resetPasswordUi = resetPasswordUi.clone(true,true);
                var callback = function(data) {
                    var form = data.find('#rssAddForm');
                    var params = {
                        submitHandler: function(form) {
                            var form = jQuery(form);
                            thisInstance.rssFeedSave(form);
                        }
                    };
                    form.vtValidate(params);
                }
                app.helper.showModal(resetPasswordUi, {cb: callback});
            }
        });
    },
    
    /**
     * Function to get the rss add form
     * @param <string> url
     */
    getRssAddFormUi : function(url) {
        var aDeferred = jQuery.Deferred();
        var resetPasswordContainer = jQuery('.rssAddFormContainer');
        var resetPasswordUi = resetPasswordContainer.find('#rssAddFormUi');
        if(resetPasswordUi.length == 0) {
            var actionParams = {
                    'module' : app.getModuleName(),
                    'view' : 'ViewTypes',
                    'mode' : 'getRssAddForm'
            };
            app.request.get({'data' : actionParams}).then(function(error, data){
                    resetPasswordContainer.html(data);
                    aDeferred.resolve(data);
                },
                function(textStatus, errorThrown){
                    aDeferred.reject(textStatus, errorThrown);
                }
            );
        } else {
            aDeferred.resolve();
        }
        return aDeferred.promise();
    },
    
    /**
     * Function to save rss feed
     * @parm form
     */
    rssFeedSave : function(form) {
        var thisInstance = this;
        var data = form.serializeFormData();
        app.helper.showProgress();
        var params = {
        'module': app.getModuleName(),
        'action' : 'Save',
        'feedurl' : data.feedurl
        }
        app.request.post({data : params}).then(function(error, result) {
                app.helper.hideProgress();
                if (result.success) {
                app.helper.hideModal();
                thisInstance.getRssFeeds(result.id).then(function () {
                    thisInstance.loadRssWidget(data, result).then(function () {
                        app.helper.showAlertNotification({message: app.vtranslate(result.message)});
                    });
                });
            } else {
                app.helper.showErrorNotification({message:app.vtranslate(result.message)});
            }
            }
        );
    },
    
    /**
     * Function to register click on the rss sidebar link
     */
    registerRssUrlClickEvent : function() {
        var thisInstance = this;
        jQuery('.quickWidgetContainer').on('click','.rssLink', function(e) {
            var element = jQuery(e.currentTarget);
            var id = element.data('id');
            thisInstance.getRssFeeds(id);
        });
    },
    
    /**
     * Function to get the feeds for specific id
     * @param <integer> id
     */
    getRssFeeds : function(id) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        var container = thisInstance.getListViewContainer();
        app.helper.showProgress();
        var params = {
            'module' : app.getModuleName(),
            'view'   : 'List',
            'id'     : id
        }
        app.request.pjax({'data': params}).then(function (error, data) {
            container.find('#listViewContents').replaceWith(data);
//            thisInstance.setFeedContainerHeight(container);
            app.helper.hideProgress();
            aDeferred.resolve(data);
        });
        
        return aDeferred.promise();  
    }, 
    
    /**
     * Function to get the height of the Feed Container 
     * @param container
     */
    setFeedContainerHeight : function(container) {
        var height = this.getDocumentHeight()/2;
        container.find('.feedListContainer').height(height);
    },
    
    /**
     * Function to register the click of feeds
     * @param container
     */
    registerFeedClickEvent : function(container) {
        var thisInstance = this;
        container.on('click' , '.feedLink', function(e) {  
            var element = jQuery(e.currentTarget);
            var url = element.data('url');
            var frameElement = thisInstance.getFrameElement(url)
            container.find('.feedFrame').html(frameElement);
        });
    },
    
    /**
     * Function to get the iframe element
     * @param <string> url
     * @retrun <element> frameElement
     */
    getFrameElement : function(url) {
        app.helper.showProgress();
        var frameElement = jQuery('<iframe>', {
            id:  'feedFrame',
            scrolling: 'auto',
            width: '100%',
            height: this.getDocumentHeight()/2
        });
        frameElement.addClass('table-bordered');
        this.getHtml(url).then(function(html) {
            app.helper.hideProgress();
            var frame = frameElement[0].contentDocument;
            frame.open();
            frame.write(html);
            frame.close();
        });
        
        return frameElement;
    },
    
    /**
     * Function to get the html contents from url
     * @param <string> url
     * @return <string> html contents
     */
    getHtml : function(url) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'GetHtml',
            'url'    : url
        }
        app.request.get({'data' :params}).then(function(error, data) {
            aDeferred.resolve(data.html);
        });
        
        return aDeferred.promise();  
    },
    
    /**
     * Function to register record delete event 
     */
    registerDeleteRecordClickEvent: function(){
        var container = this.getListViewContainer();
        var thisInstance = this;
        jQuery('#page').on('click', '#deleteButton', function(e){
            var elem = jQuery(e.currentTarget);
            var originalDropDownMenu = elem.closest('.dropdown-menu').data('original-menu');
            var parent = app.helper.getDropDownmenuParent(originalDropDownMenu);
            thisInstance.deleteRecord(parent);
        })
    },
    
    /**
     * Function to delete the record
     */
    deleteRecord : function(container) {
        var thisInstance = this;
        var recordId = container.find('#recordId').val();
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		app.helper.showConfirmationBox({'message' : message}).then(function(e) {
				var module = app.getModuleName();
				var postData = {
					"module": module,
					"action": "DeleteAjax",
					"record": recordId
				}
				var deleteMessage = app.vtranslate('JS_RECORD_GETTING_DELETED');
				app.helper.showProgress();
            app.request.post({'data': postData}).then(function (error, data) {
                app.helper.hideProgress();
                if (!error) {
                    thisInstance.getRssFeeds().then(function () {
                        thisInstance.loadRssWidget(data);
                    });
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_LBL_PERMISSION')});
                }
            },
                    function (error, err) {

                    }
				);
			},
			function(error, err){
			}
		);
    },
    
    /**
     * Function to register make default button click event
     */
    registerMakeDefaultClickEvent : function(container) {
        var thisInstance = this;
        container.on('click','#makeDefaultButton',function() {
            thisInstance.makeDefault(container);
        }); 
    },
    
    /**
     * Function to make a record as default rss feed
     */
    makeDefault : function(container) {
        var listInstance = Vtiger_List_Js.getInstance();
        var recordId = container.find('#recordId').val();
        var module = app.getModuleName();
        var postData = {
            "module": module,
            "action": "MakeDefaultAjax",
            "record": recordId
        }
        app.helper.showProgress();
        app.request.post({'data' : postData}).then(function(error, data){
                app.helper.hideProgress();
                if(!error) {
                    app.helper.showSuccessNotification({'message': 'set as default successfully' });
                } else {
                    var params = {
                        text : app.vtranslate(data.error.message),
                        title : app.vtranslate('JS_LBL_PERMISSION')
                    }
                    Vtiger_Helper_Js.showPnotify(params);
                }
            }
        );
    },
    
    loadRssWidget: function (data, result) {
        var aDeferred = jQuery.Deferred();
        var widgetContainer = jQuery('.widgetContainer');
        var noRssFeeds = widgetContainer.find('li.noRssFeeds');
        var lengthOfFeeds = widgetContainer.find('a').length;
        if (data.deleted === undefined) {
            var widgetHtml = '<li><a href="#" class="rssLink filter-name" data-id="' + result.id + '" data-url="' + data.feedurl + '" title="' + result.title + '">' + result.title + '</a></li>';
            if (lengthOfFeeds) {
                widgetContainer.append(widgetHtml);
            } else if (!lengthOfFeeds) {
                noRssFeeds.remove();
                widgetContainer.append(widgetHtml);
            }
        } else if (data.deleted) {
            widgetContainer.find('a[data-id="' + data.record + '"]').parent().remove();
            var noRssFeedsHtml = '<li  class="noRssFeeds" style="text-align:center">' + app.vtranslate('JS_NO_RECORDS') + '</li>';
            if (lengthOfFeeds === 1) {
                widgetContainer.append(noRssFeedsHtml);
            }
        }
        return aDeferred.promise();
    },    
    registerEvents : function() {
        this._super();
        var container = this.getListViewContainer();
        this.registerRssAddButtonClickEvent();
        this.registerRssUrlClickEvent();
        this.registerFeedClickEvent(container);
        this.registerMakeDefaultClickEvent(container);
        this.setFeedContainerHeight(container);
    }
});