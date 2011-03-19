<?

$cfgData = getConfigData();

$themeManifestFile = 'themes/'.cfg('theme/name', 'default').'/manifest.php';
if(file_exists($themeManifestFile)) 
{
  include($themeManifestFile); 
  $colorSchemes = cfg('themeoptions/colorschemes');
}
else
{
  $colorSchemes = array('default');
}

$form = new CQForm('admin_server');
$form

  ->add('html', '<div class="form_section">'.l10n('basic.settings').'</div>')
  ->add('string', 'service/server')
  ->add('dropdown', 'theme/defaultcolor', array('options' => $colorSchemes, 'default' => 'default'))
  ->add('checkbox', 'service/url_rewrite')
  ->add('string', 'service/maxusers', array('default' => 30))
  ->add('checkbox', 'service/privateserver')
  
  ->add('html', '<div class="form_section">'.l10n('ping.settings').'</div>')
  ->add('checkbox', 'ping/remote')
  ->add('string', 'ping/pingservice')
  ->add('string', 'ping/password')

  ->add('html', '<div class="form_section">'.l10n('memcache.enable').'</div>')
  ->add('checkbox', 'memcache/enabled')
  ->add('string', 'memcache/server')
  
  ->add('html', '<div class="form_section">'.l10n('s3.enable').'</div>')
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

?><br/><hr/>
Advanced options: 
<a href="<?= actionUrl('index', 'test') ?>">run unit tests</a>