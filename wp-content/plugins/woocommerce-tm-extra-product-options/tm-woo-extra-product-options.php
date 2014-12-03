<?php
/*
Plugin Name: TM WooCommerce Extra Product Options
Plugin URI: http://epo.themecomplete.com/
Description: A WooCommerce plugin for adding extra product options.
Version: 2.5.5
Author: themecomplete
Author URI: http://themecomplete.com/
*/

define ( 'TM_EPO_PLUGIN_SECURITY', 1 );
define ( 'TM_EPO_TRANSLATION', 'tm-extra-product-options' );
define ( 'TM_EPO_LOCAL_POST_TYPE', "tm_product_cp" );
define ( 'TM_EPO_GLOBAL_POST_TYPE', "tm_global_cp" );
define ( 'TM_EPO_VERSION', "2.5.5" );
define ( 'TM_plugin_path', untrailingslashit( plugin_dir_path(  __FILE__ ) ) );
define ( 'TM_template_path', TM_plugin_path.'/templates/');
define ( 'TM_plugin_url', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define ( 'TM_PLUGIN_NAME_HOOK', plugin_basename(__FILE__) );
define ( 'TM_ADMIN_SETTINGS_ID', 'tm_extra_product_options' );
define ( 'TM_PLUGIN_SLUG', basename( dirname( __FILE__ ) ).'/'.basename( __FILE__ ));
define ( 'TM_PLUGIN_ID', '7908619' );

/**
 * Load optional config
 */
include_once ( TM_plugin_path.'/config.php' );

/**
 * Load help functions
 */
require_once ( TM_plugin_path.'/include/tm-functions.php' );

/**
 * Load HTML functions
 */
require_once ( TM_plugin_path.'/include/class-tm-epo-html.php' );

/**
 * Load HTML functions
 */
require_once ( TM_plugin_path.'/include/class-tm-epo-update.php' );

/**
 * Load plugin health check
 */
require_once ( TM_plugin_path.'/include/class-tm-epo-check.php' );

global $epo_check;

$epo_check = new EPO_CHECK();

register_activation_hook( __FILE__, array( 'EPO_CHECK', 'activation_check' ) );

if ($epo_check->stop_plugin()){
    return;
}

if ( tm_woocommerce_check() ) {

    /**
     * Load plugin textdomain.
     */
    function tm_epo_load_textdomain() {
        load_plugin_textdomain( 'tm-extra-product-options', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
    }
    add_action( 'plugins_loaded', 'tm_epo_load_textdomain' );    

    /**
     *  Register post type
     */
    function tm_epo_register_post_type(){
        register_post_type( TM_EPO_LOCAL_POST_TYPE,
            array(
                'labels'                => array(
                    'name' => _x( 'TM Extra Product Options', 'post type general name' , TM_EPO_TRANSLATION)
                ),
                'publicly_queryable'    => false,
                'exclude_from_search'   => true,
                'rewrite'               => false,
                'show_in_nav_menus'     => false,
                'public'                => false,
                'hierarchical'          => false,
                'supports'              => false
            )
        );
        register_post_type( TM_EPO_GLOBAL_POST_TYPE,
            array(
                'show_ui'               => false,
                'capability_type'       => 'product',
                'map_meta_cap'          => true,
                'publicly_queryable'    => false,
                'exclude_from_search'   => true,
                'rewrite'               => false,
                'query_var'             => false,
                'show_in_nav_menus'     => false,
                'labels'                => array(
                    'name'          => _x( 'TM Global Product Options', 'post type general name' , TM_EPO_TRANSLATION),
                    'edit_item'     => __( 'Edit Global Product Options Form', TM_EPO_TRANSLATION ),
                    'search_items'  => __( 'Search Forms', TM_EPO_TRANSLATION ),
                ),
                'public'                => false,
                'hierarchical'          => false,
                'supports'              => array( 'title', 'excerpt' )
            )
        );
        register_taxonomy_for_object_type( 'product_cat', TM_EPO_GLOBAL_POST_TYPE );
    }
    add_action( 'init', 'tm_epo_register_post_type' );    

    /**
     * Load admin interface
     */
    if ( is_admin() ) {

        /* Settings Page */
        function tm_add_Extra_Product_Options_settings($settings){            
            $_setting = include( untrailingslashit( plugin_dir_path(  __FILE__  ) ).'/admin/class-tm-epo-settings.php'  );
            if ( $_setting instanceof WC_Settings_Page ) {
                $settings[] = $_setting;
            }
            return $settings;
        }
        add_filter( 'woocommerce_get_settings_pages', 'tm_add_Extra_Product_Options_settings' );
        /* woocommerce_bundle_rate_shipping chosen fix by removing */
        add_action('admin_enqueue_scripts',  'fix_woocommerce_bundle_rate_shipping_scripts'  ,99);
        function fix_woocommerce_bundle_rate_shipping_scripts(){
            wp_dequeue_script( 'woocommerce_bundle_rate_shipping_admin_js');
        }

        global $_TM_Global_Extra_Product_Options;
        include_once ( TM_plugin_path.'/admin/tm-global-epo-admin.php' );        
        $_TM_Global_Extra_Product_Options = new TM_Global_EPO_Admin();

        include_once ( TM_plugin_path.'/admin/tm-epo-admin.php' );
        $_TM_Extra_Product_Options_Admin = new TM_EPO_Admin();
        
        include_once ( TM_plugin_path.'/admin/class-tm-epo-builder.php' );

    }

    /**
     * Load main plugin interface
     */
    global $_TM_Extra_Product_Options;
    include_once ( TM_plugin_path.'/include/class-tm-extra-product-options.php' );    
    $_TM_Extra_Product_Options = new TM_Extra_Product_Options();
}

?>