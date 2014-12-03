<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}

if (!function_exists('tm_woocommerce_check')){
	function tm_woocommerce_check(){
	    $active_plugins = (array) get_option( 'active_plugins', array() );
	    if ( is_multisite() ){
		   $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	    }
	    return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
	}
}

if (!function_exists('tm_woocommerce_subscriptions_check')){
	function tm_woocommerce_subscriptions_check(){
	    $active_plugins = (array) get_option( 'active_plugins', array() );
	    if ( is_multisite() ){
		   $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	    }
	    return in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins ) || array_key_exists( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins );
	}
}

/* Check for require json function for PHP 4 & 5.1 */
if (!function_exists('json_decode')) {
	include_once ('json/JSON.php');
	function json_encode($data) { $json = new Services_JSON(); return( $json->encode($data) ); }
	function json_decode($data) { $json = new Services_JSON(); return( $json->decode($data) ); }
}

/* Check for require json function for PHP 4 & 5.1 */
if (!function_exists('tm_get_roles')) {
	function tm_get_roles(){
		$result = array();
		$result["@everyone"] = __('Everyone',TM_EPO_TRANSLATION);
		$result["@loggedin"] = __('Logged in users',TM_EPO_TRANSLATION);
		global $wp_roles;
		if (empty($wp_roles)){
			$all_roles = new WP_Roles();	
		}else{
			$all_roles=$wp_roles;
		}
		$roles = $all_roles->roles;		
		if ($roles) {
			foreach ($roles as $role => $details) {
				$name = translate_user_role($details['name']);
				$result[$role] = $name;
			}
		}
		return $result;
	}
}

?>