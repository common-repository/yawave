<?php

/*
 * Plugin Name: Yawave
 * Plugin URI: https://yawave.com/de/integrate/
 * Description: Import publication from Yawave
 * Version: 2.9.1
 * Author: Yawave
 * Text Domain: Yawave
 * Author URI: https://www.yawave.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace Yawave;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

### update db tables

$update_db_version = 20230720;

$installed_db_ver = get_option('yawave_db_version');

if ($installed_db_ver < $update_db_version || !isset($installed_db_ver)) {
    
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  
  $search = array('{WPPREFIX}', '{WPCOLLATE}');
  $replace = array($wpdb->prefix, $wpdb->collate);
  
  ###
  
  $update_liveblog_sql = file_get_contents(plugin_dir_path( __FILE__ ) . 'sql/update_v2021421.sql');
  $create_liveblog_table = str_replace($search, $replace, $update_liveblog_sql);  
  dbDelta($create_liveblog_table);
  
  ###
  
  update_option('yawave_db_version', $update_db_version);
  
}

###


$plugin_version = '0.0.1';
define('YAWAVE_VERSION', $plugin_version);
define( 'MY_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

### get the api mode from settings
  
$yawave_settings_mode = get_option('yawave_settings_development_option');

if(isset($yawave_settings_mode)) {
  
  if($yawave_settings_mode['yawave_development_mode'] == 'dev') {
    
    define('YAWAVE_API_MODE', 'dev');
    
  }elseif($yawave_settings_mode['yawave_development_mode'] == 'prod') {
    
   define('YAWAVE_API_MODE', 'prod');
    
  }else{
    
    define('YAWAVE_API_MODE', 'prod');
    
  }
  
}else{
  
  define('YAWAVE_API_MODE', 'prod');
  
}

###

class Yawave {

    /**
     * @var WP_Example_Request
     */
    protected $process_single;

    /**
     * @var WP_Example_Process
     */
    public $process_all;

    /**
     * Loading all dependencies
     * @return void
     */
    public function load() {

        include_once 'config.php';
        
        include_once 'includes/wp-async-request.php';
        include_once 'includes/wp-background-process.php';

        include_once 'includes/trait-importer.php';
        include_once 'includes/trait-categories-importer.php';
        include_once 'includes/trait-tags-importer.php';
        include_once 'includes/trait-portals-importer.php';
        include_once 'includes/trait-publications-importer.php';
        include_once 'includes/liveblogs.php';
        include_once 'includes/class-importer-request.php';
        include_once 'includes/class-importer-process.php';

        $this->process_single = new WP_Yawave_Importer_Request();
        $this->process_all = new WP_Yawave_Importer_Process();

        include_once 'includes/metadata.php';
        include_once 'includes/settings.php';
        
        include_once 'includes/shortcode.liveblog.php';
        include_once 'includes/shortcodes.php';
        
    }

}

function yawave_load() {
    $pg = new Yawave();
    $pg->load();
}

// We need to call the function with the namespace
add_action('plugins_loaded', 'Yawave\yawave_load');

function yawave_publication_befree_preview_handler() {
    // Make your response and echo it.
    $id = sanitize_text_field($_GET['id']);
    $content = get_post_meta($id, 'article');
    if (isset($content[0]))
        echo $content[0];

    // Don't forget to stop execution afterward.
    wp_die();
}

add_action('wp_ajax_publication', 'Yawave\yawave_publication_befree_preview_handler');
add_action('wp_ajax_nopriv_publication', 'Yawave\yawave_publication_befree_preview_handler');



add_action('wp_ajax_yawave_portals', 'Yawave\update_portals');
add_action('wp_ajax_nopriv_yawave_portals', 'Yawave\update_portals');


add_action('wp_ajax_yawave_categories', 'Yawave\update_categories');
add_action('wp_ajax_nopriv_yawave_categories', 'Yawave\update_categories');


add_action('wp_ajax_yawave_tags', 'Yawave\update_tags');
add_action('wp_ajax_nopriv_yawave_tags', 'Yawave\update_tags');

function update_portals() {
    $process_all = new WP_Yawave_Importer_Process();
    $process_all->push_to_queue("portals");
    $process_all->save()->dispatch();
    echo "OK";
}

function update_categories() {
    $process_all = new WP_Yawave_Importer_Process();
    $process_all->push_to_queue("categories");
    $process_all->save()->dispatch();
    echo "OK";
}

function update_tags() {
    $process_all = new WP_Yawave_Importer_Process();
    $process_all->push_to_queue("tags");
    $process_all->save()->dispatch();
    echo "OK";
}

