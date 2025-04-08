<?php

/**
 * Plugin Name:       Store Blocks
 * Description:       This is store blocks gutenburg pluign.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
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
	if (function_exists('wp_register_block_types_from_metadata_collection')) { // Function introduced in WordPress 6.8.
		wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
	} else {
		if (function_exists('wp_register_block_metadata_collection')) { // Function introduced in WordPress 6.7.
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
 * Server-side rendering for the block.
 */
function render_block_product_table($attributes)
{
	// Fetch WooCommerce Products
	$cate = isset($attributes['categoryId']) && !empty($attributes['categoryId']) ? intval($attributes['categoryId']) : null;
	$args = array(
		'status' => 'publish',
		'limit' => isset($attributes['perPage']) ? intval($attributes['perPage']) : 10,
		'order' => isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date',
	);

	if ($cate) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $cate,
				'operator' => 'IN',
			),
		);
	}
	$products = wc_get_products($args);
	
	// Start output buffering
	ob_start();
?>
	<table class="store-blocks-product-table">
		<thead>
			<tr>
				<th>Image</th>
				<th>Title</th>
				 <th>Category</th>
				<th>Price</th>
				<th>Quantity</th>

			</tr>
		</thead>
		<tbody>
			<?php foreach ($products as $product) : ?>
				<tr>
					<td>
						<a href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
							<?php echo $product->get_image([80, 80]); ?>
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
					<td><?php echo wc_price($product->get_price()); ?></td>
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
}

/**
 * Enqueue block assets for both frontend + backend.
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
		     "success"=> true,
			 "message" => "Product added to cart!"));
	} else {
		wp_send_json_error(array("message" => "Could not add product to cart."));
	}
}

add_action('wp_ajax_store_blocks_add_to_cart', 'store_blocks_add_to_cart');
add_action('wp_ajax_nopriv_store_blocks_add_to_cart', 'store_blocks_add_to_cart'); // Allow guests

