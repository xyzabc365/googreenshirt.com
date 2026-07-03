<?php
$year = date_i18n('Y');
$about_url = child_theme_get_page_url('about-us');
$contact_url = child_theme_get_page_url('contact-us', child_theme_get_page_url('contact'));
$privacy_url = child_theme_get_page_url('privacy-policy');
$terms_url = child_theme_get_page_url('terms-of-service');
$payment_url = child_theme_get_page_url('payment-methods');
$shipping_url = child_theme_get_page_url('shipping-delivery', child_theme_get_page_url('shipping-policy'));
$cancellation_url = child_theme_get_page_url('cancellation-order-change', child_theme_get_page_url('cancellation-and-modification-policy'));
$refund_url = child_theme_get_page_url('refund', child_theme_get_page_url('return-and-refund-policy'));
$contact_information_url = child_theme_get_page_url('contact-information', $contact_url);
?>
<div class="cf-site-footer">
	<section class="cf-footer-main" aria-label="Footer">
		<div class="cf-container cf-footer-grid">
			<div class="cf-footer-column cf-footer-brand">
				<h3>Get help</h3>
				<p><a href="tel:+16615123214">+1 (661) 512-3214</a></p>
				<p><a href="mailto:manager@googreenshirt.com">manager@googreenshirt.com</a></p>
				<p>Open Mon-Fri 9:00 AM - 5:00 PM EST</p>
				<p><strong>Address:</strong> 117 S Lexington St, Harrisonville, MO 64701, United States</p>
			</div>

			<div class="cf-footer-column">
				<h3>Company</h3>
				<ul>
					<li><a href="<?php echo esc_url($about_url); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url($privacy_url); ?>">Privacy Policy</a></li>
					<li><a href="<?php echo esc_url($terms_url); ?>">Terms Of Service</a></li>
					<li><a href="<?php echo esc_url($contact_url); ?>">Contact Us</a></li>
					<li><a href="<?php echo esc_url($payment_url); ?>">Payment Methods</a></li>
				</ul>
			</div>

			<div class="cf-footer-column">
				<h3>Information</h3>
				<ul>
					<li><a href="<?php echo esc_url($shipping_url); ?>">Shipping &amp; Delivery</a></li>
					<li><a href="<?php echo esc_url($cancellation_url); ?>">Cancellation/Order Change</a></li>
					<li><a href="<?php echo esc_url($refund_url); ?>">Refund</a></li>
					<li><a href="<?php echo esc_url($contact_information_url); ?>">Contact Information</a></li>
				</ul>
			</div>

			<div class="cf-footer-column">
				<h3>Get in touch</h3>
				<p>Have a question, feedback, or a custom request? We're always happy to hear from you - just send us a
					message.</p>
				<div class="cf-footer-socials">
					<a href="https://www.facebook.com/googreenshirt" target="_blank" rel="noopener"
						aria-label="Facebook">
						<img src="<?php echo esc_url(content_url('uploads/cloudfront-assets/facebook.svg')); ?>"
							alt="Facebook" width="20" height="20">
					</a>
				</div>
			</div>
		</div>
	</section>

	<div class="cf-footer-bottom">
		<div class="cf-container">
			<p>&copy; <?php echo esc_html($year); ?> GooGreenShirt. All Rights Reserved.</p>
		</div>
	</div>
</div>
