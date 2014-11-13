<?php
/*
Plugin Name: WooCommerce Free Gift
Plugin URI: http://codecanyon.net/item/woocommerce-free-gift/6144902
Description: Allows for rewarding customers a free gift when they spend at least a specified amount of money on purchase.
Author: Rene Puchinger
Version: 1.4
Author URI: http://codecanyon.net/user/renp

Copyright (C) 2013 - 2014 Rene Puchinger

@package WooCommerce_Free_Gift
@since 1.0

*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return; // Check if WooCommerce is active

if ( !class_exists( 'WooCommerce_Free_Gift' ) ) {

	class WooCommerce_Free_Gift {

		var $last_msg = '';

		public function __construct() {

			load_plugin_textdomain( 'wc_free_gift', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

			$this->current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : 'general';

			// Tab under WooCommerce settings
			$this->settings_tabs = array(
				'wc_free_gift' => __( 'Free Gift', 'wc_free_gift' )
			);

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

			add_action( 'woocommerce_settings_tabs', array( $this, 'add_tab' ), 10 );

			foreach ( $this->settings_tabs as $name => $label ) {
				add_action( 'woocommerce_settings_tabs_' . $name, array( $this, 'settings_tab_action' ), 10 );
				add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
			}

			// enqueue scripts
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dependencies_admin' ) );
			add_action( 'wp_head', array( $this, 'enqueue_dependencies' ) );

			add_action( 'woocommerce_loaded', array( $this, 'woocommerce_loaded' ) );

		}

		/**
		 * Register the main processing hooks.
		 */
		public function woocommerce_loaded() {

			add_action( 'woocommerce_calculate_totals', array( $this, 'cart_info' ), 10, 1 );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'fix_messages' ) );
			add_action( 'woocommerce_review_order_after_cart_contents', array( $this, 'order_info' ) );
			add_action( 'woocommerce_new_order', array( $this, 'add_to_order' ), 10, 1 );
			add_action( 'woocommerce_after_cart_table', array( $this, 'choose_gift' ) );
			add_action( 'wp_ajax_nopriv_wc_free_gift_chosen', array( $this, 'gift_chosen' ) );
			add_action( 'wp_ajax_wc_free_gift_chosen', array( $this, 'gift_chosen' ) );
			add_action( 'wp_ajax_wc_free_gift_get_products', array( $this, 'list_possible_products' ) );
			add_filter( 'woocommerce_product_categories_widget_args', array( $this, 'hide_gift_category' ) );
			add_filter( 'the_posts', array( $this, 'hide_gifts' ) );

			add_action( 'admin_notices', array( $this, 'check_in_stock' ) );

			// add a useful shortcode
			add_shortcode( 'wc_free_gift_message', array( $this, 'shortcode_gift_message' ) );

			if ( version_compare( WOOCOMMERCE_VERSION, "2.1.0" ) >= 0 ) {
				add_filter( 'woocommerce_order_item_name', array( $this, 'modify_title' ), 10, 2 );
			} else {
				add_filter( 'woocommerce_order_table_product_title', array( $this, 'modify_title' ), 10, 2 );
			}

		}

		function fix_messages() {

			global $woocommerce;

			$messages = $woocommerce->get_messages();

			if ( ( $i = array_search( $this->last_msg, $messages ) && is_order_received_page() ) !== false ) {

				unset( $messages[$i] );

			}

			$woocommerce->set_messages( $messages );
			$woocommerce->show_messages();

		}

		/**
		 * Add action links under WordPress > Plugins
		 *
		 * @param $links
		 * @return array
		 */
		public function action_links( $links ) {

			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=woocommerce&tab=wc_free_gift' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * @access public
		 * @return void
		 */
		public function add_tab() {
			foreach ( $this->settings_tabs as $name => $label ) {
				$class = 'nav-tab';
				if ( $this->current_tab == $name )
					$class .= ' nav-tab-active';
				echo '<a href="' . admin_url( 'admin.php?page=woocommerce&tab=' . $name ) . '" class="' . $class . '">' . $label . '</a>';
			}
		}

		/**
		 * @access public
		 * @return void
		 */
		public function settings_tab_action() {

			global $woocommerce_settings;

			// Determine the current tab in effect.
			$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_settings_tabs_' );

			// Load the prepared form fields.
			$this->init_form_fields();

			if ( is_array( $this->fields ) )
				foreach ( $this->fields as $k => $v )
					$woocommerce_settings[$k] = $v;

			// Display settings for this tab (make sure to add the settings to the tab).
			woocommerce_admin_fields( $woocommerce_settings[$current_tab] );
		}

		/**
		 * Save settings in a single field in the database for each tab's fields (one field per tab).
		 */
		public function save_settings() {

			global $woocommerce_settings;

			// Make sure our settings fields are recognised.
			$this->add_settings_fields();

			$current_tab = $this->get_tab_in_view( current_filter(), 'woocommerce_update_options_' );
			woocommerce_update_options( $woocommerce_settings[$current_tab] );

		}

		/**
		 * Get the tab current in view/processing.
		 */
		protected function get_tab_in_view( $current_filter, $filter_base ) {
			return str_replace( $filter_base, '', $current_filter );
		}

		/**
		 * Prepare form fields to be used in the various tabs.
		 */
		protected function init_form_fields() {

			global $woocommerce;

			// Define settings
			$this->fields['wc_free_gift'] = apply_filters( 'woocommerce_free_gift_settings_fields', array(

				array( 'name' => __( 'Free Gift', 'wc_free_gift' ), 'type' => 'title', 'desc' => __( 'The following options are specific to the Free Gift extension.', 'wc_free_gift' ), 'id' => 'wc_free_gift_options' ),

				array(
					'name' => __( 'Free Gift globally enabled', 'wc_free_gift' ),
					'id' => 'wc_free_gift_enabled',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no',
					'desc' => __( 'Free Gift is globally enabled.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'Allow free gifts only to logged in users', 'wc_free_gift' ),
					'id' => 'wc_free_gift_only_logged',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no',
					'desc' => __( 'Allow free gifts only to users which are registered and logged in.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'Allow each user to get the free gift ONLY once', 'wc_free_gift' ),
					'id' => 'wc_free_gift_only_once',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no',
				),

				array(
					'title' => sprintf( __( 'Minimal cart subtotal (%s)', 'wc_free_gift' ), get_woocommerce_currency_symbol() ),
					'desc' => sprintf( __( 'Enter a minimal Cart Subtotal in %s so that the customer is eligible for a free gift.', 'wc_free_gift' ), get_woocommerce_currency_symbol() ),
					'id' => 'wc_free_gift_minimal_total',
					'css' => 'width:180px;',
					'type' => 'number',
					'custom_attributes' => array(
						'min' => 0,
						'step' => 1
					),
					'desc_tip' => true,
					'default' => '100'
				),

				array(
					'title' => __( 'Method of offering gifts', 'wc_free_gift' ),
					'id' => 'wc_free_gift_type',
					'desc' => __( 'Reward customers a fixed gift or let them choose from a category', 'wc_free_gift' ),
					'desc_tip' => true,
					'std' => 'yes',
					'type' => 'select',
					'css' => 'width:180px;',
					'class' => 'chosen_select',
					'options' => array( '' => 'Fixed gift', 'category' => 'Gift from a category' )
				),

				array(
					'title' => __( 'Let customers choose not to receive a gift', 'wc_free_gift' ),
					'id' => 'wc_free_gift_let_choose',
					'desc' => __( 'If method of offering gifts (see above) is "Fixed gift", do you want to let customers choose not to receive the free gift?', 'wc_free_gift' ),
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no'
				),

				array(
					'title' => __( 'The free gift product', 'wc_free_gift' ),
					'id' => 'wc_free_gift_product_id',
					'desc' => __( 'Select the product which will be given to customers as a free gift after they make a purchase for at least the minimal amount.', 'wc_free_gift' ),
					'desc_tip' => true,
					'std' => 'yes',
					'type' => 'select',
					'css' => 'width:300px;',
					'class' => 'chosen_select',
					'options' => array( '' => 'Choose a product ...' ) + $this->get_products()
				),

				array(
					'title' => __( 'The free gift category', 'wc_free_gift' ),
					'id' => 'wc_free_gift_category_id',
					'desc' => __( 'Specify the category from which customers can select gifts', 'wc_free_gift' ),
					'std' => 'yes',
					'desc_tip' => true,
					'type' => 'select',
					'css' => 'width:300px;',
					'class' => 'chosen_select',
					'options' => array( '' => 'Choose a category ...' ) + $this->get_categories()
				),

				array(
					'title' => sprintf( __( 'Quantity of the free gift', 'wc_free_gift' ), get_woocommerce_currency_symbol() ),
					'desc' => sprintf( __( 'Enter the number of free gifts that the customer will receive if he has met the eligibility condition (i.e. sufficient subtotal). Important: If you set this value higher than 1 and you use stock management for the gift products, you need to adjust the "Out of Stock Threshold" on WooCommerce Settings page.', 'wc_free_gift' ), get_woocommerce_currency_symbol() ),
					'id' => 'wc_free_gift_quantity',
					'css' => 'width:100px;',
					'type' => 'number',
					'custom_attributes' => array(
						'min' => 0,
						'step' => 1
					),
					'desc_tip' => true,
					'default' => '1'
				),

				array(
					'name' => __( 'Hide the gift product / category', 'wc_free_gift' ),
					'id' => 'wc_free_gift_hide',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no',
					'desc' => __( 'If you want to hide the product or category you specified above from product search and product catalog, enable this option.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'If any coupon is applied, don\'t allow free gift to be added', 'wc_free_gift' ),
					'id' => 'wc_free_gift_coupon_no_gift',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'no',
					'desc' => ''
				),

				array(
					'name' => __( 'Motivating message visible on the Cart page', 'wc_free_gift' ),
					'id' => 'wc_free_gift_motivating_message_enabled',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes',
					'desc' => __( 'Display a message on the Cart page motivating the customer to spend more money in order to get the free gift.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'The motivating message text', 'wc_free_gift' ),
					'id' => 'wc_free_gift_motivating_message',
					'type' => 'textarea',
					'css' => 'width:100%;',
					'default' => __( 'By spending at least %PRICE%, you will be eligible for a free gift after checkout.', 'wc_free_gift' ),
					'desc_tip' => true,
					'desc' => __( 'Optionally use the placeholders %PRICE% and %PRODUCT% which will be automatically substituted by the actual minimal amount and the gift product, respectively. You can also use the shortcode [wc_free_gift_message] to show the proper message on any page including post and product pages.', 'wc_free_gift' )
				),

				array(
					'name' => __( '"Eligible for a free gift" message visible on the Cart page', 'wc_free_gift' ),
					'id' => 'wc_free_gift_eligible_message_enabled',
					'std' => 'yes',
					'type' => 'checkbox',
					'default' => 'yes',
					'desc' => __( 'Display a message on the Cart page after the customer is eligible for the free gift.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'The "Eligible for a free gift" message text', 'wc_free_gift' ),
					'id' => 'wc_free_gift_eligible_message',
					'type' => 'textarea',
					'css' => 'width:100%;',
					'default' => __( 'You are eligible for a free gift after checkout.', 'wc_free_gift' ),
					'desc_tip' => true,
					'desc' => __( 'Optionally use the placeholder %PRODUCT% which will be automatically substituted by the actual product name of the selected free gift. You can also use the shortcode [wc_free_gift_message] to show the proper message on any page including post and product pages.', 'wc_free_gift' )
				),

				array(
					'name' => __( 'The CSS style for the "Free" indicator of the free gift', 'wc_free_gift' ),
					'id' => 'wc_free_gift_price_css',
					'type' => 'textarea',
					'css' => 'width:100%;',
					'default' => 'color: #00aa00;'
				),

				array( 'type' => 'sectionend', 'id' => 'wc_free_gift_options' ),

			) ); // End settings

			$this->run_js( "

						jQuery('#wc_free_gift_motivating_message_enabled').change(function() {

							jQuery('#wc_free_gift_motivating_message').closest('tr').hide();
							if ( jQuery(this).attr('checked') ) {
								jQuery('#wc_free_gift_motivating_message').closest('tr').show();
							}

						}).change();

						jQuery('#wc_free_gift_eligible_message_enabled').change(function() {

							jQuery('#wc_free_gift_eligible_message').closest('tr').hide();
							if ( jQuery(this).attr('checked') ) {
								jQuery('#wc_free_gift_eligible_message').closest('tr').show();
							}

						}).change();

						jQuery('#wc_free_gift_only_logged').change(function() {

							jQuery('#wc_free_gift_only_once').closest('tr').hide();
							if ( jQuery(this).attr('checked') ) {
								jQuery('#wc_free_gift_only_once').closest('tr').show();
							}

						}).change();

						jQuery('#wc_free_gift_type').change(function() {

							jQuery('#wc_free_gift_product_id').closest('tr').hide();
							jQuery('#wc_free_gift_category_id').closest('tr').hide();
							jQuery('#wc_free_gift_let_choose').closest('tr').hide();

							if ( jQuery('#wc_free_gift_type').val() == '' ) {
								jQuery('#wc_free_gift_let_choose').closest('tr').show();
								jQuery('#wc_free_gift_product_id').closest('tr').show();
							} else {
								jQuery('#wc_free_gift_category_id').closest('tr').show();
							}

						}).change();

						jQuery('#wc_free_gift_category_id').change(function() {

							jQuery('.wc_free_gift_cat_info').remove();

							var data = {
								action: 'wc_free_gift_get_products',
								category: jQuery(this).val()
							};

							jQuery.post('" . admin_url( 'admin-ajax.php' ) . "', data, function (response) {

								if ( response ) {
									jQuery('#wc_free_gift_category_id').closest('td').append('<div class=\'wc_free_gift_cat_info\'>'+response+'</div>');
								}

							});

						}).change();

			" );

		}

		/**
		 * Add settings fields for each tab.
		 */
		public function add_settings_fields() {

			global $woocommerce_settings;

			// Load the prepared form fields.
			$this->init_form_fields();

			if ( is_array( $this->fields ) )
				foreach ( $this->fields as $k => $v )
					$woocommerce_settings[$k] = $v;

		}

		/**
		 * Enqueue frontend dependencies.
		 */
		public function enqueue_dependencies() {

			wp_enqueue_style( 'wc_free_gift_style', plugins_url( 'assets/css/style.css', __FILE__ ) );
			wp_enqueue_script( 'jquery' );

		}

		/**
		 * Enqueue backend dependencies.
		 */
		public function enqueue_dependencies_admin() {

			wp_enqueue_style( 'wc_free_gift_style_admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
			wp_enqueue_script( 'jquery' );

		}

		/**
		 * Hooks on woocommerce_calculate_totals action.
		 *
		 * @param WC_Cart $cart
		 */
		public function cart_info( WC_Cart $cart ) {

			global $woocommerce;

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'no' ) {
				return;
			}

			if ( is_checkout() || ( get_option( 'wc_free_gift_coupon_no_gift', 'no' ) == 'yes' && $this->is_coupon_applied() ) ) {
				return;
			}

			if ( !$this->customer_gift_allowed() ) {
				return;
			}

			$possible_products = $this->get_possible_products();

			if ( empty ( $possible_products ) ) {
				return;
			}

			if ( get_option( 'wc_free_gift_only_logged', 'no' ) == 'no' || is_user_logged_in() ) {

				$the_amount = $woocommerce->cart->subtotal;

				if ( get_option( 'wc_free_gift_minimal_total', '' ) != '' && floatval( $the_amount ) >= floatval( get_option( 'wc_free_gift_minimal_total' ) ) ) { // eligible for a free gift
					if ( get_option( 'wc_free_gift_eligible_message_enabled', 'yes' ) == 'yes' ) {
						$eligible_msg = get_option( 'wc_free_gift_eligible_message', __( 'You are eligible for a free gift after checkout.', 'wc_free_gift' ) );
						$eligible_msg = $this->str_replace_products( $possible_products, '%PRODUCT%', $eligible_msg );
						$this->add_wc_message( apply_filters( 'woocommerce_free_gift_eligible_message', $eligible_msg ) );
						$this->last_msg = $eligible_msg;
					}
				} else {
					if ( get_option( 'wc_free_gift_motivating_message_enabled', 'yes' ) == 'yes' ) {
						$motivating_msg = get_option( 'wc_free_gift_motivating_message', __( 'By spending at least %PRICE%, you will be eligible for a free gift after checkout.', 'wc_free_gift' ) );
						$motivating_msg = $this->str_replace_products( $possible_products, '%PRODUCT%', $motivating_msg );
						$motivating_msg = str_replace( '%PRICE%', woocommerce_price( get_option( 'wc_free_gift_minimal_total' ) ), $motivating_msg );
						$motivating_msg = str_replace( '%REMAINING_AMOUNT%', woocommerce_price( floatval( get_option( 'wc_free_gift_minimal_total' ) ) - floatval( $the_amount ) ), $motivating_msg );
						$this->add_wc_message( apply_filters( 'woocommerce_free_gift_motivating_message', $motivating_msg ) );
						$this->last_msg = $motivating_msg;
					}
				}
			}
		}

		/**
		 * Provides AJAX listing of products on backend.
		 */
		public function list_possible_products() {

			header( 'Content-Type: text/html; charset=utf-8' );

			if ( !empty( $_POST['category'] ) ) {
				echo __( 'Currently customers can choose one from the following products: ', 'wc_free_gift' )
					. '<strong>' . implode( ' ' . __( ', ', 'wc_free_gift' ) . ' ', array_values( $this->get_possible_products( true, $_POST['category'] ) ) ) .
					'</strong>';
			}

			die();

		}

		/**
		 * @param bool $with_id
		 * @param bool $category_id
		 * @return array
		 */
		protected function get_possible_products( $with_id = false, $category_id = false ) {

			$products = array();

			if ( get_option( 'wc_free_gift_type', '' ) == 'category' || $category_id ) {

				if ( !$category_id ) {
					$category_id = get_option( 'wc_free_gift_category_id', '' );
				}

				if ( $category_id != '' ) {
					$products_from_cat = $this->get_products( $category_id );
					foreach ( $products_from_cat as $id => $name ) {
						array_push( $products, $id );
						unset( $name );
					}
					unset( $products_from_cat );
				} else {
					return array();
				}

			} else {

				$product_id = get_option( 'wc_free_gift_product_id', '' );
				if ( $product_id != '' ) {
					array_push( $products, $product_id );
				}

			}

			$products_to_return = array();
			$products_to_return_with_id = array();
			foreach ( $products as $product_id ) {
				$_product = get_product( $product_id );
				if ( !in_array( $_product->post->post_title, array_values( $products_to_return ) ) && $_product->exists() && $_product->is_in_stock() ) {
					$products_to_return[$product_id] = $_product->post->post_title;
					$products_to_return_with_id[$product_id] = $_product->post->post_title . ' (' . ( $_product instanceof WC_Product_Variation ? 'variation id:' : 'id:' ) . $product_id . ')';
				}
				unset( $_product );
			}

			return ( $with_id ? $products_to_return_with_id : $products_to_return );
		}

		/**
		 * @param $possible_products
		 * @param $placeholder
		 * @param $message
		 * @return mixed
		 */
		protected function str_replace_products( $possible_products, $placeholder, $message ) {

			if ( empty ( $possible_products ) ) {
				return $message;
			}

			$products = implode( ' ' . __( 'or', 'wc_free_gift' ) . ' ', array_values( $possible_products ) );

			return str_replace( $placeholder, $products, $message );

		}

		/**
		 * Provides a combobox visible on cart page. Applies to category gift type.
		 */
		public function choose_gift() {

			global $woocommerce;

			if ( get_option( 'wc_free_gift_type', '' ) == '' && get_option( 'wc_free_gift_let_choose', 'no' ) == 'no' ) {
				return;
			}
			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'yes' && ( get_option( 'wc_free_gift_only_logged', 'no' ) == 'no' || is_user_logged_in() ) ) {

				$the_amount = $woocommerce->cart->subtotal;

				if ( get_option( 'wc_free_gift_minimal_total', '' ) != '' && floatval( $the_amount ) >= floatval( get_option( 'wc_free_gift_minimal_total' ) ) ) { // eligible for a free gift


					$products = $this->get_possible_products();
					if ( empty( $products ) ) {
						return;
					}

					echo '<select id="wc_free_gift_chosen_gift" name="wc_free_gift_chosen_gift">';
					echo '<option class="choose-gift-option" value="">' . __( 'Choose your free gift', 'wc_free_gift' ) . '&hellip;</option>';
					foreach ( $products as $id => $value ) {
						echo '<option value="' . $id . '" ' . ( ( $id == $woocommerce->session->wc_free_gift_chosen_gift  ) ? 'selected' : '' ) . '>' . $value . '</option>';
					}
					echo '</select>';

					$this->run_js(
						'

						jQuery("#wc_free_gift_chosen_gift").click(function() {
						    jQuery("#wc_free_gift_chosen_gift option[value=\'\']").text("' . __( 'I don\'t want any gift', 'wc_free_gift' ) . '");
						});

						jQuery("#wc_free_gift_chosen_gift").change(function() {

							var $this = jQuery(this);

							var data = {
								action: "wc_free_gift_chosen",
								security: "' . wp_create_nonce( "wc_free_gift_chosen_nonce" ) . '",
								product_id: $this.val()
							};

							jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", data, function (response) {

								if ( !response )
									return;

							});

						});
					'
					);

				}
			}

		}

		/**
		 * Triggers when customers select a gift.
		 */
		public function gift_chosen() {

			global $woocommerce;

			check_ajax_referer( 'wc_free_gift_chosen_nonce', 'security' );

			$selected_gift = isset( $_POST['product_id'] ) ? woocommerce_clean( $_POST['product_id'] ) : '';

			$woocommerce->session->wc_free_gift_chosen_gift = $selected_gift;

		}

		/**
		 * Hooks on woocommerce_review_order_after_cart_contents action.
		 */
		public function order_info() {

			global $woocommerce;

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'no' ) {
				return;
			}

			if ( ( get_option( 'wc_free_gift_coupon_no_gift', 'no' ) == 'yes' && $this->is_coupon_applied() ) ) {
				return;
			}

			if ( !$this->customer_gift_allowed() ) {
				return;
			}

			$prod_id = null;

			if ( get_option( 'wc_free_gift_type', '' ) == 'category' || get_option( 'wc_free_gift_let_choose', 'no' ) == 'yes' ) {
				$prod_id = $woocommerce->session->wc_free_gift_chosen_gift;
				if ( get_option( 'wc_free_gift_type', '' ) == 'category' && ( get_option( 'wc_free_gift_category_id', '' ) == '' || !in_array( $prod_id, array_keys( $this->get_products( get_option( 'wc_free_gift_category_id' ) ) ) ) ) ) { // we double check here that the gift product id is really from the valid category
					return;
				}
			} else {
				$prod_id = get_option( 'wc_free_gift_product_id' );
			}

			if ( !$prod_id ) {
				return;
			}
			$_product = get_product( $prod_id );

			$the_amount = $woocommerce->cart->subtotal;

			if ( $_product->exists() && $_product->is_in_stock()
				&& get_option( 'wc_free_gift_minimal_total', '' ) != '' && floatval( $the_amount ) >= floatval( get_option( 'wc_free_gift_minimal_total' ) )
				&& ( get_option( 'wc_free_gift_only_logged', 'no' ) == 'no' || is_user_logged_in() )
			) { // eligible for a free item
				$price = __( 'Free!', 'woocommerce' );
				$price = apply_filters( 'woocommerce_get_price_html', apply_filters( 'woocommerce_free_price_html', $price ) );
				echo '
					<tr>
					<td class="product-name">' .
					apply_filters( 'woocommerce_checkout_product_title', $_product->get_title(), $_product ) . ' ' .
					'<strong class="product-quantity">&times; ' . get_option( 'wc_free_gift_quantity', 1 ) . '</strong>' .
					'</td>
					<td class="product-total" style="' . get_option( 'wc_free_gift_price_css', 'color: #00aa00;' ) . '">' . $price . '</td>
					</tr>';
			}

		}

		/**
		 * Hooks on woocommerce_new_order action.
		 *
		 * @param $order_id
		 */
		public function add_to_order( $order_id ) {

			global $woocommerce;

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'no' ) {
				return;
			}

			if ( ( get_option( 'wc_free_gift_coupon_no_gift', 'no' ) == 'yes' && $this->is_coupon_applied() ) ) {
				return;
			}

			if ( !$this->customer_gift_allowed() ) {
				return;
			}

			if ( get_option( 'wc_free_gift_type', '' ) == 'category' || get_option( 'wc_free_gift_let_choose', 'no' ) == 'yes' ) {
				$prod_id = $woocommerce->session->wc_free_gift_chosen_gift;
				if ( get_option( 'wc_free_gift_type', '' ) == 'category' && ( get_option( 'wc_free_gift_category_id', '' ) == '' || !in_array( $prod_id, array_keys( $this->get_products( get_option( 'wc_free_gift_category_id' ) ) ) ) ) ) { // we double check here that the gift product id is really from the valid category
					return;
				}
			} else {
				$prod_id = get_option( 'wc_free_gift_product_id' );
			}

			if ( !$prod_id ) {
				return;
			}
			$_product = get_product( $prod_id );

			$woocommerce->session->wc_free_gift_chosen_gift = '';

			$the_amount = $woocommerce->cart->subtotal;

			if ( $_product->exists() && $_product->is_in_stock()
				&& get_option( 'wc_free_gift_minimal_total' ) != '' && floatval( $the_amount ) >= floatval( get_option( 'wc_free_gift_minimal_total' ) )
				&& ( get_option( 'wc_free_gift_only_logged', 'no' ) == 'no' || is_user_logged_in() )
			) {

				// Add line item
				$item_id = woocommerce_add_order_item( $order_id, array(
					'order_item_name' => $_product->get_title(),
					'order_item_type' => 'line_item'
				) );

				// Add line item meta
				if ( $item_id ) {
					woocommerce_add_order_item_meta( $item_id, '_qty', get_option( 'wc_free_gift_quantity', 1 ) );
					woocommerce_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
					woocommerce_add_order_item_meta( $item_id, '_product_id', $prod_id );
					woocommerce_add_order_item_meta( $item_id, '_variation_id', $_product->variation_id ? $_product->variation_id : '' );
					woocommerce_add_order_item_meta( $item_id, '_line_subtotal', woocommerce_format_decimal( 0, 4 ) );
					woocommerce_add_order_item_meta( $item_id, '_line_total', woocommerce_format_decimal( 0, 4 ) );
					woocommerce_add_order_item_meta( $item_id, '_line_tax', woocommerce_format_decimal( 0, 4 ) );
					woocommerce_add_order_item_meta( $item_id, '_line_subtotal_tax', woocommerce_format_decimal( 0, 4 ) );
					woocommerce_add_order_item_meta( $item_id, '_free_gift', 'yes' );

					// Store variation data in meta so admin can view it
					if ( @$_product->variation_data && is_array( $_product->variation_data ) )
						foreach ( $_product->variation_data as $key => $value )
							woocommerce_add_order_item_meta( $item_id, esc_attr( str_replace( 'attribute_', '', $key ) ), $value );

					if ( get_option( 'wc_free_gift_only_once', 'no' ) == 'yes' ) {
						$user_id = get_current_user_id();
						if ( $user_id ) {
							update_user_meta( $user_id, '_wc_free_gift_already_given', "yes" );
						}
					}

				}

			}

		}

		/**
		 * Hooks on woocommerce_order_table_product_title filter.
		 *
		 * @param $title
		 * @param $item
		 * @return string
		 */
		public function modify_title( $title, $item ) {

			if ( @$item['item_meta']['_free_gift'][0] == 'yes' ) {
				return $title . ' <span style="' . get_option( 'wc_free_gift_price_css', 'color: #00aa00;' ) . '">(' . __( 'Free!', 'woocommerce' ) . ')</span>';
			}

			return $title;

		}

		/**
		 * Provides a shortocde for the message if the customer is eligible for a free gift or not yet.
		 *
		 * @param $atts
		 * @return string
		 */
		public function shortcode_gift_message( $atts ) {

			global $woocommerce;

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'no' ) {
				return '';
			}

			if ( ( get_option( 'wc_free_gift_coupon_no_gift', 'no' ) == 'yes' && $this->is_coupon_applied() ) ) {
				return '';
			}

			if ( !$this->customer_gift_allowed() ) {
				return '';
			}

			extract( shortcode_atts( array(
				'class' => 'wc_free_gift_message'
			), $atts ) );

			$possible_products = $this->get_possible_products();

			if ( empty ( $possible_products ) ) {
				return '';
			}

			if ( get_option( 'wc_free_gift_only_logged', 'no' ) == 'no' || is_user_logged_in() ) {

				$the_amount = $woocommerce->cart->subtotal;

				if ( get_option( 'wc_free_gift_minimal_total', '' ) != '' && floatval( $the_amount ) >= floatval( get_option( 'wc_free_gift_minimal_total' ) ) ) { // eligible for a free gift
					if ( get_option( 'wc_free_gift_eligible_message_enabled', 'yes' ) == 'yes' ) {
						$eligible_msg = get_option( 'wc_free_gift_eligible_message', __( 'You are eligible for a free gift after checkout.', 'wc_free_gift' ) );
						$eligible_msg = $this->str_replace_products( $possible_products, '%PRODUCT%', $eligible_msg );
						$this->last_msg = $eligible_msg;
						return '<div class="' . $class . '">' . apply_filters( 'woocommerce_free_gift_eligible_message', $eligible_msg ) . '</div>';
					}
				} else {
					if ( get_option( 'wc_free_gift_motivating_message_enabled', 'yes' ) == 'yes' ) {
						$motivating_msg = get_option( 'wc_free_gift_motivating_message', __( 'By spending at least %PRICE%, you will be eligible for a free gift after checkout.', 'wc_free_gift' ) );
						$motivating_msg = $this->str_replace_products( $possible_products, '%PRODUCT%', $motivating_msg );
						$motivating_msg = str_replace( '%PRICE%', woocommerce_price( get_option( 'wc_free_gift_minimal_total' ) ), $motivating_msg );
						$motivating_msg = str_replace( '%REMAINING_AMOUNT%', woocommerce_price( floatval( get_option( 'wc_free_gift_minimal_total' ) ) - floatval( $the_amount ) ), $motivating_msg );
						$this->last_msg = $motivating_msg;
						return '<div class="' . $class . '">' . apply_filters( 'woocommerce_free_gift_motivating_message', $motivating_msg ) . '</div>';
					}
				}
			}

			return '';

		}

		/**
		 * Check if gift products are in stock.
		 */
		public function check_in_stock() {

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'yes' && current_user_can( 'manage_options' ) ) {

				if ( get_option( 'wc_free_gift_type', '' ) == 'category' ) {

					$gifts = $this->get_products( get_option( 'wc_free_gift_category_id' ) );
					foreach ( $gifts as $prod_id => $gift_product ) {
						$_product = get_product( $prod_id );
						if ( $_product && !$_product->is_in_stock() ) {
							echo '<div id="message" class="error"><p><strong>Warning from WooCommerce Free Gift:</strong> The gift product ' . $_product->get_title() . ' is out of stock!</p></div>';
						}
						unset( $_product );
					}

				} else {

					$prod_id = get_option( 'wc_free_gift_product_id', '' );
					$_product = get_product( $prod_id );
					if ( $_product && !$_product->is_in_stock() ) {
						echo '<div id="message" class="error"><p><strong>Warning from WooCommerce Free Gift:</strong> The gift product ' . $_product->get_title() . ' is out of stock!</p></div>';
					}

				}

			}

		}

		/**
		 * @param $cat_args
		 * @return mixed
		 */
		public function hide_gift_category( $cat_args ) {
			if ( get_option( 'wc_free_gift_type', '' ) == 'category' && get_option( 'wc_free_gift_hide', 'no' ) == 'yes' ) {
				$cat_args['exclude'] = array( get_option( 'wc_free_gift_category_id' ) );
			}
			return $cat_args;
		}

		/**
		 * @param $posts
		 * @return array
		 */
		public function hide_gifts( $posts ) {

			$posts_to_return = array();

			if ( is_admin() ) return $posts;

			if ( get_option( 'wc_free_gift_enabled', 'no' ) == 'no' ) return $posts;

			if ( is_single() ) return $posts;

			if ( get_option( 'wc_free_gift_hide', 'no' ) == 'yes' ) {

				if ( !empty( $posts ) ) {
					foreach ( $posts as $post ) {

						if ( get_option( 'wc_free_gift_type', '' ) == 'category' ) {

							$cat_gifts = array_keys( $this->get_products( get_option( 'wc_free_gift_category_id' ) ) );
							if ( in_array( $post->ID, $cat_gifts ) ) continue; // hide the gift products
							unset( $cat_gifts );

						} else if ( $post->ID == get_option( 'wc_free_gift_product_id', '' ) ) {

							continue; // hide the gift product

						}

						array_push( $posts_to_return, $post );

					}
				}

			} else {

				return $posts;

			}

			return $posts_to_return;

		}

		/**
		 * Includes inline JavaScript.
		 *
		 * @param $js
		 */
		protected function run_js( $js ) {

			global $woocommerce;

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				$woocommerce->add_inline_js( $js );
			}

		}

		/**
		 * Return a list of WooCommerce products.
		 *
		 * @return array
		 */
		protected function get_products( $category_id = 0 ) {

			$products = array();

			for ( $offset = 0; ; $offset += 50 ) {
				$posts = get_posts( array( 'post_type' => 'product', 'status' => 'published', 'numberposts' => '50', 'offset' => $offset, 'orderby' => 'post_date' ) );

				if ( empty( $posts ) ) break;

				foreach ( $posts as $post ) {

					$_product = get_product( $post->ID );

					if ( $category_id != 0 ) {
						$terms = get_the_terms( $post->ID, 'product_cat' );
						if ( !is_array( $terms ) ) continue;
						$found = false;
						foreach ( $terms as $term ) {
							if ( $term->term_id == $category_id ) {
								$found = true;
							}
						}
						if ( !$found ) {
							continue;
						}
					}

					if ( $_product->product_type == 'simple' ) {
						$products[$post->ID] = $post->post_title . ' (id: ' . $post->ID . ')';
					} else if ( $_product->product_type == 'variable' ) {
						$children = $_product->get_children();
						foreach ( $children as $child_id ) {
							$child_product = get_product( $child_id );
							if ( $child_product instanceof WC_Product_Variation ) {
								$products[$child_product->variation_id] = $child_product->post->post_title . ' (variation id: ' . $child_product->variation_id . ')';
							}
							unset( $child_product );
						}
						unset( $children );
					}
					unset( $_product );

				}

				unset( $posts );

			}

			return $products;

		}

		/**
		 * Return a list of WooCommerce categories.
		 *
		 * @return array
		 */
		protected function get_categories() {

			$args = array(
				'hide_empty' => true,
			);

			$cat_list = get_terms( 'product_cat', $args );

			$categories = array();
			foreach ( $cat_list as $category ) {
				$categories[$category->term_id] = $category->name . ' (category id: ' . $category->term_id . ')';
			}

			return $categories;

		}

		/**
		 * Check if a coupon code has been applied.
		 *
		 * @return bool
		 */
		protected function is_coupon_applied() {

			global $woocommerce;

			return !( empty( $woocommerce->cart->applied_coupons ) );

		}

		protected function customer_gift_allowed() {

			global $woocommerce;

			$user_id = get_current_user_id();
			if ( !$user_id ) {
				return true;
			}

			if ( get_option( 'wc_free_gift_only_once', 'no' ) == 'no' ) {
				return true;
			}

			$already_given = get_user_meta( $user_id, '_wc_free_gift_already_given', true );

			if ( !empty( $already_given ) && $already_given == "yes" ) {
				return false;
			}

			return true;

		}

		/**
		 * Adds a WooCommerce message.
		 *
		 * @param $message
		 */
		protected function add_wc_message( $message ) {

			global $woocommerce;

			$messages = $woocommerce->get_messages();

			if ( !in_array( $message, $messages ) ) { // we want to prevent adding the same message twice
				$woocommerce->add_message( $message );
			}

		}

	}

	new WooCommerce_Free_Gift();

}