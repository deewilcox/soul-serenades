jQuery(document).ready(function($) {
    
     $('.shipping_calculator #calc_shipping_postcode, .shipping-calculator-form #calc_shipping_postcode, .shipping-calculator #calc-shipping-postcode, .shipping-calculator #calc_shipping_postcode, .shipping_calculator #calc-shipping-postcode').parent().before('<p class="form-row form-row-wide"><input type="text" class="input-text" value="' + add_street_to_cart_settings.shipping_address + '" placeholder="' + add_street_to_cart_settings.street_address_placeholder + '" name="calc_shipping_address" id="calc_shipping_address" /></p>');
    
});