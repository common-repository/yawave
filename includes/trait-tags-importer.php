<?php

namespace Yawave;

trait WP_Yawave_Tags_Importer {

    /**
     * Update tags - main method to fech tags from API and push into WordPress
     * Tags are paginated import step by step
     */
    public function update_tags($page = 1) {
    
        $yawave_tags = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_TAGS);
       
    
        if ($yawave_tags && isset($yawave_tags->content) && is_array($yawave_tags->content) && sizeof($yawave_tags->content) > 0) {
            foreach ($yawave_tags->content as $tag) {
                $this->save_tag($tag);
            }
        }
        return true;
    }
    
    public function update_single_tag($post_vars, $yawave_id = 0) {
        
        if($yawave_id != 0) {
            
            $yawave_tags_endpoint = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_TAGS);
            
            
            foreach ($yawave_tags_endpoint->content as $tag) {
                if($tag->id == $yawave_id) {
                    $yawave_tags = $tag;
                }
            }
            
        }else{
            $yawave_tags = $post_vars->content;
        }
        
        if($post_vars->event_type != 'tag:deleted'){
        
            $wp_tag_id = $this->save_tag($yawave_tags);
        
        }else{
            
            $this->delete_tag($yawave_tags);
            
        }
        
        return $wp_tag_id;
        
    }

    /**
     * Save Tag to WordPress tags
     * @param object $tag
     * stdClass Object
     * (
     *    [applicationId] => 5bf40b32e7ef860001486041
     *    [id] => 5c90e225dc678000016a5fd2
     *    [name] => Tag 40
     *    [slug] => tag-40
     *    [count] => 0
     * )
     */
    public function save_tag($tag) {
        $wp_tag_id = $this->get_tag_by_yawave_id($tag->id);
        if (empty($wp_tag_id)) {
            $wp_tag_id = $this->create_wp_tag($tag->name, $tag->slug, $tag->id, $tag);
        } else {            
            wp_update_term($wp_tag_id->term_id, 'post_tag' , array('name' => $tag->name, 'slug' => $tag->slug));
            update_term_meta($wp_tag_id->term_id, 'yawave_tag_json', json_encode($tag));   
            update_term_meta($wp_tag_id->term_id, 'yawave_tag_updateDate', date('Y-m-d H:i:s', time()));         
        }
        return $wp_tag_id;
    }

    /**
     * Create new WordPress tag, then adding to metadata yawave id
     * @param type $name
     * @param type $slug
     * @param type $yawave_id
     * @return type
     */
    public function create_wp_tag($name, $slug, $yawave_id, $tag) {
        $wp_tag_ids = wp_insert_term($name, 'post_tag', array('slug' => $slug));
        if ($wp_tag_ids && is_array($wp_tag_ids) && isset($wp_tag_ids['term_id']) && !empty($wp_tag_ids['term_id'])) {
            add_term_meta($wp_tag_ids['term_id'], 'yawave_id', $yawave_id);
            add_term_meta($wp_tag_ids['term_id'], 'yawave_tag_json', json_encode($tag));
            add_term_meta($wp_tag_ids['term_id'], 'yawave_tag_createDate', date('Y-m-d H:i:s', time()));
            return $wp_tag_ids['term_id'];
        }
        return false;
    }

    /**
     * Finds categories by Yawave ID and return first
     * @param string $yawave_id 
     */
    public function get_tag_by_yawave_id($yawave_id) {
        $tags = get_terms(
                array(
                    'taxonomy' => 'post_tag',
                    'hide_empty' => false,
                    'meta_query' => array(
                        array(
                            'key' => 'yawave_id',
                            'value' => $yawave_id,
                            'compare' => '='
                        )
                    )
                )
        );
        return (!empty($tags) && is_array($tags)) ? $tags[0] : false;
    }

    /**
     * Return number of pages of endpoint
     * @param type $tags_object
     * @return integer
     */
    public function get_number_of_pages($tags_object) {
        return (isset($tags_object->totalPages)) ? $tags_object->totalPages : 1;
    }
    
    public function delete_tag($tag) {
        
        $tag_id = $this->get_tag_by_yawave_id($tag->id);
        wp_delete_term($tag_id->term_id, $tag_id->taxonomy);
        
    }


}
