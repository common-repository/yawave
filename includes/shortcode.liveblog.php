<?php

function yawave_show_liveblog_handler( $atts, $content, $tag ) {
	
	global $wpdb;
	
	$a = shortcode_atts( array(
		 'liveblog-id' 		=> 0,
		 'sorting'			=> 'DESC'
	), $atts );
	
	### Sorting
	
	if($a['sorting'] == 'DESC') {
		$liveblog_sorting = 'DESC';
	}elseif($a['sorting'] == 'ASC') {
		$liveblog_sorting = 'ASC';
	}
	
	###
	
	if($a['liveblog-id'] == 0) {
		
		### get last id
		
		$liveblog_id = $wpdb->get_var( 'SELECT id FROM '.$wpdb->prefix.'yawave_liveblogs ORDER BY start_date DESC LIMIT 1');
		
		$a['liveblog-id'] = $liveblog_id;
		
		###
		
	}
	
	$liveblog_infos =  $wpdb->get_row( 'SELECT title, description, location FROM '.$wpdb->prefix.'yawave_liveblogs WHERE id = '.$a['liveblog-id'], ARRAY_A);
	
	$entrys = '
	
	<div class="yawave-livelog-next-reload"><small>Nächste Aktualisierung in <span id="liveblog-counter-time"></span> Sekunden</small></div>
	
	<div class="yawave-liveblog-details">
	<h1>'.((!empty($liveblog_infos['title'])) ? $liveblog_infos['title'] : '').'</h1>
	<p>'.((!empty($liveblog_infos['description'])) ? $liveblog_infos['description'] : '').'</p>
	<hr />
	<small>'.((!empty($liveblog_infos['location'])) ? $liveblog_infos['location'] : '').'</small>
	</div>
	
	<div class="yawave-liveblog-container"><div id="yawave-liveblog-results"></div>
	  <div id="liveblog-load-more-entrys"></div>
	  <input type="hidden" id="liveblog-actually-show-page" value="1" />
	  <input type="hidden" id="liveblog_loaded_items" />
	  <input type="hidden" id="liveblog_all_items" />
	 
	  
	  <script type="text/javascript">
	  var liveblog_id = '.$a['liveblog-id'].';
	  var liveblog_sorting = "'.$liveblog_sorting.'";
	  </script></div>';
	
	return $entrys;
	
}

// Fetching liveblog krams for javscript reload
// URL: /wp-admin/admin-ajax.php?action=loadManualEventTimeline&gameid={wordpress-game-id}

add_action('wp_ajax_js_liveblog_update', 'js_liveblog_update');
add_action('wp_ajax_nopriv_js_liveblog_update', 'js_liveblog_update');

