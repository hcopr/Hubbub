<?

  $u1name = 'HTT_'.substr(md5(time().rand(1, 100000)), 0, 5);
  $u2name = 'HTT_'.substr(md5(time().rand(1, 100000)), 0, 5);

  tsection('Entities');
  
  $this->friends->contactServer($_SERVER['HTTP_HOST']);
  
  $erec1 = array('server' => $_SERVER['HTTP_HOST'], 'user' => $u1name, 'url' => $_SERVER['HTTP_HOST'].'/'.$u1name);
  $ne1 = new HubbubEntity();
  $ne1->create($erec1, true);
  $erec2 = array('server' => $_SERVER['HTTP_HOST'], 'user' => $u2name, 'url' => $_SERVER['HTTP_HOST'].'/'.$u2name);
  $ne2 = new HubbubEntity();
  $ne2->create($erec2, true);
  $ne1key = $ne1->ds['_key'];
  $ne2key = $ne2->ds['_key'];
  tlog($ne1->ds['_key'] > 0, 'HubbubEntity::create('.$u1name.')', 'OK', 'failed, no key assigned'); 
  tlog($ne2->ds['_key'] > 0, 'HubbubEntity::create('.$u2name.')', 'OK', 'failed, no key assigned'); 

  $u1 = array('u_name' => $u1name, 'u_entity' => $ne1->ds['_key']);
  DB_UpdateDataset('users', $u1);
  $u2 = array('u_name' => $u2name, 'u_entity' => $ne2->ds['_key']);
  DB_UpdateDataset('users', $u2);
  tlog($u1['u_key'] > 0, 'HubbubUser::create('.$u1['u_key'].')', 'OK', 'failed, no key assigned'); 
  tlog($u2['u_key'] > 0, 'HubbubUser::create('.$u2['u_key'].')', 'OK', 'failed, no key assigned'); 

  tsection('Message Basic');

  $p = new HubbubMessage('friend_request');  
  tlog($p->data['msgid'] != '', 'HubbubMessage::create(friend_request '.$p->data['msgid'].')', 'OK', 'msgid failure');
  $p->author($ne1->ds);
  $p->owner($ne2->ds);
  $p->sanitizeDataset();
	// to se if sanitizeDataset() corrupts entries
  $p->sanitizeDataset();
  tlog($p->authorEntity->ds['_key'] > 0, 'HubbubMessage->authorEntity key', 'OK ('.$p->authorEntity->ds['_key'].')', 'no key assigned');
  tlog($p->authorEntity->ds['user'] == $p->data['author']['user'], 'HubbubMessage->authorEntity username', 'OK', 'not assigned');
  tlog($p->ownerEntity->ds['_key'] > 0, 'HubbubMessage->ownerEntity', 'OK ('.$p->ownerEntity->ds['_key'].')', 'no key assigned');
  tlog($p->ownerEntity->ds['user'] == $p->data['owner']['user'], 'HubbubMessage->ownerEntity username', 'OK', 'not assigned');
  tlog($p->data['owner']['server'] != '', 'HubbubMessage->ownerEntity server', 'OK', 'not assigned');
  
	$toServer = new HubbubServer($p->ownerEntity->ds['server']);
  tlog($toServer->ds['s_key'] > 0, 'HubbubServer::new('.$p->ownerEntity->ds['server'].')', 'OK ('.$toServer->ds['s_key'].')', 'not loaded');
  tlog($toServer->outboundKey() != '', 'HubbubServer->outboundKey()', 'OK ('.$toServer->outboundKey().')', 'not found');
  tlog($toServer->inboundKey() != '', 'HubbubServer->inboundKey()', 'OK ('.$toServer->inboundKey().')', 'not found');
  $p->signForServer($toServer);
  tlog($p->signature == md5($toServer->outboundKey().$p->payload), 'HubbubMessage::signForServer('.$p->ownerEntity->ds['server'].')', 'OK ('.$p->signature.')', 'invalid signature');
  tlog($p->validateSignature(), 'HubbubMessage->validateSignature(valid)', 'OK', 'failure');
	$p->signature = md5($p->signature);
	$GLOBALS['nolog'] = true; // disable logging, because we'll trigger an error here
	tlog(!$p->validateSignature(), 'HubbubMessage->validateSignature(invalid)', 'OK', 'failure');
  $GLOBALS['nolog'] = false;
  $p->signForServer($toServer);
  $r = new HubbubMessage();
	$r->receive($p->payload, $p->signature);
  tlog($r->validateSignature(), 'HubbubMessage->receive() signature', 'OK', 'failure ('.$p->expectedSignature.')');

  tsection('Friends');
  
	$url = $ne2->ds['url'];
  require_once('lib/hubbub2_loadurl.php');
  $er = hubbub2_loadurl($url);
  tlog(!(sizeof($er) == 0 || $er['user'] == '' || $er['server'] == ''), 'hubbub2_loadurl('.$url.')', 'OK ('.$er['user'].'@'.$er['server'].')', 'failure');
  $res = $p->sendToUrl($er['server']);  
  tlog($res['result'] == 'OK', 'HubbubMessage->sendToUrl('.$url.') Message '.$p->data['msgid'], 'OK ('.json_encode($res).')', 'fail ('.$res['reason'].')');
  $con1 = new HubbubConnection($p->authorEntity->key(), $p->ownerEntity->key());
  $con2 = new HubbubConnection($p->ownerEntity->key(), $p->authorEntity->key());
  tlog($con1->ds['c_status'] == 'req.sent', 'HubbubConnection status '.$u1name.':'.$con1->ds['c_from'].' to '.$u2name.':'.$con1->ds['c_to'].' == req.sent', 'OK', 'fail ('.$con1->ds['c_status'].')');
  tlog($con2->ds['c_status'] == 'req.rcv', 'HubbubConnection status '.$u2name.':'.$con2->ds['c_from'].' to '.$u1name.':'.$con2->ds['c_to'].' == req.rcv', 'OK', 'fail ('.json_encode($con2->ds).')');
	
	// if everything went right, there should be a friend request waiting for user u1
	$frRcvList = $this->friends->getFriends('req.rcv', $u2['u_entity']);
	foreach($frRcvList as $frc) if($frc['c_to'] == $ne1->ds['_key']) $frqReceived = true;
  tlog($frqReceived, 'FriendsModel: friend_request in "pending" list', 'OK', 'fail ('.dumpArray($frRcvList).')');		
	
	// now, confirm it
  $fp = new HubbubMessage('friend_request');  
  $fp->author($ne2->ds);
  $fp->owner($r->authorEntity->ds);
	$res = $fp->sendToUrl($r->authorEntity->ds['server']);
  tlog($res['result'] == 'OK', 'friend_request confirm ('.$url.') Result', 'OK ('.$res['reason'].')', 'fail ('.$res['reason'].')');
  $con1 = new HubbubConnection($fp->authorEntity->key(), $fp->ownerEntity->key());
  $con2 = new HubbubConnection($fp->ownerEntity->key(), $fp->authorEntity->key());
  tlog($con1->ds['c_status'] == 'friend', 'HubbubConnection status '.$u2name.':'.$con1->ds['c_from'].' to '.$u1name.' == friend', 'OK', 'fail ('.$con1->ds['c_status'].')');
  tlog($con2->ds['c_status'] == 'friend', 'HubbubConnection status '.$u1name.':'.$con2->ds['c_from'].' to '.$u2name.' == friend', 'OK', 'fail ('.$con2->ds['c_status'].')');	
	
  tsection('Self Posts');

  $post = new HubbubMessage('post');
	$post->localUserEntity = $ne1->key();
  $post->author($ne1->ds);
  $post->owner($ne1->ds);
  $post->data['text'] = 'This is a test message. Umlauts like üöä should be preserved.';
  $post->save();
  tlog($post->ds['m_key'] > 0, 'HubbubMessage post in DB', 'OK (#'.$post->ds['m_key'].')', 'fail');
  tlog($post->ds['m_publish'] == 'Y', 'HubbubMessage post m_publish==Y', 'OK', 'fail (localUserEntity='.$post->localUserEntity.')');
  
  // now, this post should be on $ne1's wall
	$wallPosts1 = $this->msg->getPostList($ne1->key());
	foreach($wallPosts1['list'] as $pds)
	  if($pds['m_key'] == $post->ds['m_key']) $postFound1 = true; 
	tlog($postFound1, 'msg->getPostList(for '.$u1name.':e'.$ne1->key().') post found', 'OK', 'fail '.dumpArray($wallPosts1));
	
	// the post should also appear in $ne2's stream
	$streamPosts1 = $this->msg->getStream($ne2->key());
  foreach($streamPosts1['list'] as $pds)
    if($pds['m_key'] == $post->ds['m_key']) $postFound2 = true; 
  tlog($postFound2, 'getStream->getStream(for '.$u2name.':u'.$u2['u_key'].'-e'.$ne2->key().') post found', 'OK', 'fail '.dumpArray($streamPosts1));
  
  // feed polling, let's pretend ne2 is polling ne1
	$fpServ = new HubbubServer($ne1->ds['server']);
  $feed = $this->endpoint->pollFeed($fpServ, time()-1*60);
  tlog($feed['result'] == 'OK', 'Endpoint pollFeed('.$ne1->ds['server'].') Result Code', 'OK', 'fail ('.$feed['reason'].')');
  foreach($feed['feed'] as $item)
    if($item['msgid'] ==   $post->ds['m_id']) $postFound3 = $item;
  tlog(isset($postFound3), 'Posted item found in remote feed', 'OK', 'fail');  
  tlog($postFound3['text'] == $post->data['text'], 'Post content unicode test', 'OK', 'fail');  
  
  tsection('Foreign Posts');

  // if I'm only the author, I send this content in the form of a foreign_post to the owner (who will hopefully accept it into their stream)
  $fpost = new HubbubMessage('foreign_post');
	$fpost->localUserEntity = $ne1->key();
  $fpost->author($ne1->ds);
  $fpost->owner($ne2->ds);
  $fpost->data['text'] = 'This is a text post on someone else\'s profile. Umlauts like üöä should be preserved.';
  $fpost->save();

  $res = $fpost->sendToUrl($fpost->ownerEntity->ds['server']);
  // see if the message was accepted on the "other" end
  tlog($res['result'] == 'OK', 'foreign_post sentToUrl('.$fpost->ownerEntity->ds['url'].')', 'OK ('.$fpost->data['msgid'].')', 'fail ('.$fpost->responseData['reason'].')');
  // this tests whether the message was instantly published (a post record was returned)
  tlog(sizeof($fpost->responseData['post']) > 0, 'foreign_post auto_publish on receive', 'OK ('.$fpost->responseData['post']['msgid'].')', 'fail');
  // is the message text uncorrupted?
  tlog($fpost->responseData['post']['text'] == $fpost->data['text'], 'foreign_post text unicode', 'OK', 'fail');
  tlog($fpost->responseData['post']['msgid'] != $fpost->data['msgid'], 'foreign_post-to-post ID change', 'OK', 'fail');
  // now, this activity should appear on $ne2's profile stream
	$wallPosts1 = $this->msg->getPostList($ne2->key());
	foreach($wallPosts1['list'] as $pds)
	  if($pds['m_id'] == $fpost->responseData['post']['msgid']) $postFound4 = true; 
	tlog($postFound4, 'post found on '.$u2name.'\'s profile', 'OK', 'fail '.dumpArray($wallPosts1));

  tsection('Realtime Updates');

  // if I am the owner of the message, I send it out to my closest friends as a realtime notification!
  $post = new HubbubMessage('post');
	$post->localUserEntity = $ne1->key();
  $post->author($ne2->ds);
  $post->owner($ne1->ds);
  $post->data['text'] = 'This is a realtime message. Umlauts like üöä should be preserved.';
  // if we're the owner, we can send a realtime update to ne2
  $fpost->save();

  // now, we'll update an existing message that has already been committed to DB
  $post->data['text'] = 'This message text has changed now';
  $post->markChanged(time()+$ctr++);
  $post->sendToUrl($ne1->ds['server']);
  tlog($post->responseData['result'] == 'OK', 'updated post sentToUrl('.$post->authorEntity->ds['url'].')', 'OK', 'fail ('.$post->responseData['reason'].')');
	$wallPosts1 = $this->msg->getPostList($ne1->key());
	foreach($wallPosts1['list'] as $pds)
	  if($pds['m_id'] == $post->data['msgid']) 
	  {
	    $newData = json_decode(gzinflate($pds['m_data']), true);
	    $postValid1 = $newData['text'] ==  $post->data['text'];
    } 
  tlog($postValid1, 'updated post content valid', 'OK', 'fail (new: "'.$newData['text'].'")');
  
  // let's try and delete a post
  $post = new HubbubMessage('post');
	$post->localUserEntity = $ne1->key();
  $post->author($ne2->ds);
  $post->owner($ne1->ds);
  $post->data['text'] = 'This is a message, it will be deleted. Umlauts like üöä should be preserved.';
  $post->save();

  tlog($post->ds['m_key'] > 0, 'post saved locally', 'OK (#'.$post->ds['m_id'].')', 'fail');  

	$streamPosts1 = $this->msg->getStream($ne2->key());
  foreach($streamPosts1['list'] as $pds)
    if($pds['m_id'] == $post->data['msgid']) $matchDS7 = $pds;
  tlog(is_array($matchDS7), 'post in stream', 'OK (#'.$post->ds['m_key'].')', 'fail');  
  $textData = HubbubMessage::unpackData($matchDS);
  tlog($matchDS7['text'] == $textData['text'], 'post in stream is valid', 'OK (#'.$post->ds['m_key'].')', 'fail');  

  $post->markChanged(time()+$ctr++);
  $post->executeHandler('delete');
  
  tlog($post->data['deleted'] == 'yes', '"deleted" property set', 'OK', 'fail');  
  $mds = DB_GetDataset('messages', $post->ds['m_key']);
  $textData = HubbubMessage::unpackData($mds);
  tlog($mds['m_deleted'] == 'Y', 'm_deleted in DB', 'OK (#'.$mds['m_key'].')', 'fail');  
  tlog($textData['deleted'] == 'yes', 'deleted in record', 'OK (#'.$mds['m_key'].')', 'fail');  
  
	$streamPosts1 = $this->msg->getStream($ne2->key());
	$wallPosts1 = $this->msg->getPostList($ne1->key());
  $postFound9 = -1; $postFound10 = -1;
  foreach($streamPosts1['list'] as $pds)
    if($pds['m_id'] == $post->data['msgid']) $postFound9 = $pds['m_id']; 
  tlog($postFound9 == -1, 'Message gone from author stream', 'OK', 'fail (#'.$postFound9.')');
	foreach($wallPosts1['list'] as $pds)
	  if($pds['m_id'] == $post->data['msgid']) $postFound10 = $pds['m_id']; 
	tlog($postFound10 == -1, 'Message gone from owner profile', 'OK', 'fail ('.$postFound10.')');  

  $post = new HubbubMessage('post');
  $post->localUserEntity = $ne2->key();
  $post->author($ne2->ds);
  $post->owner($ne1->ds);
  $post->data['text'] = 'This is a message, it will be deleted. Umlauts like üöä should be preserved.';
  $post->save();

  $post->executeHandler('delete');
  tlog($post->data['deleted'] == 'yes', 'Author: "deleted" property set', 'OK', 'fail');  
  tlog($post->isDeleted, '"deleted" internal property set', 'OK', 'fail');  
	$streamPosts1 = $this->msg->getStream($ne2->key());
	$wallPosts1 = $this->msg->getPostList($ne1->key());
  $postFound9 = -1; $postFound10 = -1;
  foreach($streamPosts1['list'] as $pds)
    if($pds['m_id'] == $post->data['msgid']) $postFound9 = $pds['m_id']; 
  tlog($postFound9 == -1, 'Message gone from author stream', 'OK', 'fail (#'.$postFound9.')');
	foreach($wallPosts1['list'] as $pds)
	  if($pds['m_id'] == $post->data['msgid']) $postFound10 = $pds['m_id']; 
	tlog($postFound10 == -1, 'Message gone from owner profile', 'OK', 'fail ('.$postFound10.')');  
  
  // next, we'll try an invalid update where the author has suddenly changed
  $post->data['text'] = 'This update should not have happened.';
  $post->author($ne1->ds);
  $post->sendToUrl($ne1->ds['server']);
  tlog($post->responseData['result'] != 'OK', 'Rejection policy', 'OK ('.$post->responseData['reason'].')', 'fail');

  tsection_end();
?>















