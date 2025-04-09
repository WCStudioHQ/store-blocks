import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, RangeControl, SelectControl, ToggleControl,FontSizePicker,ColorPicker, TextControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const [categories, setCategories] = useState([]);
	const [products, setProducts] = useState([]);
	useEffect(() => {
		apiFetch({ path: '/wp/v2/product_cat?per_page=100' })
			.then((categories) => {
				const categoryOptions = categories.map((category) => ({
					label: category.name,
					value: category.id,
				}));

				setCategories(categoryOptions);
			})
			.catch((error) => console.error('Error fetching categories:', error));
	}, []);


	useEffect(() => {
		const { order, orderBy, perPage, categoryId } = attributes;
		const validOrderBy = ['date', 'id', 'title', 'price', 'popularity', 'rating'];
		const orderByFilter = validOrderBy.includes(orderBy) ? orderBy : 'date';
		const path = categoryId
			? `/wc/v3/products?category=${categoryId}&orderby=${orderByFilter}&order=${order}&per_page=${perPage}`
			: `/wc/v3/products?orderby=${orderByFilter}&order=${order}&per_page=${perPage}`;

		apiFetch({ path, method: 'GET' })
			.then((data) => {
				setProducts(data);
			})
			.catch((error) => {
				console.error("Error fetching products:", error);
			});

	}, [attributes.order, attributes.orderBy, attributes.perPage, attributes.categoryId]);

	return (
		<div>
			<InspectorControls>
				<Panel header="Product Table Settings">
					<PanelBody>
						<SelectControl
							label="Category"
							value={attributes.categoryId}
							options={[
								{ label: 'All Categories', value: '' },
								...categories,
							]}
							onChange={(categoryId) => setAttributes({ categoryId })}
						/>
						<RangeControl
							label="Product Per Page"
							value={attributes.perPage}
							onChange={(perPage) => setAttributes({ perPage })}
							min={1}
							max={100}
							step={1}
						/>
						<SelectControl
							label="Order by"
							value={attributes.orderBy}
							options={[
								{ label: 'ID', value: 'id' },
								{ label: 'Date', value: 'date' },
								{ label: 'Title', value: 'title' },
								{ label: 'Price', value: 'price' },
								{ label: 'Popularity', value: 'popularity' },
								{ label: 'Rating', value: 'rating' },
							]}
							onChange={(orderBy) => setAttributes({ orderBy })}
						/>
						<SelectControl
							label="Order"
							value={attributes.order}
							options={[
								{ label: 'Asc', value: 'asc' },
								{ label: 'Desc', value: 'desc' },

							]}
							onChange={(order) => setAttributes({ order })}
						/>
					</PanelBody>
				</Panel>

				<Panel header="Table Caption Settings">
					<PanelBody>
						<ToggleControl
							label="Show Caption"
							checked={attributes.showTableCaption}
							onChange={(showTableCaption) => setAttributes({ showTableCaption })}
						/>
						{attributes.showTableCaption && (
							<div>
								<TextControl
									label="Caption Text"
									value={attributes.tableCaption}
									onChange={(tableCaption) => setAttributes({ tableCaption })}
									placeholder="Enter caption text"
								/>
					
								<p style={{padding:"4px 0px"}}>{__('Font Size', 'store-blocks')}</p>
								<FontSizePicker
									value={attributes.captionFontSize}
									onChange={(captionFontSize) => setAttributes({ captionFontSize })}
								/>
								<p style={{padding:"10px 0px"}}>{__('Color', 'store-blocks')}</p>
								<ColorPicker
										label="Caption Text Color"
									color={attributes.captionColor}
									onChangeComplete={(value) => setAttributes({ captionColor: value.hex })}
									disableAlpha
								/>
								<RangeControl
									label={__('Caption Spacing', 'store-blocks')}
									value={attributes.captionSpacing}
									onChange={(captionSpacing) => setAttributes({ captionSpacing })}
									min={0}
									max={50}
									step={1}
									help={__('Set the spacing between caption and table', 'store-blocks')}
								/>
							</div>
						)}

					</PanelBody>
				</Panel>
			</InspectorControls>

			<div {...blockProps} >
				{ products.length > 0 ? (
					<table className="store-blocks-product-table">
						{ attributes.showTableCaption && (
							<caption style={{ fontSize: attributes.captionFontSize, color: attributes.captionColor, marginBottom: `${attributes.captionSpacing}px` }}>
								{ attributes.tableCaption}
							</caption>
						)}
						<thead>
							<tr>
								<th>{__('Image', 'store-blocks')}</th>
								<th>{__('Title', 'store-blocks')}</th>
								<th>{__('Category', 'store-blocks')}</th>
								<th>{__('Price', 'store-blocks')}</th>
								<th>{__('Quantity', 'store-blocks')}</th>
							</tr>
						</thead>
						<tbody>
							{products.map((product) => (
								<tr key={product.id}>
									<td>
										<img src={product?.images[0]?.src} alt={product?.name || 'Product'} />
									</td>
									<td>{product?.name}</td>
									<td>{product.categories?.map((cat) => cat.name).join(', ') || 'N/A'}</td>
									<td dangerouslySetInnerHTML={{ __html: product.price_html || '' }} />
									<td>{product.stock_quantity || 1}</td>
								</tr>
							))}
						</tbody>
					</table>
				) : (
					<p>{__('No products found.', 'store-blocks')}</p>
				)}
			</div>
		</div>
	);
}
