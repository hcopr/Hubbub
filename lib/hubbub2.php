<?php
/**
 * Author: udo.schroeter@gmail.com
 * Project: Hubbub2
 * Description: Hubbub-specific functions and objects
 */

/* some basic environment initialization */
function h2_init_hubbub_environment()
{
  if (substr($_SERVER['REQUEST_URI'], 0, 1) == '/')
    interpretQueryString($_SERVER['REQUEST_URI']);  
  profile_point('  - query strings');
	$GLOBALS['obj']['user'] = new HubbubUser();
	profile_point('  - user');
	l10n_load('mvc/l10n');
	profile_point('  - l10n');
	if(isset($_REQUEST['hubbub_msg']))
	{
		// we're receiving a message from another server
		$GLOBALS['msg']['touser'] = $_REQUEST['controller'];
		// invoke the endpoint controller to handle message
		$_REQUEST['controller'] = 'endpoint';
		$_REQUEST['action'] = 'index';
	}
}

function h2_uibanner($msg, $flag = '')
{
  $after = '';
  $bannerid = 'banner'.($GLOBALS['bannercount']++);
  if($flag == 'fadeout')
    $after = '<script><!--
    setTimeout(function() { $("#'.$bannerid.'").fadeOut("slow"); }, 2000);   
    //--></script>';
  return('<div id="'.$bannerid.'" class="banner">'.$msg.'</div>'.$after);
}

/* logs a transaction into the database */
function h2_audit_log($op, $data = '', $code = '', $result = '', $reason = '')
{
	if(object('user') != null) $usrname = object('user')->getUsername();
	$log = array(
	  'l_op' => $op,
		'l_user' => $usrname,
		'l_data' => $data,
    'l_returncode' => $code,
    'l_result' => $result,
    'l_reason' => $reason,
		);
	DB_UpdateDataset('auditlog', $log);
}

/* shortens a string and appends an ellipsis sign if needed */
function h2_make_excerpt($text, $length = 64)
{
	if(strlen($text) > $length) 
  {
  	$segments = explode(' ', $text);
		$text = '';
		if(is_array($segments)) foreach($segments as $seg)
			if(strlen($text) < $length) $text .= ' '.$seg;
		$text .= '…';
  }
	return(trim($text));
}

function h2_statlog($type, $call)
{

}

function h2_exceptionhandler($exception)
{
  if($GLOBALS['errorhandler_ignore']) return;
  $bt = debug_backtrace();
  ?><div class="errormsg" style="border: 1px solid red; padding: 8px; font-size: 8pt; font-family: consolas; background: #ffffe0;">
    <b>Hubbub Runtime Exception: <br/><span style="color: red"><?php echo $exception->getMessage() ?></span></b><br/>
    File: <?php echo basename($exception->getFile()).':'.$exception->getLine() ?><br/><?php 
    unset($bt[0]);
    foreach($bt as $trace)
    {
      print('^ &nbsp;'.$trace['function'].'(');
      if(cfg('debug/showparams') && is_array($trace['args'])) print(implode(', ', $trace['args']));
      print(') in '.basename($trace['file']).' | Line '.$trace['line'].'<br/>');
    }
  ?></div><?php
  $report = 'Exception: '.$exception->getMessage().' in '.$exception->getFile().':'.$exception->getLine();
  if(cfg('debug/verboselog')) logError('log/error.log', $report);
  return(true);
}

/* replaces the standard PHP error handler, mainly because we want a stack trace */
function h2_errorhandler($errno, $errstr, $errfile = __FILE__, $errline = -1)
{
  if($GLOBALS['errorhandler_ignore']) return;
  $bt = debug_backtrace();
  ?><div class="errormsg" style="border: 1px solid red; padding: 8px; font-size: 8pt; font-family: consolas; background: #ffffe0;">
    <b>Hubbub Runtime Error: <br/><span style="color: red"><?php echo $errstr ?></span></b><br/>
    <?
    if($errno > -1) {
    ?>
    File: <?php echo basename($errfile) ?><pre style="margin:0;padding:0;"><?php 
    unset($bt[0]);
    foreach($bt as $trace)
    {
      print('^ '.$trace['function'].'(');
      if(cfg('debug/showparams') && is_array($trace['args'])) print(implode(', ', $trace['args']));
      print(') in '.basename($trace['file']).' | Line '.$trace['line']."\n");
    }
  ?></pre><? } ?></div><?php
  $report = 'Error: '.$errstr.' in '.$errfile.':'.$errline."\r\n";
  logError('log/error.log', $report);
  return(true);
}
	
