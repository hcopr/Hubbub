<?php

class HomeController extends HubbubController
{
	function __init()
	{
    access_authenticated_only();
		$this->invokeModel('profile');
    $this->menu = $this->makeMenu('index');
	}
	
	function index()
	{
	}
}

?>