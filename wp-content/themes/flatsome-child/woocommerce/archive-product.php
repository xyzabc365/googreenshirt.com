<?php
/**
 * Reusable product category archive.
 *
 * @package Flatsome_Child
 */

defined( 'ABSPATH' ) || exit;

$query_post_type = get_query_var( 'post_type' );
$request_post_type = isset( $_GET['post_type'] ) ? wp_unslash( $_GET['post_type'] ) : '';

if ( is_array( $request_post_type ) ) {
	$request_post_type = reset( $request_post_type );
}

$is_product_search = is_search() && (
	'product' === $query_post_type ||
	( is_array( $query_post_type ) && in_array( 'product', $query_post_type, true ) ) ||
	'product' === sanitize_key( $request_post_type )
);
$is_shop_archive = ( function_exists( 'is_shop' ) && is_shop() ) || is_post_type_archive( 'product' );
$is_collection_archive = is_product_category();

if ( ! $is_shop_archive && ! $is_collection_archive && ! $is_product_search ) {
	include get_template_directory() . '/woocommerce/archive-product.php';
	return;
}

global $wp_query;

$shop_url      = home_url( user_trailingslashit( 'collections/all' ) );
$total         = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
$current_page  = max( 1, (int) get_query_var( 'paged' ) );
$per_page      = isset( $wp_query->query_vars['posts_per_page'] ) ? (int) $wp_query->query_vars['posts_per_page'] : 18;
$shown_first   = $total ? ( ( $current_page - 1 ) * $per_page ) + 1 : 0;
$shown_last    = $total && $per_page > 0 ? min( $total, $current_page * $per_page ) : $total;
$term          = $is_collection_archive ? get_queried_object() : null;
$term_name     = $term instanceof WP_Term ? $term->name : woocommerce_page_title( false );
$search_query  = get_search_query( false );
$category_list = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'parent'     => 0,
	)
);

if ( $is_product_search ) {
	$term_name = $search_query
		? sprintf( '%1$s search results for: "%2$s"', number_format_i18n( $total ), $search_query )
		: 'Product search results';
} elseif ( $is_shop_archive ) {
	$term_name = 'All Products';
}

if ( ! function_exists( 'child_theme_collection_product_card' ) ) {
	function child_theme_collection_product_card() {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;

		if ( ! $product ) {
			return;
		}

		$image_id           = $product->get_image_id();
		$image_url          = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : '';
		$external_image_url = get_post_meta( get_the_ID(), '_codex_demo_product_image_url', true );

		if ( ! $image_url && $external_image_url ) {
			$image_url = $external_image_url;
		}

		if ( ! $image_url && function_exists( 'wc_placeholder_img_src' ) ) {
			$image_url = wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}
		?>
		<article <?php wc_product_class( 'cf-product-item', $product ); ?>>
			<a class="cf-product-item__media" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
			</a>
			<div class="cf-product-item__info">
				<a class="cf-product-item__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<div class="cf-product-item__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			</div>
		</article>
		<?php
	}
}

if ( ! function_exists( 'child_theme_collection_result_count' ) ) {
	function child_theme_collection_result_count( $total, $first, $last ) {
		if ( 1 === $total ) {
			return 'Showing 1 product';
		}

		if ( $total <= $last ) {
			return sprintf( 'Showing all %s products', number_format_i18n( $total ) );
		}

		return sprintf(
			'Showing %1$s-%2$s of %3$s products',
			number_format_i18n( $first ),
			number_format_i18n( $last ),
			number_format_i18n( $total )
		);
	}
}

if ( ! function_exists( 'child_theme_collection_ordering' ) ) {
	function child_theme_collection_ordering() {
		$selected = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );
		$options  = array(
			'menu_order' => 'Featured',
			'popularity' => 'Best selling',
			'rating'     => 'Top rated',
			'date'       => 'Newest',
			'price'      => 'Price: low to high',
			'price-desc' => 'Price: high to low',
		);
		?>
		<form class="cf-collection-sort" method="get">
			<label class="screen-reader-text" for="cf-collection-orderby">Sort</label>
			<select id="cf-collection-orderby" name="orderby" onchange="this.form.submit()">
				<?php foreach ( $options as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $selected, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="hidden" name="paged" value="1">
			<?php wc_query_string_form_fields( null, array( 'orderby', 'submit', 'paged', 'product-page' ) ); ?>
			<button type="submit">Apply</button>
		</form>
		<?php
	}
}

