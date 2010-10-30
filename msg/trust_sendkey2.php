<?
/**
 * On-receive event handler
 * 
 * This handler gets called when server (B) receives a trust_sendkey2 message from server (A),
 * and it generally happens to determine whether server (A) did send a corresponding trust_sendkey1
 * before. It is also used to send server (B)'s key.
 * 
 * @param array $data
 * @param object $msg
 * @return boolean
 */
function trust_sendkey2_receive(&$data, &$msg)
{
  if($data['from']['server'] == '')
    $msg->fail('invalid server field in "from" array');
  else
  {
  	// does it really originate from server (A)?
  	if(!$msg->validateSignature()) return(true);
		// accept this server (A)'s key for future data
  	$msg->fromServer->ds['s_key_out'] = $msg->data['mykey'];
		DB_UpdateField('servers', $msg->fromServer->ds['s_key'], 's_key_out', $msg->data['mykey']);
		$msg->ok();
  }
  return(true);
}


?>