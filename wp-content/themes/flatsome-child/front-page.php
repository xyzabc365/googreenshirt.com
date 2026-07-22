<?php
/**
 * Reusable front page.
 *
 * @package Flatsome_Child
 */

get_header();

$back_to_school_url = home_url(user_trailingslashit('collections/back-to-school'));

if (!function_exists('child_theme_home_asset')) {
	function child_theme_home_asset($path)
	{
		return content_url('uploads/' . ltrim($path, '/'));
	}
}

if (!function_exists('child_theme_home_product_query')) {
	function child_theme_home_product_query($category = '', $limit = 8)
	{
		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'ignore_sticky_posts' => true,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		if ($category) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => $category,
				),
			);
		}

		return new WP_Query($args);
	}
}

if (!function_exists('child_theme_home_product_card')) {
	function child_theme_home_product_card()
	{
		$product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;

		if (!$product) {
			return;
		}

		$image = get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail');

		if (!$image && function_exists('wc_placeholder_img_src')) {
			$image = wc_placeholder_img_src('woocommerce_thumbnail');
		}

		?>
		<article class="cf-product-item">
			<a class="cf-product-item__media" href="<?php the_permalink(); ?>">
				<img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
			</a>
			<div class="cf-product-item__info">
				<a class="cf-product-item__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<div class="cf-product-item__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
			</div>
		</article>
		<?php
	}
}

if (!function_exists('child_theme_home_product_section')) {
	function child_theme_home_product_section($title, $category = '', $limit = 5)
	{
		$query = child_theme_home_product_query($category, $limit);
		$category_url = $category ? child_theme_get_product_category_url($category) : (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'));
		?>
		<section class="cf-home-section cf-products-section" aria-label="<?php echo esc_attr($title); ?>">
			<div class="cf-container">
				<div class="cf-section-heading">
					<h2><?php echo esc_html($title); ?></h2>
					<a href="<?php echo esc_url($category_url); ?>">View all</a>
				</div>

				<div class="cf-product-grid">
					<?php
					if ($query->have_posts()):
						while ($query->have_posts()):
							$query->the_post();
							child_theme_home_product_card();
						endwhile;
						wp_reset_postdata();
					else:
						?>
						<p class="cf-empty-products">No matching products found.</p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
?>

<div class="cf-home">
	<section class="cf-home-hero">
		<div class="cf-container">
			<a class="cf-home-banner" href="<?php echo esc_url($back_to_school_url); ?>">
				<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/back-to-school-hero.jpg')); ?>"
					alt="Back to school - ready to shine">
			</a>
		</div>
	</section>

	<?php
	child_theme_home_product_section('T-Shirt', 't-shirt', 5);
	child_theme_home_product_section('Flag', 'flag', 5);
	?>

	<section class="cf-promo-boxes">
		<div class="cf-container">
			<div class="cf-promo-grid">
				<div class="cf-promo-item">
					<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/promo-worldwide-shipping.png')); ?>"
						alt="Promotion Icon" loading="lazy">
					<div>
						<h3>US Shipping</h3>
						<p>We currently ship to addresses within the United States only</p>
					</div>
				</div>
				<div class="cf-promo-item">
					<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/promo-secure-payment.png')); ?>"
						alt="Promotion Icon" loading="lazy">
					<div>
						<h3>Secure Checkout</h3>
						<p>PayPal, debit & credit card accepted</p>
					</div>
				</div>
				<div class="cf-promo-item">
					<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/promo-support-247.png')); ?>"
						alt="Promotion Icon" loading="lazy">
					<div>
						<h3>30-Day Returns</h3>
						<p>Returns & exchanges accepted within 30 days</p>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<?php
get_footer();
