jQuery(document).ready(function($) {


//Functions for store editing page
    if ($('#store-address input').length > 0) {
        var marker = null;

        $('#store-map').width($('#store-details').width());

        var geocoder = new google.maps.Geocoder();
        var map = null;

        $('#store-locator-store-data').insertBefore('#edit-slug-box');

        $('.add-new-feature').click(function() {
            $('#store-features').append('<tr><td><input type="text" name="store_feature_labels[]" /></td><td><input type="text" name="store_feature_values[]" /></td><td>[<input type="text" name="store_feature_shortcodes[]" />]</td></tr>');
            return false;
        });

        $('.remove-features').live('click', function() {
            $(this).closest('.icon-or-feature-group').find('.remove-checkbox').each(function() {
                if ($(this).attr('checked') == 'checked')
                    $(this).closest('tr').remove();
            });
            return false;
        });
        /**
         * Gets the address on the admin options page
         */
        function getAddress() {
            var address = $('input[name=store_address_1]').val();
            address = address + ', ' + $('input[name=store_address_2]').val();
            address = address + ', ' + $('input[name=store_address_3]').val();
            address = address + ', ' + $('input[name=store_address_4]').val();
            address = address.replace(', , ', ', ').replace(', , ', ', ');
            return address;
        }

        /**
         * Edits the address for google maps GET request
         */
        function getAddressForGoogleMaps() {
            var address = getAddress();
            return address.trim().replace(' ', '+').replace('&', '+');
        }


        /**
         * Resets the map depending on address change
         */
        function resetMapFromAddress() {
            var address = getAddressForGoogleMaps();
            if (address == '' || address == ',') {
                $('#store-map-row').hide();
                return;
            }
            else {
                $('#store-map-row').show();
            }
            $('#store-map').hide(1000);
            geocoder.geocode({'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $('#store-map').show(1000);
                    if (map == null) {
                        var mapOptions = {
                            zoom: 12,
                            center: results[0].geometry.location,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                        map = new google.maps.Map(document.getElementById("store-map"), mapOptions);
                    }
                    else {
                        map.setCenter(results[0].geometry.location);
                    }
                    if (marker == null) {
                        marker = new google.maps.Marker({
                            map: map,
                            position: results[0].geometry.location,
                            draggable: true
                        });
                    }
                    else {
                        marker.setPosition(results[0].geometry.location);
                    }

                } else {
                    alert(store_locator_settings.map_error + status);
                }
            });
        }

        //If there's no lat/lng, get address from map
        var lat = $('input[name="store_latitude"]').val();
        var lng = $('input[name="store_longitude"]').val();
        if (lat == '' || lng == '') {
            resetMapFromAddress();
        } else {
            var location = new google.maps.LatLng(lat, lng);
            if (map == null) {
                var mapOptions = {
                    zoom: 12,
                    center: location,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                }
                map = new google.maps.Map(document.getElementById("store-map"), mapOptions);
            }
            else {
                map.setCenter(location);
            }
            if (marker == null) {
                marker = new google.maps.Marker({
                    map: map,
                    position: location,
                    draggable: true
                });
            }
            else {
                marker.setPosition(location);
            }

        }

        //Update the google map when the address changes
        $('input[name="store_address_1"], input[name="store_address_2"], input[name="store_address_3"], input[name="store_address_4"]').change(function() {
            $('#store-map').hide(1000);
            resetMapFromAddress();
            $('#store-map').show(1000);
        });

        //Save lat/lng when form submitted
        $('input[type="submit"]').click(function() {
            if (marker != null) {
                $('input[name="store_latitude"]').val(marker.getPosition().lat());
                $('input[name="store_longitude"]').val(marker.getPosition().lng());
            }
        });

    }
});