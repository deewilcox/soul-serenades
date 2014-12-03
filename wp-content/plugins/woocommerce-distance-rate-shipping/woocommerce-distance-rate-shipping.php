<?php

/**
 * Plugin Name: WooCommerce Distance Rate Shipping
 * Plugin URI: http://codecanyon.net/item/woocommerce-distance-rate-shipping/5586711?ref=WPShowCase
 * Description: Distance Rate Shipping is a fantastic premium plugin which allows you set versatile shipping rates using several variables including distance.
 * Author: WPShowCase
 * Version: 4.2.10
 * Author URI: http://www.codecanyon.net/user/wpshowcase/portfolio?ref=WPShowCase 
 */
if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	
//Make sure woocommerce has been activated
if ( in_array( 'woocommerce/woocommerce.php',
				apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !class_exists( 'Woocommerce_Distance_Rate_Shipping' ) ) {

	if ( !function_exists( 'value' ) ) {

		function value( $array, $index, $default = '' ) {
			if ( isset( $array[$index] ) ) {
				return $array[$index];
			}
			return $default;
		}

	}

//Include woocommerce
	require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
	require_once dirname( __FILE__ ) . '/classes/distance-rate-shipping.php';
	require_once dirname( __FILE__ ) . '/classes/collection-shipping-rate.php';
	require_once dirname( __FILE__ ) . '/classes/special-delivery-shipping-rate.php';
	if ( !in_array( 'custom-store-locator/custom-store-locator.php',
					apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
//The store locator also uses custom post type Store. If it's not been added then create custom store type
		require_once dirname( __FILE__ ) . '/classes/store-post-type.php';
	}

	class Woocommerce_Distance_Rate_Shipping {

		/**
		 * All methods for plugin but not for extended classes placed in here
		 */
		function __construct() {
			add_filter( 'woocommerce_billing_fields',
					array( $this, 'set_default_billing_address' ) );
			add_filter( 'woocommerce_shipping_fields',
					array( $this, 'set_default_shipping_address' ) );
			add_filter( 'woocommerce_get_country_locale',
					array( $this, 'get_country_locale' ) );
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ), 1 );
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ), 1 );
			add_action( 'wp_footer', array( $this, 'add_street_to_cart_script' ), 1 );
			add_action( 'woocommerce_calculated_shipping',
					array( $this, 'add_address_to_calculate_shipping' ) );
			load_plugin_textdomain( 'woocommerce_distance_rate_shipping', false,
					dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ),
					array( $this, 'settings_link' ) );
			add_action( 'wp_ajax_change_select', array( $this, 'ajax_change_select' ) );
			add_action( 'wp_ajax_nopriv_change_select',
					array( $this, 'ajax_change_select' ) );
			add_action( 'wp_ajax_change_delivery_date',
					array( $this, 'ajax_change_delivery_date' ) );
			add_action( 'wp_ajax_nopriv_change_delivery_date',
					array( $this, 'ajax_change_delivery_date' ) );
			add_action( 'wp_ajax_shipping_save_addresses',
					array( $this, 'save_addresses' ) );
			add_action( 'wp_ajax_nopriv_shipping_save_addresses',
					array( $this, 'save_addresses' ) );
			add_action( 'wp_ajax_get_state_select',
					array( $this, 'ajax_get_state_select' ) );
			add_action( 'wp_ajax_nopriv_get_state_select',
					array( $this, 'ajax_get_state_select' ) );
		}

		function ajax_get_state_select() {
			global $states;
			global $distance_rate_shipping;
			$country_codes = $_POST['country_codes'];
			$id = strval( $_POST['id'] );
			$all_states = array();
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
			if ( empty( $all_states ) ) {
				die();
			}
			$distance_rate_shipping->add_select_condition( '<br />' . __( 'Apply this rate to these states (leave none selected to apply rule to customers in all states): ',
							'woocommerce_distance_rate_shipping' ) . '<br />', 'states', $id,
					$all_states, true );
			die();
		}

		function save_addresses() {
			if ( isset( $_POST['stores'] ) && !empty( $_POST['stores'] ) ) {
				global $collection_shipping_rate;
				global $distance_rate_shipping;
				global $special_shipping_rate;
				foreach ( $_POST['stores'] as $id => $distance ) {
					if ( $id === 'base-' . $distance_rate_shipping->id ||
							$id === 'base-' . $collection_shipping_rate->id ||
							$id === 'base-' . $special_shipping_rate->id || is_int( $id ) ) {
						$_SESSION['store-' . $id] = $distance;
					}
				}
			}
			die();
		}

		/**
		 * Add a setting link to the plugins page
		 */
		public function settings_link( $links ) {
			if ( file_exists( plugin_dir_path( __FILE__ ) . '../../woocommerce/classes/abstracts/abstract-wc-email.php' ) ) {
				$settings = '<a href="admin.php?page=woocommerce_settings&tab=shipping">' . __( 'Reorder Shipping Methods',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=woocommerce_settings&tab=shipping&section=WC_Special_Delivery_Rate">' . __( 'Special Delivery Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=woocommerce_settings&tab=shipping&section=WC_Collection_Shipping_Rate">' . __( 'Collection Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=woocommerce_settings&tab=shipping&section=WC_Distance_Rate_Shipping">' . __( 'Delivery Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
			} else {
				$settings = '<a href="admin.php?page=wc-settings&tab=shipping&section=">' . __( 'Reorder Shipping Methods',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=wc-settings&tab=shipping&section=wc_special_delivery_rate">' . __( 'Special Delivery Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=wc-settings&tab=shipping&section=wc_collection_shipping_rate">' . __( 'Collection Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
				$settings = '<a href="admin.php?page=wc-settings&tab=shipping&section=wc_distance_rate_shipping">' . __( 'Delivery Settings',
								'woocommerce_distance_rate_shipping' ) . '</a>';
				array_unshift( $links, $settings );
			}
			return $links;
		}

		/**
		 * Saves the first line of the address when calculate shipping updated
		 */
		function add_address_to_calculate_shipping() {
			global $woocommerce;
			if ( isset( $_POST['calc_shipping_address'] ) ) {
				$address = woocommerce_clean( $_POST['calc_shipping_address'] );
			} else {
				$address = '';
			}
			$woocommerce->customer->set_shipping_address( $address );
			$woocommerce->customer->set_address( $address );
		}

		/**
		 * activate function - creates table in database
		 */
		function activate() {
			global $wpdb;
			$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_shipping_locations (
			  woocommerce_shipping_locations_id int AUTO_INCREMENT,
			  PRIMARY KEY (woocommerce_shipping_locations_id),
			  address VARCHAR(1024) NOT NULL,
			  lat double,
			  lng double,
			  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			);";
			$wpdb->query( $sql );

			$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_shipping_road_distances (
			  woocommerce_shipping_locations_id int AUTO_INCREMENT,
			  PRIMARY KEY (woocommerce_shipping_locations_id),
			  address_from VARCHAR(1024) NOT NULL,
              address_to VARCHAR(1024) NOT NULL,
			  distance double,
			  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			);";
			$wpdb->query( $sql );

//Make sure this is added near the top of the shipping plugins so that admin can see it
			$shipping_method_order = get_option( 'woocommerce_shipping_method_order' );
			foreach ( $shipping_method_order as $key => $value ) {
				$shipping_method_order[$key] = $value + 3;
			}
			$shipping_method_order['distance_rate_shipping'] = 0;
			$shipping_method_order['collection_shipping_rate'] = 1;
			$shipping_method_order['special_delivery_shipping'] = 1;
			update_option( 'woocommerce_shipping_method_order', $shipping_method_order );
		}

		/**
		 * uninstall function - deletes table from database
		 */
		public function uninstall() {
			global $wpdb;
			$sql = "DROP TABLE {$wpdb->prefix}woocommerce_shipping_locations;";
			$sql = "DROP TABLE {$wpdb->prefix}woocommerce_shipping_road_distances;";
			$wpdb->query( $sql );
		}

		/**
		 * Lots of countries don't use post codes.
		 */
		function get_country_locale( $countries ) {
			foreach ( $countries as $country_code => $country ) {
				$countries[$country_code]['postcode']['required'] = true;
				$countries[$country_code]['state']['required'] = true;
			}
			$countries['KE']['postcode']['required'] = false;
			$countries['LK']['postcode']['required'] = false;
			$countries['IE']['postcode']['required'] = false;
			return $countries;
		}

		function set_default_billing_address( $fields ) {
			global $woocommerce;
			if ( $woocommerce && $woocommerce->customer ) {
				$fields['billing_address_1']['default'] = $woocommerce->customer->get_address();
			}
			return $fields;
		}

		function set_default_shipping_address( $fields ) {
			global $woocommerce;
			if ( $woocommerce != null && get_class( $woocommerce->customer ) === 'WC_Customer' && method_exists( $woocommerce->customer,
							'get_shipping_address' ) ) {
				$fields['shipping_address_1']['default'] = $woocommerce->customer->get_shipping_address();
			}
			return $fields;
		}

		function get_all_opening_times() {
			global $distance_rate_shipping;
			global $special_shipping_rate;
			global $collection_shipping_rate;
			$opening_times = $this->get_opening_times( $distance_rate_shipping, array() );
			$opening_times = $this->get_opening_times( $special_shipping_rate,
					$opening_times );
			$opening_times = $this->get_opening_times( $collection_shipping_rate,
					$opening_times );
			return $opening_times;
		}

		function need_delivery_date() {
			global $distance_rate_shipping;
			global $special_shipping_rate;
			global $collection_shipping_rate;
			if ( $this->rate_needs_delivery_date( $distance_rate_shipping ) ) {
				return true;
			}
			if ( $this->rate_needs_delivery_date( $special_shipping_rate ) ) {
				return true;
			}
			if ( $this->rate_needs_delivery_date( $collection_shipping_rate ) ) {
				return true;
			}
			return false;
		}

		function rate_needs_delivery_date( $rate ) {
			if ( !empty( $rate->distance_rate_shipping_rates ) ) {
				foreach ( $rate->distance_rate_shipping_rates as $shipping_rate ) {
					if ( !empty( $shipping_rate['delivery_day'] ) && $shipping_rate['delivery_day'] != array(
						'' ) ) {
						return true;
					}
				}
			}
			return false;
		}

		function get_times_as_select() {
			$opening_times = $this->get_all_opening_times();
			$select = '<select name="distanceopeninghours" id="distanceopeninghours"><option value="-1">' .
					__( 'Select delivery time', 'woocommerce_distance_rate_shipping' ) . '</option>';
			foreach ( $opening_times as $opening_time ) {
				$select .= '<option value="' . $opening_time . '" ';
				if ( isset( $_SESSION['selected_opening_hours'] ) && $_SESSION['selected_opening_hours'] == $opening_time )
						$select .= 'selected="selected" ';
				$select .= '>' . $opening_time . '</option>';
			}
			$select .= '</select>';
			return $select;
		}

		function get_opening_times( $rate, $opening_times = array() ) {
			if ( !empty( $rate->distance_rate_shipping_rates ) ) {
				foreach ( $rate->distance_rate_shipping_rates as $shipping_rate ) {
					$times = $rate->get_times( $shipping_rate );
					if ( !empty( $times ) ) {
						$opening_times[$times] = $times;
					}
				}
			}
			return $opening_times;
		}

		function ajax_change_select() {
			$_SESSION['after_order_table_added_admin'] = false;
			$_SESSION['after_order_table_added_customer'] = false;
			define( 'WP_USE_THEMES', false );
			global $wpdb;
			$_SESSION['selected_opening_hours'] = $_POST['value'];
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%') OR `option_name` LIKE ('_transient_timeout_wc_ship_%')" );
			die();
		}

		function ajax_change_delivery_date() {
			$_SESSION['after_order_table_added_admin'] = false;
			$_SESSION['after_order_table_added_customer'] = false;
			define( 'WP_USE_THEMES', false );
			global $wpdb;
			$_SESSION['selected_delivery_date'] = $_POST['value'];
			$wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_wc_ship_%') OR `option_name` LIKE ('_transient_timeout_wc_ship_%')" );
			die();
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
							$this->change_slashes( WP_CONTENT_URL ),
							$this->change_slashes( __FILE__ ) ) );
		}

		function frontend_styles() {
			wp_register_style( 'woocommerce-distance-rate-shipping-frontend-css',
					$this->dir() . '/css/woocommerce-distance-rate-shipping-frontend.css' );
			wp_enqueue_style( 'woocommerce-distance-rate-shipping-frontend-css' );
			wp_enqueue_style( 'jquery-ui-css', $this->dir() . '/css/jquery-ui.css' );
		}

		function add_street_to_cart_script() {
			$address = '';
			global $woocommerce;
			if ( $woocommerce != null && get_class( $woocommerce->customer ) === 'WC_Customer' && method_exists( $woocommerce->customer,
							'get_shipping_address' ) ) {
				$address = $woocommerce->customer->get_shipping_address();
			}
			wp_enqueue_script( 'woocommerce-distance-add-street-to-cart',
					WP_PLUGIN_URL . '/woocommerce-distance-rate-shipping/js/add-street-to-cart.js',
					false, true );
			wp_localize_script( 'woocommerce-distance-add-street-to-cart',
					'add_street_to_cart_settings',
					array( 'street_address_placeholder' => __( 'Street Address',
						'woocommerce_distance_rate_shipping'
				), 'shipping_address' => $address, ) );
		}

		/**
		 * JS for the frontend to add the shipping rate
		 */
		function frontend_scripts() {
			global $woocommerce;
			global $distance_rate_shipping;
			global $special_shipping_rate;
			global $collection_shipping_rate;
			$need_road_distance = false;
			if ( $distance_rate_shipping->get_option( 'distance_calculation' ) === 'road' )
					$need_road_distance = true;
			if ( $special_shipping_rate->get_option( 'distance_calculation' ) === 'road' )
					$need_road_distance = true;
			if ( $collection_shipping_rate->get_option( 'distance_calculation' ) === 'road' )
					$need_road_distance = true;
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'google-api',
					'https://maps.google.com/maps/api/js?sensor=false&language=' . str_replace( '_',
							'-', get_locale() ) );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'woocommerce-distance-rate-shipping-frontend',
					$this->dir() . '/js/woocommerce-distance-rate-shipping-frontend.js',
					array( 'jquery', 'google-api', 'jquery-ui-core', 'jquery-ui-datepicker' ),
					false, true );
