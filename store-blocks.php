<?php

/**
 * Plugin Name:       Store Blocks
 * Plugin URI:        https://github.com/WCStudioHQ/store-blocks
 * Description:       A custom Gutenberg plugin that displays WooCommerce products in a responsive, customizable product table block.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            WC Studio
 * Author URI:        https://wcstudio.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       store-blocks
 *
 * @package CreateBlock
 */

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_store_blocks_init()
{
	if (function_exists('wp_register_block_types_from_metadata_collection')) {
		wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
	} else {
		if (function_exists('wp_register_block_metadata_collection')) {
			wp_register_block_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
		}
		$manifest_data = require __DIR__ . '/build/blocks-manifest.php';

		foreach (array_keys($manifest_data) as $block_type) {
			$function_name = str_replace('-', '_', $block_type);
			$function_name = "render_block_{$function_name}";
			register_block_type(
				__DIR__ . "/build/{$block_type}",

				array(
					'render_callback' => $function_name,
				)
			);
		}
		add_action('enqueue_block_assets', 'store_blocks_enqueue_block_assets');
	}
}
add_action('init', 'create_store_blocks_init');

/**
 * Server-side render callback for the product table block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML.
 */
function render_block_product_table($attributes)
{
	$showCaption = isset($attributes['showTableCaption']) ? $attributes['showTableCaption'] : false;
	$captionText = isset($attributes['tableCaption']) ? $attributes['tableCaption'] : '';
	$captionFontSize = isset($attributes['captionFontSize']) ? $attributes['captionFontSize'] : '20px';
	$captionFontColor = isset($attributes['captionColor']) ? $attributes['captionColor'] : '';
	$captionSpacing = isset($attributes['captionSpacing']) ? $attributes['captionSpacing'] : '10px';
	$cate = isset($attributes['categoryId']) && !empty($attributes['categoryId']) ? intval($attributes['categoryId']) : null;
	$args = array(
		'status' => 'publish',
		'limit' => isset($attributes['perPage']) ? intval($attributes['perPage']) : 10,
		'orderby' => isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date',
		'order' => isset($attributes['order']) ? $attributes['order'] : 'ASC',
	);

	if ($cate) {
		$args['product_category_id'] = array($cate);
	}
	$products = wc_get_products($args);

	if (!empty($products)) {
		ob_start();
?>
		<table class="store-blocks-product-table">

			<?php if ($showCaption) : ?>
				<caption style="font-size: <?php echo esc_attr($captionFontSize); ?>; color: <?php echo esc_attr($captionFontColor); ?>; margin-bottom: <?php echo esc_attr($captionSpacing); ?>;">
					<?php echo esc_html($captionText); ?>
				</caption>
			<?php endif; ?>
			<thead>
				<tr>
					<th><?php echo esc_html__('Image', 'store-blocks'); ?></th>
					<th><?php echo esc_html__('Title', 'store-blocks'); ?></th>
					<th><?php echo esc_html__('Category', 'store-blocks'); ?></th>
					<th><?php echo esc_html__('Price', 'store-blocks'); ?></th>
					<th><?php echo esc_html__('Quantity', 'store-blocks'); ?></th>

				</tr>
			</thead>
			<tbody>
				<?php foreach ($products as $product) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
								<?php echo wp_kses_post($product->get_image([60, 60])); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
								<?php echo esc_html($product->get_name()); ?>
							</a>
						</td>

						<td>
							<?php
							$categories = wp_strip_all_tags($product->get_categories(', '));
							echo esc_html($categories);
							?>
						</td>
						<td><?php echo wp_kses_post(wc_price($product->get_price())); ?></td>
						<td class="">
							<input type="number" class="quantity" name="quantity" value="1" min="1">
							<button class="add-to-cart-btn"
								data-product_id="<?php echo esc_attr($product->get_id()); ?>"
								data-product_name="<?php echo esc_attr($product->get_name()); ?>">
								<span class="dashicons dashicons-cart"></span>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
<?php
		return ob_get_clean();
	} else {
		return '<p>' . esc_html__('No products found.', 'store-blocks') . '</p>';
	}
}

/**
 * Enqueue block assets for both frontend + backend.
 * @since 0.1.0
 * @return void
 */
function store_blocks_enqueue_block_assets()
{
	wp_register_script(
		'store-blocks-view-script',
		plugins_url('build/product-table/view.js', __FILE__),
		array('jquery'),
		filemtime(plugin_dir_path(__FILE__) . 'build/product-table/view.js'),
		true
	);

	wp_localize_script('store-blocks-view-script', 'storeBlocksData', [
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('store_blocks_add_to_cart')
	]);

	wp_enqueue_script('store-blocks-view-script');
	wp_enqueue_style(
		'store-blocks-editor-style',
		plugins_url('build/product-table/index.css', __FILE__),
		array(),
		filemtime(plugin_dir_path(__FILE__) . 'build/product-table/index.css')
	);
}
/**
 * AJAX handler for adding product to cart.
 * @since 0.1.0
 * @return void
 */
function store_blocks_add_to_cart()
{

	if (!check_ajax_referer('store_blocks_add_to_cart', 'nonce', false)) {
		wp_send_json_error(array("message" => "Nonce verification failed."));
		return;
	}
	if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
		wp_send_json_error(array("message" => "Invalid product, quantity"));
	}

	$product_id = intval($_POST['product_id']);
	$quantity = intval($_POST['quantity']);

	if ($quantity <= 0) {
		$quantity = 1;
	}

	$added = WC()->cart->add_to_cart($product_id, $quantity);

	if ($added) {
		wp_send_json_success(array(
			"success" => true,
			"message" => "Product added to cart!"
		));
	} else {
		wp_send_json_error(array("message" => "Could not add product to cart."));
	}
}

add_action('wp_ajax_store_blocks_add_to_cart', 'store_blocks_add_to_cart');
add_action('wp_ajax_nopriv_store_blocks_add_to_cart', 'store_blocks_add_to_cart'); // Allow guests
