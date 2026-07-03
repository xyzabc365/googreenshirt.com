<?php
/**
 * Contact Us page.
 *
 * @package Flatsome_Child
 */

get_header();

$brand_name  = 'GooGreenShirt';
$email       = 'manager@googreenshirt.com';
$phone       = '+1 (661) 512-3214';
$hours       = 'Monday - Friday, 9:00 AM - 5:00 PM EST';
$address     = '117 S Lexington St, Harrisonville, MO 64701, United States';
$map_src     = 'https://www.google.com/maps?q=' . rawurlencode( $address ) . '&output=embed';
$has_content = have_posts();
?>

<main class="cf-contact-page">
	<div class="cf-container">
		<nav class="cf-product-breadcrumb cf-contact-breadcrumb" aria-label="Breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
			<span aria-hidden="true">/</span>
			<span>Contact Us</span>
		</nav>

		<header class="cf-contact-header">
			<h1>Contact Us</h1>
		</header>

		<section class="cf-contact-content" aria-label="Contact information">
			<p>
				At <?php echo esc_html( $brand_name ); ?>, customer satisfaction is our top priority. If you have any questions about our products, orders, shipping, returns, or need assistance with a custom request, our support team is here to help.
			</p>

			<ul class="cf-contact-list">
				<li><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
				<li><strong>Phone:</strong> <?php echo esc_html( $phone ); ?></li>
				<li><strong>Support Hours:</strong> <?php echo esc_html( $hours ); ?></li>
				<li><strong>Business Address:</strong> <?php echo esc_html( $address ); ?></li>
			</ul>

			<div class="cf-contact-map">
				<iframe
					title="<?php echo esc_attr( $brand_name . ' location' ); ?>"
					src="<?php echo esc_url( $map_src ); ?>"
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>
		</section>

		<div class="cf-contact-form-wrap">
			<?php
			if ( $has_content ) {
				while ( have_posts() ) {
					the_post();
					the_content();
				}
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();
