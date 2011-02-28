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
$GLOBALS['config']['ping'] = array(
  'ping_password' => '_ping_password_',
  );

/* Amazon S3 support */
$GLOBALS['config']['s3'] = array(
  'enabled' => _enable_s3_,
  'access' => '_s3_access_key_',
  'secret' => '_s3_secret_key_',
  );
  
/** Memcached support:
 * If you have memcached installed, uncomment the memcache line and enter your server address as needed
 **/
 
$GLOBALS['config']['memcache'] = array(
  'enabled' => _enable_memcache_,
  'server' => '_memcache_server_',
  );

/** Twitter Connector:
 * Uncomment this to allow login via Twitter. If you want your users to be able
 * to sign on with Twitter, go to https://api.twitter.com/oauth/authorize to 
 * get the necessary keys. You also need to enter a CALLBACK URL, which must be
 * http://yourdomain/subdirectory/signin-index (where "yourdomain" is your domain name,
 * "/subdirectory" is the name of the subdirectory where you installed Hubbub if
 * it's not in the root folder, and "/signin-index" must be left intact.
 **/

$GLOBALS['config']['twitter'] = array(
  'enabled' => _enable_twitter_,
  'api_key' => '_twitter_apikey_', 
  'consumer_key' => '_twitter_consumer_key',
  'consumer_secret' => '_twitter_consumer_secret_',
  );

/** Facebook Connector:
 * Uncomment this to allow login via Facebook. First, go to http://developers.facebook.com/setup/
 * and set up a new application account. As the site name, you can enter anything like "Hubbub on example.com".
 * Site URL will be the URL of your Hubbub instance, like for example "http://gapmind.net". Enter the App
 * keys in here:
 */

$GLOBALS['config']['facebook'] = array(
  'enable' => _enable_facebook_,
  'app_id' => '_fb_app_id_', 
  'app_secret' => '_fb_app_secret_',
  );

/** Event hooks for plugins
 * This is a selection of plugins that are active in a new install.
 */  
$GLOBALS['config']['plugins'] = array(
  'user_new' => array('friendlyui'),
  'show_notice' => array('friendlyui'),
  'publish_attachments_register' => array('multimedia'),
  );
 
?>