<?

class EndpointModel extends HubbubModel
{
  function pollFeed($serverObj, $time)
  {
    $pollMsg = new HubbubMessage('feed_poll');
    $pollMsg->author(HubbubServer::localEntity());
    $pollMsg->data['last'] = $time;
    $pollMsg->sendToUrl($serverObj->ds['s_url']);
    return($pollMsg->responseData);
  }
  
  
  
  
}

?>