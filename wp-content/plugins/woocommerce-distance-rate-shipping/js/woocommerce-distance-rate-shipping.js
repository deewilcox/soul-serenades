jQuery(document).ready(function($) {

    if ($('#distance-shipping-method').length > 0) {

        var shippingMethodId = $('#distance-shipping-method').val();

        /**
         * Gets the address on the admin options page
         */
        function getAddress() {
            var address = $('#woocommerce_' + shippingMethodId + '_base_address_1').val();
            address = address + ', ' + $('#woocommerce_' + shippingMethodId + '_base_address_2').val();
            address = address + ', ' + $('#woocommerce_' + shippingMethodId + '_base_city').val();
            address = address + ', ' + $('#woocommerce_' + shippingMethodId + '_base_postcode').val();
            address = address + ', ' + $('#woocommerce_' + shippingMethodId + '_base_country').val();
            address = address.replace(', , ', ', ').replace(', , ', ', ');
            return address;
        }

        /**
         * Edits the address for google maps GET request
         */
        function getAddressForGoogleMaps() {
            var address = getAddress();
            return address.replace(' ', '+').replace('&', '+');
        }

//Add google map to the admin options page using the address 
        $('#woocommerce_' + shippingMethodId + '_base_country').closest('tr').after('<tr><td colspan="2" id="base-address-map"><p>' + distance_rate_shipping_settings.google_positions + '</p><iframe id="base-address-map" width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=' + getAddressForGoogleMaps() + '&amp;output=embed"></iframe></td></tr>');
        //Update the google map when the address changes
        $('.base-address').change(function() {
            $('#base-address-map').hide(1000);
            $('#base-address-map').html('<p>' + distance_rate_shipping_settings.google_positions + '</p><iframe id="base-address-map" width="100%" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=' + getAddressForGoogleMaps() + '&amp;output=embed"></iframe>');
            $('#base-address-map').show(1000);
        });
        //Update the unit of distance when it changes
        $('#woocommerce_' + shippingMethodId + '_distance_unit').change(function() {
            updateUnits();
        });
        //Function adds km or miles to the form
        function updateUnits() {
            var unit = 'km';
            if ($('#woocommerce_' + shippingMethodId + '_distance_unit').val().indexOf('mile') >= 0)
                unit = 'mile';
            $('.distance-unit').text(unit);
            $('.distance-units').text($('#woocommerce_' + shippingMethodId + '_distance_unit').val());
            var pluralUnit = unit;
            if ($('#woocommerce_' + shippingMethodId + '_distance_unit').val().indexOf('mile') >= 0)
                pluralUnit = 'mile(s)';
            $('.distance-unit-plural').text(pluralUnit);
        }
        //Add the units when the page loads
        updateUnits();
        var first_display_condition = true;
        var first_display_cost = true;
        var parent = null;
        function updateConditionsAndCosts() {
            var distanceId = parent.find('.distance-id').val();
            var conditionsAndCosts = distance_rate_shipping_settings.if;
            first_display_condition = true;
            conditionsAndCosts = conditionsAndCosts + displayCondition('order total', 'order_total', distance_rate_shipping_settings.currencySymbol, false, distanceId, distance_rate_shipping_settings.currencySymbol);
            conditionsAndCosts = conditionsAndCosts + displayCondition('distance', 'distance', '<span class="distance-unit"></span>', true, distanceId, '<span class="distance-unit-plural"></span>');
            conditionsAndCosts = conditionsAndCosts + displayCondition('weight', 'weight', distance_rate_shipping_settings.kg, true, distanceId, distance_rate_shipping_settings.kg);
            conditionsAndCosts = conditionsAndCosts + displayCondition('volume', 'volume', distance_rate_shipping_settings.cubicCm, true, distanceId, distance_rate_shipping_settings.cubicCm);
            if (conditionsAndCosts == distance_rate_shipping_settings.if)
                conditionsAndCosts = conditionsAndCosts + distance_rate_shipping_settings.noConditions;
            conditionsAndCosts = conditionsAndCosts + distance_rate_shipping_settings.thenCharge;
            first_display_cost = true;
            var costs = '';
            var fee = parent.find('.fee').val();
            if (fee != '' && fee != 0) {
                first_display_cost = false;
                costs = costs + distance_rate_shipping_settings.currencySymbol + fee;
            }
            costs = costs + displayCost('order total', 'order_total', distance_rate_shipping_settings.currencySymbol, false, distance_rate_shipping_settings.currencySymbol, distanceId);
            costs = costs + displayCost('distance', 'distance', '<span class="distance-unit"></span>', true, '<span class="distance-unit-plural"></span>', distanceId);
            costs = costs + displayCost('weight', 'weight', distance_rate_shipping_settings.kg, true, distance_rate_shipping_settings.kg, distanceId);
            costs = costs + displayCost('volume', 'volume', distance_rate_shipping_settings.cubicCm, true, distance_rate_shipping_settings.cubicCm, distanceId);
            if (costs == '')
                costs = distance_rate_shipping_settings.currencySymbol + '0';
            costs = costs + distance_rate_shipping_settings.forShipping;
            conditionsAndCosts = conditionsAndCosts + costs;
            parent.find('.conditions-and-costs').html(conditionsAndCosts);
            updateUnits();
        }

        $(document).on('change', '.select-countries', function() {
            var id = $(this).closest('.distance-row').find('.distance-id').val();
            var selectStatesWrapper = $(this).closest('.distance-row').find('.select-states-wrapper');
             selectStatesWrapper.find('select').attr('disabled','disabled');
                        selectStatesWrapper.css('opacity','0.5');
            $.post(distance_rate_shipping_settings.ajaxurl,
                    {
                        action: 'get_state_select',
                        id: id,
                        country_codes: $(this).val(),
                    },
                    function(response) {
                        selectStatesWrapper.find('select').removeAttr('disabled');
                        selectStatesWrapper.css('opacity','1');
                        selectStatesWrapper.html(response);
                    }
            );
        });

        $(document).on('change', '.distance-row input, .distance-row select', function() {
            parent = $(this).closest('.distance-row');
            updateConditionsAndCosts();
            validateDeliveryRates();
        });

        $('distance-row').each(function() {
            parent = $(this);
            updateConditionsAndCosts();
            validateDeliveryRates();
        });

        function displayCondition(label, name, unit, unit_after, distance_rate_id, unit_plural) {
            var minimum = parent.find('.minimum_' + name).val();
            var maximum = parent.find('.maximum_' + name).val();
            if (minimum == '' && maximum == '')
                return '';

            var condition = '';
            var after = '';
            var before = '';
            if (unit_after)
                after = ' ' + unit_plural;
            else
                before = unit_plural;
            if (first_display_condition)
                first_display_condition = false;
            else
                condition = distance_rate_shipping_settings.and + condition;

            if (minimum != '' && maximum != '') {
                condition = condition + label + distance_rate_shipping_settings.isBetween + before + minimum + after + distance_rate_shipping_settings.and + before + maximum + after;
            } else if (minimum != '') {
                condition = condition + label + distance_rate_shipping_settings.isAbove + before + minimum + after;
            } else if (maximum != '') {
                condition = condition + label + distance_rate_shipping_settings.isBelow + before + maximum + after;
            }
            return condition;
        }



        function displayCost(label, name, unit, unit_after, plural_unit, distance_id) {
            var fee_per = '';
            var value = parent.find('.fee_per_' + name).val();
            if (value != '' && value != 0) {
                var after = '';
                var before = '';
                if (unit_after)
                    after = ' ' + plural_unit;
                else
                    before = plural_unit;
                if (first_display_cost)
                    first_display_cost = false;
                else
                    fee_per = distance_rate_shipping_settings.plus + fee_per;
                var starting_from = 0;
                if (parent.find('.starting_from_' + name).val() == 'minimum')
                    starting_from = parent.find('.minimum_' + name).val();
                fee_per = fee_per + distance_rate_shipping_settings.currencySymbol + value + distance_rate_shipping_settings.per + unit;
                if (starting_from > 0)
                    fee_per = fee_per + distance_rate_shipping_settings.startingFrom + before + starting_from + after;
            }
            return fee_per;
        }

        function updateRuleNumbers() {
            var rule = 1;
            $('.rule-number').each(function() {
                $(this).html(rule);
                rule = rule + 1;
            });
        }
        updateRuleNumbers();

//Add a new line to the delivery rates
        function addNewLine() {
            maxId = maxId + 1;
            var lineToAdd = newRow;
            lineToAdd = lineToAdd.replace(new RegExp("newRatenewRate", "g"), maxId);
            $('#rules').append(lineToAdd);
            $('.distance-row-' + maxId).hide(0);
            $('.distance-row-' + maxId).show(1000);
            parent = $('.distance-row-' + maxId);
            updateConditionsAndCosts();
            updateRuleNumbers();
        }

//Check that the maximum distance is more than the minimum distance etc
        function validateDeliveryRates() {
            var isValid = true;
            $('.distance-rate-error').remove();
            $('.numeric').each(function() {
                if ($(this).val() == '') {
                }
                else if (!$.isNumeric($(this).val())) {
                    isValid = false;
                    $(this).after('<span class="distance-rate-error">' + distance_rate_shipping_settings.numeric_error + '</span>');
                }
            });
            if (isValid) {
                $('.row-container input.minimum').each(function() {
                    var min = parseInt($(this).val());
                    var max = parseInt($(this).closest('tr').find('input.maximum').val());
                    if (min != '' && max != '' && max < min) {
                        isValid = false;
                        $(this).after('<span class="distance-rate-error">' + distance_rate_shipping_settings.minimum_maximum_error + '</span>');
                    }
                });

            }

            if (!isValid) {
                $('input[name=save]').after('<span class="distance-rate-error">' + distance_rate_shipping_settings.correct_errors + '</span>');
            }
            return isValid;
        }

        $('input[name=save]').click(function() {
            return validateDeliveryRates();
        });
        //Add a blank line if there are no lines
        if (maxId == -1)
            addNewLine();
        //For validation
        $(document).on('change', '#distance-rate-shipping-rates input[type=text]', function() {
            $('.distance-rate-shipping-error').remove();
            if (!$.isNumeric($(this).val())) {
                $(this).after('<span class="distance-rate-shipping-error">' + distance_rate_shipping_settings.numeric_error + '</span>');
            }
        });
        //The add button
        $('.add-distance-rate').click(function() {
            addNewLine();
            return false;
        });
        //The remove button
        $(document).on('click', '.remove-distance-rate', function() {
            var confirmation = confirm(distance_rate_shipping_settings.confirmRemove)
            if (confirmation) {
                var rowToRemove = $(this).closest('.distance-row');
                rowToRemove.addClass('remove-this-row');
                rowToRemove.hide(1000);
                setTimeout(function() {
                    $('.remove-this-row').remove();
                }, 1000);
            }
            return false;
        });
    }
});