<?php
/**
 *
 *   View for displaying add/edit screen
 *
 */

// Direct access security
if ( !defined( 'TM_EPO_PLUGIN_SECURITY' ) ) {
    die();
}
require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );
$messages = array();
$messages[TM_EPO_GLOBAL_POST_TYPE] = array(
	 0 => '', // Unused. Messages start at index 1.
	 1 => __('Form updated.', TM_EPO_TRANSLATION),
	 2 => __('Form updated.', TM_EPO_TRANSLATION),
	 3 => __('Form updated.', TM_EPO_TRANSLATION),
	 4 => __('Form updated.', TM_EPO_TRANSLATION),
	/* translators: %s: date and time of the revision */
	 5 => isset($_GET['revision']) ? sprintf( __('Form restored to revision from %s', TM_EPO_TRANSLATION), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	 6 => __('Form published.', TM_EPO_TRANSLATION),
	 7 => __('Form saved.', TM_EPO_TRANSLATION),
	 8 => __('Form submitted.', TM_EPO_TRANSLATION),
	 9 => sprintf( __('Form scheduled for: <strong>%1$s</strong>.', TM_EPO_TRANSLATION),
		/* translators: Publish box date format, see http://php.net/date */
		date_i18n( __( 'M j, Y @ G:i' , TM_EPO_TRANSLATION), strtotime( $post->post_date ) ) ),
	10 => __('Form draft updated.', TM_EPO_TRANSLATION)
);

$message = false;
if ( isset($_GET['message']) ) {
	$_GET['message'] = absint( $_GET['message'] );
	if ( isset($messages[$post_type][$_GET['message']]) ){
		$message = $messages[$post_type][$_GET['message']];
	}
}
?>
<div class="wrap">
	<h2><?php
	echo esc_html( $title );
	if ( isset( $post_new_file ) && current_user_can( $post_type_object->cap->create_posts ) )
		echo ' <a href="' . esc_url( admin_url( $post_new_file ) ) . '" class="add-new-h2">' . esc_html( $post_type_object->labels->add_new ) . '</a>';
	?></h2>
<?php if ( $message ) : ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php endif; ?>

	<form name="post" action="" method="post" id="post" >
		<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_ID; ?>" />
		<input type="hidden" id="hiddenaction" name="action" value="editpost" />
		<?php 
		wp_nonce_field($nonce_action); 
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content">

					<div id="titlediv">
						<div id="titlewrap">
								<label class="screen-reader-text" id="title-prompt-text" for="title"><?php echo apply_filters( 'enter_title_here', __( 'Enter title here', TM_EPO_TRANSLATION ), $post ); ?></label>
							<input type="text" name="post_title" size="30" value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" autocomplete="off" />
						</div>
					</div>
				</div>

				<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( null, "side", $post ); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( null, "normal", $post ); ?>
				</div>
				
			</div>
			<br class="clear">
		</div>
	</form>
</div>