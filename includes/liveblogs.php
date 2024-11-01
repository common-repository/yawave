<?php

namespace Yawave;

trait WP_Yawave_Liveblog_Importer {

	public function update_liveblog_magic($post_vars) {
				
		global $wpdb;
		
		$auth_options = get_option('yawave_settings_authorization_option');
		
		$this->save_log(json_encode($post_vars), 'update_liveblog_magic');
		
		### check if liveblog in database, if not, create process
		
		if($post_vars->event_type == 'liveblog:updated') {
		
			$check = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'yawave_liveblogs WHERE uuid = "'.$post_vars->content->id.'"', ARRAY_A); 
			
			
			if(empty($check[0]['id'])) {
				$post_vars->event_type = 'liveblog:created';
			}
		
		}elseif($post_vars->event_type == 'liveblogPost:updated') {
		
			$check = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'yawave_liveblogs_posts WHERE uuid = "'.$post_vars->content->id.'"', ARRAY_A); 
			
			if(empty($check[0]['id'])) {
				$post_vars->event_type = 'liveblogPost:created';
			}
			
		}
		
		###
		
		if($post_vars->event_type == 'liveblog:created') {
			  
		  // liveblog details
		  //$url = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$post_vars->application_uuid.'/liveblogs/'.$post_vars->liveblog_uuid;
		  //$return_post_infos = $this->get_api_endpoint_data($url);
		  
		  //$this->save_log(serialize($return_post_infos), 'return_post_infos');
		  
		  
		  
		  $table = $wpdb->prefix.'yawave_liveblogs';
		  $data = array(
					'application_uuid'    => $auth_options['yawave_authorization_appid'], 
					'uuid'                => $post_vars->content->id, 
					'createtime'          => current_time('mysql', 1),
					'sportradar_id'       => ((!empty($post_vars->content->sources[0]->sport_event_id)) ? $post_vars->content->sources[0]->sport_event_id : 0),
					'title'               => $post_vars->content->title,
					'description'         => ((!empty($post_vars->content->description)) ? $post_vars->content->description : 0),
					'wp_post_id'		  => ((!empty($wp_post_id)) ? $wp_post_id : 0),
					'cover_image'		  => ((!empty($post_vars->content->image->path)) ? serialize($post_vars->content->image) : 0),
					'yawave_type'		  => ((!empty($post_vars->content->type)) ? $post_vars->content->type : 0),
					'yawave_status'		  => ((!empty($post_vars->content->status)) ? $post_vars->content->status : 0),
					'location'		  	  => ((!empty($post_vars->content->location)) ? $post_vars->content->location : 0),
					'start_date'		  => ((!empty($post_vars->content->start_date)) ? $post_vars->content->start_date : 0),
					'home_competitor'	  => ((!empty($post_vars->content->home_competitor->name)) ? serialize($post_vars->content->home_competitor) : 0),
					'away_competitor'	  => ((!empty($post_vars->content->away_competitor->name)) ? serialize($post_vars->content->away_competitor) : 0),
					'yawave_sources'	  => ((!empty($post_vars->content->yawave_sources[0]->type)) ? serialize($post_vars->content->yawave_sources) : 0),
					'yawave_json'	  	  => json_encode($post_vars),
					);
					
		  $wpdb->insert($table,$data);
		  
		}elseif($post_vars->event_type == 'liveblog:updated') {
		  
		  // liveblog details
		  //$url = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$post_vars->application_uuid.'/liveblogs/'.$post_vars->liveblog_uuid;
		  //$return_post_infos = $this->get_api_endpoint_data($url);
		  
		  $table = $wpdb->prefix.'yawave_liveblogs';
		  $data = array(
					'sportradar_id'       => ((!empty($post_vars->content->sources[0]->sport_event_id)) ? $post_vars->content->sources[0]->sport_event_id : 0),
					'title'               => $post_vars->content->title,
					'description'         => ((!empty($post_vars->content->description)) ? $post_vars->content->description : 0),
					'wp_post_id'		  => ((!empty($wp_post_id)) ? $wp_post_id : 0),
					'cover_image'		  => ((!empty($post_vars->content->image->path)) ? serialize($post_vars->content->image) : 0),
					'yawave_type'		  => ((!empty($post_vars->content->type)) ? $post_vars->content->type : 0),
					'yawave_status'		  => ((!empty($post_vars->content->status)) ? $post_vars->content->status : 0),
					'location'		  	  => ((!empty($post_vars->content->location)) ? $post_vars->content->location : 0),
					'start_date'		  => ((!empty($post_vars->content->start_date)) ? $post_vars->content->start_date : 0),
					'home_competitor'	  => ((!empty($post_vars->content->home_competitor->name)) ? serialize($post_vars->content->home_competitor) : 0),
					'away_competitor'	  => ((!empty($post_vars->content->away_competitor->name)) ? serialize($post_vars->content->away_competitor) : 0),
					'yawave_sources'	  => ((!empty($post_vars->content->yawave_sources[0]->type)) ? serialize($post_vars->content->yawave_sources) : 0),
					'yawave_json'	  	  => json_encode($post_vars),
					'updatetime'          => current_time('mysql', 1),
					);
		  
		  $where = array(
			'uuid'                => $post_vars->content->id,
			);
					
		  $wpdb->update($table, $data, $where);
		
		}elseif($post_vars->event_type == 'liveblog:deleted') {
			   
			 $liveblog_id = $wpdb->get_results('SELECT id FROM '.$wpdb->prefix.'yawave_liveblogs WHERE uuid = "'.$post_vars->content->id.'"', ARRAY_A); 	
			 
			 ### delete liveblog posts
			 
			 $where_posts = array(
					'liveblog_id'                => $liveblog_id[0]['id'],
					);
				  
			  $query_posts = $wpdb->delete($wpdb->prefix.'yawave_liveblogs_posts', $where_posts);
			 
			 ### delete liveblog
			 
			 $where = array(
			   'uuid'                => $post_vars->content->id,
			   );
			 
			 $query = $wpdb->delete($wpdb->prefix.'yawave_liveblogs', $where);
			 
			 ###
		  
		}elseif($post_vars->event_type == 'liveblogPost:created') {
		  
			  ### check if post uuid already in databse
		  	//file_put_contents(WP_CONTENT_DIR . '/plugins/yawave/liveblog.log', print_r($post_vars, true), FILE_APPEND);
			  
		  
		   $count_post_already_in_db =  $wpdb->get_var( 'SELECT COUNT(uuid) FROM '.$wpdb->prefix.'yawave_liveblogs_posts WHERE uuid = "'.$post_vars->content->id.'"'); 
		  
		  
			 var_dump($count_post_already_in_db);
		  
			  if($count_post_already_in_db == 0) {
		  
			   ### check if liveblog in database
			   
			   $liveblog_data = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'yawave_liveblogs WHERE uuid = "'.$post_vars->content->liveblog_id.'"', ARRAY_A); 
			   
			   $liveblog_id = ($liveblog_data[0]['id'] > 0) ? $liveblog_data[0]['id'] : 0;
			   
			   ###
			   
			   // liveblog posts
			   //$url = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$post_vars->application_uuid.'/liveblogs/'.$post_vars->liveblog_uuid.'/posts/'.$post_vars->liveblog_post_uuid.'/';
			   //$return_post_infos = $this->get_api_endpoint_data($url);
						  
			   
			   
			   ### create speciefiec source value array
			   
			   if($post_vars->content->source == 'SPORT_RADAR') {
				   
				   $source_specs_values_arr = array(
								   'external_id' 	=> ((!empty($post_vars->content->external_id)) ? $post_vars->content->external_id : 0),
									'type' 			=> ((!empty($post_vars->content->type)) ? $post_vars->content->type : 0),
								   'stoppage_time' 	=> ((!empty($post_vars->content->stoppage_time)) ? $post_vars->content->stoppage_time : 0),
								   'match_clock'	=> ((!empty($post_vars->content->match_clock)) ? $post_vars->content->match_clock : 0) ,
								   'competitor' 	=> ((!empty($post_vars->content->competitor)) ? $post_vars->content->competitor : 0),
								   'players' 		=> ((!empty($post_vars->content->players[0]->external_id)) ? $post_vars->content->players : 0),
								   'home_score' 	=> ((!empty($post_vars->content->home_score)) ? $post_vars->content->home_score : 0),
								   'away_score' 	=> ((!empty($post_vars->content->away_score)) ? $post_vars->content->away_score : 0),
								   'injury_time'	=> ((!empty($post_vars->content->injury_time)) ? $post_vars->content->injury_time : 0),
							   );
							   
					### manipulate minute ob specific events
					   
				   if($post_vars->content->type == 'BREAK_START') {
					   $post_vars->content->minute = 45;
				   }elseif($post_vars->content->type == 'PERIOD_START' && $post_vars->content->period == 'SECOND') {
					   $post_vars->content->minute = 45;
				   }elseif($post_vars->content->type == 'PERIOD_SCORE' && $post_vars->content->period == 'FIRST') {
					  $post_vars->content->minute = 45;
				   }elseif($post_vars->content->type == 'MATCH_ENDED') {
						 $post_vars->content->minute = 90;
				   }
				   
				   ###
				   
			   }else{
				   
				   $source_specs_values_arr = array();
				   
			   }
			   
			   ### save person infos in array
			   
			   if(!empty($return_post_infos->person_id)) {
				   
				   $url_categorie = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$auth_options['yawave_authorization_appid'].'/categories/'.$post_vars->content->person_id.'?lang=de';
				   $return_categorie_infos = $this->get_api_endpoint_data($url_categorie);
				   
				   if($return_categorie_infos->icon->source == 'CUSTOM') {
					   $person_icon = $return_categorie_infos->icon->path;
				   }else{
					   $person_icon = '';
				   }
				   
				   $person_infos = array(
					   'name' => $return_categorie_infos->name,
					   'icon' => $person_icon,
				   );
				   
			   }else{
				   
				   $person_infos = 0;
				   
			   }
			   
			  ### save action infos in array
			  
			  if(!empty($post_vars->content->action_id)) {
				  
				  $url_categorie = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$auth_options['yawave_authorization_appid'].'/categories/'.$post_vars->content->action_id.'?lang=de';
				  $return_categorie_infos = $this->get_api_endpoint_data($url_categorie);
				  
				  if($return_categorie_infos->icon->source == 'CUSTOM') {
					 $action_icon = $return_categorie_infos->icon->path;
					 }else{
					 $action_icon = '';
					 }
				  
				  $action_infos = array(
					  'name' => $return_categorie_infos->name,
					  'icon' => $action_icon,
				  );
				  
			  }else{
				  
				  $action_infos = 0;
				  
			  }
			   
			   ###
			   
			   $table = $wpdb->prefix.'yawave_liveblogs_posts';
			   $data = array(
						 'uuid'            		=>  $post_vars->content->id,
						 'source'          		=>  ((!empty($post_vars->content->source)) ? $post_vars->content->source : 0),
						 'period'          		=>  ((!empty($post_vars->content->period)) ? $post_vars->content->period : 0),
						 'minute'          		=>  ((!empty($post_vars->content->minute)) ? $post_vars->content->minute : 0),
						  'title'           		=>  ((!empty($post_vars->content->title)) ? $post_vars->content->title : 0),
						 'post_content'    		=>  ((!empty($post_vars->content->text)) ? $post_vars->content->text : 0),
						 'url'             		=>  ((!empty($post_vars->content->url)) ? $post_vars->content->url : 0),
						 'publication_id'  		=>  ((!empty($post_vars->content->publication_id)) ? $post_vars->content->publication_id : 0),
						 'pinned'          		=>  ((!empty($post_vars->content->pinned)) ? $post_vars->content->pinned : 0),
						 'creation_date'   		=>  current_time('mysql', 1),
						 'liveblog_id'     		=>  $liveblog_id,
						 'embed_code'      		=>  ((!empty($post_vars->content->embed_code)) ? $post_vars->content->embed_code : 0),
						 'all_parms'       		=>  json_encode($post_vars),
						 'source_specs_values'  =>  ((count($source_specs_values_arr)>0) ? serialize($source_specs_values_arr) : 0),
						 'timeline_timestamp'   =>  ((!empty($post_vars->content->timeline_timestamp)) ? $post_vars->content->timeline_timestamp : 0),
						 'yawave_timestamp'   	=>  ((!empty($post_vars->content->timestamp)) ? $post_vars->content->timestamp : 0),
						 'action_id'   			=>  ((!empty($post_vars->content->action_id)) ? $post_vars->content->action_id : 0),
						 'person_id'   			=>  ((!empty($post_vars->content->person_id)) ? $post_vars->content->person_id : 0),
						 'person_infos'   		=>  ((is_array($person_infos)) ? serialize($person_infos) : 0),
						 'action_infos'   		=>  ((is_array($action_infos)) ? serialize($action_infos) : 0),
						 'wp_visible_status'	=> 1
						 );
			   
			   $query = $wpdb->insert($table,$data);
		   
			   }
		   
		}elseif($post_vars->event_type == 'liveblogPost:updated') {
		   
		   // liveblog posts
		   //$url = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$post_vars->application_uuid.'/liveblogs/'.$post_vars->liveblog_uuid.'/posts/'.$post_vars->liveblog_post_uuid.'/';
		   //$return_post_infos = $this->get_api_endpoint_data($url);
		   
		   ### save person infos in array
			  
			  if(!empty($post_vars->content->person_id)) {
				  
				  $url_categorie = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$auth_options['yawave_authorization_appid'].'/categories/'.$post_vars->content->person_id.'?lang=de';
				  $return_categorie_infos = $this->get_api_endpoint_data($url_categorie);
				  
				  if($return_categorie_infos->icon->source == 'CUSTOM') {
					  $person_icon = $return_categorie_infos->icon->path;
				  }else{
					  $person_icon = '';
				  }
				  
				  $person_infos = array(
					  'name' => $return_categorie_infos->name,
					  'icon' => $person_icon,
				  );
				  
			  }else{
				  
				  $person_infos = 0;
				  
			  }
			  
			 ### save action infos in array
			 
			 if(!empty($return_post_infos->action_id)) {
				 
				 $url_categorie = YAWAVE_API_ENDPOINT_URL . 'public/applications/'.$auth_options['yawave_authorization_appid'].'/categories/'.$post_vars->content->action_id.'?lang=de';
				 $return_categorie_infos = $this->get_api_endpoint_data($url_categorie);
				 
				 if($return_categorie_infos->icon->source == 'CUSTOM') {
					$action_icon = $return_categorie_infos->icon->path;
					}else{
					$action_icon = '';
					}
				 
				 $action_infos = array(
					 'name' => $return_categorie_infos->name,
					 'icon' => $action_icon,
				 );
				 
			 }else{
				 
				 $action_infos = 0;
				 
			 }
			  
			  ###
		   
		   $table = $wpdb->prefix.'yawave_liveblogs_posts';
		   $data = array(
					 'source'          =>  $post_vars->content->source,
					 'period'          =>  ((!empty($post_vars->content->period)) ? $post_vars->content->period : 0),
					 'minute'          =>  ((!empty($post_vars->content->minute)) ? $post_vars->content->minute : 0),
					 'title'           =>  ((!empty($post_vars->content->title)) ? $post_vars->content->title : 0),
					 'post_content'    =>  ((!empty($post_vars->content->text)) ? $post_vars->content->text : 0),
					 'url'             =>  ((!empty($post_vars->content->url)) ? $post_vars->content->url : 0),
					 'publication_id'  =>  ((!empty($post_vars->content->publication_id)) ? $post_vars->content->publication_id : 0),
					 'pinned'          =>  ((!empty($post_vars->content->pinned)) ? $post_vars->content->pinned : 0),
					 'embed_code'     =>  ((!empty($post_vars->content->embed_code)) ? $post_vars->content->embed_code : 0),
					  'action_id'   			=>  ((!empty($post_vars->content->action_id)) ? $post_vars->content->action_id : 0),
					  'person_id'   			=>  ((!empty($post_vars->content->person_id)) ? $post_vars->content->person_id : 0),
					  'person_infos'   		=>  ((is_array($person_infos)) ? serialize($person_infos) : 0),
					  'action_infos'   		=>  ((is_array($action_infos)) ? serialize($action_infos) : 0),
					  'all_parms'       		=>  json_encode($post_vars),
					   'timeline_timestamp'   =>  ((!empty($post_vars->content->timeline_timestamp)) ? $post_vars->content->timeline_timestamp : 0),
						'yawave_timestamp'   	=>  ((!empty($post_vars->content->timestamp)) ? $post_vars->content->timestamp : 0),
					   'update_date'          => current_time('mysql', 1),
					   
					 );
		   
		   $where = array(
			 'uuid'                => $post_vars->content->id,
			 );
		   
		   $query = $wpdb->update($table,$data, $where);
		   
		}elseif($post_vars->event_type == 'liveblogPost:deleted') {
		   
		   $table = $wpdb->prefix.'yawave_liveblogs_posts';
		   
		   $where = array(
			 'uuid'                => $post_vars->content->id,
			 );
		   
		   $query = $wpdb->delete($table, $where);
		   
		}
		
	}

}
