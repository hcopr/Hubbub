<?php

class AdminController extends HubbubController
{
	function __init()
	{
	  access_policy('auth,admin');
	}
	
	function index()
	{
	  
	}
}

?>