<?

/* event handler to notify closest connections when a message changes */
function post_notify(&$data, &$msg)
{
  if($msg->ownerEntity == $msg->localUserEntity)
  {
    // if the sending user is the owner, we can send an update straight out to all connections
    WriteToFile('log/activity.log', $data['msgid'].' sending out notifications'.chr(10));
  }
  else if($msg->authorEntity == $msg->localUserEntity)
  {
    // if we're just the author though, all we can do is send a foreign_post request
    $msg->type = 'foreign_post';
    $msg->data['type'] = 'foreign_post';
    WriteToFile('log/activity.log', $data['msgid'].' changed type to foreign_post'.chr(10));
    $msg->notify();
  }
}

/* event handler before a message is saved to local DB */
function post_save(&$data, &$msg)
{
  WriteToFile('log/activity.log', $data['msgid'].' save'.chr(10));
  if($msg->ownerEntity->ds['_local'] == 'Y')
	{
    WriteToFile('log/activity.log', $data['msgid'].' declared public'.chr(10));
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

/* event handler for receiving a message per direct request */
function post_receive(&$data, &$msg)
{
  // this message must be signed by the owner, who originated it
  if(!$msg->validateSignature('owner')) return(true);
  post_receive_single($data, $msg);
}

/* event handler to process a single message that is part of a stream (already authenticated) */
function post_receive_single(&$data, &$msg)
{
  // we're receiving this message because the sender(=owner) has published something on their profile
  WriteToFile('log/activity.log', $data['msgid'].':'.$msg->authorEntity.':'.$msg->ownerEntity.' received'.chr(10));
  $msg->save();  
  $msg->ok();
}

/* event handler that deletes a message */
function post_delete(&$data, &$msg)
{
  // in order to delete this message, we need to be either the owner or the author of it
  WriteToFile('log/activity.log', $data['msgid'].' deletion'.chr(10));
  if($msg->localUserEntity == $msg->ownerKey || $msg->localUserEntity == $msg->authorKey) 
  {
    // easiest case, because the message lives on this server
    unset($msg->data['text']);
    unset($msg->data['attachments']); 
    $msg->isDeleted = true;
    $msg->data['deleted'] = 'yes';
    WriteToFile('log/activity.log', $data['msgid'].' local message, deleted'.chr(10));
  }
  if($msg->localUserEntity == $msg->ownerKey) 
  {
    // if we're the owner, there is nothing left to do here    
    WriteToFile('log/activity.log', $data['msgid'].' deleted by owner'.chr(10));
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
      WriteToFile('log/activity.log', $data['msgid'].' deleted remotely'.chr(10));
      $msg->ok();
      $msg->save();
      return(true);
    }
    else
    {
      // remote delete didn't work out
      WriteToFile('log/activity.log', $data['msgid'].' remote delete failed: '.$msg->response['data']['reason'].chr(10));
      $msg->fail('remote delete failed: '.$msg->response['data']['reason']);
      return(false);
    }
  }
  $msg->fail('delete failed: insufficient rights');
  return(false);  
}


?>