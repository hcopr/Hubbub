<?php

  $GLOBALS['profiler_start'] = microtime();
  // init environment
  chdir(dirname(__FILE__));
  $_SERVER['NOREDIRECT'] = true;
  
  require('lib/genlib.php');
  require('lib/hubbub2.php');
  require('lib/config.php'); 
  require('lib/database.php'); 

  h2_init_hubbub_environment();  
  // if there was output up to this point, it has to be an error message
 
  $pingPassword = trim(cfg('ping/password'));
  if($pingPassword == '')
  {
    $pingPassword = trim(file_get_contents('conf/pingpassword'));
    logToFile('log/croncall.log', 'reading temp config: '.$pingPassword);
  }
  else
      logToFile('log/croncall.log', 'using password: '.$pingPassword);

 
  // instantiate controller, invoke action, render view	
  $action = 'cron';
  switch($_REQUEST['request'])
  {
    case('verify'): {
      if($pingPassword == trim($_REQUEST['password']))
        $result = array('result' => 'OK');
      else
        $result = array('result' => 'fail', 'reason' => 'wrong ping password');
      print(json_encode($result));      
      break; 
    }
    case('ping'): {
      if(trim(cfg('ping/password')) == trim($_REQUEST['password']))
      {        
      	$baseCtr = h2_getController('endpoint');
      	h2_invokeAction($baseCtr, 'cron');
      	print(h2_invokeView($baseCtr, 'cron'));
        h2_statlog('cron', 'cron');  
        $result = array('result' => 'OK', 'stamp' => time(), 'runtime' => profiler_microtime_diff(microtime(), $GLOBALS['profiler_start']));        
      }
      else
        $result = array('result' => 'fail', 'reason' => 'wrong ping password');
      print(json_encode($result));      
      break; 
    }
    default: {
      if($_SERVER['SERVER_PROTOCOL'] != '')
      {
        print(json_encode(array('result' => 'fail', 'reason' => 'invalid ping request')));
      }
      else
      {
      	$baseCtr = h2_getController('endpoint');
      	h2_invokeAction($baseCtr, 'cron');
      	print(h2_invokeView($baseCtr, 'cron'));
        h2_statlog('cron', 'cron');  
      }
      break; 
    }    
  }
		
?>