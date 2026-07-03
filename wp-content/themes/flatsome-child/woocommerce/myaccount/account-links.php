<?php
/**
 * Account links.
 *
 * @package Flatsome_Child
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( has_nav_menu( 'my_account' ) ) : ?>
	<?php
	echo wp_nav_menu(
		array(
			'theme_location' => 'my_account',
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 0,
			'walker'         => new FlatsomeNavSidebar(),
		)
	);
	?>
<?php elseif ( ! get_theme_mod( 'wc_account_links', 1 ) ) : ?>
	<li>Define your My Account dropdown menu in <b>Appearance > Menus</b> or enable default WooCommerce Account Endpoints.</li>
<?php endif; ?>

<?php if ( function_exists( 'wc_get_account_menu_items' ) && get_theme_mod( 'wc_account_links', 1 ) ) : ?>
	<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
		<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?>">
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
		</li>
	<?php endforeach; ?>
	<?php do_action( 'flatsome_account_links' ); ?>
	<li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--customer-logout">
		<a class="cf-logout-trigger" href="<?php echo esc_url( wc_get_account_endpoint_url( 'customer-logout' ) ); ?>"><?php esc_html_e( 'Logout', 'woocommerce' ); ?></a>
	</li>
<?php endif; ?>
