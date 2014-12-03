<?php
// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
	die();
}

/**
 * TM_Extra_Product_Options Class
 *
 * This class is responsible for displaying the Extra Product Options on the frontend.
 */

class TM_Extra_Product_Options {

	var $version        = TM_EPO_VERSION;
	var $_namespace     = 'tm-extra-product-options';
	var $plugin_path;
	var $template_path;
	var $plugin_url;

	/* Controls how fields are displayed on the front-end */
	var $display = 'normal';

	/* Product custom settings */
	var $tm_meta_cpf = array();
	/* Product custom settings options */
	var $meta_fields = array(
			'exclude' 					=> '',
			'override_display' 			=> '',
			'override_final_total_box' 	=> ''
		);

	/* Cache for all the extra options */
	var $cpf=false;

	/* Replacement name for Subscription fee fields */
	var $fee_name="tmfee_";
	var $fee_name_class="tmcp-sub-fee-field";

	/* Replacement name for cart fee fields */
	var $cart_fee_name="tmcartfee_";
	var $cart_fee_class="tmcp-fee-field";

	/* Holds the total fee added by Subscription fee fields */
	var $tmfee=0;

	/* Array of element types that get posted */
	var $element_post_types;

	/* Inline styles */
	var $inline_styles;

	/* Plugin settings */
	var $tm_epo_force_select_options;
	var $tm_epo_final_total_box;
	var $tm_epo_clear_cart_button;
	var $tm_epo_cart_field_display;
	var $tm_epo_final_total_text;
	var $tm_epo_options_total_text;
	var $tm_epo_strip_html_from_emails;
	var $tm_epo_remove_free_price_label;
	var $tm_epo_subscription_fee_text;
	var $tm_epo_replacement_free_price_text;
	var $tm_epo_hide_options_in_cart;
	var $tm_epo_hide_options_prices_in_cart;
	var $tm_epo_hide_upload_file_path;
	var $tm_epo_css_styles;
	var $tm_epo_css_styles_style;
	var $tm_epo_roles_enabled;
	var $tm_epo_options_placement;
	var $tm_epo_totals_box_placement;
	var $tm_epo_options_placement_custom_hook;
	var $tm_epo_totals_box_placement_custom_hook;
	var $tm_epo_no_lazy_load;

	/* Prevents options duplication for bad coded themes */
	var $tm_options_have_been_displayed=false;
	var $tm_options_single_have_been_displayed=false;
	var $tm_options_totals_have_been_displayed=false;

	public function __construct() {
		$this->plugin_path      						= TM_plugin_path;
		$this->template_path    						= TM_template_path;
		$this->plugin_url       						= TM_plugin_url;
		$this->inline_styles 							= '';
		$this->element_post_types 						= array("checkbox","radio","select","textarea","textfield","upload","date","range");
		$this->display 									= get_option( 'tm_epo_display' );
		$this->is_bto 									= false;
		$this->noactiondisplay 							= false;
		$this->tm_epo_options_placement 				= get_option( 'tm_epo_options_placement' );
		$this->tm_epo_totals_box_placement 				= get_option( 'tm_epo_totals_box_placement' );
		$this->tm_epo_options_placement_custom_hook 	= get_option( 'tm_epo_options_placement_custom_hook' );
		$this->tm_epo_totals_box_placement_custom_hook 	= get_option( 'tm_epo_totals_box_placement_custom_hook' );
		$this->tm_epo_force_select_options 				= get_option( 'tm_epo_force_select_options' );
		$this->tm_epo_final_total_box 					= get_option( 'tm_epo_final_total_box' );
		$this->tm_epo_clear_cart_button 				= get_option( 'tm_epo_clear_cart_button' );
		$this->tm_epo_cart_field_display 				= get_option( 'tm_epo_cart_field_display' );
		$this->tm_epo_final_total_text 					= get_option( 'tm_epo_final_total_text' );
		$this->tm_epo_options_total_text 				= get_option( 'tm_epo_options_total_text' );
		$this->tm_epo_strip_html_from_emails 			= get_option( 'tm_epo_strip_html_from_emails' );
		$this->tm_epo_remove_free_price_label 			= get_option( 'tm_epo_remove_free_price_label' );
		$this->tm_epo_subscription_fee_text 			= get_option( 'tm_epo_subscription_fee_text' );
		$this->tm_epo_replacement_free_price_text 		= get_option( 'tm_epo_replacement_free_price_text' );
		$this->tm_epo_hide_options_in_cart 				= get_option( 'tm_epo_hide_options_in_cart' );
		$this->tm_epo_hide_options_prices_in_cart 		= get_option( 'tm_epo_hide_options_prices_in_cart' );
		$this->tm_epo_hide_upload_file_path 			= get_option( 'tm_epo_hide_upload_file_path' );
		$this->tm_epo_css_styles 						= get_option( 'tm_epo_css_styles' );
		$this->tm_epo_css_styles_style 					= get_option( 'tm_epo_css_styles_style' );
		$this->tm_epo_roles_enabled 					= get_option( 'tm_epo_roles_enabled' );
		$this->tm_epo_no_lazy_load 						= get_option( 'tm_epo_no_lazy_load' );
		
		if (!$this->tm_epo_options_placement_custom_hook){
			$this->tm_epo_options_placement_custom_hook = '';
		}
		if (!$this->tm_epo_totals_box_placement_custom_hook){
			$this->tm_epo_totals_box_placement_custom_hook = '';
		}
		if (!$this->tm_epo_options_placement){
			$this->tm_epo_options_placement = 'woocommerce_before_add_to_cart_button';
		}
		if ($this->tm_epo_options_placement=="custom"){
			$this->tm_epo_options_placement = $this->tm_epo_options_placement_custom_hook;
		}
		if (!$this->tm_epo_totals_box_placement){
			$this->tm_epo_totals_box_placement = 'woocommerce_before_add_to_cart_button';
		}
		if ($this->tm_epo_totals_box_placement=="custom"){
			$this->tm_epo_totals_box_placement = $this->tm_epo_totals_box_placement_custom_hook;
		}

		if (!$this->tm_epo_roles_enabled){
			$this->tm_epo_roles_enabled = '@everyone';
		}
		if (!$this->tm_epo_hide_options_in_cart){
			$this->tm_epo_hide_options_in_cart = 'normal';
		}
		if (!$this->tm_epo_hide_options_prices_in_cart){
			$this->tm_epo_hide_options_prices_in_cart = 'normal';
		}				
		if (!$this->tm_epo_force_select_options){
			$this->tm_epo_force_select_options = 'normal';
		}
		if (!$this->tm_epo_final_total_box){
			$this->tm_epo_final_total_box = 'normal';
		}
		if (!$this->tm_epo_clear_cart_button){
			$this->tm_epo_clear_cart_button = 'normal';
		}
		if (!$this->tm_epo_cart_field_display){
			$this->tm_epo_cart_field_display = 'normal';
		}
		if (!$this->tm_epo_strip_html_from_emails){
			$this->tm_epo_strip_html_from_emails = 'yes';
		}
		if (!$this->tm_epo_remove_free_price_label){
			$this->tm_epo_remove_free_price_label = 'no';
		}
		if (!$this->tm_epo_hide_upload_file_path){
			$this->tm_epo_hide_upload_file_path = 'yes';
		}
		if (!$this->tm_epo_replacement_free_price_text){
			$this->tm_epo_replacement_free_price_text = '';
		}
		if (!$this->tm_epo_css_styles){
			$this->tm_epo_css_styles = '';
		}
		if (!$this->tm_epo_css_styles_style){
			$this->tm_epo_css_styles_style = 'round';
		}
		if (!$this->tm_epo_no_lazy_load){
			$this->tm_epo_no_lazy_load = 'no';
		}

		if (!$this->display){
			$this->display = 'normal';
		}

		foreach ( $this->meta_fields as $key=>$value ) {
			$this->tm_meta_cpf[$key] = $value;
		}

		/**
		 * Empty cart button
		 */
		if ($this->tm_epo_clear_cart_button=="show"){
			add_action( 'woocommerce_proceed_to_checkout', array( $this, 'add_empty_cart_button' ) );
			// check for empty-cart get param to clear the cart
			add_action( 'init', array( $this, 'clear_cart' ) );
		}

		/**
		 * Force Select Options
		 */
		add_filter( 'woocommerce_add_to_cart_url', array( $this, 'add_to_cart_url' ), 50, 1 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 50, 1 );
		add_action( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_text' ), 10, 1 );		

		/* enable shortcodes for labels */	
		add_filter('woocommerce_tm_epo_option_name', array( $this, 'tm_epo_option_name' ), 10, 1);

		/* Subscriptions support */
		add_filter('woocommerce_subscriptions_product_sign_up_fee', array( $this, 'tm_subscriptions_product_sign_up_fee' ), 10, 2);
		
		/* For hiding uploaded file path */
		add_filter('woocommerce_order_item_display_meta_value', array( $this, 'tm_order_item_display_meta_value' ), 10, 1);

		/* WooCommerce Currency Switcher compatibility */
		add_filter('woocommerce_tm_epo_price', array( $this, 'tm_epo_price' ), 10, 2);
		add_filter('woocommerce_tm_epo_price2', array( $this, 'tm_epo_price2' ), 10, 2);
		add_filter('woocommerce_tm_epo_price2_remove', array( $this, 'tm_epo_price2_remove' ), 10, 2);		

		if ( $this->is_quick_view() ){
			add_action( 'init', array( $this, 'init_settings' ) );
		}else{
			add_action( 'wp', array( $this, 'init_settings' ) );	
		}		

		/**
		 * Load js,css files
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'woocommerce_tm_custom_price_fields_enqueue_scripts', array( $this, 'custom_frontend_scripts' ) );
		add_action( 'woocommerce_tm_epo_enqueue_scripts', array( $this, 'custom_frontend_scripts' ) );

		/**
		 * Display in frontend
		 */
		add_action( 'woocommerce_tm_custom_price_fields', array( $this, 'frontend_display' ) );
		add_action( 'woocommerce_tm_custom_price_fields_only', array( $this, 'tm_epo_fields' ) );
		add_action( 'woocommerce_tm_custom_price_fields_totals', array( $this, 'tm_epo_totals' ) );

		add_action( 'woocommerce_tm_epo', array( $this, 'frontend_display' ) );
		add_action( 'woocommerce_tm_epo_fields', array( $this, 'tm_epo_fields' ) );
		add_action( 'woocommerce_tm_epo_totals', array( $this, 'tm_epo_totals' ) );

		/**
		 * Composite Products Support
		 */
		add_action( 'woocommerce_composite_product_add_to_cart', array( $this, 'tm_bto_display_support' ), 11, 2 );

		/**
		 * Cart manipulation
		 */
		add_filter( 'woocommerce_add_cart_item', array( &$this, 'add_cart_item' ), 50, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( &$this, 'get_cart_item_from_session' ), 50, 2 );
		add_filter( 'woocommerce_get_item_data', array( &$this, 'get_item_data' ), 50, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( &$this, 'add_cart_item_data' ), 50, 2 );
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta' ), 50, 2 );
		add_filter( 'woocommerce_add_to_cart_validation', array( &$this, 'add_to_cart_validation' ), 50, 6 );
		add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again_cart_item_data' ), 50, 3 );
		
		/* Support for fee price types */
		add_action( 'woocommerce_cart_calculate_fees', array( $this,'tm_calculate_cart_fee' ) );
	}

	public function tm_calculate_cart_fee( $cart_object ) {
		$tax=get_option('woocommerce_calc_taxes');
		if ($tax=="no"){
			$tax=false;
		}else{
			$tax=true;
		}
	    foreach ( $cart_object->cart_contents as $key => $value ) {
	    	$tmcartfee=isset($value['tmcartfee'])?$value['tmcartfee']:false;
	    	if ($tmcartfee && is_array($tmcartfee)){
	    		foreach ( $tmcartfee as $cartfee ) {
					$new_price = $cartfee["price"];
					$new_name = $cartfee["name"];
					if (empty($new_name)){
						$new_name=__("Extra fee",TM_EPO_TRANSLATION);
					}
					$canbadded=true;
			            
					foreach ( $cart_object->fees as $fee ) {
						if ( $fee->id == sanitize_title($new_name) ) {
							$fee->amount=$fee->amount+(float) esc_attr( $new_price );
							$canbadded=false;
							break;
						}
					}
					if($canbadded){
						$cart_object->add_fee( $new_name, $new_price,$tax );	
					}
				}
			}
		}
	}

	public function check_enable(){
		$enable=false;
		$enabled_roles=$this->tm_epo_roles_enabled;
		if (!is_array($enabled_roles)){
			$enabled_roles=array($this->tm_epo_roles_enabled);
		}
		/* Check if plugin is enabled for everyone */
		foreach ($enabled_roles as $key => $value) {
			if($value=="@everyone"){
				return true;
			}
			if($value=="@loggedin" && is_user_logged_in()){
				return true;
			}
		}

		/* Get all roles */
		$current_user = wp_get_current_user();
		if ( $current_user instanceof WP_User ){		
			$roles = $current_user->roles;			
			/* Check if plugin is enabled for current user */			
			foreach ($roles as $key => $value) {
				if (in_array($value, $enabled_roles)){
					$enable=true;
					break;
				}
			}
		}

		return $enable;
	}

	public function is_quick_view(){
		$qv=false;
		if ( isset($_GET['wc-api']) && $_GET['wc-api']=='WC_Quick_View' ){
			$qv=true;
		}
		return apply_filters( 'woocommerce_tm_quick_view',$qv);
	}

	/* WooCommerce Currency Switcher compatibility  */
	public function tm_epo_price($price,$type){
		if (class_exists('WOOCS')){
			global $WOOCS;
			$currencies = $WOOCS->get_currencies();
			$current_currency=$WOOCS->current_currency;
			
			$price=(double)$price* $currencies[$current_currency]['rate'];
		}
		return $price;
	}
	public function tm_epo_price2($price,$type){
		if (class_exists('WOOCS') && (empty($type) || $type=="char")){
			global $WOOCS;
			$currencies = $WOOCS->get_currencies();
			$current_currency=$WOOCS->current_currency;
			
			$price=(double)$price* $currencies[$current_currency]['rate'];
		}
		return $price;
	}
	public function tm_epo_price2_remove($price,$type){
		if (class_exists('WOOCS')){
			global $WOOCS;
			$currencies = $WOOCS->get_currencies();
			$current_currency=$WOOCS->current_currency;
			if (!empty($currencies[$current_currency]['rate'])){
				$price=(double)$price/ $currencies[$current_currency]['rate'];	
			}			
		}
		return $price;
	}
	
	public function array_map_deep($array, $array2, $callback){
	    $new = array();
	    if( is_array($array) && is_array($array2)){
	    	foreach ($array as $key => $val) {
		        if (is_array($val) && is_array($array2[$key])) {
		            $new[$key] = $this->array_map_deep($val, $array2[$key], $callback);
		        } else {
		            $new[$key] = call_user_func($callback, $val, $array2[$key]);
		        }
		    }
	    }else{
	    	$new = call_user_func($callback, $array, $array2);
	    }
	    return $new;

	}

	public function tm_epo_price_filtered($price,$type){
		return apply_filters( 'woocommerce_tm_epo_price2',$price,$type);
	}

	/* For hiding uploaded file path */
	public function tm_order_item_display_meta_value($value){
		if (is_order_received_page()){
			if($this->tm_epo_hide_upload_file_path != 'no' && filter_var($value, FILTER_VALIDATE_URL)){
				$value=basename($value);
			}
		}
		return $value;
	}
	
	/**
	 * Caclulates the extra subscription fee
	 */
	public function tm_subscriptions_product_sign_up_fee( $subscription_sign_up_fee, $product ) {
		global $woocommerce;
		$options_fee=0;
		if (!is_product() && $woocommerce->cart){
			$cart_contents = $woocommerce->cart->get_cart();
			foreach ($cart_contents as $cart_key => $cart_item) {
				foreach ($cart_item as $key => $data) {
					if ($key=="tmsubscriptionfee"){
						$options_fee=$data;
					}
				}
			}
			$subscription_sign_up_fee += $options_fee;
		}
		return $subscription_sign_up_fee;
	}

	/* enable shortcodes for labels */	
	public function tm_epo_option_name( $label ) {
		return do_shortcode($label);
	}

	/* Alters the Free label html */
	public function get_price_html( $price, $product ) {		
		return sprintf( $this->tm_epo_replacement_free_price_text, $price );
	}

	public function get_price_html_shop( $price, $product ) {
		if ($product && 
			is_object($product) && method_exists($product, "get_price") 
			&& !(float)$product->get_price()>0 ){
			
			if ($this->tm_epo_replacement_free_price_text){
				$price=sprintf( $this->tm_epo_replacement_free_price_text, $price );
			}else{
				$price='';
			}
		}
		return $price;
	}

	public function add_to_cart_text( $text ) {
		global $product;

		if ( $this->tm_epo_force_select_options=="display" ) {
			if ($this->cpf){
				$cpf=$this->cpf;
			}else{
				$cpf=$this->get_product_tm_epos($product->id);
			}
			if (is_array($cpf) && (!empty($cpf['global']) || !empty($cpf['local']))) {
				$text = __( 'Select options', TM_EPO_TRANSLATION );
			}
		}

		return $text;
	}

	public function add_to_cart_url( $url ) {
		global $product;

		if ( is_shop() && $this->tm_epo_force_select_options=="display" ) {
			if ($this->cpf){
				$cpf=$this->cpf;
			}else{
				$cpf=$this->get_product_tm_epos($product->id);
			}
			if (is_array($cpf) && (!empty($cpf['global']) || !empty($cpf['local']))) {
				$url = get_permalink( $product->id );
			}
		}

		return $url;
	}

	public function clear_cart() {
		global $woocommerce;
		if ( isset( $_POST['tm_empty_cart'] ) ) {
			$woocommerce->cart->empty_cart();
		}
	}

	public function add_empty_cart_button(){
		echo '<input type="submit" class="tm-clear-cart-button checkout-button button wc-forward" name="tm_empty_cart" value="'.__( 'Empty cart', TM_EPO_TRANSLATION ).'" />';
	}

	private function set_tm_meta($override_id=0){
		if (empty($override_id)){
			global $post;
			if (!is_null($post) && property_exists($post,'ID')){
				$override_id=$post->ID;
			}
			if (isset($_REQUEST['add-to-cart'])){
				$override_id=$_REQUEST['add-to-cart'];
			}
		}
		if (empty($override_id)){
			return;
		}
		$this->tm_meta_cpf = get_post_meta( $override_id, 'tm_meta_cpf', true );		
		
		foreach ( $this->meta_fields as $key=>$value ) {
			$this->tm_meta_cpf[$key] = isset( $this->tm_meta_cpf[$key] ) ? $this->tm_meta_cpf[$key] : $value;
		}
		$this->tm_meta_cpf['metainit']=1;
	}

	/**
	 * Initialize custom product settings
	 */
	public function init_settings(){
		if (class_exists('WOOCS')){
			global $WOOCS;
			remove_filter('woocommerce_order_amount_total', array( $WOOCS, 'woocommerce_order_amount_total' ), 999);
		}
		/* post_max_size debug */
		if(empty($_FILES) 
			&& empty($_POST) 
			&& isset($_SERVER['REQUEST_METHOD']) 
			&& strtolower($_SERVER['REQUEST_METHOD']) == 'post'){

        	$postMax = ini_get('post_max_size');
			wc_add_notice( sprintf( __( 'Trying to upload files larger than %s is not allowed!', TM_EPO_TRANSLATION ), $postMax ) , 'error' );
		}

		global $post,$product;
		$this->set_tm_meta();

		/* Check if the plugin is active for the user */
		if ($this->check_enable()){
			if ((is_product() || $this->is_quick_view() ) && ($this->display=='normal' || $this->tm_meta_cpf['override_display']=='normal') && $this->tm_meta_cpf['override_display']!='action'){
				$this->noactiondisplay=true;
				add_action( $this->tm_epo_options_placement, array( $this, 'tm_epo_fields' ), 50 );
				add_action( $this->tm_epo_totals_box_placement, array( $this, 'tm_epo_totals' ), 50 );
				
			}
		}

		if ($this->tm_epo_remove_free_price_label=='yes'){
			if ($post){
				$this->cpf=$this->get_product_tm_epos($post->ID);
				if (is_product() && is_array($this->cpf) && (!empty($this->cpf['global']) || !empty($this->cpf['local']))) {
					if ($product && 
						(is_object($product) && !method_exists($product, "get_price")) ||
						(!is_object($product) ) 
						){
						$product=get_product($post->ID);
					}
					if ($product && 
						is_object($product) && method_exists($product, "get_price") 
						&& !(float)$product->get_price()>0 ){
						if ($this->tm_epo_replacement_free_price_text){
							add_filter('woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2);
						}else{
							remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' ,10);					
						}						
					}
				}else{
					if (is_shop()){
						add_filter('woocommerce_get_price_html', array( $this, 'get_price_html_shop' ), 10, 2);	
					}					
				}
			}else{
				if ($this->is_quick_view()){
					if ($this->tm_epo_replacement_free_price_text){
						add_filter('woocommerce_get_price_html', array( $this, 'get_price_html' ), 10, 2);
					}else{
						remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price' ,10);					
					}
				}
			}
		}

	}

	public function tm_bto_display_support( $product_id, $item_id ) {
		global $product;

		if (!$product){
			$product = get_product( $product_id );
		}
	    if (!$product){
	        // something went wrong. wrond product id??
	        // if you get here the plugin will not work :(
	    }else{	    	
			$this->set_tm_meta($product_id);
			$this->is_bto=true;
			if (($this->display=='normal' || $this->tm_meta_cpf['override_display']=='normal') && $this->tm_meta_cpf['override_display']!='action'){		
				$this->frontend_display($product_id, $item_id);
			}
		}
	}

	private function _tm_temp_uniqid($s){
		$a=array();
		for ( $m = 0; $m < $s; $m++ ) {
			$a[]=uniqid('', true);
		}
		return $a;
	}

	/**
	 * Gets a list of all the Extra Product Options (local and global)
	 * for the specific $post_id.
	 */
	public function get_product_tm_epos( $post_id=0 ) {
		if ( empty( $post_id ) ) {
			return array();
		}

		$in_cat="";

		$terms = get_the_terms( $post_id, 'product_cat' );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$in_cat[] = $term->term_id;
			}
		}

		$_all_categories = get_terms( 'product_cat', array( 'fields' => "ids" ) );
		if ( !$_all_categories ) {
			$_all_categories = array();
		}

		/* Get Local options */
		$args = array(
			'post_type'     => TM_EPO_LOCAL_POST_TYPE,
			'post_status'   => array( 'publish' ), // get only enabled extra options
			'numberposts'   => -1,
			'orderby'       => 'menu_order',
			'order'       	=> 'asc',
			'post_parent'   => $post_id
		);
		$tmlocalprices = get_posts( $args );
		if (empty($this->tm_meta_cpf['metainit'])){
			$this->set_tm_meta();
		}
		if (!$this->tm_meta_cpf['exclude']){
			/* Get Global options that have no catergory set (they apply to all products) */
			$args = array(
				'post_type'     => TM_EPO_GLOBAL_POST_TYPE,
				'post_status'   => array( 'publish' ), // get only enabled global extra options
				'numberposts'   => -1,
				'orderby'       => 'date',
				'order'       	=> 'asc',
				'tax_query'		=> array(
					array(
						'field' 			=> 'term_id',
						'taxonomy' 			=> 'product_cat',
						'terms' 			=> $_all_categories,
						'include_children' 	=> false,
						'operator'			=> 'NOT IN'
					)
				),
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key' => 'tm_meta_disable_categories', // get only enabled global extra options
						'value' => '1',
						'compare' => '!='
					),
					array(
						'key' => 'tm_meta_disable_categories',// backwards compatibility
						'value' => '1',
						'compare' => 'NOT EXISTS'
					)
				)
			);
			$tmglobalprices_empty = get_posts( $args );