function js_liveblog_update() {
 
  global $wpdb;
  
  $showing_entrys_on_reload = 15;
 
  $sportradar_id = sanitize_text_field($_GET['srid']); // this means portal!
  $get_wp_id = sanitize_text_field($_GET['wpid']); // 
  $get_liveblog_id = sanitize_text_field($_GET['lbid']); // 
  $get_sorting = sanitize_text_field($_GET['sorting']); // 
  $liveblog_items_ids = array();
  
  $get_itemids = (isset($_GET['itemids'])) ? sanitize_text_field($_GET['itemids']) : ''; // loaded ids
  
  if(!empty($get_itemids)) {
	
	$load_only_new_items_filter = ' AND b.id NOT IN ('.$get_itemids.')';
	
  }else{
	
	$load_only_new_items_filter = '';
	
  }
  
  
  
  
  $filter_in_rows_var = 'a.id = '.$get_liveblog_id;
  
  
  $get_page = (isset($_GET['page'])) ? $_GET['page'] : 1;
  $get_intervallload = (isset($_GET['intervallload'])) ? $_GET['intervallload'] : 0;
  
  if($get_page == 1) {
	
	$sql_limit = 'LIMIT 0, '.$showing_entrys_on_reload;
	
  }else{
	
	//$sql_limit = 'LIMIT '.(($get_page*$showing_entrys_on_reload)-$showing_entrys_on_reload).', '.$showing_entrys_on_reload;
	$sql_limit = 'LIMIT 15,500';
	
  }
  
  if($get_intervallload == 1) {
	$sql_limit = 'LIMIT 0, '.($showing_entrys_on_reload*$get_page);
  }
  
  $output_array['debugme'] = $filter_in_rows_var;
  
	
  $rows =  $wpdb->get_results( 'SELECT b.id, b.minute, b.title, b.post_content, b.source_specs_values, b.url, b.embed_code, b.publication_id, b.period, b.yawave_timestamp, b.person_id, b.person_infos, b.action_id, b.action_infos, b.all_parms, b.timeline_timestamp, b.creation_date, b.liveblog_id FROM '.$wpdb->prefix.'yawave_liveblogs AS a
	  INNER JOIN '.$wpdb->prefix.'yawave_liveblogs_posts AS b ON (a.id = b.liveblog_id)
	  WHERE '.$filter_in_rows_var.$load_only_new_items_filter.' AND b.wp_visible_status = 1
	  ORDER BY b.timeline_timestamp '.$get_sorting.', b.creation_date '.$get_sorting.'
	  '.$sql_limit , ARRAY_A);
	  
  ### count all entrys for "more" button
  
  $count_all_posts =  $wpdb->get_var( 'SELECT COUNT(b.id) FROM '.$wpdb->prefix.'yawave_liveblogs AS a
	  INNER JOIN '.$wpdb->prefix.'yawave_liveblogs_posts AS b ON (a.id = b.liveblog_id)
	  WHERE '.$filter_in_rows_var.' AND b.wp_visible_status = 1');
  
  ###
  
  $output_array['count_all_posts'] = $count_all_posts;
  
  if($count_all_posts > 15 && $get_intervallload == 0) {
	
	$rows_all_ids =  $wpdb->get_results( 'SELECT b.id FROM '.$wpdb->prefix.'yawave_liveblogs AS a
	INNER JOIN '.$wpdb->prefix.'yawave_liveblogs_posts AS b ON (a.id = b.liveblog_id)
	WHERE '.$filter_in_rows_var.' AND b.wp_visible_status = 1
	ORDER BY b.timeline_timestamp '.$get_sorting.', b.creation_date '.$get_sorting , ARRAY_A);
	
	foreach($rows_all_ids as $rows_all_id){
	  if(!in_array($rows_all_id['id'], $liveblog_items_ids)){
		$liveblog_items_ids[] = $rows_all_id['id'];
	  }
	}
	
  }
	
	
  if($count_all_posts > 0 && count($rows) > 0) {
	
	
	
  foreach($rows as $row){
  
	$additional_text = '';
	$player_info_text = '';
	$substitution_addon = '';
	$media_content = '';
	$publication_in_card = '';
	
	$sport_radar_feedback = unserialize($row['source_specs_values']);
	$all_parms_array = unserialize($row['all_parms']);
	
	if($sport_radar_feedback['type'] == 'YELLOW_CARD') {
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/liveblog/icons/yellow_card-hand.png" class="attachment-sportspress-fit-mini size-sportspress-fit-mini wp-post-image" alt="" title="Yellow Card" width="15">';
	  
	  $player_info[$row['id']] = array(
	  'name' => $sport_radar_feedback['players'][0]->name,
	  'player_sr_id' => $sport_radar_feedback['players'][0]->external_id,
	  );
	  
	  $player_info_text = '<strong>'.$player_info[$row['id']]['name'].'</strong><br />';
	
	}elseif($sport_radar_feedback['type'] == 'SUBSTITUTION') {
	
	  if($sport_radar_feedback['players'][0]->type == 'SUBSTITUTED_OUT') {
		$substitution_addon_first = '<span class="liveticker-substituted-out-button">Raus</span>';
	  }
	  
	  if($sport_radar_feedback['players'][0]->type == 'SUBSTITUTED_IN') {
		$substitution_addon_first = '<span class="liveticker-substituted-in-button">Rein</span>';
	  }
	  
	  if($sport_radar_feedback['players'][1]->type == 'SUBSTITUTED_OUT') {
		$substitution_addon_sec = '<span class="liveticker-substituted-out-button">Raus</span>';
	  }
	  
	  if($sport_radar_feedback['players'][1]->type == 'SUBSTITUTED_IN') {
		$substitution_addon_sec = '<span class="liveticker-substituted-in-button">Rein</span>';
	  }
	  
	  if(!empty($sport_radar_feedback['players'][0]->name) && !empty($sport_radar_feedback['players'][1]->name)) {
	  
		if($sport_radar_feedback['competitor']->external_id == 'sr:competitor:2453') {
		  
		  $player_info_text = '<div style="margin-bottom:11px;">'.$substitution_addon_first . '<strong><a href="'.get_the_permalink($player_id_0).'" target="_blank">'.$sport_radar_feedback['players'][0]->name.'</a></strong><br />
		  ' . $substitution_addon_sec . '<strong><a href="'.get_the_permalink($player_id_1).'" target="_blank">'.$sport_radar_feedback['players'][1]->name.'</a></strong></div>';
		}else{
		  $player_info_text = '<div style="margin-bottom:11px;">'.$substitution_addon_first . '<strong>'.$sport_radar_feedback['players'][0]->name.'</strong><br />
		  ' . $substitution_addon_sec . '<strong>'.$sport_radar_feedback['players'][1]->name.'</strong></div>';
		}
	  
	  }      
			
	  
	  
	  $icon = '<i class="sp-icon-sub"></i>';
	
	}elseif($sport_radar_feedback['type'] == 'GOAL_KICK') {
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/liveblog/icons/ball-icon.png" class="attachment-sportspress-fit-mini size-sportspress-fit-mini wp-post-image" alt="" title="Goal" width="15">';
	
	}elseif($sport_radar_feedback['type'] == 'INJURY_TIME_SHOWN') {
	
	  $icon = '<i class="sp-icon-time"></i>';
	  $additional_text = $sport_radar_feedback['injury_time'].' Minuten';
	  $row['title'] = 'Nachspielzeit';
	  
	}elseif($sport_radar_feedback['type'] == 'SCORE_CHANGE') {
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/liveblog/icons/ball-icon.png" class="attachment-sportspress-fit-mini size-sportspress-fit-mini wp-post-image" width="15">';
	  $additional_text = '';
	  
	}else{
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/liveblog/icons/ball-icon.png" class="yawave-liveblog-icon-mini" alt="" width="15">';
	
	}
	
	
	
	if(!empty($row['person_id'])) {
	  
	  $person_info = unserialize($row['person_infos']);
	  
	  ### person icon
	  
	  if(!empty($person_info['icon'])) {
		$person_info_icon = $person_info['icon'];
		$person_info_icon_widht = 50;
	  }else{
		$person_info_icon = plugins_url().'/yawave/assets/img/liveblog/icons/trickot.svg';
		$person_info_icon_widht = 20;
	  }
	  
	  ###
	  
	  $player_info_text = '<div style="margin-bottom:11px;"><img src="'.$person_info_icon.'" width="'.$person_info_icon_widht.'" style="margin-right: 10px;"><strong>'.$person_info['name'].'</strong></div>';
	
	}
	
	
	
	$icon_array = array(
	  'break_start' => 'whistle',
	  'match_ended' => 'whistle',
	  'match_started' => 'whistle',
	  'period_start' => 'whistle',
	  'yellow_card' => 'yellow_card',
	  'yellow_red_card' => 'yellow_red_card',
	  'red_card' => 'red_card',
	  'corner_kick' => 'corner_kick',
	  'throw_in' => 'throw_in',
	  'free_kick' => 'free_kick',
	  'goal_kick' => 'free_kick',
	  'score_change' => 'goal',
	  'substitution' => 'up_down_arrow',
	  'canceled_decision_to_var' => 'memories',
	  'decision_to_var' => 'memories',
	  'decision_to_var_over' => 'memories',
	  'possible_decision_to_var' => 'memories',
	  'video_assistant_referee_over' => 'memories',
	  'video_assistant_referee' => 'memories',
	  'injury_time_shown' => 'extra_time',
	  'shot_off_target' => 'location_on',
	  'shot_on_target' => 'location_off',
	);
	
	if(!empty($icon_array[strtolower($sport_radar_feedback['type'])])){
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/liveblog/icons/'.$icon_array[strtolower($sport_radar_feedback['type'])].'.svg" width="20">';
	
	}else{
	
	  $icon = '<img src="'.plugins_url().'/yawave/assets/img/yawave.svg" class="liveticker-yawave-default-icon" width="20">';
	
	}
	
	if($sport_radar_feedback['stoppage_time'] != 0) {
	
	  $liveblog_minute = $row['minute'].'\'+'.$sport_radar_feedback['stoppage_time'];
	
	}elseif($all_parms_array->stoppage_time != 0) {
		
	  $liveblog_minute = $row['minute'].'\'+'.$all_parms_array->stoppage_time;
	  
	}else{
	  $liveblog_minute = $row['minute'].'\'';
	}
	
	### function
	
	if(!empty($row['action_id'])) {
	  
	  $action_info = unserialize($row['action_infos']);
	  
	  if(!empty($action_info['name'])){
		$row['title'] = $action_info['name'].': '.$row['title'];
	  }
	  
	  ### action icon
	  
	  if(!empty($action_info['icon'])) {
		$icon = '<img src="'.$action_info['icon'].'" class="attachment-sportspress-fit-mini size-sportspress-fit-mini wp-post-image" alt="'.$action_info['name'].'" title="'.$action_info['name'].'" width="15">';
	  }
	  
	  ###
	  
	
	}
	
	###
	
	if($sport_radar_feedback['type'] == 'MATCH_ENDED' || $sport_radar_feedback['type'] == 'BREAK_START' || ($sport_radar_feedback['type'] == 'PERIOD_START' && ($row['period'] == 'SECOND' || $row['period'] == 'OVERTIME' || $row['period'] == 'PENALTIES' || $row['period'] == 'FIRST'))){
	  
	  $title_for_feedback = array(
		'MATCH_ENDED' => 'Spiel beendet!',
		'BREAK_START' => 'Pause!',
		'PERIOD_START' => 'Es geht los!',
	  );
	  
	  if($row['period'] == 'SECOND') {
	  
		$title_for_feedback['PERIOD_START'] = 'Beginn zweite Halbzeit!';
		
	  }elseif($row['period'] == 'OVERTIME') {
		
		$title_for_feedback['PERIOD_START'] = 'Beginn Verlängerung';
		
	  }elseif($row['period'] == 'PENALTIES') {
		
		$title_for_feedback['PERIOD_START'] = 'Beginn Elfmeterschießen';
		
	  }
	  
	  if($sport_radar_feedback['type'] == 'PERIOD_START') {
	  
	  $output_array['html'] .= '<div class="liveticker-textarea">
	
	  <span class="text pt-2 pb-2">'.$title_for_feedback[$sport_radar_feedback['type']].'</span>
	  
	  </div>';
	  
	}else{
	  
	  $output_array['html'] .= '<div class="liveticker-textarea">
  
	  <span class="text">'.$title_for_feedback[$sport_radar_feedback['type']].'</span>
	  <img src="'.get_the_post_thumbnail_url($team[0]).'" class="team-logo" />
	  <div class="spielstand">'.$sport_radar_feedback['home_score'].' : '.$sport_radar_feedback['away_score'].'</div>
	  <img src="'.get_the_post_thumbnail_url($team[1]).'" class="team-logo" />
	  
	  </div>';
	  
	}
	  
	  
	  
	}elseif(!empty($row['post_content']) || !empty($row['url']) || !empty($row['title']) || !empty($row['embed_code']) || !empty($row['publication_id']) || !empty($additional_text) || !empty($player_info_text)) {	  
	
	  if($sport_radar_feedback['type'] == 'SCORE_CHANGE') {
		
		if($row['period'] == 'FIRST' || $row['period'] == 'SECOND') {
		
		  $goal_score_home = $sport_radar_feedback['home_score'];
		  $goal_score_away = $sport_radar_feedback['away_score'];
		  
		}elseif($row['period'] == 'OVERTIME') {
		  
		  $goal_score_home = $all_parms_array->home_score;
		  $goal_score_away = $all_parms_array->away_score;
		  
		}elseif($row['period'] == 'PENALTIES') {
		  
		  $goal_score_home = $all_parms_array->home_score + $all_parms_array->penalties_home_score;
		  $goal_score_away = $all_parms_array->away_score + $all_parms_array->penalties_away_score;
		  
		}
		
		$output_array['html'] .= '<div class="liveticker-textarea">
		
		  <span class="text">TOR!</span>
		  <img src="'.get_the_post_thumbnail_url($team[0]).'" class="team-logo" />
		  <div class="spielstand">'.$goal_score_home.' : '.$goal_score_away.'</div>
		  <img src="'.get_the_post_thumbnail_url($team[1]).'" class="team-logo" />
		  
		  </div>';
	  
	  }
	  
	  if($sport_radar_feedback['type'] == 'PERIOD_SCORE') {
	  
		$row['title'] = 'Halbzeit';
	  
	  }elseif($sport_radar_feedback['type'] == 'MATCH_STARTED') {
		
		$row['title'] = 'Es geht los!';
		
	  }
	
	  
	  if(!empty($row['url'])) {
	  
		#### check what for link is set
		
		if(strpos($row['url'], 'youtube.com')) {
		
		  $media_type = 'youtube';
		
		}elseif(strpos($row['url'], '.jpg') || strpos($row['url'], '.png') || strpos($row['url'], '.gif')) {
		
		  $media_type = 'image';
		
		}else{
		  
		  $media_type = '';
		  
		}
			
		####
		
		if($media_type == 'youtube') {
		
		  function convertYoutube($string) {
			return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
			"<iframe src=\"//www.youtube.com/embed/$2\" allowfullscreen style='width: 100%; height: 450px;'></iframe>",
			$string
			);
		  }
		
		  $media_content = convertYoutube($row['url']);
		
		}elseif($media_type == 'image') {
		 
		 $media_content = '<img src="'.$row['url'].'" width="100%" />';
		  
		}
		
	  }
	  
	  
	  
	  ### time showing in headline
	  
	  $liveblog_minute_class = '';
	  
	  $month_name_german = array(
		1 => 'Januar',
		2 => 'Februar',
		3 => 'März',
		4 => 'April',
		5 => 'Mai',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'August',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Dezember'
	  );
	  
	  if($row['period'] == 'YEAR') {
		$liveblog_minute = date('Y', strtotime($row['yawave_timestamp']));
	  }elseif($row['period'] == 'DATE') {
		
		$liveblog_minute = date('d.', strtotime($row['yawave_timestamp'])).' '.$month_name_german[date('n', strtotime($row['yawave_timestamp']))].' '.date('Y', strtotime($row['yawave_timestamp']));        
		$liveblog_minute_class = 'liveticker-time-date';
		
	  }elseif($row['period'] == 'TIME') {
		
		date_default_timezone_set('Europe/Berlin');
		$liveblog_minute = date('H:i', strtotime($row['yawave_timestamp'])).' &nbsp;&nbsp;|&nbsp;&nbsp; '.date('d.', strtotime($row['yawave_timestamp'])).' '.$month_name_german[date('n', strtotime($row['yawave_timestamp']))].' '.date('Y', strtotime($row['yawave_timestamp']));        
		
		
		
		$liveblog_minute_class = 'liveticker-time-time';
		
	  }
	  
	  ### set default text when no post content come
	  
	  $default_type_text_array = array(
		 'SHOT_SAVED' => 'Torschuss gehalten vom '.$row['title'],
		 'POSSIBLE_GOAL' => 'Tor Möglichkeit für den '.$row['title'],
		 'CORNER_KICK' => 'Eckball für den '.$row['title'],
		 'INJURY_TIME_SHOWN' => '',
		 'PENALTY_AWARDED' => 'Elfmeter für den '.$row['title'],
		 'THROW_IN' => 'Einwurf für den '.$row['title'],
		 'VIDEO_ASSISTANT_REFEREE' => 'Die Szene wird vom VAR &uuml;berpr&uuml;ft',
		 'VIDEO_ASSISTANT_REFEREE_OVER' => 'Die &Uuml;berpr&uuml;fung der Szene durch den VAR ist abgeschlossen',
	   );
	  
	  if(empty($row['post_content'])) {
		
		if(array_key_exists($sport_radar_feedback['type'], $default_type_text_array)) {
		  $post_content = $default_type_text_array[$sport_radar_feedback['type']];
		}else{
		  $post_content = str_replace('_', ' ', $sport_radar_feedback['type']);
		}
		
	  }else{
		
		$post_content = $row['post_content'];
		
	  }
	  
	  ###
	  
	  if(!empty($row['publication_id'])) {
		 
		   ### get wordporess id from yawave id
		   
		   $wp_publication_id = $wpdb->get_var('SELECT post_id FROM wp_postmeta WHERE meta_value = "'.$row['publication_id'].'"');
		   
		   ###
		   
		   if($wp_publication_id > 0) {
				
			   $fockus_point_addon = 'style="background-image: url('.(!empty($header_image) ? str_replace(' ', '%20', $header_image) : get_the_post_thumbnail_url($wp_publication_id, 'full')).'); background-size: cover;"';
				 
				 $fockus_point_css_class_addon = '';
				   
				   ### check if action buttons are setup
				   
				   $buttons = get_post_meta($wp_publication_id, 'action_buttons', false);
					 
					 if(!empty($buttons[0])) {
					   foreach($buttons[0] AS $button) {
						 $action_buttons .= $button['code'].' ';      
					   }    
					   $add_action_buttons = '<div id="yawave-liveblog-action-buttons">'.$action_buttons.'</div>';    
					 }else{    
					   $add_action_buttons = '<div id="yawave-liveblog-action-buttons"><a href="'.get_the_permalink($wp_publication_id).'" class="btn">Zum Artikel</a></div>';    
					 }
				   
				   ###
				   
			   $output_array['html'] .= '<div class="yawave-liveblog-card liveticker-publication" data-liveblogpostid="'.$row['id'].'" data-sort="'.strtotime($row['timeline_timestamp']).'" data-tickerid="'.$row['id'].'">
				 <header class="yawave-liveblog-card-header">
				 <h4>'.((!empty($row['minute']) || (!empty($liveblog_minute) && $liveblog_minute != "0'")) ? '<div class="liveticker-time '.$liveblog_minute_class.'"><span>'.$liveblog_minute.'</span></div>' : '').$row['title'].'</h4>
				 </header>
			   <div class="yawave-liveblog-card-content liveticker-publication-content focuspoint '.$fockus_point_css_class_addon.'" '.$fockus_point_addon.' >
			   
				
				  
			   
			   <div class="liveticker-publication-container">
			   
			   <div class="liveticker-publication-wrapper">
			   
				 <div id="yawave-header">
				   <h1><a href="'.get_the_permalink($wp_publication_id).'" target="_blank" style="color:#fff;">'.get_the_title($wp_publication_id).'</a></h1>
				   <div id="yawave-buttons">
				   	'.$add_action_buttons.'
				   </div>
				  </div>
			   
			   </div>
			   
			   </div>
			   
			   
			   </div>
			   </div>';
		   
		   }
		  
		}else{
			
		
			$output_array['html'] .= '<div class="yawave-liveblog-card" data-liveblogpostid="'.$row['id'].'" data-sort="'.strtotime($row['timeline_timestamp']).'" data-tickerid="'.$row['id'].'">
			  <header class="yawave-liveblog-card-header">
			  <h4>'.((!empty($row['minute']) || (!empty($liveblog_minute) && $liveblog_minute != "0'")) ? '<div class="liveticker-time '.$liveblog_minute_class.'"><span>'.$liveblog_minute.'</span></div>' : '').$row['title'].'</h4>
			  </header>
			  <div class="yawave-liveblog-card-content">
			  <div>
			  
			  <div class="liveticker-icon" '.((!empty($row['embed_code']) || !empty($media_content)) ? 'style="display: none;"' : '').'>
			  '.$icon.'
			  </div>
			  
			  <div class="liveticker-info" '.((!empty($row['embed_code']) || !empty($media_content)) ? 'style="width: 100%!important;"' : '').'>
			  '.((!empty($player_info_text)) ? $player_info_text : '').'
			  '.((!empty($additional_text)) ? '<strong>'.$additional_text.'</strong><br />' : '').'
			  '.((!empty($post_content)) ? $post_content : '').'
			  '.((!empty($row['embed_code'])) ? $row['embed_code'] : '').'
			  '.((!empty($media_content)) ? $media_content : '').'
			  
			  
			  </div>
			  
			  <div class="clear"></div>
			  
			  '.((!empty($publication_in_card)) ? $publication_in_card : '').'
			  
			  </div>
			  
			  
			  </div>
			  </div>';
	  
	  }
	  
	  
	  if($sport_radar_feedback['type'] == 'MATCH_STARTED') {
	  
		$output_array['html'] .= '<div class="liveticker-textarea">
		
		  <span class="text">Anpfiff!</span>
		  <img src="'.get_the_post_thumbnail_url($team[0]).'" class="team-logo" />
		  <div class="spielstand">0 : 0</div>
		  <img src="'.get_the_post_thumbnail_url($team[1]).'" class="team-logo" />
		  
		  </div>';
	  
	  }
	
	
	
	
	
	
	
	}
	
	//var_dump($row['id']);
	//die('123');
	  
	if(!in_array($row['id'], $liveblog_items_ids)){
	  $liveblog_items_ids[] = $row['id'];
	}
	
	### get id befire this box for plaement with javascript
	
	if(!empty($load_only_new_items_filter)) {
	  
	  $before_ids_rows =  $wpdb->get_row( 'SELECT b.id FROM '.$wpdb->prefix.'yawave_liveblogs AS a
		INNER JOIN '.$wpdb->prefix.'yawave_liveblogs_posts AS b ON (a.id = b.liveblog_id)
		WHERE 
		b.timeline_timestamp < "'.$row['timeline_timestamp'].'" AND 
		b.creation_date < "'.$row['creation_date'].'" AND 
		b.id NOT IN ('.$row['id'].') AND 
		b.liveblog_id = '.$row['liveblog_id'].' AND b.wp_visible_status = 1
		ORDER BY b.timeline_timestamp DESC, b.creation_date DESC
		LIMIT 1' , ARRAY_A);        
		
	  $get_id_before_this_id = $before_ids_rows['id'];
	  
	}
	
	###
	
  
  }
  //$output_array['html'] .= '<input type="text" name="" id="load_ticker_ids" value="'.substr($liveblog_items_ids, 0, -1).'" />';
  
  
  $output_array['ids'] = $liveblog_items_ids;
  $output_array['html'] .= '<div id="liveblog-load-more-entrys-page-'.$get_page.'" data-liveblogpage="'.$get_page.'"></div>';
  $output_array['before_id'] = $get_id_before_this_id;
  $output_array['no_items'] = 0;
  
  
  }elseif($count_all_posts == 0) {
	
	$output_array['ids'] = 0;
	
	$output_array['html'] .= '<div class="yawave-liveblog-card no-liveblog-posts">
	<header class="yawave-liveblog-card-header">
		<h4>Keine Einträge vorhanden!</h4>
	</header>
	<div class="yawave-liveblog-card-content">
	<div>
	
	<div class="liveticker-icon">
	'.$icon.'
	</div>
	
	<div class="liveticker-info">Für diesen Liveblog wurden noch keine Einträge erstellt.</div>
	
	<div class="clear"></div>
	
	</div>
	
	
	</div>
	</div>';
	
	$output_array['no_items'] = 1;
	
  }
  
  
  
  echo json_encode($output_array);
  
  exit();
  
}