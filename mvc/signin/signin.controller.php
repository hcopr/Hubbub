<?php

class SigninController extends HubbubController
{
	function __init()
	{
		$this->invokeModel();
		if($_REQUEST['r'])
		{
			$_SESSION['redirect.override'] = $_REQUEST['r'];
		}
	}
	
	function Index()
	{
		// this is for the Twitter sign in option
		if(isset($_REQUEST['oauth_token']))
		{
			$this->model->completeOAuth($_REQUEST['oauth_token']);
      $this->user->login();
      $this->redirect('index', 'home');
		}
    
	}
	
	function logout()
	{
		$this->user->logout();
		$this->redirect('index', 'signin');
	}
		
	function fb()
	{
		
	}
	
	function twitter()
  {
  	
  }
	
	
	function openIdSignin($openid)
	{
	  $this->model->initOpenId($openid);
		if(isset($_REQUEST['openid_mode']))
		{
			if($this->model->openid->validate())
      {
        $this->model->completeOpenID($this->model->openid);
				$this->user->login();
        $this->redirect('index', 'home');
      }
			else
			{
			  $_SESSION['msg'] = 'Something went wrong when signing in with OpenID.';
			  #logToFile('log/openid.error.log', $_REQUEST);
				$this->redirect('index', 'signin');
			}
		}
	}
	
	function google()
  {
  	$this->openIdSignin('http://www.google.com/accounts/o8/id');
  }
	
	function yahoo()
	{
		$this->openIdSignin('https://me.yahoo.com');
	}
			
  function ajax_emaillogin()
  {
    $this->skipView = false;
    $this->idAccount = DB_GetDatasetMatch('idaccounts', array(
      'ia_type' => 'email',
      'ia_url' => trim($_REQUEST['email']).':'.md5(trim($_REQUEST['password'])),  
      ));
  }

  function ajax_emailsignup()
  {
    $this->skipView = false;
    /*$this->idAccount = DB_GetDatasetMatch('idaccounts', array(
      'ia_type' => 'email',
      'ia_url' => trim($_REQUEST['email']).':'.md5(trim($_REQUEST['password'])),  
      ));*/
  }
}

?>