<?

$cfgData = getConfigData();

$form = new CQForm('admin_server');
$form

  ->add('html', '<b class="smalltext">'.l10n('basic.settings').'</b>')
  ->add('string', 'service/server')
  ->add('checkbox', 'service/url_rewrite')
  
  ->add('html', '<b class="smalltext">'.l10n('ping.settings').'</b>')
  ->add('checkbox', 'ping/remote')
  ->add('string', 'ping/pingservice')
  ->add('string', 'ping/password')

  ->add('html', '<b class="smalltext">'.l10n('memcache.enable').'</b>')
  ->add('checkbox', 'memcache/enabled')
  ->add('string', 'memcache/server')
  
  ->add('html', '<b class="smalltext">'.l10n('s3.enable').'</b>')
  ->add('checkbox', 's3/enabled')
  ->add('string', 's3/access_key')
  ->add('string', 's3/secret_key')

  ->add('submit', 'save', l10n('save'))
  ->ds($cfgData)
  ->receive(function($ndata) {
      setConfigData($ndata+getConfigData());
      print(h2_uibanner(l10n('settings.saved'), true));
    })
  ->display();

?>