<?php
// Add custom Theme Functions here.

add_action('after_setup_theme', 'child_theme_layout_hooks', 20);
add_action('wp_enqueue_scripts', 'child_theme_enqueue_child_styles', 101);
add_action('wp_enqueue_scripts', 'child_theme_enqueue_product_scripts', 120);
add_action('init', 'child_theme_use_collections_category_base', 0);
add_action('init', 'child_theme_add_collections_rewrite_rules', 9);
add_action('init', 'child_theme_add_auth_rewrite_rules', 9);
add_action('init', 'child_theme_maybe_flush_rewrite_rules', 99);
add_filter('query_vars', 'child_theme_auth_query_vars');
add_filter('woocommerce_taxonomy_args_product_cat', 'child_theme_flat_product_category_rewrite');
add_filter('term_link', 'child_theme_flat_product_category_link', 10, 3);
add_filter('woocommerce_account_menu_items', 'child_theme_account_menu_items', 20);
add_filter('woocommerce_logout_default_redirect_url', 'child_theme_logout_redirect_url');
add_filter('woocommerce_add_to_cart_redirect', 'child_theme_buy_now_redirect');
add_filter('loop_shop_per_page', 'child_theme_collection_products_per_page', 20);
add_filter('flatsome_header_class', 'child_theme_disable_sticky_header_classes', 99);
add_filter('nav_menu_css_class', 'child_theme_primary_menu_item_classes', 10, 4);
add_action('pre_get_posts', 'child_theme_collection_archive_query', 20);
add_action('template_redirect', 'child_theme_handle_auth_actions', 0);
add_action('template_redirect', 'child_theme_render_auth_pages', 0);
add_action('template_redirect', 'child_theme_redirect_account_dashboard', 2);
add_action('template_redirect', 'child_theme_redirect_product_category_urls', 1);
add_action('flatsome_after_sidebar_menu_elements', 'child_theme_mobile_sidebar_footer', 5);
add_action('wp_footer', 'child_theme_logout_modal');

function child_theme_layout_hooks()
{
	remove_action('flatsome_footer', 'flatsome_page_footer', 10);
	remove_action('flatsome_footer', 'flatsome_go_to_top');

	add_action('flatsome_footer', 'child_theme_footer', 10);
}

function child_theme_enqueue_child_styles()
{
	$stylesheet_path = get_stylesheet_directory() . '/style.css';
	$stylesheet_ver = file_exists($stylesheet_path) ? filemtime($stylesheet_path) : wp_get_theme()->get('Version');

	wp_dequeue_style('flatsome-style');
	wp_enqueue_style('flatsome-style', get_stylesheet_uri(), array('flatsome-main'), $stylesheet_ver, 'all');
}

function child_theme_enqueue_product_scripts()
{
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	$script_path = get_stylesheet_directory() . '/assets/js/product-detail.js';

	if (!file_exists($script_path)) {
		return;
	}

	wp_enqueue_script(
		'child-theme-product-detail',
		get_stylesheet_directory_uri() . '/assets/js/product-detail.js',
		array('jquery'),
		filemtime($script_path),
		true
	);
}

function child_theme_disable_sticky_header_classes($classes)
{
	return array_values(
		array_filter(
			(array) $classes,
			function ($class) {
				return 'has-sticky' !== $class && 0 !== strpos($class, 'sticky-');
			}
		)
	);
}

function child_theme_primary_menu_item_classes($classes, $item, $args, $depth)
{
	if (empty($args->theme_location) || 'primary' !== $args->theme_location || 'cf-primary-menu' !== $args->menu_id) {
		return $classes;
	}

	if (in_array('menu-item-has-children', $classes, true)) {
		$classes[] = 'cf-has-dropdown';
	}

	if (0 === (int) $depth && in_array(strtolower($item->title), array('recipients', 'products'), true)) {
		$classes[] = 'cf-dropdown-wide';
	}

	if (0 === (int) $depth && child_theme_is_active_primary_menu_item($item)) {
		$classes[] = 'is-active';
		$classes[] = 'current-menu-item';
	}

	return array_values(array_unique($classes));
}