if ( ! function_exists( 'child_theme_collection_current_url' ) ) {
	function child_theme_collection_current_url() {
		global $wp;

		$request = isset( $wp->request ) ? trim( $wp->request, '/' ) : '';

		return home_url( user_trailingslashit( $request ) );
	}
}

if ( ! function_exists( 'child_theme_collection_filter_values' ) ) {
	function child_theme_collection_filter_values( $key ) {
		$param = 'filter_' . $key;

		if ( empty( $_GET[ $param ] ) ) {
			return array();
		}

		$values = wp_unslash( $_GET[ $param ] );
		$values = is_array( $values ) ? $values : explode( ',', $values );

		return array_values( array_unique( array_filter( array_map( 'sanitize_title', $values ) ) ) );
	}
}

if ( ! function_exists( 'child_theme_collection_filter_url' ) ) {
	function child_theme_collection_filter_url( $key, $value ) {
		$param  = 'filter_' . $key;
		$value  = sanitize_title( $value );
		$params = wp_unslash( $_GET );

		unset( $params['paged'], $params['product-page'] );

		$current = child_theme_collection_filter_values( $key );

		if ( in_array( $value, $current, true ) ) {
			$current = array_values( array_diff( $current, array( $value ) ) );
		} else {
			$current[] = $value;
		}

		if ( empty( $current ) ) {
			unset( $params[ $param ] );
		} else {
			$params[ $param ] = implode( ',', $current );
		}

		return add_query_arg( child_theme_collection_sanitize_query_params( $params ), child_theme_collection_current_url() );
	}
}

if ( ! function_exists( 'child_theme_collection_price_url' ) ) {
	function child_theme_collection_price_url( $min_price = '', $max_price = '' ) {
		$params = wp_unslash( $_GET );

		unset( $params['paged'], $params['product-page'] );

		if ( '' === $min_price ) {
			unset( $params['min_price'] );
		} else {
			$params['min_price'] = $min_price;
		}

		if ( '' === $max_price ) {
			unset( $params['max_price'] );
		} else {
			$params['max_price'] = $max_price;
		}

		return add_query_arg( child_theme_collection_sanitize_query_params( $params ), child_theme_collection_current_url() );
	}
}

if ( ! function_exists( 'child_theme_collection_sanitize_query_params' ) ) {
	function child_theme_collection_sanitize_query_params( $params ) {
		$clean = array();

		foreach ( (array) $params as $key => $value ) {
			$key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$clean[ $key ] = array_map( 'sanitize_text_field', $value );
			} else {
				$clean[ $key ] = sanitize_text_field( $value );
			}
		}

		return $clean;
	}
}

if ( ! function_exists( 'child_theme_collection_hidden_query_fields' ) ) {
	function child_theme_collection_hidden_query_fields( $exclude = array() ) {
		$exclude = array_merge( array( 'paged', 'product-page' ), $exclude );

		foreach ( wp_unslash( $_GET ) as $key => $value ) {
			$key = sanitize_key( $key );

			if ( in_array( $key, $exclude, true ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $item ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( sanitize_text_field( $item ) ) . '">';
				}
			} else {
				echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( sanitize_text_field( $value ) ) . '">';
			}
		}
	}
}

if ( ! function_exists( 'child_theme_collection_url_with_query' ) ) {
	function child_theme_collection_url_with_query( $url, $exclude = array() ) {
		$params  = wp_unslash( $_GET );
		$exclude = array_merge( array( 'paged', 'product-page' ), $exclude );

		foreach ( $exclude as $key ) {
			unset( $params[ $key ] );
		}

		if ( empty( $params ) ) {
			return $url;
		}

		return add_query_arg( child_theme_collection_sanitize_query_params( $params ), $url );
	}
}

