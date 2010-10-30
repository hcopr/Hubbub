<?

function post_save(&$data, &$msg)
{
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

?>