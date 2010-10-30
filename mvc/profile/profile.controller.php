<?php

class ProfileController extends HubbubController
{
	function __init()
	{
    access_authenticated_only();
		$this->menu = $this->makeMenu('index,user,:'.actionUrl('index', 'settings'));
		$this->invokeModel();
    include_once('templates/postlist.php');
	}
	
	function index()
	{
	}
	
	function user()
	{
	}
	
	function auth()
	{
		
	}
	
	function add_openid()
	{
		
	}
	
  function ajax_deletepost()
  {
    $result = array();
    if($this->model->deletePost($_REQUEST['pid'])) $result['result'] = 'OK'; else $result['result'] = 'fail';
    print(json_encode($result)); 
  }
  
  function ajax_deletecomment()
  {
    $this->ajax_deletepost();
  }
  
	function ajax_thread()
  {
  	$parentMessage = DB_GetDataset('messages', $_REQUEST['pid']);
    tmpl_commentlist($parentMessage, $this->model->getComments($_REQUEST['pid'], 100), false);
  }
	
	function ajax_comment()
	{
    if(trim($_REQUEST['text']) != '' && $_REQUEST['pid'] > 0)
    {
    	$parentMessage = DB_GetDataset('messages', $_REQUEST['pid']);
      $ds = $this->model->postComment(array(
			  'from' => array('_key' => $parentMessage['m_owner']),
				'author' => array('_key' => $this->user->entity),
        'text' => $_REQUEST['text'],
				'parent' => $parentMessage['m_id']));   
			$comments = array('list' => array($ds)); 	
			if(substr($_REQUEST['text'], 0, 1) != '#') tmpl_commentlist($parentMessage, $comments, false);
    }
	}

  function ajax_post()
	{
		if(trim($_REQUEST['text']) != '')
		{
			$ds = $this->model->postToProfile(array(
	      'text' => $_REQUEST['text'],
	      )); 
			tmpl_postlist(array($ds), false);
		}
	}
	
	function ajax_vote()
	{
    if(substr($_REQUEST['text'], 0, 1) != '#') $_REQUEST['text'] = '#'.$_REQUEST['text'];
		$this->ajax_comment();
	}
	
}

?>