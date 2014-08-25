<?php
/**
 Template Name: Artist Profile
 */
get_header();

$video = get_post_meta( $posts[0]->ID, 'video', true );
$playlist = get_post_meta( $posts[0]->ID, 'playlist', true );
	
set_post_thumbnail_size( 50, 50, true );

if ( has_post_thumbnail() ) {
	$imageURL = the_post_thumbnail();
	$featuredImageHTML = <<<HTML
	<div id="artist-profile-image">
		$imageURL
	</div>
HTML;
 
} else {
    // the current post lacks a thumbnail
}
?>

	<div class="container">
		<div class="row">
			<div id="primary" <?php bavotasan_primary_attr(); ?>>
				<?php
				while ( have_posts() ) : the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<?php echo $featuredImageHTML; ?>
					    <div class="entry-content description clearfix">
						    <?php the_content( __( 'Read more', 'arcade') ); ?>
					    </div><!-- .entry-content -->
						<h1>View Artist Performance</h1>
						<?php echo $video; ?>
						<h1>Sample Artist Playlist</h1>
						<?php echo $video; ?>
					    <?php get_template_part( 'content', 'footer' ); ?>
					</article><!-- #post-<?php the_ID(); ?> -->

					<?php
					comments_template( '', true );
				endwhile;
				?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>

<?php get_footer(); ?>