<?php

  $GLOBALS['profiler_start'] = microtime();
  // init environment
  chdir(dirname(__FILE__));
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/config.php'); 
  require('lib/database.php'); 
  h2_init_hubbub_environment();  
  // if there was output up to this point, it has to be an error message
 
  // instantiate controller, invoke action, render view	
	$baseCtr = h2_getController('endpoint');
	h2_invokeAction($baseCtr, 'cron');
	print(h2_invokeView($baseCtr, 'cron'));
	
  h2_statlog('cron', 'cron');  
		
?>