<?php
// DO NOT REMOVE OR RENAME THIS FILE!

$GLOBALS['config']['db'] = array(
  'host' => '_db_host_',
  'user' => '_db_user_',
  'password' => '_db_password_',
  'database' => '_db_name_',
  'prefix' => 'h2_',  
  );

/* a password for hubbub-related admin functions on this server */  
$GLOBALS['config']['service']['adminpw'] = '_admin_password_';

/* your server's public domain name */
$GLOBALS['config']['service']['server'] = '_server_base_';

/* whether your server supports "pretty" urls */
$GLOBALS['config']['service']['url_rewrite'] = _enable_rewrite_;

/* a password to prevent outside forces from triggering hubbub's cron-dependent services */
$GLOBALS['config']['service']['ping_password'] = '_ping_password_';

/** Memcached support:
 * If you have memcached installed, uncomment the memcache line and enter your server address as needed
 **/
/*$GLOBALS['config']['cache']['memcache'] = 'localhost:11211';*/

/** Twitter Connector:
 * Uncomment this to allow login via Twitter. If you want your users to be able
 * to sign on with Twitter, go to https://api.twitter.com/oauth/authorize to 
 * get the necessary keys. You also need to enter a CALLBACK URL, which must be
 * http://yourdomain/subdirectory/signin-index (where "yourdomain" is your domain name,
 * "/subdirectory" is the name of the subdirectory where you installed Hubbub if
 * it's not in the root folder, and "/signin-index" must be left intact.
 **/

/*$GLOBALS['config']['twitter'] = array(
  'api_key' => '', 
  'consumer_key' => '',
  'consumer_secret' => '',
  );*/

/** Facebook Connector:
 * Uncomment this to allow login via Facebook. First, go to http://developers.facebook.com/setup/
 * and set up a new application account. As the site name, you can enter anything like "Hubbub on example.com".
 * Site URL will be the URL of your Hubbub instance, like for example "http://gapmind.net". Enter the App
 * keys in here:
 */

/*$GLOBALS['config']['facebook'] = array(
  'app_id' => '', 
  'app_secret' => '',
  );*/

/** Event hooks for plugins
 * This is a selection of plugins that are active in a new install.
 */  
$GLOBALS['config']['plugins'] = array(
  'user.new' => array('friendlyui:user_new'),
  );
 
?>