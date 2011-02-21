<?

class ProfileModel extends HubbubModel
{

  function setUsername($username)
	{
	  $userObj = object('user');
	  
	  $userObj->isNewUser = $this->ds['u_key'] > 0;
	  $userObj->server = new HubbubServer(cfg('service.server'), true);
		$userObj->loadEntity();
		
		$userObj->entityDS['user'] = safename($username);
		$userObj->entityDS['url'] = getDefault($userObj->entityDS['url'], cfg('service.server').'/'.(cfg('service.url_rewrite')?'':'?').$username);
		$userObj->entityDS['_local'] = 'Y';
		$userObj->entityDS['_serverkey'] = $userObj->server->ds['s_key'];
		$userObj->entityDS['server'] = cfg('service.server');
		
		if(trim($userObj->entityDS['user']) != '') $ekey = DB_UpdateDataset('entities', $userObj->entityDS);
		$userObj->ds['u_entity'] = $ekey;
		
		if(trim($userObj->ds['u_name']) != '') DB_UpdateDataset('users', $userObj->ds);
    if($userObj->isNewUser) h2_execute_event('user_new', $userObj->entityDS, $userObj->ds);
	}

}


?>