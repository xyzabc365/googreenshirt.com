<?php
/**
 * Reusable single product layout.
 *
 * @package Flatsome_Child
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

if ( ! function_exists( 'child_theme_single_product_related_card' ) ) {
	function child_theme_single_product_related_card( WC_Product $related_product ) {
		$image_id  = $related_product->get_image_id();
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : '';

		if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}
		?>
		<article <?php wc_product_class( 'cf-product-item', $related_product ); ?>>
			<a class="cf-product-item__media" href="<?php echo esc_url( get_permalink( $related_product->get_id() ) ); ?>" aria-label="<?php echo esc_attr( $related_product->get_name() ); ?>">
				<?php if ( $related_product->is_on_sale() ) : ?>
					<span class="cf-product-item__badge">Sale</span>
				<?php endif; ?>
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $related_product->get_name() ); ?>" loading="lazy">
			</a>
			<div class="cf-product-item__info">
				<a class="cf-product-item__title" href="<?php echo esc_url( get_permalink( $related_product->get_id() ) ); ?>"><?php echo esc_html( $related_product->get_name() ); ?></a>
				<div class="cf-product-item__price"><?php echo wp_kses_post( $related_product->get_price_html() ); ?></div>
			</div>
		</article>
		<?php
	}
}

$description       = $product->get_description();
$short_description = $product->get_short_description();
$related_ids       = wc_get_related_products( $product->get_id(), 4 );
$primary_category  = '';

$product_categories = get_the_terms( $product->get_id(), 'product_cat' );

if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
	$primary_category = reset( $product_categories );
}
?>

<div class="cf-product-page">
	<section class="cf-product-main">
		<div class="cf-container">
			<div class="cf-product-layout">
				<div class="cf-product-gallery">
					<?php
					/**
					 * Hook: woocommerce_before_single_product_summary.
					 *
					 * @hooked woocommerce_show_product_images - 20
					 */
					do_action( 'woocommerce_before_single_product_summary' );
					?>
				</div>

				<div class="cf-product-info summary entry-summary">
					<nav class="cf-product-breadcrumb" aria-label="Breadcrumb">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<?php if ( $primary_category instanceof WP_Term ) : ?>
							<span aria-hidden="true">/</span>
							<a href="<?php echo esc_url( child_theme_get_product_category_url( $primary_category->slug ) ); ?>"><?php echo esc_html( $primary_category->name ); ?></a>
						<?php endif; ?>
					</nav>

					<h1 class="cf-product-title"><?php the_title(); ?></h1>

					<div class="cf-product-price">
						<?php woocommerce_template_single_price(); ?>
					</div>

					<?php if ( $short_description ) : ?>
						<div class="cf-product-excerpt">
							<?php echo wp_kses_post( wpautop( $short_description ) ); ?>
						</div>
					<?php endif; ?>

					<div class="cf-product-cart">
						<?php woocommerce_template_single_add_to_cart(); ?>
					</div>

					<div class="cf-product-description-panel">
						<h2>Product Description</h2>
						<div class="cf-product-description-content">
							<?php
							if ( $description ) {
								echo wp_kses_post( apply_filters( 'the_content', $description ) );
							} elseif ( $short_description ) {
								echo wp_kses_post( wpautop( $short_description ) );
							} else {
								echo '<p>This product is selected to be a warm, easy-to-use gift for many occasions throughout the year.</p>';
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php if ( ! empty( $related_ids ) ) : ?>
		<section class="cf-product-related">
			<div class="cf-container">
				<div class="cf-product-section-heading">
					<h2>Related products</h2>
				</div>
				<div class="cf-product-related-grid">
					<?php foreach ( $related_ids as $related_id ) : ?>
						<?php
						$related_product = wc_get_product( $related_id );

						if ( ! $related_product ) {
							continue;
						}

						child_theme_single_product_related_card( $related_product );
						?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</div>
