<?

    $myOldUsername = $this->user->getUsername();
    
    $ctr = 1;
	  // let's get the attributes exported to us from other services
    $twitterInfo = h2_nv_retrieve('twitterinfo');
  	$fbInfo = h2_nv_retrieve('fb.basic');
  	$openInfo = h2_nv_retrieve('openid');
		
		// let's try to determine sensible user data from the login services
		$idAccountWithEmail = DB_GetDatasetWQuery('SELECT * FROM '.getTableName('idaccounts').'
		  WHERE ia_user=? AND ia_type = "email"', array($this->user->key()));
		$defaultEmail = getDefault($openInfo['contact/email'], $fbInfo['email'], $idAccountWithEmail['ia_url']);
		// determining a default username idea
		$emlName = trim(substr($defaultEmail, 0, strpos($defaultEmail, '@')));
    $defaultUsername = safename(getDefault($twitterInfo['screen_name'], $fbInfo['first_name'], $emlName, $openInfo['namePerson/friendly']));   
    if($defaultUsername != '' && !HubbubEntity::isNameAvailable($defaultUsername)) 
		{
      do {
        $defaultUsername2 = $defaultUsername.$ctr++;
      } while (!HubbubEntity::isNameAvailable($defaultUsername2));
			$defaultUsername = $defaultUsername2;
		}
		
		// making a reasonable default name of of the email address
		$nameParts = array();
		foreach(explode('.', $defaultUsername) as $namePart)
		  $nameParts[] = strtoupper(substr($namePart, 0, 1)).strtolower(substr($namePart, 1));
		$emailNameReconstruction = implode(' ', $nameParts);
		
		// other default data
    $defaultName = getDefault(
      $openInfo['namePerson/first'].' '.$openInfo['namePerson/last'], 
      $fbInfo['name'], 
      $twitterInfo['name'], 
      $openInfo['namePerson/friendly'],
      $emailNameReconstruction);			
		$gravatarImg = 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($defaultEmail))).'&s=96';
		$defaultPic = getDefault($fbInfo['picture'], $twitterInfo['profile_image_url'], $gravatarImg);
		
    if($defaultPic != '') print('<img src="'.$defaultPic.'" align="right" style="padding: 8px;" width="96"/>');

?><div class="balloonhelp"><?= l10n('user.balloon') ?></div> 
<div style="width: 70%"><?
    include_once('lib/cq-forms.php');
    $this->form = new CQForm('basicinfo', array('auto-focus' => trim($defaultName) == ''));
    $this->form->ds = $this->user->ds;
		$this->form->ds['username'] = safename(getdefault($this->user->getUsername(), $this->form->ds['username']));
		
		if($this->user->getUsername() == '')
      $this->form->add('string', 'username', array('default' => $defaultUsername, 'validate' => 'notempty', 'filter' => 'safe'));
    else
      $this->form->add('readonly', 'username');
      
    $this->form
      ->add('string', 'u_name', array('default' => $defaultName, 'validate' => 'notempty'))
		  ->add('dropdown', 'u_l10n', array('options' => array('en' => 'English', 'de' => 'Deutsch')))
      ->add('submit', 'saveprofile');
		
		if($this->form->submitted)
		{
			if($this->form->getData())
			{
			  if($this->user->getUsername() == '' && !HubbubEntity::isNameAvailable($this->form->ds['username']))
			  {
				  $this->form->errors['username'] = l10n('username.notavailable');
			  }
				else
				{
					$this->model->setUsername($this->form->ds['username']);
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

?></div>
