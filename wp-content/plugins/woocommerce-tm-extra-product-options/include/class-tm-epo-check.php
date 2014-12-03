<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}

class EPO_CHECK {
    function __construct() {
        add_action( 'admin_init', array( $this, 'check_version' ) );

        if ( ! self::compatible_version() ) {
            return;
        }
    }

    function check_version() {
        if ( ! self::compatible_version() ) {
            if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
                deactivate_plugins( plugin_basename( __FILE__ ) );
                add_action( 'admin_notices', array( $this, 'disabled_notice' ) );
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] );
                }
            }
        }
        if ( self::old_version() ) {
            deactivate_plugins( 'woocommerce-tm-custom-price-fields/tm-woo-custom-prices.php' );
            add_action( 'admin_notices', array( $this, 'deprecated_notice' ) );
        }
        if ( ! self::woocommerce_check() ) {
            add_action( 'admin_notices', array( $this, 'disabled_notice_woocommerce_check' ) );
        }

    }

    function disabled_notice_woocommerce_check() {
        $message = '<strong>Important:</strong> TM WooCommerce Extra Product Options requires WooCommerce 2.1 or later.';
        echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";       
    }

    function deprecated_notice() {
        $active_plugins = apply_filters( 'active_plugins', get_option('active_plugins' ) );
            
            if ( in_array( 'woocommerce-tm-custom-price-fields/tm-woo-custom-prices.php', $active_plugins ) ){
                $deactivate_url = 'plugins.php?action=deactivate&plugin=' . urlencode( 'woocommerce-tm-custom-price-fields/tm-woo-custom-prices.php' ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( 'deactivate-plugin_woocommerce-tm-custom-price-fields/tm-woo-custom-prices.php' ) );
                $message = '<strong>Important:</strong> It is highly recommended that you <a href="' . esc_url( admin_url( $deactivate_url ) ) . '">deactivate the old Custom Price Fields</a> plugin.';
                echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";
            }else{
                $delete_url = 'plugins.php?action=delete-selected&checked%5B0%5D=' . urlencode( 'woocommerce-tm-custom-price-fields/tm-woo-custom-prices.php' ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( 'bulk-plugins' ) );
                $message = '<strong>Important:</strong> It is highly recommended that you <a href="' . esc_url( admin_url( $delete_url ) ) . '">delete the old Custom Price Fields</a> plugin.';
                echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";
            }       
    }

    function disabled_notice() {
        $message = '<strong>Important:</strong> TM WooCommerce Extra Product Options requires WordPress 3.5 or later.';
        echo '<div class="error fade"><p>' . $message . '</p></div>' . "\n";       
    } 

    public function stop_plugin(){
        if ( ! self::compatible_version() ) {
            return true;
        }
        if ( self::old_version() ) {
            return true;
        }
        if ( ! self::woocommerce_check() ) {
            return true;
        }

        return false;
    }

    static function activation_check() {
        if ( ! self::compatible_version() ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'TM WooCommerce Extra Product Options requires WordPress 3.5 or later.', TM_EPO_TRANSLATION ) );
        }
    }

    static function compatible_version() {
        if ( version_compare( $GLOBALS['wp_version'], '3.5', '<' ) ) {
             return false;
         }

        return true;
    }

    static function old_version() {
        if (  class_exists( 'TM_Custom_Prices' )  )  {
             return true;
         }

        return false;
    }
    
    static function woocommerce_check() {
        if ( tm_woocommerce_check() && !version_compare( get_option( 'woocommerce_db_version' ), '2.1', '<' ) )  {
             return true;
         }

        return false;
    }

}

?>