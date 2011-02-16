<?

  function quickConnect($e0, $e1)
  {
    $con0to1 = new HubbubConnection($e0, $e1);
    $con0to1->status('friend');  
    $con1to0 = new HubbubConnection($e1, $e0);
    $con1to0->status('friend');  
  }

  function getFriends($eid)
  {
    $result = array();
    foreach(DB_GetList('SELECT * FROM '.getTableName('connections').' WHERE c_from = ?', array($eid)) as $cds)
    {
      $result[] = $cds['c_to'];
    }
    return($result);
  }
  
  function hasMessageInStream($ctx, $eid, $mkey)
  {
  	$streamPosts1 = $ctx->msg->getStream($eid);
    foreach($streamPosts1['list'] as $pds)
      if($pds['m_key'] == $mkey) return(true);     
    return(false);
  }

  tsection('Entities');
  $this->friends->contactServer($_SERVER['HTTP_HOST']);

  for($i = 0; $i < 5; $i++)
  {
    $usr = array('name' => 'HTT_'.substr(md5(time().rand(1, 100000)), 0, 5));
    $erec = array('server' => $_SERVER['HTTP_HOST'], 'user' => $usr['name'], 'url' => $_SERVER['HTTP_HOST'].'/'.$usr['name']);
    $usr['erec'] = $erec;
    $usr['entity'] = new HubbubEntity();
    $usr['entity']->create($usr['erec'], true); 
    $usr['id'] = $usr['entity']->ds['_key']; 
    $u[] = $usr;
    $eidx[$usr['id']] = $usr['name'];
  }

  quickConnect($u[0]['id'], $u[1]['id']);
  quickConnect($u[0]['id'], $u[2]['id']);
  quickConnect($u[1]['id'], $u[3]['id']);
  quickConnect($u[4]['id'], $u[3]['id']);

  foreach($u as $usr)
  {
    $isFriend[$usr['id']][$usr['id']] = true;
    $friends[$usr['id']] = getFriends($usr['id']);
    foreach($friends[$usr['id']] as $frid) $isFriend[$usr['id']][$frid] = true;
    $line = $usr['name'].' ('.$usr['id'].') Friends: '.implode(', ', $friends[$usr['id']]);
    tlog(true, 'Connections '.$line, 'OK', 'fail');
  }

  $post = new HubbubMessage('post');
	$post->localUserEntity = $u[0]['id'];
  $post->owner($u[0]['erec']);
  $post->data['text'] = 'This is a realtime message. Umlauts like üöä should be preserved.';
  $post->save();
  
  tlog($post->ds['m_key'] > 0, 'Message created by user ('.$u[0]['id'].')', 'OK (#'.$post->ds['m_key'].')', 'fail');

  // now let's see who has the message in their stream
  foreach($u as $usr)
  {
    if($isFriend[$u[0]['id']][$usr['id']])
      tlog(hasMessageInStream($this, $usr['id'], $post->ds['m_key']), 
        'User '.$usr['id'].' has message '.$post->ds['m_key'].' in stream', 'OK', 'fail'); 
    else
      tlog(!hasMessageInStream($this, $usr['id'], $post->ds['m_key']), 
        'User '.$usr['id'].' has message '.$post->ds['m_key'].' NOT in stream', 'OK', 'fail'); 
  }
  
  tsection_end();
?>
<div style="height: 200px"></div>
<pre><?

#print_r($this->msg->getStream($u[4]['id']));

?></pre>















