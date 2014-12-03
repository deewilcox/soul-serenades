<?php

if ( !defined( 'ABSPATH' ) )
	exit;

//Free collection shipping rate
class WC_Collection_Delivery_Rate extends WC_Distance_Rate_Shipping {

	function init_name() {
		$this->id = 'collection_delivery_shipping';
		$this->method_title = __( 'Takeout/Collection', 'woocommerce_distance_rate_shipping' );
	}

	function init_form_fields() {
		parent::init_form_fields();
		$this->form_fields['title']['default'] = __( 'Distance Rate Takeout/Collection', 'woocommerce_distance_rate_shipping' );
	}
	
}

/** object oriented */
$collection_shipping_rate = new WC_Collection_Delivery_Rate();
?>