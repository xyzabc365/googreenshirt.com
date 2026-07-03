<?php
/**
 * Orders
 *
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<section class="cf-orders-page">
	<header class="cf-orders-heading">
		<div>
			<p class="cf-eyebrow"><?php esc_html_e( 'Order history', 'woocommerce' ); ?></p>
			<h2><?php esc_html_e( 'Your orders', 'woocommerce' ); ?></h2>
			<p><?php esc_html_e( 'Track recent purchases, review totals, and open order details when you need them.', 'woocommerce' ); ?></p>
		</div>
		<a class="cf-orders-shop" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop more', 'woocommerce' ); ?></a>
	</header>

	<?php if ( $has_orders ) : ?>
		<div class="cf-orders-list">
			<?php
			foreach ( $customer_orders->orders as $customer_order ) :
				$order      = wc_get_order( $customer_order );
				$item_count = $order ? $order->get_item_count() - $order->get_item_count_refunded() : 0;

				if ( ! $order ) {
					continue;
				}

				$actions = wc_get_account_orders_actions( $order );
				?>
				<article class="cf-order-card cf-order-card--<?php echo esc_attr( $order->get_status() ); ?>">
					<div class="cf-order-card__main">
						<div class="cf-order-card__top">
							<a class="cf-order-card__number" href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View order number %s', 'woocommerce' ), $order->get_order_number() ) ); ?>">
								<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
							</a>
							<span class="cf-order-status"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
						</div>

						<div class="cf-order-card__meta">
							<div>
								<span><?php esc_html_e( 'Placed on', 'woocommerce' ); ?></span>
								<strong>
									<?php if ( $order->get_date_created() ) : ?>
										<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
									<?php else : ?>
										<?php esc_html_e( 'Unknown', 'woocommerce' ); ?>
									<?php endif; ?>
								</strong>
							</div>
							<div>
								<span><?php esc_html_e( 'Items', 'woocommerce' ); ?></span>
								<strong><?php echo esc_html( sprintf( _n( '%s item', '%s items', $item_count, 'woocommerce' ), number_format_i18n( $item_count ) ) ); ?></strong>
							</div>
							<div>
								<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
								<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
							</div>
						</div>
					</div>

					<?php if ( ! empty( $actions ) ) : ?>
						<div class="cf-order-card__actions">
							<?php
							foreach ( $actions as $key => $action ) :
								if ( empty( $action['aria-label'] ) ) {
									$action_aria_label = sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() );
								} else {
									$action_aria_label = $action['aria-label'];
								}
								?>
								<a href="<?php echo esc_url( $action['url'] ); ?>" class="woocommerce-button<?php echo esc_attr( $wp_button_class ); ?> button <?php echo esc_attr( sanitize_html_class( $key ) ); ?>" aria-label="<?php echo esc_attr( $action_aria_label ); ?>">
									<?php echo esc_html( $action['name'] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<nav class="cf-orders-pagination woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination" aria-label="<?php esc_attr_e( 'Orders pagination', 'woocommerce' ); ?>">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
				<?php endif; ?>

				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<div class="cf-orders-empty">
			<p class="cf-eyebrow"><?php esc_html_e( 'No orders yet', 'woocommerce' ); ?></p>
			<h3><?php esc_html_e( 'Start your first order', 'woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'When you place an order, tracking details and receipts will appear here.', 'woocommerce' ); ?></p>
			<a class="cf-button cf-button-primary" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Browse products', 'woocommerce' ); ?></a>
		</div>
	<?php endif; ?>
</section>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
