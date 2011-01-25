<?php

class HomeController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
		$this->invokeModel('profile');
    $this->menu = $this->makeMenu('index');
	}
	
	function index()
	{
	}
}

?>