<?php 

function init_page_custom_fields() {
	add_post_type_support( 'page', 'custom-fields' );
}

add_action('init', 'init_page_custom_fields');

add_theme_support( 'post-formats', array( 'gallery', 'image', 'video', 'audio', 'quote', 'link', 'status', 'aside' ) );

/* Handle Artist custom post type */

add_action( 'init', 'create_post_type' );

function create_post_type() {
  register_post_type( 'artist',
    array(
      'labels' => array(
        'name' => __( 'Artists' ),
        'singular_name' => __( 'Artist' ),
        'add_new' => __( 'Add New' ),
        'add_new_item' => __( 'Add New Artist' ),
        'edit' => __( 'Edit' ),
        'edit_item' => __( 'Edit Artist' ),
        'new_item' => __( 'New Artist' ),
        'view' => __( 'View Artists' ),
        'view_item' => __( 'View Artist' ),
        'search_items' => __( 'Search Artists' ),
        'not_found' => __( 'No artists found' ),
        'not_found_in_trash' => __( 'No artists found in trash' )
      ),
	  'description' => __( 'This is where you can add new Artists to your site.' ),
      'public' => true,
      'hierarchical' => false,
      '_builtin' => false,
      'capability_type' => 'post',
      'has_archive' => true,
/*       'rewrite' => true, */
      'supports' => array( 'title', 'editor', 'comments', 'excerpt', 'custom-fields', 'thumbnail')
    )
  );
  flush_rewrite_rules();
}

/*
function init_artist_custom_fields() {
	add_post_type_support( 'artist', 'custom-fields' );
}

add_action('init', 'init_artist_custom_fields');

add_theme_support( 'post-thumbnails', array( 'artist' ) );
*/ 

/* Handle Woocommerce */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);

function my_theme_wrapper_start() {
  echo '<section id="main">';
}

function my_theme_wrapper_end() {
  echo '</section>';
}

add_theme_support( 'woocommerce' );


function custom_header_images() {
	global $post;
	$post_id = ( is_attachment() && isset( $post->post_parent ) ) ? $post->post_parent : get_queried_object_id();
	$custom_image = ( is_singular() || get_option( 'page_for_posts' ) == $post_id || is_attachment() ) ? get_post_meta( $post_id, 'arcade_basic_custom_image', true ) : '';

	if ( $custom_image ) {
		$imageExists = file_exists($custom_image);
		if($imageExists){
			echo '<img src="' . esc_url( $custom_image ) . '" alt="" class="header-img" />';
		}
		else{
			echo '<img src="' . site_url() . '/images/default-header.jpg" alt="Soul Serenades" class="header-img" />';
		}
	} 
	else {
		if ( $header_image = get_header_image() ) :
			?>
			<img class="header-img" src="<?php header_image(); ?>" alt="" />
			<?php
		endif;
	}
}

/* Woocommerce Custom Order Fields */

function custom_filter_checkout_fields($fields){
    $fields['extra_fields'] = array(
            'recipient_name' => array(
                'type' => 'text',
                'required'      => true,
                'label' => __( 'Name of Serenade Recipient' )
                ),
            'recipient_phone' => array(
                'type' => 'text',
                'required'      => true,
                'label' => __( 'Phone Number of Serenade Recipient' )
                ),    
            'venue_address' => array(
                'type' => 'text',
                'required'      => true,
                'label' => __( 'Address of Venue' )
                ),
            'venue_type' => array(
                'type' => 'text',
                'required'      => true,
                'label' => __( 'Type of Venue' )
                ),
            'arrival_time' => array(
                'type' => 'text',
                'required'      => true,
                'label' => __( 'Range of Time Artist Should Arrive' )
                ),          
		// 'venue' => array(
   //             'type' => 'select',
     //           'options' => array( 'a' => __( 'apple' ), 'b' => __( 'bacon' ), 'c' => __( 'chocolate' ) ),
       //         'required'      => true,
         //       'label' => __( 'Another field' )
           //     ),
			'policies' => array(
				'type' => 'checkbox',
				'required' => true,
				'label' => __( 'I have read and agree to the <a href="/soulserenades/policies/">Policies and Terms of Use</a>.' )
				)
            );

    return $fields;
}


add_filter( 'woocommerce_checkout_fields', 'custom_filter_checkout_fields' );

function custom_extra_checkout_fields(){ 

    $checkout = WC()->checkout(); ?>

    <div class="extra-fields">
    <h3><?php _e( 'Required Information' ); ?></h3>

    <?php 
    // because of this foreach, everything added to the array in the previous function will display automagically
    foreach ( $checkout->checkout_fields['extra_fields'] as $key => $field ) : ?>

        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>

        <?php endforeach; ?>
    <?php echo '</div>' ;
}

add_action( 'woocommerce_checkout_after_customer_details' ,'custom_extra_checkout_fields' );

