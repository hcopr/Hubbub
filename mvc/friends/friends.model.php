<?

class FriendsModel extends HubbubModel 
{
	function ignore($connection)
	{
		$connection->status('undefined');
	}
	
	function friend_request($toEntity, $msgtype = 'friend_request')
	{
    $fr = new HubbubMessage($msgtype);
    $fr->to($toEntity->ds);
    $fr->from(object('user')->entity);
    $res = $fr->sendToUrl($toEntity->ds['server']);
		return($res);
	}
	
	function loadUrl($url)
	{
		$result = array();
		require_once('lib/hubbub2_loadurl.php');
    $result = hubbub2_loadurl($url);
    if(sizeof($result) == 0 || $result['user'] == '' || $result['server'] == '')
    {
    	$result['result'] = 'fail';
    	$result['reason'] = 'entity_not_found';
    }
    else
    {
      $result['result'] = 'OK';
    }
    return($result);
	}
	
	function getMyGroups()
	{
	  l10n_load('mvc/friends/l10n');
	  $result = array();
	  $grpList = DB_GetList('SELECT * FROM '.getTableName('localgroups').' WHERE lg_entity = ?', array(object('user')->ds['u_entity'])); 
	  if(sizeof($grpList) == 0)
	  {
	    foreach(explode(',', '_friends,_colleagues,_acquaintances,_family') as $gname)
	    {
	      $nds = array('lg_entity' => object('user')->ds['u_entity'], 'lg_name' => $gname);
	      $nds['lg_key'] = DB_UpdateDataset('localgroups', $nds);
	      $grpList[] = $nds; 
      }
    }
    foreach($grpList as $grp)
    {
      if(substr($grp['lg_name'], 0, 1) == '_') $grp['lg_name'] = l10n($grp['lg_name']);
      $result[] = $grp;
    }
    return($result);
  }
	
	function getFriends($filter = 'friend', $forUserEntity = null)
	{
		if($forUserEntity == null) $forUserEntity = object('user')->entity;
		return(DB_GetList('SELECT * FROM '.getTableName('connections').'
		  LEFT JOIN '.getTableName('entities').' ON (c_to = _key)
		  WHERE c_from=? AND c_status=?
		  ORDER BY `name` ASC', array($forUserEntity, $filter)));
	}
	
	function friend_ignore($entityKey)
	{
	  DB_Update('DELETE FROM '.getTableName('connections').' WHERE
	    c_from = ? AND c_to = ?', array(object('user')->ds['u_entity'], $entityKey));
  }
  
  function friend_accept($entityKey, $groupId)
  {
    $connection = new HubbubConnection(object('user')->ds['u_entity'], $entityKey);
    $connection->group($groupId);
	  $friendEntity = new HubbubEntity($entityKey);
    return($this->friend_request($friendEntity));
  }
	
  function friend_remove($entityKey)
  {
	  $friendEntity = new HubbubEntity($entityKey);
    return($this->friend_request($friendEntity, 'friend_remove'));
  }
	
	function contactServer($url)
	{
		$this->server = new HubbubServer($url, true);
    if(!$this->server->isTrusted()) 
		{
			return($this->server->msg_trust_sendkey1());
		}
		return(result_ok());
	}
	
	function ABGetEntry($searchText)
	{
	  $result = cqrequest('http://hubbub.at/ab', array('mode' => 'url', 'q' => $searchText));
	  return($result['data']['items'][0]);
  }
	
  function ABNewEntry($entityDS, $commentText)
  {
    $entityDS['comment'] = $commentText;
    $entityDS['email'] = md5($this->user->ds['u_email']);
    $entityInfo = json_encode($entityDS);
    // this is important: we need to prepare the endpoint API to give out
    // the confirmation before we send the request to the AB, because the AB
    // may hold the request until it's completed ITS confirmation request 
    // back to us!
    h2_nv_store('abreq/'.$entityDS['_key'], array(
      'abrequest' => 'pending', 
      'checksum' => md5($entityInfo)));
    // now, make the request to add our info to the AB
    $result = cqrequest('http://hubbub.at/ab', array(
      'mode' => 'new', 
      'entity' => $entityInfo, 
      'callback' => actionUrl('abconfirm', 'endpoint', array(), true)));
    return($result['data']);
  }
	
}



?>