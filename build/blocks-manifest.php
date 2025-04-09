<?php
// This file is generated. Do not modify it manually.
return array(
	'product-table' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'store-blocks/product-table',
		'title' => 'Product Table',
		'version' => '0.1.0',
		'category' => 'Woocommerce',
		'icon' => 'editor-table',
		'description' => 'A custom Gutenberg plugin that displays WooCommerce products in a responsive, customizable product table block.',
		'attributes' => array(
			'categoryId' => array(
				'type' => 'string',
				'default' => ''
			),
			'perPage' => array(
				'type' => 'number',
				'default' => 10
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'date'
			),
			'order' => array(
				'type' => 'string',
				'default' => 'desc'
			),
			'showTableCaption' => array(
				'type' => 'boolean',
				'default' => false
			),
			'tableCaption' => array(
				'type' => 'string',
				'default' => 'Product Table'
			),
			'captionFontSize' => array(
				'type' => 'string',
				'default' => '16px'
			),
			'captionColor' => array(
				'type' => 'string',
				'default' => '#000000'
			),
			'captionSpacing' => array(
				'type' => 'string',
				'default' => '10px'
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'store-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
