/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("Inventory_Edit_Js", {
    
    zeroDiscountType : 'zero' ,
	percentageDiscountType : 'percentage',
	directAmountDiscountType : 'amount',

	individualTaxType : 'individual',
	groupTaxType :  'group',
    
    lineItemPopOverTemplate : '<div class="popover lineItemPopover" role="tooltip"><div class="arrow"></div>\n\
                                <h3 class="popover-title"></h3>\n\
								<div class="popover-content"></div>\n\
									<div class="modal-footer lineItemPopupModalFooter">\n\
										<center>\n\
										<button class="btn btn-success popoverButton" type="button"><strong>'+app.vtranslate('JS_LBL_SAVE')+'</strong></button>\n\
										<a href="#" class="popoverCancel" type="reset">'+app.vtranslate('JS_LBL_CANCEL')+'</a>\n\
										</center>\n\
									</div>\n\
                                </div>'
    
}, {
    
    //Will have the mapping of address fields based on the modules
	addressFieldsMapping : {
								'Contacts' : {
									'bill_street' :  'mailingstreet',
									'ship_street' : 'otherstreet',
									'bill_pobox' : 'mailingpobox',
									'ship_pobox' : 'otherpobox',
									'bill_city' : 'mailingcity',
									'ship_city'  : 'othercity',
									'bill_state' : 'mailingstate',
									'ship_state' : 'otherstate',
									'bill_code' : 'mailingzip',
									'ship_code' : 'otherzip',
									'bill_country' : 'mailingcountry',
									'ship_country' : 'othercountry'
								} ,

								'Accounts' : {
									'bill_street' :  'bill_street',
									'ship_street' : 'ship_street',
									'bill_pobox' : 'bill_pobox',
									'ship_pobox' : 'ship_pobox',
									'bill_city' : 'bill_city',
									'ship_city'  : 'ship_city',
									'bill_state' : 'bill_state',
									'ship_state' : 'ship_state',
									'bill_code' : 'bill_code',
									'ship_code' : 'ship_code',
									'bill_country' : 'bill_country',
									'ship_country' : 'ship_country'
								},

								'Vendors' : {
									'bill_street' : 'street',
									'ship_street' : 'street',
									'bill_pobox' : 'pobox',
									'ship_pobox' : 'pobox',
									'bill_city' : 'city',
									'ship_city'  : 'city',
									'bill_state' : 'state',
									'ship_state' : 'state',
									'bill_code' : 'postalcode',
									'ship_code' : 'postalcode',
									'bill_country' : 'country',
									'ship_country' : 'country'
								},
								'Leads' : {
									'bill_street' :  'lane',
									'ship_street' : 'lane',
									'bill_pobox' : 'pobox',
									'ship_pobox' : 'pobox',
									'bill_city' : 'city',
									'ship_city'  : 'city',
									'bill_state' : 'state',
									'ship_state' : 'state',
									'bill_code' : 'code',
									'ship_code' : 'code',
									'bill_country' : 'country',
									'ship_country' : 'country'
								} 
							},

	//Address field mapping between modules specific for billing and shipping
	addressFieldsMappingBetweenModules:{
								'AccountsBillMap' : {
									'bill_street' :  'bill_street',
									'bill_pobox' : 'bill_pobox',
									'bill_city' : 'bill_city',
									'bill_state' : 'bill_state',
									'bill_code' : 'bill_code',
									'bill_country' : 'bill_country'
									},
								'AccountsShipMap' : {
									'ship_street' : 'ship_street',
									'ship_pobox' : 'ship_pobox',
									'ship_city'  : 'ship_city',
									'ship_state' : 'ship_state',
									'ship_code' : 'ship_code',
									'ship_country' : 'ship_country'
									},
								'ContactsBillMap' : {
									'bill_street' :  'mailingstreet',
									'bill_pobox' : 'mailingpobox',
									'bill_city' : 'mailingcity',
									'bill_state' : 'mailingstate',
									'bill_code' : 'mailingzip',
									'bill_country' : 'mailingcountry'
									},
								'ContactsShipMap' : {
									'ship_street' : 'otherstreet',
									'ship_pobox' : 'otherpobox',
									'ship_city'  : 'othercity',
									'ship_state' : 'otherstate',
									'ship_code' : 'otherzip',
									'ship_country' : 'othercountry'
								},
								'LeadsBillMap' : {
									'bill_street' :  'lane',
									'bill_pobox' : 'pobox',
									'bill_city' : 'city',
									'bill_state' : 'state',
									'bill_code' : 'code',
									'bill_country' : 'country'
									},
								'LeadsShipMap' : {
									'ship_street' : 'lane',
									'ship_pobox' : 'pobox',
									'ship_city'  : 'city',
									'ship_state' : 'state',
									'ship_code' : 'code',
									'ship_country' : 'country'
								}

	},

	//Address field mapping within module
	addressFieldsMappingInModule : {
										'bill_street':'ship_street',
										'bill_pobox':'ship_pobox',
										'bill_city'	:'ship_city',
										'bill_state':'ship_state',
										'bill_code'	:'ship_code',
										'bill_country':'ship_country'
								},
    
    dummyLineItemRow : false,
    lineItemsHolder : false,
    numOfLineItems : false,
    customLineItemFields : false,
    customFieldsDefaultValues : false,
    numOfCurrencyDecimals : false,
    taxTypeElement : false,
    regionElement : false,
    currencyElement : false,
    finalDiscountUIEle : false,
    conversionRateEle : false,
    overAllDiscountEle : false,
    preTaxTotalEle : false,
    
    //final calculation elements
    netTotalEle : false,
    finalDiscountTotalEle : false,
    finalTaxEle : false,
    finalDiscountEle : false,
    
    chargesTotalEle : false,
    chargesContainer : false, 
    chargeTaxesContainer : false,
    chargesTotalDisplay : false,
    chargeTaxesTotal : false,
    deductTaxesTotal : false,
    adjustmentEle : false,
    adjustmentTypeEles : false,
    grandTotal : false,
    groupTaxContainer : false,
    dedutTaxesContainer : false,
    
    
    lineItemDetectingClass : 'lineItemRow',
    
    init : function() {
       this._super();
       this.initializeVariables();
    },
    
    initializeVariables : function() {
        this.dummyLineItemRow = jQuery('#row0');
        this.lineItemsHolder = jQuery('#lineItemTab');
        this.numOfLineItems = this.lineItemsHolder.find('.'+ this.lineItemDetectingClass).length;
		if(typeof jQuery('#customFields').val() == 'undefined') {
			this.customLineItemFields = [];
		}else {
			this.customLineItemFields = JSON.parse(jQuery('#customFields').val());
		}
		
		if(typeof jQuery('#customFieldsDefaultValues').val() == 'undefined') {
			this.customFieldsDefaultValues = [];
		}else {
			this.customFieldsDefaultValues = JSON.parse(jQuery('#customFieldsDefaultValues').val());
		}
        
        this.numOfCurrencyDecimals = parseInt(jQuery('.numberOfCurrencyDecimal').val());
        this.taxTypeElement = jQuery('#taxtype');
        this.regionElement = jQuery('#region_id');
        this.currencyElement = jQuery('#currency_id');
        
        this.netTotalEle = jQuery('#netTotal');
        this.finalDiscountTotalEle = jQuery('#discountTotal_final');
        this.finalTaxEle = jQuery('#tax_final');
        this.finalDiscountUIEle = jQuery('#finalDiscountUI');
        this.finalDiscountEle = jQuery('#finalDiscount');
        this.conversionRateEle = jQuery('#conversion_rate');
        this.overAllDiscountEle = jQuery('#overallDiscount');
        this.chargesTotalEle = jQuery('#chargesTotal');
        this.preTaxTotalEle = jQuery('#preTaxTotal');
        this.chargesContainer = jQuery('#chargesBlock')
        this.chargesTotalDisplay = jQuery('#chargesTotalDisplay');
        this.chargeTaxesContainer = jQuery('#chargeTaxesBlock');
        this.chargeTaxesTotal = jQuery('#chargeTaxTotalHidden');
        this.deductTaxesTotal = jQuery('#deductTaxesTotalAmount');
        this.adjustmentEle = jQuery('#adjustment');
        this.adjustmentTypeEles = jQuery('input[name="adjustmentType"]');
        this.grandTotal = jQuery('#grandTotal');
        this.groupTaxContainer = jQuery('#group_tax_div');
        this.dedutTaxesContainer = jQuery('#deductTaxesBlock');
        
    },
    
    /**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getLineItemContentsContainer : function() {
		if(this.lineItemContentsContainer == false) {
			this.setLineItemContainer(jQuery('#lineItemTab'));
		}
		return this.lineItemContentsContainer;
	},
    
    /**
	 * Function which will copy the address details
	 */
	copyAddressDetails : function(data,container,addressMap) {
		var self = this;
		var sourceModule = data['source_module'];
		var noAddress = true;
		var errorMsg;

		this.getRecordDetails(data).then(
			function(data){
				var response = data;
				if(typeof addressMap != "undefined"){
					var result = response['data'];
					for(var key in addressMap) {
						if(result[addressMap[key]] != ""){
							noAddress = false;
							break;
						}
					}
					if(noAddress){
						if(sourceModule == "Accounts"){
							errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
						} else if(sourceModule == "Contacts"){
							errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
						} else if(sourceModule == "Leads") {
							errorMsg = 'JS_SELECTED_LEAD_DOES_NOT_HAVE_AN_ADDRESS';
						}
						app.helper.showErrorNotification({'message':app.vtranslate(errorMsg)});
					} else{
						self.mapAddressDetails(addressMap, result, container);
					}
				} else{
					self.mapAddressDetails(self.addressFieldsMapping[sourceModule], response['data'], container);
					if(sourceModule == "Accounts"){
						container.find('.accountAddress').attr('checked','checked');
					}else if(sourceModule == "Contacts"){
						container.find('.contactAddress').attr('checked','checked');
					}
				}
			},
			function(error, err){

			});
	},
    
    /**
	 * Function which will copy the address details of the selected record
	 */
	mapAddressDetails : function(addressDetails, result, container) {
		for(var key in addressDetails) {
			container.find('[name="'+key+'"]').val(result[addressDetails[key]]);
			container.find('[name="'+key+'"]').trigger('change');
		}
	},
    
    /**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress : function(swapMode){
		var self = this;
		var formElement = this.getForm();
		var addressMapping = this.addressFieldsMappingInModule;
		if(swapMode == "false"){
			for(var key in addressMapping) {
				var fromElement = formElement.find('[name="'+key+'"]');
				var toElement = formElement.find('[name="'+addressMapping[key]+'"]');
				toElement.val(fromElement.val());
			}
		} else if(swapMode){
			var swappedArray = self.swapObject(addressMapping);
			for(var key in swappedArray) {
				var fromElement = formElement.find('[name="'+key+'"]');
				var toElement = formElement.find('[name="'+swappedArray[key]+'"]');
				toElement.val(fromElement.val());
			}
			toElement.val(fromElement.val());
		}
	},
    
    /**
	 * Function to swap array
	 * @param Array that need to be swapped
	 */
	swapObject : function(objectToSwap){
		var swappedArray = {};
		var newKey,newValue;
		for(var key in objectToSwap){
			newKey = objectToSwap[key];
			newValue = key;
			swappedArray[newKey] = newValue;
		}
		return swappedArray;
	},
    
    getLineItemNextRowNumber : function() {
        return ++this.numOfLineItems;
    },
    
    formatListPrice : function(lineItemRow, listPriceValue) {
        var listPrice = parseFloat(listPriceValue).toFixed(this.numOfCurrencyDecimals);
		lineItemRow.find('.listPrice').val(listPrice);
		return this;
	},
    
    
    
    getLineItemRowNumber : function(itemRow) {
        return parseInt(itemRow.attr('data-row-num'));
    },
    
    /**
	 * Function which gives quantity value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getQuantityValue : function(lineItemRow){
		return parseFloat(lineItemRow.find('.qty').val());
	},
    
    /**
	 * Function which will get the value of cost price
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getPurchaseCostValue : function(lineItemRow){
        var rowNum = this.getLineItemRowNumber(lineItemRow);
		return parseFloat(jQuery('#purchaseCost'+rowNum).val());
	},
    
    /**
	 * Function which will set the cost price
	 * @params : lineItemRow - row which represents the line item
	 * @params : cost price
	 * @return : current instance;
	 */
	setPurchaseCostValue : function(lineItemRow, purchaseCost) {
		if(isNaN(purchaseCost)){
			purchaseCost=0;
		}
        var rowNum = this.getLineItemRowNumber(lineItemRow);
        jQuery('#purchaseCost'+rowNum).val(purchaseCost);
		var quantity = this.getQuantityValue(lineItemRow);
		var updatedPurchaseCost = parseFloat(quantity) * parseFloat(purchaseCost);
        lineItemRow.find('[name="purchaseCost'+rowNum+'"]').val(updatedPurchaseCost);
		lineItemRow.find('.purchaseCost').text(updatedPurchaseCost);
		return this;
	},
    
    /**
	 * Function which will set the image
	 * @params : lineItemRow - row which represents the line item
	 * @params : image source
	 * @return : current instance;
	 */
	setImageTag : function(lineItemRow, imgSrc) {
		var imgTag = '<img src='+imgSrc+' height="42" width="42">';
		lineItemRow.find('.lineItemImage').html(imgTag);
		return this;
	},
    
    /**
	 * Function which will give me list price value
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getListPriceValue : function(lineItemRow) {
		return parseFloat(lineItemRow.find('.listPrice').val());
	},

	setListPriceValue : function(lineItemRow, listPriceValue) {
        var listPrice = parseFloat(listPriceValue).toFixed(this.numOfCurrencyDecimals);
		lineItemRow.find('.listPrice').val(listPrice);
		return this;
	},
    
    /**
	 * Function which will set the line item total value excluding tax and discount
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemTotalValue - value which has line item total  (qty*listprice)
	 * @return : current instance;
	 */
	setLineItemTotal : function(lineItemRow, lineItemTotalValue) {
		lineItemRow.find('.productTotal').text(lineItemTotalValue);
		return this;
	},

	/**
	 * Function which will get the value of line item total (qty*listprice)
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTotal : function(lineItemRow) {
		var lineItemTotal =  this.getLineItemTotalElement(lineItemRow).text();
        if(lineItemTotal)
            return parseFloat(lineItemTotal);
        return 0;
	},
    
    /**
	 * Function which will get the line item total element
	 * @params : lineItemRow - row which represents the line item
	 * @return : jQuery element
	 */
	getLineItemTotalElement : function(lineItemRow) {
		return lineItemRow.find('.productTotal');
	},
    
    /**
	 * Function which will set the discount total value for line item
	 * @params : lineItemRow - row which represents the line item
	 *			 discountValue - discount value
	 * @return : current instance;
	 */
	setDiscountTotal : function(lineItemRow, discountValue) {
		jQuery('.discountTotal',lineItemRow).text(discountValue);
		return this;
	},

	/**
	 * Function which will get the value of total discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getDiscountTotal : function(lineItemRow) {
		var element = jQuery('.discountTotal',lineItemRow);
		if(element.length > 0) {
			return parseFloat(element.text());
		}
		return 0;
	},
    
    /**
	 * Function which will set the total after discount value
	 * @params : lineItemRow - row which represents the line item
	 *			 totalAfterDiscountValue - total after discount value
	 * @return : current instance;
	 */
	setTotalAfterDiscount : function(lineItemRow, totalAfterDiscountValue){
		lineItemRow.find('.totalAfterDiscount').text(totalAfterDiscountValue);
		return this;
	},

	/**
	 * Function which will get the value of total after discount
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getTotalAfterDiscount : function(lineItemRow) {
		var element = lineItemRow.find('.totalAfterDiscount');
		if(element.length > 0) {
			return parseFloat(element.text());
		}
		return this.getLineItemTotal(lineItemRow);
	},

	/**
	 * Function which will set the tax total
	 * @params : lineItemRow - row which represents the line item
	 *			 taxTotal -  tax total
	 * @return : current instance;
	 */
	setLineItemTaxTotal : function(lineItemRow, taxTotal) {
		jQuery('.productTaxTotal', lineItemRow).text(taxTotal);
		return this;
	},

	/**
	 * Function which will get the value of total tax
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemTaxTotal : function(lineItemRow){
		var lineItemTax = jQuery('.productTaxTotal', lineItemRow).text();
        if(lineItemTax)
            return parseFloat(lineItemTax);
        return 0;
	},
    
    /**
	 * Function which will set the line item net price
	 * @params : lineItemRow - row which represents the line item
	 *			 lineItemNetPriceValue -  line item net price value
	 * @return : current instance;
	 */
	setLineItemNetPrice : function(lineItemRow, lineItemNetPriceValue){
		lineItemRow.find('.netPrice').text(lineItemNetPriceValue);
		return this;
	},

	/**
	 * Function which will get the value of net price
	 * @params : lineItemRow - row which represents the line item
	 * @return : string
	 */
	getLineItemNetPrice : function(lineItemRow) {
        return this.formatLineItemNetPrice(lineItemRow.find('.netPrice'));
	},
    
    formatLineItemNetPrice : function(netPriceEle) {
        var lineItemNetPrice = netPriceEle.text();
        if(lineItemNetPrice)
            return parseFloat(lineItemNetPrice);
        return 0;
    },

	setNetTotal : function(netTotalValue){
		this.netTotalEle.text(netTotalValue);
		return this;
	},

	getNetTotal : function() {
		var netTotal = this.netTotalEle.text();
        if(netTotal)
            return parseFloat(netTotal);
        return 0;
	},

	/**
	 * Function to set the final discount total
	 */
	setFinalDiscountTotal : function(finalDiscountValue){
		this.finalDiscountTotalEle.text(finalDiscountValue);
		return this;
	},

	getFinalDiscountTotal : function() {
        var discountTotal = this.finalDiscountTotalEle.text();
		if(discountTotal)
			return parseFloat(discountTotal);
		return 0;
	},

	setGroupTaxTotal : function(groupTaxTotalValue) {
		this.finalTaxEle.text(groupTaxTotalValue);
	},

	getGroupTaxTotal : function() {
		var groupTax = this.finalTaxEle.text();
        if(groupTax)
            return parseFloat(groupTax);
        return 0
	},
    
    getChargesTotal : function() {
		var chargesElement = this.chargesTotalEle;
		if (chargesElement.length <= 0) {
			return 0;
		}
		return parseFloat(chargesElement.val());
	},
    
    getChargeTaxesTotal : function() {
		var taxElement = this.chargeTaxesTotal;
		if (taxElement.length <= 0) {
			return 0;
		}
		return parseFloat(taxElement.val());
	},
    
    getDeductTaxesTotal : function() {
		var taxElement = this.deductTaxesTotal;
		if (taxElement.length <= 0) {
			return 0;
		}
		return parseFloat(taxElement.text());
	},

	/**
	 * Function to set the pre tax total
	 */
	setPreTaxTotal : function(preTaxTotalValue){
		this.preTaxTotalEle.text(preTaxTotalValue);
		return this;
	},
    
    /**
	 * Function to get the pre tax total
	 */
	getPreTaxTotal : function() {
		if(this.preTaxTotalEle.length > 0){
            return parseFloat(this.preTaxTotalEle.text())
        }
	},
    
    /**
	 * Function which will set the margin
	 * @params : lineItemRow - row which represents the line item
	 * @params : margin
	 * @return : current instance;
	 */
	setMarginValue : function(lineItemRow, margin) {
        var rowNum = this.getLineItemRowNumber(lineItemRow);
		lineItemRow.find('[name="margin'+ rowNum +'"]').val(margin);
		lineItemRow.find('.margin').text(margin);
		return this;
	},
    
    getAdjustmentValue : function() {
		return parseFloat(this.adjustmentEle.val());
	},

	isAdjustMentAddType : function() {
		var adjustmentSelectElement = this.adjustmentTypeEles;
		var selectionOption;
		adjustmentSelectElement.each(function(){
			if(jQuery(this).is(':checked')){
				selectionOption = jQuery(this);
			}
		})
		if(typeof selectionOption != "undefined"){
			if(selectionOption.val() == '+'){
				return true;
			}
		}
		return false;
	},

	isAdjustMentDeductType : function() {
		var adjustmentSelectElement = this.adjustmentTypeEles;
		var selectionOption;
		adjustmentSelectElement.each(function(){
			if(jQuery(this).is(':checked')){
				selectionOption = jQuery(this);
			}
		})
		if(typeof selectionOption != "undefined"){
			if(selectionOption.val() == '-'){
				return true;
			}
		}
		return false;
	},

	setGrandTotal : function(grandTotalValue) {
		this.grandTotal.text(grandTotalValue);
		return this;
	},

	getGrandTotal : function() {
		var grandTotal = this.grandTotal.text();
        if(grandTotal)
            return parseFloat(grandTotal);
        return 0;
	},
    
    isIndividualTaxMode : function() {
        return (this.taxTypeElement.val() == Inventory_Edit_Js.individualTaxType) ? true : false;
	},

	isGroupTaxMode : function() {
		return (this.taxTypeElement.val() == Inventory_Edit_Js.groupTaxType) ? true : false;
	},
    
    /**
	 * Function which will give the closest line item row element
	 * @return : jQuery object
	 */
	getClosestLineItemRow : function(element){
		return element.closest('tr.'+this.lineItemDetectingClass);
	},
    
    isProductSelected : function(element){
		var parentRow = element.closest('tr');
		var productField = parentRow.find('.productName');
		var response = productField.valid();
		return response;
	 },
    
    checkLineItemRow : function(){
        var numRows = this.lineItemsHolder.find('.'+this.lineItemDetectingClass).length;
		if(numRows > 1) {
			this.showLineItemsDeleteIcon();
		}else{
			this.hideLineItemsDeleteIcon();
		}
	},

	showLineItemsDeleteIcon : function(){
		this.lineItemsHolder.find('.deleteRow').show();
	},

	hideLineItemsDeleteIcon : function(){
		this.lineItemsHolder.find('.deleteRow').hide();
	},
    
    clearLineItemDetails : function(parentElem) {
		var lineItemRow = this.getClosestLineItemRow(parentElem);
		jQuery('[id*="purchaseCost"]', lineItemRow).val('0');
		jQuery('.lineItemImage', lineItemRow).html('');
		jQuery('input.selectedModuleId',lineItemRow).val('');
		jQuery('input.listPrice',lineItemRow).val('0');
		jQuery('.lineItemCommentBox', lineItemRow).val('');
		jQuery('.subProductIds', lineItemRow).val('');
		jQuery('.subProductsContainer', lineItemRow).html('');
		this.quantityChangeActions(lineItemRow);
	},
    
    saveProductCount : function () {
		jQuery('#totalProductCount').val(this.lineItemsHolder.find('tr.'+this.lineItemDetectingClass).length);
	},

	saveSubTotalValue : function() {
		jQuery('#subtotal').val(this.getNetTotal());
	},

	saveTotalValue : function() {
		jQuery('#total').val(this.getGrandTotal());
	},
    
    /**
	 * Function to save the pre tax total value
	 */
	savePreTaxTotalValue : function() {
		jQuery('#pre_tax_total').val(this.getPreTaxTotal());
	},
    
    updateRowNumberForRow : function(lineItemRow, expectedSequenceNumber, currentSequenceNumber){
		if(typeof currentSequenceNumber == 'undefined') {
			//by default there will zero current sequence number
			currentSequenceNumber = 0;
		}

		var idFields = new Array('productName','subproduct_ids','hdnProductId','purchaseCost','margin',
									'comment','qty','listPrice','discount_div','discount_type','discount_percentage',
									'discount_amount','lineItemType','searchIcon','netPrice','subprod_names',
									'productTotal','discountTotal','totalAfterDiscount','taxTotal');

		var classFields = new Array('taxPercentage');
		//To handle variable tax ids
		for(var classIndex in classFields) {
			var className = classFields[classIndex];
			jQuery('.'+className,lineItemRow).each(function(index, domElement){
				var idString = domElement.id
				//remove last character which will be the row number
				idFields.push(idString.slice(0,(idString.length-1)));
			});
		}

		var expectedRowId = 'row'+expectedSequenceNumber;
		for(var idIndex in idFields ) {
			var elementId = idFields[idIndex];
			var actualElementId = elementId + currentSequenceNumber;
			var expectedElementId = elementId + expectedSequenceNumber;
			lineItemRow.find('#'+actualElementId).attr('id',expectedElementId)
					   .filter('[name="'+actualElementId+'"]').attr('name',expectedElementId);
		}

		var nameFields = new Array('discount', 'purchaseCost', 'margin');
		for (var nameIndex in nameFields) {
			var elementName = nameFields[nameIndex];
			var actualElementName = elementName+currentSequenceNumber;
			var expectedElementName = elementName+expectedSequenceNumber;
			lineItemRow.find('[name="'+actualElementName+'"]').attr('name', expectedElementName);
		}

		lineItemRow.attr('id', expectedRowId).attr('data-row-num', expectedSequenceNumber);
        lineItemRow.find('input.rowNumber').val(expectedSequenceNumber);
        
		return lineItemRow;
	},
    
    updateLineItemElementByOrder : function () {
		var self = this;
		var checkedDiscountElements = {};
        var lineItems = this.lineItemsHolder.find('tr.'+this.lineItemDetectingClass);
		lineItems.each(function(index,domElement){
			var lineItemRow = jQuery(domElement);
			var actualRowId = lineItemRow.attr('id');

			var discountContianer = lineItemRow.find('div.discountUI');
			var element = discountContianer.find('input.discounts').filter(':checked');
			checkedDiscountElements[actualRowId] = element.data('discountType');
		});

		lineItems.each(function(index,domElement){
			var lineItemRow = jQuery(domElement);
			var expectedRowIndex = (index+1);
			var expectedRowId = 'row'+expectedRowIndex;
			var actualRowId = lineItemRow.attr('id');
			if(expectedRowId != actualRowId) {
				var actualIdComponents = actualRowId.split('row');
				self.updateRowNumberForRow(lineItemRow, expectedRowIndex, actualIdComponents[1]);

				var discountContianer = lineItemRow.find('div.discountUI');
				discountContianer.find('input.discounts').each(function(index1, discountElement) {
					var discountElement = jQuery(discountElement);
					var discountType = discountElement.data('discountType');
					if (discountType == checkedDiscountElements[actualRowId]) {
						discountElement.attr('checked', true);
					}
				});
			}
		});
	},
    
    /**
     * Function which will initialize line items custom fields with default values if exists 
     */
    initializeLineItemRowCustomFields : function(lineItemRow, rowNum) {
        var lineItemType = lineItemRow.find('input.lineItemType').val();
        for(var cfName in this.customLineItemFields) {
			var elementName = cfName + rowNum;
			var element = lineItemRow.find('[name="'+elementName+'"]');

			var cfDataType = this.customLineItemFields[cfName];
			if (cfDataType == 'picklist' || cfDataType == 'multipicklist') {
                
				(cfDataType == 'multipicklist') && (element = lineItemRow.find('[name="'+elementName+'[]"]'));

				var picklistValues = element.data('productPicklistValues');
				(lineItemType == 'Services') && (picklistValues = element.data('servicePicklistValues'));
                var options = '';
				(cfDataType == 'picklist') && (options = '<option value="">'+app.vtranslate('JS_SELECT_OPTION')+'</option>');
                
				for(var picklistName in picklistValues) {
					var pickListValue = picklistValues[picklistName];
					options += '<option value="'+picklistName+'">'+pickListValue+'</option>';
				}
				element.html(options);
				element.addClass('select2');
			}

			var defaultValueInfo = this.customFieldsDefaultValues[cfName];
			if (defaultValueInfo) {
				var defaultValue = defaultValueInfo;
				if (typeof defaultValueInfo == 'object') {
					defaultValue = defaultValueInfo['productFieldDefaultValue'];
					(lineItemType == 'Services') && (defaultValue = defaultValueInfo['serviceFieldDefaultValue'])
				}

				if (cfDataType === 'multipicklist') {
					if (defaultValue.length > 0) {
						defaultValue = defaultValue.split(" |##| ");
                        var setDefaultValue = function(picklistElement, values){
                            for(var index in values) {
                                var picklistVal = values[index];
                                picklistElement.find('option[value="'+picklistVal+'"]').prop('selected',true);
                            }
                        }(element, defaultValue)
					}
				} else {
					element.val(defaultValue);
				}
			} else {
				defaultValue = '';
				element.val(defaultValue);
			}
		}

		return lineItemRow;
    },
    
    getLineItemSetype : function(row) {
        return row.find('.lineItemType').val();
    },  
    
    getNewLineItem : function(params) {
        var currentTarget = params.currentTarget;
        var itemType = currentTarget.data('moduleName');
        var newRow = this.dummyLineItemRow.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');
        var individualTax = this.isIndividualTaxMode();
		if(individualTax){
			newRow.find('.individualTaxContainer').removeClass('hide');
		}
        newRow.find('.lineItemPopup').filter(':not([data-module-name="'+ itemType +'"])').remove();
        newRow.find('.lineItemType').val(itemType);
        var newRowNum = this.getLineItemNextRowNumber();
        this.updateRowNumberForRow(newRow, newRowNum);
        this.initializeLineItemRowCustomFields(newRow, newRowNum);        
        return newRow
    },
    
   
    
    /**
	 * Function which will calculate line item total excluding discount and tax
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateLineItemTotal : function (lineItemRow) {
		var quantity = this.getQuantityValue(lineItemRow);
		var listPrice = this.getListPriceValue(lineItemRow);
		var lineItemTotal = parseFloat(quantity) * parseFloat(listPrice);
		this.setLineItemTotal(lineItemRow,lineItemTotal.toFixed(this.numOfCurrencyDecimals));
	},
    
    /**
	 * Function which will calculate discount for the line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateDiscountForLineItem : function(lineItemRow) {
		var discountContianer = lineItemRow.find('div.discountUI');
		var element = discountContianer.find('input.discounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');

		jQuery('input.discount_type',discountContianer).val(discountType);
		var rowPercentageField = jQuery('input.discount_percentage',discountContianer);
		var rowAmountField = jQuery('input.discount_amount',discountContianer);

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if(discountValue == ""){
			discountValue = 0;
		}
		if(isNaN(discountValue) ||  discountValue < 0){
			discountValue = 0;
		}
        var productTotal = this.getLineItemTotal(lineItemRow);
		var lineItemDiscount = '('+discountValue+')';
		if(discountType == Inventory_Edit_Js.percentageDiscountType) {
			lineItemDiscount = '('+discountValue+'%)';
			rowPercentageField.removeClass('hide').focus();
			//since it is percentage
			
			discountValue = (productTotal * discountValue)/100;
		} else if(discountType == Inventory_Edit_Js.directAmountDiscountType) {
			rowAmountField.removeClass('hide').focus();
		}
		jQuery('.itemDiscount', lineItemRow).text(lineItemDiscount);
		jQuery('.productTotalVal', lineItemRow).text(productTotal.toFixed(this.numOfCurrencyDecimals));
		this.setDiscountTotal(lineItemRow, parseFloat(discountValue).toFixed(this.numOfCurrencyDecimals))
			.calculateTotalAfterDiscount(lineItemRow);
    },
    
    /**
	 * Function which will calculate line item total after discount
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	calculateTotalAfterDiscount: function(lineItemRow) {
        var productTotal = this.getLineItemTotal(lineItemRow);
		var discountTotal = this.getDiscountTotal(lineItemRow);
		var totalAfterDiscount = productTotal - discountTotal;
		totalAfterDiscount = totalAfterDiscount.toFixed(this.numOfCurrencyDecimals);
		this.setTotalAfterDiscount(lineItemRow,totalAfterDiscount);
		var purchaseCost = parseFloat(lineItemRow.find('.purchaseCost').text());
		var margin = totalAfterDiscount - purchaseCost;
		margin = parseFloat(margin.toFixed(2));
		this.setMarginValue(lineItemRow, margin);
    },
    
    /**
	 * Function which will calculate tax for the line item total after discount
	 */
	calculateTaxForLineItem : function(lineItemRow) {
        var self = this;
        var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var taxPercentages = jQuery('.taxPercentage',lineItemRow);
		//intially make the tax as zero
		var taxTotal = 0;
		jQuery.each(taxPercentages,function(index,domElement){
			var taxPercentage = jQuery(domElement);
			var individualTaxRow = taxPercentage.closest('tr');
			var individualTaxPercentage = taxPercentage.val();
			if(individualTaxPercentage == ""){
				individualTaxPercentage = "0";
			}
             if(isNaN(individualTaxPercentage)){
                var individualTaxTotal = "0";
            } else {
                var individualTaxPercentage = parseFloat(individualTaxPercentage);
                var individualTaxTotal = Math.abs(individualTaxPercentage * totalAfterDiscount)/100;
                individualTaxTotal = individualTaxTotal.toFixed(self.numOfCurrencyDecimals);
            }
			individualTaxRow.find('.taxTotal').val(individualTaxTotal);
		});

		//Calculation compound taxes
		var taxTotal = 0;
        jQuery.each(taxPercentages, function(index, domElement){
			var taxElement = jQuery(domElement);
			var taxRow = taxElement.closest('tr');
			var total = jQuery('.taxTotal', taxRow).val();
        	var compoundOn = taxElement.data('compoundOn');
			if (compoundOn) {
                var amount = parseFloat(totalAfterDiscount);

				jQuery.each(compoundOn, function(index, id) {
					if(!isNaN(jQuery('.taxTotal'+id, lineItemRow).val())) {
						amount = parseFloat(amount) + parseFloat(jQuery('.taxTotal'+id, lineItemRow).val());
					}
				});

				if(isNaN(taxElement.val())) {
					var total = 0;
				} else {
					var total = Math.abs(amount * taxElement.val())/100;
				}

				taxRow.find('.taxTotal').val(total);
			}
			taxTotal += parseFloat(total);
		});
		taxTotal = parseFloat(taxTotal).toFixed(self.numOfCurrencyDecimals);
		this.setLineItemTaxTotal(lineItemRow, taxTotal);
	},
    
    /**
	 * Function which will calculate net price for the line item
	 */
	calculateLineItemNetPrice : function(lineItemRow) {
        var totalAfterDiscount = this.getTotalAfterDiscount(lineItemRow);
		var netPrice = parseFloat(totalAfterDiscount);
        if(this.isIndividualTaxMode()) {
			var productTaxTotal = this.getLineItemTaxTotal(lineItemRow);
        	netPrice +=  parseFloat(productTaxTotal)
		}
		netPrice = netPrice.toFixed(this.numOfCurrencyDecimals);
		this.setLineItemNetPrice(lineItemRow,netPrice);
	},
    
    /**
	 * Function which will caliculate the total net price for all the line items
	 */
	calculateNetTotal : function() {
		var self = this
		var netTotalValue = 0;
		this.lineItemsHolder.find('tr.'+this.lineItemDetectingClass+' .netPrice').each(function(index,domElement){
			var lineItemNetPriceEle = jQuery(domElement);
			netTotalValue += self.formatLineItemNetPrice(lineItemNetPriceEle);
		});
		this.setNetTotal(netTotalValue.toFixed(this.numOfCurrencyDecimals));
		this.finalDiscountUIEle.find('.subTotalVal').text(netTotalValue);
	},
    
    calculateFinalDiscount : function() {
        var discountContainer = this.finalDiscountUIEle;
		var element = discountContainer.find('input.finalDiscounts').filter(':checked');
		var discountType = element.data('discountType');
		var discountRow = element.closest('tr');
        var numberOfDecimal = this.numOfCurrencyDecimals;

		jQuery('#discount_type_final').val(discountType);
		var rowPercentageField = discountContainer.find('input.discount_percentage_final');
		var rowAmountField = discountContainer.find('input.discount_amount_final');

		//intially making percentage and amount discount fields as hidden
		rowPercentageField.addClass('hide');
		rowAmountField.addClass('hide');

		var discountValue = discountRow.find('.discountVal').val();
		if(discountValue == ""){
			discountValue = 0;
		}
		if(isNaN(discountValue) ||  discountValue < 0){
           discountValue = 0;
		}

		var overallDiscount = '('+discountValue+')';
		if(discountType == Inventory_Edit_Js.percentageDiscountType){
			overallDiscount = '('+discountValue+'%)';
            rowPercentageField.removeClass('hide').focus();
            //since it is percentage
            var productTotal = this.getNetTotal();
            discountValue = (productTotal * discountValue)/100;
		}else if(discountType == Inventory_Edit_Js.directAmountDiscountType){
            if(this.prevSelectedCurrencyConversionRate){
                var conversionRate = this.conversionRateEle.val();
                conversionRate = conversionRate / this.prevSelectedCurrencyConversionRate;
                discountValue = discountValue * conversionRate;
                discountRow.find('.discountVal').val(discountValue);
            }
            rowAmountField.removeClass('hide').focus();
		}
		discountValue = parseFloat(discountValue).toFixed(numberOfDecimal);
		this.overAllDiscountEle.text(overallDiscount);
		this.setFinalDiscountTotal(discountValue);
		this.calculatePreTaxTotal();
    },
    
    /**
	 * Function to calculate the preTaxTotal value
	 */
	calculatePreTaxTotal : function() {
        var numberOfDecimal = this.numOfCurrencyDecimals;
		if (this.isGroupTaxMode()) {
			var netTotal = this.getNetTotal();
		} else {
			var thisInstance = this;
			var netTotal = 0;
			var elementsList = this.lineItemsHolder.find('.'+ this.lineItemDetectingClass);
			jQuery.each(elementsList, function(index, element) {
				var lineItemRow = jQuery(element);
				netTotal = netTotal + thisInstance.getTotalAfterDiscount(lineItemRow);
			});
		}
		var chargesTotal = this.getChargesTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var preTaxTotal = netTotal + chargesTotal - finalDiscountValue;
		var preTaxTotalValue = parseFloat(preTaxTotal).toFixed(numberOfDecimal);
		this.setPreTaxTotal(preTaxTotalValue);
	},
    
    calculateCharges : function() {
		var chargesBlockContainer = this.chargesContainer;
		var numberOfDecimal = this.numOfCurrencyDecimals;
        
        var netTotal = this.getNetTotal();
        var finalDiscountValue = this.getFinalDiscountTotal();
        var amount = parseFloat(netTotal-finalDiscountValue).toFixed(numberOfDecimal);
        
		chargesBlockContainer.find('.chargePercent').each(function(index, domElement){
			var element = jQuery(domElement);
		
			if(isNaN(element.val())) {
				var value = 0;
			} else {
				var value = Math.abs(amount * element.val())/100;
			}

			element.closest('tr').find('.chargeValue').val(parseFloat(value).toFixed(numberOfDecimal));
		});

		var chargesTotal = 0;
		chargesBlockContainer.find('.chargeValue').each(function(index, domElement){
			var chargeElementValue = jQuery(domElement).val();
			if(!chargeElementValue){
				jQuery(domElement).val(0);
				chargeElementValue=0;
			}
			chargesTotal = parseFloat(chargesTotal) + parseFloat(chargeElementValue);
		});

		this.chargesTotalEle.val(parseFloat(chargesTotal));
		this.chargesTotalDisplay.text(parseFloat(chargesTotal).toFixed(numberOfDecimal));
		jQuery('#SHChargeVal').text(chargesTotal.toFixed(numberOfDecimal));

		this.calculateChargeTaxes();
//		this.calculatePreTaxTotal();
//		this.calculateGrandTotal();
	},
    
    calculateChargeTaxes : function() {
		var self = this;
		var chargesBlockContainer = this.chargeTaxesContainer;

		chargesBlockContainer.find('.chargeTaxPercentage').each(function(index, domElement){
			var element = jQuery(domElement);
			var chargeId = element.data('chargeId');
			var chargeAmount = self.chargesContainer.find('[name="charges['+chargeId+'][value]"]').val();
            if(isNaN(element.val())) {
				var value = 0;
			} else {
				var value = Math.abs(chargeAmount * element.val())/100;
			}
            element.closest('tr').find('.chargeTaxValue').val(parseFloat(value).toFixed(self.numOfCurrencyDecimals));
		});

		chargesBlockContainer.find('.chargeTaxPercentage').each(function(index, domElement){
			var element = jQuery(domElement);
			var compoundOn = element.data('compoundOn');
			if (compoundOn) {
				var chargeId = element.data('chargeId');
				var chargeAmount = parseFloat(self.chargesContainer.find('[name="charges['+chargeId+'][value]"]').val()).toFixed(self.numOfCurrencyDecimals);

				jQuery.each(compoundOn, function(index, id) {
                    var chargeTaxEle = chargesBlockContainer.find('.chargeTax'+chargeId+id);
					if(!isNaN(chargeTaxEle.val())) {
						chargeAmount = parseFloat(chargeAmount) + parseFloat(chargeTaxEle.val());
					}
				});

				if(isNaN(element.val())) {
					var value = 0;
				} else {
					var value = Math.abs(chargeAmount * element.val())/100;
				}

				element.closest('tr').find('.chargeTaxValue').val(parseFloat(value).toFixed(self.numOfCurrencyDecimals));
			}
		});

		var chargesTotal = 0;
		chargesBlockContainer.find('.chargeTaxValue').each(function(index, domElement){
			var chargeElementValue = jQuery(domElement).val();
            chargesTotal = parseFloat(chargesTotal) + parseFloat(chargeElementValue);
		});
        jQuery('#chargeTaxTotal').text(parseFloat(chargesTotal).toFixed(this.numOfCurrencyDecimals));
        this.chargeTaxesTotal.val(parseFloat(chargesTotal).toFixed(this.numOfCurrencyDecimals));
		this.calculatePreTaxTotal();
		this.calculateGrandTotal();
	},
    
    calculateGrandTotal : function(){
        var netTotal = this.getNetTotal();
		var discountTotal = this.getFinalDiscountTotal();
		var shippingHandlingCharge = this.getChargesTotal();
		var shippingHandlingTax = this.getChargeTaxesTotal();
		var deductedTaxesAmount = this.getDeductTaxesTotal();
		var adjustment = this.getAdjustmentValue();
		var grandTotal = parseFloat(netTotal) - parseFloat(discountTotal) + parseFloat(shippingHandlingCharge) + parseFloat(shippingHandlingTax) - parseFloat(deductedTaxesAmount);

		if(this.isGroupTaxMode()){
			grandTotal +=  this.getGroupTaxTotal();
		}

		if(this.isAdjustMentAddType()) {
			grandTotal +=  parseFloat(adjustment);
		}else if(this.isAdjustMentDeductType()) {
			grandTotal -=  parseFloat(adjustment);
		}

		grandTotal = grandTotal.toFixed(this.numOfCurrencyDecimals);
		this.setGrandTotal(grandTotal);
	},
    
    calculateGroupTax : function() {
        var self = this;
        var netTotal = this.getNetTotal();
		var finalDiscountValue = this.getFinalDiscountTotal();
		var amount = netTotal - finalDiscountValue;
		amount = parseFloat(amount).toFixed(this.numOfCurrencyDecimals);
		var groupTaxTotal = 0;
        this.groupTaxContainer.find('.groupTaxPercentage').each(function(index, domElement) {
			var groupTaxPercentageElement = jQuery(domElement);
			var groupTaxRow = groupTaxPercentageElement.closest('tr');
            if(isNaN(groupTaxPercentageElement.val())){
                var groupTaxValue = "0";
            } else {
                var groupTaxValue = Math.abs(amount * groupTaxPercentageElement.val())/100;
            }
			groupTaxValue = parseFloat(groupTaxValue).toFixed(self.numOfCurrencyDecimals);
			groupTaxRow.find('.groupTaxTotal').val(groupTaxValue);
		});

		//Calculating compound taxes
		groupTaxTotal = 0;
        this.groupTaxContainer.find('.groupTaxPercentage').each(function(index, domElement) {
			var groupTaxPercentageElement = jQuery(domElement);
			var compoundOn = groupTaxPercentageElement.data('compoundOn');
			var groupTaxRow = groupTaxPercentageElement.closest('tr');

			if (compoundOn) {
				var totalAmount = amount;
				jQuery.each(compoundOn, function(index, value) {
                    var groupTaxAmountValue = self.groupTaxContainer.find('[name="tax'+value+'_group_amount"]').val();
                    if(!isNaN(groupTaxAmountValue)) {
						totalAmount = parseFloat(totalAmount) + parseFloat(groupTaxAmountValue);
					}
				});

				if (isNaN(groupTaxPercentageElement.val())) {
					var groupTaxValue = 0;
				} else {
					var groupTaxValue = Math.abs(totalAmount * groupTaxPercentageElement.val()) / 100;
				}

				groupTaxValue = parseFloat(groupTaxValue).toFixed(self.numOfCurrencyDecimals);
				groupTaxRow.find('.groupTaxTotal').val(groupTaxValue);
			} else {
				var groupTaxValue = groupTaxRow.find('.groupTaxTotal').val();
			}
			if(isNaN(groupTaxValue)) {
				groupTaxValue = 0;
			}
			groupTaxTotal += parseFloat(groupTaxValue);
		});

		this.setGroupTaxTotal(groupTaxTotal.toFixed(this.numOfCurrencyDecimals));
	},
    
    calculateDeductTaxes : function() {
		var self = this;
		var netTotal = this.getNetTotal();
        var finalDiscountValue = this.getFinalDiscountTotal();
        var amount = parseFloat(netTotal-finalDiscountValue).toFixed(this.numOfCurrencyDecimals);

		var deductTaxesTotalAmount = 0;
		this.dedutTaxesContainer.find('.deductTaxPercentage').each(function(index, domElement){
				var value = 0;
			var element = jQuery(domElement);
			if(!isNaN(element.val())) {
				value = Math.abs(amount * element.val())/100;
			}

			value = parseFloat(value).toFixed(self.numOfCurrencyDecimals);
			element.closest('tr').find('.deductTaxValue').val(value);
			deductTaxesTotalAmount = parseFloat(deductTaxesTotalAmount) + parseFloat(value);
		});

		this.deductTaxesTotal.text(parseFloat(deductTaxesTotalAmount).toFixed(this.numOfCurrencyDecimals));
		this.calculateGrandTotal();
	},
    
    lineItemDirectDiscountCal: function(conversionRate){
         //LineItems Discount Calculations for direct Price reduction
        var self = this;

        self.lineItemsHolder.find('tr.'+self.lineItemDetectingClass).each(function(index, domElement) {
            var lineItemRow = jQuery(domElement);
            var discountContianer = lineItemRow.find('div.discountUI');
            var element = discountContianer.find('input.discounts').filter(':checked');
            var discountRow = element.closest('tr');
            var discountType = element.data('discountType');
            var discountValue = discountRow.find('.discountVal').val();
            if((discountType == Inventory_Edit_Js.directAmountDiscountType) ){
                var newdiscountValue = conversionRate * discountValue;
                discountRow.find('.discountVal').val(newdiscountValue);
                jQuery(element).closest('tr').find('.discountVal').val(newdiscountValue);
                self.setDiscountTotal(lineItemRow,newdiscountValue.toFixed(self.numberOfCurrencyDecimals));
            }
        });
    },
    
    
    AdjustmentShippingResultCalculation: function(conversionRate){
		//Adjustment
		var self = this;
		var adjustmentElement = this.adjustmentEle;
		var newAdjustment = jQuery(adjustmentElement).val() * conversionRate;
		jQuery(adjustmentElement).val(newAdjustment);

		//Shipping & handling
		var chargesBlockContainer = self.chargesContainer;
		chargesBlockContainer.find('.chargeValue').each(function(index, domElement){
			var chargeElement = jQuery(domElement);
			jQuery(chargeElement).val(parseFloat(jQuery(domElement).val()) * conversionRate);
		});
		this.calculateCharges();
	},
    
    lineItemRowCalculations : function(lineItemRow) {
		this.calculateLineItemTotal(lineItemRow);
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);
	},
    
    lineItemToTalResultCalculations : function(){
		this.calculateNetTotal();
		this.calculateFinalDiscount();

		this.calculateCharges();
		if(this.isGroupTaxMode()){
			this.calculateGroupTax();
		}
		this.calculateDeductTaxes();
		this.calculateGrandTotal();
	},
    
    /**
	 * Function which will handle the actions that need to be performed once the tax percentage is change for a line item
	 * @params : lineItemRow - element which will represent lineItemRow
	 */

	taxPercentageChangeActions : function(lineItemRow){
		this.calculateLineItemNetPrice(lineItemRow);
		this.calculateNetTotal();
		this.calculateFinalDiscount();
		if(this.isGroupTaxMode()){
			this.calculateGroupTax();
		}
        this.lineItemToTalResultCalculations();
		this.calculateGrandTotal();
	},
    
    lineItemDiscountChangeActions : function(lineItemRow){
		this.calculateDiscountForLineItem(lineItemRow);
		this.calculateTaxForLineItem(lineItemRow);
		this.calculateLineItemNetPrice(lineItemRow);

		this.lineItemToTalResultCalculations();
	},
    
    finalDiscountChangeActions : function() {
		this.calculateChargeTaxes();
		this.calculateFinalDiscount();
		if(this.isGroupTaxMode()){
			this.calculateGroupTax();
		}
		this.calculateCharges();
		this.calculateDeductTaxes();
		this.calculateGrandTotal();
	},
    
    lineItemDeleteActions : function() {
		this.lineItemToTalResultCalculations();
	},
    
    loadSubProducts : function(lineItemRow) {
		var recordId = jQuery('input.selectedModuleId',lineItemRow).val();
		var subProrductParams = {
            'module' : "Products",
            'action' : "SubProducts",
            'record' : recordId
		}
		app.request.get({'data':subProrductParams}).then(
			function(error, data){
                if(!data){
                    return;
                }
				var result = data;
				var isBundleViewable = result.isBundleViewable;
				var responseData = result.values;
				var subProductsContainer = jQuery('.subProductsContainer',lineItemRow);
				var subProductIdHolder = jQuery('.subProductIds',lineItemRow);

				var subProductIdsList = '';
				var subProductHtml = '';
                                for(var id in responseData) {
                                    if (isBundleViewable == 1) {
						subProductHtml += '<em> - '+responseData[id]['productName'] + ' (' +responseData[id]['quantity']+')';
						if (responseData[id]['stockMessage']) {
							 subProductHtml += ' - <span class="redColor">'+responseData[id]['stockMessage']+'</span>';
						}
						subProductHtml += '</em><br>';
					}
                                        subProductIdsList += id+':'+responseData[id]['quantity']+',';
                                    }
				subProductIdHolder.val(subProductIdsList);
				subProductsContainer.html(subProductHtml);
			}
		);
	},
    
    /**
	 * Function which will handle the actions that need to be preformed once the qty is changed like below
	 *  - calculate line item total -> discount and tax -> net price of line item -> grand total
	 * @params : lineItemRow - element which will represent lineItemRow
	 */
	quantityChangeActions : function(lineItemRow) {
		var purchaseCost = this.getPurchaseCostValue(lineItemRow);
		this.setPurchaseCostValue(lineItemRow, purchaseCost);
		this.lineItemRowCalculations(lineItemRow);
		this.lineItemToTalResultCalculations();
	},
    
    getTaxDiv: function(taxObj,parentRow){
		var rowNumber = jQuery('input.rowNumber',parentRow).val();
		var loopIterator = 1;
		var taxDiv =
				'<div class="taxUI hide" id="tax_div'+rowNumber+'">'+
                     '<p class="popover_title hide"> Set Tax for : <span class="variable"></span></p>';
			if(!jQuery.isEmptyObject(taxObj)){
				taxDiv +=
					'<div class="individualTaxDiv">'+
						'<table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table'+rowNumber+'">';

				for(var taxName in taxObj){
					var taxInfo = taxObj[taxName];
					taxDiv +=
							'<tr>'+
								'<td>  '+taxInfo.taxlabel+'</td>'+
								'<td style="text-align: right;">'+
									'<input type="text" name="'+taxName+'_percentage'+rowNumber+'" data-rule-positive=true data-rule-inventory_percentage=true  id="'+taxName+'_percentage'+rowNumber+'" value="'+taxInfo.taxpercentage+'" class="taxPercentage" data-compound-on='+taxInfo.compoundOn+' data-regions-list="'+taxInfo.regionsList+'">&nbsp;%'+
								'</td>'+
								'<td style="text-align: right; padding-right: 10px;">'+
									'<input type="text" name="popup_tax_row'+rowNumber+'" class="cursorPointer span1 taxTotal taxTotal'+taxInfo.taxid+'" value="0.0" readonly>'+
								'</td>'+
							'</tr>';
					loopIterator++;
				}
				taxDiv +=
						'</table>'+
					'</div>';
			} else {
				taxDiv +=
					'<div class="textAlignCenter">'+
						'<span>'+app.vtranslate('JS_NO_TAXES_EXISTS')+'</span>'+
					'</div>';
			}

			taxDiv += '</div>';
		return jQuery(taxDiv);
    },
    
    mapResultsToFields: function(parentRow,responseData){
		var lineItemNameElment = jQuery('input.productName',parentRow);
        var referenceModule = this.getLineItemSetype(parentRow);
        var lineItemRowNumber = parentRow.data('rowNum');
		for(var id in responseData){
			var recordId = id;
			var recordData = responseData[id];
			var selectedName = recordData.name;
			var unitPrice = recordData.listprice;
            var listPriceValues = recordData.listpricevalues;
			var taxes = recordData.taxes;
			var purchaseCost = recordData.purchaseCost;
			this.setPurchaseCostValue(parentRow, purchaseCost);
			var imgSrc = recordData.imageSource;
			this.setImageTag(parentRow, imgSrc);
			if(referenceModule == 'Products') {
				parentRow.data('quantity-in-stock',recordData.quantityInStock);
			}
			var description = recordData.description;
			jQuery('input.selectedModuleId',parentRow).val(recordId);
			jQuery('input.lineItemType',parentRow).val(referenceModule);
			lineItemNameElment.val(selectedName);
			lineItemNameElment.attr('disabled', 'disabled');
			jQuery('input.listPrice',parentRow).val(unitPrice);
			var currencyId = this.currencyElement.val();
            var listPriceValuesJson  = JSON.stringify(listPriceValues);
            if(typeof listPriceValues[currencyId]!= 'undefined') {
            	this.formatListPrice(parentRow, listPriceValues[currencyId]);
                this.lineItemRowCalculations(parentRow);
        	}
            jQuery('input.listPrice',parentRow).attr('list-info',listPriceValuesJson);
			jQuery('input.listPrice',parentRow).data('baseCurrencyId', recordData.baseCurrencyId);
			jQuery('textarea.lineItemCommentBox',parentRow).val(description);
			var taxUI = this.getTaxDiv(taxes,parentRow);
            jQuery('.taxDivContainer',parentRow).html(taxUI);

			//Take tax percentage according to tax-region, if region is selected.
			var selectedRegionId = this.regionElement.val();
            if(selectedRegionId!=0){
                var taxPercentages = jQuery('.taxPercentage', parentRow);
                jQuery.each(taxPercentages,function(index1, taxDomElement){
                    var taxPercentage = jQuery(taxDomElement);
                    var regionsList = taxPercentage.data('regionsList');
                    var value = regionsList['default'];
                    if (selectedRegionId && regionsList[selectedRegionId]) {
                        value = regionsList[selectedRegionId];
                    }
                    taxPercentage.val(parseFloat(value));
                });
            }

			if(this.isIndividualTaxMode()) {
                parentRow.find('.productTaxTotal').removeClass('hide')
            }else{
                parentRow.find('.productTaxTotal').addClass('hide')
            }
		}
		if(referenceModule == 'Products'){
			this.loadSubProducts(parentRow);
		}

		jQuery('.qty',parentRow).trigger('focusout');
    },
    
    showLineItemPopup : function(callerParams) {
        var params = {
            'module' : this.getModuleName(),
            'multi_select' : true,
            'currency_id' : this.currencyElement.val()
        };
        
        params = jQuery.extend(params, callerParams);
        var popupInstance = Vtiger_Popup_Js.getInstance();
        popupInstance.showPopup(params, 'post.LineItemPopupSelection.click');

    },
    
    
    
    postLineItemSelectionActions : function(itemRow, selectedLineItemsData, lineItemSelectedModuleName) {
        for(var index in selectedLineItemsData) {
            if(index != 0) {
                if(lineItemSelectedModuleName == 'Products') {
                    jQuery('#addProduct').trigger('click', selectedLineItemsData[index]);
                } else if(lineItemSelectedModuleName == 'Services') {
					jQuery('#addService').trigger('click', selectedLineItemsData[index]);
                }
            }else{
                itemRow.find('.lineItemType').val(lineItemSelectedModuleName);
                this.mapResultsToFields(itemRow, selectedLineItemsData[index]);
            }
        }
    },
    
    /**
	 * Function which will be used to handle price book popup
	 * @params :  popupImageElement - popup image element
	 */
	pricebooksPopupHandler : function(popupImageElement){
		var self = this;
		var lineItemRow  = popupImageElement.closest('tr.'+ this.lineItemDetectingClass);
        var lineItemProductOrServiceElement = lineItemRow.find('input.productName').closest('td');
        var params = {};
		params.module = 'PriceBooks';
		params.src_module = lineItemProductOrServiceElement.find('i.lineItemPopup').data('moduleName');
		params.src_field = lineItemProductOrServiceElement.find('i.lineItemPopup').data('fieldName');
		params.src_record = lineItemProductOrServiceElement.find('input.selectedModuleId').val();
		params.get_url = 'getProductListPriceURL';
		params.currency_id = jQuery('#currency_id option:selected').val();
        params.view = 'Popup';
        var popupInstance = Vtiger_Popup_Js.getInstance();
        popupInstance.showPopup(params, 'post.LineItemPriceBookSelect.click');
	},
    
    registerAddProductService : function() {
        var self = this;
        var addLineItemEventHandler = function(e, data){
            var currentTarget = jQuery(e.currentTarget);
            var params = {'currentTarget' : currentTarget}
            var newLineItem = self.getNewLineItem(params);
            newLineItem = newLineItem.appendTo(self.lineItemsHolder);
			newLineItem.find('input.productName').addClass('autoComplete');
            newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
            vtUtils.applyFieldElementsView(newLineItem);
            app.event.trigger('post.lineItem.New', newLineItem);
            self.checkLineItemRow();
            self.registerLineItemAutoComplete(newLineItem);
            if(typeof data != "undefined") {
                self.mapResultsToFields(newLineItem,data);
            }
        }
        jQuery('#addProduct').on('click', addLineItemEventHandler);
        jQuery('#addService').on('click', addLineItemEventHandler);
    },
    
    registerProductAndServiceSelector : function() {
        var self = this;
        
        this.lineItemsHolder.on('click','.lineItemPopup', function(e){
            var triggerer = jQuery(e.currentTarget);
            self.showLineItemPopup({'view': triggerer.data('popup')});
            var popupReferenceModule = triggerer.data('moduleName');
            var postPopupHandler = function(e, data){
                data = JSON.parse(data);
                if(!$.isArray(data)){
                    data = [data];
                }
                self.postLineItemSelectionActions(triggerer.closest('tr'), data, popupReferenceModule);
            }
            app.event.off('post.LineItemPopupSelection.click');
            app.event.one('post.LineItemPopupSelection.click', postPopupHandler);
        });
    },
    
    registerQuantityChangeEvent : function() {
        var self = this;

		this.lineItemsHolder.on('focusout','.qty',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = element.closest('tr.'+ self.lineItemDetectingClass);
			var quantityInStock = lineItemRow.data('quantityInStock');
			if(typeof quantityInStock  != 'undefined') {
				if(parseFloat(element.val()) > parseFloat(quantityInStock)) {
					lineItemRow.find('.stockAlert').removeClass('hide').find('.maxQuantity').text(quantityInStock);
				}else{
					lineItemRow.find('.stockAlert').addClass('hide');
				}
			}
                        if(self.formValidatorInstance == false){
                            self.quantityChangeActions(lineItemRow);
                        }
                        else{
                           if(self.formValidatorInstance.element(element)) {
                                self.quantityChangeActions(lineItemRow);
                            } 
                        }
                            
		});
    },
    
    /**
	  * Function which will register event for list price event change
	  */
	 registerListPriceChangeEvent : function() {
		var self = this;
		
		this.lineItemsHolder.on('focusout', 'input.listPrice',function(e){
			var element = jQuery(e.currentTarget);
			var lineItemRow = self.getClosestLineItemRow(element);
			var isPriceChanged = element.data('isPriceChanged');
            if(!self.formValidatorInstance.element(element)) {
                return;
            }
            
			if (isPriceChanged == false) {
				var listPriceValues = JSON.parse(element.attr('list-info'));
				var listPriceVal = self.getListPriceValue(lineItemRow);
				var currencyElement = self.currencyElement;
				var currencyId = currencyElement.val();
				var optionsSelected = currencyElement.find('option:selected');
				var prevSelectedCurrencyConversionRate = self.conversionRateEle.val();

				var conversionRate	= optionsSelected.data('conversionRate');
				conversionRate = parseFloat(conversionRate)/ parseFloat(prevSelectedCurrencyConversionRate);
				var convertedListPrice = listPriceValues[currencyId];
				if (typeof listPriceValues[currencyId] == 'undefined') {
					var baseCurrencyId = element.data('baseCurrencyId');
					var baseCurrencyElement = currencyElement.find("option[value='"+baseCurrencyId+"']");
					convertedListPrice = (listPriceValues[baseCurrencyId] * optionsSelected.data('conversionRate')) / baseCurrencyElement.data('conversionRate');
				}

				if(convertedListPrice != listPriceVal) {
					element.data('isPriceChanged', true);
				}
			}
			self.quantityChangeActions(lineItemRow);
		});
	 },
     
     /**
	 * Function which will regisrer price book popup
	 */
	 registerPriceBookPopUp : function () {
		var self = this;
		
		this.lineItemsHolder.on('click','.priceBookPopup',function(e){
            var element = jQuery(e.currentTarget);
            var response = self.isProductSelected(element);
            if(response == false){
                return;
            }
            var lineItemRow  = element.closest('tr.'+ self.lineItemDetectingClass);
			self.pricebooksPopupHandler(element);
            var postPriceBookPopupHandler = function(e, data) {
                var responseData = JSON.parse(data);
                for(var id in responseData){
                    self.setListPriceValue(lineItemRow,responseData[id]);
                }
                self.quantityChangeActions(lineItemRow);
            }
            app.event.off('post.LineItemPriceBookSelect.click');
            app.event.one('post.LineItemPriceBookSelect.click', postPriceBookPopupHandler);
		});
	 },
     
     registerLineItemTaxShowEvent : function() {
		var self = this;
		
		this.lineItemsHolder.on('click','.individualTax',function(e){
			var element = jQuery(e.currentTarget);
			var response = self.isProductSelected(element);
			if(response == false){
				return;
			}
			element.popover('destroy');
			var lineItemRow = self.getClosestLineItemRow(element);
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			var callBackFunction = function(element, data) {

				data.on('focusout', '.taxPercentage', function(e) {
					var currentTaxElement = jQuery(e.currentTarget);
					if (currentTaxElement.valid()) {
						var taxIdAttr = currentTaxElement.attr('id');
						var taxElement = lineItemRow.find('.taxUI').find('#'+taxIdAttr);
						taxElement.val(currentTaxElement.val());
						self.calculateTaxForLineItem(lineItemRow);
						var taxTotalValue = taxElement.closest('tr').find('.taxTotal').val();
						currentTaxElement.closest('tr').find('.taxTotal').val(taxTotalValue);
					}
				});

				data.find('.popoverButton').on('click', function(e){
					var validate = data.find('input').valid();
					if (validate) {
						element.popover('destroy');
						self.taxPercentageChangeActions(lineItemRow);
					}
				});

				data.find('.popoverCancel').on('click', function(e) {
					self.getForm().find("div[id^=qtip-]").qtip('destroy');
					element.popover('destroy');
				});
			};

			var parentElem = jQuery(e.currentTarget).closest('td');

			var taxUI = parentElem.find('div.taxUI').clone(true, true).removeClass('hide').addClass('show');
			taxUI.find('div.individualTaxDiv').removeClass('hide').addClass('show');
            var popOverTitle = taxUI.find('.popover_title').find('.variable').text(self.getTotalAfterDiscount(lineItemRow)).closest('.popover_title').text();
			var template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
            template.addClass('individualTaxForm');
            element.popover({
                'content' : taxUI,
                'html' : true,
                'placement' : 'top',
                'animation' : true,
                'title' : popOverTitle,
                'trigger' : 'manual',
                'template' : template,
                'container' : self.lineItemsHolder
                
            });
            element.one('shown.bs.popover', function(e) {
				callBackFunction(element, jQuery('.individualTaxForm'));
				if(element.next('.popover').find('.popover-content').height() > 300) {
					app.helper.showScroll(element.next('.popover').find('.popover-content'), {'height': '300px'});
				}
            })
            element.popover('toggle');

		});
	 },
     
     registerTaxTypeChange : function() {
		var self = this;
		
		this.taxTypeElement.on('change', function(e){
			if(self.isIndividualTaxMode()) {
				jQuery('#group_tax_row').addClass('hide');
				self.lineItemsHolder.find('tr.'+self.lineItemDetectingClass).each(function(index,domElement){
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').removeClass('hide');
					self.lineItemRowCalculations(lineItemRow);
				});
			}else{
				jQuery('#group_tax_row').removeClass('hide');
				self.lineItemsHolder.find('tr.'+ self.lineItemDetectingClass).each(function(index,domElement){
					var lineItemRow = jQuery(domElement);
					lineItemRow.find('.individualTaxContainer,.productTaxTotal').addClass('hide');
					self.calculateLineItemNetPrice(lineItemRow);
				});
			}
			self.lineItemToTalResultCalculations();
		});
	 },
     
     registerCurrencyChangeEvent : function() {
		var self = this;
		this.currencyElement.change(function(e){
			var element = jQuery(e.currentTarget);
			var currencyId = element.val();
			var conversionRateElem = jQuery('#conversion_rate');
			var prevSelectedCurrencyConversionRate = conversionRateElem.val();
            self.prevSelectedCurrencyConversionRate = prevSelectedCurrencyConversionRate;
			var optionsSelected = element.find('option:selected');
			var conversionRate = optionsSelected.data('conversionRate');
			conversionRateElem.val(conversionRate);
			conversionRate = parseFloat(conversionRate)/ parseFloat(prevSelectedCurrencyConversionRate);
            self.lineItemDirectDiscountCal(conversionRate);
			self.lineItemsHolder.find('tr.'+self.lineItemDetectingClass).each(function(index,domElement){
				var lineItemRow = jQuery(domElement);
				var isLineItemSelected = jQuery(lineItemRow).find('.selectedModuleId').val();
				if (!isLineItemSelected) {
					//continue == 'return' && break == 'return false' in JQuery.each();
					//Ref: http://stackoverflow.com/questions/17162334/how-to-use-continue-in-jquery-each-loop
					return;
				}

				var purchaseCostVal = self.getPurchaseCostValue(lineItemRow);
				var updatedPurchaseCost = parseFloat(purchaseCostVal) * parseFloat(conversionRate);
				self.setPurchaseCostValue(lineItemRow, updatedPurchaseCost);
				var listPriceElement = jQuery(lineItemRow).find('[name^=listPrice]');
				var listPriceValues = JSON.parse(listPriceElement.attr('list-info'));
				var isPriceChanged = listPriceElement.data('isPriceChanged');
                var listPriceVal = self.getListPriceValue(lineItemRow);
                var convertedListPrice = listPriceVal * conversionRate;
                if (isPriceChanged == false) {
					convertedListPrice = listPriceValues[currencyId];
					if (typeof listPriceValues[currencyId] == 'undefined') {
						var baseCurrencyId = listPriceElement.data('baseCurrencyId');
						var baseCurrencyElement = element.find("option[value='"+baseCurrencyId+"']");
                		convertedListPrice = (listPriceValues[baseCurrencyId] * optionsSelected.data('conversionRate')) / baseCurrencyElement.data('conversionRate');
					}
				}
				self.setListPriceValue(lineItemRow, convertedListPrice);
				self.lineItemRowCalculations(lineItemRow);
			});
            self.AdjustmentShippingResultCalculation(conversionRate);
			self.lineItemToTalResultCalculations();
			jQuery('#prev_selected_currency_id').val(optionsSelected.val());
			self.prevSelectedCurrencyConversionRate = false;
		});
	 },
     
     registerLineItemDiscountShowEvent : function() {
		var self = this;
		
		this.lineItemsHolder.on('click', '.individualDiscount', function(e){
			var element = jQuery(e.currentTarget);
			var response = self.isProductSelected(element);
			if(response == false){
				return;
			}
			element.popover('destroy');
            var lineItemRow = self.getClosestLineItemRow(element);
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');
            
			var callBackFunction = function(element, data) {
				var triggerDiscountChangeEvent = function(discountDiv) {
					var selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
					var discountType = selectedDiscountType.data('discountType');

					var rowAmountField = jQuery('input.discount_amount', discountDiv);
					var rowPercentageField = jQuery('input.discount_percentage', discountDiv);

					rowAmountField.hide();
					rowPercentageField.hide();
                    if (discountType == Inventory_Edit_Js.percentageDiscountType) {
                    	rowPercentageField.show().removeClass('hide').focus();
					} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
                    	rowAmountField.show().removeClass('hide').focus();
					}
				};

				var discountDiv = jQuery('div.discountUI', data);
				triggerDiscountChangeEvent(discountDiv);

				data.on('change', '.discounts', function(e) {
                    var ele = jQuery(e.currentTarget);
					var discountDiv = ele.closest('div.discountUI');
					triggerDiscountChangeEvent(discountDiv);

				});

				data.find('.popoverButton').on('click', function(e){
					var validate = data.find('input').valid();
					if (validate) {
						//if the element is not hidden then we need to handle the focus out
						//	if (!app.isHidden(saveButtonElement)) {
						//	var globalModal = saveButtonElement.closest('#globalmodal');
						//	var discountDiv = globalModal.find('div.discountUI');
						var selectedDiscountType = discountDiv.find('input.discounts').filter(':checked');
						var discountType = selectedDiscountType.data('discountType');
						var discountRow = selectedDiscountType.closest('tr');

						var discountValue = discountRow.find('.discountVal').val();
						if (discountValue == "" || isNaN(discountValue) || discountValue < 0) {
							discountValue = 0;
						}

						var discountDivId = discountDiv.attr('id');
						var oldDiscountDiv = jQuery('#' + discountDivId, lineItemRow);

						var discountTypes = oldDiscountDiv.find('input.discounts');
						jQuery.each(discountTypes, function(index, type) {
							var type = jQuery(type);
							type.prop('checked', false);
						});
						jQuery.each(discountTypes, function(index, type) {
							var type = jQuery(type);
							var discountTypeOfType = type.data('discountType');
							if (discountTypeOfType == discountType) {
								type.prop('checked', true);
							}
						});

						if (discountType == Inventory_Edit_Js.percentageDiscountType) {
							jQuery('input.discount_percentage', oldDiscountDiv).val(discountValue);
						} else if (discountType == Inventory_Edit_Js.directAmountDiscountType) {
							jQuery('input.discount_amount', oldDiscountDiv).val(discountValue);
						}
						element.popover('destroy');
						self.lineItemDiscountChangeActions(lineItemRow);
//						}
					}
				});

				data.find('.popoverCancel').on('click', function(e) {
					self.getForm().find("div[id^=qtip-]").qtip('destroy');
					element.popover('destroy');
				});
            }

			var parentElem = jQuery(e.currentTarget).closest('td');

			var discountUI = parentElem.find('div.discountUI').clone(true, true).removeClass('hide').addClass('show');
            var template = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate);
            template.addClass('discountForm');
            var productTotal = self.getLineItemTotal(lineItemRow);
            var popOverTitle = discountUI.find('.popover_title').find('.variable').text(productTotal).closest('.popover_title').text();
			element.popover({
                'content' : discountUI,
                'html' : true,
                'placement' : 'top',
                'animation' : true,
                'title' : popOverTitle,
                'trigger' : 'manual',
                'template' : template,
                'container' : self.lineItemsHolder
                
            });
            element.one('shown.bs.popover', function(e) {
				callBackFunction(element, jQuery('.discountForm'));
				if(element.next('.popover').find('.popover-content').height() > 300) {
					app.helper.showScroll(element.next('.popover').find('.popover-content'), {'height': '300px'});
				}
            })
            element.popover('toggle');
		});
	},
    
    registerFinalDiscountShowEvent : function(){
        var self = this;
		var finalDiscountUI = jQuery('#finalDiscountUI').clone(true,true).removeClass('hide');
        jQuery('#finalDiscountUI').remove();

        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity',0).css('z-index','-1');
        this.finalDiscountEle.popover({
			'content' : finalDiscountUI,
			'html' : true,
			'placement' : 'left',
			'animation' : true,
			'title' : 'Discount',
			'trigger' : 'manual',
			'template' : popOverTemplate
                
		});
		this.finalDiscountEle.on('shown.bs.popover', function(){
			if(jQuery(this.finalDiscountEle).next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(jQuery(this.finalDiscountEle).next('.popover').find('.popover-content'), {'height': '300px'});
			}
			var finalDiscountUI = jQuery('#finalDiscountUI');
			var finalDiscountPopOver = finalDiscountUI.closest('.popover');
			finalDiscountPopOver.find('.popoverButton').on('click', function(e){
				var validate = finalDiscountUI.find('input').valid();
				if(validate) {
					finalDiscountUI.closest('.popover').css('opacity',0).css('z-index','-1');
					self.finalDiscountChangeActions();
				}
			});
       });
       this.finalDiscountEle.popover('show');
       var popOverId = this.finalDiscountEle.attr('aria-describedby');
       var popOverEle = jQuery('#'+popOverId);
       
       //update local cache element
       this.finalDiscountUIEle = jQuery('#finalDiscountUI');
       
       this.finalDiscountEle.on('click', function(e){
		   self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

          if(popOverEle.css('opacity') == '0') {
              self.finalDiscountEle.popover('show');
              popOverEle.find('.popover-title').text(popOverEle.find('.popover_title').text());
              popOverEle.css('opacity',1).css('z-index','');
          }else{
              popOverEle.css('opacity',0).css('z-index','-1');
          }
       });
	},
    
    registerFinalDiscountChangeEvent : function() {
		var self = this;
        this.finalDiscountUIEle.on('change','.finalDiscounts',function(e){
            var element = jQuery(e.currentTarget);
            var discountContainer = self.finalDiscountUIEle;
            var element = discountContainer.find('input.finalDiscounts').filter(':checked');
            var discountType = element.data('discountType');
            
            jQuery('#discount_type_final').val(discountType);
            var rowPercentageField = discountContainer.find('input.discount_percentage_final');
            var rowAmountField = discountContainer.find('input.discount_amount_final');

            //intially making percentage and amount discount fields as hidden
            rowPercentageField.addClass('hide');
            rowAmountField.addClass('hide');
            
            if(discountType == Inventory_Edit_Js.percentageDiscountType){
                rowPercentageField.removeClass('hide').focus();
            }else if(discountType == Inventory_Edit_Js.directAmountDiscountType){
                rowAmountField.removeClass('hide').focus();
            }
            if(element.closest('form').valid()) {
                self.finalDiscountChangeActions();
            }
		});
	},
    
    registerChargeBlockShowEvent : function(){
        var self = this;
		var chargesTrigger = jQuery('#charges');
		var chargesUI = this.chargesContainer.removeClass('hide');

        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity',0).css('z-index','-1');
        chargesTrigger.popover({
                'content' : chargesUI,
                'html' : true,
                'placement' : 'left',
                'animation' : true,
                'title' : chargesTrigger.text(),
                'trigger' : 'manual',
                'template' : popOverTemplate
                
        });

		chargesTrigger.on('shown.bs.popover', function(){
			if(chargesTrigger.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(chargesTrigger.next('.popover').find('.popover-content'), {'height': '300px'});
			}
			var chargesForm = jQuery('#chargesBlock').closest('.lineItemPopover');

			chargesForm.find('.popoverButton').on('click', function(e){
				var validate = chargesForm.find('input').valid();
				if (validate) {
					chargesForm.closest('.popover').css('opacity',0).css('z-index','-1');
					self.calculateCharges();
				}
			});
		});

        chargesTrigger.popover('show');
        var popOverId = chargesTrigger.attr('aria-describedby');
        var popOverEle = jQuery('#'+popOverId);

        chargesTrigger.on('click', function(e){
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

           if(popOverEle.css('opacity') == '0') {
               chargesTrigger.popover('show');
               popOverEle.css('opacity',1).css('z-index','');
           }else{
			   chargesTrigger.popover('hide');
               popOverEle.css('opacity',0).css('z-index','-1');
           }
        });

	},
    
    registerChargeBlockChangeEvent : function(){
		var self = this;
		var chargesBlockContainer = this.chargesContainer;
		
		chargesBlockContainer.on('focusout', '.chargePercent,.chargeValue', function(e){
            var element = jQuery(e.currentTarget);
            if(element.closest('form').valid()) {
                self.calculateCharges();
            }
		});
        
		this.calculateCharges();
	},
    
    registerGroupTaxShowEvent : function() {
        var self = this;
        var finalTaxTriggerer = jQuery('#finalTax');
        var finalTaxUI = jQuery('#group_tax_row').find('.finalTaxUI').removeClass('hide');
		        
        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity',0).css('z-index','-1');
        finalTaxTriggerer.popover({
                'content' : finalTaxUI,
                'html' : true,
                'placement' : 'left',
                'animation' : true,
                'title' : finalTaxUI.find('.popover_title').val(),
                'trigger' : 'manual',
                'template' : popOverTemplate
                
        });

		finalTaxTriggerer.on('shown.bs.popover', function(){
			var finalTaxForm = jQuery('#group_tax_row').find('.finalTaxUI').closest('.lineItemPopover');
			if(finalTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(finalTaxTriggerer.next('.popover').find('.popover-content'), {'height': '300px'});
			}

			finalTaxForm.find('.popoverButton').on('click', function(e){
				var validate = finalTaxForm.find('input').valid();
				if (validate) {
					finalTaxForm.closest('.popover').css('opacity',0).css('z-index','-1');
					self.calculateGroupTax();
                    self.calculateGrandTotal();
				}
			});
		});

        finalTaxTriggerer.popover('show');
        var popOverId = finalTaxTriggerer.attr('aria-describedby');
        var popOverEle = jQuery('#'+popOverId);

        finalTaxTriggerer.on('click', function(e){
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if(popOverEle.css('opacity') == '0') {
				finalTaxTriggerer.popover('show');
				popOverEle.css('opacity',1).css('z-index','');
			} else {
				finalTaxTriggerer.popover('hide');
				popOverEle.css('opacity',0).css('z-index','-1');
			}
        });
	},

	registerGroupTaxChangeEvent : function() {
		var self = this;
		var groupTaxContainer = jQuery('#group_tax_row');

		groupTaxContainer.on('focusout','.groupTaxPercentage',function(e){
            if(groupTaxContainer.find('.finalTaxUI').closest('form').valid()) {
                self.calculateGroupTax();
                self.calculateGrandTotal();
            }
		});
        
	},
    
    registerChargeTaxesShowEvent : function(){
        var self = this;
		var chargeTaxTriggerer = jQuery('#chargeTaxes');
        var chargeTaxesUI =  this.chargeTaxesContainer.removeClass('hide');
        
        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity',0).css('z-index','-1');
        chargeTaxTriggerer.popover({
                'content' : chargeTaxesUI,
                'html' : true,
                'placement' : 'left',
                'animation' : true,
                'title' : 'Discount',
                'trigger' : 'manual',
                'template' : popOverTemplate
                
        });

		chargeTaxTriggerer.on('shown.bs.popover', function(){
			if(chargeTaxTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(chargeTaxTriggerer.next('.popover').find('.popover-content'), {'height': '300px'});
			}
			var chargesTaxForm = self.chargeTaxesContainer.closest('.lineItemPopover');

			chargesTaxForm.find('.popoverButton').on('click', function(e){
				var validate = chargesTaxForm.find('input').valid();
				if (validate) {
					chargesTaxForm.closest('.popover').css('opacity',0).css('z-index','-1');
					self.calculateChargeTaxes();
				}
			});
		});

        chargeTaxTriggerer.popover('show');
        var popOverId = chargeTaxTriggerer.attr('aria-describedby');
        var popOverEle = jQuery('#'+popOverId);

        chargeTaxTriggerer.on('click', function(e){
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if(popOverEle.css('opacity') == '0') {
				chargeTaxTriggerer.popover('show');
				popOverEle.find('.popover-title').text(popOverEle.find('.popover_title').text());
				popOverEle.css('opacity',1).css('z-index','');
			} else {
				chargeTaxTriggerer.popover('hide');
				popOverEle.css('opacity',0).css('z-index','-1');
			}
        });
	},

	registerChargeTaxesChangeEvent : function(){
		var self = this;
        
		this.chargeTaxesContainer.on('focusout', '.chargeTaxPercentage', function(e){
            if(self.chargeTaxesContainer.closest('form').valid()){
                self.calculateChargeTaxes();
            }
		});

		this.calculateChargeTaxes();
    },
    
    registerDeductTaxesShowEvent : function(){
        var self = this;
		var deductTaxesTriggerer = jQuery('#deductTaxes');
        var deductTaxForm = this.dedutTaxesContainer.removeClass('hide');
        
        var popOverTemplate = jQuery(Inventory_Edit_Js.lineItemPopOverTemplate).css('opacity',0).css('z-index','-1');
        deductTaxesTriggerer.popover({
                'content' : deductTaxForm,
                'html' : true,
                'placement' : 'left',
                'animation' : true,
                'title' : deductTaxesTriggerer.text(),
                'trigger' : 'manual',
                'template' : popOverTemplate
                
        });

		deductTaxesTriggerer.on('shown.bs.popover', function(){
			if(deductTaxesTriggerer.next('.popover').find('.popover-content').height() > 300) {
				app.helper.showScroll(deductTaxesTriggerer.next('.popover').find('.popover-content'), {'height': '300px'});
			}
			var deductTaxForm = self.dedutTaxesContainer.closest('.lineItemPopover');

			deductTaxForm.find('.popoverButton').on('click', function(e){
				var validate = deductTaxForm.find('input').valid();
				if (validate) {
					deductTaxForm.closest('.popover').css('opacity',0).css('z-index','-1');
					self.calculateDeductTaxes();
				}
			});
		});

        deductTaxesTriggerer.popover('show');
        var popOverId = deductTaxesTriggerer.attr('aria-describedby');
        var popOverEle = jQuery('#'+popOverId);

        deductTaxesTriggerer.on('click', function(e){
			self.getForm().find('.popover.lineItemPopover').css('opacity', 0).css('z-index', '-1');

			if(popOverEle.css('opacity') == '0') {
				deductTaxesTriggerer.popover('show');
				popOverEle.css('opacity',1).css('z-index','');
			} else {
				deductTaxesTriggerer.popover('hide');
				popOverEle.css('opacity',0).css('z-index','-1');
			}
        });
	},

	registerDeductTaxesChangeEvent : function(){
		var self = this;
        
		this.dedutTaxesContainer.on('focusout', '.deductTaxPercentage', function(e){
            if(self.dedutTaxesContainer.closest('form').valid()) {
                self.calculateDeductTaxes();
            }
		});

		this.calculateDeductTaxes();
	},

    registerAdjustmentTypeChange : function() {
		var self = this;
		this.adjustmentTypeEles.on('change', function(e){
            self.adjustmentEle.trigger('focusout');
		});
	},

	registerAdjustmentValueChange : function() {
		var self = this;
		this.adjustmentEle.on('focusout',function(e){
            var element = jQuery(e.currentTarget);
            if(self.getForm().data('validator').element(element)) {
                var value = element.val();
                if(value == ""){
                    element.val("0");
                }
                self.calculateGrandTotal();
            }
		});
	},
    
    registerRegionChangeEvent : function(){
		var self = this;

		var chargeTaxesBlock = jQuery('.chargeTaxesBlock');

		this.regionElement.change(function(e) {
			var element = jQuery(e.currentTarget);
			var message = app.vtranslate('JS_CONFIRM_TAXES_AND_CHARGES_REPLACE');
			app.helper.showConfirmationBox({'message' : message}).then(
			function(e) {
				var prevRegionId = jQuery('#prevRegionId').val();
				var selectedRegion = element.find('option:selected');
				var selectedRegionId = selectedRegion.val();
				var info = selectedRegion.data('info');
				var selectedCurrencyId = jQuery('#selectedCurrencyId').val();

				self.lineItemsHolder.find('tr.'+self.lineItemDetectingClass).each(function(index, domElement){
					var lineItemRow = jQuery(domElement);
					var taxPercentages = jQuery('.taxPercentage', lineItemRow);
					jQuery.each(taxPercentages,function(index1, taxDomElement){
						var taxPercentage = jQuery(taxDomElement);
						var regionsList = taxPercentage.data('regionsList');
						var value = regionsList['default'];
						if (selectedRegionId && regionsList[selectedRegionId]) {
							value = regionsList[selectedRegionId];
						}
						taxPercentage.val(parseFloat(value));
					});
					if (self.isIndividualTaxMode()) {
						self.calculateTaxForLineItem(lineItemRow);
					}
					self.calculateLineItemNetPrice(lineItemRow);
				});
				self.calculateNetTotal();
				self.calculateFinalDiscount();

				var taxes = info.taxes;
				for (var taxId in taxes) {
					element = self.groupTaxContainer.find('[name="tax'+taxId+'_group_percentage"]');
					element.val(parseFloat(taxes[taxId]['value']));
					element.data('compoundOn', taxes[taxId]['compoundOn']);
				}
				if (self.isGroupTaxMode()) {
					self.calculateGroupTax();
				}

				var charges = info.charges;
				for (var chargeId in charges) {
					var chargeInfo = charges[chargeId];
					var property = 'percent';
					var chargeValue = parseFloat(chargeInfo[property]);
					if (chargeInfo.hasOwnProperty('value')) {
						property = 'value';
						chargeValue = parseFloat(chargeInfo[property]) * parseFloat(jQuery('#conversion_rate').val());
					}
					self.chargesContainer.find('[name="charges['+chargeId+']['+property+']"]').val(chargeValue);

					var chargeTaxes = chargeInfo['taxes'];
					for (var chargeTaxId in chargeTaxes) {
						element = self.chargeTaxesContainer.find('[name="charges['+chargeId+'][taxes]['+chargeTaxId+']"]');
						element.val(parseFloat(chargeTaxes[chargeTaxId]['value']));
						element.data('compoundOn', chargeTaxes[chargeTaxId]['compoundOn']);
					}
				}
				self.calculateCharges();
			},
			function(error, err){});
		});
	},
    
    registerDeleteLineItemEvent : function(){
		var self = this;
        
		this.lineItemsHolder.on('click','.deleteRow',function(e){
			var element = jQuery(e.currentTarget);
			//removing the row
			self.getClosestLineItemRow(element).remove();
			self.checkLineItemRow();
			self.lineItemDeleteActions();
		});
	 },
     
     registerClearLineItemSelection : function() {
         var self = this;
         
         this.lineItemsHolder.on('click','.clearLineItem', function(e){
            var elem = jQuery(e.currentTarget);
            var parentElem = elem.closest('td');
            self.clearLineItemDetails(parentElem);
            parentElem.find('input.productName').removeAttr('disabled').val('');
            e.preventDefault();
         });
	},
    
    registerLineItemEvents : function() {
        this.registerQuantityChangeEvent();
        this.registerListPriceChangeEvent();
        this.registerPriceBookPopUp();
        this.registerLineItemTaxShowEvent();
        this.registerTaxTypeChange();
        this.registerCurrencyChangeEvent();
        this.registerLineItemDiscountShowEvent();
        
        this.registerFinalDiscountShowEvent();
        this.registerFinalDiscountChangeEvent();
        
        this.registerChargeBlockShowEvent();
		this.registerChargeBlockChangeEvent();
        
        this.registerGroupTaxShowEvent();
		this.registerGroupTaxChangeEvent();
        
        this.registerChargeTaxesShowEvent();
        this.registerChargeTaxesChangeEvent();
        
        this.registerDeductTaxesShowEvent();
        this.registerDeductTaxesChangeEvent();
        
        this.registerAdjustmentTypeChange();
        this.registerAdjustmentValueChange();
        
        this.registerRegionChangeEvent();
        this.registerDeleteLineItemEvent();
        
        this.registerClearLineItemSelection();
        var record = jQuery('[name="record"]').val();
        if (!record) {
            var container = this.lineItemsHolder;            
            jQuery('.qty',container).trigger('focusout');
        }
    },
    
    registerSubmitEvent : function () {
		var self = this;
		var editViewForm = this.getForm();
		//this._super();
		editViewForm.submit(function(e){
			var deletedItemInfo = jQuery('.deletedItem',editViewForm);
			if(deletedItemInfo.length > 0){
				e.preventDefault();
				var msg = app.vtranslate('JS_PLEASE_REMOVE_LINE_ITEM_THAT_IS_DELETED');
				app.helper.showErrorNotification({"message" : msg});
				editViewForm.removeData('submit');
				return false;
			}
            else if(jQuery('.lineItemRow').length<=0){  
 		        e.preventDefault();  
 		        msg = app.vtranslate('JS_NO_LINE_ITEM');  
				app.helper.showErrorNotification({"message" : msg});
 		        editViewForm.removeData('submit');  
 		        return false;
            }
			self.updateLineItemElementByOrder();
			var taxMode = self.isIndividualTaxMode();
			var elementsList = self.lineItemsHolder.find('.'+self.lineItemDetectingClass);
//			jQuery.each(elementsList, function(index, element) {
//				var lineItemRow = jQuery(element);
//				thisInstance.calculateDiscountForLineItem(lineItemRow);
//				if (taxMode) {
//					thisInstance.calculateTaxForLineItem(lineItemRow);
//				}
//				thisInstance.calculateLineItemNetPrice(lineItemRow);
//			});
			//thisInstance.lineItemToTalResultCalculations();
			self.saveProductCount();
			self.saveSubTotalValue();
			self.saveTotalValue();
			self.savePreTaxTotalValue();
			return true;
		})
	},
    
    makeLineItemsSortable : function() {
		var self = this;
		this.lineItemsHolder.sortable({
			'containment' : this.lineItemsHolder,
			'items' : 'tr.'+this.lineItemDetectingClass,
			'revert' : true,
			'tolerance':'pointer',
			'helper' : function(e,ui){
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function(index,element){
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			}
		}).mousedown(function(event){
			//TODO : work around for issue of mouse down even hijack in sortable plugin
			self.getClosestLineItemRow(jQuery(event.target)).find('input:focus').trigger('focusout');
		});
	},
    
    /**
	 * Function to register event for copying addresses
	 */
	registerEventForCopyAddress : function(){
		var self = this;
		jQuery('[name="copyAddressFromRight"],[name="copyAddressFromLeft"]').change(function(){
			var element = jQuery(this);
			var elementClass = element.attr('class');
			var targetCopyAddress = element.data('copyAddress');
			var objectToMapAddress;
			if(elementClass == "accountAddress"){
				var recordRelativeAccountId = jQuery('[name="account_id"]').val();
				if(recordRelativeAccountId == "" || recordRelativeAccountId == "0"){
					app.helper.showErrorNotification({'message':app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS')});
				} else {
					var recordRelativeAccountName = jQuery('#account_id_display').val();
					var data = {
						'record' : recordRelativeAccountId,
						'selectedName' : recordRelativeAccountName,
						'source_module': "Accounts"
					}
					if(targetCopyAddress == "billing"){
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsBillMap'];
					} else if(targetCopyAddress == "shipping"){
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsShipMap'];
					}
					self.copyAddressDetails(data,element.closest('table'),objectToMapAddress);
					element.attr('checked','checked');
				}
			}else if(elementClass == "contactAddress"){
				var recordRelativeContactId = jQuery('[name="contact_id"]').val();
				if(recordRelativeContactId == "" || recordRelativeContactId == "0"){
					app.helper.showErrorNotification({'message':app.vtranslate('JS_PLEASE_SELECT_AN_RELATED_TO_COPY_ADDRESS')});
				} else {
					var recordRelativeContactName = jQuery('#contact_id_display').val();
					var editViewLabel = jQuery('#contact_id_display').closest('td');
					var editViewSelection = jQuery(editViewLabel).find('input[name="popupReferenceModule"]').val();
					var data = {
						'record' : recordRelativeContactId,
						'selectedName' : recordRelativeContactName,
						source_module: editViewSelection
					}
					
					if(targetCopyAddress == "billing"){
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection+'BillMap'];
					} else if(targetCopyAddress == "shipping"){
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection+'ShipMap'];
					}
					self.copyAddressDetails(data,element.closest('table'),objectToMapAddress);
					element.attr('checked','checked');
				}
			} else if(elementClass == "shippingAddress"){
				var target = element.data('target');
				if(target == "shipping"){
					var swapMode = "true";
				}
				self.copyAddress(swapMode);
			} else if(elementClass == "billingAddress"){
				var target = element.data('target');
				if(target == "billing"){
					var swapMode = "false";
				}
				self.copyAddress(swapMode);
			}
		})
		jQuery('[name="copyAddress"]').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var swapMode;
			var target = element.data('target');
			if(target == "billing"){
				swapMode = "false";
			}else if(target == "shipping"){
				swapMode = "true";
			}
			self.copyAddress(swapMode);
		})
	},
    
    /**
	 * Function to toggle shipping and billing address according to layout
	 */
	registerForTogglingBillingandShippingAddress : function(){
		var billingAddressPosition = jQuery('[name="bill_street"]').closest('td').index();
		var copyAddress1Block = jQuery('[name="copyAddress1"]');
		var copyAddress2Block = jQuery('[name="copyAddress2"]');
		var copyHeader1 = jQuery('[name="copyHeader1"]');
		var copyHeader2 = jQuery('[name="copyHeader2"]');
		var copyAddress1toggleAddressLeftContainer = copyAddress1Block.find('[name="togglingAddressContainerLeft"]');
		var copyAddress1toggleAddressRightContainer = copyAddress1Block.find('[name="togglingAddressContainerRight"]');
		var copyAddress2toggleAddressLeftContainer = copyAddress2Block.find('[name="togglingAddressContainerLeft"]')
		var copyAddress2toggleAddressRightContainer = copyAddress2Block.find('[name="togglingAddressContainerRight"]');
		var headerText1 = copyHeader1.html();
		var headerText2 = copyHeader2.html();

		if(billingAddressPosition == 3){
            if(copyAddress1toggleAddressLeftContainer.hasClass('hide')){
                copyAddress1toggleAddressLeftContainer.removeClass('hide');
            }
            copyAddress1toggleAddressRightContainer.addClass('hide');
            if(copyAddress2toggleAddressRightContainer.hasClass('hide')){
                copyAddress2toggleAddressRightContainer.removeClass('hide');
            }
            copyAddress2toggleAddressLeftContainer.addClass('hide');
            copyHeader1.html(headerText2);
            copyHeader2.html(headerText1);
            copyAddress1Block.find('[data-copy-address]').each(function(){
                jQuery(this).data('copyAddress','shipping');
            })
            copyAddress2Block.find('[data-copy-address]').each(function(){
                jQuery(this).data('copyAddress','billing');
            })
        }
    },
    
    registerLineItemAutoComplete : function(container) {
		var self = this;
		if(typeof container == 'undefined') {
			container = this.lineItemsHolder;
		}
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
				self.searchModuleNames(params).then(function(data){
					var reponseDataList = new Array();
					var serverDataFormat = data;
					if(serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label' : app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type'  : 'no results'
						});
					}
					for(var id in serverDataFormat){
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select' : function(event, ui ){
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if(typeof selectedItemData.type != 'undefined' && selectedItemData.type=="no results"){
					return false;
				}
				var element = jQuery(this);
				element.attr('disabled','disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
				var popupElement = tdElement.find('.lineItemPopup');
				var dataUrl = "index.php?module=Inventory&action=GetTaxes&record="+selectedItemData.id+"&currency_id="+jQuery('#currency_id option:selected').val()+"&sourceModule="+app.getModuleName();
				app.request.get({'url':dataUrl}).then(
					function(error, data){
                        if(error == null) {
                            var itemRow = self.getClosestLineItemRow(element)
                            itemRow.find('.lineItemType').val(selectedModule);
                            self.mapResultsToFields(itemRow, data[0]);
                        }
					},
					function(error,err){

					}
				);
			},
			'change' : function(event, ui) {
				var element = jQuery(this);
				//if you dont have disabled attribute means the user didnt select the item
				if(element.attr('disabled')== undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
//		}).each(function() {
//			jQuery(this).data('autocomplete')._renderItem = function(ul, item) {
//				var term = this.element.val();
//				var regex = new RegExp('('+term+')', 'gi');
//				var htmlContent = item.label.replace(regex, '<b>$&</b>');
//				return jQuery('<li></li>').data('item.autocomplete', item).append(jQuery('<a></a>').html(htmlContent)).appendTo(ul);
//			};
		});
	},
    
    /**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		var self = this;

		jQuery('input[name="contact_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			self.referenceSelectionEventHandler(data, container);
		});
	},

	/**
	 * Reference Fields Selection Event Handler
	 */
	referenceSelectionEventHandler : function(data,container){
		var self = this;
		if (data['selectedName']) {
			var message = app.vtranslate('OVERWRITE_EXISTING_MSG1')+app.vtranslate('SINGLE_'+data['source_module'])+' ('+data['selectedName']+') '+app.vtranslate('OVERWRITE_EXISTING_MSG2');
			app.helper.showConfirmationBox({'message' : message}).then(
			function(e) {
				self.copyAddressDetails(data, container);
			},
			function(error, err){
			});
		}
	},
    
    registerPopoverCancelEvent : function() {
        this.getForm().on('click','.popover .popoverCancel', function(e){
            e.preventDefault();
            var element = jQuery(e.currentTarget);
            var popOverEle = element.closest('.popover');
			var validate = popOverEle.find('input').valid();
			if (!validate) {
				popOverEle.find('.input-error').val(0).valid();
			}
			popOverEle.css('opacity',0).css('z-index','-1');

        });
    },
    registerBasicEvents: function(container){
        this._super(container);
        this.registerAddProductService();
        this.registerProductAndServiceSelector();
        this.registerLineItemEvents();
        this.checkLineItemRow();
        this.registerSubmitEvent();
        this.makeLineItemsSortable();
        this.registerLineItemAutoComplete();
        this.registerReferenceSelectionEvent(this.getForm());
        this.registerPopoverCancelEvent();
    },
});
    

