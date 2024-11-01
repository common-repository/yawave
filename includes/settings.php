<?php

namespace Yawave;

class YawaveSettings {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $activeTab;

    /**
     * Start up
     */
    public function __construct() {
        $this->activeTab();
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'authorization_tab_init'));
        add_action('admin_init', array($this, 'import_tab_init'));
        add_action('admin_init', array($this, 'development_tab_init'));
    }

    /**
     * Add options page
     */
    public function add_settings_page() {
        // This page will be under "Settings"
        add_menu_page(
                __('Yawave settings','yawave'),
                'Yawave',
                'manage_options',
                'yawave-setting-admin',
                array($this, 'create_settings_page'),
                MY_PLUGIN_PATH . 'assets/img/menue-icon.png'
        );
                
        add_submenu_page('yawave-setting-admin', __('Settings', 'yawave'), __('Settings', 'yawave'), 'manage_options', 'yawave-setting-admin' );
        
        add_submenu_page( 
            'yawave-setting-admin', 
            'Liveblogs', 
            'Liveblogs',
            'manage_options', 
            'yawave-setting-admin-liveblog',
            array($this, 'create_liveblog_admin_site')
        );
        
    }
    
    public function create_liveblog_admin_site() {
     
         global $wpdb;
         
         $get_liveblog_id = (isset($_GET['id'])) ? $_GET['id'] : '';
         $get_liveblog_mode = (isset($_GET['mode'])) ? $_GET['mode'] : '';
         $get_liveblog_mode_id = (isset($_GET['lid'])) ? $_GET['lid'] : '';
         
         if($get_liveblog_mode == 'delete') {
             
             $delete_liveblog_items = $wpdb->delete($wpdb->prefix.'yawave_liveblogs_posts', array('liveblog_id' => $get_liveblog_mode_id));
             $delete_liveblog = $wpdb->delete($wpdb->prefix.'yawave_liveblogs', array('id' => $get_liveblog_mode_id));
             
         }
         
         if(empty($get_liveblog_id)) {
             
             require 'settings.liveblogs.php';
             
         }else{
             
             require 'settings.liveblogs.details.php';
             
         }
        
    }

    private function activeTab() {
        $this->activeTab = "home-options";
        if (isset($_GET["tab"])) {
            if ($_GET["tab"] == "authorization-options") {
                $this->activeTab = "authorization-options";
            } elseif ($_GET["tab"] == "import-options") {
                $this->activeTab = "import-options";
            } elseif ($_GET["tab"] == "tools-options") {
                $this->activeTab = "tools-options";
            } elseif ($_GET["tab"] == "yawave-development") {
                $this->activeTab = "yawave-development";
            } elseif ($_GET["tab"] == "yawave-debug") {
                $this->activeTab = "yawave-debug";
            } else {
                $this->activeTab = "home-options";
            }
        }
    }

    /**
     * Options page callback
     */
    public function create_settings_page() {
        
        global $wpdb;
        
        ?>

        <div class="wrap">
            <a href="https://yawave.com/" target="_blank"><img src="<?php echo plugin_dir_url('yawave/assets/img/yawave_logo.png') ?>yawave_logo.png" alt="Yawave" width="80" align="right" /></a>
            <h1><?=__('Yawave settings','yawave')?></h1>
            <h2 class="nav-tab-wrapper">
                <!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
                <a href="?page=yawave-setting-admin&tab=home-options" class="nav-tab <?php
                if ($this->activeTab == 'home-options') {
                    echo 'nav-tab-active';
                }
                ?> "><?php _e('About Yawave', 'yawave'); ?></a>
                <a href="?page=yawave-setting-admin&tab=authorization-options" class="nav-tab <?php
                if ($this->activeTab == 'authorization-options') {
                    echo 'nav-tab-active';
                }
                ?> "><?php _e('Authentication', 'yawave'); ?></a>
                <a href="?page=yawave-setting-admin&tab=import-options" class="nav-tab <?php
                if ($this->activeTab == 'import-options') {
                    echo 'nav-tab-active';
                }
                ?> "><?php _e('Import settings', 'yawave'); ?></a>
                <a href="?page=yawave-setting-admin&tab=yawave-development" class="nav-tab <?php
                if ($this->activeTab == 'yawave-development') {
                    echo 'nav-tab-active';
                }
                ?> "><?php _e('Development', 'yawave'); ?></a>
                
                <a href="?page=yawave-setting-admin&tab=yawave-debug" class="nav-tab <?php
                if ($this->activeTab == 'yawave-debug') {
                    echo 'nav-tab-active';
                }
                ?> "><?php _e('Debug', 'yawave'); ?></a>
                
            </h2>
            <form method="post" action="options.php">
                
                
                <?php
                
                if ($this->activeTab == 'yawave-debug') {
                    
                    echo '<h2>'.__('Debug information','yawave').'</h2>
                    <table class="wp-list-table widefat fixed striped table-view-list wp_list_text_liveblogs">
                    <thead>
                    <tr>
                    <th scope="col" class="manage-column" width="10%">#</th>
                    <th scope="col" class="manage-column" width="10%">'.__('Time','yawave').'</th>
                    <th scope="col" class="manage-column">Info</th>
                    <th scope="col" class="manage-column" width="10%" style="text-align: right;">Slug</th>
                    </tr>
                    </thead>
                    <tbody>';
                    
                    $rows =  $wpdb->get_results( 'SELECT id, logtime, logdata, slug FROM '.$wpdb->prefix.'yawave_log WHERE logdata != "N;"  ORDER BY id DESC LIMIT 50' , ARRAY_A);
                    
                    foreach($rows AS $row) {
                        
                        echo '<tr>
                            <td>'.$row['id'].'</td>
                            <td>'.$row['logtime'].'</td>
                            <td><textarea style="width: 100%;" rows="5">'.$row['logdata'].'</textarea></td>
                            <td style="text-align: right;">'.$row['slug'].'</td>
                        </tr>';
                        
                    }
                    
                    echo '</tbody></table>';
                    
                }
                
                if ($this->activeTab == 'home-options') {
                    
                    echo '<h2>'.__('CONTENT MARKETING FROM A SINGLE SOURCE', 'yawave').'</h2>';
                    
                    echo '<h4>'.__('Efficient cross-channel publishing with a powerful engagement toolbox and a central management cockpit.', 'yawave').'</h4>';
                    
                    echo '<p>'.__('A wide range of features to build a stronger relationship with your target audience.
                     With Yawave, companies of all sizes can take their content marketing and customer experience to the next level.', 'yawave').'</p>
                    
                    <hr />
                    
                    <h4>'.__('How do you embed your publications on your blog?', 'yawave').'</h4>
                    
                    <p>'.__('You have the option of displaying your publications at any point using a shortcode', 'yawave').'</p>
                    
                    <p>'.__('Simply use the following shortcode to display all publications:', 'yawave').'</p>
                    
                    <pre>[yawave-publications]</pre>
                    
                    <p>'.__('If you only want to display a certain category in one place, you have the following options:', 'yawave').'</p>
                    
                    <pre>[yawave-publications cat-id="1"]</pre>
                    
                    <p>'.__('You can do the same with tags (tag-id) or portals (portal-id).', 'yawave').'</p>
                    
                    <p>'.__('You also have the option of displaying your Wordpress articles together with your Yawave publications. You can use the following shortcode for this:', 'yawave').'</p>
                    
                    <pre>[yawave-publications show-all="1"]</pre>
                    
                    <h4>'.__('Show action buttons', 'yawave').'</h4>
                    
                    <p>'.__('So that you can display your selected action buttons in your template, you must place the following code in your template where you want to display the action buttons:', 'yawave').'</p>
                    
                    <pre>&lt;?=do_shortcode(\'[yawave-actionbuttons]\')?&gt;</pre>
                    
                    <p>'.__('If you want to display the action buttons of a specific article or publication, you can extend the shortcode with the following parameters:', 'yawave').'</p>
                    
                    <pre>&lt;?=do_shortcode(\'[yawave-actionbuttons post-id="1"]\')?&gt;</pre>';
                    
                    
                    
                }
                
                // This prints out all hidden setting fields

                if ($this->activeTab == 'authorization-options') {
                    $this->options = get_option('yawave_settings_authorization_option');
                    settings_fields('yawave_settings_authorization_group');
                    do_settings_sections('yawave-setting-authorization-admin');
                }
                
                if ($this->activeTab == 'import-options') {
                    $this->options = get_option('yawave_settings_import_option');
                    settings_fields('yawave_settings_import_group');
                    do_settings_sections('yawave-setting-import-admin');
                }
                
                if ($this->activeTab == 'yawave-development') {
                    $this->options = get_option('yawave_settings_development_option');
                    settings_fields('yawave_settings_development_group');                    
                    do_settings_sections('yawave-setting-development-admin');
                    
                    
                }
                
                if ($this->activeTab == 'yawave-debug') {
                    do_settings_sections('yawave-setting-debug-admin');
                }

                if ($this->activeTab != 'home-options' && $this->activeTab != 'tools-options' && $this->activeTab != 'yawave-debug') {
                    submit_button();
                }
                ?>
            </form>
            <?php if ($this->activeTab == "tools-options") : ?>
                <?php $this->import_buttons_render(); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Register and add settings for authorization
     */
    public function authorization_tab_init() {
        register_setting(
                'yawave_settings_authorization_group', // Option group
                'yawave_settings_authorization_option', // Option name
                array($this, 'sanitize') // Sanitize
        );
    
        add_settings_section(
                'authorization_section', // ID
                __('Authorization','yawave'), // Title
                array($this, 'print_authorization_section_info'), // Callback
                'yawave-setting-authorization-admin' // Page
        );
    
        add_settings_field(
                'yawave_key', // ID
                'Client-ID', // Title 
                array($this, 'yawave_authorization_key_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'authorization_section' // Section           
        );
    
        add_settings_field(
                'yawave_secret',
                'Client-Secret',
                array($this, 'yawave_authorization_secret_callback'),
                'yawave-setting-authorization-admin',
                'authorization_section'
        );
        
        add_settings_field(
                'yawave_appid', // ID
                'Yawave Application ID', // Title 
                array($this, 'yawave_authorization_appid_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'authorization_section' // Section           
        );
        
        add_settings_field(
                'yawave_realmname', // ID
                'Realm Name', // Title 
                array($this, 'yawave_authorization_realmname_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'authorization_section' // Section           
        );
    
        ### development settings
            
        add_settings_section(
                'dev_authorization_section', // ID
                'Dev-Authorization', // Title
                array($this, 'print_dev_authorization_section_info'), // Callback
                'yawave-setting-authorization-admin' // Page
        );
    
        add_settings_field(
                'dev_yawave_key', // ID
                'Client-ID', // Title 
                array($this, 'yawave_dev_authorization_key_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'dev_authorization_section' // Section           
        );
    
        add_settings_field(
                'dev_yawave_secret',
                'Client-Secret',
                array($this, 'yawave_dev_authorization_secret_callback'),
                'yawave-setting-authorization-admin',
                'dev_authorization_section'
        );
        
        add_settings_field(
                'dev_yawave_appid', // ID
                'Yawave Application ID', // Title 
                array($this, 'yawave_dev_authorization_appid_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'dev_authorization_section' // Section           
        );
        
        add_settings_field(
                'dev_yawave_realmname', // ID
                'Realm Name', // Title 
                array($this, 'yawave_dev_authorization_realmname_callback'), // Callback
                'yawave-setting-authorization-admin', // Page
                'dev_authorization_section' // Section           
        );
    
    }

    /**
     * Register and add settings for import
     */
    public function import_tab_init() {
        
        register_setting(
                'yawave_settings_import_group', // Option group
                'yawave_settings_import_option', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'import_section', // ID
                __('Import settings','yawave'), // Title
                array($this, 'print_import_section_info'), // Callback
                'yawave-setting-import-admin' // Page
        );

        add_settings_field(
                'yawave_import_post_type', // ID
                __('Type of publications imported','yawave'), // Title 
                array($this, 'yawave_import_post_type_callback'), // Callback
                'yawave-setting-import-admin', // Page
                'import_section' // Section     
        );

        add_settings_field(
                'yawave_import_author_user', // ID
                __('Author of the publications','yawave'), // Title 
                array($this, 'yawave_import_author_user_callback'), // Callback
                'yawave-setting-import-admin', // Page
                'import_section' // Section     
        );

        add_settings_field(
                'yawave_import_yawave_user_create', // ID
                __('Create Yawave author as user','yawave'), // Title 
                array($this, 'yawave_import_yawave_user_create_callback'), // Callback
                'yawave-setting-import-admin', // Page
                'import_section' // Section     
        );

        add_settings_field(
                'yawave_import_images', // ID
                __('Disable images import?','yawave'), // Title 
                array($this, 'yawave_import_images_callback'), // Callback
                'yawave-setting-import-admin', // Page
                'import_section' // Section     
        );

        /*add_settings_field(
                'yawave_import_tags', // ID
                __('Import tags','yawave'), // Title 
                array($this, 'yawave_import_tags_callback'), // Callback
                'yawave-setting-import-admin', // Page
                'import_section' // Section     
        );
        */
    }
    
    /**
     * Register and add settings for authorization
     */
    public function development_tab_init() {
        register_setting(
                'yawave_settings_development_group', // Option group
                'yawave_settings_development_option', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'development_section', // ID
                'Development', // Title
                array($this, 'print_development_section_info'), // Callback
                'yawave-setting-development-admin' // Page
        );

        add_settings_field(
                'yawave_development_mode', // ID
                'Development-Mode', // Title 
                array($this, 'yawave_devmode_callback'), // Callback
                'yawave-setting-development-admin', // Page
                'development_section' // Section           
        );
        
        add_settings_section(
                'development_sdk_section', // ID
                'SDK Mode', // Title
                array($this, 'print_development_sdk_section_info'), // Callback
                'yawave-setting-development-admin' // Page
        );
        
        add_settings_field(
                'yawave_sdk_autocreate_mode', // ID
                'Placement in Header?', // Title 
                array($this, 'yawave_sdk_autocreate_mode_callback'), // Callback
                'yawave-setting-development-admin', // Page
                'development_sdk_section' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {

        $new_input = array();
        if (isset($input['id_number'])) {
            $new_input['id_number'] = absint($input['id_number']);
        }

        if (isset($input['title'])) {
            $new_input['title'] = sanitize_text_field($input['title']);
        }

        if (isset($input['yawave_authorization_key'])) {
            $new_input['yawave_authorization_key'] = sanitize_text_field($input['yawave_authorization_key']);
        }
        
        if (isset($input['yawave_authorization_appid'])) {
            $new_input['yawave_authorization_appid'] = sanitize_text_field($input['yawave_authorization_appid']);
        }

        if (isset($input['yawave_authorization_secret'])) {
            $new_input['yawave_authorization_secret'] = sanitize_text_field($input['yawave_authorization_secret']);
        }
        
        if (isset($input['yawave_authorization_realmname'])) {
            $new_input['yawave_authorization_realmname'] = sanitize_text_field($input['yawave_authorization_realmname']);
        }
        
        

        if (isset($input['yawave_dev_authorization_key'])) {
            $new_input['yawave_dev_authorization_key'] = sanitize_text_field($input['yawave_dev_authorization_key']);
        }
        
        if (isset($input['yawave_dev_authorization_appid'])) {
            $new_input['yawave_dev_authorization_appid'] = sanitize_text_field($input['yawave_dev_authorization_appid']);
        }

        if (isset($input['yawave_dev_authorization_secret'])) {
            $new_input['yawave_dev_authorization_secret'] = sanitize_text_field($input['yawave_dev_authorization_secret']);
        }
        
        if (isset($input['yawave_dev_authorization_realmname'])) {
            $new_input['yawave_dev_authorization_realmname'] = sanitize_text_field($input['yawave_dev_authorization_realmname']);
        }
        
        

        if (isset($input['yawave_import_tags'])) {
            $new_input['yawave_import_tags'] = ($input['yawave_import_tags'] == "yes") ? "yes" : "no";
        }

        if (isset($input['yawave_import_post_type'])) {
            $new_input['yawave_import_post_type'] = sanitize_text_field($input['yawave_import_post_type']);
        }

        if (isset($input['yawave_import_author_user'])) {
            $new_input['yawave_import_author_user'] = sanitize_text_field($input['yawave_import_author_user']);
        }

        if (isset($input['yawave_import_yawave_user_create'])) {
            $new_input['yawave_import_yawave_user_create'] = ($input['yawave_import_yawave_user_create'] == "yes") ? "yes" : "no";
        }
        
        
        if (isset($input['yawave_import_images'])) {
            $new_input['yawave_import_images'] = ($input['yawave_import_images'] == "yes") ? "yes" : "no";
        }
        
        if (isset($input['yawave_development_mode'])) {
            $new_input['yawave_development_mode'] = ($input['yawave_development_mode'] == "dev") ? "dev" : "prod";
        }
        
        if (isset($input['yawave_sdk_autocreate_mode'])) {
            $new_input['yawave_sdk_autocreate_mode'] = ($input['yawave_sdk_autocreate_mode'] == "yes") ? "yes" : "no";
        }
        
        return $new_input;
    }

    /**
     * Print the Sections text
     */
     public function print_authorization_section_info() {
         print  __('Enter your login information for the Yawave API here:','yawave');
     }
     public function print_dev_authorization_section_info() {
         print __('Enter your credentials for the Dev-Yawave API here:','yawave');
     }

    public function print_import_section_info() {
        print __('Set here how the Yawave publications are to be imported:','yawave');
    }

    /**
     * Get the settings option array and print one of its values
     */
     public function yawave_authorization_key_callback() {
         printf(
                 '<input type="text" id="yawave_key" name="yawave_settings_authorization_option[yawave_authorization_key]" value="%s" /><br /><br />
                 <strong>'.__('Where can I find the Client ID and the Client Secret?','yawave').'</strong><br />
                 '.__('You first need a Wordpress integration in your Yawave account at:','yawave').'<br />
                 <strong>'.__('Integrate -> Connections -> Add Connection','yawave').'</strong><br />
                 '.__('After you have successfully created it, you will find the Client ID and the Client Secret in the same menu item.','yawave'),
                 isset($this->options['yawave_authorization_key']) ? esc_attr($this->options['yawave_authorization_key']) : ''
         );
     }
     public function yawave_dev_authorization_key_callback() {
         printf(
                 '<input type="text" id="yawave_dev_authorization_key" name="yawave_settings_authorization_option[yawave_dev_authorization_key]" value="%s" />',
                 isset($this->options['yawave_dev_authorization_key']) ? esc_attr($this->options['yawave_dev_authorization_key']) : ''
         );
     }
    
    /**
     * Get the settings option array and print one of its values
     */
     public function yawave_authorization_appid_callback() {
         printf(
                 '<input type="text" id="yawave_appid" name="yawave_settings_authorization_option[yawave_authorization_appid]" value="%s" /><br /><br />
                  <strong>'.__('Where can I find the Yawave Application ID?','yawave').'</strong><br />
                  '.__('In your Yawave account you have an icon with 9 small boxes in the blue top navigation. If you click there, you will find a list of your Yawave applications. If you move the mouse over the icon with the three dots for your desired application, you have the option of copying the application ID.','yawave'),
                 isset($this->options['yawave_authorization_appid']) ? esc_attr($this->options['yawave_authorization_appid']) : ''
         );
     }
     
     public function yawave_authorization_realmname_callback() {
          printf(
                  '<input type="text" id="yawave_realmname" name="yawave_settings_authorization_option[yawave_authorization_realmname]" value="%s" /><br /><br />Default: yawave',
                  isset($this->options['yawave_authorization_realmname']) ? esc_attr($this->options['yawave_authorization_realmname']) : ''
          );
      }
     
     
     public function yawave_dev_authorization_appid_callback() {
         printf(
                 '<input type="text" id="yawave_dev_authorization_appid" name="yawave_settings_authorization_option[yawave_dev_authorization_appid]" value="%s" />',
                 isset($this->options['yawave_dev_authorization_appid']) ? esc_attr($this->options['yawave_dev_authorization_appid']) : ''
         );
     }
 
     public function yawave_authorization_secret_callback() {
         printf(
                 '<input type="password" id="yawave_secret" name="yawave_settings_authorization_option[yawave_authorization_secret]" value="%s" />',
                 isset($this->options['yawave_authorization_secret']) ? esc_attr($this->options['yawave_authorization_secret']) : ''
         );
     }
 
     public function yawave_dev_authorization_secret_callback() {
         printf(
                 '<input type="password" id="yawave_dev_authorization_secret" name="yawave_settings_authorization_option[yawave_dev_authorization_secret]" value="%s" />',
                 isset($this->options['yawave_dev_authorization_secret']) ? esc_attr($this->options['yawave_dev_authorization_secret']) : ''
         );
     }
     
     public function yawave_dev_authorization_realmname_callback() {
           printf(
                   '<input type="text" id="yawave_dev_realmname" name="yawave_settings_authorization_option[yawave_dev_authorization_realmname]" value="%s" />',
                   isset($this->options['yawave_dev_authorization_realmname']) ? esc_attr($this->options['yawave_dev_authorization_realmname']) : ''
           );
       }
       
       
 
     public function yawave_import_categories_callback() {
          $checked = ( isset($this->options['yawave_import_categories']) && $this->options['yawave_import_categories'] == 'yes') ? 'checked="checked"' : '';
          /* printf(
                  '<input type="hidden" id="yawave_import_categories_no" name="yawave_settings_import_option[yawave_import_categories]" value="no" />'
          );
          printf(
                  '<input type="checkbox" id="yawave_import_categories" name="yawave_settings_import_option[yawave_import_categories]" value="yes" ' . $checked . ' />'
          ); */
      }
      
      public function yawave_import_images_callback() {
           $checked_yes = ( isset($this->options['yawave_import_images']) && $this->options['yawave_import_images'] == 'yes') ? 'checked="checked"' : '';
           printf(
                   '<input type="hidden" id="yawave_import_images_no" name="yawave_settings_import_option[yawave_import_images]" value="no" />'
           );
           printf(
                   '<input type="checkbox" id="yawave_import_images" name="yawave_settings_import_option[yawave_import_images]" value="yes" ' . $checked_yes . ' />'
           );
       }
 
     public function yawave_import_yawave_user_create_callback() {
         $checked = ( isset($this->options['yawave_import_yawave_user_create']) && $this->options['yawave_import_yawave_user_create'] == 'yes') ? 'checked="checked"' : '';
         printf(
                 '<input type="hidden" id="yawave_import_yawave_user_create_no" name="yawave_settings_import_option[yawave_import_yawave_user_create]" value="no" />'
         );
         printf(
                 '<input type="checkbox" id="yawave_import_yawave_user_create" name="yawave_settings_import_option[yawave_import_yawave_user_create]" value="yes" ' . $checked . ' />'
         );
     }

    public function yawave_import_tags_callback() {
        
        
        
        $checked = ( isset($this->options['yawave_import_tags']) && $this->options['yawave_import_tags'] == 'yes') ? 'checked="checked"' : '';
        /*printf(
                '<input type="hidden" id="yawave_import_tags_no" name="yawave_settings_import_option[yawave_import_tags]" value="no" />'
        );
        printf(
                '<input type="checkbox" id="yawave_import_tags" name="yawave_settings_import_option[yawave_import_tags]" value="yes" ' . $checked . ' />'
        );*/
    }

    public function yawave_import_author_user_callback() {
        $users = get_users();
        $user_id = (isset($this->options['yawave_import_author_user'])) ? $this->options['yawave_import_author_user'] : 0;
        printf('<select id="yawave_import_author_user" name="yawave_settings_import_option[yawave_import_author_user]">');
        foreach ($users as $user) {
            $selected = ($user->data->ID == $user_id) ? ' selected="selected"' : "";
            printf('<option value="' . $user->data->ID . '"' . $selected . '>' . $user->data->user_nicename . '</option>');
        }
        print_r('</select>');
    }

    public function yawave_import_post_type_callback() {
        
        $actually_post_type = (isset($this->options['yawave_import_post_type'])) ? $this->options['yawave_import_post_type'] : '';
                        
        echo '<select id="yawave_import_post_type" name="yawave_settings_import_option[yawave_import_post_type]">';
        
        echo '<option value="publication" '.(($actually_post_type == 'publication') ? 'selected=""' : '').'>'.__('Publication', 'yawave').'</option>';
        echo '<option value="post" '.(($actually_post_type == 'post') ? 'selected=""' : '').'>'.__('Post', 'yawave').'</option>';
        
        echo '</select>';
        
    }

    public function yawave_devmode_callback() {
        printf('<select id="yawave_development_mode" name="yawave_settings_development_option[yawave_development_mode]">');
        
        $selected_prod = '';
        $selected_dev = '';
                
        if(isset($this->options['yawave_development_mode'])) {
            
            if($this->options['yawave_development_mode'] == 'prod') {
                $selected_prod = 'selected="selected"';
            }elseif($this->options['yawave_development_mode'] == 'dev') {
                $selected_dev = 'selected="selected"';
            }
            
        }
        
        printf('<option value="prod"' . $selected_prod . '>Live</option>');
        printf('<option value="dev"' . $selected_dev . '>Development</option>');
        print_r('</select>');
    }
    
    public function print_development_section_info() {
        return true;
    }
    
    public function yawave_sdk_autocreate_mode_callback() {
        printf('<select id="yawave_sdk_autocreate_mode" name="yawave_settings_development_option[yawave_sdk_autocreate_mode]">');
        
        $selected_yes = '';
        $selected_no = '';
                
        if(isset($this->options['yawave_sdk_autocreate_mode'])) {
            
            if($this->options['yawave_sdk_autocreate_mode'] == 'regular_sdk') {
                $selected_yes = 'selected="selected"';
            }elseif($this->options['yawave_sdk_autocreate_mode'] == 'autocreate_sdk') {
                $selected_no = 'selected="selected"';
            }elseif($this->options['yawave_sdk_autocreate_mode'] == 'selfmade_sdk') {
                $selected_no = 'selected="selected"';
            }
            
        }
        
        printf('<option value="regular_sdk"' . $selected_no . '>Regular SDK</option>');
        printf('<option value="autocreate_sdk"' . $selected_yes . '>Autocreate SDK</option>');
        printf('<option value="selfmade_sdk"' . $selected_yes . '>Place SDK by yourself</option>');
        print_r('</select>');
    }
    
    public function print_development_sdk_section_info() {
        return true;
    }
    
    


}

if (is_admin()) {
    $my_settings_page = new YawaveSettings();
}