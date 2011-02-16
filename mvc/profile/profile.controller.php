<?php

class ProfileController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
		$this->menu = $this->makeMenu('index,user');
		$this->invokeModel();
		$this->invokeModel('msg');
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
	
}

?>