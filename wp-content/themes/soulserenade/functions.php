<?php 

function init_page_custom_fields() {
	add_post_type_support( 'page', 'custom-fields' );
}

add_action('init', 'init_page_custom_fields');

/* Handle Artist custom post type */

add_action( 'init', 'create_post_type' );

function create_post_type() {
  register_post_type( 'artist',
    array(
      'labels' => array(
        'name' => __( 'Artists' ),
        'singular_name' => __( 'Artist' )
      ),
    'public' => true,
    'has_archive' => true,
    'supports' => array( 'title', 'editor', 'comments', 'excerpt', 'custom-fields', 'thumbnail' )
    )
  );
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


?>