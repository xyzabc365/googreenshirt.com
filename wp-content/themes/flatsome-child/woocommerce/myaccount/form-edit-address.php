<?php
/**
 * Edit address form.
 *
 * @package Flatsome_Child
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? esc_html__( 'Billing address', 'woocommerce' ) : esc_html__( 'Shipping address', 'woocommerce' );
$intro      = ( 'billing' === $load_address )
	? 'Keep your billing details accurate for faster checkout and order records.'
	: 'Choose where your orders should be delivered by default.';

do_action( 'woocommerce_before_edit_account_address_form' );
?>

<?php if ( ! $load_address ) : ?>
	<div class="cf-account-address-overview">
		<?php wc_get_template( 'myaccount/my-address.php' ); ?>
	</div>
<?php else : ?>

	<section class="cf-account-address-page">
		<div class="cf-account-address-heading">
			<div>
				<p class="cf-eyebrow">Address book</p>
				<h2><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ) ); ?></h2>
				<p><?php echo esc_html( $intro ); ?></p>
			</div>
			<a class="cf-account-address-back" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>">Back to addresses</a>
		</div>

		<form class="cf-account-address-form" method="post" novalidate>
			<div class="woocommerce-address-fields">
				<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

				<div class="woocommerce-address-fields__field-wrapper cf-account-address-grid">
					<?php
					foreach ( $address as $key => $field ) {
						woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
					}
					?>
				</div>

				<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

				<div class="cf-account-address-actions">
					<button type="submit" class="button cf-account-address-save<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>">
						<?php esc_html_e( 'Save address', 'woocommerce' ); ?>
					</button>
					<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
					<input type="hidden" name="action" value="edit_address" />
				</div>
			</div>
		</form>
	</section>

<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>