// save the extra field when checkout is processed
function custom_save_extra_checkout_fields( $order_id, $posted ){
    // sanitize text fields
    $customTextFields = array('recipient_name','recipient_phone','venue_address','venue_type','arrival_time');
    foreach ($customTextFields as $field){
	    if( isset( $posted[$field] ) ) {
	        update_post_meta( $order_id, '_' . $field, sanitize_text_field( $posted[$field] ) );
	    }
    }
    // sanitize select box field
    if( isset( $posted['venue'] ) && in_array( $posted['venue'], array( 'a', 'b', 'c' ) ) ) {
        update_post_meta( $order_id, '_venue', $posted['venue'] );
    }
    // post checkbox field
    if( isset( $posted['policies'] ) && in_array( $posted['policies'], array( 'a', 'b', 'c' ) ) ) {
        update_post_meta( $order_id, '_policies', $posted['policies'] );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'custom_save_extra_checkout_fields', 10, 2 );

// display the extra data on order recieved page and my-account order review
function custom_display_order_data( $order_id ){  
	$customTextFields = array(
    		array('fieldname' => 'recipient_name', 'label' => 'Recipient Name'),
    		array('fieldname' => 'recipient_phone', 'label' => 'Recipient Phone'),
    		array('fieldname' => 'venue_address', 'label' => 'Venue Address'),
    		array('fieldname' => 'venue_type', 'label' => 'Venue Type'),
    		array('fieldname' => 'arrival_time', 'label' => 'Arrival Time'),
    		array('fieldname' => 'policies', 'label' => 'Policies')
		); 
?>
    <h2><?php _e( 'Additional Info' ); ?></h2>
    <table class="shop_table shop_table_responsive additional_info">
        <tbody>
        <?php foreach ($customTextFields as $field): ?>
            <tr>
                <th><?php _e( $field['label'] . ':' ); ?></th>
                <td><?php echo get_post_meta( $order_id, '_' . $field['fieldname'], true ); ?></td>
            </tr>
        <?php endforeach; ?>    
        </tbody>
    </table>
<?php }
add_action( 'woocommerce_thankyou', 'custom_display_order_data', 20 );
add_action( 'woocommerce_view_order', 'custom_display_order_data', 20 );


// display the extra data in the order admin panel
function custom_display_order_data_in_admin( $order ){  ?>
    <div class="order_data_column">
        <h4><?php _e( 'Extra Details', 'woocommerce' ); ?></h4>
<?php 
 		$customTextFields = array(
    		array('fieldname' => 'recipient_name', 'label' => 'Recipient Name'),
    		array('fieldname' => 'recipient_phone', 'label' => 'Recipient Phone'),
    		array('fieldname' => 'venue_address', 'label' => 'Venue Address'),
    		array('fieldname' => 'venue_type', 'label' => 'Venue Type'),
    		array('fieldname' => 'arrival_time', 'label' => 'Arrival Time'),
    		array('fieldname' => 'policies', 'label' => 'Policies')
		); 
		foreach ($customTextFields as $field): 
            echo '<p><strong>' . __( $field['label'] ) . ':</strong>' . get_post_meta( $order->id, '_' . $field['fieldname'], true ) . '</p>';
        endforeach; 
?>
    </div>
<?php }
add_action( 'woocommerce_admin_order_data_after_order_details', 'custom_display_order_data_in_admin' , 20, 1 );

// add order info to email
function custom_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
	$customTextFields = array(
    		array('fieldname' => 'recipient_name', 'label' => 'Recipient Name'),
    		array('fieldname' => 'recipient_phone', 'label' => 'Recipient Phone'),
    		array('fieldname' => 'venue_address', 'label' => 'Venue Address'),
    		array('fieldname' => 'venue_type', 'label' => 'Venue Type'),
    		array('fieldname' => 'arrival_time', 'label' => 'Arrival Time'),
    		array('fieldname' => 'policies', 'label' => 'Policies')
		); 
        foreach ($customTextFields as $field):
    		$fields[$field['fieldname']] = array(
                'label' => __( $field['label'] ),
                'value' => get_post_meta( $order->id, '_' . $field['fieldname'], true ),
            );
	    endforeach;
    return $fields;
}
add_filter('woocommerce_email_order_meta_fields', 'custom_email_order_meta_keys', 10, 3 );

// allow free gifts to be added to cart only if at least one package is present
function custom_validate_free_gift( $valid, $product_id, $quantity ) {
	$cart = WC()->cart->get_cart();
	$freeGiftCategoryId = '16';
	$packageCategoryId = '18';
	$packageIsInCart = false;
    
	if(!empty($cart)) {
    	// loop through cart and make sure a package is present
        foreach($cart as $key=>$value){
        	$product = $value['data'];
        	$productId = $product->product_id;
        	
        	$terms = get_the_terms($productId, 'product_cat');
        	if(!empty($terms)) { 
	        	foreach($terms as $term) {
	        		$productCategoryId = $term->term_id;
	        		if($productCategoryId == $packageCategoryId) {
	        			$packageIsInCart = true;
	        		}
        		}
        	}
        	else {
        		$valid = false;
    			wc_add_notice( 'You must order a package before you can select a free gift.', 'error' );
        	}
    	}
    	if(!$packageIsInCart) {
    		$valid = false;
    		wc_add_notice( 'You must order a package before you can select a free gift.', 'error' );
    	}
    }
    else {
    	$valid = false;
		wc_add_notice( 'You must order a package before you can select a free gift.', 'error' );
    }
    return $valid;
}
//add_filter( 'woocommerce_add_to_cart_validation', 'custom_validate_free_gift', 1, 3 );
