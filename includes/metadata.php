<?php
namespace Yawave;

// Save the field
/**
 * Display Yawave ID in category and tag edit screen, no edit allowed.
 * @param type $term
 */

function yw_display_external_id( $term ){
	if (!in_array($term->taxonomy, ['category','portal','post_tag'])) return;
	$term_id = $term->term_id;
	$term_meta = get_term_meta($term_id, 'yawave_id', true );
	$term_json = get_term_meta($term_id, 'yawave_json', true );
	$term_createDate = get_term_meta($term_id, 'yawave_createDate', true );
	$term_updateDate = get_term_meta($term_id, 'yawave_updateDate', true );
?>
	<tr class="form-field">
		<th scope="row">
			<label for="term_meta[featured]"><?php echo _e('Yawave internal ID') ?></label>
			<td>
				<?php echo (!empty($term_meta)) ? $term_meta : "-"; ?>
			</td>
		</th>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="term_meta[featured]"><?php echo _e('Yawave JSON') ?></label>
			<td><textarea><?=$term_json?></textarea></td>
		</th>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="term_meta[featured]"><?php echo _e('Yawave create date') ?></label>
			<td><textarea><?=$term_createDate?></textarea></td>
		</th>
	</tr>
	<tr class="form-field">
		<th scope="row">
			<label for="term_meta[featured]"><?php echo _e('Yawave update date') ?></label>
			<td><textarea><?=$term_updateDate?></textarea></td>
		</th>
	</tr>
<?php
}

add_action( 'category_edit_form_fields', 'Yawave\yw_display_external_id' );
add_action( 'edit_tag_form_fields', 'Yawave\yw_display_external_id' );



