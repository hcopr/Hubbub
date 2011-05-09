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
    access_policy('post');
  	$this->msg = new HubbubMessage();
    WriteToFile('log/activity.log', 'endpoint.receive '.$_REQUEST['hubbub_sig'].chr(10));
    $result = $this->msg->receive($_REQUEST['hubbub_msg'], $_REQUEST['hubbub_sig']);
    $GLOBALS['stats']['msgtype'] = $this->msg->type;
		if($result === false)
      $response = array('result' => 'fail', 'reason' => 'invalid message type', 'op' => $this->msg->type);
		else
      $response = $this->msg->response;
    print(json_encode($response));
    $GLOBALS['stats']['response'] = $response['result'];
    WriteToFile('log/activity.log', 'endpoint.response '.implode(', ', $response).chr(10));
  }
  
  function cron()
  {
    access_policy('cron');
		$this->skipView = false;
    $this->invokeModel();
  }
  
  function verifyPwd()
  {
    if(md5(cfg('ping/password')) == $_REQUEST['p'])
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