//Add all the stores and base addresses to javascript	
			$localize_array['need_road_distance'] = $need_road_distance;
			$localize_array['stores'] = array();
			global $distance_rate_shipping;
			$localize_array['showRouteMap'] = $distance_rate_shipping->get_option( 'show_route_map' );
			$localize_array['address_error'] = __( 'Please enter more details into your address so that we can calculate your shipping cost',
					'woocommerce_distance_rate_shipping' );
			$localize_array['delivery_times'] = false;
			$all_opening_hours = $this->get_all_opening_times();
			if ( !empty( $all_opening_hours ) && $all_opening_hours != array( '' => '' ) ) {
				$localize_array['delivery_times'] = true;
			}
			$localize_array ['distanceopeninghours'] = $this->get_times_as_select();
			$localize_array['delivery_dates'] = $this->need_delivery_date();
			$localize_array['selected_delivery_date'] = '';
			if ( !empty( $_SESSION['selected_delivery_date'] ) ) {
				$localize_array['selected_delivery_date'] = $_SESSION['selected_delivery_date'];
			}
			$localize_array['ajaxurl'] = admin_url( 'admin-ajax.php' );
			$stores = get_posts( array(
				'posts_per_page' => -1,
				'post_type' => 'store' ) );
			if ( !empty( $stores ) ) {
				foreach ( $stores as $store ) {
					$store_address = $distance_rate_shipping->get_store_address( $store->ID );
					$localize_array['stores'][$store->ID] = $store_address;
				}
			}
