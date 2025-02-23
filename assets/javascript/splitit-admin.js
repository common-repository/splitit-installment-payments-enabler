( function( $ ) {
	"use strict";
 

	function define_custom_url(){
		var customURLselect = $('#woocommerce_splitit_custom_urls').val(); 
		if(customURLselect == 'default'){
			$(".custom_urls").closest('tr').hide(); 
		}else{
			$(".custom_urls").closest('tr').show();
		}
	}

	var productListInterval = setInterval(function(){
	  var prodList = document.getElementById('woocommerce_splitit_splitit_product_sku_list');
	  if((typeof prodList != 'undefined')&&prodList){
	    clearInterval(productListInterval);
	    jQuery(document).on('click','.close-btn-prod-list',function(){
	        console.log('prod remove clicked');
	        var inputPadding  = jQuery(prodList).css('padding-left'),
	            widthLastItem = jQuery('.selected-item-conatiner .search-item-box:last-of-type').outerWidth();
	        var $elemId = jQuery('#'+jQuery(prodList).attr('id')+'_prodlist');
	        var prodId = jQuery(this).parent().attr('data-proid');
	        var terms = split( $elemId.val() );
	        terms = terms.filter(function(v){return v!==''});
	        var index = terms.indexOf(prodId);
	        if (index > -1) {
	          terms.splice(index, 1);
	        }
	        $elemId.val(terms.join(","));
	        jQuery(this).parent().remove();
	    });
	    autoPopulateProds(prodList);
	    autoCompleteWizard(prodList);
	  }
	},2000);

	function split( val ) {
	      return val.split( /,\s*/ );
	}
	function extractLast( term ) {
	  return split( term ).pop();
	}

	function autoPopulateProds(prodList){
	  var prodIds = jQuery(prodList).val();
	  jQuery.ajax({
	    url: ajaxurl,
	    data: {isAjax: 1, prodIds: prodIds, action: 'fetch_prods'},
	    type: 'POST',
	    dataType: 'json',
	    success: function(result){
	      console.log(result);
	      result.forEach(function(ash){
	        jQuery('<div class="search-item-box" title="'+ash.label+'" data-proid="'+ash.value+'">'
	          +ash.label+'<span class="close-btn-prod-list"></span</div>')
	        .appendTo('.selected-item-conatiner');
	      });
	    }
	  });
	  jQuery(prodList).val('');
	}

	function autoCompleteWizard(prodList){
	  var gutterWidth = 8, itemPerColumn = 4;
	  var $prod = jQuery(prodList);
	  $prod.attr('placeholder','Product Name/SKU');
	  $prod.wrapAll('<div class="ui-widget-prod-list"></div>');
	  var eleHtml = $prod.parent().html();
	  var textId = $prod.attr('id');
	  $prod.attr('name','').parent().append('<div class="selected-item-conatiner"></div>').append(jQuery(eleHtml).attr('type','hidden').attr('id',textId+"_prodlist"));
	  $prod.on( "keydown", function( event ) {
	    if ( event.keyCode === jQuery.ui.keyCode.TAB &&
	        jQuery( this ).autocomplete().data("uiAutocomplete").menu.active ) {
	      event.preventDefault();
	    }
	  })
	  .autocomplete({
	    minLength: 3,
	    source: function( request, response ) {
	      jQuery.getJSON( ajaxurl, {
	        term: extractLast( request.term ),
	        action: 'fetch_prods'
	      }, response );
	    },
	    search: function() {
	      // custom minLength
	      var term = extractLast( this.value );
	      if ( term.length < 3 ) {
	        return false;
	      }
	    },
	    focus: function() {
	      // prevent value inserted on focus
	      return false;
	    },
	    select: function( event, ui ) {
	      var terms = split( jQuery('#'+textId+"_prodlist").val() );
	      terms = terms.filter(function(v){return v!==''});
	      // remove the current input
	      // terms.pop();
	      // add the selected item
	      // terms.push( ui.item.value );
	      if(jQuery.inArray(ui.item.value,terms)==-1){
	        terms.push( ui.item.value );
	        var itemBoxWidth = jQuery(this).outerWidth() / itemPerColumn;
	        jQuery('<div class="search-item-box" title="'+ui.item.label+'" data-proid="'+ui.item.value+'">'+ui.item.label+'<span class="close-btn-prod-list"></span</div>')
	        .appendTo('.selected-item-conatiner');
	      }
	        jQuery('.ui-helper-hidden-accessible').text('');
	      // add placeholder to get the comma-and-space at the end
	      // terms.push( "" );
	      // this.value = terms.join( ", " );
	      jQuery('#'+textId+"_prodlist").val(terms.join(","));
	      this.value = '';      
	      return false;
	    }
	  });
	}

	$(document).ready(function() {
		
		var disTypeVal = $('#woocommerce_splitit_splitit_discount_type').val(); 
		if(disTypeVal == 'depending_on_cart_total'){
			$("#main_ct_container").show(); 
			$("#woocommerce_splitit_splitit_discount_type_fixed").closest('tr').hide();
		}else{
			$("#main_ct_container").hide(); 
			$("#woocommerce_splitit_splitit_discount_type_fixed").closest('tr').show();
		}
		define_custom_url();
		/*option to change discount type*/
		$("#woocommerce_splitit_custom_urls").on("change",function(){
			define_custom_url();
		});
		/*option to change discount type*/

		/*show field 3d amount when enable*/
		$("#woocommerce_splitit_splitit_3d_secure").on("change",function(){
			if($(this).val()=='yes'){
				$('#woocommerce_splitit_splitit_3d_secure_min_amount').closest('tr').show();
			} else {
				$('#woocommerce_splitit_splitit_3d_secure_min_amount').closest('tr').hide();		
			}
		});
		$("#woocommerce_splitit_splitit_3d_secure").trigger('change');
		/*show field order percent when percent*/
		$("#woocommerce_splitit_splitit_first_installment").on("change",function(){
			if($(this).val()=='percent'){
				$('#woocommerce_splitit_splitit_first_installment_percent').closest('tr').show();
			} else {
				$('#woocommerce_splitit_splitit_first_installment_percent').closest('tr').hide();				
			}
		});
		$("#woocommerce_splitit_splitit_first_installment").trigger('change');
		

		$('#checkApiCredentials').on('click', function(e) {
			e.preventDefault();
			var $this = $(this);
			$this.closest('tr').find('td').append('<div class="lds-dual-ring"></div>');
			var data = {
				'action': 'splitit_check_api_credentials',
			
				// We pass php values differently!
			};
			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			jQuery.post(ajaxurl, data, function(response) {
				$this.closest('tr').find('td').find('.lds-dual-ring').remove();
				alert(response);
			});
		});


	/*option to change discount type*/
		$("#woocommerce_splitit_splitit_discount_type").on("change",function(){
			var selected_option = $(this).val();
			 
			if(selected_option == "depending_on_cart_total"){
				$("#woocommerce_splitit_splitit_discount_type_fixed").closest('tr').hide();
				$("#main_ct_container").show(); 
			}else{
				$("#woocommerce_splitit_splitit_discount_type_fixed").closest('tr').show();
				$("#main_ct_container").hide(); 
			}
		})	
	/*option to change discount type*/ 

		$('form#mainform').on('submit', function(event){

			var flag1 = 0;
			var flag2 = 0;
			var percentageFlag = 0;
			var overlaps = 0;
			var fromBigger = 0;
			var hasGap = 0;

			var disTypeVal = $('#woocommerce_splitit_splitit_discount_type').val();
			 
			if(disTypeVal == 'fixed'){
				var count = $("#woocommerce_splitit_splitit_discount_type_fixed :selected").length; 
				 
				if(count == 0){
					$("#woocommerce_splitit_splitit_discount_type_fixed").css("border","1px solid red");
					$('html, body').animate({
				        scrollTop: $("#woocommerce_splitit_splitit_discount_type_fixed").offset().top
				    }, 2000);
					return false;	
				}else{
					$("#woocommerce_splitit_splitit_discount_type_fixed").css("border","1px solid #ddd");
				}
				
			}

			// validation for depanding on cart 
	      	if(jQuery('#woocommerce_splitit_splitit_discount_type').val() == 'depending_on_cart_total'){
				var fromToArr = {};
				var i=0;
				var tempI = 0;
				var tempFromToArr = {};
				jQuery("#tier_price_container tr.ct_tr").each(function(){ 
					var temp_doctv_from = jQuery(this).find(".doctv_from").val();
					var temp_doctv_to = jQuery(this).find(".doctv_from").val();
					var temp_installmentsCount = jQuery(this).find("select.doctv_installments  :selected").length;


					// This logic is created for allow the Last empty fields, in this we are created an objects of values either empty or not. 
					tempFromToArr[tempI] = {};
					if((temp_doctv_from == "" || isNaN(temp_doctv_from)) && (temp_doctv_to == "" || isNaN(temp_doctv_to))){
						// do nothing
					}else{
						tempFromToArr[tempI]["from"] = parseFloat(temp_doctv_from);
						tempFromToArr[tempI]["to"] = parseFloat(temp_doctv_to);	
						tempFromToArr[tempI]["installmentsCount"] = parseFloat(temp_installmentsCount);	
					}
					


					tempI++;
				});
				
				// traverse array top to bottom, delete empty fields before found filled values
				var foundEmpty = 1;
				for(var fl = Object.keys(tempFromToArr).length; fl >= 1; fl--){
					if(jQuery.isEmptyObject(tempFromToArr[fl-1]) && foundEmpty == 1){
						delete tempFromToArr[fl-1]; 
					}else{
						foundEmpty = 0;
					}
				}
				


				jQuery("#tier_price_container tr.ct_tr").each(function(){ 

					if(i >= Object.keys(tempFromToArr).length){
						// do nothing
					}else{
	        	
						var doctv_from = parseFloat(jQuery(this).find(".doctv_from").val());
						var doctv_to = parseFloat(jQuery(this).find(".doctv_to").val());
						jQuery(this).find(".doctv_from").css("border","1px solid #ccc");
						jQuery(this).find(".doctv_to").css("border","1px solid #ccc");
						jQuery(this).find("select.doctv_installments").css("border","1px solid #ccc");
						//alert(doctv_from+"--"+doctv_to);

						// validation for installments
						var installmentsCount = jQuery(this).find("select.doctv_installments  :selected").length;
						
						if(installmentsCount == 0){
							jQuery(this).find("select.doctv_installments").css("border-color","red");
							flag1++; 
						}

						// validation for from and to amount
						if((doctv_from == "" || isNaN(doctv_from)) && (doctv_to == "" || isNaN(doctv_to))){
							// all empty and string
							jQuery(this).find(".doctv_from").css("border","1px solid red");
							jQuery(this).find(".doctv_to").css("border","1px solid red");
							flag1++; 
						}else if(doctv_from != "" || isNaN(doctv_from)){
							if( doctv_to == "" || isNaN(doctv_to)){
								// check from less than 1000 and to is empty
								if(doctv_from < 1000){
									jQuery(this).find(".doctv_to").css("border","1px solid red");
									flag1++; 
								}
							}
							if(doctv_from == "" || isNaN(doctv_from)){
								// when from empty
								jQuery(this).find(".doctv_from").css("border"," 1px solid red");
								flag1++; 
							}  
						}

						//  validation that there are no overlaps with the periods
						fromToArr[i] = {};
						fromToArr[i]["from"] = doctv_from;
						fromToArr[i]["to"] = doctv_to;

						if(flag1 == 0 && Object.keys(fromToArr).length > 1){
							for(var j=0; j<Object.keys(fromToArr).length-1; j++){
								if((doctv_from >= fromToArr[j]["from"] && 
									doctv_from <= fromToArr[j]["to"]) || 
									(doctv_to >= fromToArr[j]["from"] && 
									doctv_to <= fromToArr[j]["to"]) ){
									console.log("forrrr");
									jQuery(this).find(".doctv_from").css("border","1px solid red");
									jQuery(this).find(".doctv_to").css("border","1px solid red");
									flag1++;
									overlaps++;
								}

								// check if there is gap between previous to and next from
								if((fromToArr[j]["to"]+1) != fromToArr[j+1]["from"]){
									jQuery(this).find(".doctv_from").css("border","1px solid red");
									jQuery(this).find(".doctv_to").css("border","1px solid red");
									flag1++;
									hasGap++;  
								}  
							}
						}

			        	i++;

						// check if from is bigger than to
						if(doctv_from > doctv_to){
							jQuery(this).find(".doctv_from").css("border","1px solid red");
							jQuery(this).find(".doctv_to").css("border","1px solid red");  
							fromBigger++;
						}
					} 
	        	});
	      	}  

			if(flag1 == 0){   
				return true;
			}else{
				if(fromBigger){
					alert("From amount should be lesser than To.");
				}else if(overlaps){
					alert("From and To amount should not Overlap"); 
				}else if(hasGap){
					alert("There should not be Gap between To and From amounts.");
				}else{
					alert('Please fill the required fields in Splitit section "Depending on cart total"');  
				}
		        
		        $('html, body').animate({
			        scrollTop: $("#tier_price_container").offset().top
			    }, 2000);

		        return false;
	      	} 
	    }); 
	}); 

})(jQuery);
