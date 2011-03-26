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
    $this->profileId = object('user')->entity;
    $this->isMyProfile = true;
	}

  function user()
  {
    
  }

  function __call($profileId, $args)
  {
    $this->viewName = 'index';
    $this->profileId = $profileId;
    $this->viewEntity = $this->profileId;
  }
		
}

?>