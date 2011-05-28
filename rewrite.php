<?php

  $version = explode('.', phpversion());
  if(!($version[0] > 4 && $version[1] > 2)) die('Error: PHP 5.3 or greater needed'); 

  $GLOBALS['profiler_start'] = microtime();
  $GLOBALS['APP.BASEDIR'] = dirname(__FILE__);

  // init environment
  ob_start("ob_gzhandler");
  
  ob_start();
  chdir($GLOBALS['APP.BASEDIR']);
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/database.php'); 

  profile_point('classes ready');
  require('lib/config.php'); 
    
  profile_point('config loaded');
  h2_init_hubbub_environment();  
  // if there was output up to this point, it has to be an error message
  $GLOBALS['content.startuperrors'] = trim(ob_get_clean());

  // enable gzip compression by default
  profile_point('environment ready');
  WriteToFile('log/activity.log', 'call '.$_REQUEST['controller'].'-'.$_REQUEST['action']."\n");

  // instantiate controller, invoke action, render view	

  include('mvc/test/test.rewrite.php');

/* temporarily disabled (fixme)
  if($_REQUEST['controller'] != 'endpoint')
    h2_statlog('web', $_REQUEST['controller'].'.'.$_REQUEST['action']);  
  else
    h2_statlog('ept', $GLOBALS['stats']['msgtype'].'('.$GLOBALS['stats']['response'].')');  
*/
	
?>