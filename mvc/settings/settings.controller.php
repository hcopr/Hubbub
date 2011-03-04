<?php

class SettingsController extends HubbubController
{
	function __init()
	{
    access_policy('auth');
		$this->menu = $this->makeMenu('index,auth,url');
		$this->invokeModel();
    include_once('lib/cq-forms.php');
	}
	
	function index()
	{
		$this->form = new CQForm('settings');
		$this->form->ds = &$this->user->settings;
    $this->form
      ->add('html', '<div class="balloonhelp">'.l10n('email.address').'</div>')
		  ->add('string', 'email')
		  ->add('html', l10n('email.notify'))
      ->add('checkbox', 'email_friendrequest')
      ->add('checkbox', 'email_wallpost')
      ->add('checkbox', 'email_comment')
      ->add('checkbox', 'email_response')
      ->add('checkbox', 'email_message')
		  ->add('html', '<br/>')
		  ->add('submit', 'save');
		if($this->form->submitted)
		{
			$this->form->getData();
			$this->user->ds['u_email'] = getDefault($this->user->ds['u_email'], $this->form->ds['email']);
			$this->user->save();
		}
	}
	
	function url()
	{		
	}
	
	function ajax_changeurl()
	{
	  access_policy('write');
    $this->skipView = false;
		$this->myEntityRecord = $this->user->selfEntity();
    unset($this->myEntityRecord['url']);
		$this->urlSuggestion = getDefault($_REQUEST['newurl'], $this->user->getUrl());
		if($_REQUEST['newurl'])
			$this->changeResult = $this->model->checkNewUrl($_REQUEST['newurl']);
	}
	
	function ajax_commiturl()
	{
	  access_policy('write');
    $this->skipView = false;
		if($_REQUEST['newurl'])
      $this->changeResult = $this->model->changeMyUrl($_REQUEST['newurl']);
    $this->myNewUrl = getDefault($_REQUEST['newurl'], $this->user->getUrl());
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
	
  function onOpenIDLogin(&$openid)
  {
    $ids = DB_GetDataset('idaccounts', $openid->identity, 'ia_url');
		if(sizeof($ids) > 0)
    {

    }
    else
    {
      // new account
      $uds = array('u_name' => '',);
      $ukey = DB_UpdateDataset('users', $uds);
      $ids = array(
        'ia_type' => 'openid',
        'ia_url' => $openid->identity,
        'ia_user' => $this->user->id,
        );
      DB_UpdateDataset('idaccounts', $ids);
    }   
    $this->redirect('auth');
  }

	
}

?>