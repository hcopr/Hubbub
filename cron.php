<?php

  // init environment
  chdir(dirname(__FILE__));
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/config.php'); 
  require('lib/database.php'); 
  init_hubbub_environment();  
  // if there was output up to this point, it has to be an error message
 
  // instantiate controller, invoke action, render view	
	$baseCtr = getController('endpoint');
	invokeAction($baseCtr, 'cron');
	print(invokeView($baseCtr, 'cron'));
		
?>