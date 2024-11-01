<?php

namespace Yawave;

trait WP_Yawave_Categories_Importer {

    /**
     * Update categories - main method to fech categories from API and push into WordPress
     * First are imported parent categories, then child categoires 
     */
    public function update_categories($page=0, $trid_mode = 0) {
        $yawave_categories = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_CATEGORIES . '?page=' . $page);
        
        if (class_exists('SitePress')) {
            global $sitepress, $wpdb; 
            $list_lang = $sitepress->get_active_languages();
        }else{
            global $wpdb;
            $list_lang = array(array('code' => substr(get_bloginfo('language'), 0, 2)));
        }
        
        
        asort($list_lang);
        
        
        if ($yawave_categories && isset($yawave_categories->content) && is_array($yawave_categories->content) && sizeof($yawave_categories->content) > 0) {
            
             foreach ($yawave_categories->content as $category) {
                 
                 $trid_id = 0;
                 
                 foreach($list_lang AS $wp_install_languages){
                  
                  $array = json_decode(json_encode($category->name), true);
                  
                  if(empty($array[$wp_install_languages['code']])) {
                   continue;   
                  }else{
                       
                      $categoy_id = $this->save_category($category, $wp_install_languages['code'], $trid_id, $list_lang);
                                  
                      if($trid_mode == 1 && class_exists('SitePress')) {
                          $categoy_id_trid = (isset($categoy_id->term_taxonomy_id)) ? (int)$categoy_id->term_taxonomy_id : (int)$categoy_id;
                          $trid_id = $this->get_trid($categoy_id_trid);
                      }
                      
                        
                        
                  }
                  
                  
                     
                 }
                 
                 
             }
            
        }
        