add_action('wp_ajax_yawave_update', 'Yawave\yawave_update');
add_action('wp_ajax_nopriv_yawave_update', 'Yawave\yawave_update');

function yawave_update()
{
    
    
    $body = file_get_contents("php://input");
    $post_vars = json_decode($body);
    
    
    $liveblog_actions = array(
      'liveblog:created',
      'liveblog:updated',
      'liveblog:deleted',
      'liveblogPost:created',
      'liveblogPost:updated',
      'liveblogPost:deleted',
    );
    
    $category_actions = array(
      'category:created',
      'category:updated',
      'category:deleted',
    );
    
    $tag_actions = array(
      'tag:created',
      'tag:updated',
      'tag:deleted',
    );
    
    if(in_array($post_vars->event_type, $liveblog_actions)) {
      
      $process_all = new WP_Yawave_Importer_Process();
      $process_all->set_api_token_and_app_id();
      
      $process_all->update_liveblog_magic($post_vars);
      
    }elseif(in_array($post_vars->event_type, $category_actions)) {
      
      $process_all = new WP_Yawave_Importer_Process();
      $process_all->set_api_token_and_app_id();
      
      $process_all->update_single_categorie($post_vars);
      
    }elseif(in_array($post_vars->event_type, $tag_actions)) {
      
      $process_all = new WP_Yawave_Importer_Process();
      $process_all->set_api_token_and_app_id();
      
      $process_all->update_single_tag($post_vars);
      
    }else{
    
    
      if (isset($post_vars->content->id)) {
          $response['message'] = 'Updating publication...';
          $response['status'] = 'ok';
          $post = get_wp_publication_by_yawave_id($post_vars->content->id);
          
          if ($post_vars->event_type != "publication:created") {
              
              if($post_vars->content->status == 'DELETED') {
                //if ($post[0]->ID > 0) {
                  foreach($post_vars->content->languages AS $language_id) {
                    $post_lang_delete = get_wp_publication_by_yawave_id($post_vars->content->id, $language_id);
                    wp_delete_post($post_lang_delete[0]->ID, true);
                  }
                //}
              }else{
                if ($post[0]->ID > 0) {
                    wp_update_post(array(
                        'ID'    =>  $post[0]->ID,
                        'post_status'   =>  'draft'
                    ));
                }
              }
            
              
          } else {
              if ($post[0]->ID > 0) {
                  wp_update_post(array(
                      'ID'    =>  $post[0]->ID,
                      'post_status'   =>  'publish'
                  ));
              }
              
          }
          
          
      } else {
          $response['message'] = 'Missing params';
          $response['status'] = 'error';
      }
      
      
      
      if($post_vars->content->status != 'DELETED') {

        $process_all = new WP_Yawave_Importer_Process();
        $process_all->set_api_token_and_app_id();
        //$process_all->update_categories(0);
        //$process_all->update_tags(0);
        $process_all->update_portals(0);
        $process_all->update_single_publication($post_vars->content->id, $post_vars->content->status, $post_vars->content, $post_vars);
        //$process_all->update_categories(0, 1);
        echo "update initialized...";

      }
      
    
    }
    
    save_log(json_encode($post_vars), 'yawave_update');
    
     
    exit();
    
    
     
}

 function save_log($message, $slug = '') {
    
    global $wpdb;
    
    $data = array(
        'logtime'    => current_time('mysql', 1), 
        'logdata'    => ((!empty($message)) ? $message : 0), 
        'slug'       => ((!empty($slug)) ? $slug : 0),
        );
    
    $query = $wpdb->insert($wpdb->prefix.'yawave_log', $data);
    
}


