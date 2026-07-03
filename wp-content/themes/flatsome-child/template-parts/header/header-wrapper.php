<?php
/**
 * Reusable header layout for the child theme.
 *
 * @package Flatsome_Child
 */

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$account_url = is_user_logged_in()
	? (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : admin_url('profile.php'))
	: child_theme_get_login_url();
$account_label = is_user_logged_in() ? 'My account' : 'Log In';
$register_url = child_theme_get_register_url();
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : child_theme_get_page_url('cart');
$contact_url = child_theme_get_page_url('contact-us', child_theme_get_page_url('contact'));
$about_url = child_theme_get_page_url('about-us', child_theme_get_page_url('about'));
$cart_count = child_theme_get_cart_count();
$header_nav_items = array(
	array('label' => 'Browser', 'url' => $shop_url),
	array('label' => 'T-Shirt', 'url' => child_theme_get_product_category_url('t-shirt')),
	array('label' => 'Flag', 'url' => child_theme_get_product_category_url('flag')),
	array('label' => 'Contact Us', 'url' => $contact_url),
	array('label' => 'About Us', 'url' => $about_url),
);
?>

<div class="cf-site-header">
	<div class="cf-mainbar">
		<div class="cf-container">
			<a href="#" data-open="#main-menu" data-pos="<?php echo esc_attr(flatsome_option('mobile_overlay')); ?>"
				data-bg="main-menu-overlay"
				data-color="<?php echo esc_attr(flatsome_option('mobile_overlay_color')); ?>"
				class="cf-mobile-menu-toggle" aria-label="<?php esc_attr_e('Menu', 'flatsome'); ?>"
				aria-controls="main-menu" aria-expanded="false">
				<?php echo get_flatsome_icon('icon-menu'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<a class="cf-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
				<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/site-logo.jpg')); ?>" alt="<?php bloginfo('name'); ?>" width="80" height="80">
			</a>

			<form role="search" method="get" class="cf-search-form" action="<?php echo esc_url(home_url('/')); ?>">
				<label class="screen-reader-text" for="cf-product-search">Search products:</label>
				<input id="cf-product-search" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>"
					placeholder="What are you looking for?">
				<input type="hidden" name="post_type" value="product">
				<button type="submit" aria-label="Search">
					<?php echo get_flatsome_icon('icon-search'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</form>

			<div class="cf-header-actions">
				<a class="cf-action cf-account <?php echo is_user_logged_in() ? 'is-logged-in' : 'is-logged-out'; ?>"
					href="<?php echo esc_url($account_url); ?>" aria-label="<?php echo esc_attr($account_label); ?>">
					<?php echo get_flatsome_icon('icon-user'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php echo esc_html($account_label); ?></span>
				</a>
				<?php if (!is_user_logged_in()) : ?>
					<span class="cf-auth-separator" aria-hidden="true">|</span>
					<a class="cf-action cf-register" href="<?php echo esc_url($register_url); ?>">
						<span>Sign Up</span>
					</a>
				<?php endif; ?>
				<a class="cf-action cf-mobile-search-action" href="<?php echo esc_url(home_url('/?s=&post_type=product')); ?>" aria-label="Search">
					<?php echo get_flatsome_icon('icon-search'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<a class="cf-cart" href="<?php echo esc_url($cart_url); ?>" aria-label="Cart">
					<span class="cf-cart-icon" aria-hidden="true"></span>
					<span class="cf-cart-count"><?php echo esc_html($cart_count); ?></span>
				</a>
			</div>
		</div>
	</div>

	<nav class="cf-nav" aria-label="Main menu">
		<div class="cf-container">
			<?php if (has_nav_menu('primary')) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_id'        => 'cf-primary-menu',
						'menu_class'     => '',
						'items_wrap'     => '<ul id="%1$s">%3$s</ul>',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			<?php else : ?>
				<ul>
					<?php foreach ($header_nav_items as $item) : ?>
						<li class="<?php echo child_theme_is_active_primary_menu_item((object) $item) ? 'is-active current-menu-item' : ''; ?>"><a class="header_nav" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</nav>

	<?php do_action('flatsome_header_wrapper'); ?>
</div>
