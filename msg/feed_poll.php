<?

function feed_poll_receive(&$data, &$msg)
{
  if(!$msg->validateSignature()) return(true);
  
  $forServer = new HubbubServer($msg->authorEntity->ds['server']);
  $msg->response['listing_time'] = time();
  $msg->response['_listing_from_date'] = gmdate('Y-m-d H:i:s', $msg->data['last']);
  $msg->response['_listing_to_date'] = gmdate('Y-m-d H:i:s');
  $msg->response['_for_server'] = $msg->authorEntity->ds['server'].':'.$forServer->ds['s_key'];
  $serverKey = $forServer->ds['s_key'];
  // let's get a list of items 
  $msg->response['feed'] = array();
  
  foreach(DB_GetList('SELECT * FROM '.getTableName('messages').
    ' LEFT JOIN '.getTableName('connections').' ON (c_toserverkey = '.$serverKey.' AND (c_from = m_owner OR c_from = m_author)) '.
    ' WHERE c_toserverkey > 0 AND m_publish="Y" AND m_changed >= "'.($msg->data['last']+0).'" '.
    ' LIMIT '.cfg('service.maxfeedsize', 200)) as $msgDS)
  {
    $resultCount++;
    $msg->response['feed'][] = json_decode(gzinflate($msgDS['m_data']), true);
  } 
  
  $msg->response['_count'] = $resultCount;
  
  $msg->ok();
}

?>