<?

class FriendsModel extends HubbubModel 
{
	function ignore($connection)
	{
		$connection->status('undefined');
	}
	
	function accept($connection)
	{
		$toEntity = new HubbubEntity(array('_key' => $connection->ds['c_to']));
    $res = $this->friend_request($toEntity);
		if($res['data']['result'] == 'OK')
		{
      $connection->status('friend');			
		}
		return($res);
    #$connection->status('undefined');
	}
	
	function remove($connection)
	{
    $toEntity = new HubbubEntity(array('_key' => $connection->ds['c_to']));
    $res = $this->friend_request($toEntity, 'friend_remove');
		$connection->status('undefined');
	}
	
	function friend_request($toEntity, $msgtype = 'friend_request')
	{
    $fr = new HubbubMessage($msgtype);
    $fr->data['to'] = array('server' => $toEntity->ds['server'], 'user' => $toEntity->ds['user']); 
    $res = $fr->sendToUrl($toEntity->ds['server']);
		return($res);
	}
	
	function loadUrl($url)
	{
		$result = array();
		require_once('lib/hubbub2_loadurl.php');
    $er = hubbub2_loadurl($url);
    if(sizeof($er) == 0 || $er['user'] == '' || $er['server'] == '')
    {
    	$result['result'] = 'fail';
    	$result['reason'] = 'entity_not_found';
    }
    else
    {
      $result['result'] = 'OK';
      $result['entity'] = $er;
    }
    return($result);
	}
	
	function getFriends($filter = 'friend')
	{
		return(DB_GetList('SELECT * FROM '.getTableName('connections').'
		  LEFT JOIN '.getTableName('entities').' ON (c_to = _key)
		  WHERE c_from=? AND c_status=?
		  ORDER BY `name` ASC', array(object('user')->id, $filter)));
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
	
}



?>