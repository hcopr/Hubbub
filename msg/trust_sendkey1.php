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
	if($data['from']['server'] == '')
	  $msg->fail('invalid server field in "from" array');
	else
	{
		// accept the new key (it's not confirmed yet)
		$server = new HubbubServer($data['from']['server'], true);
		$server->ds['s_newkey_out'] = $data['mykey'];
		$server->ds['s_key_in'] = getDefault($server->ds['s_key_in'], md5(time().rand(1, 100000)));
    DB_UpdateField('servers', $server->ds['s_key'], 's_key_in', $server->ds['s_key_in']);
		// now, get origin confirmation
		$confirmMsg = new HubbubMessage('trust_sendkey2');
		$confirmMsg->from($server->localEntity());
		$confirmMsg->to($server->entity());
		$confirmMsg->data['mykey'] = $server->ds['s_key_in'];
		$response = $confirmMsg->sendtourl($server->ds['s_url'], $server->ds['s_newkey_out']);
		$responseData = $response['data'];
		if($responseData['result'] == 'OK')
		{
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