//Add the base addresses too.
			if ( $distance_rate_shipping->enabled !== 'no' )
					$localize_array['stores']['base-' . $distance_rate_shipping->id] = $distance_rate_shipping->tidy_address( $distance_rate_shipping->get_base_address() );
			if ( $special_shipping_rate->enabled !== 'no' )
					$localize_array['stores']['base-' . $special_shipping_rate->id] = $special_shipping_rate->tidy_address( $special_shipping_rate->get_base_address() );
			if ( $collection_shipping_rate->enabled !== 'no' )
					$localize_array['stores']['base-' . $collection_shipping_rate->id] = $collection_shipping_rate->tidy_address( $collection_shipping_rate->get_base_address() );
			$localize_array['delivery_time'] = __( 'Delivery Time',
					'woocommerce_distance_rate_shipping' );
			$localize_array['delivery_date'] = __( 'Delivery Date',
					'woocommerce_distance_rate_shipping' );
			$localize_array['select_delivery_time'] = __( 'Please select a delivery time:',
					'woocommerce_distance_rate_shipping' );
			$localize_array['select_delivery_date'] = __( 'Please select a delivery date:',
					'woocommerce_distance_rate_shipping' );
			wp_localize_script( 'woocommerce-distance-rate-shipping-frontend',
					'distance_rate_shipping_settings', $localize_array );
		}

	}

	$woocommerce_distance_rate_shipping = new Woocommerce_Distance_Rate_Shipping();
}
?>