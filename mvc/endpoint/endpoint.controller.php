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
  
  function abconfirm()
  {
    // the global address book connects here to find out if this
    // server really sent a request modifying ab AB entry
    $result = array();
    $entityUrl = $_REQUEST['entity_url'];
    $checksum = $_REQUEST['checksum'];
    $thisEntity = new HubbubEntity(array('url' => $entityUrl));
    if(sizeof($thisEntity->ds) == 0)
    {
      $result['error'] = 'invalid entity';
    }
    else
    {
      $nvIdentifier = 'abreq/'.$thisEntity->ds['_key'];
      $reqInfo = h2_nv_retrieve($nvIdentifier, 'sys');
      if($reqInfo['checksum'] != $checksum)
      {
        $result['error'] = 'invalid checksum'; 
      }
      else
      {
        // remove the "pending" entry from the NV store
        h2_nv_store($nvIdentifier, null, 'sys');
        // confirm to the address book that we did indeed make this request
        $result['result'] = 'OK';
      }
    }
    print(json_encode($result));
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