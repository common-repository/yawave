<?php

#### disable entry

$get_action = (isset($_GET['mode'])) ? $_GET['mode'] : '';
$get_liveblog_post_id = (isset($_GET['postid'])) ? $_GET['postid'] : '';
$get_liveblog_post_status = (isset($_GET['poststatus'])) ? $_GET['poststatus'] : '';

if($get_action == 'updatestatus') {
	
	$data = array('wp_visible_status' => $get_liveblog_post_status);
	$where = array('id' => $get_liveblog_post_id);
	
	$update_status = $wpdb->update($wpdb->prefix.'yawave_liveblogs_posts', $data, $where);
	
}

####

$liveblog_details = $wpdb->get_row( 'SELECT title, description FROM '.$wpdb->prefix.'yawave_liveblogs WHERE id = '.$get_liveblog_id, ARRAY_A); 

?>

<div class="wrap">
	
	<h1><?=$liveblog_details['title']?></h1>
	
	<p><?=$liveblog_details['description']?></p>
	
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
		  'singular'=> 'wp_list_text_items_liveblog', //Singular label
		  'plural' => 'wp_list_text_items_liveblogs', //plural label, also this well be one of the table css class
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
			  'col_title' => __('Title', 'yawave'),
			  'col_content' => __('Content', 'yawave'),
			  'col_json' => __('json', 'yawave'),
			  'col_actions' => __('Actions', 'yawave'),
		   );
		}
		
		/**
		 * Decide which columns to activate the sorting functionality on
		 * @return array $sortable, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
		   return $sortable = array(
			  'col_id' => array('id', false),
			  'col_createtime' => array('creation_date', false),
			  'col_title' => array('title', false),
		   );
		}
		
		/**
		 * Prepare the table with different parameters, pagination, columns and table elements
		 */
		function prepare_items() {
		   global $wpdb, $_wp_column_headers;
		   $screen = get_current_screen();
		   
		   		$get_liveblog_id = (isset($_GET['id'])) ? $_GET['id'] : '';
		   
		   /* -- Preparing your query -- */
				$query = "SELECT * FROM ".$wpdb->prefix."yawave_liveblogs_posts WHERE liveblog_id = ".$get_liveblog_id;
		
		   /* -- Ordering parameters -- */
			   //Parameters that are going to be used to order the result
			   $orderby = !empty($_GET["orderby"]) ? $_GET["orderby"] : 'ASC';
			   $order = !empty($_GET["order"]) ? $wpdb->esc_like($_GET["order"]) : '';
			   if(!empty($orderby) && !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }
				
		   /* -- Pagination parameters -- */
				//Number of elements in your table?
				$totalitems = $wpdb->query($query); //return the total number of affected rows
				//How many to display per page?
				$perpage = 25;
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
					case "col_id":  echo '<td '.$attributes.'>'.stripslashes($rec->id).'</td>';   break;
					case "col_createtime": echo '<td '.$attributes.'>'.stripslashes(date('d.m.Y', strtotime($rec->creation_date))).' '.stripslashes(date('H:i', strtotime($rec->creation_date))).' Uhr</td>'; break;
					case "col_updatetime": echo '<td '.$attributes.'>'.(($rec->update_date != '0000-00-00 00:00:00') ? (stripslashes(date('d.m.Y', strtotime($rec->update_date))).' '.stripslashes(date('H:i', strtotime($rec->update_date))).' Uhr') : '-').'</td>'; break;
					case "col_title": echo '<td '.$attributes.'>'.stripslashes($rec->title).'</td>'; break;
					case "col_content": echo '<td '.$attributes.'>'.stripslashes($rec->post_content).'</td>'; break;
					case "col_json": echo '<td '.$attributes.'><textarea>'.stripslashes($rec->all_parms).'</textarea></td>'; break;
					case "col_actions": 
						$new_post_status_value = ($rec->wp_visible_status == 1) ? 0 : 1;
						$new_post_status_text = ($rec->wp_visible_status == 1) ? __('Hide', 'yawave') : __('Show', 'yawave');
						echo '<td '.$attributes.'><a href="?page='.$_REQUEST['page'].'&id='.$_REQUEST['id'].'&mode=updatestatus&postid='.stripslashes($rec->id).'&poststatus='.$new_post_status_value.'">'.$new_post_status_text.'</a></td>'; 
						break;
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