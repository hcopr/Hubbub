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
		$srv = getDefault($_SERVER['HTTP_HOST'], l10n('this.server'));
    $this->srvName = strtoupper(substr($srv, 0, 1)).substr($srv, 1);
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
			  $_SESSION['msg'] = l10n('openid.fail');
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
			
  function recover()
  {
    require_once('lib/cq-forms.php');
    require_once('lib/special-io.php');
  }

  function reset()
  {
    require_once('lib/cq-forms.php');
    require_once('lib/special-io.php');
    $this->uds = DB_GetDataset('idaccounts', $_REQUEST['i'], 'ia_recovery');
    $this->userFound = $this->uds['ia_user'] > 0;
    if($this->userFound) 
      $this->usr = DB_GetDataset('users', $this->uds['ia_user']);
    else
    {
      $this->skipView = true;
      print('<br/><br/><div class="banner">'.l10n('email.recovery.failload').'</div><br/>&gt; <a href="'.actionUrl('index', 'signin').'">'.l10n('cancel').'</a>'); 
    }
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
          $msg = '<div class="banner">'.l10n('openid.please').'</div>';
        }
        else
        {
          $_SESSION['myopenidurl'] = trim($_REQUEST['openid']);
          $_SESSION['load_signin'] = 'openid';
          $msg = l10n('openid.signing.in').' '.$_SESSION['myopenidurl'].'...';
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
          $msg = '<div class="banner">'.l10n('fillout.fields').'</div>';
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
              $msg = '<div class="banner">'.l10n('email.inuse').'</div>';
            }
            else
            {
              if(strlen($loginPassword) < 5)
              {
                $msg = '<div class="banner">'.l10n('email.password.tooshort').'</div>';
              }
              else if(is_email($emailAddress, true, E_WARNING) != ISEMAIL_VALID)
              {
                $msg = '<div class="banner">'.l10n('email.invalid').'</div>';
              }
              else
              {
                $msg = l10n('email.creating.account').'...';
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
              $msg = '<img src="themes/default/ajax-loader.gif"/> '.l10n('email.signing.in').'...';
              $url = actionUrl('index', 'home');
            }
            else
            {
              $msg = '<div class="banner">'.l10n('email.login.fail').'<br/><a href="'.actionUrl('recover', 'signin').'">'.l10n('email.recover').'</a></div>';
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