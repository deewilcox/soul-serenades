<?php

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !isset( $_SESSION ) ) {
	@session_start();
}
//if ( !class_exists( 'WC_Email_New_Order' ) ) {
if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/classes/abstracts/abstract-wc-email.php' ) ) {
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/abstracts/abstract-wc-email.php';
} else {
	require_once WP_PLUGIN_DIR . '/woocommerce/includes/abstracts/abstract-wc-email.php';
}
if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-completed-order.php' ) ) {
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-completed-order.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-invoice.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-new-account.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-note.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-reset-password.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-processing-order.php';
	require_once WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-new-order.php';
} else {
	
}
//}

/**
 * Distance Rate Shipping Method
 *
 * A simple shipping method allowing site's to set the delivery rate dependent upon distance.
 */
class WC_Distance_Rate_Shipping extends WC_Shipping_Method {

	/**
	 * __construct function.
	 *
	 */
	function __construct() {
		$this->init_name();
		$this->distance_rate_shipping_rates = get_option( 'woocommerce_' . $this->id . '_rates',
				array() );
		$this->distance_rate_shipping_settings = get_option( 'woocommerce_' . $this->id . '_rate_settings',
				array( 'calculate_shipping' => 'minimum' ) );
		$this->init();
		add_action( 'woocommerce_update_options_shipping_' . $this->id,
				array( $this, 'process_rates' ) );
		add_filter( 'woocommerce_shipping_methods',
				array( $this, 'add_shipping_method' ) );
		add_action( 'woocommerce_update_options_shipping_distance_rate_shipping',
				array( $this, 'update_order_review' ) );
		add_filter( 'woocommerce_add_error',
				array( $this, 'change_shipping_error_message' ) );
		add_action( 'woocommerce_checkout_update_order_meta',
				array( $this, 'order_updated' ) );
		add_filter( 'woocommerce_cart_shipping_method_full_label',
				array( $this, 'edit_title' ), 10, 2 );
		add_action( 'woocommerce_order_status_pending_to_processing_notification',
				array( $this, 'send_store_email' ) );
		add_action( 'woocommerce_new_order_item',
				array( $this, 'woocommerce_new_order_item' ), 10, 3 );
		if ( $this->id === 'distance_rate_shipping' ) {
			add_filter( 'woocommerce_no_shipping_available_html',
					array( $this, 'woocommerce_no_shipping_available_html' ) );
			add_filter( 'woocommerce_cart_no_shipping_available_html',
					array( $this, 'woocommerce_cart_no_shipping_available_html' ) );
		}
		add_action( 'woocommerce_checkout_update_order_meta',
				array( $this, 'woocommerce_checkout_update_order_meta' ), 10, 2 );
		add_action( 'woocommerce_email_after_order_table',
				array( $this, 'woocommerce_email_after_order_table' ), 10, 3 );
	}

	function woocommerce_cart_no_shipping_available_html( $html ) {
		return '<div class="woocommerce-error">' . $this->woocommerce_no_shipping_available_html( $html ) . '</div>';
	}

	function woocommerce_no_shipping_available_html( $html ) {
		$html = '<p>' .
				$this->get_option( 'no_shipping_available' ) . '</p>';
		$html = str_replace( '[to_your_country]',
				WC()->countries->shipping_to_prefix() . ' ' . WC()->countries->countries[WC()->customer->get_shipping_country()],
				$html );
		return $html;
	}

	function woocommerce_new_order_item( $item_id, $item, $order_id ) {
		global $wpdb;
		if ( $item['order_item_type'] === 'shipping' && $item['order_item_name'] == $this->title ) {
			$order_item_name = $item['order_item_name'];
			if ( isset( $_SESSION['delivery-rate-' . $this->id] ) ) {
				$order_item_name = $this->get_title();
				$order_item_name = $this->edit_title( $order_item_name, $this );
				if ( $order_item_name !== $item['order_item_name'] ) {
					$wpdb->update( $wpdb->prefix . "woocommerce_order_items",
							array(
						'order_item_name' => $order_item_name,
							), array( 'order_item_id' => $item_id ) );
				}
			}
		}
	}

