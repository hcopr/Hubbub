<?
if($this->user->ds['u_name'] == '') 
  print(l10n('user.firstuse'));
else
  print(l10n('user.balloon'));
	
?>
<br/>
<br/>
<?

    include_once('lib/cq-forms.php');
    $this->form = new CQForm('basicinfo');
    $this->form->ds = $this->user->ds;
    $this->form
      ->add('string', 'u_name', l10n('u_name'), array('validate' => 'notempty'))
		  ->add('dropdown', 'u_l10n', l10n('u_l10n'), array('options' => array('en' => 'English', 'de' => 'Deutsch')))
		  ->add('file', 'pic', l10n('pic'))
      ->add('submit', 'OK');

    $this->form->display();
		
		if($this->form->submitted)
		{
			if($this->form->getData())
			{
        $this->user->ds['u_name'] = trim(strip_tags($this->form->ds['u_name']));
        $this->user->ds['u_l10n'] = trim(strip_tags($this->form->ds['u_l10n']));
				DB_UpdateDataset('users', $this->user->ds);
				redirect(actionUrl('index', 'home'));
			}			
		}

?>