function child_theme_is_active_primary_menu_item($item)
{
	$item_path = child_theme_normalize_menu_path(isset($item->url) ? $item->url : '');

	if (!$item_path) {
		return false;
	}

	$current_path = child_theme_current_request_path();

	if ($current_path && $item_path === $current_path) {
		return true;
	}

	if (function_exists('is_shop') && is_shop() && in_array($item_path, array('shop', 'collections/all'), true)) {
		return true;
	}

	if (is_post_type_archive('product') && in_array($item_path, array('shop', 'collections/all'), true)) {
		return true;
	}

	if (function_exists('is_product_category') && is_product_category()) {
		$term = get_queried_object();

		if ($term instanceof WP_Term) {
			return $item_path === 'collections/' . $term->slug;
		}
	}

	return false;
}

function child_theme_current_request_path()
{
	if (empty($_SERVER['REQUEST_URI'])) {
		return '';
	}

	return child_theme_normalize_menu_path(home_url(wp_unslash($_SERVER['REQUEST_URI'])));
}

function child_theme_normalize_menu_path($url)
{
	if (!$url || '#' === $url) {
		return '';
	}

	$path = parse_url($url, PHP_URL_PATH);

	if (null === $path || false === $path) {
		return '';
	}

	$home_path = parse_url(home_url('/'), PHP_URL_PATH);

	if ($home_path && '/' !== $home_path && 0 === strpos($path, $home_path)) {
		$path = substr($path, strlen($home_path));
	}

	return trim(untrailingslashit($path), '/');
}