	function get_title() {
		if ( isset( $_SESSION['delivery-rate-' . $this->id] ) ) {
			if ( isset( $_SESSION['delivery-rate-' . $this->id] ) && !empty( $this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]] ) && !empty( $this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]]['title'] ) ) {
				return $this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]]['title'];
			}
		}
		return $this->get_option( 'title' );
	}

	function edit_title( $title, $rate ) {
		if ( $rate->id === $this->id && isset( $_SESSION['store-delivery-title-' . $this->id] ) && isset( $_SESSION['store-delivery-address-' . $this->id] ) && isset( $_SESSION['delivery-rate-' . $this->id] ) ) {
			if ( !empty( $this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]] ) && !empty( $this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]]['title'] ) ) {
				$title = str_replace( $this->title,
						$this->distance_rate_shipping_rates[$_SESSION['delivery-rate-' . $this->id]]['title'],
						$title );
			}
			if ( !empty( $_SESSION['selected_opening_hours'] ) ) {
				$title = str_replace( '[delivery_time]',
						$_SESSION['selected_opening_hours'], $title );
			}
			$title = str_replace( '[store_title]',
					$_SESSION['store-delivery-title-' . $this->id], $title );
			return str_replace( '[store_address]',
					$_SESSION['store-delivery-address-' . $this->id], $title );
		}
		return $title;
	}

	/**
	 * See if this was the shipping method for the order and whether it was ordered by a store.
	 * If so, check settings and send store an email
	 */
	function order_updated( $order_id ) {
		$order = new WC_Order( $order_id );
		$shipping_method_title = $order->get_shipping_method();
		$this->send_store_email( $order_id );
	}

	/**
	 * Sends email to the store
	 * @param int $order_id
	 */
	function send_store_email( $order_id ) {
//Send email to store if the store has an email address
		if ( !isset( $_SESSION['store_email_sent_for_order_' . $order_id] ) && isset( $_SESSION['store-delivery-id-' . $this->id] ) && $_SESSION['store-delivery-id-' . $this->id] !== 'base' ) {
			$store_id = $_SESSION['store-delivery-id-' . $this->id];
			$email = get_post_meta( $store_id, 'store_email', true );
			if ( !empty( $email ) ) {

				$emailer = $this->get_email_new_order();
				$emailer->recipient = $email;
				$_SESSION['store_email_sent_for_order_' . $order_id] = 'sent';
			}
		}
	}

	function get_email_new_order() {
		if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/classes/emails/class-wc-email-customer-completed-order.php' ) ) {
			return new WC_Email_New_Order();
		} else {
			$email_new_order = include( WP_PLUGIN_DIR . '/woocommerce/includes/emails/class-wc-email-new-order.php');
			return $email_new_order;
		}
	}

	/**
	 * Needs to be overridden.
	 */
	function init_name() {
		$this->id = 'distance_rate_shipping';
		$this->method_title = __( 'Distance Rate Delivery',
				'woocommerce_distance_rate_shipping' );
	}

	/**
	 * init function - completes construction of object
	 */
	function init() {
// Load form fields and settings
		$this->init_form_fields();
		$this->init_settings();
		$this->tax_status = $this->get_option( 'tax_status' );

// Load user variables
		$this->enabled = $this->get_option( 'enabled' );
		$this->title = $this->get_option( 'title' );

// Actions and filters
		add_action( 'woocommerce_update_options_shipping_' . $this->id,
				array( $this, 'process_admin_options' ) );
	}

	/**
	 * process_rates function - Woocommerce update options shipping hook to save rates.
	 */
	function process_rates() {
		$rates = array();
		if ( !empty( $_POST['distance_rate'] ) ) {
			$rates = $_POST['distance_rate'];
		}
		update_option( 'woocommerce_' . $this->id . '_rates', $rates );
		$this->distance_rate_shipping_rates = $rates;
		$settings = array();
		if ( !empty( $_POST['distance_settings'] ) ) {
			$settings = $_POST['distance_settings'];
		}
		update_option( 'woocommerce_' . $this->id . '_rate_settings', $settings );
		$this->distance_rate_shipping_settings = $settings;
	}

	/**
	 * filter that adds this class as a shipping rate.
	 *
	 */
	function add_shipping_method( $methods ) {
		$methods[] = get_class( $this );
		return $methods;
	}

	/**
	 * curl function - Reads remote file - required to get JSON results from Google Maps.
	 */
	function curl( $url ) {
//Used for reading JSON response from Google Maps API
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20 );
		$data = curl_exec( $ch );
		curl_close( $ch );
		if ( empty( $data ) ) {
			$data = file_get_contents( $url );
		}
		return $data;
	}

	/**
	 * Returns the array as an address.
	 */
	function get_address( $addressAsArray ) {
		global $woocommerce;
		$country = $woocommerce->countries->countries[$addressAsArray['country']];
		$address = $addressAsArray['address'] . ', ' . $addressAsArray['address_2'] . ', ' .
				$addressAsArray['city'] . ', ' . $addressAsArray['state'] . ' ' .
				$addressAsArray['postcode'] . ', ' . $country;
//remove double/triple commas (in case parts of the array were empty)
		$address = str_replace( ', , ', ', ', $address );
		$address = str_replace( ', , ', ', ', $address );
		return $address;
	}

	/**
	 * Gets the base address as stored in this class.
	 */
	function get_base_address() {

		$address = $this->get_option( 'base_company' ) . ', ' . $this->get_option( 'base_address_1' ) . ', ' . $this->get_option( 'base_address_2' ) . ', ' .
				$this->get_option( 'base_city' ) . ', ' . $this->get_option( 'base_postcode' ) . ', ' .
				$this->get_option( 'base_country' );
//remove double/triple commas (in case parts of the array were empty)
		$address = str_replace( ', , ', ', ', $address );
		$address = str_replace( ', , ', ', ', $address );
		return $address;
	}

	/**
	 * Calculates distance between two addresses
	 */
	function calculate_distance( $id, $address_from, $address_to ) {
		global $wpdb;
		$distance_calculation = $this->get_option( 'distance_calculation' );
		if ( $distance_calculation == 'road' ) {

			if ( $id == 'base' ) $id = 'base-' . $this->id;
			$distance = 9999999999;
			if ( isset( $_SESSION['store-' . $id] ) ) {
				$distance = $_SESSION['store-' . $id];
			}

			if ( strpos( $this->get_option( 'distance_unit' ), 'mile' ) !== false ) {
				$distance = $distance * 0.621371192;
			}
		} else {
			$latlng1 = $this->get_lat_and_lng( $address_from );
			$latlng2 = $this->get_lat_and_lng( $address_to );
			$lat1 = $latlng1['lat'];
			$lng1 = $latlng1['lng'];
			$lat2 = $latlng2['lat'];
			$lng2 = $latlng2['lng'];
			if ( ($lat1 == 0 && $lng1 == 0) || ($lat2 == 0 && $lng2 == 0) ) {
				$distance = 9999999999;
			} else {
				$earthRadius = 3958.75;

				$dLat = deg2rad( $lat2 - $lat1 );
				$dLng = deg2rad( $lng2 - $lng1 );


				$a = sin( $dLat / 2 ) * sin( $dLat / 2 ) +
						cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) *
						sin( $dLng / 2 ) * sin( $dLng / 2 );
				$c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
				$distance = $earthRadius * $c;
				if ( strpos( $this->get_option( 'distance_unit' ), 'mile' ) === false )
						$distance = $distance / 0.621371192;
			}
		}

		if ( $this->get_option( 'distance_rounding' ) == 'round_up' ) {
			$distance = ceil( $distance );
		} elseif ( $this->get_option( 'distance_rounding' ) == 'round_to_nearest' ) {
			$distance = round( $distance );
		}
		return $distance;
	}

	/**
	 * Gets the latitude/longitude of an address (returning it as an array with ['lat'] and ['lng'])
	 */
	function get_lat_and_lng( $address ) {
		global $wpdb;
		global $woocommerce;
		$lat = 0;
		$lng = 0;
//See if added to database within last 30 days
		$addressInDatabase = $wpdb->get_row( $wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}woocommerce_shipping_locations
				 WHERE address = %s AND DATEDIFF(NOW(),ts)<30", $address ) );

		if ( null === $addressInDatabase ) {//Otherwise look it up from google
			$addressToLookup = urlencode( $address );
//$addressToLookup = str_replace( " ", "+", $address );
			//$addressToLookup = str_replace( "&", "+", $addressToLookup );
//Curl is needed to get response from google in php.
			$googlejson = $this->curl( "https://maps.google.com/maps/api/geocode/json?address={$addressToLookup}&sensor=false&language=" . str_replace( '_',
							'-', get_locale() ) );
			$json = json_decode( $googlejson );
			$lat = 0;
			$lng = 0;
			if ( $json->{'status'} === 'OK' ) {
				$lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
				$lng = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
				$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}woocommerce_shipping_locations (address, lat, lng) VALUES ('%s', %f, %f)",
								$address, $lat, $lng ) );
			}
		} else {
			$lat = $addressInDatabase->lat;
			$lng = $addressInDatabase->lng;
//Delete old data
			$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_shipping_locations WHERE DATEDIFF(NOW(),ts)>30" );
		}
		return array( 'lat' => $lat, 'lng' => $lng );
	}

	/**
	 * Checks whether the current rate has the current store
	 * @return boolean
	 */
	function check_store() {
//If there are no branches, return true.
		if ( !$this->stores_exist() ) {
			return true;
		}
//If there are stores but the current store is not set, return false 
		if ( !isset( $this->current_rate['store'][$this->current_store] ) ) {
			return false;
		}
		return true;
	}

	function calculate_shipping_from_address( $id, $store_address, $package,
			$store_title = '' ) {
//Go through all the stores selected in rule and calculate the closest.
		$distance = $this->calculate_distance( $id, $store_address,
				$this->get_address( $package['destination'] ) );
		$order_total = $package['contents_cost'];
		$cost = 999999999;
		if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'sum_order' ) {
			$cost = 0;
		}
		if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'maximum' )
				$cost = -999999999;
		if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'sum_rows' ) {
			$cost = 0;
			foreach ( $package['contents'] as $order_line ) {
//Get the minimum for the row.
				$cost_for_this_row = 99999999;
				foreach ( $this->distance_rate_shipping_rates as $delivery_rate_id =>
							$delivery_rate ) {
					$this->current_rate = $this->distance_rate_shipping_rates[$delivery_rate_id];
					$this->current_rate_id = $delivery_rate_id;
					$this->init_posts_from_query();
					$volume_and_weight = $this->calculate_line_volume_and_weight( $order_line );
					$volume = $volume_and_weight['volume'];
					$weight = $volume_and_weight['weight'];
					$quantity = $volume_and_weight['quantity'];
					$order_total = $volume_and_weight['line_total'];
					$is_on_backorder = $volume_and_weight['is_on_backorder'];
					$dimensional_weight = 0.0;
					if ( floatval( $weight ) > 0 ) {
						$dimensional_weight = floatval( $volume ) / floatval( $weight );
					}
					if ( $this->check_on_backorder( $delivery_rate, $is_on_backorder ) && $this->check_opening_hours() && $this->check_country( $package ) && $this->check_zipcode( $package ) && $this->check_store() && $this->check_class_conditions_for_row( $order_line ) && $this->check_condition( 'quantity',
									$delivery_rate, $quantity ) && $this->check_condition( 'volume',
									$delivery_rate, $volume ) && $this->check_condition( 'weight',
									$delivery_rate, $weight ) && $this->check_condition( 'order_total',
									$delivery_rate, $order_total ) && $this->check_condition( 'distance',
									$delivery_rate, $distance ) && $this->check_condition( 'dimensional_weight',
									$delivery_rate, $dimensional_weight ) ) {
						$fee = '';
						if ( isset( $delivery_rate['fee'] ) ) {
							$fee = $delivery_rate['fee'];
						}
						if ( $fee === '' ) {
							$fee = 0;
						}
						$cost_for_this_rate = $fee +
								$this->get_cost( 'quantity', $delivery_rate, $quantity ) + $this->get_cost( 'volume',
										$delivery_rate, $volume ) + $this->get_cost( 'weight', $delivery_rate,
										$weight ) + $this->get_cost( 'distance', $delivery_rate, $distance ) + $this->get_cost( 'order_total',
										$delivery_rate, $order_total ) + $this->get_cost( 'dimensional_weight',
										$delivery_rate, $dimensional_weight );
						if ( $cost_for_this_rate < $cost_for_this_row ) {
							$cost_for_this_row = $cost_for_this_rate;
							$_SESSION['delivery-rate-' . $this->id] = $this->current_rate_id;
						}
					}
				}
				$cost = $cost + $cost_for_this_row;
			}
		} else {
			foreach ( $this->distance_rate_shipping_rates as $delivery_rate_id =>
						$delivery_rate ) {
//check a row satisfies this rule
				$satisfies_class_conditions = false;
				$this->current_rate = $this->distance_rate_shipping_rates[$delivery_rate_id];
				$this->current_rate_id = $delivery_rate_id;
				$this->init_posts_from_query();
				foreach ( $package['contents'] as $order_line ) {
					if ( $this->check_class_conditions_for_row( $order_line ) ) {
						$satisfies_class_conditions = true;
					} else {
						$all_rows_sat = value( $this->distance_rate_shipping_settings,
								'all_rows_sat' );
						if ( !empty( $all_rows_sat ) ) {
							continue;
						}
					}
				}
				if ( !$satisfies_class_conditions ) continue;
//get the distance and order total
				$volume_and_weight = $this->calculate_volume_and_weight( $package );
				$volume = $volume_and_weight['volume'];
				$weight = $volume_and_weight['weight'];
				$quantity = $volume_and_weight['quantity'];
				$order_total = $volume_and_weight['total'];
				$is_on_backorder = $volume_and_weight['is_on_backorder'];
				$dimensional_weight = 0.0;
				if ( floatval( $weight ) > 0 ) {
					$dimensional_weight = floatval( $volume ) / floatval( $weight );
				}
//find which shipping rate applies
				if ( $this->check_on_backorder( $delivery_rate, $is_on_backorder ) && $this->check_opening_hours() && $this->check_country( $package ) && $this->check_zipcode( $package ) && $this->check_store() && $this->check_condition( 'quantity',
								$delivery_rate, $quantity ) && $this->check_condition( 'volume',
								$delivery_rate, $volume ) && $this->check_condition( 'weight',
								$delivery_rate, $weight ) && $this->check_condition( 'order_total',
								$delivery_rate, $order_total ) && $this->check_condition( 'distance',
								$delivery_rate, $distance ) && $this->check_condition( 'dimensional_weight',
								$delivery_rate, $dimensional_weight ) ) {
//calculate the delivery rate
					$fee = '';
					if ( isset( $delivery_rate['fee'] ) ) {
						$fee = $delivery_rate['fee'];
					}
					if ( $fee === '' ) {
						$fee = 0;
					}
					$cost_for_this_rate = $fee +
							$this->get_cost( 'quantity', $delivery_rate, $quantity ) + $this->get_cost( 'volume',
									$delivery_rate, $volume ) + $this->get_cost( 'weight', $delivery_rate,
									$weight ) + $this->get_cost( 'distance', $delivery_rate, $distance ) + $this->get_cost( 'order_total',
									$delivery_rate, $order_total ) + $this->get_cost( 'dimensional_weight',
									$delivery_rate, $dimensional_weight );
					if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'sum_order' ) {
						$cost = $cost + $cost_for_this_rate;
						$_SESSION['delivery-rate-temp-' . $this->id] = $this->current_rate_id;
						$_SESSION['store-delivery-title-' . $this->id] = $store_title;
						$_SESSION['store-delivery-address-' . $this->id] = $store_address;
					}
					if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'minimum' ) {
						if ( $cost_for_this_rate < $cost ) {
							$cost = $cost_for_this_rate;
							$_SESSION['delivery-rate-temp-' . $this->id] = $this->current_rate_id;
						}
					}
					if ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'maximum' ) {
						if ( $cost_for_this_rate > $cost ) {
							$cost = $cost_for_this_rate;
							$_SESSION['delivery-rate-temp-' . $this->id] = $this->current_rate_id;
						}
					}
				}
			}
		}
		return apply_filters( 'distance_calculate_shipping_from_address', $cost,
				$this->id, $this->current_rate_id, $store_address, $package );
	}

	function check_on_backorder( $delivery_rate, $is_on_backorder ) {
		if ( empty( $delivery_rate['is_on_backorder'] ) ) {
			return true;
		}
		if ( $delivery_rate['is_on_backorder'] == 'on_backorder' ) {
			return $is_on_backorder;
		}
		return !$is_on_backorder;
	}

	function tidy_address( $address ) {
		$address = str_replace( ', , ', ', ', $address );
		$address = str_replace( ', , ', ', ', $address );
		$address = trim( $address, ', ' );
		return $address;
	}

	function get_store_address( $store_id ) {
		$store_address = get_post_meta( $store_id, 'store_address_1', true );
		$store_address .= ', ' . get_post_meta( $store_id, 'store_address_2', true );
		$store_address .= ', ' . get_post_meta( $store_id, 'store_address_3', true );
		$store_address .= ', ' . get_post_meta( $store_id, 'store_address_4', true );
		$store_address = $this->tidy_address( $store_address );
		return $store_address;
	}

	function init_posts_from_query() {
		$query = trim( value( $this->current_rate, 'query' ) );
		$this->query_posts = array();
		if ( !empty( $query ) ) {
			$posts = get_posts( $query );
			foreach ( $posts as $post ) {
				$this->query_posts[] = $post->ID;
			}
		}
	}

	/**
	 * calculates the shipping based on the user settings and the package destination
	 */
	function calculate_shipping( $package ) {
		$this->current_store = 'base';
		$store_cost = $this->calculate_shipping_from_address( 'base',
				$this->get_base_address(), $package );
		$_SESSION['store-delivery-id-' . $this->id] = 'base';
		$_SESSION['store-delivery-title-' . $this->id] = __( 'Base Store',
				'woocommerce_distance_rate_shipping' );
		$_SESSION['store-delivery-address-' . $this->id] = $this->tidy_address( $this->get_base_address() );
		if ( isset( $_SESSION['delivery-rate-temp-' . $this->id] ) ) {
			$_SESSION['delivery-rate-' . $this->id] = $_SESSION['delivery-rate-temp-' . $this->id];
		}
		$stores = get_posts( array(
			'posts_per_page' => -1,
			'post_type' => 'store' ) );
		if ( !empty( $stores ) ) {
			foreach ( $stores as $store ) {
				$this->current_store = $store->ID;
				$store_address = $this->get_store_address( $store->ID );
				$cost = $this->calculate_shipping_from_address( $store->ID, $store_address,
						$package, $store->post_title );
				if ( ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'minimum' && $cost > -99999999 && $cost < $store_cost ) || $this->distance_rate_shipping_settings['calculate_shipping'] == 'maximum' && $cost < 99999999 && $cost > $store_cost ) {
					$store_cost = $cost;
					$_SESSION['store-delivery-id-' . $this->id] = $store->ID;
					$_SESSION['store-delivery-title-' . $this->id] = $store->post_title;
					$_SESSION['store-delivery-address-' . $this->id] = $store_address;
					if ( isset( $_SESSION['delivery-rate-temp-' . $this->id] ) ) {
						$_SESSION['delivery-rate-' . $this->id] = $_SESSION['delivery-rate-temp-' . $this->id];
					}
				}
			}
		}
		if ( $store_cost < 99999999 && $store_cost > -99999999 ) {
			$rate = array(
				'id' => $this->id,
				'label' => $this->title,
				'cost' => $store_cost
			);
			$this->add_rate( $rate );
		}
	}

	/**
	 * USA, UK, Canada and Ireland use miles. 
	 */
	function get_default_distance_unit() {
		if ( 'US' === get_option( 'woocommerce_default_country' ) || 'GB' === get_option( 'woocommerce_default_country' ) || 'CA' === get_option( 'woocommerce_default_country' ) || 'IE' === get_option( 'woocommerce_default_country' ) )
				return __( 'mile(s)', 'woocommerce_distance_rate_shipping' );
		return __( 'km', 'woocommerce_distance_rate_shipping' );
	}

	/**
	 * Delete the transient when saving settings to ensure that shipping is recalculated.
	 */
	function update_order_review() {
		global $wpdb;
		$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%') OR `option_name` LIKE ('_transient_timeout_wc_ship_%')" );
	}

	function change_slashes( $string ) {
		return str_replace( '\\', '/', str_replace( '\\\\', '/', $string ) );
	}

	/**
	 * Gets the directory as a url
	 * @return string
	 */
	function dir() {
		return dirname( str_replace( $this->change_slashes( WP_CONTENT_DIR ),
						$this->change_slashes( WP_CONTENT_URL ), $this->change_slashes( __FILE__ ) ) );
	}

	/**
	 * add css and js files
	 */
	function scripts() {
		wp_enqueue_script( 'woocommerce-distance-rate-shipping',
				$this->dir() . '/../js/woocommerce-distance-rate-shipping.js',
				array( 'jquery' ), false, true );
		$localize_array = array( 'google_positions' => __( 'This is how google positions you from your address.<br>Please enter your address in full so that directions to your store can be found easily.',
					'woocommerce_distance_rate_shipping' ) );
		$localize_array['showRouteMap'] = $this->get_option( 'show_route_map' );
		$localize_array['confirmRemove'] = __( 'Remove this rate?',
				'woocommerce_distance_rate_shipping' );
		$localize_array['noConditions'] = __( ' no conditions',
				'woocommerce_distance_rate_shipping' );
		$localize_array['numeric_error'] = __( 'Please enter a numeric value for this field.',
				'woocommerce_distance_rate_shipping' );
		$localize_array['minimum_maximum_error'] = __( 'The value of this field should be less than the maximum!',
				'woocommerce_distance_rate_shipping' );
		$localize_array['correct_errors'] = __( 'You have errors in your input. Please make corrections above.',
				'woocommerce_distance_rate_shipping' );
		$localize_array['and'] = __( ' and ', 'woocommerce_distance_rate_shipping' );
		$localize_array['isBetween'] = __( ' is between ',
				'woocommerce_distance_rate_shipping' );
		$localize_array['isAbove'] = __( ' is above ',
				'woocommerce_distance_rate_shipping' );
		$localize_array['isBelow'] = __( ' is below ',
				'woocommerce_distance_rate_shipping' );
		$localize_array['thenCharge'] = __( ' then charge ',
				'woocommerce_distance_rate_shipping' );
		$localize_array['plus'] = __( ' plus ', 'woocommerce_distance_rate_shipping' );
		$localize_array['currencySymbol'] = get_woocommerce_currency_symbol();
		$localize_array['per'] = __( ' per ', 'woocommerce_distance_rate_shipping' );
		$localize_array['startingFrom'] = __( ' starting from ',
				'woocommerce_distance_rate_shipping' );
		$localize_array['forShipping'] = __( ' for shipping.',
				'woocommerce_distance_rate_shipping' );
		$localize_array['kg'] = __( 'kg', 'woocommerce_distance_rate_shipping' );
		$localize_array['cubicCm'] = __( 'cubic ',
						'woocommerce_distance_rate_shipping' ) . get_option( 'woocommerce_dimension_unit' );
		$localize_array['if'] = __( 'If ', 'woocommerce_distance_rate_shipping' );
		$localize_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
		wp_localize_script( 'woocommerce-distance-rate-shipping',
				'distance_rate_shipping_settings', $localize_array );
		wp_register_style( 'woocommerce-distance-rate-shipping-css',
				$this->dir() . '/../css/woocommerce-distance-rate-shipping.css' );
		wp_enqueue_style( 'woocommerce-distance-rate-shipping-css' );
	}

	/**
	 * Init the admin form
	 */
	function init_form_fields() {
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

		ob_start();

//To get the default country
		$countries = new WC_Countries();
		$default_country = '';
		if ( isset( $countries->countries[get_option( 'woocommerce_default_country' )] ) )
				$default_country = $countries->countries[get_option( 'woocommerce_default_country' )];

		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable', 'woocommerce_distance_rate_shipping' ),
				'type' => 'checkbox',
				'label' => __( 'Enable', 'woocommerce_distance_rate_shipping' ) . ' ' . $this->method_title,
				'default' => 'no',
				'class' => 'distance-rate-shipping-enabled',
				'description' => __( 'You should enable this shipping method if you would like customers to be able to use it.',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'tax_status' => array(
				'title' => __( 'Tax Status', 'woocommerce_distance_rate_shipping' ),
				'type' => 'select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woocommerce_distance_rate_shipping' ),
					'none' => __( 'None', 'woocommerce_distance_rate_shipping' ),
				),
			),
			'distance_unit' => array(
				'title' => __( 'Unit of Distance', 'woocommerce_distance_rate_shipping' ),
				'type' => 'select',
				'label' => __( 'Unit of Distance', 'woocommerce_distance_rate_shipping' ),
				'default' => $this->get_default_distance_unit(),
				'options' => array(
					'km' => __( 'km', 'woocommerce_distance_rate_shipping' ),
					'mile(s)' => __( 'mile(s)', 'woocommerce_distance_rate_shipping' ),
				),
				'description' => __( 'Choose whether you prefer to measure distances using km or miles.',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'distance_rounding' => array(
				'title' => __( 'How would you like to round distances?',
						'woocommerce_distance_rate_shipping' ),
				'type' => 'select',
				'label' => __( 'Unit of Distance', 'woocommerce_distance_rate_shipping' ),
				'default' => 'no_rounding',
				'options' => array(
					'round_up' => __( 'Round up to nearest mile or km',
							'woocommerce_distance_rate_shipping' ),
					'round_to_nearest' => __( 'Round to nearest mile or km',
							'woocommerce_distance_rate_shipping' ),
					'no_rounding' => __( 'No rounding', 'woocommerce_distance_rate_shipping' ),
				),
				'description' => __( 'Choose whether you would you like to round distances. Rounding up to the nearest mile rounds 2.3 miles up to 3 miles. Rounding to the nearest mile rounds 2.3 miles to 2 miles.',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'distance_calculation' => array(
				'title' => __( 'What measure of distance would you like to use?',
						'woocommerce_distance_rate_shipping' ),
				'type' => 'select',
				'label' => __( 'Unit of Distance', 'woocommerce_distance_rate_shipping' ),
				'default' => 'point',
				'options' => array(
					'point' => __( 'Point to point, straight line distance',
							'woocommerce_distance_rate_shipping' ),
					'road' => __( 'Road distance', 'woocommerce_distance_rate_shipping' ),
				),
				'description' => __( 'Choose road distance to use the distance by car or point-to-point distance to measure the distance between the customer and your store.',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'title' => array(
				'title' => __( 'Title', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout. You can use shortcodes [delivery_time], [store_title] and [store_address].',
						'woocommerce_distance_rate_shipping' ),
				'default' => __( 'Delivery from [store_title], [store_address]',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
				'placeholder' => 'Delivery from [store_title], [store_address]',
			),
			'no_shipping_available' => array(
				'title' => __( 'Error message when no shipping method is available (with shortcode [to_your_country])',
						'woocommerce_distance_rate_shipping' ),
				'type' => 'textarea',
				'description' => __( 'This controls the message that the user sees when no shipping method is available (instead of "Invalid Shipping Method"). You can override other WooCommerce messages using the multilanguage mo/po files.',
						'woocommerce_distance_rate_shipping' ),
				'default' => __( 'Sorry, it seems that there are no available shipping methods [to_your_country]. If you require assistance or wish to make alternate arrangements please contact us.',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'show_route_map' => array(
				'title' => __( 'Show Route Map?', 'woocommerce_distance_rate_shipping' ),
				'type' => 'checkbox',
				'label' => __( 'Show Route Map?', 'woocommerce_distance_rate_shipping' ),
				'default' => 'yes',
				'class' => 'distance-rate-shipping-show-route-map',
				'description' => __( 'Uncheck this box to show the customer location on the map (instead of the route from the store to the customer).',
						'woocommerce_distance_rate_shipping' ),
				'desc_tip' => true,
			),
			'base_address_title' => array(
				'type' => 'base_address_title'
			),
			/* 'base_company' => array(
			  'title' => __( 'Your company name', 'woocommerce_distance_rate_shipping' ),
			  'type' => 'text',
			  'description' => __( 'Your company name to help locate your company if you are on Google Maps. If you are not on Google Maps, please do not enter your company name here.',
			  'woocommerce_distance_rate_shipping' ),
			  'default' => __( '', 'woocommerce_distance_rate_shipping' ),
			  'class' => 'base-address',
			  'desc_tip' => true,
			  ), */
			'base_address_1' => array(
				'title' => __( 'Address 1', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'The first line of the address where items are shipped from.',
						'woocommerce_distance_rate_shipping' ),
				'class' => 'base-address',
				'desc_tip' => true,
			),
			'base_address_2' => array(
				'title' => __( 'Address 2', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'The second line of the address where items are shipped from.',
						'woocommerce_distance_rate_shipping' ),
				'class' => 'base-address',
				'desc_tip' => true,
			),
			'base_city' => array(
				'title' => __( 'City', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'The city where items are shipped from.',
						'woocommerce_distance_rate_shipping' ),
				'class' => 'base-address',
				'desc_tip' => true,
			),
			'base_postcode' => array(
				'title' => __( 'Zip/Post Code', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'The zip/post code where items are shipped from.',
						'woocommerce_distance_rate_shipping' ),
				'class' => 'base-address',
				'desc_tip' => true,
			),
			'base_country' => array(
				'title' => __( 'County/State/Country', 'woocommerce_distance_rate_shipping' ),
				'type' => 'text',
				'description' => __( 'The country where items are shipped from.',
						'woocommerce_distance_rate_shipping' ),
				'default' => $default_country,
				'class' => 'base-address',
				'desc_tip' => true,
			),
			'delivery_costs_table' => array(
				'type' => 'delivery_costs_table'
			),
		);
		if ( $this->id !== 'distance_rate_shipping' ) {
			unset( $this->form_fields['no_shipping_available'] );
			unset( $this->form_fields['show_route_map'] );
		}
		return ob_get_clean();
	}

	/**
	 * Don't need to validate text
	 */
	function validate_base_address_title_field( $key ) {
		return false;
	}

	/**
	 * Adds title to address in admin page
	 */
	function generate_base_address_title_html() {
		ob_start();
		print '<tr><th colspan="2">';
		_e( "Please enter your address details so we can calculate your customer's shipping distance from you. (If you only have one store, then this is the only address you need to enter. If you deliver from more than one address, then you should add a store for each address you ship from).",
				'woocommerce_distance_rate_shipping' );
		print '</th></tr>';
		return ob_get_clean();
	}

	/**
	 * This table is validated by jQuery
	 */
	function validate_delivery_costs_table_field( $key ) {
		return false;
	}

	/**
	 * Create the table of delivery rates within parent table
	 */
	function generate_delivery_costs_table_html() {
		ob_start();
		print '<tr style="vertical-align:top;"><th colspan="2">';
		_e( 'Delivery Rates', 'woocommerce_distance_rate_shipping' );
		print '</th></tr>';
		print '<tr><td colspan="2">';
		print $this->get_delivery_costs_table();
		print '</td>';
		print '</tr>';
		return ob_get_clean();
	}

	function add_text_condition( $label_before, $name, $distance_id,
			$default = null, $label_after = '' ) {
		$value = '';
		if ( $distance_id == '' && isset( $this->distance_rate_shipping_settings[$name] ) ) {
			$value = $this->distance_rate_shipping_settings[$name];
		} elseif ( isset( $this->current_rate[$name] ) ) {
			$value = $this->current_rate[$name];
		}
		if ( $value == '' && $default != null ) {
			$value = $default;
		}
		print '<label>' . $label_before . '
			<input type="text" name="distance_rate[' . $distance_id . '][' . $name . ']" value="' . $value . '" /> '
				. $label_after . '</label>';
	}

	/**
	 * Adds a condition as a select
	 */
	function add_select_condition( $label_before, $name, $distance_id, $options,
			$multi, $default = null, $label_after = '' ) {
		$value = '';
		if ( $distance_id == '' && isset( $this->distance_rate_shipping_settings[$name] ) ) {
			$value = $this->distance_rate_shipping_settings[$name];
		} elseif ( isset( $this->current_rate[$name] ) ) {
			$value = $this->current_rate[$name];
		}
		if ( $value == '' && $default != null ) $value = $default;
		print $label_before . '
		<select name="';
		if ( strval( $distance_id ) != '' ) {
			print 'distance_rate[' . $distance_id . '][' . $name . ']';
		} else {
			print 'distance_settings[' . $name . ']';
		}
		if ( $multi ) {
			print '[]';
		}
		print '" ';
		if ( $multi ) {
			print 'multiple ';
		}
		print ' class="select-' . $name . '" >';
		foreach ( $options as $option_id => $option_label ) {
			print '<option value="' . $option_id . '" ';
			if ( $option_id == $value || ($multi && is_array( $value ) && in_array( $option_id,
							$value )) ) {
				print ' selected ';
			}
			print '>' . $option_label . '</option>';
		}
		print '</select>
		' . $label_after;
	}

	function add_checkbox( $label_before, $label_after, $name, $value ) {
		print '<label>' . $label_before . '<input type="checkbox" value="yes" name="' . $name . '" ';
		if ( !empty( $value ) ) {
			print 'checked="checked"';
		}
		print ' /> ' . $label_after . '</label>';
	}

	function add_store_checkbox( $label_before, $label_after, $store_id,
			$distance_id ) {
		print '<label>' . $label_before . '<input type="checkbox" value="yes" name="distance_rate[' . $distance_id . '][store][' . $store_id . ']" ';
		if ( isset( $this->current_rate['store'][$store_id] ) ) {
			print 'checked="checked"';
		}
		print ' /> ' . $label_after . '</label>';
	}

	function add_numeric_condition( $label, $name, $distance_id, $unit, $unit_after ) {
		$after = '';
		$before = '&nbsp;';
		if ( $unit_after ) $after = ' ' . $unit;
		else $before = $unit;
		$min_value = '';
		if ( isset( $this->current_rate['minimum_' . $name] ) )
				$min_value = $this->current_rate['minimum_' . $name];
		$max_value = '';
		if ( isset( $this->current_rate['maximum_' . $name] ) )
				$max_value = $this->current_rate['maximum_' . $name];
		print '
	<td>' . $label . '</td>
	<td>' . $before . '<input class="numeric minimum minimum_' . $name . '" value="' . $min_value . '" type="text" class="numeric" placeholder="0" name="distance_rate[' . $distance_id . '][minimum_' . $name . ']" />' . $after . '</td>
	<td>' . $before . '<input class="numeric maximum maximum_' . $name . '" value="' . $max_value . '" type="text" class="numeric" placeholder="0" name="distance_rate[' . $distance_id . '][maximum_' . $name . ']" />' . $after . '</td>';
	}

	function add_numeric_cost( $label, $per, $name, $distance_id ) {
		$value = '';
		if ( isset( $this->current_rate['fee_per_' . $name] ) )
				$value = $this->current_rate['fee_per_' . $name];
		$zero_selected = '';
		$minimum_selected = 'selected';
		if ( isset( $this->current_rate['starting_from_' . $name] ) && $this->current_rate['starting_from_' . $name] == '0' ) {
			$zero_selected = 'selected';
			$minimum_selected = '';
		}
		print '
	<td>' . get_woocommerce_currency_symbol() . '<input class="numeric fee_per_' . $name . '" value="' . $value . '" type="text" class="numeric" placeholder="0" name="distance_rate[' . $distance_id . '][fee_per_' . $name . ']" />' . __( 'per',
						'woocommerce_distance_rate_shipping' ) . ' ' . $per . '</td>
	<td>' . __( ' starting from ', 'woocommerce_distance_rate_shipping' ) . '
	<select class="starting_from_' . $name . '" name="distance_rate[' . $distance_id . '][starting_from_' . $name . ']">
		<option value="minimum" ' . $minimum_selected . '>' . __( 'Minimum ',
						'woocommerce_distance_rate_shipping' ) . $label . '</option>
		<option value="0" ' . $zero_selected . '>' . __( '0 ',
						'woocommerce_distance_rate_shipping' ) . '</option>
	</select>
	</td>';
	}

	function display_numeric_condition( $label, $name, $unit, $unit_after ) {
		$after = '';
		$before = '';
		if ( $unit_after ) $after = ' ' . $unit;
		else $before = $unit;
		if ( (isset( $this->current_rate['minimum_' . $name] ) && $this->current_rate['minimum_' . $name] != '') || (isset( $this->current_rate['maximum_' . $name] ) && $this->current_rate['minimum_' . $name] != '') ) {
			if ( $this->first_display_condition ) $this->first_display_condition = false;
			else _e( ' and ', 'woocommerce_distance_rate_shipping' );
		}
		if ( isset( $this->current_rate['minimum_' . $name] ) && $this->current_rate['minimum_' . $name] != '' && isset( $this->current_rate['maximum_' . $name] ) && $this->current_rate['maximum_' . $name] != '' )
				print $label . __( ' is between ', 'woocommerce_distance_rate_shipping' ) . $before . $this->current_rate['minimum_' . $name] . $after . __( ' and ',
							'woocommerce_distance_rate_shipping' ) . $before . $this->current_rate['maximum_' . $name] . $after;
		elseif ( isset( $this->current_rate['minimum_' . $name] ) && $this->current_rate['minimum_' . $name] != '' )
				print $label . __( ' is above ', 'woocommerce_distance_rate_shipping' ) . $before . $this->current_rate['minimum_' . $name] . $after;
		elseif ( isset( $this->current_rate['maximum_' . $name] ) && $this->current_rate['maximum_' . $name] != '' )
				print $label . __( ' is below ', 'woocommerce_distance_rate_shipping' ) . $before . $this->current_rate['maximum_' . $name] . $after;
	}

	function display_numeric_cost( $label, $name, $unit, $unit_after, $plural_unit ) {
		if ( isset( $this->current_rate['fee_per_' . $name] ) && $this->current_rate['fee_per_' . $name] != '' && $this->current_rate['fee_per_' . $name] != '0' ) {
			$after = '';
			$before = '';
			if ( $unit_after ) $after = ' ' . $plural_unit;
			else $before = $plural_unit;
			if ( $this->first_display_cost ) $this->first_display_cost = false;
			else _e( ' plus ', 'woocommerce_distance_rate_shipping' );
			print get_woocommerce_currency_symbol() . $this->current_rate['fee_per_' . $name] . __( ' per ',
							'woocommerce_distance_rate_shipping' ) . $unit;
			_e( ' starting from ', 'woocommerce_distance_rate_shipping' );
			print $before . $this->starting_from( $name, $this->current_rate ) . $after;
		}
	}

	function starting_from( $name, $rate ) {
		$starting_from = 0;
		if ( $rate['starting_from_' . $name] == 'minimum' )
				$starting_from = $rate['minimum_' . $name];
		if ( $starting_from == '' ) $starting_from = 0;
		return $starting_from;
	}

	/**
	 * Prints the table of delivery rates
	 */
	function get_delivery_costs_table() {

		$maxId = -1;
		$ids = '';

		print '<div id="rules">';
		print '<h2>' . __( 'How to combine shipping rates?',
						'woocommerce_distance_rate_shipping' ) . '</h2>
		<p>' . __( 'When multiple Shipping Rates apply to an order, how would you like to combine the rates:',
						'woocommerce_distance_rate_shipping' ) . '</p>';
		$cat_options = array(
			'minimum' => __( 'Minimum - The cost is defined by the minimum rate that applies to the order.',
					'woocommerce_distance_rate_shipping' ),
			'maximum' => __( 'Maximum - The cost is defined by the maximum rate that applies to the order.',
					'woocommerce_distance_rate_shipping' ),
			'sum_order' => __( 'Sum Over Rates  - The cost is the sum of each rate that applies to the order.',
					'woocommerce_distance_rate_shipping' ),
			'sum_rows' => __( 'Sum Over Product Rows - The cost is the sum of the minimum cost of each product row.',
					'woocommerce_distance_rate_shipping' ),
		);
		$this->add_select_condition( '', 'calculate_shipping', '', $cat_options,
				false, 'minimum' );
		print '<br>';
		$this->add_checkbox( '',
				__( 'Require every row in cart to satisfy rules?',
						'woocommerce_distance_rate_shipping' ), 'distance_settings[all_rows_sat]',
				value( $this->distance_rate_shipping_settings, 'all_rows_sat' ) );

		print '
		<h2>' . __( 'Shipping Rates (i.e. Shipping Rules)',
						'woocommerce_distance_rate_shipping' ) . '</h2><p>';
		_e( 'Please enter your shipping rates:', 'woocommerce_distance_rate_shipping' );
		print '</p>';
		if ( !empty( $this->distance_rate_shipping_rates ) ) {
			foreach ( $this->distance_rate_shipping_rates as
						$distance_rate_shipping_rates_row_id => $rate ) {
				$this->get_distance_rate_shipping_row( $distance_rate_shipping_rates_row_id,
						$rate, $maxId, $ids );
			}
		}
		$ids = ltrim( $ids, ',' );
		print '</div>';
		print '<a class="button add-distance-rate">' . __( 'Add New Rate',
						'woocommerce_distance_rate_shipping' ) . '</a>';
		print '<input type="hidden" id="delivery_ids" name="delivery_ids" value="' . $ids . '"/>';
		print '<script type="text/javascript">
								var maxId = ' . $maxId . ';
';
		ob_start();
		$this->get_distance_rate_shipping_row( 'newRatenewRate', array(), $maxId, $ids );
		$new_row = ob_get_clean();
		$new_row = str_replace( '\'', '\\\'', $new_row );
		$new_row = str_replace( '\n', ' ', $new_row );
		$new_row = str_replace( '\r', ' ', $new_row );
		$new_row = str_replace( '
', ' ', $new_row );
		print '
			var newRow = \'' . $new_row . '\';
			</script>';
	}

	function opening_hours_array() {
		$times = array( '',
			'00:00', '00:30', '01:00', '01:30', '02:00', '02:30',
			'03:00', '03:30', '04:00', '04:30', '05:00', '05:30',
			'06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
			'10:00', '10:30', '11:00', '11:30',
			'12:00', '12:30', '13:00', '13:30',
			'14:00', '14:30', '15:00', '15:30',
			'16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
			'20:00', '20:30', '21:00', '21:30', '22:00', '22:30',
			'23:00', '23:30', );
		$array = array();
		foreach ( $times as $time ) {
			$array[$time] = $time;
		}
		return $array;
	}

	/**
	 * Returns one row of the delivery rate table using rate to get the values
	 */
	function get_distance_rate_shipping_row( $distance_rate_shipping_rates_row_id,
			$rate, &$maxId, &$ids ) {
		global $woocommerce;

		if ( $distance_rate_shipping_rates_row_id >= $maxId )
				$maxId = $distance_rate_shipping_rates_row_id;
		$ids .= ',' . $distance_rate_shipping_rates_row_id;

		$this->current_rate = $rate;
		$this->current_rate_id = $distance_rate_shipping_rates_row_id;
		print '<div class="distance-row distance-row-' . $distance_rate_shipping_rates_row_id . '"><div>
		<a class="button remove-distance-rate right">' . __( 'Remove Rate Below',
						'woocommerce_distance_rate_shipping' ) . '</a>
		<h3>' . __( 'Rule ', 'woocommerce_distance_rate_shipping' ) . '<span class="rule-number"></span></h3>';
		print '<p>';
		_e( 'Would you like this shipping rate to have its own name? If so, please choose a name. You can use shortcodes [delivery_time], [store_title] and [store_address].',
				'woocommerce_distance_rate_shipping' );
		print '<br />';
		$title = '';
		if ( isset( $this->current_rate['title'] ) ) {
			$title = $this->current_rate['title'];
		}
		print '<input class="rate-title" value="' . $title . '" type="text" class="rate-title" name="distance_rate[' . $distance_rate_shipping_rates_row_id . '][title]" placeholder="'
				. '' . __( 'Delivery', 'woocommerce_distance_rate_shipping' ) . '" />';
		print '</p>';

		$product_cats = get_terms( 'product_cat', array( "hide_empty" => 0 ) );
		$cat_options = array();
		foreach ( $product_cats as $cat ) {
			$cat_options[$cat->term_id] = $cat->name;
		}
		$this->add_select_condition( __( 'Apply costs/conditions for this rate to these product categories (leave none selected to apply to all product categories): ',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'product_cat',
				$distance_rate_shipping_rates_row_id, $cat_options, true );

//product
		$product_cats = get_posts( array(
			'posts_per_page' => -1,
			'post_type' => 'product' ) );
		$cat_options = array();
		foreach ( $product_cats as $cat ) {
			$cat_options[$cat->ID] = $cat->post_title;
		}
		$this->add_select_condition( '<br />' . __( 'Apply costs/conditions for this rate to these products (leave none selected to select apply rate to all products): ',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'product',
				$distance_rate_shipping_rates_row_id, $cat_options, true );

		$this->add_select_condition( '<br />' . __( 'Apply this rate to these countries (leave none selected to apply rule to customers in all countries): ',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'countries',
				$distance_rate_shipping_rates_row_id, $woocommerce->countries->countries,
				true );
		print '<br />';
		$country_codes = array();
		if ( $distance_rate_shipping_rates_row_id == '' && isset( $this->distance_rate_shipping_settings['countries'] ) ) {
			$country_codes = $this->distance_rate_shipping_settings['countries'];
		} elseif ( isset( $this->current_rate['countries'] ) ) {
			$country_codes = $this->current_rate['countries'];
		}
		$all_states = array();
		global $states;
		if ( !empty( $country_codes ) ) {
			foreach ( $country_codes as $country_code ) {
				$country_states = value( $states, $country_code, array() );
				if ( !empty( $country_states ) ) {
					foreach ( $country_states as $state_code => $state_name ) {
						$all_states[$state_code] = $state_name;
					}
				}
			}
		}
		print '<div class="select-states-wrapper">';
		if ( !empty( $all_states ) ) {
			$this->add_select_condition( '<br />' . __( 'Apply this rate to these states (leave none selected to apply rule to customers in all states): ',
							'woocommerce_distance_rate_shipping' ) . '<br />', 'states',
					$distance_rate_shipping_rates_row_id, $all_states, true );
		}
		print '</div>';
		$this->add_text_condition( '<br />' . __( 'Apply this rate to these comma separated zipcodes (e.g. "06513, 06514") using wildcards (e.g. 0651* for 06510-06519): ',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'zipcodes',
				$distance_rate_shipping_rates_row_id );
		print '<br />';
		_e( 'You can also select a delivery time (leave blank if you do not need the customer to select a delivery time)',
				'woocommerce_distance_rate_shipping' );
		print '<br />';
		$this->add_select_condition( '<br />' . __( 'Delivery Time Starting From:',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'open',
				$distance_rate_shipping_rates_row_id, $this->opening_hours_array(), false );
		print '<br />';
		$this->add_select_condition( '<br />' . __( 'Delivery Time Ending:',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'close',
				$distance_rate_shipping_rates_row_id, $this->opening_hours_array(), false );
		print '<br />';
		_e( 'You can also select delivery days (leave blank if you do not need the customer to select a delivery date)',
				'woocommerce_distance_rate_shipping' );
		print '<br />';
		$this->add_select_condition( '<br />' . __( 'Delivery Day:',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'delivery_day',
				$distance_rate_shipping_rates_row_id,
				array(
			'' => __( 'Not important', 'woocommerce_distance_rate_shipping' ),
			'1' => __( 'Sunday', 'woocommerce_distance_rate_shipping' ),
			'2' => __( 'Monday', 'woocommerce_distance_rate_shipping' ),
			'3' => __( 'Tuesday', 'woocommerce_distance_rate_shipping' ),
			'4' => __( 'Wednesday', 'woocommerce_distance_rate_shipping' ),
			'5' => __( 'Thursday', 'woocommerce_distance_rate_shipping' ),
			'6' => __( 'Friday', 'woocommerce_distance_rate_shipping' ),
			'7' => __( 'Saturday', 'woocommerce_distance_rate_shipping' ),
				), true );
		print '<br />';
		$this->add_select_condition( __( 'Do the products need to be on backorder?',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'is_on_backorder',
				$distance_rate_shipping_rates_row_id,
				array( '' => 'Not important', 'on_backorder' => 'Yes, at least one product must be on back order',
			'not_on_backorder' => 'No, none of the products must be on back order' ),
				false );
		print '<br />';
		$this->add_text_condition( '<br />' . __( 'Apply this rate to products that are found by this query (e.g. post_type=product&author=1&posts_per_page=-1): ',
						'woocommerce_distance_rate_shipping' ) . '<br />', 'query',
				$distance_rate_shipping_rates_row_id );
		print '<br />';
		print '<div class="row-content conditions-and-costs">';
		_e( 'If ', 'woocommerce_distance_rate_shipping' );
		$this->first_display_condition = true;
		$this->display_numeric_condition( __( 'Distance',
						'woocommerce_distance_rate_shipping' ), 'distance',
				'<span class="distance-unit-plural"></span>', true );
		$this->display_numeric_condition( __( 'Total',
						'woocommerce_distance_rate_shipping' ), 'order_total',
				get_woocommerce_currency_symbol(), false );
		$this->display_numeric_condition( __( 'Weight',
						'woocommerce_distance_rate_shipping' ), 'weight',
				__( 'kg', 'woocommerce_distance_rate_shipping' ), true );
		$this->display_numeric_condition( __( 'Volume',
						'woocommerce_distance_rate_shipping' ), 'volume',
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>', true );
		$this->display_numeric_condition( __( 'Dimensional Weight',
						'woocommerce_distance_rate_shipping' ), 'dimensional_weight',
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' . __( '/kg',
						'woocommerce_distance_rate_shipping' ), true );
		$this->display_numeric_condition( __( 'Quantity',
						'woocommerce_distance_rate_shipping' ), 'quantity',
				__( 'product(s)', 'woocommerce_distance_rate_shipping' ), true );
		_e( ' then charge ', 'woocommerce_distance_rate_shipping' );
		$this->first_display_cost = true;
		if ( isset( $rate['fee'] ) ) {
			$this->first_display_cost = false;
			print get_woocommerce_currency_symbol() . $rate['fee'];
		}
		$this->display_numeric_cost( __( 'Distance',
						'woocommerce_distance_rate_shipping' ), 'distance',
				'<span class="distance-unit"></span>', true,
				'<span class="distance-unit-plural"></span>' );
		$this->display_numeric_cost( __( 'Total', 'woocommerce_distance_rate_shipping' ),
				'order_total', get_woocommerce_currency_symbol(), false,
				get_woocommerce_currency_symbol() );
		$this->display_numeric_cost( __( 'Weight',
						'woocommerce_distance_rate_shipping' ), 'weight',
				__( 'kg', 'woocommerce_distance_rate_shipping' ), true,
				__( 'kg', 'woocommerce_distance_rate_shipping' ) );
		$this->display_numeric_cost( __( 'Volume',
						'woocommerce_distance_rate_shipping' ), 'volume',
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>', true,
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' );
		$this->display_numeric_cost( __( 'Dimensional Weight',
						'woocommerce_distance_rate_shipping' ), 'dimensional_weight',
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' . __( '/kg',
						'woocommerce_distance_rate_shipping' ), true,
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' . __( '/kg',
						'woocommerce_distance_rate_shipping' ) );
		$this->display_numeric_cost( __( 'Quantity',
						'woocommerce_distance_rate_shipping' ), 'quantity',
				__( 'product', 'woocommerce_distance_rate_shipping' ), true,
				__( 'product(s)', 'woocommerce_distance_rate_shipping' ) );
		_e( ' for shipping.', 'woocommerce_distance_rate_shipping' );
		print '</div></div>';
		print '<div class="row-container">';
		print '<table class="shippingrows widefat">
<tr><th>' . __( 'Variable', 'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Minimum',
						'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Maximum',
						'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Cost',
						'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Calculate Cost starting from',
						'woocommerce_distance_rate_shipping' ) . '</th></tr>';

		print '<tr><td>' . __( 'Fee', 'woocommerce_distance_rate_shipping' ) . '</td><td></td><td></td><td>';
		$fee = '';
		if ( isset( $rate['fee'] ) ) $fee = $rate['fee'];
		print '<input type="hidden" class="distance-id" value="' . $distance_rate_shipping_rates_row_id . '" />';
		print get_woocommerce_currency_symbol() . '<input class="fee" value="' . $fee . '" type="text" class="fee numeric" name="distance_rate[' . $distance_rate_shipping_rates_row_id . '][fee]" placeholder="0" />';
		print '</td><td>' . __( '(Flat fee when conditions fulfilled)',
						'woocommerce_distance_rate_shipping' ) . '</td>';
		print '</tr><tr>';

		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Total',
						'woocommerce_distance_rate_shipping' ), 'order_total',
				$distance_rate_shipping_rates_row_id, get_woocommerce_currency_symbol(),
				false );
		$this->add_numeric_cost( __( 'Total', 'woocommerce_distance_rate_shipping' ),
				get_woocommerce_currency_symbol(), 'order_total',
				$distance_rate_shipping_rates_row_id, false );
		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Distance',
						'woocommerce_distance_rate_shipping' ), 'distance',
				$distance_rate_shipping_rates_row_id,
				'<span class="distance-unit-plural"></span>', true );
		$this->add_numeric_cost( __( 'Distance', 'woocommerce_distance_rate_shipping' ),
				'<span class="distance-unit"></span>', 'distance',
				$distance_rate_shipping_rates_row_id, true );
		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Weight',
						'woocommerce_distance_rate_shipping' ), 'weight',
				$distance_rate_shipping_rates_row_id,
				__( 'kg', 'woocommerce_distance_rate_shipping' ), true );
		$this->add_numeric_cost( __( 'Weight', 'woocommerce_distance_rate_shipping' ),
				__( 'kg', 'woocommerce_distance_rate_shipping' ), 'weight',
				$distance_rate_shipping_rates_row_id, true );
		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Volume',
						'woocommerce_distance_rate_shipping' ), 'volume',
				$distance_rate_shipping_rates_row_id,
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>', true );
		$this->add_numeric_cost( __( 'Volume', 'woocommerce_distance_rate_shipping' ),
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>', 'volume',
				$distance_rate_shipping_rates_row_id, true );
		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Dimensional Weight',
						'woocommerce_distance_rate_shipping' ), 'dimensional_weight',
				$distance_rate_shipping_rates_row_id,
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' . __( '/kg',
						'woocommerce_distance_rate_shipping' ), true );
		$this->add_numeric_cost( __( 'Dimensional Weight',
						'woocommerce_distance_rate_shipping' ),
				get_option( 'woocommerce_dimension_unit' ) . '<sup>3</sup>' . __( '/kg',
						'woocommerce_distance_rate_shipping' ), 'dimensional_weight',
				$distance_rate_shipping_rates_row_id, true );
		print '</tr><tr>';
		$this->add_numeric_condition( __( 'Quantity',
						'woocommerce_distance_rate_shipping' ), 'quantity',
				$distance_rate_shipping_rates_row_id,
				__( 'product(s)', 'woocommerce_distance_rate_shipping' ), true );
		$this->add_numeric_cost( __( 'Quantity', 'woocommerce_distance_rate_shipping' ),
				__( 'product', 'woocommerce_distance_rate_shipping' ), 'quantity',
				$distance_rate_shipping_rates_row_id, true );
		print '</tr>';
		print '</table>';
		if ( $this->stores_exist() ) {
			_e( 'Please select which stores you would like this rate to apply to (you should select at least one store):',
					'woocommerce_distance_rate_shipping' );
			print '<br />';
//Add checkboxes for each store
			$this->add_store_checkbox( '', 'Base store (address above) &nbsp; ', 'base',
					$distance_rate_shipping_rates_row_id );
			$stores = get_posts( array( 'post_type' => 'store', 'posts_per_page' => -1 ) );
			foreach ( $stores as $store ) {
				$this->add_store_checkbox( '', $store->post_title . ' &nbsp; ', $store->ID,
						$distance_rate_shipping_rates_row_id );
			}
		}
		print '</div></div>';
	}

	/**
	 * See if need to look through stores
	 */
	function stores_exist() {
//How many stores have been created
		return count( get_posts( array( 'post_type' => 'store' ) ) ) > 0;
	}

	/**
	 * admin_options 
	 */
	function admin_options() {
		global $woocommerce;
		print '<h3>' . $this->method_title . '</h3>
<p>';
		_e( 'This is a fantastic shipping method that allows you to set the rate of shipping dependent on a wide range of variables including distance.',
				'woocommerce_distance_rate_shipping' );
		print '</p>
			<input type="hidden" id="distance-shipping-method" value="' . $this->id . '" />
<table class = "form-table">';
		$this->generate_settings_html();
		print '</table>';
	}

	function check_opening_hours() {
		if ( !$this->check_delivery_date() ) {
			return false;
		}
		if ( empty( $this->current_rate['open'] ) || empty( $this->current_rate['close'] ) ) {
			return true;
		}
		if ( !isset( $_SESSION['selected_opening_hours'] ) ) {//nothing selected yet
			return true;
		}
		$selected_times = $_SESSION['selected_opening_hours'];
		$selected_opening_hours = $this->get_opening_hours_from_times( $selected_times );
		if ( $this->current_rate['open'] == $selected_opening_hours['open'] && $this->current_rate['close'] == $selected_opening_hours['close'] ) {
			return true;
		}
		return false;
	}

	function check_delivery_date() {
		if ( empty( $this->current_rate['delivery_day'] ) || empty( $this->current_rate['delivery_day'][0] ) ) {
			return true;
		}
		if ( !isset( $_SESSION['selected_delivery_date'] ) ) {//nothing selected yet
			return false;
		}
		$date = strval( $_SESSION['selected_delivery_date'] );
		$day_of_week_selected = intval( date( 'w', strtotime( $date ) ) ) + 1;
		if ( in_array( $day_of_week_selected, $this->current_rate['delivery_day'] ) ) {
			return true;
		}
		return false;
	}

	function get_times( $shipping_rate ) {
		if ( !empty( $shipping_rate['open'] ) && !empty( $shipping_rate['close'] ) ) {
			return $shipping_rate['open'] . ' - ' . $shipping_rate['close'];
		}
		return '';
	}

	function get_opening_hours_from_times( $times ) {
		$opening_hours = explode( ' - ', $times );
		if ( count( $opening_hours ) == 2 ) {
			return array( 'open' => $opening_hours[0],
				'close' => $opening_hours[1] );
		}
		return array();
	}

	function prepare_zipcode( $zipcode ) {
		return str_replace( ' ', '', strtolower( $zipcode ) );
	}

	function check_zipcode( $package ) {
		if ( empty( $this->current_rate['zipcodes'] ) ) {
			return true;
		}
		$zipcodes = array_map( array( $this, 'prepare_zipcode' ),
				explode( ',', $this->current_rate['zipcodes'] ) );
		if ( isset( $package['destination']['postcode'] ) ) {
			$destination_zipcode = $this->prepare_zipcode( $package['destination']['postcode'] );
			foreach ( $zipcodes as $zipcode ) {
				$zipcode = str_replace( '*', '[a-z0-9]+', $zipcode );
				if ( preg_match( "/^{$zipcode}$/", $destination_zipcode ) ) {
					return true;
				}
			}
		}
		return false;
	}

	function check_country( $package ) {
		if ( !$this->check_state( $package ) ) {
			return false;
		}
		if ( empty( $this->current_rate['countries'] ) ) {
			return true;
		}
		if ( isset( $package['destination']['country'] ) &&
				in_array( $package['destination']['country'],
						$this->current_rate['countries'] ) ) {
			return true;
		}
		return false;
	}

	function check_state( $package ) {
		if ( empty( $this->current_rate['states'] ) ) {
			return true;
		}
		if ( isset( $package['destination']['state'] ) &&
				in_array( $package['destination']['state'], $this->current_rate['states'] ) ) {
			return true;
		}
		return false;
	}

	function is_available_for_address( $id, $store_address, $package ) {
		$distance = $this->calculate_distance( $id, $store_address,
				$this->get_address( $package['destination'] ) );
		$order_total = $package['contents_cost'];
		if ( !empty( $this->distance_rate_shipping_rates ) && $this->distance_rate_shipping_settings['calculate_shipping'] != 'sum_rows' ) {
			foreach ( $this->distance_rate_shipping_rates as $delivery_rate_id =>
						$delivery_rate ) {
				$this->current_rate = $this->distance_rate_shipping_rates[$delivery_rate_id];
				$this->current_rate_id = $delivery_rate_id;
				$this->init_posts_from_query();
				$volume_and_weight = $this->calculate_volume_and_weight( $package );
				$volume = $volume_and_weight['volume'];
				$weight = $volume_and_weight['weight'];
				$quantity = $volume_and_weight['quantity'];
				$order_total = $volume_and_weight['total'];
				$is_on_backorder = $volume_and_weight['is_on_backorder'];
				$dimensional_weight = 0.0;
				if ( floatval( $weight ) > 0 ) {
					$dimensional_weight = floatval( $volume ) / floatval( $weight );
				}
				if ( $this->check_on_backorder( $delivery_rate, $is_on_backorder ) && $this->check_opening_hours() && $this->check_country( $package ) && $this->check_zipcode( $package ) && $this->check_class_conditions_for_order( $package ) && $this->check_condition( 'quantity',
								$delivery_rate, $quantity ) && $this->check_condition( 'volume',
								$delivery_rate, $volume ) && $this->check_condition( 'weight',
								$delivery_rate, $weight ) && $this->check_condition( 'order_total',
								$delivery_rate, $order_total ) && $this->check_condition( 'distance',
								$delivery_rate, $distance ) && $this->check_condition( 'dimensional_weight',
								$delivery_rate, $dimensional_weight ) ) {
					return true;
				}
			}
		} elseif ( $this->distance_rate_shipping_settings['calculate_shipping'] == 'sum_rows' ) {
			foreach ( $package['contents'] as $order_line ) {
				$line_condition_met = false;
				foreach ( $this->distance_rate_shipping_rates as $delivery_rate_id =>
							$delivery_rate ) {
					$this->current_rate = $this->distance_rate_shipping_rates[$delivery_rate_id];
					$this->current_rate_id = $delivery_rate_id;
					$this->init_posts_from_query();
					$volume_and_weight = $this->calculate_line_volume_and_weight( $order_line );
					$volume = $volume_and_weight['volume'];
					$weight = $volume_and_weight['weight'];
					$quantity = $volume_and_weight['quantity'];
					$order_total = $volume_and_weight['line_total'];
					$is_on_backorder = $volume_and_weight['is_on_backorder'];
					$dimensional_weight = 0.0;
					if ( floatval( $weight ) > 0 ) {
						$dimensional_weight = floatval( $volume ) / floatval( $weight );
					}
					if ( !( $this->check_on_backorder( $delivery_rate, $is_on_backorder ) && $this->check_opening_hours() && $this->check_country( $package ) && $this->check_zipcode( $package ) && $this->check_class_conditions_for_row( $order_line ) && $this->check_condition( 'quantity',
									$delivery_rate, $quantity ) && $this->check_condition( 'volume',
									$delivery_rate, $volume ) && $this->check_condition( 'weight',
									$delivery_rate, $weight ) && $this->check_condition( 'order_total',
									$delivery_rate, $order_total ) && $this->check_condition( 'distance',
									$delivery_rate, $distance ) && $this->check_condition( 'dimensional_weight',
									$delivery_rate, $dimensional_weight )
							) ) continue;
					$line_condition_met = true;
				}
				if ( !$line_condition_met ) return false;
			}
			return true;
		}
		return false;
	}

	function save_addresses() {
		if ( isset( $_POST['post_data'] ) ) {
			parse_str( $_POST['post_data'], $data );
			if ( isset( $data['base-' . $this->id] ) ) {
				foreach ( $data as $id => $store ) {
					if ( $id == 'base-' . $this->id || is_int( $id ) ) {
						$_SESSION['store-' . $id] = $store;
					}
				}
			}
		}
		if ( isset( $_POST['stores'] ) && !empty( $_POST['stores'] ) ) {
			foreach ( $_POST['stores'] as $id => $store ) {
				if ( $id === 'base-' . $this->id || is_int( $id ) ) {
					$_SESSION['store-' . $id] = $store;
				}
			}
		}
	}

	/**
	 * is_available function.
	 *
	 * @access public
	 * @param array $package
	 * @return bool
	 */
	function is_available( $package ) {
		unset( $_SESSION['delivery-rate-' . $this->id] );
		//$this->save_addresses();
		if ( $this->enabled == "no" ) {
			return false;
		}

		$this->current_store = 'base';
		if ( $this->is_available_for_address( 'base', $this->get_base_address(),
						$package ) ) {
			return true;
		}
		$stores = get_posts( array(
			'posts_per_page' => -1,
			'post_type' => 'store' ) );
		if ( !empty( $stores ) ) {
			foreach ( $stores as $store ) {
				$this->current_store = $store->ID;
				$store_address = get_post_meta( $store->ID, 'store_address_1', true );
				$store_address .= ', ' . get_post_meta( $store->ID, 'store_address_2', true );
				$store_address .= ', ' . get_post_meta( $store->ID, 'store_address_3', true );
				$store_address .= ', ' . get_post_meta( $store->ID, 'store_address_4', true );
				$store_address = $this->tidy_address( $store_address );
				if ( $this->is_available_for_address( $store->ID, $store_address, $package ) )
						return true;
			}
		}
		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available',
				false, $package );
	}

	function check_condition( $name, $delivery_rate, $value ) {
		if ( $name == 'distance' && $value >= 9999999 ) {
			return false;
		}
		if ( !empty( $delivery_rate['minimum_' . $name] ) && $value < $delivery_rate['minimum_' . $name] ) {
			return false;
		}
		if ( !empty( $delivery_rate['maximum_' . $name] ) && $value > $delivery_rate['maximum_' . $name] ) {
			return false;
		}
		return true;
	}

	function get_cost( $name, $delivery_rate, $value ) {
		if ( !isset( $delivery_rate['fee_per_' . $name] ) || $delivery_rate['fee_per_' . $name] == '' ) {
			return 0;
		}
		$starting_from = $this->starting_from( $name, $delivery_rate );
		$value = $value - $starting_from;
		$rate_per = $delivery_rate['fee_per_' . $name];
		return $rate_per * $value;
	}

	function calculate_volume_and_weight( $package ) {
		$volume = 0;
		$weight = 0;
		$quantity = 0;
		$total = 0;
		$is_on_backorder = false;
		foreach ( $package['contents'] as $order_line ) {
			$calculated_line = $this->calculate_line_volume_and_weight( $order_line );
			$volume = $volume + $calculated_line['volume'];
			$weight = $weight + $calculated_line['weight'];
			$quantity = $quantity + $calculated_line['quantity'];
			$total = $total + $calculated_line['line_total'];
			if ( !empty( $calculated_line['is_on_backorder'] ) ) $is_on_backorder = true;
		}
		return array( 'volume' => $volume, 'weight' => $weight, 'quantity' => $quantity,
			'total' => $total, 'is_on_backorder' => $is_on_backorder );
	}

	function calculate_line_volume_and_weight( $order_line ) {
		$volume = 0;
		$weight = 0;
		$quantity = 0;
		$total = $order_line['line_total'];
		$product = get_product( $order_line['product_id'] );
		if ( !$this->check_class_conditions_for_row( $order_line ) ) {
			return array( 'volume' => 0, 'weight' => 0, 'quantity' => 0, 'line_total' => 0 );
		}
		$quantity += floatval( $order_line['quantity'] );
		$is_on_backorder = $product->is_on_backorder( $quantity );
		$product_data = ( array ) $order_line['data'];
		if ( isset( $product_data['weight'] ) ) {
			$weight += floatval( value( $product_data, 'weight', 9999999999 ) ) * $quantity;
		} else {
			$product_weight = get_post_meta( $order_line['product_id'], '_weight', true );
			if ( !empty( $product_weight ) ) {
				$weight += floatval( $product_weight ) * $quantity;
			}
		}
		if ( isset( $product_data['length'] ) && isset( $product_data['width'] ) && isset( $product_data['height'] ) ) {
			$volume += $quantity * floatval( value( $product_data, 'length', 9999999999 ) ) *
					floatval( value( $product_data, 'width', 9999999999 ) ) * floatval( value( $product_data,
									'height', 9999999999 ) );
		} else {
			$length = get_post_meta( $order_line['product_id'], '_weight', true );
			$width = get_post_meta( $order_line['product_id'], '_width', true );
			$height = get_post_meta( $order_line['product_id'], '_height', true );
			if ( !empty( $length ) && !empty( $width ) && !empty( $height ) ) {
				$volume += $quantity * floatval( $length ) * floatval( $width ) * floatval( $height );
			}
		}
		return array( 'volume' => $volume, 'weight' => $weight, 'quantity' => $quantity,
			'line_total' => $total, 'is_on_backorder' => $is_on_backorder );
	}

	function check_query_condition_for_row( $order_line ) {
		$query = trim( value( $this->current_rate, 'query' ) );
		if ( empty( $query ) ) {
			return true;
		}
		$product_id = intval( $order_line['product_id'] );
		if ( in_array( $product_id, $this->query_posts ) ) {
			return true;
		}
		return false;
	}

	function check_class_conditions_for_row( $order_line ) {
		$order_line_query = $this->check_query_condition_for_row( $order_line );
		if ( empty( $order_line_query ) ) {
			return false;
		}
		$product = get_product( $order_line['product_id'] );
		if ( !isset( $this->current_rate['product_cat'] ) && !isset( $this->current_rate['product'] ) ) {
			return true;
		}
		if ( !empty( $this->current_rate['product_cat'] ) ) {
			$product_cats = get_the_terms( $product->id, 'product_cat' );
			$category_found = false;
			if ( !empty( $product_cats ) ) {
				foreach ( $product_cats as $product_cat ) {
					if ( in_array( $product_cat->term_id, $this->current_rate['product_cat'] ) ) {
						$category_found = true;
					}
				}
			}
			if ( !$category_found ) {
				return false;
			}
		}
		if ( !empty( $this->current_rate['product'] ) && !in_array( $product->id,
						$this->current_rate['product'] ) ) {
			return false;
		}
		return true;
	}

	function check_class_conditions_for_order( $package ) {
		foreach ( $package['contents'] as $order_line ) {
			if ( $this->check_class_conditions_for_row( $order_line ) ) {
				return true;
			} else {
				$all_rows_sat = value( $this->distance_rate_shipping_settings,
						'all_rows_sat' );
				if ( !empty( $all_rows_sat ) ) {
					return false;
				}
			}
		}
		return false;
	}

	function change_shipping_error_message( $error ) {
		if ( $error === __( 'Invalid shipping method.',
						'woocommerce_distance_rate_shipping' ) ) {
			$error = $this->get_option( 'invalid_shipping_address' );
		}
		return $error;
	}

	function woocommerce_checkout_update_order_meta( $order_id, $posted ) {
		if ( !empty( $_SESSION['selected_opening_hours'] ) ) {
			add_post_meta( $order_id, 'delivery time',
					$_SESSION['selected_opening_hours'], true );
		}
		if ( !empty( $_SESSION['selected_delivery_date'] ) ) {
			add_post_meta( $order_id, 'delivery date',
					$_SESSION['selected_delivery_date'], true );
		}
	}

	function woocommerce_email_after_order_table( $order, $sent_to_admin,
			$plain_text ) {
		if ( $sent_to_admin && !empty( $_SESSION['after_order_table_added_admin'] ) ) {
			return;
		}
		if ( !$sent_to_admin && !empty( $_SESSION['after_order_table_added_customer'] ) ) {
			return;
		}
		if ( $sent_to_admin ) {
			$_SESSION['after_order_table_added_admin'] = true;
		} else {
			$_SESSION['after_order_table_added_customer'] = true;
		}
		if ( !empty( $_SESSION['selected_opening_hours'] ) ) {
			print '<br>Delivery time: ' . $_SESSION['selected_opening_hours'];
		}
		if ( !empty( $_SESSION['selected_delivery_date'] ) ) {
			print '<br>Delivery date: ' . $_SESSION['selected_delivery_date'];
		}
	}

}

/** object oriented * */
$distance_rate_shipping = new WC_Distance_Rate_Shipping();
?>