import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, RangeControl, SelectControl } from '@wordpress/components';
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
		const { orderBy, perPage, categoryId } = attributes;
		const validOrderBy = ['date', 'id', 'title', 'price', 'popularity', 'rating']; // Define valid order options
		const order = validOrderBy.includes(orderBy) ? orderBy : 'date';
			const path = categoryId
            ? `/wc/v3/products?category=${categoryId}&orderby=${orderBy}&per_page=${perPage}`
            : `/wc/v3/products?orderby=${orderBy}&per_page=${perPage}`;
		apiFetch({ path ,method: 'GET'})
			.then((data) => {
				setProducts(data);			
			})
			.catch((error) => {
				console.error("Error fetching products:", error);
			});
	}, [attributes.orderBy, attributes.perPage, attributes.categoryId]);
	console.log(products);
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
						max={30}
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
				</PanelBody>
			</Panel>
		</InspectorControls>

		<div {...blockProps} >
			<table  className="store-blocks-product-table">
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
	
					{
					  products.length > 0 && products.map((product) => (
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
		</div>
	</div>
);
}
