<?php

  // some basic environment initialization
  function init_hubbub_environment()
  {
	  if (substr($_SERVER['REQUEST_URI'], 0, 1) == '/')
	    interpretQueryString($_SERVER['REQUEST_URI']);    
		$GLOBALS['obj']['user'] = new HubbubUser();
		
		switch($GLOBALS['obj']['user']->lang)
		{
      case('en'): {
        $GLOBALS['l10n'] = array(
          'stream' => 'Stream',
          'profile' => 'Profile',
          'friends' => 'Friends',
					'settings' => 'Settings',
          'logout' => 'Log out',
					'.title' => 'Home',
          'change' => 'change',
					'field.cannotbeempty' => 'please fill out this field',
          );
        break;
      }
      case('de'): {
        $GLOBALS['l10n'] = array(
          'stream' => 'Neuigkeiten',
          'profile' => 'Profil',
          'friends' => 'Freunde',
					'settings' => 'Optionen',
          'logout' => 'Abmelden',
					'.title' => 'Home',
          'change' => 'ändern',
					'field.cannotbeempty' => 'bitte fülle dieses Feld aus',
          );
        break;
      }
		}
		
		if(isset($_REQUEST['hubbub_msg']))
		{
			// we're receiving a message from another server
			$GLOBALS['msg']['touser'] = $_REQUEST['controller'];
			// invoke the endpoint controller to handle message
			$_REQUEST['controller'] = 'endpoint';
			$_REQUEST['action'] = 'index';
		}
  }
	
	function l10n($s)
	{
		return($GLOBALS['obj']['controller']->l10n($s));
	}
	
	function cfg($name, $default = null)
	{
		$vr = &$GLOBALS['config'];
		foreach(explode('.', $name) as $ni) if(isset($vr)) $vr = &$vr[$ni];
		$vr = getDefault($vr, $default);
		return($vr);
	}
	
	function dumpArray($r)
	{
    ob_start(); print_r($r); return(ob_get_clean());		
	}
	
	function object($name)
	{
		return($GLOBALS['obj'][$name]);
	}
	
	function audit_log($op, $data = '', $code = '', $result = '', $reason = '')
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
	
	function redirect($tourl)
	{
		header('location: '.$tourl);
		die();
	}
  
	// replaces the standard PHP error handler, mainly because we want a stack trace
  function h2_errorhandler($errno, $errstr, $errfile = __FILE__, $errline = -1)
  {
    $bt = debug_backtrace();
    ?><div class="errormsg" style="border: 1px solid red; padding: 8px; font-size: 8pt; font-family: consolas; background: #ffffe0;">
      <b>Hubbub Runtime Error: <br/><span style="color: red"><?php echo $errstr ?></span></b><br/>
      File: <?php echo $errfile ?><br/><?php 
      unset($bt[0]);
      foreach($bt as $trace)
      {
        print('^ &nbsp;'.$trace['function'].'(');
        if(cfg('debug.showparams') && is_array($trace['args'])) print(implode(', ', $trace['args']));
        print(') in '.$trace['file'].' | Line '.$trace['line'].'<br/>');
      }
      /*ob_start();
      print_r($bt);
      $report = ob_get_clean();*/
			
    ?></div><?php
    if(cfg('debug.verboselog')) logToFile('log/error.php.log', $report);
    return(true);
  }
		
	// use this to instance a controller object
	function getController($controllerName)
	{
		$controllerFile = 'mvc/'.strtolower($controllerName).'/'.strtolower($controllerName).'.controller.php';
		if(!file_exists($controllerFile))
		{
			// maybe this is a user URL
		  $entityDS = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE 
		    user=? AND _local="Y"', array($controllerName));
		  if(sizeof($entityDS) > 0)
		  {
		  	$GLOBALS['msg']['entity'] = $entityDS;
		  	$GLOBALS['msg']['touser'] = $controllerName;
		  	$controllerName = 'userpage';
		  	$_REQUEST['action'] = 'index';
		  	$controllerFile = 'mvc/'.strtolower($controllerName).'/'.strtolower($controllerName).'.controller.php';
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

  // executing an action on a controller object
  // executing an action on a controller object
  function invokeAction(&$controller, $action)
  {
    $controller->invokeAction($action);
  }

  // invoke a controller's view
  function invokeView(&$controller, $action)
	{
		return($controller->invokeView($action));
	}

  function access_authenticated_only()
	{
    if($_SESSION['uid'] > 0)
    {

		}
		else
		{
			redirect(actionUrl('index', 'signin'));
		}
	}
	
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
				}
			}
      if($_SESSION['uid'] > 0)
			{
	      if(!is_array($GLOBALS['userds'])) $GLOBALS['userds'] = DB_GetDataset('users', $_SESSION['uid']);
	      $this->ds = &$GLOBALS['userds'];
				$this->settings = unserialize($this->ds['u_settings']);
				$this->lang = getDefault($this->ds['u_l10n'], 'en');
				$this->id = $GLOBALS['userds']['u_key'];
				$this->entity = $GLOBALS['userds']['u_entity'];
				if(!is_array($this->settings)) $this->settings = array();
				if((trim($this->ds['u_name']) == '' || $this->ds['u_entity'] == 0)
				  && $_REQUEST['controller'] != 'profile' && $_REQUEST['controller'] != 'signin' && $_REQUEST['action'] != 'user')
				{
					redirect(actionUrl('user', 'profile'));
				}
			}
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
          $this->ds['u_sessionkey'] = md5(time().rand(1, 100000));
          $this->save();
        }
        setcookie('session-key', $this->ds['u_sessionkey'], time()+3600*24*30*3);
      }
      $this->save();
		}
		
		function logout()
		{
			$_SESSION = array();
			foreach($_COOKIE as $k => $v)
				setcookie($k, '', time()-3600);
			session_destroy();
		}
		
		function save()
		{
      $this->loadEntity();
      $this->entityDS['name'] = $this->ds['u_name'];
			if($this->entityDS['_local'] == 'Y') $this->entityDS['server'] = cfg('service.server');
			if($this->entityDS['user'] != '')  $this->entityDS['_key'] = DB_UpdateDataset('entities', $this->entityDS);
			$this->ds['u_settings'] = serialize($this->settings);
			DB_UpdateDataset('users', $this->ds);
		}
		
		function selfEntity()
		{
			$this->loadEntity();
			return($this->entityDS);
		}
		
		function selfEntityEx()
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
		
		function setUsername($username)
		{
			$this->loadEntity();
			$this->entityDS['user'] = safename($username);
			$this->entityDS['url'] = getDefault($this->entityDS['url'], $GLOBALS['config']['service']['server'].'/'.$username);
			$this->entityDS['_local'] = 'Y';
			$this->entityDS['server'] = cfg('service.server');
			$ekey = DB_UpdateDataset('entities', $this->entityDS);
			$this->ds['u_entity'] = $ekey;
			DB_UpdateDataset('users', $this->ds);
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
		}		
		
		function l10n($key, $key2 = '', $silent = false)
		{
			$lang = getDefault($this->user->lang, 'en');
			if(!isset($this->l10n))
			{
				// if no language file has been loaded yet, we need to do so now:
				$this->l10n = array();
				$langFile = 'mvc/'.$this->name.'/l10n.'.$lang.'.cfg';
				if(file_exists($langFile)) $this->l10n = stringsToStringlist(file($langFile));
				if(is_array($GLOBALS['l10n'])) foreach($GLOBALS['l10n'] as $k => $v) $this->l10n[$k] = $v;
			}
			// if the key is valid
			if(isset($this->l10n[$key])) $result = $this->l10n[$key]; else 
			{
				if($silent) $result = $key;
				else 
				  $result = getDefault($this->l10n[$key2], '['.$key.' '.$lang.']');
			}
			if(substr($result, 0, 1) == '>') 
			  $result = getDefault($this->l10n[substr($result, 1)], '['.substr($result, 1).' '.$lang.']');
			return($result);
		}
		
		function loadl10n($file)
		{
			$fle = stringsToStringlist(file($file.'.'.getDefault($this->user->lang, 'en').'.cfg'));
			foreach($fle as $k => $v) $this->l10n[$k] = $v;
		}
		
		function redirect($action, $controller = null, $params = array())
		{
			if($controller == null) $controller = $_REQUEST['controller'];
			ob_clean();
			if($_SESSION['redirect.override'])
			{
				header('location: '.$_SESSION['redirect.override']);
				unset($_SESSION['redirect.override']);
			}
			else
			{
        header('location: '.actionUrl($action, $controller, $params));
			}
			ob_end_flush();
			die();
		}
		
		function makeMenu($str, $add = array())
		{
			$result = array();
			$ctr = -1;
			foreach(explode(',', $str) as $item)
			{
				$ctr++;
				if(substr($item, 0, 1) == ':') $url = substr($item, 1); else $url = actionUrl($item, $this->name);
				$result[] = array('url' => $url, 'action' => $item, 'caption' => $this->l10n($item).$add[$ctr]);
			} 
			return($result);
		}

		function invokeAction($action)
    {
      $action = getDefault($action, cfg('service.defaultaction'));
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
      $this->pageTitle = $this->l10n($action.'.title', $action);
	    ob_start();
	    $action = getDefault($action, cfg('service.defaultaction'));
			if($this->skipView)
			{
				// do nothing
			}
			else
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
	
	class HubbubModel
	{
		
	}
	
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
	 */
	class HubbubMessage
	{
		function __construct($type = null)
		{			
		  if($type != null) $this->create($type);
		}
		
		/**
		 * Prepare a "fail" response
		 * @param string $reason
		 * @return 
		 */
		function fail($reason)
		{
			$this->response = result_fail($reason, $this->response);
		}

    /**
     * Prepare an all-clear response
     * @return 
     */
    function ok()
		{
			$this->response = result_ok($this->response);
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
      if(!is_callable($handlerFunc) && file_exists($handlerFile)) include_once($handlerFile);
      if(is_callable($handlerFunc)) $result = $handlerFunc($this->data, $this, $opt); 
			return($result);
		}
		
		function from($entity)
		{
			$this->data['from'] = $entity;
		}
		
		function to($entity)
		{
			$this->data['to'] = $entity;
		}
		
		function newMsgId()
		{
			return(md5($GLOBALS['config']['service']['server'].time().rand(1, 1000000)));
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
		
		/**
		 * saves the message to the database
		 * @return 
		 */
		function save()
		{
			$this->doSave = true;
			$this->sanitizeDataset();
      // if this message has a parent
      // todo: origin verification!
      if($this->data['parent'] != '')
      {
        $this->parentDS = DB_GetDataset('messages', $this->data['parent'], 'm_id');
        $this->parentKey = getDefault($this->parentDS['m_key'], '-1');
      }
			$this->authorKey = $this->authorEntity->key();
			$this->ownerKey = $this->fromEntity->key();
			$this->executeHandler('save');
			if($this->doSave)
			{
				$this->payload = json_encode($this->data);
				$packedData = gzdeflate($this->payload);
				$sd = array(
				  'm_id' => $this->data['msgid'],
					'm_owner' => $this->ownerKey,
					'm_author' => getDefault($this->authorKey, $this->ownerKey),
					'm_created' => gmdate('Y-m-d H:i:s', getDefault($this->data['created'], time())),
					'm_changed' => gmdate('Y-m-d H:i:s', $this->data['changed']),
					'm_type' => $this->data['type'],
					'm_data' => $packedData,
					'm_compression' => round(100-100*(strlen($packedData)/strlen($this->payload))),
					'm_votehash' => $this->voteHash,
          'm_parent' => $this->parentKey,
					);
				$this->ds['m_key'] = DB_UpdateDataset('messages', $sd);
				$this->ds = $sd;
				$this->index($this->ds);
			}
		}
		
		function index(&$ds)
		{
			// assuming all friends can read this message 
			DB_Update('REPLACE INTO '.getTableName('index').' (i_userkey,i_msgkey)
			  SELECT u_entity,"'.$ds['m_key'].'" FROM '.getTableName('connections').' 
				LEFT JOIN '.getTableName('users').' ON (u_entity = c_to)
				WHERE c_from="'.object('user')->entity.'" AND c_status="friend" AND u_key > 0');
		}
		
		function unpackData($dataset)
		{
			return(json_decode(gzinflate($dataset['m_data']), true));
		}
		
		/**
		 * receive a message packet
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
      $this->fromEntity = new HubbubEntity($this->data['from']);
      $this->toEntity = new HubbubEntity($this->data['to']);
			return($this->executeHandler('receive'));
		}
		
		function sanitizeDataset()
		{
      if(!$this->data['from'] || sizeof($this->data['from']) == 0) $this->data['from'] = object('user')->selfEntityEx();
      $this->fromEntity = new HubbubEntity($this->data['from']);
      $this->toEntity = new HubbubEntity($this->data['to']);
      $this->authorEntity = new HubbubEntity($this->data['author']);
      $this->data['created'] = getDefault($this->data['created'], time());
      $this->data['changed'] = getDefault($this->data['changed'], time());
      $this->data['msgid'] = getDefault($this->data['msgid'], $this->newMsgId());
      $this->payload = json_encode($this->data);
		}
		
		/**
		 * sends the current message to a server URL
		 * @param string $url
		 * @return 
		 */
		function sendToUrl($url, $forceKey = null)
		{			
		  $this->sanitizeDataset();
		  $this->toServer = new HubbubServer($url);
			if($this->toServer->isTrusted() || $forceKey != null) $this->signForServer($this->toServer, $forceKey);
      $this->executeHandler('before_sendtourl', array('url' => $url));
		  $result = HubbubEndpoint::request($url, array('hubbub_msg' => $this->payload, 'hubbub_sig' => $this->signature));
			//audit_log('msg.send:'.$this->data['type'], $this->signature.': '.$this->payload);
		  $this->responseData = $result;
      $this->executeHandler('after_sendtourl', array('url' => $url, 'result' => $result));
			return($result);
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
			$this->signature = md5($overrideKey.$this->payload);
		}
				
    /**
     * Validates the message's signature. To succeed, the message must be signed (=must have
     * a "msgid" and a "signature" field) and the signature must correspond to the key
     * stored for the sending server. If any of these criteria isn't met, the message is
     * rejected.
     * @return 
     */
    function validateSignature()
    {
      $this->fromServer = new HubbubServer($this->data['from']['server']);
			$validSignature = md5(trim($this->fromServer->inboundKey()).trim($this->payload));
			//$this->usedSig = 's:'.$this->signature.'/v:'.$validSignature.'/f:'.$this->fromServer->inboundKey().'/e:'.md5($this->fromServer->inboundKey()).'/nk:'.md5($this->payload);
			if($validSignature != $this->signature)
			{
				$this->fail('invalid signature');
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
				// if the record contains a "_key"
				if($record['_key'])
				{
				  $this->ds = DB_GetDataset('entities', $record['_key']);
					if($this->ds['_local'] == 'Y') $this->ds['server'] = cfg('service.server');
					return;
				}
				// if the user is identified by their Hubbub URL:
				if($record['url'] != '')
				{
					$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE url=?', array($record['url']));
					if(sizeof($ds) > 0) return;
				}
				$record['server'] = strtolower(trim($record['server']));
				if(!$this->load($record['user'], $record['server']) && $record['server'] != cfg('service.server'))
				{
					// if this entity isn't known yet, create it
					$this->ds = array(
					  'user' => $record['user'],
						'server' => $record['server'],
						'url' => $record['url'],
						'pic' => $record['pic'],
						'name' => $record['name'],
						);
					$this->ds['_key'] = DB_UpdateDataset('entities', $this->ds);
				}
			}
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
		
		function linkFromId($idkey)
		{
			if(!isset($GLOBALS['entitycache'][$idkey])) 
				$GLOBALS['entitycache'][$idkey] = DB_GetDataset('entities', $idkey);
      $entity = $GLOBALS['entitycache'][$idkey];
			return('<a href="'.actionUrl('view', 'profile', array('id' => $idkey)).'">'.getDefault($entity['name'], '(unknown)').'</a>');
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
		
		function findEntity($record)
		{
			$rec = array();
			$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE _local="Y" AND user=?', 
        array($record['user']));
      if($ds['_local'] == 'Y') $ds['server'] = cfg('service.server');
      return($ds);
		}
		
    function findEntityGlobal($record)
    {
      $rec = array();
      $record['server'] = strtolower(trim($record['server']));
      $ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('entities').' WHERE user=? AND server=?', 
        array($record['user'], getDefault($record['server'], cfg('service.server'))));
      if($ds['_local'] == 'Y') $ds['server'] = cfg('service.server');
      return($ds);
    }
	}
	
	class HubbubEndpoint
	{
		function parseHubbubURL($url)
		{
			return($url);
		}
		
		function parseResponse($txt)
		{
	    $result = array();
	    $headerMode = true;
	    foreach(explode("\n", $txt) as $line)
	    {
	      if(trim($line) == '' && $headerMode && $result['headers']['response'] != '100')
	      {
	        $headerMode = false;
	      }
	      else if($headerMode)
	      {
	        if(substr($line, 0, 5) == 'HTTP/') 
	        {
	          $key = 'response';
	          CutSegment(' ', $line);
	          $code = CutSegment(' ', $line);
	          $result['headers'][$key] = trim($code);
	          $result['headers'][$key.'-full'] = trim($code.' '.$line);
	          $result['headers']['signed'] = $recipient['e_key_outbound'];
	        }
	        else
	        {
	          $key = trim(CutSegment(':', $line));
	          if($key != '') $result['headers'][$key] = trim($line);
	        }
	      }
	      else
	      {
	        $result['body'] .= trim($line)."\n";
	      }
	    }
			if(substr(trim($result['body']), 0, 1) == '{') $result['data'] = json_decode(trim($result['body']), true);
			return($result);
		}
		
		function request($url, $postData = array(), $options = array())
		{
			// set URL and other appropriate options
			$defaults = array(
	        CURLOPT_POST => (sizeof($postData) > 0),
	        CURLOPT_HEADER => 1,
	        CURLOPT_URL => $url,
	        CURLOPT_FRESH_CONNECT => 1,
					CURLOPT_FOLLOWLOCATION => 1,
					CURLOPT_RETURNTRANSFER => 1,
	        CURLOPT_FORBID_REUSE => 1,
	        CURLOPT_TIMEOUT => 3,
	      );	
			if(sizeof($postData) > 0) $defaults[CURLOPT_POSTFIELDS] = http_build_query($postData);
	    $ch = curl_init();
	    curl_setopt_array($ch, ($options + $defaults)); 
			// grab URL and pass it to the browser
			$result = HubbubEndpoint::parseResponse(curl_exec($ch));			
			// close cURL resource, and free up system resources
			curl_close($ch);
			return($result);
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
				));
		}
		
		function localEntity()
		{
      return(array(
        'server' => cfg('service.server'),
        'type' => 'server',
        ));
		}
		
		/**
		 * If the server has an outbound key associated with it, it's "trusted"
		 * @return 
		 */
		function isTrusted()
		{
			return(trim($this->ds['s_key_out']) != '');
		}
		
		function msg_trust_sendkey1()
		{
			// make a trust_sendkey1 message
			$msg = new HubbubMessage('trust_sendkey1');
			$msg->to($this->entity());
			$msg->from($this->localEntity());
			$this->save();
			// make new key if there is none
      $this->ds['s_key_in'] = getDefault($this->ds['s_key_in'], md5(time().rand(1, 10000)));
			DB_UpdateField('servers', $this->ds['s_key'], 's_key_in', $this->ds['s_key_in']);
			$msg->data['mykey'] = $this->ds['s_key_in'];
			// we need to save at this point because the other server will try to make a trust_sendkey2-request in the meantime
			// send message to other server
			$response = $msg->sendToUrl($this->ds['s_url']);
			$responseData = $response['data'];
			if($responseData['result'] == 'OK')
			{
        $this->ds['s_status'] = 'OK';
				$ret = result_ok();
			}
			else
			{
        $this->ds['s_status'] = 'fail';
				$this->ds['s_key_in'] = '';
				dumpArray($response);
				$ret = result_fail('trust_sendkey1 failed: '.getDefault($responseData['reason'], $responseData['result']));
			}
			return($ret);
		}
		
		function save()
		{
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
		 	 if($this->ds['c_from'] != 0 && $this->ds['c_to'] != 0)
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
		 
		 
		 		
	}

  function nv_store($name, $value)
	{
    $nm = $_SESSION['uid'].'/'.$name;
		$ds = array('nv_name' => $nm, 'nv_value' => json_encode($value));
		DB_UpdateDataset('nvstore', $ds);
	}
	
	function nv_retrieve($name)
	{
    $nm = $_SESSION['uid'].'/'.$name;
		$ds = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('nvstore').' WHERE nv_name LIKE ?', array($nm));
		return(json_decode($ds['nv_value'], true));
	}

?>