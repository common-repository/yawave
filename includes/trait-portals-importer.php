<?php

namespace Yawave;

trait WP_Yawave_Portals_Importer {

    /**
     * Update categories - main method to fech categories from API and push into WordPress
     * First are imported parent categories, then child categoires 
     */
    public function update_portals() {
        
        $yawave_portals = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_PORTALS);
        
        if ($yawave_portals && isset($yawave_portals->content) && is_array($yawave_portals->content) && sizeof($yawave_portals->content) > 0) {
            foreach ($yawave_portals->content as $portal) {
                $wp_portal_id = $this->save_portal($portal);
                
                $this->assign_publications_to_portal($wp_portal_id, $portal->publication_ids, $portal->id);
               
                $this->unassign_publications_from_portal($wp_portal_id, $portal->publication_ids);
                $this->update_portal_image($wp_portal_id, $portal->background_image->path);    
            }
        }
        
        return true;
    }

    /**
     * Search for publication belongs to portal and then assing it
     * @param type $portal
     */
    public function assign_publications_to_portal($wp_portal_id, $yawave_publication_ids, $yawave_portal_id) {

        $order = [];
        $i = 1;
        
        if (class_exists('SitePress')) {
            global $sitepress; 
            $list_lang = $sitepress->get_active_languages();
        }else{
            $list_lang = array(array('code' => substr(get_bloginfo('language'), 0, 2)));
        }
        
        
        asort($list_lang);
       
       
        if (!empty($yawave_publication_ids) && !empty($wp_portal_id) && is_array($yawave_publication_ids) && sizeof($yawave_publication_ids) > 0) {
           
           foreach($list_lang AS $wp_install_languages){
           
            foreach ($yawave_publication_ids as $yawave_id) {
                    
                    
                    $wp_publication = $this->get_wp_publication_by_yawave_id($yawave_id, $wp_install_languages['code']);
                    
                    if ($wp_publication && is_array($wp_publication) && isset($wp_publication[0]->ID)) {
                        
                        wp_set_post_terms($wp_publication[0]->ID, array($wp_portal_id), 'portal', true);
                        $order[] = $wp_publication[0]->ID;
                        
                        ### get portal weight
                        
                        $publication_json = get_post_meta($wp_publication[0]->ID, 'yawave_publication_json');
                        $publication_json = json_decode($publication_json[0]);
                        
                        $found_key = array_search($yawave_portal_id, array_column($publication_json->content->portals, 'id'));
                        
                        ###
                        
                        update_post_meta($wp_publication[0]->ID,"yawave_publication_portal_".$wp_portal_id."_order",$publication_json->content->portals[$found_key]->publication_weight);
                        $i++;
                        
                   }
           
                }
                
            }
        }        
    }
    
    /**
     * 
     * @param type $query
     * @param type $match
     */
    
    protected function filter_query($query, $match){
		add_filter('query', function($q) use ($query, $match) {
			if(strpos($q, $match)!==false) $q = $query;
			return $q;
		},PHP_INT_MAX);
    }

    /**
     * Unassign publications from selecetd portal
     * @param type $portal
     */
    public function unassign_publications_from_portal($wp_portal_id, $yawave_publication_ids) {
        
        
        if (class_exists('SitePress')) {
            global $sitepress; 
            $list_lang = $sitepress->get_active_languages();
        }else{
            $list_lang = array(array('code' => substr(get_bloginfo('language'), 0, 2)));
        }
        
        
        asort($list_lang);
        
        foreach($list_lang AS $wp_install_languages){
        
        
            $wp_new_publication_ids = [];
            $wp_publication_ids_to_remove_form_portal = [];
    
            $wp_current_publications = $this->get_wp_publications_by_wp_portal_id($wp_portal_id, $wp_install_languages['code']);
            
            foreach ($yawave_publication_ids as $yawave_publication_id) {
                $wp_publication = $this->get_wp_publication_by_yawave_id($yawave_publication_id, $wp_install_languages['code']);
                if (isset($wp_publication[0])) {
                    $wp_new_publication_ids[] = $wp_publication[0]->ID;
                }
            }
            foreach ($wp_current_publications as $wp_publication) {
                if (!in_array($wp_publication->ID, $wp_new_publication_ids)) {
                    $wp_publication_ids_to_remove_form_portal[] = $wp_publication->ID;
                }
            }
            foreach ($wp_publication_ids_to_remove_form_portal as $wp_publication_id) {
                $this->remove_publication_from_portal($wp_publication_id, $wp_portal_id);
            }
            
        }
        
    }

    /**
     * Remove single publicaiton from selected portal
     * @param type $wp_publication_id
     * @param type $wp_portal_id
     */
    public function remove_publication_from_portal($wp_publication_id, $wp_portal_id) {
        global $wpdb;
        $post_terms = wp_get_post_terms($wp_publication_id, 'portal', array('fields' => 'ids'));
        unset($post_terms[array_search($wp_portal_id, $post_terms)]);
        wp_set_post_terms($wp_publication_id, $post_terms, 'portal', false);  
        $wpdb->delete ($wpdb->postmeta, array('meta_key' => 'yawave_publication_portal_'.$wp_portal_id.'_order'));
    }

    /**
     * Get all publication assigned to portal
     * @param type $portal_id
     * @return type
     */
    public function get_wp_publications_by_wp_portal_id($wp_portal_id, $language = 'de') {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => array('publication', 'post'),
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'portal',
                    'field' => 'term_id',
                    'terms' => $wp_portal_id,
                )
            ),
            'meta_query' => array(
                array(
                    'key' => 'yawave_publication_language',
                    'value' => $language,
                    'compare' => '='
                )
            )
        );
        return get_posts($args);
    }

    /**
     * Save category into WP
     * @category object $category 
     * stdClass Object
     *  (
     *      [applicationId] => 5bf40b32e7ef860001486041
     *      [id] => 5c3ed1bd66c9d600012f1636
     *      [parentId] => 5c3ed1bd66c9d600012f1635
     *      [name] => Other - Find
     *      [slug] => other-find
     *      [weight] => 6
     *      [active] => 1
     *      [count] => 66
     *  )
     */
    public function save_portal($portal) {

        $wp_portal = $this->get_portal_by_yawave_id($portal->id);

        if (empty($wp_portal)) {
            $wp_portal = $this->create_wp_portal($portal->title, $portal->description, $portal->id, $portal);
            $wp_portal_id = $wp_portal['term_id'];
            $this->log("portal " . $portal->title . " created");
        } else {
            // category exist - updating if needed
            // TODO: update
            $wp_portal_id = $wp_portal->term_id;
            $this->log("portal " . $portal->title . " exist");
        }
        return $wp_portal_id;
    }

    /**
     * Create new WordPress category, then adding to metadata yawave id
     * @param type $name
     * @param type $slug
     * @param type $yawave_id
     * @return type array with term object data
     */
    public function create_wp_portal($name, $description, $yawave_id, $portal_json = '') {
        $wp_portal_attributes = array(
            'description' => $description,
        );
        $wp_portal = get_term_by('name', $name, 'portal');
        if (!$wp_portal) {
            $wp_portal = \wp_insert_term($name, 'portal', $wp_portal_attributes);
        } else {
            $wp_portal = (array) $wp_portal;
        }
        $this->log($wp_portal);
        if ($wp_portal && is_array($wp_portal) && isset($wp_portal['term_id']) && !empty($yawave_id)) {
            update_metadata('term', $wp_portal['term_id'], 'yawave_id', $yawave_id);
            update_metadata('term', $wp_portal['term_id'], 'yawave_portal_json', json_encode($portal_json));
        }
        return $wp_portal;
    }

    /**
     * Finds portals by Yawave ID and return first
     * @param string $yawave_id 
     */
    public function get_portal_by_yawave_id($yawave_id) {
        $portals = get_terms(
                array(
                    'taxonomy' => 'portal',
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
        return (!empty($portals) && is_array($portals)) ? $portals[0] : false;
    }

    /**
     * Save background image for display portal header
     * 
     * @param type $wp_portal_id
     * @param type $url
     * @return boolean
     */
    public function update_portal_image($wp_portal_id, $url) {
        if ($wp_portal_id && !empty($url)) {
            \update_metadata('term', $wp_portal_id, 'yawave_portal_url', $url);
        }
        return true;
    }

}