/* use this to instance a controller object */
function h2_getController($controllerName)
{
  $controllerName = safeName($controllerName);
	$controllerFile = 'mvc/'.strtolower($controllerName).'/'.strtolower($controllerName).'.controller.php';
	if(!file_exists($controllerFile))
	{
		// maybe this is a user URL
	  $entityDS = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE 
	    user=? AND _local="Y"', array($controllerName));
	  if(sizeof($entityDS) > 0)
	  {
	  	$GLOBALS['msg']['entity'] = HubbubEntity::ds2array($entityDS);
	  	$GLOBALS['msg']['touser'] = $controllerName;
	  	$controllerName = 'userpage';
	  	$_REQUEST['action'] = 'index';
	  	$controllerFile = 'mvc/'.strtolower($controllerName).'/'.strtolower($controllerName).'.controller.php';
	  }
	  else
	  {
	    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	    header('Status: 404 Not Found');	
	    die('File not found: '.$_SERVER['REQUEST_URI'].'<br/>'.$controllerName);     
    }
	}
	require_once($controllerFile);
  $controllerClassName = $controllerName.'Controller';
	$thisController = new $controllerClassName($controllerName);
	if (is_callable(array($thisController, '__init'))) $thisController->__init();
	$GLOBALS['controllers'][] = &$thisController;
	$GLOBALS['obj']['controller'] = &$thisController;
	return($thisController);
}

/* executing an action on a controller object */
function h2_invokeAction(&$controller, $action)
{
  $controller->invokeAction($action);
}

/* invoke a controller's view */
function h2_invokeView(&$controller, $action)
{
	return($controller->invokeView($action));
}

/* require enhanced security for calls that modify data */
function access_policy($types = 'auth')
{  
  foreach(explode(',', str_replace('write', 'origin,post', $types)) as $type)
    switch(trim($type))
    {
      case('auth'): {
        /* allow only sessions with a valid uid, if not present redirect to signin page */
        if($_SESSION['uid']+0 == 0)
      		redirect(actionUrl('index', 'signin'));
        break; 
      }
      case('origin'): {
        // require the origin to be the same server
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        if(!is_this_host($ref['host'])) die('Error: Hubbub access policy violation (origin)');
        break; 
      }
      case('post'): {
        // require the POST HTTP method
        if($_SERVER['REQUEST_METHOD'] != 'POST') die('Error: Hubbub access policy violation (POST required)');
        break; 
      }
      case('admin'): {
        if(!object('user')->isAdmin()) die('Error: admin access required'); 
        break; 
      }
    }
}

function is_this_host($hostName)
{
  return(true);
  $hostName = strtolower($hostName);  
  return($hostName == strtolower(cfg('service/server')) || 
    $hostName == strtolower($_SERVER['SERVER_ADDR']) ||
    $hostName == strtolower($_SERVER['SERVER_NAME']) ||
    $hostName == strtolower($_SERVER['HTTP_HOST']));
}

function h2_execute_event($event_name, &$data, &$d2 = array(), &$d3 = array())
{
  $event_name = str_replace('.', '_', $event_name);
  $handlers = $GLOBALS['config']['plugins'][$event_name];
  if(isset($handlers)) foreach($handlers as $plugin_name)
  {
    $func_name = $plugin_name.'_'.$event_name;
    if(!is_callable($func_name)) include_once('plugins/'.$plugin_name.'/events.php');
    if(is_callable($func_name)) return($func_name($data, $d2, $d3));
    else logError('plugins', 'invalid plugin event call: '.$func_name.'() not found');
  }
}

/* class for the currently signed-in user */
class HubbubUser
{
	function __construct()
	{
		$GLOBALS['userobj'] = &$this;
		if($_SESSION['uid'] == 0 && $_COOKIE['session-key'] != '')
		{
			$cds = DB_GetDataset('users', $_COOKIE['session-key'], 'u_sessionkey');
			if(sizeof($cds) == 0)
				setcookie('session-key', '', time()-3600);
			else
			{
				$_SESSION['uid'] = $cds['u_key'];
        $GLOBALS['userds'] = $cds;
        redirect(actionUrl($_REQUEST['action'], $_REQUEST['controller'], $_REQUEST));
			}
		}
    if($_SESSION['uid'] > 0)
		{
      if(!is_array($GLOBALS['userds'])) 
      {
        // the first user object to be initialized is the current user
        $GLOBALS['userds'] = DB_GetDataset('users', $_SESSION['uid']);
        $this->ds = &$GLOBALS['userds'];
      }
			$this->settings = unserialize($this->ds['u_settings']);
			$this->lang = getDefault($this->ds['u_l10n'], 'en');
			$this->id = $this->ds['u_key'];
			$this->entity = $this->ds['u_entity'];
			if(!is_array($this->settings)) $this->settings = array();
			// if essential user data is missing, redirect to the page where they can be filled in
			if(($this->ds['u_entity'] == 0 || trim($this->ds['u_name']) == '')
			  && $_REQUEST['controller'] != 'profile' && $_REQUEST['controller'] != 'signin' && $_REQUEST['action'] != 'user')
			{
				redirect(actionUrl('user', 'profile'));
			}
		}
	}
		
