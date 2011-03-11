<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: basic initialization and config loading, please do not edit directly. Modify etc/yourserver.com.php instead
 */

  error_reporting(E_ALL ^ E_NOTICE);
  set_error_handler('h2_errorhandler', E_ALL ^ E_NOTICE);
  set_exception_handler('h2_exceptionhandler');

  // this is the kind of boilerplate code PHP would have been fine without necessitating
  if(strpos(PHP_OS, "WIN") !== false) $config['os.path.separator'] = ';'; else $config['os.path.separator'] = ':';
  
  // sadly, include paths aren't standardized, so we have to roll our own
	ini_set('include_path', implode($config['os.path.separator'], array('.', '../')));
  date_default_timezone_set('UTC');
  
	// set cookies  
  session_name('hubbub2');
  session_start();

	// log errors, basic ini stuff
  ini_set('error_log', 'log/error.log');
  ini_set('magic_quotes_gpc', 0);
	ini_set('magic_quotes_runtime', 0);
  ini_set('log_errors', true);
  ini_set('display_errors', 'on');
  
  define('CSS_COL_QUANTUM', 205);
  define('POST_MAX_WORDSIZE', 30);

	 // forcing magic quotes off for legacy environments
  if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
  }
  // preventing the "mark of the beast" bug
  foreach($_REQUEST as $k => $v)
    if(substr(str_replace('.', '', $v), 0, 8) == '22250738') $_REQUEST[$k] = 'F'.$v;
	
	// set httpOnly flag for more secure cookie handling
  ini_set('session.cookie_httponly', 1);
		
  // include the server-specific config
  if(file_exists('conf/'.$_SERVER['HTTP_HOST'].'.php'))
    require('conf/'.$_SERVER['HTTP_HOST'].'.php');
  // or the general config file maybe
  else if(file_exists('conf/default.php'))
    require('conf/default.php');
  // if no config was found, load the installer
	else
	  require('ext/installer/entry_point.php');
  
  // setting some default values
  $svc = &$GLOBALS['config']['service'];
  foreach(array(
    // enable stats
    // server's standard date/time format
    'menu' => 'home,profile,friends,mail',
    'sysmenu' => 'settings',
    'dateformat' => 'H:i d.m.Y',
    'name' => 'Hubbub2',
		'defaultcontroller' => 'home',
		'defaultaction' => 'index',
		'version' => 2011.0303,
    // number of open account signups this server should provide
		'maxaccounts' => 30,
		// the Hubbub server URL, please change this if your Hubbub instance is running in a subdirectory
		'server' => $_SERVER['HTTP_HOST'],
		// server poll interval in seconds
		'poll_interval' => 60*10,
		// size of the DMN list
		'dmn_maxsize' => 10,
    ) as $k => $v) if(!isset($svc[$k])) $svc[$k] = $v;
    
  $GLOBALS['config']['debug']['verboselog'] = true;
  
  foreach(array(
    'user_new' => 'friendlyui',
    'show_notice' => 'friendlyui',
    'publish_attachments_register' => 'multimedia',
    ) as $k => $v) $GLOBALS['config']['plugins'][$k][] = $v;

?>