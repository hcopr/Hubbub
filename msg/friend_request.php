<?php

/**
 * This handler prepares a message to be sent as a friend_request. The message must already have
 * a "to" entity record for this to work.
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_request_before_sendtourl(&$data, &$msg)
{
	
  return(true);
}

/**
 * This handler processes a message after a friend_request was sent. 
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_request_after_sendtourl(&$data, &$msg)
{
	// if the friend request was received alright on the other side
  if($msg->responseData['result'] == 'OK')
  {
	  // get our side of the connection
	  $con = new HubbubConnection($msg->authorEntity->key(), $msg->ownerEntity->key());
    $usr = new HubbubUser($msg->authorEntity->key());
	  switch($con->status())
	  {
	    case('req.rcv'): {
	      // if this entity already requested a connection, complete the request
	      $con->status('friend');
	      $usr->notify('friend/added', $msg->ownerEntity);
	      break;
	    }
	    case('undefined'): {
	      // if this connection is undefined, register as pending
	      $con->status('req.sent');
	      break;
	    }
	  }
  }
  return(true);
}


/**
 * Receipt of a friend request message
 * @param object $data
 * @param object $msg
 * @return 
 */
function friend_request_receive(&$data, &$msg)
{
	// allow only if the server is trusted
	if(!$msg->validateSignature()) return(true);
  
  $con = new HubbubConnection($msg->ownerEntity->key(), $msg->authorEntity->key());
  $usr = new HubbubUser($msg->ownerEntity->key());
  WriteToFile('log/activity.log', $data['msgid'].' friend_request received'.chr(10));
  h2_audit_log('msg/friend_request/rcv', array('me' => $msg->ownerEntity->key(), 'sender' => $msg->authorEntity->key()), $data['msgid']);
  
  switch($con->status())
  {
  	case('req.sent'): {
      // if we already sent a request to them, complete the process
      $con->status('friend');
      $usr->notify('friend/added', $msg->authorEntity);
  	  $msg->response['comment'] = 'status updated to {friend}'; 
  		break;
  	}
  	case('undefined'): {
  		$con->status('req.rcv');
      $usr->notify('friend/request', $msg->authorEntity);
  	  $msg->response['comment'] = 'status updated to {received}'; 
  		break;
  	}
  	default: {
  	  $msg->response['comment'] = 'status not updated'; 
  	  break;
    }
  }    

  $msg->ok();
  return(true);
}


?>