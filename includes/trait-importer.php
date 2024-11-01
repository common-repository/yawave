<?php

namespace Yawave;

trait WP_Yawave_Importer {

    protected $token = '';
    protected $app_id = '';

    /**
     * This is helper method used in development and debuging
     * reguire activate debug mode in wp-config.php
     *
     * @param string $message
     */
    public function log($message) {}
    
    public function save_log($message, $slug = '') {
        
        global $wpdb;
        
        $data = array(
            'logtime'    => current_time('mysql', 1), 
            'logdata'    => ((!empty($message)) ? $message : 0), 
            'slug'       => ((!empty($slug)) ? $slug : 0),
            );
        
        $query = $wpdb->insert($wpdb->prefix.'yawave_log', $data);
        
    }

    /**
     * 
     */
    public function set_api_token_and_app_id() {
        
        
        $auth_options = get_option('yawave_settings_authorization_option');
        if (empty($auth_options) || 
            !is_array($auth_options) || 
            !isset($auth_options['yawave_authorization_key']) || 
            !isset($auth_options['yawave_authorization_secret']) || 
            !isset($auth_options['yawave_authorization_appid'])) {
            return false;
        }
        
        
        if(YAWAVE_API_MODE == 'prod') {
        
            $key = $auth_options['yawave_authorization_key'];
            $secret = $auth_options['yawave_authorization_secret'];
            
            if(!empty($auth_options['yawave_authorization_realmname'])) {
                $realmname = $auth_options['yawave_authorization_realmname'];
            }else{
                $realmname = 'yawave';
            }
            
            $this->app_id = $auth_options['yawave_authorization_appid'];
        
        }elseif(YAWAVE_API_MODE == 'dev') {
         
            $key = $auth_options['yawave_dev_authorization_key'];
            $secret = $auth_options['yawave_dev_authorization_secret'];
            
            if(!empty($auth_options['yawave_dev_authorization_realmname'])) {
                $realmname = $auth_options['yawave_dev_authorization_realmname'];
            }else{
                $realmname = 'yawave';
            }
            
            $this->app_id = $auth_options['yawave_dev_authorization_appid'];
            
        }else{
            
            $key = $auth_options['yawave_authorization_key'];
            $secret = $auth_options['yawave_authorization_secret'];
            $this->app_id = $auth_options['yawave_authorization_appid'];
            
            if(!empty($auth_options['yawave_authorization_realmname'])) {
                $realmname = $auth_options['yawave_authorization_realmname'];
            }else{
                $realmname = 'yawave';
            }
            
        }
        
        
        
        $yawave_api_token_url = str_replace('/realms/yawave/', '/realms/'.$realmname.'/', YAWAVE_API_TOKEN_URL);
        
        $result_full = wp_remote_retrieve_body ( wp_remote_post(
            
            $yawave_api_token_url,
            array(
                'method'      => 'POST',
                'timeout'     => 30,
                'headers'     => array(
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode( $key.':'.$secret )
                ),
                'body'        => array('grant_type' => 'client_credentials'),
            )
        ) );
                
        $response = json_decode($result_full);
        
        
        if ($response && isset($response->access_token) && !empty($response->access_token)) {
            $this->token = $response->access_token;
        }
    }

    /**
     * Return array of data from called API ENDPOINT
     * @todo error handling, add params, check response format and validate
     * 
     * @param type $url
     * @param type $params (optional)
     * @return array (parsed JSON)
     */
    public function get_api_endpoint_data($url, $params = []) {
        
        $url = str_replace("YAWAVE_APP_ID", $this->app_id, $url);
        
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
        );
        
        $response = wp_remote_retrieve_body ( wp_remote_get(
            $url,
            array(
                'headers'     => $headers,
            )
        ) );
        
       
                
        return json_decode($response);
        
    }
    
    
    public function put_api_endpoint_data($url, $params = []) {
        
        $url = str_replace("YAWAVE_APP_ID", $this->app_id, $url);
                
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
        );
        
        $result_full = wp_remote_retrieve_body ( wp_remote_post(
            $url,
            array(
                'method'      => 'PUT',
                'timeout'     => 30,
                'headers'     => $headers,
                'body'        => $params,
            )
        ) );
                
        return json_decode($result_full);
        
    }

    /**
     * Return value of attribute by language, if language not present return in defatult language
     * If attribute not existing in choosen language, return false
     * This method is used for data imported form Yawave platform for multilanguage elements
     * @param type $object
     * @param type $language
     * @return string or false
     */
    
    public function get_value($object, $language = false) {
        return $object;
    }

    /**
     * Return default WordPress language code mapped to Yawave language codes
     * It's needed in Yawave API to get content
     * @todo Temporary its hardcoded and return "en". 
     * @return string
     */
    public function get_default_language_code() {
        //return $this->get_language_code(get_locale());
        return "en";
    }

    /**
     * return language code as used in Yawave API
     * @param type $language
     * @return string
     */
    public function get_language_code($language) {
        switch ($language) {
            case "en_GB": return "en";
                break;
            case "en_US": return "en";
                break;
            case "de_DE": return "de";
                break;
            default:
                return $language;
        }
    }

    public function get_author_id() {
        $options = get_option('yawave_settings_import_option');
        return (isset($options['yawave_import_author_user'])) ? $options['yawave_import_author_user'] : false;
    }

}
