<?php



namespace Yawave;

define('YAWAVE_CONFIG_API_LIVE_ENDPOINT_URL', 'https://api.yawave.com/');
define('YAWAVE_CONFIG_API_LIVE_TOKEN_URL', 'https://sso.yawave.com/auth/realms/yawave/protocol/openid-connect/token');

define('YAWAVE_CONFIG_API_DEV_ENDPOINT_URL', 'https://api.test-yawave.com/');
define('YAWAVE_CONFIG_API_DEV_TOKEN_URL', 'https://sso.test-yawave.com/auth/realms/yawave/protocol/openid-connect/token');

### dont make any changes here

if(defined('YAWAVE_API_MODE')) {
   
   if(YAWAVE_API_MODE == 'dev') {
       
       define('YAWAVE_API_ENDPOINT_URL', YAWAVE_CONFIG_API_DEV_ENDPOINT_URL);
       define('YAWAVE_API_TOKEN_URL', YAWAVE_CONFIG_API_DEV_TOKEN_URL);
       
   }elseif(YAWAVE_API_MODE == 'prod') {
          
      define('YAWAVE_API_ENDPOINT_URL', YAWAVE_CONFIG_API_LIVE_ENDPOINT_URL);
      define('YAWAVE_API_TOKEN_URL', YAWAVE_CONFIG_API_LIVE_TOKEN_URL);
      
   }else{
      
      define('YAWAVE_API_ENDPOINT_URL', YAWAVE_CONFIG_API_LIVE_ENDPOINT_URL);
      define('YAWAVE_API_TOKEN_URL', YAWAVE_CONFIG_API_LIVE_TOKEN_URL);
      
   }
   
}else{
   
   define('YAWAVE_API_ENDPOINT_URL', YAWAVE_CONFIG_API_LIVE_ENDPOINT_URL);
   define('YAWAVE_API_TOKEN_URL', YAWAVE_CONFIG_API_LIVE_TOKEN_URL);
   
}


define('YAWAVE_API_ENDPOINT_CATEGORIES', YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/categories');
define('YAWAVE_API_ENDPOINT_TAGS', YAWAVE_API_ENDPOINT_URL . 'public/applications/YAWAVE_APP_ID/tags?page=0');
define('YAWAVE_API_ENDPOINT_PUBLICATIONS', YAWAVE_API_ENDPOINT_URL . 'public/multilang/applications/YAWAVE_APP_ID/publications?page=0&lang=en');
define('YAWAVE_API_ENDPOINT_PORTALS', YAWAVE_API_ENDPOINT_URL . 'public/applications/YAWAVE_APP_ID/portals?page=0&lang=en');



