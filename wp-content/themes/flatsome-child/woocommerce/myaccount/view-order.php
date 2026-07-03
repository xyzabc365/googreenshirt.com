<?php
/**
 * View Order
 *
 * @package WooCommerce\Templates
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes      = $order->get_customer_order_notes();
$item_count = $order->get_item_count() - $order->get_item_count_refunded();
$actions    = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);
$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
?>

<section class="cf-view-order-page">
	<header class="cf-view-order-hero">
		<div class="cf-view-order-hero__copy">
			<p class="cf-eyebrow"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></p>
			<h2>
				<?php
				printf(
					/* translators: %s: order number */
					esc_html__( 'Order #%s', 'woocommerce' ),
					esc_html( $order->get_order_number() )
				);
				?>
			</h2>
			<p>
				<?php
				echo wp_kses_post(
					apply_filters(
						'woocommerce_order_details_status',
						sprintf(
							/* translators: 1: order date 2: order status */
							esc_html__( 'Placed on %1$s and currently %2$s.', 'woocommerce' ),
							'<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>',
							'<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>'
						),
						$order
					)
				);
				?>
			</p>
		</div>

		<div class="cf-view-order-hero__actions">
			<a class="cf-view-order-back" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'Back to orders', 'woocommerce' ); ?></a>
			<?php foreach ( $actions as $key => $action ) : ?>
				<?php
				$action_aria_label = empty( $action['aria-label'] )
					? sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() )
					: $action['aria-label'];
				?>
				<a href="<?php echo esc_url( $action['url'] ); ?>" class="woocommerce-button<?php echo esc_attr( $wp_button_class ); ?> button <?php echo esc_attr( sanitize_html_class( $key ) ); ?>" aria-label="<?php echo esc_attr( $action_aria_label ); ?>">
					<?php echo esc_html( $action['name'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</header>

	<div class="cf-view-order-summary" aria-label="<?php esc_attr_e( 'Order summary', 'woocommerce' ); ?>">
		<div>
			<span><?php esc_html_e( 'Status', 'woocommerce' ); ?></span>
			<strong class="cf-view-order-status cf-view-order-status--<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></strong>
		</div>
		<div>
			<span><?php esc_html_e( 'Date', 'woocommerce' ); ?></span>
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

	<?php if ( $notes ) : ?>
		<section class="cf-view-order-updates">
			<h3><?php esc_html_e( 'Order updates', 'woocommerce' ); ?></h3>
			<ol class="woocommerce-OrderUpdates commentlist notes">
				<?php foreach ( $notes as $note ) : ?>
					<li class="woocommerce-OrderUpdate comment note">
						<p class="woocommerce-OrderUpdate-meta meta"><?php echo esc_html( date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ) ); ?></p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<div class="cf-view-order-details">
		<?php do_action( 'woocommerce_view_order', $order_id ); ?>
	</div>
</section>
