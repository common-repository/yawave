<div class="wrap">
	
	<h1>Yawave Liveblogs</h1>
	
	<p><?=__('Here you can see a list of the live blogs you have created in Yawave. You can place these anywhere on your pages or similar by simply copying the WordPress shortcode from the table below.', 'yawave')?></p>
	
	<div id="setting-error-tgmpa" class="notice notice-success settings-error"> 
		<h3><?=__('How do you integrate live blogs on your site?', 'yawave')?></h3>
		<p><?=__('The best way to do this is to use our shortcodes. You can use these shortcodes freely in your posts, pages or directly in the code. On the one hand, you have the option of viewing a special live blog from the list below. However, if you just want to always display the most recently created live blog at a certain point, simply use the following shortcode:', 'yawave')?></p>
		<p><strong>[yawave-liveblog]</strong></p>
	</div>
	
	<?php
	
	//Our class extends the WP_List_Table class, so we need to make sure that it's there
	if(!class_exists('WP_List_Table')){
	   require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}
	
	class Liveblog_List_Table extends WP_List_Table {
		
	   /**
		* Constructor, we override the parent to pass our own arguments
		* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
		*/
		function __construct() {
		   parent::__construct( array(
		  'singular'=> 'wp_list_text_liveblog', //Singular label
		  'plural' => 'wp_list_text_liveblogs', //plural label, also this well be one of the table css class
		  'ajax'   => false //We won't support Ajax for this table
		  ) );
		}
		
		
		/**
		 * Define the columns that are going to be used in the table
		 * @return array $columns, the array of columns to use with the table
		 */
		function get_columns() {
		   return $columns = array(
			  'col_id' => 'ID',
			  'col_createtime' => __('Date', 'yawave'),
			  'col_updatetime' => __('Update date', 'yawave'),
			  'col_title' => __('Event', 'yawave'),
			  'col_standort' => __('Location', 'yawave'),
			  'col_shortcode' => __('Shortcode', 'yawave'),
			  'col_json' => __('json', 'yawave'),
			  'col_options' => __('Actions', 'yawave'),
		   );
		}
		
		/**
		 * Decide which columns to activate the sorting functionality on
		 * @return array $sortable, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
		   return $sortable = array(
			  'col_id' => array('id', false),
			  'col_createtime' => array('start_date', false),
			  'col_title' => array('title', false),
		   );
		}
		
		/**
		 * Prepare the table with different parameters, pagination, columns and table elements
		 */
		function prepare_items() {
		   global $wpdb, $_wp_column_headers;
		   $screen = get_current_screen();
		
		   /* -- Preparing your query -- */
				$query = "SELECT * FROM ".$wpdb->prefix."yawave_liveblogs";
		
		   /* -- Ordering parameters -- */
			   //Parameters that are going to be used to order the result
			   $orderby = !empty($_GET["orderby"]) ? $wpdb->esc_like($_GET["orderby"]) : 'ASC';
			   $order = !empty($_GET["order"]) ? $wpdb->esc_like($_GET["order"]) : '';
			   if(!empty($orderby) && !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
		
		   /* -- Pagination parameters -- */
				//Number of elements in your table?
				$totalitems = $wpdb->query($query); //return the total number of affected rows
				//How many to display per page?
				$perpage = 5;
				//Which page is this?
				$paged = !empty($_GET["paged"]) ? $wpdb->esc_like($_GET["paged"]) : '';
				//Page Number
				if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; } //How many pages do we have in total? 
				$totalpages = ceil($totalitems/$perpage); //adjust the query to take pagination into account 
				if(!empty($paged) && !empty($perpage)){ $offset=($paged-1)*$perpage; $query.=' LIMIT '.(int)$offset.','.(int)$perpage; } 
				/* -- Register the pagination -- */ 
				$this->set_pagination_args( array(
					 "total_items" => $totalitems,
					 "total_pages" => $totalpages,
					 "per_page" => $perpage,
				  ) );
			  //The pagination links are automatically built according to those parameters
		
		   
			  
			  $this->_column_headers = array($this->get_columns(), array(), $this->get_sortable_columns());
			  
		   /* -- Fetch the items -- */
			  $this->items = $wpdb->get_results($query);
			  
			  
			  
		}
		
		/**
		 * Display the rows of records in the table
		 * @return string, echo the markup of the rows
		 */
		function display_rows() {
		
		   //Get the records registered in the prepare_items method
		   $records = $this->items;
		   
		   
		   
		   //Get the columns registered in the get_columns and get_sortable_columns methods
		   //list( $columns, $hidden ) = $this->get_column_info();
		   
		   $columns = $this->get_columns();
		   $hidden = array();
		   
		   //Loop for each record
		   if(!empty($records)){foreach($records as $rec){
		
			  //Open the line
				echo '<tr id="record_'.$rec->id.'">';
				
				
			  foreach ( $columns as $column_name => $column_display_name ) {
		
				 //Style attributes for each col
				 $class = "class='$column_name column-$column_name'";
				 $style = "";
				 if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				 $attributes = $class . $style;
		
				 //Display the cell
				 switch ( $column_name ) {
					case "col_id":  echo '<td '.$attributes.'><a href="admin.php?'.$_SERVER['QUERY_STRING'].'&id='.stripslashes($rec->id).'">'.stripslashes($rec->id).'</a></td>';   break;
					case "col_createtime": echo '<td '.$attributes.'>'.stripslashes(date('d.m.Y', strtotime($rec->start_date))).' '.stripslashes(date('H:i', strtotime($rec->start_date))).' Uhr</td>'; break;
					case "col_updatetime": echo '<td '.$attributes.'>'.stripslashes(date('d.m.Y', strtotime($rec->updatetime))).' '.stripslashes(date('H:i', strtotime($rec->updatetime))).' Uhr</td>'; break;
					case "col_title": echo '<td '.$attributes.'><a href="admin.php?'.$_SERVER['QUERY_STRING'].'&id='.stripslashes($rec->id).'">'.stripslashes($rec->title).'</a></td>'; break;
					case "col_standort": echo '<td '.$attributes.'>'.stripslashes($rec->location).'</td>'; break;
					case "col_shortcode": echo '<td '.$attributes.'>[yawave-liveblog liveblog-id="'.stripslashes($rec->id).'"]</td>'; break;
					case "col_json": echo '<td '.$attributes.'><textarea>'.stripslashes($rec->yawave_json).'</textarea></td>'; break;
					case "col_options": echo '<td '.$attributes.'><a href="admin.php?'.$_SERVER['QUERY_STRING'].'&id='.stripslashes($rec->id).'">'.__('Show entrys', 'yawave').'</a> | <a href="admin.php?'.$_SERVER['QUERY_STRING'].'&lid='.stripslashes($rec->id).'&mode=delete" onclick="return confirm(\''.__('Do you really want to remove this live blog from your Wordpress? All entries for this live blog will be completely removed from your Wordpress system.', 'yawave').'\')">'.__('Delete', 'yawave').'</a></td>'; break;
				 }
			  }
		
			  //Close the line
			  echo'</tr>';
		   }}
		}
	
	}
	
	$wp_list_table = new Liveblog_List_Table();
	$wp_list_table->prepare_items();
	
	//Table of elements
	$wp_list_table->display();
	
	?>
	
</div>