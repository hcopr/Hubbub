<?php

class ProfileController extends HubbubController
{
	function __init()
	{
    access_authenticated_only();
		$this->menu = $this->makeMenu('index,user');
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
	
	function clearAllTables()
	{
		$this->skipView = true;
		foreach(explode(',', 'auditlog,connections,entities,idaccounts,index,index_servers,messages,nvstore,servers,users,votes') as $table)
		  DB_Update('TRUNCATE '.getTableName($table));
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
      $ds = $this->model->post(array(
			  'owner' => array('_key' => $parentMessage['m_owner']),
				'author' => array('_key' => $this->user->entity),
        'text' => $_REQUEST['text'],
				'parent' => $parentMessage['m_id']));   
			$comments = array('list' => array($ds)); 	
			if(substr($_REQUEST['text'], 0, 1) != '#') 
			{
			  ob_start();
			  tmpl_commentlist($parentMessage, $comments, false);
			  $comment = ob_get_clean();
      }
      print(json_encode(array(
        'result' => 'OK',
        'post' => $comment,
        )));
    }
	}

  function ajax_post()
	{
		if(trim($_REQUEST['text']) != '')
		{
			$ds = $this->model->post(array(
        'text' => $_REQUEST['text'],
        'author' => array('_key' => $this->user->entity),
        'owner' => array('_key' => getDefault($_REQUEST['to'], $this->user->entity)),
        )); 
      ob_start();
			tmpl_postlist(array('list' => array($ds)), false);
			print(json_encode(array(
			  'post' => ob_get_clean(),
			  'result' => 'OK',
			  )));
		}
	}
	
	function ajax_vote()
	{
    if(substr($_REQUEST['text'], 0, 1) != '#') $_REQUEST['text'] = '#'.$_REQUEST['text'];
		$this->ajax_comment();
	}
	
}

?>