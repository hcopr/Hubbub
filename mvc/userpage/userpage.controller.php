<?php

class UserPageController extends HubbubController
{
	function __init()
	{
    $_REQUEST['action'] = 'index';
	}
	
	function index()
	{
		$this->info = $GLOBALS['msg']['entity'];
	}
}

?>