	function isAdmin()
	{
	  return($this->ds['u_type'] == 'A' || $this->ds['u_key'] == 1);
  }
		
	function key()
	{
		return($this->ds['u_key']);
	}
		
	function loginWithId($id)
	{
		$_SESSION['uid'] = $id;
		$this->login();
	}
	
	function login()
	{
		$this->ds = DB_GetDataset('users', $_SESSION['uid']);
		if(sizeof($this->ds) == 0) return;
		$this->settings = unserialize($this->ds['u_settings']);
    if(!is_array($this->settings)) $this->settings = array();
    foreach(array('email_friendrequest' => 'Y',
      'email_wallpost' => 'Y',
      'email_comment' => 'Y',
      'email_response' => 'Y',
      'email_message' => 'Y',
      ) as $setk => $setv) $this->settings[$setk] = getDefault($this->settings[$setk], $setv);
    if(!isset($_COOKIE['session-key']) || $_COOKIE['session-key'] == '') 
    {
      if($this->ds['u_sessionkey'] == '')
      {
        $this->ds['u_sessionkey'] = randomHashId();
        $this->save();
      }
      setcookie('session-key', $this->ds['u_sessionkey'], time()+3600*24*30*3);
    }
    $this->save();
    h2_execute_event('user_login', $this->entityDS, $this->ds);
	}
	
	function logout()
	{
	  h2_execute_event('user_logout', $this->entityDS, $this->ds);
		$_SESSION = array();
		foreach($_COOKIE as $k => $v)
			setcookie($k, '', time()-3600);
		session_destroy();
	}
	
	function save()
	{
    $this->loadEntity();
    $this->entityDS['name'] = $this->ds['u_name'];
		if($this->entityDS['_local'] == 'Y') $this->entityDS['server'] = cfg('service/server');
		h2_execute_event('user_save', $this->entityDS, $this->ds);
		if(trim($this->entityDS['user']) != '')  $this->entityDS['_key'] = DB_UpdateDataset('entities', $this->entityDS);
		$this->ds['u_settings'] = serialize($this->settings);
		if(trim($this->ds['u_settings']) != '') DB_UpdateDataset('users', $this->ds);
	}
	
	function selfEntity()
	{
		$this->loadEntity();
		return($this->entityDS);
	}
	
	function loadEntity()
	{
    if(!isset($this->entityDS)) $this->entityDS = DB_GetDataset('entities', $this->ds['u_entity']);		
	}
	
	function getUsername()
	{
		if($this->ds['u_entity'] == 0) return('');
		$this->entityDS = DB_GetDataset('entities', $this->ds['u_entity']);  
		return($this->entityDS['user']);
	}
	
	function getUrl()
	{
		$this->loadEntity();
		return($this->entityDS['url']);
	}
}

class HubbubController
{
	function __construct($name)
	{
    $this->name = $name;
		$this->user = &$GLOBALS['obj']['user'];
		$this->menu = array();
		$GLOBALS['submenu'] = &$this->menu;
		$GLOBALS['currentcontroller'] = &$this;
    l10n_load('mvc/'.$this->name.'/l10n');
	}		
	
	function redirect($action, $controller = null, $params = array())
	{
		if($controller == null) $controller = $_REQUEST['controller'];
		ob_clean();
		if($_SESSION['redirect.override'])
		{
		  header('X-Redirect: '.$_SERVER['REQUEST_URI']);
			header('location: '.$_SESSION['redirect.override']);
			unset($_SESSION['redirect.override']);
		}
		else
		{
		  header('X-Redirect: '.$_SERVER['REQUEST_URI']);
      header('location: '.actionUrl($action, $controller, $params));
		}
		ob_end_flush();
		die();
	}
	
	/*
	 * creates contextual menu items by letting controllers specify what actions should be menu items
	 */
	function makeMenu($str, $add = array(), $params = array())
	{
		$result = array();
		$ctr = -1;
		foreach(explode(',', $str) as $item)
		{
			$ctr++;
			if(substr($item, 0, 1) == ':') $url = substr($item, 1); else $url = actionUrl($item, $this->name, $params);
			if(substr($item, 0, 1) == '#') 
			  $result[] = array('type' => 'header', 'caption' => l10n($item).$add[$ctr]);
      else
  			$result[] = array('url' => $url, 'action' => $item, 'caption' => l10n($item).$add[$ctr]);
		} 
		return($result);
	}

