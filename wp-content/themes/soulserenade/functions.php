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


?>