<?php

$GLOBALS['config']['db'] = array(
  'host' => 'localhost',
  'user' => 'hubbub',
  'password' => 'MzSbXvZKmvcT5phD',
  'database' => 'hubbub2',
  'prefix' => 'h2_',  
  );

$GLOBALS['config']['service']['adminpw'] = 'Y2EwZTRl';
$GLOBALS['config']['service']['server'] = 'hubme.net';
$GLOBALS['config']['service']['url_rewrite'] = 'true';

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
?>