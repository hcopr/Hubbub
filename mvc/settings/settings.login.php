<?

$cfgData = getConfigData();

$form = new CQForm('admin_login');
$form

  ->add('html', '<b class="smalltext">'.l10n('login.fb.enable').'</b>')
  ->add('checkbox', 'facebook/enabled')
  ->add('string', 'facebook/app_id')
  ->add('string', 'facebook/api_key')
  ->add('string', 'facebook/app_secret')
  
  ->add('html', '<b class="smalltext">'.l10n('login.twitter.enable').'</b>')
  ->add('checkbox', 'twitter/enabled')
  ->add('string', 'twitter/api_key')
  ->add('string', 'twitter/consumer_key')
  ->add('string', 'twitter/consumer_secret')

  ->add('submit', 'save', l10n('save'))
  ->ds($cfgData)
  ->receive(function($ndata) {
      setConfigData($ndata+getConfigData());
      print(h2_uibanner(l10n('settings.saved'), true));
    })
  ->display();

?>