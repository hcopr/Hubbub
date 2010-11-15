<?

function post_save(&$data, &$msg)
{
  if($msg->ownerEntity->ds['_local'] == 'Y')
	{
		$msg->doPublish = 'Y';
	}
	// if this comment is a vote, we need to update the vote hash in order for it to display correctly
	$msg->voteHash = '';
	if(substr($data['text'], 0, 1) == '#' && $msg->parentKey > 0) 
	{
		$msg->voteHash = md5(strtolower(substr($data['text'], 1)));
	  // but if this person already voted, we need to unregister these previous votes
		DB_Update('UPDATE '.getTableName('messages').' SET m_votehash = "", m_deleted = "Y" WHERE m_parent = ? AND m_author = ?', 
		  array($msg->parentKey, $msg->authorEntity->key()));	
		DB_Update('DELETE FROM '.getTableName('votes').' WHERE v_msg = ?', array($msg->parentKey));
		// update the vote summary
		foreach(DB_GetList('SELECT COUNT(*) as votecount,m_data,m_id,m_votehash as count FROM '.getTableName('messages').'
      WHERE m_parent = ? AND m_deleted = "N" AND m_type="post" AND m_votehash != ""
      GROUP BY m_votehash') as $vds)
		{
			$msgData = HubbubMessage::unpackData($vds);
			// get the some exemplary votes for this
			$voterList = array();
			foreach(DB_GetList('SELECT m_author FROM '.getTableName('messages').' WHERE m_parent = ? AND m_votehash = ? ORDER BY m_created DESC LIMIT 3', 
			  array($msg->parentKey, $vds['m_votehash'])) as $vex)
			    $voterList[] = $vex;
			// if this is also what this message votes for, add it to the list
			if($this->$msg->voteHash == $vds['m_votehash']) $voterList[] = getDefault($msg->authorKey, $msg->ownerKey);
			// make the vote summary dataset
			$voteDS = array(
			  'v_msg' => $msg->parentKey,
				'v_choice' => $vds['m_votehash'],
				'v_text' => $msgData['text'],
				'v_voters' => implode(',', $voterList),
				'v_count' => $vds['votecount'],
				);
			DB_UpdateDataset('votes', $voteDS);
		}
	}
	return(true);
}

function post_receive(&$data, &$msg)
{
  // this message must be signed by the owner, who originated it
  if(!$msg->validateSignature('owner')) return(true);
  // we're receiving this message because the sender(=owner) has published something on their profile
  
  $msg->save();
  
  $msg->ok();
}

function post_delete(&$data, &$msg)
{
  // in order to delete this message, we need to be either the owner or the author of it
  if($msg->localUserEntity == $msg->ownerKey || $msg->localUserEntity == $msg->authorKey) 
  {
    // easiest case, because the message lives on this server
    unset($msg->data['text']);
    unset($msg->data['attachments']); 
    $msg->isDeleted = true;
    $msg->data['deleted'] = 'yes';
  }
  if($msg->localUserEntity == $msg->ownerKey) 
  {
    // if we're the owner, there is nothing left to do here    
    $msg->save();
    $msg->ok();
    return(true);
  }
  else if($msg->localUserEntity == $msg->authorKey) 
  {
    // if we're not the owner, but the author of it, we need to send this sucker to its owner
    // so they can actually publish the change
    $msg->sendToUrl($msg->data['owner']['server']);
    if($msg->response['result'] == 'OK')
    {
      // remote delete confirmed, everything is OK
      $msg->ok();
      $msg->save();
      return(true);
    }
    else
    {
      // remote delete didn't work out
      $msg->fail('remote delete failed: '.$msg->response['data']['reason']);
      return(false);
    }
  }
  $msg->fail('delete failed: insufficient rights');
  return(false);  
}


?>