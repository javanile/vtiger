/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger.Class("Vtiger_Tag_Js",{},{
    
    editTagTemplate : '<div class="popover" role="tooltip"><div class="arrow"></div>\n\
                                <form onsubmit="return false;">\n\
                                    <div class="popover-content"></div>\n\
                                </form>\n\
                                </div>',
    editTagContainerCached : false,
    
    init : function() {
        this.editTagContainerCached = jQuery('.editTagContainer');
    },
    
    saveTag : function(callerParams) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'TagCloud',
            'mode'   : 'saveTags'
            
        };
        var params = jQuery.extend(params, callerParams);
        app.helper.showProgress();
        app.request.post({'data': params}).then(
            function(error, data) {
                app.helper.hideProgress();
                if(error == null) {
                    aDeferred.resolve(data);
                }else{
                    aDeferred.reject(error);
                }
            }
        );
        return aDeferred.promise();
    },
    
    updateTag : function(callerParams) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module' : app.getModuleName(),
            'action' : 'TagCloud',
            'mode'   : 'update'
        }
        params = jQuery.extend(params, callerParams);
        app.request.post({'data' : params}).then(function(error, data){
            if(error == null) {
                aDeferred.resolve(data);
            }else{
                aDeferred.reject(error);
            }
        });
        return aDeferred.promise();
    },
    
    constructTagElement : function (params) {
        var tagElement = jQuery(jQuery('#dummyTagElement').html()).clone(true);
        tagElement.attr('data-id',params.id).attr('data-type',params.type);
        tagElement.find('.tagLabel').html(params.name);
        return tagElement
    },
    
    addTagsToShowAllTagContianer : function(tagsList) {
        var showAllTagContainer = jQuery('.showAllTagContainer');
        var viewAllTagContainer = jQuery('.viewAllTagsContainer');
        var currentTagHolder = showAllTagContainer.find('.currentTag');
        var viewAllCurrentTagHolder = viewAllTagContainer.find('.currentTag');
        var currentTagMenu = showAllTagContainer.find('.currentTagMenu');
        
        for(var index in tagsList) {
            var tagInfo = tagsList[index];
            var tagId = tagInfo.id;
            
            if(currentTagHolder.find('[data-id="'+ tagId +'"]').length > 0) {
                continue;
            }
            var newTagEle = this.constructTagElement(tagInfo);
            currentTagHolder.append(newTagEle);
            viewAllCurrentTagHolder.append(newTagEle.clone());
            currentTagMenu.find('[data-id="'+ tagId +'"]').closest('li.tag-item').remove();
        }
        
        if(currentTagHolder.find('.tag').length > 0){
            currentTagHolder.find('.noTagsPlaceHolder').hide();
        }
    },
    
    removeTagsFromShowTagContainer : function(tagsList, container) {
        var showAllTagContainer = (typeof container === 'undefined') ? jQuery('.showAllTagContainer') : container;
        var currentTagHolder = showAllTagContainer.find('.currentTag');
        var currentTagMenu = showAllTagContainer.find('.currentTagMenu');
        
        for(var index in tagsList) {
            var tagInfo = tagsList[index];
            var tagId = tagInfo.id;
            
            var tagEle = currentTagHolder.find('[data-id="'+ tagId +'"]');
            if(tagEle.length <= 0) {
                continue;
            }
            tagEle.find('.editTag,.deleteTag').remove();
            var newTagLiEle = jQuery('<li class="tag-item list-group-item"> <a style="margin-left:0px"></a> </li>').find('a').html(tagEle).closest('li');
            currentTagMenu.find('ul').append(newTagLiEle);
            currentTagMenu.find(".noTagExistsPlaceHolder").hide();  
        }
    },
    
    viewAllTags : function(container) {
        var viewAllTagContainer = container.find('.viewAllTagsContainer').clone(true);
        // There is no delete option from view All Tags
        viewAllTagContainer.find(".deleteTag").remove();
        app.helper.showModal(viewAllTagContainer.find('.modal-dialog'), {'cb' : function(modalContainer){
                
                var registerViewAllTagEvents = function(modalContainer) {
                    var currentTagHolder = modalContainer.find('.currentTag');
                    app.helper.showScroll(currentTagHolder);
                }
                registerViewAllTagEvents(modalContainer);
        }});
    },
    
    showAllTags : function(container, callerParams) {
        var self = this;
        var showTagModal = container.find('.showAllTagContainer').clone(true);
        app.helper.showModal(showTagModal.find('.modal-dialog'),{'cb' : function(modalContainer){
                
                var registerShowAllTagEvents = function(modalContainer) {
                    var currentTagsSelected = new Array();
                    var currentTagHolder = modalContainer.find('.currentTag');
                    var currentTagMenuHolder = modalContainer.find(".currentTagMenu");
                    var currentTagScroll = modalContainer.find('.currentTagScroll');
                    var deletedTags = new Array();
                    
                    if(currentTagHolder.find(".tag").length <= 0){ 
                       currentTagHolder.find(".noTagsPlaceHolder").show();
                    }
                    
                    if(currentTagMenuHolder.find(".tag").length <= 1){ 
                       currentTagMenuHolder.find(".noTagExistsPlaceHolder").show();
                    }else{
                        currentTagMenuHolder.find(".noTagExistsPlaceHolder").hide();
                    }
                    
                    modalContainer.find('.dropdown-menu').on('click',function(e){
                        e.stopPropagation();
                   });
                   
                   app.helper.showVerticalScroll(modalContainer.find('.dropdown-menu .scrollable'));
                   
                    modalContainer.find('.currentTagMenu').off('click', 'li > a').on('click', 'li > a',function(e){   
                        var element = jQuery(e.currentTarget);
                        var selectedTag = jQuery(element.html());
                        selectedTag.append('<i class="editTag fa fa-pencil"></i><i class="deleteTag fa fa-times"></i>');
                        currentTagsSelected.push(selectedTag.data('id'));
                        element.remove();
                        currentTagHolder.append(selectedTag);
                        currentTagHolder.find(".noTagsPlaceHolder").hide();
                        
                        if(currentTagMenuHolder.find(".tag").length <= 1){
                            currentTagMenuHolder.find(".noTagExistsPlaceHolder").show();
                        }
                    });
                    

                    app.helper.showScroll(currentTagHolder,{alwaysVisible:false});
                    
                    modalContainer.find('.currentTagSelector').instaFilta({
                        targets : '.currentTagMenu  li',
                        sections : '.currentTagMenu ul',
                        scope : modalContainer, 
                        hideEmptySections : true,
                        beginsWith : false, 
                        caseSensitive : false, 
                        typeDelay : 0
                     });
                     
                    var tagInputEle = modalContainer.find('input[name="createNewTag"]');
//                    var params = {tags : [], tokenSeparators: [","]};
//                    vtUtils.showSelect2ElementView(tagInputEle, params);
                    
                    var form = modalContainer.find('form');
                    form.off('submit').on('submit',function(e){
                        e.preventDefault();
                        var modalContainerClone = modalContainer.clone(true);
                        app.helper.hideModal();
                
                        if(typeof callerParams == 'undefined') {
                            var saveParams = {};
                        }else{
                            var saveParams = callerParams;
                        }
                        var saveTagList = {};
                        saveTagList['existing'] = currentTagsSelected;
                        saveTagList['new'] = tagInputEle.val().split(',');
                        saveTagList['deleted'] = deletedTags;
                        saveParams['tagsList'] = saveTagList;
                       
                        var formData = form.serializeFormData();
                        saveParams['newTagType'] = formData['visibility'];
                        self.saveTag(saveParams).then(function(data){
                            app.event.trigger('post.MassTag.save', modalContainerClone, data);
                        }, function(error){
                            //app.helper.showAlertBox({'message' : error})
                        })
                        return false;
                    }); 
                    
                    modalContainer.off('click', '.deleteTag').on('click', '.deleteTag', function(e){
                        var currenttarget = jQuery(e.currentTarget);
                        var currentTagHolder = currenttarget.closest(".currentTag");
                        var tag = currenttarget.closest('.tag');
                        var deletedTagId = tag.data('id');
                        
                        var index = currentTagsSelected.indexOf(deletedTagId);
                        //if the tag is currently selected then remove it from currently selected list
                        if(index >= 0) {
                            currentTagsSelected.splice(index, 1);
                        }else{
                            deletedTags.push(deletedTagId);
                        }
                        var tagInfo = {
                            'id' : deletedTagId
                        };
                        self.removeTagsFromShowTagContainer(new Array(tagInfo),modalContainer);
                        
                        if(currentTagHolder.find(".tag").length <= 0){
                            currentTagHolder.find(".noTagsPlaceHolder").show();
                        }
                    });
                     
                }
                registerShowAllTagEvents(modalContainer);
        }});
    },
    
    registerShowMassTagListener : function() {
        var self = this;
        app.event.on('Request.MassTag.show',function(e, container, saveParams){
            if(typeof container == 'undefined') {
                container = jQuery('body');
            }
            self.showAllTags(container, saveParams);
        });
    },
    
    registerEditTagEvents : function(){
        var self = this;
        jQuery(document).on('click','.editTag', function(e){
            var element = jQuery(e.currentTarget);
            var tag = element.closest('.tag');
            var editTagContainer = self.editTagContainerCached.clone();
            editTagContainer.find('[name="id"]').val(tag.data('id'));
            editTagContainer.find('[name="tagName"]').val(tag.find('.tagLabel').text());
            if(tag.attr('data-type') == "public") {
                editTagContainer.find('[type="checkbox"]').prop('checked',true);
            }else{
                editTagContainer.find('[type="checkbox"]').prop('checked', false);
            }
            editTagContainer.removeClass('hide');
            var container = element.closest('.modal').length ? element.closest('.modal') : jQuery('body');
            var placement = app.view() == "Detail" ? 'bottom' : 'top';
            element.popover({
                'content' : editTagContainer,
                'html' : true,
                'placement' : placement,
                'animation' : true,
                'trigger' : 'manual',
                'template' : self.editTagTemplate,
                'container' : container
                
            });
            element.popover('show');
        });
       
        jQuery(document).on('click', '.editTagContainer .saveTag', function(e){
            var element = jQuery(e.currentTarget);
            var editTagContainer = element.closest('.editTagContainer');
            var tagName = editTagContainer.find('[name="tagName"]').val();
            
            if(tagName.trim() == ""){
                var message = app.vtranslate('JS_PLEASE_ENTER_VALID_TAG_NAME');
                app.helper.showErrorNotification({'message':message});
    			return;
            }
            
            var valueParams = {};
            valueParams['name'] = editTagContainer.find('[name="tagName"]').val();
            var visibility = 'private';
            if(editTagContainer.find('[name="visibility"][type="checkbox"]').is(':checked')) {
                visibility = editTagContainer.find('[name="visibility"][type="checkbox"]').val();
            }
            valueParams.visibility = visibility;
            var tagId = editTagContainer.find('[name="id"]').val();
            valueParams.id = tagId;
            self.updateTag(valueParams).then(function(data){
                var tagElement = jQuery('[data-id="'+ tagId +'"]');
                tagElement.find('.tagLabel').text(data.name);
                tagElement.attr('data-type', data.type);
                var popOverId = element.closest('.popover').attr('id');
                jQuery('[aria-describedby="'+ popOverId +'"]').popover('destroy');
            }, function(error){
                app.helper.showAlertBox({'message' : error.message});
            });
        });
       
        jQuery(document).on('click', '.editTagContainer .cancelSaveTag', function(e){
            var element = jQuery(e.currentTarget);
            var popOverId = element.closest('.popover').attr('id');
            jQuery('[aria-describedby="'+ popOverId +'"]').popover('destroy');
        });
        
        jQuery(document).on('keyup', '.editTagContainer [name="tagName"]', function(e) {
            (e.keyCode || e.which) === 13 && 
            jQuery(e.target).closest('.editTagContainer').find('.saveTag').trigger('click');
        });
    },
    
    registerViewAllTagsListener : function() {
        var self = this;
        app.event.on('Request.AllTag.show', function(e, container){
            if(typeof container == 'undefined') {
                container = jQuery('body');
            }
            self.viewAllTags(container);
        });
    },
    
    registerEvents : function() {
        this.registerShowMassTagListener();
        this.registerEditTagEvents();
        this.registerViewAllTagsListener();
    }
});

