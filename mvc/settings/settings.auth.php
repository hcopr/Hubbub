<div class="balloonhelp">
  <?= l10n('auth.balloon') ?>
</div>
<div class="masonry_container"><?

$idTypes = array(
  'fb' => 'Facebook',
  'twitter' => 'Twitter',
  'openid' => '<img src="img/authtypes/openid.png" align="absmiddle" title="OpenID"/>',
  'google' => 'Google',
  'email' => 'Email',
	);

$openIDList = DB_GetList('SELECT * FROM '.getTableName('idaccounts').' 
  WHERE ia_user=? ORDER BY ia_url', array($this->user->id));

foreach($openIDList as $ida)
{
	if(sizeof($openIDList) > 1 && $_REQUEST['del'] == $ida['ia_key']) 
	{
	  DB_RemoveDataset('idaccounts', $ida['ia_key']);
	  $msg = h2_uibanner(l10n('auth.deleted'), 'fadeout');
	}
	else
	{
		$p = parse_url($ida['ia_url']);
		$host = strtolower($p['host']);
		if(substr($host, 0, 4) == 'www.') $host = substr($host, 4);
		$properties = json_decode($ida['ia_properties'], true);
		$svcType = $idTypes[$ida['ia_type']];
    $description = getDefault($ida['ia_comments'], $host); 
		
		switch($ida['ia_type'])
		{
		  case('email'): {
        $description = $ida['ia_url'];      
        break;
      }
      case('openid'): {
        if($host == 'google.com') $svcType = 'Google '.$svcType;
        if($host == 'yahoo.com') $svcType = 'Yahoo '.$svcType;
        if($host == 'yahoo.co.jp') $svcType = 'Yahoo Japan '.$svcType;
        if($host == 'yahoo.de') $svcType = 'Yahoo Germany '.$svcType;
        $desc = array();
        foreach($properties as $k => $v)
        {
          $caption = l10n(strtolower(trim($k)), true);
          if(trim($caption) != '')
            $desc[] = '<span style="color:gray">'.$caption.'</span>: '.htmlspecialchars($v).''; 
        }        
        if(sizeof($desc) > 0)
          $description = implode('<br/>', $desc);
        break;
      }
		  default: {

        break;
      }
    }
		
		?><div class="dynamic_box action_tile bubble added_extra">
			<div style="float: right"><?
	      if(sizeof($openIDList) > 1) {
	      ?><a onclick="
	        if(confirm('<?= l10n('auth.del.confirm') ?>'))
	        document.location.href='<?= actionUrl('auth', 'settings', array('del' => $ida['ia_key'])) ?>';" title="<?= l10n('auth.del') ?>" class="btn">x</a><? } ?></div>
			<div class="emphasis"><?= $svcType ?></div>
			<div style="background: #fff; padding: 4px; font-size: 90%;"><?= $description ?></div>
		</div><br/><?
		
	}
}

$r = array('r' => actionUrl('auth', 'settings'));

?></div><?

if($msg) print('<br/>'.$msg);

if(sizeof($openIDList) < 11)
{

  ?><div class="balloonhelp"><?= l10n('account.add') ?></div><?
  include('mvc/signin/signin.widget.php');

}
?>