	function invokeAction($action)
  {
    $action = getDefault($action, cfg('service/defaultaction'));
		$this->lastAction = $action;

    if(substr($action, 0, 5) == 'ajax_')
    {
      $this->skipView = true;
      $GLOBALS['config']['page']['template'] = 'blank';
    }
    
    if(is_callable(array($this, $action)))
      $this->$action($_REQUEST);
    else
      h2_errorhandler(0, 'Action not defined: '.$this->name.'.'.$action);
      
    $GLOBALS['config']['page']['title'] = $action;      
  }
  		
	function invokeView($action)
	{
    $this->pageTitle = l10n($action.'.title', $action);
    ob_start();
    $action = getDefault($action, cfg('service/defaultaction'));
		if(!$this->skipView)
		{
      include('mvc/'.strtolower($this->name).'/'.strtolower($this->name).'.'.getDefault($this->viewName, $action).'.php');
		}
    return(ob_get_clean());			
	}
	
	function invokeModel($modelname = null)
	{
		// model instances should be singletons, so we're making sure they are
		$modelname = strtolower(getDefault($modelname, $this->name));
		if($GLOBALS['models'][$modelname])
		{
			$this->$modelname = &$GLOBALS['models'][$modelname];
			return($GLOBALS['models'][$modelname]);
		}
		else
		{
      $modelClassName = $modelname.'Model';
      require_once('mvc/'.$modelname.'/'.$modelname.'.model.php');
      $thisModel = new $modelClassName($modelname);
      $GLOBALS['models'][$modelname] = &$thisModel;
			$this->$modelname = &$thisModel;
			if($modelname == $this->name) 
				$this->model = &$thisModel;
			return($thisModel);
		}
	}
}

class HubbubModel { }

function result_fail($reason = 'n/a', $preArray = array())
{
  $preArray['result'] = 'fail';
  $preArray['reason'] = $reason;
	return($preArray);
}

function result_ok($preArray = array())
{
	$preArray['result'] = 'OK';
	return($preArray);
}

/**
 * Generic Hubbub2 message class
 * Though it _does_ contain a lot of methods, the goal here is to provide basic functionality agnostic regarding
 * the message type. Message type-specific code is supposed to be confined to the type-specific event handlers
 * in msg/ (see there). In retrospect, this class should probably have been split into at least two separate 
 * layers - one for local storage and one for network transmission - but it's quite convenient as it is right now.
 */
class HubbubMessage
{
  // these are just setters or getters, with no deeper program logic (yet)
  function author($ads) { $this->data['author'] = $ads; }
  function owner($ads) { $this->data['owner'] = $ads; }
	function to($ads) { $this->owner($ads); }
  function from($ads) { $this->author($ads); }
	function newMsgId() {	return(randomHashId()); }
	function getExistingDS()	{ return(DB_GetDataset('messages', $this->data['msgid'], 'm_id')); }
  function markChanged($time = null) { if($time == null) $time = time(); $this->data['changed'] = $time; }
	function index(&$ds)	{ /* indexing hook, not used right now */ }
	function unpackData($dataset)	{	return(json_decode(gzinflate($dataset['m_data']), true)); }
	
	function __construct($type = null)
	{			
	  if($type != null) $this->create($type);
		$this->doPublish = 'N';
		$this->localUserEntity = object('user')->entity;
		$this->doSave = true;
	}
	
	/**
	 * Prepare a "fail" response
	 * @param string $reason
	 * @return 
	 */
	function fail($reason)
	{
		$this->response = result_fail($reason, $this->response);
    $s = '[IN] msg fail, type "'.$this->data['type'].'": '.$reason.' / ID:'.$this->data['msgid'];
    WriteToFile('log/activity.log', $s.chr(10));
		logError('notrace', $s);
		return(true);
	}

  /**
   * Prepare an all-clear response
   * @return 
   */
  function ok()
	{
	  if($this->response['result'] != 'fail') 
	    $this->response = result_ok($this->response);
		return(true);
	}
	
  /**
   * Execute message event handler
   * @param string $event
   * @param array $opt [optional]
   * @return false if not handled
   */
  function executeHandler($event, $opt = array())
	{
    $result = false;
		$handlerFile = 'msg/'.$this->type.'.php';
    $handlerFunc = $this->type.'_'.$event;
    h2_execute_event($this->type.'_'.$event.'_init', $this->data, $this, $result);
    if(!is_callable($handlerFunc) && file_exists($handlerFile)) include_once($handlerFile);
    if(is_callable($handlerFunc)) $result = $handlerFunc($this->data, $this, $opt); 
    h2_execute_event($this->type.'_'.$event, $this->data, $this, $result);
		return($result);
	}
		
