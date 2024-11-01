( function( blocks, element ) {

	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var registerBlockType = wp.blocks.registerBlockType;
	
	const {createElement} = wp.element; //React.createElement
	const {InspectorControls} = wp.editor; //Block inspector wrapper
	const {TextControl,SelectControl,ServerSideRender} = wp.components; //WordPress form inputs and server-side renderer

	const iconEmail = el('svg', { width: 20, height: 20, viewBox: '0 0 1181 1134' },
		el( 'path',
			{
				d: "M583,1.1C471.4,7.1,363.1,46.4,274.5,113c-23,17.3-38.5,30.9-61.6,53.9c-30,30.1-49,53.1-71.4,86.6  c-19.9,29.7-41,69.9-54.5,103.4C69.3,401.2,56,453.6,50.5,501c-1.4,12.8-3.5,37.4-3.2,37.6c0.1,0,9,4.6,19.7,10.1  c64.6,33.2,136.6,81.6,224,150.7c46.4,36.7,62.5,49.2,71.7,55.3c9.8,6.6,13.8,8.2,15.4,6.6c2-2,3.8-54.4,7.9-218.3  c2.2-89,2.4-93.2,5-103.5c10.9-42.8,53.9-63.2,99-46.9c15.1,5.4,32.5,15.6,48,28.1c24.7,19.9,62.6,64.7,121,142.8  c16.8,22.5,43.9,58.8,60.2,80.5c45.7,61.1,67.7,87.9,89.4,109c17.7,17.1,28.2,23.3,36.1,21.1c12.6-3.5,26.1-19.2,42.7-49.6  c34.4-63.2,79.3-197.2,116.5-347.4c18.3-73.5,38.6-174,36.8-181.3c-0.8-3.2-32.2-35.5-49.3-50.9C878.8,44.4,732.9-7,583,1.1z   M1158.1,442.8c-13.4,31.4-45.6,98.2-71,147.2C1018.7,721.4,964,810.1,914.2,870c-29.8,35.9-54,51.6-76.4,49.7  c-12.8-1.1-24-8-43.7-27.2c-35.2-34.2-77.9-89.4-149.6-193.5c-58.8-85.3-70.4-101.3-91.1-124.4c-16.8-19-34.9-31.6-45.1-31.6  c-13.5,0-23.4,9.2-28.9,26.7c-5.5,17.2-11.5,84.1-17.4,194.3c-3.6,66.8-5.6,95-7.1,100.5c-4.8,17-22.3,32.3-42.4,37.1  c-5.2,1.2-9.9,1.5-18.5,1.1c-28.8-1.3-42.2-11.1-82.5-60.5c-72-88.4-147.4-165.1-204.1-207.7c-22.3-16.7-46.6-31.3-59.8-35.8  c-4.5-1.6-4.6-1.6-4.6,0.6c-0.1,1.2-9.7,121.4-21.4,267.2C9.9,1012.2,0.4,1131.6,0.5,1131.8c0.1,0.1,141.2,0.8,313.6,1.4  c335.3,1.2,332.8,1.2,371.5-3.7c164.2-20.8,311.4-113.1,402.7-252.5c47.5-72.6,77.9-156.1,88.1-242c5.4-45.2,5-99.2-1-143.5  c-2.4-18.1-10.5-59.9-11.7-61.2C1163.5,430.2,1161,435.8,1158.1,442.8z"
			}
		)
	);
	
	registerBlockType( 'yawaveblock/publications', {
		title: 'Publications',
		icon: iconEmail,
		category: 'common',
		keywords: [ 'yawave', 'publications' ],
		
		attributes:  {
			publications_tag_id: {
				default: 0
			},
			publications_cat_id: {
				default: 0
			},
			publications_portal_id: {
				default: 0
			}
		},
		
		edit(props){
			const attributes =  props.attributes;
			const setAttributes =  props.setAttributes;
	
			function changeHeading_tags(publications_tag_id){
				setAttributes({publications_tag_id});
			}
			
			function changeHeading_cats(publications_cat_id){
				setAttributes({publications_cat_id});
			}
			
			function changeHeading_portal(publications_portal_id){
				setAttributes({publications_portal_id});
			}
			
			function getSelectOptions(get_url_area) {
				
				if(get_url_area == 'tags') {
					var request_url_function = 'yawave_blocks_get_publication_tags_for_select';
				}else if(get_url_area == 'cat') {
					var request_url_function = 'yawave_blocks_get_publication_categories_for_select';
				}else if(get_url_area == 'portal') {
					var request_url_function = 'yawave_blocks_get_publication_portals_for_select';
				}
				
				var output = jQuery.ajax({
					url: '/wp-admin/admin-ajax.php?action=' + request_url_function,
					async: false,
					dataType: 'json'
				}).responseJSON;
				
				var select_options = [];
				var block_optoons = output;
				select_options.push( { value: 0, label: '---' } );
				for (let i=0; i <= (block_optoons.length-1); i++) {
					select_options.push({value:block_optoons[i]['value'], label:block_optoons[i]['label']});
				}
				
				return select_options;
				
			}
			
			var yawave_tag_items = getSelectOptions('tags');
			var yawave_cat_items = getSelectOptions('cat');
			var yawave_portal_items = getSelectOptions('portal');
			
			//Display block preview and UI
			return createElement('div', {}, [
				
				createElement( 'div', {class: 'yawave-block-publications-container'}, 'Hier werden die Publikationen angezeigt.'),
				
				//Block inspector
				  createElement( InspectorControls, {},
					  [
						  createElement(SelectControl, {
								value: attributes.publications_tag_id,
								label: 'Tag auswählen',
								onChange: changeHeading_tags,
								options: yawave_tag_items
							}),
							createElement(SelectControl, {
								  value: attributes.publications_cat_id,
								  label: 'Kategorie auswählen',
								  onChange: changeHeading_cats,
								  options: yawave_cat_items
							  }),
							  createElement(SelectControl, {
									value: attributes.publications_portal_id,
									label: 'Portal auswählen',
									onChange: changeHeading_portal,
									options: yawave_portal_items
								})
					  ]
				  )
				
				
			] )
		},
		save: function( props ) {
			return null
		},
	} );
	
})();
