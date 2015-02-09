<?php 
/**
 * The template for displaying posts in the Artist post format
 *
 */

	
set_post_thumbnail_size( 50, 50, true );


get_header(); ?>

	<div class="container">
		<div class="row">
			<div id="primary" <?php bavotasan_primary_attr(); ?>>
				<?php 
				while ( have_posts() ) : the_post(); 
					$video = get_post_meta( $posts[0]->ID, 'video', true );
					$playlist = get_post_meta( $posts[0]->ID, 'playlist', true );
					$mileage = get_post_meta( $posts[0]->ID, 'mileage', true );
					
					if ( has_post_thumbnail() ) {
						$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
						$imageURL = $thumbnail['0'];
						$title = get_the_title($posts[0]->ID);
						$featuredImageHTML = <<<HTML
						<div id="artist-profile-image">
							<img src="$imageURL" alt="$title" />
						</div>
HTML;
					 
					} else {
					    $featuredImageHTML = '';
					}
?>

					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="entry-title">Artist Profile :: <?php the_title(); ?></h1>
						<?php echo $featuredImageHTML; ?>
						<div class="entry-content description clearfix">
					    	<?php the_content( __( 'Read more', 'arcade') ); ?>
					    	<br>
					    	<p><a href="/soulserenades/serenade-packages/" >Choose a Package</a></p>
						</div><!-- .entry-content -->
						<h1>View Artist Performance</h1>
						<?php echo $video; ?>
						<br />
						<h1>Artist Playlist</h1>
						<?php echo $playlist; ?>
						<hr style="border-top:1px solid #ccc;">
						<p style="font-style:italic;"><?php echo $mileage; ?></p>
						<?php get_template_part( 'content', 'footer' ); ?>
					</article><!-- #post-<?php the_ID(); ?> -->

					<div id="posts-pagination" class="clearfix">
						<h3 class="sr-only"><?php _e( 'Post navigation', 'arcade' ); ?></h3>
						<div class="previous pull-left"><?php previous_post_link( '%link', __( '&larr; %title', 'arcade' ) ); ?></div>
						<div class="next pull-right"><?php next_post_link( '%link', __( '%title &rarr;', 'arcade' ) ); ?></div>
					</div><!-- #posts-pagination -->

					<?php comments_template( '/comments-artist.php', true ); ?>

				<?php endwhile; // end of the loop. ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>



					