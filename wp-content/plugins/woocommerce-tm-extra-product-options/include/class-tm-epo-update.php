<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}



class TM_Licenser {

    function __construct() {
        add_action( 'wp_ajax_tm_activate_license', array( $this, 'activate' ) );
        add_action( 'wp_ajax_tm_deactivate_license', array( $this, 'deactivate' ) );
    }

    public static function api_url( $array ) {
        $array1 = array(
            'http://themecomplete.com/api/activation/',
        );
        
        return implode( '', array_merge( $array1, $array ) );
    }

    private function get_ajax_var($param, $default = null){
        return isset( $_POST[$param] ) ? $_POST[$param] : $default;
    }
    
    public function get_license(){
        return get_option( 'tm_license_activation_key');
    }

    public function check_license(){
        $a1=$this->get_license();
        $a2=get_option( 'tm_epo_envato_username');
        $a3=get_option( 'tm_epo_envato_apikey');
        $a4=get_option( 'tm_epo_envato_purchasecode');
        return (
            !empty($a1) &&
            !empty($a2) &&
            !empty($a3) &&
            !empty($a4) 
            );
    }

    public function activate() {
        $this->request('activation');
    }

    public function deactivate() {
        $this->request('deactivation');
    }

    public function request($action='') {
        check_ajax_referer( 'settings-nonce', 'security' );
        $params = array();
        $params['username'] = $this->get_ajax_var( 'username' );
        $params['key'] = $this->get_ajax_var( 'key' );
        $params['api_key'] = $this->get_ajax_var( 'api_key' );
        $params['url'] = get_site_url();
        $params['plugin'] = TM_PLUGIN_ID;
        $params['ip'] = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : '';
        $params['license'] = $this->get_license();
        $params['action'] = $action;
        $string = 'activation.php?';
        $message_wrap='<div class="%s"><p>%s</p></div>';
        
        $request_url = self::api_url( array( $string, http_build_query( $params, '', '&' ) ) );        
        $response = wp_remote_get( $request_url, array( 'timeout' => 300 ) );        

        if ( is_wp_error( $response ) ) {
            echo json_encode( array( 'result' => 'wp_error', 'message'=>sprintf($message_wrap, 'error', __('Error connecting to server!',TM_EPO_TRANSLATION)) ) );
            die();
        }

        $result = json_decode( $response['body'] );
        
        if ( ! is_object( $result ) ) {
            echo json_encode( array( 'result' => 'server_error', 'message'=> sprintf($message_wrap, 'error', __('Error getting data from the server!',TM_EPO_TRANSLATION)) ) );
            die();
        }

        if ( (boolean)$result->result === true && $result->key && $result->code && $result->code=="200") {            
            $license=$result->key;
            if ($action=='activation'){
                update_option( 'tm_epo_envato_username', $params['username'] );
                update_option( 'tm_epo_envato_apikey', $params['api_key'] );
                update_option( 'tm_epo_envato_purchasecode', $params['key'] );
                
                update_option( 'tm_license_activation_key', $license );

                delete_site_transient( 'update_plugins' );
                
                echo json_encode( array( 'result' => '4', 'message'=>sprintf($message_wrap, 'updated', __('License activated!',TM_EPO_TRANSLATION)) ) );
            }elseif ($action=='deactivation'){
                delete_option( 'tm_license_activation_key' );
                delete_site_transient( 'update_plugins' );
                echo json_encode( array( 'result' => '4', 'message'=>sprintf($message_wrap, 'updated', __('License deactivated!',TM_EPO_TRANSLATION)) ) );
            }
            die();
        }

        if ( (boolean)$result->result === false) {
            $message=__('Invalid data!',TM_EPO_TRANSLATION);
            $status='error';
            $rs=$result->code;
            if (!empty($rs)){
                switch ($result->code){
                case "1":
                    $message=__('Invalid action.',TM_EPO_TRANSLATION);
                    break;
                case "2":
                    $message=__('Please fill all fields before trying to activate.',TM_EPO_TRANSLATION);
                    break;
                case "3":
                    $message=__('Trying to activate from outside the plugin interface is not allowed!',TM_EPO_TRANSLATION);
                    break;
                case "4":
                    $message=__('Error connecting to Envato API. Please try again later.',TM_EPO_TRANSLATION);
                    break;
                case "5":
                    $message=__('Trying to activate with an invalid purchase code!',TM_EPO_TRANSLATION);
                    break;
                case "6":
                    $message=__('That username is not valid for this item purchase code. Please make sure you entered the correct username (case sensitive).',TM_EPO_TRANSLATION);
                    break;
                case "7":
                    $message=__('Trying to activate from an invalid domain!',TM_EPO_TRANSLATION);
                    break;
                case "8":
                    $message=__('Trying to activate from an invalid IP address!',TM_EPO_TRANSLATION);
                    break;
                case "9":
                    $message=__('The purchase code is already activated!',TM_EPO_TRANSLATION);//by another username
                    break;
                case "10":
                    $message=__('The purchase code is already activated on another domain!',TM_EPO_TRANSLATION);
                    break;
                case "11":
                    $message=__('You have already activated that purchase code on another domain!',TM_EPO_TRANSLATION);
                    break;
                case "12":
                    $message=__('The purchase code is already activated! Please buy a valid license!',TM_EPO_TRANSLATION);
                    break;
                case "13":
                    $status='updated';
                    $message=__('You have already activated your purchase code!',TM_EPO_TRANSLATION);
                    break;
                case "14":
                    $message=__('Cannot deactivate. Purchase code is not activated!',TM_EPO_TRANSLATION);
                    break;
                case "15":
                    $status='updated';
                    $message=__('Cannot deactivate. Purchase code is not valid for your save license key!',TM_EPO_TRANSLATION);
                    break;
                }
            }
            echo json_encode( array( 'result' => '-2', 'message'=>sprintf($message_wrap, $status, $message) ) ); 
            die();
        }
        echo json_encode( array( 'result' => '-3', 'message'=>sprintf($message_wrap, 'error', __('Could not complete request!',TM_EPO_TRANSLATION)) ) );
        die();
    }

}

