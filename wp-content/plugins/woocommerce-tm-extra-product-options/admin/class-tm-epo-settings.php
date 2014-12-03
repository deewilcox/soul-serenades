<?php
/**
 * Settings class.
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if (class_exists('WC_Settings_Page')){
	global $_TM_Global_Extra_Product_Options;
	$_TM_Global_Extra_Product_Options->tm_load_scripts();
class TM_Settings_Extra_Product_Options extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = TM_ADMIN_SETTINGS_ID;
		$this->label = __('Extra Product Options', TM_EPO_TRANSLATION);
		$this->tab_count=0;

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'woocommerce_admin_field_tm_tabs_header', array( $this, 'tm_tabs_header_setting' ) );
		

		add_action( 'woocommerce_settings_' . 'epo_page_options' , array( $this, 'tm_settings_hook' ) );
		add_action( 'woocommerce_settings_' . 'epo_page_options' . '_end', array( $this, 'tm_settings_hook_end' ) );
		add_action( 'woocommerce_settings_' . 'epo_page_options' . '_after', array( $this, 'tm_settings_hook_after' ) );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'tm_settings_hook_all_end' ) );
	}

	public function tm_tabs_header_setting() {
		?>
		<div class='tm-settings-wrap tm_wrapper'>
			<div class="header"><h3><?php  _e( 'Extra Product Options Settings', TM_EPO_TRANSLATION );?></h3></div>
		<div class='transition tm-tabs'>
			<div class='transition tm-tab-headers tmsettings-tab'><?php
			$_general_settings='<div class="tm-box">
					<h4 class="tab-header open" data-id="tmsettings1-tab">'.__( 'General', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>';
			$_display_settings='<div class="tm-box">
					<h4 class="tab-header closed" data-id="tmsettings2-tab">'.__( 'Display', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>';
			$_cart_settings='<div class="tm-box">
					<h4 class="tab-header closed" data-id="tmsettings3-tab">'.__( 'Cart', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>';
			$_string_settings='<div class="tm-box">
					<h4 class="tab-header closed" data-id="tmsettings4-tab">'.__( 'Strings', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>';
			$_style_settings='<div class="tm-box">
					<h4 class="tab-header closed" data-id="tmsettings5-tab">'.__( 'Style', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>';
			$_license_settings=( !defined('TM_DISABLE_LICENSE') )?'<div class="tm-box">
					<h4 class="tab-header closed" data-id="tmsettings6-tab">'.__( 'License', TM_EPO_TRANSLATION ).'<span class="fa tm-arrow fa-angle-down"></span></h4>
				</div>':'';
			echo $_general_settings.$_display_settings.$_cart_settings.$_string_settings.$_style_settings.$_license_settings;
			?>
		</div>
		<?php
	}

	public function tm_settings_hook() {
		$this->tab_count++;
		echo '</table><div class="transition tm-tab tmsettings'.$this->tab_count.'-tab"><table class="form-table">';
	}
	public function tm_settings_hook_end() {
		echo '</table></div><table>';
	}
	public function tm_settings_hook_after() {
		
	}
	public function tm_settings_hook_all_end() {
		echo '</div></div>'; // close transition tm-tabs , tm-settings-wrap
	}
	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings() {
		global $tm_license;

		$_general_settings=array(
				array( 
					'type' => 'title',				
					'id' => 'epo_page_options' 
					),
				array(
						'title' => __( 'Enable for roles', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select the roles that will have access to the extra options.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_roles_enabled',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> '@everyone',
						'type' 		=> 'multiselect',
						'options' 	=> tm_get_roles(),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Final total box', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select when to show the final total box', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_final_total_box',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 			=> __( 'Show Both Final and Options total box', TM_EPO_TRANSLATION ),
							'final' 			=> __( 'Show only Final total', TM_EPO_TRANSLATION ),
							'hideoptionsifzero' => __( 'Show Final total and hide Options total if zero', TM_EPO_TRANSLATION ),
							'hide' 				=> __( 'Hide Final total box', TM_EPO_TRANSLATION ),
							'pxq' 				=> __( 'Always show only Final total (Price x Quantity)', TM_EPO_TRANSLATION ),
						),
						'desc_tip'	=>  false,
					),		
				array(
						'title' => __( 'Strip html from emails', TM_EPO_TRANSLATION ),
						'desc' 		=> __( 'Check to strip the html tags from emails', TM_EPO_TRANSLATION ),
						'id' 		=> 'tm_epo_strip_html_from_emails',
						'default' 	=> 'yes',
						'type' 		=> 'checkbox',					
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Disable lazy load images', TM_EPO_TRANSLATION ),
						'desc' 		=> __( 'Check to disable lazy loading images.', TM_EPO_TRANSLATION ),
						'id' 		=> 'tm_epo_no_lazy_load',
						'default' 	=> 'no',
						'type' 		=> 'checkbox',					
						'desc_tip'	=>  false,
					),				
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),
		);
		
		$_display_settings=array(
				array(
					'type' => 'title', 
					'id' => 'epo_page_options' 
				),
				array(
						'title' 	=> __( 'Display', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'This controls how your fields are displayed on the front-end.<br />If you choose "Show using action hooks" you have to manually write the code to your theme or plugin to display the fields and the placement settings below will not work. <br />If you use Composite Products extension you must leave this setting to "Normal" otherwise the extra options cannot be displayed on the composite product bundles.<br />See more at the documentation.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_display',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' => __( 'Normal', TM_EPO_TRANSLATION ),
							'action' => __( 'Show using action hooks', TM_EPO_TRANSLATION ),
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' 	=> __( 'Extra Options placement', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select where you want the extra options to appear.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_options_placement',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'woocommerce_before_add_to_cart_button',
						'type' 		=> 'select',
						'options' 	=> array(
							'woocommerce_before_add_to_cart_button' 	=> __( 'Before add to cart button', TM_EPO_TRANSLATION ),
							'woocommerce_after_add_to_cart_button' 		=> __( 'After add to cart button', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_add_to_cart_form' 		=> __( 'Before cart form', TM_EPO_TRANSLATION ),
							'woocommerce_after_add_to_cart_form' 		=> __( 'After cart form', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_single_product' 	=> __( 'Before product', TM_EPO_TRANSLATION ),
							'woocommerce_after_single_product' 	=> __( 'After product', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_single_product_summary' 	=> __( 'Before product summary', TM_EPO_TRANSLATION ),
							'woocommerce_after_single_product_summary' 	=> __( 'After product summary', TM_EPO_TRANSLATION ),
							
							'woocommerce_product_thumbnails' 	=> __( 'After product image', TM_EPO_TRANSLATION ),

							'custom' 	=> __( 'Custom hook', TM_EPO_TRANSLATION ),
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Extra Options placement custom hook', TM_EPO_TRANSLATION ),
						'desc' 		=> '',
						'id' 		=> 'tm_epo_options_placement_custom_hook',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),
				array(
						'title' 	=> __( 'Totals box placement', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select where you want the Totals box to appear.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_totals_box_placement',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'woocommerce_before_add_to_cart_button',
						'type' 		=> 'select',
						'options' 	=> array(
							'woocommerce_before_add_to_cart_button' 	=> __( 'Before add to cart button', TM_EPO_TRANSLATION ),
							'woocommerce_after_add_to_cart_button' 		=> __( 'After add to cart button', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_add_to_cart_form' 		=> __( 'Before cart form', TM_EPO_TRANSLATION ),
							'woocommerce_after_add_to_cart_form' 		=> __( 'After cart form', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_single_product' 	=> __( 'Before product', TM_EPO_TRANSLATION ),
							'woocommerce_after_single_product' 	=> __( 'After product', TM_EPO_TRANSLATION ),
							
							'woocommerce_before_single_product_summary' 	=> __( 'Before product summary', TM_EPO_TRANSLATION ),
							'woocommerce_after_single_product_summary' 	=> __( 'After product summary', TM_EPO_TRANSLATION ),
							
							'woocommerce_product_thumbnails' 	=> __( 'After product image', TM_EPO_TRANSLATION ),

							'custom' 	=> __( 'Custom hook', TM_EPO_TRANSLATION ),
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Totals box placement custom hook', TM_EPO_TRANSLATION ),
						'desc' 		=> '',
						'id' 		=> 'tm_epo_totals_box_placement_custom_hook',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),
				array(
						'title' 	=> __( 'Force Select Options', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'This changes the add to cart button to display select options when the product has extra product options.<br />Enabling this will remove the ajax functionality.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_force_select_options',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 	=> __( 'Disable', TM_EPO_TRANSLATION ),
							'display' 	=> __( 'Enable', TM_EPO_TRANSLATION ),
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Remove Free price label', TM_EPO_TRANSLATION ),
						'desc' 		=> __( 'Check to remove Free price label when product has extra options', TM_EPO_TRANSLATION ),
						'id' 		=> 'tm_epo_remove_free_price_label',
						'default' 	=> 'no',
						'type' 		=> 'checkbox',					
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Hide uploaded file path', TM_EPO_TRANSLATION ),
						'desc' 		=> __( 'Check to hide the uploaded file path from users.', TM_EPO_TRANSLATION ),
						'id' 		=> 'tm_epo_hide_upload_file_path',
						'default' 	=> 'yes',
						'type' 		=> 'checkbox',					
						'desc_tip'	=>  false,
					),
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),
		);

		$_cart_settings=array(
				array(  
					'type' => 'title', 				
					'id' => 'epo_page_options' 
					),
				array(
						'title' => __( 'Clear cart button', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Enables or disables the clear cart button', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_clear_cart_button',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 	=> __( 'Hide', TM_EPO_TRANSLATION ),
							'show' 		=> __( 'Show', TM_EPO_TRANSLATION )
							
						),
						'desc_tip'	=>  false,
					),	
				array(
						'title' => __( 'Cart Field Display', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select how to display your fields in the cart', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_cart_field_display',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 	=> __( 'Normal display', TM_EPO_TRANSLATION ),
							'link' 		=> __( 'Display a pop-up link', TM_EPO_TRANSLATION )						
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Hide extra options in cart', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Enables or disables the display of options in the cart.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_hide_options_in_cart',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 	=> __( 'Show', TM_EPO_TRANSLATION ),
							'hide' 		=> __( 'Hide', TM_EPO_TRANSLATION )
							
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Hide extra options prices in cart', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Enables or disables the display of prices of options in the cart.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_hide_options_prices_in_cart',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'normal',
						'type' 		=> 'select',
						'options' 	=> array(
							'normal' 	=> __( 'Show', TM_EPO_TRANSLATION ),
							'hide' 		=> __( 'Hide', TM_EPO_TRANSLATION )
							
						),
						'desc_tip'	=>  false,
					),
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),

		);

		$_string_settings=array(
				array(  
					'type' => 'title', 				
					'id' => 'epo_page_options' 
					),
				array(
						'title' => __( 'Final total text', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select the Final total text or leave blank for default.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_final_total_text',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),

				array(
						'title' => __( 'Options total text', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select the Options total text or leave blank for default.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_options_total_text',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),

				(tm_woocommerce_subscriptions_check())?
				array(
						'title' => __( 'Subscription fee text', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select the Subscription fee text or leave blank for default.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_subscription_fee_text',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					):
				array(),

				array(
						'title' => __( 'Free Price text replacement', TM_EPO_TRANSLATION ),
						'desc' 		=> __( 'Enter a text to replace the Free price label when product has extra options.', TM_EPO_TRANSLATION ),
						'id' 		=> 'tm_epo_replacement_free_price_text',
						'default' 	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),
		);
		
		$_style_settings=array(
				array(  
					'type' => 'title', 				
					'id' => 'epo_page_options' 
					),
				
				array(
						'title' => __( 'Enable checkbox and radio styles', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Enables or disables extra styling for checkboxes and radio buttons.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_css_styles',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> '',
						'type' 		=> 'select',
						'options' 	=> array(
							'' 			=> __( 'Disable', TM_EPO_TRANSLATION ),
							'on' 		=> __( 'Enable', TM_EPO_TRANSLATION )
							
						),
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Style', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Select a style.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_css_styles_style',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'round',
						'type' 		=> 'select',
						'options' 	=> array(
							'round' 	=> __( 'Round', TM_EPO_TRANSLATION ),
							'square' 	=> __( 'Square', TM_EPO_TRANSLATION )
							
						),
						'desc_tip'	=>  false,
					),
				
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),

		);

		$_license_settings=(!defined('TM_DISABLE_LICENSE'))?
			array(				
				array( 
					'type' => 'title', 
					'id' => 'epo_page_options' 
					),
				array(
						'title' => __( 'Username', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'Your Envato username.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_envato_username',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
						
						//'custom_attributes'=>($tm_license->get_license())?array('disabled'=>'disabled'):""
					),
				array(
						'title' => __( 'Envato API Key', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div>'.__( 'You can find your API key by visiting your Account page then clicking the My Settings tab. At the bottom of the page you’ll find your account’s API key and a button to regenerate it as needed.', TM_EPO_TRANSLATION ).'</div>',
						'id' 		=> 'tm_epo_envato_apikey',
						'default'	=> '',
						'type' 		=> 'password',					
						'desc_tip'	=>  false,
					),
				array(
						'title' => __( 'Purchase code', TM_EPO_TRANSLATION ),
						'desc' 		=> '<div><p>'.__( 'Please enter your <strong>CodeCanyon WooCommerce Extra Product Options purchase code</strong>.', TM_EPO_TRANSLATION ).'</p><p>'.__( 'To access your Purchase Code for an item:', TM_EPO_TRANSLATION ).'</p>'
										.'<ol>'
										.'<li>'.__('Log into your Marketplace account', TM_EPO_TRANSLATION ).'</li>'
										.'<li>'.__('From your account dropdown links, select "Downloads"', TM_EPO_TRANSLATION ).'</li>'
										.'<li>'.__('Click the "Download" button that corresponds with your purchase', TM_EPO_TRANSLATION ).'</li>'
										.'<li>'.__('Select the "License certificate &amp; purchase code" download link. Your Purchase Code will be displayed within the License Certificate.', TM_EPO_TRANSLATION ).'</li>'
										.'</ol>'
										.'<p><img alt="Purchase Code Location" src="'.TM_plugin_url.'/assets/images/download_button.gif" title="Purchase Code Location" style="vertical-align: middle;"></p>'
										.'<div class="tm-license-button">'
										
										.'<a href="#" class="'.($tm_license->get_license()?"":"tm-hidden ").'tm-button button button-primary button-large tm-deactivate-license" id="tm_deactivate_license">'.__('Deactivate License', TM_EPO_TRANSLATION ).'</a>'
										.'<a href="#" class="'.($tm_license->get_license()?"tm-hidden ":"").'tm-button button button-primary button-large tm-activate-license" id="tm_activate_license">'.__('Activate License', TM_EPO_TRANSLATION ).'</a>'
										
										.'</div>'
										.'<div class="tm-license-result">'
										.(($tm_license->get_license())?
										"<div class='activated'><p>".__("License activated.",TM_EPO_TRANSLATION)."</p></div>"
										:""
										)
										.'</div>'
										.'</div>',
						'id' 		=> 'tm_epo_envato_purchasecode',
						'default' 	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
						//'custom_attributes'=>($tm_license->get_license())?array('disabled'=>'disabled'):""
					),
				array( 'type' => 'sectionend', 'id' => 'epo_page_options' ),		
			):array();

		return apply_filters( 'tm_' . $this->id . '_settings', 
			array_merge(
				array(
					array( 'type' 		=> 'tm_tabs_header' )
				),
				$_general_settings,
				$_display_settings,
				$_cart_settings,
				$_string_settings,
				$_style_settings,
				$_license_settings
			)
		); // End pages settings
	}
}
new TM_Settings_Extra_Product_Options();
}
?>