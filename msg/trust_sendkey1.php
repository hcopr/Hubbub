<?
/**
 * On-receive event handler
 * 
 * Gets called when this server (A) receives a trust_sendkey1 message from another server (B).
 * Usually this means, server (B) wants to establish contact for the first time or it wants
 * to revoke its existing key. In both cases, the key can only be accepted when this server (A)
 * contacts server (B) to confirm the origin of the trust_sendkey1 message.
 * 
 * @param array $data
 * @param object $msg
 * @return boolean
 */
function trust_sendkey1_receive(&$data, &$msg)
{
  $data['mykey'] = trim($data['mykey']);
  $serverUrl = getDefault($data['author']['server']);
  if($serverUrl == '')
	  $msg->fail('invalid server field in "author" array');
  if($data['mykey'] == '')
	  $msg->fail('"mykey" field missing');
	else
	{
		// accept the new key (it's not confirmed yet)
		$server = new HubbubServer($serverUrl, true);
		$server->ds['s_newkey_out'] = $data['mykey'];
		$server->ds['s_key_in'] = getDefault($server->ds['s_key_in'], randomHashId());
    DB_UpdateField('servers', $server->ds['s_key'], 's_key_in', $server->ds['s_key_in']);
    logError('notrace', 'received temp outbound key: '.$data['mykey'].' /// '.dumpArray($server->ds));
		// now, get origin confirmation
		$confirmMsg = new HubbubMessage('trust_sendkey2');
		$confirmMsg->author($server->localEntity());
		$confirmMsg->owner($server->entity());
		$confirmMsg->data['mykey'] = $server->ds['s_key_in'];
		$responseData = $confirmMsg->sendtourl($server->ds['s_url'], $server->ds['s_newkey_out']);
		if($responseData['result'] == 'OK')
		{
		  /* we need to reload, because the server record might have changed in the meantime */
		  $server = new HubbubServer($serverUrl, true);
			// okay, the remote server really sent the original message
			$server->ds['s_key_out'] = $server->ds['s_newkey_out'];
			$server->ds['s_status'] = 'OK';
			DB_UpdateField('servers', $server->ds['s_key'], 's_key_out', $data['mykey']);
			$msg->ok();
		}
		else
		{
			// this didn't work
      $server->ds['s_newkey_out'] = '';
			$msg->fail('unsuccessful trust_sendkey2: '.$responseData['reason']);
		}
	}
	return(true);
}


?>