function child_theme_mobile_sidebar_footer()
{
	$account_url = is_user_logged_in()
		? (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : admin_url('profile.php'))
		: child_theme_get_login_url();
	$account_label = is_user_logged_in() ? 'My account' : 'Sign in';
	$contact_url = child_theme_get_page_url('contact');
	?>
	<div class="cf-mobile-sidebar-foot">
		<div class="cf-mobile-sidebar-support">
			<a href="<?php echo esc_url($contact_url); ?>">Need help ?</a>
			<p>Address: 117 S Lexington St, Harrisonville, MO 64701, United States</p>
			<p>Email: <strong>manager@googreenshirt.com</strong></p>
			<p>Phone: <strong>+1 (661) 512-3214</strong></p>
		</div>
		<a class="cf-mobile-sidebar-login"
			href="<?php echo esc_url($account_url); ?>"><?php echo get_flatsome_icon('icon-user'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html($account_label); ?></span></a>
	</div>
	<?php
}

function child_theme_use_collections_category_base()
{
	$permalinks = (array) get_option('woocommerce_permalinks', array());

	if (isset($permalinks['category_base']) && 'collections' === $permalinks['category_base']) {
		return;
	}

	$permalinks['category_base'] = 'collections';

	update_option('woocommerce_permalinks', $permalinks);
	update_option('woocommerce_queue_flush_rewrite_rules', 'yes');
}

function child_theme_flat_product_category_rewrite($args)
{
	$rewrite = isset($args['rewrite']) && is_array($args['rewrite']) ? $args['rewrite'] : array();

	$rewrite['slug'] = 'collections';
	$rewrite['with_front'] = false;
	$rewrite['hierarchical'] = false;

	$args['rewrite'] = $rewrite;

	return $args;
}

function child_theme_add_collections_rewrite_rules()
{
	add_rewrite_rule('^collections/all/page/([0-9]+)/?$', 'index.php?post_type=product&paged=$matches[1]', 'top');
	add_rewrite_rule('^collections/all/?$', 'index.php?post_type=product', 'top');
	add_rewrite_rule('^collections/([^/]+)/page/([0-9]+)/?$', 'index.php?product_cat=$matches[1]&paged=$matches[2]', 'top');
	add_rewrite_rule('^collections/([^/]+)/?$', 'index.php?product_cat=$matches[1]', 'top');
}

function child_theme_add_auth_rewrite_rules()
{
	add_rewrite_rule('^login/?$', 'index.php?child_theme_auth_page=login', 'top');
	add_rewrite_rule('^lost-password/?$', 'index.php?child_theme_auth_page=lost-password', 'top');
	add_rewrite_rule('^register/?$', 'index.php?child_theme_auth_page=register', 'top');
}

function child_theme_maybe_flush_rewrite_rules()
{
	$rewrite_version = '20260703-collections-all';

	if (get_option('child_theme_rewrite_version') === $rewrite_version) {
		return;
	}

	flush_rewrite_rules(false);
	update_option('child_theme_rewrite_version', $rewrite_version);
}

function child_theme_auth_query_vars($vars)
{
	$vars[] = 'child_theme_auth_page';
	$vars[] = 'auth_notice';

	return $vars;
}

function child_theme_get_login_url()
{
	return home_url('/login/');
}

function child_theme_get_register_url()
{
	return home_url('/register/');
}

function child_theme_get_lost_password_url()
{
	return home_url('/lost-password/');
}

function child_theme_logout_redirect_url()
{
	return child_theme_get_login_url();
}

function child_theme_buy_now_redirect($url)
{
	if (empty($_REQUEST['cf_buy_now'])) {
		return $url;
	}

	return function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : $url;
}

function child_theme_get_auth_page_from_request()
{
	$auth_page = get_query_var('child_theme_auth_page');

	if ($auth_page) {
		return $auth_page;
	}

	if (empty($_SERVER['REQUEST_URI'])) {
		return '';
	}

	$request_uri = wp_unslash($_SERVER['REQUEST_URI']);
	$path = parse_url($request_uri, PHP_URL_PATH);

	if (!$path) {
		return '';
	}

	$home_path = parse_url(home_url('/'), PHP_URL_PATH);

	if ($home_path && '/' !== $home_path && 0 === strpos($path, $home_path)) {
		$path = substr($path, strlen($home_path));
	}

	$path = trim($path, '/');

	if (in_array($path, array('login', 'lost-password', 'register'), true)) {
		return $path;
	}

	return '';
}

function child_theme_get_account_redirect_url()
{
	return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : admin_url('profile.php');
}

function child_theme_auth_redirect_with_notice($page, $notice)
{
	wp_safe_redirect(
		add_query_arg(
			'auth_notice',
			$notice,
			home_url('/' . trim($page, '/') . '/')
		)
	);
	exit;
}

function child_theme_handle_auth_actions()
{
	$auth_page = child_theme_get_auth_page_from_request();

	if (!$auth_page || 'POST' !== $_SERVER['REQUEST_METHOD']) {
		return;
	}

	if ('login' === $auth_page) {
		if (empty($_POST['child_theme_login_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['child_theme_login_nonce'])), 'child_theme_login')) {
			child_theme_auth_redirect_with_notice('login', 'Security check failed. Please try again.');
		}

		$creds = array(
			'user_login' => isset($_POST['username']) ? sanitize_text_field(wp_unslash($_POST['username'])) : '',
			'user_password' => isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '',
			'remember' => !empty($_POST['rememberme']),
		);
		$user = wp_signon($creds, is_ssl());

		if (is_wp_error($user)) {
			child_theme_auth_redirect_with_notice('login', $user->get_error_message());
		}

		wp_safe_redirect(child_theme_get_account_redirect_url());
		exit;
	}

	if ('register' === $auth_page) {
		if (empty($_POST['child_theme_register_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['child_theme_register_nonce'])), 'child_theme_register')) {
			child_theme_auth_redirect_with_notice('register', 'Security check failed. Please try again.');
		}

		$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
		$username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
		$password = isset($_POST['password']) ? (string) wp_unslash($_POST['password']) : '';

		if (!$username && $email) {
			$username = sanitize_user(current(explode('@', $email)), true);
		}

		if (!is_email($email)) {
			child_theme_auth_redirect_with_notice('register', 'Please enter a valid email address.');
		}

		if (!$username || username_exists($username)) {
			child_theme_auth_redirect_with_notice('register', 'Please choose a different username.');
		}

		if (email_exists($email)) {
			child_theme_auth_redirect_with_notice('register', 'An account already exists with this email address.');
		}

		if (strlen($password) < 8) {
			child_theme_auth_redirect_with_notice('register', 'Please use a password with at least 8 characters.');
		}

		$user_id = wp_create_user($username, $password, $email);

		if (is_wp_error($user_id)) {
			child_theme_auth_redirect_with_notice('register', $user_id->get_error_message());
		}

		wp_update_user(
			array(
				'ID' => $user_id,
				'display_name' => $username,
			)
		);

		$user = new WP_User($user_id);

		if (get_role('customer')) {
			$user->set_role('customer');
		}

		wp_set_current_user($user_id);
		wp_set_auth_cookie($user_id, true);
		wp_safe_redirect(child_theme_get_account_redirect_url());
		exit;
	}

	if ('lost-password' === $auth_page) {
		if (empty($_POST['child_theme_lost_password_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['child_theme_lost_password_nonce'])), 'child_theme_lost_password')) {
			child_theme_auth_redirect_with_notice('lost-password', 'Security check failed. Please try again.');
		}

		$user_login = isset($_POST['user_login']) ? sanitize_text_field(wp_unslash($_POST['user_login'])) : '';
		$user = is_email($user_login) ? get_user_by('email', $user_login) : get_user_by('login', $user_login);

		if ($user) {
			$reset_key = get_password_reset_key($user);

			if (is_wp_error($reset_key)) {
				child_theme_auth_redirect_with_notice('lost-password', $reset_key->get_error_message());
			}

			$reset_url = network_site_url(
				'wp-login.php?action=rp&key=' . rawurlencode($reset_key) . '&login=' . rawurlencode($user->user_login),
				'login'
			);

			wp_mail(
				$user->user_email,
				sprintf('[%s] Password reset', wp_specialchars_decode(get_option('blogname'), ENT_QUOTES)),
				"Someone requested a password reset for your account.\n\nReset your password here:\n\n" . $reset_url
			);
		}

		child_theme_auth_redirect_with_notice('lost-password', 'Password reset instructions have been sent if the account exists.');
	}
}

function child_theme_render_auth_pages()
{
	$auth_page = child_theme_get_auth_page_from_request();

	if (!$auth_page) {
		return;
	}

	if (is_user_logged_in()) {
		wp_safe_redirect(child_theme_get_account_redirect_url());
		exit;
	}

	status_header(200);
	include get_stylesheet_directory() . '/template-parts/auth/auth-page.php';
	exit;
}

function child_theme_collection_products_per_page($per_page)
{
	if ((function_exists('is_product_category') && is_product_category()) || is_post_type_archive('product')) {
		return 16;
	}

	return $per_page;
}

function child_theme_collection_archive_query($query)
{
	if (is_admin() || !$query->is_main_query()) {
		return;
	}

	$post_type = $query->get('post_type');
	$is_product_search = $query->is_search() && (
		'product' === $post_type ||
		(is_array($post_type) && in_array('product', $post_type, true))
	);

	if (!$query->is_tax('product_cat') && !$query->is_post_type_archive('product') && !$is_product_search) {
		return;
	}

	$query->set('posts_per_page', 16);

	$tax_query = $query->get('tax_query');
	$tax_query = is_array($tax_query) ? $tax_query : array();

	foreach (array('size' => 'pa_size', 'color' => 'pa_color') as $filter_key => $taxonomy) {
		if (empty($_GET['filter_' . $filter_key])) {
			continue;
		}

		$values = wp_unslash($_GET['filter_' . $filter_key]);
		$values = is_array($values) ? $values : explode(',', $values);
		$values = array_filter(array_map('sanitize_title', $values));

		if (empty($values)) {
			continue;
		}

		if (taxonomy_exists($taxonomy)) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $values,
				'operator' => 'IN',
			);
			continue;
		}

		$fallback_terms = child_theme_collection_filter_fallback_category_slugs($filter_key, $values);

		if (!empty($fallback_terms)) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field' => 'slug',
				'terms' => $fallback_terms,
				'operator' => 'IN',
			);
		}
	}

	if (!empty($tax_query)) {
		$query->set('tax_query', $tax_query);
	}

	$meta_query = $query->get('meta_query');
	$meta_query = is_array($meta_query) ? $meta_query : array();
	$min_price = isset($_GET['min_price']) && '' !== $_GET['min_price'] ? (float) sanitize_text_field(wp_unslash($_GET['min_price'])) : null;
	$max_price = isset($_GET['max_price']) && '' !== $_GET['max_price'] ? (float) sanitize_text_field(wp_unslash($_GET['max_price'])) : null;

	if (null !== $min_price || null !== $max_price) {
		$price_filter = array(
			'key' => '_price',
			'type' => 'DECIMAL(10,2)',
		);

		if (null !== $min_price && null !== $max_price) {
			$price_filter['value'] = array($min_price, $max_price);
			$price_filter['compare'] = 'BETWEEN';
		} elseif (null !== $min_price) {
			$price_filter['value'] = $min_price;
			$price_filter['compare'] = '>=';
		} else {
			$price_filter['value'] = $max_price;
			$price_filter['compare'] = '<=';
		}

		$meta_query[] = $price_filter;
	}

	if (!empty($meta_query)) {
		$query->set('meta_query', $meta_query);
	}
}

function child_theme_collection_filter_fallback_category_slugs($filter_key, $values)
{
	$map = array(
		'size' => array(
			'12x18' => array('flag'),
			'28x40' => array('flag'),
			'xs' => array('t-shirt'),
			's' => array('t-shirt'),
			'm' => array('t-shirt'),
			'l' => array('t-shirt'),
			'xl' => array('t-shirt'),
			'2xl' => array('t-shirt'),
			'3xl' => array('t-shirt'),
			'4xl' => array('t-shirt'),
			'5xl' => array('t-shirt'),
		),
		'color' => array(
			'black' => array('t-shirt'),
			'cardinal-red' => array('t-shirt'),
			'colorful' => array('flag', 't-shirt'),
			'ice-grey' => array('t-shirt'),
			'military-green' => array('t-shirt'),
			'navy' => array('t-shirt'),
			'sand' => array('t-shirt'),
			'white' => array('t-shirt'),
		),
	);

	if (empty($map[$filter_key])) {
		return array();
	}

	$terms = array();

	foreach ($values as $value) {
		if (!empty($map[$filter_key][$value])) {
			$terms = array_merge($terms, $map[$filter_key][$value]);
		}
	}

	return array_values(array_unique($terms));
}

function child_theme_flat_product_category_link($termlink, $term, $taxonomy)
{
	if ('product_cat' !== $taxonomy || empty($term->slug)) {
		return $termlink;
	}

	return home_url(user_trailingslashit('collections/' . $term->slug));
}

function child_theme_deleted_child_category_parent_map()
{
	return array(
		'albums' => 'music',
		'hoodies' => 'clothing',
		'jeans' => 'women',
		'singles' => 'music',
		't-shirts' => 'men',
		'tops' => 'women',
	);
}

function child_theme_redirect_product_category_urls()
{
	if (is_admin() || wp_doing_ajax() || empty($_SERVER['REQUEST_URI'])) {
		return;
	}

	$request_uri = wp_unslash($_SERVER['REQUEST_URI']);
	$current_path = parse_url($request_uri, PHP_URL_PATH);

	if (!$current_path) {
		return;
	}

	$original_path = '/' . trim($current_path, '/');
	$home_path = parse_url(home_url('/'), PHP_URL_PATH);

	if ($home_path && '/' !== $home_path && 0 === strpos($current_path, $home_path)) {
		$current_path = substr($current_path, strlen($home_path));
	}

	$segments = array_values(array_filter(explode('/', trim($current_path, '/'))));

	if (count($segments) < 2) {
		return;
	}

	$base = array_shift($segments);

	if (!in_array($base, array('product-category', 'collections'), true)) {
		return;
	}

	$paged = '';
	$page_position = array_search('page', $segments, true);

	if (false !== $page_position) {
		$paged = isset($segments[$page_position + 1]) ? absint($segments[$page_position + 1]) : '';
		$segments = array_slice($segments, 0, $page_position);
	}

	if (empty($segments)) {
		return;
	}

	$slug = sanitize_title(end($segments));
	$term = get_term_by('slug', $slug, 'product_cat');

	if (!$term || is_wp_error($term)) {
		$deleted_child_map = child_theme_deleted_child_category_parent_map();

		if (empty($deleted_child_map[$slug])) {
			return;
		}

		$term = get_term_by('slug', $deleted_child_map[$slug], 'product_cat');

		if (!$term || is_wp_error($term)) {
			return;
		}
	}

	$target = child_theme_get_product_category_url($term->slug);

	if ($paged) {
		$target = trailingslashit($target) . 'page/' . $paged . '/';
	}

	$target_path = parse_url($target, PHP_URL_PATH);

	if (untrailingslashit($original_path) === untrailingslashit($target_path)) {
		return;
	}

	$query_string = parse_url($request_uri, PHP_URL_QUERY);

	if ($query_string) {
		$target .= (false === strpos($target, '?') ? '?' : '&') . $query_string;
	}

	wp_safe_redirect($target, 301);
	exit;
}

function child_theme_get_page_url($slug, $fallback = '')
{
	$page = get_page_by_path($slug);

	if ($page) {
		return get_permalink($page);
	}

	return $fallback ? $fallback : home_url('/');
}

function child_theme_get_product_category_url($slug)
{
	$term = get_term_by('slug', $slug, 'product_cat');

	if ((!$term || is_wp_error($term)) && function_exists('child_theme_deleted_child_category_parent_map')) {
		$deleted_child_map = child_theme_deleted_child_category_parent_map();

		if (!empty($deleted_child_map[$slug])) {
			$term = get_term_by('slug', $deleted_child_map[$slug], 'product_cat');
		}
	}

	if ($term && !is_wp_error($term)) {
		$link = get_term_link($term);

		if (!is_wp_error($link)) {
			return $link;
		}
	}

	return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
}

function child_theme_get_cart_count()
{
	if (function_exists('WC') && WC()->cart) {
		return WC()->cart->get_cart_contents_count();
	}

	return 0;
}

function child_theme_account_menu_items($items)
{
	unset($items['dashboard'], $items['downloads']);

	return $items;
}

function child_theme_redirect_account_dashboard()
{
	if (is_admin() || wp_doing_ajax() || !function_exists('is_account_page') || !is_account_page() || !is_user_logged_in()) {
		return;
	}

	if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
		return;
	}

	wp_safe_redirect(wc_get_account_endpoint_url('orders'));
	exit;
}

function child_theme_logout_modal()
{
	if (!is_user_logged_in()) {
		return;
	}
	?>
	<div class="cf-logout-modal" id="cf-logout-modal" aria-hidden="true">
		<div class="cf-logout-modal__backdrop" data-cf-logout-close></div>
		<div class="cf-logout-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="cf-logout-modal-title">
			<button class="cf-logout-modal__close" type="button" aria-label="Close" data-cf-logout-close>&times;</button>
			<p class="cf-eyebrow">Account</p>
			<h2 id="cf-logout-modal-title">Log out?</h2>
			<p class="cf-logout-modal__text">Are you sure you want to log out of your account?</p>
			<div class="cf-logout-modal__actions">
				<button class="cf-logout-modal__cancel" type="button" data-cf-logout-close>Cancel</button>
				<a class="cf-logout-modal__confirm" id="cf-logout-confirm"
					href="<?php echo esc_url(wc_get_account_endpoint_url('customer-logout')); ?>">Confirm and log
					out</a>
			</div>
		</div>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			var modal = document.getElementById('cf-logout-modal');
			var confirmLink = document.getElementById('cf-logout-confirm');

			if (!modal || !confirmLink) {
				return;
			}

			var openModal = function (href) {
				confirmLink.setAttribute('href', href || confirmLink.getAttribute('href'));
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
				document.documentElement.classList.add('cf-logout-modal-open');
			};

			var closeModal = function () {
				modal.classList.remove('is-open');
				modal.setAttribute('aria-hidden', 'true');
				document.documentElement.classList.remove('cf-logout-modal-open');
			};

			document.addEventListener('click', function (event) {
				var logoutLink = event.target.closest('.cf-logout-trigger, .woocommerce-MyAccount-navigation-link--customer-logout a, a[href*="customer-logout"]');

				if (!logoutLink || logoutLink === confirmLink) {
					return;
				}

				event.preventDefault();
				openModal(logoutLink.getAttribute('href'));
			});

			modal.addEventListener('click', function (event) {
				if (event.target.closest('[data-cf-logout-close]')) {
					closeModal();
				}
			});

			document.addEventListener('keydown', function (event) {
				if (event.key === 'Escape' && modal.classList.contains('is-open')) {
					closeModal();
				}
			});
		});
	</script>
	<?php
}

function child_theme_footer()
{
	get_template_part('template-parts/footer/footer-custom');
}