class TM_Updater {
    protected $version_url = 'http://themecomplete.com/api/?';

    var $title = 'TM WooCommerce Extra Product Options';

    function __construct() {
        $this->setup();
        add_filter('upgrader_pre_download', array($this, 'upgradeFilterFromEnvato'), 10, 4);
        add_action('upgrader_process_complete', array($this, 'removeTemporaryDir'));
    }

    public function setup() {
        $instance = new TM_Updating_Manager ( TM_EPO_VERSION, $this->get_url(), TM_PLUGIN_SLUG, $this );
    }

    public function get_url() {
        return $this->version_url . time();
    }
    
    public function upgradeFilterFromEnvato($reply, $package, $updater) {
        global $wp_filesystem;       

        if((isset($updater->skin->plugin) && $updater->skin->plugin === TM_PLUGIN_SLUG) ||
          (isset($updater->skin->plugin_info) && $updater->skin->plugin_info['Name'] === $this->title)
        ) {
            $updater->strings['download_envato'] = __( 'Downloading package from envato market...', TM_EPO_TRANSLATION );
            $updater->skin->feedback( 'download_envato' );
            $package_filename = 'woocommerce-tm-extra-product-options.zip';
            $res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
            if ( ! $res ) {
                return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", TM_EPO_TRANSLATION ) );
            }
            $username = get_option( 'tm_epo_envato_username' );
            $api_key = get_option( 'tm_epo_envato_apikey' );
            $purchase_code = get_option( 'tm_epo_envato_purchasecode' );

            global $tm_license;
            if (!$tm_license->check_license()){
                return new WP_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . admin_url( 'admin.php?page=wc-settings&tab='.TM_ADMIN_SETTINGS_ID ) . '">' .  'Settings</a> to activate WooCommerce Extra Product Options.', TM_EPO_TRANSLATION ) );
            }