			/* Get Global options that belong to the product categories */
			$args = array(
				'post_type'     => TM_EPO_GLOBAL_POST_TYPE,
				'post_status'   => array( 'publish' ), // get only enabled global extra options
				'numberposts'   => -1,
				'orderby'       => 'date',
				'order'       	=> 'asc',
				'tax_query'		=> array(
					array(
						'field' 			=> 'term_id',
						'taxonomy' 			=> 'product_cat',
						'terms' 			=> $in_cat,
						'include_children' 	=> false,
						'operator'			=> 'IN'
					)
				),
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key' => 'tm_meta_disable_categories',
						'value' => '1',
						'compare' => '!='
					),
					array(
						'key' => 'tm_meta_disable_categories',// backwards compatibility
						'value' => '1',
						'compare' => 'NOT EXISTS'
					)
				)
			);
			$tmglobalprices = get_posts( $args );

			/* Merge Global options */
			if ( $tmglobalprices_empty ) {
				foreach ( $tmglobalprices_empty as $price ) {
					$tmglobalprices[]=$price;
				}
			}
			
			/* Get Global options that apply to the product */
			$args = array(
				'post_type'     => TM_EPO_GLOBAL_POST_TYPE,
				'post_status'   => array( 'publish' ), // get only enabled global extra options
				'numberposts'   => -1,
				'orderby'       => 'date',
				'order'       	=> 'asc',
				'meta_query' => array(
					array(
						'key' => 'tm_meta_product_ids',
						'value' => '"'.$post_id.'";',
						'compare' => 'LIKE'
					)
				)
			);
			$tmglobalprices_products = get_posts( $args );	
			
			/* Merge Global options */
			if ( $tmglobalprices_products ) {
				$global_id_array=array();
				if ( $tmglobalprices ) {
					foreach ( $tmglobalprices as $price ) {
						$global_id_array[]=$price->ID;
					}
				}else{
					$tmglobalprices=array();
				}
				foreach ( $tmglobalprices_products as $price ) {
					if (!in_array($price->ID, $global_id_array)){
						$tmglobalprices[]=$price;	
					}				
				}
			}
		}

		/* Add current product to Global options array (has to be last to not conflict) */
		if ( empty($tmglobalprices) ) {
			$tmglobalprices=array();
		}
		$tmglobalprices[]=get_post($post_id);

		$product_epos=array();
		$global_epos=array();

		if ( $tmglobalprices ) {
			foreach ( $tmglobalprices as $price ) {
				if (!is_object($price)){
					continue;
				}
				$tmcp_id  	= absint( $price->ID );
				$tmcp_meta	= get_post_meta( $price->ID, 'tm_meta', true );

				$priority  	= isset( $tmcp_meta['priority'] )?absint( $tmcp_meta['priority'] ):1000;

				if ( isset( $tmcp_meta['tmfbuilder'] ) ) {

					$global_epos[$priority][$tmcp_id]['is_form']   	= 1;
					$global_epos[$priority][$tmcp_id]['is_taxonomy'] 	= 0;
					$global_epos[$priority][$tmcp_id]['name']    		= $price->post_title;
					$global_epos[$priority][$tmcp_id]['description'] 	= $price->post_excerpt;
					$global_epos[$priority][$tmcp_id]['sections'] 		= array();

					$builder=$tmcp_meta['tmfbuilder'];
					if ( is_array( $builder ) && count( $builder )>0 && isset( $builder['element_type'] ) && is_array( $builder['element_type'] ) && count( $builder['element_type'] )>0 ) {
						// All the elements
						$_elements=$builder['element_type'];
						// All element sizes
						$_div_size=$builder['div_size'];

						// All sections (holds element count for each section)
						$_sections=$builder['sections'];
						// All section sizes
						$_sections_size=$builder['sections_size'];
						// All section styles
						$_sections_style=$builder['sections_style'];
						// All section placements
						$_sections_placement=$builder['sections_placement'];


						if ( !is_array( $_sections ) ) {
							$_sections=array( count( $_elements ) );
						}
						if ( !is_array( $_sections_size ) ) {
							$_sections_size=array_fill(0, count( $_sections ) ,"w100");
						}
						if ( !is_array( $_sections_style ) ) {
							$_sections_style=array_fill(0, count( $_sections ) ,"");
						}
						if ( !is_array( $_sections_placement ) ) {
							$_sections_placement=array_fill(0, count( $_sections ) ,"before");
						}
						
						$_sections_uniqid 	= isset($builder['sections_uniqid'])?$builder['sections_uniqid']:$this->_tm_temp_uniqid(count( $_sections )) ;
						$_sections_clogic 	= isset($builder['sections_clogic'])?$builder['sections_clogic']:array_fill(0, count( $_sections ) ,false);
						$_sections_logic 	= isset($builder['sections_logic'])?$builder['sections_logic']:array_fill(0, count( $_sections ) ,"");
						$_sections_class 	= isset($builder['sections_class'])?$builder['sections_class']:array_fill(0, count( $_sections ) ,"");
						$_sections_type 	= isset($builder['sections_type'])?$builder['sections_type']:array_fill(0, count( $_sections ) ,"");

						$_helper_counter=0;
						$_counter=array();

						for ( $_s = 0; $_s < count( $_sections ); $_s++ ) {
							$global_epos[$priority][$tmcp_id]['sections'][$_s]=array(
								'total_elements'		=> $_sections[$_s],
								'sections_size'			=> $_sections_size[$_s],
								'sections_style'		=> $_sections_style[$_s],
								'sections_placement'	=> $_sections_placement[$_s],
								'sections_uniqid'		=> $_sections_uniqid[$_s],
								'sections_clogic'		=> $_sections_clogic[$_s],
								'sections_logic'		=> $_sections_logic[$_s],
								'sections_class'		=> $_sections_class[$_s],
								'sections_type'			=> $_sections_type[$_s],

								'label_size'			=> isset( $builder['section_header_size'][$_s])?$builder['section_header_size'][$_s]:"",
								'label'					=> isset( $builder['section_header_title'][$_s])?$builder['section_header_title'][$_s]:"",
								'label_color'			=> isset( $builder['section_header_title_color'][$_s])?$builder['section_header_title_color'][$_s]:"",
								'description'			=> isset( $builder['section_header_subtitle'][$_s])?$builder['section_header_subtitle'][$_s]:"",
								'description_position'	=> isset( $builder['section_header_subtitle_position'][$_s])?$builder['section_header_subtitle_position'][$_s]:"",
								'description_color'		=> isset( $builder['section_header_subtitle_color'][$_s])?$builder['section_header_subtitle_color'][$_s]:"",
								'divider_type'			=> isset( $builder['section_divider_type'][$_s])?$builder['section_divider_type'][$_s]:""

							);

							for ( $k0 = $_helper_counter; $k0 < intval( $_helper_counter+intval( $_sections[$_s] ) ); $k0++ ) {
								if ( isset( $_elements[$k0] ) ) {
									if ( !isset( $_counter[$_elements[$k0]] ) ) {
										$_counter[$_elements[$k0]]=0;
									}else {
										$_counter[$_elements[$k0]]++;
									}

									$_options=array();									
									$_regular_price=array();
									$_regular_price_filtered=array();
									$_regular_price_type=array();
									$_new_type=$_elements[$k0];
									$_prefix="";

									switch ( $_elements[$k0] ) {

									case "textarea":
									case "textfield":
									case "upload":
									case "date":
									case "range":
										$_prefix=$_elements[$k0]."_";
										if ( empty( $builder[$_elements[$k0].'_price'][$_counter[$_elements[$k0]]] ) ) {
											$builder[$_elements[$k0].'_price'][$_counter[$_elements[$k0]]]=0;
										}

										$_price	= $builder[$_elements[$k0].'_price'][$_counter[$_elements[$k0]]];
										$_regular_price=array( array( wc_format_decimal( $_price ) ) );
										
										$_regular_price_type=isset($builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]])?array( array( ( $builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]] ) ) ):array();

										$_for_filter_price_type=isset($builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]])?$builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]] :"";

										$_price	= apply_filters( 'woocommerce_tm_epo_price2', $_price, $_for_filter_price_type );

										$_regular_price_filtered=array( array( wc_format_decimal( $_price ) ) );
										
										break;

									case "selectbox":
									case "radiobuttons":
									case "checkboxes":
										$_prefix=$_elements[$k0]."_";
										if ( isset( $builder['multiple_'.$_elements[$k0].'_options_price'][$_counter[$_elements[$k0]]] ) ) {
											if ( empty( $builder['multiple_'.$_elements[$k0].'_options_price'][$_counter[$_elements[$k0]]] ) ) {
												$builder['multiple_'.$_elements[$k0].'_options_price'][$_counter[$_elements[$k0]]]=0;
											}
											$_prices=$builder['multiple_'.$_elements[$k0].'_options_price'][$_counter[$_elements[$k0]]];
											$_values=$builder['multiple_'.$_elements[$k0].'_options_value'][$_counter[$_elements[$k0]]];
											$_titles=$builder['multiple_'.$_elements[$k0].'_options_title'][$_counter[$_elements[$k0]]];
											$_images=isset($builder['multiple_'.$_elements[$k0].'_options_image'][$_counter[$_elements[$k0]]])?$builder['multiple_'.$_elements[$k0].'_options_image'][$_counter[$_elements[$k0]]]:array();
											$_url=isset($builder['multiple_'.$_elements[$k0].'_options_url'][$_counter[$_elements[$k0]]])?$builder['multiple_'.$_elements[$k0].'_options_url'][$_counter[$_elements[$k0]]]:array();
											$_prices_type=isset($builder['multiple_'.$_elements[$k0].'_options_price_type'][$_counter[$_elements[$k0]]])?$builder['multiple_'.$_elements[$k0].'_options_price_type'][$_counter[$_elements[$k0]]]:array();
											$_regular_price=array();
											$_regular_price_type=array();
											$_values_c=$_values;
											foreach ( $_prices as $_n=>$_price ) {
												$_regular_price[esc_attr( ( $_values[$_n] ) )."_".$_n]=array( wc_format_decimal( $_price ) );
												$_for_filter_price_type = isset($_prices_type[$_n])? $_prices_type[$_n] :"";
												$_price	= apply_filters( 'woocommerce_tm_epo_price2',$_price, $_for_filter_price_type);

												$_regular_price_filtered[esc_attr( ( $_values[$_n] ) )."_".$_n]=array( wc_format_decimal( $_price ) );
												$_regular_price_type[esc_attr( ( $_values[$_n] ) )."_".$_n]=isset($_prices_type[$_n])?array( ( $_prices_type[$_n] ) ):array('');
												$_options[esc_attr( ( $_values[$_n] ) )."_".$_n]=$_titles[$_n];	
												$_values_c[$_n]=$_values[$_n]."_".$_n;
											}
										}
										break;

									}

									$default_value = isset( $builder['multiple_'.$_elements[$k0].'_options_default_value'][$_counter[$_elements[$k0]]] )?$builder['multiple_'.$_elements[$k0].'_options_default_value'][$_counter[$_elements[$k0]]]:"";
									$selectbox_fee=false;
									$selectbox_cart_fee=false;
									switch ( $_elements[$k0] ) {

									case "selectbox":
										$_new_type="select";
										$selectbox_fee=isset($builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]])?array( array( ( $builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]] ) ) ):false;
										$selectbox_cart_fee=isset($builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]])?array( array( ( $builder[$_elements[$k0].'_price_type'][$_counter[$_elements[$k0]]] ) ) ):false;
										break;

									case "radiobuttons":
										$_new_type="radio";
										break;

									case "checkboxes":
										$_new_type="checkbox";
										break;

									}

									$_rules=$_regular_price;
									$_rules_filtered=$_regular_price_filtered;
									foreach ( $_regular_price as $key=>$value ) {
										foreach ( $value as $k=>$v ) {																						
											$_regular_price[$key][$k]=wc_format_localized_price( $v );
											$_regular_price_filtered[$key][$k]=wc_format_localized_price( $v );
										}
									}
									$_rules_type=$_regular_price_type;
									foreach ( $_regular_price_type as $key=>$value ) {
										foreach ( $value as $k=>$v ) {
											$_regular_price_type[$key][$k]= $v ;
										}
									}

									switch ( $_elements[$k0] ) {

									case "range":
									case "date":
									case "upload":
									case "textarea":
									case "textfield":
									case "selectbox":
									case "radiobuttons":
									case "checkboxes":
										$global_epos[$priority][$tmcp_id]['sections'][$_s]['elements'][]=array(
											'section' 			=> $_sections_uniqid[$_s],
											'type'				=> $_new_type,
											'size'				=> $_div_size[$k0],
											'required'			=> isset( $builder[$_prefix.'required'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'required'][$_counter[$_elements[$k0]]]:"",
											'use_images'		=> isset( $builder[$_prefix.'use_images'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'use_images'][$_counter[$_elements[$k0]]]:"",
											'use_url'			=> isset( $builder[$_prefix.'use_url'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'use_url'][$_counter[$_elements[$k0]]]:"",
											'items_per_row'		=> isset( $builder[$_prefix.'items_per_row'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'items_per_row'][$_counter[$_elements[$k0]]]:"",
											'label_size'		=> isset( $builder[$_prefix.'header_size'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_size'][$_counter[$_elements[$k0]]]:"",
											'label'				=> isset( $builder[$_prefix.'header_title'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_title'][$_counter[$_elements[$k0]]]:"",
											'label_color'		=> isset( $builder[$_prefix.'header_title_color'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_title_color'][$_counter[$_elements[$k0]]]:"",
											'description'		=> isset( $builder[$_prefix.'header_subtitle'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_subtitle'][$_counter[$_elements[$k0]]]:"",
											'description_position' => isset( $builder[$_prefix.'header_subtitle_position'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_subtitle_position'][$_counter[$_elements[$k0]]]:"",
											'description_color'	=> isset( $builder[$_prefix.'header_subtitle_color'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_subtitle_color'][$_counter[$_elements[$k0]]]:"",
											'divider_type'		=> isset( $builder[$_prefix.'divider_type'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'divider_type'][$_counter[$_elements[$k0]]]:"",
											'placeholder'		=> isset( $builder[$_prefix.'placeholder'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'placeholder'][$_counter[$_elements[$k0]]]:"",
											'max_chars'			=> isset( $builder[$_prefix.'max_chars'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'max_chars'][$_counter[$_elements[$k0]]]:"",
											'hide_amount'		=> isset( $builder[$_prefix.'hide_amount'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'hide_amount'][$_counter[$_elements[$k0]]]:"",
											'options'			=> $_options,
											'rules'				=> $_rules,
											'price_rules'		=> $_regular_price,
											'rules_filtered' 	=> $_rules_filtered,
											'price_rules_filtered' => $_regular_price_filtered,
											'price_rules_type'	=> $_regular_price_type,
											'rules_type'		=> $_rules_type,
											'images'			=> isset( $_images )?$_images:"",
											'url' 				=> isset( $_url )?$_url:"",
											'limit'				=> isset( $builder[$_prefix.'limit_choices'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'limit_choices'][$_counter[$_elements[$k0]]]:"",
											'option_values'		=> isset( $_values_c )?$_values_c:array(),
											'button_type' 		=> isset( $builder[$_prefix.'button_type'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'button_type'][$_counter[$_elements[$k0]]]:"",
											'uniqid' 			=> isset( $builder[$_prefix.'uniqid'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'uniqid'][$_counter[$_elements[$k0]]]:uniqid('', true) ,
											'clogic' 			=> isset( $builder[$_prefix.'clogic'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'clogic'][$_counter[$_elements[$k0]]]:false,
											'logic' 			=> isset( $builder[$_prefix.'logic'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'logic'][$_counter[$_elements[$k0]]]:"",
											'format' 			=> isset( $builder[$_prefix.'format'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'format'][$_counter[$_elements[$k0]]]:"",
											'start_year' 		=> isset( $builder[$_prefix.'start_year'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'start_year'][$_counter[$_elements[$k0]]]:"",
											'end_year' 			=> isset( $builder[$_prefix.'end_year'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'end_year'][$_counter[$_elements[$k0]]]:"",
											'tranlation_day' 	=> isset( $builder[$_prefix.'tranlation_day'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'tranlation_day'][$_counter[$_elements[$k0]]]:"",
											'tranlation_month' 	=> isset( $builder[$_prefix.'tranlation_month'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'tranlation_month'][$_counter[$_elements[$k0]]]:"",
											'tranlation_year' 	=> isset( $builder[$_prefix.'tranlation_year'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'tranlation_year'][$_counter[$_elements[$k0]]]:"",
											"default_value" 	=> $default_value,
											'text_after_price' 	=> isset( $builder[$_prefix.'text_after_price'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'text_after_price'][$_counter[$_elements[$k0]]]:"",
											'selectbox_fee' 	=> $selectbox_fee,
											'selectbox_cart_fee' => $selectbox_cart_fee,
											'class' 			=> isset( $builder[$_prefix.'class'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'class'][$_counter[$_elements[$k0]]]:"",
											'swatchmode' 		=> isset( $builder[$_prefix.'swatchmode'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'swatchmode'][$_counter[$_elements[$k0]]]:"",
											'changes_product_image' => isset( $builder[$_prefix.'changes_product_image'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'changes_product_image'][$_counter[$_elements[$k0]]]:"",
											'min' 				=> isset( $builder[$_prefix.'min'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'min'][$_counter[$_elements[$k0]]]:"",
											'max' 				=> isset( $builder[$_prefix.'max'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'max'][$_counter[$_elements[$k0]]]:"",
											'step' 				=> isset( $builder[$_prefix.'step'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'step'][$_counter[$_elements[$k0]]]:"",
											'pips' 				=> isset( $builder[$_prefix.'pips'][$_counter[$_elements[$k0]]] )?$builder[$_prefix.'pips'][$_counter[$_elements[$k0]]]:"",
										);
										break;

									case "header":
										$global_epos[$priority][$tmcp_id]['sections'][$_s]['elements'][]=array(
											'section' 			=> $_sections_uniqid[$_s],
											'type'				=> $_new_type,
											'size'				=> $_div_size[$k0],
											'required'			=> "",
											'use_images' 		=> "",
											'use_url' 			=> "",
											'items_per_row' 	=> "",
											'label_size'		=> isset( $builder[$_prefix.'header_size'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_size'][$_counter[$_elements[$k0]]]:"",
											'label'				=> isset( $builder[$_prefix.'header_title'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_title'][$_counter[$_elements[$k0]]]:"",
											'label_color'		=> isset( $builder[$_prefix.'header_title_color'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_title_color'][$_counter[$_elements[$k0]]]:"",
											'description'		=> isset( $builder[$_prefix.'header_subtitle'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_subtitle'][$_counter[$_elements[$k0]]]:"",
											'description_color'	=> isset( $builder[$_prefix.'header_subtitle_color'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'header_subtitle_color'][$_counter[$_elements[$k0]]]:"",
											'divider_type'		=> "",
											'placeholder'		=> "",
											'max_chars'			=> "",
											'hide_amount'		=> "",
											"options"			=> $_options,
											'rules'				=> $_rules,
											'price_rules'		=> $_regular_price,
											'rules_filtered' 	=> $_rules_filtered,
											'price_rules_filtered' => $_regular_price_filtered,
											'price_rules_type'	=> $_regular_price_type,
											'rules_type'		=> $_rules_type,
											'images'			=> "",
											'limit'				=> "",
											'option_values'		=> array(),
											'button_type' 		=>'',
											'class' 			=> isset($builder['header_class'][$_counter[$_elements[$k0]]])?$builder['header_class'][$_counter[$_elements[$k0]]]:'' ,
											'uniqid' 			=> isset($builder['header_uniqid'][$_counter[$_elements[$k0]]])?$builder['header_uniqid'][$_counter[$_elements[$k0]]]:uniqid('', true) ,
											'clogic' 			=> isset($builder['header_clogic'][$_counter[$_elements[$k0]]])?$builder['header_clogic'][$_counter[$_elements[$k0]]]:false,
											'logic' 			=> isset($builder['header_logic'][$_counter[$_elements[$k0]]])?$builder['header_logic'][$_counter[$_elements[$k0]]]:"",
											'format' 			=> '',
											'start_year' 		=> '',
											'end_year' 			=> '',
											'tranlation_day' 	=> '',
											'tranlation_month' 	=> '',
											'tranlation_year' 	=> '',
											'swatchmode' 		=> "",
											'changes_product_image' => "",
											'min' 				=> "",
											'max' 				=> "",
											'step' 				=> "",
											'pips' 				=> "",

										);									
										break;

									case "divider":
										$global_epos[$priority][$tmcp_id]['sections'][$_s]['elements'][]=array(
											'section' 			=> $_sections_uniqid[$_s],
											'type'				=> $_new_type,
											'size'				=> $_div_size[$k0],
											'required'			=> "",
											'use_images' 		=> "",
											'use_url' 			=> "",
											'items_per_row' 	=> "",
											'label_size'		=> "",
											'label'				=> "",
											'label_color'		=> "",
											'description'		=> "",
											'description_color'	=> "",
											'divider_type'		=> isset( $builder[$_prefix.'divider_type'][$_counter[$_elements[$k0]]])?$builder[$_prefix.'divider_type'][$_counter[$_elements[$k0]]]:"",
											'placeholder'		=> "",
											'max_chars'			=> "",
											'hide_amount'		=> "",
											"options"			=> $_options,
											'rules'				=> $_rules,
											'price_rules'		=> $_regular_price,
											'rules_filtered' 	=> $_rules_filtered,
											'price_rules_filtered' => $_regular_price_filtered,
											'price_rules_type'	=> $_regular_price_type,
											'rules_type'		=> $_rules_type,
											'images'			=> "",
											'limit'				=> "",
											'option_values'		=> array(),
											'button_type'=>'',
											'class' 			=> isset($builder['divider_class'][$_counter[$_elements[$k0]]])?$builder['divider_class'][$_counter[$_elements[$k0]]]:'' ,
											'uniqid' 			=> isset($builder['divider_uniqid'][$_counter[$_elements[$k0]]])?$builder['divider_uniqid'][$_counter[$_elements[$k0]]]:uniqid('', true) ,
											'clogic' 			=> isset($builder['divider_clogic'][$_counter[$_elements[$k0]]])?$builder['divider_clogic'][$_counter[$_elements[$k0]]]:false,
											'logic' 			=> isset($builder['divider_logic'][$_counter[$_elements[$k0]]])?$builder['divider_logic'][$_counter[$_elements[$k0]]]:"",
											'format' 			=> '',
											'start_year' 		=> '',
											'end_year' 			=> '',
											'tranlation_day' 	=> '',
											'tranlation_month' 	=> '',
											'tranlation_year' 	=> '',
											'swatchmode' 		=> "",
											'changes_product_image' => "",
											'min' 				=> "",
											'max' 				=> "",
											'step' 				=> "",
											'pips' 				=> "",
										);									
										break;

									}
								}
							}

							$_helper_counter=intval( $_helper_counter+intval( $_sections[$_s] ) );

						}
					}
				}
			}
		}

		ksort( $global_epos );

		if ( $tmlocalprices ) {

			$attributes=maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

			foreach ( $tmlocalprices as $price ) {
				$tmcp_id           								= absint( $price->ID );
				$tmcp_required          						= get_post_meta( $tmcp_id, 'tmcp_required', true );
				$tmcp_hide_price          						= get_post_meta( $tmcp_id, 'tmcp_hide_price', true );
				$tmcp_limit          							= get_post_meta( $tmcp_id, 'tmcp_limit', true );
				$product_epos[$tmcp_id]['is_form']  			= 0;
				$product_epos[$tmcp_id]['required']  			= empty( $tmcp_required )?0:1;
				$product_epos[$tmcp_id]['hide_price']  			= empty( $tmcp_hide_price )?0:1;
				$product_epos[$tmcp_id]['limit']  				= empty( $tmcp_limit )?"":$tmcp_limit;
				$product_epos[$tmcp_id]['name']   				= get_post_meta( $tmcp_id, 'tmcp_attribute', true );
				$product_epos[$tmcp_id]['is_taxonomy'] 			= get_post_meta( $tmcp_id, 'tmcp_attribute_is_taxonomy', true );
				$product_epos[$tmcp_id]['label']   				= wc_attribute_label( $product_epos[$tmcp_id]['name'] );
				$product_epos[$tmcp_id]['type']   				= get_post_meta( $tmcp_id, 'tmcp_type', true );

				// Retrieve attributes
				$product_epos[$tmcp_id]['attributes']  = array();
				if ( $product_epos[$tmcp_id]['is_taxonomy'] ) {
					if ( !( $attributes[$product_epos[$tmcp_id]['name']]['is_variation'] ) ) {
						$all_terms = get_terms( $attributes[$product_epos[$tmcp_id]['name']]['name'] , 'orderby=name&hide_empty=0' );
						if ( $all_terms ) {
			                foreach ( $all_terms as $term ) {
			                    $has_term = has_term( (int) $term->term_id, $attributes[$product_epos[$tmcp_id]['name']]['name'], $post_id ) ? 1 : 0;
			                    if ($has_term ){			                        
			                        $product_epos[$tmcp_id]['attributes'][esc_attr( $term->slug )]=apply_filters( 'woocommerce_tm_epo_option_name', esc_html( $term->name ) ) ;
			                    }
			                }
			            }
						
					}
				}else {
					if ( isset( $attributes[$product_epos[$tmcp_id]['name']] ) ) {
						$options = array_map( 'trim', explode( WC_DELIMITER, $attributes[$product_epos[$tmcp_id]['name']]['value'] ) );
						foreach ( $options as $option ) {
							$product_epos[$tmcp_id]['attributes'][esc_attr( sanitize_title( $option ) )]=esc_html( apply_filters( 'woocommerce_tm_epo_option_name', $option ) ) ;
						}
					}
				}

				// Retrieve price rules
				$_regular_price=get_post_meta( $tmcp_id, '_regular_price', true );
				$_regular_price_type=get_post_meta( $tmcp_id, '_regular_price_type', true );
				$product_epos[$tmcp_id]['rules']=$_regular_price;
				
				$_regular_price_filtered= $this->array_map_deep($_regular_price, $_regular_price_type, array($this, 'tm_epo_price_filtered'));
				$product_epos[$tmcp_id]['rules_filtered']=$_regular_price_filtered;

				$product_epos[$tmcp_id]['rules_type']=$_regular_price_type;
				if ( !is_array( $_regular_price ) ) {
					$_regular_price=array();
				}
				if ( !is_array( $_regular_price_type ) ) {
					$_regular_price_type=array();
				}
				foreach ( $_regular_price as $key=>$value ) {
					foreach ( $value as $k=>$v ) {
						$_regular_price[$key][$k]=wc_format_localized_price( $v );						
					}
				}
				foreach ( $_regular_price_type as $key=>$value ) {
					foreach ( $value as $k=>$v ) {
						$_regular_price_type[$key][$k]= $v ;
					}
				}
				$product_epos[$tmcp_id]['price_rules']=$_regular_price;
				$product_epos[$tmcp_id]['price_rules_filtered']=$_regular_price_filtered;
				$product_epos[$tmcp_id]['price_rules_type']=$_regular_price_type;
			}
		}
		$global_epos = $this->tm_fill_element_names($post_id,$global_epos, $product_epos, "");

		return array(
			'global'=> $global_epos,
			'local' => $product_epos
		);
	}

	/**
	 * Filters an $input array by key.
	 */
	private function array_filter_key( $input ,$what="tmcp_",$where="start") {
		if ( !is_array( $input ) || empty( $input ) ) {
			return array();
		}

		$filtered_result=array();

		if ($where=="end"){
			$what=strrev($what);
		}

		foreach ( $input as $key => $value ) {
			$k=$key;
			if ($where=="end"){
				$k=strrev($key);
			}
			if ( strpos( $k, $what ) === 0 ) {
				$filtered_result[$key] = $value;
			}
		}

		return $filtered_result;
	}

	/**
	 * Translate $attributes to post names.
	 */
	private function translate_fields( $attributes, $type, $section, $form_prefix="",$name_prefix="" ) {
		$fields=array();
		$loop=0;

		/* $form_prefix should be passed with _ if not empty */
		if ( !empty( $attributes ) ) {

			foreach ( $attributes as $key=>$attribute ) {
				$name_inc="";
				switch ( $type ) {
				case "radio":
					$name_inc ="tmcp_".$name_prefix."radio_".$section.$form_prefix;
					break;
				case "select":
					$name_inc ="tmcp_".$name_prefix."select_".$section.$form_prefix;
					break;
				case "checkbox":
					$name_inc ="tmcp_".$name_prefix."checkbox_".$section."_".$loop.$form_prefix;
					break;
				}
				$fields[]=$name_inc;
				$loop++;
			}

		}else {

			switch ( $type ) {
			case "date":
				$name_inc ="tmcp_".$name_prefix."date_".$section.$form_prefix;
				break;		
			case "upload":
				$name_inc ="tmcp_".$name_prefix."upload_".$section.$form_prefix;
				break;	
			case "textarea":
				$name_inc ="tmcp_".$name_prefix."textarea_".$section.$form_prefix;
				break;
			case "textfield":
				$name_inc ="tmcp_".$name_prefix."textfield_".$section.$form_prefix;;
				break;
			case "range":
				$name_inc ="tmcp_".$name_prefix."range_".$section.$form_prefix;;
				break;
			case "select":
				$name_inc ="tmcp_".$name_prefix."select_".$section.$form_prefix;
				break;
			}
			if (!empty($name_inc)){
				$fields[]=$name_inc;
			}

		}

		return $fields;
	}

	/**
	 * Adds an item to the cart.
	 */
	public function add_cart_item( $cart_item ) {

		if ( ! empty( $cart_item['tmcartepo'] ) ) {
			$tmcp_prices = 0;
			foreach ( $cart_item['tmcartepo'] as $tmcp ) {
				$tmcp['price']=(float)wc_format_decimal($tmcp['price'],"",true);
				$tmcp_prices += $tmcp['price'];
			}
			$cart_item['data']->adjust_price( $tmcp_prices );
		}

		/**
		 * variation slug-to-name-for order again
		 */
		if ( isset( $cart_item["variation"] ) && is_array( $cart_item["variation"] ) ) {
			$_variation_name_fix=array();
			$_temp=array();
			foreach ( $cart_item["variation"] as $meta_name => $meta_value ) {
				if ( strpos( $meta_name, "attribute_" )!==0 ) {
					$_variation_name_fix["attribute_".$meta_name]=$meta_value;
					$_temp[$meta_name]=$meta_value;
				}
			}
			$cart_item["variation"]=array_diff_key( $cart_item["variation"], $_temp );
			$cart_item["variation"]=array_merge( $cart_item["variation"], $_variation_name_fix );
		}

		return $cart_item;
	}

	/**
	 * Gets the cart from session.
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {
		if ( ! empty( $values['tmcartepo'] ) ) {
			$cart_item['tmcartepo'] = $values['tmcartepo'];
			$cart_item = $this->add_cart_item( $cart_item );
		}
		if ( ! empty( $values['tmcartepo_bto'] ) ) {
			$cart_item['tmcartepo_bto'] = $values['tmcartepo_bto'];
		}
		if ( ! empty( $values['tmsubscriptionfee'] ) ) {
			$cart_item['tmsubscriptionfee'] = $values['tmsubscriptionfee'];
		}
		if ( ! empty( $values['tmcartfee'] ) ) {
			$cart_item['tmcartfee'] = $values['tmcartfee'];
		}
		return $cart_item;
	}

	/**
	 * Filters our cart items.
	 */
	private function filtered_get_item_data( $cart_item ) {
		$filtered_array=array();

		foreach ( $cart_item['tmcartepo'] as $tmcp ) {

			if ( !isset( $filtered_array[$tmcp['section']] ) ) {
				$filtered_array[$tmcp['section']]=array(
					'label' 		=> $tmcp['section_label'],
					'other_data' 	=> array( 
						array(
							'name'    	=> $tmcp['name'],
							'value'   	=> $tmcp['value'],
							'display' 	=> isset( $tmcp['display'] ) ? $tmcp['display'] : '',
							'images' 	=> isset( $tmcp['images'] ) ? $tmcp['images'] : ''
						) ),
					'price' 		=> $tmcp['price'],
					'percentcurrenttotal' => isset($tmcp['percentcurrenttotal'])?$tmcp['percentcurrenttotal']:0
				);
			}else {
				$filtered_array[$tmcp['section']]['price'] +=$tmcp['price'];
				$filtered_array[$tmcp['section']]['other_data'][] =  array(
					'name'    	=> $tmcp['name'],
					'value'   	=> $tmcp['value'],
					'display' 	=> isset( $tmcp['display'] ) ? $tmcp['display'] : '',
					'images' 	=> isset( $tmcp['images'] ) ? $tmcp['images'] : ''
				);
			}
		}

		return $filtered_array;
	}

	private function get_price_for_cart($price=0){
		$symbol="+";
		if (floatval($price)<0){
			$symbol="-";
		}
		if (floatval($price)==0){
			$symbol="";
		}else{
			$price = ( wc_price( abs($price) ) );
			if ($this->tm_epo_strip_html_from_emails=="yes"){
				$price=strip_tags($price);
			}			
			$symbol=" ($symbol" .$price.")";
		}	
		return $symbol;
	}

	/**
	 * Gets cart item to display in the frontend.
	 */
	public function get_item_data( $other_data, $cart_item ) {

		if ( $this->tm_epo_hide_options_in_cart=="normal" && !empty( $cart_item['tmcartepo'] ) ) {

			$filtered_array=$this->filtered_get_item_data( $cart_item );
			$price=0;
			$link_data=array();
			foreach ( $filtered_array as $section ) {

				$value=array();

				foreach ( $section['other_data'] as $key=>$data ) {
					$display_value = ! empty( $data['display'] ) ? $data['display'] : $data['value'];

					if (!empty($data['images']) && !$this->tm_epo_strip_html_from_emails){
						$display_value ='<div class="cpf-img-on-cart"><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="'.$data['images'].'" /></div>'.$display_value;
					}
					$value[]=$display_value;
				}

				if ( !empty( $value ) && count( $value )>0 ) {
					$value=implode( " , ", $value );
				}else {
					$value="";
				}				

				$price=$price+$section['price'];
				if ($this->tm_epo_hide_options_prices_in_cart=="normal"){
					$format_price=$this->get_price_for_cart($section['price']);
				}else{
					$format_price='';
				}
				
				$other_data[] = array(
					'name'    => $section['label'] . $format_price,
					'value'   => do_shortcode(html_entity_decode ($value))
				);
				$link_data[] = array(
					'name'    => $section['label'] ,
					'value'   => $value,
					'price'   => $format_price
				);
			}

			if ($this->tm_epo_cart_field_display=="link"){
				if (empty($price) || $this->tm_epo_hide_options_prices_in_cart!="normal"){
					$price='';
				}else{
					$price=$this->get_price_for_cart($price);
				}
				$uni=uniqid('');
				$data='<div class="tm-extra-product-options">';
				foreach ( $link_data as $link ) {
					$data .= '<div class="row tm-cart-row">'
							. '<div class="cell col-5 cpf-name">'.$link['name'].'</div>'
							. '<div class="cell col-4 cpf-value">'.do_shortcode(html_entity_decode ($link['value'])).'</div>'
							. '<div class="cell col-3 cpf-price">'.$link['price'].'</div>'
							. '</div>';

				}

				$other_data=array(
					array(
						'name' 	=> '<a href="#tm-cart-link-data-'.$uni.'" class="tm-cart-link">'.__( 'Additional options', TM_EPO_TRANSLATION ).'</a>',
						'value' => $price.'<div id="tm-cart-link-data-'.$uni.'" class="tm-cart-link-data tm-hidden">'.$data.'</div>'
						)
					);
			}

		}	
		
		return $other_data;
	}

	private function calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id ) {
		$_price=0;
		$_price_type="";
		$key=esc_attr($key);
		if ($per_product_pricing){

			if ( !isset( $element['price_rules'][$key] ) ) {// field price rule
				if ( $variation_id && isset( $element['price_rules'][0][$variation_id] ) ) {// general variation rule
					$_price=$element['price_rules'][0][$variation_id];
				}elseif ( isset( $element['price_rules'][0][0] ) ) {// general rule
					$_price=$element['price_rules'][0][0];
				}
			}else {
				if ( $variation_id && isset( $element['price_rules'][$key][$variation_id] ) ) {// field price rule
					$_price=$element['price_rules'][$key][$variation_id];
				}elseif ( isset( $element['price_rules'][$key][0] ) ) {// general field variation rule
					$_price=$element['price_rules'][$key][0];
				}elseif ( $variation_id && isset( $element['price_rules'][0][$variation_id] ) ) {// general variation rule
					$_price=$element['price_rules'][0][$variation_id];
				}elseif ( isset( $element['price_rules'][0][0] ) ) {// general rule
					$_price=$element['price_rules'][0][0];
				}
			}

			if ( !isset( $element['price_rules_type'][$key] ) ) {// field price rule
				if ( $variation_id && isset( $element['price_rules_type'][0][$variation_id] ) ) {// general variation rule
					$_price_type=$element['price_rules_type'][0][$variation_id];
				}elseif ( isset( $element['price_rules_type'][0][0] ) ) {// general rule
					$_price_type=$element['price_rules_type'][0][0];
				}
			}else {
				if ( $variation_id && isset( $element['price_rules_type'][$key][$variation_id] ) ) {// field price rule
					$_price_type=$element['price_rules_type'][$key][$variation_id];
				}elseif ( isset( $element['price_rules_type'][$key][0] ) ) {// general field variation rule
					$_price_type=$element['price_rules_type'][$key][0];
				}elseif ( $variation_id && isset( $element['price_rules_type'][0][$variation_id] ) ) {// general variation rule
					$_price_type=$element['price_rules_type'][0][$variation_id];
				}elseif ( isset( $element['price_rules_type'][0][0] ) ) {// general rule
					$_price_type=$element['price_rules_type'][0][0];
				}
			}
			
			if ($_price_type=="percent"){
				if ($cpf_product_price){
					$cpf_product_price=apply_filters( 'woocommerce_tm_epo_price2_remove',$cpf_product_price,"percent");
					$_price=($_price/100)*floatval($cpf_product_price);
				}
			}
			if ($_price_type=="percentcurrenttotal"){
				if (isset($_POST[$attribute.'_hidden'])){
					$_price=floatval($_POST[$attribute.'_hidden']);
					$_price=apply_filters( 'woocommerce_tm_epo_price2_remove',$_price,"percentcurrenttotal");
				}
			}
			if ($_price_type=="char"){
				if ($cpf_product_price){
					$_price=floatval($_price*strlen(stripcslashes($_POST[$attribute])));
				}
			}

		}
		return $_price;
	}

	/**
	 * Adds data to the cart.
	 */
	public function add_cart_item_data( $cart_item_meta, $product_id ) {

		/* Workaround to get unique items in cart for bto */
		$terms 			= get_the_terms( $product_id, 'product_type' );
		$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
		if ( $product_type == 'bto' && isset( $_REQUEST[ 'add-product-to-cart' ] ) && is_array( $_REQUEST[ 'add-product-to-cart' ] ) ) {
			$copy=array();
			foreach ( $_REQUEST[ 'add-product-to-cart' ] as $bundled_item_id => $bundled_product_id ) {
				$copy=array_merge($copy,$this->array_filter_key( $_POST ,$bundled_item_id,"end"));				
			}
			$copy=$this->array_filter_key( $copy);
			$cart_item_meta['tmcartepo_bto']=$copy;
		}

		$form_prefix="";
		$variation_id=false;
		$cpf_product_price=false;
		$per_product_pricing=true;

		if (isset($cart_item_meta['composite_item'])){
			global $woocommerce;
			$cart_contents = $woocommerce->cart->get_cart();

			if ( isset( $cart_item_meta[ 'composite_parent' ] ) && ! empty( $cart_item_meta[ 'composite_parent' ] ) ) {
				$parent_cart_key = $cart_item_meta[ 'composite_parent' ];
				$per_product_pricing 	= $cart_contents[ $parent_cart_key ][ 'data' ]->per_product_pricing;
				if ( $per_product_pricing == 'no' ) {
					$per_product_pricing=false;
				}
			}
			
			$form_prefix="_".$cart_item_meta['composite_item'];
			$bundled_item_id= $cart_item_meta['composite_item'];
			if (isset($_REQUEST[ 'bto_variation_id' ][ $bundled_item_id ])){
				$variation_id=$_REQUEST[ 'bto_variation_id' ][ $bundled_item_id ];
			}
			if (isset($_POST['cpf_product_price'.$form_prefix])){
				$cpf_product_price=$_POST['cpf_product_price'.$form_prefix];
			}
		}else{
			if (isset($_POST['variation_id'])){
				$variation_id=$_POST['variation_id'];
			}
			if (isset($_POST['cpf_product_price'])){
				$cpf_product_price=$_POST['cpf_product_price'];
			}		
		}

		$cpf_price_array  = $this->get_product_tm_epos( $product_id );
		$global_price_array = $cpf_price_array['global'];
		$local_price_array  = $cpf_price_array['local'];

		$global_prices=array( 'before'=>array(), 'after'=>array() );
		foreach ( $global_price_array as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				foreach ( $field['sections'] as $section_id=>$section ) {
					if ( isset( $section['sections_placement'] ) ) {
						$global_prices[$section['sections_placement']][$priority][$pid]['sections'][$section_id]=$section;
					}
				}
			}
		}

		$files=array();
		foreach ( $_FILES as $k=>$file){
			if (!empty($file['name'])){
				$files[$k]=$file['name'];
			}
		}

		$tmcp_post_fields = array_merge($this->array_filter_key( $_POST ),$this->array_filter_key( $files ));
		if ( is_array( $tmcp_post_fields ) ) {
			$tmcp_post_fields = array_map( 'stripslashes_deep', $tmcp_post_fields );
		}

		if ( empty( $cart_item_meta['tmcartepo'] ) ) {
			$cart_item_meta['tmcartepo'] = array();
		}
		if ( empty( $cart_item_meta['tmsubscriptionfee'] ) ) {
			$cart_item_meta['tmsubscriptionfee'] = 0;
		}
		if ( empty( $cart_item_meta['tmcartfee'] ) ) {
			$cart_item_meta['tmcartfee'] = array();
		}

		$loop=0;
		$field_loop=0;

		foreach ( $global_prices['before'] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id=>$section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {

							/* Cart fees */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ,$this->cart_fee_name) )  );
							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {;
								switch ( $element['type'] ) {

								case "checkbox" :
								case "radio" :
								case "select" :
									if (empty($key)){
										break;
									}
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartfee'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> $_price,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
									}
									break;

								case "textarea" :
								case "textfield" :	
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$cart_item_meta['tmcartfee'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> $_price,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0
										);
										
									}
									break;
								}
															
							}

							/* Subscription fees */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ,$this->fee_name) )  );							
							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {;
								switch ( $element['type'] ) {

								case "checkbox" :
								case "radio" :
								case "select" :
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartepo'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> 0,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
										$this->tmfee=$this->tmfee+(float)$_price;
									}
									break;

								case "textarea" :
								case "textfield" :	
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$cart_item_meta['tmcartepo'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> 0,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0
										);
										$this->tmfee=$this->tmfee+(float)$_price;
									}
									break;
								}
								$cart_item_meta['tmsubscriptionfee'] = $this->tmfee;							
							}
							
							/* Normal fields */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ,"") )  );
							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {
								switch ( $element['type'] ) {
								
								case "upload" :
									$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
									if (empty($key)){
										$_price=0;
									}
									if ( ! empty( $_FILES[ $attribute ] ) && ! empty( $_FILES[ $attribute ]['name'] ) ) {
										$upload = $this->upload_file( $_FILES[ $attribute ] );
										
										if ( empty( $upload['error'] ) && ! empty( $upload['file'] ) ) {
											$value  = wc_clean( $upload['url'] );
											wc_add_notice( __("Upload successful",TM_EPO_TRANSLATION) , 'success' );
											$cart_item_meta['tmcartepo'][] = array(
												'name'   => esc_html( $element['label'] ),
												'value'  => esc_html( $value ),
												'display'	=> esc_html(basename( $value )),
												'price'  => esc_attr( $_price ),
												'section'  => esc_html( $element['uniqid'] ),
												'section_label'  => esc_html( $element['label'] ),
												'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0
											);
										}else{											
											wc_add_notice( $upload['error'] , 'error' );
										}
									}

									break;

								case "checkbox" :
								case "radio" :
								case "select" :
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartepo'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> esc_attr( $_price ),
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
									}
									break;

								case "textarea" :
								case "textfield" :	
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
									 
										$cart_item_meta['tmcartepo'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> esc_attr( $_price ),
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0
										);
									}
									break;

								}
							}

							if (in_array($element['type'], $this->element_post_types)   ){
								$field_loop++;
							}
							$loop++;

						}
					}
				}
			}
		}

		if ( ! empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0 ) {

			if ( is_array( $tmcp_post_fields ) ) {

				foreach ( $local_price_array as $tmcp ) {
					if ( empty( $tmcp['type'] ) ) {
						continue;
					}

					$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $tmcp['attributes'], $tmcp['type'], $field_loop, $form_prefix ) ) );

					foreach ( $current_tmcp_post_fields as $attribute=>$key ) {
						
						switch ( $tmcp['type'] ) {

						case "checkbox" :
						case "radio" :
						case "select" :
							$_price=$this->calculate_price( $tmcp, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
							
							$cart_item_meta['tmcartepo'][] = array(
								'name'   => esc_html( $tmcp['name'] ),
								'value'  => esc_html( $tmcp['attributes'][$key]  ),
								'price'  => esc_attr( $_price ),
								'section'  => esc_html( $tmcp['name'] ),
								'section_label'  => esc_html( urldecode($tmcp['label']) ),
								'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0
							);
							break;

						}
					}
					if (in_array($tmcp['type'], $this->element_post_types)   ){
						$field_loop++;
					}
					$loop++;

				}
			}
		}

		foreach ( $global_prices['after'] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id=>$section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {

							/* Cart fees */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ,$this->cart_fee_name) )  );
							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {;
								switch ( $element['type'] ) {

								case "checkbox" :
								case "radio" :
								case "select" :
									if (empty($key)){
										break;
									}
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartfee'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> $_price,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
									}
									
									break;

								case "textarea" :
								case "textfield" :	
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$cart_item_meta['tmcartfee'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> $_price,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0
										);
										
									}
									break;
								}
															
							}
							
							/* Subscription fees */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ,$this->fee_name) )  );
							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {
								switch ( $element['type'] ) {

								case "checkbox" :
								case "radio" :
								case "select" :
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartepo'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> 0,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
										$this->tmfee=$this->tmfee+(float)$_price;
									}
									break;

								case "textarea" :
								case "textfield" :	
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$cart_item_meta['tmcartepo'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> 0,
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => 0
										);
										$this->tmfee=$this->tmfee+(float)$_price;
									}
									break;
								}
								$cart_item_meta['tmsubscriptionfee'] = $this->tmfee;								
							}
							
							/* Normal fields */
							$current_tmcp_post_fields=array_intersect_key(  $tmcp_post_fields , 
								array_flip( $this->translate_fields( $element['options'], $element['type'], $field_loop, $form_prefix ) )  );

							foreach ( $current_tmcp_post_fields as $attribute=>$key ) {
								
								switch ( $element['type'] ) {

								case "upload" :
									$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
									if (empty($key)){
										$_price=0;
									}
									if ( ! empty( $_FILES[ $attribute ] ) && ! empty( $_FILES[ $attribute ]['name'] ) ) {
										$upload = $this->upload_file( $_FILES[ $attribute ] );
										
										if ( empty( $upload['error'] ) && ! empty( $upload['file'] ) ) {
											$value  = wc_clean( $upload['url'] );
											wc_add_notice( __("Upload successful",TM_EPO_TRANSLATION) , 'success' );
											$cart_item_meta['tmcartepo'][] = array(
												'name'   => esc_html( $element['label'] ),
												'value'  => esc_html( $value ),
												'display'	=> esc_html(basename( $value )),
												'price'  => esc_attr( $_price ),
												'section'  => esc_html( $element['uniqid'] ),
												'section_label'  => esc_html( $element['label'] ),
												'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0
											);
										}else{											
											wc_add_notice( $upload['error'] , 'error' );
										}
									}

									break;

								case "checkbox" :
								case "radio" :
								case "select" :
									/* select placeholder check */
									if(isset($element['options'][esc_attr($key)])){
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
										$_image_key= array_search($key, $element['option_values']);
										if ($_image_key===NULL || $_image_key===FALSE){
											$_image_key=FALSE;
										}
										$cart_item_meta['tmcartepo'][] = array(
											'name'   		=> esc_html( $element['label'] ),
											'value'  		=> esc_html( $element['options'][esc_attr($key)] ),
											'price'  		=> esc_attr( $_price ),
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0,
											'use_images' 	=> !empty($element['use_images'])?$element['use_images']:"",
											'images' 		=> ($_image_key!==FALSE && isset($element['images'][$_image_key]))?$element['images'][$_image_key]:""
										);
									}
									break;

								case "textarea" :
								case "textfield" :
								case "date" :
								case "range":
									if (isset($key) && $key!=''){
										
										$_price=$this->calculate_price( $element, $key, $attribute, $per_product_pricing, $cpf_product_price, $variation_id );
									 
										$cart_item_meta['tmcartepo'][] = array(
											'name' 			=> esc_html( $element['label'] ),
											'value' 		=> esc_html( $key ),
											'price' 		=> esc_attr( $_price ),
											'section' 		=> esc_html( $element['uniqid'] ),
											'section_label' => esc_html( $element['label'] ),
											'percentcurrenttotal' => isset($_POST[$attribute.'_hidden'])?1:0
										);
									}
									break;

								}
							}

							if (in_array($element['type'], $this->element_post_types)   ){
								$field_loop++;
							}
							$loop++;

						}
					}
				}
			}
		}	

		return $cart_item_meta;
	}

	/**
	 * Adds meta data to the order.
	 */
	public function order_item_meta( $item_id, $values ) {
		if ( ! empty( $values['tmcartepo'] ) ) {			
			wc_add_order_item_meta( $item_id, '_tmcartepo_data', $values['tmcartepo'] );
			$filtered_array=$this->filtered_get_item_data( $values );
			
			foreach ( $filtered_array as $section ) {
				$value=array();
				foreach ( $section['other_data'] as $key=>$data ) {
					$display_value = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
					if (!empty($data['images']) && !$this->tm_epo_strip_html_from_emails){
						$display_value ='<div><img alt="" class="attachment-shop_thumbnail wp-post-image epo-option-image" src="'.$data['images'].'" /></div>'.$display_value;
					}else{
						$display_value = $data['value'];
					}
					$value[]=$display_value;
				}
				if ( !empty( $value ) && count( $value )>0 ) {
					$value=implode( " , ", $value );
				}else {
					$value="";
				}				
				
				if (class_exists('WOOCS')){
					$_not_woocs="";
				}else{
					$_not_woocs=$this->get_price_for_cart($section['price']);
				}
				$name = $section['label'] . $_not_woocs;
				if (empty($name)){
					$name=" ";
				}
				wc_add_order_item_meta( $item_id, $name, $value );
			}
		}
		if ( ! empty( $values['tmsubscriptionfee'] ) ) {
			wc_add_order_item_meta( $item_id, '_tmsubscriptionfee_data', array($values['tmsubscriptionfee']) );
			wc_add_order_item_meta( $item_id, __("Options Subscription fee",TM_EPO_TRANSLATION), $values['tmsubscriptionfee'] );
		}
		if ( ! empty( $values['tmcartfee'] ) ) {
			wc_add_order_item_meta( $item_id, '_tmcartfee_data', array($values['tmcartfee']) );
		}
	}

	/**
	 * Validates the cart data.
	 */

	public function add_to_cart_validation( $passed, $product_id, $qty, $variation_id = '', $variations = array(), $cart_item_data = array() ) {
		/* disables add_to_cart_button class on shop page */
		if (is_ajax() && $this->tm_epo_force_select_options=="display" ){
			
			if ($this->cpf){
				$cpf=$this->cpf;
			}else{
				$cpf=$this->get_product_tm_epos($product_id);
			}
			if (is_array($cpf) && (!empty($cpf['global']) || !empty($cpf['local']))) {
				return false;
			}
		 
		}	

		$is_validate=true;

		// Get product type
		$terms 			= get_the_terms( $product_id, 'product_type' );
		$product_type 	= ! empty( $terms ) && isset( current( $terms )->name ) ? sanitize_title( current( $terms )->name ) : 'simple';
		if ( $product_type == 'bto' ) {

			$bto_data 	= maybe_unserialize( get_post_meta( $product_id, '_bto_data', true ) );
			$valid_ids 	= array_keys( $bto_data );

			foreach ( $valid_ids as $bundled_item_id ) {

				if ( isset( $_REQUEST[ 'add-product-to-cart' ][ $bundled_item_id ] ) && $_REQUEST[ 'add-product-to-cart' ][ $bundled_item_id ] !== '' ) {
					$bundled_product_id = $_REQUEST[ 'add-product-to-cart' ][ $bundled_item_id ];
				} elseif ( isset( $cart_item_data[ 'composite_data' ][ $bundled_item_id ][ 'product_id' ] ) && isset( $_GET[ 'order_again' ] ) ) {
					$bundled_product_id = $cart_item_data[ 'composite_data' ][ $bundled_item_id ][ 'product_id' ];
				}

				if (isset($bundled_product_id) && !empty($bundled_product_id)){

					$_passed=true;

					if ( isset( $_REQUEST[ 'item_quantity' ][ $bundled_item_id ] ) && is_numeric( $_REQUEST[ 'item_quantity' ][ $bundled_item_id ] ) ) {
						$item_quantity = absint( $_REQUEST[ 'item_quantity' ][ $bundled_item_id ] );
					} elseif ( isset( $cart_item_data[ 'composite_data' ][ $bundled_item_id ][ 'quantity' ] ) && isset( $_GET[ 'order_again' ] ) ) {
						$item_quantity = $cart_item_data[ 'composite_data' ][ $bundled_item_id ][ 'quantity' ];
					}
					if ( !empty($item_quantity)){
						$item_quantity = absint( $item_quantity );
						
						$_passed = $this->validate_product_id( $bundled_item_id, $item_quantity, $bundled_item_id );
						
					}

					if (!$_passed){
						$is_validate=false;
					}
					
				}
			}
		}

		$passed = $this->validate_product_id( $product_id, $qty );

		/* Try to validate uploads before they happen */
		$files=array();
		foreach ( $_FILES as $k=>$file){
			if (!empty($file['name'])){
				if(!empty($file['error'])){
					$passed=false;
					// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
					$upload_error_strings = array( false,
						__( "The uploaded file exceeds the upload_max_filesize directive in php.ini.", TM_EPO_TRANSLATION  ),
						__( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.", TM_EPO_TRANSLATION  ),
						__( "The uploaded file was only partially uploaded.", TM_EPO_TRANSLATION  ),
						__( "No file was uploaded.", TM_EPO_TRANSLATION  ),
						'',
						__( "Missing a temporary folder.", TM_EPO_TRANSLATION  ),
						__( "Failed to write file to disk.", TM_EPO_TRANSLATION  ),
						__( "File upload stopped by extension.", TM_EPO_TRANSLATION  ));
					if (isset($upload_error_strings[$file['error']])){
						wc_add_notice( $upload_error_strings[$file['error']] , 'error' );
					}
				}
				$check_filetype=wp_check_filetype( $file['name'] ) ;
				$check_filetype=$check_filetype['ext'];
				if (!$check_filetype){
					$passed=false;
					wc_add_notice( __( "Sorry, this file type is not permitted for security reasons.", TM_EPO_TRANSLATION  ) , 'error' );
				}
			}			

		}

		if (!$is_validate){
			$passed=false;
		}		

		return $passed;

	}

	public function encodeURIComponent($str) {
	    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
	    return strtr(rawurlencode($str), $revert);
	}

	public function reverse_strrchr($haystack, $needle, $trail=0) {
	    return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) + $trail) : false;
	}

	public function is_visible($element=array(), $section=array(), $sections=array() , $form_prefix=""){
		
		/* Element */
		$logic = false;
		if (isset($element['section'])){
			if(!$this->is_visible($section, array(), $sections, $form_prefix)){
				return false;
			}
			if (!isset($element['logic']) || empty($element['logic'])){
				return true;
			}
			$logic= (array) json_decode($element['clogic']) ;
		/* Section */
		}else{
			if (!isset($element['sections_logic']) || empty($element['sections_logic'])){
				return true;
			}
			$logic= (array) json_decode($element['sections_clogic']) ;
		}

		if ($logic){
			$rule_toggle=$logic['toggle'];
			$rule_what=$logic['what'];
			$matches = 0;
			$checked = 0;
			$show=true;
			switch ($rule_toggle){
				case "show":
                	$show=false;
					break;
				case "hide":
					$show=true;
					break;
			}
			
			foreach($logic['rules'] as $key=>$rule){
				$matches++;
				
				if ($this->tm_check_field_match($rule, $sections, $form_prefix)){
                    $checked++;
                }
				
			}
			if ($rule_what=="all"){
				if ($checked==$matches){
					$show=!$show;
				}
			}else{
				if ($checked>0){
					$show=!$show;
				}
			}
			return $show;

		}

		return false;
	}

	public function tm_check_field_match($rule=false, $sections=false, $form_prefix=""){
		if (empty($rule) || empty($sections)){
			return false;
		}

		$section_id=$rule->section;
		$element_id=$rule->element;
		$operator=$rule->operator;
		$value=$rule->value;

		if (!isset($sections[$section_id]) 
			|| !isset($sections[$section_id]['elements']) 
			|| !isset($sections[$section_id]['elements'][$element_id]) 
			|| !isset($sections[$section_id]['elements'][$element_id]['name_inc']) 
			|| !isset($sections[$section_id]['elements'][$element_id]['type']) 
			){
			return false;
		}
		/* element array cannot hold the form_prefix for bto support, so we append manually */
		$element_to_check = $sections[$section_id]['elements'][$element_id]['name_inc'];		
		$element_type = $sections[$section_id]['elements'][$element_id]['type'];
		$posted_value = null;

		switch ($element_type){
			case "radio":
				$radio_checked_length=0;
				$element_to_check=array_unique($element_to_check);
				$element_to_check=$element_to_check[0];
				if ($sections[$section_id]['elements'][$element_id]['type']==""){
					$element_to_check=$element_to_check[0].$form_prefix;
				}				
				if (isset($_POST[$element_to_check])){
					$radio_checked_length++;
					$posted_value = $_POST[$element_to_check];
					$posted_value = stripslashes( $posted_value );
					$posted_value=$this->encodeURIComponent($posted_value);
					$posted_value=$this->reverse_strrchr($posted_value,"_");
				}
				if ($operator=='is' || $operator=='isnot'){
					if ($radio_checked_length==0){
						return false;
					}
				}else if ($operator=='isnotempty'){
					return $radio_checked_length>0;
				}else if ($operator=='isempty'){
					return $radio_checked_length==0;
				} 
			break;
			case "checkbox":
				$checkbox_checked_length=0;
				$ret=false;
				$element_to_check=array_unique($element_to_check);
				foreach ($element_to_check as $key => $name_value) {
					if ($sections[$section_id]['elements'][$element_id]['type']==""){
						$element_to_check[$key]=$name_value.$form_prefix;	
					}
					if (isset($_POST[$element_to_check[$key]])){
						$checkbox_checked_length++;
						$posted_value=$_POST[$element_to_check[$key]];
						$posted_value = stripslashes( $posted_value );
						$posted_value=$this->encodeURIComponent($posted_value);
						$posted_value=$this->reverse_strrchr($posted_value,"_");
					}
					if ($this->tm_check_match($posted_value,$value,$operator)){
						$ret=true;
					}
				}
				if ($operator=='is' || $operator=='isnot'){
					if ($checkbox_checked_length==0){
						return false;
					}
					return $ret;
				}else if ($operator=='isnotempty'){
					return $checkbox_checked_length>0;
				}else if ($operator=='isempty'){
					return $checkbox_checked_length==0;
				} 
			break;
			case "select":
			case "textarea":
			case "textfield":
				$element_to_check .=$form_prefix;
				if (isset($_POST[$element_to_check])){
					$posted_value = $_POST[$element_to_check];
					$posted_value = stripslashes( $posted_value );
					if ($element_type=="select"){
						$posted_value=$this->encodeURIComponent($posted_value);
						$posted_value=$this->reverse_strrchr($posted_value,"_");
					}
				}
			break;
		}
		return $this->tm_check_match($posted_value,$value,$operator);
	}

	public function tm_check_match($posted_value,$value,$operator){
		switch ($operator){
		case "is":
			return ($posted_value!=null && $value == $posted_value);
			break;
		case "isnot":
			return ($posted_value!=null && $value != $posted_value);
			break;
		case "isempty":								
			return (!(($posted_value != null && $posted_value!='')));
			break;
		case "isnotempty":								
			return (($posted_value != null && $posted_value!=''));
			break;
		}
		return false;
	}

	public function validate_product_id( $product_id, $qty, $form_prefix="" ) {

		$passed=true;
		if ($form_prefix){
			$form_prefix="_".$form_prefix;
		}
		if ($this->cpf){
			$cpf_price_array=$this->cpf;
		}else{
			$cpf_price_array=$this->get_product_tm_epos($product_id);
		}
		$global_price_array = $cpf_price_array['global'];
		$local_price_array  = $cpf_price_array['local'];

		$global_prices=array( 'before'=>array(), 'after'=>array() );
		$global_sections=array();
		foreach ( $global_price_array as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				if (isset($field['sections'])){
					foreach ( $field['sections'] as $section_id=>$section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[$section['sections_placement']][$priority][$pid]['sections'][$section_id]=$section;
							$global_sections[$section['sections_uniqid']]=$section;
						}
					}
				}
			}
		}

		if ( ( ! empty( $global_price_array ) && is_array( $global_price_array ) && count( $global_price_array ) > 0 ) || ( ! empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0 ) ) {
			$tmcp_post_fields = $this->array_filter_key( $_POST);			
			if ( is_array( $tmcp_post_fields ) && !empty( $tmcp_post_fields ) && count( $tmcp_post_fields )>0  ) {
				$tmcp_post_fields = array_map( 'stripslashes_deep', $tmcp_post_fields );
			}
		}

		$loop=-1;

		foreach ( $global_prices['before'] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id=>$section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {
							
							if (in_array($element['type'], $this->element_post_types)   ){
								$loop++;
							}
							
							if ( $element['required'] && $this->is_visible($element, $section, $global_sections, $form_prefix)) {

								$tmcp_attributes=$this->translate_fields( $element['options'], $element['type'], $loop, $form_prefix );
								$_passed = true;
								switch ( $element['type'] ) {

								case "upload" :									
									foreach ( $tmcp_attributes as $attribute ) {
										if ( empty( $_FILES[ $attribute ] ) || empty( $_FILES[ $attribute ]['name'] ) ) {
											$_passed = false;
										}										
									}
									break;

								case "checkbox" :
									$_check=array_intersect( $tmcp_attributes, array_keys( $tmcp_post_fields ) );

									if ( empty( $_check ) || count( $_check )==0 ) {
										$_passed = false;
									}
									break;

								case "radio" :
									foreach ( $tmcp_attributes as $attribute ) {
										if ( !isset( $tmcp_post_fields[$attribute] ) ) {
											$_passed = false;
										}
									}
									break;

								case "select" :
								case "textarea" :
								case "textfield" :
								case "date" :
								case "range":
									foreach ( $tmcp_attributes as $attribute ) {
										if ( !isset( $tmcp_post_fields[$attribute] ) ||  $tmcp_post_fields[$attribute]=="" ) {
											$_passed = false;
										}
									}
									break;

								}

								if ( ! $_passed ) {
									$passed = false;
									wc_add_notice( sprintf( __( '"%s" is a required field.', TM_EPO_TRANSLATION ), $element['label'] ) , 'error' );
									
								}
							}
						}
					}
				}
			}
		}



		if ( ! empty( $local_price_array ) && is_array( $local_price_array ) && count( $local_price_array ) > 0 ) {

			foreach ( $local_price_array as $tmcp ) {

				if (in_array($tmcp['type'], $this->element_post_types)   ){
					$loop++;
				}
				if ( empty( $tmcp['type'] ) || empty( $tmcp['required'] ) ) {
					continue;
				}

				if ( $tmcp['required'] ) {

					$tmcp_attributes=$this->translate_fields( $tmcp['attributes'], $tmcp['type'], $loop, $form_prefix );
					$_passed=true;

					switch ( $tmcp['type'] ) {

					case "checkbox" :
						$_check=array_intersect( $tmcp_attributes, array_keys( $tmcp_post_fields ) );
						if ( empty( $_check ) || count( $_check )==0 ) {
							$_passed = false;
						}
						break;

					case "radio" :
						foreach ( $tmcp_attributes as $attribute ) {
							if ( !isset( $tmcp_post_fields[$attribute] ) ) {
								$_passed = false;
							}
						}
						break;

					case "select" :
						foreach ( $tmcp_attributes as $attribute ) {
							if ( !isset( $tmcp_post_fields[$attribute] ) ||  $tmcp_post_fields[$attribute]=="" ) {
								$_passed = false;
							}
						}
						break;

					}

					if ( ! $_passed ) {
						$passed=false;
						wc_add_notice( sprintf( __( '"%s" is a required field.', TM_EPO_TRANSLATION ), $tmcp['label'] ) , 'error' );
						
					}
				}
			}

		}

		foreach ( $global_prices['after'] as $priorities ) {
			foreach ( $priorities as $field ) {
				foreach ( $field['sections'] as $section_id=>$section ) {
					if ( isset( $section['elements'] ) ) {
						foreach ( $section['elements'] as $element ) {

							if (in_array($element['type'], $this->element_post_types)   ){
								$loop++;
							}
							
							if ( $element['required'] && $this->is_visible($element, $section, $global_sections, $form_prefix)) {
								$tmcp_attributes=$this->translate_fields( $element['options'], $element['type'], $loop, $form_prefix );
								$_passed = true;

								switch ( $element['type'] ) {

								case "upload" :									
									foreach ( $tmcp_attributes as $attribute ) {
										if ( empty( $_FILES[ $attribute ] ) || empty( $_FILES[ $attribute ]['name'] ) ) {
											$_passed = false;
										}										
									}
									break;

								case "checkbox" :
									$_check=array_intersect( $tmcp_attributes, array_keys( $tmcp_post_fields ) );
									if ( empty( $_check ) || count( $_check )==0 ) {
										$_passed = false;
									}
									break;

								case "radio" :
									foreach ( $tmcp_attributes as $attribute ) {
										if ( !isset( $tmcp_post_fields[$attribute] ) ) {
											$_passed = false;
										}
									}
									break;

								case "select" :
								case "textarea" :
								case "textfield" :
								case "date" :
								case "range":
									foreach ( $tmcp_attributes as $attribute ) {
										if ( !isset( $tmcp_post_fields[$attribute] ) ||  $tmcp_post_fields[$attribute]=="" ) {
											$_passed = false;
										}
									}
									break;

								}

								if ( ! $_passed ) {
									$passed = false;
									wc_add_notice( sprintf( __( '"%s" is a required field.', TM_EPO_TRANSLATION ), $element['label'] ) , 'error' );
									
								}
							}
						}
					}
				}
			}
		}


		return $passed;
	}

	/**
	 * Gets the stored card data for the order again functionality.
	 */
	public function order_again_cart_item_data( $cart_item_meta, $product, $order ) {
		global $woocommerce;

		// Disable validation
		remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 50, 6 );

		$_backup_cart = isset( $product['item_meta']['tmcartepo_data'] ) ? $product['item_meta']['tmcartepo_data'] : false;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmcartepo_data'] ) ? $product['item_meta']['_tmcartepo_data'] : false;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			$_backup_cart=maybe_unserialize( $_backup_cart[0] );
			$cart_item_meta['tmcartepo'] = $_backup_cart;
		}

		$_backup_cart = isset( $product['item_meta']['tmsubscriptionfee_data'] ) ? $product['item_meta']['tmsubscriptionfee_data'] : false;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmsubscriptionfee_data'] ) ? $product['item_meta']['_tmsubscriptionfee_data'] : false;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			$_backup_cart=maybe_unserialize( $_backup_cart[0] );
			$cart_item_meta['tmsubscriptionfee'] = $_backup_cart[0];
		}
		
		$_backup_cart = isset( $product['item_meta']['tmcartfee_data'] ) ? $product['item_meta']['tmcartfee_data'] : false;
		if ( !$_backup_cart ) {
			$_backup_cart = isset( $product['item_meta']['_tmcartfee_data'] ) ? $product['item_meta']['_tmcartfee_data'] : false;
		}
		if ( $_backup_cart && is_array( $_backup_cart ) && isset( $_backup_cart[0] ) ) {
			$_backup_cart=maybe_unserialize( $_backup_cart[0] );
			$cart_item_meta['tmcartfee'] = $_backup_cart[0];
		}

		
		return $cart_item_meta;
	}

	/**
	 * Handles the display of builder sections.
	 */
	public function get_builder_display( $field, $where, $args, $form_prefix="" ) {
		
		/* $form_prefix	shoud be passed with _ if not empty */			
		
		$columns=array(
			"w25"=>array( "col-3", 25 ),
			"w33"=>array( "col-4", 33 ),
			"w50"=>array( "col-6", 50 ),
			"w66"=>array( "col-8", 66 ),
			"w75"=>array( "col-9", 75 ),
			"w100"=>array( "col-12", 100 )
		);

		extract( $args, EXTR_OVERWRITE );

		if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {

			$args = array(
				'field_id'  => 'tm-epo-field-'.$unit_counter
			);
			wc_get_template(
				'builder-start.php',
				$args ,
				$this->_namespace,
				$this->template_path
			);

			$_section_totals=0;

			foreach ( $field['sections'] as $section ) {
				if ( !isset( $section['sections_placement'] ) || $section['sections_placement']!=$where ) {
					continue;
				}
				if ( isset( $section['sections_size'] ) && isset( $columns[$section['sections_size']] ) ) {
					$size=$columns[$section['sections_size']][0];
				}else {
					$size="col-12";
				}

				$_section_totals=$_section_totals+$columns[$section['sections_size']][1];
				if ( $_section_totals>100 ) {
					$_section_totals=$columns[$section['sections_size']][1];
					echo '<div class="cpfclear"></div>';
				}

				$divider="";
				if ( isset( $section['divider_type'] ) ) {
					switch ( $section['divider_type'] ) {
					case "hr":
						$divider='<hr>';
						break;
					case "divider":
						$divider='<div class="tm_divider"></div>';
						break;
					case "padding":
						$divider='<div class="tm_padding"></div>';
						break;
					}
				}
				$label_size='h3';
				if ( !empty( $section['label_size'] )){
					switch($section['label_size']){
						case "1":
							$label_size='h1';
						break;
						case "2":
							$label_size='h2';
						break;
						case "3":
							$label_size='h3';
						break;
						case "4":
							$label_size='h4';
						break;
						case "5":
							$label_size='h5';
						break;
						case "6":
							$label_size='h6';
						break;
						case "7":
							$label_size='p';
						break;
						case "8":
							$label_size='div';
						break;
						case "9":
							$label_size='span';
						break;
					}
				}
				$args = array(
					'column' 			=> $size,
					'style' 			=> $section['sections_style'],
					'uniqid' 			=> $section['sections_uniqid'],
					'logic' 			=> esc_html(json_encode( (array) json_decode($section['sections_clogic']) ) ),
					'haslogic' 			=> $section['sections_logic'],
					'sections_class' 	=> $section['sections_class'],
					'sections_type' 	=> $section['sections_type'],
					'title_size'   		=> $label_size,
					'title'    			=> !empty( $section['label'] )?esc_html( wc_attribute_label( $section['label'] ) ):"",
					'title_color'   	=> !empty( $section['label_color'] )? $section['label_color'] :"",
					'description'   	=> !empty( $section['description'] )?  $section['description']  :"",
					'description_color' => !empty( $section['description_color'] )? $section['description_color'] :"",
					'description_position' => !empty( $section['description_position'] )? $section['description_position'] :"",
					'divider'    		=> $divider
							
				);
				wc_get_template(
					'builder-section-start.php',
					$args ,
					$this->_namespace,
					$this->template_path
				);

				if ( isset( $section['elements'] ) && is_array( $section['elements'] ) ) {
					$totals=0;
					foreach ( $section['elements'] as $element ) {

						$empty_rules="";
						if ( isset( $element['rules_filtered'] ) ) {
							$empty_rules=esc_html( json_encode( ( $element['rules_filtered'] ) ) );
						}
						$empty_rules_type="";
						if ( isset( $element['rules_type'] ) ) {
							$empty_rules_type=esc_html( json_encode( ( $element['rules_type'] ) ) );
						}
						if ( isset( $element['size'] ) && isset( $columns[$element['size']] ) ) {
							$size=$columns[$element['size']][0];
						}else {
							$size="col-12";
						}
						
						$fee_name=$this->fee_name;
						$cart_fee_name=$this->cart_fee_name;
						$totals=$totals+$columns[$element['size']][1];
						if ( $totals>100 ) {
							$totals=$columns[$element['size']][1];
							echo '<div class="cpfclear"></div>';
						}
						$divider="";
						if ( isset( $element['divider_type'] ) ) {
							$divider_class="";
							if ( $element['type']=='divider' && !empty( $element['class'] ) ) {
								$divider_class=" ".$element['class'];
							}
							switch ( $element['divider_type'] ) {
							case "hr":
								$divider='<hr'.$divider_class.'>';
								break;
							case "divider":
								$divider='<div class="tm_divider'.$divider_class.'"></div>';
								break;
							case "padding":
								$divider='<div class="tm_padding'.$divider_class.'"></div>';
								break;
							}
						}
						$label_size='h3';
						if ( !empty( $element['label_size'] )){
							switch($element['label_size']){
								case "1":
									$label_size='h1';
								break;
								case "2":
									$label_size='h2';
								break;
								case "3":
									$label_size='h3';
								break;
								case "4":
									$label_size='h4';
								break;
								case "5":
									$label_size='h5';
								break;
								case "6":
									$label_size='h6';
								break;
								case "7":
									$label_size='p';
								break;
								case "8":
									$label_size='div';
								break;
								case "9":
									$label_size='span';
								break;
							}
						}
						$args = array(
							'column'    		=> $size,
							'class'   			=> !empty( $element['class'] )? $element['class'] :"",
							'title_size'   		=> $label_size,
							'title'    			=> !empty( $element['label'] )?esc_html( wc_attribute_label( $element['label'] ) ):"",
							'title_color'   	=> !empty( $element['label_color'] )? $element['label_color'] :"",
							'description'   	=> !empty( $element['description'] )?  $element['description']  :"",
							'description_color' => !empty( $element['description_color'] )? $element['description_color'] :"",
							'description_position' => !empty( $element['description_position'] )? $element['description_position'] :"",
							'divider'    		=> $divider,
							'required'    		=> esc_html( wc_attribute_label( $element['required'] ) ),
							'type'        		=> $element['type'],
							'use_images'        => $element['use_images'],
							'use_url'        	=> $element['use_url'],
							'rules'       		=> $empty_rules,
							'rules_type' 		=> $empty_rules_type,
							'element'			=> $element['type'],
							'class_id'			=> "element_".$element_counter,
							'uniqid' 			=> $element['uniqid'],
							'logic' 			=> esc_html(json_encode( (array) json_decode($element['clogic']) ) ),
							'haslogic' 			=> $element['logic']
						);
						wc_get_template(
							'builder-element-start.php',
							$args ,
							$this->_namespace,
							$this->template_path
						);
						$field_counter=0;
						switch ( $element['type'] ) {
						case "header":
							
							break;
						case "divider":
							
							break;

						case "range":
							$name_inc ="range_".$element_counter.$form_prefix;
							$tabindex++;
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",								
								'rules'   		=> isset( $element['rules_filtered'] )?esc_html( json_encode( ( $element['rules_filtered'] ) ) ):'',
								'rules_type'   	=> isset( $element['rules_type'] )?esc_html( json_encode( ( $element['rules_type'] ) ) ):'',
								'id'    		=> 'tmcp_range_'.$tabindex.$form_prefix,
								'name'    		=> 'tmcp_'.$name_inc,
								'amount'     	=> '0 '.$_currency,
								'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'min'  			=> isset( $element['min'] )?$element['min']:"",
								'max'  			=> isset( $element['max'] )?$element['max']:"",
								'step' 			=> isset( $element['step'] )?$element['step']:"",
								'pips' 			=> isset( $element['pips'] )?$element['pips']:"",
								'tabindex'  	=> $tabindex,
								'fieldtype' 	=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
							);
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;

						case "upload":
							$name_inc ="upload_".$element_counter.$form_prefix;
							$tabindex++;
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}
							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",
								'max_size' 		=> size_format( wp_max_upload_size() ),
								'rules'   		=> isset( $element['rules_filtered'] )?esc_html( json_encode( ( $element['rules_filtered'] ) ) ):'',
								'rules_type'   	=> isset( $element['rules_type'] )?esc_html( json_encode( ( $element['rules_type'] ) ) ):'',
								'id'    		=> 'tmcp_upload_'.$tabindex.$form_prefix,
								'name'    		=> 'tmcp_'.$name_inc,
								'amount'     	=> '0 '.$_currency,
								'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'style' 		=> isset( $element['button_type'] )?$element['button_type']:"",
								'tabindex'  	=> $tabindex,
								'fieldtype' 	=> $is_fee?"tmcp-sub-fee-field":"tmcp-field"
							);
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;

						case "date":
							$name_inc ="date_".$element_counter.$form_prefix;
							$tabindex++;
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",
								'rules'   			=> isset( $element['rules_filtered'] )?esc_html( json_encode( ( $element['rules_filtered'] ) ) ):'',
								'rules_type'   		=> isset( $element['rules_type'] )?esc_html( json_encode( ( $element['rules_type'] ) ) ):'',
								'id'    			=> 'tmcp_date_'.$tabindex.$form_prefix,
								'name'    			=> 'tmcp_'.$name_inc,
								'amount'     		=> '0 '.$_currency,
								'textafterprice' 	=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  		=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'style' 			=> isset( $element['button_type'] )?$element['button_type']:"",
								'format' 			=> !empty( $element['format'] )?$element['format']:0,
								'start_year' 		=> !empty( $element['start_year'] )?$element['start_year']:"1900",
								'end_year' 			=> !empty( $element['end_year'] )?$element['end_year']:(date("Y")+10),
								'tranlation_day' 	=> !empty( $element['tranlation_day'] )?$element['tranlation_day']:"",
								'tranlation_month' 	=> !empty( $element['tranlation_month'] )?$element['tranlation_month']:"",
								'tranlation_year' 	=> !empty( $element['tranlation_year'] )?$element['tranlation_year']:"",
								'tabindex'  		=> $tabindex,
								'fieldtype' 		=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
							);
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;

						case "textarea":
							$name_inc ="textarea_".$element_counter.$form_prefix;
							$tabindex++;
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",
								'placeholder'  	=> isset( $element['placeholder'] )?esc_attr(  $element['placeholder']  ):'',
								'max_chars'  	=> isset( $element['max_chars'] )?absint( $element['max_chars'] ):'',
								'options'   	=> '',
								'rules'   		=> isset( $element['rules_filtered'] )?esc_html( json_encode( ( $element['rules_filtered'] ) ) ):'',
								'rules_type'   	=> isset( $element['rules_type'] )?esc_html( json_encode( ( $element['rules_type'] ) ) ):'',
								'id'    		=> 'tmcp_textarea_'.$tabindex.$form_prefix,
								'name'    		=> 'tmcp_'.$name_inc,
								'amount'     	=> '0 '.$_currency,
								'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'tabindex'  	=> $tabindex,
								'fieldtype' 	=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
							);
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;
						case "textfield":
							$name_inc ="textfield_".$element_counter.$form_prefix;
							$tabindex++;							
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}

							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",
								'placeholder'  	=> isset( $element['placeholder'] )?esc_attr(  $element['placeholder']  ):'',
								'max_chars'  	=> isset( $element['max_chars'] )?absint( $element['max_chars'] ):'',
								'options'   	=> '',
								'rules'   		=> isset( $element['rules_filtered'] )?esc_html( json_encode( ( $element['rules_filtered'] ) ) ):'',
								'rules_type' 	=> isset( $element['rules_type'] )?esc_html( json_encode( ( $element['rules_type'] ) ) ):'',
								'id'    		=> 'tmcp_textfield_'.$tabindex.$form_prefix,
								'name'    		=> 'tmcp_'.$name_inc,
								'amount'     	=> '0 '.$_currency,
								'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'tabindex'   	=> $tabindex,
								'fieldtype' 	=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
							);
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;
						case "select":
							$name_inc ="select_".$element_counter.$form_prefix;
							$tabindex++;
							$is_fee=(isset( $element['selectbox_fee'] ) && $element['selectbox_fee'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['selectbox_cart_fee'] ) && $element['selectbox_cart_fee'][0][0]=="fee");							
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$args = array(
								'class'   		=> !empty( $element['class'] )? $element['class'] :"",
								'options'   	=> '',
								'id'    		=> 'tmcp_select_'.$tabindex.$form_prefix,
								'name'    		=> 'tmcp_'.$name_inc,
								'amount'     	=> '0 '.$_currency,
								'use_url'		=> $element['use_url'],
								'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
								'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
								'tabindex'   	=> $tabindex,
								'fieldtype' 	=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
							);
							$_default_value_counter=0;
							if (!empty($element['placeholder'])){
								$args['options'] .='<option value="" data-price="" data-rules="" data-rulestype="">'.
													wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $element['placeholder'] ) ).'</option>';								
							}
							foreach ( $element['options'] as $value=>$label ) {								
								$default_value=isset( $element['default_value'] )
								?
								(($element['default_value']!="")
									?((int) $element['default_value'] == $_default_value_counter)
									:false)
								:false;
								$selected=false;
								
								if (isset($_POST['tmcp_'.$name_inc]) && esc_attr(stripcslashes($_POST['tmcp_'.$name_inc]))==esc_attr( ( $value ) ) ){
									$selected=true;
								}
								elseif (empty($_POST) && isset($default_value)){
									if ($default_value){
										$selected=true;
									}
								}
								$data_url=isset($element['url'][$_default_value_counter])?$element['url'][$_default_value_counter]:"";
								$args['options'] .='<option '.
									selected( $selected, true, 0 ).
									' value="'.esc_attr( $value ).'"'.
									(!empty($data_url)?' data-url="'.esc_attr($data_url).'"':'').
									' data-price="'.( isset( $element['rules_filtered'][$value][0] )?$element['rules_filtered'][$value][0]:0).'"'.
									' data-rules="'.( isset( $element['rules_filtered'][$value] )?esc_html( json_encode( ( $element['rules_filtered'][$value] ) ) ):'' ).'"'.
									' data-rulestype="'.( isset( $element['rules_type'][$value] )?esc_html( json_encode( ( $element['rules_type'][$value] ) ) ):'' ).'">'
									.wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $label ) ).'</option>';
								$_default_value_counter++;
							}
							
							wc_get_template(
								$element['type'].'.php',
								$args ,
								$this->_namespace,
								$this->template_path
							);
							$element_counter++;
							break;
						case "radio":
						case "checkbox":
							$items_per_row=$element['items_per_row'];
							$grid_break="";
							$_percent=100;
							$_columns=0;
							if (!empty($items_per_row)){
								
								if ($items_per_row=="auto"){
									$items_per_row=0;
									$css_string=".element_".$element_counter." li{float:left !important;width:auto !important;}";
								}else{
									$items_per_row=(float) $element['items_per_row'];
									$_percent=(float) (100/$items_per_row);
									$css_string=".element_".$element_counter." li{float:left !important;width:".$_percent."% !important;}";	
								}
								
								$css_string = str_replace(array("\r", "\n"), "", $css_string);								
								$this->inline_styles=$this->inline_styles.$css_string;
							}else{
								$items_per_row=(float) $element['items_per_row'];	
							}
							
							$_default_value_counter=0;
							foreach ( $element['options'] as $value=>$label ) {
								$tabindex++;
								
								$_columns++;
								$grid_break="";
								$default_value=false;

								if ( $element['type']=='radio' ) {
									$name_inc ="radio_".$element_counter.$form_prefix;
									$default_value=isset( $element['default_value'] )?(($element['default_value']!="")?((int) $element['default_value'] == $_default_value_counter):false):false;
								}
								if ( $element['type']=='checkbox' ) {
									$name_inc ="checkbox_".$element_counter."_".$field_counter.$form_prefix;
									$default_value=isset( $element['default_value'] )
									?
									((is_array($element['default_value']))
										?in_array((string)$_default_value_counter,$element['default_value'])
										:false)
									:false;
								}

								if ((float)$_columns>(float)$items_per_row && $items_per_row>0){
									$grid_break=" cpf_clear";
									$_columns=1;
								}
								$is_fee=(isset( $element['rules_type'][$value] ) && $element['rules_type'][$value][0]=="subscriptionfee");
								$is_cart_fee=(isset( $element['rules_type'][$value]) && $element['rules_type'][$value][0]=="fee");
								if ($is_fee){
									$name_inc = $fee_name.$name_inc;
								}elseif($is_cart_fee){
									$name_inc = $cart_fee_name.$name_inc;
								}
								$args = array(
									'class'   		=> !empty( $element['class'] )? $element['class'] :"",
									'label'   		=> wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $label ) ),
									'value'   		=> esc_attr( ( $value ) ),
									'rules'   		=> isset( $element['rules_filtered'][$value] )?esc_html( json_encode( ( $element['rules_filtered'][$value] ) ) ):'',
									'rules_type' 	=> isset( $element['rules_type'][$value] )?esc_html( json_encode( ( $element['rules_type'][$value] ) ) ):'',
									'id'    		=> 'tmcp_choice_'.$element_counter."_".$field_counter."_".$tabindex.$form_prefix,
									'name'    		=> 'tmcp_'.$name_inc,
									'amount'     	=> '0 '.$_currency,
									'textafterprice'=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
									'hide_amount'  	=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
									'use_images'	=> $element['use_images'],
									'use_url'		=> $element['use_url'],
									'tabindex'   	=> $tabindex,
									'grid_break'	=> $grid_break,
									'percent'		=> $_percent,
									'image'   		=> isset($element['images'][$field_counter])?$element['images'][$field_counter]:"",
									'url'   		=> isset($element['url'][$field_counter])?$element['url'][$field_counter]:"",
									'limit' 		=> empty( $element['limit'] )?"":$element['limit'],
									'swatchmode' 	=> empty( $element['swatchmode'] )?"":$element['swatchmode'],
									'tm_epo_no_lazy_load' => $this->tm_epo_no_lazy_load,
									'changes_product_image' => empty( $element['changes_product_image'] )?"":$element['changes_product_image'],
									'default_value' => $default_value,
									'fieldtype' 	=> $is_fee?$this->fee_name_class:($is_cart_fee?$this->cart_fee_class:"tmcp-field")
								);
								$_default_value_counter++;
								wc_get_template(
									$element['type'].'.php',
									$args ,
									$this->_namespace,
									$this->template_path
								);
								$field_counter++;
							}
							$element_counter++;
							break;
						}

						wc_get_template(
							'builder-element-end.php',
							array(
								'element' 				=> $element['type'],
								'description'   		=> !empty( $element['description'] )?  $element['description']  :"",
								'description_color' 	=> !empty( $element['description_color'] )? $element['description_color'] :"",
								'description_position' 	=> !empty( $element['description_position'] )? $element['description_position'] :"",
							) ,
							$this->_namespace,
							$this->template_path
						);

					}
				}
				$args = array(
					'column' 		=> $size,
					'style' 		=> $section['sections_style'],
					'sections_type' => $section['sections_type']
				);
				wc_get_template(
					'builder-section-end.php',
					$args ,
					$this->_namespace,
					$this->template_path
				);

			}

			wc_get_template(
				'builder-end.php',
				array() ,
				$this->_namespace,
				$this->template_path
			);

			$unit_counter++;

		}
		return array(
			'tabindex'   		=> $tabindex,
			'unit_counter'  	=> $unit_counter,
			'field_counter'  	=> $field_counter,
			'element_counter'  	=> $element_counter,
			'_currency'   		=> $_currency
		);

	}


	/**
	 * Handles the display of all the extra options on the product page.
	 */
	public function frontend_display($product_id=0, $form_prefix="") {
		global $product,$woocommerce;
		if ($woocommerce->product_factory===NULL || ($this->tm_options_have_been_displayed && !$this->is_bto)){
			return;// bad function call
		}
		$this->tm_options_have_been_displayed=true;
		$this->tm_epo_fields($product_id, $form_prefix);
		$this->tm_epo_totals($product_id, $form_prefix);
	}

	public function tm_epo_totals($product_id=0, $form_prefix="") {
		global $product,$woocommerce;		
		if ($woocommerce->product_factory===NULL || ($this->tm_options_totals_have_been_displayed && !$this->is_bto)){
			return;// bad function call
		}
		$this->tm_options_totals_have_been_displayed=true;
		$this->print_price_fields( $product_id, $form_prefix );		
	}

	public function tm_epo_fields($product_id=0, $form_prefix="") {
		global $woocommerce;
		if ($woocommerce->product_factory===NULL || ($this->tm_options_single_have_been_displayed && !$this->is_bto)){
			return;// bad function call
		}
		$this->tm_options_single_have_been_displayed=true;
		if (!$product_id){
			global $product;
			if ($product){
				$product_id=$product->id;
			}
		}else{
			$product=get_product($product_id);
		}
		if (!$product_id || empty($product) ){
			return;
		}
		
		$post_id=$product_id;

		if ($form_prefix){	
			$form_prefix="_".$form_prefix;
			echo '<input type="hidden" class="cpf-bto-id" name="cpf_bto_id[]" value="'.$form_prefix.'" />';
			echo '<input type="hidden" value="" name="cpf_bto_price[]" class="cpf-bto-price" />';
			echo '<input type="hidden" value="0" name="cpf_bto_optionsprice[]" class="cpf-bto-optionsprice" />';
		}
		if ($this->cpf && !$this->is_bto && $this->noactiondisplay){
			$cpf_price_array=$this->cpf;
		}else{
			$cpf_price_array=$this->get_product_tm_epos($post_id);
		}
		if (!$cpf_price_array){
			return;
		}
		$global_price_array = $cpf_price_array['global'];
		$local_price_array  = $cpf_price_array['local'];

		$global_prices=array( 'before'=>array(), 'after'=>array() );
		foreach ( $global_price_array as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				if (isset($field['sections']) && is_array($field['sections'])){
					foreach ( $field['sections'] as $section_id=>$section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[$section['sections_placement']][$priority][$pid]['sections'][$section_id]=$section;
						}
					}
				}
			}
		}


		$tabindex   		= 0;
		$_currency   		= get_woocommerce_currency_symbol();
		$unit_counter  		= 0;
		$field_counter  	= 0;
		$element_counter	= 0;

		wc_get_template(
			'start.php',
			array('form_prefix' => $form_prefix) ,
			$this->_namespace,
			$this->template_path
		);

		// global options before local
		foreach ( $global_prices['before'] as $priorities ) {
			foreach ( $priorities as $field ) {
				$args=array(
					'tabindex'   		=> $tabindex,
					'unit_counter'  	=> $unit_counter,
					'field_counter'  	=> $field_counter,
					'element_counter'  	=> $element_counter,
					'_currency'   		=> $_currency
				);
				$_return=$this->get_builder_display( $field, 'before', $args , $form_prefix);
				extract( $_return, EXTR_OVERWRITE );
			}
		}

		// local options
		if ( is_array( $local_price_array ) && sizeof( $local_price_array ) > 0 ) {

			$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

			if ( is_array( $attributes ) && count( $attributes )>0 ) {
				foreach ( $local_price_array as $field ) {
					if ( isset( $field['name'] ) && isset( $attributes[$field['name']] ) && !$attributes[$field['name']]['is_variation'] ) {

						$attribute=$attributes[$field['name']];

						$empty_rules="";
						if ( isset( $field['rules_filtered'][0] ) ) {
							$empty_rules=esc_html( json_encode( ( $field['rules_filtered'][0] ) ) );
						}
						$empty_rules_type="";
						if ( isset( $field['rules_type'][0] ) ) {
							$empty_rules_type=esc_html( json_encode( ( $field['rules_type'][0] ) ) );
						}

						$args = array(
							'title'  	=> ( !$attribute['is_taxonomy'] && isset($attributes[$field['name']]["name"]))?esc_html($attributes[$field['name']]["name"]):esc_html( wc_attribute_label( $field['name'] ) ),
							'required'  => esc_html( wc_attribute_label( $field['required'] ) ),
							'field_id'  => 'tm-epo-field-'.$unit_counter,
							'type'      => $field['type'],
							'rules'     => $empty_rules,
							'rules_type'     => $empty_rules_type
						);
						wc_get_template(
							'field-start.php',
							$args ,
							$this->_namespace,
							$this->template_path
						);

						$name_inc="";
						$field_counter=0;
						if ( $attribute['is_taxonomy'] ) {
						
							$all_terms = get_terms( $attribute['name'] , 'orderby=name&hide_empty=0' );
							
							switch ( $field['type'] ) {

							case "select":
								$name_inc ="select_".$element_counter;
								$tabindex++;

								$args = array(
									'options'   	=> '',
									'textafterprice' => '',
									'id'    		=> 'tmcp_select_'.$tabindex.$form_prefix,
									'name'    		=> 'tmcp_'.$name_inc.$form_prefix,
									'amount'     	=> '0 '.$_currency,
									'hide_amount'  	=> !empty( $field['hide_price'] )?" hidden":"",
									'tabindex'   	=> $tabindex
								);
								if ( $all_terms ) {
					                foreach ( $all_terms as $term ) {
					                    $has_term = has_term( (int) $term->term_id, $attribute['name'], $post_id ) ? 1 : 0;
					                    if ($has_term ){
					                    	$args['options'] .='<option '.( isset( $_POST['tmcp_'.$name_inc.$form_prefix] )?selected( $_POST['tmcp_'.$name_inc.$form_prefix], esc_attr( sanitize_title( $term->slug ) ), 0 ) :"" ).' value="'.sanitize_title( $term->slug ).'" data-price="" data-rules="'.( isset( $field['rules_filtered'][$term->slug] )?esc_html( json_encode( ( $field['rules_filtered'][$term->slug] ) ) ):'' ).'" data-rulestype="'.( isset( $field['rules_type'][$term->slug] )?esc_html( json_encode( ( $field['rules_type'][$term->slug] ) ) ):'' ).'">'.wptexturize( $term->name ).'</option>';					                        
					                    }
					                }
					            }
								
								wc_get_template(
									$field['type'].'.php',
									$args ,
									$this->_namespace,
									$this->template_path
								);
								$element_counter++;
								break;

							case "radio":
							case "checkbox":
								if ( $all_terms ) {
									foreach ( $all_terms as $term ) {
										$has_term = has_term( (int) $term->term_id, $attribute['name'], $post_id ) ? 1 : 0;
										if ($has_term ){
								
											$tabindex++;

											if ( $field['type']=='radio' ) {
												$name_inc ="radio_".$element_counter;
											}
											if ( $field['type']=='checkbox' ) {
												$name_inc ="checkbox_".$element_counter."_".$field_counter;
											}

											$args = array(
												'label'   		=> wptexturize( $term->name ),
												'textafterprice' => '',
												'value'   		=> sanitize_title( $term->slug ),
												'rules'   		=> isset( $field['rules_filtered'][$term->slug] )?esc_html( json_encode( ( $field['rules_filtered'][$term->slug] ) ) ):'',
												'rules_type' 	=> isset( $field['rules_type'][$term->slug] )?esc_html( json_encode( ( $field['rules_type'][$term->slug] ) ) ):'',
												'id'    		=> 'tmcp_choice_'.$element_counter."_".$field_counter."_".$tabindex.$form_prefix,
												'name'    		=> 'tmcp_'.$name_inc.$form_prefix,
												'amount'     	=> '0 '.$_currency,
												'hide_amount'  	=> !empty( $field['hide_price'] )?" hidden":"",
												'tabindex'   	=> $tabindex,
												'use_images'	=> "",
												'grid_break'	=> "",
												'percent'		=> "",
												'limit' 		=> empty( $field['limit'] )?"":$field['limit']
											);
											wc_get_template(
												$field['type'].'.php',
												$args ,
												$this->_namespace,
												$this->template_path
											);

											$field_counter++;
										}
					                }
					            }								

								$element_counter++;
								break;

							}
						} else {

							$options = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );

							switch ( $field['type'] ) {

							case "select":
								$name_inc ="select_".$element_counter;
								$tabindex++;

								$args = array(
									'options'   	=> '',
									'textafterprice' => '',
									'id'    		=> 'tmcp_select_'.$tabindex.$form_prefix,
									'name'    		=> 'tmcp_'.$name_inc.$form_prefix,
									'amount'     	=> '0 '.$_currency,
									'hide_amount'  	=> !empty( $field['hide_price'] )?" hidden":"",
									'tabindex'   	=> $tabindex
								);
								foreach ( $options as $option ) {
									$args['options'] .='<option '.( isset( $_POST['tmcp_'.$name_inc.$form_prefix] )?selected( $_POST['tmcp_'.$name_inc.$form_prefix], esc_attr( sanitize_title( $option ) ), 0 ) :"" ).' value="'.esc_attr( sanitize_title( $option ) ).'" data-price="" data-rules="'.( isset( $field['rules_filtered'][esc_attr( sanitize_title( $option ) )] )?esc_html( json_encode( ( $field['rules_filtered'][esc_attr( sanitize_title( $option ) )] ) ) ):'' ).'" data-rulestype="'.( isset( $field['rules_type'][esc_attr( sanitize_title( $option ) )] )?esc_html( json_encode( ( $field['rules_type'][esc_attr( sanitize_title( $option ) )] ) ) ):'' ).'">'.wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $option ) ).'</option>';
								}
								wc_get_template(
									$field['type'].'.php',
									$args ,
									$this->_namespace,
									$this->template_path
								);
								$element_counter++;
								break;

							case "radio":
							case "checkbox":
								foreach ( $options as $option ) {
									$tabindex++;

									if ( $field['type']=='radio' ) {
										$name_inc ="radio_".$element_counter;
									}
									if ( $field['type']=='checkbox' ) {
										$name_inc ="checkbox_".$element_counter."_".$field_counter;
									}

									$args = array(
										'label'   		=> wptexturize( apply_filters( 'woocommerce_tm_epo_option_name', $option ) ),
										'textafterprice' => '',
										'value'   		=> esc_attr( sanitize_title( $option ) ),
										'rules'   		=> isset( $field['rules_filtered'][sanitize_title( $option )] )?esc_html( json_encode( ( $field['rules_filtered'][sanitize_title( $option )] ) ) ):'',
										'rules_type' 	=> isset( $field['rules_type'][sanitize_title( $option )] )?esc_html( json_encode( ( $field['rules_type'][sanitize_title( $option )] ) ) ):'',
										'id'    		=> 'tmcp_choice_'.$element_counter."_".$field_counter."_".$tabindex.$form_prefix,
										'name'    		=> 'tmcp_'.$name_inc.$form_prefix,
										'amount'     	=> '0 '.$_currency,
										'hide_amount'  	=> !empty( $field['hide_price'] )?" hidden":"",
										'tabindex'   	=> $tabindex,
										'use_images'	=> "",
										'grid_break'	=> "",
										'percent'		=> "",
										'limit' 		=> empty( $field['limit'] )?"":$field['limit']
									);
									wc_get_template(
										$field['type'].'.php',
										$args ,
										$this->_namespace,
										$this->template_path
									);
									$field_counter++;
								}
								$element_counter++;
								break;

							}
						}

						wc_get_template(
							'field-end.php',
							array() ,
							$this->_namespace,
							$this->template_path
						);

						$unit_counter++;
					}
				}
			}
		}

		// global options after local
		foreach ( $global_prices['after'] as $priorities ) {
			foreach ( $priorities as $field ) {
				$args=array(
					'tabindex'   		=> $tabindex,
					'unit_counter'  	=> $unit_counter,
					'field_counter'  	=> $field_counter,
					'element_counter'  	=> $element_counter,
					'_currency'   		=> $_currency
				);
				$_return=$this->get_builder_display( $field, 'after', $args, $form_prefix );
				extract( $_return, EXTR_OVERWRITE );
			}
		}

		wc_get_template(
			'end.php',
			array() ,
			$this->_namespace,
			$this->template_path
		);

		$this->tm_add_inline_style();
		

	}

	public function frontend_scripts() {
		global $product;
		if ( (class_exists( 'WC_Quick_View' ) && (is_shop() || is_product_category())) || is_product() || is_cart() || is_checkout() || is_order_received_page() ) {
			$this->custom_frontend_scripts();	
		}else{
			return;
		}		
	}

	public function custom_frontend_scripts() {	
		$product = get_product();

        wp_enqueue_style( 'tm-font-awesome', $this->plugin_url .'/external/font-awesome/css/font-awesome.min.css', false, '4.1', 'screen' );
        wp_enqueue_style( 'tm-epo-animate-css', $this->plugin_url  . '/assets/css/animate.css' );
		wp_enqueue_style( 'tm-epo-css', $this->plugin_url . '/assets/css/tm-epo.css' );

		wp_register_script( 'tm-accounting', $this->plugin_url . '/assets/js/accounting.min.js', '', '0.3.2', true );
		wp_register_script( 'tm-modernizr', $this->plugin_url. '/assets/js/modernizr.js', '', '2.8.2' );
		wp_register_script( 'tm-scripts', $this->plugin_url . '/assets/js/tm-scripts.js', '', '1.0', true );
		
		wp_enqueue_script( 'tm-epo', $this->plugin_url. '/assets/js/tm-epo.js', array( 'jquery', 'jquery-ui-datepicker', 'tm-accounting', 'tm-modernizr', 'tm-scripts' ), $this->version, true );

		$extra_fee=0;
		global $wp_locale;
		$args = array(
			'extra_fee' 					=> apply_filters( 'woocommerce_tm_final_price_extra_fee', $extra_fee,$product ),
			'tm_epo_final_total_box' 		=> (empty($this->tm_meta_cpf['override_final_total_box']))?$this->tm_epo_final_total_box:$this->tm_meta_cpf['override_final_total_box'],
			'i18n_extra_fee'           		=> __( 'Extra fee', TM_EPO_TRANSLATION ),
			'i18n_options_total'           	=> (!empty($this->tm_epo_options_total_text))?$this->tm_epo_options_total_text:__( 'Options amount', TM_EPO_TRANSLATION ),
			'i18n_final_total'             	=> (!empty($this->tm_epo_final_total_text))?$this->tm_epo_final_total_text:__( 'Final total', TM_EPO_TRANSLATION ),
			'i18n_sign_up_fee' 				=> (!empty($this->tm_epo_subscription_fee_text))?$this->tm_epo_subscription_fee_text:__( 'Sign up fee', TM_EPO_TRANSLATION ),
			'i18n_cancel'					=> __( 'Cancel', TM_EPO_TRANSLATION ),
			'i18n_close'					=> __( 'Close', TM_EPO_TRANSLATION ),
			'i18n_addition_options'			=> __( 'Additional Options', TM_EPO_TRANSLATION ),
			'currency_format_num_decimals' 	=> absint( get_option( 'woocommerce_price_num_decimals' ) ),
			'currency_format_symbol'       	=> get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'  	=> esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep' 	=> esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format'              	=> esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
			'css_styles' 					=> $this->tm_epo_css_styles,
			'css_styles_style' 				=> $this->tm_epo_css_styles_style,
			'tm_epo_options_placement' 		=> $this->tm_epo_options_placement,
			'tm_epo_totals_box_placement' 	=> $this->tm_epo_totals_box_placement,
			'tm_epo_no_lazy_load' 			=> $this->tm_epo_no_lazy_load,

			'monthNames'        			=> $this->strip_array_indices( $wp_locale->month ),
			'monthNamesShort'   			=> $this->strip_array_indices( $wp_locale->month_abbrev ),
			'dayNames' 						=> $this->strip_array_indices( $wp_locale->weekday ),
 			'dayNamesShort' 				=> $this->strip_array_indices( $wp_locale->weekday_abbrev ),
			'dayNamesMin' 					=> $this->strip_array_indices( $wp_locale->weekday_initial ),
 			'isRTL'             			=> $wp_locale->text_direction=='rtl',

		);
		wp_localize_script( 'tm-epo', 'tm_epo_js', $args );
	}

	/**
	 * Format array for the datepicker
	 *
	 * WordPress stores the locale information in an array with a alphanumeric index, and
	 * the datepicker wants a numerical index. This function replaces the index with a number
	*/
	private function strip_array_indices( $ArrayToStrip ) {
		foreach( $ArrayToStrip as $objArrayItem) {
			$NewArray[] = $objArrayItem;
		}
		return( $NewArray );
	}

	private function print_price_fields( $product_id=0, $form_prefix="") {		
		if (!$product_id){
			global $product;
			if ($product){
				$product_id=$product->id;
			}
		}else{
			$product=get_product($product_id);
		}
		if (!$product_id || empty($product) ){
			return;
		}

		if ($form_prefix){	
			$form_prefix="_".$form_prefix;
		}

		if (class_exists('WC_Dynamic_Pricing')){
			$id = isset($product->variation_id) ? $product->variation_id : $product->id;
			$dp=WC_Dynamic_Pricing::instance();
			if ($dp && 
				is_object($dp) && property_exists($dp, "discounted_products") 
				&& isset($dp->discounted_products[$id]) ){
				$price= $dp->discounted_products[$id];
			}else{
				$price=$product->get_price();
			}			
		}else{
			$price=$product->get_price();
		}
		$price=apply_filters( 'woocommerce_tm_epo_price', $price,"");
		$variations = array();
		$variations_subscription_period = array();
		$variations_subscription_sign_up_fee = array();
		foreach ( $product->get_children() as $child_id ) {

			$variation = $product->get_child( $child_id );
			if ( ! $variation->exists() ){
				continue;
			}
			$variations[$child_id] = $variation->get_price();
			$variations_subscription_period[$child_id] = $variation->subscription_period;
			$variations_subscription_sign_up_fee[$child_id] = $variation->subscription_sign_up_fee;
		}

		$is_subscription=false;
		$subscription_period='';
		$subscription_sign_up_fee=0;
		if (class_exists('WC_Subscriptions_Product')){
			if (WC_Subscriptions_Product::is_subscription( $product )){
				$is_subscription=true;
				$subscription_period = WC_Subscriptions_Product::get_period( $product );
				$subscription_sign_up_fee= WC_Subscriptions_Product::get_sign_up_fee( $product );
			}
		}
		wc_get_template(
			'totals.php',
			array(
				'variations' 			=> esc_html(json_encode( (array) $variations ) ),
				'variations_subscription_period' => esc_html(json_encode( (array) $variations_subscription_period ) ),
				'variations_subscription_sign_up_fee' => esc_html(json_encode( (array) $variations_subscription_sign_up_fee ) ),
				'subscription_period' 	=> $subscription_period,
				'subscription_sign_up_fee' 	=> $subscription_sign_up_fee,
				'is_subscription' 		=> $is_subscription,
				'is_sold_individually' 	=> $product->is_sold_individually(),
				'hidden' 				=> ($this->tm_meta_cpf['override_final_total_box'])?(($this->tm_epo_final_total_box=='hide')?' hidden':''):(($this->tm_meta_cpf['override_final_total_box']=='hide')?' hidden':''),
				'form_prefix' 			=> $form_prefix,
				'type'  				=> esc_html( $product->product_type ),
				'price' 				=> esc_html( ( is_object( $product ) ? apply_filters( 'woocommerce_tm_final_price', $price,$product ) : '' ) )
			) ,
			$this->_namespace,
			$this->template_path
		);
	}

	private function tm_add_inline_style(){
		if (!empty($this->inline_styles)){
			echo '<style type="text/css">';
			echo $this->inline_styles;
			echo '</style>';
		}
	}

	public function upload_file($file) {
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
		include_once( ABSPATH . 'wp-admin/includes/media.php' );
		add_filter( 'upload_dir',  array( $this, 'upload_dir_trick' ) );
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		remove_filter( 'upload_dir',  array( $this, 'upload_dir_trick' ) );
		return $upload;
	}

	public function upload_dir_trick( $param ) {
		global $woocommerce;
		$dir="/extra_product_options/";
		$unique_dir=md5( $woocommerce->session->get_customer_id() );
		if ( empty( $param['subdir'] ) ) {
			$param['path']   = $param['path'] . $dir . $unique_dir;
			$param['url']    = $param['url']. $dir . $unique_dir;
			$param['subdir'] = $dir . $unique_dir;
		} else {
			$subdir             = $dir . $unique_dir;
			$param['path']   = str_replace( $param['subdir'], $subdir, $param['path'] );
			$param['url']    = str_replace( $param['subdir'], $subdir, $param['url'] );
			$param['subdir'] = str_replace( $param['subdir'], $subdir, $param['subdir'] );
		}
		return $param;
	}	




	/* APPEND name_inc functions (required for condition logic to check if an element is visible) */
	public function tm_fill_element_names($post_id=0, $global_epos=array(), $product_epos=array(), $form_prefix="") {
		$global_price_array = $global_epos;
		$local_price_array  = $product_epos;

		$global_prices=array( 'before'=>array(), 'after'=>array() );
		foreach ( $global_price_array as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				if (isset($field['sections']) && is_array($field['sections'])){
					foreach ( $field['sections'] as $section_id=>$section ) {
						if ( isset( $section['sections_placement'] ) ) {
							$global_prices[$section['sections_placement']][$priority][$pid]['sections'][$section_id]=$section;
						}
					}
				}
			}
		}
		$unit_counter  		= 0;
		$field_counter  	= 0;
		$element_counter	= 0;
		// global options before local
		foreach ( $global_prices['before'] as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				$args=array(
					'priority'  		=> $priority,
					'pid'  				=> $pid,
					'unit_counter'  	=> $unit_counter,
					'field_counter'  	=> $field_counter,
					'element_counter'  	=> $element_counter
				);
				$_return=$this->fill_builder_display( $global_epos, $field, 'before', $args , $form_prefix);
				extract( $_return, EXTR_OVERWRITE );
			}
		}
		// local options
		if ( is_array( $local_price_array ) && sizeof( $local_price_array ) > 0 ) {
			$attributes = maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );
			if ( is_array( $attributes ) && count( $attributes )>0 ) {
				foreach ( $local_price_array as $field ) {
					if ( isset( $field['name'] ) && isset( $attributes[$field['name']] ) && !$attributes[$field['name']]['is_variation'] ) {
						$attribute=$attributes[$field['name']];
						$name_inc="";
						$field_counter=0;
						if ( $attribute['is_taxonomy'] ) {													
							switch ( $field['type'] ) {
							case "select":								
								$element_counter++;
								break;
							case "radio":
							case "checkbox":					
								$element_counter++;
								break;
							}
						} else {
							switch ( $field['type'] ) {
							case "select":
								$element_counter++;
								break;
							case "radio":
							case "checkbox":
								$element_counter++;
								break;
							}
						}
						$unit_counter++;
					}
				}
			}
		}
		// global options after local
		foreach ( $global_prices['after'] as $priority=>$priorities ) {
			foreach ( $priorities as $pid=>$field ) {
				$args=array(
					'priority'  		=> $priority,
					'pid'  				=> $pid,
					'unit_counter'  	=> $unit_counter,
					'field_counter'  	=> $field_counter,
					'element_counter'  	=> $element_counter
				);
				$_return=$this->fill_builder_display( $global_epos, $field, 'after', $args, $form_prefix );
				extract( $_return, EXTR_OVERWRITE );
			}
		}
		return $global_epos;
	}

	public function fill_builder_display( $global_epos, $field, $where, $args, $form_prefix="" ) {
		/* $form_prefix	shoud be passed with _ if not empty */			
		extract( $args, EXTR_OVERWRITE );
		if ( isset( $field['sections'] ) && is_array( $field['sections'] ) ) {
			foreach ( $field['sections'] as $_s => $section ) {
				if ( !isset( $section['sections_placement'] ) || $section['sections_placement']!=$where ) {
					continue;
				}
				if ( isset( $section['elements'] ) && is_array( $section['elements'] ) ) {
					foreach ( $section['elements'] as $arr_element_counter=>$element ) {
						$fee_name=$this->fee_name;
						$cart_fee_name=$this->cart_fee_name;
						$field_counter=0;
						switch ( $element['type'] ) {
						case "header":						
							break;
						case "divider":							
							break;
						case "range":
							$name_inc ="range_".$element_counter.$form_prefix;
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;
							$element_counter++;
							break;
						case "upload":
							$name_inc ="upload_".$element_counter.$form_prefix;							
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$name_inc = 'tmcp_'.$name_inc.$form_prefix;
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;
							$element_counter++;
							break;
						case "date":
							$name_inc ="date_".$element_counter.$form_prefix;							
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$name_inc = 'tmcp_'.$name_inc.$form_prefix;
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;							
							$element_counter++;
							break;
						case "textarea":
							$name_inc ="textarea_".$element_counter.$form_prefix;
							
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$name_inc = 'tmcp_'.$name_inc.$form_prefix;
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;							
							$element_counter++;
							break;
						case "textfield":
							$name_inc ="textfield_".$element_counter.$form_prefix;
													
							$is_fee=(isset( $element['rules_type'] ) && $element['rules_type'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$name_inc = 'tmcp_'.$name_inc.$form_prefix;
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;
							$element_counter++;
							break;
						case "select":
							$name_inc ="select_".$element_counter.$form_prefix;
							
							$is_fee=(isset( $element['selectbox_fee'] ) && $element['selectbox_fee'][0][0]=="subscriptionfee");
							$is_cart_fee=(isset( $element['rules_type'] ) && isset($element['rules_type'][0]) && isset($element['rules_type'][0][0]) && $element['rules_type'][0][0]=="fee");
							if ($is_fee){
								$name_inc = $fee_name.$name_inc;
							}elseif ($is_cart_fee){
								$name_inc = $cart_fee_name.$name_inc;
							}
							$name_inc = 'tmcp_'.$name_inc.$form_prefix;
							$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc']=$name_inc;							
							$element_counter++;
							break;
						case "radio":
						case "checkbox":							
							foreach ( $element['options'] as $value=>$label ) {							
								if ( $element['type']=='radio' ) {
									$name_inc ="radio_".$element_counter.$form_prefix;
								}
								if ( $element['type']=='checkbox' ) {
									$name_inc ="checkbox_".$element_counter."_".$field_counter.$form_prefix;
								}
								$is_fee=(isset( $element['rules_type'][$value] ) && $element['rules_type'][$value][0]=="subscriptionfee");
								$is_cart_fee=(isset( $element['rules_type'][$value] ) && $element['rules_type'][$value][0]=="fee");
								if ($is_fee){
									$name_inc = $fee_name.$name_inc;
								}elseif($is_cart_fee){
									$name_inc = $cart_fee_name.$name_inc;
								}
								$name_inc = 'tmcp_'.$name_inc.$form_prefix;
								$global_epos[$priority][$pid]['sections'][$_s]['elements'][$arr_element_counter]['name_inc'][]=$name_inc;
								$field_counter++;
							}
							$element_counter++;
							break;
						}						
					}
				}				
			}
			$unit_counter++;
		}
		return array(
			'global_epos' 		=> $global_epos,
			'unit_counter'  	=> $unit_counter,
			'field_counter'  	=> $field_counter,
			'element_counter'  	=> $element_counter
		);

	}

}
?>
