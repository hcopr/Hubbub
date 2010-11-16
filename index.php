<?php

  // init environment
  ob_start();
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/config.php'); 
  require('lib/database.php'); 
  init_hubbub_environment();  
  // if there was output up to this point, it has to be an error message
  $GLOBALS['content.startuperrors'] = trim(ob_get_clean());

  // enable gzip compression by default
  ob_start("ob_gzhandler");

  // instantiate controller, invoke action, render view	
	$baseCtr = getController(getDefault($_REQUEST['controller'], cfg('service.defaultcontroller')));
	invokeAction($baseCtr, $_REQUEST['action']);
	$GLOBALS['content']['main'] = invokeView($baseCtr, $_REQUEST['action']);
			
	// output through page template
	$templateName = cfg('page.template', 'default');
	switch($templateName)
	{
		case('blank'): {
			print($GLOBALS['content']['main']);
			break;
		}		
		default: {
			header('content-type: text/html;charset=UTF-8');
      require('themes/'.cfg('page.theme', 'default').'/'.$templateName.'.php');
      break;
		}
	}
		
?>