<?php

  error_reporting(E_ALL ^ E_NOTICE);
  set_error_handler('h2_errorhandler', E_ALL ^ E_NOTICE);

  // this is the kind of boilerplate code PHP would have been fine without necessitating
  if(strpos(PHP_OS, "WIN") !== false) $config['os.path.separator'] = ';'; else $config['os.path.separator'] = ':';
  
  // sadly, include paths aren't standardized, so we have to roll our own
	ini_set('include_path', implode($config['os.path.separator'], array('.', '../')));
  
	// set cookies  
  session_name('hubbub2');
  session_start();

	// log errors, basic ini stuff
  ini_set('error_log', 'log/error.php.log');
  ini_set('magic_quotes_gpc', 0);
	ini_set('magic_quotes_runtime', 0);
  ini_set('log_errors', true);
  ini_set('display_errors', 'on');
  
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

	
	// allow only http cookies
  ini_set('session.cookie_httponly', 1);
	
	// global config array
	$GLOBALS['config']['debug'] = array(
    'showparams' => false,
    'verboselog' => false,
		);
  $GLOBALS['config']['service'] = array(
    'name' => 'Hubbub2',
		'defaultcontroller' => 'home',
		'defaultaction' => 'index',
		'maxaccounts' => 30,
		'currentusers' => 1,
		// the Hubbub server URL, please change this if your Hubbub instance is running in a subdirectory
		'server' => $_SERVER['SERVER_NAME'],
    );
  $GLOBALS['config']['page']['template'] = 'default';
		
  // include the server-specific config
  if(file_exists('conf/'.$_SERVER['SERVER_NAME'].'.php'))
    require('conf/'.$_SERVER['SERVER_NAME'].'.php');
	else
    die(h2_errorhandler(-1, 'Server-specific config file missing (domain '.$_SERVER['SERVER_NAME'].')', __FILE__, 0));
?>