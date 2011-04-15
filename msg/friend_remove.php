<?php

/**
 * This handler prepares a message to be sent as a friend_request. The message must already have
 * a "to" entity record for this to work.
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_remove_before_sendtourl(&$data, &$msg)
{
  
  return(true);
}

/**
 * This handler processes a message after a friend_request was sent. 
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_remove_after_sendtourl(&$data, &$msg)
{
  $con = new HubbubConnection($msg->authorEntity->key(), $msg->ownerEntity->key());
  $con->status('undefined');
  return(true);
}


/**
 * Receipt of a friend request message
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_remove_receive(&$data, &$msg)
{
  // allow only if the server is trusted
  if(!$msg->validateSignature()) return(true);
  
  $con = new HubbubConnection($msg->ownerEntity->key(), $msg->authorEntity->key());
  if($con->status() != 'friend') return($msg->fail('no connection'));
	$con->status('undefined');
  $msg->ok();
  return(true);
}


?>