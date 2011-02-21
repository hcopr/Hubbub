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
	
	function ajax_search()
	{
	  $this->skipView = false;
  }
  
  function ajax_friend_request()
  {
	  $this->skipView = false;
  }
	
}

?>