            $json = wp_remote_get( $this->envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) );
            $result = json_decode( $json['body'], true );
            if ( ! isset( $result['download-purchase']['download_url'] ) ) {
                return new WP_Error( 'no_credentials', __( 'Error! Envato API error' . ( isset( $result['error'] ) ? ': ' . $result['error'] : '.' ), TM_EPO_TRANSLATION ) );
            }
            $download_file = download_url( $result['download-purchase']['download_url'] );
            if ( is_wp_error( $download_file ) ) {
                return $download_file;
            }
            $upgrade_folder = $wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package';
            if ( is_dir( $upgrade_folder ) ) {
                $wp_filesystem->delete( $upgrade_folder );
            }
            $result = unzip_file( $download_file, $upgrade_folder );
            if ( $result && is_file( $upgrade_folder . '/' . $package_filename ) ) {
                return $upgrade_folder . '/' . $package_filename;
            }
            return new WP_Error( 'no_credentials', __( 'Error on unzipping package', TM_EPO_TRANSLATION ) );
        }
        return $reply;
    }
    public function removeTemporaryDir() {
        global $wp_filesystem;
        if(is_dir($wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package')) {
            $wp_filesystem->delete($wp_filesystem->wp_content_dir() . 'uploads/woocommerce-tm-extra-product-options-envato-package', true);
        }
    }
    protected function envatoDownloadPurchaseUrl( $username, $api_key, $purchase_code ) {
        return 'http://marketplace.envato.com/api/edge/' . rawurlencode( $username ) . '/' . rawurlencode( $api_key ) . '/download-purchase:' . rawurlencode( $purchase_code ) . '.json';
    }
}   


class TM_Updating_Manager {

    public $current_version;
    public $update_path;
    public $plugin_slug;
    public $slug;    
    public $TM_Updater_instance;

    protected $url = 'http://bit.ly/1syDtHe';

    function __construct( $current_version, $update_path, $plugin_slug, $instance) {

        $this->TM_Updater_instance = $instance;
        $this->plugin_envato_id=TM_PLUGIN_ID;
        $this->current_version = $current_version;
        $this->plugin_slug = $plugin_slug;                   
        $this->update_path = $update_path;        
        $this->slug = explode( '/', $plugin_slug );
        $this->slug = str_replace( '.php', '', $this->slug[1] );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'tm_update_plugins' ) );
        add_filter( 'plugins_api', array( $this, 'tm_plugins_api' ), 10, 3 );
        add_action( 'in_plugin_update_message-' . $this->plugin_slug, array( $this, 'tm_update_message' ) );
    }

    public function tm_update_plugins( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote_version = $this->remote_api_call('version');

        if ( version_compare( $this->current_version, $remote_version, '<' ) ) {         
            $obj = new stdClass();
            $obj->slug = $this->slug;
            $obj->new_version = $remote_version;
            
            global $tm_license;
            if ($tm_license->check_license()){ 
                $obj->url = $this->update_path;
                $obj->package = $this->update_path;
            }else{
                $obj->url = '';
                $obj->package = '';
            }
            
            $obj->name = $this->TM_Updater_instance->title;
            $transient->response[$this->plugin_slug] = $obj;
        }

        return $transient;
    }

    public function tm_plugins_api( $false, $action, $arg ) {
        if ( isset( $arg->slug ) && $arg->slug === $this->slug ) {
            $info = $this->remote_api_call('info',true);
            if ($info){
                $info->name = $this->TM_Updater_instance->title;                
                $info->slug = $this->slug;
                global $tm_license;
                if ($tm_license->check_license()){ 
                    $info->download_link = $this->update_path;
                } 
            }
            
            return $info;
        }
        return $false;
    }

    public function remote_api_call($action="", $is_serialized=false) {
        $request = wp_remote_post( $this->update_path, 
            array( 'body' => 
                array(  'action' => $action ,
                        'id'    => $this->plugin_envato_id,
                        'type'  => 'plugin'
                    ) ) );
        if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
            return ($is_serialized)?unserialize( ( $request['body'] ) ):$request['body'];
        }
        return false;
    }

    public function tm_update_message() {
        global $tm_license;
        if (!$tm_license->check_license()){
            echo '<br /><a href="' . $this->url . '">' . __( 'Download new version from CodeCanyon', TM_EPO_TRANSLATION ) . '</a>'.' '.__( 'or register the plugin to receive automatic updates.', TM_EPO_TRANSLATION );
        }
    }
}
global $tm_license;
$tm_license = new TM_Licenser();
$tm_update = new TM_Updater(); 
?>