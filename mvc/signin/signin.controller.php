<?php

class SigninController extends HubbubController
{
	function __init()
	{
		$this->invokeModel();
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
      $this->redirectAfterSignin();
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
				$this->redirectAfterSignin();
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
      print('<br/><br/>'.h2_uibanner(l10n('email.recovery.failload')).'<br/>&gt; 
        <a href="'.actionUrl('index', 'signin').'">'.l10n('cancel').'</a>'); 
    }
  }
  
  function redirectAfterSignin()
  {
    if(is_array($this->model->redirectOverride))
    {
      $this->redirect($this->model->redirectOverride[0], $this->model->redirectOverride[1]);
    }
    else
      $this->redirect('index', 'home'); 
  }
  
  function getUrlAfterSignin()
  {
    if(is_array($this->model->redirectOverride))
    {
      return(actionUrl($this->model->redirectOverride[0], $this->model->redirectOverride[1]));
    }
    else
      return(actionUrl('index', 'home')); 
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
          $msg = h2_uibanner(l10n('openid.please'));
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
          $msg = h2_uibanner(l10n('fillout.fields'));
        }
        else
        {
          require_once('lib/is_email.php');
          $_SESSION['load_signin'] = 'email';
          if($_REQUEST['mode'] == 'new')
          {
            $nds = $this->model->getAccount('email', $emailAddress);
            if($nds['ia_user'] > 0)
            {
              $msg = h2_uibanner(l10n('email.inuse'));
            }
            else
            {
              if(strlen($loginPassword) < 5)
              {
                $msg = h2_uibanner(l10n('email.password.tooshort'));
              }
              else if(is_email($emailAddress, true, E_WARNING) != ISEMAIL_VALID)
              {
                $msg = h2_uibanner(l10n('email.invalid'));
              }
              else
              {
                $msg = l10n('email.creating.account').'...';
                $nds['ia_password'] = $passwordHash;
                $this->model->newAccount($nds);
                $url = $this->getUrlAfterSignin();
              }
            }
          }
          else
          {
            $ids = $this->model->getAccount('email', $emailAddress);
            if($ids['ia_user'] > 0 && $ids['ia_password'] == $passwordHash)
            {
              object('user')->loginWithId($ids['ia_user']);
              $msg = '<img src="themes/default/ajax-loader.gif"/> '.l10n('email.signing.in').'...';
              $url = $this->getUrlAfterSignin();
            }
            else
            {
              $msg = h2_uibanner(l10n('email.login.fail').'<br/><a href="'.actionUrl('recover', 'signin').'">'.l10n('email.recover').'</a>');
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