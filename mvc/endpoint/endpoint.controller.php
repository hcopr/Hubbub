<?php

class EndpointController extends HubbubController
{
  function __init()
  {
    $GLOBALS['config']['page']['template'] = 'blank';
		$this->skipView = true;
  }
  
  function index()
  {
  	$this->msg = new HubbubMessage();
    $result = $this->msg->receive($_REQUEST['hubbub_msg'], $_REQUEST['hubbub_sig']);
		if($result === false)
      $response = array('result' => 'fail', 'reason' => 'invalid message type', 'op' => $this->msg->type);
		else
      $response = $this->msg->response;
		/*
		audit_log('msg.receive:'.$this->msg->data['type'], 
		  $this->msg->data['from']['server'].':'.$this->msg->data['from']['user'].'>'.$this->msg->data['to']['user'].' '.$this->msg->usedSig, 
			'SENT', $response['result'], $response['reason']);*/
    print(json_encode($response));
  }
  
  function cron()
  {
		$this->skipView = false;

  }
 
}

?>