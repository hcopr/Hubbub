<?

function feed_poll_receive(&$data, &$msg)
{
  if(!$msg->validateSignature()) return(true);
  
  $forServer = new HubbubServer($msg->authorEntity->ds['server']);
  
  $msg->response['_listing_from'] = gmdate('Y-m-d H:i:s', $msg->data['last']);
  $msg->response['_for_server'] = $msg->authorEntity->ds['server'].':'.$forServer->ds['s_key'];

  // let's get a list of items 
  $msg->response['feed'] = array();
  
  foreach(DB_GetList('SELECT * FROM '.getTableName('messages').
    ' LEFT JOIN '.getTableName('index_servers').' ON (si_serverkey = '.$forServer->ds['s_key'].' AND si_msgkey = m_key) '.
    ' WHERE si_serverkey > 0 AND m_publish="Y" AND m_changed >= "'.gmdate('Y-m-d H:i:s', $msg->data['last']).'" '.
    ' LIMIT '.cfg('service.maxfeedsize', 200)) as $msgDS)
  {
    $resultCount++;
    $msg->response['feed'][] = json_decode(gzinflate($msgDS['m_data']), true);
  } 
  
  $msg->response['_count'] = $resultCount;
  
  $msg->ok();
}

?>