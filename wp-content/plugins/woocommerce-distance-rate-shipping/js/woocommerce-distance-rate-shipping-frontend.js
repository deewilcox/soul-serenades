jQuery(document).ready(function($) {

    function updateMap(address) {
        $('#customer-map').hide(1000);
        $('#customer-map').remove();
        var htmlForMap = '<iframe id="customer-map" width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=' + address + '&amp;output=embed"></iframe>';
        $('.shipping-calculator-form, form.shipping_calculator').first().append(htmlForMap);
        $('#customer_details, #customer-details, #checkout-accordion').after(htmlForMap);
        $('#customer-map').show(1000);
    }

    var map = null;
    var directionsService = null;
    var directionsDisplay = null;
    function updateMapWithRoute(storeAddress, address) {
        $('#customer-map').hide().remove();
        if (distance_rate_shipping_settings.showRouteMap != 'yes') {
            updateMap(address);
            return;
        }
        if ($('#store-route-map').length == 0) {
            $('#customer_details, #customer-details, #checkout-accordion, .shipping_calculator, .shipping-calculator, .shipping-calculator-form, .shipping_calculator_form').after('<div id="store-route-map"></div>');
        }
        if (map == null) {
            var mapOptions = {
                zoom: 12,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            map = new google.maps.Map(document.getElementById('store-route-map'), mapOptions);
        }
        if (directionsService == null) {
            directionsService = new google.maps.DirectionsService();
        }
        if (directionsDisplay == null) {
            directionsDisplay = new google.maps.DirectionsRenderer();
        }
        directionsDisplay.setMap(map);
        var request = {
            origin: storeAddress,
            destination: address,
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
            }
        });
    }

//opening hours
    if (distance_rate_shipping_settings.delivery_times) {
        $('#order_review_heading').before('<h2>'+distance_rate_shipping_settings.delivery_time+'</h2><p>'+distance_rate_shipping_settings.select_delivery_time+'</p><span id="checkoutselect">' + distance_rate_shipping_settings.distanceopeninghours + '</span>');
        $('.calculated_shipping.cart_totals table').first().prepend('<h3>'+distance_rate_shipping_settings.delivery_time+'</h3><p class="form-row form-row-wide"><span id="checkoutselect">' + distance_rate_shipping_settings.distanceopeninghours + '</span></p>');
    }
    //delivery date
    if (distance_rate_shipping_settings.delivery_dates) {
        $('#order_review_heading').before('<h2>'+distance_rate_shipping_settings.delivery_date+'</h2><p>'+distance_rate_shipping_settings.select_delivery_date+'</p><span id="deliverydate"><input type="text" class="deliverydate" value="' + distance_rate_shipping_settings.selected_delivery_date + '" /></span>');
        $('.shipping-calculator-form').first().prepend('<p class="form-row form-row-wide"><span id="deliverydate"><input type="text" class="deliverydate" value="' + distance_rate_shipping_settings.selected_delivery_date + '" placeholder="'+distance_rate_shipping_settings.delivery_date+'" /></span></p>');
    }

    $('.deliverydate').datepicker();

    $(document).on('change', '#distanceopeninghours', function() {
        $('button[type=submit]').attr('disabled', 'disabled');
        jQuery.post(distance_rate_shipping_settings.ajaxurl,
                {
                    'action': 'change_select',
                    'value': $('#distanceopeninghours').val()
                },
        function(response) {
            $('button[type="submit"]').removeAttr('disabled');
            $('.address-field select').change();
        }
        );
    });

    $(document).on('change', '#deliverydate', function() {
        $('button[type=submit]').attr('disabled', 'disabled');
        jQuery.post(distance_rate_shipping_settings.ajaxurl,
                {
                    'action': 'change_delivery_date',
                    'value': $('#deliverydate input.deliverydate').val()
                },
        function(response) {
            $('button[type="submit"]').removeAttr('disabled');
            $('.address-field select').change();
        }
        );
    });
//other stuff

    var distanceService = null;
    var addressFound = false;
    if ($('form.shipping_calculator').length > 0 || $('.shipping-calculator-form').length>0 || $('form.shipping-calculator').length > 0 || $('form.checkout').length > 0) {
  //When the shipping updates, recalculate the distances to the stores
        function calculateLocationInfo(address, form, checkout) {
            var stores = new Array();
            var storeIds = new Array();
            $.each(distance_rate_shipping_settings.stores, function(storeId, storeAddress) {
                stores.push(storeAddress);
                storeIds.push(storeId);
            });
            $('.distance-hidden').remove();
            addressFound = false;
            if (distanceService == null)
                distanceService = new google.maps.DistanceMatrixService();
            distanceService.getDistanceMatrix(
                    {
                        origins: stores,
                        destinations: [address],
                        travelMode: google.maps.TravelMode.DRIVING,
                        avoidHighways: false,
                        avoidTolls: false
                    }, function(response) {
                $('.address-error').remove();
                var store_id = 0;
                var storeAddress = '';
                var storeDataForAjax = {};
                if (response != null) {
                    $.each(response.rows, function(index, value) {
                        var distance = 9999999999;
                        if (value.elements[0].status == 'OK') {
                            addressFound = true;
                            distance = parseInt(value.elements[0].distance.value) / 1000;
                        }
                        store_id = storeIds[index];
                        storeAddress = stores[index];
                        if (checkout) {
                            form.append('<input class="distance-hidden checkout-distance" type="hidden" value="' + distance + '" name="' + store_id + '" />')
                        } else {
                            form.append('<input class="distance-hidden" type="hidden" value="' + distance + '" name="stores[' + store_id + ']" />')
                        }
                        storeDataForAjax[store_id] = distance;
                    });
                }
                $.post(distance_rate_shipping_settings.ajaxurl,
                        {
                            action: 'shipping_save_addresses',
                            stores: storeDataForAjax
                        },
                function(response) {
                    shippingCalculatorClicked = true;
                    if (!addressFound) {
                        updatingDistanceAlreadyStarted = false;
                        $('#customer_details, .shipping_calculator, .shipping-calculator, .shipping-calculator-form, .shipping_calculator_form').after('<p class="address-error" style="color:red">' + distance_rate_shipping_settings.address_error + '</p>');
                        $('#store-route-map').hide();
                    } else {
                        $('#store-route-map').show(0);
                        updateMapWithRoute(storeAddress, address);
                        $('.shipping-calculator-form input[type=submit], .shipping-calculator-form button[type=submit], form.shipping_calculator button[type="submit"], form.shipping_calculator input[type="submit"]').click();
                        inProgress = true;
                        //$('select.shipping_method').change();
                        $('body').trigger('update_checkout');
                    }
                });
            });
        }

        function getCartAddress() {
            var address = '';
            if ($('#calc_shipping_address').length > 0 && $('#calc_shipping_address').val().length > 0)
                address = address + $('#calc_shipping_address').val() + ', ';
            if ($('#calc_shipping_postcode').length > 0 && $('#calc_shipping_postcode').val().length > 0)
                address = address + $('#calc_shipping_postcode').val() + ', ';
            if ($('select#calc_shipping_state').length > 0 && $('#calc_shipping_state').val().length > 0) {
                var state = $('#calc_shipping_state option[value="' + $('#calc_shipping_state').val() + '"]').html();
                address = address + state + ', ';
            }
            if ($('input#calc_shipping_state').length > 0 && $('#calc_shipping_state').val().length > 0)
                address = address + $('#calc_shipping_state').val() + ', ';
            if ($('select#calc_shipping_country').length > 0 && $('#calc_shipping_country').val().length > 0) {
                var state = $('#calc_shipping_country option[value="' + $('#calc_shipping_country').val() + '"]').html();
                address = address + state + ', ';
            }
            if ($('input#calc_shipping_country').length > 0 && $('#calc_shipping_country').val().length > 0)
                address = address + $('#calc_shipping_country').val() + ', ';
            return address;
        }

        $('.shipping-calculator-form, form.shipping-calculator, form.shipping_calculator').change(function() {
            var address = getCartAddress();
        });

        if ($('.shipping-calculator-form input[type=submit], form.shipping_calculator button[type=submit], form.shipping_calculator button[type="submit"], form.shipping_calculator input[type="submit"]').length > 0) {
            updateMap(getCartAddress());
        }
        $('.shipping-calculator-form, form.shipping_calculator, form.shipping_calculator').find('input, select').change(
                function() {
                    updateMap(getCartAddress());
                });

        var inProgress = false;
        var updatingDistanceAlreadyStarted = false;
        var shippingCalculatorClicked = false;
        if (distance_rate_shipping_settings.need_road_distance) {
            $('.shipping-calculator-form input[type=submit], .shipping-calculator-form button[type=submit], form.shipping_calculator button[type=submit],  form.shipping_calculator button[type=submit], form.shipping_calculator button[type="submit"], form.shipping_calculator input[type="submit"]').click(function() {
                if (!shippingCalculatorClicked) {
                    var form = $(this).closest('form');
                    var address = getCartAddress();
                    calculateLocationInfo(address, form, false);
                    return false;
                }
                shippingCalculatorClicked = false;
                return addressFound;
            });
            $.ajaxSetup({
                beforeSend: function(jqXHR, settings) {
                    if (typeof (settings.data) !== 'undefined' && !inProgress && !updatingDistanceAlreadyStarted && settings.data.indexOf("action=woocommerce_update_order_review") >= 0) {
                        updatingDistanceAlreadyStarted = true;
                        $('.checkout-distance').remove();
                        var address = '';
                        var billing = 'billing';
                        if ($('#shiptobilling-checkbox').length > 0 && !$('#shiptobilling-checkbox').is(':checked')) {
                            billing = 'shipping';
                        }
                        if ($('#ship-to-different-address-checkbox').is(':checked')) {
                            billing = 'shipping';
                        }
                        if ($('#' + billing + '_address_1').length > 0 && $('#' + billing + '_address_1').val().length > 0)
                            address = address + $('#' + billing + '_address_1').val() + ', ';
                        if ($('#' + billing + '_address_2').length > 0 && $('#' + billing + '_address_2').val().length > 0)
                            address = address + $('#' + billing + '_address_2').val() + ', ';
                        if ($('#' + billing + '_city').length > 0 && $('#' + billing + '_city').val().length > 0)
                            address = address + $('#' + billing + '_city').val() + ', ';
                        if ($('select#' + billing + '_state').length > 0 && $('#' + billing + '_state').val().length > 0) {
                            var state = $('#' + billing + '_state option[value="' + $('#' + billing + '_state').val() + '"]').html();
                            address = address + state + ', ';
                        }
                        if ($('input#' + billing + '_state').length > 0 && $('#' + billing + '_state').val().length > 0)
                            address = address + $('#' + billing + '_state').val() + ', ';
                        if ($('#' + billing + '_postcode').length > 0 && $('#' + billing + '_postcode').val().length > 0)
                            address = address + $('#' + billing + '_postcode').val() + ', ';
                        if ($('select#' + billing + '_country').length > 0 && $('#' + billing + '_country').val().length > 0) {
                            var state = $('#' + billing + '_country option[value="' + $('#' + billing + '_country').val() + '"]').html();
                            address = address + state + ', ';
                        }
                        if ($('input#' + billing + '_country').length > 0 && $('#' + billing + '_country').val().length > 0)
                            address = address + $('#' + billing + '_country').val() + ', ';
                        calculateLocationInfo(address, $('form.checkout'), true);
                        return false;
                    } else if (typeof (settings.data) !== 'undefined' && updatingDistanceAlreadyStarted && settings.data.indexOf("action=woocommerce_update_order_review") >= 0) {
                        if (inProgress) {
                            inProgress = false;
                            updatingDistanceAlreadyStarted = false;
                        }
                    }
                }
            });
        }
    }
});