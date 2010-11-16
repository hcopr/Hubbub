<?php

  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('conf/config.php'); 
  require('lib/database.php'); 

  init_hubbub_environment();
  ob_start("ob_gzhandler");
	
	$baseCtr = getController(getDefault($_REQUEST['controller'], cfg('service.defaultcontroller')));
	invokeAction($baseCtr, $_REQUEST['action']);
	$GLOBALS['content']['main'] = invokeView($baseCtr, $_REQUEST['action']);
			
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