        return true;
        
    }
    
    public function update_single_categorie($post_vars, $trid_mode = 0, $yawave_id = 0) {
            
            
            
            if (class_exists('SitePress')) {
                global $sitepress, $wpdb; 
                $list_lang = $sitepress->get_active_languages();
            }else{
                global $wpdb;
                $list_lang = array(array('code' => substr(get_bloginfo('language'), 0, 2)));
            }
            
            asort($list_lang);
            
            if($yawave_id != 0) {
                
                $yawave_categorie = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/categories/'.$yawave_id);
                $category = $yawave_categorie;
                
                if(!empty($category->parent_id)) {
                  $yawave_parent_categorie = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/categories/'.$category->parent_id);  
                  
                  $categories_array = array($yawave_parent_categorie, $category);
                  
                }
                
                //file_put_contents(WP_CONTENT_DIR . '/plugins/yawave/yawave_category.log', print_r($category, true), FILE_APPEND);
                
            }else{
              
                $category = $post_vars->content;
                
                if(!empty($category->parent_id)) {
                  $yawave_parent_categorie = $this->get_api_endpoint_data(YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/categories/'.$category->parent_id);  
                  
                  $categories_array = array($yawave_parent_categorie, $category);
                  
                }
                
            }
            
            foreach($list_lang AS $wp_install_languages){
              
              if($post_vars->event_type != 'category:deleted'){
                    
                    if(is_array($categories_array)) {
                        foreach($categories_array AS $categories){
                            
                            $array = json_decode(json_encode($categories->name), true);
                              
                              if(empty($array[$wp_install_languages['code']])) {
                               continue;   
                              }else{
                                   
                                  $categoy_id = $this->save_category($categories, $wp_install_languages['code'], $trid_id, $list_lang);
                                              
                                  if($trid_mode == 1 && class_exists('SitePress')) {
                                      $categoy_id_trid = (isset($categoy_id->term_taxonomy_id)) ? (int)$categoy_id->term_taxonomy_id : (int)$categoy_id;
                                      $trid_id = $this->get_trid($categoy_id_trid);
                                  }
                                  
                                    
                                    
                              }
                            
                        }
                    }else{
                        
                        $array = json_decode(json_encode($category->name), true);
                          
                          if(empty($array[$wp_install_languages['code']])) {
                           continue;   
                          }else{
                               
                              $categoy_id = $this->save_category($category, $wp_install_languages['code'], $trid_id, $list_lang);
                                          
                              if($trid_mode == 1 && class_exists('SitePress')) {
                                  $categoy_id_trid = (isset($categoy_id->term_taxonomy_id)) ? (int)$categoy_id->term_taxonomy_id : (int)$categoy_id;
                                  $trid_id = $this->get_trid($categoy_id_trid);
                              }
                              
                                
                                
                          }
                        
                    }
                    
                  
              
              }else{
                  
                  $array = json_decode(json_encode($category->name), true);
                  
                  if(empty($array[$wp_install_languages['code']])) {
                     continue;   
                    }else{
                        $this->delete_category($category, $wp_install_languages['code']);
                    }
                    
              }
                 
             }
        
            return $categoy_id;
    }
    
    
    
    
    /**
     * Return number of pages of endpoint
     * @param type $tags_object
     * @return integer
     */
    public function get_number_of_categories_pages($categories_object) {
        return (isset($categories_object->number_of_all_pages)) ? $categories_object->number_of_all_pages : 1;
    }
    
    public function get_trid($id) {
        
        global $wpdb;
        $trid = $wpdb->get_var( 'SELECT trid FROM '.$wpdb->prefix.'icl_translations WHERE element_id = '.intval($id) );
        return intval($trid);
        
    }
    /**
     * Save category into WP
     * @category object $category 
     */
    public function save_category($category, $language = 'de', $wpml_trid_id = 0, $list_lang = 0, $trid_mode = 0) {
        
         
        if (class_exists('SitePress')) {
            global $sitepress, $wpdb; 
            $default_lang = $sitepress->get_default_language(); 
        }else{
            global $wpdb;
            $default_lang = substr(get_bloginfo('language'), 0, 2);
        }
            
            //$wp_category_id = $this->get_category_by_yawave_id($category->id, $language, 'create');
                    
            $slug_adffix = ($default_lang != $language) ? '-'.$language : '';
            
            $category_name = (empty($category->name->$language)) ? $category->name->$default_lang : $category->name->$language;
            
            $category_checksum = md5($category->id.$language);
            
            $wp_category_id = $wpdb->get_row( 'SELECT b.term_taxonomy_id, a.term_id FROM '.$wpdb->prefix.'termmeta AS a
                                               INNER JOIN '.$wpdb->prefix.'term_taxonomy AS b ON (a.term_id = b.term_id) 
                                               WHERE a.meta_key = "yawave_categorie_checksum" AND a.meta_value = "'.$category_checksum.'"' );
           
           
        if($trid_mode == 0) {
            
            
            
            if (empty($wp_category_id)) {
                    
                    $wp_category_id = $this->create_wp_category($category_name, $category->localized_slugs->$language, $category->id, $language, $category);
                    
                    $return_categorie_id = get_term_by('id', $wp_category_id, 'category');
                    
                    if (!empty($category->parent_id)) {                       
                       
                       
                       
                       $this->assign_child_category_to_parent($wp_category_id, $category->parent_id, $language);
                       
                    }
                    
                
            } else {
                
                wp_update_term($wp_category_id->term_id, 'category' , array('name' => $category_name, 'slug' => $category->localized_slugs->$language));
                update_term_meta($wp_category_id->term_id, 'language_code', $language);
                update_term_meta($wp_category_id->term_id, 'yawave_categorie_json', json_encode($category));
                
                update_term_meta($wp_category_id->term_id, 'yawave_categorie_updateDate', date('Y-m-d H:i:s', time()));
                
                $return_categorie_id = $wp_category_id->term_taxonomy_id;
                if (!empty($category->parent_id)) {                   
                   $this->assign_child_category_to_parent($wp_category_id->term_id, $category->parent_id, $language);
                }
                
            }
        
        }
        
        
        if($wpml_trid_id > 0 && class_exists('SitePress')) {
            
            ### update trid to recived trid
            
            $trid_update_sql = 'UPDATE '.$wpdb->prefix . 'icl_translations SET trid = '.$wpml_trid_id.', language_code = "'.$language.'", source_language_code = "'.array_key_first($list_lang).'" WHERE element_id = '.$return_categorie_id;
            
            $update_manual = $wpdb->query($trid_update_sql);
            
            
            ###
            
        }
        
        
        
        ###
        
        return $return_categorie_id;
    }

    /**
     * Create new WordPress category, then adding to metadata yawave id
     * @param type $name
     * @param type $slug
     * @param type $yawave_id
     * @return type
     */
    public function create_wp_category($name, $slug, $yawave_id, $language = 'de', $category = '') {
        
        if (class_exists('SitePress')) {
            global $sitepress; 
            $default_lang = $sitepress->get_default_language();
            $sitepress->switch_lang($language);
        }
        
        
        $category_id = 0;
        
        $wp_category_args = array(
            'cat_ID' => $category_id,
            'cat_name' => trim($name),
            'category_nicename' => $slug,
            'taxonomy' => 'category'
        );
        
        $wp_category_id = wp_insert_category($wp_category_args);
        
        if ($wp_category_id && !empty($yawave_id)) {
            add_term_meta($wp_category_id, 'yawave_id', $yawave_id);
            add_term_meta($wp_category_id, 'language_code', $language);
            add_term_meta($wp_category_id, 'yawave_categorie_checksum', md5($yawave_id.$language));
            add_term_meta($wp_category_id, 'yawave_categorie_json', json_encode($category));
            add_term_meta($wp_category_id, 'yawave_categorie_createDate', date('Y-m-d H:i:s', time()));
        }
        
        if (class_exists('SitePress')) {
            $sitepress->switch_lang($default_lang);
        }
        
       
        return $wp_category_id;
    }

    /**
     * Find a parent category by Yavawe id and assign it to relevant WordPress category
     * @param type $wp_category_id
     * @param type $yawave_parent_category_id
     */
    public function assign_child_category_to_parent($wp_category_id, $yawave_parent_category_id, $language = 'de') {
        
        if (class_exists('SitePress')) {
            global $sitepress;
            $default_lang = $sitepress->get_default_language();
            $sitepress->switch_lang($language);
        }
        
        
        
        $wp_parent_category = get_terms(
                array(
                    'taxonomy' => 'category',
                    'hide_empty' => false,
                    'number' => 1,
                    'meta_query' => array(
                        array(
                            'key' => 'yawave_id',
                            'value' => $yawave_parent_category_id,
                        ),
                        array(
                            'key' => 'language_code',
                            'value' => $language,
                        )
                    )
                )
        );
        
        if (class_exists('SitePress')) {
           $sitepress->switch_lang($default_lang);
        }
        
        
        
        if ($wp_parent_category) {
            
            wp_update_category(['cat_ID' => $wp_category_id, 'category_parent' => $wp_parent_category[0]->term_id]);
            
        }
    }

    /**
     * Finds categories by Yawave ID and return first
     * @param string $yawave_id 
     */
    public function get_category_by_yawave_id($yawave_id, $language = 'de', $request_from = 'none') {
        
        if (class_exists('SitePress')) {
           global $sitepress;
           $default_lang = $sitepress->get_default_language();
           
           $sitepress->switch_lang($language);
        }
        
        
        
        $categories = get_terms(
                array(
                    'taxonomy' => 'category',
                    'hide_empty' => false,
                    'number' => 1,
                    'meta_query' => array(
                        array(
                            'key' => 'yawave_id',
                            'value' => $yawave_id,
                        ),
                        array(
                            'key' => 'language_code',
                            'value' => $language,
                        )
                    )
                )
        );
        
        if(empty($categories)) {
            
            $categories = get_terms(
                    array(
                        'taxonomy' => 'category',
                        'hide_empty' => false,
                        'number' => 1,
                        'meta_query' => array(
                            array(
                                'key' => 'yawave_id',
                                'value' => $yawave_id,
                            ),
                            array(
                                'key' => 'language_code',
                                'value' => $default_lang,
                            )
                        )
                    )
            );
            
        }
        
        if (class_exists('SitePress')) {
           $sitepress->switch_lang($default_lang);
        }
        
        
            
        return (!empty($categories) && is_array($categories)) ? $categories[0] : false;
    }
    
    public function delete_category($category, $language) {
        
        global $wpdb;
        
        $category_checksum = md5($category->id.$language);
        
        $wp_category_id = $wpdb->get_row( 'SELECT b.term_taxonomy_id, a.term_id FROM '.$wpdb->prefix.'termmeta AS a
                                           INNER JOIN '.$wpdb->prefix.'term_taxonomy AS b ON (a.term_id = b.term_id) 
                                           WHERE a.meta_key = "yawave_categorie_checksum" AND a.meta_value = "'.$category_checksum.'"' );
        
        wp_delete_category($wp_category_id->term_id);
        
    }


}
