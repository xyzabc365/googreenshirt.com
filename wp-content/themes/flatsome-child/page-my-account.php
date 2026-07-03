<?php
/**
 * Template name: WooCommerce - My Account
 *
 * @package Flatsome_Child
 */

get_header();

$current_user = wp_get_current_user();
$account_name = $current_user && $current_user->exists() ? $current_user->display_name : '';
?>

<?php do_action( 'flatsome_before_page' ); ?>

<main class="cf-account-shell">
	<div class="cf-container">
		<?php if ( is_user_logged_in() ) : ?>
			<header class="cf-account-header">
				<div>
					<p class="cf-eyebrow">Customer account</p>
					<h1>My account</h1>
					<p>Manage your orders, saved addresses, payment methods, and account details.</p>
				</div>
			</header>

			<div class="cf-account-layout">
				<aside class="cf-account-sidebar" aria-label="Account navigation">
					<div class="cf-account-user-card">
						<div class="cf-account-avatar"><?php echo get_avatar( $current_user->ID, 72 ); ?></div>
						<div>
							<strong><?php echo esc_html( $account_name ); ?></strong>
							<span><?php echo esc_html( $current_user->user_email ); ?></span>
						</div>
					</div>

					<?php do_action( 'woocommerce_before_account_navigation' ); ?>

					<ul id="my-account-nav" class="cf-account-nav">
						<?php wc_get_template( 'myaccount/account-links.php' ); ?>
					</ul>

					<?php do_action( 'woocommerce_after_account_navigation' ); ?>
				</aside>

				<section class="cf-account-content">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>
				</section>
			</div>
		<?php else : ?>
			<div class="cf-account-guest">
				<?php while ( have_posts() ) : ?>
					<?php the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php do_action( 'flatsome_after_page' ); ?>

<?php get_footer(); ?>
