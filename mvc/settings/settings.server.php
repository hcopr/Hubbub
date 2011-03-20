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

$side_column_start = '</td></tr></table></td><td>&nbsp;</td><td valign="top"><br/><br/>';
$side_column_end = '</td></tr><tr><td valign="top"><table width="100%"><tr><td>';
$loadergif = '<img src="themes/default/ajax-loader.gif"/>';

?><table width="95%">
  <tr>
    
    <td width="50%" valign="top"><?
    
    $form = new CQForm('admin_server');
    $form
    
      ->add('html', '<div class="form_section">'.l10n('basic.settings').'</div>')
      ->add('string', 'service/server')
      ->add('dropdown', 'theme/defaultcolor', array('options' => $colorSchemes, 'default' => 'default'))
      ->add('checkbox', 'service/url_rewrite')
      ->add('string', 'service/maxusers', array('default' => 30))
      ->add('checkbox', 'service/privateserver')
      ->add('html', $side_column_start.'<div id="server_status">'.$loadergif.'</div>'.$side_column_end)
      
      ->add('html', '<div class="form_section">'.l10n('ping.settings').'</div>')
      ->add('checkbox', 'ping/remote')
      ->add('string', 'ping/pingservice')
      ->add('string', 'ping/password')
      ->add('html', $side_column_start.'<div id="ping_status">'.$loadergif.'</div>'.$side_column_end)
    
      ->add('html', '<div class="form_section">'.l10n('memcache.enable').'</div>')
      ->add('checkbox', 'memcache/enabled')
      ->add('string', 'memcache/server')
      ->add('html', $side_column_start.'<div id="memcache_status">'.$loadergif.'</div>'.$side_column_end)
      
      ->add('html', '<div class="form_section">'.l10n('s3.enable').'</div>')
      ->add('checkbox', 's3/enabled')
      ->add('string', 's3/access_key')
      ->add('string', 's3/secret_key')
      ->add('html', $side_column_start.'<div id="s3_status">'.$loadergif.'</div>'.$side_column_end)
    
      ->add('submit', 'save', l10n('save'))
      ->ds($cfgData)
      ->receive(function($ndata) {
          setConfigData($ndata+getConfigData());
          print(h2_uibanner(l10n('settings.saved'), true));
        })
      ->display();

    ?></td>

  </tr>
</table>
<br/><hr/>
Advanced options: 
<a href="<?= actionUrl('index', 'test') ?>">run unit tests</a>
<script>
  
  setInterval(function() {
    $.post('<?= actionUrl('ajax_servercheck', 'settings') ?>', function(data) {
      $('#server_status').html(data.server_status);
      $('#ping_status').html(data.ping_status);
      $('#memcache_status').html(data.memcache_status);
      $('#s3_status').html(data.s3_status);
      }, 'json');
    }, 2000);  
  
</script>