if ( ! function_exists( 'child_theme_collection_attribute_options' ) ) {
	function child_theme_collection_attribute_options( $attribute_name, $term = null ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		$target_name = sanitize_title( $attribute_name );
		$query_args  = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( $term instanceof WP_Term ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => (int) $term->term_id,
				),
			);
		}

		$product_ids = get_posts( $query_args );

		$options = array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			foreach ( $product->get_attributes() as $attribute ) {
				$name = sanitize_title( str_replace( 'pa_', '', $attribute->get_name() ) );

				if ( $target_name !== $name ) {
					continue;
				}

				if ( $attribute->is_taxonomy() ) {
					$values = wc_get_product_terms( $product_id, $attribute->get_name(), array( 'fields' => 'names' ) );
				} else {
					$values = $attribute->get_options();
				}

				foreach ( $values as $value ) {
					$label = trim( wp_strip_all_tags( (string) $value ) );

					if ( '' === $label ) {
						continue;
					}

					$options[ sanitize_title( $label ) ] = $label;
				}
			}
		}

		natcasesort( $options );

		return $options;
	}
}

if ( ! function_exists( 'child_theme_collection_filter_group' ) ) {
	function child_theme_collection_filter_group( $label, $key, $options, $active_values ) {
		if ( empty( $options ) ) {
			return;
		}
		?>
		<div class="cf-collection-filter-block">
			<button type="button" aria-expanded="true"><?php echo esc_html( $label ); ?> <span aria-hidden="true">&#xf102;</span></button>
			<ul>
				<?php foreach ( $options as $option_slug => $option_label ) : ?>
					<?php $is_active = in_array( $option_slug, $active_values, true ); ?>
					<li>
						<a class="cf-collection-filter-option<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( child_theme_collection_filter_url( $key, $option_slug ) ); ?>">
							<span aria-hidden="true"></span>
							<span><?php echo esc_html( $option_label ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

if ( ! function_exists( 'child_theme_collection_pagination' ) ) {
	function child_theme_collection_pagination() {
		$total   = isset( $GLOBALS['wp_query']->max_num_pages ) ? (int) $GLOBALS['wp_query']->max_num_pages : 1;
		$current = max( 1, (int) get_query_var( 'paged' ) );

		if ( $total <= 1 ) {
			return;
		}

		$pages = paginate_links(
			array(
				'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
				'current'   => $current,
				'total'     => $total,
				'prev_text' => '<span aria-hidden="true">&#8249;</span>',
				'next_text' => '<span aria-hidden="true">&#8250;</span>',
				'type'      => 'array',
				'end_size'  => 1,
				'mid_size'  => 2,
			)
		);

		if ( ! is_array( $pages ) ) {
			return;
		}
		?>
		<nav class="cf-collection-pagination" aria-label="Product pagination">
			<ul>
				<?php foreach ( $pages as $page ) : ?>
					<li><?php echo wp_kses_post( $page ); ?></li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php
	}
}

remove_action( 'flatsome_after_header', 'flatsome_category_header' );

$active_min   = isset( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : '';
$active_max   = isset( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : '';
$active_styles = child_theme_collection_filter_values( 'style' );
$active_sizes  = child_theme_collection_filter_values( 'size' );
$active_colors = child_theme_collection_filter_values( 'color' );
$filter_term   = $is_collection_archive ? $term : null;
$style_options = child_theme_collection_attribute_options( 'Style', $filter_term );
$size_options  = child_theme_collection_attribute_options( 'Size', $filter_term );
$color_options = child_theme_collection_attribute_options( 'Color', $filter_term );
$price_ranges = array(
	array( 'label' => 'All', 'min' => '', 'max' => '' ),
	array( 'label' => 'Up to $25', 'min' => '', 'max' => '25' ),
	array( 'label' => '$25 to $50', 'min' => '25', 'max' => '50' ),
	array( 'label' => '$50 to $100', 'min' => '50', 'max' => '100' ),
	array( 'label' => '$100 to $150', 'min' => '100', 'max' => '150' ),
	array( 'label' => '$150 & above', 'min' => '150', 'max' => '' ),
);

get_header( 'shop' );
?>

<main class="cf-collection-page">
	<section class="cf-collection-main">
		<div class="cf-container">
			<?php wc_print_notices(); ?>

			<div class="cf-collection-layout">
				<aside class="cf-collection-sidebar" aria-label="Product filters">
					<?php if ( $is_shop_archive ) : ?>
						<div class="cf-collection-filter-block">
							<button type="button" aria-expanded="true">Style <span aria-hidden="true">&#xf102;</span></button>
							<ul>
									<li>
										<a class="cf-collection-filter-option is-active" href="<?php echo esc_url( child_theme_collection_url_with_query( $shop_url ) ); ?>">
											<span aria-hidden="true"></span>
											<span>All</span>
										</a>
								</li>
								<?php if ( ! is_wp_error( $category_list ) ) : ?>
									<?php foreach ( $category_list as $category ) : ?>
										<li>
											<a class="cf-collection-filter-option" href="<?php echo esc_url( child_theme_collection_url_with_query( child_theme_get_product_category_url( $category->slug ) ) ); ?>">
												<span aria-hidden="true"></span>
												<span><?php echo esc_html( $category->name ); ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>

					<?php
					if ( ! $is_shop_archive ) {
						child_theme_collection_filter_group( 'Style', 'style', $style_options, $active_styles );
					}

					child_theme_collection_filter_group( 'Size', 'size', $size_options, $active_sizes );
					child_theme_collection_filter_group( 'Color', 'color', $color_options, $active_colors );
					?>

					<div class="cf-collection-filter-block">
						<button type="button" aria-expanded="true">Price <span aria-hidden="true">&#xf102;</span></button>
						<ul>
							<?php foreach ( $price_ranges as $price ) : ?>
								<?php $is_price_active = (string) $price['min'] === (string) $active_min && (string) $price['max'] === (string) $active_max; ?>
								<li>
									<a class="cf-collection-filter-option<?php echo $is_price_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( child_theme_collection_price_url( $price['min'], $price['max'] ) ); ?>">
										<span aria-hidden="true"></span>
										<span><?php echo esc_html( $price['label'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<form class="cf-collection-price-inputs" method="get" action="<?php echo esc_url( child_theme_collection_current_url() ); ?>">
							<?php child_theme_collection_hidden_query_fields( array( 'min_price', 'max_price' ) ); ?>
							<label><span>$</span><input type="number" name="min_price" value="<?php echo esc_attr( $active_min ); ?>" placeholder="Min" min="0" step="1"></label>
							<label><span>$</span><input type="number" name="max_price" value="<?php echo esc_attr( $active_max ); ?>" placeholder="Max" min="0" step="1"></label>
							<button type="submit">Go</button>
						</form>
					</div>
				</aside>

				<div class="cf-collection-results">
					<div class="cf-collection-topbar">
						<div>
							<h1><?php echo esc_html( $term_name ); ?></h1>
							<p><?php echo esc_html( child_theme_collection_result_count( $total, $shown_first, $shown_last ) ); ?></p>
						</div>
						<?php child_theme_collection_ordering(); ?>
					</div>

					<?php if ( woocommerce_product_loop() ) : ?>
						<div id="cf-collection-grid" class="cf-collection-grid" style="--cf-collection-cols: 3;">
							<?php
							while ( have_posts() ) :
								the_post();
								do_action( 'woocommerce_shop_loop' );
								child_theme_collection_product_card();
							endwhile;
							?>
						</div>
						<?php child_theme_collection_pagination(); ?>
					<?php else : ?>
						<div class="cf-collection-empty">
							<p><?php echo esc_html( $is_product_search ? 'No products matched this search.' : 'No matching products found in this category.' ); ?></p>
							<a class="cf-button cf-button-primary" href="<?php echo esc_url( $shop_url ); ?>">View all products</a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer( 'shop' );
