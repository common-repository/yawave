<?php

function yawave_shortcodes_init(){	
	add_shortcode( 'yawave-publications', 'yawave_shortcode_handler_function' ); 
	add_shortcode( 'yawave-actionbuttons', 'yawave_show_action_buttons_handler' ); 
	add_shortcode( 'yawave-liveblog', 'yawave_show_liveblog_handler' ); 
}

add_action('init', 'yawave_shortcodes_init');


function yawave_shortcode_handler_function( $atts, $content, $tag ) {
	
	global $sitepress;
	
	$a = shortcode_atts( array(
		 'cat-id' 		=> 0,
		 'portal-id'	=> 0,
		 'tag-id'		=> 0,
		 'post-id'		=> 0,
		 'show-all'		=> 0,
		 'show-action-buttons' => 0,
		 'loop-design' => 'tpl1',
	), $atts );
	
	$count = get_option('posts_per_page', 10);
	$paged = get_query_var('paged') ? get_query_var('paged') : 1;
	$offset = ($paged - 1) * $count;
	
	if($a['cat-id'] != 0) {
		$post_category = $a['cat-id'];
	}else{
		$post_category = 0;
	}
	
	if($a['tag-id'] != 0) {
		$tag_ids = $a['tag-id'];
	}else{
		$tag_ids = 0;
	}
	
	if($a['show-all'] != 0) {
		$post_type = array('publication', 'post');
	}else{
		$post_type = 'post';
	}
		
	if($a['portal-id'] != 0) {
		$tax_query = array(
			 array(
			 'taxonomy' => 'portal',
			 'field' => 'term_id',
			 'terms' => $a['portal-id'] // you need to know the term_id of your term "example 1"
			  )
		   );
	}else{
		$tax_query = 0;
	}
	
	if($a['post-id'] != 0) {
		$post_id = $a['post-id'];
	}else{
		$post_id = 0;
	}
	
	if($a['cat-id'] != 0 || $a['tag-id'] != 0 || $a['portal-id'] != 0) {
		$post_type = array('publication', 'post');
	}
	
	$args = array (
		 'p' => $post_id,
		 'posts_per_page' => $count,
		 'paged' => $paged,
		 'offset' => $offset,
		 'post_type' => $post_type,
		 'post_status' => 'publish',
		 'cat' => $post_category,
		 'tag_id' => $tag_ids,
		 'tax_query' => $tax_query,
	  );
	
	if ($sitepress) {
		$current_lang = $sitepress->get_current_language();
		$default_lang = $sitepress->get_default_language();  	
		$sitepress->switch_lang($default_lang); 
	}
		  
	$wp_query = new WP_Query( $args );
	
	$entrys .= '<div class="yawave-publications-loop">';
	
	$count_loop_boxes = 1;
	
	while($wp_query->have_posts()) {
		
		$wp_query->the_post();
		
		$categorie_tag_output = '';
		$margin_for_middle_boxes = '';	
		
		### handle action buttons setup
		
		if($a['show-action-buttons'] == 1) {
		
			$buttons = get_post_meta(get_the_ID(), 'action_buttons', false);
		
			if(!empty($buttons)) {
				
				foreach($buttons[0] AS $button) {
					$action_buttons .= $button['code'].' '; 
					break;     
				}  
				
				$action_buttons = $buttons[0][0]['code'];
				
				$add_action_buttons = '<div class="yawave-single-action-buttons">'.$action_buttons.'</div>';    
				
			}else{    
				$add_action_buttons = '';    
			}
		
			$action_buttons_addon = '<div class="yawave-content-loop-action-buttons">'.$action_buttons.'</div>';
		
		}else{
			
			$action_buttons_addon = '';
		
		}
		
		###
		
		$featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
		
		if($a['loop-design'] == 'tpl1') {
		
			$entrys .= '<div class="yawave-publication-box">
							<div class="yawave-publication-image"><img src="'.$featured_img_url.'" /></div>
							<div class="yawave-title"><h2 style="margin-bottom: 0;"><a href="'.get_the_permalink().'">'.get_the_title().'</a></h2></div>
							<div class="yawave-publications-meta">'.get_the_date().'</div>
							<div class="yawave-content">'.get_the_excerpt().'</div>
							'.$action_buttons_addon.'
						</div>';
					
		}elseif($a['loop-design'] == 'tpl2') {
			
			
			
			$main_category_id = get_post_meta(get_the_id(), 'yawave_publication_main_category');
			
			if(!empty($main_category_id[0])) {
				
				$post_categories = get_the_category_by_ID($main_category_id[0]);
				$categorie_tag_output =  '<span class="yawave-category-tag">'.$post_categories.'</span>';
				
			}
			
			if($count_loop_boxes == 2) {
				$margin_for_middle_boxes = 'yawave-middle-margin';
			}
			
			$yawave_cover_image_focuspoints = get_post_meta(get_the_id(), 'yawave_publication_cover_image_focus');
			$yawave_cover_image_focuspoints = json_decode($yawave_cover_image_focuspoints[0]);
			
			$yawave_cover_image_focuspoints->x = ($yawave_cover_image_focuspoints->x) ? $yawave_cover_image_focuspoints->x : '0.00';
			$yawave_cover_image_focuspoints->y = ($yawave_cover_image_focuspoints->y) ? $yawave_cover_image_focuspoints->y : '0.00';
			
			$entrys .= '<div class="yawave-publication-box '.$margin_for_middle_boxes.'">
					<div class="yawave-publication-image focuspoint" data-x="'.$yawave_cover_image_focuspoints->x.'" data-y="'.$yawave_cover_image_focuspoints->y.'"><a href="'.get_the_permalink().'"><img src="'.$featured_img_url.'" /></a></div>
					<div class="yawave-title"><h2 style="margin-bottom: 0;"><a href="'.get_the_permalink().'">'.get_the_title().'</a></h2></div>
					
					<div class="yawave-content">'.get_the_excerpt().'</div>
					<div class="yawave-publications-meta"><div class="yawave-publication-author">by Yawave</div> <div class="yawave-publication-date">on '.get_the_date().'</div></div>
					<div class="yawave-categories-tags">'.$categorie_tag_output.'</div>
					'.$action_buttons_addon.'
				</div>';
			
			$count_loop_boxes++;
			
			if($count_loop_boxes == 4) {
				$count_loop_boxes = 1;
			}
			
		}elseif($a['loop-design'] == 'tpl3') {
			
			$yawave_cover_title = get_post_meta(get_the_id(), 'yawave_cover_title', true);
			$yawave_cover_description = get_post_meta(get_the_id(), 'yawave_cover_description', true);
			
			$entrys .= '<div class="yawave-publication-box">
				<div class="yawave-publication-image"><a href="'.get_the_permalink().'"><img src="'.$featured_img_url.'" /></a></div>
				<div class="yawave-title"><h2><a href="'.get_the_permalink().'">'.$yawave_cover_description.'</a></h2></div>
				<div class="yawave-publications-meta">'.get_the_date().'</div>
				<div class="yawave-content">'.$yawave_cover_title.'</div>
			</div>';
			
		}
	
	}
	
	wp_reset_postdata();
	
	$entrys .= '<nav class="pagination">'.pagination_bar( $wp_query ).'</nav>';
	
	$entrys .= '</div>';
	
	return $entrys;
	
}

