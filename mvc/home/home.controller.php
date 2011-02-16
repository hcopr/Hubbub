<?php

class HomeController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
		$this->invokeModel('msg');
    $this->menu = $this->makeMenu('index');
	}
	
	function index()
	{
	}
}

?>