	/**
	 * create a new message
	 * @param object $type
	 * @param object $opt [optional]
	 * @return 
	 */
	function create($type, $opt = array())
	{
		$this->data = array(
		  'type' => $type, 
			'msgid' => $this->newMsgId());
		$this->type = &$this->data['type'];
		$this->executeHandler('create', $opt);
	}

	function load($p)
	{
	  $ds = DB_GetDataset('messages', $p['id'], getDefault($p['field'], 'm_key')); 
	  if(sizeof($ds) == 0) return(false);
		$this->data = $this->unpackData($ds);
		$this->initEntities();
    $this->ds = $ds;
		return($this->executeHandler('load'));
    return(true);		  
  }
	
	
	function compareWithDS($ds, $fields)
	{
	  $fails = array();
	  $data = $this->unpackData($ds);
	  foreach($fields as $v)
	  {
	    if(!is_array($this->data[$v]))
	    {
	      // compare text fields by their string value
	      if(trim($this->data[$v]) != trim($data[$v])) $fails[] = $v.':'.$this->data[$v].'!='.$data[$v];
      }
      else
      {
        // compare entity records by URL
        if($this->data[$v]['url'] != $data[$v]['url']) $fails[] = $v;
      }
    }
    return($fails);
  }


	/**
	 * saves the message to the database
	 * @return 
	 */
	function save()
	{
		$this->sanitizeDataset();
    $this->existingDS = $this->getExistingDS();
		if(sizeof($this->existingDS) > 0)
		{
		  // if this message already exists in the database, we need to verify that the user hasn't changed
		  // any of the immutable fields: author, owner, parent, created
		  $compareFails = $this->compareWithDS($this->existingDS, array('author', 'owner', 'parent', 'created'));
		  if(sizeof($compareFails) > 0)
		  {
		    // fields have changed, we cannot allow this
		    $this->fail('Immutable field violation: '.implode(', ', $compareFails)); 
		    return(false);
      }
    }
    // if this message has a parent, we need to retrieve its DB key
    // todo: origin verification!
    if($this->data['parent'] != '')
    {
      $this->parentDS = DB_GetDataset('messages', $this->data['parent'], 'm_id');
      $this->parentKey = getDefault($this->parentDS['m_key'], '-1');
    }
		$this->executeHandler('save');
		if($this->doSave)
		{
			$this->payload = json_encode($this->data);
			$packedData = gzdeflate($this->payload);
			$this->ds = array(
			  'm_id' => $this->data['msgid'],
				'm_owner' => $this->ownerKey,
				'm_author' => getDefault($this->authorKey, $this->ownerKey),
				'm_created' => getDefault($this->data['created'], time()),
				'm_changed' => getDefault($this->data['changed'], $this->data['created']),
				'm_type' => $this->data['type'],
				'm_data' => $packedData,
				'm_compression' => round(100-100*(strlen($packedData)/strlen($this->payload))),
				'm_votehash' => $this->voteHash,
        'm_parent' => $this->parentKey,
				'm_publish' => $this->doPublish,
				'm_tag' => $this->vTag,
				'm_deleted' => ($this->data['deleted'] == 'yes') ? 'Y' : 'N',
				);
			if($this->existingDS['m_key'] > 0)
			{
        $this->ds['m_key'] = $this->existingDS['m_key'];
			  // only update an existing message if the "changed" stamp is later than before
			  #if($this->existingDS['m_changed'] > 0 && $this->data['changed'] <= $this->existingDS['m_changed']) return(false);
			}
			$this->ds['m_key'] = DB_UpdateDataset('messages', $this->ds);
			$this->index($this->ds);
			return(true);
		}
		return(false);
	}
		
	/**
	 * receive a message packet, check for sanity, pass on to type-specific event handler "receive"
	 * @param array $msgData
	 * @return 
	 */
	function receive($json_data, $signature = '')
	{
		$this->signature = $signature;
		$this->payload = trim($json_data);
		$this->data = json_decode($this->payload, true);
		$this->type = &$this->data['type'];
		$this->response = array();
		$this->initEntities();
    $this->sanitizeFields();
		return($this->executeHandler('receive'));
	}
	
	/**
	 * receive one message as part of a stream, check for sanity, pass on to type-specific event handler "receive_single"
	 * (the stream itself is supposed to be properly authenticated and checked beforehand)
	 * @param array $msgData
	 * @return 
	 */
	function receive_single(&$dataArray)
	{
    $this->data = $dataArray;
		$this->type = &$this->data['type'];
		$this->initEntities();
    $this->sanitizeFields();
		return($this->executeHandler('receive_single'));
  }