function pagination_bar( $custom_query ) {

	$total_pages = $custom_query->max_num_pages;
	$big = 999999999; // need an unlikely integer

	if ($total_pages > 1){
		$current_page = max(1, get_query_var('paged'));

		return paginate_links(array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => $current_page,
			'total' => $total_pages,
		));
	}
}

function yawave_show_action_buttons_handler($atts, $content, $tag) {
	
	$a = shortcode_atts( array(
		 'post-id'		=> 0,
	), $atts );
	
	if($a['post-id'] != 0) {
		$post_id = $a['post-id'];
	}else{
		$post_id = get_the_ID();
	}
	
	$buttons = get_post_meta($post_id, 'yawave_publication_action_buttons', false);
	
	if($buttons) {
	
		$count_all_buttons = count($buttons[0]);
		$n = 1;
		$action_buttons = '';
	  	
	  	if(!empty($buttons)) {
			foreach($buttons[0] AS $button) {
		  	if($count_all_buttons > 1 && $n == 1) {
			  	$button['code'] = str_replace("class='btn btn-default'", "class='btn btn-default yawave-first-action-button'", $button['code']);
		  	}
		  	$action_buttons .= $button['code'].' ';  
		  	$n++;    
			}    
			$add_action_buttons = '<div class="yawave-single-action-buttons">'.$action_buttons.'</div>';    
	  	}else{    
			$add_action_buttons = '';    
	  	}
	  	
	  	$return = '<div id="yawave-actionbuttons">'.$add_action_buttons.'</div>';
		
		return $return;
	
	}else{
		return false;
	}
	
}

