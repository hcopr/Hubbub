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
  ini_set('error_log', 'log/error.php.log');
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
	
	// global config array
  $GLOBALS['config']['service'] = array(
    // enable stats
    'statlog' => true,
    // server's standard date/time format
    'dateformat' => 'H:i d.m.Y',
    'name' => 'Hubbub2',
		'defaultcontroller' => 'home',
		'defaultaction' => 'index',
    // number of open account signups this server should provide
		'maxaccounts' => 30,
		// the Hubbub server URL, please change this if your Hubbub instance is running in a subdirectory
		'server' => $_SERVER['HTTP_HOST'],
		// server poll interval in seconds
		'poll_interval' => 60*10,
    );
  $GLOBALS['config']['page']['template'] = 'default';
		
  // include the server-specific config
  if(file_exists('conf/'.$_SERVER['HTTP_HOST'].'.php'))
    require('conf/'.$_SERVER['HTTP_HOST'].'.php');
  else if(file_exists('conf/default.php'))
    require('conf/default.php');
	else
	{
	  // if no server-specific config was found, load the installer
	  if (substr($_SERVER['REQUEST_URI'], 0, 1) == '/')
	    interpretQueryString($_SERVER['REQUEST_URI']);    
    ob_start();
    $GLOBALS['config']['page']['title'] = 'Install';
    $GLOBALS['page.h1'] = 'Hubbub 0.2 Installer';
    l10n_load('ext/installer/l10n');
    include('ext/installer/'.getDefault(getDefault($_REQUEST['p'], $_REQUEST['controller']), 'index').'.php');
    $GLOBALS['content']['main'] = '<div class="installer">'.ob_get_clean().'</div>';
    header('content-type: text/html;charset=UTF-8');
    include('themes/default/default.php');
    die();
  }

?>