  /* init the object's properties from the data at hand */		
  function initEntities()
  {
		$this->type = &$this->data['type'];
    $this->ownerEntity = new HubbubEntity($this->data['owner']);
		if(sizeof($this->data['author']) > 0)
      $this->authorEntity = new HubbubEntity($this->data['author']);
    else
      $this->authorEntity = $this->ownerEntity;
    $this->authorKey = $this->authorEntity->key();
    $this->ownerKey = $this->ownerEntity->key();
    $this->doPublish = getDefault($this->ds['m_publish'], 'N');
    $this->isDeleted = $this->data['deleted'] == 'yes';
  }

	/* clean up fields and fill default values where necessary */
	function sanitizeFields()
	{
	  $this->ownerKey = $this->ownerEntity->key();
	  $this->authorKey = $this->authorEntity->key();
		$this->data['author'] = HubbubEntity::ds2arrayShort($this->authorEntity->ds);
    $this->data['owner'] = HubbubEntity::ds2arrayShort($this->ownerEntity->ds);
    $this->data['created'] = getDefault($this->data['created'], time());
    $this->data['changed'] = getDefault($this->data['changed'], $this->data['created']);
    $this->data['msgid'] = getDefault($this->data['msgid'], $this->newMsgId());
  }
	
	/* load associated entity objects, clean up fields, prepare payload */
	function sanitizeDataset()
	{
    $this->initEntities();
    $this->sanitizeFields();
    $this->payload = json_encode($this->data);
	}
	
	/* send this message to the owner's server */
	function sendToOwner()
	{
    $ownerServer = $msg->ownerEntity->ds['server'];
    return($this->sendToUrl($ownerServer));
  }

  /**
   * This sends a blanket DMN to the user's closest connections
   */
  function broadcast()
  {
		$this->sanitizeDataset();
		$this->executeHandler('broadcast');
  }
	
	/**
	 * sends the current message to a server URL
	 * @param string $url
	 * @return 
	 */
	function sendToUrl($url, $forceKey = null)
	{			
	  if(trim($url) == '')
	  {
	    logError('', 'Message['.$this->type.']::sendToUrl(empty)');
	    return(array('result' => 'fail', 'reason' => 'Server URL cannot be empty')); 
    }
	  $this->sanitizeDataset();
	  $this->toServer = new HubbubServer($url, true);
	  
	  if(!$this->toServer->isTrusted() && !strStartsWith($this->type, 'trust'))
	  {
	    $r = $this->toServer->msg_trust_sendkey1();
	    if($r['result'] != 'OK') return($r);
    }
	  
    $this->executeHandler('before_sendtourl', array('url' => $url));
    $this->payload = json_encode($this->data);
		if($this->toServer->outboundKey() != '' || $forceKey != null) $this->signForServer($this->toServer, $forceKey);
	  $result = HubbubEndpoint::request($url, array('hubbub_msg' => $this->payload, 'hubbub_sig' => $this->signature));
		//h2_audit_log('msg.send:'.$this->data['type'], $this->signature.': '.$this->payload);
	  $this->responseData = $result['data'];
    $this->executeHandler('after_sendtourl', array('url' => $url, 'result' => $result));
		return($result['data']);
	}
	
	/**
	 * signs an outgoing message
	 * @param object $srvObj
	 * @param string $overrideKey (optional)
	 * @return 
	 */
	function signForServer($srvObj, $overrideKey = '')
	{
		$overrideKey = getDefault($overrideKey, $srvObj->outboundKey());
		$this->signature = md5($overrideKey.trim($this->payload));
	}
			
  /**
   * Validates the message's signature. To succeed, the message must be signed (=must have
   * a "msgid" and a "signature" field) and the signature must correspond to the key
   * stored for the sending server. If any of these criteria isn't met, the message is
   * rejected.
   * @return 
   */
  function validateSignature($signedBy = 'author')
  {
    $this->fromServer = new HubbubServer($this->data[$signedBy]['server']);
		$validSignature = md5(trim($this->fromServer->inboundKey()).trim($this->payload));
		if($validSignature != $this->signature)
		{
			$this->expectedSignature = $validSignature;
			$this->fail('invalid signature');
			WriteToFile('log/activity.log', $this->data['msgid'].' rcv INVALID signature '.$this->signature.' for payload '.$this->fromServer->inboundKey().':'.md5($this->payload).'='.$validSignature.'!'.$this->signature.chr(10));
			return(false);
		}
		else return(true);
  }
}

