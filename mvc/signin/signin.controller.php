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
        if(trim($_REQUEST['email']) == '' || trim($_REQUEST['password']) == '')
        {
          $msg = '<div class="banner">Please fill out both the email and password fields.</div>';
        }
        else
        {
          $_SESSION['load_signin'] = 'email';
          if($_REQUEST['mode'] == 'new')
          {
            
          }
          else
          {
            $ids = $this->model->getAccount(trim($_REQUEST['email']), md5(trim($_REQUEST['password'])));
            $msg = dumpArray($ids);
            /*
            if(sizeof($ids) == '' && $ids['ia_user'] > 0)
            {
              $msg = '<div class="banner">Wrong email/password, please check and try again.</div>';
            }
            else
            {
              object('user')->loginWithId($ids['ia_user']);
              $msg = '<img src="themes/default/ajax-loader.gif"/> signing in...';
              $url = actionUrl('index', 'home');
            }*/
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