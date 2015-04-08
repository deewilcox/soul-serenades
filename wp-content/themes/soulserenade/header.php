<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <main>
 * and the left sidebar conditional
 *
 * @since 1.0.0
 */
?><!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<link href='http://fonts.googleapis.com/css?family=Lovers+Quarrel' rel='stylesheet' type='text/css'>
<!--[if IE]><script src="<?php echo BAVOTASAN_THEME_URL; ?>/library/js/html5.js"></script><![endif]-->
<?php wp_head(); ?>
</head>
<?php
$bavotasan_theme_options = bavotasan_theme_options();
$space_class = '';
?>
<body <?php body_class(); ?>>

	<div id="page">

		<header id="header">
			<nav id="site-navigation" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
				<h3 class="sr-only"><?php _e( 'Main menu', 'arcade' ); ?></h3>
				<a class="sr-only" href="#primary" title="<?php esc_attr_e( 'Skip to content', 'arcade' ); ?>"><?php _e( 'Skip to content', 'arcade' ); ?></a>

				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				        <span class="icon-bar"></span>
				        <span class="icon-bar"></span>
				        <span class="icon-bar"></span>
				    </button>
				</div>

				<div class="collapse navbar-collapse">
					<?php
					$menu_class = ( is_rtl() ) ? ' navbar-right' : '';
					wp_nav_menu( array( 'theme_location' => 'primary', 'container' => '', 'menu_class' => 'nav navbar-nav' . $menu_class, 'fallback_cb' => 'bavotasan_default_menu', 'depth' => 2 ) );
					?>
					<!-- Social Icons -->
					<div id="social-icon-bar" style="float:right; padding-top:12px; width:200px;">
						<a href="https://www.facebook.com/SoulSerenadesTN" title="Facebook" target="_blank"><img src="/images/Facebook.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="Facebook"></a>
						<a href="https://twitter.com/SoulSerenadesTN" title="Twitter" target="_blank"><img src="/images/Twitter.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="Twitter"></a>
						<a href="https://instagram.com/soulserenadestn/" title="Instagram" target="_blank"><img src="/images/Instagram.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="Instagram"></a>
						<a href="https://www.pinterest.com/SoulSerenadesTN/" title="Pinterest" target="_blank"><img src="/images/Pinterest.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="Pinterest"></a>
						<a href="https://plus.google.com/u/0/107838615997922935370/about" title="Google Plus" target="_blank"><img src="/images/GooglePlus.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="Google Plus"></a>
						<a href="https://www.youtube.com/channel/UCashgBEhyC-dZCv2gfti9_A/about" title="YouTube" target="_blank"><img src="/images/YouTube.png" style="float:left;height:25px;padding-right:3px;border:none!important;" alt="YouTube"></a>
					</div>
				</div>
			</nav><!-- #site-navigation -->

			 <div class="title-card-wrapper">
                <div class="title-card">
    				<div id="site-meta">
    					<h1 id="site-title" style="display:none!important;">
    						<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
    					</h1>
    					<a href="<?php echo esc_url( home_url() ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><img src="<?php echo esc_url( home_url() ); ?>/images/logo3.png" alt="Soul Serenades"></a>

    					<?php if ( $bavotasan_theme_options['header_icon'] ) { ?>
    					<i class="fa <?php echo $bavotasan_theme_options['header_icon']; ?>"></i>
    					<?php } else {
    						$space_class = ' class="margin-top"';
    					} ?>

    					<h2 id="site-description"<?php echo $space_class; ?>>
    						<?php bloginfo( 'description' ); ?>
    					</h2>

    					<a href="#" id="more-site" class="btn btn-default btn-lg"><?php _e( 'See More', 'arcade' ); ?></a>
    				</div>

    				<?php
    				// Header image section
    				custom_header_images();
    				?>
				</div>
			</div>

		</header>

		<main>