class HubbubEntity
{
	function __construct($record = null)
  {     
    if($record != null) 
		{
		  if(!is_array($record))
		  {
		    $record['_key'] = $record;
      }
		  if($record['type'] == 'server' || $record['user'] == '*')
		  {
		    $this->ds = $record;
		    return;
      }
			// if the record contains a "_key"
			if($record['_key'] > 0)
			{
			  $this->ds = DB_GetDataset('entities', $record['_key']);
				if($this->ds['_local'] == 'Y') $this->ds['server'] = cfg('service/server');
				if($this->ds['_key'] > 0) return;
			}
			// if the user is identified by their Hubbub URL:
			if($record['url'] != '')
			{
				$this->ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE url=?', array($record['url']));
				if(sizeof($this->ds) > 0) return;
			}
			$record['server'] = strtolower(trim($record['server']));
			if(!$this->load($record['user'], $record['server']))
			{
        $this->create($record, $record['server'] == cfg('service/server'));
			}
		}
  }
	
	function create($record, $isLocal)
	{
		if(!$this->load($record['user'], $record['server']))
		{
		  $this->server = new HubbubServer($record['server'], true);
      $this->ds = array(
        'user' => $record['user'],
        'server' => $record['server'],
        'url' => $record['url'],
        'pic' => $record['pic'],
        'name' => $record['name'],
        '_serverkey' => $this->server->ds['s_key'],
        );
      if($isLocal) $this->ds['_local'] = 'Y'; else $this->ds['_local'] = 'N';
      if(trim($this->ds['user']) != '') $this->ds['_key'] = DB_UpdateDataset('entities', $this->ds);
		}
		return($this->ds);
	}
	
	function ds2array($ds)
	{
		$result = array();
		if(is_array($ds)) foreach($ds as $k => $v) if(substr($k, 0, 1) != '_') $result[$k] = $v;
		return($result);
	}

	function ds2arrayShort($ds)
	{
    return(array(
      'url' => $ds['url'],
      'server' => $ds['server'],
      'user' => $ds['user'],
      ));
	}
	
	function key()
	{
		return($this->ds['_key']);
	}
	
  function link($ds)
  {
  	return('<a href="'. 
  	  actionUrl('view', 'profile', array('id' => 0+$ds['_key'])).
  	  '">'.htmlspecialchars(getDefault($ds['name'], $ds['url'])).'</a>');
  }
	
	function linkFromId($idkey, $options = array())
	{
		if(!isset($GLOBALS['entitycache'][$idkey])) 
			$GLOBALS['entitycache'][$idkey] = DB_GetDataset('entities', $idkey);
    $entity = $GLOBALS['entitycache'][$idkey];
		$entityName = $entity['name'];
		if($options['short']) $entityName = CutSegment(' ', $entityName);
		if($options['nolink']) return(getDefault($entityName, '(unknown)'));
		if(object('user')->entity == $idkey)
      return('<a href="'.actionUrl('index', 'profile').'">'.getDefault($entityName, '(unknown)').'</a>');
		else
      return('<a href="'.actionUrl('index', 'view', array('id' => $idkey)).'">'.getDefault($entityName, '(unknown)').'</a>');
	}
	
	function load($user, $server)
	{
		$this->ds = $this->findEntityGlobal(array('user' => $user, 'server' => $server));
		if(sizeof($ds) > 0)
		{
			// todo: some other stuff
			return(true);
		}
		else return(false);
	}
	
	function isNameAvailable($username)
	{
		$match = DB_GetDatasetWQuery('SELECT count(*) FROM '.getTableName('entities').' WHERE _local="Y" AND user=?', array($username));
		return($match['count(*)'] == 0);
	}
	
	/* find a local entity */
	function findEntity($record)
	{
		$rec = array();
		$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE _local="Y" AND user=?', 
      array($record['user']));
    if($ds['_local'] == 'Y') $ds['server'] = cfg('service/server');
    return($ds);
	}
	
	/*finding an entity an entity identified by user and server*/
  function findEntityGlobal($record)
  {
    $rec = array();
    $record['server'] = strtolower(trim($record['server']));
    $ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE user=? AND server=?', 
      array($record['user'], getDefault($record['server'], cfg('service/server'))));
    if($ds['_local'] == 'Y') $ds['server'] = cfg('service/server');
    return($ds);
  }
}

class HubbubEndpoint
{
  function urlUnify($raw)
  {
    if(!strStartsWith($raw, 'http')) $raw = 'http://'.$raw;
    $u = parse_url($raw);
    $s = strtolower($u['host']).$u['path']; 
    if($u['query'] != '') $s .= '?'.$u['query'];
    return($s);
  }
	
	function request($url, $postData = array(), $options = array())
	{
	  return(cqrequest($url, $postData, 3));
	}
}

class HubbubServer
{
	function __construct($serverUrl, $createIfNotExistant = false)
  {     
    $this->url = trim(strtolower($serverUrl));
		$this->ds = DB_GetDataset('servers', $this->url, 's_url');
		if($createIfNotExistant == true)
		{
			if(sizeof($this->ds) == 0)
			{
				$this->ds['s_url'] = $this->url;
				$this->ds['s_name'] = $this->url;
				$this->save();
			}
		}
  }
	
