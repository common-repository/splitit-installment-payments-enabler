( function( $ ) { 
    "use strict";

    /*****************************************************************************************************************
     * Main logic
     *****************************************************************************************************************/

        //resetting cookies
   /* setCookie('splitit_ec_session_id', 0);
    setCookie('splitit_button_loaded', 0);
    setCookie('splitit_validation_passed', 0);
    //setCookie('splitit_checkout', 0);
    setCookie('splitit_checkout_session_id_data',0);*/

    //on Place Order button:
    // - validate checkout fields
    // - init EcSession if valid
    // - insert Splitit button if session initialized
    $(document).on('click', '#place_order', function(e){
        if($('#payment_method_splitit').is(':checked')) {
            e.preventDefault();
            validateFields();
        }
    });
    $(document).ready(function($){
        $(document.body).on('change', 'input[name="payment_method"]', function() {
            $('body').trigger('update_checkout');
        });

        $('#calc_shipping_country').change(function() {
            var countryList = $(this).val();
            var countriesWithoutPostCode = ['AE', 'AF', 'AG', 'AI', 'AL', 'AN', 'AO', 'AW', 'BB', 'BF', 'BH', 'BI', 'BJ', 'BM', 'BO', 'BS',
                'BT', 'BW', 'BZ', 'CD', 'CF', 'CG', 'CI', 'CK', 'CL', 'CM', 'CO', 'CR', 'CV', 'DJ', 'DM', 'DO', 'EC',
                'EG', 'ER', 'ET', 'FJ', 'FK', 'GA', 'GD', 'GH', 'GI', 'GM', 'GN', 'GQ', 'GT', 'GW', 'GY', 'HN', 'HT',
                'IE', 'IQ', 'IR', 'JM', 'JO', 'KE', 'KH', 'KI', 'KM', 'KN', 'KP', 'KW', 'KY', 'LA', 'LB', 'LC', 'LK',
                'LR', 'LS', 'LY', 'ML', 'MM', 'MO', 'MR', 'MS', 'MT', 'MU', 'MW', 'MY', 'MZ', 'NA', 'NE', 'NG', 'NI',
                'NP', 'NR', 'NU', 'OM', 'PA', 'PE', 'PF', 'PY', 'QA', 'RW', 'SA', 'SB', 'SC', 'SD', 'SL', 'SN', 'SO',
                'SR', 'SS', 'ST', 'SV', 'SY', 'TC', 'TD', 'TG', 'TL', 'TO', 'TR', 'TT', 'TV', 'TZ', 'UG', 'UY', 'VC',
                'VE', 'VG', 'VN', 'VU', 'WS', 'XA', 'XB', 'XC', 'XE', 'XL', 'XM', 'XN', 'XS', 'YE', 'ZM', 'ZW'];
            if ($.inArray(countryList, countriesWithoutPostCode) !== -1) {
                $('#calc_shipping_city').show();
            } else {
                $('#calc_shipping_city').val('').hide();
            }
        });
    });

    var decodeHTML = function (html) {
        var txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };
    $(document).ready(function(){
        var elem = jQuery('.order_details').find(':contains(payment-title-checkout)').closest('td');
        if(elem != undefined){
            elem.html(decodeHTML(elem.html()));
        }
    });

    function initEcSession() {
        var fields = getFormFields();

        $.ajax({
            url: '?wc-api=splitit_scripts_on_checkout',
            type: 'post',
            data: fields,
            success: function (data) {
                if(data.CheckoutUrl) {
                    window.location.href = data.CheckoutUrl;
                } else {
                    if(data.message) {
                        alert(data.message);
                        location.reload(true);
                    }
                    if(data.error.message) {
                        alert(data.error.message);
                       location.reload(true);
                    }
                    location.reload(true);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                 
                if(errorThrown=="Internal Server Error"){
                    alert('Failed to connect splitit payment gateway. Please check settings.');
                }else{
                    alert('err: ' + textStatus + ', errorThrown: ' + errorThrown);
                }
               //location.reload(true);
            }
        });
    }



    function validateFields() {
        //disable button to avoid double sending
        $('#place_order').attr('disabled', true);
        var fields = getFormFields();
        $.ajax({
            url: '?wc-api=splitit_checkout_validate',
            type: 'post',
            data: fields,
            success: function (data) {
                $('.woocommerce-error, .woocommerce-message').remove();

                // Check for error
                if ('failure' == data.result) {
                    var $form = $('form.woocommerce-checkout');

                    // Add new errors
                    if (data.messages) {
                        $form.prepend('<ul class="woocommerce-error">' + data.messages + '</ul>');
                    } else {
                        $form.prepend('<ul class="woocommerce-error">' + data + '</ul>');
                    }

                    // Lose focus for all fields
                    $form.find('.input-text, select').blur();

                    // Scroll to top
                    $('html, body').animate({
                        scrollTop: ( $('form.woocommerce-checkout').offset().top - 100 )
                    }, 1000);

                   // setCookie('splitit_validation_passed', 0);
                    $('#place_order').attr('disabled', false);
//                    return; //stop further processing

                } else if ('success' == data.result) {
                    $('#place_order').val('Loading Splitit...');
                    
                    saveFieldsToCookie();
                    initEcSession();
                  //  setCookie('splitit_validation_passed', 1);
                } else {
                    alert('Error occured, please try again later');
                    $('#place_order').attr('disabled', false);
                }
                $( document.body ).trigger( 'update_checkout' );
            },

            error: function (jqXHR, textStatus, errorThrown) {
                $('.woocommerce-error, .woocommerce-message').remove();
                wc_checkout_form.$checkout_form.prepend('<div class="woocommerce-error">' + errorThrown + '</div>');
                wc_checkout_form.$checkout_form.removeClass('processing').unblock();
                wc_checkout_form.$checkout_form.find('.input-text, select').blur();
                $('html, body').animate({
                    scrollTop: ( $('form.checkout').offset().top - 100 )
                }, 1000);
            }
        });
    }

    /*****************************************************************************************************************
     * Helper functions
     *****************************************************************************************************************/

    /**
     * Formatting checkout form fields data, to pass to validation
     * @returns {{}}
     */
    function getFormFields() {
        var field_blocks = $('form.woocommerce-checkout .validate-required,#ship-to-different-address,#terms,#billing_city_field');
        var fields = {};
        field_blocks.each(function() {
            if ($(this).prop('id') == 'account_password_field') {
                return true;
            }
            if ($(this).prop('id') == 'account_username_field') {
                 if(!$("#createaccount").is(':checked')){
                    return true;
                 }
                
            }
            if($(this).closest("#payment").attr('id')=="payment"){   
                return true;
            }
            //billing custom
            if ($(this).prop('id') == 'billing_country_field') {
                var elem = $('#billing_country').val();
            } else if($(this).prop('id') == 'billing_state_field') {
                //state can be input or select
                if($('#billing_state').hasClass('input-text')) {
                    var elem = $('#billing_state').val();
                } else {
                    var elem = $('#billing_state option:selected').val();
                }
                if(elem == 'undefined') { elem = ''; }

                //shipping custom
            } else if ($(this).prop('id') == 'ship-to-different-address') {
                var elem = 0;
                if($(this).find('input.input-checkbox').is(':checked')) {
                    elem = 1;
                }
                //terms custom
            } else if ($(this).prop('id') == 'terms') {
                var elem = 0;
//                alert('terms is checked ='+$(this).is(':checked'));
                if($(this).is(':checked')) {
                    elem = 1;
                }
            } else if ($(this).prop('id') == 'shipping_country_field') {
                var elem = $('#shipping_country').val();
                if(!$('#shipping_country') || $('#shipping_country').val() == 'undefined') {
                    var elem = '';
                }
            } else if ($(this).prop('id') == 'shipping_state_field') {
                //state can be input or select
                if($('#shipping_state').hasClass('input-text')) {
                    var elem = $('#shipping_state').val();
                } else {
                    var elem = $('#shipping_state option:selected').val();
                }
                if(elem == 'undefined') { elem = ''; }

                //default behaviour
            } else {
                var self = this,
                    elem;

                ['select', 'textarea', 'input.input-text', 'input.input-radio:checked','input.input-checkbox:checked'].forEach(function(type) {
                    if (elem === undefined) {
                        elem = $(self).find(type).val();
                    }
                });
            }

            if ($(this).prop('id') == 'billing_city_field' && !elem) {
                elem = $('#billing_country option:selected').text();
            }

            var label = $(this).find('label:first').text();
            //if($.trim(elem) == '' || !$(this).hasClass('woocommerce-validated')) {
            if($(this).prop('id') == 'terms') {
                label = 'Terms';
            } else {
                label = $.trim(label.replace('*',''));
                label = $.trim(label.replace('?',''));
            }
            fields[$(this).prop('id')] = [label, elem];
            //}
        });
        if($('[name="terms-field"]').val()){
            fields['terms-field'] = 1;
        }
        if ($('#terms').is(':checked')) {
//            alert('terms is checked ='+$('#terms').is(':checked'));
            fields['terms'] = 1;
        }
        return fields;
    }

    function saveFieldsToCookie() {
        var post_data = $('form.woocommerce-checkout').serialize();
        setCookie('splitit_checkout', post_data);
    }

    function setCookie(name, value, expires, path, theDomain, secure) {
        var expires = "";
        if (expires) {
            var date = new Date();
            date.setTime(date.getTime() + (expires*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function getCookie(Name) {
        var search = Name + "="
        if (document.cookie.length > 0) { // if there are any cookies
            var offset = document.cookie.indexOf(search)
            if (offset != -1) { // if cookie exists
                offset += search.length
                // set index of beginning of value
                var end = document.cookie.indexOf(";", offset)
                // set index of end of cookie value
                if (end == -1) end = document.cookie.length
                return /*unescape(*/document.cookie.substring(offset, end)/*) */
            }
        }
    }



})(jQuery);
