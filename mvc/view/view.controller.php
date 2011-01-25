<?php

class ViewController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
		$this->invokeModel('profile');
    include_once('templates/postlist.php');
    $this->menu = $this->makeMenu('index', array(), array('id' => $_REQUEST['id']));
	}
	
	function index()
	{
		$this->viewEntity = $_REQUEST['id'];
	}
}

?>