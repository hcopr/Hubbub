<?
$errors = array();
$startTime = time();
newFile('log/cron.last.log', json_encode(array(
  'started' => $startTime,
  'from' => $_SERVER['REMOTE_ADDR'],
  )));

$srvInterval = cfg('service/poll_interval', 60*10);

foreach(DB_GetList('SELECT * FROM '.getTableName('servers').'
  WHERE s_lastpolled < '.(time()-$srvInterval)) as $sds) if($sds['s_url'] != cfg('service/server'))
{
  $sds['s_lastpolled'] = time(); 
  WriteToFile('log/cron.message.log', "Polling Server ".$sds['s_key'].' '.$sds['s_url'].chr(10));
  $srvObj = new HubbubServer($sds['s_url']);
  if(sizeof($srvObj->ds) > 0)
  {
    $feedRaw = $this->model->pollFeed($srvObj, $sds['s_lastdata']);
    $feed = $feedRaw['data'];
    $sds['s_lastdata'] = getDefault($feed['listing_time'], time()-60);
    if(is_array($feed['feed'])) foreach($feed['feed'] as $item)
    {
  	  $msg = new HubbubMessage();
      if($msg->receive_single($item)) 
        WriteToFile('log/cron.message.log', 'Message rcv/upd '.$item['msgid'].' '.gmdate('Y-m-d H:i', $item['changed']).chr(10));
    }
  }
  else
  {
    $errors[] = 'Error: server URL mismatch';
    WriteToFile('log/cron.message.log', 'Error: server URL mismatch ('.$sds['s_key'].')');
  }
  DB_UpdateDataset('servers', $sds);
}

newFile('log/cron.last.log', json_encode(array(
  'started' => $startTime,
  'finished' => time(),
  'errors' => $errors, 
  'profile' => $GLOBALS['profiler_log'],
  'from' => $_SERVER['REMOTE_ADDR'],
  )));


?>