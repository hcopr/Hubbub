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
    $this->menu = $this->makeMenu('index,add,rcv,pending', $countArray);
		$this->invokeModel();
	}
	
	function index()
	{
	}
	
	function add()
	{
	  include('lib/cq-forms.php');	
	}
	
	function rcv()
	{
		
	}
	
	function pending()
	{
	}
	
	function ajax_ignore()
  {
    access_policy('write');
    $this->skipView = false;
    $this->model->ignore(new HubbubConnection($this->user->entity, $_REQUEST['key']));
  }
	
  function ajax_accept()
  {
    access_policy('write');
    $this->skipView = false;
    $this->result = $this->model->accept(new HubbubConnection($this->user->entity, $_REQUEST['key']));
  }
  
  function ajax_remove()
  {
    access_policy('write');
    $this->result = $this->model->remove(new HubbubConnection($this->user->entity, $_REQUEST['key']));
  }
  
	function ajax_addbyurl()
	{
		$this->skipView = false;
		$this->step = getDefault($_REQUEST['step'], 1);
		switch($this->step)
		{
			case(1): {
				print('Server status of '.$_REQUEST['server'].': ');
				$result = $this->model->contactServer($_REQUEST['server']);
				if($result['result'] != 'OK')
				{
					print('connection error ('.htmlspecialchars($result['reason']).')<br/>');
				}
				else
				{
					print('<b style="win">OK</b><br/>');
					$toEntity = new HubbubEntity(array('user' => $_REQUEST['user'], 'server' => $_REQUEST['server']));
          $res = $this->model->friend_request($toEntity);
					if($res['data']['result'] != 'OK')
					{
						?><div class="fail"><?= $this->l10n('friend.req.error').' ('.$res['data']['reason'].')' ?></div><?
					}
					else
					{
						?><div class="win"><?= $this->l10n('friend.req.ok').' '.$toEntity->ds['server'].' :: '.$toEntity->ds['user'] ?></div><?
					}
				}
				break;
			}
		}	
	}
	
}

?>