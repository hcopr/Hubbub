<?

class ProfileModel extends HubbubModel
{

  function getPostList($owner, $singlePostId = null)
  {
  	if($singlePostId != null) $match = 'AND m_key='.($singlePostId+0);
  	return(array(
		  'blurp_entity' => $owner,
		  'current_entity' => $owner,
		  'list' =>
			  DB_GetList('SELECT * FROM '.getTableName('messages').'
	      WHERE ((m_owner = ? AND m_parent = 0) OR (m_author = ? AND m_owner != ?)) AND m_deleted = "N" AND m_type="post" '.$match.'
	      ORDER BY m_created DESC', array($owner, $owner, $owner))));
  }
  
  function getStream($ownerKey)
  {
    return(array(
		  'current_entity' => $ownerKey,
			'list' => 
			  DB_GetList('SELECT * FROM '.getTableName('messages').' 
  			  LEFT JOIN '.getTableName('index').' ON (i_msgkey = m_key AND (i_entitykey = ? OR m_owner = ?))
  	      WHERE m_parent = 0 AND m_deleted = "N" AND m_type="post" 
  	      ORDER BY m_created DESC', array($ownerKey, $ownerKey))));
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
	  $msg = new HubbubMessage('post');
	  $msg->load(array('id' => $postKey, 'field' => 'm_key'));
	  $result = $msg->executeHandler('delete');
	  $this->msg = $msg;
	  return($result);
	}
	
	function makePostMessage($type, $post)
	{
		$msg = new HubbubMessage($type);
		foreach($post as $k => $v) $msg->data[$k] = $v;
    $msg->save();
		return($msg);
	}
	
	function Post($p)
	{
		// posts and comments have the same basic structure: author, owner and text
		// if the author is also the owner, we do not need to send a foreign_post message
		if($p['author']['_key'] == $p['owner']['_key'])
		{
			$msg = $this->makePostMessage('post', $p);
			
		}
		else
		{
      $msg = $this->makePostMessage('foreign_post', $p);
			
		}
		return($msg->ds);
	}

}


?>