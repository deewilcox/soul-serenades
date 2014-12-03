<?php

/**
 * Creates store post type if not created by Custom Store Locator plugin
 */
class Store_Post_Type {

	function __construct() {
		//Create store post type
		add_action( 'init', array( $this, 'create_store_post_type' ) );
		//Add fields to store in admin
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		//Save fields
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
		//Enqueue scripts for backend
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	function admin_scripts() {
		wp_enqueue_script( 'store-admin', $this->dir() . '/../js/store-admin.js',
				array( 'jquery' ), false, true );
		wp_register_style( 'store-css', $this->dir() . '/../css/store.css' );
		wp_enqueue_style( 'store-css' );
	}

	/**
	 * Change \ to /
	 */
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
	 * Saves the store meta data
	 */
	function save_meta_boxes( $post_id, $post ) {
		//If not store, do nothing
		if ( $post->post_type != 'store' ) return;

		$this->save_meta( $post_id, 'store_email' );
		$this->save_meta( $post_id, 'store_address_1' );
		$this->save_meta( $post_id, 'store_address_2' );
		$this->save_meta( $post_id, 'store_address_3' );
		$this->save_meta( $post_id, 'store_address_4' );
		$this->save_meta( $post_id, 'store_latitude' );
		$this->save_meta( $post_id, 'store_longitude' );
	}

	/**
	 * 	Saves meta_name which is in the $_POST variable
	 */
	function save_meta( $post_id, $meta_name, $unique = true ) {
		$value = '';
		if ( isset( $_POST[$meta_name] ) ) $value = $_POST[$meta_name];
		if ( !add_post_meta( $post_id, $meta_name, $value, true ) )
				update_post_meta( $post_id, $meta_name, $value );
	}

	/**
	 * All the meta boxes
	 */
	function add_meta_boxes() {
		add_meta_box( 'store-locator-store-data',
				__( 'Store Data', 'woocommerce_distance_rate_shipping' ),
				array( $this, 'store_details' ), 'store', 'normal', 'high' );
	}

	/**
	 * Adds store details meta box
	 */
	function store_details() {
		global $post;
		global $store_locator_shortcodes;
		$post_id = $post->ID;
		print '
		<script src="https://maps.googleapis.com/maps/api/js?sensor=false&language=' . str_replace( '_',
						'-', get_locale() ) . '"></script>
		<div id="store-details">
			<table>
				<tr>
					<td>
						<table id="store-address">
							<tr><td colspan="3"><h2>';
		_e( 'Store Address', 'woocommerce_distance_rate_shipping' );
		print ' [store_address]</h2></td></tr>
							<p>';
		_e( 'Please fill out the store address and check that google positions it correctly on the map below.',
				'woocommerce_distance_rate_shipping' );
		print '</p></td></tr>';
		print '<tr><th>' . __( 'Label', 'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Value',
						'woocommerce_distance_rate_shipping' ) . '</th></tr>';
		$this->add_text_input( 'store_address_1',
				__( 'Address 1', 'woocommerce_distance_rate_shipping' ),
				'[store_address_1]', get_post_meta( $post_id, 'store_address_1', true ),
				true,
				__( 'House Number/Street Address', 'woocommerce_distance_rate_shipping' ) );
		$this->add_text_input( 'store_address_2',
				__( 'Address 2', 'woocommerce_distance_rate_shipping' ),
				'[store_address_2]', get_post_meta( $post_id, 'store_address_2', true ),
				true, __( 'Town', 'woocommerce_distance_rate_shipping' ) );
		$this->add_text_input( 'store_address_3',
				__( 'Address 3', 'woocommerce_distance_rate_shipping' ),
				'[store_address_3]', get_post_meta( $post_id, 'store_address_3', true ),
				true, __( 'Region/State', 'woocommerce_distance_rate_shipping' ) );
		$this->add_text_input( 'store_address_4',
				__( 'Address 4', 'woocommerce_distance_rate_shipping' ),
				'[store_address_4]', get_post_meta( $post_id, 'store_address_4', true ),
				true, __( 'Country', 'woocommerce_distance_rate_shipping' ) );
		print '<tr id="store-map-row"><td colspan="3"><div id="store-map"></div>';
		$this->add_hidden_input( 'store_latitude',
				get_post_meta( $post_id, 'store_latitude', true ) );
		$this->add_hidden_input( 'store_longitude',
				get_post_meta( $post_id, 'store_longitude', true ) );
		_e( 'You can drag and drop the marker to position the store more precisely.',
				'woocommerce_distance_rate_shipping' );
		print '</td></tr>
							<tr><td colspan="3"><h2>';
		_e( 'Store Details', 'woocommerce_distance_rate_shipping' );
		print '</h2>
							<p>';
		_e( 'Please complete your store details. Please leave values blank if unavailable',
				'woocommerce_distance_rate_shipping' );
		print '</p></td></tr>
							<tr><th>' . __( 'Label', 'woocommerce_distance_rate_shipping' ) . '</th><th>' . __( 'Value',
						'woocommerce_distance_rate_shipping' ) . '</th></tr>';
		$this->add_text_input( 'store_email',
				__( 'E-mail', 'woocommerce_distance_rate_shipping' ), '[store_email]',
				get_post_meta( $post_id, 'store_email', true ), true,
				__( 'Email Address for Store', 'woocommerce_distance_rate_shipping' ) );
		print '</tr>';
		print '			</table></table>
		</div>
		';
	}

	/**
	 * 	Creates Store as a post type
	 */
	function create_store_post_type() {
		register_post_type( 'store',
				array( 'labels' => array(
				'name' => __( 'Stores', 'woocommerce_distance_rate_shipping' ),
				'singular_name' => __( 'Store', 'woocommerce_distance_rate_shipping' ),
				'menu_name' => __( 'Stores', 'woocommerce_distance_rate_shipping' ),
				'add_new' => __( 'Add Store', 'woocommerce_distance_rate_shipping' ),
				'add_new_item' => __( 'Add New Store', 'woocommerce_distance_rate_shipping' ),
				'edit' => __( 'Edit', 'woocommerce_distance_rate_shipping' ),
				'edit_item' => __( 'Edit Store', 'woocommerce_distance_rate_shipping' ),
				'new_item' => __( 'New Store', 'woocommerce_distance_rate_shipping' ),
				'view' => __( 'View Store', 'woocommerce_distance_rate_shipping' ),
				'view_item' => __( 'View Store', 'woocommerce_distance_rate_shipping' ),
				'search_items' => __( 'Search Stores', 'woocommerce_distance_rate_shipping' ),
				'not_found' => __( 'No Stores found', 'woocommerce_distance_rate_shipping' ),
				'not_found_in_trash' => __( 'No Stores found in trash',
						'woocommerce_distance_rate_shipping' ),
				'parent' => __( 'Parent Store', 'woocommerce_distance_rate_shipping' )
			),
			'public' => true,
			'exclude_from_search' => false,
			'description' => __( 'This is where you can add/edit stores for your stores.',
					'woocommerce_distance_rate_shipping' ),
			'menu_icon' => $this->dir() . '/../images/store-locator.png',
			'show_ui' => true,
			'capability_type' => array( 'store', 'stores' ),
			'map_meta_cap' => true,
			'query_var' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'custom_fields', 'post_formats' ),
			'hierarchical' => false,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
//			'menu_position' => 50
				)
		);
		$role = get_role( 'store_author' );
		if ( empty( $role ) ) {
			$role = add_role( 'store_author',
					__( 'Store Author', 'woocommerce_distance_rate_shipping' ) );
		}
		$role->add_cap( 'read' );
		$role->add_cap( 'edit_stores' );
		$role->add_cap( 'edit_published_stores' );
		$role->add_cap( 'delete_stores' );
		$role->add_cap( 'read_stores' );
		$role->add_cap( 'publish_stores' );
		$role->add_cap( 'can_edit_stores' );

		$roles = array(
			get_role( 'administrator' ),
			get_role( 'editor' ),
		);
		$capabilities = array( 'edit_stores', 'edit_published_stores', 'edit_others_stores',
			'delete_stores', 'delete_others_stores', 'delete_published_stores',
			'read_stores', 'read_others_stores', 'publish_stores', 'can_edit_stores' );
		foreach ( $roles as $role ) {
			foreach ( $capabilities as $cap ) {
				if ( !empty( $role ) ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Adds a hidden input
	 */
	public static function add_hidden_input( $name, $value ) {
		print '<input type="hidden" value="' . $value . '" name="' . $name . '"/>';
	}

	/**
	 * Adds row to table on add/edit store page with input boxes.
	 */
	public static function add_text_input( $name, $label, $shortcode, $value,
			$unique, $placeholder ) {
		print "<tr><td>{$label}</td><td><input name=\"{$name}\" value=\"{$value}\" placeholder=\"{$placeholder}\" /></td></tr>";
	}

}

$store_post_type = new Store_Post_Type();
?>
