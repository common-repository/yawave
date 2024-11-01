<?php

namespace Yawave;

trait WP_Yawave_Publications_Importer {

    /**
     * Update tags - main method to fech tags from API and push into WordPress
     * Tags are paginated import step by step
     */
    public function update_publications($page = 0, $publication_id = 0) {

        $url = YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/publications?lang=en&page=' . $page;

        $yawave_publications = $this->get_api_endpoint_data($url);
        
        
        
       if ($yawave_publications && isset($yawave_publications->content) && is_array($yawave_publications->content) && sizeof($yawave_publications->content) > 0) {
            foreach ($yawave_publications->content as $publication) {
                $this->save_publication($publication);
                $this->update_portals();
            }
        }
        
        return true;
    }

    public function update_single_publication($uuid, $status = 0, $yawave_publications_raw = '', $json_request = '') {

        $yawave_publications = $yawave_publications_raw;
        
        if (in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            
            $this->check_languages($uuid, $yawave_publications->languages);
                            
            foreach($yawave_publications->languages AS $language_id) {
                
                
                $this->save_publication($yawave_publications, $status, $language_id, $json_request);
            
            }
            
            $this->yawave_wpml_connector($yawave_publications);
            
        }else{
            
           
            $language_id = substr(get_bloginfo('language'), 0, 2);
            
            $this->save_publication($yawave_publications, $status, $language_id, $json_request);
            
        }
        
        
    
        $this->update_portals();
        
        return true;
    }
    
    public function check_languages($yawave_id, $languages) {
        
        ### get all languages actuall
        
        $args = array(
            'post_type' => array('publication', 'post'),
            'post_status' => array('publish', 'draft'),
            'numberposts' => 1,
            'meta_query' => array(
                array(
                    'key' => 'yawave_id',
                    'value' => $yawave_id,
                    'compare' => '='
                ),
            )
        );
        
        $publication = get_posts($args);
        
        $post_id = $publication[0]->ID;
          
        $type = apply_filters( 'wpml_element_type', get_post_type( $post_id ) );
        $trid = apply_filters( 'wpml_element_trid', false, $post_id, $type );
          
        $translations = apply_filters( 'wpml_get_element_translations', array(), $trid, $type );
        
        foreach ( $translations as $lang => $translation ) {
            $publication_languages[] = $translation->language_code;
            
            if(!in_array($translation->language_code, $languages)) {
                
                $args_delete = array(
                    'post_type' => array('publication', 'post'),
                    'post_status' => array('publish', 'draft'),
                    'numberposts' => 1,
                    'meta_query' => array(
                        array(
                            'key' => 'yawave_id',
                            'value' => $yawave_id,
                            'compare' => '='
                        ),
                        array(
                            'key' => 'yawave_publication_language',
                            'value' => $translation->language_code,
                            'compare' => '='
                        )
                    )
                );
                
                $publication_for_delete = get_posts($args_delete);
                wp_delete_post($publication_for_delete[0]->ID, true);
                
            }
            
        }
        
        
        
        ###
        
    }
    
    public function yawave_wpml_connector($yawave_publications) {
        
        global $wpdb;
        
        foreach($yawave_publications->languages AS $language_id) {
            
            $wp_post = $this->get_wp_publication_by_yawave_id($yawave_publications->id, $language_id);
            if ($wp_post && is_array($wp_post) && isset($wp_post[0])) {
                $wp_post_ids[] = array($wp_post[0]->ID, $language_id);
            }
        
        }
        
        echo '<pre>';
        var_dump($wp_post_ids);
        echo '</pre>';
        
        foreach($wp_post_ids AS $wp_id) {
            
            echo '<pre>';
                var_dump($wp_id);
                echo '</pre>';
                
                $count = $wpdb->get_var( 'SELECT COUNT(translation_id) AS wpmlcounting FROM '.$wpdb->prefix.'icl_translations WHERE element_id = '.$wp_id[0].' AND trid = '.$wp_post_ids[0][0].' AND language_code = "'.$wp_id[1].'"' );
                
                
                echo '<br />';
                
                var_dump($count);
                
                echo '<br />';
                echo '<br />';  
                            
                
                if($count == 0) {
                
                    $wpdbupdate = $wpdb->update( 
                        $wpdb->prefix.'icl_translations', 
                        array( 
                            'trid' => $wp_post_ids[0][0], 
                            'language_code' => $wp_id[1], 
                            'source_language_code' => $wp_post_ids[0][1], 
                        ), 
                        array('element_id' => $wp_id[0]) 
                    );
                    
                    $feedback_arr = array( 
                        'trid' => $wp_post_ids[0][0], 
                        'language_code' => $wp_id[1], 
                        'source_language_code' => $wp_post_ids[0][1], 
                        'element_id' => $wp_id[0]
                    );
                    
                    var_dump('feedback array');
                    var_dump($feedback_arr);
                    
                    echo '<br />';
                    echo '<br />'; 
                    
                    var_dump('sql feedback');
                    var_dump($wpdbupdate);
                    
                    echo '<br />';  
                    echo '<br />'; 
                    
                    echo '####################################';
                    
                    echo '<br />';  
                    echo '<br />';  
                    
                }
                
            }
        
        return true;
        
    }