if ( ! function_exists('publication_post_type') ) {

// Register Custom Post Type
function publication_post_type() {

	$labels = array(
		'name'                  => __( 'Yawave publications', 'yawave' ),
		'singular_name'         => __( 'Publication', 'yawave' ),
		'menu_name'             => __( 'Publications', 'yawave' ),
		'name_admin_bar'        => __( 'Publication', 'yawave' ),
		'archives'              => __( 'Item Archives', 'yawave' ),
		'attributes'            => __( 'Item Attributes', 'yawave' ),
		'parent_item_colon'     => __( 'Parent Item:', 'yawave' ),
		'all_items'             => __( 'All Items', 'yawave' ),
		'add_new_item'          => __( 'Add New Item', 'yawave' ),
		//'add_new'               => __( 'Add New', 'yawave' ),
		'new_item'              => __( 'New Item', 'yawave' ),
		'edit_item'             => __( 'Edit Item', 'yawave' ),
		'update_item'           => __( 'Update Item', 'yawave' ),
		'view_item'             => __( 'View Item', 'yawave' ),
		'view_items'            => __( 'View Items', 'yawave' ),
		'search_items'          => __( 'Search Item', 'yawave' ),
		'not_found'             => __( 'Not found', 'yawave' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'yawave' ),
		'featured_image'        => __( 'Featured Image', 'yawave' ),
		'set_featured_image'    => __( 'Set featured image', 'yawave' ),
		'remove_featured_image' => __( 'Remove featured image', 'yawave' ),
		'use_featured_image'    => __( 'Use as featured image', 'yawave' ),
		'insert_into_item'      => __( 'Insert into item', 'yawave' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'yawave' ),
		'items_list'            => __( 'Items list', 'yawave' ),
		'items_list_navigation' => __( 'Items list navigation', 'yawave' ),
		'filter_items_list'     => __( 'Filter items list', 'yawave' ),
	);
	$args = array(
		'label'                 => __( 'Publication', 'yawave' ),
		'description'           => __( 'Publication Description', 'yawave' ),
		'labels'                => $labels,
				'show_in_rest'          => true,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields', 'excerpt' ),
				 //'supports' => array('editor'),
		'taxonomies'            => array( 'category', 'post_tag', 'portals' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'menu_icon' => MY_PLUGIN_PATH . 'assets/img/menue-icon.png'
	);
	register_post_type( 'publication', $args );

}
add_action( 'init', 'Yawave\publication_post_type', 0 );

}

if ( ! function_exists( 'portal_taxonomy' ) ) {

// Register Custom Taxonomy
function portal_taxonomy() {

	$labels = array(
		'name'                       => __( 'Portals', 'yawave' ),
		'singular_name'              => __( 'Portal', 'yawave' ),
		'menu_name'                  => __( 'Portals', 'yawave' ),
		'all_items'                  => __( 'All Items', 'yawave' ),
		'parent_item'                => __( 'Parent Item', 'yawave' ),
		'parent_item_colon'          => __( 'Parent Item:', 'yawave' ),
		'new_item_name'              => __( 'New Item Name', 'yawave' ),
		'add_new_item'               => __( 'Add New Item', 'yawave' ),
		'edit_item'                  => __( 'Edit Item', 'yawave' ),
		'update_item'                => __( 'Update Item', 'yawave' ),
		'view_item'                  => __( 'View Item', 'yawave' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'yawave' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'yawave' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'yawave' ),
		'popular_items'              => __( 'Popular Items', 'yawave' ),
		'search_items'               => __( 'Search Items', 'yawave' ),
		'not_found'                  => __( 'Not Found', 'yawave' ),
		'no_terms'                   => __( 'No items', 'yawave' ),
		'items_list'                 => __( 'Items list', 'yawave' ),
		'items_list_navigation'      => __( 'Items list navigation', 'yawave' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'portal', array( 'publication' ), $args );
		
		$labels = array(
		'name'                       => __( 'Yawave Type', 'yawave' ),
		'singular_name'              => __( 'Type', 'yawave' ),
		'menu_name'                  => __( 'Types', 'yawave' ),
		'all_items'                  => __( 'All Items', 'yawave' ),
		'parent_item'                => __( 'Parent Item', 'yawave' ),
		'parent_item_colon'          => __( 'Parent Item:', 'yawave' ),
		'new_item_name'              => __( 'New Item Name', 'yawave' ),
		'add_new_item'               => __( 'Add New Item', 'yawave' ),
		'edit_item'                  => __( 'Edit Item', 'yawave' ),
		'update_item'                => __( 'Update Item', 'yawave' ),
		'view_item'                  => __( 'View Item', 'yawave' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'yawave' ),
		'add_or_remove_items'        => __( 'Add or remove items', 'yawave' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'yawave' ),
		'popular_items'              => __( 'Popular Items', 'yawave' ),
		'search_items'               => __( 'Search Items', 'yawave' ),
		'not_found'                  => __( 'Not Found', 'yawave' ),
		'no_terms'                   => __( 'No items', 'yawave' ),
		'items_list'                 => __( 'Items list', 'yawave' ),
		'items_list_navigation'      => __( 'Items list navigation', 'yawave' ),
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'media_type', array( 'publication' ), $args );

}
add_action( 'init', 'Yawave\portal_taxonomy', 0 );

}


if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Publications Filters settings',
		'menu_title'	=> 'Publications Filters settings',
		'menu_slug' 	=> 'publications-filters-settings',
		'capability'	=> 'edit_posts'
	));
	
}

### add shortcode column in portal edit page

add_filter('manage_edit-portal_columns', function ( $columns ) {
	$columns['shortcode'] = 'Shortcode';
	return $columns;
});


add_filter('manage_portal_custom_column', function ( $content, $name , $term_id ) {
	$content .= '<input type="text" value="[yawave-publications portal-id=\''.$term_id.'\']" />';
	return $content;
}, 10, 3);

### add shortcode column in tags edit page

add_filter('manage_edit-post_tag_columns', function ( $columns ) {
	$columns['shortcode'] = 'Shortcode';
	return $columns;
});


add_filter('manage_post_tag_custom_column', function ( $content, $name , $tag_id ) {
	$content .= '<input type="text" value="[yawave-publications tag-id=\''.$tag_id.'\']" />';
	return $content;
}, 10, 3);

### add shortcode column in kategorien edit page

add_filter('manage_edit-category_columns', function ( $columns ) {
	$columns['shortcode'] = 'Shortcode';
	return $columns;
});


add_filter('manage_category_custom_column', function ( $content, $name , $cat_id ) {
	$content .= '<input type="text" value="[yawave-publications cat-id=\''.$cat_id.'\']" />';
	return $content;
}, 10, 3);

### add shortcode column in publication edit page

add_filter('manage_publication_posts_columns', function ( $columns ) {
	$columns['shortcode'] = 'Shortcode';
	return $columns;
});

add_filter('manage_publication_posts_custom_column', function ( $column, $post_id ) {	
	if($column == 'shortcode') {
		echo '<input type="text" value="[yawave-publications post-id=\''.$post_id.'\']" />';
	}	
}, 10, 2);