function get_wp_publication_by_yawave_id($yawave_id, $language = 'de') {
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

### place scripts and styles

$auth_options = get_option('yawave_settings_authorization_option');

class yawavePostIdInHeader {
    private $postId;    
    public function __construct($wp_query) {
        if ($wp_query && $wp_query->post) {
            $this->postId = $wp_query->post->ID;
        }
    }
    public function getPostId() {
        return $this->postId;
    }
}

add_action('wp', function () {    
  if(!is_home()) {      
    
    
    $sdk_autocreate_mode = get_option('yawave_settings_development_option');
    $user = wp_get_current_user();
    
    global $wp_query;
    $yawavePostIdInHeader = new yawavePostIdInHeader($wp_query);
    $wp_post_id = $yawavePostIdInHeader->getPostId();       
    ###
    $auth_options = get_option('yawave_settings_authorization_option');    
    ###    
    if($wp_post_id) {
    $publication_id = get_post_meta($wp_post_id, 'yawave_id');
    $yawave_load_sdk = get_post_meta($wp_post_id, 'yawave_load_sdk');
    if(!empty($publication_id[0])) {
      $js_publication_id = $publication_id[0];
    }else{
      $js_publication_id = 0;
    }
    }else{
    $js_publication_id = 0;
    }    
    ###    
    if (class_exists('SitePress')) {
    global $sitepress;
    if ($sitepress) {
      $current_lang = $sitepress->get_current_language();
    }else{
      $current_lang = 'de';    
    }
    }else{
      $current_lang = 'de';
    }   
    ### 
    
    
    $sdk_app_id = ($sdk_autocreate_mode['yawave_development_mode'] == 'dev') ? $auth_options['yawave_dev_authorization_key'] : $auth_options['yawave_authorization_key'];
    $sdk_domain = ($sdk_autocreate_mode['yawave_development_mode'] == 'dev') ? 'test-yawave.com' : 'yawave.com';


    $yawave_sdk_vars = array(
    'sdk_client_id' => $sdk_app_id,
    'sdk_language_code' => $current_lang,
    'sdk_publication_id' => $js_publication_id,
    'sdk_domain' => $sdk_domain,
    );   
    
    if($sdk_autocreate_mode['yawave_sdk_autocreate_mode'] == 'autocreate_sdk') {
      
      if($yawave_load_sdk[0] == 'autocreate') {
        wp_register_script('yawave-wp-sdk-script', plugins_url('yawave/assets/js/yawave.sdk.autocreate.js', dirname(__FILE__)), 0, '2.0');
      }elseif($yawave_load_sdk[0] == 'default') {
        wp_register_script('yawave-wp-sdk-script', plugins_url('yawave/assets/js/yawave.sdk.js', dirname(__FILE__)), 0, '2.0'); 
      }
      
    }elseif($sdk_autocreate_mode['yawave_sdk_autocreate_mode'] == 'regular_sdk' || $sdk_autocreate_mode['yawave_sdk_autocreate_mode'] == 'no') {
      wp_register_script('yawave-wp-sdk-script', plugins_url('yawave/assets/js/yawave.sdk.js', dirname(__FILE__)), 0, '2.0');
    }
    
    wp_enqueue_script( 'yawave-wp-sdk-script' );
    wp_localize_script( 'yawave-wp-sdk-script', 'yawavesdkobject', $yawave_sdk_vars ); 
    
  }
  
});

add_action( 'wp_enqueue_scripts', 'Yawave\my_plugin_assets' );
function my_plugin_assets() {
    wp_enqueue_script( 'yawave-livelog-js', plugins_url('yawave/assets/js/yawave.liveblog.js', dirname(__FILE__)), array( 'jquery' ) );
    wp_enqueue_script( 'yawave-focuspoint-js', plugins_url('yawave/assets/js/yawave.focuspoint2.min.js', dirname(__FILE__)) );
    wp_enqueue_script( 'yawave-app-js', plugins_url('yawave/assets/js/yawave.app.js', dirname(__FILE__)) );
    wp_enqueue_script( 'yawave-liveblog-block', plugin_dir_url( __FILE__ ) . 'assets/js/yawave.liveblog.block.js', array( 'wp-blocks', 'wp-editor' ),  filemtime( dirname( __FILE__ ) . '/assets/js/yawave.liveblog.block.js' ));
    wp_enqueue_script( 'yawave-publications-block', plugin_dir_url( __FILE__ ) . 'assets/css/yawave.liveblog.block.css', array( 'wp-blocks', 'wp-editor' ), filemtime( dirname( __FILE__ ) . '/assets/js/yawave.publications.block.js' ));
    
    wp_enqueue_style( 'yawave-liveblog-css', plugins_url('yawave/assets/css/yawave.liveblog.default.css', dirname(__FILE__)) );
    wp_enqueue_style( 'yawave-focuspoint-css', plugins_url('yawave/assets/css/yawave.focuspoint2.css', dirname(__FILE__)) );
    wp_enqueue_style( 'yawave-liveblog-block-css', plugin_dir_url( __FILE__ ) . 'assets/css/yawave.liveblog.block.css', array( 'wp-edit-blocks' ), filemtime( dirname( __FILE__ ) . '/assets/css/yawave.liveblog.block.css' ) );
    wp_enqueue_style( 'yawave-publications-block-css', plugin_dir_url( __FILE__ ) . 'assets/css/yawave.publications.block.css', array( 'wp-edit-blocks' ), filemtime( dirname( __FILE__ ) . '/assets/css/yawave.publications.block.css' ) );
    
}

### liveblog update

add_action('wp_ajax_yawave_liveblog_update', 'Yawave\yawave_liveblog_update');
add_action('wp_ajax_nopriv_yawave_liveblog_update', 'Yawave\yawave_liveblog_update');

function yawave_liveblog_update() {
    
    $body = file_get_contents("php://input");
    $post_vars = json_decode($body);
    
    
    $process_all = new WP_Yawave_Importer_Process();
    $process_all->set_api_token_and_app_id();
    
    $process_all->update_liveblog_magic($post_vars);
    
    exit();
     
}



register_block_type(
  'yawaveblock/liveblog', array(
  'render_callback' => 'Yawave\yawave_block_liveblog_callback',
  'attributes' => [
      'liveblogid' => [
        'default' => 0
      ],
    ]
  )
);


register_block_type(
  'yawaveblock/publications', array(
  'render_callback' => 'Yawave\yawave_block_publications_callback',
  'attributes' => [
      'publications_cat_id' => [
        'default' => 0
      ],
      'publications_tag_id' => [
        'default' => 0
      ],
      'publications_portal_id' => [
        'default' => 0
      ],
    ]
  )
);

function yawave_block_liveblog_callback($atts) {
  
  if($atts['liveblogid'] > 0) {
    return do_shortcode('[yawave-liveblog liveblog-id="'.$atts['liveblogid'].'"]');
  }else{
    return do_shortcode('[yawave-publications]');
  }
  
}

function yawave_block_publications_callback($atts) {
  
  if($atts['publications_cat_id'] > 0) {
    $publications_cat_id = ' cat-id="'.$atts['publications_cat_id'].'" ';
  }
  
  if($atts['publications_tag_id'] > 0) {
    $publications_tag_id = ' tag-id="'.$atts['publications_tag_id'].'" ';
  }
  
  if($atts['publications_portal_id'] > 0) {
    $publications_portal_id = ' portal-id="'.$atts['publications_portal_id'].'" ';
  }
    
  return do_shortcode('[yawave-publications '.$publications_cat_id.$publications_tag_id.$publications_portal_id.']');  
  
}



add_action('wp_ajax_yawave_blocks_get_liveblogs_for_select', 'Yawave\yawave_blocks_get_liveblogs_for_select');
add_action('wp_ajax_nopriv_blocks_get_liveblogs_for_select', 'Yawave\yawave_blocks_get_liveblogs_for_select');

function yawave_blocks_get_liveblogs_for_select() {
    
    global $wpdb;
    
    $rows = $wpdb->get_results( 'SELECT id, title FROM '.$wpdb->prefix.'yawave_liveblogs' , ARRAY_A);
    
    foreach($rows AS $row) {
      $array[] = array(
        'value' => $row['id'],        
        'label' => $row['title']
      );
    }
    
    echo json_encode($array);
    
    exit();
     
}


add_action('wp_ajax_yawave_blocks_get_publication_tags_for_select', 'Yawave\yawave_blocks_get_publication_tags_for_select');
add_action('wp_ajax_nopriv_yawave_blocks_get_publication_tags_for_select', 'Yawave\yawave_blocks_get_publication_tags_for_select');

function yawave_blocks_get_publication_tags_for_select() {    
    
    $tags = get_tags(array(
      'hide_empty' => false
    ));
    
    foreach ($tags as $tag) {
      $array[] = array(
        'value' => $tag->term_id,        
        'label' => $tag->name
      );
    }    
    
    echo json_encode($array);
    
    exit();
     
}



add_action('wp_ajax_yawave_blocks_get_publication_categories_for_select', 'Yawave\yawave_blocks_get_publication_categories_for_select');
add_action('wp_ajax_nopriv_yawave_blocks_get_publication_categories_for_select', 'Yawave\yawave_blocks_get_publication_categories_for_select');

function yawave_blocks_get_publication_categories_for_select() {    
    
    $categories = get_categories(array(
      'hide_empty' => false
    ));
    
    foreach ($categories as $categorie) {
      $array[] = array(
        'value' => $categorie->term_id,        
        'label' => $categorie->name
      );
    }    
    
    echo json_encode($array);
    
    exit();
     
}




add_action('wp_ajax_yawave_blocks_get_publication_portals_for_select', 'Yawave\yawave_blocks_get_publication_portals_for_select');
add_action('wp_ajax_nopriv_yawave_blocks_get_publication_portals_for_select', 'Yawave\yawave_blocks_get_publication_portals_for_select');

function yawave_blocks_get_publication_portals_for_select() {    
    
    $terms = get_terms(array(
      'taxonomy' => 'portal',
      'hide_empty' => false,
    ));
    
    
    
    foreach ($terms as $term) {
      
      $array[] = array(
        'value' => $term->term_id,        
        'label' => $term->name
      );
    }   
    
    echo json_encode($array); 
    
    exit();
     
}



add_filter( 'the_content', 'Yawave\yawave_update_kpi_metrics' );
function yawave_update_kpi_metrics( $content ) {
   
    $wp_post_id = get_the_ID();
    $publication_id = get_post_meta($wp_post_id, 'yawave_id');
    
    if(!empty($publication_id[0])) {
    
      $auth_options = get_option('yawave_settings_authorization_option');
      
      $kpi_app_id = (YAWAVE_API_MODE == 'prod') ? $auth_options['yawave_authorization_appid'] : $auth_options['yawave_dev_authorization_appid'];
      
      $url = YAWAVE_API_ENDPOINT_URL . 'public/v1/open/applications/'.$kpi_app_id.'/publications/'.$publication_id[0].'/metrics';
      
      $response_metrics = wp_remote_retrieve_body ( wp_remote_get( $url ) );
      $return_yawave_kpi = json_decode($response_metrics);
      
      $metrics = array(
        'views' => $return_yawave_kpi->views,
        'recipients' => $return_yawave_kpi->recipients,
        'engagements' => $return_yawave_kpi->engagements,
      );
      
      update_post_meta($wp_post_id, 'yawave_publication_kpi_metrics', $response_metrics);
        
    }
    
    return $content;
    
}



function plugin_name_print_stylesheet() {
  
  if(is_single()) {
    
    $publication_css_styles = get_post_meta(get_the_ID(), 'yawave_publication_styles');
    
    if(!empty($publication_css_styles[0])) {
      echo '<style type="text/css">';
      echo $publication_css_styles[0];
      echo '</style>';
    }
    
  }
  
  ?>
  <style type="text/css">
  /* CSS definitions go here */
  </style>
  <?php
}
add_action( 'wp_print_styles', 'Yawave\plugin_name_print_stylesheet' );


add_action('plugins_loaded', 'Yawave\yawave_plugin_load_languages'); 

function yawave_plugin_load_languages() {

    load_plugin_textdomain( 'yawave', false, dirname(plugin_basename(__FILE__)).'/languages/' );

}

function wpb_admin_notice_warn() {
  
  
  if (class_exists('SitePress')) {
    global $sitepress;
    $sitepress_settings = $sitepress->get_settings();
    
    if($sitepress_settings['taxonomies_sync_option']['category'] != 0 || 
        $sitepress_settings['taxonomies_sync_option']['post_tag'] != 0) {
    
    echo '<div class="notice notice-warning is-dismissible">
          <h2>Yawave - WPML Hinweis!</h2>
          <p>Damit Sie die Mehrsprachigkeit mit dem Yawave Plugin vollständig nutzen können, stellen Sie bitte in den WPML Einstellungen die Taxonomien-Übersetzung bei Kategorien und Tags auf <strong>Nicht übersetzbar</strong>.</p>
            <p><a href="/wp-admin/admin.php?page=tm%2Fmenu%2Fsettings#ml-content-setup-sec-8">Hier kommen Sie direkt zu den Einstellungen</a></p>
          </div>'; 
          
    }
          
  }
  
}
add_action( 'admin_notices', 'Yawave\wpb_admin_notice_warn' );

### liveblog update

add_action('wp_ajax_yawave_blog_test', 'Yawave\yawave_blog_test');
add_action('wp_ajax_nopriv_yawave_blog_test', 'Yawave\yawave_blog_test');

function yawave_blog_test() {
    
    global $sitepress;
    $default_lang = $sitepress->get_default_language();
    
    $sitepress->switch_lang('de');
    
    $args = array(
      'numberposts' => 10,
      'cat' => 225,
      'suppress_filters' => false,
    );
    
    
    $latest_posts = get_posts( $args );
    
    foreach($latest_posts AS $post) {
      
      echo 'ID: '.$post->ID;
      echo '<br />';
      echo 'Title: '.$post->post_title;
      echo '<br />';
      
      $categories = wp_get_post_categories($post->ID);
      
      echo 'category: ';
      
      foreach($categories AS $categorie) {
        
        echo $categorie.', ';
        
      }
      
      echo '<br />';
      echo '<br />';
      
      /*
      echo '<pre>';
      var_dump($categories);
      echo '</pre>';
      */
      
    }
    
    //echo '<pre>';
    //var_dump($latest_posts);
    //echo '</pre>';
    
    $sitepress->switch_lang($default_lang);
    
    exit();
     
}