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
    $GLOBALS['stats']['msgtype'] = $this->msg->type;
		if($result === false)
      $response = array('result' => 'fail', 'reason' => 'invalid message type', 'op' => $this->msg->type);
		else
      $response = $this->msg->response;
		/* h2_audit_log('msg.receive:'.$this->msg->data['type'], 
		  $this->msg->data['from']['server'].':'.$this->msg->data['from']['user'].'>'.$this->msg->data['to']['user'].' '.$this->msg->usedSig, 
			'SENT', $response['result'], $response['reason']);*/
    print(json_encode($response));
    $GLOBALS['stats']['response'] = $response['result'];
  }
  
  function cron()
  {
		$this->skipView = false;
    $this->invokeModel();
  }
  
  function verifyPwd()
  {
    if(md5(cfg('service.ping_password')) == $_REQUEST['p'])
    {
      ?>OK<? 
    }
    else
    {
      ?>fail<? 
    }
  }
 
}

?>