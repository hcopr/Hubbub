<div class="balloonhelp">
  <?= $this->l10n('auth.balloon') ?>
</div>

<?

$idTypes = array(
  'fb' => array('Facebook'),
  'twitter' => array('Twitter'),
  'openid' => array('OpenID'),
	);

$openIDList = DB_GetList('SELECT * FROM '.getTableName('idaccounts').' WHERE ia_user=?', array($this->user->id));

foreach($openIDList as $ida)
{
	if(sizeof($openIDList) > 1 && $_REQUEST['del'] == $ida['ia_key']) 
	  DB_RemoveDataset('idaccounts', $ida['ia_key']);
	else
	{
		$p = parse_url($ida['ia_url']);
		$host = $p['host'];
		if(substr($host, 0, 4) == 'www.') $host = substr($host, 4);
		
		$svcType = $idTypes[$ida['ia_type']];
		
		?><div style="border-bottom: 1px solid gray;">
			<div style="float: right"><?
	      if(sizeof($openIDList) > 1) {
	      ?><a onclick="
	        if(confirm('<?= $this->l10n('auth.del.confirm') ?>'))
	        document.location.href='<?= actionUrl('auth', 'settings', array('del' => $ida['ia_key'])) ?>';" title="<?= $this->l10n('auth.del') ?>" class="btn">x</a><? } ?></div>
			<div class="emphasis"><?= $svcType[0] ?></div>
			<div class="smalltext"><?= getDefault($ida['ia_comments'], $host) ?></div>
		</div><br/><?
		
	}
}

$r = array('r' => actionUrl('auth', 'settings'));

?>
<div class="balloonhelp">Add another account:</div>
<a class="btn" href="<?= actionUrl('fb', 'signin', $r) ?>">Facebook</a>
<a class="btn" href="<?= actionUrl('twitter', 'signin', $r) ?>">Twitter</a>
<a class="btn" href="<?= actionUrl('google', 'signin', $r) ?>">Google</a>
<a class="btn" href="<?= actionUrl('yahoo', 'signin', $r) ?>">Yahoo</a>
