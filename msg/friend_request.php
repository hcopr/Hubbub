<?

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
  if($msg->responseData['data']['result'] == 'OK')
  {
	  // get our side of the connection
	  $con = new HubbubConnection($msg->fromEntity->key(), $msg->toEntity->key());
	  switch($con->status())
	  {
	    case('req.rcv'): {
	      // if this entity already requested a connection, complete the request
	      $con->status('friend');
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
  
  $con = new HubbubConnection($msg->toEntity->key(), $msg->fromEntity->key());
  switch($con->status())
    {
  	case('req.sent'): {
      // if we already sent a request to them, complete the process
      $con->status('friend');
  		break;
  	}
  	case('undefined'): {
  		$con->status('req.rcv');
  		break;
  	}
  }    

  $msg->ok();
  return(true);
}


?>