<div id="tabs" style="margin-top: -9px;">
  <ul><? $selectTab = 0; ?>
    <li><a href="#tabs-1"><?= $this->l10n('add.byusername') ?></a></li>
    <li><a href="#tabs-2"><?= $this->l10n('add.search') ?></a></li>
    <li><a href="#tabs-3"><?= $this->l10n('add.invite') ?></a></li>
  </ul>
  <div id="tabs-1">
    <div class="balloonhelp"><?= $this->l10n('add.byusername.balloon') ?></div>
		<blockquote><?= $this->user->getUrl() ?></blockquote>
		<?
		$form = new CQForm('addbyurl');
		
		$form->add('string', 'friendurl', array('validate' => 'notempty', 'default' => cfg('service.server').'/'));
		$form->add('submit', $this->l10n('addbyurl.btn'));
		$form->ds = $_REQUEST;
		
    $form->display();   

		if($form->submitted)		
		{
			$selectTab = 0;
			$form->getData();
			$result = $this->model->loadUrl($form->ds['friendurl']);		

			if($result['result'] != 'OK')
			{
				?><div style="padding-top: 16px" class="merde"><?= $this->l10n('friendurl_invalid') ?></div><?php 
			}	
			else
      {
        ?><div style="padding-top: 16px" id="div_friendadd"><?= $this->l10n('friendurl_found') ?>:
        <blockquote>
	        <table width="100%"><tr><td valign="top" width="138">
	        &nbsp;
	      </td><td width="50%" valign="top">
	        <div>Name: <b><?= $result['entity']['name'] ?></b></div>
          <div><a class="btn" onclick="$.post('<?= actionUrl('ajax_addbyurl', 'friends') ?>', 
		    { 'user' : '<?= addslashes($result['entity']['user']) ?>', 'server' : '<?= addslashes($result['entity']['server']) ?>' }, 
			function(data) { $('#div_friendadd').html(data); });"><?= $this->l10n('friend.addnow') ?></a></div>
        </td><td width="50%" valign="top" style="color: gray">
          Details:
          <div>Server: <b><?= $result['entity']['server'] ?></b></div>
          <div>Benutzername: <b><?= $result['entity']['user'] ?></b></div>
        </td></tr></table></blockquote></div><?php 
      } 
			
		}
		
		?>
  </div>
  <div id="tabs-2">

  </div>
  <div id="tabs-3">

  </div>
</div>

<script type="text/javascript">

  $("#tabs").tabs().tabs('select', <?= $selectTab ?>);

</script>

