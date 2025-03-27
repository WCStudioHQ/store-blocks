import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

import './editor.scss';
export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
    const [categories, setCategories] = useState([]);
	console.log(attributes);
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

	return (
		<div {...blockProps}>
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
						  	label='Product Per Page'
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
								{ label: 'ASC', value: 'ASC' },
								{ label: 'DESC', value: 'DESC' },
							]}
							onChange={(orderBy) => setAttributes({ orderBy })}
						/>
						 
					</PanelBody>
				</Panel>
			</InspectorControls>

			<h2>hello</h2>
		</div>
	);
}