  function outboundKey()
  {
    return($this->ds['s_key_out']);
  }
  
  function inboundKey()
  {
    return($this->ds['s_key_in']);
  }
  
	function entity()
	{
		return(array(
      'server' => $this->ds['s_url'],
      'type' => 'server',
      'user' => '*',
			));
	}
	
	function localEntity()
	{
    return(array(
      'server' => cfg('service/server'),
      'type' => 'server',
      'user' => '*',
      ));
	}
	
	/**
	 * If the server has an outbound and inbound key associated with it, it's "trusted"
	 * @return 
	 */
	function isTrusted()
	{
		return(trim($this->ds['s_key_out']) != '' && trim($this->ds['s_key_in']) != '');
	}
	
	function msg_trust_sendkey1()
	{
		// make a trust_sendkey1 message
		$msg = new HubbubMessage('trust_sendkey1');
		$msg->to($this->entity());
		$msg->data['author'] = $this->localEntity();
		$this->save();
		// make new key if there is none
    $this->ds['s_key_in'] = getDefault($this->ds['s_key_in'], randomHashId());
		if($this->ds['s_url'] != '')
      DB_UpdateField('servers', $this->ds['s_key'], 's_key_in', $this->ds['s_key_in']);
		$msg->data['mykey'] = $this->ds['s_key_in'];
		// we need to save at this point because the other server will try to make a trust_sendkey2-request in the meantime
		// send message to other server
		$responseData = $msg->sendToUrl($this->ds['s_url']);
		if($responseData['result'] == 'OK')
		{
      $this->ds['s_status'] = 'OK';
			$ret = result_ok();
		}
		else
		{
      $this->ds['s_status'] = 'fail';
			$this->ds['s_key_in'] = '';
			logError('notrace', '[OUT] trust_sendkey1 failed, server '.$this->ds['s_url'].' says: '.getDefault($responseData['reason'], $responseData['result']));
			$ret = result_fail('trust_sendkey1 failed: '.getDefault($responseData['reason'], $responseData['result']));
		}
		return($ret);
	}
	
	function save()
	{
	  if(trim($this->ds['s_url']) != '')
      $this->ds['s_key'] = DB_UpdateDataset('servers', $this->ds);			
	}
	
}


class HubbubConnection
{
   function __construct($fromEntityId = null, $toEntityId = null)
	 {
	 	 if($fromEntityId != null & $toEntityId != null)
		   $this->ds = DB_GetDatasetMatch('connections', array(
			   'c_from' => $fromEntityId,
				 'c_to' => $toEntityId,
				 ));
	 }	
	
   function save()
	 {
	 	 if($this->ds['c_from'] == 0 && $this->ds['c_to'] == 0) return;
	   if($this->ds['c_toserverkey'] == 0)
	   {
	     $toEntity = new HubbubEntity(array('_key' => $this->ds['c_to']));
       $this->ds['c_toserverkey'] = $toEntity->ds['_serverkey'];
     }
 	   DB_UpdateDataset('connections', $this->ds);
	 }
	 
	 function status($status = null)
	 {
	 	 if($status != null) 
	 	 {
	 	 	 $this->ds['c_status'] = $status;
	 	 	 $this->save();
	 	 }
		 return(getDefault($this->ds['c_status'], 'undefined'));
	 }
	 
	 function increaseCount($fromId, $toId, $type = 'sent')
	 {
	   if($fromId == 0 || $toId == 0) return;
	   $varname = 'c_count_'.$type;
	   DB_Update('UPDATE '.getTableName('connections').' SET '.$varname.' = '.$varname.'+1 WHERE c_from=? AND c_to = ?', array($fromId, $toId)); 
   }
	 
	 function GetClosestConnections($entityId)
	 {
	   return(DB_GetList('SELECT *,SUM(c_count_sent) as sent_count FROM '.getTableName('connections').' 
	     WHERE c_from = "'.($entityId+0).'" and c_status="friend" and c_count_sent > 0
	     GROUP BY c_toserverkey
	     ORDER BY sent_count DESC
	     LIMIT '.getDefault($GLOBALS['config']['service']['dmn_maxsize'], 10)));
   }
	 		
}

function h2_nv_store($name, $value)
{
  $nm = $_SESSION['uid'].'/'.$name;
	$ds = array('nv_name' => $nm, 'nv_value' => json_encode($value));
	DB_UpdateDataset('nvstore', $ds);
}

function h2_nv_retrieve($name)
{
  $nm = $_SESSION['uid'].'/'.$name;
	$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('nvstore').' WHERE nv_name LIKE ?', array($nm));
	$arv = json_decode($ds['nv_value'], true);
	if(!is_array($arv)) $arv = array();
	return($arv);
}

?>