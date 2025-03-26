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
		'icon' => 'smiley',
		'description' => 'This is Product table blocks.',
		'attributes' => array(
			'categoryId' => array(
				'type' => 'string',
				'default' => ''
			),
			'numberOfProducts' => array(
				'type' => 'number',
				'default' => 10
			),
			'products' => array(
				'type' => 'array',
				'default' => array(
					
				)
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
