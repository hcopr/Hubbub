<?

class ProfileModel extends HubbubModel
{

  function getPostList($owner)
  {
    return(DB_GetList('SELECT * FROM '.getTableName('messages').'
      WHERE m_owner = ? AND m_parent = 0 AND m_deleted = "N" AND m_type="post"
      ORDER BY m_created DESC', array($owner)));
  }
  
  function getStream($forUserKey, $ownerKey)
  {
    return(DB_GetList('SELECT * FROM '.getTableName('messages').' 
		  LEFT JOIN '.getTableName('index').' ON (i_msgkey = m_key AND i_userkey = ?)
      WHERE (i_userkey = ? OR m_owner = ?) AND m_parent = 0 AND m_deleted = "N" AND m_type="post" 
      ORDER BY m_created DESC', array($forUserKey, $forUserKey, $ownerKey)));
  }
  
	function getComments($forPostKey, $getLimit = 3)
	{
		$countDS = DB_GetDatasetWQuery('SELECT COUNT(*) as count FROM '.getTableName('messages').'
        WHERE m_parent = ? AND m_deleted = "N" AND m_type="post" AND m_votehash = ""
        ', array($forPostKey));
		return(array(
		  'count' => $countDS['count'],
			'votes' => DB_GetList('SELECT COUNT(*) as votecount,m_data,m_id as count FROM '.getTableName('messages').'
        WHERE m_parent = ? AND m_deleted = "N" AND m_type="post" AND m_votehash != ""
        GROUP BY m_votehash
				ORDER BY votecount DESC, m_created ASC
				LIMIT 10
        ', array($forPostKey)),
		  'list' => array_reverse(DB_GetList('SELECT * FROM '.getTableName('messages').'
	      WHERE m_parent = ? AND m_deleted = "N" AND m_type="post" AND m_votehash = ""
	      ORDER BY m_created DESC
				LIMIT '.$getLimit, array($forPostKey))),
			));
	}
	
	function deletePost($postKey)
	{
    $msg = DB_GetDataset('messages', $postKey);
    if(object('user')->entity == $msg['m_owner'] || object('user')->entity == $msg['m_author'])
    {
      $msg['m_deleted'] = 'Y';
      DB_UpdateDataset('messages', $msg);
			return(true);
    }		
		else
		{
			return(false);
		}
	}
	
	function postToProfile($post)
	{
		$msg = new HubbubMessage('post');
		foreach($post as $k => $v) $msg->data[$k] = $v;
		$msg->save();
		return($msg->ds);
	}
	
	function postComment($comment)
	{
		// todo: origin verification!
    return($this->postToProfile($comment));
	}

}


?>