<?

function foreign_post_save(&$data, &$msg)
{
  //$msg->vTag = 'A'; // queue for approval 
  //$msg->doSave = false; 
}

function foreign_post_receive(&$data, &$msg)
{
  if(!$msg->validateSignature()) return(true);
  
  $con = new HubbubConnection($msg->ownerEntity->key(), $msg->authorEntity->key());
  if($con->status() != 'friend') return($msg->fail('no connection'));
  
  if($con->ds['c_auto_approve'] == 'Y')
  {
    // if we're gonna approve this anyway, there is no reason to store the message
    // let's just create a post out of this
    WriteToFile('log/activity.log', $data['msgid'].' foreign_post received, accepted'.chr(10));
    $post = new HubbubMessage('post');
    $npid = $post->data['msgid'];
    $post->data = $msg->data;
    $post->data['type'] = 'post';
    $post->data['msgid'] = $npid;
    $post->author($msg->authorEntity->ds);
    $post->owner($msg->ownerEntity->ds);
    $post->data['changed'] = time();
    $post->data['received'] = time();
    $post->save();
    WriteToFile('log/activity.log', $post->data['msgid'].' created from foreign_post'.chr(10));
    $msg->response['post'] = $post->data;
    $msg->doSave = false;
  }
  else
  {
    // if not, let's store this message for later approval
    $msg->vTag = 'A';
    $msg->save();  
  }
  
  $msg->ok();
}

?>