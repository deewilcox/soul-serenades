<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the main and #page div elements.
 *
 * @since 1.0.0
 */
$bavotasan_theme_options = bavotasan_theme_options();
?>
	</main><!-- main -->

	<footer id="footer" role="contentinfo">
		<div id="footer-content" class="container">
			<div class="row">
				<div class="copyright col-lg-12">
					<span class="pull-left"><?php printf( __( 'Copyright &copy; %s %s. All Rights Reserved.', 'arcade' ), date( 'Y' ), ' <a href="' . home_url() . '">' . get_bloginfo( 'name' ) .'</a>' ); ?></span>
					<span class="credit-link pull-right"><?php printf( __( 'Development by %s.', 'arcade' ), '<a href="https://github.com/deewilcox/soul-serenades" target="_blank" title="Dee Wilcox on Github">Dee Wilcox</a>' ); ?></span>
				</div><!-- .col-lg-12 -->
			</div><!-- .row -->
		</div><!-- #footer-content.container -->
	</footer><!-- #footer -->
</div><!-- #page -->

<?php wp_footer(); ?>
<script>
var div = document.getElementById("wc_free_gift_chosen_gift"); 
div.innerHTML = '<h2>Select Your Free Gift</h2><p>Please select your free gift, or indicate that you do not want a free gift with this purchase.</p>' + div.innerHTML;
</script>
</body>
</html>