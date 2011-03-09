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
		include_once('lib/cq-forms.php');
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
  	$this->openIdSignin('https://www.google.com/accounts/o8/id');
  }
	
	function openid()
  {
  	$this->openIdSignin($_REQUEST['id']);
  }
	
	function yahoo()
	{
		$this->openIdSignin('https://me.yahoo.com');
	}
			
  function ajax_do()
  {
    $msg = '';
    $url = '';
    switch($_REQUEST['method'])
    {
      case('openid'): {
        if(trim($_REQUEST['openid']) == '')
        {
          $msg = '<div class="banner">Please enter your OpenID URL into the field above.</div>';
        }
        else
        {
          $_SESSION['myopenidurl'] = trim($_REQUEST['openid']);
          $_SESSION['load_signin'] = 'openid';
          $msg = 'Signing in with OpenID URL '.$_SESSION['myopenidurl'].'...';
          $url = actionUrl('openid', 'signin', array('id' => $_SESSION['myopenidurl']));
        }
        break;
      }
      case('email'): {
        $emailAddress = trim(strtolower($_REQUEST['email']));
        $loginPassword = trim($_REQUEST['password']);
        $passwordHash = md5($emailAddress.$loginPassword);
        if($emailAddress == '' || $loginPassword == '')
        {
          $msg = '<div class="banner">Please fill out both the email and password fields.</div>';
        }
        else
        {
          require_once('lib/is_email.php');
          $_SESSION['load_signin'] = 'email';
          if($_REQUEST['mode'] == 'new')
          {
            $nds = $this->model->getAccount('email', $emailAddress);
            if($ids['ia_user'] > 0)
            {
              $msg = '<div class="banner">This email address is already in use on this server, you cannot create a new account with it. Please sign in normally.</div>';
            }
            else
            {
              if(strlen($loginPassword) < 5)
              {
                $msg = '<div class="banner">For your own safety, please choose a password that has at least 5 characters.</div>';
              }
              else if(is_email($emailAddress, true, E_WARNING) != ISEMAIL_VALID)
              {
                $msg = '<div class="banner">Please enter a valid email address.</div>';
              }
              else
              {
                $msg = 'Your account is being created...';
                $nds['ia_comments'] = $passwordHash;
                $this->model->newAccount($nds);
                $url = actionUrl('user', 'profile');
              }
            }
          }
          else
          {
            $ids = $this->model->getAccount('email', $emailAddress);
            if($ids['ia_user'] > 0 && $ids['ia_comments'] == $passwordHash)
            {
              object('user')->loginWithId($ids['ia_user']);
              $msg = '<img src="themes/default/ajax-loader.gif"/> signing in...';
              $url = actionUrl('index', 'home');
            }
            else
            {
              $msg = '<div class="banner">Wrong email/password, please check and try again.</div>';
            }
          }
        }
        break; 
      }
    }
    
    print(json_encode(array(
      'html' => $msg,
      'url' => $url,
      ))); 
  }

}

?>