<?php

class FriendsController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
    
		foreach(DB_GetList('SELECT c_status,COUNT(*) as count FROM '.getTableName('connections').'
		  WHERE c_from = ?
			GROUP BY c_status', array($this->user->entity)) as $item)
			$menuCount[$item['c_status']] = $item['count'];
		$countArray = array();
    if($menuCount['friend'] > 0) $countArray[] = ' ('.$menuCount['friend'].')'; else $countArray[] = '';
		$countArray[] = '';
    if($menuCount['req.rcv'] > 0) $countArray[] = ' ('.$menuCount['req.rcv'].')'; else $countArray[] = '';
    if($menuCount['req.sent'] > 0) $countArray[] = ' ('.$menuCount['req.sent'].')'; else $countArray[] = '';
    
    $this->menu = $this->makeMenu('index,add,ab,rcv', $countArray);
		$this->invokeModel();
    $this->myEntity = $this->user->selfEntity();
    $this->integrate('ab');
	}
	
	function index()
	{
	  $this->myFriends = $this->model->getFriends('friend');
	  if(sizeof($this->myFriends) == 0)
	  {
	    $this->redirect('add'); 
    }
	}
	
	function add()
	{
	  include('lib/cq-forms.php');	
	}
	
	function rcv()
	{
	  
  }
  
	function ajax_pingserver()
	{
	  $server = new HubbubServer($_REQUEST['server'], true);
	  $reload = '<script>
	      setTimeout("reload_'.md5($_REQUEST['server']).'()", 2000);
	    </script>';
	  if($server->isTrusted())
	  {
	    ?><span class="green">
        <?= htmlspecialchars($_REQUEST['url']) ?>
      </span><? 
    } 
    else if($server->isWaitingForKey())
    {
      ?><?= htmlspecialchars($_REQUEST['url']) ?><div class="gray">
        waiting for response...
      </div><?= $reload ?><?
    }
    else
    {
      $res = $server->msg_trust_sendkey1();
      if($res['result'] == 'OK')
      {
        ?><?= htmlspecialchars($_REQUEST['url']) ?><div class="gray">
          exchanging credentials...
        </div><?= $reload ?><?
      }
      else
      {
        ?><div class="red">
          ✘ <a class="red" title="<?= htmlspecialchars(getDefault($res['reason'], 'no connection')) ?>"><?= htmlspecialchars($_REQUEST['url']) ?></a>
        </div><?
      }
    }
  }
	
	function ajax_search()
	{
	  $this->skipView = false;
  }
  
  function ajax_remove()
  {
    $this->model->friend_remove($_REQUEST['key']);
  }
  
  function ajax_friend_request()
  {
	  $this->skipView = false;
  }
	
	function ajax_accept()
	{
    $res = $this->model->friend_accept($_REQUEST['key'], $_REQUEST['group']);
    if($res['result'] == 'OK')
      print('<span class="win">'.l10n('accepted').'</span>');
    else
      print('<span class="fail">'.l10n('accept_error').'</span>');
  }
  
  function ajax_ignore()
  {
    $this->model->friend_ignore($_REQUEST['key']);
    print('<span class="gray">'.l10n('ignored').'</span>');
  }
	
}

?>