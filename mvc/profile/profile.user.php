<?

    $myOldUsername = $this->user->getUsername();
    
    $ctr = 1;
	  // let's get the attributes exported to us from other services
    $twitterInfo = h2_nv_retrieve('twitterinfo');
  	$fbInfo = h2_nv_retrieve('fb.basic');
  	$openInfo = h2_nv_retrieve('openid');
		
		// let's try to determine sensible user data from the login services
		$defaultEmail = getFirst($openInfo['contact/email'], $fbInfo['email'], $this->user->ds['u_email']);
		// determining a default username idea
		$emlName = trim(substr($defaultEmail, 0, strpos($defaultEmail, '@')));
    $defaultUsername = safename(getFirst($twitterInfo['screen_name'], $fbInfo['first_name'], $emlName, $openInfo['namePerson/friendly']));   
    if($defaultUsername != '' && !HubbubEntity::isNameAvailable($defaultUsername)) 
		{
      do {
        $defaultUsername2 = $defaultUsername.$ctr++;
      } while (!HubbubEntity::isNameAvailable($defaultUsername2));
			$defaultUsername = $defaultUsername2;
		}
		// other default data
    $defaultName = getFirst($openInfo['namePerson/first'].' '.$openInfo['namePerson/last'], $fbInfo['name'], $twitterInfo['name'], $openInfo['namePerson/friendly']);		
		$gravatarImg = 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($defaultEmail))).'&s=48';
		$defaultPic = getFirst($fbInfo['picture'], $twitterInfo['profile_image_url'], $gravatarImg);
		
?><div class="balloonhelp"><?

if($defaultPic != '') print('<img src="'.$defaultPic.'" align="left" style="padding-right: 8px"/>');

print($this->l10n('user.balloon'));
	
?></div>
<? 
    
    include_once('lib/cq-forms.php');
    $this->form = new CQForm('basicinfo');
    $this->form->ds = $this->user->ds;
		$this->form->ds['username'] = safename(getdefault($this->user->getUsername(), $this->form->ds['username']));
		if($this->user->getUsername() == '')
      $this->form->add('string', 'username', $this->l10n('username'), array('default' => $defaultUsername, 'validate' => 'notempty', 'filter' => 'safe'));
    else
      $this->form->add('readonly', 'username', $this->l10n('username'));
    $this->form->add('string', 'u_name', $this->l10n('u_name'), array('default' => $defaultName, 'validate' => 'notempty'));
		$this->form->add('dropdown', 'u_l10n', $this->l10n('u_l10n'), array('options' => array('en' => 'English', 'de' => 'Deutsch')));
    $this->form->add('submit', 'saveprofile');
		
		if($this->form->submitted)
		{
			if($this->form->getData())
			{
			  if($this->user->getUsername() == '' && !HubbubEntity::isNameAvailable($this->form->ds['username']))
			  {
				  $this->form->errors['username'] = $this->l10n('username.notavailable');
			  }
				else
				{
					$this->user->setUsername($this->form->ds['username']);
				}
				if(sizeof($this->form->errors) == 0)
				{
	        $this->user->ds['u_name'] = trim(strip_tags($this->form->ds['u_name']));
	        $this->user->ds['u_l10n'] = trim(strip_tags($this->form->ds['u_l10n']));
	        $this->user->save();
	        redirect(actionUrl('index', 'home'));
				}
			}			
		}

    $this->form->display();

?>