    public function get_wp_publication_by_yawave_id($yawave_id, $language = 'de') {
        
        $args = array(
            'post_type' => array('publication', 'post'),
            'post_status' => array('publish', 'draft'),
            'numberposts' => 1,
            'meta_query' => array(
                array(
                    'key' => 'yawave_id',
                    'value' => $yawave_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'yawave_publication_language',
                    'value' => $language,
                    'compare' => '='
                )
            )
        );
        return get_posts($args);
    }

    public function save_publication($publication, $status = 0, $language = 'de', $json_request = '') {
       
        $this->save_yawave_publication($publication, $status, $language, $json_request);
    }

    /**
     * Add, update or skip publication
     * Update only if checksum is changed
     * @param type $args
     * @param type $publication
     */
    public function save_prepared_wp_post_with_featured_image($args, $publication, $status = 0, $language = 'de', $json_request = '') {
                  
        $wp_post = $this->get_wp_publication_by_yawave_id($publication->id, $language);
        $yawave_import_settings = get_option('yawave_settings_import_option');
        
        if ($wp_post && is_array($wp_post) && isset($wp_post[0])) {
            
            update_post_meta($wp_post[0]->ID, 'yawave_publication_cover_title', $publication->cover->title->$language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_cover_description', $publication->cover->description->$language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_cover_image_focus', json_encode($publication->cover->image->$language->focus)); 
            update_post_meta($wp_post[0]->ID, 'yawave_publication_cover_image_url', $publication->cover->image->$language->path);
                        
            update_post_meta($wp_post[0]->ID, 'yawave_publication_header_title', $publication->header->title->$language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_header_description', $publication->header->description->$language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_header_image_url', $publication->header->image->$language->path);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_header_image_focus', json_encode($publication->header->image->$language->focus)); 
            
            update_post_meta($wp_post[0]->ID, 'yawave_publication_type', $publication->type);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_styles', $publication->content->styles->$language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_language', $language);
            update_post_meta($wp_post[0]->ID, 'yawave_publication_json', wp_slash(json_encode($json_request)));
            
            update_post_meta($wp_post[0]->ID, 'yawave_publication_updateDate', date('Y-m-d H:i:s', time()));
            
            ### get wordpress main_category_id
            
            if(isset($publication->main_category_id)) {
                
                $main_category_id = $this->get_category_by_yawave_id($publication->main_category_id, $language);
                update_post_meta($wp_post[0]->ID, 'yawave_publication_main_category_id', $main_category_id->term_id);
                
            }
            
            ###
            
            if ($this->is_publication_diff($publication, $wp_post[0]->ID) || true) {
                
                $args['ID'] = $wp_post[0]->ID;
                kses_remove_filters();
                wp_update_post($args);
                echo 'update post';
                echo '<br />';
                kses_init_filters();
                
                ### update location url in yawave
                
                $this->yawave_alternativeLocationUrl($wp_post[0]->ID, $publication, $language);
                
                ###
                
                update_post_meta($wp_post[0]->ID, 'yawave_publication_control_sum', $this->publication_control_sum($publication));
                
                $action_buttons = [];  
                               
                if ($publication->tools) {
                    
                    foreach($publication->tools AS $tools) {
                        
                        if($tools->type == 'LINK') {
                            
                           $action_buttons_tmp['code'] = "<a href='".$tools->reference->link_url->$language."' class='btn btn-default' target='_blank'>".$tools->label->$language."</a>";
                            
                        }else{
                            
                            $action_buttons_tmp['code'] = "<button class='btn btn-default' onclick='YawaveSDK.handleButtonClick(this)' 
                            data-yawave-tool='".strtolower($tools->type)."' 
                            data-yawave-type='button' 
                            data-yawave-publication='".$publication->id."'
                            data-yawave-landing-url='".get_the_permalink($wp_post[0]->ID)."' 
                            data-yawave-referral-url='".get_the_permalink($wp_post[0]->ID)."'
                            data-yawave-id='".$tools->id."' 
                            data-bg-color='#153f76' 
                            data-link=''>".$tools->label->$language."</button>";
                            
                        }
                        
                        $action_buttons_tmp['type'] = $tools->type;
                        $action_buttons[] = $action_buttons_tmp;
                        
                    }
                    
                }
                               
                update_post_meta($wp_post[0]->ID, 'yawave_publication_action_buttons', $action_buttons);
                
                $this->assign_publication_to_media_type($publication,$wp_post[0]->ID);
                
                
                if($yawave_import_settings['yawave_import_images'] != 'yes') {
                
                    if (!empty($this->get_value($publication->cover->image->$language->path)) && $this->is_publication_featured_image_diff($publication, $wp_post[0]->ID, $language)) {
                        $this->upload_and_save_featured_image($this->get_value($publication->cover->image->$language), $wp_post[0]->ID);
                        update_post_meta($wp_post[0]->ID, 'yawave_publication_cover_image_control_sum', $this->publication_control_sum($this->get_value($publication->cover->image->$language->path)));
                    }
                    
                    if ($wp_post[0]->ID && !empty($this->get_value($publication->header->image->$language->path)) && $this->is_publication_header_image_diff($publication, $wp_post[0]->ID, $language) ) {
                        $yawave_header_id = $this->upload_and_save_featured_image($this->get_value($publication->header->image->$language), $wp_post[0]->ID, 'header');
                        update_post_meta($wp_post[0]->ID, 'yawave_publication_header_asset_id', $yawave_header_id);
                        update_post_meta($wp_post[0]->ID, 'yawave_publication_header_image_control_sum', $this->publication_control_sum($this->get_value($publication->header->image->$language->path)));
                    } 
                
                }
                
                
                
            }
            
        } else {
            
            kses_remove_filters();
            $wp_post_id = wp_insert_post($args);
            echo 'create post';
            echo '<br />';
            kses_init_filters();
            
            ### update location url in yawave
            
            
            $this->yawave_alternativeLocationUrl($wp_post_id, $publication, $language);

            ###
            
            add_post_meta($wp_post_id, 'yawave_publication_cover_title', $publication->cover->title->$language);
            add_post_meta($wp_post_id, 'yawave_publication_cover_description', $publication->cover->description->$language);
            add_post_meta($wp_post_id, 'yawave_publication_cover_image_focus', json_encode($publication->cover->image->$language->focus));
            add_post_meta($wp_post_id, 'yawave_publication_cover_image_url', $publication->cover->image->$language->path);
            
            add_post_meta($wp_post_id, 'yawave_publication_header_title', $publication->header->title->$language);
            add_post_meta($wp_post_id, 'yawave_publication_header_description', $publication->header->description->$language);
            add_post_meta($wp_post_id, 'yawave_publication_header_image_url', $publication->header->image->$language->path);
            add_post_meta($wp_post_id, 'yawave_publication_header_image_focus', json_encode($publication->header->image->$language->focus)); 
            
            add_post_meta($wp_post_id, 'yawave_publication_type', $publication->type);
            add_post_meta($wp_post_id, 'yawave_id', $publication->id, true);
            add_post_meta($wp_post_id, 'yawave_publication_control_sum', $this->publication_control_sum($publication), true);
            add_post_meta($wp_post_id, 'yawave_publication_styles', $publication->content->styles->$language);
            add_post_meta($wp_post_id, 'yawave_publication_language', $language);
            add_post_meta($wp_post_id, 'yawave_publication_kpi_metrics', '0');
            
            add_post_meta($wp_post_id, 'yawave_publication_json', wp_slash(json_encode($json_request)));
            
            add_post_meta($wp_post_id, 'yawave_publication_createDate', date('Y-m-d H:i:s', time()));
            
            ### get wordpress main_category_id
            
            if(isset($publication->main_category_id)) {
                
                $main_category_id = $this->get_category_by_yawave_id($publication->main_category_id, $language);
                add_post_meta($wp_post_id, 'yawave_publication_main_category_id', $main_category_id->term_id);
                
            }
            
            ###
            $action_buttons = [];
            
            if ($publication->tools) {
                                    
                foreach($publication->tools AS $tools) {
                    
                    $action_buttons_tmp['code'] = "<button class='btn btn-default' onclick='YawaveSDK.handleButtonClick(this)' 
                    data-yawave-tool='".strtolower($tools->type)."' 
                    data-yawave-type='button' 
                    data-yawave-publication='".$publication->id."'
                    data-yawave-landing-url='".get_the_permalink($wp_post_id)."' 
                    data-yawave-referral-url='".get_the_permalink($wp_post_id)."'
                    data-yawave-id='".$tools->id."' 
                    data-bg-color='#153f76' 
                    data-link=''>".$tools->label->$language."</button>";
                    $action_buttons_tmp['type'] = $tools->type;
                    
                    $action_buttons[] = $action_buttons_tmp;
                    
                }
                
            }
                           
            update_post_meta($wp_post_id, 'yawave_publication_action_buttons', $action_buttons);
            
            
            
            $this->assign_publication_to_media_type($publication,$wp_post_id);
            
            if($yawave_import_settings['yawave_import_images'] != 'yes') {
            
                if ($wp_post_id && !empty($this->get_value($publication->cover->image->$language->path))) {
                    $this->upload_and_save_featured_image($this->get_value($publication->cover->image->$language), $wp_post_id);
                    update_post_meta($wp_post_id, 'yawave_publication_cover_image_control_sum', $this->publication_control_sum($this->get_value($publication->cover->image->$language->path)));
                    
                } 
                
                if ($wp_post_id && !empty($this->get_value($publication->header->image->$language->path))) {
                    $yawave_header_id = $this->upload_and_save_featured_image($this->get_value($publication->header->image->$language), $wp_post_id, 'header');
                    
                    update_post_meta($wp_post_id, 'yawave_publication_header_asset_id', $yawave_header_id);
                    update_post_meta($wp_post_id, 'yawave_publication_header_image_control_sum', $this->publication_control_sum($this->get_value($publication->header->image->$language->path)));
                
                } 
            
            } 
            
             
            
        }
    }

    public function assign_publication_to_media_type($publication, $wp_post_id) {
        $term = get_term_by('slug', $publication->type, 'media_type');
        if (!$term) {
            $wp_term = wp_insert_term($publication->type, 'media_type');
            $wp_media_type_id = $wp_term['term_id'];
        } else {
            $wp_media_type_id = $term->term_id;
        }
        wp_set_post_terms($wp_post_id, array($wp_media_type_id), 'media_type');
    }

    public function save_yawave_publication($publication, $status = 0, $language = 'de', $json_request = '') {
        
        
        $args = $this->get_basic_wp_post_args($publication, $status, $language);
        
        //$args['post_content'] = html_entity_decode($this->get_value($publication->layout->content));
        $tabs = $this->get_value($publication->layout->tabs);
        $content = html_entity_decode($this->get_content_converted_from_tabs($tabs));
        $args['post_content'] = $content;
        
        if ($publication->type == "ARTICLE" || $publication->type == "NEWSLETTER" || $publication->type == "LANDING_PAGE") {
            
            if($publication->type == "LANDING_PAGE") {
            
                ### search patterns in landingpage and replace it with nothing
                
                $landingpage_search = array(
                    '<!DOCTYPE html>', 
                    '<html>',
                    '<head>',
                    '<title></title>',
                    '<meta charset="UTF-8">',
                    '<meta name="viewport" content="width=device-width">',
                    '</head>',
                    '<body>',
                    '</body>',
                    '</html>',
                );
                
                $landingpage_search_replace = array('','','','','','','','','','');
                
                $publication->content->html_tailored->$language = str_replace($landingpage_search, $landingpage_search_replace, $publication->content->html_tailored->$language);
                
                ###
                
            }
            
            $args['post_content'] = $publication->content->html_tailored->$language;
        }
        
        if ($publication->type == "PDF") {
            $args['post_content'] = '<embed src="'.$publication->content->url.'" type="application/pdf" width="100%" height="600">';
        }
        
        if ($publication->type == "EMBED_CODE") {
            $args['post_content'] = $publication->content->embed_code->$language;
        }
        
        if ($publication->type == "VIDEO") {
            
            if(!empty($publication->content->embed_code->$language)) {
                
                $args['post_content'] = $publication->content->embed_code->$language;
                
            }elseif(strpos($publication->content->url->$language, 'youtube.com')) {
                
                function convertYoutube($string) {
                    return preg_replace(
                    "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                    "<iframe src=\"//www.youtube.com/embed/$2\" allowfullscreen style='width: 100%; height: 450px; border:0;' class='yawave-youtube-embeded'></iframe>",
                    $string
                    );
                  }
                
                  $args['post_content'] = convertYoutube($publication->content->url->$language);
                
            }
            
            $args['post_content'] = $args['post_content'] . '<div id="yawave-video-description">' . $publication->content->description->$language . '</div>';
            
        }
        
        $this->save_prepared_wp_post_with_featured_image($args, $publication, $status, $language, $json_request);
        
    }
    

    public function get_content_converted_from_tabs($tabs) {
        $wp_tabs = [];
        foreach ($tabs as $tab) {
            if (isset($tab->enabled) && $tab->enabled) {
                $wp_tabs[$tab->name] = isset($tab->content) ? $tab->content : "";
            }
        }
        if (sizeof($wp_tabs) > 0) {
            $i = 1;
            $content = '<div id="tabs"><ul>';
            foreach ($wp_tabs as $label => $tab_content) {
                $content .= '<li><a href="#tabs-' . $i . '">' . $label . '</a></li>';
                $i++;
            }
            $content .= '</ul>';
            $i = 1;
            foreach ($wp_tabs as $label => $tab_content) {
                $content .= '<div id="tabs-' . $i . '">' . $tab_content . '</div>';
            }
            $content .= '</div>';
            return $content;
        } else {
            return "";
        }
    }

    public function get_basic_wp_post_args($publication, $post_status, $language = 'de') {
        
        ### get beginn date if set, use this for post_date
        
        if(!empty($publication->begin_date)) {            
            $post_date = $publication->begin_date;            
        }else{            
            $post_date = $publication->creation_date;            
        }
        
        ###
        
        
        
        $yawave_import_settings = get_option('yawave_settings_import_option');
        
        if(isset($yawave_import_settings)) {
            
            $post_type = $yawave_import_settings['yawave_import_post_type'];
            
        }else{
            
            $post_type = 'publication';
            
        }
        
        ### post status
        
        if ($post_status != "PUBLISHED") {
            
            $wp_post_status = 'draft';
            
        } else {
            
            $wp_post_status = 'publish';
            
        }
        
        
        
        
        ### post_author
        
        if($yawave_import_settings['yawave_import_yawave_user_create'] == 'yes') {
            
            $publication_username = strtolower($publication->author->first_name).strtolower($publication->author->last_name);
            
            $wordpress_user = get_user_by('login', $publication_username);
           
           if($wordpress_user) {
                
                $post_author = $wordpress_user->ID;                
                
            }else{
                
                $userdata = array(
                    'user_pass'             => $this->yawave_user_generatePassword(8, 2, 2, true),
                    'user_login'            => $publication_username,
                    'user_nicename'         => $publication_username,
                    'display_name'          => $publication->author->first_name.' '.$publication->author->last_name,
                    'nickname'              => $publication_username,
                    'first_name'            => $publication->author->first_name,
                    'last_name'             => $publication->author->last_name,
                    'description'           => 'Yawave created user',
                    'user_registered'       => date('Y-m-d H:i:s', time()), 
                    'role'                  => 'author',
                 
                );
                
                $post_author = wp_insert_user($userdata);
                
                if(!$post_author) {
                    
                    $post_author = $this->get_author_id();
                    
                }
                
            }
            
        }else{
            
            $post_author = $this->get_author_id();
            
        }
        
        ###
        
        $UTC = new \DateTimeZone("UTC");
        $newTZ = new \DateTimeZone(wp_timezone_string());
        $date = new \DateTime( $post_date, $UTC );
        $date->setTimezone( $newTZ );
        
        $post_date = $date->format('Y-m-d H:i:s');
        
        ###
        
        return array(
            'post_type' => $post_type,
            'post_author' => $post_author,
            'post_status' => $wp_post_status,
            'post_date' =>  $post_date,
            //'post_date_gmt' => $post_date,
            'post_title' => $publication->cover->title->$language,
            'post_excerpt' => (!empty($publication->cover->description->$language) ? $publication->cover->description->$language : 0),
            'post_category' => $this->get_assigned_categories_ids($publication->category_ids, $publication->main_category_id, $language),
            'tags_input' => $this->get_assigned_tags_ids($publication->tag_ids),
            'post_name' => $publication->localized_slugs->$language,
        );
        
    }

    public function get_assigned_categories_ids($yawave_categories_ids, $main_category_id, $language = 'de') {
        
        if (!empty($main_category_id)) {
            $yawave_categories_ids[] = $main_category_id;
        }

        $wp_categories_ids = [];
        if ($yawave_categories_ids && is_array($yawave_categories_ids) && sizeof($yawave_categories_ids)) {
            foreach ($yawave_categories_ids as $yawave_category_id) {
                $wp_category = $this->get_category_by_yawave_id($yawave_category_id, $language);
                if (!empty($wp_category) && !in_array($wp_category->term_id, $wp_categories_ids)) {
                    $wp_categories_ids[] = $wp_category->term_id;
                }else{
                    
                    $wp_category = $this->update_single_categorie(0, $language, $yawave_category_id);
                    $wp_categories_ids[] = $wp_category;
                    
                }
            }
        }
        
        ### check if select any category, if not, set default wordpress category
        
        if(count($wp_categories_ids) == 0) {
            $wp_categories_ids[] = 1;
        }
        
        ###
        
        return $wp_categories_ids;
        
    }

    public function get_assigned_tags_ids($yawave_tags) {
        $wp_tags_ids = [];
        if ($yawave_tags && is_array($yawave_tags) && sizeof($yawave_tags)) {
            foreach ($yawave_tags as $yawave_tag) {
                $wp_tag = $this->get_tag_by_yawave_id($yawave_tag);
                if (!empty($wp_tag)) {
                    $wp_tags_ids[] = $wp_tag->term_id;
                }else{
                   $wp_tag_create = $this->update_single_tag(0, $yawave_tag); 
                   $wp_tags_ids[] = $wp_tag_create;
                }
            }
        }
        
        return $wp_tags_ids;
    }

    public function upload_and_save_featured_image($publication_image, $post_id, $area = '') {
        $url = $publication_image->path;
        $file = array();
        $file['name'] = $url;
        $file['tmp_name'] = download_url($url);

        if (is_wp_error($file['tmp_name'])) {
            @unlink($file['tmp_name']);
            return false; //var_dump( $file['tmp_name']->get_error_messages( ) );
        } else {
            $attachmentId = media_handle_sideload($file, $post_id);
            if($area != 'header') {
                set_post_thumbnail($post_id, $attachmentId);
            }
            if (is_wp_error($attachmentId)) {
                @unlink($file['tmp_name']);
            }
            return $attachmentId;
        }
    }

    public function get_url_publication_image($publication_image) {
        $url = false;
        if (!empty($publication_image->path)) {
            if (strpos($publication_image->path, 's3://') >= 0) {
                $url = str_replace('s3://', YAWAVE_S3_URL, $publication_image->path);
            } elseif (strpos($publication_image->path, 'public://') >= 0) {
                $url = str_replace('public://', YAWAVE_PUBLIC_URL, $publication_image->path);
            }
        }
        $url = $this->get_redirect_final_target($url);
        return $url;
    }

    /**
     * Return number of pages of endpoint
     * @param type $tags_object
     * @return integer
     */
    public function get_number_of_publication_pages($tags_object) {
        return (isset($tags_object->number_of_all_pages)) ? $tags_object->number_of_all_pages : 1;
    }


    public function video_embeded($url) {
        if (strpos($url, '.youtube.') > 0) {
            $url = 'https://www.youtube.com/watch?v=u9-kU7gfuFA';
            preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
            $id = $matches[1];
            return '<iframe id="ytplayer" type="text/html" width="100%" height="500px" src="https://www.youtube.com/embed/' . $id . '?rel=0&showinfo=0&color=white&iv_load_policy=3" frameborder="0" allowfullscreen></iframe>';
        }
    }

    public function link_template($link, $title) {
        return '<a href="' . $link . '" target="_blank">' . $title . '</a>';
    }

    public function photo_template($url, $title) {
        $img_url = $this->get_url_publication_image($url);
        return (!empty($img_url)) ? '<img src="' . $img_url . '" alt="' . $title . '" title="' . $title . '" />' : "";
    }

    public function is_publication_diff($publication, $wp_post_id) {
        $sum = get_post_meta($wp_post_id, 'yawave_publication_control_sum', true);
        return ($this->publication_control_sum($publication) !== $sum);
    }

    public function is_publication_featured_image_diff($publication, $wp_post_id, $language = 'de') {
        if (!has_post_thumbnail($wp_post_id)) {
            return true;
        } // if no featured image
        $sum = get_post_meta($wp_post_id, 'yawave_publication_cover_image_control_sum', true);
        return ($this->publication_control_sum($this->get_value($publication->cover->image->$language->path)) !== $sum);
    }
    
    public function is_publication_header_image_diff($publication, $wp_post_id, $language = 'de') {
        $sum = get_post_meta($wp_post_id, 'yawave_publication_header_image_control_sum', true);
        return ($this->publication_control_sum($this->get_value($publication->header->image->$language->path)) !== $sum);
    }

    public function publication_control_sum($publication) {
        return md5(serialize($publication));
    }

    public function get_redirect_final_target($url) {
        return $url;
    }
    
    public function yawave_user_generatePassword($passwordlength = 8, $numNonAlpha = 0, $numNumberChars = 0, $useCapitalLetter = false ) {

        $numberChars = '123456789';
        $specialChars = '!$%&=?*-:;.,+~@_';
        $secureChars = 'abcdefghjkmnpqrstuvwxyz';
        $stack = '';
        
        // Stack für Password-Erzeugung füllen
        $stack = $secureChars;
        
        if ( $useCapitalLetter == true )
        $stack .= strtoupper ( $secureChars );
        
        $count = $passwordlength - $numNonAlpha - $numNumberChars;
        $temp = str_shuffle ( $stack );
        $stack = substr ( $temp , 0 , $count );
        
        if ( $numNonAlpha > 0 ) {
        $temp = str_shuffle ( $specialChars );
        $stack .= substr ( $temp , 0 , $numNonAlpha );
        }
        
        if ( $numNumberChars > 0 ) {
        $temp = str_shuffle ( $numberChars );
        $stack .= substr ( $temp , 0 , $numNumberChars );
        }
        
        
        // Stack durchwürfeln
        $stack = str_shuffle ( $stack );
        
        // Rückgabe des erzeugten Passwort
        return $stack;
        
    }
    
    public function yawave_alternativeLocationUrl($wp_id, $publication, $language) {
        
        if (in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            global $sitepress;
            $default_lang = $sitepress->get_default_language();
            $sitepress->switch_lang($language);
        }
        
        $public_url = get_the_permalink($wp_id);
        
        $url = YAWAVE_API_ENDPOINT_URL . 'public/applications/YAWAVE_APP_ID/publications/'.$publication->id.'/alternativeLocationUrl?lang='.$language;
        
        $data = array(
            'url' => $public_url,
        );
        
        $yawave_update_alternativeLocationUrl = $this->put_api_endpoint_data($url, json_encode($data)); 
        
        if (in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            $sitepress->switch_lang($default_lang);    
        }    
        
    }

}
