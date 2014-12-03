<?php

if ( !defined( 'ABSPATH' ) )
	exit;

//Free collection shipping rate
class WC_Special_Delivery_Rate extends WC_Distance_Rate_Shipping {

	/**
	 * Needs to be overridden.
	 */
	function init_name() {
		$this->id = 'special_delivery_shipping';
		$this->method_title = __( 'Special Delivery', 'woocommerce_distance_rate_shipping' );
	}

	function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['title']['default'] = __( 'Distance Rate Special delivery', 'woocommerce_distance_rate_shipping' );
	}

}

/** object oriented */
$special_shipping_rate = new WC_Special_Delivery_Rate();
?>